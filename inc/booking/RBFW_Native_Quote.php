<?php
/**
 * Authoritative price quote for a standalone (native) booking submission.
 *
 * The single place that turns a posted booking form into money. Everything monetary in the
 * standalone flow — the checkout total, the coupon context, the live coupon preview, and
 * (in Pro) the seeded order-request quote — MUST come through here, because this is the only
 * code that rebuilds the price from the item's own stored pricing configuration instead of
 * believing what the browser sent.
 *
 * Why this exists as its own class rather than a private method on RBFW_Native_Checkout:
 * that class is only loaded when Booking Mode is Standalone, while the coupon context is
 * loaded in every mode and must never be left with a client-priced fallback. Keeping the
 * quote here removes that load-order trap and gives every caller the same numbers.
 *
 * Threat model. A rental form posts dozens of fields. Some of them are *selection* data the
 * customer legitimately controls (dates, quantities, chosen services) — those are validated
 * downstream by availability and pricing rules. Others are *display* echoes of prices and
 * billed durations the browser already computed. The display echoes have no authority at all,
 * and the standalone endpoints used to read some of them back. Two rounds of reports showed
 * why replacing them one alias at a time does not hold: it only takes one forgotten spelling
 * (`rbfw_total`, then `rbfw_total_days`) to drive the stored total to zero. So the rule here
 * is categorical — sanitize_payload() overwrites or removes EVERY known money/duration echo,
 * and the callers build their context from the quote, not from the payload.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Native_Quote' ) ) {
	class RBFW_Native_Quote {

		/**
		 * Posted keys that echo a price the browser computed. None of them is ever an input to
		 * the price; each is overwritten with the authoritative figure before the payload is
		 * used or stored.
		 *
		 * Keep in sync with the aliases RBFW_Coupon_Context reads.
		 */
		const PRICE_KEYS = array(
			'rbfw_subtotal',
			'rbfw_sub_total',
			'rbfw_total',
		);

		/**
		 * Posted keys that echo a per-item duration price. Replaced from the quote when the
		 * quote carries the matching figure, and zeroed out otherwise so a forged one cannot
		 * survive as the free_days per-unit rate.
		 */
		const DURATION_PRICE_KEYS = array(
			'rbfw_duration_price',
			'rbfw_bikecarsd_duration_price',
			'rbfw_room_duration_price',
		);

		/**
		 * Posted keys that echo the billed duration. `total_days` is the one the shipped forms
		 * actually post; the other two are aliases the coupon context accepts and were therefore
		 * forgeable — `rbfw_total_days` in particular is read FIRST and is never posted by any
		 * legitimate form, which is exactly what made it a way in.
		 */
		const DURATION_UNIT_KEYS = array(
			'rbfw_total_days',
			'total_days',
			'rbfw_duration_days',
		);

		/**
		 * Posted keys that echo a discount. The engine recomputes discounts from the coupon's
		 * own configuration, so these are dropped outright rather than replaced.
		 */
		const DISCOUNT_KEYS = array(
			'rbfw_coupon_discount',
			'rbfw_discount',
			'rbfw_discount_amount',
			'rbfw_coupon_amount',
		);

		/**
		 * Rebuild the rental price for a posted booking form from the item's stored pricing
		 * configuration, using the very same cart builder the WooCommerce flow uses. That reuse
		 * is deliberate: a second pricing implementation would drift, and a drifting price is a
		 * pricing bug on one side and a discount hole on the other.
		 *
		 * Delivery is deliberately excluded — callers quote it separately from the stored
		 * distance bands and add it on top, so it is never counted twice.
		 *
		 * @param int   $item_id Rental item ID.
		 * @param array $form    Sanitized, unslashed booking form payload.
		 * @return array{subtotal:float,cart_data:array}|WP_Error
		 */
		public static function build( $item_id, $form ) {
			$item_id = absint( $item_id );
			if ( ! $item_id || get_post_type( $item_id ) !== 'rbfw_item' ) {
				return new WP_Error( 'rbfw_invalid_item', esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			if ( ! class_exists( 'RBFW_Woocommerce' ) ) {
				require_once RBFW_PLUGIN_DIR . '/Frontend/RBFW_Woocommerse.php';
			}
			if ( ! class_exists( 'RBFW_Woocommerce' ) ) {
				return new WP_Error( 'rbfw_pricing_unavailable', esc_html__( 'Could not calculate the booking price. Please refresh and try again.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$item_type = get_post_meta( $item_id, 'rbfw_item_type', true );

			$pricing_input                           = is_array( $form ) ? $form : array();
			$pricing_input['nonce']                  = wp_create_nonce( 'rbfw_ajax_action' );
			$pricing_input['rbfw_delivery_wanted']   = 'no';
			$pricing_input['rbfw_collection_wanted'] = 'no';

			$calculator = new RBFW_Woocommerce( false );
			// Some legacy/add-on pricing filters still inspect $_POST instead of the request
			// array passed to the cart builder. Give them the same sanitized pricing payload,
			// then restore the real request immediately afterward.
			$outer_post = $_POST;
			$_POST      = wp_slash( $pricing_input ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.VIP.SuperGlobalInputUsage
			try {
				$cart_data = $calculator->rbfw_add_cart_item_func( array(), $item_id, $pricing_input );
			} finally {
				$_POST = $outer_post; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage
			}

			if ( ! is_array( $cart_data ) || ! isset( $cart_data['rbfw_tp'] ) || ! is_numeric( $cart_data['rbfw_tp'] ) ) {
				return new WP_Error( 'rbfw_price_calculation_failed', esc_html__( 'Could not calculate the booking price. Please review your selection and try again.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			if ( ! self::has_valid_selection( $item_type, $cart_data ) ) {
				return new WP_Error( 'rbfw_invalid_booking_selection', esc_html__( 'Please choose valid rental dates and quantities before booking.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$subtotal = max( 0, (float) $cart_data['rbfw_tp'] );

			// The historical cart builder includes deposits in the resort total, while the
			// other item types carry an additional deposit as metadata. Match the public total
			// without double-charging Pro's "included in price" deposit policy.
			$deposit_mode = isset( $cart_data['rbfw_security_deposit_price_mode'] ) ? sanitize_key( $cart_data['rbfw_security_deposit_price_mode'] ) : 'additional';
			if ( 'resort' !== $item_type && 'included' !== $deposit_mode && ! empty( $cart_data['security_deposit_amount'] ) ) {
				$subtotal += max( 0, (float) $cart_data['security_deposit_amount'] );
			}

			return array(
				'subtotal'  => $subtotal,
				'cart_data' => $cart_data,
			);
		}

		/**
		 * Strip every client-supplied money and billed-duration echo from a booking payload and
		 * put the authoritative figures in their place.
		 *
		 * Call this on the payload BEFORE it is used to build a coupon context, handed to a
		 * pricing filter, or stored on the booking. After it returns, no monetary key in the
		 * payload originates from the browser — which is what makes the alias question moot
		 * instead of a list to keep chasing.
		 *
		 * @param array $form  Sanitized booking form payload.
		 * @param array $quote Result of build(): subtotal + cart_data.
		 * @return array The payload with authoritative pricing.
		 */
		public static function sanitize_payload( $form, $quote ) {
			$form      = is_array( $form ) ? $form : array();
			$subtotal  = isset( $quote['subtotal'] ) ? max( 0, (float) $quote['subtotal'] ) : 0.0;
			$cart_data = ( isset( $quote['cart_data'] ) && is_array( $quote['cart_data'] ) ) ? $quote['cart_data'] : array();

			foreach ( self::PRICE_KEYS as $key ) {
				$form[ $key ] = $subtotal;
			}

			// Present in the quote → use it. Absent (this rent type has no such figure) → zero,
			// never the posted value: an unmatched key must lose its authority, not keep it.
			foreach ( self::DURATION_PRICE_KEYS as $key ) {
				if ( isset( $cart_data[ $key ] ) && is_numeric( $cart_data[ $key ] ) ) {
					$form[ $key ] = max( 0, (float) $cart_data[ $key ] );
				} elseif ( isset( $form[ $key ] ) ) {
					$form[ $key ] = 0;
				}
			}

			// Every duration alias gets the SAME server-derived figure, so which one a reader
			// happens to check first stops mattering.
			$units = self::billed_units( $cart_data );
			foreach ( self::DURATION_UNIT_KEYS as $key ) {
				$form[ $key ] = $units;
			}

			foreach ( self::DISCOUNT_KEYS as $key ) {
				unset( $form[ $key ] );
			}

			/**
			 * Filter the de-fanged booking payload.
			 *
			 * Add-ons that introduce their own posted price fields should strip them here.
			 *
			 * @param array $form  Payload with authoritative pricing.
			 * @param array $quote The authoritative quote (subtotal + cart_data).
			 */
			return apply_filters( 'rbfw_native_sanitized_payload', $form, $quote );
		}

		/**
		 * Billed units for a quote, derived only from server-computed values: the cart builder's
		 * own `total_days` where it sets one, otherwise the span between the dates it resolved.
		 * Always >= 1 so a per-unit rate can never divide by zero.
		 *
		 * @param array $cart_data Authoritative cart-style quote data.
		 * @return float
		 */
		public static function billed_units( $cart_data ) {
			$cart_data = is_array( $cart_data ) ? $cart_data : array();

			if ( isset( $cart_data['total_days'] ) && is_numeric( $cart_data['total_days'] ) && (float) $cart_data['total_days'] > 0 ) {
				return (float) $cart_data['total_days'];
			}

			$start = isset( $cart_data['rbfw_start_date'] ) ? strtotime( (string) $cart_data['rbfw_start_date'] ) : 0;
			$end   = isset( $cart_data['rbfw_end_date'] ) ? strtotime( (string) $cart_data['rbfw_end_date'] ) : 0;
			if ( $start && $end && $end > $start ) {
				return max( 1.0, round( ( $end - $start ) / DAY_IN_SECONDS ) );
			}

			return 1.0;
		}

		/**
		 * Reject incomplete forged requests while allowing legitimately free rentals.
		 *
		 * @param string $item_type Rental item type.
		 * @param array  $cart_data Authoritative cart-style quote data.
		 * @return bool
		 */
		public static function has_valid_selection( $item_type, $cart_data ) {
			$start_date = isset( $cart_data['rbfw_start_date'] ) ? (string) $cart_data['rbfw_start_date'] : '';
			$end_date   = isset( $cart_data['rbfw_end_date'] ) ? (string) $cart_data['rbfw_end_date'] : '';
			if ( '' === trim( $start_date ) || '' === trim( $end_date ) || false === strtotime( $start_date ) || false === strtotime( $end_date ) ) {
				return false;
			}

			if ( in_array( $item_type, array( 'bike_car_sd', 'appointment' ), true ) ) {
				return self::has_positive_quantity( isset( $cart_data['rbfw_type_info'] ) ? $cart_data['rbfw_type_info'] : array() );
			}
			if ( 'resort' === $item_type ) {
				return self::has_positive_quantity( isset( $cart_data['rbfw_room_info'] ) ? $cart_data['rbfw_room_info'] : array() );
			}
			if ( 'multiple_items' === $item_type ) {
				foreach ( (array) ( isset( $cart_data['multiple_items_info'] ) ? $cart_data['multiple_items_info'] : array() ) as $line ) {
					if ( is_array( $line ) && ! empty( $line['item_qty'] ) ) {
						return true;
					}
				}

				return false;
			}

			return isset( $cart_data['rbfw_item_quantity'] ) && absint( $cart_data['rbfw_item_quantity'] ) > 0;
		}

		/**
		 * Whether a flat selection map contains at least one positive quantity.
		 *
		 * @param array $quantities Quantity map.
		 * @return bool
		 */
		public static function has_positive_quantity( $quantities ) {
			foreach ( (array) $quantities as $quantity ) {
				if ( is_numeric( $quantity ) && (float) $quantity > 0 ) {
					return true;
				}
			}

			return false;
		}
	}
}
