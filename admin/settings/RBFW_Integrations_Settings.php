<?php
/**
 * Third-party integration status and setup guidance.
 *
 * Every setup step on the card runs in place over AJAX: the step's request makes
 * the change, then a second request renders the card again, so plugins activated
 * by the first request are fully loaded when their status is read.
 *
 * @package Booking_And_Rental_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Integrations_Settings' ) ) {
	class RBFW_Integrations_Settings {

		const SECTION                      = 'rbfw_integrations_settings';
		const SECUREHOLD_PLUGIN            = RBFW_SecureHold_Compat::PLUGIN;
		const SECUREHOLD_SLUG              = 'securehold-security-deposit-holds';
		const STRIPE_PLUGIN                = 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php';
		const STRIPE_SLUG                  = 'woocommerce-gateway-stripe';
		const WOOCOMMERCE_PLUGIN           = 'woocommerce/woocommerce.php';
		const MIN_SECUREHOLD               = RBFW_SecureHold_Compat::MIN_VERSION;
		const SECUREHOLD_MAGEPEOPLE_OPTION = RBFW_SecureHold_Compat::BRIDGE_OPTION;
		const SECUREHOLD_GLOBAL_OPTION     = 'securehold_default_hold_amount';
		const NONCE_ACTION                 = 'rbfw_manage_integration_plugins';

		public function __construct() {
			add_filter( 'rbfw_settings_sec_reg', array( $this, 'register_section' ), 14 );
			add_action( 'wsa_form_bottom_' . self::SECTION, array( $this, 'render' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_ajax_rbfw_manage_integration_plugin', array( $this, 'ajax_manage_plugin' ) );
			add_action( 'wp_ajax_rbfw_integration_enable_securehold_bridge', array( $this, 'ajax_enable_securehold_bridge' ) );
			add_action( 'wp_ajax_rbfw_integration_use_wc_checkout', array( $this, 'ajax_use_wc_checkout' ) );
			add_action( 'wp_ajax_rbfw_integration_securehold_global_hold_off', array( $this, 'ajax_securehold_global_hold_off' ) );
			add_action( 'wp_ajax_rbfw_integration_card', array( $this, 'ajax_card' ) );
		}

		/** Load the in-place setup script only on the rental settings screen. */
		public function enqueue_assets() {
			$screen = get_current_screen();
			if ( ! $screen || false === strpos( (string) $screen->id, 'rbfw_settings_page' ) ) {
				return;
			}

			$script_path = RBFW_PLUGIN_DIR . '/admin/js/rbfw-integrations.js';
			wp_enqueue_script(
				'rbfw-integrations',
				RBFW_PLUGIN_URL . '/admin/js/rbfw-integrations.js',
				array( 'jquery' ),
				file_exists( $script_path ) ? filemtime( $script_path ) : false,
				true
			);
			wp_localize_script(
				'rbfw-integrations',
				'rbfwIntegrationInstaller',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
					'i18n'    => array(
						'working' => __( 'Please wait…', 'booking-and-rental-manager-for-woocommerce' ),
						'failed'  => __( 'That step could not be completed. Please try again.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				)
			);
		}

		/**
		 * Plugins that may be installed from this screen.
		 *
		 * @return array<string,array{slug:string,file:string,label:string}>
		 */
		private function managed_plugins() {
			return array(
				'woocommerce' => array(
					'slug'  => 'woocommerce',
					'file'  => self::WOOCOMMERCE_PLUGIN,
					'label' => __( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ),
				),
				'securehold'  => array(
					'slug'  => self::SECUREHOLD_SLUG,
					'file'  => self::SECUREHOLD_PLUGIN,
					'label' => __( 'SecureHold WP', 'booking-and-rental-manager-for-woocommerce' ),
				),
				'stripe'      => array(
					'slug'  => self::STRIPE_SLUG,
					'file'  => self::STRIPE_PLUGIN,
					'label' => __( 'WooCommerce Stripe Gateway', 'booking-and-rental-manager-for-woocommerce' ),
				),
			);
		}

		/**
		 * Verify the request nonce and a capability, or stop with a JSON error.
		 *
		 * @param string $capability Required capability.
		 */
		private function verify_request( $capability ) {
			check_ajax_referer( self::NONCE_ACTION, 'nonce' );
			if ( ! current_user_can( $capability ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
			}
		}

		/** Install, update when required, and activate an approved integration plugin. */
		public function ajax_manage_plugin() {
			$this->verify_request( 'activate_plugins' );

			$plugin_key = isset( $_POST['plugin'] ) ? sanitize_key( wp_unslash( $_POST['plugin'] ) ) : '';
			$plugins    = $this->managed_plugins();
			if ( ! isset( $plugins[ $plugin_key ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid integration plugin.', 'booking-and-rental-manager-for-woocommerce' ) ), 400 );
			}

			$plugin = $plugins[ $plugin_key ];

			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$state        = $this->plugin_state( $plugin['file'] );
			$needs_update = 'securehold' === $plugin_key && $state['installed'] && version_compare( $state['version'], self::MIN_SECUREHOLD, '<' );
			$operation    = 'none';

			if ( ! $state['installed'] || $needs_update ) {
				$capability = $state['installed'] ? 'update_plugins' : 'install_plugins';
				if ( ! current_user_can( $capability ) ) {
					wp_send_json_error( array( 'message' => __( 'You do not have permission to install or update plugins.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
				}

				if ( function_exists( 'wp_raise_memory_limit' ) ) {
					wp_raise_memory_limit( 'admin' );
				}
				@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

				$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
				if ( $needs_update ) {
					$result    = $upgrader->upgrade( $plugin['file'] );
					$operation = 'updated';
				} else {
					$api = plugins_api(
						'plugin_information',
						array(
							'slug'   => $plugin['slug'],
							'fields' => array( 'sections' => false ),
						)
					);
					if ( is_wp_error( $api ) ) {
						wp_send_json_error( array( 'message' => $api->get_error_message() ), 500 );
					}
					$result    = $upgrader->install( $api->download_link );
					$operation = 'installed';
				}

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
				}
				if ( ! $result ) {
					wp_send_json_error( array( 'message' => __( 'The plugin installation or update failed.', 'booking-and-rental-manager-for-woocommerce' ) ), 500 );
				}

				wp_clean_plugins_cache( true );
				if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['file'] ) ) {
					wp_send_json_error( array( 'message' => __( 'The expected plugin file was not found after installation.', 'booking-and-rental-manager-for-woocommerce' ) ), 500 );
				}
			}

			if ( ! is_plugin_active( $plugin['file'] ) ) {
				if ( 'woocommerce' === $plugin_key ) {
					$this->activate_woocommerce();
				} else {
					$result = activate_plugin( $plugin['file'] );
					if ( is_wp_error( $result ) ) {
						wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
					}
				}
				$operation = 'none' === $operation ? 'activated' : $operation;
			}

			// The Stripe gateway queues a jump to its own settings for the next plugin
			// activation; setup continues on this screen instead.
			if ( 'stripe' === $plugin_key ) {
				delete_transient( 'wc_stripe_redirect_to_settings' );
			}

			/* translators: %s: plugin name. */
			$message = sprintf( __( '%s is installed and active.', 'booking-and-rental-manager-for-woocommerce' ), $plugin['label'] );
			if ( 'updated' === $operation ) {
				/* translators: %s: plugin name. */
				$message = sprintf( __( '%s was updated and is active.', 'booking-and-rental-manager-for-woocommerce' ), $plugin['label'] );
			}

			wp_send_json_success(
				array(
					'message'   => $message,
					'operation' => $operation,
				)
			);
		}

		/**
		 * Activate WooCommerce without loading it into this request.
		 *
		 * Same approach as the Payments tab installer: loading woocommerce.php here
		 * would collide with the plugin's wc_price()/WC() fallbacks. WooCommerce runs
		 * its installer on its first real load, which is the card refresh request.
		 */
		private function activate_woocommerce() {
			$active = get_option( 'active_plugins', array() );
			$active = is_array( $active ) ? $active : array();
			if ( ! in_array( self::WOOCOMMERCE_PLUGIN, $active, true ) ) {
				$active[] = self::WOOCOMMERCE_PLUGIN;
				sort( $active );
				update_option( 'active_plugins', $active );
			}
			do_action( 'activate_' . self::WOOCOMMERCE_PLUGIN );
			do_action( 'activated_plugin', self::WOOCOMMERCE_PLUGIN, false );
		}

		/**
		 * Turn on SecureHold's "Use MagePeople security deposit amounts" setting.
		 *
		 * Writes the same option, with the same value and capability, as SecureHold's
		 * own Deposit Rules form; it can still be switched off there.
		 */
		public function ajax_enable_securehold_bridge() {
			$this->verify_request( 'manage_options' );

			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$securehold = $this->plugin_state( self::SECUREHOLD_PLUGIN );
			if ( ! $securehold['active'] || version_compare( $securehold['version'], self::MIN_SECUREHOLD, '<' ) ) {
				wp_send_json_error(
					array(
						/* translators: %s: minimum SecureHold version. */
						'message' => sprintf( __( 'SecureHold WP %s or later must be active first.', 'booking-and-rental-manager-for-woocommerce' ), self::MIN_SECUREHOLD ),
					),
					409
				);
			}

			update_option( self::SECUREHOLD_MAGEPEOPLE_OPTION, 'yes' );

			wp_send_json_success( array( 'message' => __( 'MagePeople compatibility is enabled in SecureHold.', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}

		/**
		 * Stop SecureHold's Global default hold, so it only holds fixed rental deposits.
		 *
		 * SecureHold holds its Default Hold Amount on every order that has no fixed
		 * rental deposit, including rentals whose percentage deposit this plugin
		 * already charges. Writes "0" to the same option as SecureHold's own
		 * Default Hold Amount field, where it can be changed back.
		 */
		public function ajax_securehold_global_hold_off() {
			$this->verify_request( 'manage_options' );

			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( ! is_plugin_active( self::SECUREHOLD_PLUGIN ) ) {
				wp_send_json_error( array( 'message' => __( 'SecureHold WP must be active first.', 'booking-and-rental-manager-for-woocommerce' ) ), 409 );
			}

			update_option( self::SECUREHOLD_GLOBAL_OPTION, '0' );

			wp_send_json_success( array( 'message' => __( 'SecureHold now holds only fixed WpRently deposits.', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}

		/**
		 * SecureHold's Global default hold as stored ("300", "20%", or "0").
		 *
		 * @return array{raw:string,active:bool}
		 */
		private function securehold_global_hold() {
			$raw = trim( (string) get_option( self::SECUREHOLD_GLOBAL_OPTION, '300' ) );

			return array(
				'raw'    => $raw,
				'active' => (float) str_replace( '%', '', $raw ) > 0,
			);
		}

		/** Switch rental bookings to the WooCommerce checkout, as the Payments tab does. */
		public function ajax_use_wc_checkout() {
			$this->verify_request( 'manage_options' );

			if ( RBFW_Function::use_wc() ) {
				wp_send_json_success( array( 'message' => __( 'Rental bookings already use the WooCommerce checkout.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			// Same rule as the Payments tab: only a real two-way choice can be changed.
			if ( 'both' !== RBFW_Function::mode_availability() ) {
				wp_send_json_error( array( 'message' => __( 'WooCommerce must be active before bookings can use its checkout.', 'booking-and-rental-manager-for-woocommerce' ) ), 409 );
			}

			RBFW_Function::set_booking_mode( 'woocommerce' );

			wp_send_json_success( array( 'message' => __( 'Rental bookings now use the WooCommerce checkout.', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}

		/** Render the card again after a setup step. */
		public function ajax_card() {
			$this->verify_request( 'manage_options' );

			// A brand-new WooCommerce install queues its onboarding redirect on this, its
			// first load; setup continues on this screen instead.
			if ( isset( $_POST['after'] ) && 'woocommerce' === sanitize_key( wp_unslash( $_POST['after'] ) ) ) {
				delete_transient( '_wc_activation_redirect' );
			}

			ob_start();
			$this->render();
			wp_send_json_success( array( 'html' => ob_get_clean() ) );
		}

		/**
		 * Register the Integrations tab in Global Settings.
		 *
		 * @param array $sections Existing settings sections.
		 * @return array
		 */
		public function register_section( $sections ) {
			$sections[] = array(
				'id'    => self::SECTION,
				'title' => '<i class="fas fa-plug"></i>' . esc_html__( 'Integrations', 'booking-and-rental-manager-for-woocommerce' ),
			);

			return $sections;
		}

		/**
		 * Return installed plugin metadata without loading the integration.
		 *
		 * @param string $basename Plugin basename.
		 * @return array{installed:bool,active:bool,version:string}
		 */
		private function plugin_state( $basename ) {
			if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();

			return array(
				'installed' => isset( $plugins[ $basename ] ),
				'active'    => is_plugin_active( $basename ),
				'version'   => isset( $plugins[ $basename ]['Version'] ) ? (string) $plugins[ $basename ]['Version'] : '',
			);
		}

		/**
		 * Titles of enabled WooCommerce payment methods other than Stripe.
		 *
		 * @return string[]
		 */
		private function other_enabled_gateways() {
			if ( ! function_exists( 'WC' ) || ! WC() || ! method_exists( WC(), 'payment_gateways' ) ) {
				return array();
			}

			$titles = array();
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( 'yes' === $gateway->enabled && 0 !== strpos( (string) $gateway->id, 'stripe' ) ) {
					$titles[] = wp_strip_all_tags( $gateway->get_method_title() ? $gateway->get_method_title() : $gateway->get_title() );
				}
			}

			return $titles;
		}

		/**
		 * Render one readiness row.
		 *
		 * @param string $label  Row label.
		 * @param string $state  'ready', 'action' or 'advice'.
		 * @param string $detail Human-readable status.
		 */
		private function status_row( $label, $state, $detail ) {
			$icons = array(
				'ready'  => 'dashicons-yes-alt',
				'action' => 'dashicons-warning',
				'advice' => 'dashicons-info-outline',
			);
			$class = 'ready' === $state ? 'is-ready' : ( 'advice' === $state ? 'is-advice' : 'needs-action' );
			?>
			<div class="rbfw-integration-status-row">
				<span class="rbfw-integration-status-icon <?php echo esc_attr( $class ); ?>">
					<span class="dashicons <?php echo esc_attr( $icons[ $state ] ); ?>"></span>
				</span>
				<span class="rbfw-integration-status-copy">
					<strong><?php echo esc_html( $label ); ?></strong>
					<small><?php echo esc_html( $detail ); ?></small>
				</span>
			</div>
			<?php
		}

		/**
		 * Render one setup step as an in-place AJAX button.
		 *
		 * @param array $step {action, label, progress, plugin?, confirm?}.
		 * @param bool  $primary Whether this is the next recommended step.
		 */
		private function step_button( $step, $primary ) {
			?>
			<button type="button"
				class="button <?php echo esc_attr( $primary ? 'button-primary' : '' ); ?> rbfw-integration-action"
				data-action="<?php echo esc_attr( $step['action'] ); ?>"
				data-plugin="<?php echo esc_attr( isset( $step['plugin'] ) ? $step['plugin'] : '' ); ?>"
				data-progress-label="<?php echo esc_attr( $step['progress'] ); ?>"
				<?php if ( ! empty( $step['confirm'] ) ) : ?>data-confirm="<?php echo esc_attr( $step['confirm'] ); ?>"<?php endif; ?>
			><?php echo esc_html( $step['label'] ); ?></button>
			<?php
		}

		/**
		 * Render a setup page that cannot run in place, opened in a new tab so this
		 * screen stays put and refreshes its status when the admin returns.
		 *
		 * @param string $url     Destination.
		 * @param string $label   Button label.
		 * @param bool   $primary Whether this is the next recommended step.
		 */
		private function setup_link( $url, $label, $primary ) {
			?>
			<a class="button <?php echo esc_attr( $primary ? 'button-primary' : '' ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" data-refresh-on-return="1"><?php echo esc_html( $label ); ?></a>
			<?php
		}

		/** Render the SecureHold integration card. */
		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$securehold    = $this->plugin_state( self::SECUREHOLD_PLUGIN );
			$stripe        = $this->plugin_state( self::STRIPE_PLUGIN );
			$woocommerce   = $this->plugin_state( self::WOOCOMMERCE_PLUGIN );
			$woo_loaded    = $woocommerce['active'] && RBFW_Function::has_woocommerce();
			$woo_mode      = $woo_loaded && RBFW_Function::use_wc();
			$version_ready = $securehold['installed'] && version_compare( $securehold['version'], self::MIN_SECUREHOLD, '>=' );
			$bridge_ready  = $securehold['active'] && 'yes' === get_option( self::SECUREHOLD_MAGEPEOPLE_OPTION, 'no' );
			$keys          = RBFW_SecureHold_Compat::stripe_status();
			$gateway_ready = $stripe['active'] && $keys['gateway_enabled'] && $keys['gateway_keys'];
			$link_ready    = $securehold['active'] && $keys['securehold_keys'] && $keys['modes_match'];
			$ready         = $woo_mode && $gateway_ready && $securehold['active'] && $version_ready && $link_ready && $bridge_ready;
			$mode_names    = array(
				'test' => __( 'test', 'booking-and-rental-manager-for-woocommerce' ),
				'live' => __( 'live', 'booking-and-rental-manager-for-woocommerce' ),
			);

			// WooCommerce checkout.
			if ( ! $woocommerce['installed'] ) {
				$woo_detail = __( 'WooCommerce is not installed', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $woo_loaded ) {
				$woo_detail = __( 'WooCommerce is installed but not active', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $woo_mode ) {
				$woo_detail = __( 'Rental bookings use the Standalone checkout, where SecureHold cannot hold deposits', 'booking-and-rental-manager-for-woocommerce' );
			} else {
				$woo_detail = __( 'Active booking mode', 'booking-and-rental-manager-for-woocommerce' );
			}

			// Official Stripe gateway.
			if ( ! $stripe['installed'] ) {
				$stripe_detail = __( 'Not installed', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $stripe['active'] ) {
				$stripe_detail = __( 'Installed but not active', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $keys['gateway_enabled'] ) {
				$stripe_detail = __( 'Active, but not enabled as a checkout payment method', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $keys['gateway_keys'] ) {
				/* translators: %s: Stripe mode, "test" or "live". */
				$stripe_detail = sprintf( __( 'Enabled, but no %s mode API keys are connected', 'booking-and-rental-manager-for-woocommerce' ), $mode_names[ $keys['gateway_mode'] ] );
			} else {
				/* translators: %s: Stripe mode, "test" or "live". */
				$stripe_detail = sprintf( __( 'Enabled in %s mode', 'booking-and-rental-manager-for-woocommerce' ), $mode_names[ $keys['gateway_mode'] ] );
			}

			// SecureHold's own Stripe connection.
			if ( ! $securehold['active'] ) {
				$link_detail = __( 'Available once SecureHold is active', 'booking-and-rental-manager-for-woocommerce' );
			} elseif ( ! $keys['securehold_keys'] ) {
				/* translators: %s: Stripe mode, "test" or "live". */
				$link_detail = sprintf( __( 'Add your Stripe %s mode API keys in SecureHold', 'booking-and-rental-manager-for-woocommerce' ), $mode_names[ $keys['securehold_mode'] ] );
			} elseif ( ! $keys['modes_match'] ) {
				$link_detail = sprintf(
					/* translators: 1: SecureHold Stripe mode, 2: WooCommerce Stripe mode. */
					__( 'SecureHold uses %1$s mode but the Stripe gateway uses %2$s mode; both must match', 'booking-and-rental-manager-for-woocommerce' ),
					$mode_names[ $keys['securehold_mode'] ],
					$mode_names[ $keys['gateway_mode'] ]
				);
			} else {
				/* translators: %s: Stripe mode, "test" or "live". */
				$link_detail = sprintf( __( '%s mode API keys saved', 'booking-and-rental-manager-for-woocommerce' ), ucfirst( $mode_names[ $keys['securehold_mode'] ] ) );
			}

			// Setup steps, in the order they unblock each other. The first one is primary.
			$steps = array();
			if ( ! $woo_loaded ) {
				$steps[] = array(
					'action'   => 'rbfw_manage_integration_plugin',
					'plugin'   => 'woocommerce',
					'label'    => $woocommerce['installed'] ? __( 'Activate WooCommerce', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Install WooCommerce', 'booking-and-rental-manager-for-woocommerce' ),
					'progress' => $woocommerce['installed'] ? __( 'Activating WooCommerce…', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Installing WooCommerce…', 'booking-and-rental-manager-for-woocommerce' ),
				);
			} elseif ( ! $woo_mode && 'both' === RBFW_Function::mode_availability() ) {
				$steps[] = array(
					'action'   => 'rbfw_integration_use_wc_checkout',
					'label'    => __( 'Use WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' ),
					'progress' => __( 'Switching to WooCommerce checkout…', 'booking-and-rental-manager-for-woocommerce' ),
					'confirm'  => __( 'Rental bookings will be paid through the WooCommerce checkout instead of the Standalone checkout. Continue?', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}
			if ( ! $securehold['installed'] || ! $securehold['active'] || ! $version_ready ) {
				if ( ! $securehold['installed'] ) {
					$label    = __( 'Install SecureHold', 'booking-and-rental-manager-for-woocommerce' );
					$progress = __( 'Installing SecureHold…', 'booking-and-rental-manager-for-woocommerce' );
				} elseif ( ! $version_ready ) {
					$label    = __( 'Update SecureHold', 'booking-and-rental-manager-for-woocommerce' );
					$progress = __( 'Updating SecureHold…', 'booking-and-rental-manager-for-woocommerce' );
				} else {
					$label    = __( 'Activate SecureHold', 'booking-and-rental-manager-for-woocommerce' );
					$progress = __( 'Activating SecureHold…', 'booking-and-rental-manager-for-woocommerce' );
				}
				$steps[] = array(
					'action'   => 'rbfw_manage_integration_plugin',
					'plugin'   => 'securehold',
					'label'    => $label,
					'progress' => $progress,
				);
			}
			if ( ! $stripe['active'] ) {
				$steps[] = array(
					'action'   => 'rbfw_manage_integration_plugin',
					'plugin'   => 'stripe',
					'label'    => $stripe['installed'] ? __( 'Activate Stripe Gateway', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Install Stripe Gateway', 'booking-and-rental-manager-for-woocommerce' ),
					'progress' => $stripe['installed'] ? __( 'Activating Stripe Gateway…', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Installing Stripe Gateway…', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}
			if ( $securehold['active'] && $version_ready && ! $bridge_ready ) {
				$steps[] = array(
					'action'   => 'rbfw_integration_enable_securehold_bridge',
					'label'    => __( 'Enable Compatibility', 'booking-and-rental-manager-for-woocommerce' ),
					'progress' => __( 'Enabling compatibility…', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}
			// SecureHold's Global default hold lands on every order without a fixed rental
			// deposit, on top of a percentage deposit this plugin already charges.
			$global_hold = $this->securehold_global_hold();
			if ( $bridge_ready && $global_hold['active'] ) {
				$steps[] = array(
					'action'   => 'rbfw_integration_securehold_global_hold_off',
					'label'    => __( 'Hold Only WpRently Deposits', 'booking-and-rental-manager-for-woocommerce' ),
					'progress' => __( 'Updating SecureHold…', 'booking-and-rental-manager-for-woocommerce' ),
					'confirm'  => __( 'SecureHold’s Default Hold Amount will be set to 0, so it only holds fixed WpRently security deposits. Continue?', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}

			// Steps that need a third-party screen (Stripe account connection).
			$links = array();
			if ( $stripe['active'] && $woo_loaded && ! $gateway_ready ) {
				$links[] = array(
					'url'   => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=stripe' ),
					'label' => __( 'Set Up Stripe Gateway', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}
			if ( $securehold['active'] && ! $link_ready ) {
				$links[] = array(
					'url'   => admin_url( 'admin.php?page=securehold-settings&tab=connection' ),
					'label' => __( 'Connect Stripe in SecureHold', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}

			$other_gateways = $woo_mode ? $this->other_enabled_gateways() : array();
			?>
			<div class="rbfw-integration-card <?php echo esc_attr( $ready ? 'is-ready' : 'needs-action' ); ?>">
				<div class="rbfw-integration-card-head">
					<img src="<?php echo esc_url( RBFW_PLUGIN_URL . '/assets/images/securehold-icon.png' ); ?>" alt="<?php esc_attr_e( 'SecureHold WP', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<div>
						<span class="rbfw-integration-eyebrow"><?php esc_html_e( 'Compatible third-party option', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<h3><?php esc_html_e( 'SecureHold WP', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Use fixed WpRently security deposits as separate Stripe authorization holds instead of adding them to the WooCommerce payable total.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<span class="rbfw-integration-ready-badge <?php echo esc_attr( $ready ? 'is-ready' : 'needs-action' ); ?>">
						<?php echo esc_html( $ready ? __( 'Ready', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Setup required', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
					</span>
				</div>

				<div class="rbfw-integration-card-body">
					<div class="rbfw-integration-status-list">
						<?php
						$this->status_row( __( 'WooCommerce checkout', 'booking-and-rental-manager-for-woocommerce' ), $woo_mode ? 'ready' : 'action', $woo_detail );
						$this->status_row( __( 'Official WooCommerce Stripe Gateway', 'booking-and-rental-manager-for-woocommerce' ), $gateway_ready ? 'ready' : 'action', $stripe_detail );
						$this->status_row(
							__( 'SecureHold WP', 'booking-and-rental-manager-for-woocommerce' ),
							$securehold['active'] && $version_ready ? 'ready' : 'action',
							$securehold['installed']
								? sprintf(
									/* translators: 1: installed SecureHold version, 2: minimum supported version. */
									__( 'Installed version %1$s; requires %2$s or later', 'booking-and-rental-manager-for-woocommerce' ),
									$securehold['version'],
									self::MIN_SECUREHOLD
								)
								: __( 'Not installed', 'booking-and-rental-manager-for-woocommerce' )
						);
						$this->status_row( __( 'SecureHold Stripe connection', 'booking-and-rental-manager-for-woocommerce' ), $link_ready ? 'ready' : 'action', $link_detail );
						$this->status_row(
							__( 'MagePeople compatibility', 'booking-and-rental-manager-for-woocommerce' ),
							$bridge_ready ? 'ready' : 'action',
							$bridge_ready ? __( 'Enabled in SecureHold', 'booking-and-rental-manager-for-woocommerce' ) : __( 'Enable “Use MagePeople security deposit amounts” in SecureHold', 'booking-and-rental-manager-for-woocommerce' )
						);
						if ( $bridge_ready ) {
							$this->status_row(
								__( 'SecureHold global hold', 'booking-and-rental-manager-for-woocommerce' ),
								$global_hold['active'] ? 'advice' : 'ready',
								$global_hold['active']
									? sprintf(
										/* translators: %s: SecureHold Default Hold Amount, e.g. "300" or "20%". */
										__( 'SecureHold also holds its default %s on every order without a fixed WpRently deposit, including rentals whose percentage deposit WpRently already charges.', 'booking-and-rental-manager-for-woocommerce' ),
										false !== strpos( $global_hold['raw'], '%' ) || ! function_exists( 'wc_price' ) ? $global_hold['raw'] : wp_strip_all_tags( wc_price( (float) $global_hold['raw'] ) )
									)
									: __( 'Off: only fixed WpRently deposits are held', 'booking-and-rental-manager-for-woocommerce' )
							);
						}
						if ( $other_gateways ) {
							$this->status_row(
								__( 'Other payment methods', 'booking-and-rental-manager-for-woocommerce' ),
								'advice',
								sprintf(
									/* translators: %s: comma-separated payment method names. */
									__( 'Bookings paid with %s get no deposit hold; only Stripe card payments do.', 'booking-and-rental-manager-for-woocommerce' ),
									implode( ', ', $other_gateways )
								)
							);
						}
						?>
					</div>

					<div class="rbfw-integration-notes">
						<h4><?php esc_html_e( 'Supported configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Fixed security deposit amounts on linked WooCommerce rental products.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Percentage-based and unsupported deposits remain handled normally by WpRently.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Until every step above is ready, WpRently keeps charging deposits as part of the booking total.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Stripe authorization duration must suit the rental period.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
						</ul>
						<p><?php esc_html_e( 'Enable Compatibility turns on SecureHold’s own “Use MagePeople security deposit amounts” setting, which can be switched off again in SecureHold.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
				</div>

				<div class="rbfw-integration-actions">
					<?php
					$primary = true;
					foreach ( $steps as $step ) {
						$this->step_button( $step, $primary );
						$primary = false;
					}
					foreach ( $links as $link ) {
						$this->setup_link( $link['url'], $link['label'], $primary );
						$primary = false;
					}
					if ( $ready ) {
						$this->setup_link( admin_url( 'admin.php?page=securehold-settings&tab=rule-engine-global' ), __( 'Open SecureHold Settings', 'booking-and-rental-manager-for-woocommerce' ), true );
					}
					?>
					<a class="button" href="https://secureholdwp.com/docs/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
					<a class="button" href="https://secureholdwp.com/support/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
					<span class="rbfw-integration-action-status" role="status" aria-live="polite"></span>
				</div>
			</div>
			<?php
		}
	}

	new RBFW_Integrations_Settings();
}
