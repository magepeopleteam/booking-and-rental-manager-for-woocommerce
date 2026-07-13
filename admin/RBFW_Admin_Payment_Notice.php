<?php
/**
 * Admin warning notice shown when the booking system has no usable payment
 * method at all (no enabled WooCommerce gateway AND no enabled Pro custom
 * payment method). Without one, customers cannot complete any booking.
 *
 * Availability logic lives in RBFW_Payment_Status_Checker; this class only
 * wires it into `admin_notices` and renders the markup, so the check itself
 * stays reusable and unit-testable outside of WordPress hooks.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Admin_Payment_Notice' ) ) {

	class RBFW_Admin_Payment_Notice {

		/** @var RBFW_Payment_Status_Checker */
		private $checker;

		public function __construct( $checker = null ) {
			$this->checker = ( $checker instanceof RBFW_Payment_Status_Checker ) ? $checker : new RBFW_Payment_Status_Checker();
			add_action( 'admin_notices', array( $this, 'render' ) );
		}

		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Mode-aware: warn when the system that actually owns bookings right now has
			// no usable gateway — even if the other (inactive) system does.
			if ( $this->checker->has_gateway_for_active_mode() ) {
				return;
			}

			$mode = $this->checker->active_mode();
			$msg  = ( 'woocommerce' === $mode )
				? esc_html__( 'Bookings run through WooCommerce, but no WooCommerce payment gateway is enabled. Customers will not be able to complete bookings until you enable at least one.', 'booking-and-rental-manager-for-woocommerce' )
				: esc_html__( 'Bookings run through Custom Payment (Standalone), but no custom payment method is enabled. Customers will not be able to complete bookings until you enable at least one.', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Booking & Rental Manager:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
					<?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above. ?>
				</p>
				<p><?php echo wp_kses_post( $this->action_links() ); ?></p>
			</div>
			<?php
		}

		/** Build the contextual "fix it" links shown under the notice. */
		private function action_links() {
			$links = array();

			if ( function_exists( 'rbfw_has_woocommerce' ) && rbfw_has_woocommerce() ) {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ),
					esc_html__( 'Configure WooCommerce Payments', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			$is_pro = function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();

			if ( $is_pro ) {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings_page#rbfw_payment_settings' ) ),
					esc_html__( 'Configure Pro Payment Methods', 'booking-and-rental-manager-for-woocommerce' )
				);
			} else {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_go_pro_page' ) ),
					esc_html__( 'Upgrade to Pro', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			return implode( ' &nbsp;|&nbsp; ', $links );
		}
	}

	new RBFW_Admin_Payment_Notice();
}
