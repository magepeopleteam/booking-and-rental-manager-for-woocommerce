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


function want_loco_translate()
{
   return rbfw_get_option('want_loco_translate', 'rbfw_basic_gen_settings');
}

function rbfw_get_label( $rbfw, $key, $default ) {
    if ( $rbfw->get_option_trans( $key, 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' ) {
        return esc_html( $rbfw->get_option_trans( $key, 'rbfw_basic_translation_settings' ) );
    } else {
        return esc_html__( $default, 'booking-and-rental-manager-for-woocommerce' );
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

    $query = new WP_Query( array(
        'name'        => $slug,
        'post_type'   => 'page', // Specify 'page' if you're looking for pages
        'post_status' => 'publish', // Only look for published pages
    ) );

    $result = $query->post_count;

    if(  $result > 0 ) {
        return false;
    } else {
        return true;
    }
}

function rbfw_exist_page_by_title( $title ) {
    $query = new WP_Query( array(
        'title'        => $title,
        'post_type'   => 'page', // Specify 'page' if you're looking for pages
        'post_status' => 'publish', // Only look for published pages
    ) );

    $result = $query->post_count;

    if(  $result > 0 ) {
        return true;
    } else {
        return false;
    }
}

function rbfw_page_create() {
    $pages = [
        'rent-list' => [
            'title' => 'Rent List',
            'content' => "[rent-list style='list']"
        ],
        'rent-grid' => [
            'title' => 'Rent Grid',
            'content' => "[rent-list style='grid']"
        ],
        'search-item-list' => [
            'title' => 'Search Item List',
            'content' => '[rbfw_search_ac] [search-result]'
        ]
    ];

    foreach ($pages as $slug => $page) {
        if (get_page_by_path($slug) === null) {
            $page_id = wp_insert_post([
                'post_title'    => $page['title'],
                'post_content'  => $page['content'],
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => $slug,
                'post_author'   => 1 // Set admin as author
            ]);

            if (!is_wp_error($page_id)) {
                error_log("Page '{$page['title']}' created successfully with ID: $page_id");
            } else {
                error_log("Failed to create page '{$page['title']}': " . $page_id->get_error_message());
            }
        }
    }

    wp_cache_flush(); // Clear cache to avoid stale queries
}


add_action('woocommerce_cart_calculate_fees', 'custom_taxable_fee', 20);
function custom_taxable_fee() {
    $total_deposit_amount = 0;
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item_key => $cart_item) {
        if(!empty($cart_item['rbfw_ticket_info'])){
            foreach ($cart_item['rbfw_ticket_info'] as $item_dep){
                $total_deposit_amount = $total_deposit_amount + $item_dep['security_deposit_amount'];
            }
        }
    }
    if($total_deposit_amount){
        WC()->cart->add_fee(__('Security Deposit', 'booking-and-rental-manager-for-woocommerce'), $total_deposit_amount, false); // 'true' makes it taxable
    }
}

