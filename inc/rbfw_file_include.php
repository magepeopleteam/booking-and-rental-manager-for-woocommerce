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

require_once RBFW_PLUGIN_DIR . '/lib/classes/class-order-page.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_order_meta.php';
require_once RBFW_PLUGIN_DIR . '/inc/class-bike-car-md-function.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-thankyou-page.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-account-page.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-search-page.php';
require_once RBFW_PLUGIN_DIR . '/inc/rbfw_import_demo.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-rating-notice.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-inventory-page.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-time-slots-page.php';
require_once RBFW_PLUGIN_DIR . '/support/elementor/elementor-support.php';
require_once RBFW_PLUGIN_DIR . '/lib/classes/class-quick-setup.php';

add_action('wp_head', 'rbfw_php_variable_to_js');

function rbfw_php_variable_to_js()
{
    ?>
    <script>

        let wp_date_format = "<?php echo  get_option('date_format') ?>";
        let wp_time_format = "<?php echo  get_option('time_format') ?>";

        let js_date_format = 'yy-mm-dd';

        if(wp_date_format=='F j, Y'){
            js_date_format = 'dd M yy';
        }else if(wp_date_format=='m/d/Y'){
            js_date_format = 'mm/dd/yy';
        }else if(wp_date_format=='d/m/Y'){
            js_date_format = 'dd/mm/yy';
        }else{
            js_date_format = 'yy-mm-dd';
        }

    </script>

    <?php
}


add_action('init', 'rbfw_category_update');
function rbfw_category_update(){
    $rbfw_category_update = get_option('rbfw_category_update');

    if($rbfw_category_update != 'yes'){

        $args = array(
            'post_type' => 'rbfw_item',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);

        if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();
            $rbfw_category_name = get_post_meta(get_the_ID(),'rbfw_category_name',true);
            $category_name=isset(get_term($rbfw_category_name)->name) ? get_term($rbfw_category_name)->name : '';
            $rbfw_categories = array(0=>$category_name);
            update_post_meta(get_the_ID(),'rbfw_categories',$rbfw_categories);
        endwhile;
        endif;
        update_option( 'rbfw_category_update', 'yes' );
    }
}

/*************************************************
* if Woocommerce Payment System is Enabled
**************************************************/
add_action('wp_loaded', 'rbfw_free_woocommerce_integrate');




function rbfw_free_woocommerce_integrate(){

    $rbfw_payment_system = get_option("rbfw_basic_payment_settings");

    if(!empty($rbfw_payment_system)){
        $rbfw_payment_system = $rbfw_payment_system['rbfw_payment_system'];
        $wc_folder_exist = rbfw_free_chk_plugin_folder_exist('booking-and-rental-manager-for-woocommerce-pro/inc/woocommerce');
        if(rbfw_check_pro_active() === true && $wc_folder_exist === true){
            // do nothing
        }else {
            if($rbfw_payment_system == 'wps'){
                require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/rbfw_wc_notice.php");
                require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/rbfw_functions.php");
                require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/class-status.php");
                require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/class-meta.php");
                require_once(RBFW_PLUGIN_DIR . "/inc/woocommerce/rbfw_cart_price_function.php");
            }
        }
    }

    add_filter('rbfw_payment_systems','rbfw_payment_systems_free', 9);

    function rbfw_payment_systems_free(){

        $ps = array(
            'wps' => 'WC Payment System',
        );

        return $ps;
    }
}


