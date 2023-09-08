<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function rbfw_prepar_and_add_user_data($ticket_info, $user_info, $rbfw_id, $order_id, $service_info = array(), $rbfw_duration_cost = null, $rbfw_service_cost = null, $type_info = array(), $rbfw_regf_info = array()) {
    global $rbfw;
    $rbfw_rent_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );
         
    $order          = wc_get_order( $order_id );
    $order_meta     = get_post_meta( $order_id );
    $order_status   = $order->get_status();
    $payment_method = isset( $order_meta['_payment_method_title'][0] ) ? $order_meta['_payment_method_title'][0] : '';
    $user_id        = isset( $order_meta['_customer_user'][0] ) ? $order_meta['_customer_user'][0] : '';


    foreach ( $ticket_info as $_ticket ) {
        $qty = 1;
        for ( $key = 0; $key < $qty; $key ++ ) {

                $zdata[ $key ]['rbfw_ticket_total_price'] = ((float)$_ticket['ticket_price'] * (int)$_ticket['ticket_qty']);
                $zdata[ $key ]['rbfw_ticket_qty']         = $_ticket['ticket_qty'];
                $zdata[ $key ]['rbfw_ticket_info']        = $ticket_info;
                $zdata[ $key ]['rbfw_duration_cost']      = $rbfw_duration_cost;
                $zdata[ $key ]['rbfw_service_cost']       = $rbfw_service_cost;
                $zdata[ $key ]['discount_amount']         = $_ticket['discount_amount'];
                $zdata[ $key ]['rbfw_order_id']           = $order_id;
                $zdata[ $key ]['rbfw_order_status']       = $order_status;
                $zdata[ $key ]['rbfw_payment_method']     = $payment_method;
                $zdata[ $key ]['rbfw_user_id']            = $user_id;
                $zdata[ $key ]['rbfw_billing_name']       = $order_meta['_billing_first_name'][0] . ' ' . $order_meta['_billing_last_name'][0];
                $zdata[ $key ]['rbfw_billing_email']      = $order_meta['_billing_email'][0];
                $zdata[ $key ]['rbfw_billing_phone']      = $order_meta['_billing_phone'][0];
                $zdata[ $key ]['rbfw_billing_address']    = $order_meta['_billing_address_1'][0] . ' ' . $order_meta['_billing_address_2'][0];
                $zdata[ $key ]['rbfw_id']                 = $rbfw_id;

                $meta_data = array_merge($zdata[ $key ], $ticket_info, $user_info);
                $order_id = $rbfw->rbfw_add_order_data($meta_data, $ticket_info );
                $order_meta_id = $rbfw->rbfw_add_order_meta_data($meta_data, $ticket_info);
                
                if($order_id && $order_meta_id){
                    update_post_meta($order_id, 'rbfw_order_status', $order_status);
                    update_post_meta($order_meta_id, 'rbfw_order_status', $order_status);
                }
                
        }
    }
    
}