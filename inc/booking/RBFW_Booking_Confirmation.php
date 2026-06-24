<?php
/**
 * Renders a booking confirmation/pending notice on the rental item page after a native
 * (standalone) checkout redirects back with ?rbfw_booking=success|pending|cancelled.
 *
 * Mirrors mage-eventpress: inc/MPWEM_Booking_Confirmation.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Confirmation' ) ) {
	class RBFW_Booking_Confirmation {

		public function __construct() {
			add_action( 'rbfw_single_page_before_wrapper', array( $this, 'render' ) );
		}

		public function render() {
			$status = isset( $_GET['rbfw_booking'] ) ? sanitize_key( wp_unslash( $_GET['rbfw_booking'] ) ) : '';
			if ( ! in_array( $status, array( 'success', 'pending', 'cancelled' ), true ) ) {
				return;
			}

			$reference = isset( $_GET['rbfw_booking_id'] ) ? sanitize_text_field( wp_unslash( $_GET['rbfw_booking_id'] ) ) : '';

			$template = RBFW_Function::get_template_path( 'layout/booking_confirmation.php' );
			if ( $template && file_exists( $template ) ) {
				include $template;
			}
		}
	}
	new RBFW_Booking_Confirmation();
}
