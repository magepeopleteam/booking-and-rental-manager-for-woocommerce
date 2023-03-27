<?php

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

global $post_id;

$rent_type = get_post_meta($post_id, 'rbfw_item_type', true);

if($rent_type == 'bike_car_sd'){

    $file_name = 'bike-car-sd.php';
}
elseif($rent_type == 'bike_car_md'){

    $file_name = 'bike.php';
}
elseif($rent_type == 'equipment'){

    $file_name = 'bike.php';
}
elseif($rent_type == 'dress'){

    $file_name = 'bike.php';
}
elseif($rent_type == 'resort'){

    $file_name = 'resort.php';
}
elseif($rent_type == 'appointment'){

    $file_name = 'bike-car-sd.php';
}
elseif($rent_type == 'others'){

    $file_name = 'bike.php';
}												
else{

    $file_name = 'bike.php';
}

$file_path = RBFW_Function::template_path('contents/muffin-templates/'.$file_name);

if ( file_exists($file_path) ) {

    include($file_path);

} else {
    
    echo __( 'Sorry, No Template Found!', 'booking-and-rental-manager-for-woocommerce' );
}