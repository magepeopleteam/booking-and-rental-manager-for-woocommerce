<?php
/**
 * Standalone (native checkout) application layer for the unified coupon engine.
 *
 * Loaded only when the WooCommerce cart/checkout flow is NOT in use, so it cannot rely on a
 * WC session: the applied code travels with the booking form as `rbfw_coupon_code` and is
 * re-validated server-side on submit (see RBFW_Native_Checkout::process()).
 *
 * This class provides the live preview endpoint used by the coupon field. It is READ-ONLY —
 * it never records usage and never trusts a client-sent discount; it only echoes back what the
 * engine computes from the coupon's own configuration.
 *
 * Endpoint name is deliberately distinct from the WooCommerce one: when WooCommerce is active
 * but Booking Mode = Standalone, BOTH layers are loaded and must not collide.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Native' ) ) {
	class RBFW_Coupon_Native {

		public function __construct() {
			add_action( 'wp_ajax_rbfw_apply_coupon_native', array( $this, 'ajax_apply' ) );
			add_action( 'wp_ajax_nopriv_rbfw_apply_coupon_native', array( $this, 'ajax_apply' ) );
		}

		/**
		 * Validate a code (or resolve automatic discounts) against the posted booking form and
		 * return a preview of the discount. Nothing is persisted.
		 */
		public function ajax_apply() {
			check_ajax_referer( 'rbfw_apply_coupon_action', 'nonce' );

			if ( RBFW_Function::use_wc() || ! RBFW_Coupon_Engine::is_enabled() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Coupons are not available.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// preview=1 → resolve automatic discounts with no code entered (page load).
			$preview = isset( $_POST['preview'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['preview'] ) );
			$code    = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

			if ( ! $preview && '' === trim( $code ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please enter a coupon code.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$raw     = RBFW_Function::data_sanitize( wp_unslash( $_POST ) );
			$item_id = isset( $raw['rbfw_post_id'] ) ? absint( $raw['rbfw_post_id'] ) : 0;
			// Type alone is not enough — an unpublished item must not be priced either.
			if ( ! rbfw_can_view_item( $item_id ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// Price the booking exactly the way the checkout will. Previewing against the
			// browser's own numbers would show a discount the checkout then declines to honour
			// — and, before the payload was de-fanged, a forged one could be talked into a
			// discount here that the customer would reasonably expect to be charged.
			$quote = RBFW_Native_Quote::build( $item_id, $raw );
			if ( is_wp_error( $quote ) ) {
				wp_send_json_error( array( 'message' => $quote->get_error_message() ) );
			}
			$raw = RBFW_Native_Quote::sanitize_payload( $raw, $quote );

			$ctx = RBFW_Coupon_Context::from_native_quote( $item_id, $quote );
			$res = RBFW_Coupon_Engine::resolve( $ctx, $code );

			if ( '' !== trim( $code ) && '' !== $res['manual_error'] ) {
				wp_send_json_error( array( 'message' => $res['manual_error'] ) );
			}

			$gross     = self::gross_total( $item_id, $raw, $quote );
			$discount  = min( (float) $res['total_discount'], $gross );
			$new_total = max( 0, $gross - $discount );

			$codes = array();
			foreach ( $res['applied'] as $a ) {
				$codes[] = $a['code'];
			}

			wp_send_json_success( array(
				'message'       => $discount > 0
					? esc_html__( 'Coupon applied.', 'booking-and-rental-manager-for-woocommerce' )
					: esc_html__( 'No discount applies to this booking.', 'booking-and-rental-manager-for-woocommerce' ),
				'code'          => implode( ', ', $codes ),
				'applied'       => $codes,
				// Plain text, not markup: the modal prints these with .text(), so an undecoded
				// currency entity would be shown to the customer verbatim ("−&#2547;5.67").
				'discount'      => round( $discount, wc_get_price_decimals() ),
				'discount_html' => RBFW_Coupon_Engine::price_text( $discount ),
				'total'         => round( $new_total, wc_get_price_decimals() ),
				'total_html'    => RBFW_Coupon_Engine::price_text( $new_total ),
			) );
		}

		/**
		 * The authoritative grand total this booking would be charged before any coupon:
		 * the repriced rental subtotal plus delivery/collection quoted from the shop's own
		 * distance bands. Mirrors RBFW_Native_Checkout::process() so the previewed total and
		 * the charged total are the same number.
		 *
		 * A delivery choice the shop refuses is simply left out here rather than failing the
		 * preview — the customer is still filling the form, and the checkout is where a refused
		 * distance has to stop the booking.
		 *
		 * @param int   $item_id Rental item ID.
		 * @param array $raw     De-fanged booking payload.
		 * @param array $quote   RBFW_Native_Quote::build() result.
		 * @return float
		 */
		public static function gross_total( $item_id, $raw, $quote ) {
			$gross = isset( $quote['subtotal'] ) ? max( 0, (float) $quote['subtotal'] ) : 0.0;

			if ( ! function_exists( 'rbfw_delivery_quote' ) || ! function_exists( 'rbfw_delivery_input_from_form' ) ) {
				return $gross;
			}

			$choice = rbfw_delivery_input_from_form( $raw );
			if ( empty( $choice['delivery'] ) && empty( $choice['collection'] ) ) {
				return $gross;
			}

			$delivery = rbfw_delivery_quote(
				$item_id,
				isset( $choice['distance'] ) ? $choice['distance'] : 0,
				! empty( $choice['delivery'] ),
				! empty( $choice['collection'] )
			);

			if ( '' === $delivery['error'] ) {
				$gross += max( 0, (float) $delivery['total'] );
			}

			return $gross;
		}

		/**
		 * Back-compat shim for add-ons that called this before the preview was made
		 * authoritative. The posted total is no longer consulted at all.
		 *
		 * @deprecated Use gross_total().
		 */
		public static function posted_total( $raw, $ctx ) {
			return isset( $ctx['subtotal'] ) ? max( 0, (float) $ctx['subtotal'] ) : 0.0;
		}
	}

	new RBFW_Coupon_Native();
}
