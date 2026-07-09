<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Quote_Order' ) ) {
	class RBFW_Quote_Order {

		public function __construct() {
			add_action( 'wp_ajax_rbfw_create_quote_order', array( $this, 'ajax_create_quote_order' ) );
			add_action( 'wp_ajax_nopriv_rbfw_create_quote_order', array( $this, 'ajax_create_quote_order' ) );
		}

		public function ajax_create_quote_order() {
			check_ajax_referer( 'rbfw_create_quote_order_action', 'quote_nonce' );

			$rbfw_id = isset( $_POST['rbfw_post_id'] ) ? absint( $_POST['rbfw_post_id'] ) : 0;
			if ( ! $rbfw_id || get_post_type( $rbfw_id ) !== 'rbfw_item' ) {
				wp_send_json_error( array( 'message' => __( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$quote_mode = rbfw_get_quote_mode( $rbfw_id );
			if ( $quote_mode === 'off' ) {
				wp_send_json_error( array( 'message' => __( 'Quote requests are not enabled for this item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			if ( ! function_exists( 'wc_create_order' ) ) {
				wp_send_json_error( array( 'message' => __( 'WooCommerce is not available.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$name  = isset( $_POST['rbfw_quote_billing_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_quote_billing_name'] ) ) : '';
			$email = isset( $_POST['rbfw_quote_billing_email'] ) ? sanitize_email( wp_unslash( $_POST['rbfw_quote_billing_email'] ) ) : '';
			$phone = isset( $_POST['rbfw_quote_billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_quote_billing_phone'] ) ) : '';

			if ( empty( $name ) || empty( $email ) ) {
				wp_send_json_error( array( 'message' => __( 'Please provide your name and email address.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : $rbfw_id;
			$product    = wc_get_product( $product_id );
			if ( ! $product ) {
				wp_send_json_error( array( 'message' => __( 'Linked WooCommerce product not found.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// Build the same cart-item data used by the normal add-to-cart flow.
			$rbfw_wc     = new RBFW_Woocommerce();
			$cart_data   = $rbfw_wc->rbfw_add_cart_item_func( array(), $rbfw_id );
			if ( empty( $cart_data ) || ! isset( $cart_data['rbfw_ticket_info'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Unable to calculate booking details. Please check your selections.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$total = isset( $cart_data['rbfw_tp'] ) ? (float) $cart_data['rbfw_tp'] : 0;
			$qty   = isset( $cart_data['rbfw_item_quantity'] ) ? (int) $cart_data['rbfw_item_quantity'] : 1;
			if ( $qty < 1 ) {
				$qty = 1;
			}

			$order = wc_create_order(
				array(
					'customer_id'   => get_current_user_id(),
					'customer_note' => __( 'Quote / Reserve Your Trip request', 'booking-and-rental-manager-for-woocommerce' ),
				)
			);

			if ( is_wp_error( $order ) ) {
				wp_send_json_error( array( 'message' => __( 'Could not create order. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$item = new WC_Order_Item_Product();
			$item->set_product( $product );
			$item->set_quantity( $qty );
			$item->set_subtotal( $total );
			$item->set_total( $total );
			$order->add_item( $item );
			$item->save();

			// Add the same meta the normal checkout flow writes.
			$rbfw_wc->rbfw_validate_add_order_item_func( $cart_data, $item, $rbfw_id );

			$order->set_billing_first_name( $name );
			$order->set_billing_last_name( '' );
			$order->set_billing_email( $email );
			$order->set_billing_phone( $phone );
			$order->set_payment_method( '' );
			$order->set_payment_method_title( __( 'Quote / Reserve', 'booking-and-rental-manager-for-woocommerce' ) );

			$order->calculate_totals();
			$order->update_status( 'pending', __( 'Quote request received', 'booking-and-rental-manager-for-woocommerce' ), true );

			$order_id = $order->get_id();

			update_post_meta( $order_id, '_rbfw_quote_request', 1 );
			update_post_meta( $order_id, '_rbfw_id', $rbfw_id );
			update_post_meta( $order_id, '_rbfw_ticket_info', $cart_data['rbfw_ticket_info'] );

			// Create the internal rbfw_order / rbfw_order_meta mirror.
			$rbfw_wc->rbfw_booking_management( $order_id );

			// Let other parts send notifications.
			do_action( 'rbfw_quote_order_created', $order_id, $rbfw_id );

			$redirect_url = add_query_arg(
				array(
					'rbfw_quote_submitted' => 1,
					'order_id'             => $order_id,
				),
				get_permalink( $rbfw_id )
			);

			wp_send_json_success(
				array(
					'message'      => __( 'Your quote request has been submitted.', 'booking-and-rental-manager-for-woocommerce' ),
					'order_id'     => $order_id,
					'redirect_url' => $redirect_url,
				)
			);
		}
	}

	new RBFW_Quote_Order();
}
