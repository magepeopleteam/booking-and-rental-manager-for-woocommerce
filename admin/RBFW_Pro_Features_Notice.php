<?php
/**
 * Dismissible "Booking & Rental Manager Pro" upsell notice.
 *
 * Highlights the key Pro-only features as a compact grid of chips and links to the
 * Get PRO page. Shown only in the free build (hidden once Pro is active), only on
 * the plugin's own admin screens (plus the main Plugins list), and never again once
 * the admin dismisses it (stored in the rbfw_pro_promo_dismissed option).
 *
 * Reuses rbfw_admin_notice_styles() for the shared card chrome. The highlighted
 * features mirror the Get PRO page categories — Offline is deliberately excluded
 * because it is a free payment method (see RBFW_Function::offline_payment_enabled()).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Pro_Features_Notice' ) ) {

	class RBFW_Pro_Features_Notice {

		const DISMISS_OPTION = 'rbfw_pro_promo_dismissed';

		public function __construct() {
			add_action( 'admin_init', array( $this, 'handle_dismiss' ) );
			add_action( 'admin_notices', array( $this, 'render' ) );
		}

		private function is_pro() {
			return function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();
		}

		/** Persist dismissal (nonce + capability checked) so the notice never returns. */
		public function handle_dismiss() {
			if ( ! isset( $_GET['rbfw_dismiss_pro_promo'] ) || '1' !== $_GET['rbfw_dismiss_pro_promo'] ) {
				return;
			}
			if ( isset( $_GET['_wpnonce'] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'rbfw_dismiss_pro_promo' )
				&& current_user_can( 'manage_options' ) ) {
				update_option( self::DISMISS_OPTION, 'yes' );
			}
		}

		/** Limit to the plugin's own admin pages + the main Plugins list. */
		private function is_target_screen() {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}
			$screen = get_current_screen();
			if ( ! $screen ) {
				return false;
			}
			if ( 'rbfw_item_page_rbfw_go_pro_page' === $screen->id ) {
				return false; // already on the Pro page
			}
			if ( false !== strpos( $screen->id, 'rbfw_item' ) ) {
				return true;
			}
			return 'plugins' === $screen->id;
		}

		/**
		 * The Pro-only highlights (label + core Dashicon), mirroring the Get PRO page
		 * feature categories. Offline is a free method, so it is not listed here.
		 */
		private function features() {
			return array(
				array( 'icon' => 'cart',               'label' => __( 'PayPal & Stripe checkout', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'calendar-alt',       'label' => __( 'Booking calendar & orders dashboard', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'media-document',     'label' => __( 'Branded PDF & POS receipts', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'welcome-write-blog', 'label' => __( 'Drag-and-drop booking forms', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'star-filled',        'label' => __( 'Reviews & ratings', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'chart-bar',          'label' => __( 'Reports & CSV export', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'share',              'label' => __( 'Google Calendar sync', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'email-alt',          'label' => __( 'Editable notification emails', 'booking-and-rental-manager-for-woocommerce' ) ),
			);
		}

		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( $this->is_pro() ) {
				return; // Pro is active — nothing to upsell.
			}
			if ( 'yes' === get_option( self::DISMISS_OPTION ) ) {
				return; // Dismissed for good.
			}
			if ( ! $this->is_target_screen() ) {
				return;
			}

			if ( function_exists( 'rbfw_admin_notice_styles' ) ) {
				rbfw_admin_notice_styles();
			}
			$this->print_styles();

			$pro_url     = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_go_pro_page' );
			$dismiss_url = wp_nonce_url( add_query_arg( 'rbfw_dismiss_pro_promo', '1' ), 'rbfw_dismiss_pro_promo' );
			?>
			<div class="notice rbfw-admin-notice rbfw-admin-notice--pro">
				<div class="rbfw-an-inner">
					<span class="rbfw-an-icon"><span class="dashicons dashicons-awards" aria-hidden="true"></span></span>
					<div class="rbfw-an-content">
						<span class="rbfw-an-eyebrow"><?php esc_html_e( 'Booking & Rental Manager Pro', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<div class="rbfw-an-title"><?php esc_html_e( 'Unlock every premium feature', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
						<p class="rbfw-an-text"><?php esc_html_e( 'You\'re on the free plugin. Upgrade to Pro to add online payments and the full booking toolkit:', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<ul class="rbfw-pro-chips">
							<?php foreach ( $this->features() as $f ) : ?>
								<li class="rbfw-pro-chip">
									<span class="dashicons dashicons-<?php echo esc_attr( $f['icon'] ); ?>" aria-hidden="true"></span>
									<?php echo esc_html( $f['label'] ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
						<div class="rbfw-an-actions">
							<a class="rbfw-an-btn rbfw-an-btn-primary" href="<?php echo esc_url( $pro_url ); ?>">
								<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
								<?php esc_html_e( 'Get Pro Now', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</a>
							<a class="rbfw-an-btn rbfw-an-btn-ghost" href="<?php echo esc_url( $dismiss_url ); ?>">
								<?php esc_html_e( 'Maybe later', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/** Pro-specific styling layered on top of the shared notice chrome. Printed once. */
		private function print_styles() {
			static $printed = false;
			if ( $printed ) {
				return;
			}
			$printed = true;
			?>
			<style id="rbfw-pro-promo-styles">
				.rbfw-admin-notice--pro{border-left-color:#7b2ff7 !important;}
				.rbfw-admin-notice--pro .rbfw-an-icon{background:linear-gradient(135deg,#F12971 0%,#7b2ff7 100%);color:#fff;}
				.rbfw-admin-notice--pro .rbfw-an-eyebrow{color:#7b2ff7;}
				.rbfw-pro-chips{list-style:none;margin:12px 0 4px;padding:0;display:flex;flex-wrap:wrap;gap:8px;}
				.rbfw-pro-chip{display:inline-flex;align-items:center;gap:8px;font-size:12.5px;color:#374151;background:#faf5ff;border:1px solid #efe3fb;border-radius:8px;padding:7px 12px;line-height:1.35;white-space:nowrap;}
				.rbfw-pro-chip .dashicons{font-size:16px;width:16px;height:16px;line-height:1;color:#F12971;flex:0 0 auto;}
				@media (max-width:600px){.rbfw-pro-chip{flex:1 1 100%;white-space:normal;}}
			</style>
			<?php
		}
	}

	new RBFW_Pro_Features_Notice();
}
