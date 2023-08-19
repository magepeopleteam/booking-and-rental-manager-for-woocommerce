<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'rbfw_add_cart_item', 'rbfw_add_cart_item_func', 10, 2 );
function rbfw_add_cart_item_func( $cart_item_data, $rbfw_id ) {
    $rbfw_rent_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );

    $rbfw_service_info_all  = isset( $_POST['rbfw_service_info'] ) ? rbfw_array_strip( $_POST['rbfw_service_info'] ) : [];
    $rbfw_service_info = array();
    $variation_info = [];
    $rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
    $rbfw_item_quantity     = isset( $_POST['rbfw_item_quantity'] ) ? $_POST['rbfw_item_quantity'] : 1;

    $c = 0;
    foreach ($rbfw_service_info_all as $key => $value) {
        $service_name = $_POST['rbfw_service_info'][$c]['service_name'];
        $service_qty  = $_POST['rbfw_service_info'][$c]['service_qty'];
        
        if($rbfw_item_quantity > 1 && $service_qty == 1 && $rbfw_enable_extra_service_qty != 'yes'){
            $service_qty = $rbfw_item_quantity;
        }

        if(!empty($service_qty)):
        $rbfw_service_info[$service_name] = $service_qty;
        endif;

        $c++;
    }

    /* Start Discount Calculations */
    $discount_type = '';
    $discount_amount = '';
    /* End Discount Calculations */

    /* Start: Get Registration Form Info */
    $rbfw_regf_info = [];

    if(class_exists('Rbfw_Reg_Form')){
        $ClassRegForm = new Rbfw_Reg_Form();
        $rbfw_regf_info = $ClassRegForm->rbfw_regf_value_array_function($rbfw_id);
    }
    /* End: Get Registration Form Info */

    /* Type: Resort */
    $rbfw_resort 				= new RBFW_Resort_Function();
    $rbfw_checkin_datetime        	= isset( $_POST['rbfw_start_datetime'] ) ? strip_tags( $_POST['rbfw_start_datetime'] ) : '';
    $rbfw_checkout_datetime     	= isset( $_POST['rbfw_end_datetime'] ) ? strip_tags( $_POST['rbfw_end_datetime'] ) : '';
    $start_date = $rbfw_checkin_datetime;
    $end_date = $rbfw_checkout_datetime;
    $rbfw_room_price_category 	= isset( $_POST['rbfw_room_price_category'] ) ? rbfw_array_strip( $_POST['rbfw_room_price_category'] ) : '';
    $rbfw_room_info_all  		= isset( $_POST['rbfw_room_info'] ) ? rbfw_array_strip( $_POST['rbfw_room_info'] ) : [];
    $rbfw_room_info      		= array();
    $i = 0;
    foreach ($rbfw_room_info_all as $key => $value) {
        $room_type = $_POST['rbfw_room_info'][$i]['room_type'];
        $room_qty  = $_POST['rbfw_room_info'][$i]['room_qty'];
        
        if(!empty($room_qty)):
        $rbfw_room_info[$room_type] = $room_qty;
        endif;

        $i++;
    }



    $rbfw_room_duration_price = $rbfw_resort->rbfw_resort_price_calculation($rbfw_id,$rbfw_checkin_datetime,$rbfw_checkout_datetime,$rbfw_room_price_category,$rbfw_room_info,$rbfw_service_info,'rbfw_room_duration_price');
    $rbfw_room_service_price = $rbfw_resort->rbfw_resort_price_calculation($rbfw_id,$rbfw_checkin_datetime,$rbfw_checkout_datetime,$rbfw_room_price_category,$rbfw_room_info,$rbfw_service_info,'rbfw_room_service_price');
    $rbfw_room_total_price  = $rbfw_resort->rbfw_resort_price_calculation($rbfw_id,$rbfw_checkin_datetime,$rbfw_checkout_datetime,$rbfw_room_price_category,$rbfw_room_info,$rbfw_service_info,'rbfw_room_total_price');

    
    if(function_exists('rbfw_get_discount_array')){

        $discount_arr = rbfw_get_discount_array($rbfw_id, $start_date, $end_date, $rbfw_room_total_price);

    } else {

        $discount_arr = [];
    }

    if(!empty($discount_arr)){
        $rbfw_room_total_price = $discount_arr['total_amount'];
        $discount_type = $discount_arr['discount_type'];
        $discount_amount = $discount_arr['discount_amount'];
    }

    $rbfw_resort_ticket_info = $rbfw_resort->rbfw_resort_ticket_info($rbfw_id,$rbfw_checkin_datetime,$rbfw_checkout_datetime,$rbfw_room_price_category,$rbfw_room_info,$rbfw_service_info, $rbfw_regf_info);
    /* End Type: Resort */

    /* Type: Bikecarsd */
    $rbfw_bikecarsd = new RBFW_BikeCarSd_Function();
    $rbfw_bikecarsd_selected_date  = isset( $_POST['rbfw_bikecarsd_selected_date'] ) ? rbfw_array_strip( $_POST['rbfw_bikecarsd_selected_date'] ) : '';
    $bikecarsd_selected_date  = isset( $_POST['rbfw_bikecarsd_selected_date'] ) ? rbfw_array_strip( $_POST['rbfw_bikecarsd_selected_date'] ) : '';
    $rbfw_bikecarsd_selected_time  = isset( $_POST['rbfw_bikecarsd_selected_time'] ) ? rbfw_array_strip( $_POST['rbfw_bikecarsd_selected_time'] ) : '';
    $rbfw_start_datetime  = $rbfw_bikecarsd_selected_date;
    $rbfw_end_datetime  = $rbfw_bikecarsd_selected_date;
    $rbfw_type_info_all  = isset( $_POST['rbfw_bikecarsd_info'] ) ? rbfw_array_strip( $_POST['rbfw_bikecarsd_info'] ) : [];
    $rbfw_type_info = array();

    $date_to_string = new DateTime($rbfw_bikecarsd_selected_date);
    $rbfw_bikecarsd_selected_date = $date_to_string->format('F j, Y');
    
    $a = 1;

    foreach ($rbfw_type_info_all as $key => $value) {

        if(!empty($_POST['rbfw_bikecarsd_info'][$a]['rent_type'])){
            $rent_type = $_POST['rbfw_bikecarsd_info'][$a]['rent_type'];
            $rent_qty  = $_POST['rbfw_bikecarsd_info'][$a]['qty'];

            if(!empty($rent_qty) && $rent_qty > 0):
            $rbfw_type_info[$rent_type] = $rent_qty;
            endif;
        }

        $a++;
    }

    $rbfw_bikecarsd_duration_price = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info,$rbfw_service_info,'rbfw_bikecarsd_duration_price');
    $rbfw_bikecarsd_service_price = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info,$rbfw_service_info,'rbfw_bikecarsd_service_price');
    $rbfw_bikecarsd_total_price  = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation($rbfw_id, $rbfw_type_info,$rbfw_service_info,'rbfw_bikecarsd_total_price');
    $rbfw_bikecarsd_ticket_info = $rbfw_bikecarsd->rbfw_bikecarsd_ticket_info($rbfw_id, $rbfw_start_datetime,$rbfw_end_datetime, $rbfw_type_info,$rbfw_service_info,$rbfw_bikecarsd_selected_time, $rbfw_regf_info);
    /* End Type: Bikecarsd */


    $rbfw_pickup_point      = isset( $_POST['rbfw_pickup_point'] ) ? $_POST['rbfw_pickup_point'] : '';
    $rbfw_dropoff_point     = isset( $_POST['rbfw_dropoff_point'] ) ? $_POST['rbfw_dropoff_point'] : '';
    $rbfw_pickup_start_date = isset( $_POST['rbfw_pickup_start_date'] ) ? $_POST['rbfw_pickup_start_date'] : '';
    $rbfw_pickup_start_time = isset( $_POST['rbfw_pickup_start_time'] ) ? $_POST['rbfw_pickup_start_time'] : '00:00:00';
    $rbfw_pickup_end_date   = isset( $_POST['rbfw_pickup_end_date'] ) ? $_POST['rbfw_pickup_end_date'] : '';
    $rbfw_pickup_end_time   = isset( $_POST['rbfw_pickup_end_time'] ) ? $_POST['rbfw_pickup_end_time'] : '24:00:00';


    

    if($rbfw_rent_type == 'resort'):
        $base_price     = $rbfw_room_total_price;
        $total_price    = apply_filters( 'rbfw_cart_base_price', $base_price );

    elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'):
        $base_price     = $rbfw_bikecarsd_total_price;
        $total_price    = apply_filters( 'rbfw_cart_base_price', $base_price );
    else:
        $start_date = $rbfw_pickup_start_date;
        $start_time = $rbfw_pickup_start_time;
        $end_date = $rbfw_pickup_end_date;
        $end_time = $rbfw_pickup_end_time;



        $start_datetime = date( 'Y-m-d H:i', strtotime( $rbfw_pickup_start_date . ' ' . $rbfw_pickup_start_time ) );
        $end_datetime   = date( 'Y-m-d H:i', strtotime( $rbfw_pickup_end_date . ' ' . $rbfw_pickup_end_time ) );
        $base_price     = rbfw_price_calculation( $rbfw_id, $start_datetime, $end_datetime, $start_date );
        $base_price = $base_price * $rbfw_item_quantity;
        $total_price    = apply_filters( 'rbfw_cart_base_price', $base_price );
        


        $rbfw_service_price = 0;
        
        $rbfw_duration_price = $base_price;
        

        $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : '';

        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        foreach ($rbfw_service_info as $key => $value):
            $service_name = $key; //Service1
            if(array_key_exists($service_name, $extra_services)){ // if Service1 exist in array

                if($rbfw_item_quantity > 1 && (int)$extra_services[$service_name] == 1 && $rbfw_enable_extra_service_qty != 'yes'){
                    $rbfw_service_price += (int)$rbfw_item_quantity * (float)$value; // quantity * price
                } else {
                    $rbfw_service_price += (int)$extra_services[$service_name] * (float)$value; // quantity * price
                }


            }
        endforeach;

        $variation_data = get_post_meta($rbfw_id,'rbfw_variations_data',true);
        $variation_info = [];
        if(!empty($variation_data)){
            $i = 0;
            foreach ($variation_data as $level_one_arr) {

                $selected_field_value = !empty($_POST[$level_one_arr['field_id']]) ? $_POST[$level_one_arr['field_id']] : [];

                $level_two_arr = $level_one_arr['value'];

                foreach ($level_two_arr as $level_two_arr_value) {
                    if($selected_field_value == $level_two_arr_value['name']){

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

        $total_price = $rbfw_duration_price + $rbfw_service_price;
        
        if(function_exists('rbfw_get_discount_array')){

            $discount_arr = rbfw_get_discount_array($rbfw_id, $start_date, $end_date, $total_price);

        } else {

            $discount_arr = [];
        }

        if(!empty($discount_arr)){
            $total_price = $discount_arr['total_amount'];
            $discount_type = $discount_arr['discount_type'];
            $discount_amount = $discount_arr['discount_amount'];
        }

        $rbfw_ticket_info = rbfw_cart_ticket_info($rbfw_id, $rbfw_pickup_start_date, $rbfw_pickup_start_time, $rbfw_pickup_end_date, $rbfw_pickup_end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price, $total_price, $rbfw_service_info,$variation_info, $discount_type, $discount_amount, $rbfw_regf_info);
    endif;	
    
    $cart_item_data['rbfw_id'] = $rbfw_id;

    if($rbfw_rent_type == 'resort'):
        $cart_item_data['rbfw_start_datetime']     = $rbfw_checkin_datetime;
        $cart_item_data['rbfw_end_datetime']       = $rbfw_checkout_datetime;
        $cart_item_data['rbfw_start_date'] = $rbfw_checkin_datetime;
        $cart_item_data['rbfw_start_time'] = '';
        $cart_item_data['rbfw_end_date'] = $rbfw_checkout_datetime;
        $cart_item_data['rbfw_end_time'] = '';
        $cart_item_data['rbfw_room_price_category'] = $rbfw_room_price_category;
        $cart_item_data['rbfw_room_info'] 			= $rbfw_room_info;
        $cart_item_data['rbfw_type_info'] 			= $rbfw_room_info;
        $cart_item_data['rbfw_service_info'] 		= $rbfw_service_info;
        $cart_item_data['rbfw_room_duration_price'] = $rbfw_room_duration_price;
        $cart_item_data['rbfw_room_service_price']  = $rbfw_room_service_price;
        $cart_item_data['rbfw_ticket_info']  = $rbfw_resort_ticket_info;
        $cart_item_data['discount_type']  = $discount_type;
        $cart_item_data['discount_amount']  = $discount_amount;



    elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'):
        $cart_item_data['rbfw_start_datetime'] = $rbfw_start_datetime;
        $cart_item_data['rbfw_end_datetime'] = $rbfw_end_datetime;
        $cart_item_data['rbfw_start_date'] = $bikecarsd_selected_date;
        $cart_item_data['rbfw_start_time'] = $rbfw_bikecarsd_selected_time;
        $cart_item_data['rbfw_end_date'] = $bikecarsd_selected_date;
        $cart_item_data['rbfw_end_time'] = '';
        $cart_item_data['rbfw_type_info'] 			= $rbfw_type_info;
        $cart_item_data['rbfw_service_info'] 			= $rbfw_service_info;
        $cart_item_data['rbfw_bikecarsd_duration_price']= $rbfw_bikecarsd_duration_price;
        $cart_item_data['rbfw_bikecarsd_service_price'] = $rbfw_bikecarsd_service_price;
        $cart_item_data['rbfw_ticket_info']  			= $rbfw_bikecarsd_ticket_info;

    else:

        $cart_item_data['rbfw_pickup_point']       = $rbfw_pickup_point;
        $cart_item_data['rbfw_dropoff_point']      = $rbfw_dropoff_point;
        $cart_item_data['rbfw_start_date']         = $start_date;
        $cart_item_data['rbfw_start_time']         = $start_time;
        $cart_item_data['rbfw_end_date']           = $end_date;
        $cart_item_data['rbfw_end_time']           = $end_time;       
        $cart_item_data['rbfw_start_datetime']     = $start_datetime;
        $cart_item_data['rbfw_end_datetime']       = $end_datetime;
        $cart_item_data['rbfw_item_quantity']      = $rbfw_item_quantity;
        $cart_item_data['rbfw_service_info']       = $rbfw_service_info;
        $cart_item_data['rbfw_variation_info']     = $variation_info;
        $cart_item_data['rbfw_ticket_info']        = $rbfw_ticket_info;
        $cart_item_data['rbfw_duration_price'] 	   = $rbfw_duration_price;
        $cart_item_data['rbfw_service_price']  	   = $rbfw_service_price;
        $cart_item_data['discount_type']            = $discount_type;
        $cart_item_data['discount_amount']          = $discount_amount;
        
    endif;

    $cart_item_data['rbfw_tp']                 = $total_price;
    $cart_item_data['line_total']              = $total_price;
    $cart_item_data['line_subtotal']           = $total_price;

    return apply_filters('rbfw_add_cart_function_after', $cart_item_data, $rbfw_id);
}