<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter('woocommerce_add_cart_item_data', 'rbfw_add_info_to_cart_item', 90, 3);
add_action('woocommerce_before_calculate_totals', 'rbfw_set_new_cart_price', 90, 1);
add_filter('woocommerce_get_item_data', 'rbfw_show_cart_items', 90, 2);


/*after place order*/
add_action('woocommerce_after_checkout_validation', 'rbfw_validation_before_checkout');
add_action('woocommerce_checkout_create_order_line_item', 'rbfw_add_order_item_data', 90, 4);
add_action( 'woocommerce_before_thankyou', 'rbfw_booking_management', 10 );
/*order status change from woocommerse order or rbfw order list*/
add_action( 'rbfw_wc_order_status_change', 'rbfw_change_user_order_status_on_order_status_change', 10, 3 );

/*add_filter( 'woocommerce_billing_fields', 'wc_unrequire_wc_phone_field');
function wc_unrequire_wc_phone_field( $fields ) {
    $fields['billing_phone']['required'] = false;
    return $fields;
}*/

function rbfw_add_info_to_cart_item($cart_item_data, $product_id, $variation_id)
{
    global $rbfw;
    $linked_rbfw_id   = get_post_meta($product_id, 'link_rbfw_id', true) ? get_post_meta($product_id, 'link_rbfw_id', true) : $product_id;
    $product_id       = rbfw_check_product_exists($linked_rbfw_id) ? $linked_rbfw_id : $product_id;
    if (get_post_type($product_id) == $rbfw->get_cpt_name()) {
        $cart_item_data = rbfw_add_cart_item_func(  $cart_item_data,$product_id );
    }
    $cart_item_data['rbfw_id'] = $product_id;
    return $cart_item_data;
}


function rbfw_add_cart_item_func( $cart_item_data, $rbfw_id )
{

    $rbfw_rent_type = get_post_meta($rbfw_id, 'rbfw_item_type', true);
    $rbfw_item_quantity = isset($_POST['rbfw_item_quantity']) ? $_POST['rbfw_item_quantity'] : 1;
    $rbfw_service_info_all = isset($_POST['rbfw_service_info']) ? rbfw_array_strip($_POST['rbfw_service_info']) : [];

    $rbfw_service_info = array();
    $rbfw_enable_extra_service_qty = get_post_meta($rbfw_id, 'rbfw_enable_extra_service_qty', true) ? get_post_meta($rbfw_id, 'rbfw_enable_extra_service_qty', true) : 'no';
    $c = 0;
    if (!empty($rbfw_service_info_all)) {
        foreach ($rbfw_service_info_all as $key => $value) {
            $service_name = !empty($_POST['rbfw_service_info'][$c]['service_name']) ? $_POST['rbfw_service_info'][$c]['service_name'] : '';
            $service_qty = !empty($_POST['rbfw_service_info'][$c]['service_qty']) ? $_POST['rbfw_service_info'][$c]['service_qty'] : 0;
            if ($rbfw_item_quantity > 1 && $service_qty == 1 && $rbfw_enable_extra_service_qty != 'yes') {
                $service_qty = $rbfw_item_quantity;
            }
            if ($service_qty > 0) {
                $rbfw_service_info[$service_name] = $service_qty;
            }
            $c++;
        }
    }


    $discount_type = '';
    $discount_amount = 0;

    $rbfw_regf_info = [];
    if (class_exists('Rbfw_Reg_Form')) {
        $ClassRegForm = new Rbfw_Reg_Form();
        $rbfw_regf_info = $ClassRegForm->rbfw_regf_value_array_function($rbfw_id);
    }



    $cart_item_data['rbfw_id'] = $rbfw_id;


    if($rbfw_rent_type == 'resort') {

        $rbfw_resort = new RBFW_Resort_Function();
        $rbfw_checkin_datetime = isset($_POST['rbfw_start_datetime']) ? strip_tags($_POST['rbfw_start_datetime']) : '';
        $rbfw_checkout_datetime = isset($_POST['rbfw_end_datetime']) ? strip_tags($_POST['rbfw_end_datetime']) : '';
        $rbfw_room_price_category = isset($_POST['rbfw_room_price_category']) ? rbfw_array_strip($_POST['rbfw_room_price_category']) : '';
        $rbfw_room_info_all = isset($_POST['rbfw_room_info']) ? rbfw_array_strip($_POST['rbfw_room_info']) : [];


        $rbfw_room_info = array();
        $i = 0;
        foreach ($rbfw_room_info_all as $key => $value) {
            $room_type = $_POST['rbfw_room_info'][$i]['room_type'];
            $room_qty = $_POST['rbfw_room_info'][$i]['room_qty'];
            if (!empty($room_qty)) {
                $rbfw_room_info[$room_type] = $room_qty;
            }
            $i++;
        }
    
        $rbfw_room_duration_price = $rbfw_resort->rbfw_resort_price_calculation($rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info, 'rbfw_room_duration_price');
        $rbfw_room_service_price =  $rbfw_resort->rbfw_resort_price_calculation($rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info, 'rbfw_room_service_price');
        $rbfw_room_total_price =    $rbfw_resort->rbfw_resort_price_calculation($rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info, 'rbfw_room_total_price');



        $origin             = date_create($rbfw_checkin_datetime);
        $target             = date_create($rbfw_checkout_datetime);
        $interval           = date_diff($origin, $target);
        $total_days         = $interval->format('%a');

        if (function_exists('rbfw_get_discount_array')) {
            $discount_arr = rbfw_get_discount_array($rbfw_id, $total_days, $rbfw_room_total_price);
        } else {
            $discount_arr = [];
        }

        if (!empty($discount_arr)) {
            $rbfw_room_total_price = $discount_arr['total_amount'];
            $discount_type = $discount_arr['discount_type'];
            $discount_amount = $discount_arr['discount_amount'];
        }





        $rbfw_resort_ticket_info = $rbfw_resort->rbfw_resort_ticket_info($rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info, $rbfw_regf_info);




        $base_price = $rbfw_room_total_price;
        $total_price = apply_filters('rbfw_cart_base_price', $base_price);

        $security_deposit = rbfw_security_deposit($rbfw_id,$total_price);
        $total_price = $total_price + $security_deposit['security_deposit_amount'];

        $start_date = $rbfw_checkin_datetime;
        $end_date = $rbfw_checkout_datetime;
        $cart_item_data['rbfw_start_datetime'] = $rbfw_checkin_datetime;
        $cart_item_data['rbfw_end_datetime'] = $rbfw_checkout_datetime;
        $cart_item_data['rbfw_start_date'] = $rbfw_checkin_datetime;
        $cart_item_data['rbfw_start_time'] = '';
        $cart_item_data['rbfw_end_date'] = $rbfw_checkout_datetime;
        $cart_item_data['rbfw_end_time'] = '';
        $cart_item_data['rbfw_room_price_category'] = $rbfw_room_price_category;
        $cart_item_data['rbfw_room_info'] = $rbfw_room_info;
        $cart_item_data['rbfw_type_info'] = $rbfw_room_info;
        $cart_item_data['rbfw_service_info'] = $rbfw_service_info;
        $cart_item_data['rbfw_room_duration_price'] = $rbfw_room_duration_price;
        $cart_item_data['rbfw_room_service_price'] = $rbfw_room_service_price;
        $cart_item_data['rbfw_ticket_info'] = $rbfw_resort_ticket_info;
        $cart_item_data['discount_type'] = $discount_type;
        $cart_item_data['discount_amount'] = $discount_amount;
        $cart_item_data['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
        $cart_item_data['security_deposit_desc'] = $security_deposit['security_deposit_desc'];

    }elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') {


        $rbfw_bikecarsd = new RBFW_BikeCarSd_Function();
        $rbfw_bikecarsd_selected_date = isset($_POST['rbfw_bikecarsd_selected_date']) ? rbfw_array_strip($_POST['rbfw_bikecarsd_selected_date']) : '';
        $bikecarsd_selected_date = isset($_POST['rbfw_bikecarsd_selected_date']) ? rbfw_array_strip($_POST['rbfw_bikecarsd_selected_date']) : '';
        $rbfw_bikecarsd_selected_time = isset($_POST['rbfw_bikecarsd_selected_time']) ? rbfw_array_strip($_POST['rbfw_bikecarsd_selected_time']) : '';


        $rbfw_start_datetime = $rbfw_bikecarsd_selected_date;
        $rbfw_end_datetime = $rbfw_bikecarsd_selected_date;
        $rbfw_type_info_all = isset($_POST['rbfw_bikecarsd_info']) ? rbfw_array_strip($_POST['rbfw_bikecarsd_info']) : [];
        $rbfw_type_info = array();

        $date_to_string = new DateTime($rbfw_bikecarsd_selected_date);


        $a = 1;
        foreach ($rbfw_type_info_all as $key => $value) {
            if (!empty($_POST['rbfw_bikecarsd_info'][$a]['rent_type'])) {
                $rent_type = $_POST['rbfw_bikecarsd_info'][$a]['rent_type'];
                $rent_qty = $_POST['rbfw_bikecarsd_info'][$a]['qty'];
                if (!empty($rent_qty) && $rent_qty > 0) {
                    $rbfw_type_info[$rent_type] = $rent_qty;
                }
            }
            $a++;
        }

        $rbfw_bikecarsd_duration_price = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info, $rbfw_service_info, 'rbfw_bikecarsd_duration_price');
        $rbfw_bikecarsd_service_price = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info, $rbfw_service_info, 'rbfw_bikecarsd_service_price');
        $rbfw_bikecarsd_total_price = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info, $rbfw_service_info, 'rbfw_bikecarsd_total_price');
        $rbfw_pickup_point = isset($_POST['rbfw_pickup_point']) ? $_POST['rbfw_pickup_point'] : '';
        $rbfw_dropoff_point = isset($_POST['rbfw_dropoff_point']) ? $_POST['rbfw_dropoff_point'] : '';
        $rbfw_bikecarsd_ticket_info = $rbfw_bikecarsd->rbfw_bikecarsd_ticket_info($rbfw_id, $rbfw_start_datetime, $rbfw_end_datetime, $rbfw_type_info, $rbfw_service_info, $rbfw_bikecarsd_selected_time, $rbfw_regf_info,$rbfw_pickup_point,$rbfw_dropoff_point);




        $base_price = $rbfw_bikecarsd_total_price;
        $total_price = apply_filters('rbfw_cart_base_price', $base_price);
        $security_deposit = rbfw_security_deposit($rbfw_id,$total_price);
        $total_price = $total_price + $security_deposit['security_deposit_amount'];
        $start_date = $bikecarsd_selected_date;
        $end_date = $bikecarsd_selected_date;
        $cart_item_data['rbfw_pickup_point'] = $rbfw_pickup_point;
        $cart_item_data['rbfw_dropoff_point'] = $rbfw_dropoff_point;
        $cart_item_data['rbfw_start_datetime'] = $rbfw_start_datetime;
        $cart_item_data['rbfw_end_datetime'] = $rbfw_end_datetime;
        $cart_item_data['rbfw_start_date'] = $bikecarsd_selected_date;
        $cart_item_data['rbfw_start_time'] = $rbfw_bikecarsd_selected_time;
        $cart_item_data['rbfw_end_date'] = $bikecarsd_selected_date;
        $cart_item_data['rbfw_end_time'] = '';
        $cart_item_data['rbfw_type_info'] = $rbfw_type_info;
        $cart_item_data['rbfw_service_info'] = $rbfw_service_info;
        $cart_item_data['rbfw_bikecarsd_duration_price'] = $rbfw_bikecarsd_duration_price;
        $cart_item_data['rbfw_bikecarsd_service_price'] = $rbfw_bikecarsd_service_price;
        $cart_item_data['rbfw_ticket_info'] = $rbfw_bikecarsd_ticket_info;
        $cart_item_data['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
        $cart_item_data['security_deposit_desc'] = $security_deposit['security_deposit_desc'];

    }else {

        $start_date = ($_POST['wp_date_format']=='d/m/Y')?str_replace('/', '-', $_POST['rbfw_pickup_start_date']):$_POST['rbfw_pickup_start_date'];
        $end_date = ($_POST['wp_date_format']=='d/m/Y')?str_replace('/', '-', $_POST['rbfw_pickup_end_date']):$_POST['rbfw_pickup_end_date'];
        $start_time = isset($_POST['rbfw_pickup_start_time'])?$_POST['rbfw_pickup_start_time']:'00:00:00';
        $end_time = isset($_POST['rbfw_pickup_end_time'])?$_POST['rbfw_pickup_end_time']:rbfw_end_time();

        $pickup_datetime = date('Y-m-d H:i', strtotime($start_date . ' ' . $start_time));
        $dropoff_datetime = date('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));

        $rbfw_pickup_point = isset($_POST['rbfw_pickup_point']) ? $_POST['rbfw_pickup_point'] : '';
        $rbfw_dropoff_point = isset($_POST['rbfw_dropoff_point']) ? $_POST['rbfw_dropoff_point'] : '';

        $duration_price_info = rbfw_md_duration_price_calculation($rbfw_id,$pickup_datetime,$dropoff_datetime,$start_date,$end_date,$start_time,$end_time);

        $duration_price_individual = $duration_price_info['duration_price'];

        $duration_price = $duration_price_info['duration_price'] * $rbfw_item_quantity;

        $total_days = $duration_price_info['total_days'];
        $actual_days = $duration_price_info['actual_days'];
        $hours = $duration_price_info['hours'];

        /* service price start for multiple days */

        $rbfw_service_price = 0;
        $rbfw_service_infos_post = isset($_POST['rbfw_service_price_data']) ? rbfw_array_strip($_POST['rbfw_service_price_data']) : [];

        //echo '<pre>';print_r($rbfw_service_infos_post);echo '<pre>';exit;

        $rbfw_service_infos = [];
        if (!empty($rbfw_service_infos_post)) {
            foreach ($rbfw_service_infos_post as $key_cat => $value) {
                $rbfw_service_infos[$value['cat_title']] = [];
                foreach ($value as $key_ser => $item) {
                    if (isset($item['main_cat_name']) && $item['main_cat_name']) {
                        $rbfw_service_infos[$value['cat_title']][] = $item;
                        if($item['service_price_type']=='day_wise'){
                            $rbfw_service_price =  $rbfw_service_price+$item['price']*$item['quantity']*$total_days;
                        }else{
                            $rbfw_service_price = $rbfw_service_price+$item['price']*$item['quantity'];
                        }
                    }
                }
            }
        }


        $rbfw_service_infos_new = [];
        foreach ($rbfw_service_infos as $item_s){
            if(!empty($item_s)){
                $rbfw_service_infos_new = $rbfw_service_infos;
            }
        }

        $rbfw_service_infos = $rbfw_service_infos_new;


        $rbfw_service_price = $rbfw_service_price * $rbfw_item_quantity;


        /* service price end for multiple days */


        $rbfw_extra_service_price = 0;
        $rbfw_duration_price = $duration_price;

        $rbfw_extra_service_data = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : '';
        if (!empty($rbfw_extra_service_data)) {
            $extra_services = array_column($rbfw_extra_service_data, 'service_price', 'service_name');
        } else {
            $extra_services = array();
        }

        foreach ($rbfw_service_info as $key => $value) {
            $service_name = $key; //Service1
            if (array_key_exists($service_name, $extra_services)) { // if Service1 exist in array

                if ($rbfw_item_quantity > 1 && (int)$extra_services[$service_name] == 1 && $rbfw_enable_extra_service_qty != 'yes') {
                    $rbfw_extra_service_price += (int)$rbfw_item_quantity * (float)$value; // quantity * price
                } else {
                    $rbfw_extra_service_price += (int)$extra_services[$service_name] * (float)$value; // quantity * price
                }
            }
        }

        $variation_data = get_post_meta($rbfw_id, 'rbfw_variations_data', true);
        $variation_info = [];
        if (!empty($variation_data)) {
            $i = 0;
            foreach ($variation_data as $level_one_arr) {

                $selected_field_value = !empty($_POST[$level_one_arr['field_id']]) ? $_POST[$level_one_arr['field_id']] : [];

                $level_two_arr = $level_one_arr['value'];

                foreach ($level_two_arr as $level_two_arr_value) {
                    if ($selected_field_value == $level_two_arr_value['name']) {

                        $field_label = $level_one_arr['field_label'];
                        $field_id = $level_one_arr['field_id'];

                        $variation_info[$i]['field_id'] = $field_id;
                        $variation_info[$i]['field_label'] = $field_label;
                        $variation_info[$i]['field_value'] = $selected_field_value;
                    }
                }

                $i++;
            }
        }

        $sub_total_price = $rbfw_duration_price + $rbfw_service_price + $rbfw_extra_service_price;

        $discount_amount = 0;
        if (function_exists('rbfw_get_discount_array')) {
            $discount_arr = rbfw_get_discount_array($rbfw_id, $total_days, $sub_total_price);
        } else {
            $discount_arr = [];
        }
        if (!empty($discount_arr)) {
            $discount_type = $discount_arr['discount_type'];
            $discount_amount = $discount_arr['discount_amount'];
        }


        $security_deposit = rbfw_security_deposit($rbfw_id,$sub_total_price);

        $total_price = $sub_total_price + $security_deposit['security_deposit_amount'] - $discount_amount;






        $rbfw_ticket_info = rbfw_cart_ticket_info($rbfw_id, $start_date, $end_date, $start_time, $end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price+$rbfw_extra_service_price, $total_price, $rbfw_service_info, $variation_info, $discount_type, $discount_amount, $rbfw_regf_info,$rbfw_service_infos,$total_days,$security_deposit);
        $cart_item_data['rbfw_pickup_point'] = $rbfw_pickup_point;
        $cart_item_data['rbfw_dropoff_point'] = $rbfw_dropoff_point;
        $cart_item_data['rbfw_start_date'] = $start_date;
        $cart_item_data['rbfw_start_time'] = $start_time;
        $cart_item_data['rbfw_end_date'] = $end_date;
        $cart_item_data['rbfw_end_time'] = $end_time;

        $cart_item_data['rbfw_start_datetime'] = $pickup_datetime;
        $cart_item_data['rbfw_end_datetime'] = $dropoff_datetime;
        $cart_item_data['rbfw_item_quantity'] = $rbfw_item_quantity;
        $cart_item_data['rbfw_service_info'] = $rbfw_service_info;
        $cart_item_data['rbfw_service_infos'] = $rbfw_service_infos;
        $cart_item_data['rbfw_variation_info'] = $variation_info;
        $cart_item_data['rbfw_ticket_info'] = $rbfw_ticket_info;
        $cart_item_data['rbfw_duration_price_individual'] = $duration_price_individual;
        $cart_item_data['rbfw_duration_price'] = $rbfw_duration_price;
        $cart_item_data['rbfw_service_price'] = $rbfw_service_price+$rbfw_extra_service_price;
        $cart_item_data['discount_type'] = $discount_type;
        $cart_item_data['discount_amount'] = $discount_amount;
        $cart_item_data['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
        $cart_item_data['security_deposit_desc'] = $security_deposit['security_deposit_desc'];
        $cart_item_data['total_days'] = $total_days;

    }

    $cart_item_data['start_date']               = $start_date;
    $cart_item_data['end_date']                 = $end_date;
    $cart_item_data['rbfw_tp']                 = $total_price;
    $cart_item_data['line_total']              = $total_price;
    $cart_item_data['line_subtotal']           = $total_price;



    return apply_filters('rbfw_add_cart_function_after', $cart_item_data, $rbfw_id);
}


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


function rbfw_show_cart_items($item_data, $cart_item)
{
    global $rbfw;
    $rbfw_id  = array_key_exists('rbfw_id', $cart_item) ? $cart_item['rbfw_id'] : 0;

    ob_start();

    if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {

        include( RBFW_Function::get_template_path( 'cart_page.php' ) );

        //rbfw_show_cart_item_func($cart_item, $rbfw_id);
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


function rbfw_add_order_item_data($item, $cart_item_key, $values, $order)
{


    global $rbfw;
    $rbfw_id = array_key_exists('rbfw_id', $values) ? $values['rbfw_id'] : 0;
    if (get_post_type($rbfw_id) == $rbfw->get_cpt_name()) {
        rbfw_validate_add_order_item_func($values,$item, $rbfw_id);
    }
}


function rbfw_validate_add_order_item_func( $values, $item, $rbfw_id ) {









    global $rbfw;
    $rbfw_rent_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );




    /* Type: Resort */
    if($rbfw_rent_type == 'resort'){
        $item->add_meta_data( 'start_date', $values['start_date'] );
        $item->add_meta_data( 'end_date', $values['end_date']);
        $rbfw_start_datetime = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
        $rbfw_end_datetime = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
        $rbfw_room_price_category = $values['rbfw_room_price_category'] ? $values['rbfw_room_price_category'] : '';
        $rbfw_ticket_info = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];
        $rbfw_room_info = $values['rbfw_room_info'] ? $values['rbfw_room_info'] : [];
        $rbfw_type_info = $values['rbfw_type_info'] ? $values['rbfw_type_info'] : [];
        $rbfw_resort_room_data 	= get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) : array();

        if($rbfw_room_price_category == 'daynight'):
            $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daynight_rate','room_type');
        elseif($rbfw_room_price_category == 'daylong'):
            $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daylong_rate','room_type');
        else:
            $room_types = array();
        endif;

        $rbfw_service_info 			= $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
        $rbfw_extra_service_data 	= get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();

        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $rbfw_room_duration_price 	= $values['rbfw_room_duration_price'] ? $values['rbfw_room_duration_price'] : '';
        $rbfw_room_service_price 	= $values['rbfw_room_service_price'] ? $values['rbfw_room_service_price'] : '';

        $discount_type 	= $values['discount_type'] ? $values['discount_type'] : '';
        $discount_amount = $values['discount_amount'] ? $values['discount_amount'] : '';

        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_start_datetime));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_end_datetime));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_package', 'rbfw_basic_translation_settings', __('Package','booking-and-rental-manager-for-woocommerce')), $rbfw_room_price_category);



        if ( ! empty( $rbfw_room_info ) ):
            $resort_type_arr = [];
            foreach ($rbfw_room_info as $key => $value):
                $room_type = $key; //Type
                if(array_key_exists($room_type, $room_types)){ // if Type exist in array
                    $room_price = $room_types[$room_type]; // get type price from array
                    $room_qty = $value;
                    $total_price = (float)$room_price * (float)$room_qty;
                    $room_description = '';
                    foreach ($rbfw_resort_room_data as $resort_room_data) {
                        if($resort_room_data['room_type'] == $room_type){
                            $room_description = $resort_room_data['rbfw_room_desc']; // get type description from array
                        }
                    }

                    $resort_type_arr[]  = array(
                        $room_type => $room_qty
                    );
                    $room_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    $room_content .= '<tr>';
                    $room_content .= '<td style="border:1px solid #f5f5f5;">';
                    $room_content .= '<strong>'.$room_type.'</strong>';
                    $room_content .= '<br>';
                    $room_content .= '<span>'.$room_description.'</span>';
                    $room_content .= '</td>';
                    $room_content .= '<td style="border:1px solid #f5f5f5;">';
                    $room_content .= '('.wc_price($room_price).' x '.$room_qty.') = '.wc_price($total_price);
                    $room_content .= '</td>';
                    $room_content .= '</tr>';
                    $room_content .= '</table>';
                    if($room_qty > 0):
                        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_room_information', 'rbfw_basic_translation_settings', __('Room Information','booking-and-rental-manager-for-woocommerce')), $room_content );
                    endif;
                }

            endforeach;
        endif;

        $resort_service_arr = [];

        if ( ! empty( $rbfw_service_info ) ):

            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name
                if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                    $service_price = $extra_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty;
                    $resort_service_arr[]  = array(
                        $service_name => $service_qty
                    );
                    $room_service_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    $room_service_content .= '<tr>';
                    $room_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $room_service_content .= '<strong>'.$service_name.'</strong>';
                    $room_service_content .= '</td>';
                    $room_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $room_service_content .= '('.wc_price($service_price).' x '.$service_qty.') = '.wc_price($total_service_price);
                    $room_service_content .= '</td>';
                    $room_service_content .= '</tr>';
                    $room_service_content .= '</table>';

                    if($service_qty > 0):
                        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_room_service_information', 'rbfw_basic_translation_settings', __('Service Information','booking-and-rental-manager-for-woocommerce')), $room_service_content );
                    endif;
                }

            endforeach;
        endif;

        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_room_duration_price));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_room_service_price));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')), wc_price($discount_amount));

        $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
        $item->add_meta_data( '_rbfw_type_info', $resort_type_arr );
        $item->add_meta_data( '_rbfw_resort_package', $rbfw_room_price_category );
        $item->add_meta_data( '_rbfw_service_info', $resort_service_arr );
        $item->add_meta_data( '_rbfw_duration_cost', $rbfw_room_duration_price );
        $item->add_meta_data( '_rbfw_service_cost', $rbfw_room_service_price );
        $item->add_meta_data( '_rbfw_discount_type', $discount_type );
        $item->add_meta_data( '_rbfw_discount_amount', $discount_amount );

        if($values['security_deposit_desc']){
            $item->add_meta_data( (!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit'), $values['security_deposit_desc']);
        }
        /* End Type: Resort */

        /* Type: Bikecarsd */
    } elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') {

        $pickup_location  = $values['rbfw_pickup_point'] ? $values['rbfw_pickup_point'] : '';
        $dropoff_location = $values['rbfw_dropoff_point'] ? $values['rbfw_dropoff_point'] : '';

        $rbfw_start_datetime = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
        $rbfw_end_datetime = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
        $rbfw_start_date = $values['rbfw_start_date'] ? $values['rbfw_start_date'] : '';
        $rbfw_start_time = $values['rbfw_start_time'] ? $values['rbfw_start_time'] : '';
        $rbfw_end_date = $values['rbfw_end_date'] ? $values['rbfw_end_date'] : '';
        $rbfw_end_time = $values['rbfw_end_time'] ? $values['rbfw_end_time'] : '';
        $rbfw_ticket_info = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];
        $rbfw_type_info = $values['rbfw_type_info'] ? $values['rbfw_type_info'] : [];
        $rbfw_bikecarsd_data = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) : array();

        if(!empty($rbfw_bikecarsd_data)):
            $rent_types = array_column($rbfw_bikecarsd_data,'price','rent_type');
        else:
            $rent_types = array();
        endif;

        $rbfw_service_info 			= $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
        $rbfw_extra_service_data 	= get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();

        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $rbfw_bikecarsd_duration_price 	= $values['rbfw_bikecarsd_duration_price'] ? $values['rbfw_bikecarsd_duration_price'] : '';
        $rbfw_bikecarsd_service_price 	= $values['rbfw_bikecarsd_service_price'] ? $values['rbfw_bikecarsd_service_price'] : '';

        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings', __('Start Date and Time','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_start_datetime).' '.$rbfw_start_time);

        if ( ! empty( $pickup_location ) ) {

            $item->add_meta_data(rbfw_string_return('rbfw_text_pickup_location',__('Pickup Location','rbfw-pro')), $pickup_location );
        }

        if ( ! empty( $dropoff_location ) ) {

            $item->add_meta_data(rbfw_string_return('rbfw_text_dropoff_location',__('Drop-off Location','rbfw-pro')), $dropoff_location );
        }

        if ( ! empty( $rbfw_type_info ) ):
            $bikecarsd_type_arr = [];
            foreach ($rbfw_type_info as $key => $value):
                $rent_type = $key; //Type
                if(array_key_exists($rent_type, $rent_types)){ // if Type exist in array
                    $rent_price = $rent_types[$rent_type]; // get type price from array
                    $rent_qty = $value;
                    $total_price = (float)$rent_price * (float)$rent_qty;
                    $rent_description = '';
                    foreach ($rbfw_bikecarsd_data as $bikecarsd_data) {
                        if($bikecarsd_data['rent_type'] == $rent_type){
                            $rent_description = $bikecarsd_data['rent_type']; // get type description from array
                        }
                    }

                    $bikecarsd_type_arr[]  = array(
                        $rent_type => $rent_qty
                    );
                    $rent_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    $rent_content .= '<tr>';
                    $rent_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_content .= '<strong>'.$rent_type.'</strong>';
                    $rent_content .= '<br>';
                    $rent_content .= '<span>'.$rent_description.'</span>';
                    $rent_content .= '</td>';
                    $rent_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_content .= '('.wc_price($rent_price).' x '.$rent_qty.') = '.wc_price($total_price);
                    $rent_content .= '</td>';
                    $rent_content .= '</tr>';
                    $rent_content .= '</table>';
                    if($rent_qty > 0):
                        $item->add_meta_data(rbfw_string_return('rbfw_text_rent_information',__('Rent Information','rbfw-pro')), $rent_content );
                    endif;
                }

            endforeach;
        endif;

        $bikecarsd_service_arr = [];

        if ( ! empty( $rbfw_service_info ) ):

            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name
                if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                    $service_price = $extra_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty;
                    $bikecarsd_service_arr[]  = array(
                        $service_name => $service_qty
                    );
                    $rent_service_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    $rent_service_content .= '<tr>';
                    $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_service_content .= '<strong>'.$service_name.'</strong>';
                    $rent_service_content .= '</td>';
                    $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_service_content .= '('.wc_price($service_price).' x '.$service_qty.') = '.wc_price($total_service_price);
                    $rent_service_content .= '</td>';
                    $rent_service_content .= '</tr>';
                    $rent_service_content .= '</table>';

                    if($service_qty > 0):
                        $item->add_meta_data(rbfw_string_return('rbfw_text_extra_service_information',__('Extra Service Information','rbfw-pro')), $rent_service_content );
                    endif;
                }

            endforeach;
        endif;

        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_bikecarsd_duration_price));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_bikecarsd_service_price));

        $item->add_meta_data( '_rbfw_pickup_point', $pickup_location );
        $item->add_meta_data( '_rbfw_dropoff_point', $dropoff_location );
        $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
        $item->add_meta_data( '_rbfw_type_info', $bikecarsd_type_arr );
        $item->add_meta_data( '_rbfw_service_info', $bikecarsd_service_arr );
        $item->add_meta_data( '_rbfw_duration_cost', $rbfw_bikecarsd_duration_price );
        $item->add_meta_data( '_rbfw_service_cost', $rbfw_bikecarsd_service_price );
        if($values['security_deposit_desc']){
            $item->add_meta_data( (!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit'), $values['security_deposit_desc']);
        }
        /* End Type: Bikecarsd */

    } else {

        $item->add_meta_data( 'start_date', $values['start_date'] );
        $item->add_meta_data( 'end_date', $values['end_date']);

        $rbfw_extra_service_data 	= get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();

        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $variation_info = $values['rbfw_variation_info'] ? $values['rbfw_variation_info'] : [];
        $rbfw_service_info = $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
        $rbfw_service_infos = $values['rbfw_service_infos'] ? $values['rbfw_service_infos'] : [];
        $rbfw_ticket_info = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];


        if($values['rbfw_start_time'] && $values['rbfw_end_time']){
            $start_datetime = rbfw_get_datetime( $values['rbfw_start_datetime'], 'date-time-text' );
            $end_datetime = rbfw_get_datetime( $values['rbfw_end_datetime'], 'date-time-text' );
        }else{
            $start_datetime = $values['rbfw_start_datetime'] ? rbfw_get_datetime( $values['rbfw_start_datetime'], 'date-text' ) : '';
            $end_datetime = $values['rbfw_end_datetime'] ? rbfw_get_datetime( $values['rbfw_end_datetime'], 'date-text' ) : '';
        }

        $start_date_raw = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
        $end_date_raw = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
        $start_date = $values['rbfw_start_date'] ? $values['rbfw_start_date'] : '';
        $start_time = $values['rbfw_start_time'] ? $values['rbfw_start_time'] : '';
        $end_date = $values['rbfw_end_date'] ? $values['rbfw_end_date'] : '';
        $end_time = $values['rbfw_end_time'] ? $values['rbfw_end_time'] : '';
        $total_days = $values['total_days'] ? $values['total_days'] : '';

        $pickup_location  = $values['rbfw_pickup_point'] ? $values['rbfw_pickup_point'] : '';
        $dropoff_location = $values['rbfw_dropoff_point'] ? $values['rbfw_dropoff_point'] : '';

        $rbfw_item_quantity = $values['rbfw_item_quantity'] ? $values['rbfw_item_quantity'] : 1;
        $rbfw_duration_price = $values['rbfw_duration_price'] ? $values['rbfw_duration_price'] : '';
        $rbfw_service_price	= $values['rbfw_service_price'] ? $values['rbfw_service_price'] : '';
        $discount_type 	= $values['discount_type'] ? $values['discount_type'] : '';
        $discount_amount = $values['discount_amount'] ? $values['discount_amount'] : '';

        $item->add_meta_data( rbfw_string_return('rbfw_text_start_date_and_time',__('Start Date and Time','rbfw-pro')), $start_datetime );
        $item->add_meta_data( rbfw_string_return('rbfw_text_end_date_and_time',__('End Date and Time','rbfw-pro')), $end_datetime );

        if ( ! empty( $pickup_location ) ) {

            $item->add_meta_data(rbfw_string_return('rbfw_text_pickup_location',__('Pickup Location','rbfw-pro')), $pickup_location );
        }

        if ( ! empty( $dropoff_location ) ) {

            $item->add_meta_data(rbfw_string_return('rbfw_text_dropoff_location',__('Drop-off Location','rbfw-pro')), $dropoff_location );
        }

        if(!empty($variation_info)){
            $variation_content = '';
            $variation_content .= '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';

            foreach ($variation_info as $key => $value) {

                $variation_content .= '<tr>';
                $variation_content .= '<td style="border:1px solid #f5f5f5;"><strong>'.esc_html($value['field_label']).'</strong></td>';
                $variation_content .= '<td style="border:1px solid #f5f5f5;">'.esc_html($value['field_value']).'</td>';
                $variation_content .= '</tr>';

            }

            $variation_content .= '</table>';

            $item->add_meta_data(rbfw_string_return('rbfw_text_variation_information',__('Variation Information','rbfw-pro')), $variation_content );
        }

        $item->add_meta_data( rbfw_string_return('rbfw_text_item_quantity',__('Item Quantity','rbfw-pro')), $rbfw_item_quantity );

        $rbfw_service_infos_order = '';

        if ( ! empty( $rbfw_service_infos ) ){
            $rbfw_service_infos_order .='<table>';
            foreach ($rbfw_service_infos as $key => $item_parent){
                if(count($item_parent)){
                    $rbfw_service_infos_order .='<tr><th colspan="2" >'.$key.'</th></tr>';
                    foreach ($item_parent as $key1=>$item_child){
                        $rbfw_service_infos_order .='<tr><td>'.$item_child['name'].'</td><td>';
                        if($item_child['service_price_type']=='day_wise'){
                            $rbfw_service_infos_order .= '('.wc_price((float)$item_child['price']). 'x'. $item_child['quantity'] . 'x' .$total_days .'='.wc_price((float)$item_child['price']*(int)$item_child['quantity']*(int)$total_days).')';
                        }else{
                            $rbfw_service_infos_order .= '('.wc_price($item_child['price']). 'x'. $item_child['quantity'] .'='.wc_price((float)$item_child['price']*(int)$item_child['quantity']).')';
                        }
                        $rbfw_service_infos_order .='</td></tr>';
                    }
                }
            }
            $rbfw_service_infos_order .='</table>';
        }
        $item->add_meta_data( rbfw_string_return('rbfw_text_service_info',__('Service Info','booking-and-rental-manager-for-woocommerce')), $rbfw_service_infos_order );

        $bikecarmd_service_arr = [];
        if ( ! empty( $rbfw_service_info ) ):
            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name
                if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                    $service_price = $extra_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty;
                    $bikecarmd_service_arr[]  = array(
                        $service_name => $service_qty
                    );
                    $rent_service_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    $rent_service_content .= '<tr>';
                    $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_service_content .= '<strong>'.$service_name.'</strong>';
                    $rent_service_content .= '</td>';
                    $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                    $rent_service_content .= '('.wc_price($service_price).' x '.$service_qty.') = '.wc_price($total_service_price);
                    $rent_service_content .= '</td>';
                    $rent_service_content .= '</tr>';
                    $rent_service_content .= '</table>';
                    if($service_qty > 0):
                        $item->add_meta_data(rbfw_string_return('rbfw_text_extra_service_information',__('Extra Service Information','rbfw-pro')), $rent_service_content );
                    endif;
                }

            endforeach;
        endif;

        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_duration_price));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_service_price));
        $item->add_meta_data($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')), wc_price($discount_amount));

        $item->add_meta_data( '_rbfw_start_datetime', $start_date_raw );
        $item->add_meta_data( '_rbfw_end_datetime', $end_date_raw );
        $item->add_meta_data( '_rbfw_pickup_point', $pickup_location );
        $item->add_meta_data( '_rbfw_dropoff_point', $dropoff_location );
        $item->add_meta_data( '_rbfw_variation_info', $variation_info );
        $item->add_meta_data( '_rbfw_item_quantity', $rbfw_item_quantity );
        $item->add_meta_data( '_rbfw_service_info', $bikecarmd_service_arr );
        $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
        $item->add_meta_data( '_rbfw_duration_cost', $rbfw_duration_price );
        $item->add_meta_data( '_rbfw_service_cost', $rbfw_service_price );
        $item->add_meta_data( '_rbfw_discount_type', $discount_type );
        $item->add_meta_data( '_rbfw_discount_amount', $discount_amount );

        if($values['security_deposit_desc']){
            $item->add_meta_data( (!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit'), $values['security_deposit_desc']);
        }
    }

    $item->add_meta_data( '_rbfw_id', $rbfw_id );

    $rbfw_regf_info = isset($values['rbfw_regf_info']) ? $values['rbfw_regf_info'] : [];

    if ( ! empty( $rbfw_regf_info ) ){
        $rbfw_regf_info_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
        foreach ($rbfw_regf_info as $key => $value):

            $the_label = $value['label'];
            $the_value = $value['value'];

            if(is_array($the_value) && !empty($the_value)){
                $new_value = '';
                $i = 1;
                $count_value = count($the_value);

                foreach ($the_value as $val) {

                    if($i < $count_value){
                        $new_value .= $val.', ';
                    } else {
                        $new_value .= $val;
                    }
                    $i++;
                }
                $the_value = $new_value;
            }

            if(!empty($the_label) && !empty($the_value)){
                $rbfw_regf_info_content .= '<tr>';
                $rbfw_regf_info_content .= '<td style="border:1px solid #f5f5f5;">';
                $rbfw_regf_info_content .= '<strong>'.$the_label.'</strong>';
                $rbfw_regf_info_content .= '</td>';
                $rbfw_regf_info_content .= '<td style="border:1px solid #f5f5f5;">';
                $rbfw_regf_info_content .= $the_value;
                $rbfw_regf_info_content .= '</td>';
                $rbfw_regf_info_content .= '</tr>';
            }

        endforeach;
        $rbfw_regf_info_content .= '</table>';
        $item->add_meta_data(rbfw_string_return('rbfw_text_customer_information',__('Customer Information','booking-and-rental-manager-for-woocommerce')), $rbfw_regf_info_content );

    }

}

function rbfw_wc_price( $post_id, $price, $args = array() ) {
    global $rbfw;
    $display_suffex = get_option( 'woocommerce_price_display_suffix' ) ? get_option( 'woocommerce_price_display_suffix' ) : '';

    return wc_price( $rbfw->get_wc_raw_price( $post_id, $price, $args ) ) . ' ' . $display_suffex;
}


function rbfw_cart_ticket_info($product_id, $rbfw_pickup_start_date, $rbfw_pickup_end_date, $rbfw_pickup_start_time, $rbfw_pickup_end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price, $total_price, $rbfw_service_info, $variation_info, $discount_type = null, $discount_amount = null, $rbfw_regf_info = array(),$rbfw_service_infos=null,$total_days=0,$security_deposit=[]) {


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
                $ticket_type_arr[ $i ]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
                $ticket_type_arr[ $i ]['rbfw_regf_info'] = $rbfw_regf_info;
                $ticket_type_arr[ $i ]['rbfw_service_infos'] = $rbfw_service_infos;
                $ticket_type_arr[ $i ]['total_days'] = $total_days;
            }
        }
    }

    return $ticket_type_arr;

}


function rbfw_change_user_order_status_on_order_status_change( $order_status, $rbfw_id, $order_id ) {

    // Update meta on rbfw_order_meta post type

    rbfw_update_inventory_extra( $rbfw_id, $order_id,$order_status);

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
        rbfw_update_inventory($rbfw_post_id, $order_status);
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
        rbfw_update_inventory($rbfw_post_id, $order_status);
    }


}


function rbfw_booking_management( $order_id ) {


    global $rbfw;

    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    $order_status = $order->get_status();




    if ( $order_status != 'failed' ) {

        foreach ( $order->get_items() as $item_id => $item_values ) {

            $item_id = $item_id;

            $start_date = wc_get_order_item_meta( $item_id, 'start_date', true );

            $end_date = wc_get_order_item_meta( $item_id, 'end_date', true );
            $rbfw_service_price_data_actual = wc_get_order_item_meta( $item_id, '_rbfw_service_price_data_actual', true ) ? wc_get_order_item_meta( $item_id, '_rbfw_service_price_data_actual', true ) : [];


            $rbfw_id = rbfw_get_order_item_meta( $item_id, '_rbfw_id', true );



            if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {

                $ticket_info  = rbfw_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ) : [];
                $user_info    = rbfw_get_order_item_meta( $item_id, '_rbfw_user_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_user_info', true ) ) : [];
                $type_info    = rbfw_get_order_item_meta( $item_id, '_rbfw_type_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_type_info', true ) ) : [];
                $service_info = rbfw_get_order_item_meta( $item_id, '_rbfw_service_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_service_info', true ) ) : [];
                $rbfw_duration_cost = rbfw_get_order_item_meta( $item_id, '_rbfw_duration_cost', true ) ? rbfw_get_order_item_meta( $item_id, '_rbfw_duration_cost', true ) : '';
                $rbfw_service_cost = rbfw_get_order_item_meta( $item_id, '_rbfw_service_cost', true ) ? rbfw_get_order_item_meta( $item_id, '_rbfw_service_cost', true ) : '';

                rbfw_prepar_and_add_user_data( $ticket_info, $user_info, $rbfw_id, $order_id, $service_info, $rbfw_duration_cost, $rbfw_service_cost, $type_info,$start_date,$end_date,$rbfw_service_price_data_actual);
            }
        }
    }
}


function rbfw_prepar_and_add_user_data($ticket_info, $user_info, $rbfw_id, $order_id, $service_info = array(), $rbfw_duration_cost = null, $rbfw_service_cost = null, $type_info = array(), $start_date=null,$end_date=null,$rbfw_service_price_data_actual=array()) {

    global $rbfw;
    $rbfw_rent_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );

    $order          = wc_get_order( $order_id );
    $order_meta     = get_post_meta( $order_id );

    $billing_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
    $billing_email = $order->get_billing_email();
    $billing_phone = $order->get_billing_phone();
    $billing_address = $order->get_shipping_address_1().' '.$order->get_shipping_address_2();


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
            $zdata[ $key ]['discount_amount']         = isset($_ticket['discount_amount'])?$_ticket['discount_amount']:0;
            $zdata[ $key ]['rbfw_order_id']           = $order_id;
            $zdata[ $key ]['rbfw_order_status']       = $order_status;
            $zdata[ $key ]['rbfw_payment_method']     = $payment_method;
            $zdata[ $key ]['rbfw_user_id']            = $user_id;
            $zdata[ $key ]['rbfw_billing_name']       = $billing_name;
            $zdata[ $key ]['rbfw_billing_email']      = $billing_email;
            $zdata[ $key ]['rbfw_billing_phone']      = $billing_phone;
            $zdata[ $key ]['rbfw_billing_address']    = $billing_address;
            $zdata[ $key ]['start_date']              = $start_date;
            $zdata[ $key ]['end_date']                = $end_date;
            $zdata[ $key ]['rbfw_id']                 = $rbfw_id;

            $meta_data = array_merge($zdata[ $key ], $ticket_info, $user_info);

            /*rbfw_order add*/
            $order_id = $rbfw->rbfw_add_order_data($meta_data, $ticket_info,$rbfw_service_price_data_actual );
            /*rbfw_order_mata add and manage inventory*/
            $order_meta_id = rbfw_add_order_meta_data($meta_data, $ticket_info);

           if($order_id && $order_meta_id){
                update_post_meta($order_id, 'rbfw_order_status', $order_status);
                update_post_meta($order_meta_id, 'rbfw_order_status', $order_status);
            }

        }
    }

}