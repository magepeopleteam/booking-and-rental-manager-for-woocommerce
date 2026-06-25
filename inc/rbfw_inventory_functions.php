<?php

add_action('wp_ajax_rbfw_get_stock_details', 'rbfw_get_stock_details');
add_action('wp_ajax_rbfw_get_stock_by_filter', 'rbfw_get_stock_by_filter');


function rbfw_add_order_meta_data($meta_data = array(), $ticket_info = array()) {

    $title = $meta_data['rbfw_billing_name'];
    $cpt_name = 'rbfw_order_meta';

        $wc_order_id = intval($meta_data['rbfw_order_id']);
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
                            $value = gmdate('Y-m-d', strtotime($value));
                        }
                        if ($key == 'rbfw_start_datetime' || $key == 'rbfw_end_datetime') {
                            $value = gmdate('Y-m-d h:i A', strtotime($value));
                        }
                        update_post_meta($post_id, $key, $value);
                    }
                    rbfw_create_inventory_meta($item, $rbfw_id, $wc_order_id);
                }
            }
            wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$wc_order_id.' '.$title));
        }

        update_post_meta($post_id, 'rbfw_pin', $rbfw_pin);

        if(!empty($order_tax)){
            update_post_meta($post_id, 'rbfw_order_tax', $order_tax);
        }

        update_post_meta($post_id, 'rbfw_ticket_total_price', $total_cost);
        update_post_meta($post_id, 'rbfw_link_order_id', $wc_order_id);
        /* End */

        rbfw_update_inventory( $wc_order_id, 'processing');


    return $post_id;
}


function rbfw_create_inventory_meta($ticket_info, $rbfw_id, $order_id, $order_status = null){

    global $rbfw;
    $rbfw_item_type = !empty(get_post_meta($rbfw_id, 'rbfw_item_type', true)) ? get_post_meta($rbfw_id, 'rbfw_item_type', true) : '';
    $rbfw_inventory_info = !empty(get_post_meta($rbfw_id, 'rbfw_inventory', true)) ? get_post_meta($rbfw_id, 'rbfw_inventory', true) : [];

    if(!is_array($rbfw_inventory_info)){
        $rbfw_inventory_info = [];
    }

    // When an explicit status is supplied (native/standalone booking) use it; otherwise
    // resolve it from the WooCommerce order. This lets the inventory writer be reused
    // without WooCommerce.
    if ( $order_status !== null ) {
        $rbfw_order_status = $order_status;
    } else {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return false;
        }
        $rbfw_order_status = $order->get_status();
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


    if($rbfw_item_type == 'multiple_items'){
        $rbfw_service_info = !empty($ticket_info['multiple_items_info']) ? $ticket_info['multiple_items_info'] : [];
        $rbfw_service_infos = !empty($ticket_info['rbfw_category_wise_info']) ? $ticket_info['rbfw_category_wise_info'] : [];
    }

    $date_range = [];





    if( ($rbfw_item_type == 'bike_car_md') || ($rbfw_item_type == 'dress') || ($rbfw_item_type == 'equipment') || ($rbfw_item_type == 'others') ){

        // Start: Date Time Calculation
        $start_datetime  = gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) );
        $end_datetime = gmdate( 'Y-m-d H:i', strtotime( $end_date . ' ' . $end_time ) );
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



            if ( ($hours > 0)  || ($start_time == '' && $end_time == '') ) {

                $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');

                if($rbfw_count_extra_day_enable=='on'){
                    for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
                        $date = gmdate('d-m-Y', $currentDate);
                        $date_range[] = $date;
                    }
                }else{
                    for ($currentDate = $start_date; $currentDate < $end_date; $currentDate += (86400)) {
                        $date = gmdate('d-m-Y', $currentDate);
                        $date_range[] = $date;
                    }
                }
            } else {

                for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
                    $date = gmdate('d-m-Y', $currentDate);
                    $date_range[] = $date;
                }
            }
        }
        // End: Date Time Calculation

    } elseif($rbfw_item_type=='bike_car_sd'){
        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
            $date = gmdate('d-m-Y', $currentDate);
            $date_range[] = $date;
        }
    } else{

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        for ($currentDate = $start_date; $currentDate <= $end_date; $currentDate += (86400)) {
            $date = gmdate('d-m-Y', $currentDate);
            $date_range[] = $date;
        }
    }


    $order_array = [];

    $order_array['rbfw_start_date_ymd'] = !empty($ticket_info['rbfw_start_date']) ? $ticket_info['rbfw_start_date'] : '';
    $order_array['rbfw_end_date_ymd'] = !empty($ticket_info['rbfw_end_date']) ? $ticket_info['rbfw_end_date'] : '';
    $order_array['rbfw_start_time_24'] = !empty($ticket_info['rbfw_start_time']) ? $ticket_info['rbfw_start_time'] : '';
    $order_array['rbfw_end_time_24'] = !empty($ticket_info['rbfw_end_time']) ? $ticket_info['rbfw_end_time'] : '';


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


function rbfw_update_inventory($order_id, $current_status = null) {



    // Retrieve the WooCommerce order object.
    $order = wc_get_order($order_id);

    if (!$order) {
        return; // Exit if the order doesn't exist.
    }

    // Loop through each item in the order.
    foreach ($order->get_items() as $item_id => $item) {
        // Get the custom meta '_rbfw_id' for the order item.
        $rbfw_id = $item->get_meta('_rbfw_id', true);

        if ($rbfw_id) {
            // Retrieve the inventory data for the associated product.
            $inventory = get_post_meta($rbfw_id, 'rbfw_inventory', true);

            // Check if the inventory exists and contains the order ID.
            if (!empty($inventory) && is_array($inventory) && array_key_exists($order_id, $inventory)) {
                // Update the order status in the inventory data.
                $inventory[$order_id]['rbfw_order_status'] = $current_status;

                // Save the updated inventory back to the post meta.
                update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
            }
        }
    }
}

function rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date, $type = null,$pickup_datetime=null,$dropoff_datetime=null,$rbfw_enable_time_slot='off'){


    if (empty($post_id) || empty($start_date) || empty($end_date)) {
        return;
    }

    $rbfw_enable_variations_raw = get_post_meta( $post_id, 'rbfw_enable_variations', true );
    $rbfw_enable_variations = $rbfw_enable_variations_raw ? $rbfw_enable_variations_raw : 'no';
    $rbfw_variations_stock = rbfw_get_variations_stock($post_id);
    $rbfw_variations_data_raw = get_post_meta( $post_id, 'rbfw_variations_data', true );
    $rbfw_variations_data = $rbfw_variations_data_raw ? $rbfw_variations_data_raw : [];

    $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);
    $total_stock = 0;
    $total_booked = 0;


    $date_range = [];

    for ($currentDate = strtotime($start_date); $currentDate <= strtotime($end_date); $currentDate += (86400)) {
        $date = gmdate('d-m-Y', $currentDate);
        $date_range[] = $date;
    }

    // End: Get Date Range

    if ($rent_type == 'resort') {
        $rbfw_resort_room_data = get_post_meta($post_id, 'rbfw_resort_room_data', true);
        if (!empty($rbfw_resort_room_data)) {
            foreach ($rbfw_resort_room_data as $key => $resort_room_data) {
                if($resort_room_data['room_type'] == $type){
                    $total_stock += !empty($resort_room_data['rbfw_room_available_qty']) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                }
            }
        }
    } else {
        // For Bike/car Multiple Day Type
        if($rbfw_enable_variations == 'yes'){
            $total_stock += $rbfw_variations_stock;
        } else {
            $total_stock += (int)get_post_meta($post_id, 'rbfw_item_stock_quantity', true);
        }
        // End Bike/car Multiple Day Type
    }

    $inventory_based_on_return       = rbfw_get_option( 'inventory_based_on_return', 'rbfw_basic_gen_settings' );
    $stock_manage_on_return_date_raw = get_post_meta( $post_id, 'stock_manage_on_return_date', true );
    $stock_manage_on_return_date     = $stock_manage_on_return_date_raw ? $stock_manage_on_return_date_raw : 'no';
    $rbfw_buffer_time_after_raw      = get_post_meta( $post_id, 'rbfw_buffer_time_after', true );
    $rbfw_buffer_time_after          = $rbfw_buffer_time_after_raw ? $rbfw_buffer_time_after_raw : 0;
    $mepp_reduce_stock_multi         = get_option( 'mepp_reduce_stock', 'full' );
    $inventory_managed_order_status  = rbfw_get_option( 'inventory_managed_order_status', 'rbfw_basic_gen_settings' );
    $inventory_managed_order_status  = is_array( $inventory_managed_order_status ) ? $inventory_managed_order_status : [
        'processing' => 'processing',
        'completed'  => 'completed',
    ];





    if(is_array($rbfw_inventory)){
        foreach ($rbfw_inventory as $wc_order_id => $inventory) {

            $order = wc_get_order($wc_order_id);

            if($order && !($order->get_status() == 'trash' || $order->get_status() == 'cancelled')){

                $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;

                $partial_stock = true;
                if($inventory['rbfw_order_status'] == 'partially-paid' && $mepp_reduce_stock_multi=='deposit'){
                    $partial_stock = false;
                }

                $checkValues = $inventory['rbfw_order_status'];


                if ( (in_array($checkValues, $inventory_managed_order_status) || $inventory['rbfw_order_status'] == 'picked' || ($inventory_based_on_return == 'yes' && $inventory['rbfw_order_status'] == 'returned')) && $partial_stock) {

                    $inventory_start_date = $inventory['rbfw_start_date_ymd'];
                    $inventory_end_date = $inventory['rbfw_end_date_ymd'];
                    $inventory_start_time = $inventory['rbfw_start_time_24'];
                    $inventory_end_time = $inventory['rbfw_end_time_24'];

                    if ($rbfw_buffer_time_after) {
                        $datetime = new DateTime("$inventory_end_date $inventory_end_time");
                        $datetime->modify('+' . $rbfw_buffer_time_after . ' hours');
                        $inventory_end_date = $datetime->format('Y-m-d');
                        $inventory_end_time = $datetime->format('H:i');
                    } else {
                        if ($stock_manage_on_return_date == 'no') {
                            $date = new DateTime($inventory_end_date);
                            $date->modify('-1 day');
                            $inventory_end_date = $date->format('Y-m-d');
                        }
                    }









                    $date_inventory_start = new DateTime($inventory_start_date . ' ' . $inventory_start_time);
                    $date_inventory_end = new DateTime($inventory_end_date . ' ' . $inventory_end_time);

                    if ($rent_type == 'resort') {
                        $start_date_time = new DateTime( $start_date );
                        $end_date_time = new DateTime( $end_date );

                        if ($date_inventory_start <= $end_date_time && $start_date_time <= $date_inventory_end) {
                            $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                            foreach ($rbfw_type_info as $type_name => $type_qty) {
                                if ($type_name == $type) {
                                    $total_booked += $type_qty;
                                }
                            }
                        }
                    }else{
                        $start_date_time = new DateTime( $pickup_datetime );
                        $end_date_time = new DateTime( $dropoff_datetime );
                        if ($date_inventory_start <= $end_date_time && $start_date_time <= $date_inventory_end) {
                            $total_booked += $rbfw_item_quantity;
                        }
                    }
                }
            }
        }
    }



    $remaining_stock = $total_stock - $total_booked;


    /*start service inventory*/
    $service_stock = [];

    if ($rent_type != 'resort'){

        $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
        if(!is_array($rbfw_service_category_price)){
            $rbfw_service_category_price = json_decode($rbfw_service_category_price, true);
        }



        if (!empty($rbfw_service_category_price)) {
            foreach($rbfw_service_category_price as $key=>$item1){
                $cat_title = $item1['cat_title'];
                foreach ($item1['cat_services'] as $key1=>$single){
                    if($single['title']){
                        $service_q = [];
                        foreach($date_range as $date){
                            $service_q[] = array('date'=>$date,$single['title']=>total_service_quantity($cat_title,$single['title'],$date,$rbfw_inventory,$inventory_based_on_return));
                        }
                        $service_stock[] = (int)$single['stock_quantity'] - max(array_column($service_q, $single['title']));
                    }
                }
            }
        }
    }





    /*end service inventory*/

    /*start variation inventory*/
    $variant_instock = [];

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
                        if(isset($single['quantity'])){
                            $variant_instock[] = $single['quantity'] - max($booked_quantity);
                        }
                    }
                }
            }
        }
        $remaining_stock = max($variant_instock);
    }

    /*end variation inventory*/


    /*start extra service inventory*/

    $extra_service_instock = [];
    $rbfw_extra_service_info = get_post_meta($post_id, 'rbfw_extra_service_data', true);

    if (!empty($rbfw_extra_service_info)) {
        foreach ($rbfw_extra_service_info as $service => $es) {
            $service_q = [];

            foreach ($date_range as $date) {
                $qty = total_extra_service_quantity($es['service_name'], $date, $rbfw_inventory, $inventory_based_on_return);
                $service_q[] = $qty;
            }

            $max_qty = !empty($service_q) ? max($service_q) : 0;
            $extra_service_instock[$service] = $es['service_qty'] - $max_qty;
        }
    }


    /*end extra service inventory*/

    return array('remaining_stock'=>$remaining_stock,
        'extra_service_instock'=>$extra_service_instock,
        'service_stock'=>$service_stock,
        'variant_instock'=>$variant_instock,
    );
}



function rbfw_get_multi_items_available_qty($post_id, $start_date, $end_date, $type = null,$pickup_datetime=null,$dropoff_datetime=null,$rbfw_enable_time_slot='off'){


    if (empty($post_id) || empty($start_date) || empty($end_date)) {
        return;
    }

    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);
    $date_range = [];

    for ($currentDate = strtotime($start_date); $currentDate <= strtotime($end_date); $currentDate += (86400)) {
        $date = gmdate('d-m-Y', $currentDate);
        $date_range[] = $date;
    }

    $inventory_based_on_return = rbfw_get_option('inventory_based_on_return','rbfw_basic_gen_settings');


    /*start extra service inventory*/

    $multiple_items_instock = [];
    $multiple_items_info = get_post_meta($post_id, 'multiple_items_info', true);

    if (!empty($multiple_items_info)) {
        foreach ($multiple_items_info as $service => $es) {
            $service_q = [];
            foreach ($date_range as $date) {
                $qty = total_multi_items_quantity($es['item_name'], $date, $rbfw_inventory, $inventory_based_on_return);
                $service_q[] = $qty;
            }
            $max_qty = !empty($service_q) ? max($service_q) : 0;
            $extra_service_instock[$service] = $es['available_qty'] - $max_qty;
        }
    }
    /*end extra service inventory*/

    /*start service inventory*/


    $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
    if(!is_array($rbfw_service_category_price)){
        $rbfw_service_category_price = json_decode($rbfw_service_category_price, true);
    }

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
                    $service_stock[] = (int)$single['stock_quantity'] - max(array_column($service_q, $single['title']));
                }
            }
        }
    }
    /*end service inventory*/

    return array(
        'extra_service_instock' => $extra_service_instock,
        'service_stock' => $service_stock,
    );
}


function total_multi_items_quantity($service,$date,$inventory,$inventory_based_on_return){
    $total_single_service = 0;
    if(!empty($inventory)){
        foreach($inventory as $item){
            //echo '<pre>';print_r($item['rbfw_service_info']);echo '<pre>';
            if(in_array($date,$item['booked_dates']) ){
                //$total_single_service += $item['rbfw_service_info'][$service];


                foreach ($item['rbfw_service_info'] as $single) {

                    if ($single['item_name'] == $service) {
                        $total_single_service += $single['item_qty'];
                    }
                }
            }
        }
    }
    return $total_single_service;
}






function rbfw_day_wise_sold_out_check_by_month($post_id, $year,  $month, $total_days){



    if (empty($post_id) || empty($year)  || empty($month) ) {
        return;
    }





    $rbfw_enable_variations_raw = get_post_meta( $post_id, 'rbfw_enable_variations', true );
    $rbfw_enable_variations = $rbfw_enable_variations_raw ? $rbfw_enable_variations_raw : 'no';
    $rbfw_variations_stock = rbfw_get_variations_stock($post_id);

    $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);

    // --- Hoist: fetch once per call, not once per day ---
    $rbfw_resort_room_data          = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
    $rbfw_item_stock_quantity        = (int) get_post_meta( $post_id, 'rbfw_item_stock_quantity', true );
    $inventory_based_on_return       = rbfw_get_option( 'inventory_based_on_return', 'rbfw_basic_gen_settings' );
    $stock_manage_on_return_date_raw = get_post_meta( $post_id, 'stock_manage_on_return_date', true );
    $stock_manage_on_return_date     = $stock_manage_on_return_date_raw ? $stock_manage_on_return_date_raw : 'no';
    $mepp_reduce_stock               = get_option( 'mepp_reduce_stock', 'full' );
    $inventory_managed_order_status  = rbfw_get_option( 'inventory_managed_order_status', 'rbfw_basic_gen_settings' );
    $inventory_managed_order_status  = is_array( $inventory_managed_order_status ) ? $inventory_managed_order_status : [
        'processing' => 'processing',
        'completed'  => 'completed',
    ];
    $rbfw_variations_data_raw = get_post_meta( $post_id, 'rbfw_variations_data', true );
    $rbfw_variations_data     = $rbfw_variations_data_raw ? $rbfw_variations_data_raw : [];

    // Pre-compute base stock (same value for every day in the month)
    $base_total_stock = 0;
    if ( $rent_type == 'resort' ) {
        if ( ! empty( $rbfw_resort_room_data ) ) {
            foreach ( $rbfw_resort_room_data as $resort_room_data ) {
                if ( $resort_room_data['room_type'] == $rent_type ) {
                    $base_total_stock += ! empty( $resort_room_data['rbfw_room_available_qty'] ) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                }
            }
        }
    } else {
        if ( $rbfw_enable_variations == 'yes' ) {
            $base_total_stock += $rbfw_variations_stock;
        } else {
            $base_total_stock += $rbfw_item_stock_quantity;
        }
    }
    // --- End hoist ---


    $date_range = [];

    $day_wise_inventory = [];

    for($i=1;$i<=$total_days;$i++){

        $total_stock = $base_total_stock;
        $date = str_pad($i, 2, '0', STR_PAD_LEFT).'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.$year;
        $date_range[] = $date;



        $total_booked = 0;



        if(is_array($rbfw_inventory)){
            foreach ($rbfw_inventory as $wc_order_id => $inventory) {

                $order = wc_get_order($wc_order_id);

                if($order){
                    $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;

                    $partial_stock = true;
                    if($inventory['rbfw_order_status'] == 'partially-paid' && $mepp_reduce_stock=='deposit'){
                        $partial_stock = false;
                    }

                    $checkValues = $inventory['rbfw_order_status'];

                    if ( (in_array($checkValues, $inventory_managed_order_status) || $inventory['rbfw_order_status'] == 'picked' || ($inventory_based_on_return == 'yes' && $inventory['rbfw_order_status'] == 'returned')) && $partial_stock) {


                        $booked_dates = $inventory['booked_dates'];
                        if($stock_manage_on_return_date=='no'){
                            array_pop($booked_dates);
                        }
                        if (in_array($date,$booked_dates)) {
                            $total_booked += $rbfw_item_quantity;
                        }
                    }
                }



            }

        }



        $remaining_stock = $total_stock - $total_booked;




        /*start variation inventory*/
        $variant_instock = [];

        if(($rbfw_enable_variations=='yes') && !empty($rbfw_variations_data)){
            $variant_q = [];
            foreach($rbfw_variations_data as $key=>$item1){
                $field_label = $item1['field_label'];
                if($field_label){
                    foreach ($item1['value'] as $key1=>$single){
                        if($single['name']){
                            foreach($date_range as $date1){
                                $variant_q[] = array('date'=>$date1,$single['name']=>total_variant_quantity($field_label,$single['name'],$date,$rbfw_inventory,$inventory_based_on_return));
                            }
                            $booked_quantity = array_column($variant_q, $single['name']);
                            $variant_instock[] = $single['quantity'] - max($booked_quantity);
                        }
                    }
                }
            }
            $remaining_stock = max($variant_instock);


        }

        $day_wise_inventory[$date] = $remaining_stock;
    }

    return $day_wise_inventory;

}
function total_service_quantity($paraent,$service,$date,$inventory,$inventory_based_on_return,$start_time = null, $end_time = null){
    $total_single_service = 0;

    if(is_array($inventory)){
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
    }


    return $total_single_service;
}


function total_extra_service_quantity($service,$date,$inventory,$inventory_based_on_return){
    $total_single_service = 0;
    if(!empty($inventory)){
        foreach($inventory as $item){
            if(in_array($date,$item['booked_dates'])  && ($item['rbfw_order_status'] == 'completed' || $item['rbfw_order_status'] == 'processing' || $item['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$item['rbfw_order_status'] == 'returned':'') ) && isset($item['rbfw_service_info'][$service])){
                $total_single_service += $item['rbfw_service_info'][$service];
            }
        }
    }
    return $total_single_service;
}


function total_variant_quantity($field_label,$variation,$date,$inventory,$inventory_based_on_return){

    $total_single_service = 0;
    if(is_array($inventory)){
        foreach($inventory as $item){
            if(!empty($item['rbfw_variation_info'])){
                foreach ($item['rbfw_variation_info'] as $key=>$single){
                    if(in_array($date,$item['booked_dates']) && in_array($variation,$single) && ($item['rbfw_order_status'] == 'completed' || $item['rbfw_order_status'] == 'processing' || $item['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$item['rbfw_order_status'] == 'returned':'')  )){
                        $total_single_service += $item['rbfw_item_quantity'];
                    }
                }
            }
        }
    }

    return $total_single_service;
}


/**
 * Compute an rbfw_item's total purchasable stock and total extra-service stock.
 *
 * This mirrors the totals logic used by the inventory table so the summary cards
 * and each table row stay in sync. It is read-only (no side effects).
 *
 * @param int $post_id rbfw_item post ID.
 * @return array{rent_type:string, item_stock:float, es_qty:float}
 */
function rbfw_inventory_item_stock_totals( $post_id ) {

    $rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );
    $rent_type = ! empty( $rent_type ) ? $rent_type : '';

    $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true );
    $rbfw_enable_variations = ! empty( $rbfw_enable_variations ) ? $rbfw_enable_variations : 'no';

    $rbfw_variations_data = get_post_meta( $post_id, 'rbfw_variations_data', true );
    $rbfw_variations_data = ! empty( $rbfw_variations_data ) ? $rbfw_variations_data : array();

    $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
    $rbfw_resort_room_data = ! empty( $rbfw_resort_room_data ) ? $rbfw_resort_room_data : array();

    $rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
    $rbfw_bike_car_sd_data = ! empty( $rbfw_bike_car_sd_data ) ? $rbfw_bike_car_sd_data : array();

    $manage_inventory_as_timely = get_post_meta( $post_id, 'manage_inventory_as_timely', true );
    $manage_inventory_as_timely = ! empty( $manage_inventory_as_timely ) ? $manage_inventory_as_timely : '';

    $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true );
    $rbfw_extra_service_data = ! empty( $rbfw_extra_service_data ) ? $rbfw_extra_service_data : array();

    $es_qty = 0;
    foreach ( $rbfw_extra_service_data as $value ) {
        $es_qty += ! empty( $value['service_qty'] ) ? $value['service_qty'] : 0;
    }

    $item_stock = 0;
    if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
        if ( $manage_inventory_as_timely == 'on' ) {
            $timely     = get_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', true );
            $item_stock = ! empty( $timely ) ? $timely : 0;
        } else {
            foreach ( $rbfw_bike_car_sd_data as $bike_car_sd_data ) {
                $item_stock += ! empty( $bike_car_sd_data['qty'] ) ? $bike_car_sd_data['qty'] : 0;
            }
        }
    } elseif ( $rent_type == 'resort' ) {
        foreach ( $rbfw_resort_room_data as $resort_room_data ) {
            $item_stock += ! empty( $resort_room_data['rbfw_room_available_qty'] ) ? $resort_room_data['rbfw_room_available_qty'] : 0;
        }
    } else {
        if ( $rbfw_enable_variations == 'yes' ) {
            foreach ( $rbfw_variations_data as $_variations_data ) {
                if ( ! empty( $_variations_data['value'] ) ) {
                    foreach ( $_variations_data['value'] as $value ) {
                        if ( ! ( empty( $value['quantity'] ) || $value['quantity'] <= 0 ) ) {
                            $item_stock = $value['quantity'] + $item_stock;
                        }
                    }
                }
            }
        } else {
            $stock      = get_post_meta( $post_id, 'rbfw_item_stock_quantity', true );
            $item_stock = ! empty( $stock ) ? $stock : 0;
        }
    }

    return array(
        'rent_type'  => $rent_type,
        'item_stock' => (float) $item_stock,
        'es_qty'     => (float) $es_qty,
    );
}

/**
 * Visual state for a stock pill: 'full' (green), 'zero' (red) or '' (neutral).
 *
 * @param float $remain Remaining/available quantity.
 * @param float $total  Total quantity.
 * @return string
 */
function rbfw_inv_stock_state( $remain, $total ) {
    $remain = (float) $remain;
    $total  = (float) $total;
    if ( $total <= 0 || $remain <= 0 ) {
        return 'zero';
    }
    if ( $remain >= $total ) {
        return 'full';
    }
    return '';
}

/**
 * Return an inline SVG icon (self-contained — no icon font / CDN dependency).
 *
 * The plugin's bundled Font Awesome ships without its webfont files, so icon
 * fonts do not render in this admin. Inline SVG removes that dependency
 * entirely. Icons inherit colour via `currentColor` and size via `1em`.
 *
 * @param string $name        Icon key.
 * @param string $extra_class Optional extra class on the <svg>.
 * @return string Safe, static SVG markup.
 */
function rbfw_inv_icon( $name, $extra_class = '' ) {
    $paths = array(
        'box'      => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M12 12l8-4.5"/><path d="M12 12v9"/><path d="M12 12L4 7.5"/>',
        'filter'   => '<path d="M4 5h16l-6.5 8v5.5l-3 1.5V13z"/>',
        'x'        => '<path d="M18 6L6 18"/><path d="M6 6l12 12"/>',
        'refresh'  => '<path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 4v5h-5"/>',
        'layers'   => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
        'sparkles' => '<path d="M12 3l1.7 4.6L18 9l-4.3 1.4L12 15l-1.7-4.6L6 9l4.3-1.4z"/><path d="M18 14l.8 2.2L21 17l-2.2.8L18 20l-.8-2.2L15 17l2.2-.8z"/>',
        'tag'      => '<path d="M3 4h8l9 9-7 7-9-9z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'bed'      => '<path d="M3 7v12"/><path d="M3 13h18v6"/><path d="M21 13v-1a3 3 0 0 0-3-3H8v4"/><circle cx="6.5" cy="10.5" r="1.5"/>',
        'car'      => '<path d="M5 13l1.6-4.4A2 2 0 0 1 8.5 7h7a2 2 0 0 1 1.9 1.6L19 13"/><path d="M4 13h16v4H4z"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/>',
        'clone'    => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V6a2 2 0 0 1 2-2h10"/>',
        'chev_l'   => '<path d="M15 6l-6 6 6 6"/>',
        'chev_r'   => '<path d="M9 6l6 6-6 6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/>',
        'plus'     => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'pencil'   => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'trash'    => '<path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M6 7l1 13h10l1-13"/><path d="M9 7V4h6v3"/>',
        'check'    => '<path d="M5 12l5 5 9-11"/>',
        'clipboard' => '<rect x="8" y="3.5" width="8" height="4" rx="1.5"/><path d="M9 5.5H6.5A1.5 1.5 0 0 0 5 7v12.5A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V7a1.5 1.5 0 0 0-1.5-1.5H15"/><path d="M8.5 12h7"/><path d="M8.5 16h5"/>',
        'file'     => '<path d="M14 3v5h5"/><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8z"/><path d="M9 13h6"/><path d="M9 17h5"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'eye'      => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
        'calendar' => '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M4 10h16"/><path d="M8 3v4"/><path d="M16 3v4"/>',
        'receipt'  => '<path d="M6 3h12v18l-2.2-1.5L13.5 21 12 19.5 10.5 21 8.2 19.5 6 21z"/><path d="M9 8h6"/><path d="M9 12h6"/>',
        'calculator' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M8.5 12h.01"/><path d="M12 12h.01"/><path d="M15.5 12h.01"/><path d="M8.5 16h.01"/><path d="M12 16h.01"/><path d="M15.5 16h.01"/>',
    );

    if ( ! isset( $paths[ $name ] ) ) {
        return '';
    }

    $class = 'rbfw_inv_ic' . ( $extra_class ? ' ' . $extra_class : '' );

    return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

function rbfw_inventory_page(){
    $args = array(
        'post_type'              => 'rbfw_item',
        'order'                  => 'DESC',
        'posts_per_page'         => -1,
        'update_post_meta_cache' => true,
        'update_term_meta_cache' => true,
    );
    $query = new WP_Query( $args );
    $total_items = (int) $query->found_posts;

    /* Summary stats for the cards (read-only). */
    $in_stock     = 0;
    $out_of_stock = 0;
    $with_extra   = 0;
    if ( ! empty( $query->posts ) ) {
        foreach ( $query->posts as $inv_post ) {
            $totals = rbfw_inventory_item_stock_totals( $inv_post->ID );
            if ( $totals['item_stock'] > 0 ) {
                $in_stock++;
            } else {
                $out_of_stock++;
            }
            if ( $totals['es_qty'] > 0 ) {
                $with_extra++;
            }
        }
    }
    ?>
    <div class="rbfw_inv rbfw_inventory_page_wrap wrap">

        <!-- Header -->
        <div class="rbfw_inv_header">
            <div class="rbfw_inv_title">
                <?php echo rbfw_inv_icon('box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                <h1><?php esc_html_e('Inventory','booking-and-rental-manager-for-woocommerce'); ?></h1>
                <span class="rbfw_inv_badge"><?php
                    /* translators: %s: number of inventory items. */
                    echo esc_html( sprintf( _n( '%s Item', '%s Items', $total_items, 'booking-and-rental-manager-for-woocommerce' ), number_format_i18n( $total_items ) ) );
                ?></span>
            </div>
        </div>
        <hr class="wp-header-end">

        <!-- Stat cards -->
        <div class="rbfw_inv_stats">
            <div class="rbfw_inv_stat_card">
                <div class="rbfw_inv_stat_label"><?php esc_html_e('Total Items','booking-and-rental-manager-for-woocommerce'); ?></div>
                <div class="rbfw_inv_stat_value"><?php echo esc_html( number_format_i18n( $total_items ) ); ?></div>
                <div class="rbfw_inv_stat_sub"><?php esc_html_e('As of today','booking-and-rental-manager-for-woocommerce'); ?></div>
            </div>
            <div class="rbfw_inv_stat_card rbfw_inv_green">
                <div class="rbfw_inv_stat_label"><?php esc_html_e('In Stock','booking-and-rental-manager-for-woocommerce'); ?></div>
                <div class="rbfw_inv_stat_value"><?php echo esc_html( number_format_i18n( $in_stock ) ); ?></div>
                <div class="rbfw_inv_stat_sub"><?php esc_html_e('Items available','booking-and-rental-manager-for-woocommerce'); ?></div>
            </div>
            <div class="rbfw_inv_stat_card">
                <div class="rbfw_inv_stat_label"><?php esc_html_e('Out of Stock','booking-and-rental-manager-for-woocommerce'); ?></div>
                <div class="rbfw_inv_stat_value"><?php echo esc_html( number_format_i18n( $out_of_stock ) ); ?></div>
                <div class="rbfw_inv_stat_sub"><?php esc_html_e('Needs restock','booking-and-rental-manager-for-woocommerce'); ?></div>
            </div>
            <div class="rbfw_inv_stat_card rbfw_inv_blue">
                <div class="rbfw_inv_stat_label"><?php esc_html_e('Extra Services','booking-and-rental-manager-for-woocommerce'); ?></div>
                <div class="rbfw_inv_stat_value"><?php echo esc_html( number_format_i18n( $with_extra ) ); ?></div>
                <div class="rbfw_inv_stat_sub"><?php esc_html_e('With extra stock','booking-and-rental-manager-for-woocommerce'); ?></div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="rbfw_inv_filters rbfw_inventory_page_filter">
            <div class="rbfw_inv_filter_group rbfw_inventory_filter_input_group">
                <label><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></label>
                <input type="text" class="rbfw_inventory_filter_date" placeholder="dd-mm-yyyy"/>
            </div>
            <div class="rbfw_inv_filter_group rbfw_inventory_filter_input_group">
                <label><?php esc_html_e('Start Time','booking-and-rental-manager-for-woocommerce'); ?></label>
                <input type="time" id="rbfw_inventory_event_start_time" value="">
            </div>
            <div class="rbfw_inv_filter_group rbfw_inventory_filter_input_group">
                <label><?php esc_html_e('End Time','booking-and-rental-manager-for-woocommerce'); ?></label>
                <input type="time" id="rbfw_inventory_event_end_time" value="">
            </div>
            <div class="rbfw_inv_filter_actions">
                <button type="button" class="rbfw_inv_btn rbfw_inv_btn_primary rbfw_inventory_filter_btn">
                    <?php echo rbfw_inv_icon('filter'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Filter','booking-and-rental-manager-for-woocommerce'); ?>
                </button>
                <button type="button" class="rbfw_inv_btn rbfw_inv_btn_reset rbfw_inventory_reset_btn">
                    <?php echo rbfw_inv_icon('x'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Reset','booking-and-rental-manager-for-woocommerce'); ?>
                </button>
                <button type="button" class="rbfw_inv_btn rbfw_inv_btn_refresh rbfw_inventory_refresh_btn">
                    <?php echo rbfw_inv_icon('refresh'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Refresh','booking-and-rental-manager-for-woocommerce'); ?>
                </button>
            </div>
        </div>

        <!-- Table card -->
        <div class="rbfw_inv_card">
            <div class="rbfw_inv_table_scroll">
                <div class="rbfw_inventory_page_table_wrap">
                    <?php echo wp_kses(rbfw_inventory_page_table($query),rbfw_allowed_html()); ?>
                </div>
            </div>
            <div class="rbfw_inv_footer">
                <span class="rbfw_inv_row_info"></span>
                <div class="rbfw_inv_pager"></div>
            </div>
        </div>
    </div>
    <div id="rbfw_stock_view_result_wrap">
        <div id="rbfw_stock_view_result_inner_wrap"></div>
    </div>
    <div class="rbfw-inventory-page-ph">
        <div class="rbfw-ph-item">
            <div class="rbfw-ph-col-12">
                <div class="rbfw-ph-row">
                    <div class="rbfw-ph-col-12 big"></div>
                </div>
                <div class="rbfw-ph-row">
                    <?php for ($i=0; $i < $total_items; $i++) { ?>
                        <div class="rbfw-ph-col-12"></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function rbfw_check_available_by_specific_date_md($post_id, $specific_date = null){

    $rbfw_enable_variations_raw = get_post_meta( $post_id, 'rbfw_enable_variations', true );
    $rbfw_enable_variations     = ! empty( $rbfw_enable_variations_raw ) ? $rbfw_enable_variations_raw : 'no';
    $rbfw_variations_data_raw   = get_post_meta( $post_id, 'rbfw_variations_data', true );
    $rbfw_variations_data       = ! empty( $rbfw_variations_data_raw ) ? $rbfw_variations_data_raw : [];

    $rbfw_item_stock_quantity = 0;

    if($rbfw_enable_variations=='yes'){
        foreach ($rbfw_variations_data as $_variations_data) {
            if(!empty($_variations_data['value'])){
                foreach ($_variations_data['value'] as $value) {
                    if(empty($value['quantity']) || $value['quantity'] <= 0){
                        ////
                    } else{
                        $rbfw_item_stock_quantity =  $value['quantity'] + $rbfw_item_stock_quantity;
                    }
                }
            }
        }
    }else{
        $rbfw_item_stock_quantity_raw = get_post_meta( $post_id, 'rbfw_item_stock_quantity', true );
        $rbfw_item_stock_quantity     = ! empty( $rbfw_item_stock_quantity_raw ) ? $rbfw_item_stock_quantity_raw : 0;
    }

    $rbfw_inventory_raw = get_post_meta( $post_id, 'rbfw_inventory', true );
    $rbfw_inventory     = ! empty( $rbfw_inventory_raw ) ? $rbfw_inventory_raw : [];

    $inventory_based_on_return      = rbfw_get_option( 'inventory_based_on_return', 'rbfw_basic_gen_settings' );
    $mepp_reduce_stock_md           = get_option( 'mepp_reduce_stock', 'full' );
    $inventory_managed_order_status = rbfw_get_option( 'inventory_managed_order_status', 'rbfw_basic_gen_settings' );
    $inventory_managed_order_status = is_array( $inventory_managed_order_status ) ? $inventory_managed_order_status : [
        'processing' => 'processing',
        'completed'  => 'completed',
    ];

    $remaining_item_stock = $rbfw_item_stock_quantity;
    $sold_item_qty = 0;

    if(!empty($rbfw_inventory)){
        foreach ($rbfw_inventory as $key => $inventory) {
            $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];

            $partial_stock = true;
            if($inventory['rbfw_order_status'] == 'partially-paid' && $mepp_reduce_stock_md=='deposit'){
                $partial_stock = false;
            }

            $checkValues = $inventory['rbfw_order_status'];

            if ( in_array($specific_date, $booked_dates) && (in_array($checkValues, $inventory_managed_order_status) || $inventory['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$inventory['rbfw_order_status'] == 'returned':'')) && $partial_stock ){
                $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;
                $sold_item_qty += $rbfw_item_quantity;
            }
        }
        $remaining_item_stock = $rbfw_item_stock_quantity - (int)$sold_item_qty;

    }

    return $remaining_item_stock;
}



function rbfw_inventory_page_table($query, $date = null, $start_time = null, $end_time = null){

    ob_start();
    $inventory_based_on_return = rbfw_get_option('inventory_based_on_pickup_return','rbfw_basic_gen_settings');
    ?>
    <table class="rbfw_inv_table">
        <thead  class="rbfw_inv_thead">
        <tr class="rbfw_inv_thead_row">
            <th><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_inv_th_left"><?php esc_html_e('Item Name','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Item Stock','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Item Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Extra Service Stock','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Extra Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Category Service','booking-and-rental-manager-for-woocommerce'); ?></th>
            <th class="rbfw_text_center"><?php esc_html_e('Category Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $tbl_inventory_based_on_return = rbfw_get_option( 'inventory_based_on_return', 'rbfw_basic_gen_settings' );
        $tbl_mepp_reduce_stock         = get_option( 'mepp_reduce_stock', 'full' );
        $tbl_inv_managed_order_status  = rbfw_get_option( 'inventory_managed_order_status', 'rbfw_basic_gen_settings' );
        $tbl_inv_managed_order_status  = is_array( $tbl_inv_managed_order_status ) ? $tbl_inv_managed_order_status : [
            'processing' => 'processing',
            'completed'  => 'completed',
        ];
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                global $post;
                $post_id = $post->ID;

                /* Total item stock + total extra-service stock (shared with the summary cards). */
                $stock_totals             = rbfw_inventory_item_stock_totals( $post_id );
                $rent_type                = $stock_totals['rent_type'];
                $rbfw_item_stock_quantity = $stock_totals['item_stock'];
                $total_es_qty             = $stock_totals['es_qty'];

                if ( !empty($date) ){
                    $current_date = $date;
                } else {
                    $current_date = date_i18n('d-m-Y');
                }

                $rbfw_inventory_raw = get_post_meta( $post_id, 'rbfw_inventory', true );
                $rbfw_inventory     = ! empty( $rbfw_inventory_raw ) ? $rbfw_inventory_raw : [];

                $inventory_based_on_return      = $tbl_inventory_based_on_return;
                $mepp_reduce_stock_table        = $tbl_mepp_reduce_stock;
                $inv_managed_order_status_table = $tbl_inv_managed_order_status;

                $remaining_item_stock = $rbfw_item_stock_quantity;
                $remaining_es_stock = $total_es_qty;
                $sold_item_qty = 0;
                $sold_es_qty = 0;

                if(!empty($rbfw_inventory)){
                    foreach ($rbfw_inventory as $key => $inventory) {
                        $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];

                        $partial_stock = true;
                        if($inventory['rbfw_order_status'] == 'partially-paid' && $mepp_reduce_stock_table=='deposit'){
                            $partial_stock = false;
                        }

                        $inventory_managed_order_status = $inv_managed_order_status_table;
                        $checkValues = $inventory['rbfw_order_status'];

                        if ( in_array($current_date, $booked_dates) && (in_array($checkValues, $inventory_managed_order_status) || $inventory['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$inventory['rbfw_order_status'] == 'returned':'')) && $partial_stock ){
                            $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                            $rbfw_variation_info = !empty($inventory['rbfw_variation_info']) ? $inventory['rbfw_variation_info'] : [];
                            $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];
                            $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;

                            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment' || $rent_type == 'resort') {
                                if (!empty($rbfw_type_info)) {
                                    foreach ($rbfw_type_info as $key => $type_info) {
                                        $sold_item_qty += $type_info;
                                    }
                                }
                                if (!empty($rbfw_service_info)) {
                                    foreach ($rbfw_service_info as $key => $service_info) {
                                        $sold_es_qty += $service_info;
                                    }
                                }
                            }else {
                                $inventory_start_date = $booked_dates[0];
                                $inventory_end_date = end($booked_dates);
                                $inventory_start_time = $inventory['rbfw_start_time'];
                                $inventory_end_time = $inventory['rbfw_end_time'];
                                $inventory_start_datetime = strtotime($inventory_start_date . ' ' . $inventory_start_time);
                                $inventory_end_datetime =  strtotime($inventory_end_date . ' ' . $inventory_end_time);
                                if($start_time && $end_time){
                                    $pickup_datetime = strtotime($date . ' ' . $start_time);
                                    $dropoff_datetime = strtotime($date . ' ' . $end_time);
                                    if(!(($inventory_start_datetime>$pickup_datetime && $inventory_start_datetime>$dropoff_datetime) || ($inventory_end_datetime<$pickup_datetime && $inventory_end_datetime<$dropoff_datetime))){
                                        $sold_item_qty += $rbfw_item_quantity;
                                        if (!empty($rbfw_service_info)) {
                                            foreach ($rbfw_service_info as $key => $service_info) {
                                                $sold_es_qty += $service_info;
                                            }
                                        }
                                    }
                                }else{
                                    $sold_item_qty += $rbfw_item_quantity;
                                    if (!empty($rbfw_service_info)) {
                                        foreach ($rbfw_service_info as $key => $service_info) {
                                            $sold_es_qty += (int)$service_info;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $remaining_item_stock = $rbfw_item_stock_quantity - (int)$sold_item_qty;
                    $remaining_es_stock = $total_es_qty - $sold_es_qty;
                }


                $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
                if(!is_array($rbfw_service_category_price)){
                    $rbfw_service_category_price = json_decode($rbfw_service_category_price, true);
                }

                $service_quantity = [];
                $service_stock = [];
                if (!empty($rbfw_service_category_price)) {
                    foreach($rbfw_service_category_price as $key=>$item1){
                        $cat_title = $item1['cat_title'];
                        $service_q = [];
                        foreach ($item1['cat_services'] as $key1=>$single){
                            if($single['title']){
                                $service_quantity[] = esc_html($single['stock_quantity']);
                                $service_q[] = array('date'=>$date,$single['title']=>total_service_quantity($cat_title,$single['title'],$date,$rbfw_inventory,$inventory_based_on_return,$start_time , $end_time ));
                                $service_stock[] = (int)$single['stock_quantity'] - max(array_column($service_q, $single['title']));
                            }
                        }
                    }
                }


                $cat_total = (float) array_sum( $service_quantity );
                $cat_stock = (float) array_sum( $service_stock );
                $cat_sold  = $cat_total - $cat_stock;

                $item_state = rbfw_inv_stock_state( $remaining_item_stock, $rbfw_item_stock_quantity );
                $es_state   = rbfw_inv_stock_state( $remaining_es_stock, $total_es_qty );
                /* Category column stays neutral when no category stock is configured. */
                $cat_state  = $cat_total > 0 ? rbfw_inv_stock_state( $cat_stock, $cat_total ) : '';
                ?>
                <tr class="rbfw_inv_row">
                    <td class="rbfw_inv_td_date" data-th="<?php esc_attr_e('Date','booking-and-rental-manager-for-woocommerce'); ?>"><?php echo esc_html(gmdate(get_option('date_format'),strtotime($current_date))); ?></td>

                    <td class="rbfw_inv_td_name" data-th="<?php esc_attr_e('Item Name','booking-and-rental-manager-for-woocommerce'); ?>"><a href="<?php echo esc_url(admin_url('post.php?post='.$post_id.'&action=edit')); ?>" class="rbfw_item_title"><?php echo esc_html(get_the_title()); ?></a></td>

                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Item Stock','booking-and-rental-manager-for-woocommerce'); ?>">
                        <span class="rbfw_inv_stock_wrap">
                            <span class="rbfw_inv_pill <?php echo esc_attr( $item_state ); ?>"><?php echo esc_html( $remaining_item_stock ); ?>/<?php echo esc_html( $rbfw_item_stock_quantity ); ?></span>
                            <a
                                class="rbfw_inv_view_btn rbfw_stock_view_details"
                                data-request="closing"
                                data-date="<?php echo esc_attr( $current_date ); ?>"
                                data-id="<?php echo esc_attr( get_the_ID() ); ?>"
                            >
                                <?php esc_html_e( 'View Details', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </a>
                        </span>
                    </td>


                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Item Sold Qty','booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw_inv_qty_badge <?php echo esc_attr( $sold_item_qty > 0 ? 'rbfw_inv_qty_pos' : 'rbfw_inv_qty_zero' ); ?>"><?php echo esc_html($sold_item_qty); ?></span></td>
                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Extra Service Stock','booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw_inv_pill <?php echo esc_attr( $es_state ); ?>"><?php echo esc_html($remaining_es_stock); ?>/<?php echo esc_html($total_es_qty); ?></span></td>
                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Extra Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw_inv_qty_badge <?php echo esc_attr( $sold_es_qty > 0 ? 'rbfw_inv_qty_pos' : 'rbfw_inv_qty_zero' ); ?>"><?php echo esc_html($sold_es_qty); ?></span></td>
                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Category Service','booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw_inv_pill <?php echo esc_attr( $cat_state ); ?>"><?php echo esc_html( $cat_stock ); ?>/<?php echo esc_html( $cat_total ); ?></span></td>
                    <td class="rbfw_text_center" data-th="<?php esc_attr_e('Category Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw_inv_qty_badge <?php echo esc_attr( $cat_sold > 0 ? 'rbfw_inv_qty_pos' : 'rbfw_inv_qty_zero' ); ?>"><?php echo esc_html( $cat_sold ); ?></span></td>
                </tr>
                <?php
            }
        }else{
            ?>
            <tr class="rbfw_inv_empty_tr">
                <td colspan="20" class="rbfw_inv_empty_cell"><?php esc_html_e( 'Sorry, No data found!', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
        ?>
        </tbody>
    </table>
    <?php
    $content = ob_get_clean();
    return $content;
}



function rbfw_get_stock_by_filter(){

    check_ajax_referer( 'rbfw_get_stock_by_filter_action', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Unauthorized access', 403 );
    }

        $selected_date = isset($_POST['selected_date'])?sanitize_text_field(wp_unslash($_POST['selected_date'])):'';
        $start_date = isset($_POST['start_date'])?sanitize_text_field(wp_unslash($_POST['start_date'])):'';
        $end_date = isset($_POST['end_date'])?sanitize_text_field(wp_unslash($_POST['end_date'])):'';

        $args = array(
            'post_type'              => 'rbfw_item',
            'order'                  => 'DESC',
            'posts_per_page'         => -1,
            'update_post_meta_cache' => true,
            'update_term_meta_cache' => true,
        );
        $query = new WP_Query( $args );
        $content = rbfw_inventory_page_table($query, $selected_date,$start_date,$end_date);
        echo wp_kses($content,rbfw_allowed_html());
        wp_die();

}

function rbfw_get_stock_details(){

    /*if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
        return;
    }*/

    check_ajax_referer( 'rbfw_get_stock_details_action', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Unauthorized access', 403 );
    }

            $data_request = isset($_POST['data_request'])?sanitize_text_field(wp_unslash($_POST['data_request'])):'';
            $data_date = isset($_POST['data_date'])?sanitize_text_field(wp_unslash($_POST['data_date'])):'';
            $data_id = isset($_POST['data_id'])?sanitize_text_field(wp_unslash($_POST['data_id'])):'';
            $inventory_based_on_return = rbfw_get_option('inventory_based_on_pickup_return','rbfw_basic_gen_settings');
            $_raw = get_post_meta( $data_id, 'rbfw_item_type', true );
            $rent_type = ! empty( $_raw ) ? $_raw : '';
            $_raw = get_post_meta( $data_id, 'rbfw_enable_variations', true );
            $rbfw_enable_variations = ! empty( $_raw ) ? $_raw : 'no';
            $_raw = get_post_meta( $data_id, 'rbfw_variations_data', true );
            $rbfw_variations_data = ! empty( $_raw ) ? $_raw : [];
            $_raw = get_post_meta( $data_id, 'rbfw_resort_room_data', true );
            $rbfw_resort_room_data = ! empty( $_raw ) ? $_raw : [];
            $_raw = get_post_meta( $data_id, 'rbfw_bike_car_sd_data', true );
            $rbfw_bike_car_sd_data = ! empty( $_raw ) ? $_raw : [];
            $_raw = get_post_meta( $data_id, 'rbfw_extra_service_data', true );
            $rbfw_extra_service_data = ! empty( $_raw ) ? $_raw : [];
            $total_es_qty = 0;


            foreach ($rbfw_extra_service_data as $key => $extra_service_data) {

                $total_es_qty += !empty($extra_service_data['service_qty']) ? $extra_service_data['service_qty'] : 0;
            }

            $rbfw_item_stock_quantity = 0;

            if ($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){

                foreach ($rbfw_bike_car_sd_data as $key => $bike_car_sd_data) {

                    $rbfw_item_stock_quantity += !empty($bike_car_sd_data['qty']) ? $bike_car_sd_data['qty'] : 0;
                }

            } elseif ($rent_type == 'resort'){
                foreach ($rbfw_resort_room_data as $key => $resort_room_data) {
                    $rbfw_item_stock_quantity += !empty($resort_room_data['rbfw_room_available_qty']) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                }
            } else {
                if($rbfw_enable_variations=='yes'){
                    foreach ($rbfw_variations_data as $_variations_data) {
                        if(!empty($_variations_data['value'])){
                            foreach ($_variations_data['value'] as $value) {
                                if(empty($value['quantity']) || $value['quantity'] <= 0){
                                  ////
                                } else{
                                    $rbfw_item_stock_quantity =  $value['quantity'] + $rbfw_item_stock_quantity;
                                }
                            }
                        }
                    }
                }else{
                    $rbfw_item_stock_quantity = !empty(get_post_meta($data_id, 'rbfw_item_stock_quantity', true)) ? get_post_meta($data_id, 'rbfw_item_stock_quantity', true) : 0;
                }
            }

            $remaining_item_stock = $rbfw_item_stock_quantity;
            $sold_item_qty = 0;

            if($data_request == 'closing'){

                $rbfw_inventory =  get_post_meta($data_id, 'rbfw_inventory', true);

                if(!empty($rbfw_inventory)){

                    $rbfw_resort_room_data_closing = $rbfw_resort_room_data;
                    $rbfw_bike_car_sd_data_closing = $rbfw_bike_car_sd_data;
                    $rbfw_extra_service_data_closing = $rbfw_extra_service_data;
                    $rbfw_variations_data_closing = $rbfw_variations_data;
                    $mepp_reduce_stock_closing        = get_option( 'mepp_reduce_stock', 'full' );
                    $inv_managed_order_status_closing = rbfw_get_option( 'inventory_managed_order_status', 'rbfw_basic_gen_settings' );
                    $inv_managed_order_status_closing = is_array( $inv_managed_order_status_closing ) ? $inv_managed_order_status_closing : [
                        'processing' => 'processing',
                        'completed'  => 'completed',
                    ];

                    foreach ($rbfw_inventory as $key => $inventory) {

                        $partial_stock = true;
                        if($inventory['rbfw_order_status'] == 'partially-paid' && $mepp_reduce_stock_closing=='deposit'){
                            $partial_stock = false;
                        }

                        $inventory_managed_order_status = $inv_managed_order_status_closing;
                        $checkValues = $inventory['rbfw_order_status'];

                        if ( in_array($data_date, $inventory['booked_dates']) && (in_array($checkValues, $inventory_managed_order_status) || $inventory['rbfw_order_status'] == 'picked') && $partial_stock ){

                            $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                            $rbfw_variation_info = !empty($inventory['rbfw_variation_info']) ? $inventory['rbfw_variation_info'] : [];
                            $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];
                            $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;

                            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment' || $rent_type == 'resort') {
                                if (!empty($rbfw_type_info)) {
                                    foreach ($rbfw_type_info as $name => $qty) {
                                        $sold_item_qty += $qty;
                                    }
                                }
                                $i = 0;
                                foreach ($rbfw_resort_room_data_closing as $key => $resort_room_data) {
                                    $type_name = $rbfw_resort_room_data_closing[$i]['room_type'];
                                    $type_qty =$rbfw_resort_room_data_closing[$i]['rbfw_room_available_qty'];
                                    if (!empty($rbfw_type_info)) {
                                        foreach ($rbfw_type_info as $name => $qty) {
                                            if ($name == $type_name) {
                                                $rbfw_resort_room_data_closing[$i]['rbfw_room_available_qty'] = $type_qty - $qty;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                                $c = 0;
                                foreach ($rbfw_bike_car_sd_data_closing as $key => $bike_car_sd_data) {
                                    $type_name = $rbfw_bike_car_sd_data_closing[$c]['rent_type'];
                                    $type_qty =$rbfw_bike_car_sd_data_closing[$c]['qty'];
                                    if (!empty($rbfw_type_info)) {
                                        foreach ($rbfw_type_info as $name => $qty) {
                                            if ($name == $type_name) {
                                                $rbfw_bike_car_sd_data_closing[$c]['qty'] = $type_qty - $qty;
                                            }
                                        }
                                    }
                                    $c++;
                                }
                            } else {

                                $sold_item_qty += $rbfw_item_quantity;
                                $f = 0;
                                foreach ($rbfw_variations_data_closing as $key => $v_data) {
                                    $field_id = $rbfw_variations_data_closing[$f]['field_id'];
                                    $field_label = $rbfw_variations_data_closing[$f]['field_label'];
                                    $field_value = $rbfw_variations_data_closing[$f]['value'];

                                    if(!empty($rbfw_variation_info)){
                                        foreach ($rbfw_variation_info as $key => $v_info) {
                                            $s_field_id = $v_info['field_id'];
                                            $s_field_label = $v_info['field_label'];
                                            $s_field_value = $v_info['field_value'];
                                            if($s_field_id == $field_id){
                                                $g = 0;
                                                foreach ($field_value as $key => $f_value) {

                                                    $fv_name = $f_value['name'];
                                                    $fv_qty = $f_value['quantity'];

                                                    if ($s_field_value == $fv_name) {
                                                        $rbfw_variations_data_closing[$f]['value'][$g]['quantity'] = $fv_qty - $rbfw_item_quantity;
                                                    }
                                                    $g++;
                                                }
                                            }
                                        }
                                    }
                                    $f++;
                                }
                            }
                            $d = 0;
                            foreach ($rbfw_extra_service_data_closing as $key => $extra_service_data) {
                                $es_name = $rbfw_extra_service_data_closing[$d]['service_name'];
                                $es_qty =$rbfw_extra_service_data_closing[$d]['service_qty'];
                                if (!empty($rbfw_service_info)) {
                                    foreach ($rbfw_service_info as $name => $qty) {
                                        if ($name == $es_name) {
                                            $rbfw_extra_service_data_closing[$d]['service_qty'] = $es_qty - $qty;
                                        }
                                    }
                                }
                                $d++;
                            }
                        }
                    }



                    $remaining_item_stock = (float)$rbfw_item_stock_quantity - (float)$sold_item_qty;
                    $rbfw_resort_room_data = $rbfw_resort_room_data_closing;
                    $rbfw_bike_car_sd_data = $rbfw_bike_car_sd_data_closing;
                    $rbfw_extra_service_data = $rbfw_extra_service_data_closing;
                    $rbfw_variations_data = $rbfw_variations_data_closing;
                }


            }

            $modal_title    = get_the_title( $data_id );
            $modal_date_fmt = $data_date ? gmdate( get_option( 'date_format' ), strtotime( $data_date ) ) : '';
            $avail_zero     = ( empty( $remaining_item_stock ) || $remaining_item_stock <= 0 );
            ?>
            <div class="rbfw_inv_modal">
                <div class="rbfw_inv_modal_head">
                    <div class="rbfw_inv_modal_icon"><?php echo rbfw_inv_icon('box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                    <div class="rbfw_inv_modal_title_wrap">
                        <div class="rbfw_inv_modal_title"><?php echo esc_html( $modal_title ); ?></div>
                        <?php if ( $modal_date_fmt ) { ?>
                            <div class="rbfw_inv_modal_sub"><?php echo esc_html( $modal_date_fmt ); ?></div>
                        <?php } ?>
                    </div>
                    <a href="#" class="rbfw_inv_modal_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon('x'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                </div>

                <div class="rbfw_inv_modal_body">

                    <!-- Available qty hero -->
                    <div class="rbfw_inv_avail_hero">
                        <div>
                            <div class="rbfw_inv_avail_label"><?php esc_html_e('Available Quantity','booking-and-rental-manager-for-woocommerce'); ?></div>
                            <div class="rbfw_inv_avail_qty <?php echo esc_attr( $avail_zero ? 'rbfw_inv_avail_zero' : '' ); ?>"><?php echo esc_html( $remaining_item_stock ); ?></div>
                        </div>
                        <?php echo rbfw_inv_icon('layers','rbfw_inv_avail_icon'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                    </div>

                    <?php if(!empty($rbfw_resort_room_data) && $rent_type == 'resort'){ ?>
                    <div class="rbfw_inv_modal_section">
                        <div class="rbfw_inv_section_label"><?php echo rbfw_inv_icon('bed'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Room Info','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <table class="rbfw_inv_mini_table">
                            <thead><tr><th><?php esc_html_e('Room Type','booking-and-rental-manager-for-woocommerce'); ?></th><th class="rbfw_inv_ta_r"><?php esc_html_e('Available Qty','booking-and-rental-manager-for-woocommerce'); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($rbfw_resort_room_data as $resort_room_data) { ?>
                                <tr><td><?php echo esc_html($resort_room_data['room_type']); ?></td><td class="rbfw_inv_qty_cell"><?php echo esc_html($resort_room_data['rbfw_room_available_qty']); ?></td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>

                    <?php if(!empty($rbfw_bike_car_sd_data) && ($rent_type == 'bike_car_sd' || $rent_type == 'appointment')){ ?>
                    <div class="rbfw_inv_modal_section">
                        <div class="rbfw_inv_section_label"><?php echo rbfw_inv_icon('car'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Rent Info','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <table class="rbfw_inv_mini_table">
                            <thead><tr><th><?php esc_html_e('Rent Type','booking-and-rental-manager-for-woocommerce'); ?></th><th class="rbfw_inv_ta_r"><?php esc_html_e('Available Qty','booking-and-rental-manager-for-woocommerce'); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($rbfw_bike_car_sd_data as $bike_car_sd_data) { ?>
                                <tr><td><?php echo esc_html($bike_car_sd_data['rent_type']); ?></td><td class="rbfw_inv_qty_cell"><?php echo esc_html($bike_car_sd_data['qty']); ?></td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>

                    <?php if($rbfw_enable_variations == 'yes' && !empty($rbfw_variations_data) && $rent_type != 'resort' && $rent_type != 'bike_car_sd' && $rent_type != 'appointment'){ ?>
                        <?php foreach ($rbfw_variations_data as $_variations_data) { ?>
                        <div class="rbfw_inv_modal_section">
                            <div class="rbfw_inv_section_label"><?php echo rbfw_inv_icon('clone'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php echo esc_html( $_variations_data['field_label'] ); ?></div>
                            <?php if(!empty($_variations_data['value'])){ ?>
                            <table class="rbfw_inv_mini_table">
                                <thead><tr><th><?php esc_html_e('Name','booking-and-rental-manager-for-woocommerce'); ?></th><th class="rbfw_inv_ta_r"><?php esc_html_e('Available Qty','booking-and-rental-manager-for-woocommerce'); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($_variations_data['value'] as $value) {
                                    $v_zero = ( empty( $value['quantity'] ) || $value['quantity'] <= 0 ); ?>
                                    <tr><td><?php echo esc_html($value['name']); ?></td><td class="rbfw_inv_qty_cell <?php echo esc_attr( $v_zero ? 'rbfw_inv_qty_cell_zero' : '' ); ?>"><?php echo esc_html( $value['quantity'] ); ?></td></tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    <?php } ?>

                    <!-- Extra services -->
                    <div class="rbfw_inv_modal_section">
                        <div class="rbfw_inv_section_label"><?php echo rbfw_inv_icon('sparkles'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Extra Services','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <table class="rbfw_inv_mini_table">
                            <thead><tr><th><?php esc_html_e('Service Name','booking-and-rental-manager-for-woocommerce'); ?></th><th class="rbfw_inv_ta_r"><?php esc_html_e('Available Qty','booking-and-rental-manager-for-woocommerce'); ?></th></tr></thead>
                            <tbody>
                            <?php if(!empty($rbfw_extra_service_data)){
                                foreach ($rbfw_extra_service_data as $extra_service_data) { ?>
                                <tr><td><?php echo esc_html($extra_service_data['service_name']); ?></td><td class="rbfw_inv_qty_cell"><?php echo esc_html($extra_service_data['service_qty']); ?></td></tr>
                            <?php }
                            } else { ?>
                                <tr><td colspan="2" class="rbfw_inv_empty_modal"><?php esc_html_e('No extra services available','booking-and-rental-manager-for-woocommerce'); ?></td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Category wise -->
                    <?php
                    $rbfw_service_category_price = get_post_meta($data_id, 'rbfw_service_category_price', true);
                    if(!is_array($rbfw_service_category_price)){
                        $rbfw_service_category_price = json_decode($rbfw_service_category_price, true);
                    }
                    ?>
                    <div class="rbfw_inv_modal_section">
                        <div class="rbfw_inv_section_label"><?php echo rbfw_inv_icon('tag'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e('Category Wise Service','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <?php if (!empty($rbfw_service_category_price)) {
                            foreach($rbfw_service_category_price as $key=>$item1){
                                $cat_title = $item1['cat_title'];
                                ?>
                                <div class="rbfw_inv_cat_block">
                                    <div class="rbfw_inv_cat_title"><?php echo rbfw_inv_icon('tag'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php echo esc_html($cat_title); ?></div>
                                    <table class="rbfw_inv_mini_table">
                                        <tbody>
                                        <?php
                                        $service_q = [];
                                        foreach ($item1['cat_services'] as $key1=>$single){
                                            if($single['title']){
                                                $service_q[] = array('date'=>$data_date,$single['title']=>total_service_quantity($cat_title,$single['title'],$data_date,$rbfw_inventory,$inventory_based_on_return));
                                                ?>
                                                <tr><td><?php echo esc_html($single['title']); ?></td><td class="rbfw_inv_qty_cell"><?php echo esc_html($single['stock_quantity'] - max(array_column($service_q, $single['title']))); ?></td></tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php }
                        } else { ?>
                            <div class="rbfw_inv_empty_modal_note"><?php esc_html_e('No category services configured','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <?php } ?>
                    </div>

                </div><!-- /.rbfw_inv_modal_body -->
            </div><!-- /.rbfw_inv_modal -->
            <?php
            wp_die();
        }

