<?php
/*
 * Fee Management Functions for Booking and Rental Manager
 * @Author 		mage people
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Fee_Functions' ) ) {
	class RBFW_Fee_Functions {
		
		/**
		 * Get fees for a specific rental item
		 */
		public static function get_rental_fees( $post_id ) {
			$fees = get_post_meta( $post_id, 'rbfw_fees', true );
			return is_array( $fees ) ? $fees : [];
		}

		/**
		 * Calculate fees for a booking
		 */
		public static function calculate_booking_fees( $post_id, $booking_data = [] ) {
			$fees = self::get_rental_fees( $post_id );
			$calculated_fees = [];
			$total_fee_amount = 0;

			foreach ( $fees as $fee ) {
				if ( ! $fee['active'] ) {
					continue;
				}

				$fee_amount = self::calculate_fee_amount( $fee, $booking_data );
				if ( $fee_amount > 0 ) {
					$calculated_fees[] = [
						'label' => $fee['label'],
						'description' => $fee['description'],
						'amount' => $fee_amount,
						'calculation_type' => $fee['calculation_type'],
						'frequency' => $fee['frequency'],
						'when_to_apply' => $fee['when_to_apply'],
						'refundable' => $fee['refundable'],
						'taxable' => $fee['taxable'],
						'icon' => $fee['icon'],
						'icon_class' => $fee['icon_class']
					];
					$total_fee_amount += $fee_amount;
				}
			}

			return [
				'fees' => $calculated_fees,
				'total_amount' => $total_fee_amount
			];
		}

		/**
		 * Calculate individual fee amount
		 */
		public static function calculate_fee_amount( $fee, $booking_data ) {
			$base_amount = isset( $booking_data['base_amount'] ) ? floatval( $booking_data['base_amount'] ) : 0;
			$days = isset( $booking_data['days'] ) ? intval( $booking_data['days'] ) : 1;
			$quantity = isset( $booking_data['quantity'] ) ? intval( $booking_data['quantity'] ) : 1;

			$fee_amount = 0;

			// Calculate based on calculation type
			if ( $fee['calculation_type'] === 'percentage' ) {
				$fee_amount = ( $base_amount * floatval( $fee['amount'] ) ) / 100;
			} else {
				$fee_amount = floatval( $fee['amount'] );
			}

			// Apply frequency multiplier
			if ( $fee['frequency'] === 'per_day' ) {
				$fee_amount = $fee_amount * $days;
			} elseif ( $fee['frequency'] === 'per_night' ) {
				$fee_amount = $fee_amount * max( 1, $days - 1 );
			}

			// Apply quantity multiplier
			$fee_amount = $fee_amount * $quantity;

			return round( $fee_amount, 2 );
		}

		/**
		 * Display fees in booking summary
		 */
		public static function display_booking_fees( $post_id, $booking_data = [] ) {
			$fee_data = self::calculate_booking_fees( $post_id, $booking_data );
			
			if ( empty( $fee_data['fees'] ) ) {
				return '';
			}

			ob_start();
			?>
			<div class="rbfw-fees-section">
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
							<?php echo wp_kses( wc_price( $fee['amount'] ), rbfw_allowed_html() ); ?>
							<?php if ( $fee['refundable'] ) : ?>
								<span class="rbfw-fee-badge refundable"><?php esc_html_e( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<?php else : ?>
								<span class="rbfw-fee-badge non-refundable"><?php esc_html_e( 'Non-refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Add fees to cart item data
		 */
		public static function add_fees_to_cart_item( $cart_item_data, $post_id, $booking_data = [] ) {
			$fee_data = self::calculate_booking_fees( $post_id, $booking_data );
			
			if ( ! empty( $fee_data['fees'] ) ) {
				$cart_item_data['rbfw_fees'] = $fee_data['fees'];
				$cart_item_data['rbfw_fees_total'] = $fee_data['total_amount'];
			}

			return $cart_item_data;
		}

		/**
		 * Display fees in cart
		 */
		public static function display_cart_fees( $cart_item ) {
			if ( empty( $cart_item['rbfw_fees'] ) ) {
				return '';
			}

			ob_start();
			?>
			<div class="rbfw-cart-fees">
				<?php foreach ( $cart_item['rbfw_fees'] as $fee ) : ?>
					<tr class="rbfw-cart-fee-row">
						<th>
							<div class="rbfw-fee-cart-info">
								<span class="rbfw-fee-cart-icon"><?php echo esc_html( $fee['icon'] ); ?></span>
								<span class="rbfw-fee-cart-label"><?php echo esc_html( $fee['label'] ); ?></span>
								<?php if ( $fee['refundable'] ) : ?>
									<span class="rbfw-fee-cart-badge refundable"><?php esc_html_e( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</div>
						</th>
						<td><?php echo wp_kses( wc_price( $fee['amount'] ), rbfw_allowed_html() ); ?></td>
					</tr>
				<?php endforeach; ?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Add fees to WooCommerce order
		 */
		public static function add_fees_to_order( $order, $cart_item ) {
			if ( empty( $cart_item['rbfw_fees'] ) ) {
				return;
			}

			foreach ( $cart_item['rbfw_fees'] as $fee ) {
				$fee_name = $fee['label'];
				if ( $fee['refundable'] ) {
					$fee_name .= ' (' . __( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ) . ')';
				}

				$order->add_fee( $fee_name, $fee['amount'] );
			}
		}

		/**
		 * Get fee options for admin display
		 */
		public static function get_fee_options() {
			return [
				'icons' => [
					'🔒' => 'security',
					'🛡️' => 'insurance', 
					'🧹' => 'cleaning',
					'🐾' => 'pet',
					'💰' => 'money',
					'🚗' => 'vehicle',
					'🏠' => 'accommodation',
					'🍽️' => 'food',
					'🎫' => 'ticket',
					'📱' => 'service'
				],
				'calculation_types' => [
					'percentage' => __( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ),
					'fixed' => __( 'Fixed Amount', 'booking-and-rental-manager-for-woocommerce' )
				],
				'frequencies' => [
					'one_time' => __( 'One-time', 'booking-and-rental-manager-for-woocommerce' ),
					'per_day' => __( 'Per day', 'booking-and-rental-manager-for-woocommerce' ),
					'per_night' => __( 'Per night', 'booking-and-rental-manager-for-woocommerce' )
				],
				'when_to_apply' => [
					'at_booking' => __( 'At booking', 'booking-and-rental-manager-for-woocommerce' ),
					'at_checkin' => __( 'At check-in', 'booking-and-rental-manager-for-woocommerce' )
				]
			];
		}

		/**
		 * Validate fee data
		 */
		public static function validate_fee_data( $fee_data ) {
			$errors = [];

			if ( empty( $fee_data['label'] ) ) {
				$errors[] = __( 'Fee label is required', 'booking-and-rental-manager-for-woocommerce' );
			}

			if ( empty( $fee_data['amount'] ) || $fee_data['amount'] < 0 ) {
				$errors[] = __( 'Fee amount must be a positive number', 'booking-and-rental-manager-for-woocommerce' );
			}

			if ( $fee_data['calculation_type'] === 'percentage' && $fee_data['amount'] > 100 ) {
				$errors[] = __( 'Percentage fees cannot exceed 100%', 'booking-and-rental-manager-for-woocommerce' );
			}

			return $errors;
		}

		/**
		 * Get fee statistics for admin dashboard
		 */
		public static function get_fee_statistics( $post_id ) {
			$fees = self::get_rental_fees( $post_id );
			$stats = [
				'total_fees' => count( $fees ),
				'active_fees' => 0,
				'refundable_fees' => 0,
				'taxable_fees' => 0
			];

			foreach ( $fees as $fee ) {
				if ( $fee['active'] ) {
					$stats['active_fees']++;
				}
				if ( $fee['refundable'] ) {
					$stats['refundable_fees']++;
				}
				if ( $fee['taxable'] ) {
					$stats['taxable_fees']++;
				}
			}

			return $stats;
		}
	}
}
