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
			if ( ! $item_id || get_post_type( $item_id ) !== 'rbfw_item' ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$ctx = RBFW_Coupon_Context::from_native_post( $raw );
			$res = RBFW_Coupon_Engine::resolve( $ctx, $code );

			if ( '' !== trim( $code ) && '' !== $res['manual_error'] ) {
				wp_send_json_error( array( 'message' => $res['manual_error'] ) );
			}

			$gross     = self::posted_total( $raw, $ctx );
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
				'discount'      => round( $discount, wc_get_price_decimals() ),
				'discount_html' => wp_strip_all_tags( wc_price( $discount ) ),
				'total'         => round( $new_total, wc_get_price_decimals() ),
				'total_html'    => wp_strip_all_tags( wc_price( $new_total ) ),
			) );
		}

		/** The client-computed grand total (pre-coupon). Used only as a ceiling. */
		public static function posted_total( $raw, $ctx ) {
			if ( isset( $raw['rbfw_total'] ) && '' !== $raw['rbfw_total'] ) {
				return max( 0, (float) preg_replace( '/[^0-9.]/', '', (string) $raw['rbfw_total'] ) );
			}
			return isset( $ctx['subtotal'] ) ? max( 0, (float) $ctx['subtotal'] ) : 0.0;
		}
	}

	new RBFW_Coupon_Native();
}
