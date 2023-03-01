<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**********************
WooCommerce Item Meta
***********************/
add_action( 'rbfw_validate_add_order_item', 'rbfw_validate_add_order_item_func', 10, 3 );

function rbfw_validate_add_order_item_func( $values, $item, $rbfw_id ) {
    global $rbfw;
    $rbfw_rent_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );
    
    /* Type: Resort */
    if($rbfw_rent_type == 'resort'):
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

        $item->add_meta_data($rbfw->get_option('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_start_datetime));
        $item->add_meta_data($rbfw->get_option('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_end_datetime));
        $item->add_meta_data($rbfw->get_option('rbfw_text_package', 'rbfw_basic_translation_settings', __('Package','booking-and-rental-manager-for-woocommerce')), $rbfw_room_price_category);

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
                        $item->add_meta_data($rbfw->get_option('rbfw_text_room_information', 'rbfw_basic_translation_settings', __('Room Information','booking-and-rental-manager-for-woocommerce')), $room_content );
                    endif;
                }
         
            endforeach;
        endif;

        if ( ! empty( $rbfw_service_info ) ): 
            $resort_service_arr = [];
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
                        $item->add_meta_data($rbfw->get_option('rbfw_text_room_service_information', 'rbfw_basic_translation_settings', __('Service Information','booking-and-rental-manager-for-woocommerce')), $room_service_content );
                    endif;
                }
         
            endforeach;
        endif;
        
        $item->add_meta_data($rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_room_duration_price));
        $item->add_meta_data($rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_room_service_price));
        $item->add_meta_data($rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')), wc_price($discount_amount));			
        $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
        $item->add_meta_data( '_rbfw_type_info', $resort_type_arr );
        $item->add_meta_data( '_rbfw_resort_package', $rbfw_room_price_category );
        $item->add_meta_data( '_rbfw_service_info', $resort_service_arr );
        $item->add_meta_data( '_rbfw_duration_cost', $rbfw_room_duration_price );
        $item->add_meta_data( '_rbfw_service_cost', $rbfw_room_service_price );
        $item->add_meta_data( '_rbfw_discount_type', $discount_type );
        $item->add_meta_data( '_rbfw_discount_amount', $discount_amount );
    /* End Type: Resort */

    /* Type: Bikecarsd */
    elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'):
    
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

    $item->add_meta_data($rbfw->get_option('rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings', __('Start Date and Time','booking-and-rental-manager-for-woocommerce')), rbfw_date_format($rbfw_start_datetime).' '.$rbfw_start_time);

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
    
    $item->add_meta_data($rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_bikecarsd_duration_price));
    $item->add_meta_data($rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_bikecarsd_service_price));	
    $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
    $item->add_meta_data( '_rbfw_type_info', $bikecarsd_type_arr );
    $item->add_meta_data( '_rbfw_service_info', $bikecarsd_service_arr );
    $item->add_meta_data( '_rbfw_duration_cost', $rbfw_bikecarsd_duration_price );
    $item->add_meta_data( '_rbfw_service_cost', $rbfw_bikecarsd_service_price );		
    /* End Type: Bikecarsd */

    else:
        
        $rbfw_extra_service_data 	= get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();
        
        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $variation_info = $values['rbfw_variation_info'] ? $values['rbfw_variation_info'] : [];
        $rbfw_service_info = $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
        $rbfw_ticket_info = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];
        $start_datetime = $values['rbfw_start_datetime'] ? rbfw_get_datetime( $values['rbfw_start_datetime'], 'date-time-text' ) : '';
        $start_date_raw = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
        $end_datetime = $values['rbfw_end_datetime'] ? rbfw_get_datetime( $values['rbfw_end_datetime'], 'date-time-text' ) : '';
        $end_date_raw = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
        $start_date = $values['rbfw_start_date'] ? $values['rbfw_start_date'] : '';
        $start_time = $values['rbfw_start_time'] ? $values['rbfw_start_time'] : '';
        $end_date = $values['rbfw_end_date'] ? $values['rbfw_end_date'] : '';
        $end_time = $values['rbfw_end_time'] ? $values['rbfw_end_time'] : '';    

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


        if ( ! empty( $rbfw_service_info ) ): 
            $bikecarmd_service_arr = [];
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
        
        $item->add_meta_data($rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_duration_price));
        $item->add_meta_data($rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')), wc_price($rbfw_service_price));	
        $item->add_meta_data($rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')), wc_price($discount_amount));
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
    endif;

        $item->add_meta_data( '_rbfw_id', $rbfw_id );

        $rbfw_regf_info = $values['rbfw_regf_info'] ? $values['rbfw_regf_info'] : [];

        if ( ! empty( $rbfw_regf_info ) ):
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

                if(!empty($the_value)){
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
            $item->add_meta_data(rbfw_string_return('rbfw_text_customer_information',__('Customer Information','rbfw-pro')), $rbfw_regf_info_content );
            $item->add_meta_data( '_rbfw_regf_info', $rbfw_regf_info );
        endif;
}


/******************************************************************
WooCommerce After Checkout the Order, Add Data to Custom Post Type
********************************************************************/
add_action( 'woocommerce_checkout_order_processed', 'rbfw_booking_management', 10 );

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
            $rbfw_id = rbfw_get_order_item_meta( $item_id, '_rbfw_id', true );

            if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {

                $ticket_info  = rbfw_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ) : [];
                $user_info    = rbfw_get_order_item_meta( $item_id, '_rbfw_user_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_user_info', true ) ) : [];
                $type_info    = rbfw_get_order_item_meta( $item_id, '_rbfw_type_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_type_info', true ) ) : [];
                $service_info = rbfw_get_order_item_meta( $item_id, '_rbfw_service_info', true ) ? maybe_unserialize( rbfw_get_order_item_meta( $item_id, '_rbfw_service_info', true ) ) : [];
                $rbfw_duration_cost = rbfw_get_order_item_meta( $item_id, '_rbfw_duration_cost', true ) ? rbfw_get_order_item_meta( $item_id, '_rbfw_duration_cost', true ) : '';
                $rbfw_service_cost = rbfw_get_order_item_meta( $item_id, '_rbfw_service_cost', true ) ? rbfw_get_order_item_meta( $item_id, '_rbfw_service_cost', true ) : '';

                rbfw_prepar_and_add_user_data( $ticket_info, $user_info, $rbfw_id, $order_id, $service_info, $rbfw_duration_cost, $rbfw_service_cost, $type_info);
            }
        }
    }
}