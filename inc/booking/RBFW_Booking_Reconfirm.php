<?php
/**
 * Self-service reconfirmation for pending offline/standalone bookings.
 *
 * On the booking confirmation page, a customer whose booking is still "pending" (paid
 * offline / on pickup / by bank transfer — nobody has taken payment yet) can confirm it
 * themselves instead of waiting for a phone call: they request a one-time code, it's
 * emailed to the address on the booking, and entering it correctly moves the booking to
 * "Confirmed" — exactly as if an admin had done it from the Bookings list, including the
 * same email/inventory side effects (RBFW_Booking_Actions::apply_transition(), via
 * RBFW_Booking_Normalizer::update_status()) — plus a note on the booking's timeline and a
 * flag (`rbfw_confirmed_by`) so the admin list can show it was the CUSTOMER who confirmed,
 * not staff. Controlled by Payments -> Custom Payment -> Offline Payment -> "Reconfirm
 * Booking Button" (default on) — RBFW_Function::offline_reconfirm_enabled().
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Reconfirm' ) ) {
	class RBFW_Booking_Reconfirm {

		/** Minutes an emailed code stays valid. */
		const OTP_TTL = 10 * MINUTE_IN_SECONDS;

		/** Seconds a customer must wait before requesting another code. */
		const RESEND_COOLDOWN = 45;

		/** Wrong-code attempts allowed per emailed code before a fresh one is required. */
		const MAX_ATTEMPTS = 5;

		public function __construct() {
			add_action( 'wp_ajax_rbfw_reconfirm_send_otp', array( $this, 'ajax_send_otp' ) );
			add_action( 'wp_ajax_nopriv_rbfw_reconfirm_send_otp', array( $this, 'ajax_send_otp' ) );
			add_action( 'wp_ajax_rbfw_reconfirm_verify_otp', array( $this, 'ajax_verify_otp' ) );
			add_action( 'wp_ajax_nopriv_rbfw_reconfirm_verify_otp', array( $this, 'ajax_verify_otp' ) );
		}

		/**
		 * Nonce action string for one booking — scoped per booking so a code request for
		 * one reference can't be replayed against another.
		 *
		 * @param int $booking_id
		 * @return string
		 */
		public static function nonce_action( $booking_id ) {
			return 'rbfw_reconfirm_' . absint( $booking_id );
		}

		/**
		 * Shared guard for both AJAX endpoints: booking exists, feature is on, and it is
		 * still pending. Returns the booking id on success, or sends a JSON error and
		 * returns null.
		 *
		 * @return int|null
		 */
		private function guard() {
			$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
			$nonce      = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( ! $booking_id || RBFW_Booking_Post_Type::POST_TYPE !== get_post_type( $booking_id ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Booking not found.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( ! wp_verify_nonce( $nonce, self::nonce_action( $booking_id ) ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please reload the page and try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( ! class_exists( 'RBFW_Function' ) || ! RBFW_Function::offline_reconfirm_enabled() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Self-service confirmation is not available.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			// Re-check the REAL current status server-side — never trust that the page the
			// request came from is still accurate (an admin may have acted on it since).
			$status = RBFW_Booking_Normalizer::normalize_status( get_post_meta( $booking_id, 'rbfw_status', true ) );
			if ( 'pending' !== $status ) {
				wp_send_json_error( array( 'message' => esc_html__( 'This booking is no longer pending, so it cannot be self-confirmed.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			return $booking_id;
		}

		/** AJAX: generate and email a one-time code. */
		public function ajax_send_otp() {
			$booking_id = $this->guard();
			if ( ! $booking_id ) {
				return; // guard() already sent the JSON error response.
			}

			$email = get_post_meta( $booking_id, 'rbfw_customer_email', true );
			if ( ! $email || ! is_email( $email ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'No email address is on file for this booking.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$last_sent = (int) get_post_meta( $booking_id, 'rbfw_reconfirm_otp_sent_at', true );
			$wait      = self::RESEND_COOLDOWN - ( time() - $last_sent );
			if ( $last_sent && $wait > 0 ) {
				wp_send_json_error( array(
					/* translators: %d: seconds to wait */
					'message' => sprintf( esc_html__( 'Please wait %d seconds before requesting another code.', 'booking-and-rental-manager-for-woocommerce' ), $wait ),
				) );
			}

			$otp       = (string) wp_rand( 100000, 999999 );
			$reference = get_post_meta( $booking_id, 'rbfw_reference', true );

			update_post_meta( $booking_id, 'rbfw_reconfirm_otp_hash', wp_hash( $otp . '|' . $booking_id ) );
			update_post_meta( $booking_id, 'rbfw_reconfirm_otp_expires', time() + self::OTP_TTL );
			update_post_meta( $booking_id, 'rbfw_reconfirm_otp_attempts', 0 );
			update_post_meta( $booking_id, 'rbfw_reconfirm_otp_sent_at', time() );

			$subject = esc_html__( 'Your booking confirmation code', 'booking-and-rental-manager-for-woocommerce' );
			$body    = sprintf(
				/* translators: 1: reference, 2: OTP code, 3: number of minutes the code is valid */
				esc_html__( "Booking reference: %1\$s\n\nYour confirmation code is: %2\$s\n\nEnter this code on the booking confirmation page to confirm your booking yourself. This code expires in %3\$d minutes.\n\nIf you didn't request this, you can ignore this email.", 'booking-and-rental-manager-for-woocommerce' ),
				$reference,
				$otp,
				(int) ( self::OTP_TTL / MINUTE_IN_SECONDS )
			);

			global $rbfw;
			if ( isset( $rbfw ) && method_exists( $rbfw, 'send_email' ) ) {
				$rbfw->send_email( $email, get_post_meta( $booking_id, 'rbfw_item_id', true ), $subject, $body, $booking_id );
			} else {
				wp_mail( $email, $subject, $body );
			}

			wp_send_json_success( array(
				'message' => esc_html__( 'A confirmation code has been sent to your email.', 'booking-and-rental-manager-for-woocommerce' ),
			) );
		}

		/** AJAX: verify a submitted code and, if correct, confirm the booking. */
		public function ajax_verify_otp() {
			$booking_id = $this->guard();
			if ( ! $booking_id ) {
				return;
			}

			$otp = isset( $_POST['otp'] ) ? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['otp'] ) ) ) : '';

			$attempts = (int) get_post_meta( $booking_id, 'rbfw_reconfirm_otp_attempts', true );
			if ( $attempts >= self::MAX_ATTEMPTS ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Too many incorrect attempts. Please request a new code.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$expires = (int) get_post_meta( $booking_id, 'rbfw_reconfirm_otp_expires', true );
			$hash    = get_post_meta( $booking_id, 'rbfw_reconfirm_otp_hash', true );

			if ( ! $hash || ! $expires ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please request a confirmation code first.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( time() > $expires ) {
				wp_send_json_error( array( 'message' => esc_html__( 'This code has expired. Please request a new one.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( ! $otp || ! hash_equals( $hash, wp_hash( $otp . '|' . $booking_id ) ) ) {
				update_post_meta( $booking_id, 'rbfw_reconfirm_otp_attempts', $attempts + 1 );
				$left = max( 0, self::MAX_ATTEMPTS - ( $attempts + 1 ) );
				wp_send_json_error( array(
					/* translators: %d: attempts remaining */
					'message' => sprintf( esc_html__( 'Incorrect code. %d attempt(s) remaining.', 'booking-and-rental-manager-for-woocommerce' ), $left ),
				) );
			}

			// Correct — consume the code so it can't be reused, then confirm the booking
			// exactly as an admin doing it manually would (same email/inventory side
			// effects), and leave a clear trail that it was the CUSTOMER who did it.
			delete_post_meta( $booking_id, 'rbfw_reconfirm_otp_hash' );
			delete_post_meta( $booking_id, 'rbfw_reconfirm_otp_expires' );
			delete_post_meta( $booking_id, 'rbfw_reconfirm_otp_attempts' );
			delete_post_meta( $booking_id, 'rbfw_reconfirm_otp_sent_at' );

			update_post_meta( $booking_id, 'rbfw_confirmed_by', 'customer_otp' );
			update_post_meta( $booking_id, 'rbfw_confirmed_at', current_time( 'mysql', true ) );

			if ( class_exists( 'RBFW_Booking_Actions' ) ) {
				RBFW_Booking_Actions::add_note( $booking_id, esc_html__( 'Booking confirmed by the customer themselves via an emailed one-time code.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			RBFW_Booking_Normalizer::update_status( $booking_id, 'confirmed' );

			wp_send_json_success( array(
				'message' => esc_html__( 'Thank you — your booking is now confirmed.', 'booking-and-rental-manager-for-woocommerce' ),
			) );
		}
	}
	new RBFW_Booking_Reconfirm();
}
