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



    }
}