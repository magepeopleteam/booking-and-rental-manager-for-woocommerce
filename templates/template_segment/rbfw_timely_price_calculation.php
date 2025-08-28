<?php
global $rbfw;

// Verify nonce before processing the data
if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
    return;
}
    // Sanitize input values
    $id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $es_service_price = isset( $_POST['es_service_price'] ) ? floatval( $_POST['es_service_price'] ) : 0;
    $duration_cost = isset( $_POST['duration_price'] ) ? floatval( $_POST['duration_price'] ) : 0;
    $sub_total_price = $duration_cost + $es_service_price;

?>
<div class="item-content rbfw-costing">
    <ul class="rbfw-ul">
        <li class="duration-costing rbfw-cond">
            <?php echo esc_html__('Duration Cost','booking-and-rental-manager-for-woocommerce'); ?>
            <?php echo wp_kses( wc_price( $duration_cost ) , rbfw_allowed_html()); ?>
        </li>

        <li class="resource-costing rbfw-cond">
            <?php echo esc_html__('Resource Cost','booking-and-rental-manager-for-woocommerce'); ?>
            <?php echo wp_kses( wc_price( $es_service_price ) , rbfw_allowed_html()); ?>
        </li>

        <li class="subtotal">
            <?php echo esc_html__('Subtotal','booking-and-rental-manager-for-woocommerce'); ?>
            <?php echo wp_kses( wc_price( $duration_cost + $es_service_price )  , rbfw_allowed_html()); ?>
        </li>

        <?php
        // Ensure $security_deposit is set correctly
        $security_deposit = rbfw_security_deposit( $id, $sub_total_price );
        if ( $security_deposit['security_deposit_desc'] ) { ?>
            <li class="subtotal">
                <?php echo esc_html( ( ! empty( get_post_meta( $id, 'rbfw_security_deposit_label', true ) ) ? get_post_meta( $id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ) ); ?>
                <?php echo wp_kses( wc_price( $security_deposit['security_deposit_amount'] ) , rbfw_allowed_html()); ?>
            </li>
        <?php }
        
        // Calculate the total price including security deposit
        $total_price = $duration_cost + $es_service_price + $security_deposit['security_deposit_amount'];
        ?>
        
        <li class="total">
            <strong><?php echo esc_html__('Total','booking-and-rental-manager-for-woocommerce'); ?></strong>
            <?php echo wp_kses( wc_price( $total_price )  , rbfw_allowed_html()); ?>
        </li>
    </ul>
    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
</div>
