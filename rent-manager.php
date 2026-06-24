<?php
	/**
	 * Plugin Name: Booking and Rental Manager for Bike | Car | Resort | Appointment | Dress | Equipment
	 * Plugin URI: https://mage-people.com
	 * Description: A complete booking & rental solution for WordPress.
	 * Version: 2.7.2
	 * Author: MagePeople Team
	 * Author URI: https://www.mage-people.com/
	 * Text Domain: booking-and-rental-manager-for-woocommerce
	 * License: GPL v2 or later
	 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
	 * Domain Path: /languages/
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Rent_Manager' ) ) {
		/**
		 * Class RBFW_Rent_Manager
		 *
		 * This class serves as the main entry point for the Rent Manager plugin.
		 *
		 *
		 */
		final class RBFW_Rent_Manager {
			public function __construct() {
				$this->define_contstants();
				$this->include_plugin_files();
				$this->load_rbfw_plugin();
			}

			private function load_rbfw_plugin() {
				require_once RBFW_PLUGIN_DIR . '/functions.php';
				// WooCommerce fallback shims (only take effect when WooCommerce is inactive).
				require_once RBFW_PLUGIN_DIR . '/inc/rbfw_wc_fallbacks.php';
				// These hooks are not WooCommerce-specific (rewrite-rule self-healing, body class,
				// admin links) and must run in both WooCommerce and Standalone modes.
				add_filter( 'plugin_action_links', [ $this, 'plugin_action_link' ], 10, 2 );
				add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
				add_filter( 'post_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
				add_filter( 'body_class', [ $this, 'add_body_class' ] );
				add_action( 'save_post', [ $this, 'flush_rules_on_save_posts' ], 20, 2 );
				add_action( 'admin_init', [ $this, 'get_plugin_data' ] );
				add_action( 'admin_init', [ $this, 'flush_rules_rbfw_post_list_page' ] );
				// Rebuild permalinks automatically (once) – no manual Settings → Permalinks save needed.
				add_action( 'init', [ $this, 'rbfw_auto_flush_rewrite_rules' ], 99 );
				require_once RBFW_PLUGIN_DIR . '/admin/RBFW_Hidden_Product.php';
				require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Woo_Installer.php';
				require_once RBFW_PLUGIN_DIR . '/inc/rbfw_import_demo.php';
				// Create default pages (Rent List, Grid, Search)
				add_action( 'admin_init', 'rbfw_page_create', 20 );
			}

			public function define_contstants() {
				define( 'RBFW_PLUGIN_DIR', dirname( __FILE__ ) );
				define( 'RBFW_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );
				define( 'RBFW_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
			}

			public function add_body_class( $classes ) {
				$post_id        = get_the_ID();
				$template_value = get_post_meta( $post_id, 'rbfw_single_template', true );
				$template       = ! empty( $template_value ) ? $template_value : 'Default';

				return array_merge( $classes, array( 'rbfw_single_' . strtolower( $template ) . '_template' ) );
			}

			public function duplicate_post_link( $actions, $post ) {
				if ( $post->post_type == 'rbfw_item' ) {
					$nonce                     = wp_create_nonce( 'duplicate_post_action' );
					$actions['rbfw_duplicate'] = '<a href="' . esc_url( admin_url() ) . 'edit.php?post_type=rbfw_item&rbfw_duplicate=' . $post->ID . '&nonce=' . $nonce . '" title="" rel="permalink">' . esc_html__( 'Duplicate', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
				}

				return $actions;
			}

			public function plugin_row_meta( $links_array, $plugin_file_name ) {
				if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
					if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce/rent-pro.php' ) ) {
						$rbfw_links = array(
							'docs'    => '<a href="' . esc_url( "https://docs.mage-people.com/rent-and-booking-manager/" ) . '" target="_blank">' . __( 'Docs', 'booking-and-rental-manager-for-woocommerce' ) . '</a>',
							'support' => '<a href="' . esc_url( "https://mage-people.com/my-account" ) . '" target="_blank">' . __( 'Support', 'booking-and-rental-manager-for-woocommerce' ) . '</a>',
						);
					} else {
						$rbfw_links = array(
							'docs'    => '<a href="' . esc_url( "https://docs.mage-people.com/rent-and-booking-manager/" ) . '" target="_blank">' . __( 'Docs', 'booking-and-rental-manager-for-woocommerce' ) . '</a>',
							'support' => '<a href="' . esc_url( "https://mage-people.com/my-account" ) . '" target="_blank">' . __( 'Support', 'booking-and-rental-manager-for-woocommerce' ) . '</a>'
						);
					}
					$links_array = array_merge( $links_array, $rbfw_links );
				}

				return $links_array;
			}

			public function plugin_action_link( $links_array, $plugin_file_name ) {
				if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
					if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php' ) ) {
						array_unshift( $links_array, '<a href="' . esc_url( admin_url() ) . 'edit.php?post_type=rbfw_item&page=rbfw_settings_page">' . __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ) . '</a>' );
						array_unshift( $links_array, '<a href="' . esc_url( "https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/" ) . '" target="_blank" class="rbfw_plugin_pro_meta_link">' . __( 'Get Booking and Rental Manager Pro', 'booking-and-rental-manager-for-woocommerce' ) . '</a>' );
					} else {
						array_unshift( $links_array, '<a href="' . esc_url( admin_url() ) . 'edit.php?post_type=rbfw_item&page=rbfw_settings_page">' . __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ) . '</a>' );
					}
				}

				return $links_array;
			}

			public function include_plugin_files() {
				require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Dependencies.php';
			}

			public function flush_rules_on_save_posts( $post_id ) {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				if ( ! empty( $_POST['post_type'] ) && sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) != 'rbfw_item' ) {
					return;
				}
				flush_rewrite_rules();
			}

			function flush_rules_rbfw_post_list_page() {

				// if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
				// 	return;
				// }

				if ( isset( $_GET['post_type'] ) && sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) == 'rbfw_item' ) {
					flush_rewrite_rules();
				}
			}

			/**
			 * Keep the rental permalinks healthy automatically – no manual
			 * Settings → Permalinks save ever required. Runs on every request but
			 * only actually flushes in two cases:
			 *
			 *   (a) First load after an install/update/deploy, or the rental slug
			 *       changed in the settings (detected via a stored "signature").
			 *
			 *   (b) Self-heal: the /rent/... routes are missing from the stored
			 *       rewrite rules for ANY reason – another plugin flushed them away,
			 *       an old DB backup was restored, a buggy update, etc. This is
			 *       throttled with a 1-hour transient so a persistently broken
			 *       environment can never cause a flush on every single request.
			 *
			 * In the healthy steady state nothing is flushed – it is just two
			 * autoloaded-option reads, so the per-request cost is negligible.
			 */
			public function rbfw_auto_flush_rewrite_rules() {
				global $rbfw;

				if ( is_object( $rbfw ) ) {
					$slug = sanitize_title( $rbfw->get_slug() );
				} else {
					$gen_settings = get_option( 'rbfw_basic_gen_settings' );
					$gen_settings = is_array( $gen_settings ) ? $gen_settings : array();
					$slug         = sanitize_title( ! empty( $gen_settings['rbfw_rent_slug'] ) ? $gen_settings['rbfw_rent_slug'] : 'rent' );
				}
				if ( empty( $slug ) ) {
					$slug = 'rent';
				}

				// (a) Deploy / update / slug change. Bump the 'rbfw1' token if a future
				// change alters the rewrite structure so every site re-flushes once.
				$signature = 'rbfw1|' . $slug;
				if ( get_option( 'rbfw_rewrite_signature' ) !== $signature ) {
					flush_rewrite_rules();
					update_option( 'rbfw_rewrite_signature', $signature );
					delete_transient( 'rbfw_rewrite_selfheal_lock' );

					return;
				}

				// (b) Self-heal broken rules from any source (throttled to once / hour).
				if ( ! $this->rbfw_rewrite_rules_have_item_route()
					&& ! get_transient( 'rbfw_rewrite_selfheal_lock' ) ) {
					set_transient( 'rbfw_rewrite_selfheal_lock', 1, HOUR_IN_SECONDS );
					flush_rewrite_rules();
				}
			}

			/**
			 * Whether the stored rewrite rules still contain the rbfw_item routes.
			 *
			 * @return bool
			 */
			private function rbfw_rewrite_rules_have_item_route() {
				$rules = get_option( 'rewrite_rules' );
				if ( empty( $rules ) || ! is_array( $rules ) ) {
					return false;
				}
				foreach ( $rules as $query ) {
					if ( is_string( $query ) && false !== strpos( $query, 'rbfw_item' ) ) {
						return true;
					}
				}

				return false;
			}

			public function activation_redirect() {
				// RBFW_Woo_Installer handles activation redirect via transient.
				// This method is kept for backward compatibility – no-op now.
			}

			public static function get_plugin_data( $data ) {
				$get_rbfw_plugin_data = get_plugin_data( __FILE__ );
				$rbfw_data            = isset( $get_rbfw_plugin_data[ $data ] ) ? $get_rbfw_plugin_data[ $data ] : '';

				return $rbfw_data;
			}

			public static function activate() {
				set_transient( 'rbfw_plugin_activated', true, 30 );
				flush_rewrite_rules();
				rbfw_update_settings();
			}

			public static function deactivate() {
				flush_rewrite_rules();
			}

			public static function uninstall() {
			}
		}
	}
	if ( class_exists( 'RBFW_Rent_Manager' ) ) {
		register_activation_hook( __FILE__, array( 'RBFW_Rent_Manager', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'RBFW_Rent_Manager', 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( 'RBFW_Rent_Manager', 'uninstall' ) );
		new RBFW_Rent_Manager();
	}
	// Load the full plugin in every mode. WooCommerce-specific integration is wired
	// conditionally inside rbfw_file_include.php (see rbfw_free_woocommerce_integrate()).
	require_once RBFW_PLUGIN_DIR . '/inc/rbfw_file_include.php';


// this include file can't added inside class method due to fatal error. need to fix.
