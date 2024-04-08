<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

/******************************
 * Rent List Shortcode
 ******************************/
add_shortcode('rent-list', 'rbfw_rent_list_shortcode_func');
function rbfw_rent_list_shortcode_func($atts = null) {


    $attributes = shortcode_atts( array(
        'style' => 'grid',
        'show'  => -1,
        'order' => 'DESC',
        'type'  => '',
        'location' => '',
        'category' => '',
        'columns' => '',
    ), $atts );

    $style  = $attributes['style'];
    $show   = $attributes['show'];
    $order  = $attributes['order'];
    $type   = $attributes['type'];
    $location   = $attributes['location'];
    $category   = $attributes['category'];
    $columns   = $attributes['columns'];

    $rent_type = !empty($type) ? array(
        'key' => 'rbfw_item_type',
        'value' => $type,
        'compare' => '==',
    ) : '';
    $location_query = !empty($location) ? array(
        'key' => 'rbfw_pickup_data',
        'value' => $location,
        'compare' => 'LIKE'
    ) : '';


    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            $rent_type,
            $location_query
        )
    );

    if(!empty($category)):
        $category = explode(',', $attributes['category']);
        foreach ($category as $cat){
            $category_name=isset(get_term($cat)->name) ? get_term($cat)->name : '';
            $args['meta_query'][] = array(
                'key' => 'rbfw_categories',
                'value' => $category_name,
                'compare' => 'LIKE'
            );
        }
    endif;

    $query = new WP_Query($args);

    ob_start();
//echo '<pre>';print_r($query);echo '</pre>';
    $grid_class = 'rbfw-w-33';

    if($columns){
        $grid_class = ($columns==1 || $columns==2)?'rbfw-w-50':(($columns==3)?'rbfw-w-33':(($columns==4)?'rbfw-w-25':(($columns==5)?'rbfw-w-20':'rbfw-w-20')));
    }

    ?>
    <div class="rbfw_rent_list_wrapper <?php echo $grid_class ?> rbfw_rent_list_style_<?php echo esc_attr($style); ?>">
        <?php
        $d = 1;
        if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();
            $the_content = get_the_content();

            $rbfw_id = get_the_id();

            $expire = 'no';
            $rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';

            if($rbfw_enable_start_end_date=='no'){
                $rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
                $rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
                $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                $rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
                $rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));
                if($rbfw_event_last_date<$rbfw_todays_date){
                    $expire = 'yes';
                }
            }

            if($expire == 'no'){
                if($style == 'grid'):
                    include( RBFW_Function::template_path( 'rent_list_styles/grid.php' ) );
                elseif($style == 'list'):
                    include( RBFW_Function::template_path( 'rent_list_styles/list.php' ) );
                else:
                    include( RBFW_Function::template_path( 'rent_list_styles/grid.php' ) );
                endif;
            }



            $d++;
        endwhile;

        else:

            ?>
            <div class="rbfw-lsn-new-message-box">
                <div class="rbfw-lsn-new-message-box-info">
                    <div class="rbfw-lsn-info-tab rbfw-lsn-tip-icon-info" title="error"><i></i></div>
                    <div class="rbfw-lsn-tip-box-info">
                        <p><?php rbfw_string('rbfw_text_nodatafound',__('Sorry, no data found!','booking-and-rental-manager-for-woocommerce')); ?></p>
                    </div>
                </div>
            </div>
        <?php
        endif;

        wp_reset_query();
        ?>
    </div>
    <?php
    $content = ob_get_clean();
    return $content;
}

/******************************
 * Single Add to Cart Shortcode
 ******************************/
add_shortcode('rent-add-to-cart', 'rbfw_add_to_cart_shortcode_func');

function rbfw_add_to_cart_shortcode_func($atts){

    $attributes = shortcode_atts( array(
        'id' => ''
    ), $atts );

    $post_id = $attributes['id'];

    if(empty($post_id)){

        return;
    }

    $rbfw_item_type = get_post_meta($post_id, 'rbfw_item_type', true);

    ob_start();

    do_action( 'woocommerce_before_single_product' );

    if($rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){

        include( RBFW_Function::template_path( 'forms/bike-car-sd-registration.php' ) );
        $BikeCarSdclass = new RBFW_BikeCarSd_Function();
        $BikeCarSdclass->rbfw_bike_car_sd_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'bike_car_md' || $rbfw_item_type == 'equipment' || $rbfw_item_type == 'dress' || $rbfw_item_type == 'others'){

        include( RBFW_Function::template_path( 'forms/bike-registration.php' ) );
        $BikeCarMdclass = new RBFW_BikeCarMd_Function();
        $BikeCarMdclass->rbfw_bike_car_md_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'resort'){

        include( RBFW_Function::template_path( 'forms/resort-registration.php' ) );
        $Resortclass = new RBFW_Resort_Function();
        $Resortclass->rbfw_resort_frontend_scripts($post_id);
    }

    $content = ob_get_clean();

    return $content;
}

/******************************
 * Rent Filter Form Shortcode
 ******************************/
add_shortcode('rbfw-search', 'rbfw_rent_search_shortcode_func');
function rbfw_rent_search_shortcode_func() {

    $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');
    $search_page_link = get_page_link($search_page_id);
    $location_arr = rbfw_get_location_arr();
    $location = !empty($_GET['rbfw_search_location']) ? strip_tags($_GET['rbfw_search_location']) : '';
    ?>
    <div class="rbfw_search_form_wrap">
        <form class="rbfw_search_form" action="<?php echo esc_url($search_page_link); ?>" method="GET">
            <div class="rbfw_search_form_col">
                <label><?php rbfw_string('rbfw_text_pickup_location',__('Pickup Location','booking-and-rental-manager-for-woocommerce')); ?></label>
                <select name="rbfw_search_location">
                    <?php foreach ( $location_arr as $key => $value ) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php if($location == $key){ echo 'selected'; }?>><?php echo esc_html($value); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="rbfw_search_form_col">
                <label></label>
                <button type="submit" name="rbfw_search_submit" class="rbfw_search_submit"><?php rbfw_string('rbfw_text_search',__('Search','booking-and-rental-manager-for-woocommerce')); ?></button>
            </div>
        </form>
    </div>
    <?php
}