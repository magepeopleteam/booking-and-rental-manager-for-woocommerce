<?php
/**
 * Fee Management Helper Functions
 * @Author: Shahnur Alam
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Get fee data for a specific item
 * @param int $item_id
 * @return array
 * @since 1.0.0
 */
function rbfw_get_fee_data( $item_id ) {
	$fee_data = get_post_meta( $item_id, 'rbfw_fee_data', true );
	return is_array( $fee_data ) ? $fee_data : array();
}

/**
 * Get active fees for a specific item
 * @param int $item_id
 * @return array
 * @since 1.0.0
 */
function rbfw_get_active_fees( $item_id ) {
	$all_fees = rbfw_get_fee_data( $item_id );
	$active_fees = array();
	
	foreach ( $all_fees as $fee ) {
		if ( isset( $fee['status'] ) && $fee['status'] === 'active' ) {
			$active_fees[] = $fee;
		}
	}
	
	return $active_fees;
}

/**
 * Calculate fee amount based on base price and days
 * @param array $fee_data
 * @param float $base_price
 * @param int $days
 * @return float
 * @since 1.0.0
 */
function rbfw_calculate_fee_amount( $fee_data, $base_price, $days = 1 ) {
	if ( empty( $fee_data ) || ! isset( $fee_data['amount'] ) ) {
		return 0;
	}
	
	$amount = floatval( $fee_data['amount'] );
	$calculation_type = isset( $fee_data['calculation_type'] ) ? $fee_data['calculation_type'] : 'fixed';
	$frequency = isset( $fee_data['frequency'] ) ? $fee_data['frequency'] : 'one-time';
	
	// Calculate base amount based on calculation type
	if ( $calculation_type === 'percentage' ) {
		$calculated_amount = ( $base_price * $amount ) / 100;
	} else {
		$calculated_amount = $amount;
	}
	
	// Apply frequency multiplier
	switch ( $frequency ) {
		case 'per-day':
		case 'per-night':
			$final_amount = $calculated_amount * $days;
			break;
		case 'one-time':
		default:
			$final_amount = $calculated_amount;
			break;
	}
	
	return $final_amount;
}

/**
 * Calculate total fees for booking
 * @param int $item_id
 * @param float $base_price
 * @param int $days
 * @return array
 * @since 1.0.0
 */
function rbfw_calculate_total_fees( $item_id, $base_price, $days = 1 ) {
	$fees = rbfw_get_active_fees( $item_id );
	$fee_breakdown = array();
	$total_fees = 0;
	
	foreach ( $fees as $fee ) {
		$fee_amount = rbfw_calculate_fee_amount( $fee, $base_price, $days );
		$fee_breakdown[] = array(
			'label' => $fee['label'],
			'description' => isset( $fee['description'] ) ? $fee['description'] : '',
			'amount' => $fee_amount,
			'calculation_type' => $fee['calculation_type'],
			'frequency' => $fee['frequency'],
			'refundable' => isset( $fee['refundable'] ) ? $fee['refundable'] : 'no',
			'taxable' => isset( $fee['taxable'] ) ? $fee['taxable'] : 'no'
		);
		$total_fees += $fee_amount;
	}
	
	return array(
		'fees' => $fee_breakdown,
		'total_amount' => $total_fees
	);
}

/**
 * Get refundable fees
 * @param int $item_id
 * @return array
 * @since 1.0.0
 */
function rbfw_get_refundable_fees( $item_id ) {
	$active_fees = rbfw_get_active_fees( $item_id );
	$refundable_fees = array();
	
	foreach ( $active_fees as $fee ) {
		$refundable = isset( $fee['refundable'] ) ? $fee['refundable'] : 'no';
		if ( $refundable === 'yes' ) {
			$refundable_fees[] = $fee;
		}
	}
	
	return $refundable_fees;
}

/**
 * Get taxable fees
 * @param int $item_id
 * @return array
 * @since 1.0.0
 */
function rbfw_get_taxable_fees( $item_id ) {
	$active_fees = rbfw_get_active_fees( $item_id );
	$taxable_fees = array();
	
	foreach ( $active_fees as $fee ) {
		$taxable = isset( $fee['taxable'] ) ? $fee['taxable'] : 'no';
		if ( $taxable === 'yes' ) {
			$taxable_fees[] = $fee;
		}
	}
	
	return $taxable_fees;
}

/**
 * Display fees in checkout/booking summary
 * @param int $item_id
 * @param float $base_price
 * @param int $days
 * @return string
 * @since 1.0.0
 */
function rbfw_display_fee_summary( $item_id, $base_price, $days = 1 ) {
	$fee_calculation = rbfw_calculate_total_fees( $item_id, $base_price, $days );
	
	if ( empty( $fee_calculation['fees'] ) ) {
		return '';
	}
	
	ob_start();
	?>
	<div class="rbfw-fee-summary">
		<h4><?php echo esc_html__( 'Additional Fees', 'booking-and-rental-manager-for-woocommerce' ); ?></h4>
		<div class="rbfw-fee-items">
			<?php foreach ( $fee_calculation['fees'] as $fee ) : ?>
				<div class="rbfw-fee-item">
					<span class="rbfw-fee-label"><?php echo esc_html( $fee['label'] ); ?></span>
					<?php if ( ! empty( $fee['description'] ) ) : ?>
						<span class="rbfw-fee-description"><?php echo esc_html( $fee['description'] ); ?></span>
					<?php endif; ?>
					<span class="rbfw-fee-amount"><?php echo wc_price( $fee['amount'] ); ?></span>
					<?php if ( $fee['refundable'] === 'yes' ) : ?>
						<span class="rbfw-fee-refundable"><?php echo esc_html__( '(Refundable)', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="rbfw-fee-total">
			<strong><?php echo esc_html__( 'Total Additional Fees: ', 'booking-and-rental-manager-for-woocommerce' ); ?><?php echo wc_price( $fee_calculation['total_amount'] ); ?></strong>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Add fees to WooCommerce cart
 * @param array $cart_item_data
 * @param int $product_id
 * @param int $variation_id
 * @return array
 * @since 1.0.0
 */
function rbfw_add_fees_to_cart_item( $cart_item_data, $product_id, $variation_id = 0 ) {
	// Get rental item ID (might be different from product ID)
	$item_id = $product_id; // This might need adjustment based on your plugin structure
	
	// Get booking details from cart item data or session
	$base_price = isset( $cart_item_data['rbfw_price'] ) ? $cart_item_data['rbfw_price'] : 0;
	$days = isset( $cart_item_data['rbfw_rental_days'] ) ? $cart_item_data['rbfw_rental_days'] : 1;
	
	// Calculate fees for all active fees
	$fee_calculation = rbfw_calculate_total_fees( $item_id, $base_price, $days );
	
	if ( ! empty( $fee_calculation['fees'] ) ) {
		$cart_item_data['rbfw_fees'] = $fee_calculation['fees'];
		$cart_item_data['rbfw_total_fees'] = $fee_calculation['total_amount'];
	}
	
	return $cart_item_data;
}

/**
 * Apply fees to WooCommerce product price
 * @param float $price
 * @param object $cart_item
 * @param string $cart_item_key
 * @return float
 * @since 1.0.0
 */
function rbfw_apply_fees_to_price( $price, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['rbfw_total_fees'] ) && $cart_item['rbfw_total_fees'] > 0 ) {
		return $price + $cart_item['rbfw_total_fees'];
	}
	
	return $price;
}

/**
 * Save fee information to order meta
 * @param int $order_id
 * @param array $cart_item
 * @param string $cart_item_key
 * @param int $item_id
 * @since 1.0.0
 */
function rbfw_save_fees_to_order( $order_id, $cart_item, $cart_item_key, $item_id ) {
	if ( isset( $cart_item['rbfw_fees'] ) ) {
		wc_add_order_item_meta( $item_id, '_rbfw_fees', $cart_item['rbfw_fees'] );
		wc_add_order_item_meta( $item_id, '_rbfw_total_fees', $cart_item['rbfw_total_fees'] );
	}
}

// Hook the fee functionality into WooCommerce if WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {
	// Add fees to cart item data
	add_filter( 'woocommerce_add_cart_item_data', 'rbfw_add_fees_to_cart_item', 10, 3 );
	
	// Apply fees to product price in cart
	add_filter( 'woocommerce_cart_item_price', 'rbfw_apply_fees_to_price', 10, 3 );
	
	// Save fee information to order
	add_action( 'woocommerce_add_order_item_meta', 'rbfw_save_fees_to_order', 10, 4 );
}