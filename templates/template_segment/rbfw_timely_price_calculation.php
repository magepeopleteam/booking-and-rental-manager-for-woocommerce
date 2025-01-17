<?php
global $rbfw;

// Verify nonce before processing the data
if ( isset( $_POST['rbfw_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_nonce_field'] ) ), 'rbfw_nonce_action' ) ) {
    
    // Sanitize input values
    $id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $es_service_price = isset( $_POST['es_service_price'] ) ? floatval( $_POST['es_service_price'] ) : 0;
    $duration_cost = isset( $_POST['duration_price'] ) ? floatval( $_POST['duration_price'] ) : 0;
    $sub_total_price = $duration_cost + $es_service_price;
}
?>
<div class="item-content rbfw-costing">
    <ul class="rbfw-ul">
        <li class="duration-costing rbfw-cond">
            <?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce') ) ); ?>
            <?php echo esc_html( wc_price( $duration_cost ) ); ?>
        </li>

        <li class="resource-costing rbfw-cond">
            <?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce') ) ); ?>
            <?php echo esc_html( wc_price( $es_service_price ) ); ?>
        </li>

        <li class="subtotal">
            <?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce') ) ); ?>
            <?php echo esc_html( wc_price( $duration_cost + $es_service_price ) ); ?>
        </li>

        <?php
        // Ensure $security_deposit is set correctly
        $security_deposit = rbfw_security_deposit( $id, $sub_total_price );
        if ( $security_deposit['security_deposit_desc'] ) { ?>
            <li class="subtotal">
                <?php echo esc_html( ( ! empty( get_post_meta( $id, 'rbfw_security_deposit_label', true ) ) ? get_post_meta( $id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ) ); ?>
                <?php echo esc_html( wc_price( $security_deposit['security_deposit_amount'] ) ); ?>
            </li>
        <?php }
        
        // Calculate the total price including security deposit
        $total_price = $duration_cost + $es_service_price + $security_deposit['security_deposit_amount'];
        ?>
        
        <li class="total">
            <strong><?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce') ) ); ?></strong>
            <?php echo esc_html( wc_price( $total_price ) ); ?>
        </li>
    </ul>
    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
</div>
