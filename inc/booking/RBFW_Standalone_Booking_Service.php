<?php
/**
 * Standalone booking service — persists a native booking without WooCommerce.
 *
 * Creates an `rbfw_booking` post with structured meta, writes an inventory entry so the
 * dates are reserved exactly like a WooCommerce order, and fires `rbfw_native_booking_created`
 * as the seam future payment providers will hook (Phase 2). Bookings are created as `pending`
 * — Phase 1 implements NO payment processing.
 *
 * Mirrors mage-eventpress: mep_native_ticket_attendee_create().
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Standalone_Booking_Service' ) ) {
	class RBFW_Standalone_Booking_Service implements RBFW_Booking_Service_Interface {

		public function get_mode() {
			return 'standalone';
		}

		/**
		 * @param int   $item_id The rbfw_item post id.
		 * @param array $payload {
		 *     @type array  customer    [name, email, phone]
		 *     @type array  dates       [start_date, end_date, start_time, end_time]
		 *     @type float  total       Grand total.
		 *     @type string item_type   rbfw item type.
		 *     @type int    quantity    Item quantity.
		 *     @type array  ticket_info Inventory payload (optional, see rbfw_create_inventory_meta()).
		 *     @type array  raw         Full sanitized form payload (stored verbatim).
		 * }
		 * @return array|WP_Error
		 */
		public function create_booking( $item_id, $payload ) {
			$item_id = absint( $item_id );
			if ( ! $item_id || get_post_type( $item_id ) !== 'rbfw_item' ) {
				return new WP_Error( 'rbfw_invalid_item', esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$customer  = isset( $payload['customer'] ) && is_array( $payload['customer'] ) ? $payload['customer'] : array();
			$dates     = isset( $payload['dates'] ) && is_array( $payload['dates'] ) ? $payload['dates'] : array();
			$total     = isset( $payload['total'] ) ? (float) $payload['total'] : 0.0;
			$item_type = isset( $payload['item_type'] ) ? sanitize_text_field( $payload['item_type'] ) : get_post_meta( $item_id, 'rbfw_item_type', true );
			$quantity  = isset( $payload['quantity'] ) ? absint( $payload['quantity'] ) : 1;

			$name  = isset( $customer['name'] ) ? sanitize_text_field( $customer['name'] ) : '';
			$email = isset( $customer['email'] ) ? sanitize_email( $customer['email'] ) : '';
			$phone = isset( $customer['phone'] ) ? sanitize_text_field( $customer['phone'] ) : '';

			$reference = 'RBFW-' . $item_id . '-' . time() . wp_rand( 100, 999 );
			$status    = 'pending'; // Phase 1: no payment processing yet.

			$post_id = wp_insert_post( array(
				'post_type'   => RBFW_Booking_Post_Type::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $reference . ( $name ? ' — ' . $name : '' ),
			), true );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				return is_wp_error( $post_id ) ? $post_id : new WP_Error( 'rbfw_booking_failed', esc_html__( 'Could not create the booking.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// Structured meta (used by the admin list and future payment phase).
			update_post_meta( $post_id, 'rbfw_reference', $reference );
			update_post_meta( $post_id, 'rbfw_item_id', $item_id );
			update_post_meta( $post_id, 'rbfw_item_type', $item_type );
			update_post_meta( $post_id, 'rbfw_item_name', get_the_title( $item_id ) );
			update_post_meta( $post_id, 'rbfw_customer_name', $name );
			update_post_meta( $post_id, 'rbfw_customer_email', $email );
			update_post_meta( $post_id, 'rbfw_customer_phone', $phone );
			update_post_meta( $post_id, 'rbfw_start_date', isset( $dates['start_date'] ) ? sanitize_text_field( $dates['start_date'] ) : '' );
			update_post_meta( $post_id, 'rbfw_end_date', isset( $dates['end_date'] ) ? sanitize_text_field( $dates['end_date'] ) : '' );
			update_post_meta( $post_id, 'rbfw_start_time', isset( $dates['start_time'] ) ? sanitize_text_field( $dates['start_time'] ) : '' );
			update_post_meta( $post_id, 'rbfw_end_time', isset( $dates['end_time'] ) ? sanitize_text_field( $dates['end_time'] ) : '' );
			update_post_meta( $post_id, 'rbfw_quantity', $quantity );

			// Money breakdown. `rbfw_total` stays the payable grand total (what the payment phase
			// charges); subtotal/discount record how it was reached. Values are computed
			// server-side in RBFW_Native_Checkout::process() — never taken from the client.
			$subtotal    = isset( $payload['subtotal'] ) ? max( 0, (float) $payload['subtotal'] ) : $total;
			$discount    = isset( $payload['discount'] ) ? max( 0, (float) $payload['discount'] ) : 0.0;
			$coupon_code = isset( $payload['coupon_code'] ) ? sanitize_text_field( $payload['coupon_code'] ) : '';

			update_post_meta( $post_id, 'rbfw_subtotal', $subtotal );
			update_post_meta( $post_id, 'rbfw_discount', $discount );
			update_post_meta( $post_id, 'rbfw_coupon_code', $coupon_code );
			update_post_meta( $post_id, 'rbfw_total', $total );
			update_post_meta( $post_id, 'rbfw_currency', get_woocommerce_currency() );
			update_post_meta( $post_id, 'rbfw_status', $status );
			update_post_meta( $post_id, 'rbfw_payment_method', '' ); // set in the payment phase.
			update_post_meta( $post_id, 'rbfw_user_id', get_current_user_id() );
			update_post_meta( $post_id, 'rbfw_created_gmt', current_time( 'mysql', true ) );

			// Store the full sanitized form payload verbatim, guarding against object injection
			// the same way the rest of the plugin does (allowed_classes => false on read).
			$raw = isset( $payload['raw'] ) && is_array( $payload['raw'] ) ? RBFW_Function::data_sanitize( $payload['raw'] ) : array();
			update_post_meta( $post_id, 'rbfw_booking_data', $raw );

			// Reserve the dates in inventory exactly like a WooCommerce order would.
			$ticket_info = isset( $payload['ticket_info'] ) && is_array( $payload['ticket_info'] ) ? $payload['ticket_info'] : array();
			if ( ! empty( $ticket_info ) && function_exists( 'rbfw_create_inventory_meta' ) ) {
				rbfw_create_inventory_meta( $ticket_info, $item_id, $post_id, $status );
			}

			// Record coupon redemptions. Done at creation (like the inventory reservation above) so
			// usage limits cannot be bypassed by never completing payment. RBFW_Coupon_Usage::record()
			// is idempotent per (coupon, booking), so re-entry can never double count.
			$coupon_applied = isset( $payload['coupon_applied'] ) && is_array( $payload['coupon_applied'] ) ? $payload['coupon_applied'] : array();
			if ( $coupon_applied && class_exists( 'RBFW_Coupon_Usage' ) ) {
				foreach ( $coupon_applied as $applied ) {
					$cid = isset( $applied['id'] ) ? absint( $applied['id'] ) : 0;
					if ( $cid ) {
						RBFW_Coupon_Usage::record(
							$cid,
							array( 'type' => 'booking', 'id' => $post_id ),
							get_current_user_id(),
							$email,
							isset( $applied['amount'] ) ? (float) $applied['amount'] : 0.0
						);
					}
				}
			}

			/**
			 * Fires after a native booking is persisted. Future payment providers hook here
			 * to start payment for the pending booking.
			 *
			 * @param int    $post_id The rbfw_booking id.
			 * @param int    $item_id The rbfw_item id.
			 * @param float  $total   Grand total.
			 * @param string $status  Booking status (pending).
			 * @param array  $payload Full payload.
			 */
			do_action( 'rbfw_native_booking_created', $post_id, $item_id, $total, $status, $payload );

			return array(
				'booking_id' => $post_id,
				'reference'  => $reference,
				'status'     => $status,
				'redirect'   => $this->get_redirect_url( $item_id, $status, $reference ),
			);
		}

		private function get_redirect_url( $item_id, $status, $reference ) {
			$item_url = get_permalink( $item_id );
			if ( ! $item_url ) {
				$item_url = home_url( '/' );
			}
			return add_query_arg(
				array(
					'rbfw_booking'    => ( $status === 'completed' ? 'success' : 'pending' ),
					'rbfw_booking_id' => rawurlencode( $reference ),
				),
				$item_url
			);
		}
	}
}
