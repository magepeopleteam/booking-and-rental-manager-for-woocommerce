<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
add_action( 'rbfw_set_cart_item_price', 'rbfw_set_cart_item_price_func', 10, 2 );
function rbfw_set_cart_item_price_func( $value, $rbfw_id ) {
    global $rbfw;
    $total_price = isset($value['rbfw_tp'])?$value['rbfw_tp']:0;
    $allow_duplicate = ( is_object( $rbfw ) && method_exists( $rbfw, 'get_option_trans' ) ) ? $rbfw->get_option_trans( 'rbfw_allow_duplicate_rental_cart_item', 'rbfw_basic_gen_settings', 'yes' ) : 'yes';
    $value['data']->set_price( $total_price );
    $value['data']->set_regular_price( $total_price );
    $value['data']->set_sale_price( $total_price );
    // fixed by shahnur: control sold individually by global setting.
    $value['data']->set_sold_individually( $allow_duplicate === 'yes' ? 'no' : 'yes' );
    $value['data']->get_price();
}
