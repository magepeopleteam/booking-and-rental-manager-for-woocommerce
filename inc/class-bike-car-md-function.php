<?php
/*
* Author 	:	MagePeople Team
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( ! class_exists( 'RBFW_BikeCarMd_Function' ) ) {
    class RBFW_BikeCarMd_Function {
        public function __construct(){
            add_action('wp_footer', array($this, 'rbfw_bike_car_md_frontend_scripts'));
            add_action('wp_ajax_rbfw_bikecarmd_ajax_price_calculation', array($this, 'rbfw_md_duration_price_calculation_ajax'));
            add_action('wp_ajax_nopriv_rbfw_bikecarmd_ajax_price_calculation', array($this,'rbfw_md_duration_price_calculation_ajax'));

            add_action('wp_ajax_rbfw_total_day_calcilation', array($this, 'rbfw_total_day_calcilation'));
            add_action('wp_ajax_nopriv_rbfw_total_day_calcilation', array($this,'rbfw_total_day_calcilation'));
        }



        public function rbfw_get_bikecarmd_service_array_reorder($product_id, $service_info){

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

        function rbfw_total_day_calcilation(){

            $start_date = $_POST['pickup_date'];
            $end_date = $_POST['dropoff_date'];
            $star_time = (isset($_POST['pickup_time']) && $_POST['pickup_time'])?$_POST['pickup_time']:'00:00:00';
            $end_time = (isset($_POST['dropoff_time']) && $_POST['dropoff_time'])?$_POST['dropoff_time']:rbfw_end_time();

            $pickup_datetime = date('Y-m-d H:i', strtotime($start_date . ' ' . $star_time));
            $dropoff_datetime = date('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));


            $diff = date_diff(new DateTime($pickup_datetime), new DateTime($dropoff_datetime));
            $total_days = $diff->days;
            $total_hours = $diff->h;
            $countable_time = 'yes';
            if(!($total_days || $total_hours)){
                $total_days = 1;
            }

            echo json_encode( array(
                'total_days' => $total_days,
                'countable_time' => $countable_time,
            ));

            wp_die();
        }

        function rbfw_md_duration_price_calculation_ajax(){

            $post_id = $_POST['post_id'];

            $start_date = $_POST['pickup_date'];
            $end_date = $_POST['dropoff_date'];
            $star_time = isset($_POST['pickup_time'])?$_POST['pickup_time']:'00:00:00';
            $end_time = isset($_POST['dropoff_time'])?$_POST['dropoff_time']:rbfw_end_time();
            $pickup_datetime = date('Y-m-d H:i', strtotime($start_date . ' ' . $star_time));
            $dropoff_datetime = date('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));

            $item_quantity = $_POST['item_quantity'];
            $rbfw_enable_variations = $_POST['rbfw_enable_variations'];
            $rbfw_available_time = $_POST['rbfw_available_time']??'no';
            $rbfw_service_price = $_POST['rbfw_service_price'] * $item_quantity;


            $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date,'',$pickup_datetime,$dropoff_datetime);
            $duration_price_info = rbfw_md_duration_price_calculation($post_id,$pickup_datetime,$dropoff_datetime,$start_date,$end_date,$star_time,$end_time,$rbfw_available_time);
            $duration_price = $duration_price_info['duration_price']*$item_quantity;
            $total_days = $duration_price_info['total_days'];
            $actual_days = $duration_price_info['actual_days'];
            $hours = $duration_price_info['hours'];

            $service_cost = $_POST['rbfw_es_service_price'];

            $sub_total_price = (float)$duration_price + (float)$service_cost + (float)$rbfw_service_price;
            $security_deposit = rbfw_security_deposit($post_id,$sub_total_price);


            $discount_amount = 0;
            if (is_plugin_active('booking-and-rental-manager-discount-over-x-days/rent-discount-over-x-days.php')){
                if(function_exists('rbfw_get_discount_array')){
                    $discount_arr = rbfw_get_discount_array($post_id, $total_days, $sub_total_price);
                    $discount_amount = $discount_arr['discount_amount'];
                }
            }

            $duration = '';

            if ( $actual_days > 0 ) {
                $duration .= $actual_days > 1 ? $actual_days.' '.rbfw_string_return('rbfw_text_days',__('Days','booking-and-rental-manager-for-woocommerce')).' ' : $actual_days.' '.rbfw_string_return('rbfw_text_day',__('Day','booking-and-rental-manager-for-woocommerce')).' ';
            }
            if ( $hours > 0 ) {
                $duration .= $hours > 1 ? $hours.' '.rbfw_string_return('rbfw_text_hours',__('Hours','booking-and-rental-manager-for-woocommerce')) : $hours.' '.rbfw_string_return('rbfw_text_hour',__('Hour','booking-and-rental-manager-for-woocommerce'));
            }

            echo json_encode( array(
                'duration_price' => $duration_price,
                'duration_price_html' => wc_price($duration_price),
                'rbfw_service_price' => $rbfw_service_price,
                'rbfw_service_price_html' => wc_price($rbfw_service_price),
                'service_cost' => $service_cost+$rbfw_service_price,
                'service_cost_html' => wc_price($service_cost+$rbfw_service_price),
                'sub_total_price_html' => wc_price($sub_total_price),
                'discount' => $discount_amount,
                'discount_html' => wc_price((float)$discount_amount),
                'security_deposit_desc' => $security_deposit['security_deposit_desc'],
                'security_deposit_amount' => $security_deposit['security_deposit_amount'],
                'total_price' => (float)$sub_total_price + (float)$security_deposit['security_deposit_amount'] - (float)$discount_amount,
                'total_price_html' => wc_price((float)$sub_total_price + (float)$security_deposit['security_deposit_amount'] -  (float)$discount_amount),
                'max_available_qty' => $max_available_qty,
                'total_days' => $total_days,
                'total_duration' => $duration,
                'ticket_item_quantity' => $item_quantity,
                'rbfw_enable_variations' => $rbfw_enable_variations,
            ));

            wp_die();
        }


        public function rbfw_bike_car_md_frontend_scripts($rbfw_post_id){

            global $post;
            $post_id = !empty($post->ID) ? $post->ID : '';
            if(!empty($rbfw_post_id)){
                $post_id = $rbfw_post_id;
            }
            if(empty($post_id)){
                return;
            }
            $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
            if(($rent_type != 'bike_car_md') && ($rent_type != 'dress') && ($rent_type != 'equipment') && ($rent_type != 'others') && ( is_a( $post, 'WP_Post' ) && ! has_shortcode( $post->post_content, 'rent-add-to-cart') ) ):
                return;
            endif;
            $rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';

        }

        public function rbfw_bikecarmd_ticket_info($product_id, $rbfw_start_datetime = null, $rbfw_end_datetime = null, $pickup_point = null, $dropoff_point = null, $rbfw_service_info = array(), $duration_cost = null, $service_cost = null, $ticket_total_price = null, $item_quantity = null, $start_date = null,$end_date = null,$start_time = null,$end_time = null, $variation_info = array(), $discount_type = null, $discount_amount = null, $rbfw_regf_info = array()){
            global $rbfw;

            if(!empty($product_id)):

                $title = get_the_title($product_id);
                $main_array = array();
                $rbfw_rent_type 		= get_post_meta( $product_id, 'rbfw_item_type', true );
                $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
                if(! empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                else:
                    $extra_services = array();
                endif;

                $rbfw_enable_extra_service_qty = get_post_meta( $product_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $product_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

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
                    $percent = $percentInDecimal * $ticket_total_price;
                    $ticket_total_price = $ticket_total_price + $percent;
                }

                /* End Tax Calculations */

                $main_array[0]['ticket_name'] = $title;
                $main_array[0]['ticket_price'] = $ticket_total_price;
                $main_array[0]['ticket_qty'] = 1;
                $main_array[0]['rbfw_start_date'] = $start_date;
                $main_array[0]['rbfw_start_time'] = $start_time;
                $main_array[0]['rbfw_end_date'] = $end_date;
                $main_array[0]['rbfw_end_time'] = $end_time;
                $main_array[0]['rbfw_start_datetime'] = $rbfw_start_datetime;
                $main_array[0]['rbfw_end_datetime'] = $rbfw_end_datetime;
                $main_array[0]['rbfw_pickup_point'] = $pickup_point;
                $main_array[0]['rbfw_dropoff_point'] = $dropoff_point;
                $main_array[0]['rbfw_service_info'] = [];
                $main_array[0]['rbfw_item_quantity'] = $item_quantity;
                $main_array[0]['rbfw_rent_type'] = $rbfw_rent_type;
                $main_array[0]['rbfw_id'] = $product_id;
                $main_array[0]['rbfw_variation_info'] = [];

                if(!empty($rbfw_service_info)){
                    foreach ($rbfw_service_info as $key => $value):
                        $service_name = $key; //Service name
                        if(array_key_exists($service_name, $extra_services)){ // if Service name exist in array

                            if($item_quantity > 1 && $value == 1 && $rbfw_enable_extra_service_qty != 'yes'){
                                $value = $item_quantity;
                            }

                            $main_array[0]['rbfw_service_info'][$service_name] = $value; // name = quantity
                        }
                    endforeach;
                }

                if(!empty($variation_info)){
                    $c = 0;
                    foreach ($variation_info as $key => $value):

                        $main_array[0]['rbfw_variation_info'][$c]['field_id'] = $value['field_id'];
                        $main_array[0]['rbfw_variation_info'][$c]['field_label'] = $value['field_label'];
                        $main_array[0]['rbfw_variation_info'][$c]['field_value'] = $value['field_value'];
                        $c++;
                    endforeach;
                }

                $main_array[0]['rbfw_mps_tax'] = $percent;
                $main_array[0]['duration_cost'] = $duration_cost;
                $main_array[0]['service_cost'] = $service_cost;
                $main_array[0]['discount_type'] = $discount_type;
                $main_array[0]['discount_amount'] = $discount_amount;
                $main_array[0]['rbfw_regf_info'] = $rbfw_regf_info;

                return $main_array;

            else:
                return false;
            endif;
        }

        public function rbfw_get_bikecarmd_service_info($product_id, $service_info){
            $service_price = 0;
            $main_array = [];

            $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();

            if(! empty($rbfw_extra_service_data)):
                $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                $extra_service_qty = array_column($rbfw_extra_service_data,'service_qty','service_name');
            else:
                $extra_services = array();
            endif;

            if(!empty($service_info)){

                foreach ($service_info as $key => $value) {
                    $service_name = $key; //Type1
                    if($value > 0){
                        if(array_key_exists($service_name, $extra_services)){ // if Type1 exist in array
                            $service_price += (float)$extra_services[$service_name] * (float)$value;// addup price
                            $main_array[$service_name] = '('.rbfw_mps_price($extra_services[$service_name]) .' x '. (float)$value.') = '.rbfw_mps_price((float)$extra_services[$service_name] * (float)$value); // type = quantity
                        }
                    }
                }
            }


            return $main_array;
        }
    }
    new RBFW_BikeCarMd_Function();
}