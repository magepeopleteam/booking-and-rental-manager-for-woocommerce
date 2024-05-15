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



        
        

        $rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? $_POST['rbfw_available_qty_info_switch']  : 'no';
        $rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';
        $rbfw_enable_faq_content  = isset( $_POST['rbfw_enable_faq_content'] ) ? rbfw_array_strip( $_POST['rbfw_enable_faq_content'] ) : 'no';
        $rbfw_enable_variations  = isset( $_POST['rbfw_enable_variations'] ) ? rbfw_array_strip( $_POST['rbfw_enable_variations'] ) : 'no';
        $rbfw_enable_md_type_item_qty  = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? $_POST['rbfw_enable_md_type_item_qty'] : 'no';



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


        // saving FAQ switch
        update_post_meta( $post_id, 'rbfw_enable_faq_content', $rbfw_enable_faq_content );
        // end FAQ switch

        // saving Appointment ondays

        // end Appointment ondays


        
        //save_rbfw_repeated_setting( $post_id, 'mep_event_faq' );
    }
}