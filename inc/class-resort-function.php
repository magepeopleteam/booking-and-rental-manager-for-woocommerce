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
        public function __construct(){
            add_action('wp_footer', array($this, 'rbfw_resort_frontend_scripts'));
            add_action('admin_footer', array($this, 'rbfw_resort_admin_scripts'));
            add_action('wp_ajax_rbfw_check_resort_availibility', array($this, 'rbfw_check_resort_availibility'));
            add_action('wp_ajax_nopriv_rbfw_check_resort_availibility', array($this,'rbfw_check_resort_availibility'));
            add_action('wp_ajax_rbfw_get_active_price_table', array($this, 'rbfw_get_active_price_table'));
            add_action('wp_ajax_nopriv_rbfw_get_active_price_table', array($this,'rbfw_get_active_price_table'));
            add_action('wp_ajax_rbfw_room_price_calculation', array($this, 'rbfw_room_price_calculation'));
            add_action('wp_ajax_nopriv_rbfw_room_price_calculation', array($this,'rbfw_room_price_calculation'));                        
        }
        public function rbfw_get_resort_room_array_reorder($product_id, $room_info){
            
            $main_array = [];

            if(!empty($room_info)){
                $room_info = array_column($room_info,'room_qty','room_type');
                $i = 0;
                foreach ($room_info as $key => $value):
                    $type = $key;
                    $qty = $value;
                    if($qty > 0){
                        $main_array[$i][$type] = $qty;
                    }
                    
                    $i++;
                endforeach;
            }

            return $main_array;
        }

        public function rbfw_get_resort_service_array_reorder($product_id, $service_info){
            
            $main_array = [];

            if(!empty($service_info)){
                $service_info = array_column($service_info,'service_qty','service_name');
                $i = 0;
                foreach ($service_info as $key => $value):
                    $type = $key;
                    $qty = $value;
                    if($qty > 0){
                        $main_array[$i][$type] = $qty;
                    }
                    
                    $i++;
                endforeach;
            }

            return $main_array;
        }

        public function rbfw_get_resort_room_info($product_id, $rent_info, $package){
            $rent_price = 0;
            $main_array = [];
            $rbfw_rent_data = get_post_meta( $product_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $product_id, 'rbfw_resort_room_data', true ) : array();

            if($package == 'daylong'){
                $g_rate = 'rbfw_room_daylong_rate';
            }
            elseif($package == 'daynight'){
                $g_rate = 'rbfw_room_daynight_rate';
            }
            else{
                $g_rate = '';
            }

            if(!empty($rbfw_rent_data) && !empty($g_rate)):
                $rent_types = array_column($rbfw_rent_data, $g_rate,'room_type'); 
            else:
                $rent_types = array();
            endif;



            foreach ($rent_info as $key => $value) {
                $rent_type = $key; //Type1
                if($value > 0){
                    if(array_key_exists($rent_type, $rent_types)){ // if Type1 exist in array
                        $rent_price += (float)$rent_types[$rent_type] * (float)$value; // addup price
                        $main_array[$rent_type] = '('.rbfw_mps_price($rent_types[$rent_type]) .' x '.$value.') = '.rbfw_mps_price((float)$rent_types[$rent_type] * (float)$value); // type = quantity
                    }
                }

            }


            return $main_array;
        }

        public function rbfw_get_resort_service_info($product_id, $service_info){
            $service_price = 0;
            $main_array = [];

            $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();

            if(! empty($rbfw_extra_service_data)):
                $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                $extra_service_qty = array_column($rbfw_extra_service_data,'service_qty','service_name');
            else:
                $extra_services = array();
            endif;


            foreach ($service_info as $key => $value) {
                $service_name = $key; //Type1
                if($value > 0){
                    if(array_key_exists($service_name, $extra_services)){ // if Type1 exist in array
                        $service_price += (float)$extra_services[$service_name] * (float)$value;// addup price
                        $main_array[$service_name] = '('.rbfw_mps_price($extra_services[$service_name]) .' x '. (float)$value.') = '.rbfw_mps_price((float)$extra_services[$service_name] * (float)$value); // type = quantity
                    }
                }
            }


            return $main_array;
        }

        public function rbfw_resort_ticket_info($product_id, $checkin_date, $checkout_date, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info = null){
            global $rbfw;
            if( !empty($product_id) && !empty($checkin_date) && !empty($checkout_date) && !empty($rbfw_room_info) ):
                $post_id = $product_id;
                $start_date = $checkin_date;
                $end_date = $checkout_date; 
                $origin             = date_create($checkin_date);
                $target             = date_create($checkout_date);
                $interval           = date_diff($origin, $target);
                $total_days         = $interval->format('%a');
                $room_price         = 0;
                $service_price      = 0;
                $total_room_price   = 0;
                $total_service_price = 0;
                $subtotal_price     = 0;
                $total_price        = 0;
                $title = get_the_title($product_id);
                $main_array = array();
                $rbfw_rent_type 		= get_post_meta( $product_id, 'rbfw_item_type', true );
                $rbfw_resort_room_data = get_post_meta( $product_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $product_id, 'rbfw_resort_room_data', true ) : array();
                if($rbfw_room_price_category == 'daynight'):
                    $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daynight_rate','room_type'); 
                elseif($rbfw_room_price_category == 'daylong'):
                    $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daylong_rate','room_type');
                else:
                    $room_types = array();
                endif;

                $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
                if(! empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                else:
                    $extra_services = array();
                endif;
               
                foreach ($rbfw_room_info as $key => $value):
                    $room_type = $key; //Type1
                    if(array_key_exists($room_type, $room_types)){ // if Type1 exist in array
                        $room_price += (float)$room_types[$room_type] * (float)$value; // addup price
                    }
             
                endforeach;

                
                if($room_price > 0 && $total_days > 0):
                    $total_room_price = (float)$room_price * (float)$total_days;
                else:
                    $total_room_price = (float)$room_price;
                endif;
    
                foreach ($rbfw_service_info as $key => $value):
                    $service_name = $key; //Service1
                    if(array_key_exists($service_name, $extra_services)){ // if Service1 exist in array
                        $service_price += (float)$extra_services[$service_name] * (float)$value; // quantity * price
                    }
                endforeach;

                if($service_price > 0):
                    $total_service_price = (float)$service_price;
                endif;
                
                if($total_room_price > 0 || $total_service_price > 0):
                    $subtotal_price = (float)$total_room_price + (float)$total_service_price;
                endif;
    
                if($subtotal_price > 0):
                    $total_price = (float)$subtotal_price;
                endif;

                /* Start Tax Calculations */
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
                $mps_tax_percentage = !empty(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) : '';
                $percent = 0;
                $tax_status = '';
                
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_price;
                    $total_price = $total_price + $percent;
                }

                /* End Tax Calculations */

                /* Start Discount Calculations */
                $discount_arr = rbfw_get_discount_array($post_id, $start_date, $end_date, $total_price);

                if(!empty($discount_arr)){
                    $total_price = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                }
                /* End Discount Calculations */

                $main_array[0]['ticket_name'] = $title;
                $main_array[0]['ticket_price'] = $total_price;
                $main_array[0]['ticket_qty'] = 1;
                $main_array[0]['rbfw_start_date'] = $checkin_date;
                $main_array[0]['rbfw_start_time'] = '';
                $main_array[0]['rbfw_end_date'] = $checkout_date;
                $main_array[0]['rbfw_end_time'] = '';
                $main_array[0]['rbfw_start_datetime'] = $checkin_date;
                $main_array[0]['rbfw_end_datetime'] = $checkout_date;
                $main_array[0]['rbfw_resort_package'] = $rbfw_room_price_category;
                $main_array[0]['rbfw_type_info'] = [];
                $main_array[0]['rbfw_service_info'] = [];
                $main_array[0]['rbfw_rent_type'] = $rbfw_rent_type;
                $main_array[0]['rbfw_id'] = $product_id;
                
                if(!empty($rbfw_room_info)){
                    foreach ($rbfw_room_info as $key => $value):
                        $room_type = $key; //Type
                        if($value > 0){
                            if(array_key_exists($room_type, $room_types)){ // if Type exist in array
                                
                                $main_array[0]['rbfw_type_info'][$room_type] = $value; // type = quantity
                            }
                        }
    
                    endforeach;
                }

                if(!empty($rbfw_service_info)){
                    foreach ($rbfw_service_info as $key => $value):
                        $service_name = $key; //Service name
                        if($value > 0){
                            if(array_key_exists($service_name, $extra_services)){ // if Service name exist in array
                                
                                $main_array[0]['rbfw_service_info'][$service_name] = $value; // name = quantity 
                            }
                        }
                    endforeach;
                }

                $main_array[0]['rbfw_mps_tax'] = $percent;
                $main_array[0]['duration_cost'] = $total_room_price;
                $main_array[0]['service_cost'] = $total_service_price;
                $main_array[0]['discount_type'] = $discount_type;
                $main_array[0]['discount_amount'] = $discount_amount;

                return $main_array;

            else:
                return false;
            endif; 
        }

        public function rbfw_resort_price_calculation($product_id, $checkin_date, $checkout_date, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info = array(), $rbfw_request = null){
            global $rbfw;
            if( !empty($product_id) && !empty($checkin_date) && !empty($checkout_date) && !empty($rbfw_room_info) ):
                $origin             = date_create($checkin_date);
                $target             = date_create($checkout_date);
                $interval           = date_diff($origin, $target);
                $total_days         = $interval->format('%a');
                $room_price         = 0;
                $service_price      = 0;
                $total_room_price   = 0;
                $total_service_price = 0;
                $subtotal_price     = 0;
                $total_price        = 0;

                $rbfw_resort_room_data = get_post_meta( $product_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $product_id, 'rbfw_resort_room_data', true ) : array();
                if($rbfw_room_price_category == 'daynight'):
                    $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daynight_rate','room_type'); 
                elseif($rbfw_room_price_category == 'daylong'):
                    $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daylong_rate','room_type');
                else:
                    $room_types = array();
                endif;

                $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
                if(! empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                else:
                    $extra_services = array();
                endif;
               
                foreach ($rbfw_room_info as $key => $value):
                    $room_type = $key; //Type1
                    if(array_key_exists($room_type, $room_types)){ // if Type1 exist in array
                        $room_price += (float)$room_types[$room_type] * (float)$value; // addup price
                    }
             
                endforeach;

                
                if($room_price > 0 && $total_days > 0):
                    $total_room_price = (float)$room_price * (float)$total_days;
                else:
                    $total_room_price = (float)$room_price;
                endif;
    
                foreach ($rbfw_service_info as $key => $value):
                    $service_name = $key; //Service1
                    if(array_key_exists($service_name, $extra_services)){ // if Service1 exist in array
                        $service_price += (float)$extra_services[$service_name] * (float)$value; // quantity * price
                    }
                endforeach;

                if($service_price > 0):
                    $total_service_price = (float)$service_price;
                endif;
                
                if($total_room_price > 0 || $total_service_price > 0):
                    $subtotal_price = (float)$total_room_price + (float)$total_service_price;
                endif;
    
                if($subtotal_price > 0):
                    $total_price = (float)$subtotal_price;
                endif;

                /* Start Tax Calculations */
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
                $mps_tax_percentage = !empty(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) : '';
                $percent = 0;
                $tax_status = '';

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_price;
                    $total_price = $total_price + $percent;
                }

                /* End Tax Calculations */                
                
                if($rbfw_request == 'rbfw_room_total_price'):
                    return $total_price;
                elseif($rbfw_request == 'rbfw_room_duration_price'):
                    return $total_room_price;
                elseif($rbfw_request == 'rbfw_room_service_price'):
                    return $total_service_price;
                elseif($rbfw_request == 'rbfw_tax_price'):
                    return $percent;    
                else:
                    return $total_price;
                endif;

            else:
                return false;
            endif;    
        }

        public function rbfw_check_resort_availibility(){
            global $rbfw;
            $errors = '';

            if(empty($_POST['post_id'])):
            $errors .= '<p class="rbfw_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Something is wrong! Please try again.','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;
        
            if(empty($_POST['checkin_date'])):
            $errors .= '<p class="rbfw_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Check-In date is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;
        
        
            if(empty($_POST['checkout_date'])):
            $errors .= '<p class="rbfw_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Check-Out date is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;
        
            if(empty($errors)){
                global $rbfw;
                $post_id 				= strip_tags($_POST['post_id']);
                $checkin_date 			= strip_tags($_POST['checkin_date']);
                $checkout_date 			= strip_tags($_POST['checkout_date']);
                $rbfw_resort_room_data 	= get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
                $daylong_counter  		= 0;
                $daynight_counter 		= 0;
                $content				= '';

                $rbfw_minimum_booking_day = get_post_meta( $post_id, 'rbfw_minimum_booking_day', true );
                $rbfw_maximum_booking_day = get_post_meta( $post_id, 'rbfw_maximum_booking_day', true );
                $min_max_day_notice = '';

                $checkin_date  = date( 'Y-m-d', strtotime( $checkin_date ) );
                $checkout_date = date( 'Y-m-d', strtotime( $checkout_date ) );
                $checkin_date  = new DateTime( $checkin_date );
                $checkout_date = new DateTime( $checkout_date );
                $diff = date_diff( $checkin_date, $checkout_date );
                $days = $diff->days;

                if(!empty($rbfw_minimum_booking_day) && $days < $rbfw_minimum_booking_day){
                    $min_max_day_notice .= '<span class="min_max_day_notice mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.rbfw_string_return('rbfw_text_min_number_days_have_to_book',__('Minimum number of days have to book is','booking-and-rental-manager-for-woocommerce')). ': '.'<strong>'.$rbfw_minimum_booking_day.'</strong></span>';
                }

                if(!empty($rbfw_maximum_booking_day) && $days > $rbfw_maximum_booking_day){
                    $min_max_day_notice .= '<span class="min_max_day_notice mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.rbfw_string_return('rbfw_text_max_number_days_have_to_book',__('Maximum number of days can book is','booking-and-rental-manager-for-woocommerce')). ': '.'<strong>'.$rbfw_maximum_booking_day.'</strong></span>';
                }
    
                if(!empty($min_max_day_notice)  && rbfw_check_min_max_booking_day_active() === true){
    
                    $content .= $min_max_day_notice;
                    echo $content;
                    wp_die();
                }

                foreach ($rbfw_resort_room_data as $key => $value) {
                    $daylong_counter  += (float)$value['rbfw_room_daylong_rate'];	
                    $daynight_counter += (float)$value['rbfw_room_daynight_rate'];
                }
                
                $content .= '<div class="rbfw_room_price_category_tabs_title mb-08">'.$rbfw->get_option('rbfw_text_select_booking_type', 'rbfw_basic_translation_settings', __('Select Booking Type','booking-and-rental-manager-for-woocommerce')).'</div>';

                $content .= '<div class="rbfw_room_price_category_tabs_label">';

                if($daylong_counter > 0 && $daynight_counter > 0):
                    $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label"><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price">'.$rbfw->get_option('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                    $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" checked>'.$rbfw->get_option('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                endif;
                if($daylong_counter > 0 && $daynight_counter == 0):
                    $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price" checked>'.$rbfw->get_option('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                    $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label disabled"><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" disabled>'.$rbfw->get_option('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                endif;
                if($daylong_counter == 0 && $daynight_counter > 0):
                    $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label disabled"><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price" disabled>'.$rbfw->get_option('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                    $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" checked>'.$rbfw->get_option('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                endif;

                $content .= '</div>';
                echo $content;
            }
            else{
                echo $errors;
            }
            wp_die();            
        }

        public function rbfw_get_active_price_table(){
            global $rbfw;
            if(isset($_POST['post_id']) && isset($_POST['active_tab'])):
                $post_id    = $_POST['post_id'];
                $active_tab = $_POST['active_tab'];
                $checkin_date = strip_tags($_POST['checkin_date']);
                $checkout_date = strip_tags($_POST['checkout_date']);
                $origin             = date_create($checkin_date);
                $target             = date_create($checkout_date);
                $interval           = date_diff($origin, $target);
                $total_days         = $interval->format('%a');                                 
                $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
                $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
                $rbfw_product_id = get_post_meta( $post_id, "link_wc_product", true ) ? get_post_meta( $post_id, "link_wc_product", true ) : $post_id;
                $currency_symbol = rbfw_mps_currency_symbol();
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                if($rbfw_payment_system == 'mps'){
                    $rbfw_payment_system = 'mps_enabled';
                }else{
                    $rbfw_payment_system = 'wps_enabled'; 
                }

                $available_qty_info_switch = get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) : 'off';

                $content    = '';

                $content   .= '<table class="rbfw_room_price_table rbfw_resort_rt_price_table">';
                $content   .= '<thead>';
                $content   .= '<tr>';
                $content   .= '<th>'.$rbfw->get_option('rbfw_text_room_type', 'rbfw_basic_translation_settings', __('Room Type','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th>'.$rbfw->get_option('rbfw_text_room_image', 'rbfw_basic_translation_settings', __('Image','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th>'.$rbfw->get_option('rbfw_text_room_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th class="w_30_pc">'.$rbfw->get_option('rbfw_text_room_qty', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '</tr>';
                $content   .= '</thead>';
                $content   .= '<tbody>';
                $i = 0;
                foreach ($rbfw_resort_room_data as $key => $value):

                    $img_url    = wp_get_attachment_url($value['rbfw_room_image']);
                    $uniq_id    = rand();    
                    if($img_url):
                        $img    = '<a href="#rbfw_room_img_'.$uniq_id.'" rel="mage_modal:open"><img src="'.esc_url($img_url).'"/></a>';
                        $img   .= '<div id="rbfw_room_img_'.$uniq_id.'" class="mage_modal"><img src="'.esc_url($img_url).'"/></div>';
                    else:
                        $img    = '';
                    endif;

                    if($active_tab == 'daylong'):
                        $price = $value['rbfw_room_daylong_rate'];
                    elseif($active_tab == 'daynight'):
                        $price = $value['rbfw_room_daynight_rate'];
                    endif;

                if($value['rbfw_room_available_qty'] > 0){

                    $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $checkin_date, $checkout_date, $value['room_type']);

                    $content   .= '<tr>';
                    $content   .= '<td>';
                    $content   .= esc_html($value['room_type']);
                    $content   .= '<input type="hidden" name="rbfw_room_info['.$i.'][room_type]" value="'.$value['room_type'].'"/>';
                    if($value['rbfw_room_desc']):
                    $content .= '<small class="rbfw_room_desc">';
                    $content .= $value['rbfw_room_desc'];
                    $content .= '</small>';
                    $content .= '<input type="hidden" name="rbfw_room_info['.$i.'][room_desc]" value="'.$value['rbfw_room_desc'].'"/>';
                    endif;                
                    $content   .= '</td>';
                    $content   .= '<td>'.$img.'</td>';
                    $content   .= '<td>';
                    $content   .= rbfw_mps_price($price);
                    $content   .= '<input type="hidden" name="rbfw_room_info['.$i.'][room_price]" value="'.$price.'"/>';            
                    $content   .='</td>';
                    $content   .= '<td>';
                    $content   .= '<div class="rbfw_qty_input">';
                    $content   .= '<a class="rbfw_qty_minus rbfw_room_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                    $content   .= '<input type="number" min="0" max="'.esc_attr($max_available_qty).'" value="0" name="rbfw_room_info['.$i.'][room_qty]" class="rbfw_room_qty" data-price="'.$price.'" data-type="'.$value['room_type'].'" data-cat="room"/>';
                    $content   .= '<a class="rbfw_qty_plus rbfw_room_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                    $content   .= '</div>';

                    if($available_qty_info_switch == 'on'){
                        $content .= '<div class="rbfw_available_qty_notice">'.$max_available_qty.' '.rbfw_string_return('rbfw_text_left_qty',__('Left','booking-and-rental-manager-for-woocommerce')).'</div>';
                    }

                    $content   .= '</td>';
                    $content   .= '</tr>';
                }

                $i++;
                endforeach;                
                $content   .= '</tbody>';                
                $content   .= '</table>';

                $content   .= '<table class="rbfw_room_price_table rbfw_resort_es_price_table">';
                $content   .= '<thead>';
                $content   .= '<tr>';
                $content   .= '<th>'.$rbfw->get_option('rbfw_text_room_service_name', 'rbfw_basic_translation_settings', __('Service Name','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th>'.$rbfw->get_option('rbfw_text_room_service_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th class="w_30_pc">'.$rbfw->get_option('rbfw_text_room_service_qty', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')).'</th>';                
                $content   .= '</tr>';             
                $content   .= '</thead>';
                $content   .= '<tbody>';

                $c = 0;

                foreach ($rbfw_extra_service_data as $key => $value):

                    $max_es_available_qty = rbfw_get_multiple_date_es_available_qty($post_id, $checkin_date, $checkout_date, $value['service_name']);

                    if($value['service_qty'] > 0){
                   
                        $content   .= '<tr>';
                        $content   .= '<td>';
                        $content   .= $value['service_name'];
                        $content   .= '<input type="hidden" name="rbfw_service_info['.$c.'][service_name]" value="'.$value['service_name'].'"/>';            
                        $content   .= '</td>'; 
                        $content   .= '<td>';
                        $content   .= rbfw_mps_price($value['service_price']);
                        $content   .= '<input type="hidden" name="rbfw_service_info['.$c.'][service_price]" value="'.$value['service_price'].'"/>';            
                        $content   .= '</td>';
                        $content   .= '<td>';
                        $content   .= '<div class="rbfw_qty_input">';
                        $content   .= '<a class="rbfw_qty_minus rbfw_service_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                        $content   .= '<input type="number" min="0" max="'.esc_attr($max_es_available_qty).'" value="0" name="rbfw_service_info['.$c.'][service_qty]" class="rbfw_service_qty" data-price="'.$value['service_price'].'" data-type="'.$value['service_name'].'" data-cat="service"/>';
                        $content   .= '<a class="rbfw_qty_plus rbfw_service_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                        $content   .= '</div>';
                        
                        if($available_qty_info_switch == 'on'){
                            $content .= '<div class="rbfw_available_qty_notice">'.$max_es_available_qty.' '.rbfw_string_return('rbfw_text_left_qty',__('Left','booking-and-rental-manager-for-woocommerce')).'</div>';
                        }

                        $content   .= '</td>';              
                        $content   .= '</tr>';
                    }
                    
                $c++;
                endforeach;
                $content   .= '</tbody>';                 
                $content   .= '</table>';

                $content   .= '<div class="item rbfw_room_price_summary">
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>
                                        <li class="resource-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>
                                        <li class="subtotal">'.$rbfw->get_option('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                        <li class="total"><strong>'.$rbfw->get_option('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>';
                $content  .= '<div class="item">
                                <button type="submit" name="add-to-cart" value="'.$rbfw_product_id.'" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_resort_book_now_btn '.$rbfw_payment_system.'" disabled>
                                    '.$rbfw->get_option('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce')).'
                                </button>
                            </div>';
                            
                if($active_tab == 'daynight' && $total_days > 0):            
                    echo $content;
                elseif($active_tab == 'daylong'):
                    echo $content;
                else:
                    echo '<div class="rbfw_alert_warning"><i class="fa-solid fa-circle-info"></i> '.esc_html__("Sorry, the day-night package is not available on the same check-in and check-out date.","booking-and-rental-manager-for-woocommerce").'</div>';   
                endif;    
            endif;
            
            wp_die();
        }

        public function rbfw_room_price_calculation(){
            if(isset($_POST['checkin_date']) && isset($_POST['checkout_date'])):
            global $rbfw;
            $content            = '';
            $checkin_date       = $_POST['checkin_date'];
            $checkout_date      = $_POST['checkout_date'];
            $start_date = $checkin_date;
            $end_date = $checkout_date;
            $post_id            = strip_tags($_POST['post_id']);
            $origin             = date_create($checkin_date);
            $target             = date_create($checkout_date);
            $interval           = date_diff($origin, $target);
            $total_days         = $interval->format('%a');            
            $room_price_arr     = $_POST['room_price_arr'];
            $service_price_arr  = !empty($_POST['service_price_arr']) ? $_POST['service_price_arr'] : [];
            $room_price         = 0;
            $service_price      = 0;
            $total_room_price   = 0;
            $total_service_price = 0;
            $subtotal_price     = 0;
            $total_price        = 0;

            foreach ($room_price_arr as $key => $value):
                $room_price += (float)$value['data_qty'] * (float)$value['data_price'];
            endforeach;

            if($room_price > 0 && $total_days > 0):
                $total_room_price = (float)$room_price * (int)$total_days;
            else:
                $total_room_price = (float)$room_price;
            endif;

            if(!empty($service_price_arr)){
                foreach ($service_price_arr as $key => $value):
                    $service_price += (float)$value['data_qty'] * (float)$value['data_price'];
                endforeach;
            }

            if($service_price > 0):
                $total_service_price = (float)$service_price;
            endif;

            if($total_room_price > 0 || $total_service_price > 0):
                $subtotal_price = (float)$total_room_price + (float)$total_service_price;
            endif;

            if($subtotal_price > 0):
                $total_price = (float)$subtotal_price;
            endif;

            /* Start Tax Calculations */
            $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
            $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
            $mps_tax_percentage = !empty(get_post_meta($post_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($post_id, 'rbfw_mps_tax_percentage', true)) : '';
            $percent = 0;
            $tax_status = '';
            if($rbfw_payment_system == 'mps' &&  $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                //Convert our percentage value into a decimal.
                $percentInDecimal = $mps_tax_percentage / 100;
                //Get the result.
                $percent = $percentInDecimal * $total_price;
                $total_price = $total_price + $percent;
            }

            if($rbfw_payment_system == 'mps' &&  $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){
                $tax_status = '('.__('Includes','booking-and-rental-manager-for-woocommerce').' '.rbfw_mps_price($percent).' '.__('Tax','booking-and-rental-manager-for-woocommerce').')';
            }

            /* End Tax Calculations */

            $content.= '<div class="item rbfw_room_price_summary">
                            <div class="item-content rbfw-costing">
                                <ul class="rbfw-ul">
                                    <li class="duration-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_room_price.'">'.rbfw_mps_price($total_room_price).'</span></li>
                                    <li class="resource-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_service_price.'">'.rbfw_mps_price($total_service_price).'</span></li>
                                    <li class="subtotal">'.$rbfw->get_option('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$subtotal_price.'">'.rbfw_mps_price($subtotal_price).'</span></li>';

                                    if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){

                                        $content.= '<li class="tax">'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$percent.'">'.rbfw_mps_price($percent).'</span></li>';
                                    }

                                    /* Start Discount Calculations */

                                    $discount_arr = rbfw_get_discount_array($post_id, $start_date, $end_date, $total_price);

                                    if(!empty($discount_arr)){
                                        
                                        $total_price = $discount_arr['total_amount'];
                                        $discount_type = $discount_arr['discount_type'];
                                        $discount_amount = $discount_arr['discount_amount'];
                                        $discount_desc = $discount_arr['discount_desc'];

                                        $content .= '<li class="discount">';
                                        $content .= $rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'));
                                        $content .= '<span>'.$discount_desc.'</span>';                
                                        $content .= '</li>';                    
                                    }
                                    /* End Discount Calculations */

                                    $content.='<li class="total"><strong>'.$rbfw->get_option('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span class="price-figure" data-price="'.$total_price.'">'.rbfw_mps_price($total_price).' '.$tax_status.'</span></li>
                                </ul>
                                <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                            </div>
                        </div>';          
            echo $content;

            else:
                esc_html_e('Something is wrong! Please try again.','booking-and-rental-manager-for-woocommerce');
            endif;

            wp_die();
        }

        public function rbfw_resort_frontend_scripts($rbfw_post_id){
            global $rbfw;
            global $post;
            $post_id = !empty($post->ID) ? $post->ID : '';

            if(!empty($rbfw_post_id)){
                $post_id = $rbfw_post_id;
            }

            if(empty($post_id)){
                return;
            }

            $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
            if($rent_type != 'resort' && ( is_a( $post, 'WP_Post' ) && ! has_shortcode( $post->post_content, 'rent-add-to-cart') )):
                return;
            endif;
            $rbfw_enable_resort_daylong_price  = get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) : 'no';
            ?>
            <script>
            
            jQuery(document).ready(function(){  
                // Check-in date picker

                jQuery('#checkin_date').datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0
                });
                
                jQuery('#checkin_date').change(function(e) {

                    let selected_date = jQuery(this).val();
                    const [gYear, gMonth, gDay] = selected_date.split('-');
                    jQuery("#checkout_date").datepicker("destroy");
                    jQuery("#checkout_date").val('');
                    jQuery("#checkout_date").attr('value', '');
                    jQuery('#checkout_date').datepicker({
                        dateFormat: 'yy-mm-dd',
                        minDate: new Date(gYear, gMonth - 1, gDay)
                    });

                });
                
                // end check-in date picker

                // resort check availability ajax
                jQuery(".rbfw_chk_availability_btn").click(function(e) {	
                        e.preventDefault();
                        let checkin_date_notice 	= "<?php echo esc_html($rbfw->get_option('rbfw_text_choose_checkin_date', 'rbfw_basic_translation_settings', __('Please Choose Check-In Date','booking-and-rental-manager-for-woocommerce'))); ?>";
                        let checkout_date_notice 	= "<?php echo esc_html($rbfw->get_option('rbfw_text_choose_checkout_date', 'rbfw_basic_translation_settings', __('Please Choose Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>";
                        let checkin_date 			= jQuery('#checkin_date').val();
                        let checkout_date 			= jQuery('#checkout_date').val();
                        let post_id 				= jQuery('#rbfw_post_id').val();
                        let reset_active_tab        = jQuery('.rbfw_room_price_category_tabs').removeAttr('data-active');
                        let reset_active_class      = jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label').removeClass('active');
                        let reset_pricing_table     = jQuery('.rbfw_room_price_category_details').empty();

                        if(checkin_date == ''){
                            tippy('#checkin_date', {content: checkin_date_notice,theme: 'blue',placement: 'top',trigger: 'click'});
                            jQuery('#checkin_date').trigger('click');
                            return false;
                        }
                        if(checkout_date == ''){
                            tippy('#checkout_date', {content: checkout_date_notice,theme: 'blue',placement: 'top',trigger: 'click'});
                            jQuery('#checkout_date').trigger('click');
                            return false;
                        }				
                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action' 		: 'rbfw_check_resort_availibility',
                                'post_id' 		: post_id,
                                'checkin_date' 	: checkin_date,
                                'checkout_date' : checkout_date
                            },
                            beforeSend: function() {
                                jQuery('.rbfw_room_price_category_tabs').empty();
                                jQuery('.rbfw-availability-loader').css("display","block");
                            },		
                            success: function (response) {
                                jQuery('.rbfw-availability-loader').hide();
                                
                                if (response.indexOf('min_max_day_notice') >= 0){

                                    jQuery('.rbfw_room_price_category_details').html(response);

                                } else{

                                    jQuery('.rbfw_room_price_category_tabs').html(response);
                                }

                                rbfw_room_price_label_func();

                            }
                        });
                        
                    });
                // end resort check availability ajax
                
                // resort price label function
                function rbfw_room_price_label_func(){
                    let active_value = jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label.active .rbfw_room_price_category').val();
                    jQuery('.rbfw_room_price_category_tabs').attr('data-active',active_value);        
                    tippy('.rbfw_room_price_label.disabled', {content: 'Not Available!',theme: 'blue',placement: 'top'});

  

                    // onclick resort price button
                    jQuery('.rbfw_room_price_label').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            let target_label = jQuery(this);
                            let target_value = jQuery(this).find('.rbfw_room_price_category').val();                   
                            if(jQuery(target_label).hasClass('disabled')){								
                                return false;
                            }
                            jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label').removeClass('active');
                            jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label .rbfw_room_price_category').prop('checked',false);
                            jQuery(this).addClass('active');
                            jQuery(this).find('.rbfw_room_price_category').prop('checked',true);
                            jQuery('.rbfw_room_price_category_tabs').attr('data-active',target_value);
                            rbfw_resort_get_price_table();
                        });
                    // end onclick resort price button 
                    
                    <?php if($rbfw_enable_resort_daylong_price != 'yes') { ?>

                        jQuery('.rbfw_room_price_label[for="rbfw_room_daynight_price"]').trigger('click');
                        jQuery('.rbfw_room_price_category_tabs').hide();
                        
                    <?php } ?>
                }
                // end resort price label function

                // rbfw_resort_get_price_table
               function rbfw_resort_get_price_table(){
                    let active_tab_value = jQuery('.rbfw_room_price_category_tabs').attr('data-active');
                    let post_id 		 = jQuery('#rbfw_post_id').val();
                    let checkin_date     = jQuery('#checkin_date').val();
                    let checkout_date    = jQuery('#checkout_date').val();
                    jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action'        : 'rbfw_get_active_price_table',
                                'post_id'       : post_id,
                                'active_tab'    : active_tab_value,
                                'checkin_date'  : checkin_date,
                                'checkout_date' : checkout_date
                            },
                            beforeSend: function() {
                                jQuery('.rbfw_room_price_category_details').empty();
                                jQuery('.rbfw_room_price_category_details_loader').css("display","block");
                            },		
                            success: function (response) {
                                jQuery('.rbfw_room_price_category_details_loader').hide();
                                jQuery('.rbfw_room_price_category_details').html(response);
                                rbfw_update_input_value_onchange_onclick();
                                rbfw_room_price_calculation();
                                rbfw_mps_book_now_btn_action();
                                rbfw_display_resort_es_box_onchange_onclick()
                            }
                    });                    
               }
                // end rbfw_resort_get_price_table

                // On change quantity value calculate price
                function rbfw_room_price_calculation(){
                    let room_price_arr = {};
                    let service_price_arr = {};
                    let post_id = jQuery('#rbfw_post_id').val();
                    jQuery('.rbfw_room_qty_plus,.rbfw_room_qty_minus,.rbfw_service_qty_minus,.rbfw_service_qty_plus').click(function (e) {
                        let checkin_date     = jQuery('#checkin_date').val();
                        let checkout_date    = jQuery('#checkout_date').val();
                        let data_cat         = jQuery(this).siblings('input[type=number]').attr('data-cat');
                        if(data_cat == 'room'){
                            let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
                            let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
                            let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
                            if(data_qty == 0){
                                delete room_price_arr[data_type];
                            }
                            else{
                                room_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
                            }
                        }
                        if(data_cat == 'service'){
                            let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
                            let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
                            let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
                            if(data_qty == 0){
                                delete service_price_arr[data_type];
                            }
                            else{
                                service_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
                            }
                        }           
                        jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax.rbfw_ajaxurl,
                                data: {
                                    'action'        : 'rbfw_room_price_calculation',
                                    'post_id'       : post_id,
                                    'checkin_date'  : checkin_date,
                                    'checkout_date' : checkout_date,
                                    'room_price_arr': room_price_arr,
                                    'service_price_arr': service_price_arr
                                },
                                beforeSend: function() {
                                    jQuery('.rbfw_room_price_summary').empty();
                                    jQuery('.rbfw_room_price_summary').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');
                                },		
                                success: function (response) {
                                    jQuery('.rbfw_rp_loader').hide();
                                    jQuery('.rbfw_room_price_summary').html(response);
                                    let get_total_price = jQuery('.rbfw_room_price_summary .duration-costing .price-figure').attr('data-price');
                                    if(get_total_price > 0){
                                        jQuery('.rbfw_room_price_category_details button.rbfw_resort_book_now_btn').removeAttr('disabled');
                                    }
                                    else{
                                        jQuery('.rbfw_room_price_category_details button.rbfw_resort_book_now_btn').attr('disabled',true);
                                    }
                                    
                                }
                        });
                        
                    });
                    jQuery('.rbfw_room_qty,.rbfw_service_qty').change(function (e) {
                        let checkin_date     = jQuery('#checkin_date').val();
                        let checkout_date    = jQuery('#checkout_date').val();
                        let data_cat         = jQuery(this).attr('data-cat');
                        if(data_cat == 'room'){
                            let data_qty         = jQuery(this).attr('value');
                            let data_price       = jQuery(this).attr('data-price');
                            let data_type        = jQuery(this).attr('data-type');
                            if(data_qty == 0){
                                delete room_price_arr[data_type];
                            }
                            else{
                                room_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
                            }
                        }
                        if(data_cat == 'service'){
                            let data_qty         = jQuery(this).attr('value');
                            let data_price       = jQuery(this).attr('data-price');
                            let data_type        = jQuery(this).attr('data-type');
                            if(data_qty == 0){
                                delete service_price_arr[data_type];
                            }
                            else{
                                service_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
                            }
                        }           
                        jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax.rbfw_ajaxurl,
                                data: {
                                    'action'        : 'rbfw_room_price_calculation',
                                    'checkin_date'  : checkin_date,
                                    'checkout_date' : checkout_date,
                                    'room_price_arr': room_price_arr,
                                    'service_price_arr': service_price_arr
                                },
                                beforeSend: function() {
                                    jQuery('.rbfw_room_price_summary').empty();
                                    jQuery('.rbfw_room_price_summary').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');
                                },		
                                success: function (response) {
                                    jQuery('.rbfw_rp_loader').hide();
                                    jQuery('.rbfw_room_price_summary').html(response);
                                    let get_total_price = jQuery('.rbfw_room_price_summary .duration-costing .price-figure').attr('data-price');
                                    if(get_total_price > 0){
                                        jQuery('.rbfw_room_price_category_details button.rbfw_resort_book_now_btn').removeAttr('disabled');
                                    }
                                    else{
                                        jQuery('.rbfw_room_price_category_details button.rbfw_resort_book_now_btn').attr('disabled',true);
                                    }
                                }
                        });
                    });
                  
                }
               // On change quantity value calculate price
                
                // update input value onclick and onchange
                function rbfw_update_input_value_onchange_onclick(){
                    jQuery('.rbfw_room_qty_plus,.rbfw_service_qty_plus').click(function (e) {
                        let target_input = jQuery(this).siblings("input[type=number]");
                        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
                        let max_value = parseInt(jQuery(this).siblings("input[type=number]").attr('max'));
                        let update_value = current_value + 1;

                        if(update_value <= max_value){
                            jQuery(target_input).val(update_value);
                            jQuery(target_input).attr('value',update_value);
                        }else{
                            let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
                            tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top',trigger: 'click'});
                        }                      
                    });
                    jQuery('.rbfw_room_qty_minus,.rbfw_service_qty_minus').click(function (e) { 
                        let target_input = jQuery(this).siblings("input[type=number]");
                        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
                        let update_value = current_value - 1;
                        if(current_value > 0){
                            jQuery(target_input).val(update_value);
                            jQuery(target_input).attr('value',update_value);
                        }
                    });
                    jQuery('.rbfw_room_qty,.rbfw_service_qty').change(function (e) { 
                        let get_value = jQuery(this).val();
                        let max_value = parseInt(jQuery(this).attr('max'));

                        if(get_value <= max_value){
                            jQuery(this).val(get_value);
                            jQuery(this).attr('value',get_value);
                        }else{
                            jQuery(this).val(max_value);
                            jQuery(this).attr('value',max_value);
                            let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
                            tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top'});
                        }
                    });
                }
                // end update input value onclick and onchange

                // display extra services box onclick and onchange
                function rbfw_display_resort_es_box_onchange_onclick(){
                    
                    jQuery('.rbfw_room_qty_plus,.rbfw_room_qty_minus').click(function (e) {
                        
                        let count = jQuery('.rbfw_resort_rt_price_table tbody tr').length;
                        let total_qty = 0;
                        for (let index = 0; index < count; index++) {
                            let qty = jQuery('input[name="rbfw_room_info['+index+'][room_qty]"]').val();
                            total_qty += parseInt(qty); 
                        }

                        if(total_qty > 0){
                            jQuery('.rbfw_resort_es_price_table').show();
                            jQuery('.rbfw_resort_available_es_qty_notice').show();
                        }else{
                            jQuery('.rbfw_service_qty').val('0');
                            jQuery('.rbfw_service_qty').trigger('change');
                            jQuery('.rbfw_resort_es_price_table').hide();
                            jQuery('.rbfw_resort_available_es_qty_notice').hide();
                        }
                        
                    });

                    jQuery('.rbfw_room_qty').change(function (e) {
                        let count = jQuery('.rbfw_resort_rt_price_table tbody tr').length;
                        let total_qty = 0;
                        for (let index = 0; index < count; index++) {
                            let qty = jQuery('input[name="rbfw_room_info['+index+'][room_qty]"]').val();
                            total_qty += parseInt(qty); 
                        }

                        if(total_qty > 0){
                            
                            jQuery('.rbfw_resort_es_price_table').show();
                            jQuery('.rbfw_resort_available_es_qty_notice').show();
                        }else{
                            jQuery('.rbfw_service_qty').val('0');
                            jQuery('.rbfw_service_qty').trigger('change');
                            jQuery('.rbfw_resort_es_price_table').hide();
                            jQuery('.rbfw_resort_available_es_qty_notice').hide();
                        }
                    });
                }
                // end display extra services box onclick and onchange                
             
                function rbfw_mps_book_now_btn_action(){
                    jQuery('button.rbfw_resort_book_now_btn.mps_enabled').click(function (e) { 
                        e.preventDefault();
                        let start_date = jQuery('#checkin_date').val();
                        let end_date = jQuery('#checkout_date').val();
                        let rent_type = jQuery('#rbfw_rent_type').val();
                        let package = jQuery('.rbfw_room_price_category_tabs').attr('data-active');
                        let type_length = jQuery('.rbfw_resort_rt_price_table tbody tr').length;
                        let service_length = jQuery('.rbfw_resort_es_price_table tbody tr').length;
                        let type_array = {};
                        let service_array = {};
                        let post_id = jQuery('#rbfw_post_id').val();
                        for (let index = 0; index < type_length; index++) {
                            let qty = jQuery('input[name="rbfw_room_info['+index+'][room_qty]"]').val();
                            let data_type = jQuery('input[name="rbfw_room_info['+index+'][room_qty]"]').attr('data-type');
                            if(qty > 0){
                                type_array[data_type] = qty;
                            }
                        }
                        for (let index = 0; index < service_length; index++) {
                            let qty = jQuery('input[name="rbfw_service_info['+index+'][service_qty]"]').val();
                            let data_type = jQuery('input[name="rbfw_service_info['+index+'][service_qty]"]').attr('data-type');
                            if(qty > 0){
                                service_array[data_type] = qty;
                            }
                        }
                  
                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action' : 'rbfw_mps_user_login',
                                'post_id': post_id,
                                'rent_type': rent_type,
                                'start_date': start_date,
                                'end_date': end_date,
                                'package': package,
                                'type_info[]': type_array,
                                'service_info[]': service_array

                            },
                            beforeSend: function() {
                                jQuery('.rbfw_resort_item_wrapper').hide();
                                jQuery('.rbfw-resort-result-loader').html('<i class="fas fa-spinner fa-spin"></i>');
                            },		
                            success: function (response) {
                                jQuery('.rbfw-resort-result-loader').hide();
                                jQuery('.rbfw-resort-result').append(response);
                                rbfw_on_submit_user_form_action(post_id,rent_type,start_date,end_date,package,type_array,service_array);
                                rbfw_mps_checkout_header_link();
                            }
                        });
                                               
                    });
                }

                function rbfw_on_submit_user_form_action(post_id,rent_type,start_date,end_date,package,type_array,service_array){
                    jQuery( ".rbfw_mps_form_wrap form" ).on( "submit", function( e ) {
                        e.preventDefault();
                        let this_form = jQuery(this);
                        let form_data = jQuery(this).serialize();

                        jQuery.ajax({
                        type: 'POST',
                        url: rbfw_ajax.rbfw_ajaxurl,
                        data: form_data,
                        beforeSend: function() {
                            jQuery('.rbfw_mps_user_form_result').empty();
                            jQuery('.rbfw_mps_user_button i').addClass('fa-spinner');
                        },		
                        success: function (response) {  
                            jQuery('.rbfw_mps_user_button i').removeClass('fa-spinner');
                            
                            this_form.find('.rbfw_mps_user_form_result').html(response);
                            if (response.indexOf('mps_alert_login_success') >= 0){
                                jQuery('.rbfw_mps_user_order_summary').remove();
                                jQuery('.rbfw_mps_user_form_wrap').remove();                         
                                jQuery('button.rbfw_resort_book_now_btn.mps_enabled').trigger('click');
                            } 
                        }
                        });
                    });

                    jQuery('.rbfw_mps_user_payment_method').click(function (e) {
                        let this_value = jQuery(this).val();
                        jQuery(this).prop("checked", true);
                        jQuery('.rbfw_mps_pay_now_button').removeAttr('disabled');
                        jQuery('input[name="rbfw_mps_payment_method"]').val(this_value);
                        jQuery('.rbfw_mps_user_form_result').empty();
                        jQuery('.rbfw_mps_payment_form_notice').empty();
                        
                        if(this_value == 'stripe'){
                            let target = jQuery('.mp_rbfw_ticket_form');
                            let first_name = target.find('input[name="rbfw_mps_user_fname"]').val();
                            let last_name = target.find('input[name="rbfw_mps_user_lname"]').val();
                            let email = target.find('input[name="rbfw_mps_user_email"]').val();
                            let submit_request = target.find('input[name="rbfw_mps_user_submit_request"]').val();
                            let security = target.find('input[name="rbfw_mps_order_place_nonce"]').val();
                            let payment_method = target.find('input[name="rbfw_mps_payment_method"]').val();

                            jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action' : 'rbfw_mps_stripe_form',
                                'post_id': post_id,
                                'rent_type': rent_type,
                                'start_date': start_date,
                                'start_time': '',
                                'end_date': end_date,
                                'end_time': '',
                                'package': package,
                                'type_info[]': type_array,
                                'service_info[]': service_array,
                                'security' : security,
                                'first_name' : first_name,
                                'last_name' : last_name,
                                'email' : email,
                                'payment_method' : payment_method,
                                'submit_request' : submit_request
                            },
                            beforeSend: function(response) {
                                target.find('.rbfw_mps_payment_form_wrap').empty();
                                target.find('.rbfw_mps_payment_form_wrap').html('<i class="fas fa-spin fa-spinner"></i>');
                                jQuery('.rbfw_mps_pay_now_button').hide();
                            },		
                            success: function (response) { 
                                target.find('.rbfw_mps_payment_form_wrap').empty();
                                target.find('.rbfw_mps_payment_form_wrap').html(response);
                            }
                            });

                        }else{
                            jQuery('.rbfw_mps_payment_form_wrap').empty();
                            jQuery('.rbfw_mps_pay_now_button').show();
                        }
                    });

                    jQuery('.mp_rbfw_ticket_form').on( "submit", function( e ) {
                        let target = jQuery(this);
                        let payment_method = target.find('input[name="rbfw_mps_payment_method"]').val();

                        if(payment_method == 'offline'){
                            e.preventDefault();
                            let target = jQuery(this);
                            let first_name = target.find('input[name="rbfw_mps_user_fname"]').val();
                            let last_name = target.find('input[name="rbfw_mps_user_lname"]').val();
                            let email = target.find('input[name="rbfw_mps_user_email"]').val();
                            let submit_request = target.find('input[name="rbfw_mps_user_submit_request"]').val();
                            let security = target.find('input[name="rbfw_mps_order_place_nonce"]').val();

                            
                            jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action' : 'rbfw_mps_place_order_form_submit',
                                'post_id': post_id,
                                'rent_type': rent_type,
                                'start_date': start_date,
                                'start_time': '',
                                'end_date': end_date,
                                'end_time': '',
                                'package': package,
                                'type_info[]': type_array,
                                'service_info[]': service_array,
                                'security' : security,
                                'first_name' : first_name,
                                'last_name' : last_name,
                                'email' : email,
                                'payment_method' : payment_method,
                                'submit_request' : submit_request

                            },
                            beforeSend: function() {
                                target.find('.rbfw_mps_user_form_result').empty();
                                jQuery('.rbfw_mps_pay_now_button i').addClass('fa-spinner');
                            },		
                            success: function (response) { 
                                jQuery('.rbfw_mps_pay_now_button i').removeClass('fa-spinner');
                                target.find('.rbfw_mps_user_form_result').html(response); 
                                
                            }
                            });
                            
                        }
                        
                        if(payment_method == 'paypal'){

                        let first_name = target.find('input[name="rbfw_mps_user_fname"]').val();
                        let last_name = target.find('input[name="rbfw_mps_user_lname"]').val();
                        let email = target.find('input[name="rbfw_mps_user_email"]').val();

                        if(first_name == '' || last_name == '' || email == ''){
                            e.preventDefault();
                        }

                            jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax.rbfw_ajaxurl,
                                data: {
                                    'action' : 'rbfw_mps_paypal_form_validation',
                                    'first_name' : first_name,
                                    'last_name' : last_name,
                                    'email' : email
                                },
                                beforeSend: function() {
                                    target.find('.rbfw_mps_user_form_result').empty();
                                    jQuery('.rbfw_mps_pay_now_button i').addClass('fa-spinner');
                                },		
                                success: function (response) { 
                                    jQuery('.rbfw_mps_pay_now_button i').removeClass('fa-spinner');
                                    target.find('.rbfw_mps_user_form_result').html(response);    
                                }
                            });
                        }                        
                                                    
                    });
                }

                function rbfw_mps_checkout_header_link(){
                    jQuery('.rbfw_mps_header_action_link').click(function (e) { 
                        e.preventDefault();
                        jQuery('.rbfw_mps_user_form_result').empty();
                        jQuery('.rbfw_mps_form_wrap').hide();
                        let this_data_id = jQuery(this).attr('data-id');
                        jQuery('.rbfw_mps_form_wrap[data-id="'+this_data_id+'"]').show();
                    });
                }
            });
            </script>
            <?php
        }

        public function rbfw_resort_admin_scripts(){
            $rbfw_item_type  = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : '';
            $rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';
            ?>
            <script>
            jQuery(document).ready(function(){    
				// Rent type change action

                let rbfw_item_type = jQuery('#rbfw_item_type').val();

                if(rbfw_item_type == 'resort'){
                    jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').hide();
                }else{
                    jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                }
				// End type change action

				// Room type add image button and remove image button function
				function rbfw_room_type_image_addup(){				
					// onclick resort type add image button action
					jQuery('.rbfw_room_type_image_btn').click(function() {
						let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
						let send_attachment_bkp = wp.media.editor.send.attachment;
						wp.media.editor.send.attachment = function(props, attachment) {
							jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview img').remove();
							jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview').append('<img src="'+attachment.url+'"/>');
							jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_image').val(attachment.id);
							wp.media.editor.send.attachment = send_attachment_bkp;
						}
						wp.media.editor.open(jQuery(this));
						return false;
					});
					// end onclick resort type add image button action

					// onclick resort type remove image button action
					jQuery('.rbfw_remove_room_type_image_btn').click(function() {
						let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
						jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview img').remove();
						jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_image').val('');
					});
					// end onclick resort type remove image button action
				}
				rbfw_room_type_image_addup();
				// End room type add image button and remove image button function

				jQuery( ".rbfw_resort_price_table_body" ).sortable();
				
				// onclick add-resort-type-btn action
					jQuery('#add-resort-type-row').click(function (e) { 
						e.preventDefault();
						let current_time = jQuery.now();
						if(jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row').length){						
							let resort_last_row = jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row:last-child()');
							let resort_type_last_data_key = parseInt(resort_last_row.attr('data-key'));
							let resort_type_new_data_key = resort_type_last_data_key + 1;
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="'+resort_type_new_data_key+'"><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][room_type]" value="" placeholder="<?php esc_html_e( "Room type", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( "Add Image", "rent-manager-for-woocommerce" ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_image]" value="" class="rbfw_room_image"></td><td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data[0][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( "Day-long Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( "Day-night Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_desc]" value="" placeholder="<?php esc_html_e( "Short Description", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_available_qty]" value="" placeholder="<?php esc_html_e( "Available Qty", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
							let resort_type_add_new_row = jQuery('.rbfw_resort_price_table').append(resort_type_row);
						}
						else{
							let resort_type_new_data_key = 0;
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="'+resort_type_new_data_key+'"><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][room_type]" value="" placeholder="<?php esc_html_e( "Room type", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( "Add Image", "rent-manager-for-woocommerce" ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_image]" value="" class="rbfw_room_image"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( "Day-long Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( "Day-night Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_desc]" value="" placeholder="<?php esc_html_e( "Short Description", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_available_qty]" value="" placeholder="<?php esc_html_e( "Available Qty", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
							let resort_type_add_new_row = jQuery('.rbfw_resort_price_table').append(resort_type_row);					
						}
						jQuery('.remove-row.'+current_time+'').on('click', function () {
							e.preventDefault();
							e.stopImmediatePropagation();
							if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
								jQuery(this).parents('tr').remove();
							} else {
								return false;
							}
						});
						jQuery( ".rbfw_resort_price_table_body" ).sortable();	
						rbfw_room_type_image_addup();														
					});
				// end add-resort-type-btn action

            });
            </script>
            <?php
        }
    }
    new RBFW_Resort_Function();
}