<?php
	/*
	* Author 	:	MagePeople Team
	* Developer :   Ariful
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RBFW_Resort_Function' ) ) {
		class RBFW_Resort_Function {
			public function __construct() {
				add_action( 'wp_ajax_rbfw_check_resort_availibility', array( $this, 'rbfw_check_resort_availibility' ) );
				add_action( 'wp_ajax_nopriv_rbfw_check_resort_availibility', array( $this, 'rbfw_check_resort_availibility' ) );
				/*add_action( 'wp_ajax_rbfw_get_active_price_table', array( $this, 'rbfw_get_active_price_table' ) );
				add_action( 'wp_ajax_nopriv_rbfw_get_active_price_table', array( $this, 'rbfw_get_active_price_table' ) );*/
				add_action( 'wp_ajax_rbfw_room_price_calculation', array( $this, 'rbfw_room_price_calculation' ) );
				add_action( 'wp_ajax_nopriv_rbfw_room_price_calculation', array( $this, 'rbfw_room_price_calculation' ) );

                add_action( 'wp_ajax_rbfw_get_resort_sessional_day_wise_price', array( $this, 'rbfw_get_resort_sessional_day_wise_price' ) );
                add_action( 'wp_ajax_nopriv_rbfw_get_resort_sessional_day_wise_price', array( $this, 'rbfw_get_resort_sessional_day_wise_price' ) );

            }

			public function rbfw_get_resort_room_array_reorder( $product_id, $room_info ) {
				$main_array = [];
				if ( ! empty( $room_info ) ) {
					$room_info = array_column( $room_info, 'room_qty', 'room_type' );
					$i         = 0;
					foreach ( $room_info as $key => $value ):
						$type = $key;
						$qty  = $value;
						if ( $qty > 0 ) {
							$main_array[ $i ][ $type ] = $qty;
						}
						$i ++;
					endforeach;
				}

				return $main_array;
			}

			public function rbfw_get_resort_service_array_reorder( $product_id, $service_info ) {
				$main_array = [];
				if ( ! empty( $service_info ) ) {
					$service_info = array_column( $service_info, 'service_qty', 'service_name' );
					$i            = 0;
					foreach ( $service_info as $key => $value ):
						$type = $key;
						$qty  = $value;
						if ( $qty > 0 ) {
							$main_array[ $i ][ $type ] = $qty;
						}
						$i ++;
					endforeach;
				}

				return $main_array;
			}

			public function rbfw_get_resort_room_info( $product_id, $rent_info, $package, $rbfw_room_price ) {
				$rent_price     = 0;
				$main_array     = [];
				$rbfw_rent_data = get_post_meta( $product_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $product_id, 'rbfw_resort_room_data', true ) : array();
				if ( $package == 'daylong' ) {
					$g_rate = 'rbfw_room_daylong_rate';
				} elseif ( $package == 'daynight' ) {
					$g_rate = 'rbfw_room_daynight_rate';
				} else {
					$g_rate = '';
				}
				if ( ! empty( $rbfw_rent_data ) && ! empty( $g_rate ) ):
					$rent_types = array_column( $rbfw_rent_data, $g_rate, 'room_type' );
				else:
					$rent_types = array();
				endif;
				foreach ( $rent_info as $key => $value ) {
					$rent_type = $key; //Type1
					if ( $value > 0 ) {
						if ( array_key_exists( $rent_type, $rent_types ) ) {
                            $room_price = !empty($rbfw_room_price)?$rbfw_room_price[ $rent_type ] :$rent_types[$rent_type];
							$main_array[ $rent_type ] = '(' . wc_price( $room_price ) . ' x ' . $value . ') = ' . wc_price( (float) $room_price * (float) $value ); // type = quantity
						}
					}
				}

				return $main_array;
			}

			public function rbfw_get_resort_service_info( $product_id, $service_info ) {
				$service_price = 0;
				$main_array    = [];
				$rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();
				if ( ! empty( $rbfw_extra_service_data ) ):
					$extra_services    = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
					$extra_service_qty = array_column( $rbfw_extra_service_data, 'service_qty', 'service_name' );
				else:
					$extra_services = array();
				endif;
				foreach ( $service_info as $key => $value ) {
					$service_name = $key; //Type1
					if ( $value > 0 ) {
						if ( array_key_exists( $service_name, $extra_services ) ) { // if Type1 exist in array
							$service_price               += (float) $extra_services[ $service_name ] * (float) $value;// addup price
							$main_array[ $service_name ] = '(' . wc_price( $extra_services[ $service_name ] ) . ' x ' . (float) $value . ') = ' . wc_price( (float) $extra_services[ $service_name ] * (float) $value ); // type = quantity
						}
					}
				}

				return $main_array;
			}

			public function rbfw_resort_ticket_info( $product_id, $checkin_date, $checkout_date, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info = null, $rbfw_regf_info = array() , $rbfw_room_price = array() ) {
				global $rbfw;
				if ( ! empty( $product_id ) && ! empty( $checkin_date ) && ! empty( $checkout_date ) && ! empty( $rbfw_room_info ) ):
					$post_id               = $product_id;
					$start_date            = $checkin_date;
					$end_date              = $checkout_date;
					$origin                = date_create( $checkin_date );
					$target                = date_create( $checkout_date );
					$interval              = date_diff( $origin, $target );
					$total_days            = $interval->format( '%a' );

                    $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                    if ($rbfw_count_extra_day_enable == 'on') {
                        $total_days = $total_days + 1;
                    }

					$room_price            = 0;
					$service_price         = 0;
					$total_room_price      = 0;
					$total_service_price   = 0;
					$subtotal_price        = 0;
					$total_price           = 0;
					$title                 = get_the_title( $product_id );
					$main_array            = array();
					$rbfw_rent_type        = get_post_meta( $product_id, 'rbfw_item_type', true );
					$rbfw_resort_room_data = get_post_meta( $product_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $product_id, 'rbfw_resort_room_data', true ) : array();
					if ( $rbfw_room_price_category == 'daynight' ):
						$room_types = array_column( $rbfw_resort_room_data, 'rbfw_room_daynight_rate', 'room_type' );
					elseif ( $rbfw_room_price_category == 'daylong' ):
						$room_types = array_column( $rbfw_resort_room_data, 'rbfw_room_daylong_rate', 'room_type' );
					else:
						$room_types = array();
					endif;
					//   echo print_r($room_types);exit;
					$rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
					if ( ! empty( $rbfw_extra_service_data ) ):
						$extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
					else:
						$extra_services = array();
					endif;


                    foreach ( $rbfw_room_info as $key => $value ) {

                        $room_type = $key; //Type1
                        if (array_key_exists($room_type, $room_types)) {

                            if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php') || is_plugin_active('multi-day-price-saver-addon-for-wprently/additional-day-price.php') ) {

                                $rbfw_resort_data_mds = get_post_meta($product_id, 'rbfw_resort_data_mds', true) ? get_post_meta($product_id, 'rbfw_resort_data_mds', true) : [];
                                $rbfw_resort_data_sp = get_post_meta($product_id, 'rbfw_resort_data_sp', true) ? get_post_meta($product_id, 'rbfw_resort_data_sp', true) : [];



                                if(is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' ) && !empty($rbfw_resort_data_mds)){

                                    if (($sp_price = check_seasonal_price_resort_mds($total_days, $rbfw_resort_data_mds, $key, $rbfw_room_price_category)) != '0') {
                                        $room_price += (float)$sp_price;
                                    } else {
                                        $room_price += (float)$room_types[$room_type];
                                    }
                                    $total_room_price = $room_price * (int) $total_days * $value;

                                }elseif(is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php') && !empty($rbfw_resort_data_sp)){
                                    $rbfw_resort_data_sp = get_post_meta($product_id, 'rbfw_resort_data_sp', true) ? get_post_meta($product_id, 'rbfw_resort_data_sp', true) : [];
                                    $book_dates = getAllDates( $checkin_date, $checkout_date );
                                    for($d = 0; $d < $total_days; $d++) {
                                        if (($sp_price = check_seasonal_price_resort($book_dates[$d], $rbfw_resort_data_sp, $key, $rbfw_room_price_category)) != 'not_found') {
                                            $room_price += (float)$sp_price;
                                        } else {
                                            $room_price += (float)$room_types[$room_type];
                                        }

                                    }
                                    $total_room_price = $room_price;

                                }else{
                                    $room_price += (float)$room_types[$room_type] * (float)$value;

                                    if ( $room_price > 0 && $total_days > 0 ):
                                        $total_room_price = (float) $room_price * (float) $total_days;
                                    else:
                                        $total_room_price = (float) $room_price;
                                    endif;
                                }


                            }else{
                                $room_price += (float)$room_types[$room_type] * (float)$value;

                                if ( $room_price > 0 && $total_days > 0 ):
                                    $total_room_price = (float) $room_price * (float) $total_days;
                                else:
                                    $total_room_price = (float) $room_price;
                                endif;
                            }
                        }
                    }

					foreach ( $rbfw_service_info as $key => $value ):
						$service_name = $key; //Service1
						if ( array_key_exists( $service_name, $extra_services ) ) { // if Service1 exist in array
							$service_price += (float) $extra_services[ $service_name ] * (float) $value; // quantity * price
						}
					endforeach;
					if ( $service_price > 0 ):
						$total_service_price = (float) $service_price;
					endif;

					if ( $total_room_price > 0 || $total_service_price > 0 ):
						$subtotal_price = (float) $total_room_price + (float) $total_service_price;
					endif;

					if ( $subtotal_price > 0 ):
						$total_price = (float) $subtotal_price;
					endif;
					$security_deposit = rbfw_security_deposit( $product_id, $total_price );
					$total_price      = $total_price + $security_deposit['security_deposit_amount'];
					$percent          = 0;
					if ( function_exists( 'rbfw_get_discount_array' ) ) {
						$discount_arr = rbfw_get_discount_array( $post_id, $total_days, $total_price );
					} else {
						$discount_arr = [];
					}
					$discount_type   = '';
					$discount_amount = 0;
					if ( ! empty( $discount_arr ) ) {
						$total_price     = $discount_arr['total_amount'];
						$discount_type   = $discount_arr['discount_type'];
						$discount_amount = $discount_arr['discount_amount'];
					}
					/* End Discount Calculations */
					$main_array[0]['ticket_name']             = $title;
					$main_array[0]['ticket_price']            = $total_price;
					$main_array[0]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
					$main_array[0]['ticket_qty']              = 1;
					$main_array[0]['rbfw_start_date']         = $checkin_date;
					$main_array[0]['rbfw_start_time']         = '';
					$main_array[0]['rbfw_end_date']           = $checkout_date;
					$main_array[0]['rbfw_end_time']           = '';
					$main_array[0]['rbfw_start_datetime']     = $checkin_date;
					$main_array[0]['rbfw_end_datetime']       = $checkout_date;
					$main_array[0]['rbfw_resort_package']     = $rbfw_room_price_category;
					$main_array[0]['rbfw_type_info']          = [];
					$main_array[0]['rbfw_service_info']       = [];
					$main_array[0]['rbfw_room_price']         = $rbfw_room_price;
					$main_array[0]['rbfw_rent_type']          = $rbfw_rent_type;
					$main_array[0]['rbfw_id']                 = $product_id;
					if ( ! empty( $rbfw_room_info ) ) {
						foreach ( $rbfw_room_info as $key => $value ):
							$room_type = $key; //Type
							if ( $value > 0 ) {
								if ( array_key_exists( $room_type, $room_types ) ) { // if Type exist in array
									$main_array[0]['rbfw_type_info'][ $room_type ] = $value; // type = quantity
								}
							}
						endforeach;
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
					$main_array[0]['rbfw_mps_tax']    = $percent;
					$main_array[0]['duration_cost']   = $total_room_price;
					$main_array[0]['service_cost']    = $total_service_price;
					$main_array[0]['discount_type']   = $discount_type;
					$main_array[0]['discount_amount'] = $discount_amount;
					$main_array[0]['rbfw_regf_info']  = $rbfw_regf_info;

					return $main_array;
				else:
					return false;
				endif;
			}



			public function rbfw_check_resort_availibility() {

                check_ajax_referer( 'rbfw_check_resort_availibility_action', 'nonce' );

				$start_date = isset( $_POST['checkin_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkin_date'] ) ) : '';
				$end_date   = isset( $_POST['checkout_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkout_date'] ) ) : '';
				$post_id    = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
				$origin     = date_create( $start_date );
				$target     = date_create( $end_date );
				$interval   = date_diff( $origin, $target );
				$total_days = $interval->format( '%a' );


				if ($total_days ) {
                    $active_tab = 'daynight';
				} else {
                    $active_tab = 'daylong';
				}
                include( RBFW_Function::get_template_path( 'template_segment/resort_info.php' ) );
                wp_die();
			}



			public function rbfw_get_active_price_table( $post_id = 0, $active_tab = '', $checkin_date = '', $checkout_date = '' ) {

				include( RBFW_Function::get_template_path( 'template_segment/resort_info.php' ) );
				wp_die();
			}

			public function rbfw_room_price_calculation() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				if ( isset( $_POST['checkin_date'] ) && isset( $_POST['checkout_date'] ) ) {
					global $rbfw;
					$content       = '';
					$checkin_date  = isset( $_POST['checkin_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkin_date'] ) ) : '';
					$checkout_date = isset( $_POST['checkout_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkout_date'] ) ) : '';
					$post_id       = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
					$active_tab       = isset( $_POST['active_tab'] ) ?  sanitize_text_field( wp_unslash( $_POST['active_tab'] ) )  : '';
					$origin        = date_create( $checkin_date );
					$target        = date_create( $checkout_date );
					$interval      = date_diff( $origin, $target );

					$total_days    = $interval->format( '%a' );

					$rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                

                    if ($rbfw_count_extra_day_enable == 'on') {
						$total_days = $total_days + 1;
                	}



					$room_price_arr      = isset( $_POST['room_price_arr'] ) ? RBFW_Function::data_sanitize( $_POST['room_price_arr'] ) : [];
					$service_price_arr   = isset( $_POST['service_price_arr'] ) ? RBFW_Function::data_sanitize( $_POST['service_price_arr'] ) : [];
					$room_price          = 0;
					$service_price       = 0;
					$total_service_price = 0;
					$subtotal_price      = 0;
					$total_price         = 0;
                    $discount_amount     = 0;


                    foreach ( $room_price_arr as $key => $value ) {
                        $room_price += (float) $value['data_qty'] * (float) $value['data_price'];
                    }
                    if ( $room_price > 0 && $total_days > 0 ):
                        $total_room_price = (float) $room_price * (int) $total_days;
                    else:
                        $total_room_price = (float) $room_price;
                    endif;



					if ( ! empty( $service_price_arr ) ) {
						foreach ( $service_price_arr as $key => $value ):
							$service_price += (float) $value['data_qty'] * (float) $value['data_price'];
						endforeach;
					}
					if ( $service_price > 0 ):
						$total_service_price = (float) $service_price;
					endif;
					if ( $total_room_price > 0 || $total_service_price > 0 ):
						$subtotal_price = (float) $total_room_price + (float) $total_service_price;
					endif;
					$total_room_price_org = $total_room_price;
					if ( $subtotal_price > 0 ):
						$total_price = (float) $subtotal_price;
					endif;
					/* Start Tax Calculations */

					$tax_status          = '';

					/* End Tax Calculations */
					$content          .= '<div class="item rbfw_room_price_summary">
                            <div class="item-content rbfw-costing">
                                <ul class="rbfw-ul">
                                
                                
                                <li class="duration-costing rbfw-cond">' . (
                        ( $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) .' :'
                        )  .
                        ' <span class="price-figure" data-price="' . $total_room_price_org . '">' . wc_price( $total_room_price_org ) . '</span></li>
<li class="resource-costing rbfw-cond">' . (
                        ( $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) .' :'
                        )  .
                        ' <span class="price-figure" data-price="' . $total_service_price . '">' . wc_price( $total_service_price ) . '</span></li>

<li class="subtotal">' . (
                        ( $rbfw->get_option_trans( 'rbfw_text_subtotal', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_subtotal', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ) .' :'
                        ) .
                        '<span class="price-figure" data-price="' . $subtotal_price . '">' . wc_price( $subtotal_price ) . '</span></li>';



                    $security_deposit = rbfw_security_deposit( $post_id, $subtotal_price );
					if ( $security_deposit['security_deposit_amount'] ) {
						$content .= '<li class="subtotal">' . ( ! empty( get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) ) ? get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ) . '<span class="price-figure" data-price="' . $subtotal_price . '">' . $security_deposit['security_deposit_desc'] . '</span></li>';
					}

					if ( rbfw_check_discount_over_days_plugin_active() === true ) {
						if ( function_exists( 'rbfw_get_discount_array' ) ) {
							$discount_arr = rbfw_get_discount_array( $post_id, $total_days, $total_price );
						} else {
							$discount_arr = [];
						}
						if ( ( $discount_arr['discount_amount'] ) ) {
							$discount_amount = $discount_arr['discount_amount'];
							$discount_desc   = $discount_arr['discount_desc'];
                            $content .= '<li class="discount">' . (
                                ( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                                    ? esc_html( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' ) )
                                    : esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' ) . ' :'
                                ) . '<span>' . wc_price( $discount_amount ) . '</span></li>';

						}
					}
					/* End Discount Calculations */
					$content .= '<li class="total"><strong>' . (
                        ( $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' )
                        ) . '</strong> 
<span class="price-figure" data-price="' . ( $total_price - $discount_amount + $security_deposit['security_deposit_amount'] ) . '">' . wc_price( $total_price - $discount_amount + $security_deposit['security_deposit_amount'] ) . ' ' . $tax_status . '</span></li>
                                </ul>
                                <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                            </div>
                        </div>';
					echo wp_kses( $content, rbfw_allowed_html() );
				} else {
					esc_html_e( 'Something is wrong! Please try again.', 'booking-and-rental-manager-for-woocommerce' );
				}
				wp_die();
			}


            public function rbfw_get_resort_sessional_day_wise_price() {

               /* if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }*/

                check_ajax_referer( 'rbfw_get_resort_sessional_day_wise_price_action', 'nonce' );

                if ( isset( $_POST['post_id'] ) ) {

                    $post_id = $_POST['post_id'];
                    $price = $_POST['price'];
                    $total_days = $_POST['total_days'];
                    $checkout_date = $_POST['checkout_date'];
                    $checkin_date = $_POST['checkin_date'];
                    $room_type = $_POST['room_type'];
                    $active_tab = $_POST['active_tab'];

                    $all_infos      = '<div class="rbfw_container">';
                    $all_infos .= '<div class="rbfw_header">Price Details</div>';

                    $rbfw_resort_data_sp = get_post_meta($post_id, 'rbfw_resort_data_sp', true) ? get_post_meta($post_id, 'rbfw_resort_data_sp', true) : [];
                    $book_dates = getAllDates( $checkin_date, $checkout_date );

                        for($d = 0; $d < $total_days; $d++) {
                            $all_infos .='<div class="rbfw_entry">';
                            if (($sp_price = check_seasonal_price_resort($book_dates[$d], $rbfw_resort_data_sp, $room_type, $active_tab)) != 'not_found') {
                                $all_infos .= '<span class="rbfw_date">'.rbfw_date_format($book_dates[$d]).'</span><span class="rbfw_amount">'.wp_kses(wc_price($sp_price) , rbfw_allowed_html()).'</span>';
                            } else {
                                $all_infos .= '<span class="rbfw_date">'.rbfw_date_format($book_dates[$d]).'</span> <span class="rbfw_amount">'.wp_kses(wc_price($price) , rbfw_allowed_html()).'</span>';
                            }
                            $all_infos .='</div>';
                        }

                    $all_infos .= '</div>';
                }
                wp_send_json_success( $all_infos );
            }




		}
		new RBFW_Resort_Function();
	}