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
            add_action('wp_ajax_rbfw_check_resort_availibility', array($this, 'rbfw_check_resort_availibility'));
            add_action('wp_ajax_nopriv_rbfw_check_resort_availibility', array($this,'rbfw_check_resort_availibility'));
            add_action('wp_ajax_rbfw_get_active_price_table', array($this, 'rbfw_get_active_price_table'));
            add_action('wp_ajax_nopriv_rbfw_get_active_price_table', array($this,'rbfw_get_active_price_table'));
            add_action('wp_ajax_rbfw_room_price_calculation', array($this, 'rbfw_room_price_calculation'));
            add_action('wp_ajax_nopriv_rbfw_room_price_calculation', array($this,'rbfw_room_price_calculation'));
        }

        public function rbfw_resort_frontend_scripts()
        {
            wp_enqueue_script( 'resort_script', RBFW_PLUGIN_URL . '/assets/mp_script/resort_script.js', array(), time(), true );
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

        public function rbfw_resort_ticket_info($product_id, $checkin_date, $checkout_date, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info = null, $rbfw_regf_info = array()){
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

                $security_deposit = rbfw_security_deposit($product_id,$total_price);
                $total_price = $total_price + $security_deposit['security_deposit_amount'];

                /* Start Tax Calculations */
                $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option_trans('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_format = $rbfw->get_option_trans('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
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


                if(function_exists('rbfw_get_discount_array')){

                    $discount_arr = rbfw_get_discount_array($post_id, $start_date, $end_date, $total_price);

                } else {

                    $discount_arr = [];
                }
                $discount_type = '';
                $discount_amount = 0;
                if(!empty($discount_arr)){
                    $total_price = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                }
                /* End Discount Calculations */

                $main_array[0]['ticket_name'] = $title;
                $main_array[0]['ticket_price'] = $total_price;
                $main_array[0]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
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
                $main_array[0]['rbfw_regf_info'] = $rbfw_regf_info;

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
                $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option_trans('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_format = $rbfw->get_option_trans('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
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

            if($_POST['rbfw_enable_resort_daylong_price']=='yes'){

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

                $is_muffin_template = $_POST['is_muffin_template'];

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

                    if($is_muffin_template == 1){
                        $content .= do_action('rbfw_discount_ad', $post_id, 'muffin');
                    }

                    $content .= '<div class="rbfw_room_price_category_tabs_title mb-08">'.$rbfw->get_option_trans('rbfw_text_select_booking_type', 'rbfw_basic_translation_settings', __('CHOOSE BOOKING TYPE','booking-and-rental-manager-for-woocommerce')).'</div>';

                    $content .= '<div class="rbfw_room_price_category_tabs_label" data-days="'.$days.'">';

                    if($daylong_counter > 0 && $daynight_counter > 0){
                        $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label"><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price">'.$rbfw->get_option_trans('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option_trans('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                        $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" checked>'.$rbfw->get_option_trans('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option_trans('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                    }
                    if($daylong_counter > 0 && $daynight_counter == 0) {
                        $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price" checked>'.$rbfw->get_option_trans('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option_trans('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                        $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label disabled"><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" disabled>'.$rbfw->get_option_trans('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce')).'<small>'.$rbfw->get_option_trans('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay','booking-and-rental-manager-for-woocommerce')).'</small></label>';
                    }
                    if($daylong_counter == 0 && $daynight_counter > 0) {
                        $content .= '<label for="rbfw_room_daylong_price" class="rbfw_room_price_label disabled"><input type="radio" name="rbfw_room_price_category" value="daylong" class="rbfw_room_price_category" id="rbfw_room_daylong_price" disabled>' . $rbfw->get_option_trans('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong', 'booking-and-rental-manager-for-woocommerce')) . '<small>' . $rbfw->get_option_trans('rbfw_text_daylong_subtitle', 'rbfw_basic_translation_settings', __('9 AM to 6 PM', 'booking-and-rental-manager-for-woocommerce')) . '</small></label>';
                        $content .= '<label for="rbfw_room_daynight_price" class="rbfw_room_price_label "><input type="radio" name="rbfw_room_price_category" value="daynight" class="rbfw_room_price_category" id="rbfw_room_daynight_price" checked>' . $rbfw->get_option_trans('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight', 'booking-and-rental-manager-for-woocommerce')) . '<small>' . $rbfw->get_option_trans('rbfw_text_daynight_subtitle', 'rbfw_basic_translation_settings', __('Day & Night Stay', 'booking-and-rental-manager-for-woocommerce')) . '</small></label>';
                    }

                    $content .= '</div>';
                    echo $content;
                }
                else{
                    echo $errors;
                }
                wp_die();

            }else{
                $this->rbfw_get_active_price_table($_POST['post_id'],'daynight',strip_tags($_POST['checkin_date']),strip_tags($_POST['checkout_date']));
            }
        }

        public function rbfw_get_active_price_table($post_id=0,$active_tab='',$checkin_date='',$checkout_date=''){

            global $rbfw;
            if(!($post_id && $active_tab)){
                $post_id = $_POST['post_id'];
                $active_tab = $_POST['active_tab'];
                $checkin_date = strip_tags($_POST['checkin_date']);
                $checkout_date = strip_tags($_POST['checkout_date']);
            }
            if(isset($post_id) && isset($active_tab)){
                $origin             = date_create($checkin_date);
                $target             = date_create($checkout_date);
                $interval           = date_diff($origin, $target);
                $total_days         = $interval->format('%a');
                $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
                $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
                $rbfw_product_id = get_post_meta( $post_id, "link_wc_product", true ) ? get_post_meta( $post_id, "link_wc_product", true ) : $post_id;
                $currency_symbol = rbfw_mps_currency_symbol();
                $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                if($rbfw_payment_system == 'mps'){
                    $rbfw_payment_system = 'mps_enabled';
                }else{
                    $rbfw_payment_system = 'wps_enabled';
                }

                $available_qty_info_switch = get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) : 'no';

                $content    = '';
                $content   .= '<div class="rbfw_resort_rt_price_table_container">';
                $content   .= '<table class="rbfw_room_price_table rbfw_resort_rt_price_table">';
                $content   .= '<thead>';
                $content   .= '<tr>';
                $content   .= '<th>'.$rbfw->get_option_trans('rbfw_text_room_type', 'rbfw_basic_translation_settings', __('Room Type','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th>'.$rbfw->get_option_trans('rbfw_text_room_image', 'rbfw_basic_translation_settings', __('Image','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th>'.$rbfw->get_option_trans('rbfw_text_room_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th class="w_30_pc">'.$rbfw->get_option_trans('rbfw_text_room_qty', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '</tr>';
                $content   .= '</thead>';
                $content   .= '<tbody>';
                $i = 0;
                foreach ($rbfw_resort_room_data as $key => $value):

                    $img_url    = wp_get_attachment_url($value['rbfw_room_image']);
                    $uniq_id    = rand();
                    if($img_url) {
                        $img = '<a href="#rbfw_room_img_' . $uniq_id . '" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                        $img .= '<div id="rbfw_room_img_' . $uniq_id . '" class="mage_modal"><img src="' . esc_url($img_url) . '"/></div>';
                    }else{
                        $img = '';
                    }

                    if($active_tab == 'daylong') {
                        $price = $value['rbfw_room_daylong_rate'];
                    }elseif($active_tab == 'daynight') {
                        $price = $value['rbfw_room_daynight_rate'];
                    }

                if($value['rbfw_room_available_qty'] > 0){

                    $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $checkin_date, $checkout_date, $value['room_type'],'','');

                    $max_available_qty = $max_available_qty['remaining_stock'];


                    $content   .= '<tr>';
                    $content   .= '<td>';
                    $content   .= '<span class="room_type_title">'.esc_html($value['room_type']).'</span>';
                    $content   .= '<input type="hidden" name="rbfw_room_info['.$i.'][room_type]" value="'.$value['room_type'].'"/>';

                    if($value['rbfw_room_desc']) {
                        $content .= '<small class="rbfw_room_desc">';
                        $content .= $value['rbfw_room_desc'];
                        $content .= '</small>';

                        if ($available_qty_info_switch == 'yes') {
                            $content .= '<small class="rbfw_available_qty_notice">(' . rbfw_string_return('rbfw_text_available', __('Available:', 'booking-and-rental-manager-for-woocommerce')) . $max_available_qty . ')</small>';
                        }

                        $content .= '<input type="hidden" name="rbfw_room_info[' . $i . '][room_desc]" value="' . $value['rbfw_room_desc'] . '"/>';
                    }

                    $content   .= '</td>';
                    $content   .= '<td>'.$img.'</td>';
                    $content   .= '<td>';
                    $content   .= rbfw_mps_price($price);
                    $content   .= '<input type="hidden" name="rbfw_room_info['.$i.'][room_price]" value="'.$price.'"/>';
                    $content   .='</td>';
                    $content   .= '<td>';
                    $content .= '<div class="rbfw_service_price_wrap">';
                    $content   .= '<div class="rbfw_qty_input">';
                    $content   .= '<a class="rbfw_qty_minus rbfw_room_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                    $content   .= '<input type="number" min="0" max="'.esc_attr($max_available_qty).'" value="0" name="rbfw_room_info['.$i.'][room_qty]" class="rbfw_room_qty" data-price="'.$price.'" data-type="'.$value['room_type'].'" data-cat="room"/>';
                    $content   .= '<a class="rbfw_qty_plus rbfw_room_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                    $content   .= '</div>';
                    $content   .= '</div>';
                    $content   .= '</td>';
                    $content   .= '</tr>';
                }

                $i++;
                endforeach;
                $content   .= '</tbody>';
                $content   .= '</table>';
                $content   .= '</div>';


                if(!empty($rbfw_extra_service_data)) {

                    $content .= '<table class="rbfw_room_price_table rbfw_resort_es_price_table">';
                    $content .= '<thead>';
                    $content .= '<tr>';
                    $content .= '<th>' . $rbfw->get_option_trans('rbfw_text_room_service_name', 'rbfw_basic_translation_settings', __('Service Name', 'booking-and-rental-manager-for-woocommerce')) . '</th>';
                    $content .= '<th>' . $rbfw->get_option_trans('rbfw_text_room_image', 'rbfw_basic_translation_settings', __('Image', 'booking-and-rental-manager-for-woocommerce')) . '</th>';
                    $content .= '<th>' . $rbfw->get_option_trans('rbfw_text_room_service_price', 'rbfw_basic_translation_settings', __('Price', 'booking-and-rental-manager-for-woocommerce')) . '</th>';
                    $content .= '<th class="w_30_pc">' . $rbfw->get_option_trans('rbfw_text_room_service_qty', 'rbfw_basic_translation_settings', __('Quantity', 'booking-and-rental-manager-for-woocommerce')) . '</th>';
                    $content .= '</tr>';
                    $content .= '</thead>';
                    $content .= '<tbody>';

                    $c = 0;

                    foreach ($rbfw_extra_service_data as $key => $value) {

                        $max_es_available_qty = rbfw_get_multiple_date_es_available_qty($post_id, $checkin_date, $checkout_date, $value['service_name']);

                        $img_url = wp_get_attachment_url($value['service_img']);
                        $uniq_id = rand();
                        if ($img_url) {
                            $img = '<a href="#rbfw_room_img_' . $uniq_id . '" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                            $img .= '<div id="rbfw_room_img_' . $uniq_id . '" class="mage_modal"><img src="' . esc_url($img_url) . '"/></div>';
                        } else {
                            $img = '';
                        }

                        if ($value['service_qty'] > 0) {

                            $content .= '<tr>';
                            $content .= '<td>';
                            $content .= $value['service_name'];
                            $content .= '<input type="hidden" name="rbfw_service_info[' . $c . '][service_name]" value="' . $value['service_name'] . '"/>';

                            if ($value['service_desc']) {
                                $content .= '<small class="rbfw_room_desc">';
                                $content .= $value['service_desc'];
                                $content .= '</small>';

                                if ($available_qty_info_switch == 'yes') {
                                    $content .= '<small class="rbfw_available_qty_notice">(' . rbfw_string_return('rbfw_text_available', __('Available:', 'booking-and-rental-manager-for-woocommerce')) . $max_es_available_qty . ')</small>';
                                }

                                $content .= '<input type="hidden" name="rbfw_service_info[' . $c . '][service_desc]" value="' . $value['service_desc'] . '"/>';
                            }

                            $content .= '</td>';
                            $content .= '<td>' . $img . '</td>';
                            $content .= '<td>';
                            $content .= rbfw_mps_price($value['service_price']);
                            $content .= '<input type="hidden" name="rbfw_service_info[' . $c . '][service_price]" value="' . $value['service_price'] . '"/>';
                            $content .= '</td>';
                            $content .= '<td>';
                            $content .= '<div class="rbfw_service_price_wrap">';
                            $content .= '<div class="rbfw_qty_input">';
                            $content .= '<a class="rbfw_qty_minus rbfw_service_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                            $content .= '<input type="number" min="0" max="' . esc_attr($max_es_available_qty) . '" value="0" name="rbfw_service_info[' . $c . '][service_qty]" class="rbfw_service_qty" data-price="' . $value['service_price'] . '" data-type="' . $value['service_name'] . '" data-cat="service"/>';
                            $content .= '<a class="rbfw_qty_plus rbfw_service_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                            $content .= '</div>';


                            $content .= '</div>';
                            $content .= '</td>';
                            $content .= '</tr>';
                        }

                        $c++;
                    }
                    $content .= '</tbody>';
                    $content .= '</table>';

                }

                $content   .= '<div class="item rbfw_room_price_summary">
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">'.$rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>
                                        <li class="resource-costing rbfw-cond">'.$rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>
                                        <li class="subtotal">'.$rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                        <li class="total"><strong>'.$rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>';

                /* Include Custom Registration Form */
                if(class_exists('Rbfw_Reg_Form')){
                    $reg_form = new Rbfw_Reg_Form();
                    $reg_fields = $reg_form->rbfw_generate_regf_fields($post_id);
                    $content.= $reg_fields;
                }
                /* End: Include Custom Registration Form */

                $content  .= '<div class="item rbfw_text_book_now">
                                <button type="submit" name="add-to-cart" value="'.$rbfw_product_id.'" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_resort_book_now_btn '.$rbfw_payment_system.'" disabled>
                                    '.$rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce')).'
                                </button>
                            </div>';

                if($active_tab == 'daynight' && $total_days > 0) {
                    echo $content;
                }elseif($active_tab == 'daylong') {
                    echo $content;
                }else{
                    echo '<div class="rbfw_alert_warning"><i class="fa-solid fa-circle-info"></i> '.esc_html__("Sorry, the day-night package is not available on the same check-in and check-out date.","booking-and-rental-manager-for-woocommerce").'</div>';
                }
            }

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

                $total_room_price_org = $total_room_price;

                //echo $total_room_price_org;exit;

            if($subtotal_price > 0):
                $total_price = (float)$subtotal_price;
            endif;

            /* Start Tax Calculations */
            $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $mps_tax_switch = $rbfw->get_option_trans('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
            $mps_tax_format = $rbfw->get_option_trans('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
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
                $tax_status = '('.rbfw_string_return('rbfw_text_includes',__('Includes','booking-and-rental-manager-for-woocommerce')).' '.rbfw_mps_price($percent).' '.rbfw_string_return('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')).')';
            }

            /* End Tax Calculations */


            $content.= '<div class="item rbfw_room_price_summary">
                            <div class="item-content rbfw-costing">
                                <ul class="rbfw-ul">
                                    <li class="duration-costing rbfw-cond">'.$rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_room_price_org.'">'.rbfw_mps_price($total_room_price_org).'</span></li>
                                    <li class="resource-costing rbfw-cond">'.$rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_service_price.'">'.rbfw_mps_price($total_service_price).'</span></li>
                                    <li class="subtotal">'.$rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$subtotal_price.'">'.rbfw_mps_price($subtotal_price).'</span></li>';
                                     $security_deposit = rbfw_security_deposit($post_id,$subtotal_price);
                                     if($security_deposit['security_deposit_amount']){
                                         $content.= '<li class="subtotal">'.(!empty(get_post_meta($post_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($post_id, 'rbfw_security_deposit_label', true) : 'Security Deposit') .'<span class="price-figure" data-price="'.$subtotal_price.'">'.$security_deposit['security_deposit_desc'].'</span></li>';
                                     }


                                    if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){

                                        $content.= '<li class="tax">'.$rbfw->get_option_trans('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$percent.'">'.rbfw_mps_price($percent).'</span></li>';
                                    }

                                    /* Start Discount Calculations */

                                    if(rbfw_check_discount_over_days_plugin_active() === true){

                                        if(function_exists('rbfw_get_discount_array')){

                                            $discount_arr = rbfw_get_discount_array($post_id, $start_date, $end_date, $total_price);

                                        } else {
                                            $discount_arr = [];
                                        }

                                        if(!empty($discount_arr)){
                                            $discount_amount = $discount_arr['discount_amount'];
                                            $discount_desc = $discount_arr['discount_desc'];
                                            $content .= '<li class="discount">';
                                            $content .= $rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'));
                                            $content .= '<span>'.$discount_desc.'</span>';
                                            $content .= '</li>';
                                        }
                                    }


                                    /* End Discount Calculations */

                                    $content.='<li class="total"><strong>'.$rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span class="price-figure" data-price="'.($total_price-$discount_amount+$security_deposit['security_deposit_amount']).'">'.rbfw_mps_price($total_price-$discount_amount+$security_deposit['security_deposit_amount']).' '.$tax_status.'</span></li>
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



        public function rbfw_resort_admin_scripts($post_id){
            $rbfw_item_type  = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
            $rbfw_enable_resort_daylong_price  = get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) : 'no';
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
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="'+resort_type_new_data_key+'"><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][room_type]" value="" placeholder="<?php esc_html_e( "Room type", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( "Add Image", "rent-manager-for-woocommerce" ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_image]" value="" class="rbfw_room_image"></td><td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daylong_rate]" step=".01" value="" placeholder="<?php esc_html_e( "Day-long Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daynight_rate]" step=".01" value="" placeholder="<?php esc_html_e( "Day-night Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_desc]" value="" placeholder="<?php esc_html_e( "Short Description", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_available_qty]" value="" placeholder="<?php esc_html_e( "Available Qty", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
							let resort_type_add_new_row = jQuery('.rbfw_resort_price_table').append(resort_type_row);
						}
						else{
							let resort_type_new_data_key = 0;
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="'+resort_type_new_data_key+'"><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][room_type]" value="" placeholder="<?php esc_html_e( "Room type", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( "Add Image", "rent-manager-for-woocommerce" ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_image]" value="" class="rbfw_room_image"></td><td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daylong_rate]" step=".01" value="" placeholder="<?php esc_html_e( "Day-long Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( "Day-night Rate", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="text" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_desc]" value="" placeholder="<?php esc_html_e( "Short Description", "rent-manager-for-woocommerce" ); ?>"></td><td><input type="number" name="rbfw_resort_room_data['+resort_type_new_data_key+'][rbfw_room_available_qty]" value="" placeholder="<?php esc_html_e( "Available Qty", "rent-manager-for-woocommerce" ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
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

                        var daylong_price_label_val = jQuery('.rbfw_resort_daylong_price_switch label.active').find('input').val();

if (daylong_price_label_val == 'yes') {
    jQuery('.resort_day_long_price').show();
} else {
    jQuery('.resort_day_long_price').hide();
}
					});
				// end add-resort-type-btn action

            });
            </script>
            <?php
        }
    }
    new RBFW_Resort_Function();
}