<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.


add_action( 'save_post', 'rbfw_save_meta_box_data', 99 );


function rbfw_save_meta_box_data( $post_id ) {
    global $wpdb;
    if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( $_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( get_post_type( $post_id ) == 'rbfw_item' ) {

        $hourly_rate = isset( $_POST['rbfw_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_hourly_rate'] ) : 0;
        $daily_rate  = isset( $_POST['rbfw_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_daily_rate'] ) : 0;

        //sun
        $hourly_rate_sun = isset( $_POST['rbfw_sun_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_hourly_rate'] ) : '';
        $daily_rate_sun  = isset( $_POST['rbfw_sun_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_daily_rate'] ) : '';
        $enabled_sun     = isset( $_POST['rbfw_enable_sun_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sun_day'] ) : 'no';
        //mon
        $hourly_rate_mon = isset( $_POST['rbfw_mon_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_hourly_rate'] ) : '';
        $daily_rate_mon  = isset( $_POST['rbfw_mon_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_daily_rate'] ) : '';
        $enabled_mon     = isset( $_POST['rbfw_enable_mon_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_mon_day'] ) : 'no';
        //tue
        $hourly_rate_tue = isset( $_POST['rbfw_tue_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_hourly_rate'] ) : '';
        $daily_rate_tue  = isset( $_POST['rbfw_tue_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_daily_rate'] ) : '';
        $enabled_tue     = isset( $_POST['rbfw_enable_tue_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_tue_day'] ) : 'no';
        //wed
        $hourly_rate_wed = isset( $_POST['rbfw_wed_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_hourly_rate'] ) : '';
        $daily_rate_wed  = isset( $_POST['rbfw_wed_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_daily_rate'] ) : '';
        $enabled_wed     = isset( $_POST['rbfw_enable_wed_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_wed_day'] ) : 'no';
        //thu
        $hourly_rate_thu = isset( $_POST['rbfw_thu_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_hourly_rate'] ) : '';
        $daily_rate_thu  = isset( $_POST['rbfw_thu_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_daily_rate'] ) : '';
        $enabled_thu     = isset( $_POST['rbfw_enable_thu_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_thu_day'] ) : 'no';
        //fri
        $hourly_rate_fri = isset( $_POST['rbfw_fri_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_hourly_rate'] ) : '';
        $daily_rate_fri  = isset( $_POST['rbfw_fri_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_daily_rate'] ) : '';
        $enabled_fri     = isset( $_POST['rbfw_enable_fri_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_fri_day'] ) : 'no';
        //sat
        $hourly_rate_sat         = isset( $_POST['rbfw_sat_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_hourly_rate'] ) : '';
        $daily_rate_sat          = isset( $_POST['rbfw_sat_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_daily_rate'] ) : '';
        $enabled_sat             = isset( $_POST['rbfw_enable_sat_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sat_day'] ) : 'no';
        $rbfw_item_type          = isset( $_POST['rbfw_item_type'] ) ? rbfw_array_strip( $_POST['rbfw_item_type'] ) : 'others';
        
        $rbfw_enable_pick_point  = isset( $_POST['rbfw_enable_pick_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_pick_point'] ) : 'no';
        $rbfw_enable_dropoff_point  = isset( $_POST['rbfw_enable_dropoff_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_dropoff_point'] ) : 'no';
        $rbfw_enable_daywise_price  = isset( $_POST['rbfw_enable_daywise_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daywise_price'] ) : 'no';
        $rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? $_POST['rbfw_available_qty_info_switch']  : 'no';
        $rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';
        $rbfw_enable_daily_rate  = isset( $_POST['rbfw_enable_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daily_rate'] ) : 'no';
        $rbfw_enable_hourly_rate = isset( $_POST['rbfw_enable_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_hourly_rate'] ) : 'no';
        $rbfw_enable_faq_content  = isset( $_POST['rbfw_enable_faq_content'] ) ? rbfw_array_strip( $_POST['rbfw_enable_faq_content'] ) : 'no';
        $rbfw_enable_variations  = isset( $_POST['rbfw_enable_variations'] ) ? rbfw_array_strip( $_POST['rbfw_enable_variations'] ) : 'no';
        $rbfw_enable_md_type_item_qty  = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? $_POST['rbfw_enable_md_type_item_qty'] : 'no';



        $rbfw_item_stock_quantity = isset( $_POST['rbfw_item_stock_quantity'] ) ? $_POST['rbfw_item_stock_quantity'] : 0;

        $rbfw_enable_resort_daylong_price  = isset( $_POST['rbfw_enable_resort_daylong_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_resort_daylong_price'] ) : 'no';
        // getting resort value
        $rbfw_resort_room_data 	 = isset( $_POST['rbfw_resort_room_data'] ) ? rbfw_array_strip( $_POST['rbfw_resort_room_data'] ) : 0;
        // End getting resort value

        // getting bike/car single day value
        $rbfw_bike_car_sd_data 	 = isset( $_POST['rbfw_bike_car_sd_data'] ) ? rbfw_array_strip( $_POST['rbfw_bike_car_sd_data'] ) : 0;
        // End getting resort value

        // getting bike/car single day value
        $rbfw_variations_data 	 = isset( $_POST['rbfw_variations_data'] ) ? rbfw_array_strip( $_POST['rbfw_variations_data'] ) : [];
        // End getting resort value


        // getting appointment days
        $rbfw_sd_appointment_ondays 	 = isset( $_POST['rbfw_sd_appointment_ondays'] ) ? rbfw_array_strip( $_POST['rbfw_sd_appointment_ondays'] ) : [];
        $rbfw_sd_appointment_max_qty_per_session 	 = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ?  $_POST['rbfw_sd_appointment_max_qty_per_session'] : '';

        // End getting appointment days

        $rbfw_enable_start_end_date  = isset( $_POST['rbfw_enable_start_end_date'] ) ? rbfw_array_strip( $_POST['rbfw_enable_start_end_date'] ) : 'yes';
        $rbfw_event_start_date  = isset( $_POST['rbfw_event_start_date'] ) ? rbfw_array_strip( $_POST['rbfw_event_start_date'] ) : '';
        $rbfw_event_start_time  = isset( $_POST['rbfw_event_start_time'] ) ? rbfw_array_strip( $_POST['rbfw_event_start_time'] ) : '';
        $rbfw_event_end_date  = isset( $_POST['rbfw_event_end_date'] ) ? rbfw_array_strip( $_POST['rbfw_event_end_date'] ) : '';
        $rbfw_event_end_time  = isset( $_POST['rbfw_event_end_time'] ) ? rbfw_array_strip( $_POST['rbfw_event_end_time'] ) : '';



        $_tax_class 	 = isset( $_POST['_tax_class'] ) ? rbfw_array_strip( $_POST['_tax_class'] ) : '';
        $_tax_status 	 = isset( $_POST['_tax_status'] ) ? rbfw_array_strip( $_POST['_tax_status'] ) : '';

        // $rbfw_service_category_price      = isset( $_POST['rbfw_service_category_price'] ) ? rbfw_array_strip( $_POST['rbfw_service_category_price'] ) : [];

        // update_post_meta( $post_id, 'rbfw_service_category_price', $rbfw_service_category_price );




        //update_post_meta( $post_id, 'rbfw_enable_start_end_date', $rbfw_enable_start_end_date );
        update_post_meta( $post_id, 'rbfw_event_start_date', $rbfw_event_start_date );
        update_post_meta( $post_id, 'rbfw_event_start_time', $rbfw_event_start_time );
        update_post_meta( $post_id, 'rbfw_event_end_date', $rbfw_event_end_date );
        update_post_meta( $post_id, 'rbfw_event_end_time', $rbfw_event_end_time );

       // update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
       // update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
        //update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );

        update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $rbfw_enable_dropoff_point );

        //update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );
        update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );
        update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );
       // update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );
        update_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', $rbfw_enable_md_type_item_qty );

        //update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );

        update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
        

        update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );
        update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );

        // sun
        update_post_meta( $post_id, 'rbfw_sun_hourly_rate', $hourly_rate_sun );
        update_post_meta( $post_id, 'rbfw_sun_daily_rate', $daily_rate_sun );
        update_post_meta( $post_id, 'rbfw_enable_sun_day', $enabled_sun );
        // mon
        update_post_meta( $post_id, 'rbfw_mon_hourly_rate', $hourly_rate_mon );
        update_post_meta( $post_id, 'rbfw_mon_daily_rate', $daily_rate_mon );
        update_post_meta( $post_id, 'rbfw_enable_mon_day', $enabled_mon );
        // tue
        update_post_meta( $post_id, 'rbfw_tue_hourly_rate', $hourly_rate_tue );
        update_post_meta( $post_id, 'rbfw_tue_daily_rate', $daily_rate_tue );
        update_post_meta( $post_id, 'rbfw_enable_tue_day', $enabled_tue );
        // wed
        update_post_meta( $post_id, 'rbfw_wed_hourly_rate', $hourly_rate_wed );
        update_post_meta( $post_id, 'rbfw_wed_daily_rate', $daily_rate_wed );
        update_post_meta( $post_id, 'rbfw_enable_wed_day', $enabled_wed );
        // thu
        update_post_meta( $post_id, 'rbfw_thu_hourly_rate', $hourly_rate_thu );
        update_post_meta( $post_id, 'rbfw_thu_daily_rate', $daily_rate_thu );
        update_post_meta( $post_id, 'rbfw_enable_thu_day', $enabled_thu );
        // fri
        update_post_meta( $post_id, 'rbfw_fri_hourly_rate', $hourly_rate_fri );
        update_post_meta( $post_id, 'rbfw_fri_daily_rate', $daily_rate_fri );
        update_post_meta( $post_id, 'rbfw_enable_fri_day', $enabled_fri );
        // sat
        update_post_meta( $post_id, 'rbfw_sat_hourly_rate', $hourly_rate_sat );
        update_post_meta( $post_id, 'rbfw_sat_daily_rate', $daily_rate_sat );
        update_post_meta( $post_id, 'rbfw_enable_sat_day', $enabled_sat );

        // saving resort
        update_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', $rbfw_enable_resort_daylong_price );
        update_post_meta( $post_id, 'rbfw_resort_room_data', $rbfw_resort_room_data );
        // end saving resort

        // saving bike/car single day data
        update_post_meta( $post_id, 'rbfw_bike_car_sd_data', $rbfw_bike_car_sd_data );
        // end saving bike/car single day data

        // saving variations data
        update_post_meta( $post_id, 'rbfw_variations_data', $rbfw_variations_data );
        // end saving variations data


       // update_post_meta( $post_id, 'rbfw_category_name', $rbfw_category_name );

        update_post_meta( $post_id, '_tax_class', $_tax_class );
        update_post_meta( $post_id, '_tax_status', $_tax_status );







        // saving FAQ switch
        update_post_meta( $post_id, 'rbfw_enable_faq_content', $rbfw_enable_faq_content );
        // end FAQ switch

        // saving Appointment ondays
        update_post_meta( $post_id, 'rbfw_sd_appointment_ondays', $rbfw_sd_appointment_ondays );
        update_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', $rbfw_sd_appointment_max_qty_per_session );

        // end Appointment ondays

        $old_extra_service = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
        $new_extra_service = array();

        $service_img     = !empty($_POST['service_img']) ? rbfw_array_strip( $_POST['service_img'] ) : array();
        $names    = $_POST['service_name'] ? rbfw_array_strip( $_POST['service_name'] ) : array();
        $urls     = $_POST['service_price'] ? rbfw_array_strip( $_POST['service_price'] ) : array();
        $service_desc     = $_POST['service_desc'] ? rbfw_array_strip( $_POST['service_desc'] ) : array();
        $qty      = $_POST['service_qty'] ? rbfw_array_strip( $_POST['service_qty'] ) : array();
        $qty_type = !empty($_POST['service_qty_type']) ? rbfw_array_strip( $_POST['service_qty_type'] ) : array();
        $count    = count( $names );
        for ( $i = 0; $i < $count; $i ++ ) {

            if (!empty($service_img[ $i ])) :
                $new_extra_service[ $i ]['service_img'] = stripslashes( strip_tags( $service_img[ $i ] ) );
            endif;

            if ( $names[ $i ] != '' ) :
                $new_extra_service[ $i ]['service_name'] = stripslashes( strip_tags( $names[ $i ] ) );
            endif;

            if ( $urls[ $i ] != '' ) :
                $new_extra_service[ $i ]['service_price'] = stripslashes( strip_tags( $urls[ $i ] ) );
            endif;

            if ( $service_desc[ $i ] != '' ) :
                $new_extra_service[ $i ]['service_desc'] = stripslashes( strip_tags( $service_desc[ $i ] ) );
            endif;

            if ( $qty[ $i ] != '' ) :
                $new_extra_service[ $i ]['service_qty'] = stripslashes( strip_tags( $qty[ $i ] ) );
            endif;

            if ( !empty($qty_type[ $i ]) && $qty_type[ $i ] != '' ) :
                $new_extra_service[ $i ]['service_qty_type'] = stripslashes( strip_tags( $qty_type[ $i ] ) );
            endif;
        }

        $extra_service_data_arr = apply_filters( 'rbfw_extra_service_arr_save', $new_extra_service );

        if ( ! empty( $extra_service_data_arr ) && $extra_service_data_arr != $old_extra_service ) {
            update_post_meta( $post_id, 'rbfw_extra_service_data', $extra_service_data_arr );
        } elseif ( empty( $extra_service_data_arr ) && $old_extra_service ) {
            delete_post_meta( $post_id, 'rbfw_extra_service_data', $old_extra_service );
        }

        // Saving Pickup Location Data
        $old_rbfw_pickup_data = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
        $new_rbfw_pickup_data = array();
        $names                = $_POST['loc_pickup_name'] ? rbfw_array_strip( $_POST['loc_pickup_name'] ) : array();
        $count                = count( $names );
        for ( $i = 0; $i < $count; $i ++ ) {
            if ( $names[ $i ] != '' ) :
                $new_rbfw_pickup_data[ $i ]['loc_pickup_name'] = stripslashes( strip_tags( $names[ $i ] ) );
            endif;
        }
        $pickup_data_arr = apply_filters( 'rbfw_pickup_arr_save', $new_rbfw_pickup_data );
        if ( ! empty( $pickup_data_arr ) && $pickup_data_arr != $old_rbfw_pickup_data ) {
            update_post_meta( $post_id, 'rbfw_pickup_data', $pickup_data_arr );
        } elseif ( empty( $pickup_data_arr ) && $old_rbfw_pickup_data ) {
            delete_post_meta( $post_id, 'rbfw_pickup_data', $old_rbfw_pickup_data );
        }
        // Saving Dropoff Data
        $old_rbfw_dropoff_data = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
        $new_rbfw_dropoff_data = array();
        $names                 = $_POST['loc_dropoff_name'] ? rbfw_array_strip( $_POST['loc_dropoff_name'] ) : array();
        $count                 = count( $names );
        for ( $i = 0; $i < $count; $i ++ ) {
            if ( $names[ $i ] != '' ) :
                $new_rbfw_dropoff_data[ $i ]['loc_dropoff_name'] = stripslashes( strip_tags( $names[ $i ] ) );
            endif;
        }
        $dropoff_data_arr = apply_filters( 'rbfw_dropoff_arr_save', $new_rbfw_dropoff_data );
        if ( ! empty( $dropoff_data_arr ) && $dropoff_data_arr != $old_rbfw_dropoff_data ) {
            update_post_meta( $post_id, 'rbfw_dropoff_data', $dropoff_data_arr );
        } elseif ( empty( $dropoff_data_arr ) && $old_rbfw_dropoff_data ) {
            delete_post_meta( $post_id, 'rbfw_dropoff_data', $old_rbfw_dropoff_data );
        }
        //save_rbfw_repeated_setting( $post_id, 'mep_event_faq' );
    }
}