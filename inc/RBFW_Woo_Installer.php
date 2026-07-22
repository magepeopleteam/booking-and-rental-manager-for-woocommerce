<?php
/**
 * RBFW WooCommerce Installer
 * Handles WooCommerce dependency check, beautiful popup display,
 * and AJAX-based installation & activation.
 * The popup shows on EVERY admin page when WooCommerce is not active.
 *
 * @package BookingAndRentalManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Woo_Installer' ) ) {

	class RBFW_Woo_Installer {

		/**
		 * Constructor – hooks into WordPress.
		 */
		public function __construct() {
			// On admin_init, check if our plugin was just activated (for redirect)
			add_action( 'admin_init', array( $this, 'handle_activation_redirect' ) );
			// Enqueue popup assets on all admin pages (only outputs if WooCommerce is missing)
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			// Render the popup markup in admin footer
			add_action( 'admin_footer', array( $this, 'render_popup' ) );
			// AJAX handlers for install, activate
			add_action( 'wp_ajax_rbfw_install_woocommerce', array( $this, 'ajax_install_woocommerce' ) );
			add_action( 'wp_ajax_rbfw_activate_woocommerce', array( $this, 'ajax_activate_woocommerce' ) );
		}

		/**
		 * Check if WooCommerce plugin file exists (installed but maybe not active).
		 *
		 * @return bool
		 */
		private function is_woo_installed() {
			$plugin_file = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			return file_exists( $plugin_file );
		}

		/**
		 * Check if WooCommerce is active.
		 *
		 * @return bool
		 */
		private function is_woo_active() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			return is_plugin_active( 'woocommerce/woocommerce.php' );
		}

		/**
		 * Runs on admin_init. If the transient from activation exists
		 * and WooCommerce IS active, redirect to rental lists page.
		 */
		public function handle_activation_redirect() {
			if ( ! get_transient( 'rbfw_plugin_activated' ) ) {
				return;
			}

			// Don't redirect on multi-site bulk activations
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				delete_transient( 'rbfw_plugin_activated' );
				return;
			}

			// WooCommerce is active → redirect immediately
			if ( $this->is_woo_active() ) {
				delete_transient( 'rbfw_plugin_activated' );
				wp_safe_redirect( admin_url( 'edit.php?post_type=rbfw_item' ) );
				exit;
			}

			// WooCommerce is NOT active → clear transient, popup will show via should_show_popup()
			delete_transient( 'rbfw_plugin_activated' );
		}

		/**
		 * Should we show the popup on this page load?
		 * Always show when WooCommerce is not active and our plugin is active.
		 *
		 * @return bool
		 */
		private function should_show_popup() {
			// WooCommerce is now optional: do not force the installer popup once the admin
			// has chosen to continue in Standalone mode (dismissed the notice). The plugin
			// is fully usable without WooCommerce.
			if ( get_option( 'rbfw_standalone_dismissed' ) === 'yes' ) {
				return false;
			}
			return ! $this->is_woo_active();
		}

		/**
		 * Enqueue CSS & JS for the popup only when needed.
		 */
		public function enqueue_assets() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			wp_enqueue_style(
				'rbfw-woo-installer',
				RBFW_PLUGIN_URL . '/assets/admin/css/rbfw_woo_installer.css',
				array(),
				filemtime( RBFW_PLUGIN_DIR . '/assets/admin/css/rbfw_woo_installer.css' )
			);

			wp_enqueue_script(
				'rbfw-woo-installer',
				RBFW_PLUGIN_URL . '/assets/admin/js/rbfw_woo_installer.js',
				array( 'jquery' ),
				filemtime( RBFW_PLUGIN_DIR . '/assets/admin/js/rbfw_woo_installer.js' ),
				true
			);

			wp_localize_script( 'rbfw-woo-installer', 'rbfw_woo_installer', array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'install_nonce'    => wp_create_nonce( 'rbfw_install_woo' ),
				'activate_nonce'   => wp_create_nonce( 'rbfw_activate_woo' ),
				'redirect_url'     => admin_url( 'edit.php?post_type=rbfw_item' ),
				'woo_installed'    => $this->is_woo_installed() ? 'yes' : 'no',
				'i18n'             => array(
					'installing'     => __( 'Installing WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
					'activating'     => __( 'Activating WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
					'success'        => __( 'WooCommerce activated successfully!', 'booking-and-rental-manager-for-woocommerce' ),
					'redirecting'    => __( 'Redirecting...', 'booking-and-rental-manager-for-woocommerce' ),
					'error'          => __( 'Something went wrong. Please try again.', 'booking-and-rental-manager-for-woocommerce' ),
					'install_error'  => __( 'Installation failed. Please install WooCommerce manually.', 'booking-and-rental-manager-for-woocommerce' ),
					'activate_error' => __( 'Activation failed. Please activate WooCommerce manually.', 'booking-and-rental-manager-for-woocommerce' ),
				),
			) );
		}

		/**
		 * Render the popup HTML in admin footer.
		 */
		public function render_popup() {
			return false;
			if ( ! $this->should_show_popup() ) {
				return;
			}

			$is_installed = $this->is_woo_installed();
			$btn_text     = $is_installed
				? __( 'Activate WooCommerce', 'booking-and-rental-manager-for-woocommerce' )
				: __( 'Install & Activate WooCommerce', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<!-- RBFW WooCommerce Installer Popup Overlay -->
			<div id="rbfw-woo-overlay" class="rbfw-woo-overlay">
				<div class="rbfw-woo-popup">

					<!-- Header strip -->
					<div class="rbfw-woo-header">
						<div class="rbfw-woo-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="rbfw-woo-header-text"><?php esc_html_e( 'Booking & Rental Manager', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<!-- Icon -->
					<div class="rbfw-woo-icon-wrapper">
						<div class="rbfw-woo-icon">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
								<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
					</div>

					<!-- Content -->
					<div class="rbfw-woo-content">
						<h2 class="rbfw-woo-title"><?php esc_html_e( 'WooCommerce Required', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p class="rbfw-woo-desc">
							<?php esc_html_e( 'Booking & Rental Manager requires WooCommerce to manage bookings, rentals, and payments. Please install and activate WooCommerce to continue using this plugin.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</p>
					</div>

					<!-- Feature highlights -->
					<div class="rbfw-woo-features">
						<div class="rbfw-woo-feature">
							<span class="rbfw-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Booking & payments', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<div class="rbfw-woo-feature">
							<span class="rbfw-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Order management', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<div class="rbfw-woo-feature">
							<span class="rbfw-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Rental inventory', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
					</div>

					<!-- Progress area (hidden by default) -->
					<div id="rbfw-woo-progress" class="rbfw-woo-progress" style="display:none;">
						<div class="rbfw-woo-progress-bar">
							<div id="rbfw-woo-progress-fill" class="rbfw-woo-progress-fill"></div>
						</div>
						<p id="rbfw-woo-status-text" class="rbfw-woo-status-text"></p>
					</div>

					<!-- Action buttons -->
					<div class="rbfw-woo-actions">
						<button type="button" id="rbfw-woo-install-btn" class="rbfw-woo-btn rbfw-woo-btn-primary">
							<span class="rbfw-woo-btn-icon">
								<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
									<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
							<span class="rbfw-woo-btn-text"><?php echo esc_html( $btn_text ); ?></span>
						</button>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="rbfw-woo-btn rbfw-woo-btn-secondary">
							<?php esc_html_e( 'Install Manually', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
					</div>

					<!-- Footer note -->
					<p class="rbfw-woo-footer-note">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: -2px; flex-shrink: 0;">
							<path d="M7 1a6 6 0 100 12A6 6 0 007 1zm0 8.5a.75.75 0 110-1.5.75.75 0 010 1.5zM7.75 6.25a.75.75 0 01-1.5 0V4a.75.75 0 011.5 0v2.25z" fill="currentColor"/>
						</svg>
						<?php esc_html_e( 'WooCommerce is free, open-source, and trusted by millions of stores worldwide.', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX: Install WooCommerce from WordPress.org repository.
		 */
		public function ajax_install_woocommerce() {
			check_ajax_referer( 'rbfw_install_woo', 'nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// Download + unzip is the heaviest part of the flow; give it room.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}
			@set_time_limit( 0 );
			@ignore_user_abort( true );

			// Already installed? Skip straight to activation on the client.
			if ( $this->is_woo_installed() ) {
				wp_send_json_success( array( 'message' => __( 'WooCommerce is already installed.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$api = plugins_api( 'plugin_information', array(
				'slug'   => 'woocommerce',
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			) );

			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}

			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			if ( $result === false ) {
				wp_send_json_error( array( 'message' => __( 'Installation failed.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			wp_send_json_success( array( 'message' => __( 'WooCommerce installed successfully.', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}

		/**
		 * AJAX: Activate WooCommerce plugin.
		 */
		public function ajax_activate_woocommerce() {
			check_ajax_referer( 'rbfw_activate_woo', 'nonce' );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			// WooCommerce runs installers on activation; give it room too.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}
			@set_time_limit( 0 );

			$result = activate_plugin( 'woocommerce/woocommerce.php' );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			wp_send_json_success( array( 'message' => __( 'WooCommerce activated successfully!', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}
	}

	new RBFW_Woo_Installer();
}
