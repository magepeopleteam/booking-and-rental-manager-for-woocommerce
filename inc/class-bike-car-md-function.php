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

            add_action('wp_ajax_rbfw_check_available', array($this,'rbfw_check_available'));
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



        function rbfw_md_duration_price_calculation_ajax(){

            if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                return;
            }
            global $rbfw;
            $post_id = isset($_POST['post_id'])? absint(sanitize_text_field(wp_unslash($_POST['post_id']))):'';

            $start_date = isset($_POST['pickup_date'])?sanitize_text_field(wp_unslash($_POST['pickup_date'])):'';
            $end_date = isset($_POST['dropoff_date'])?sanitize_text_field(wp_unslash($_POST['dropoff_date'])):'';
            $star_time = isset($_POST['pickup_time'])?sanitize_text_field(wp_unslash($_POST['pickup_time'])):'';
            $end_time = isset($_POST['dropoff_time'])?sanitize_text_field(wp_unslash($_POST['dropoff_time'])):'';

            $pickup_datetime = gmdate('Y-m-d H:i', strtotime($start_date . ' ' . $star_time));
            $dropoff_datetime = gmdate('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));

            $item_quantity = isset($_POST['item_quantity'])?absint($_POST['item_quantity']):'';
            $rbfw_enable_variations = isset($_POST['rbfw_enable_variations'])?sanitize_text_field(wp_unslash($_POST['rbfw_enable_variations'])):'';
            $rbfw_available_time = isset($_POST['rbfw_available_time'])?sanitize_text_field(wp_unslash($_POST['rbfw_available_time'])):'no';
            $rbfw_service_price = isset($_POST['rbfw_service_price'])?floatval(sanitize_text_field(wp_unslash($_POST['rbfw_service_price']))):'' * $item_quantity;


            $rbfw_enable_time_slot = isset($_POST['rbfw_enable_time_slot'])?sanitize_text_field(wp_unslash($_POST['rbfw_enable_time_slot'])):'off';


            $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date,'',$pickup_datetime,$dropoff_datetime,$rbfw_enable_time_slot);
            $duration_price_info = rbfw_md_duration_price_calculation($post_id,$pickup_datetime,$dropoff_datetime,$start_date,$end_date,$star_time,$end_time,$rbfw_enable_time_slot);

           // echo '<pre>';print_r($duration_price_info);echo '<pre>';exit;

            $duration_price = $duration_price_info['duration_price'] * $item_quantity;
            $total_days = $duration_price_info['total_days'];
            $actual_days = $duration_price_info['actual_days'];
            $hours = $duration_price_info['hours'];

            if($rbfw_enable_time_slot=='off'){
                $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                if($rbfw_count_extra_day_enable=='on'){
                    $actual_days = $actual_days + 1;
                }
                $hours = 0;
            }


            $service_cost = isset($_POST['rbfw_es_service_price'])?floatval(sanitize_text_field(wp_unslash($_POST['rbfw_es_service_price']))):'';

            $sub_total_price = (float)$duration_price + (float)$service_cost + (float)$rbfw_service_price;
            $security_deposit = rbfw_security_deposit($post_id,$sub_total_price);


            $discount_amount = 0;
            if (is_plugin_active('booking-and-rental-manager-discount-over-x-days/rent-discount-over-x-days.php')){
                if(function_exists('rbfw_get_discount_array')){
                    $discount_arr = rbfw_get_discount_array($post_id, $total_days, $sub_total_price,$item_quantity);
                    $discount_amount = isset($discount_arr['discount_amount'])?$discount_arr['discount_amount']:0;
                }
            }

            $duration = '';

            if ( $actual_days > 0 ) {
                $duration .= $actual_days > 1 ? $actual_days.' '.esc_html__($rbfw->get_option_trans('rbfw_text_days','rbfw_basic_translation_settings',__('Days','booking-and-rental-manager-for-woocommerce'))).' ' : $actual_days.' '.esc_html__($rbfw->get_option_trans('rbfw_text_day','rbfw_basic_translation_settings',__('Day','booking-and-rental-manager-for-woocommerce'))).' ';
            }
            if ( $hours > 0 ) {
                $duration .= $hours > 1 ? $hours.' '.rbfw_string_return('rbfw_text_hours',esc_html__('Hours','booking-and-rental-manager-for-woocommerce')) : $hours.' '.rbfw_string_return('rbfw_text_hour',esc_html__('Hour','booking-and-rental-manager-for-woocommerce'));
            }

            if($actual_days == 0 && $hours == 0){
                $actual_days = 1;
                $duration .= $actual_days > 1 ? $actual_days.' '.rbfw_string_return('rbfw_text_days',esc_html__('Days','booking-and-rental-manager-for-woocommerce')).' ' : $actual_days.' '.rbfw_string_return('rbfw_text_day',esc_html__('Day','booking-and-rental-manager-for-woocommerce')).' ';
            }

            echo wp_json_encode( array(
                'duration_price' => $duration_price,
                'duration_price_html' => wc_price($duration_price),
                'duration_price_number' => $duration_price,
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
                $percent = 0;

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


        function rbfw_check_available(){

            if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action')) {

                $selected_date = isset($_POST['selected_date'])?sanitize_text_field(wp_unslash($_POST['selected_date'])):'';
                $rbfw_post_id = isset($_POST['rbfw_post_id'])?sanitize_text_field(wp_unslash($_POST['rbfw_post_id'])):'';

                $args = array(
                    'post_type' => 'rbfw_item',
                    'ID' => $rbfw_post_id
                );
                $query = new WP_Query( $args );

                ob_start();
                ?>

                    <?php
                    if ( $query->have_posts() ) {
                        while ( $query->have_posts() ) {
                            $query->the_post();
                            global $post;
                            $post_id = $post->ID;

                            $rbfw_enable_variations = !empty(get_post_meta($post_id, 'rbfw_enable_variations', true)) ? get_post_meta($post_id, 'rbfw_enable_variations', true) : 'no';
                            $rbfw_variations_data = !empty(get_post_meta($post_id, 'rbfw_variations_data', true)) ? get_post_meta($post_id, 'rbfw_variations_data', true) : [];

                            $rbfw_item_stock_quantity = 0;

                            if($rbfw_enable_variations=='yes'){
                                foreach ($rbfw_variations_data as $_variations_data) {
                                    if(!empty($_variations_data['value'])){
                                        foreach ($_variations_data['value'] as $value) {
                                            if(empty($value['quantity']) || $value['quantity'] <= 0){
                                                ////
                                            } else{
                                                $rbfw_item_stock_quantity =  $value['quantity'] + $rbfw_item_stock_quantity;
                                            }
                                        }
                                    }
                                }
                            }else{
                                $rbfw_item_stock_quantity = !empty(get_post_meta($post_id, 'rbfw_item_stock_quantity', true)) ? get_post_meta($post_id, 'rbfw_item_stock_quantity', true) : 0;
                            }


                            if ( !empty($date) ){
                                $current_date = $date;
                            } else {
                                $current_date = date_i18n('d-m-Y');
                            }

                            $rbfw_inventory = !empty(get_post_meta($post_id, 'rbfw_inventory', true)) ? get_post_meta($post_id, 'rbfw_inventory', true) : [];

                            $inventory_based_on_return = rbfw_get_option('inventory_based_on_return','rbfw_basic_gen_settings');

                            $remaining_item_stock = $rbfw_item_stock_quantity;
                            $sold_item_qty = 0;

                            if(!empty($rbfw_inventory)){
                                foreach ($rbfw_inventory as $key => $inventory) {
                                    $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];

                                    $partial_stock = true;
                                    if($inventory['rbfw_order_status'] == 'partially-paid' && get_option('mepp_reduce_stock', 'full')=='deposit'){
                                        $partial_stock = false;
                                    }

                                    if ( in_array($selected_date, $booked_dates) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$inventory['rbfw_order_status'] == 'returned':'')) && $partial_stock ){
                                        $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;
                                        $sold_item_qty += $rbfw_item_quantity;
                                    }
                                }
                                $remaining_item_stock = $rbfw_item_stock_quantity - (int)$sold_item_qty;
                            }
                            echo $remaining_item_stock;

                        }
                    }

                    ?>

                <?php

                wp_die();
            }
        }
    }
    new RBFW_BikeCarMd_Function();
}