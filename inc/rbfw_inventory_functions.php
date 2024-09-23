<?php

function rbfw_add_order_meta_data($meta_data = array(), $ticket_info = array()) {

    //echo '<pre>';print_r($meta_data);echo '<pre>';exit;

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

    //echo '<pre>';print_r($ticket_info);echo '<pre>';exit;

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
                for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
                    $date = date('d-m-Y', $currentDate);
                    $date_range[] = $date;
                }
            }

        }
        // End: Date Time Calculation

    } else {

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
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

function rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date, $type = null,$pickup_datetime=null,$dropoff_datetime=null){

    if (empty($post_id) || empty($start_date) || empty($end_date)) {
        return;
    }

    $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
    $rbfw_variations_stock = rbfw_get_variations_stock($post_id);

    $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);
    $type_stock = 0;

    // Start: Get Date Range
    $date_range = [];
    $start_date = strtotime($start_date);
    $end_date = strtotime($end_date);

    for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
        $date = date('d-m-Y', $currentDate);
        $date_range[] = $date;
    }
    // End: Get Date Range

    if ($rent_type == 'resort') {
        $rbfw_resort_room_data = get_post_meta($post_id, 'rbfw_resort_room_data', true);
        if (!empty($rbfw_resort_room_data)) {
            foreach ($rbfw_resort_room_data as $key => $resort_room_data) {
                if($resort_room_data['room_type'] == $type){
                    $type_stock += !empty($resort_room_data['rbfw_room_available_qty']) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                }
            }
        }
    } else {
        // For Bike/car Multiple Day Type
        if($rbfw_enable_variations == 'yes'){
            $type_stock += $rbfw_variations_stock;
        } else {
            $type_stock += (int)get_post_meta($post_id, 'rbfw_item_stock_quantity', true);
        }
        // End Bike/car Multiple Day Type
    }

    $inventory_based_on_return = rbfw_get_option('inventory_based_on_return','rbfw_basic_gen_settings');


    if (!empty($rbfw_inventory)) {

        $total_qty = 0;
        $qty_array = [];
        $extra_service_quantity = [];

        foreach ($date_range as $key1 => $range_date) {
            foreach ($rbfw_inventory as $key => $inventory) {

                $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
                $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;


                if ( in_array($range_date, $booked_dates) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$inventory['rbfw_order_status'] == 'returned':'') ) ) {

                    if ($rent_type == 'resort') {
                        foreach ($rbfw_type_info as $type_name => $type_qty) {
                            if ($type_name == $type) {
                                $total_qty += $type_qty;
                            }
                        }
                    } else {
                        $inventory_start_date = $booked_dates[0];
                        $inventory_end_date = end($booked_dates);
                        $inventory_start_time = $inventory['rbfw_start_time'];
                        $inventory_end_time = $inventory['rbfw_end_time'];

                        $inventory_start_datetime = date('Y-m-d H:i', strtotime($inventory_start_date . ' ' . $inventory_start_time));
                        $inventory_end_datetime = date('Y-m-d H:i', strtotime($inventory_end_date . ' ' . $inventory_end_time));
                        if($key1==0){
                             if($inventory_end_datetime>$pickup_datetime){
                                 $total_qty += $rbfw_item_quantity;
                             }
                         }elseif($key1==(count($date_range)-1)){
                             if($inventory_start_datetime<$dropoff_datetime){
                                 $total_qty += $rbfw_item_quantity;
                             }
                         }else{
                             $total_qty += $rbfw_item_quantity;
                         }

                    }
                }

            }

            $remaining_stock = $type_stock - $total_qty;
            $remaining_stock = max(0, $remaining_stock);
            $qty_array[] = $remaining_stock;
            $total_qty = 0;
        }
    }

    if (empty($qty_array)) {
        $remaining_stock = $type_stock;
    } else {
        $remaining_stock = min($qty_array);
    }

    /*start service inventory*/
    $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
    $service_stock = [];
    if (!empty($rbfw_service_category_price)) {
        foreach($rbfw_service_category_price as $key=>$item1){
            $cat_title = $item1['cat_title'];

            foreach ($item1['cat_services'] as $key1=>$single){
                if($single['title']){
                    $service_q = [];
                    foreach($date_range as $date){
                        $service_q[] = array('date'=>$date,$single['title']=>total_service_quantity($cat_title,$single['title'],$date,$rbfw_inventory,$inventory_based_on_return));
                    }

                    //echo '<pre>';print_r($service_q);echo '<pre>';

                    $service_stock[] = (int)$single['stock_quantity'] - max(array_column($service_q, $single['title']));
                }
            }
        }
    }


    /*end service inventory*/

    /*start variation inventory*/
    $variant_instock = [];
    $rbfw_variations_data = get_post_meta( $post_id, 'rbfw_variations_data', true ) ? get_post_meta( $post_id, 'rbfw_variations_data', true ) : [];
    $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';

    if(($rbfw_enable_variations=='yes') && !empty($rbfw_variations_data)){
        $variant_q = [];
        foreach($rbfw_variations_data as $key=>$item1){
            $field_label = $item1['field_label'];
            if($field_label){
                foreach ($item1['value'] as $key1=>$single){
                    if($single['name']){
                        foreach($date_range as $date){
                            $variant_q[] = array('date'=>$date,$single['name']=>total_variant_quantity($field_label,$single['name'],$date,$rbfw_inventory,$inventory_based_on_return));
                        }
                        $booked_quantity = array_column($variant_q, $single['name']);
                        $variant_instock[] = $single['quantity'] - max($booked_quantity);
                    }
                }
            }
        }
    }

    /*end variation inventory*/


    /*start extra service inventory*/
    $extra_service_instock = [];
    $rbfw_extra_service_info = get_post_meta($post_id, 'rbfw_extra_service_data', true);
    if(!empty($rbfw_extra_service_info)){
        $service_q = [];
        foreach($rbfw_extra_service_info as $service=>$es){
            foreach($date_range as $date){
                $service_q[] = array('date'=>$date,$es['service_name']=>total_extra_service_quantity($es['service_name'],$date,$rbfw_inventory,$inventory_based_on_return));
            }
            $extra_service_instock[$service] = $es['service_qty'] - max(array_column($service_q, $es['service_name']));
        }
    }
    /*end extra service inventory*/

    return array('remaining_stock'=>$remaining_stock,
        'extra_service_instock'=>$extra_service_instock,
        'service_stock'=>$service_stock,
        'variant_instock'=>$variant_instock,
    );
}


function total_service_quantity($paraent,$service,$date,$inventory,$inventory_based_on_return,$start_time = null, $end_time = null){
    $total_single_service = 0;

    foreach($inventory as $item){

        $booked_dates = !empty($item['booked_dates']) ? $item['booked_dates'] : [];

        if(in_array($date,$item['booked_dates']) && array_key_exists($paraent,$item['rbfw_service_infos']) && ($item['rbfw_order_status'] == 'completed' || $item['rbfw_order_status'] == 'processing' || $item['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$item['rbfw_order_status'] == 'returned':'')  )){
            $inventory_start_date = $booked_dates[0];
            $inventory_end_date = end($booked_dates);
            $inventory_start_time = $item['rbfw_start_time'];
            $inventory_end_time = $item['rbfw_end_time'];
            $inventory_start_datetime = strtotime($inventory_start_date . ' ' . $inventory_start_time);
            $inventory_end_datetime =  strtotime($inventory_end_date . ' ' . $inventory_end_time);
            if($start_time && $end_time){
                $pickup_datetime = strtotime($date . ' ' . $start_time);
                $dropoff_datetime = strtotime($date . ' ' . $end_time);
                if(!(($inventory_start_datetime>$pickup_datetime && $inventory_start_datetime>$dropoff_datetime) || ($inventory_end_datetime<$pickup_datetime && $inventory_end_datetime<$dropoff_datetime))){
                    foreach ($item['rbfw_service_infos'] as $key=>$single){
                        foreach ($single as $basic_item){
                            if(in_array($service,$basic_item)){
                                $total_single_service += $basic_item['quantity'];
                            }
                        }
                    }
                }
            }else{
                foreach ($item['rbfw_service_infos'] as $key=>$single){
                    foreach ($single as $basic_item){
                        if(in_array($service,$basic_item)){
                            $total_single_service += $basic_item['quantity'];
                        }
                    }
                }
            }
        }
    }
    return $total_single_service;
}


function total_extra_service_quantity($service,$date,$inventory,$inventory_based_on_return){

    $total_single_service = 0;
    foreach($inventory as $item){
        if(in_array($date,$item['booked_dates'])  && ($item['rbfw_order_status'] == 'completed' || $item['rbfw_order_status'] == 'processing' || $item['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$item['rbfw_order_status'] == 'returned':'') ) && isset($item['rbfw_service_info'][$service])){
            $total_single_service += $item['rbfw_service_info'][$service];
        }
    }
    return $total_single_service;
}


function total_variant_quantity($field_label,$variation,$date,$inventory,$inventory_based_on_return){

    $total_single_service = 0;
    foreach($inventory as $item){
        foreach ($item['rbfw_variation_info'] as $key=>$single){
            if(in_array($date,$item['booked_dates']) && in_array($variation,$single) && ($item['rbfw_order_status'] == 'completed' || $item['rbfw_order_status'] == 'processing' || $item['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$item['rbfw_order_status'] == 'returned':'')  )){
                $total_single_service += $item['rbfw_item_quantity'];
            }
        }
    }
    return $total_single_service;
}