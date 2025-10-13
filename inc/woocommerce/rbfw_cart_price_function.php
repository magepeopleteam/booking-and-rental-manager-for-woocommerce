<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
add_action( 'rbfw_set_cart_item_price', 'rbfw_set_cart_item_price_func', 10, 2 );
function rbfw_set_cart_item_price_func( $value, $rbfw_id ) {
    $total_price = isset($value['rbfw_tp'])?$value['rbfw_tp']:0;
    $value['data']->set_price( $total_price );
    $value['data']->set_regular_price( $total_price );
    $value['data']->set_sale_price( $total_price );
    $value['data']->set_sold_individually( 'yes' );
    $value['data']->get_price();
}