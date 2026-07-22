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

			// Resolve the real booking so the ticket-download gate reflects the booking's
			// CURRENT status (it can change after this redirect, e.g. an admin marking an
			// offline payment received), not just the one-time status hint in the URL.
			$booking_id = $this->find_booking_by_reference( $reference );

			$template = RBFW_Function::get_template_path( 'layout/booking_confirmation.php' );
			if ( $template && file_exists( $template ) ) {
				include $template;
			}
		}

		/**
		 * Resolve the rbfw_booking post id from its reference. Returns 0 when not found.
		 *
		 * @param string $reference
		 * @return int
		 */
		private function find_booking_by_reference( $reference ) {
			$reference = trim( (string) $reference );
			if ( '' === $reference ) {
				return 0;
			}
			$ids = get_posts( array(
				'post_type'        => 'rbfw_booking',
				'post_status'      => 'publish',
				'numberposts'      => 1,
				'fields'           => 'ids',
				'meta_key'         => 'rbfw_reference',
				'meta_value'       => $reference,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			) );
			return ! empty( $ids ) ? absint( $ids[0] ) : 0;
		}
	}
	new RBFW_Booking_Confirmation();
}
