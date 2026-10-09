<?php
	/*
	* Author 	:	MagePeople Team
	* Copyright	: 	mage-people.com
	* Developer :   Ariful
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( ! class_exists( 'Rbfw_Thankyou_Page' ) ) {
		class Rbfw_Thankyou_Page {
			public function __construct() {
				add_action( 'wp_loaded', array( $this, 'rbfw_thankyou_page' ) );
				add_shortcode( 'rbfw_thankyou', array( $this, 'rbfw_thankyou_shortcode_func' ) );
				add_filter( 'display_post_states', array( $this, 'rbfw_add_post_state' ), 10, 2 );
			}

			public function rbfw_thankyou_page() {
				$t_page_id = rbfw_get_option( 'rbfw_thankyou_page', 'rbfw_basic_gen_settings' );
				if ( $t_page_id ) {
					if ( empty( get_post_meta( $t_page_id, 'rbfw_thankyou_page', true ) ) ) {
						$args = array(
							'ID'           => $t_page_id,
							'post_content' => '[rbfw_thankyou]',
						);
						wp_update_post( $args );
						update_post_meta( $t_page_id, 'rbfw_thankyou_page', 'generated' );
					}
				} else {
					$page_obj = rbfw_exist_page_by_title( 'Thank You' );
					if ( $page_obj === false ) {
						$args    = array(
							'post_title'   => 'Thank You',
							'post_content' => '[rbfw_thankyou]',
							'post_status'  => 'publish',
							'post_type'    => 'page'
						);
						$post_id = wp_insert_post( $args );
						if ( $post_id ) {
							$gen_settings     = ! empty( get_option( 'rbfw_basic_gen_settings' ) ) ? get_option( 'rbfw_basic_gen_settings' ) : [];
							$new_gen_settings = array_merge( $gen_settings, [ 'rbfw_thankyou_page' => $post_id ] );
							update_option( 'rbfw_basic_gen_settings', $new_gen_settings );
							update_post_meta( $post_id, 'rbfw_thankyou_page', 'generated' );
						}
					}
				}
			}

			public function rbfw_add_post_state( $post_states, $post ) {
				$t_page_id = rbfw_get_option( 'rbfw_thankyou_page', 'rbfw_basic_gen_settings' );
				if ( ! empty( $t_page_id ) ) {
					if ( $post->ID == $t_page_id ) {
						$post_states[] = 'Thank You Page';
					}
				}

				return $post_states;
			}

			/**
			 * Render the Order Received page: a success banner followed by
			 * card sections (Order Information, Booking Details, Customer
			 * Information, Price Summary) instead of nested HTML tables.
			 * Shared by both payment-confirmation code paths below (the
			 * PayPal/Stripe return and the offline-payment email link) so
			 * the design lives in one place rather than two copies.
			 *
			 * @param array $v {
			 *     @type int    $order_id
			 *     @type string $order_date          Pre-formatted date + time.
			 *     @type string $billing_name
			 *     @type string $billing_email
			 *     @type string $payment_method
			 *     @type string $payment_id          '' when not available (offline payments).
			 *     @type string $item_name
			 *     @type string $rent_type
			 *     @type string $package             Resort package name, '' otherwise.
			 *     @type array  $rent_info            key => value pairs (Rent/Room Information).
			 *     @type array  $service_info         key => value pairs (Extra Service Information).
			 *     @type array  $regf_rows            Pre-computed rbfw_regf_display_rows() output.
			 *     @type string $rbfw_start_datetime
			 *     @type string $rbfw_end_datetime
			 *     @type array  $variation_info
			 *     @type string $item_quantity
			 *     @type string $duration_cost        Pre-formatted price (wc_price()).
			 *     @type string $service_cost          Pre-formatted price.
			 *     @type string $discount_amount       Pre-formatted price, '' when none.
			 *     @type string $total_cost            Pre-formatted price.
			 *     @type string $tax_status
			 * }
			 * @return string
			 */
			private function render_thankyou_markup( $v ) {
				global $rbfw;
				$rent_type = $v['rent_type'];
				ob_start();
				?>
				<div class="rbfw-order-received">

					<div class="rbfw-or-banner">
						<div class="rbfw-or-banner-icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
						</div>
						<h1 class="rbfw-or-banner-title"><?php rbfw_string( 'rbfw_text_thankyou_ur_order_received', __( 'Thank you. Your order has been received.', 'booking-and-rental-manager-for-woocommerce' ) ); ?></h1>
						<div class="rbfw-or-banner-chip">
							<?php rbfw_string( 'rbfw_text_order_number', __( 'Order number', 'booking-and-rental-manager-for-woocommerce' ) ); ?> #<?php echo esc_html( $v['order_id'] ); ?>
						</div>
					</div>

					<?php do_action( 'rbfw_before_thankyou_page_info', $v['order_id'] ); ?>

					<div class="rbfw-or-card">
						<div class="rbfw-or-card-label"><?php rbfw_string( 'rbfw_text_order_received', __( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
						<div class="rbfw-or-grid">
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_order_number', __( 'Order number', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value">#<?php echo esc_html( $v['order_id'] ); ?></div>
							</div>
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_order_created_date', __( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value"><?php echo esc_html( $v['order_date'] ); ?></div>
							</div>
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_name', __( 'Name', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value"><?php echo esc_html( $v['billing_name'] ); ?></div>
							</div>
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_email', __( 'Email', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value"><?php echo esc_html( $v['billing_email'] ); ?></div>
							</div>
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_payment_method', __( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value"><?php echo esc_html( $v['payment_method'] ); ?></div>
							</div>
							<?php if ( ! empty( $v['payment_id'] ) ) : ?>
							<div class="rbfw-or-grid-item">
								<div class="rbfw-or-grid-label"><?php rbfw_string( 'rbfw_text_payment_id', __( 'Payment ID', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<div class="rbfw-or-grid-value"><?php echo esc_html( $v['payment_id'] ); ?></div>
							</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="rbfw-or-card">
						<div class="rbfw-or-card-label"><?php rbfw_string( 'rbfw_text_item_information', __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>

						<div class="rbfw-or-row rbfw-or-row-main">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo esc_html( $v['item_name'] ); ?></span>
						</div>

						<?php if ( $rent_type === 'resort' && ! empty( $v['package'] ) ) : ?>
						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_package', __( 'Package', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo esc_html( $v['package'] ); ?></span>
						</div>
						<?php endif; ?>

						<?php if ( ( $rent_type === 'bike_car_sd' || $rent_type === 'appointment' || $rent_type === 'resort' ) && ! empty( $v['rent_info'] ) ) : ?>
						<div class="rbfw-or-subsection">
							<div class="rbfw-or-subsection-label">
								<?php
								if ( $rent_type === 'resort' ) {
									rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) );
								} else {
									rbfw_string( 'rbfw_text_rent_information', __( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) );
								}
								?>
							</div>
							<?php foreach ( $v['rent_info'] as $key => $value ) : ?>
							<div class="rbfw-or-row">
								<span class="rbfw-or-row-label"><?php echo esc_html( $key ); ?></span>
								<span class="rbfw-or-row-value"><?php echo wp_kses_post( $value ); ?></span>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $v['service_info'] ) ) : ?>
						<div class="rbfw-or-subsection">
							<div class="rbfw-or-subsection-label"><?php rbfw_string( 'rbfw_text_extra_service_information', __( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
							<?php foreach ( $v['service_info'] as $key => $value ) : ?>
							<div class="rbfw-or-row">
								<span class="rbfw-or-row-label"><?php echo esc_html( $key ); ?></span>
								<span class="rbfw-or-row-value"><?php echo wp_kses_post( $value ); ?></span>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<div class="rbfw-or-divider"></div>

						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_start_date_and_time', __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo esc_html( $v['rbfw_start_datetime'] ); ?></span>
						</div>
						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_end_date_and_time', __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo esc_html( $v['rbfw_end_datetime'] ); ?></span>
						</div>

						<?php if ( ! empty( $v['variation_info'] ) ) :
							foreach ( $v['variation_info'] as $value ) : ?>
							<div class="rbfw-or-row">
								<span class="rbfw-or-row-label"><?php echo esc_html( $value['field_label'] ?? '' ); ?></span>
								<span class="rbfw-or-row-value"><?php
									$vi_text  = esc_html( $value['field_value'] ?? '' );
									$vi_qty   = isset( $value['qty'] ) ? (int) $value['qty'] : 0;
									$vi_price = isset( $value['price'] ) ? (float) $value['price'] : 0;
									if ( $vi_qty > 0 ) { $vi_text .= ' &times; ' . esc_html( $vi_qty ); }
									if ( $vi_price > 0 ) { $vi_text .= ' <span class="rbfw_variation_surcharge">(+' . wp_kses_post( wc_price( $vi_price ) ) . ')</span>'; }
									echo wp_kses_post( $vi_text );
								?></span>
							</div>
							<?php endforeach;
						endif; ?>

						<?php if ( ! empty( $v['item_quantity'] ) ) : ?>
						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label">
								<?php
								if ( $rbfw->get_option_trans( 'rbfw_text_quantity', 'rbfw_basic_translation_settings' ) && want_loco_translate() === 'no' ) {
									echo esc_html( $rbfw->get_option_trans( 'rbfw_text_quantity', 'rbfw_basic_translation_settings' ) );
								} else {
									echo esc_html__( 'Quantity', 'booking-and-rental-manager-for-woocommerce' );
								}
								?>
							</span>
							<span class="rbfw-or-row-value"><?php echo esc_html( $v['item_quantity'] ); ?></span>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $v['regf_rows'] ) ) : ?>
						<div class="rbfw-or-subsection">
							<div class="rbfw-or-subsection-label"><?php rbfw_string( 'rbfw_text_customer_information', __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
							<div class="rbfw-or-list">
								<?php foreach ( $v['regf_rows'] as $info ) :
									$label = $info['label'];
									$value = $info['value'];
									if ( ! empty( $info['heading'] ) ) : ?>
										<div class="rbfw-or-list-heading"><?php echo esc_html( $label ); ?></div>
										<?php continue;
									endif;
									if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
										$value_markup = '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener">' . esc_html__( 'View File', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
									} else {
										$value_markup = esc_html( $value );
									}
									?>
									<div class="rbfw-or-row">
										<span class="rbfw-or-row-label"><?php echo esc_html( $label ); ?></span>
										<span class="rbfw-or-row-value"><?php echo wp_kses_post( $value_markup ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>

					<div class="rbfw-or-card rbfw-or-summary">
						<div class="rbfw-or-card-label"><?php esc_html_e( 'Price Summary', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_duration_cost', __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo wp_kses_post( $v['duration_cost'] ); ?></span>
						</div>
						<div class="rbfw-or-row">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_resource_cost', __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo wp_kses_post( $v['service_cost'] ); ?></span>
						</div>
						<?php if ( ! empty( $v['discount_amount'] ) ) : ?>
						<div class="rbfw-or-row rbfw-or-row-discount">
							<span class="rbfw-or-row-label">
								<?php
								if ( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' ) && want_loco_translate() === 'no' ) {
									echo esc_html( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' ) );
								} else {
									echo esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' );
								}
								?>
							</span>
							<span class="rbfw-or-row-value">&minus;<?php echo wp_kses_post( $v['discount_amount'] ); ?></span>
						</div>
						<?php endif; ?>
						<div class="rbfw-or-row rbfw-or-row-total">
							<span class="rbfw-or-row-label"><?php rbfw_string( 'rbfw_text_total_cost', __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
							<span class="rbfw-or-row-value"><?php echo wp_kses_post( $v['total_cost'] ) . ' ' . esc_html( $v['tax_status'] ); ?></span>
						</div>
					</div>

					<?php do_action( 'rbfw_after_thankyou_page_info', $v['order_id'] ); ?>
				</div>
				<?php
				return ob_get_clean();
			}

			public function rbfw_thankyou_shortcode_func() {

				global $rbfw;
				$t_page_id           = rbfw_get_option( 'rbfw_thankyou_page', 'rbfw_basic_gen_settings' );
				$current_page_id     = get_queried_object_id();
				$checkout_account    = $rbfw->get_option_trans( 'rbfw_mps_checkout_account', 'rbfw_basic_payment_settings', 'on' );
				if ( $current_page_id != $t_page_id ) {
					return;
				}
				if ( $checkout_account == 'on' && ! is_user_logged_in() ) {
					return;
				}
				// For Paypal and stripe Payment
				if ( isset( $_GET['paymentId'] ) ) {
					$payment_id = sanitize_text_field( wp_unslash( $_GET['paymentId'] ) );
					$payer_id   = ! empty( $_GET['PayerID'] ) ? sanitize_text_field( wp_unslash( $_GET['PayerID'] ) ) : '';
					$args       = array(
						'post_type'  => 'rbfw_order',
						'meta_query' => array(
							array(
								'key'     => 'rbfw_reference',
								'value'   => $payment_id,
								'compare' => '='
							),
						)
					);
					$the_query  = new WP_Query( $args );
					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							global $post;
							$order_id        = $post->ID;
							$billing_name    = get_post_meta( $order_id, 'rbfw_billing_name', true );
							$billing_email   = get_post_meta( $order_id, 'rbfw_billing_email', true );
							$payment_method  = get_post_meta( $order_id, 'rbfw_payment_method', true );
							$ticket_info     = ! empty( get_post_meta( $order_id, 'rbfw_ticket_info', true )[0] ) ? get_post_meta( $order_id, 'rbfw_ticket_info', true )[0] : [];
							$item_name       = $ticket_info['ticket_name'] ? $ticket_info['ticket_name'] : '';
							$rbfw_id         = $ticket_info['rbfw_id'];
							$item_id         = $rbfw_id;
							$rent_type       = $ticket_info['rbfw_rent_type'];
							$rbfw_start_time = ! empty( $ticket_info['rbfw_start_time'] ) ? $ticket_info['rbfw_start_time'] : '';
							$rbfw_end_time   = ! empty( $ticket_info['rbfw_end_time'] ) ? $ticket_info['rbfw_end_time'] : '';
							if ( $rent_type == 'resort' || ! rbfw_booking_has_time( $rbfw_start_time ) ) {
								$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-text' );
								$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-text' );
							} elseif ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
								$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
								$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date' );
							} else {
								$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
								$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-time-text' );
							}
							$tax        = ! empty( $ticket_info['rbfw_mps_tax'] ) ? $ticket_info['rbfw_mps_tax'] : 0;
							// Recorded tax for this booking; empty string when the order carried none.
							$tax_status = rbfw_booking_tax_note( $order_id );

							if ( ! empty( $_GET['paymentStatus'] ) && empty( get_post_meta( $order_id, 'rbfw_payment_status', true ) ) ) {
								$paymentStatus = sanitize_text_field( wp_unslash( $_GET['paymentStatus'] ) );
							} else {
								$paymentStatus = get_post_meta( $order_id, 'rbfw_payment_status', true );
							}
							update_post_meta( $order_id, 'rbfw_payment_id', $payment_id );
							update_post_meta( $order_id, 'rbfw_payer_id', $payer_id );
							update_post_meta( $order_id, 'rbfw_payment_status', $paymentStatus );
							update_post_meta( $order_id, 'rbfw_order_status', 'processing' );

							$item_quantity = '';
							if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
								$BikeCarSdClass = new RBFW_BikeCarSd_Function();
								$rent_info      = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
								$service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
								$rent_info      = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info( $item_id, $rent_info );
								$service_info   = $BikeCarSdClass->rbfw_get_bikecarsd_service_info( $item_id, $service_info );
							} elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
								$BikeCarMdClass = new RBFW_BikeCarMd_Function();
								$service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
								$service_info   = $BikeCarMdClass->rbfw_get_bikecarmd_service_info( $item_id, $service_info );
								$item_quantity  = ! empty( $ticket_info['rbfw_item_quantity'] ) ? $ticket_info['rbfw_item_quantity'] : '';
							}elseif ( $rent_type == 'multiple_items' ) {
                                $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                                $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                                $service_info   = $BikeCarMdClass->rbfw_get_bikecarmd_service_info( $item_id, $service_info );
                                $item_quantity  = ! empty( $ticket_info['rbfw_item_quantity'] ) ? $ticket_info['rbfw_item_quantity'] : '';
                            }
                            elseif ( $rent_type == 'resort' ) {
								$ResortClass  = new RBFW_Resort_Function();
								$package      = ! empty( $ticket_info['rbfw_resort_package'] ) ? $ticket_info['rbfw_resort_package'] : '';
								$rent_info    = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
								$rent_info    = $ResortClass->rbfw_get_resort_room_info( $item_id, $rent_info, $package );
								$service_info = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
								$service_info = $ResortClass->rbfw_get_resort_service_info( $item_id, $service_info );
							} else {
								$rent_info    = '';
								$service_info = '';
							}
							$variation_info  = ! empty( $ticket_info['rbfw_variation_info'] ) ? $ticket_info['rbfw_variation_info'] : [];
							$duration_cost   = wc_price( $ticket_info['duration_cost'] );
							$service_cost    = wc_price( $ticket_info['service_cost'] );
							$total_cost      = wc_price( $ticket_info['ticket_price'] );
							$discount_amount = ! empty( $ticket_info['discount_amount'] ) ? wc_price( $ticket_info['discount_amount'] ) : '';
							$rbfw_regf_info  = ! empty( $ticket_info['rbfw_regf_info'] ) ? $ticket_info['rbfw_regf_info'] : [];
							$regf_rows       = ! empty( $rbfw_regf_info ) ? rbfw_regf_display_rows( $ticket_info ) : [];
							$package         = isset( $package ) ? $package : '';

							return $this->render_thankyou_markup( array(
								'order_id'            => $order_id,
								'order_date'          => get_the_date( 'F j, Y' ) . ' ' . get_the_time(),
								'billing_name'        => $billing_name,
								'billing_email'       => $billing_email,
								'payment_method'      => $payment_method,
								'payment_id'          => $payment_id,
								'item_name'           => $item_name,
								'rent_type'           => $rent_type,
								'package'             => $package,
								'rent_info'           => $rent_info,
								'service_info'        => $service_info,
								'regf_rows'           => $regf_rows,
								'rbfw_start_datetime' => $rbfw_start_datetime,
								'rbfw_end_datetime'   => $rbfw_end_datetime,
								'variation_info'      => $variation_info,
								'item_quantity'       => $item_quantity,
								'duration_cost'       => $duration_cost,
								'service_cost'        => $service_cost,
								'discount_amount'     => $discount_amount,
								'total_cost'          => $total_cost,
								'tax_status'          => $tax_status,
							) );
						}
					}
				}
				// For Offline Payment
				if ( ! empty( $_GET['order_id'] ) && ! empty( $_GET['token'] ) ) {
					$order_id      = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
					$current_token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
					$origin_token  = get_post_meta( $order_id, 'rbfw_token', true );
					if ( $current_token != $origin_token ) {
						return;
					}
					$billing_name    = get_post_meta( $order_id, 'rbfw_billing_name', true );
					$billing_email   = get_post_meta( $order_id, 'rbfw_billing_email', true );
					$payment_method  = get_post_meta( $order_id, 'rbfw_payment_method', true );
					$ticket_info     = ! empty( get_post_meta( $order_id, 'rbfw_ticket_info', true )[0] ) ? get_post_meta( $order_id, 'rbfw_ticket_info', true )[0] : [];
					$item_name       = ! empty( $ticket_info['ticket_name'] ) ? $ticket_info['ticket_name'] : '';
					$rbfw_id         = $ticket_info['rbfw_id'];
					$item_id         = $rbfw_id;
					$rent_type       = $ticket_info['rbfw_rent_type'];
					$variation_info  = ! empty( $ticket_info['rbfw_variation_info'] ) ? $ticket_info['rbfw_variation_info'] : [];
					$rbfw_start_time = ! empty( $ticket_info['rbfw_start_time'] ) ? $ticket_info['rbfw_start_time'] : '';
					$rbfw_end_time   = ! empty( $ticket_info['rbfw_end_time'] ) ? $ticket_info['rbfw_end_time'] : '';
					if ( $rent_type == 'resort' || ( empty( $rbfw_start_time ) && empty( $rbfw_end_time ) ) ) {
						$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-text' );
						$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-text' );
					} elseif ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
						$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
						$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date' );
					} else {
						$rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
						$rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-time-text' );
					}
					$tax        = ! empty( $ticket_info['rbfw_mps_tax'] ) ? $ticket_info['rbfw_mps_tax'] : 0;
					$tax_status = rbfw_booking_tax_note( $order_id );

					$item_quantity = '';
					if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
						$BikeCarSdClass = new RBFW_BikeCarSd_Function();
						$rent_info      = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
						$service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
						$rent_info      = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info( $item_id, $rent_info );
						$service_info   = $BikeCarSdClass->rbfw_get_bikecarsd_service_info( $item_id, $service_info );
					} elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
						$BikeCarMdClass = new RBFW_BikeCarMd_Function();
						$service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
						$service_info   = $BikeCarMdClass->rbfw_get_bikecarmd_service_info( $item_id, $service_info );
						$item_quantity  = ! empty( $ticket_info['rbfw_item_quantity'] ) ? $ticket_info['rbfw_item_quantity'] : '';
					} elseif ( $rent_type == 'resort' ) {
						$ResortClass  = new RBFW_Resort_Function();
						$package      = ! empty( $ticket_info['rbfw_resort_package'] ) ? $ticket_info['rbfw_resort_package'] : '';
						$rent_info    = $ticket_info['rbfw_type_info'];
						$rent_info    = $ResortClass->rbfw_get_resort_room_info( $item_id, $rent_info, $package );
						$service_info = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
						$service_info = $ResortClass->rbfw_get_resort_service_info( $item_id, $service_info );
					} else {
						$rent_info    = '';
						$service_info = '';
					}
					$duration_cost   = wc_price( $ticket_info['duration_cost'] );
					$service_cost    = wc_price( $ticket_info['service_cost'] );
					$total_cost      = wc_price( $ticket_info['ticket_price'] );
					$discount_amount = ! empty( $ticket_info['discount_amount'] ) ? wc_price( $ticket_info['discount_amount'] ) : '';
					$rbfw_regf_info  = ! empty( $ticket_info['rbfw_regf_info'] ) ? $ticket_info['rbfw_regf_info'] : [];
					$regf_rows       = ! empty( $rbfw_regf_info ) ? rbfw_regf_display_rows( $ticket_info ) : [];
					$package         = isset( $package ) ? $package : '';

					return $this->render_thankyou_markup( array(
						'order_id'            => $order_id,
						'order_date'          => get_the_date( 'F j, Y', $order_id ) . ' ' . get_the_time( '', $order_id ),
						'billing_name'        => $billing_name,
						'billing_email'       => $billing_email,
						'payment_method'      => $payment_method,
						'payment_id'          => '',
						'item_name'           => $item_name,
						'rent_type'           => $rent_type,
						'package'             => $package,
						'rent_info'           => $rent_info,
						'service_info'        => $service_info,
						'regf_rows'           => $regf_rows,
						'rbfw_start_datetime' => $rbfw_start_datetime,
						'rbfw_end_datetime'   => $rbfw_end_datetime,
						'variation_info'      => $variation_info,
						'item_quantity'       => $item_quantity,
						'duration_cost'       => $duration_cost,
						'service_cost'        => $service_cost,
						'discount_amount'     => $discount_amount,
						'total_cost'          => $total_cost,
						'tax_status'          => $tax_status,
					) );
				}
			}
		}
		new Rbfw_Thankyou_Page();
	}
