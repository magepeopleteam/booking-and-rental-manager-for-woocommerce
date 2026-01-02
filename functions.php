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

/**
 * Detect dangerous serialized payloads (objects/custom classes).
 *
 * @param string $value Serialized string.
 *
 * @return bool
 */
function rbfw_feature_category_has_disallowed_types( $value ) {
    return (bool) preg_match( '/(^|;)(O|C)\:\d+\:\"/i', $value );
}

/**
 * Recursively sanitize feature category arrays without depending on plugin classes.
 *
 * @param array $value
 *
 * @return array
 */
function rbfw_sanitize_feature_category_array( $value ) {
    if ( class_exists( 'RBFW_Function' ) ) {
        return RBFW_Function::data_sanitize( $value );
    }

    array_walk_recursive(
        $value,
        static function ( &$item ) {
            if ( is_scalar( $item ) ) {
                $item = sanitize_text_field( $item );
            } else {
                $item = '';
            }
        }
    );

    return $value;
}

/**
 * Convert raw meta into a safe, sanitized feature category array.
 *
 * @param mixed $value
 *
 * @return array
 */
function rbfw_prepare_feature_category_meta_value( $value ) {
    if ( empty( $value ) ) {
        return array();
    }

    if ( is_string( $value ) ) {
        $value = wp_unslash( $value );

        if ( ! is_serialized( $value ) ) {
            return array();
        }

        if ( rbfw_feature_category_has_disallowed_types( $value ) ) {
            return array();
        }

        if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
            $value = @unserialize( $value, array( 'allowed_classes' => false ) );
        } else {
            $value = @unserialize( $value );
            if ( is_object( $value ) ) {
                return array();
            }
        }
    } elseif ( is_array( $value ) ) {
        $value = wp_unslash( $value );
    } else {
        return array();
    }

    if ( ! is_array( $value ) ) {
        return array();
    }

    return rbfw_sanitize_feature_category_array( $value );
}

/**
 * Helper to fetch the sanitized feature category meta.
 *
 * @param int $post_id
 *
 * @return array
 */
function rbfw_get_feature_category_meta( $post_id ) {
    $raw_value = get_post_meta( $post_id, 'rbfw_feature_category', true );

    return rbfw_prepare_feature_category_meta_value( $raw_value );
}

/**
 * Sanitize feature category meta on save regardless of the source.
 *
 * @param mixed  $meta_value
 * @param string $meta_key
 * @param string $meta_type
 *
 * @return array
 */
function rbfw_filter_feature_category_meta( $meta_value, $meta_key, $meta_type ) {
    return rbfw_prepare_feature_category_meta_value( $meta_value );
}
add_filter( 'sanitize_post_meta_rbfw_feature_category', 'rbfw_filter_feature_category_meta', 10, 3 );


function want_loco_translate()
{
   return rbfw_get_option('want_loco_translate', 'rbfw_basic_gen_settings');
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


add_action( 'rbfw_ticket_feature_info', 'rbfw_ticket_feature_info' );
function rbfw_ticket_feature_info(){

    $rbfw_real_time_availability_display = rbfw_get_option('rbfw_real_time_availability_display', 'rbfw_basic_gen_settings');

    if($rbfw_real_time_availability_display=='yes'){

    ?>
    <div class="rbfw-bikecarsd-calendar-header">
        <div class="rbfw-bikecarsd-calendar-header-feature">
            <i class="fas fa-clock"></i>
            <?php esc_html_e('Real-time availability','booking-and-rental-manager-for-woocommerce'); ?>
        </div>
        <div class="rbfw-bikecarsd-calendar-header-feature">
            <i class="fas fa-bolt"></i>
            <?php esc_html_e('Instant confirmation','booking-and-rental-manager-for-woocommerce'); ?>
        </div>
    </div>
    <?php
    }
}


