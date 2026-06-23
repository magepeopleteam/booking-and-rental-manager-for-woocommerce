<?php
/**
 * Payment provider contract — PLACEHOLDER for the upcoming custom payment phase.
 *
 * Phase 1 does NOT implement any payment processing. This interface only defines the
 * seam that future gateways (offline, Stripe, PayPal, SSLCommerz, …) will implement so
 * the standalone booking flow can hand a booking to a provider. Standalone bookings are
 * currently created as `pending` and the `rbfw_native_booking_created` action is the hook
 * future providers will use.
 *
 * Do not add gateway logic here in Phase 1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! interface_exists( 'RBFW_Payment_Provider_Interface' ) ) {
	interface RBFW_Payment_Provider_Interface {

		/** Unique provider id, e.g. 'offline', 'stripe'. */
		public function get_id();

		/** Human-readable label shown at checkout. */
		public function get_label();

		/** Whether this provider is enabled in settings. */
		public function is_enabled();

		/**
		 * Process payment for a created booking.
		 *
		 * @param int   $booking_id The rbfw_booking post id.
		 * @param array $context    Arbitrary context (amount, currency, return urls…).
		 * @return array Result: [ 'success' => bool, 'redirect' => string, 'message' => string ].
		 */
		public function process( $booking_id, $context = array() );
	}
}
