<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter('woocommerce_add_cart_item_data', 'rbfw_add_info_to_cart_item', 90, 3);  
function rbfw_add_info_to_cart_item($cart_item_data, $product_id, $variation_id)
{
    global $rbfw;
    $linked_rbfw_id   = get_post_meta($product_id, 'link_rbfw_id', true) ? get_post_meta($product_id, 'link_rbfw_id', true) : $product_id;
    $product_id       = rbfw_check_product_exists($linked_rbfw_id) ? $linked_rbfw_id : $product_id;
    if (get_post_type($product_id) == $rbfw->get_cpt_name()) {    
        $cart_item_data = apply_filters( 'rbfw_add_cart_item', $cart_item_data,$product_id );
    }
    $cart_item_data['rbfw_id'] = $product_id;
    return $cart_item_data;
}

add_action('woocommerce_before_calculate_totals', 'rbfw_set_new_cart_price', 90, 1);
function rbfw_set_new_cart_price($cart_object)
{
    global $rbfw;
  foreach ($cart_object->cart_contents as $key => $value) {
    $rbfw_id = array_key_exists('rbfw_id', $value) ? $value['rbfw_id'] : 0;
      if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {
        do_action('rbfw_set_cart_item_price',$value, $rbfw_id);
      }
  }
}

add_filter('woocommerce_get_item_data', 'rbfw_show_cart_items', 90, 2);
function rbfw_show_cart_items($item_data, $cart_item)
{
  global $rbfw;
  $rbfw_id  = array_key_exists('rbfw_id', $cart_item) ? $cart_item['rbfw_id'] : 0;

  ob_start();

  if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {

    do_action('rbfw_show_cart_item',$cart_item, $rbfw_id);
  }

  $content = ob_get_clean();

  $item_data[] = array(
        'name'     => '',
        'key'     => '',
        'value'   => $content,
        'display' => '',
    );

  return $item_data;
}

add_action('woocommerce_after_checkout_validation', 'rbfw_validation_before_checkout');
function rbfw_validation_before_checkout($posted)
{
  global $woocommerce,$rbfw;
  $items    = $woocommerce->cart->get_cart();
  foreach ($items as $item => $values) {
    $rbfw_id              = array_key_exists('rbfw_id', $values) ? $values['rbfw_id'] : 0;   
    if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {
        do_action('rbfw_validate_cart_item',$values, $rbfw_id);
    }
  }
}

add_action('woocommerce_checkout_create_order_line_item', 'rbfw_add_order_item_data', 90, 4);
function rbfw_add_order_item_data($item, $cart_item_key, $values, $order)
{
global $rbfw;
  $rbfw_id = array_key_exists('rbfw_id', $values) ? $values['rbfw_id'] : 0;
  if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {
    do_action('rbfw_validate_add_order_item',$values,$item, $rbfw_id);
  }
}

function rbfw_wc_price( $post_id, $price, $args = array() ) {
    global $rbfw;
    $display_suffex = get_option( 'woocommerce_price_display_suffix' ) ? get_option( 'woocommerce_price_display_suffix' ) : '';

    return wc_price( $rbfw->get_wc_raw_price( $post_id, $price, $args ) ) . ' ' . $display_suffex;
}

function rbfw_cart_ticket_info($product_id, $rbfw_pickup_start_date, $rbfw_pickup_start_time, $rbfw_pickup_end_date, $rbfw_pickup_end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price, $total_price, $rbfw_service_info, $variation_info, $discount_type = null, $discount_amount = null, $rbfw_regf_info = array()) {
    global $rbfw;
    $rbfw_rent_type 		= get_post_meta( $product_id, 'rbfw_item_type', true );
    $names                  = [ get_the_title( $product_id ) ];
    $qty                    = [ 1 ];
    $count                  = count( $names );
    $ticket_type_arr        = [];
    $start_datetime = date( 'Y-m-d H:i', strtotime( $rbfw_pickup_start_date . ' ' . $rbfw_pickup_start_time ) );
    $end_datetime   = date( 'Y-m-d H:i', strtotime( $rbfw_pickup_end_date . ' ' . $rbfw_pickup_end_time ) );

    if ( sizeof( $names ) > 0 ) {
        for ( $i = 0; $i < $count; $i ++ ) {
            if ( $qty[ $i ] > 0 ) {
                $ticket_type_arr[ $i ]['ticket_name']         = ! empty( $names[ $i ] ) ? strip_tags( $names[ $i ] ) : '';
                $ticket_type_arr[ $i ]['ticket_price']        = $total_price;
                $ticket_type_arr[ $i ]['ticket_qty']          = ! empty( $qty[ $i ] ) ? stripslashes( strip_tags( $qty[ $i ] ) ) : '';
                $ticket_type_arr[ $i ]['rbfw_start_date'] = $rbfw_pickup_start_date;
                $ticket_type_arr[ $i ]['rbfw_start_time'] = $rbfw_pickup_start_time;
                $ticket_type_arr[ $i ]['rbfw_end_date']   = $rbfw_pickup_end_date;
                $ticket_type_arr[ $i ]['rbfw_end_time']   = $rbfw_pickup_end_time;
                $ticket_type_arr[ $i ]['rbfw_start_datetime'] = $start_datetime;
                $ticket_type_arr[ $i ]['rbfw_end_datetime']   = $end_datetime;
                $ticket_type_arr[ $i ]['rbfw_pickup_point']   = $rbfw_pickup_point;
                $ticket_type_arr[ $i ]['rbfw_dropoff_point']  = $rbfw_dropoff_point;
                $ticket_type_arr[ $i ]['rbfw_item_quantity']     = $rbfw_item_quantity;
                $ticket_type_arr[ $i ]['rbfw_rent_type']     = $rbfw_rent_type;
                $ticket_type_arr[ $i ]['rbfw_id'] = stripslashes( strip_tags( $product_id ) );
                $ticket_type_arr[ $i ]['rbfw_service_info']     = $rbfw_service_info;
                $ticket_type_arr[ $i ]['rbfw_variation_info']     = $variation_info;
                $ticket_type_arr[ $i ]['duration_cost']     = $rbfw_duration_price;
                $ticket_type_arr[ $i ]['service_cost']     = $rbfw_service_price;
                $ticket_type_arr[ $i ]['discount_type'] = $discount_type;
                $ticket_type_arr[ $i ]['discount_amount'] = $discount_amount;
                $ticket_type_arr[ $i ]['rbfw_regf_info'] = $rbfw_regf_info;
            }
        }
    }

    return $ticket_type_arr;
    
}

add_action( 'rbfw_wc_order_status_change', 'rbfw_change_user_order_status_on_order_status_change', 10, 3 );
function rbfw_change_user_order_status_on_order_status_change( $order_status, $rbfw_id, $order_id ) {

  // Update meta on rbfw_order_meta post type
  
    $args = array(
        'post_type'      => 'rbfw_order_meta',
        'posts_per_page' => - 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                array(
                    'key'     => 'rbfw_id',
                    'value'   => $rbfw_id,
                    'compare' => '='
                ),
                array(
                    'key'     => 'rbfw_order_id',
                    'value'   => $order_id,
                    'compare' => '='
                )
            )
        )
    );

    $loop = new WP_Query( $args );
    foreach ( $loop->posts as $rbfw_post ) {
        $rbfw_post_id = $rbfw_post->ID;
        update_post_meta( $rbfw_post_id, 'rbfw_order_status', $order_status );
    }

    // Update meta on rbfw_order post type

    $args = array(
        'post_type'      => 'rbfw_order',
        'posts_per_page' => - 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                array(
                    'key'     => 'rbfw_order_id',
                    'value'   => $order_id,
                    'compare' => '='
                )
            )
        )
    );

    $loop = new WP_Query( $args );
    foreach ( $loop->posts as $rbfw_post ) {
        $rbfw_post_id = $rbfw_post->ID;
        update_post_meta( $rbfw_post_id, 'rbfw_order_status', $order_status );
    }
}