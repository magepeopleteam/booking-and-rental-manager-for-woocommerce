<?php
/**
 * Coupon Engine — the mode-agnostic brain. Validates a coupon against a normalized context,
 * computes the per-line discount, and reconciles manual + automatic coupons with stacking.
 *
 * Everything here is authoritative and server-side: the engine trusts only the coupon CODE and
 * the coupon's own configuration — never a client-sent discount amount. Both booking modes call
 * the same resolve() so behaviour is identical.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Engine' ) ) {
	class RBFW_Coupon_Engine {

		/* -------------------------------------------------------------------------
		 * Global on/off
		 * ---------------------------------------------------------------------- */

		/**
		 * The Settings API checkbox stores 'on' when checked and 'off' when unchecked (it renders a
		 * hidden "off" input). Before the tab is ever saved the key is absent — default to enabled.
		 */
		public static function is_enabled() {
			$opt    = get_option( 'rbfw_coupon_settings' );
			$enable = ( is_array( $opt ) && isset( $opt['rbfw_coupon_enable'] ) ) ? $opt['rbfw_coupon_enable'] : 'on';
			return (bool) apply_filters( 'rbfw_coupon_engine_enabled', 'on' === $enable );
		}

		/** A setting from the Coupons settings tab. */
		public static function setting( $key, $default = '' ) {
			$opt = get_option( 'rbfw_coupon_settings' );
			return ( is_array( $opt ) && isset( $opt[ $key ] ) && '' !== $opt[ $key ] ) ? $opt[ $key ] : $default;
		}

		/* -------------------------------------------------------------------------
		 * Validation
		 * ---------------------------------------------------------------------- */

		/**
		 * @return array{valid:bool,reason:string}
		 */
		public static function validate( RBFW_Coupon $coupon, array $ctx ) {
			$fail = static function ( $reason ) {
				return array( 'valid' => false, 'reason' => $reason );
			};

			if ( ! $coupon->exists() || ! $coupon->is_active() ) {
				return $fail( esc_html__( 'This coupon is not available.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Validity window is evaluated against "now" (coupon live?), in GMT.
			$today = gmdate( 'Y-m-d' );
			$from  = $coupon->get_valid_from();
			$to    = $coupon->get_valid_to();
			if ( $from && $today < $from ) {
				return $fail( esc_html__( 'This coupon is not active yet.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			if ( $to && $today > $to ) {
				return $fail( esc_html__( 'This coupon has expired.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Booking-date based rules use the context date (earliest booking start).
			$booking_date = isset( $ctx['date'] ) ? $ctx['date'] : $today;
			$weekdays     = $coupon->get_weekdays();
			if ( $weekdays ) {
				$w = (int) gmdate( 'w', strtotime( $booking_date ) );
				if ( ! in_array( $w, $weekdays, true ) ) {
					return $fail( esc_html__( 'This coupon is not valid for the selected booking day.', 'booking-and-rental-manager-for-woocommerce' ) );
				}
			}
			if ( in_array( $booking_date, $coupon->get_blackout_dates(), true ) ) {
				return $fail( esc_html__( 'This coupon cannot be used for the selected date.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Targeting — the coupon must apply to at least one line in the cart/booking.
			$targeted = self::get_targeted_lines( $coupon, $ctx );
			if ( empty( $targeted ) ) {
				return $fail( esc_html__( 'This coupon does not apply to the selected item(s).', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Spend window (evaluated on the whole booking subtotal).
			$subtotal = isset( $ctx['subtotal'] ) ? (float) $ctx['subtotal'] : 0.0;
			$min      = $coupon->get_min_amount();
			$max      = $coupon->get_max_amount();
			if ( $min > 0 && $subtotal < $min ) {
				return $fail( sprintf(
					/* translators: %s: minimum amount */
					esc_html__( 'A minimum booking amount of %s is required for this coupon.', 'booking-and-rental-manager-for-woocommerce' ),
					wp_strip_all_tags( wc_price( $min ) )
				) );
			}
			if ( $max > 0 && $subtotal > $max ) {
				return $fail( esc_html__( 'This coupon is not valid for a booking of this amount.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Eligibility.
			$user_id = isset( $ctx['user_id'] ) ? absint( $ctx['user_id'] ) : 0;
			$email   = isset( $ctx['email'] ) ? strtolower( (string) $ctx['email'] ) : '';

			$roles = $coupon->get_allowed_roles();
			if ( $roles ) {
				$user       = $user_id ? get_userdata( $user_id ) : null;
				$user_roles = ( $user && ! empty( $user->roles ) ) ? (array) $user->roles : array();
				if ( ! array_intersect( $roles, $user_roles ) ) {
					return $fail( esc_html__( 'This coupon is not available for your account.', 'booking-and-rental-manager-for-woocommerce' ) );
				}
			}

			$emails = $coupon->get_allowed_emails();
			if ( $emails && ! in_array( $email, $emails, true ) ) {
				return $fail( esc_html__( 'This coupon is restricted to specific customers.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			if ( $coupon->is_first_booking_only() && self::has_prior_bookings( $user_id, $email ) ) {
				return $fail( esc_html__( 'This coupon is valid on your first booking only.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Usage limits.
			$cid = $coupon->get_id();
			if ( $coupon->get_usage_limit() > 0 && RBFW_Coupon_Usage::count_total( $cid ) >= $coupon->get_usage_limit() ) {
				return $fail( esc_html__( 'This coupon has reached its usage limit.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			if ( $coupon->get_usage_limit_per_user() > 0
				&& RBFW_Coupon_Usage::count_for_user( $cid, $user_id, $email ) >= $coupon->get_usage_limit_per_user() ) {
				return $fail( esc_html__( 'You have already used this coupon the maximum number of times.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			if ( $coupon->get_usage_limit_per_day() > 0
				&& RBFW_Coupon_Usage::count_for_day( $cid ) >= $coupon->get_usage_limit_per_day() ) {
				return $fail( esc_html__( 'This coupon has reached its daily usage limit. Please try again later.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			/**
			 * Final validity gate — lets Pro add extra conditions.
			 *
			 * @param bool        $valid  Currently true.
			 * @param RBFW_Coupon $coupon The coupon.
			 * @param array       $ctx    The context.
			 */
			$valid = (bool) apply_filters( 'rbfw_coupon_is_valid', true, $coupon, $ctx );
			if ( ! $valid ) {
				return $fail( esc_html__( 'This coupon cannot be applied to your booking.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			return array( 'valid' => true, 'reason' => '' );
		}

		/* -------------------------------------------------------------------------
		 * Targeting
		 * ---------------------------------------------------------------------- */

		/**
		 * The lines in $ctx this coupon applies to (include filters minus exclude filters).
		 *
		 * @return array List of item rows from $ctx['items'].
		 */
		public static function get_targeted_lines( RBFW_Coupon $coupon, array $ctx ) {
			$out = array();
			foreach ( ( isset( $ctx['items'] ) ? $ctx['items'] : array() ) as $item ) {
				if ( self::item_is_targeted( $coupon, $item ) ) {
					$out[] = $item;
				}
			}
			return $out;
		}

		protected static function item_is_targeted( RBFW_Coupon $coupon, array $item ) {
			$id        = isset( $item['item_id'] ) ? absint( $item['item_id'] ) : 0;
			$names     = isset( $item['rent_type_names'] ) ? (array) $item['rent_type_names'] : array();
			$rent_type = isset( $item['rent_type'] ) ? (string) $item['rent_type'] : '';
			$locations = isset( $item['locations'] ) ? (array) $item['locations'] : array();

			// Rent-type targeting matches either a category NAME or the rent_type slug, for flexibility.
			$type_haystack = array_merge( $names, array( $rent_type ) );

			// Exclusions win.
			if ( in_array( $id, $coupon->get_exclude_items(), true )
				|| array_intersect( $type_haystack, $coupon->get_exclude_rent_types() )
				|| array_intersect( $locations, $coupon->get_exclude_locations() ) ) {
				return false;
			}

			if ( $coupon->targets_everything() ) {
				return true;
			}

			// Union across include dimensions.
			return in_array( $id, $coupon->get_target_items(), true )
				|| (bool) array_intersect( $type_haystack, $coupon->get_target_rent_types() )
				|| (bool) array_intersect( $locations, $coupon->get_target_locations() );
		}

		/* -------------------------------------------------------------------------
		 * Discount calculation
		 * ---------------------------------------------------------------------- */

		/**
		 * Compute the per-line discount for one coupon.
		 *
		 * @param array $available Optional map line_key => remaining base (for stacking). When a
		 *                         line is present here its value is used as the base ceiling.
		 * @return array{total:float,per_line:array<string,float>}
		 */
		public static function calculate_discount( RBFW_Coupon $coupon, array $ctx, $available = null ) {
			$decimals = wc_get_price_decimals();
			$targeted = self::get_targeted_lines( $coupon, $ctx );
			if ( empty( $targeted ) ) {
				return array( 'total' => 0.0, 'per_line' => array() );
			}

			// Base per targeted line: remaining balance if stacking, else the (filtered) line base.
			$base = array();
			foreach ( $targeted as $item ) {
				$key = (string) $item['line_key'];
				if ( is_array( $available ) && array_key_exists( $key, $available ) ) {
					$base[ $key ] = max( 0, (float) $available[ $key ] );
				} else {
					$base[ $key ] = max( 0, (float) apply_filters( 'rbfw_coupon_line_base', self::line_base( $item ), $item, $coupon ) );
				}
			}

			$type     = $coupon->get_discount_type();
			$value    = $coupon->get_discount_value();
			$per_line = array();

			if ( 'free_days' === $type ) {
				// Per-line, independent: free N billed-units at that line's effective per-unit rate.
				// The rate comes from the DURATION price (services/fees are not per-day), but the
				// resulting share is still capped by the line's remaining discountable base.
				foreach ( $targeted as $item ) {
					$key   = (string) $item['line_key'];
					$units = isset( $item['duration_units'] ) ? (float) $item['duration_units'] : 1.0;
					$units = $units > 0 ? $units : 1.0;
					$free  = min( $value, $units );

					$duration = isset( $item['duration_price'] ) ? (float) $item['duration_price'] : self::line_base( $item );
					$duration = min( $duration, $base[ $key ] ); // stacking may have eaten into it
					$rate     = $duration / $units;

					$share            = min( $base[ $key ], $free * $rate );
					$per_line[ $key ] = round( $share, $decimals );
				}
			} else {
				// percentage / fixed → a target total distributed across lines proportional to base.
				$sum_base = array_sum( $base );
				if ( $sum_base <= 0 ) {
					return array( 'total' => 0.0, 'per_line' => array() );
				}

				if ( 'percentage' === $type ) {
					$pct    = min( 100, max( 0, $value ) ) / 100;
					$target = $sum_base * $pct;
					$cap    = $coupon->get_max_discount();
					if ( $cap > 0 && $target > $cap ) {
						$target = $cap;
					}
				} else { // fixed
					$target = min( $value, $sum_base );
				}

				$per_line = self::distribute( $target, $base, $decimals );
			}

			$total = 0.0;
			foreach ( $per_line as $k => $amt ) {
				$amt            = max( 0, (float) $amt );
				$per_line[ $k ] = $amt;
				$total         += $amt;
			}

			return array( 'total' => round( $total, $decimals ), 'per_line' => $per_line );
		}

		/**
		 * The discountable base of a line: the rental subtotal (line total minus mandatory
		 * management fee). Falls back gracefully for contexts that only carry a duration price.
		 *
		 * @param array $item
		 * @return float
		 */
		public static function line_base( array $item ) {
			if ( isset( $item['base_price'] ) ) {
				return max( 0, (float) $item['base_price'] );
			}
			if ( isset( $item['duration_price'] ) ) {
				return max( 0, (float) $item['duration_price'] );
			}
			return isset( $item['line_total'] ) ? max( 0, (float) $item['line_total'] ) : 0.0;
		}

		/**
		 * Distribute a total across weighted buckets using largest-remainder rounding so the
		 * rounded parts sum EXACTLY to the (rounded) total. Keys are preserved.
		 *
		 * @param float                $total
		 * @param array<string,float>  $weights key => weight (also the per-line ceiling)
		 * @param int                  $decimals
		 * @return array<string,float>
		 */
		protected static function distribute( $total, array $weights, $decimals ) {
			$out = array();
			foreach ( $weights as $k => $w ) {
				$out[ $k ] = 0.0;
			}
			$sum_w = array_sum( $weights );
			if ( $total <= 0 || $sum_w <= 0 ) {
				return $out;
			}

			$factor      = (int) pow( 10, max( 0, (int) $decimals ) );
			$total_units = (int) round( $total * $factor );

			$floor_units = array();
			$remainders  = array();
			$assigned    = 0;
			foreach ( $weights as $k => $w ) {
				$exact          = $total_units * ( $w / $sum_w );
				$floor          = (int) floor( $exact );
				$floor_units[ $k ] = $floor;
				$remainders[ $k ]  = $exact - $floor;
				$assigned         += $floor;
			}

			$leftover = $total_units - $assigned;
			if ( $leftover > 0 ) {
				arsort( $remainders );
				foreach ( array_keys( $remainders ) as $k ) {
					if ( $leftover <= 0 ) {
						break;
					}
					$floor_units[ $k ]++;
					$leftover--;
				}
			}

			foreach ( $floor_units as $k => $units ) {
				// Never exceed the line's own ceiling.
				$out[ $k ] = min( (float) $weights[ $k ], $units / $factor );
			}
			return $out;
		}

		/* -------------------------------------------------------------------------
		 * Resolution — manual + automatic + stacking
		 * ---------------------------------------------------------------------- */

		/**
		 * Resolve the discount for a context, given an optional manually entered code.
		 *
		 * @return array{
		 *   applied:array<int,array{id:int,code:string,type:string,amount:float}>,
		 *   per_line:array<string,float>,
		 *   total_discount:float,
		 *   manual_error:string
		 * }
		 */
		public static function resolve( array $ctx, $manual_code = '' ) {
			$empty = array( 'applied' => array(), 'per_line' => array(), 'total_discount' => 0.0, 'manual_error' => '' );

			if ( ! self::is_enabled() || empty( $ctx['items'] ) ) {
				return $empty;
			}

			$manual       = null;
			$manual_error = '';
			$manual_code  = RBFW_Coupon::normalize_code( $manual_code );

			// Cheap short-circuit: nothing typed and no automatic rules exist → no work to do.
			if ( '' === $manual_code && ! RBFW_Coupon::has_auto_coupons() ) {
				return $empty;
			}
			if ( '' !== $manual_code ) {
				$candidate = RBFW_Coupon::load_by_code( $manual_code );
				if ( ! $candidate ) {
					$manual_error = esc_html__( 'Invalid coupon code.', 'booking-and-rental-manager-for-woocommerce' );
				} else {
					$v = self::validate( $candidate, $ctx );
					if ( $v['valid'] ) {
						$manual = $candidate;
					} else {
						$manual_error = $v['reason'];
					}
				}
			}

			// Valid automatic coupons, priority-ordered.
			$autos = array();
			foreach ( RBFW_Coupon::get_active_auto_coupons() as $auto ) {
				if ( $manual && $auto->get_id() === $manual->get_id() ) {
					continue; // don't double-apply a coupon that is also the manual one
				}
				$v = self::validate( $auto, $ctx );
				if ( $v['valid'] ) {
					$autos[] = $auto;
				}
			}

			// Build the ordered apply list, honouring stacking.
			$list = array();
			if ( $manual ) {
				$list[] = $manual;
				if ( $manual->allows_combine() ) {
					foreach ( $autos as $a ) {
						if ( $a->allows_combine() ) {
							$list[] = $a;
						}
					}
				}
			} elseif ( $autos ) {
				$best   = $autos[0]; // highest priority
				$list[] = $best;
				if ( $best->allows_combine() ) {
					foreach ( array_slice( $autos, 1 ) as $a ) {
						if ( $a->allows_combine() ) {
							$list[] = $a;
						}
					}
				}
			}

			// Apply sequentially against remaining per-line balances.
			$remaining = array();
			foreach ( $ctx['items'] as $item ) {
				$key               = (string) $item['line_key'];
				$remaining[ $key ] = max( 0, (float) apply_filters( 'rbfw_coupon_line_base', self::line_base( $item ), $item, null ) );
			}

			$per_line_merged = array();
			$applied         = array();
			foreach ( $list as $coupon ) {
				$res = self::calculate_discount( $coupon, $ctx, $remaining );
				if ( $res['total'] <= 0 ) {
					continue;
				}
				foreach ( $res['per_line'] as $key => $amt ) {
					$remaining[ $key ]       = max( 0, ( isset( $remaining[ $key ] ) ? $remaining[ $key ] : 0 ) - $amt );
					$per_line_merged[ $key ] = ( isset( $per_line_merged[ $key ] ) ? $per_line_merged[ $key ] : 0 ) + $amt;
				}
				$applied[] = array(
					'id'     => $coupon->get_id(),
					'code'   => $coupon->get_code(),
					'type'   => $coupon->get_discount_type(),
					'amount' => $res['total'],
				);
			}

			$total = round( array_sum( $per_line_merged ), wc_get_price_decimals() );

			$result = array(
				'applied'        => $applied,
				'per_line'       => $per_line_merged,
				'total_discount' => $total,
				'manual_error'   => $manual_error,
			);

			/**
			 * Fires after resolution. Read-only observation seam for Pro (analytics etc.).
			 *
			 * @param array $result
			 * @param array $ctx
			 */
			do_action( 'rbfw_coupon_applied', $result, $ctx );

			return $result;
		}

		/* -------------------------------------------------------------------------
		 * Usage recording (delegates to RBFW_Coupon_Usage)
		 * ---------------------------------------------------------------------- */

		public static function record_usage( $code, $object, $user_id = 0, $email = '', $amount = 0.0 ) {
			$coupon = RBFW_Coupon::load_by_code( $code );
			if ( ! $coupon ) {
				// Fall back to any coupon (incl. inactive) so a later-deactivated coupon still counts.
				$coupon = self::load_any_by_code( $code );
			}
			if ( ! $coupon ) {
				return false;
			}
			return RBFW_Coupon_Usage::record( $coupon->get_id(), $object, $user_id, $email, $amount );
		}

		/** Load a coupon by code regardless of active/inactive status (for usage recording). */
		protected static function load_any_by_code( $code ) {
			$code = RBFW_Coupon::normalize_code( $code );
			if ( '' === $code ) {
				return null;
			}
			$q = new WP_Query( array(
				'post_type'      => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => 'rbfw_code', 'value' => $code, 'compare' => '=' ),
				),
			) );
			return empty( $q->posts ) ? null : new RBFW_Coupon( (int) $q->posts[0] );
		}

		/* -------------------------------------------------------------------------
		 * Helpers
		 * ---------------------------------------------------------------------- */

		/** Whether the user/email already has a prior booking or WooCommerce order. */
		protected static function has_prior_bookings( $user_id, $email ) {
			$user_id = absint( $user_id );
			$email   = sanitize_email( $email );

			if ( $user_id && function_exists( 'wc_get_customer_order_count' ) && wc_get_customer_order_count( $user_id ) > 0 ) {
				return true;
			}

			$meta_query = array( 'relation' => 'OR' );
			if ( $user_id ) {
				$meta_query[] = array( 'key' => 'rbfw_user_id', 'value' => $user_id, 'compare' => '=' );
			}
			if ( $email ) {
				$meta_query[] = array( 'key' => 'rbfw_customer_email', 'value' => $email, 'compare' => '=' );
			}
			if ( count( $meta_query ) < 2 ) {
				return false; // nothing to match on
			}

			$q = new WP_Query( array(
				'post_type'      => 'rbfw_booking',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => $meta_query,
			) );
			return ! empty( $q->posts );
		}
	}
}
