<?php
/**
 * Loyalty Points Display Template
 *
 * This template can be overridden by copying it to yourtheme/booking-and-rental-manager-for-woocommerce/loyalty/loyalty-points-display.php
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get loyalty program status
$loyalty_settings = get_option( 'rbfw_loyalty_settings' );
$is_enabled = isset( $loyalty_settings['rbfw_loyalty_enable'] ) ? $loyalty_settings['rbfw_loyalty_enable'] : 'no';

if ( $is_enabled !== 'yes' ) {
    return;
}

// Get order total
$order_total = $order->get_total();

// Get points per currency setting
$points_per_currency = isset( $loyalty_settings['rbfw_loyalty_points_per_currency'] ) ? intval($loyalty_settings['rbfw_loyalty_points_per_currency']) : 10;

// Calculate potential points
$potential_points = round( $order_total * $points_per_currency );

// Check if points were already awarded
$points_awarded = get_post_meta( $order->get_id(), 'rbfw_loyalty_points_awarded', true );
$points_amount = get_post_meta( $order->get_id(), 'rbfw_loyalty_points_amount', true );

// Ensure points amount is an integer
if (!empty($points_amount)) {
    $points_amount = intval($points_amount);
}

// Check if order contains RBFW items
$has_rbfw_items = false;
foreach ( $order->get_items() as $item ) {
    $product_id = $item->get_product_id();
    $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true );
    
    if ( $linked_rbfw_id || get_post_type( $product_id ) === 'rbfw_item' ) {
        $has_rbfw_items = true;
        break;
    }
}

if ( ! $has_rbfw_items ) {
    return;
}
?>

<div class="rbfw-loyalty-points-order-display">
    <h3><?php _e( 'Loyalty Points', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
    
    <?php if ( $points_awarded === 'yes' ) : ?>
        <p><?php printf( __( 'You earned %s points from this order.', 'booking-and-rental-manager-for-woocommerce' ), '<strong>' . esc_html( $points_amount ) . '</strong>' ); ?></p>
    <?php else : ?>
        <p><?php printf( __( 'You will earn %s points when this order is completed.', 'booking-and-rental-manager-for-woocommerce' ), '<strong>' . esc_html( $potential_points ) . '</strong>' ); ?></p>
    <?php endif; ?>
    
    <?php if ( is_user_logged_in() ) : ?>
        <p><a href="<?php echo esc_url( wc_get_endpoint_url( 'loyalty-rewards' ) ); ?>"><?php _e( 'View your loyalty rewards', 'booking-and-rental-manager-for-woocommerce' ); ?></a></p>
    <?php endif; ?>
</div>

<style>
.rbfw-loyalty-points-order-display {
    margin: 20px 0;
    padding: 15px;
    background: #f8f8f8;
    border-radius: 5px;
}

.rbfw-loyalty-points-order-display h3 {
    margin-top: 0;
    margin-bottom: 10px;
}
</style>