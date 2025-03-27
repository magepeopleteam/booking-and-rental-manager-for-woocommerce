<?php
	/**
	 * Plugin Name: Booking and Rental Manager for Bike | Car | Resort | Appointment | Dress | Equipment
	 * Plugin URI: https://mage-people.com
	 * Description: A complete booking & rental solution for WordPress.
	 * Version: 2.3.6
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
		 * @author Sahahdat <raselsha@gmail.com>
		 * @version 1.0.0
		 * @since 2.1.1
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
				if ( rbfw_woo_install_check() == 'Yes' ) {
					add_filter( 'plugin_action_links', [ $this, 'plugin_action_link' ], 10, 2 );
					add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
					add_filter( 'post_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
					add_filter( 'body_class', [ $this, 'add_body_class' ] );
					add_action( 'save_post', [ $this, 'flush_rules_on_save_posts' ], 20, 2 );
					add_action( 'admin_init', [ $this, 'get_plugin_data' ] );
					add_action( 'admin_init', [ $this, 'flush_rules_rbfw_post_list_page' ] );
				}
				require_once RBFW_PLUGIN_DIR . '/admin/RBFW_Hidden_Product.php';
				require_once RBFW_PLUGIN_DIR . '/admin/RBFW_Quick_Setup.php';
				require_once RBFW_PLUGIN_DIR . '/inc/rbfw_import_demo.php';
				add_action( 'admin_init', [ $this, 'activation_redirect' ], 90 );
			}

			public function define_contstants() {
				define( 'RBFW_PLUGIN_DIR', dirname( __FILE__ ) );
				define( 'RBFW_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );
				define( 'RBFW_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
			}

			public function add_body_class( $classes ) {
				$post_id  = get_the_ID();
				$template = ! empty( get_post_meta( $post_id, 'rbfw_single_template', true ) ) ? get_post_meta( $post_id, 'rbfw_single_template', true ) : 'Default';

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
					if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce/rent-pro.php' ) ) {
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

			public function activation_redirect( $plugin ) {

				$rbfw_quick_setup_done = get_option( 'rbfw_quick_setup_done' ) ? get_option( 'rbfw_quick_setup_done' ) : 'no';
				$first_redirect = get_option( 'first_redirect' ) ? get_option( 'first_redirect' ) : 'no';

				if ( $rbfw_quick_setup_done == 'no' && $first_redirect == 'no' ) {						
						wp_redirect( esc_url_raw( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup' ) ) );
						update_option( 'first_redirect', 'yes' );
						exit();
				}
			}

			public static function get_plugin_data( $data ) {
				$get_rbfw_plugin_data = get_plugin_data( __FILE__ );
				$rbfw_data            = isset( $get_rbfw_plugin_data[ $data ] ) ? $get_rbfw_plugin_data[ $data ] : '';

				return $rbfw_data;
			}

			public static function activate() {
				// rbfw_activation_redirect();
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
	if ( rbfw_woo_install_check() == 'Yes' ) {
		require_once RBFW_PLUGIN_DIR . '/inc/rbfw_file_include.php';
	}


	// this include file can't added inside class method due to fatal error. need to fix.
