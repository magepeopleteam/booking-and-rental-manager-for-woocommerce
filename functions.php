<?php
function rbfw_woo_install_check() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        return 'Yes';
    } elseif ( is_dir( $plugin_dir ) ) {
        return 'Installed But Not Active';
    } else {
        return 'No';
    }
}

function rbfw_update_settings(){
    $payment_settings = maybe_unserialize('a:6:{s:19:"rbfw_payment_system";s:3:"wps";s:17:"rbfw_mps_currency";s:3:"USD";s:26:"rbfw_mps_currency_position";s:4:"left";s:32:"rbfw_mps_currency_decimal_number";s:1:"2";s:25:"rbfw_mps_checkout_account";s:2:"on";s:24:"rbfw_mps_payment_gateway";a:1:{s:7:"offline";s:7:"offline";}}');

    if (get_option('rbfw_basic_payment_settings') === false) {

        update_option('rbfw_basic_payment_settings', $payment_settings);

    }

    $pdf_settings = maybe_unserialize('a:9:{s:13:"rbfw_send_pdf";s:3:"yes";s:13:"rbfw_pdf_logo";s:0:"";s:11:"rbfw_pdf_bg";s:0:"";s:16:"rbfw_pdf_address";s:0:"";s:14:"rbfw_pdf_phone";s:0:"";s:17:"rbfw_pdf_tc_title";s:0:"";s:16:"rbfw_pdf_tc_text";s:0:"";s:17:"rbfw_pdf_bg_color";s:0:"";s:19:"rbfw_pdf_text_color";s:0:"";}');

    if (get_option('rbfw_basic_pdf_settings') === false) {

        update_option('rbfw_basic_pdf_settings', $pdf_settings);

    }
}

function rbfw_exist_page_by_slug( $slug ) {
    global $wpdb;
    $posts_table = $wpdb->prefix.'posts';
    $return = $wpdb->get_row( "SELECT ID FROM $posts_table WHERE post_name = '" . $slug . "' && post_status = 'publish' && post_type = 'page' ", 'ARRAY_N' );

    if( empty( $return ) ) {
        return false;
    } else {
        return true;
    }
}

function rbfw_exist_page_by_title( $title ) {
    global $wpdb;
    $posts_table = $wpdb->prefix.'posts';
    $return = $wpdb->get_row( "SELECT ID FROM $posts_table WHERE post_title = '" . $title . "' && post_status = 'publish' && post_type = 'page' ", 'ARRAY_N' );

    if( empty( $return ) ) {
        return false;
    } else {
        return true;
    }
}


function rbfw_page_create()
{
    $page_obj = rbfw_exist_page_by_slug('rent-list');


    if($page_obj === false){
        $args = array(
            'post_title'    => 'Rent List',
            'post_content'  => "[rent-list style='list']",
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );
        wp_insert_post( $args );
    }

    $page_obj = rbfw_exist_page_by_slug('rent-grid');

    if($page_obj === false){
        $args = array(
            'post_title'    => 'Rent Grid',
            'post_content'  => "[rent-list style='grid']",
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );
        wp_insert_post( $args );
    }


    $page_slug = 'search-item-list';
    // Check if the page already exists
    $existing_page = get_page_by_path($page_slug);
    if (!$existing_page) {
        // Page doesn't exist, so create it
        $page_data = array(
            'post_title'    => 'Search Item List',
            'post_content'  => '[rbfw_search] [search-result]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => $page_slug,
        );

        // Insert the page into the database
        wp_insert_post($page_data);
    }


}