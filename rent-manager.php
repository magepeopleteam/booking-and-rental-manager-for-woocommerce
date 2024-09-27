<?php
/**
 * Plugin Name: Booking and Rental Manager for Bike | Car | Resort | Appointment | Dress | Equipment - WpRently
 * Plugin URI: https://mage-people.com
 * Description: A complete booking & rental solution for WordPress.
 * Version: 2.1.1
 * Author: MagePeople Team
 * Author URI: https://www.mage-people.com/
 * Text Domain: booking-and-rental-manager-for-woocommerce
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

define( 'RBFW_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'RBFW_TEMPLATE_PATH', plugin_dir_path(__FILE__).'templates/' );
define( 'RBFW_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );

require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Dependencies.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_file_include.php';

/**
 * Initialize the plugin tracker
 *
 * @return void
 */


function appsero_init_tracker_booking_and_rental_manager_for_woocommerce() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
        require_once __DIR__ . '/lib/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'ee4b230e-9589-4bac-a5e0-d61ad547c855', 'Booking and Rental Manager', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_booking_and_rental_manager_for_woocommerce();

// Get Plugin Data
if(!function_exists('rbfw_get_plugin_data')) {
    function rbfw_get_plugin_data($data) {
        $get_rbfw_plugin_data = get_plugin_data( __FILE__ );
        $rbfw_data = $get_rbfw_plugin_data[$data];
        return $rbfw_data;
    }
}

// Added Settings link to plugin action links
add_filter( 'plugin_action_links', 'rbfw_plugin_action_link', 10, 2 );

function rbfw_plugin_action_link( $links_array, $plugin_file_name ){

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

// Added links to plugin row meta
add_filter( 'plugin_row_meta', 'rbfw_plugin_row_meta', 10, 2 );

function rbfw_plugin_row_meta( $links_array, $plugin_file_name ) {

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

/***********************************************************
 * Flush rewrite rules on plugin activation and deactivation.
 ***********************************************************/
register_activation_hook( __FILE__, 'rbfw_register_activation_func' );
register_deactivation_hook( __FILE__, 'rbfw_register_deactivation_func' );


// add_action( 'activated_plugin', 'rbfw_activation_redirect' );
add_action('admin_init', 'rbfw_activation_redirect', 90);
function rbfw_activation_redirect( $plugin ) {
    if(get_option('rbfw_sz_form_submit') === false){



        if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'rbfw_quick_setup'){
            return null;
        }else{
            exit( wp_redirect( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup' ) ) );
        }


        // if( $plugin == plugin_basename( __FILE__ ) ) { }
    }
}


function rbfw_register_activation_func() {
    update_option('rewrite_rules','');
    rbfw_update_settings();
    rbfw_page_create();
    do_action('rbfw_after_register_activation');
}

function rbfw_register_deactivation_func() {
    flush_rewrite_rules();
}

add_action( 'save_post', 'rbfw_flush_rules_on_save_posts', 20, 2);

function rbfw_flush_rules_on_save_posts( $post_id ) {

    if ( ! empty( $_POST['post_type'] ) && $_POST['post_type'] != 'rbfw_item' ) {
        return;
    }

    flush_rewrite_rules();

}
add_filter('post_row_actions', 'rbfw_duplicate_post_link', 10, 2);

function rbfw_duplicate_post_link($actions, $post)
{
    if ($post->post_type=='rbfw_item')
    {
        $actions['rbfw_duplicate'] = '<a href="'.esc_url(admin_url()).'edit.php?post_type=rbfw_item&rbfw_duplicate='.$post->ID.'" title="" rel="permalink">'.esc_html__('Duplicate','booking-and-rental-manager-for-woocommerce').'</a>';
    }
    return $actions;
}


add_filter('body_class', 'rbfw_add_body_class');
/**
 * rbfw_add_body_class (will add a css class in body tag based on template)
 *
 * @author Shahadat <raselsha@gmail.com>
 * @since 1.3.4
 *
 */
function rbfw_add_body_class($classes)
{
    $post_id = get_the_ID();
    $template = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';

    return array_merge( $classes, array( 'rbfw_single_'.strtolower($template).'_template' ) );

}


