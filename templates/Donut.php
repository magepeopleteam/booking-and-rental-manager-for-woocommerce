<?php
/**
 * @author Shahadat Hossain <raselsha@gmail.com>
 * @since 2.0.4
 * @version 1.0.0
 * 
 * This template can be overridden by copying it to yourtheme/templates/.
 */


if ( ! defined( 'ABSPATH' ) ) die;

global $post_id;

$rent_type = get_post_meta($post_id, 'rbfw_item_type', true);

switch($rent_type){
    case 'bike_car_sd':
    case 'appointment':
        $file_name = 'single-day.php';
    break;
    case 'bike_car_md':
    case 'equipment':
    case 'dress':
    case 'others':
        $file_name = 'multi-day.php';
    break;
    case 'resort':
        $file_name = 'resort.php';
    break;
    default:
        $file_name = 'multi-day.php';
}

$template_path = __DIR__.'/single-view/donut-template/'.$file_name;	
if ( file_exists( $template_path ) ) {
    include($template_path);
} else {
    echo __( 'Sorry, No Template Found!', 'booking-and-rental-manager-for-woocommerce' );
}
