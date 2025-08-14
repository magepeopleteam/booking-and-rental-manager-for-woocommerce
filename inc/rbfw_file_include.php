<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Function.php';
require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Frontend.php';
require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Super_Slider.php';
require_once RBFW_PLUGIN_DIR . '/inc/RBFW_Style.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class.settings-api.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-admin-menu.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-form-fields-generator.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-form-fields-wrapper.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-meta-box.php';
require_once RBFW_PLUGIN_DIR . '/admin/admin.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-icon-library.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_functions.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_inventory_functions.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_dynamic_css.php';
require_once RBFW_PLUGIN_DIR . '/inc/class-resort-function.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_shortcodes.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-pro-page.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-welcome-page.php';
require_once RBFW_PLUGIN_DIR . '/inc/class-bike-car-sd-function.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_currency.php';


require_once RBFW_PLUGIN_DIR . '/inc/rbfw_order_meta.php';
require_once RBFW_PLUGIN_DIR . '/inc/class-bike-car-md-function.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-thankyou-page.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-search-page.php';

require_once RBFW_PLUGIN_DIR . '/lib/classes/class-rating-notice.php';

require_once RBFW_PLUGIN_DIR . '/lib/classes/class-time-slots-page.php';
require_once RBFW_PLUGIN_DIR . '/support/elementor/elementor-support.php';

require_once RBFW_PLUGIN_DIR . '/support/blocks/block-support.php';
//require_once RBFW_PLUGIN_DIR . '/lib/classes/class-quick-setup.php';



add_action('init', 'rbfw_category_update');
function rbfw_category_update(){
    $rbfw_category_update = get_option('rbfw_enable_time_picker_option');

    if($rbfw_category_update != 'yes'){

        $args = array(
            'post_type' => 'rbfw_item',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);

        if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();

         $enable_specific_duration =  get_post_meta(get_the_ID(), 'enable_specific_duration', true) ? get_post_meta(get_the_ID(), 'enable_specific_duration', true) : 'off';
            $rbfw_time_slot_switch = !empty(get_post_meta(get_the_ID(),'rbfw_time_slot_switch',true)) ? get_post_meta(get_the_ID(),'rbfw_time_slot_switch',true) : 'off';
            $available_times = get_post_meta(get_the_ID(), 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta(get_the_ID(), 'rdfw_available_time', true)) : [];
            $enable_hourly_rate = get_post_meta(get_the_ID(), 'rbfw_enable_hourly_rate', true) ? get_post_meta(get_the_ID(), 'rbfw_enable_hourly_rate', true) : 'no';

            if($rbfw_time_slot_switch == 'on' && !empty($available_times) &&  $enable_specific_duration =='off' ){
                update_post_meta(get_the_ID(),'rbfw_enable_time_picker','yes');
            }

            if($rbfw_time_slot_switch == 'on' && !empty($availabe_time) && $enable_hourly_rate == 'yes' ){
                update_post_meta(get_the_ID(),'rbfw_enable_time_picker','yes');
            }

            endwhile;
        endif;
        update_option( 'rbfw_enable_time_picker_option', 'yes' );
    }
}

/*************************************************
* if Woocommerce Payment System is Enabled
**************************************************/
add_action('wp_loaded', 'rbfw_free_woocommerce_integrate');

function rbfw_free_woocommerce_integrate(){

    require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/rbfw_wc_notice.php");
    require_once(RBFW_PLUGIN_DIR . "/Frontend/RBFW_Woocommerse.php");
    require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/class-status.php");
    require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/class-meta.php");
    require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/rbfw_cart_price_function.php");
    require_once(RBFW_PLUGIN_DIR . "/inc/rbfw_woocommerce_products.php");

}


add_filter('rbfw_payment_systems','rbfw_payment_systems_free', 9);

function rbfw_payment_systems_free(){

    $ps = array(
        'wps' => 'WC Payment System',
    );

    return $ps;
}


