<?php
/**
 * Single Booking & Rental Manager payment notice, shown ONLY while the active booking
 * mode has no usable payment method:
 *
 *   - No usable method for the active mode -> amber "action needed" card (not
 *     dismissible — bookings can't complete until it's fixed). When WooCommerce is off
 *     the Standalone framing rides in the title, folding in the old standalone note.
 *   - A method is enabled (incl. the free Offline method) -> nothing. Setup is done, so
 *     the notice vanishes automatically the moment a payment method goes live.
 *
 * Availability logic lives in RBFW_Payment_Status_Checker; this class only wires it
 * into `admin_notices` and renders the markup, so the check stays reusable and
 * unit-testable outside of WordPress hooks. (Merges the former standalone-mode notice
 * that used to live in functions.php.)
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

			// Only speak up while the active booking mode still has NO usable payment
			// method. Once one is enabled (including the free Offline method), setup is
			// done and no notice is shown — the standalone-mode heads-up is retired so it
			// vanishes the moment a payment method goes live.
			if ( $this->checker->has_gateway_for_active_mode() ) {
				return;
			}

			if ( function_exists( 'rbfw_admin_notice_styles' ) ) {
				rbfw_admin_notice_styles();
			}

			$has_wc = function_exists( 'rbfw_has_woocommerce' ) ? rbfw_has_woocommerce() : class_exists( 'WooCommerce' );
			$this->render_action_needed( $has_wc );
		}

		/**
		 * The one payment notice: shown only when the active booking mode has no usable
		 * payment method. Amber, actionable, not dismissible. When WooCommerce is off the
		 * Standalone framing rides in the title, so this single card also carries the
		 * "running in Standalone mode" context the old separate notice used to show.
		 */
		private function render_action_needed( $has_wc ) {
			$mode = $this->checker->active_mode();
			$msg  = ( 'woocommerce' === $mode )
				? esc_html__( 'Bookings run through WooCommerce, but no WooCommerce payment gateway is enabled. Customers will not be able to complete bookings until you enable at least one.', 'booking-and-rental-manager-for-woocommerce' )
				: esc_html__( 'Bookings run through Custom Payment (Standalone), but no custom payment method is enabled. Customers will not be able to complete bookings until you enable at least one.', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<div class="notice rbfw-admin-notice rbfw-admin-notice--warning">
				<div class="rbfw-an-inner">
					<span class="rbfw-an-icon"><span class="dashicons dashicons-warning" aria-hidden="true"></span></span>
					<div class="rbfw-an-content">
						<?php if ( ! $has_wc ) : ?>
							<div class="rbfw-an-title"><?php esc_html_e( 'Booking & Rental Manager is running in Standalone mode.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
						<?php else : ?>
							<span class="rbfw-an-eyebrow"><?php esc_html_e( 'Booking & Rental Manager:', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<?php endif; ?>
						<p class="rbfw-an-text"><?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above. ?></p>
						<div class="rbfw-an-actions"><?php echo wp_kses_post( $this->action_links() ); ?></div>
					</div>
				</div>
			</div>
			<?php
		}


		/** Build the contextual "fix it" buttons shown under the notice. */
		private function action_links() {
			$links = array();
			$mode  = $this->checker->active_mode();

			// Primary fix: jump straight to the Payments settings section, where a method
			// is actually enabled. In Standalone this includes the free Offline gateway
			// (and Pro gateways); in WooCommerce mode it's where the flow is configured.
			$links[] = sprintf(
				'<a class="rbfw-an-btn rbfw-an-btn-primary" href="%s"><span class="dashicons dashicons-money-alt" aria-hidden="true"></span>%s</a>',
				esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings_page#rbfw_payment_settings' ) ),
				esc_html__( 'Set up a payment method', 'booking-and-rental-manager-for-woocommerce' )
			);

			// In WooCommerce mode, also offer a shortcut to WooCommerce's own gateway screen.
			if ( 'woocommerce' === $mode && function_exists( 'rbfw_has_woocommerce' ) && rbfw_has_woocommerce() ) {
				$links[] = sprintf(
					'<a class="rbfw-an-btn rbfw-an-btn-secondary" href="%s"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>%s</a>',
					esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ),
					esc_html__( 'Configure WooCommerce Payments', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			// Upgrade path stays available (as a low-emphasis link) for the free build.
			$is_pro = function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();
			if ( ! $is_pro ) {
				$links[] = sprintf(
					'<a class="rbfw-an-btn rbfw-an-btn-ghost" href="%s"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span>%s</a>',
					esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_go_pro_page' ) ),
					esc_html__( 'Upgrade to Pro', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			return implode( ' ', $links );
		}
	}

	new RBFW_Admin_Payment_Notice();
}
