<?php
/**
 * Loyalty Points Cart Notice Template
 *
 * This template can be overridden by copying it to yourtheme/booking-and-rental-manager-for-woocommerce/loyalty/loyalty-cart-notice.php
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

// Get cart total
$cart_total = WC()->cart->get_total('edit');

// Get points per currency setting
$points_per_currency = isset( $loyalty_settings['rbfw_loyalty_points_per_currency'] ) ? intval($loyalty_settings['rbfw_loyalty_points_per_currency']) : 10;

// Calculate potential points
$potential_points = round( $cart_total * $points_per_currency );

// Ensure points are integers
$potential_points = intval($potential_points);

// Check if cart contains RBFW items
$has_rbfw_items = false;
foreach ( WC()->cart->get_cart() as $cart_item ) {
    $product_id = $cart_item['product_id'];
    $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true );
    
    if ( $linked_rbfw_id || get_post_type( $product_id ) === 'rbfw_item' ) {
        $has_rbfw_items = true;
        break;
    }
}

if ( ! $has_rbfw_items || $potential_points <= 0 ) {
    return;
}
?>

<div class="rbfw-loyalty-points-cart-notice">
    <p><?php printf( __( 'Complete this order to earn %s loyalty points!', 'booking-and-rental-manager-for-woocommerce' ), '<strong>' . esc_html( $potential_points ) . '</strong>' ); ?></p>
    
    <?php if ( is_user_logged_in() ) : ?>
        <p><a href="<?php echo esc_url( wc_get_endpoint_url( 'loyalty-rewards' ) ); ?>"><?php _e( 'View your loyalty rewards', 'booking-and-rental-manager-for-woocommerce' ); ?></a></p>
    <?php endif; ?>
</div>

<style>
.rbfw-loyalty-points-cart-notice {
    margin: 20px 0;
    padding: 15px;
    background: #f8f8f8;
    border-radius: 5px;
    border-left: 4px solid #2271b1;
}
</style>