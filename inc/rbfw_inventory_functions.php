<?php

function rbfw_add_order_meta_data($meta_data = array(), $ticket_info = array()) {



    global $rbfw;
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
    $title = $meta_data['rbfw_billing_name'];
    $cpt_name = 'rbfw_order_meta';

    if($rbfw_payment_system == 'wps'){

        $wc_order_id = $meta_data['rbfw_order_id'];
        $ticket_info = $meta_data['rbfw_ticket_info'];
        $order_tax = !empty(get_post_meta($wc_order_id, '_order_tax', true)) ? get_post_meta($wc_order_id, '_order_tax', true) : 0;
        $total_cost = get_post_meta($wc_order_id, '_order_total', true);
        $rbfw_link_order_id = get_post_meta($wc_order_id, '_rbfw_link_order_id', true);
        $rbfw_pin = get_post_meta($rbfw_link_order_id, 'rbfw_pin', true);

        /* If Order not exist, create the order */
        $args = array(
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => $cpt_name
        );

        $post_id = wp_insert_post($args);

        if (sizeof($meta_data) > 0) {
            foreach ($meta_data as $key => $value) {
                if($key != 'rbfw_ticket_info'){
                    update_post_meta($post_id, $key, $value);
                }
            }
            if(!empty($ticket_info)){
                foreach ($ticket_info as $key =>$item) {

                    $rbfw_id = $item['rbfw_id'];
                    foreach ($item as $key => $value) {
                        if ($key == 'rbfw_start_date' || $key == 'rbfw_end_date') {
                            $value = date('Y-m-d', strtotime($value));
                        }
                        if ($key == 'rbfw_start_datetime' || $key == 'rbfw_end_datetime') {
                            $value = date('Y-m-d h:i A', strtotime($value));
                        }
                        update_post_meta($post_id, $key, $value);
                    }
                    rbfw_create_inventory_meta($item, $rbfw_id, $wc_order_id);
                }
            }
            wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$wc_order_id.' '.$title));
        }

        update_post_meta($post_id, 'rbfw_pin', $rbfw_pin);

        if(!empty($order_tax)){ update_post_meta($post_id, 'rbfw_order_tax', $order_tax); }

        update_post_meta($post_id, 'rbfw_ticket_total_price', $total_cost);
        update_post_meta($post_id, 'rbfw_link_order_id', $wc_order_id);
        /* End */

        rbfw_update_inventory( $wc_order_id, 'processing');
    }

    return $post_id;
}


function rbfw_create_inventory_meta($ticket_info, $rbfw_id, $order_id){



    global $rbfw;
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
    $rbfw_item_type = !empty(get_post_meta($rbfw_id, 'rbfw_item_type', true)) ? get_post_meta($rbfw_id, 'rbfw_item_type', true) : '';
    $rbfw_inventory_info = !empty(get_post_meta($rbfw_id, 'rbfw_inventory', true)) ? get_post_meta($rbfw_id, 'rbfw_inventory', true) : [];

    if($rbfw_payment_system == 'wps'){
        $order = wc_get_order( $order_id );
        $rbfw_order_status = $order->get_status();
    } else {
        $rbfw_order_status = !empty(get_post_meta($order_id, 'rbfw_order_status', true)) ? get_post_meta($order_id, 'rbfw_order_status', true) : '';
    }

    $start_date = !empty($ticket_info['rbfw_start_date']) ? $ticket_info['rbfw_start_date'] : '';
    $end_date = !empty($ticket_info['rbfw_end_date']) ? $ticket_info['rbfw_end_date'] : '';
    $start_time = !empty($ticket_info['rbfw_start_time']) ? $ticket_info['rbfw_start_time'] : '';
    $end_time = !empty($ticket_info['rbfw_end_time']) ? $ticket_info['rbfw_end_time'] : '';
    $rbfw_item_quantity = !empty($ticket_info['rbfw_item_quantity']) ? $ticket_info['rbfw_item_quantity'] : 0;
    $rbfw_type_info = !empty($ticket_info['rbfw_type_info']) ? $ticket_info['rbfw_type_info'] : [];
    $rbfw_variation_info = !empty($ticket_info['rbfw_variation_info']) ? $ticket_info['rbfw_variation_info'] : [];
    $rbfw_service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
    $rbfw_service_infos = !empty($ticket_info['rbfw_service_infos']) ? $ticket_info['rbfw_service_infos'] : [];
    $date_range = [];



    if( ($rbfw_item_type == 'bike_car_md') || ($rbfw_item_type == 'dress') || ($rbfw_item_type == 'equipment') || ($rbfw_item_type == 'others') ){

        // Start: Date Time Calculation
        $start_datetime  = date( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) );
        $end_datetime = date( 'Y-m-d H:i', strtotime( $end_date . ' ' . $end_time ) );
        $start_datetime  = new DateTime( $start_datetime );
        $end_datetime = new DateTime( $end_datetime );

        $diff = date_diff( $start_datetime, $end_datetime );
        $days = 0;
        $hours = 0;

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        if ( $diff ) {
            $days    = $diff->days;
            $hours   += $diff->h;



            if ( ($hours > 0)  || ($start_time == '00:00:00' && $end_time == rbfw_end_time()) ) {


                $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                if($rbfw_count_extra_day_enable=='on'){
                    for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {

                        $date = date('d-m-Y', $currentDate);
                        $date_range[] = $date;

                    }
                }else{
                    for ($currentDate = $start_date; $currentDate < $end_date; $currentDate += (86400)) {

                        $date = date('d-m-Y', $currentDate);
                        $date_range[] = $date;

                    }
                }



            } else {

                for ($currentDate = $start_date; $currentDate < $end_date; $currentDate += (86400)) {

                    $date = date('d-m-Y', $currentDate);

                    $date_range[] = $date;

                }

            }

        }
        // End: Date Time Calculation

    } else {

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        for ($currentDate = $start_date; $currentDate <= $end_date;

             $currentDate += (86400)) {

            $date = date('d-m-Y', $currentDate);

            $date_range[] = $date;

        }
    }







    $order_array = [];
    $order_array['booked_dates'] = $date_range;
    $order_array['rbfw_start_time'] = $start_time;
    $order_array['rbfw_end_time'] = $end_time;
    $order_array['rbfw_type_info'] = $rbfw_type_info;
    $order_array['rbfw_variation_info'] = $rbfw_variation_info;
    $order_array['rbfw_service_info'] = $rbfw_service_info;
    $order_array['rbfw_service_infos'] = $rbfw_service_infos;
    $order_array['rbfw_item_quantity'] = $rbfw_item_quantity;
    $order_array['rbfw_order_status'] = $rbfw_order_status;

    $rbfw_inventory_info[$order_id] = $order_array;







    update_post_meta($rbfw_id, 'rbfw_inventory', $rbfw_inventory_info);

    return true;
}

function rbfw_update_inventory($order_id, $current_status = null){
    global $rbfw;
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

    /* get order meta data from wp_postmeta table */
    global $wpdb;
    $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
    $order = $wpdb->get_results("SELECT * FROM `$order_items_table` WHERE order_id = ".$order_id."");



    if($rbfw_payment_system == 'wps'){

        foreach( $order as $item ) {
            $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
            $item_id = $item->order_item_id;
            $item_meta_data = $wpdb->get_results("SELECT * FROM `$order_itemmeta_table` WHERE order_item_id = ".$item_id." AND meta_key = '_rbfw_id' ");

            foreach ($item_meta_data as $meta_data) {
                $rbfw_id = $meta_data->meta_value;
                $inventory = get_post_meta($rbfw_id,'rbfw_inventory', true);

                if (!empty($inventory) && array_key_exists($order_id, $inventory)){

                    $inventory[$order_id]['rbfw_order_status'] = $current_status;


                    update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
                }
            }

        }

    } else {

        $rbfw_id = get_post_meta($order_id, 'rbfw_id', true);
        $inventory = get_post_meta($rbfw_id,'rbfw_inventory', true);

        if (!empty($inventory) && array_key_exists($order_id, $inventory)){

            $inventory[$order_id]['rbfw_order_status'] = $current_status;

            update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
        }
    }



}