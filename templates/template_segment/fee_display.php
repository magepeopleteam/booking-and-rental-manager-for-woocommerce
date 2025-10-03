<?php
/**
 * Fee Display Template
 * Displays fees in booking summary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $rbfw;
$rbfw_id = get_the_ID();

// Get fees for this rental item
$fees = RBFW_Fee_Functions::get_rental_fees( $rbfw_id );

if ( empty( $fees ) ) {
	return;
}

// Calculate booking data for fee calculation
$booking_data = [
	'base_amount' => 0, // This will be calculated by JavaScript
	'days' => 1,
	'quantity' => 1
];

// Get calculated fees
$fee_data = RBFW_Fee_Functions::calculate_booking_fees( $rbfw_id, $booking_data );

if ( empty( $fee_data['fees'] ) ) {
	return;
}
?>

<div class="rbfw-fees-section" style="display: none;">
	<?php foreach ( $fee_data['fees'] as $fee ) : ?>
		<li class="rbfw-fee-item">
			<div class="rbfw-fee-info">
				<div class="rbfw-fee-icon <?php echo esc_attr( $fee['icon_class'] ); ?>">
					<?php echo esc_html( $fee['icon'] ); ?>
				</div>
				<div class="rbfw-fee-details">
					<span class="rbfw-fee-label"><?php echo esc_html( $fee['label'] ); ?></span>
					<?php if ( ! empty( $fee['description'] ) ) : ?>
						<span class="rbfw-fee-description"><?php echo esc_html( $fee['description'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="rbfw-fee-amount">
				<span class="rbfw-fee-price" data-fee-amount="<?php echo esc_attr( $fee['amount'] ); ?>">
					<?php echo wp_kses( wc_price( $fee['amount'] ), rbfw_allowed_html() ); ?>
				</span>
				<?php if ( $fee['refundable'] ) : ?>
					<span class="rbfw-fee-badge refundable"><?php esc_html_e( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				<?php else : ?>
					<span class="rbfw-fee-badge non-refundable"><?php esc_html_e( 'Non-refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</div>

<style>
.rbfw-fees-section {
	margin: 10px 0;
}

.rbfw-fee-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 0;
	border-bottom: 1px solid #eee;
}

.rbfw-fee-item:last-child {
	border-bottom: none;
}

.rbfw-fee-info {
	display: flex;
	align-items: center;
	gap: 10px;
}

.rbfw-fee-icon {
	width: 24px;
	height: 24px;
	border-radius: 4px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 14px;
}

.rbfw-fee-icon.security {
	background: #fee;
}

.rbfw-fee-icon.insurance {
	background: #e3f2fd;
}

.rbfw-fee-icon.cleaning {
	background: #f3e5f5;
}

.rbfw-fee-icon.pet {
	background: #fff3e0;
}

.rbfw-fee-details {
	display: flex;
	flex-direction: column;
}

.rbfw-fee-label {
	font-weight: 600;
	color: #333;
}

.rbfw-fee-description {
	font-size: 12px;
	color: #666;
}

.rbfw-fee-amount {
	display: flex;
	flex-direction: column;
	align-items: flex-end;
	gap: 4px;
}

.rbfw-fee-price {
	font-weight: 600;
	color: #333;
}

.rbfw-fee-badge {
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 600;
	text-transform: uppercase;
}

.rbfw-fee-badge.refundable {
	background: #d4edda;
	color: #155724;
}

.rbfw-fee-badge.non-refundable {
	background: #f8d7da;
	color: #721c24;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Update fees when booking data changes
	function updateFees() {
		var baseAmount = parseFloat($('.duration-costing .price-figure').attr('data-price')) || 0;
		var days = parseInt($('input[name="rbfw_start_date"]').length > 0 ? 
			calculateDays() : 1);
		var quantity = parseInt($('input[name="rbfw_item_quantity"]').val()) || 1;
		
		$('.rbfw-fee-item').each(function() {
			var $fee = $(this);
			var feeAmount = parseFloat($fee.find('.rbfw-fee-price').attr('data-fee-amount')) || 0;
			var calculationType = $fee.data('calculation-type') || 'fixed';
			var frequency = $fee.data('frequency') || 'one_time';
			
			// Calculate fee based on type
			if (calculationType === 'percentage') {
				feeAmount = (baseAmount * feeAmount) / 100;
			}
			
			// Apply frequency
			if (frequency === 'per_day') {
				feeAmount = feeAmount * days;
			} else if (frequency === 'per_night') {
				feeAmount = feeAmount * Math.max(1, days - 1);
			}
			
			// Apply quantity
			feeAmount = feeAmount * quantity;
			
			// Update display
			$fee.find('.rbfw-fee-price').text(wc_price(feeAmount));
		});
	}
	
	function calculateDays() {
		var startDate = $('input[name="rbfw_start_date"]').val();
		var endDate = $('input[name="rbfw_end_date"]').val();
		
		if (startDate && endDate) {
			var start = new Date(startDate);
			var end = new Date(endDate);
			var diffTime = Math.abs(end - start);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
			return diffDays;
		}
		return 1;
	}
	
	// Update fees when price changes
	$(document).on('change', '.rbfw-costing .price-figure', updateFees);
	$(document).on('change', 'input[name="rbfw_start_date"], input[name="rbfw_end_date"]', updateFees);
	$(document).on('change', 'input[name="rbfw_item_quantity"]', updateFees);
	
	// Initial update
	updateFees();
});
</script>
