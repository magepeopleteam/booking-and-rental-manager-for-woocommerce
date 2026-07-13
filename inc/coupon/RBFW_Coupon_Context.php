<?php
/**
 * Coupon Context — the single normalized shape both booking modes feed into the engine.
 *
 * This is what makes "one engine, both modes" hold: RBFW_Coupon_Engine only ever sees this
 * array, so validate()/calculate_discount() are written once and reused by WooCommerce and
 * Standalone alike.
 *
 *   [
 *     'items'    => [ [ item_id, rent_type, rent_type_names[], locations[], qty,
 *                       duration_units, unit, duration_price, line_total, line_key ] ... ],
 *     'subtotal' => float,        // sum of line_total across items
 *     'user_id'  => int,
 *     'email'    => string,
 *     'date'     => 'Y-m-d',      // earliest booking start date (weekday / blackout basis)
 *     'mode'     => 'woocommerce'|'standalone',
 *   ]
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Context' ) ) {
	class RBFW_Coupon_Context {

		/**
		 * Build the context from the live WooCommerce cart.
		 *
		 * Reads the per-line data already computed by rbfw_add_cart_item_func (rbfw_tp etc.).
		 * Only rental lines (cart items carrying an `rbfw_id` that is an rbfw_item) are included.
		 *
		 * @return array
		 */
		public static function from_wc_cart() {
			$items    = array();
			$subtotal = 0.0;
			$dates    = array();

			if ( function_exists( 'WC' ) && WC()->cart ) {
				foreach ( WC()->cart->get_cart() as $key => $ci ) {
					$item_id = isset( $ci['rbfw_id'] ) ? absint( $ci['rbfw_id'] ) : 0;
					if ( ! $item_id || get_post_type( $item_id ) !== 'rbfw_item' ) {
						continue;
					}

					$qty        = isset( $ci['rbfw_item_quantity'] ) ? absint( $ci['rbfw_item_quantity'] ) : ( isset( $ci['quantity'] ) ? absint( $ci['quantity'] ) : 1 );
					$line_total = isset( $ci['rbfw_tp'] ) ? (float) $ci['rbfw_tp'] : 0.0;
					$line_total = max( 0, $line_total );

					// The discountable BASE is the rental subtotal: the line total minus the
					// mandatory management/handling fee. (The security deposit is never part of
					// rbfw_tp — it is added separately as a WooCommerce cart fee.) Defining it this
					// way makes WooCommerce and Standalone agree, and guarantees base <= line_total
					// so a coupon can never drive a line negative even when a large external
					// multi-day discount has already reduced rbfw_tp.
					$management = isset( $ci['rbfw_management_price'] ) && is_numeric( $ci['rbfw_management_price'] )
						? max( 0, (float) $ci['rbfw_management_price'] )
						: 0.0;
					$base_price = max( 0, $line_total - $management );

					// duration_price drives only the free_days per-unit rate; never above the base.
					$duration_price = min( self::resolve_duration_price( $ci, $base_price ), $base_price );
					$duration_units = self::resolve_duration_units( $ci );

					$desc = self::item_descriptor( $item_id );

					$items[] = array(
						'item_id'         => $item_id,
						'rent_type'       => $desc['rent_type'],
						'rent_type_names' => $desc['rent_type_names'],
						'locations'       => $desc['locations'],
						'qty'             => max( 1, $qty ),
						'duration_units'  => $duration_units,
						'unit'            => isset( $ci['duration_type'] ) ? (string) $ci['duration_type'] : '',
						'duration_price'  => max( 0, $duration_price ),
						'base_price'      => $base_price,
						'line_total'      => $line_total,
						'line_key'        => (string) $key,
					);
					$subtotal += $line_total;

					$start = self::extract_line_start_date( $ci );
					if ( $start ) {
						$dates[] = $start;
					}
				}
			}

			return self::finalize( $items, $subtotal, $dates, 'woocommerce', self::current_email() );
		}

		/**
		 * Build the context from a Standalone (native checkout) POST payload — exactly one item.
		 *
		 * The standalone form computes prices client-side; v1 uses the posted duration price as
		 * the discount BASE (a ceiling) and the engine recomputes the discount VALUE server-side.
		 * The base subtotal itself remains partly client-derived (documented pre-existing gap).
		 *
		 * @param array $post Sanitized POST array.
		 * @return array
		 */
		public static function from_native_post( $post ) {
			$post    = is_array( $post ) ? $post : array();
			$item_id = isset( $post['rbfw_post_id'] ) ? absint( $post['rbfw_post_id'] ) : 0;

			$items    = array();
			$subtotal = 0.0;

			if ( $item_id && get_post_type( $item_id ) === 'rbfw_item' ) {
				$qty  = isset( $post['rbfw_item_quantity'] ) ? absint( $post['rbfw_item_quantity'] ) : 1;
				$days = 1.0;
				foreach ( array( 'rbfw_total_days', 'total_days', 'rbfw_duration_days' ) as $dk ) {
					if ( isset( $post[ $dk ] ) && '' !== $post[ $dk ] ) {
						$days = (float) self::to_number( $post[ $dk ] );
						break;
					}
				}
				$days = $days > 0 ? $days : 1.0;

				// Base price: the booking SUBTOTAL (rental + services + variations, excluding the
				// management fee and the security deposit) — the same definition the WooCommerce
				// context uses. Falls back to the posted grand total. Client-derived, so it is only
				// ever a ceiling: the engine recomputes the discount VALUE server-side.
				$base = 0.0;
				foreach ( array( 'rbfw_subtotal', 'rbfw_sub_total', 'rbfw_total' ) as $pk ) {
					if ( isset( $post[ $pk ] ) && '' !== $post[ $pk ] ) {
						$base = (float) self::to_number( $post[ $pk ] );
						break;
					}
				}
				$base = max( 0, $base );

				// Duration-only figure (drives the free_days per-unit rate); never above the base.
				$duration = $base;
				if ( isset( $post['rbfw_duration_price'] ) && '' !== $post['rbfw_duration_price'] ) {
					$duration = min( max( 0, (float) self::to_number( $post['rbfw_duration_price'] ) ), $base );
				}

				$desc = self::item_descriptor( $item_id );

				$items[] = array(
					'item_id'         => $item_id,
					'rent_type'       => $desc['rent_type'],
					'rent_type_names' => $desc['rent_type_names'],
					'locations'       => $desc['locations'],
					'qty'             => max( 1, $qty ),
					'duration_units'  => $days,
					'unit'            => '',
					'duration_price'  => $duration,
					'base_price'      => $base,
					'line_total'      => $base,
					'line_key'        => 'native_0',
				);
				$subtotal = $base;
			}

			$dates = array();
			foreach ( array( 'rbfw_bikecarsd_selected_date', 'rbfw_pickup_start_date' ) as $dk ) {
				if ( ! empty( $post[ $dk ] ) ) {
					$dates[] = sanitize_text_field( $post[ $dk ] );
					break;
				}
			}

			$email = isset( $post['rbfw_billing_email'] ) ? sanitize_email( $post['rbfw_billing_email'] ) : self::current_email();

			return self::finalize( $items, $subtotal, $dates, 'standalone', $email );
		}

		/* -------------------------------------------------------------------------
		 * Helpers
		 * ---------------------------------------------------------------------- */

		protected static function finalize( $items, $subtotal, $dates, $mode, $email ) {
			$ctx = array(
				'items'    => $items,
				'subtotal' => round( (float) $subtotal, wc_get_price_decimals() ),
				'user_id'  => get_current_user_id(),
				'email'    => $email,
				'date'     => self::earliest_date( $dates ),
				'mode'     => $mode,
			);

			/**
			 * Filter the normalized coupon context before validation.
			 *
			 * @param array $ctx The context array.
			 */
			return apply_filters( 'rbfw_coupon_validate_context', $ctx );
		}

		/**
		 * Item targeting descriptor: rent type slug, category NAMES (name-based, matching the
		 * rbfw_categories convention), and location term slugs.
		 *
		 * @return array{rent_type:string,rent_type_names:string[],locations:string[]}
		 */
		public static function item_descriptor( $item_id ) {
			$item_id   = absint( $item_id );
			$rent_type = (string) get_post_meta( $item_id, 'rbfw_item_type', true );

			$names = get_post_meta( $item_id, 'rbfw_categories', true );
			$names = is_array( $names ) ? array_values( array_map( 'strval', $names ) ) : array();

			$locations = array();
			$terms     = wp_get_post_terms( $item_id, 'rbfw_item_location', array( 'fields' => 'slugs' ) );
			if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
				$locations = array_map( 'strval', $terms );
			}

			return array(
				'rent_type'       => $rent_type,
				'rent_type_names' => $names,
				'locations'       => $locations,
			);
		}

		/**
		 * The duration (base) price of a WooCommerce cart line. rbfw_add_cart_item_func() stores it
		 * under a DIFFERENT key per rent type:
		 *   - multi-day / others / multiple_items → rbfw_duration_price
		 *   - single-day (bike_car_sd)            → rbfw_bikecarsd_duration_price
		 *   - resort                              → rbfw_room_duration_price
		 * Falls back to the full line total when none is present.
		 */
		protected static function resolve_duration_price( $ci, $line_total ) {
			$keys = array( 'rbfw_duration_price', 'rbfw_bikecarsd_duration_price', 'rbfw_room_duration_price' );
			foreach ( $keys as $k ) {
				if ( isset( $ci[ $k ] ) && '' !== $ci[ $k ] && is_numeric( $ci[ $k ] ) ) {
					return max( 0, (float) $ci[ $k ] );
				}
			}
			return $line_total;
		}

		/**
		 * Billed units for a line. `total_days` is only set on the multi-day/others/multiple_items
		 * paths; single-day and resort lines have none, so derive nights from the date span.
		 * Always >= 1 so the free_days per-unit rate can never divide by zero.
		 */
		protected static function resolve_duration_units( $ci ) {
			if ( isset( $ci['total_days'] ) && is_numeric( $ci['total_days'] ) && (float) $ci['total_days'] > 0 ) {
				return (float) $ci['total_days'];
			}
			$start = isset( $ci['rbfw_start_date'] ) ? strtotime( (string) $ci['rbfw_start_date'] ) : 0;
			$end   = isset( $ci['rbfw_end_date'] ) ? strtotime( (string) $ci['rbfw_end_date'] ) : 0;
			if ( $start && $end && $end > $start ) {
				$days = ( $end - $start ) / DAY_IN_SECONDS;
				return max( 1.0, round( $days ) );
			}
			return 1.0;
		}

		protected static function extract_line_start_date( $ci ) {
			foreach ( array( 'rbfw_start_date', 'rbfw_pickup_start_date', 'rbfw_bikecarsd_selected_date' ) as $k ) {
				if ( ! empty( $ci[ $k ] ) ) {
					return sanitize_text_field( is_array( $ci[ $k ] ) ? reset( $ci[ $k ] ) : $ci[ $k ] );
				}
			}
			if ( ! empty( $ci['rbfw_ticket_info'] ) && is_array( $ci['rbfw_ticket_info'] ) ) {
				foreach ( $ci['rbfw_ticket_info'] as $ti ) {
					if ( ! empty( $ti['rbfw_start_date'] ) ) {
						return sanitize_text_field( $ti['rbfw_start_date'] );
					}
				}
			}
			return '';
		}

		/** Earliest parseable date among the collected line dates, as Y-m-d; today (GMT) if none. */
		protected static function earliest_date( $dates ) {
			$stamps = array();
			foreach ( (array) $dates as $d ) {
				$ts = strtotime( (string) $d );
				if ( $ts ) {
					$stamps[] = $ts;
				}
			}
			$ts = $stamps ? min( $stamps ) : time();
			return gmdate( 'Y-m-d', $ts );
		}

		protected static function current_email() {
			if ( is_user_logged_in() ) {
				$u = wp_get_current_user();
				if ( $u && $u->user_email ) {
					return $u->user_email;
				}
			}
			// Only the real WooCommerce customer has get_billing_email(); the standalone WC()
			// fallback shim (WooCommerce fully deactivated) does not — guard against it.
			if ( function_exists( 'WC' ) && is_object( WC()->customer ) && method_exists( WC()->customer, 'get_billing_email' ) ) {
				$e = WC()->customer->get_billing_email();
				if ( $e ) {
					return $e;
				}
			}
			return '';
		}

		protected static function to_number( $v ) {
			return preg_replace( '/[^0-9.\-]/', '', (string) $v );
		}
	}
}
