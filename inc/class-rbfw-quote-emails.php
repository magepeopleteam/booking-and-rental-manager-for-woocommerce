<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Quote_Emails' ) ) {
	class RBFW_Quote_Emails {

		public function __construct() {
			add_action( 'rbfw_quote_order_created', array( $this, 'send_quote_request_emails' ), 10, 2 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'send_payment_link_on_processing' ), 20, 2 );
			add_filter( 'woocommerce_order_actions', array( $this, 'add_manual_payment_link_action' ) );
			add_action( 'woocommerce_order_action_rbfw_send_payment_link', array( $this, 'send_payment_link' ) );
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'render_quote_badge' ) );
		}

		public function send_quote_request_emails( $order_id, $rbfw_id ) {
			global $rbfw;
			if ( ! is_object( $rbfw ) || ! method_exists( $rbfw, 'send_email' ) ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$item_name = get_the_title( $rbfw_id );
			$total     = $order->get_formatted_order_total();
			$email     = $order->get_billing_email();

			// Customer confirmation.
			$customer_subject = sprintf(
				/* translators: %s: item name */
				__( 'Your quote request for %s has been received', 'booking-and-rental-manager-for-woocommerce' ),
				$item_name
			);
			$customer_body = sprintf(
				/* translators: 1: item name, 2: order total, 3: order number */
				__( 'Thank you for your quote request.\n\nItem: %1$s\nEstimated Total: %2$s\nOrder Number: %3$s\n\nWe are reviewing your request and will contact you shortly with the next steps.', 'booking-and-rental-manager-for-woocommerce' ),
				$item_name,
				$total,
				$order->get_order_number()
			);
			$rbfw->send_email( $email, $rbfw_id, $customer_subject, $customer_body, $order_id );

			// Admin notification.
			$admin_email = get_option( 'admin_email' );
			$admin_subject = sprintf(
				/* translators: %s: order number */
				__( 'New quote request - Order #%s', 'booking-and-rental-manager-for-woocommerce' ),
				$order->get_order_number()
			);
			$admin_body = sprintf(
				/* translators: 1: item name, 2: customer email, 3: order total, 4: order number, 5: admin order URL */
				__( 'A new quote request has been submitted.\n\nItem: %1$s\nCustomer Email: %2$s\nEstimated Total: %3$s\nOrder Number: %4$s\n\nReview the order: %5$s', 'booking-and-rental-manager-for-woocommerce' ),
				$item_name,
				$email,
				$total,
				$order->get_order_number(),
				esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) )
			);
			$rbfw->send_email( $admin_email, $rbfw_id, $admin_subject, $admin_body, $order_id );
		}

		public function send_payment_link_on_processing( $order_id, $order ) {
			if ( ! $order ) {
				$order = wc_get_order( $order_id );
			}
			if ( ! $order ) {
				return;
			}

			if ( ! get_post_meta( $order_id, '_rbfw_quote_request', true ) ) {
				return;
			}

			$this->send_payment_link( $order );
		}

		public function send_payment_link( $order ) {
			global $rbfw;
			if ( ! is_object( $rbfw ) || ! method_exists( $rbfw, 'send_email' ) ) {
				return;
			}

			$order_id = is_numeric( $order ) ? (int) $order : $order->get_id();
			$order    = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$rbfw_id = (int) $order->get_meta( '_rbfw_id' );
			if ( ! $rbfw_id ) {
				foreach ( $order->get_items() as $item ) {
					$rbfw_id = (int) $item->get_meta( '_rbfw_id' );
					if ( $rbfw_id ) {
						break;
					}
				}
			}

			$item_name = $rbfw_id ? get_the_title( $rbfw_id ) : __( 'your selected item', 'booking-and-rental-manager-for-woocommerce' );
			$payment_url = $order->get_checkout_payment_url();
			$email       = $order->get_billing_email();

			$subject = sprintf(
				/* translators: %s: item name */
				__( 'Payment link for %s', 'booking-and-rental-manager-for-woocommerce' ),
				$item_name
			);
			$body = sprintf(
				/* translators: 1: item name, 2: order total, 3: payment URL */
				__( 'Good news! Your quote request for %1$s has been confirmed.\n\nOrder Total: %2$s\n\nPlease complete your payment using the link below:\n%3$s\n\nThank you for choosing us.', 'booking-and-rental-manager-for-woocommerce' ),
				$item_name,
				$order->get_formatted_order_total(),
				esc_url( $payment_url )
			);

			$rbfw->send_email( $email, $rbfw_id, $subject, $body, $order_id );
		}

		public function render_quote_badge( $order ) {
			$order_id = $order->get_id();
			if ( ! get_post_meta( $order_id, '_rbfw_quote_request', true ) ) {
				return;
			}

			$payment_url = $order->get_checkout_payment_url();
			?>
			<div class="notice notice-info inline" style="margin-top: 12px;">
				<p>
					<strong><?php esc_html_e( 'Quote / Reserve Your Trip', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'This order was created from a quote request. The customer has not paid yet.', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Customer payment link:', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<input type="text" value="<?php echo esc_url( $payment_url ); ?>" readonly style="width: 100%;" onclick="this.select();">
				</p>
			</div>
			<?php
		}

		public function add_manual_payment_link_action( $actions ) {
			$screen = get_current_screen();
			if ( ! $screen || $screen->id !== 'shop_order' ) {
				return $actions;
			}

			$order_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			if ( ! $order_id ) {
				return $actions;
			}

			if ( get_post_meta( $order_id, '_rbfw_quote_request', true ) ) {
				$actions['rbfw_send_payment_link'] = __( 'Send quote payment link', 'booking-and-rental-manager-for-woocommerce' );
			}

			return $actions;
		}
	}

	new RBFW_Quote_Emails();
}
