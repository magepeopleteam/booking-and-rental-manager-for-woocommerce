<?php
/**
 * WooCommerce booking service — thin delegate to the existing WooCommerce flow.
 *
 * The classic booking flow is driven by the standard WooCommerce add-to-cart product form
 * (button name="add-to-cart") and handled by Frontend/RBFW_Woocommerse.php (cart item data,
 * totals, checkout, order line meta, thank-you booking management). That behaviour is left
 * completely intact for backward compatibility; this service only exists so the rest of the
 * plugin can resolve a booking handler through RBFW_Booking_Manager without special-casing
 * WooCommerce, and to expose the cart/checkout redirect target.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Woo_Booking_Service' ) ) {
	class RBFW_Woo_Booking_Service implements RBFW_Booking_Service_Interface {

		public function get_mode() {
			return 'woocommerce';
		}

		/**
		 * In WooCommerce mode the booking is created by WooCommerce itself when the
		 * add-to-cart form is submitted; there is no separate creation step here. This
		 * returns the post-add redirect target (cart or checkout) per the admin setting.
		 */
		public function create_booking( $item_id, $payload ) {
			$redirect_to = function_exists( 'rbfw_get_option' )
				? rbfw_get_option( 'rbfw_wps_add_to_cart_redirect', 'rbfw_basic_payment_settings', 'checkout' )
				: 'checkout';

			$redirect = ( $redirect_to === 'cart' ) ? wc_get_cart_url() : wc_get_checkout_url();

			return array(
				'booking_id' => 0,
				'status'     => 'cart',
				'redirect'   => $redirect,
			);
		}
	}
}
