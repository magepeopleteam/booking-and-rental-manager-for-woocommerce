<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

/******************************
 * Rent List Shortcode
 ******************************/
add_shortcode('rent-list', 'rbfw_rent_list_shortcode_func');
function rbfw_rent_list_shortcode_func($atts) {
    $attributes = shortcode_atts( array(
        'style' => 'grid',
        'show'  => -1,
        'order' => 'DESC',
        'type'  => ''
        ), $atts );

    $style  = $attributes['style'];      
    $show   = $attributes['show'];
    $order  = $attributes['order'];
    $type   = $attributes['type'];

    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => $show,
        'post_status' => 'publish',
        'orderby'=> 'post_date', 
        'order' => $order
    );

    if(!empty($type)):
        $meta_query = array(
            'meta_query' => array(
                'meta_value' => array(
                        'key' => 'rbfw_item_type',
                        'value' => $type,
                        'compare' => '==', 
                )
            )
        );
    $args = array_merge($args,$meta_query);    
    endif;
    
    $query = new WP_Query($args);

    ob_start();
    ?>
    <div class="rbfw_rent_list_wrapper rbfw_rent_list_style_<?php echo esc_attr($style); ?>">
    <?php
    $d = 1;    
    if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();

    if($style == 'grid'):

        include( RBFW_Function::template_path( 'rent_list_styles/grid.php' ) ); 

    elseif($style == 'list'):

        include( RBFW_Function::template_path( 'rent_list_styles/list.php' ) );

    else:

        include( RBFW_Function::template_path( 'rent_list_styles/grid.php' ) );

    endif;

    $d++;
    endwhile; endif;
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