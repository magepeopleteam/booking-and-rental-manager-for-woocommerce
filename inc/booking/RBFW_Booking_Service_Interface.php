<?php
/**
 * Booking service contract.
 *
 * Both booking modes implement this so the rest of the plugin can create a booking
 * without knowing whether WooCommerce or the native flow is active:
 *  - RBFW_Woo_Booking_Service        — delegates to the existing WooCommerce cart/checkout.
 *  - RBFW_Standalone_Booking_Service — persists a native rbfw_booking record.
 *
 * RBFW_Booking_Manager picks the implementation based on RBFW_Function::booking_mode().
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! interface_exists( 'RBFW_Booking_Service_Interface' ) ) {
	interface RBFW_Booking_Service_Interface {

		/** Mode id: 'woocommerce' | 'standalone'. */
		public function get_mode();

		/**
		 * Create a booking for a rental item.
		 *
		 * @param int   $item_id The rbfw_item post id.
		 * @param array $payload Sanitized booking payload (customer, dates, line items, total…).
		 * @return array|WP_Error Result: [ 'booking_id' => mixed, 'status' => string, 'redirect' => string ].
		 */
		public function create_booking( $item_id, $payload );
	}
}
