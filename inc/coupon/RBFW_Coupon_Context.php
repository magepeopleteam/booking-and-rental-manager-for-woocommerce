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

					$line = self::line_from_priced_data( $item_id, $ci, (string) $key );

					$items[]   = $line;
					$subtotal += $line['line_total'];

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
		 * The payload is *selection* data only. Nothing monetary in it is believed: this method
		 * reprices the booking from the item's own stored configuration through RBFW_Native_Quote
		 * and builds the line from that, exactly as the WooCommerce path builds its lines from
		 * the priced cart item.
		 *
		 * That matters because the engine's free_days rate is `duration_price / duration_units`.
		 * When either side of that division could come from the browser, a caller only had to
		 * post one unpatched alias — `rbfw_total_days=1` against a five-day booking, say — to
		 * collapse the rate onto the whole duration price and take the total to zero. Repricing
		 * removes the division's inputs from the attacker's reach instead of filtering names.
		 *
		 * Fails CLOSED: if the booking cannot be repriced, the context carries no lines, so no
		 * coupon can target it and no discount is produced.
		 *
		 * @param array $post Sanitized POST array.
		 * @return array
		 */
		public static function from_native_post( $post ) {
			$post    = is_array( $post ) ? $post : array();
			$item_id = isset( $post['rbfw_post_id'] ) ? absint( $post['rbfw_post_id'] ) : 0;
			$email   = isset( $post['rbfw_billing_email'] ) ? sanitize_email( $post['rbfw_billing_email'] ) : self::current_email();

			$quote = ( $item_id && class_exists( 'RBFW_Native_Quote' ) )
				? RBFW_Native_Quote::build( $item_id, $post )
				: new WP_Error( 'rbfw_pricing_unavailable', '' );

			if ( is_wp_error( $quote ) ) {
				return self::finalize( array(), 0.0, array(), 'standalone', $email );
			}

			return self::from_native_quote( $item_id, $quote, $email );
		}

		/**
		 * Build the context from an authoritative standalone quote — the preferred entry point.
		 *
		 * Callers that have already priced the booking (the native checkout, the live coupon
		 * preview) pass their quote straight in rather than making this class reprice it.
		 *
		 * @param int         $item_id Rental item ID.
		 * @param array       $quote   RBFW_Native_Quote::build() result: subtotal + cart_data.
		 * @param string|null $email   Customer email; falls back to the current user's.
		 * @return array
		 */
		public static function from_native_quote( $item_id, $quote, $email = null ) {
			$item_id = absint( $item_id );
			$email   = ( null === $email ) ? self::current_email() : $email;

			// build() returns a WP_Error when the booking cannot be priced. Accept that here
			// rather than making every caller remember to check: an unpriceable booking must
			// produce an empty context, never a fatal and never a discountable one.
			$quote     = is_array( $quote ) ? $quote : array();
			$cart_data = ( isset( $quote['cart_data'] ) && is_array( $quote['cart_data'] ) ) ? $quote['cart_data'] : array();
			$subtotal  = isset( $quote['subtotal'] ) ? max( 0, (float) $quote['subtotal'] ) : 0.0;

			if ( ! $item_id || ! $cart_data || get_post_type( $item_id ) !== 'rbfw_item' ) {
				return self::finalize( array(), 0.0, array(), 'standalone', $email );
			}

			$line = self::line_from_priced_data( $item_id, $cart_data, 'native_0' );

			// The standalone grand total can exceed the cart line total: an "additional" security
			// deposit is a separate WooCommerce cart fee in the other mode, but part of the one
			// figure here. Record it on line_total so the coupon's spend rules judge what the
			// customer actually pays — while base_price stays the rental-only, discountable part,
			// so a refundable deposit is never discounted away.
			$line['line_total'] = max( $line['line_total'], $subtotal );

			$dates = array();
			$start = self::extract_line_start_date( $cart_data );
			if ( $start ) {
				$dates[] = $start;
			}

			return self::finalize( array( $line ), $line['line_total'], $dates, 'standalone', $email );
		}

		/* -------------------------------------------------------------------------
		 * Helpers
		 * ---------------------------------------------------------------------- */

		/**
		 * Normalize one server-priced rental line into the engine's item shape.
		 *
		 * The single builder behind BOTH modes. `$data` is whatever rbfw_add_cart_item_func()
		 * produced — a WooCommerce cart item, or the standalone quote's cart_data, which is the
		 * same array from the same function. Sharing it is what actually guarantees the promise
		 * in this file's header (identical coupon behaviour in either mode); two builders reading
		 * the same keys drifted apart once already.
		 *
		 * @param int    $item_id  Rental item ID.
		 * @param array  $data     Priced cart-style data for the line.
		 * @param string $line_key Stable key for this line.
		 * @return array
		 */
		public static function line_from_priced_data( $item_id, $data, $line_key ) {
			$data = is_array( $data ) ? $data : array();

			$qty = isset( $data['rbfw_item_quantity'] )
				? absint( $data['rbfw_item_quantity'] )
				: ( isset( $data['quantity'] ) ? absint( $data['quantity'] ) : 1 );

			$line_total = isset( $data['rbfw_tp'] ) ? max( 0, (float) $data['rbfw_tp'] ) : 0.0;

			// The discountable BASE is the rental subtotal: the line total minus the
			// mandatory management/handling fee. (The security deposit is never part of
			// rbfw_tp — it is added separately as a WooCommerce cart fee.) Defining it this
			// way makes WooCommerce and Standalone agree, and guarantees base <= line_total
			// so a coupon can never drive a line negative even when a large external
			// multi-day discount has already reduced rbfw_tp.
			$management = isset( $data['rbfw_management_price'] ) && is_numeric( $data['rbfw_management_price'] )
				? max( 0, (float) $data['rbfw_management_price'] )
				: 0.0;
			$base_price = max( 0, $line_total - $management );

			// duration_price drives only the free_days per-unit rate; never above the base.
			$duration_price = min( self::resolve_duration_price( $data, $base_price ), $base_price );

			$desc = self::item_descriptor( $item_id );

			return array(
				'item_id'         => $item_id,
				'rent_type'       => $desc['rent_type'],
				'rent_type_names' => $desc['rent_type_names'],
				'locations'       => $desc['locations'],
				'qty'             => max( 1, $qty ),
				'duration_units'  => self::resolve_duration_units( $data ),
				'unit'            => isset( $data['duration_type'] ) ? (string) $data['duration_type'] : '',
				'duration_price'  => max( 0, $duration_price ),
				'base_price'      => $base_price,
				'line_total'      => $line_total,
				'line_key'        => (string) $line_key,
			);
		}

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
		 *
		 * Both keys it reads are written by the pricing code, never by the browser — the shared
		 * implementation lives with the quote so there is exactly one answer to "how many units
		 * is this booking billed for".
		 */
		protected static function resolve_duration_units( $ci ) {
			return RBFW_Native_Quote::billed_units( $ci );
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

		/**
		 * Earliest parseable date among the collected line dates, normalized to Y-m-d so the
		 * blackout comparison (a strict string match) can never miss because one template posts
		 * "2026-08-11 10:00" and another "2026-08-11". Falls back to the SITE's today, not UTC's.
		 */
		protected static function earliest_date( $dates ) {
			$stamps = array();
			foreach ( (array) $dates as $d ) {
				$ts = strtotime( (string) $d );
				if ( $ts ) {
					$stamps[] = $ts;
				}
			}
			return $stamps ? gmdate( 'Y-m-d', min( $stamps ) ) : current_time( 'Y-m-d' );
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
	}
}
