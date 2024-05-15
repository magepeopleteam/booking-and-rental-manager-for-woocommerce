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



        
        

        $rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';
        

        // getting bike/car single day value
        // End getting resort value


        // getting appointment days
        $rbfw_sd_appointment_ondays 	 = isset( $_POST['rbfw_sd_appointment_ondays'] ) ? rbfw_array_strip( $_POST['rbfw_sd_appointment_ondays'] ) : [];
        $rbfw_sd_appointment_max_qty_per_session 	 = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ?  $_POST['rbfw_sd_appointment_max_qty_per_session'] : '';


        update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );





        // saving variations data
       

        
    }
}