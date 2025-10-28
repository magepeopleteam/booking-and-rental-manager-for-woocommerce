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

			public function rbfw_thankyou_shortcode_func() {

                if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }

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
							$tax_status = '';

							if ( ! empty( $_GET['paymentStatus'] ) && empty( get_post_meta( $order_id, 'rbfw_payment_status', true ) ) ) {
								$paymentStatus = sanitize_text_field( wp_unslash( $_GET['paymentStatus'] ) );
							} else {
								$paymentStatus = get_post_meta( $order_id, 'rbfw_payment_status', true );
							}
							update_post_meta( $order_id, 'rbfw_payment_id', $payment_id );
							update_post_meta( $order_id, 'rbfw_payer_id', $payer_id );
							update_post_meta( $order_id, 'rbfw_payment_status', $paymentStatus );
							update_post_meta( $order_id, 'rbfw_order_status', 'processing' );


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
							ob_start();
							?>
                            <div class="rbfw_thankyou_page_wrap">
                                <div class="mps_alert_login_success"><?php rbfw_string( 'rbfw_text_thankyou_ur_order_received', __( 'Thank you. Your order has been received.', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
								<?php do_action( 'rbfw_before_thankyou_page_info', $order_id ); ?>
                                <table>
                                    <thead>
                                    <tr>
                                        <th colspan="2"><?php rbfw_string( 'rbfw_text_order_received', __( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_order_number', __( 'Order number', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $order_id ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_order_created_date', __( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( get_the_date( 'F j, Y' ) ) . ' ' . esc_html( get_the_time() ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_name', __( 'Name', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $billing_name ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_email', __( 'Email', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $billing_email ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_payment_method', __( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $payment_method ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_payment_id', __( 'Payment ID', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $payment_id ); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <table>
                                    <thead>
                                    <tr>
                                        <th colspan="2"><?php rbfw_string( 'rbfw_text_item_information', __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) );
												echo ':'; ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $item_name ); ?></td>
                                    </tr>
									<?php if ( $rent_type == 'resort' ) { ?>
                                        <tr>
                                            <td><strong><?php rbfw_string( 'rbfw_text_package', __( 'Package', 'booking-and-rental-manager-for-woocommerce' ) );
														echo ':'; ?></strong></td>
                                            <td><?php echo esc_html( $package ); ?></td>
                                        </tr>
									<?php } ?>
									<?php if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) { ?>
                                        <tr>
                                            <td><strong><?php rbfw_string( 'rbfw_text_rent_information', __( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) );
														echo ':'; ?></strong></td>
                                            <td>
                                                <table>
													<?php
														if ( ! empty( $rent_info ) ) {
															foreach ( $rent_info as $key => $value ) {
																?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
																<?php
															}
														}
													?>
                                                </table>
                                            </td>
                                        </tr>
									<?php } ?>
									<?php if ( $rent_type == 'resort' ) { ?>
                                        <tr>
                                            <td><strong><?php rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) );
														echo ':'; ?></strong></td>
                                            <td>
                                                <table>
													<?php
														if ( ! empty( $rent_info ) ) {
															foreach ( $rent_info as $key => $value ) {
																?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
																<?php
															}
														}
													?>
                                                </table>
                                            </td>
                                        </tr>
									<?php } ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?php rbfw_string( 'rbfw_text_extra_service_information', __( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) );echo ':'; ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <table>
												<?php
													if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
														if ( ! empty( $service_info ) ) {
															foreach ( $service_info as $key => $value ) {
																?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
																<?php
															}
														}
													} elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
														if ( ! empty( $service_info ) ) {
															foreach ( $service_info as $key => $value ) {
																?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
																<?php
															}
														}
													} elseif ( $rent_type == 'multiple_items'  ) { echo 'fff';
                                                        if ( ! empty( $service_info ) ) {
                                                            foreach ( $service_info as $key => $value ) {
                                                                ?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }

                                                    elseif ( $rent_type == 'resort' ) {
														if ( ! empty( $service_info ) ) {
															foreach ( $service_info as $key => $value ) {
																?>
                                                                <tr>
                                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                                    <td><?php echo esc_html( $value ); ?></td>
                                                                </tr>
																<?php
															}
														}
													}
												?>
                                            </table>
                                        </td>
                                    </tr>

									<?php if ( ! empty( $rbfw_regf_info ) ) { ?>
                                        <tr>
                                            <td><strong><?php rbfw_string( 'rbfw_text_customer_information', __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) );
														echo ':'; ?></strong></td>
                                            <td>
                                                <ol>
													<?php
														foreach ( $rbfw_regf_info as $info ) {
															$label = $info['label'];
															$value = $info['value'];
															if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
																$value = '<a href="' . esc_url( $value ) . '" target="_blank" style="text-decoration:underline">' . esc_html__( 'View File', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
															}
															?>
                                                            <li><?php echo esc_html( $label ); ?>: <?php echo esc_html( $value ); ?></li>
															<?php
														}
													?>
                                                </ol>
                                            </td>
                                        </tr>
									<?php } ?>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_start_date_and_time', __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $rbfw_start_datetime ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_end_date_and_time', __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $rbfw_end_datetime ); ?></td>
                                    </tr>
									<?php if ( ! empty( $variation_info ) ) {
										foreach ( $variation_info as $key => $value ) {
											?>
                                            <tr>
                                                <td><strong><?php echo esc_html( $value['field_label'] ); ?></strong></td>
                                                <td><?php echo esc_html( $value['field_value'] ); ?></td>
                                            </tr>
										<?php }
									} ?>
									<?php if ( ! empty( $item_quantity ) ) { ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?php
                                                    if($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                                        echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings'));
                                                    }else{
                                                        echo esc_html__('Quantity','booking-and-rental-manager-for-woocommerce');
                                                    }
                                                    ?>
                                                </strong>
                                            </td>
                                            <td><?php echo esc_html( $item_quantity ); ?></td>
                                        </tr>
									<?php } ?>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_duration_cost', __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $duration_cost ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_resource_cost', __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $service_cost ); ?></td>
                                    </tr>

									<?php 
									// Add WooCommerce Products to thankyou page
									$rbfw_wc_products_info = ! empty( $ticket_info['rbfw_wc_products_info'] ) ? $ticket_info['rbfw_wc_products_info'] : [];
									$rbfw_wc_products_total = ! empty( $ticket_info['rbfw_wc_products_total'] ) ? $ticket_info['rbfw_wc_products_total'] : 0;
									
									if ( ! empty( $rbfw_wc_products_info ) ) { ?>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Additional Products', 'booking-and-rental-manager-for-woocommerce' ); ?>:</strong></td>
                                            <td>
                                                <table style="border:1px solid #f5f5f5;margin:0;width: 100%;">
													<?php foreach ( $rbfw_wc_products_info as $product_id => $product_data ) { 
														$product = wc_get_product( $product_id );
														if ( $product ) { ?>
                                                            <tr>
                                                                <td style="border:1px solid #f5f5f5;"><strong><?php echo esc_html( $product->get_name() ); ?></strong></td>
                                                                <td style="border:1px solid #f5f5f5;">(<?php echo wc_price( $product_data['price'] ); ?> x <?php echo esc_html( $product_data['quantity'] ); ?>) = <?php echo wc_price( $product_data['total'] ); ?></td>
                                                            </tr>
														<?php }
													} ?>
                                                </table>
                                            </td>
                                        </tr>
									<?php } ?>

									<?php if ( ! empty( $discount_amount ) ) { ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?php
                                                    if($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                                        echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings'));
                                                    }else{
                                                        echo esc_html__('Discount','booking-and-rental-manager-for-woocommerce') . ' :';
                                                    }
                                                    ?>
                                                </strong>
                                            </td>
                                            <td><?php echo esc_html( $discount_amount ); ?></td>
                                        </tr>
									<?php } ?>
                                    <tr>
                                        <td><strong><?php rbfw_string( 'rbfw_text_total_cost', __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) );
													echo ':'; ?></strong></td>
                                        <td><?php echo esc_html( $total_cost ) . ' ' . esc_html( $tax_status ); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
								<?php do_action( 'rbfw_after_thankyou_page_info', $order_id ); ?>
                            </div>
							<?php
							return ob_get_clean();
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
					$tax_status = '';

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
					ob_start();
					?>
                    <div class="rbfw_thankyou_page_wrap">
                        <div class="mps_alert_login_success"><?php rbfw_string( 'rbfw_text_thankyou_ur_order_received', __( 'Thank you. Your order has been received.', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
						<?php do_action( 'rbfw_before_thankyou_page_info', $order_id ); ?>
                        <table>
                            <thead>
                            <tr>
                                <th colspan="2"><?php rbfw_string( 'rbfw_text_order_received', __( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_order_number', __( 'Order number', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $order_id ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_order_created_date', __( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( get_the_date( 'F j, Y', $order_id ) ) . ' ' . esc_html( get_the_time( '', $order_id ) ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_name', __( 'Name', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $billing_name ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_email', __( 'Email', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $billing_email ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_payment_method', __( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $payment_method ); ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <table>
                            <thead>
                            <tr>
                                <th colspan="2"><?php rbfw_string( 'rbfw_text_item_information', __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) );
										echo ':'; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $item_name ); ?></td>
                            </tr>
							<?php if ( $rent_type == 'resort' ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_package', __( 'Package', 'booking-and-rental-manager-for-woocommerce' ) );
												echo ':'; ?></strong></td>
                                    <td><?php echo esc_html( $package ); ?></td>
                                </tr>
							<?php } ?>
							<?php if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_rent_information', __( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) );
												echo ':'; ?></strong></td>
                                    <td>
                                        <table>
											<?php
												if ( ! empty( $rent_info ) ) {
													foreach ( $rent_info as $key => $value ) {
														?>
                                                        <tr>
                                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                            <td><?php echo esc_html( $value ); ?></td>
                                                        </tr>
														<?php
													}
												}
											?>
                                        </table>
                                    </td>
                                </tr>
							<?php } ?>
							<?php if ( $rent_type == 'resort' ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) );
												echo ':'; ?></strong></td>
                                    <td>
                                        <table>
											<?php
												if ( ! empty( $rent_info ) ) {
													foreach ( $rent_info as $key => $value ) {
														?>
                                                        <tr>
                                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                            <td><?php echo esc_html( $value ); ?></td>
                                                        </tr>
														<?php
													}
												}
											?>
                                        </table>
                                    </td>
                                </tr>
							<?php } ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_extra_service_information', __( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td>
                                    <table>
										<?php
											if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
												if ( ! empty( $service_info ) ) {
													foreach ( $service_info as $key => $value ) {
														?>
                                                        <tr>
                                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                            <td><?php echo esc_html( $value ); ?></td>
                                                        </tr>
														<?php
													}
												}
											} elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
												if ( ! empty( $service_info ) ) {
													foreach ( $service_info as $key => $value ) {
														?>
                                                        <tr>
                                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                            <td><?php echo esc_html( $value ); ?></td>
                                                        </tr>
														<?php
													}
												}
											} elseif ( $rent_type == 'resort' ) {
												if ( ! empty( $service_info ) ) {
													foreach ( $service_info as $key => $value ) {
														?>
                                                        <tr>
                                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                            <td><?php echo esc_html( $value ); ?></td>
                                                        </tr>
														<?php
													}
												}
											}
										?>
                                    </table>
                                </td>
                            </tr>
							<?php if ( ! empty( $rbfw_regf_info ) ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_customer_information', __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) );
												echo ':'; ?></strong></td>
                                    <td>
                                        <ol>
											<?php
												foreach ( $rbfw_regf_info as $info ) {
													$label = $info['label'];
													$value = $info['value'];
													if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
														$value = '<a href="' . esc_url( $value ) . '" target="_blank" style="text-decoration:underline">' . esc_html__( 'View File', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
													}
													?>
                                                    <li><?php echo esc_html( $label ); ?>: <?php echo esc_html( $value ); ?></li>
													<?php
												}
											?>
                                        </ol>
                                    </td>
                                </tr>
							<?php } ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_start_date_and_time', __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $rbfw_start_datetime ); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_end_date_and_time', __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $rbfw_end_datetime ); ?></td>
                            </tr>
							<?php if ( ! empty( $variation_info ) ) {
								foreach ( $variation_info as $key => $value ) {
									?>
                                    <tr>
                                        <td><strong><?php echo esc_html( $value['field_label'] ); ?></strong></td>
                                        <td><?php echo esc_html( $value['field_value'] ); ?></td>
                                    </tr>
								<?php }
							} ?>
							<?php if ( ! empty( $item_quantity ) ) { ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php
                                            if($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                                echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings'));
                                            }else{
                                                echo esc_html__('Quantity','booking-and-rental-manager-for-woocommerce');
                                            }
                                            ?>
                                        </strong>
                                    </td>
                                    <td><?php echo esc_html( $item_quantity ); ?></td>
                                </tr>
							<?php } ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_duration_cost', __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $duration_cost ); ?></td>
                            </tr>

                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_resource_cost', __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $service_cost ); ?></td>
                            </tr>

							<?php 
							// Add WooCommerce Products to thankyou page (offline payment)
							$rbfw_wc_products_info = ! empty( $ticket_info['rbfw_wc_products_info'] ) ? $ticket_info['rbfw_wc_products_info'] : [];
							$rbfw_wc_products_total = ! empty( $ticket_info['rbfw_wc_products_total'] ) ? $ticket_info['rbfw_wc_products_total'] : 0;
							
							if ( ! empty( $rbfw_wc_products_info ) ) { ?>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Additional Products', 'booking-and-rental-manager-for-woocommerce' ); ?>:</strong></td>
                                    <td>
                                        <table style="border:1px solid #f5f5f5;margin:0;width: 100%;">
											<?php foreach ( $rbfw_wc_products_info as $product_id => $product_data ) { 
												$product = wc_get_product( $product_id );
												if ( $product ) { ?>
                                                    <tr>
                                                        <td style="border:1px solid #f5f5f5;"><strong><?php echo esc_html( $product->get_name() ); ?></strong></td>
                                                        <td style="border:1px solid #f5f5f5;">(<?php echo wc_price( $product_data['price'] ); ?> x <?php echo esc_html( $product_data['quantity'] ); ?>) = <?php echo wc_price( $product_data['total'] ); ?></td>
                                                    </tr>
												<?php }
											} ?>
                                        </table>
                                    </td>
                                </tr>
							<?php } ?>

							<?php if ( ! empty( $discount_amount ) ) { ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings', __( 'Discount', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>:</strong></td>
                                    <td><?php echo esc_html( $discount_amount ); ?></td>
                                </tr>
							<?php } ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_total_cost', __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) );
											echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $total_cost . ' ' . $tax_status ); ?></td>
                            </tr>
                            </tbody>
                        </table>
						<?php do_action( 'rbfw_after_thankyou_page_info', $order_id ); ?>
                    </div>
					<?php
					return ob_get_clean();
				}
			}
		}
		new Rbfw_Thankyou_Page();
	}