<?php
	/*
	* Author 	:	MagePeople Team
	* Developer :   Ariful
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RBFW_BikeCarSd_Function' ) ) {
		class RBFW_BikeCarSd_Function {
			public function __construct() {
				add_action( 'wp_footer', array( $this, 'rbfw_bike_car_sd_frontend_scripts' ) );
				add_action( 'wp_ajax_rbfw_bikecarsd_time_table', array( $this, 'rbfw_bikecarsd_time_table' ) );
				add_action( 'wp_ajax_nopriv_rbfw_bikecarsd_time_table', array( $this, 'rbfw_bikecarsd_time_table' ) );
				add_action( 'wp_ajax_rbfw_bikecarsd_type_list', array( $this, 'rbfw_bikecarsd_type_list' ) );
				add_action( 'wp_ajax_nopriv_rbfw_bikecarsd_type_list', array( $this, 'rbfw_bikecarsd_type_list' ) );
				add_action( 'wp_ajax_rbfw_bikecarsd_ajax_price_calculation', array( $this, 'rbfw_bikecarsd_ajax_price_calculation' ) );
				add_action( 'wp_ajax_nopriv_rbfw_bikecarsd_ajax_price_calculation', array( $this, 'rbfw_bikecarsd_ajax_price_calculation' ) );
				add_action( 'wp_ajax_rbfw_timely_variation_price_calculation', array( $this, 'rbfw_timely_variation_price_calculation' ) );
				add_action( 'wp_ajax_nopriv_rbfw_timely_variation_price_calculation', array( $this, 'rbfw_timely_variation_price_calculation' ) );
				add_action( 'wp_ajax_rbfw_timely_price_calculation', array( $this, 'rbfw_timely_price_calculation' ) );
				add_action( 'wp_ajax_nopriv_rbfw_timely_price_calculation', array( $this, 'rbfw_timely_price_calculation' ) );
				add_action( 'wp_ajax_particular_time_date_dependent', array( $this, 'particular_time_date_dependent' ) );
				add_action( 'wp_ajax_nopriv_particular_time_date_dependent', array( $this, 'particular_time_date_dependent' ) );
				add_action( 'wp_ajax_rbfw_service_type_timely_stock', array( $this, 'rbfw_service_type_timely_stock' ) );
				add_action( 'wp_ajax_nopriv_rbfw_service_type_timely_stock', array( $this, 'rbfw_service_type_timely_stock' ) );
			}

			public function rbfw_get_bikecarsd_rent_info( $product_id, $rent_info ) {
				$main_array     = [];
				$rbfw_rent_data = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();
				if ( ! empty( $rbfw_rent_data ) ):
					$rent_types = array_column( $rbfw_rent_data, 'price', 'rent_type' );
				else:
					$rent_types = array();
				endif;
				if ( ! empty( $rent_info ) ) {
					foreach ( $rent_info as $key => $value ) {
						$rent_type = $key; //Type1
						if ( $value > 0 ) {
							if ( array_key_exists( $rent_type, $rent_types ) ) { // if Type1 exist in array
								$main_array[ $rent_type ] = '(' . rbfw_mps_price( $rent_types[ $rent_type ] ) . ' x ' . (float) $value . ') = ' . rbfw_mps_price( (float) $rent_types[ $rent_type ] * (float) $value ); // type = quantity
							}
						}
					}
				}

				return $main_array;
			}

			public function rbfw_get_bikecarsd_service_info( $product_id, $service_info ) {
				$main_array              = [];
				$rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();
				if ( ! empty( $rbfw_extra_service_data ) ):
					$extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
				else:
					$extra_services = array();
				endif;
				foreach ( $service_info as $key => $value ) {
					$service_name = $key; //Type1
					if ( $value > 0 ) {
						if ( array_key_exists( $service_name, $extra_services ) ) { // if Type1 exist in array
							$main_array[ $service_name ] = '(' . rbfw_mps_price( $extra_services[ $service_name ] ) . ' x ' . (float) $value . ') = ' . rbfw_mps_price( (float) $extra_services[ $service_name ] * (float) $value ); // type = quantity
						}
					}
				}

				return $main_array;
			}

			public function rbfw_bikecarsd_ticket_info( $product_id, $rbfw_start_datetime = null, $rbfw_end_datetime = null, $rbfw_type_info = array(), $rbfw_service_info = array(), $selected_time = null, $rbfw_regf_info = array(), $rbfw_pickup_point = null, $rbfw_dropoff_point = null, $end_time = null, $rbfw_item_quantity = null ) {
				global $rbfw;
				if ( ! empty( $product_id ) && ! empty( $rbfw_type_info ) ):
					$rent_price          = 0;
					$service_price       = 0;
					$total_rent_price    = 0;
					$total_service_price = 0;
					$subtotal_price      = 0;
					$total_price         = 0;
					$title               = get_the_title( $product_id );
					$main_array          = array();
					$rbfw_rent_type      = get_post_meta( $product_id, 'rbfw_item_type', true );
					$rbfw_rent_data      = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();
					if ( ! empty( $rbfw_rent_data ) ):
						$rent_types = array_column( $rbfw_rent_data, 'price', 'rent_type' );
					else:
						$rent_types = array();
					endif;
					$rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();
					if ( ! empty( $rbfw_extra_service_data ) ):
						$extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
					else:
						$extra_services = array();
					endif;
					foreach ( $rbfw_type_info as $key => $value ):
						$rent_type = $key; //Type1
						if ( array_key_exists( $rent_type, $rent_types ) ) { // if Type1 exist in array
							$rent_price += (float) $rent_types[ $rent_type ] * (float) $value; // addup price
						}
					endforeach;
					if ( $rent_price > 0 ):
						$total_rent_price = (float) $rent_price;
					endif;
					foreach ( $rbfw_service_info as $key => $value ):
						$service_name = $key; //Service1
						if ( array_key_exists( $service_name, $extra_services ) ) { // if Service1 exist in array
							$service_price += (float) $extra_services[ $service_name ] * (float) $value; // quantity * price
						}
					endforeach;
					if ( $service_price > 0 ):
						$total_service_price = (float) $service_price;
					endif;
					if ( $total_rent_price > 0 || $total_service_price > 0 ):
						$subtotal_price = (float) $total_rent_price + (float) $total_service_price;
					endif;
					if ( $subtotal_price > 0 ):
						$total_price = (float) $subtotal_price;
					endif;
					$security_deposit = rbfw_security_deposit( $product_id, $total_price );
					$total_price      = $total_price + $security_deposit['security_deposit_amount'];
					/* Start Tax Calculations */
					$rbfw_payment_system = $rbfw->get_option_trans( 'rbfw_payment_system', 'rbfw_basic_payment_settings', 'mps' );
					$mps_tax_switch      = $rbfw->get_option_trans( 'rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off' );
					$mps_tax_percentage  = ! empty( get_post_meta( $product_id, 'rbfw_mps_tax_percentage', true ) ) ? wp_strip_all_tags( get_post_meta( $product_id, 'rbfw_mps_tax_percentage', true ) ) : '';
					$percent             = 0;
					if ( $rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && ! empty( $mps_tax_percentage ) ) {
						//Convert our percentage value into a decimal.
						$percentInDecimal = $mps_tax_percentage / 100;
						//Get the result.
						$percent     = $percentInDecimal * $total_price;
						$total_price = $total_price + $percent;
					}
					/* End Tax Calculations */
					$main_array[0]['ticket_name']             = $title;
					$main_array[0]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
					$main_array[0]['ticket_price']            = $total_price;
					$main_array[0]['ticket_qty']              = 1;
					$main_array[0]['rbfw_pickup_point']       = $rbfw_pickup_point;
					$main_array[0]['rbfw_dropoff_point']      = $rbfw_dropoff_point;
					$main_array[0]['rbfw_start_date']         = $rbfw_start_datetime;
					$main_array[0]['rbfw_start_time']         = $selected_time;
					$main_array[0]['rbfw_end_date']           = $rbfw_end_datetime;
					$main_array[0]['rbfw_end_time']           = $end_time;
					$main_array[0]['rbfw_start_datetime']     = $rbfw_start_datetime . ' ' . $selected_time;
					$main_array[0]['rbfw_type_info']          = $rbfw_type_info;
					$main_array[0]['rbfw_service_info']       = $rbfw_service_info;
					$main_array[0]['rbfw_rent_type']          = $rbfw_rent_type;
					$main_array[0]['rbfw_id']                 = $product_id;
					$main_array[0]['rbfw_item_quantity']      = $rbfw_item_quantity;
					if ( ! empty( $rbfw_type_info ) ) {
						foreach ( $rbfw_type_info as $key => $value ) {
							$rent_type = $key;
							if ( $value > 0 ) {
								if ( array_key_exists( $rent_type, $rent_types ) ) { // if Type exist in array
									$main_array[0]['rbfw_type_info'][ $rent_type ] = $value; // type = quantity
								}
							}
						}
					}
					if ( ! empty( $rbfw_service_info ) ) {
						foreach ( $rbfw_service_info as $key => $value ):
							$service_name = $key; //Service name
							if ( $value > 0 ) {
								if ( array_key_exists( $service_name, $extra_services ) ) { // if Service name exist in array
									$main_array[0]['rbfw_service_info'][ $service_name ] = $value; // name = quantity
								}
							}
						endforeach;
					}
					$main_array[0]['rbfw_mps_tax']   = $percent;
					$main_array[0]['duration_cost']  = $total_rent_price;
					$main_array[0]['service_cost']   = $total_service_price;
					$main_array[0]['rbfw_regf_info'] = $rbfw_regf_info;

					return $main_array;
				else:
					return false;
				endif;
			}

			public function rbfw_bikecarsd_price_calculation( $product_id, $rbfw_bikecarsd_info, $rbfw_service_info = null, $rbfw_request = null ) {
				global $rbfw;
				if ( ! empty( $product_id ) && ! empty( $rbfw_bikecarsd_info ) ):
					$rent_price          = 0;
					$service_price       = 0;
					$total_rent_price    = 0;
					$total_service_price = 0;
					$subtotal_price      = 0;
					$total_price         = 0;
					$rbfw_rent_data      = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();
					if ( ! empty( $rbfw_rent_data ) ):
						$rent_types = array_column( $rbfw_rent_data, 'price', 'rent_type' );
					else:
						$rent_types = array();
					endif;
					$rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
					if ( ! empty( $rbfw_extra_service_data ) ):
						$extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
					else:
						$extra_services = array();
					endif;
					foreach ( $rbfw_bikecarsd_info as $key => $value ):
						$rent_type = $key; //Type1
						if ( array_key_exists( $rent_type, $rent_types ) ) {
							// if Type1 exist in array
							$rent_price += (float) $rent_types[ $rent_type ] * (float) $value; // addup price
						}
					endforeach;
					if ( $rent_price > 0 ):
						$total_rent_price = (float) $rent_price;
					endif;
					foreach ( $rbfw_service_info as $key => $value ):
						$service_name = $key; //Service1
						if ( array_key_exists( $service_name, $extra_services ) ) { // if Service1 exist in array
							$service_price += (float) $extra_services[ $service_name ] * (float) $value; // quantity * price
						}
					endforeach;
					if ( $service_price > 0 ):
						$total_service_price = (float) $service_price;
					endif;
					if ( $total_rent_price > 0 || $total_service_price > 0 ):
						$subtotal_price = (float) $total_rent_price + (float) $total_service_price;
					endif;
					if ( $subtotal_price > 0 ):
						$total_price = (float) $subtotal_price;
					endif;
					/* Start Tax Calculations */
					$rbfw_payment_system = $rbfw->get_option_trans( 'rbfw_payment_system', 'rbfw_basic_payment_settings', 'mps' );
					$mps_tax_switch      = $rbfw->get_option_trans( 'rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off' );
					$mps_tax_percentage  = ! empty( get_post_meta( $product_id, 'rbfw_mps_tax_percentage', true ) ) ? wp_strip_all_tags( get_post_meta( $product_id, 'rbfw_mps_tax_percentage', true ) ) : '';
					if ( $rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && ! empty( $mps_tax_percentage ) ) {
						//Convert our percentage value into a decimal.
						$percentInDecimal = $mps_tax_percentage / 100;
						//Get the result.
						$percent     = $percentInDecimal * $total_price;
						$total_price = $total_price + $percent;
					}
					/* End Tax Calculations */
					if ( $rbfw_request == 'rbfw_bikecarsd_total_price' ):
						return $total_price;
					elseif ( $rbfw_request == 'rbfw_bikecarsd_duration_price' ):
						return $total_rent_price;
					elseif ( $rbfw_request == 'rbfw_bikecarsd_service_price' ):
						return $total_service_price;
					else:
						return $total_price;
					endif;
				else:
					return false;
				endif;
			}

			public function rbfw_get_time_slot_by_label( $ts_label ) {
				$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
				$ts_time         = '';
				if ( ! empty( $rbfw_time_slots ) ) {
					foreach ( $rbfw_time_slots as $key => $value ) {
						if ( $key == $ts_label ) {
							$ts_time = $value;
						}
					}
				}

				return $ts_time;
			}

			/****************************************************
			 * Appointment Type:
			 * Get Booked Time
			 ****************************************************/
			public function rbfw_get_time_booking_status( $post_id, $selected_date, $time ) {
				if ( empty( $post_id ) || empty( $selected_date ) || empty( $time ) ) {
					return false;
				}
				$rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );
				if ( $rbfw_rent_type != 'appointment' ) {
					return false;
				}
				// Start: Get Date Range
				$date_range    = [];
				$selected_date = strtotime( $selected_date );
				for (
					$currentDate = $selected_date; $currentDate <= $selected_date;
					$currentDate += ( 86400 )
				) {
					$date         = gmdate( 'd-m-Y', $currentDate );
					$date_range[] = $date;
				}
				// End: Get Date Range
				$total_qty                       = 0;
				$appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true );
				if ( ! empty( $rbfw_inventory ) ) {
					foreach ( $date_range as $key => $range_date ) {
						foreach ( $rbfw_inventory as $key => $inventory ) {
							$booked_dates    = ! empty( $inventory['booked_dates'] ) ? $inventory['booked_dates'] : [];
							$rbfw_start_time = ! empty( $inventory['rbfw_start_time'] ) ? $inventory['rbfw_start_time'] : '';
							$rbfw_type_info  = ! empty( $inventory['rbfw_type_info'] ) ? $inventory['rbfw_type_info'] : [];
							if ( in_array( $range_date, $booked_dates ) && ( $time == $rbfw_start_time ) ) {
								foreach ( $rbfw_type_info as $type_name => $type_qty ) {
									$total_qty += $type_qty;
								}
							}
						}
					}
				}
				$remaining_stock = $appointment_max_qty_per_session - $total_qty;
				$remaining_stock = max( 0, $remaining_stock );
				if ( $remaining_stock > 0 ) {
					return false;
				} else {
					return true;
				}
			}

			public function rbfw_bikecarsd_time_table() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				if ( isset( $_POST['post_id'] ) ) {
					$id                 = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
					$selected_date      = isset( $_POST['selected_date'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_date'] ) ) : '';
					$is_muffin_template = isset( $_POST['is_muffin_template'] ) ? sanitize_text_field( wp_unslash( $_POST['is_muffin_template'] ) ) : '';
					$time_slot_switch   = isset( $_POST['time_slot_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['time_slot_switch'] ) ) : '';
					if ( $time_slot_switch == 'on' ) {
						$available_times  = rbfw_get_available_times_particulars( $id, $selected_date, 'time_enable', '' );
						$default_timezone = wp_timezone_string();
						$date             = new DateTime( "now", new DateTimeZone( $default_timezone ) );
						$nowTime          = $date->format( 'H:i' );
						$nowDate          = $date->format( 'Y-m-d' );
						$date_to_string   = new DateTime( $selected_date );
						$result           = $date_to_string->format( get_option( 'date_format' ) );
						ob_start();
						$content = '';
						$content .= '<div class="rbfw_bikecarsd_time_table_container rbfw-bikecarsd-step" data-step="2">';
						$content .= '<a class="rbfw_back_step_btn" back-step="1" data-step="2"><i class="fas fa-circle-left"></i> ' . rbfw_string_return( 'rbfw_text_back_to_previous_step', esc_html__( 'Back to Previous Step', 'booking-and-rental-manager-for-woocommerce' ) ) . '</a>';
						if ( $is_muffin_template == 0 ) {
							$content .= '<div class="rbfw_step_selected_date"><i class="fas fa-calendar-check"></i> ' . rbfw_string_return( 'rbfw_text_you_selected', esc_html__( 'You selected', 'booking-and-rental-manager-for-woocommerce' ) ) . ': ' . $result . '</div>';
							$content .= '<div class="single-day-notice"><i class="fas fa-circle-info"></i> ' . esc_html__( 'Please pick up a time', 'booking-and-rental-manager-for-woocommerce' ) . '</div>';
						}
						if ( $is_muffin_template == 1 ) {
							$content .= '<div class="rbfw_step_selected_date rbfw_muff_selected_date">';
							$content .= '<div class="rbfw_muff_selected_date_col"><span class="rbfw_muff_selected_date_value">' . $result . '</span></div>';
							$content .= '</div>';
						}
						$content .= '<div class="rbfw_bikecarsd_time_table_wrap">';
						if ( ! empty( $available_times[0] ) ) {
							foreach ( $available_times[0] as $value ) {
								$converted_time = gmdate( get_option( 'time_format' ), strtotime( $value[1] ) );
								$ts_time        = $this->rbfw_get_time_slot_by_label( $value[1] );
								$is_booked      = $this->rbfw_get_time_booking_status( $id, $selected_date, $ts_time );
								$disabled       = '';
								if ( ( ( $nowDate == $selected_date ) && ( $converted_time < $nowTime ) ) || ( $is_booked === true ) ) {
									$disabled = 'disabled';
								}
								$content .= '<a data-time="' . gmdate( 'H:i', strtotime( $value[1] ) ) . '" class="rbfw_bikecarsd_time ' . $disabled . '"><span class="rbfw_bikecarsd_time_span">' . $converted_time . '</span>';
								if ( $is_booked === true ) {
									$content .= '<span class="rbfw_bikecarsd_time_booked">' . rbfw_string_return( 'rbfw_text_booked', esc_html__( 'Booked', 'booking-and-rental-manager-for-woocommerce' ) ) . '</span>';
								}
								$content .= '</a>';
							}
						}
						$content .= '</div>';
						$content .= '</div>';
						echo wp_kses( $content, rbfw_allowed_html() );
						$output = ob_get_clean();
						echo wp_kses( $output, rbfw_allowed_html() );
					} else {
						include( RBFW_Function::get_template_path( 'template_segment/single_day_info.php' ) );
					}
				}
				wp_die();
			}

			public function rbfw_bikecarsd_type_list() {
				include( RBFW_Function::get_template_path( 'template_segment/single_day_info.php' ) );
				wp_die();
			}

			public function rbfw_bikecarsd_ajax_price_calculation() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}

				global $rbfw;
				$content                 = '';
				$rules                   = [
					'name'        => 'sanitize_text_field',
					'email'       => 'sanitize_email',
					'age'         => 'absint',
					'preferences' => [
						'color'         => 'sanitize_text_field',
						'notifications' => function ( $value ) {
							return $value === 'yes' ? 'yes' : 'no';
						}
					]
				];
				$sd_input_data_sabitized = sanitize_post_array( $_POST, $rules );
				$post_id             = ! empty( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				$bikecarsd_price     = 0;
				$service_price       = 0;
				$total_service_price = 0;
				$subtotal_price      = 0;
				$total_price         = 0;
				foreach ( $sd_input_data_sabitized['bikecarsd_price_arr'] as $key => $value ):
					$bikecarsd_price += (float) $value['data_qty'] * (float) $value['data_price'];
				endforeach;
				$total_bikecarsd_price = (float) $bikecarsd_price;
				if ( ! empty( $sd_input_data_sabitized['service_price_arr'] ) ) {
					foreach ( $sd_input_data_sabitized['service_price_arr'] as $key => $value ):
						$service_price += (float) $value['data_qty'] * (float) $value['data_price'];
					endforeach;
				}
				if ( $service_price > 0 ):
					$total_service_price = (float) $service_price;
				endif;
				if ( $total_bikecarsd_price > 0 || $total_service_price > 0 ):
					$subtotal_price = (float) $total_bikecarsd_price + (float) $total_service_price;
				endif;
				if ( $subtotal_price > 0 ):
					$total_price = (float) $subtotal_price;
				endif;
				/* Start Tax Calculations */
				$rbfw_payment_system = $rbfw->get_option_trans( 'rbfw_payment_system', 'rbfw_basic_payment_settings', 'mps' );
				$mps_tax_switch      = $rbfw->get_option_trans( 'rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off' );
				$mps_tax_format      = $rbfw->get_option_trans( 'rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax' );
				$mps_tax_percentage  = ! empty( get_post_meta( $post_id, 'rbfw_mps_tax_percentage', true ) ) ? wp_strip_all_tags( get_post_meta( $post_id, 'rbfw_mps_tax_percentage', true ) ) : '';
				$percent             = 0;
				$tax_status          = '';
				/* End Tax Calculations */
				$content .= '<div class="item rbfw_bikecarsd_price_summary">
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">' . $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings', esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ) . ' <span class="price-figure" data-price="' . $total_bikecarsd_price . '">' . wc_price( $total_bikecarsd_price ) . '</span></li>';
				if ( ! empty( $service_price_arr ) ) {
					$content .= '<li class="resource-costing rbfw-cond">' . $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings', esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ) . ' <span class="price-figure" data-price="' . $total_service_price . '">' . rbfw_mps_price( $total_service_price ) . '</span></li>';
				}
				$content .= '<li class="subtotal">' . $rbfw->get_option_trans( 'rbfw_text_subtotal', 'rbfw_basic_translation_settings', esc_html__( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ) ) . '<span class="price-figure" data-price="' . $subtotal_price . '">' . rbfw_mps_price( $subtotal_price ) . '</span></li>';
				if ( $rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && ! empty( $mps_tax_percentage ) && $mps_tax_format == 'excluding_tax' ) {
					$content .= '<li class="tax">' . $rbfw->get_option_trans( 'rbfw_text_tax', 'rbfw_basic_translation_settings', esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ) ) . '<span class="price-figure" data-price="' . $percent . '">' . rbfw_mps_price( $percent ) . '</span></li>';
				}
				$security_deposit = rbfw_security_deposit( $post_id, $subtotal_price );
				if ( $security_deposit['security_deposit_desc'] ) {
					$content .= '<li class="subtotal">' . ( ! empty( get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) ) ? get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ) . '<span class="price-figure" data-price="' . $security_deposit['security_deposit_amount'] . '">' . $security_deposit['security_deposit_desc'] . '</span></li>';
				}
				$total_price = $total_price + $security_deposit['security_deposit_amount'];
				$content     .= '<li class="total"><strong>' . $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings', esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' ) ) . '</strong> <span class="price-figure" data-price="' . $total_price . '">' . rbfw_mps_price( $total_price ) . ' ' . $tax_status . '</span></li>


                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>';
				echo wp_kses( $content, rbfw_allowed_html() );
				wp_die();
			}

			public function rbfw_timely_variation_price_calculation() {
				include( RBFW_Function::get_template_path( 'template_segment/timely_info.php' ) );
				wp_die();
			}

			public function rbfw_timely_price_calculation() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				include( RBFW_Function::get_template_path( 'template_segment/rbfw_timely_price_calculation.php' ) );
				wp_die();
			}

			public function particular_time_date_dependent() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				$post_id           = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
				$selected_date     = isset( $_POST['selected_date'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_date'] ) ) : '';
				$type              = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
				$selector          = ( isset( $_POST['selector'] ) && sanitize_text_field( wp_unslash( $_POST['selector'] ) ) ) ? sanitize_text_field( wp_unslash( $_POST['selector'] ) ) : '.rbfw-select.rbfw-time-price.pickup_time';
				$times_particulars = rbfw_get_available_times_particulars( $post_id, $selected_date, $type, $selector );
				echo wp_json_encode( $times_particulars );
				wp_die();
			}

			public function rbfw_service_type_timely_stock() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				$post_id                  = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
				$start_date               = isset( $_POST['rbfw_bikecarsd_selected_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_bikecarsd_selected_date'] ) ) : '';
				$enable_specific_duration = isset( $_POST['enable_specific_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_specific_duration'] ) ) : '';
				$start_time               = isset( $_POST['pickup_time'] ) ? sanitize_text_field( wp_unslash( $_POST['pickup_time'] ) ) : '00:00';
				$rbfw_bike_car_sd_data    = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
				$content                  = '';
				foreach ( $rbfw_bike_car_sd_data as $value ) {
					if ( $enable_specific_duration == 'on' ) {
						$d_type   = $value['start_time'];
						$duration = $value['end_time'];
					} else {
						$d_type   = $value['d_type'];
						$duration = $value['duration'];
					}
					$rbfw_timely_available_quantity = rbfw_timely_available_quantity_updated( $post_id, $start_date, $start_time, $d_type, $duration, $enable_specific_duration );
					$content                        .= '<label><input type="radio" name="option" class="radio-input">';
					if ( $rbfw_timely_available_quantity > 0 ) {
						$content .= '<span title="' . $value['short_desc'] . '" data-duration="' . $value['duration'] . '" data-price="' . $value['price'] . '" data-d_type="' . $value['d_type'] . '" data-start_time="' . $value['start_time'] . '" data-end_time="' . $value['end_time'] . '" data-available_quantity="' . $rbfw_timely_available_quantity . '" class="radio-button single-type-timely">' . $value['rent_type'] .$rbfw_timely_available_quantity . '</span>';
					} else {
						$content .= '<span style="text-decoration: line-through;cursor:text" title="' . $value['short_desc'] . '" data-duration="' . $value['duration'] . '" data-price="' . $value['price'] . '" data-d_type="' . $value['d_type'] . '" class="radio-button">' . $value['rent_type'] . '</span>';
					}
					$content .= ' </label>';
				}
				echo wp_kses( $content , rbfw_allowed_html());
				wp_die();
			}

			public function rbfw_bike_car_sd_frontend_scripts( $rbfw_post_id ) {
				global $post;
				$post_id = ! empty( $post->ID ) ? $post->ID : '';
				if ( ! empty( $rbfw_post_id ) ) {
					$post_id = $rbfw_post_id;
				}
				if ( empty( $post_id ) ) {
					return;
				}
				$rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );
				if ( $rent_type != 'bike_car_sd' && $rent_type != 'appointment' && ( is_a( $post, 'WP_Post' ) && ! has_shortcode( $post->post_content, 'rent-add-to-cart' ) ) ):
					return;
				endif;
			}
		}
		new RBFW_BikeCarSd_Function();
	}