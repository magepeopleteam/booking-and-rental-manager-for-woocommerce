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


                $checkin_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $checkin_date):$checkin_date;
                $checkout_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $checkout_date):$checkout_date;



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


            $start_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $_POST['checkin_date']):$_POST['checkin_date'];
            $end_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $_POST['checkout_date']):$_POST['checkout_date'];



            $origin             = date_create($start_date);
            $target             = date_create($end_date);
            $interval           = date_diff($origin, $target);
            $total_days         = $interval->format('%a');

            if($total_days){
                $price_type = 'daynight';
            }else{
                $price_type = 'daylong';
            }
            $this->rbfw_get_active_price_table($_POST['post_id'],$price_type,strip_tags($_POST['checkin_date']),strip_tags($_POST['checkout_date']));
        }

        public function rbfw_get_active_price_table($post_id=0,$active_tab='',$checkin_date='',$checkout_date=''){
            include( RBFW_Function::get_template_path( 'template_segment/resort_info.php' ) );
            wp_die();
        }

        public function rbfw_room_price_calculation(){
            if(isset($_POST['checkin_date']) && isset($_POST['checkout_date'])):
            global $rbfw;
            $content            = '';


            $checkin_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $_POST['checkin_date']):$_POST['checkin_date'];
            $checkout_date = (get_option('date_format')=='d/m/Y')?str_replace('/', '-', $_POST['checkout_date']):$_POST['checkout_date'];


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

                                            $discount_arr = rbfw_get_discount_array($post_id, $total_days, $total_price);

                                        } else {
                                            $discount_arr = [];
                                        }

                                        if(($discount_arr['discount_amount'])){
                                            $discount_amount = $discount_arr['discount_amount'];
                                            $discount_desc = $discount_arr['discount_desc'];
                                            $content .= '<li class="discount">';
                                            $content .= $rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'));
                                            $content .= '<span>'.wc_price($discount_arr['discount_amount']).'</span>';
                                            $content .= '</li>';
                                        }
                                    }


                                    /* End Discount Calculations */

                                    $content.='<li class="total"><strong>'.$rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span class="price-figure" data-price="'.($total_price-$discount_amount+$security_deposit['security_deposit_amount']).'">'.rbfw_mps_price($total_price - $discount_amount + $security_deposit['security_deposit_amount']).' '.$tax_status.'</span></li>
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