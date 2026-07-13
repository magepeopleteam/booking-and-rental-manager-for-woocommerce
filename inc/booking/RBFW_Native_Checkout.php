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
			$subtotal = isset( $_POST['rbfw_total'] ) ? (float) preg_replace( '/[^0-9.]/', '', wp_unslash( $_POST['rbfw_total'] ) ) : 0.0;
			$subtotal = max( 0, $subtotal );

			// 6b. Coupon — ALWAYS authoritative server-side. Only the coupon CODE is accepted from
			// the client; the discount value is recomputed from the coupon's own configuration, so a
			// tampered `rbfw_coupon_discount` in the POST is ignored entirely. Automatic (no-code)
			// rules resolve here too. (The base subtotal above remains client-derived — that is the
			// pre-existing gap noted at step 6 and is unchanged by this feature.)
			$coupon_code     = '';
			$coupon_discount = 0.0;
			$coupon_applied  = array();

			if ( class_exists( 'RBFW_Coupon_Engine' ) && RBFW_Coupon_Engine::is_enabled() ) {
				$posted_code = isset( $_POST['rbfw_coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_coupon_code'] ) ) : '';
				$ctx         = RBFW_Coupon_Context::from_native_post( $raw );
				$resolution  = RBFW_Coupon_Engine::resolve( $ctx, $posted_code );

				// A code was typed but is not (or no longer) valid — refuse rather than silently
				// charging full price after the customer saw a discounted total.
				if ( '' !== trim( $posted_code ) && '' !== $resolution['manual_error'] ) {
					wp_send_json_error( array( 'message' => $resolution['manual_error'] ) );
				}

				$coupon_discount = min( (float) $resolution['total_discount'], $subtotal );
				$coupon_applied  = $resolution['applied'];
				$codes           = array();
				foreach ( $coupon_applied as $a ) {
					$codes[] = $a['code'];
				}
				$coupon_code = implode( ', ', $codes );
			}

			$total = max( 0, $subtotal - $coupon_discount );

			// 7. Persist via the booking manager.
			$result = RBFW_Booking_Manager::create_booking( $item_id, array(
				'customer'        => array( 'name' => $name, 'email' => $email, 'phone' => $phone ),
				'dates'           => $dates,
				'subtotal'        => $subtotal,
				'discount'        => $coupon_discount,
				'coupon_code'     => $coupon_code,
				'coupon_applied'  => $coupon_applied,
				'total'           => $total,
				'item_type'       => $item_type,
				'quantity'        => $quantity,
				'ticket_info'     => $ticket_info,
				'raw'             => $raw,
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
			// gateway — in the paid flow the booking is confirmed only after payment.
			global $rbfw;
			if ( empty( $response['requires_redirect'] ) && $email && isset( $rbfw ) && method_exists( $rbfw, 'send_email' ) ) {
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
