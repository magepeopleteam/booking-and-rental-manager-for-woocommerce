<?php
/**
 * Plugin Name: Booking and Rental Manager for Bike | Car | Resort | Appointment | Dress | Equipment - WpRently
 * Plugin URI: https://mage-people.com
 * Description: A complete booking & rental solution for WordPress.
 * Version: 2.1.4
 * Author: MagePeople Team
 * Author URI: https://www.mage-people.com/
 * Text Domain: booking-and-rental-manager-for-woocommerce
 * Domain Path: /languages/
 */

if( ! defined('ABSPATH') )die;

if(! class_exists('RBFW_Rent_Manager')){
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

    final class RBFW_Rent_Manager{
        public function __construct() {
            $this->define_contstants();
            $this->include_plugin_files(); 
            add_action('init', [$this, 'init_tracker']);
            add_filter( 'plugin_action_links', [$this,'plugin_action_link'],10, 2);
            add_filter( 'plugin_row_meta', [$this,'plugin_row_meta'], 10, 2 );
            add_filter('post_row_actions', [$this,'duplicate_post_link'], 10, 2);
            add_filter('body_class', [$this,'add_body_class']);
            add_action( 'save_post', [$this,'flush_rules_on_save_posts'], 20, 2);
            add_action('admin_init', [$this,'activation_redirect'], 90);
            add_action('admin_init', [$this,'get_plugin_data']);
        }

        public function define_contstants(){
            define( 'RBFW_PLUGIN_DIR', dirname( __FILE__ ) );
            define( 'RBFW_TEMPLATE_PATH', plugin_dir_path(__FILE__).'templates/' );
            define( 'RBFW_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
        }

        public function init_tracker() {
            if ( ! class_exists( 'Appsero\Client' ) ) {
                require_once __DIR__ . '/lib/appsero/src/Client.php';
            }
            $client = new Appsero\Client( 'ee4b230e-9589-4bac-a5e0-d61ad547c855', 'Booking and Rental Manager', __FILE__ );
            $client->insights()->init();
        }
        


        public function add_body_class($classes)
        {
            $post_id = get_the_ID();
            $template = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
            return array_merge( $classes, array( 'rbfw_single_'.strtolower($template).'_template' ) );
        }

        public function duplicate_post_link($actions, $post)
        {
            if ($post->post_type=='rbfw_item')
            {
                $actions['rbfw_duplicate'] = '<a href="'.esc_url(admin_url()).'edit.php?post_type=rbfw_item&rbfw_duplicate='.$post->ID.'" title="" rel="permalink">'.esc_html__('Duplicate','booking-and-rental-manager-for-woocommerce').'</a>';
            }
            return $actions;
        }
        
        public function plugin_row_meta( $links_array, $plugin_file_name ) {

            if( strpos( $plugin_file_name, basename(__FILE__) ) ) {

                if(!is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php')){
                    $rbfw_links = array(
                        'docs' => '<a href="'.esc_url("https://docs.mage-people.com/rent-and-booking-manager/").'" target="_blank">'.__('Docs','booking-and-rental-manager-for-woocommerce').'</a>',
                        'support' => '<a href="'.esc_url("https://mage-people.com/my-account").'" target="_blank">'.__('Support','booking-and-rental-manager-for-woocommerce').'</a>',
                    );
                }else{
                    $rbfw_links = array(
                        'docs' => '<a href="'.esc_url("https://docs.mage-people.com/rent-and-booking-manager/").'" target="_blank">'.__('Docs','booking-and-rental-manager-for-woocommerce').'</a>',
                        'support' => '<a href="'.esc_url("https://mage-people.com/my-account").'" target="_blank">'.__('Support','booking-and-rental-manager-for-woocommerce').'</a>'
                    );
                }
                $links_array = array_merge( $links_array, $rbfw_links );
            }

            return $links_array;
        }

        public function plugin_action_link($links_array, $plugin_file_name ) {
            if( strpos( $plugin_file_name, basename(__FILE__) ) ) {

                if(!is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php')){
        
                    array_unshift( $links_array, '<a href="'.esc_url(admin_url()).'edit.php?post_type=rbfw_item&page=rbfw_settings_page">'.__('Settings','booking-and-rental-manager-for-woocommerce').'</a>');
        
                    array_unshift( $links_array, '<a href="'.esc_url("https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/").'" target="_blank" class="rbfw_plugin_pro_meta_link">'.__('Get Booking and Rental Manager Pro','booking-and-rental-manager-for-woocommerce').'</a>');
        
                }else{
                    array_unshift( $links_array, '<a href="'.esc_url(admin_url()).'edit.php?post_type=rbfw_item&page=rbfw_settings_page">'.__('Settings','booking-and-rental-manager-for-woocommerce').'</a>');
                }
            }
        
            return $links_array;
        }

        public function include_plugin_files() {
            require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Dependencies.php';
        }

        public function flush_rules_on_save_posts( $post_id ) {
            if ( ! empty( $_POST['post_type'] ) && $_POST['post_type'] != 'rbfw_item' ) {
                return;
            }
            flush_rewrite_rules();
        }

        public function activation_redirect( $plugin ) {
            if(get_option('rbfw_sz_form_submit') === false){
                if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'rbfw_quick_setup'){
                    return null;
                }else{
                    exit( wp_redirect( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup' ) ) );
                }
            }
        }

        public static function get_plugin_data($data) {
            $get_rbfw_plugin_data = get_plugin_data( __FILE__ );
            $rbfw_data = isset($get_rbfw_plugin_data[$data])?$get_rbfw_plugin_data[$data]:'';
            return $rbfw_data;
        }

        public static function activate(){
            update_option('rewrite_rules','');
            rbfw_update_settings();
            rbfw_page_create();
            do_action('rbfw_after_register_activation');
        }

        public static function deactivate(){
            flush_rewrite_rules();
        }

        public static function uninstall(){

        }
    }
}

if(class_exists('RBFW_Rent_Manager')){
    register_activation_hook( __FILE__, array( 'RBFW_Rent_Manager','activate' ) );
    register_deactivation_hook( __FILE__, array( 'RBFW_Rent_Manager','deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'RBFW_Rent_Manager','uninstall' ) );
    new RBFW_Rent_Manager();
}
// this include file can't added inside class method due to fatal error. need to fix.
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_file_include.php';