<?php
/**
 * Native checkout handler — processes a rental booking through the standalone flow when the
 * WooCommerce cart/checkout is not in use (WooCommerce inactive, or Booking Mode = Standalone).
 *
 * It captures the booking form payload, builds an inventory ticket_info per item type, and
 * delegates persistence to RBFW_Booking_Manager (RBFW_Standalone_Booking_Service). Payment is
 * NOT processed in Phase 1 — the booking is created as pending.
 *
 * Mirrors mage-eventpress: inc/MPWEM_Native_Checkout.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Native_Checkout' ) ) {
	class RBFW_Native_Checkout {

		public function __construct() {
			add_action( 'wp_ajax_rbfw_native_checkout', array( $this, 'process' ) );
			add_action( 'wp_ajax_nopriv_rbfw_native_checkout', array( $this, 'process' ) );
			// Logged-in only: called right after a guest logs in/registers from the inline
			// auth panel, so it never needs a nopriv counterpart.
			add_action( 'wp_ajax_rbfw_native_checkout_fields', array( $this, 'render_fields_ajax' ) );
			add_action( 'wp_footer', array( $this, 'render_modal' ) );
		}

		/**
		 * Print the native checkout modal once in the footer on single rental item pages.
		 */
		public function render_modal() {
			if ( ! is_singular( 'rbfw_item' ) ) {
				return;
			}
			$template = RBFW_Function::get_template_path( 'layout/native_checkout_modal.php' );
			if ( $template && file_exists( $template ) ) {
				include $template;
			}
		}

		/**
		 * Re-render the modal's billing/payment fields, now that the customer is logged in.
		 *
		 * Called by the inline auth panel (RBFW_Customer_Portal::render_auth_panel, Pro)
		 * right after a successful login/registration, instead of reloading the page — a
		 * reload would wipe the item/quantity already selected in the surrounding booking
		 * form. Returns the same markup native_checkout_modal.php renders inline, so the
		 * client can swap it in place of the auth panel with no other DOM changes.
		 *
		 * Deliberately NOT nonce-protected (mirrors ecab-taxi-booking-manager's equivalent
		 * step-rebuild endpoint, MPTBM_Transport_Search::get_mptbm_extra_service, which relies
		 * on validate_post_access() rather than a nonce). A WordPress nonce is bound to the
		 * session token in the `logged_in` cookie, which only becomes valid on the client's
		 * NEXT request after login — a nonce minted anywhere in the same request/response
		 * cycle as the login is unreliable, and there's nothing here worth that fragility to
		 * protect: this only renders the current user's OWN profile fields (already visible to
		 * them everywhere) and has no side effects. is_user_logged_in() is the real gate — the
		 * action has no wp_ajax_nopriv_ counterpart, so WordPress itself rejects guests before
		 * this method ever runs.
		 */
		public function render_fields_ajax() {
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please log in to continue.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$posted_item_id             = isset( $_POST['item_id'] ) ? absint( wp_unslash( $_POST['item_id'] ) ) : 0;
			$rbfw_native_fields_item_id = ( $posted_item_id && get_post_type( $posted_item_id ) === 'rbfw_item' ) ? $posted_item_id : 0;

			$template = RBFW_Function::get_template_path( 'layout/native_checkout_fields.php' );
			if ( ! $template || ! file_exists( $template ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Could not load the booking form. Please refresh and try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			ob_start();
			include $template;
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}

		public function process() {
			// 0. Neither WooCommerce nor Pro is active — there is no checkout path to complete
			// this booking, so refuse it server-side even if a disabled button was bypassed.
			if ( ! RBFW_Function::is_booking_available() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Booking is currently not possible. Please contact us directly.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// 1. Nonce.
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_native_checkout_action' ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please refresh and try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// 1a. Login gate. The inline auth panel is the UX; this is the server-side gate
			// that can't be bypassed by posting straight to the endpoint.
			if ( function_exists( 'rbfw_login_required' ) && rbfw_login_required() && ! is_user_logged_in() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please log in or create an account to complete your booking.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// 2. Item.
			$item_id = isset( $_POST['rbfw_post_id'] ) ? absint( wp_unslash( $_POST['rbfw_post_id'] ) ) : 0;
			if ( ! $item_id || get_post_type( $item_id ) !== 'rbfw_item' ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// 3. Customer (from the native checkout modal).
			$name  = isset( $_POST['rbfw_billing_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_billing_name'] ) ) : '';
			$email = isset( $_POST['rbfw_billing_email'] ) ? sanitize_email( wp_unslash( $_POST['rbfw_billing_email'] ) ) : '';
			$phone = isset( $_POST['rbfw_billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_billing_phone'] ) ) : '';

			if ( ! $name ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please enter your name.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( ! $email || ! is_email( $email ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please enter a valid email address.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// 4. Whole sanitized form payload (stored verbatim on the booking).
			$raw = RBFW_Function::data_sanitize( wp_unslash( $_POST ) );
			unset( $raw['nonce'], $raw['action'] );

			// 5. Dates / quantity / ticket_info per item type.
			$item_type   = get_post_meta( $item_id, 'rbfw_item_type', true );
			$dates       = $this->extract_dates( $item_type );
			$quantity    = isset( $_POST['rbfw_item_quantity'] ) ? absint( wp_unslash( $_POST['rbfw_item_quantity'] ) ) : 1;
			$ticket_info = $this->build_ticket_info( $item_type, $dates, $quantity );

			// 6. Total — computed live on the frontend and posted back. Phase 1 trusts the
			// sanitized value; server-side recomputation/validation lands with the payment phase.
			$total = isset( $_POST['rbfw_total'] ) ? (float) preg_replace( '/[^0-9.]/', '', wp_unslash( $_POST['rbfw_total'] ) ) : 0.0;

			// 7. Persist via the booking manager.
			$result = RBFW_Booking_Manager::create_booking( $item_id, array(
				'customer'    => array( 'name' => $name, 'email' => $email, 'phone' => $phone ),
				'dates'       => $dates,
				'total'       => $total,
				'item_type'   => $item_type,
				'quantity'    => $quantity,
				'ticket_info' => $ticket_info,
				'raw'         => $raw,
			) );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			// 8. Build the response, then let add-ons (e.g. the Pro payment gateways) take over.
			$response = array(
				'message'    => esc_html__( 'Booking received! Please follow up with payment to confirm your reservation.', 'booking-and-rental-manager-for-woocommerce' ),
				'redirect'   => isset( $result['redirect'] ) ? $result['redirect'] : '',
				'booking_id' => isset( $result['booking_id'] ) ? $result['booking_id'] : 0,
				'reference'  => isset( $result['reference'] ) ? $result['reference'] : '',
				'status'     => isset( $result['status'] ) ? $result['status'] : 'pending',
			);

			/**
			 * Filter the native checkout response before it is returned to the browser.
			 *
			 * The Pro payment add-on hooks this to charge the pending booking: it replaces
			 * `redirect` with the gateway's hosted-checkout URL and sets `requires_redirect`.
			 * Implementations may also short-circuit with wp_send_json_error() on failure.
			 *
			 * @param array $response The response array about to be sent.
			 * @param int   $item_id  The rental item id.
			 * @param array $result   The create_booking() result (booking_id, reference, status, redirect).
			 */
			$response = apply_filters( 'rbfw_native_checkout_response', $response, $item_id, $result );

			// 9. Confirmation email (best-effort). Skipped when redirecting to an external
			// gateway — in the paid flow the booking is confirmed only after payment. Also
			// skipped when a richer handler owns booking emails (Pro's RBFW_Native_Booking_Mail
			// listens on rbfw_native_booking_created), so a booking is never emailed twice.
			global $rbfw;
			if ( empty( $response['requires_redirect'] ) && ! has_action( 'rbfw_native_booking_created' ) && $email && isset( $rbfw ) && method_exists( $rbfw, 'send_email' ) ) {
				$subject = esc_html__( 'Your booking has been received', 'booking-and-rental-manager-for-woocommerce' );
				$body    = sprintf(
					/* translators: 1: item name, 2: booking reference */
					esc_html__( 'Thank you. Your booking for "%1$s" has been received. Reference: %2$s. We will follow up with payment details.', 'booking-and-rental-manager-for-woocommerce' ),
					get_the_title( $item_id ),
					isset( $result['reference'] ) ? $result['reference'] : ''
				);
				$rbfw->send_email( $email, $item_id, $subject, $body, isset( $result['booking_id'] ) ? $result['booking_id'] : '' );
			}

			wp_send_json_success( $response );
		}

		/**
		 * Resolve start/end date+time from the posted form, which differs by item type
		 * (single-day uses rbfw_bikecarsd_selected_date; multi-day uses rbfw_pickup_*).
		 */
		private function extract_dates( $item_type ) {
			$get = function ( $key ) {
				return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			};

			if ( $item_type === 'bike_car_sd' ) {
				return array(
					'start_date' => $get( 'rbfw_bikecarsd_selected_date' ),
					'end_date'   => $get( 'rbfw_end_date' ) ? $get( 'rbfw_end_date' ) : $get( 'rbfw_bikecarsd_selected_date' ),
					'start_time' => $get( 'rbfw_start_time' ),
					'end_time'   => $get( 'rbfw_end_time' ),
				);
			}

			// Multi-day / dress / equipment / others / multiple_items.
			return array(
				'start_date' => $get( 'rbfw_pickup_start_date' ),
				'end_date'   => $get( 'rbfw_pickup_end_date' ),
				'start_time' => $get( 'rbfw_pickup_start_time' ),
				'end_time'   => $get( 'rbfw_pickup_end_time' ),
			);
		}

		/**
		 * Build the inventory ticket_info array consumed by rbfw_create_inventory_meta().
		 */
		private function build_ticket_info( $item_type, $dates, $quantity ) {
			return array(
				'rbfw_start_date'    => $dates['start_date'],
				'rbfw_end_date'      => $dates['end_date'],
				'rbfw_start_time'    => $dates['start_time'],
				'rbfw_end_time'      => $dates['end_time'],
				'rbfw_item_quantity' => $quantity,
				'rbfw_type_info'     => array(),
				'rbfw_variation_info'=> array(),
				'rbfw_service_info'  => array(),
				'rbfw_service_infos' => array(),
			);
		}
	}
	new RBFW_Native_Checkout();
}
