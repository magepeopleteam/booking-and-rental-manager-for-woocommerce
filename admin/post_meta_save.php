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



        
        
        $rbfw_enable_pick_point  = isset( $_POST['rbfw_enable_pick_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_pick_point'] ) : 'no';
        $rbfw_enable_dropoff_point  = isset( $_POST['rbfw_enable_dropoff_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_dropoff_point'] ) : 'no';

        $rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? $_POST['rbfw_available_qty_info_switch']  : 'no';
        $rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';
        $rbfw_enable_faq_content  = isset( $_POST['rbfw_enable_faq_content'] ) ? rbfw_array_strip( $_POST['rbfw_enable_faq_content'] ) : 'no';
        $rbfw_enable_variations  = isset( $_POST['rbfw_enable_variations'] ) ? rbfw_array_strip( $_POST['rbfw_enable_variations'] ) : 'no';
        $rbfw_enable_md_type_item_qty  = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? $_POST['rbfw_enable_md_type_item_qty'] : 'no';

        update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );

        $rbfw_item_stock_quantity = isset( $_POST['rbfw_item_stock_quantity'] ) ? $_POST['rbfw_item_stock_quantity'] : 0;

        // getting resort value
        // End getting resort value

        // getting bike/car single day value
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


        //update_post_meta( $post_id, 'rbfw_enable_start_end_date', $rbfw_enable_start_end_date );
        update_post_meta( $post_id, 'rbfw_event_start_date', $rbfw_event_start_date );
        update_post_meta( $post_id, 'rbfw_event_start_time', $rbfw_event_start_time );
        update_post_meta( $post_id, 'rbfw_event_end_date', $rbfw_event_end_date );
        update_post_meta( $post_id, 'rbfw_event_end_time', $rbfw_event_end_time );



        update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $rbfw_enable_dropoff_point );

        update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );
        update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );
       // update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );
        update_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', $rbfw_enable_md_type_item_qty );

        //update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );


        


        

        // saving resort
        

        // end saving resort

        // saving bike/car single day data

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

        // end Appointment ondays


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