<?php
/*
* Author 	:	MagePeople Team
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( ! class_exists( 'RBFW_MPS_Function' ) ) {
    class RBFW_MPS_Function {
        public function __construct(){
            add_action('wp_ajax_rbfw_mps_user_login', array($this, 'rbfw_mps_user_login'));
            add_action('wp_ajax_nopriv_rbfw_mps_user_login', array($this,'rbfw_mps_user_login'));
            add_action('wp_ajax_rbfw_mps_user_signin_signup_form_submit', array($this, 'rbfw_mps_user_signin_signup_form_submit'));
            add_action('wp_ajax_nopriv_rbfw_mps_user_signin_signup_form_submit', array($this,'rbfw_mps_user_signin_signup_form_submit'));
            add_action('wp_ajax_rbfw_mps_place_order_form_submit', array($this, 'rbfw_mps_place_order_form_submit'));
            add_action('wp_ajax_nopriv_rbfw_mps_place_order_form_submit', array($this,'rbfw_mps_place_order_form_submit')); 
            add_action('wp_ajax_rbfw_mps_paypal_form_validation', array($this, 'rbfw_mps_paypal_form_validation'));
            add_action('wp_ajax_nopriv_rbfw_mps_paypal_form_validation', array($this,'rbfw_mps_paypal_form_validation')); 
        }
        
        public function rbfw_mps_paypal_form_validation(){

            $first_name = isset($_POST['first_name']) ? strip_tags($_POST['first_name']) : '';
            $last_name = isset($_POST['last_name']) ? strip_tags($_POST['last_name']) : '';
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';

            $errors = '';

            if(empty($first_name)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('First name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            if(empty($last_name)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Last name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;                

            if(empty($email)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is not valid!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            echo $errors;

            wp_die();
        }

        public function rbfw_mps_place_order_form_submit(){
            check_ajax_referer( 'rbfw_mps_place_order_form_submit', 'security' );
            global $rbfw;
            $rbfw_thankyou_class = new Rbfw_Thankyou_Page();

            $post_id = isset($_POST['post_id']) ? strip_tags($_POST['post_id']) : '';
            $request = isset($_POST['submit_request']) ? strip_tags($_POST['submit_request']) : '';
            $payment_method = isset($_POST['payment_method']) ? strip_tags($_POST['payment_method']) : '';
            $rent_type = isset($_POST['rent_type']) ? strip_tags($_POST['rent_type']) : '';

            $start_date = isset($_POST['start_date']) ? strip_tags($_POST['start_date']) : '';
            $start_time = isset($_POST['start_time']) ? strip_tags($_POST['start_time']) : '';
            $end_date   = isset($_POST['end_date']) ? strip_tags($_POST['end_date']) : '';
            $end_time   = isset($_POST['end_time']) ? strip_tags($_POST['end_time']) : '';

            $pickup_point   = isset($_POST['pickup_point']) ? strip_tags($_POST['pickup_point']) : '';
            $dropoff_point   = isset($_POST['dropoff_point']) ? strip_tags($_POST['dropoff_point']) : '';
            $item_quantity   = isset($_POST['item_quantity']) ? strip_tags($_POST['item_quantity']) : 1;

            $type_info  = !empty($_POST['type_info']) ? $_POST['type_info'] : [];
            $service_info = !empty($_POST['service_info']) ? $_POST['service_info'] : [];

            $variation_info = !empty($_POST['variation_info']) ? $_POST['variation_info'] : [];

            $first_name = isset($_POST['first_name']) ? strip_tags($_POST['first_name']) : '';
            $last_name = isset($_POST['last_name']) ? strip_tags($_POST['last_name']) : '';
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
            
            $package = !empty($_POST['package']) ? strip_tags($_POST['package']) : '';

            /* Start: Registration Form Variables */
            $rbfw_regf_info = !empty($_POST['rbfw_regf_info']) ? $_POST['rbfw_regf_info'] : [];
            $rbfw_regf_checkboxes[0] = !empty($_POST['rbfw_regf_checkboxes']) ? $_POST['rbfw_regf_checkboxes'] : [];
            $rbfw_regf_radio[0] = !empty($_POST['rbfw_regf_radio']) ? $_POST['rbfw_regf_radio'] : [];
            $rbfw_regf_info = array_merge($rbfw_regf_info, $rbfw_regf_checkboxes, $rbfw_regf_radio);
            $rbfw_regf_info = !empty($rbfw_regf_info) ? array_reduce($rbfw_regf_info, 'array_merge', array()) : [];

            if(class_exists('Rbfw_Reg_Form')){
                $ClassRegForm = new Rbfw_Reg_Form();
                $rbfw_regf_info = $ClassRegForm->rbfw_organize_regf_value_array_mps_func($post_id, $rbfw_regf_info);
            }
            /* End: Registration Form Variables */

            $checkout_account = $rbfw->get_option('rbfw_mps_checkout_account', 'rbfw_basic_payment_settings','on');
            
            $errors = '';

            if(empty($first_name)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('First name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            if(empty($last_name)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Last name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;                

            if(empty($email)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is required!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)):
            $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is not valid!','booking-and-rental-manager-for-woocommerce').'</p>';
            endif;

            if(empty($errors)){

                if(is_user_logged_in()){
                    $current_user = wp_get_current_user();
                    $current_user_email = $current_user->user_email;
                    if($current_user_email == $email){

                        $order = $this->rbfw_mps_create_order($post_id, $rent_type, $start_date, $start_time, $end_date, $end_time, $pickup_point, $dropoff_point, $type_info, $service_info, $payment_method, $first_name, $last_name, $email, $package, '', '', '', '', $item_quantity,$variation_info,$rbfw_regf_info);

                        $msg = '<p class="mps_alert_login_success"><i class="fa-solid fa-circle-check"></i> '.rbfw_string_return('rbfw_text_order_succesful_msg',__('Order successful, redirecting...','booking-and-rental-manager-for-woocommerce')).'</p>';
                        echo $msg;
                        
                        $rbfw_thankyou_class->rbfw_redirect_to_thankyou_page($order);

                        if (class_exists('Rbfw_Mps_Email')) {

                            $MpsEmailClass = new Rbfw_Mps_Email();

                            $MpsEmailClass->rbfw_mps_new_order_user_notification($order);
                            update_post_meta($order['order_id'], 'rbfw_order_email_status', 'sent');
                        }
                         

                    }else{
                        $msg = '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('You are logged-in. Please enter the registered email!','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;                            
                    }
                }else{

                    // If Account creation is enabled
                    if($checkout_account == 'on'){
                        $user_id = wp_create_user( $email, '' ,$email );

                        if ( is_wp_error( $user_id ) ) {
                            $msg = '';
                            foreach( $user_id->errors as $key => $val ){
                                foreach( $val as $k => $v ){
                                    $msg .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.$v.'</p>';
                                }
                            }
                            echo $msg;
                        }else{
                            wp_new_user_notification($user_id, 'both');
                            wp_set_current_user($user_id);
                            wp_set_auth_cookie($user_id);
                            update_user_meta( $user_id, 'first_name', $first_name );
                            update_user_meta( $user_id, 'last_name', $last_name );
    
                            $order = $this->rbfw_mps_create_order($post_id, $rent_type, $start_date, $start_time, $end_date, $end_time, $pickup_point, $dropoff_point, $type_info, $service_info, $payment_method, $first_name, $last_name, $email, $package, '', '', '', '', $item_quantity,$variation_info,$rbfw_regf_info);
    
                            $msg = '<p class="mps_alert_login_success"><i class="fa-solid fa-circle-check"></i> '.rbfw_string_return('rbfw_text_order_succesful_msg',__('Order successful, redirecting...','booking-and-rental-manager-for-woocommerce')).'</p>';
                            echo $msg;
                            
                            $rbfw_thankyou_class->rbfw_redirect_to_thankyou_page($order);

                            if (class_exists('Rbfw_Mps_Email')) {

                                $MpsEmailClass = new Rbfw_Mps_Email();
                            
                                $MpsEmailClass->rbfw_mps_new_order_user_notification($order);
                                update_post_meta($order['order_id'], 'rbfw_order_email_status', 'sent');
                            } 
                        }
                    }else{
                        // Else Create order without account
                        $order = $this->rbfw_mps_create_order($post_id, $rent_type, $start_date, $start_time, $end_date, $end_time, $pickup_point, $dropoff_point, $type_info, $service_info, $payment_method, $first_name, $last_name, $email, $package, '', '', '', '', $item_quantity,$variation_info,$rbfw_regf_info);
        
                        $msg = '<p class="mps_alert_login_success"><i class="fa-solid fa-circle-check"></i> '._return('rbfw_text_order_succesful_msg',__('Order successful, redirecting...','booking-and-rental-manager-for-woocommerce')).'</p>';
                        echo $msg;
                        
                        $rbfw_thankyou_class->rbfw_redirect_to_thankyou_page($order);

                        if (class_exists('Rbfw_Mps_Email')) {

                            $MpsEmailClass = new Rbfw_Mps_Email();
                        
                            $MpsEmailClass->rbfw_mps_new_order_user_notification($order);
                            update_post_meta($order['order_id'], 'rbfw_order_email_status', 'sent');
                        }                            
                        // End Create order without account
                    }
                }
            }
            else{
                echo $errors;
            }

            wp_die();
        }
        
        public function rbfw_mps_create_order($post_id, $rent_type, $start_date = null, $start_time = null, $end_date = null, $end_time = null, $pickup_point = null, $dropoff_point = null, $type_info = array(), $service_info = array(), $payment_method = null, $first_name = null, $last_name = null, $email = null, $package = null, $order_status = null, $payment_id = null, $payer_id = null, $reference = null, $item_quantity = null, $variation_info = array(), $rbfw_regf_info = array()){
            global $rbfw;
            $ticket_name = get_the_title($post_id);
            $rbfw_id = $post_id;
            $duration_cost = 0;
            $service_cost = 0;

            if(empty($order_status)){
                $order_status = 'processing';
            }

            $rbfw_pin = '';
            $ticket_qty = 1;
            $ticket_total_price = 0;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;

            $g_services = get_post_meta($rbfw_id,'rbfw_extra_service_data',true);
            $g_services = !empty($g_services) ? array_column($g_services, 'service_price', 'service_name') : [];

            $reduced_quantity = 0;
            $stock_quantity = !empty(get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true)) ? get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true) : 0;

            $ticket_info = [];  


            /* Start Discount Calculations */
            $discount_type = '';
            $discount_amount = '';
            /* End Discount Calculations */

            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                $rbfw_bikecarsd = new RBFW_BikeCarSd_Function();
                $g_rent_types = get_post_meta($rbfw_id,'rbfw_bike_car_sd_data',true);
                $g_rent_types = array_column($g_rent_types, 'price', 'rent_type');
                $type_info_merged_array = array_reduce($type_info, 'array_merge', array());
                $service_info_merged_array = array_reduce($service_info, 'array_merge', array());
                $ticket_info = $rbfw_bikecarsd->rbfw_bikecarsd_ticket_info($rbfw_id,$start_date,$end_date,$type_info_merged_array,$service_info_merged_array,$start_time,$rbfw_regf_info);
                $service_info_merged = $service_info_merged_array;

                if(!empty($type_info)){
                    foreach ($type_info as $type_arr) {
                        foreach ($type_arr as $type_name => $type_qty) {
                            foreach ($g_rent_types as $g_rent_type => $g_rent_type_price) {
                               if($type_name == $g_rent_type){
                                    $price = (float)$g_rent_type_price * (float)$type_qty;
                                    $duration_cost += $price;

                               }
                            }
                        }
                    }
                }

                if(!empty($service_info)){
                    foreach ($service_info as $service_arr) {
                        foreach ($service_arr as $service_name => $service_qty) {
                            foreach ($g_services as $g_service_name => $g_service_price) {
                                if($service_name == $g_service_name){
                                    $price = (float)$g_service_price * (float)$service_qty;
                                    $service_cost += $price;

                                }
                            }
                        }
                    }
                }

                $ticket_total_price = $duration_cost + $service_cost;

            }
            elseif(($rent_type == 'bike_car_md') || ($rent_type == 'dress') || ($rent_type == 'equipment') || ($rent_type == 'others')){
                $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                $start_time = !empty($start_time) ? $start_time : '00:00:00';
                $end_time = !empty($end_time) ? $end_time : '24:00:00';
                $start_datetime = $start_date.' '.$start_time;
                $end_datetime = $end_date.' '.$end_time;
                $duration_cost = rbfw_price_calculation( $rbfw_id, $start_datetime, $end_datetime, $start_date ) * (float)$item_quantity;

                if(!empty($service_info)){
                    foreach ($service_info as $service_arr) {
                        foreach ($service_arr as $service_name => $service_qty) {
                            foreach ($g_services as $g_service_name => $g_service_price) {
                                if($service_name == $g_service_name){
                                    $price = (float)$g_service_price * (float)$service_qty;
                                    $service_cost += $price;
                                }
                            }
                        }
                    }
                }

                $ticket_total_price = $duration_cost + $service_cost;

                if(function_exists('rbfw_get_discount_array')){

                    $discount_arr = rbfw_get_discount_array($rbfw_id, $start_date, $end_date, $ticket_total_price);
            
                } else {
            
                    $discount_arr = [];
                }                

                if(!empty($discount_arr)){
                    $ticket_total_price = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                }

                $service_info_merged = array_reduce($service_info, 'array_merge', array());
                
                $ticket_info = $BikeCarMdClass->rbfw_bikecarmd_ticket_info($rbfw_id, $start_datetime, $end_datetime, $pickup_point, $dropoff_point, $service_info_merged,$duration_cost,$service_cost,$ticket_total_price,$item_quantity,$start_date,$end_date,$start_time,$end_time,$variation_info, $discount_type, $discount_amount, $rbfw_regf_info);
                                
            }
            elseif($rent_type == 'resort'){
                $type_info_merged_array = array_reduce($type_info, 'array_merge', array());
                $service_info_merged_array = array_reduce($service_info, 'array_merge', array());
                $service_info_merged = $service_info_merged_array;
                $resortClass = new RBFW_Resort_Function();
                $duration_cost = $resortClass->rbfw_resort_price_calculation($rbfw_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_duration_price');
                $service_cost  = $resortClass->rbfw_resort_price_calculation($rbfw_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_service_price');
                $ticket_total_price = $resortClass->rbfw_resort_price_calculation($rbfw_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_total_price');
                $percent = $resortClass->rbfw_resort_price_calculation($rbfw_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_tax_price');
                $ticket_info = $resortClass->rbfw_resort_ticket_info($rbfw_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array, $rbfw_regf_info);
                


                if(function_exists('rbfw_get_discount_array')){

                    $discount_arr = rbfw_get_discount_array($rbfw_id, $start_date, $end_date, $ticket_total_price);
            
                } else {
            
                    $discount_arr = [];
                }                

                if(!empty($discount_arr)){
                    $ticket_total_price = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                }
            
            }

            /* Start Tax Calculations */
            $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
            $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
            $mps_tax_percentage = !empty(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) : '';
            $percent = 0;

            if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $rent_type != 'resort'){
                
                //Convert our percentage value into a decimal.
                $percentInDecimal = $mps_tax_percentage / 100;
                //Get the result.
                $percent = $percentInDecimal * $ticket_total_price;
                $ticket_total_price = $ticket_total_price + $percent;
            }

            /* End Tax Calculations */

            $args = array(
                'post_title'    => $first_name.' '.$last_name,
                'post_status'   => 'publish',
                'post_type'     => 'rbfw_order'
            );
            
            $post_id = wp_insert_post( $args );

            if(!empty($post_id)){

                $rbfw_pin = $user_id.$rbfw_id.$post_id;
                //Generate a random string.
                $token = openssl_random_pseudo_bytes(16);
                //Convert the binary data into hexadecimal representation.
                $token = bin2hex($token);
                $order = [];
                $order['order_id'] = $post_id;
                $order['token'] = $token;
                $order['status'] = $order_status;
                $order['payment_id'] = $payment_id;
                $order['payer_id'] = $payer_id;

                wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$post_id.' '.$first_name.' '.$last_name));

                if(!empty($user_id)){ wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name]); }

                update_post_meta($post_id, 'rbfw_billing_email', $email);
                update_post_meta($post_id, 'rbfw_billing_name', $first_name.' '.$last_name);
                update_post_meta($post_id, 'rbfw_duration_cost', $duration_cost);
                update_post_meta($post_id, 'rbfw_service_cost', $service_cost); 
                update_post_meta($post_id, 'rbfw_id', $rbfw_id);
                update_post_meta($post_id, 'rbfw_order_status', $order_status);
                update_post_meta($post_id, 'rbfw_payment_method', $payment_method);
                update_post_meta($post_id, 'rbfw_pin', $rbfw_pin);
                update_post_meta($post_id, 'rbfw_ticket_qty', $ticket_qty);
                update_post_meta($post_id, 'rbfw_ticket_total_price', $ticket_total_price);
                update_post_meta($post_id, 'rbfw_mps_tax', $percent);
                update_post_meta($post_id, 'rbfw_user_id', $user_id);
                update_post_meta($post_id, 'rbfw_token', $token);
                update_post_meta($post_id, 'rbfw_ticket_info', $ticket_info);
                update_post_meta($post_id, 'rbfw_payment_id', $payment_id);
                update_post_meta($post_id, 'rbfw_payer_id', $payer_id);
                update_post_meta($post_id, 'rbfw_reference', $reference);
                update_post_meta($post_id, 'discount_type', $discount_type);
                update_post_meta($post_id, 'discount_amount', $discount_amount);

                $order_meta_args = array(
                    'post_title'    => $first_name.' '.$last_name,
                    'post_status'   => 'publish',
                    'post_type'     => 'rbfw_order_meta'
                );
                
                $order_meta_id = wp_insert_post($order_meta_args);

                if(!empty($order_meta_id)){

                    wp_update_post(array('ID' => $order_meta_id, 'post_title' => '#'.$post_id.' '.$first_name.' '.$last_name));
                    update_post_meta($order_meta_id, 'rbfw_billing_email', $email);
                    update_post_meta($order_meta_id, 'rbfw_billing_name', $first_name.' '.$last_name);
                    update_post_meta($order_meta_id, 'rbfw_duration_cost', $duration_cost);
                    update_post_meta($order_meta_id, 'rbfw_service_cost', $service_cost);
                    update_post_meta($order_meta_id, 'rbfw_id', $rbfw_id);
                    update_post_meta($order_meta_id, 'rbfw_order_status', $order_status);
                    update_post_meta($order_meta_id, 'rbfw_payment_method', $payment_method);
                    update_post_meta($order_meta_id, 'rbfw_pin', $rbfw_pin);
                    update_post_meta($order_meta_id, 'rbfw_ticket_qty', $ticket_qty);
                    update_post_meta($order_meta_id, 'rbfw_ticket_total_price', $ticket_total_price);
                    update_post_meta($order_meta_id, 'rbfw_mps_tax', $percent);
                    update_post_meta($order_meta_id, 'rbfw_user_id', $user_id);
                    update_post_meta($order_meta_id, 'rbfw_token', $token);
                    update_post_meta($order_meta_id, 'rbfw_ticket_info', $ticket_info);
                    update_post_meta($order_meta_id, 'rbfw_payment_id', $payment_id);
                    update_post_meta($order_meta_id, 'rbfw_payer_id', $payer_id);
                    update_post_meta($order_meta_id, 'rbfw_reference', $reference);
                    update_post_meta($order_meta_id, 'rbfw_link_order_id', $post_id);
                    update_post_meta($order_meta_id, 'discount_type', $discount_type);
                    update_post_meta($order_meta_id, 'discount_amount', $discount_amount);

                    if(!empty($ticket_info)){
                        $i = 0;
                        foreach ($ticket_info[$i] as $key => $value) {
                            
                            if($key == 'rbfw_start_date' || $key == 'rbfw_end_date'){

                                $value = date('Y-m-d', strtotime($value));
                            }

                            if($key == 'rbfw_start_datetime' || $key == 'rbfw_end_datetime'){

                                //$value = date('Y-m-d H:i:s', strtotime($value));
                                $value = $value;
  
                            }

                            update_post_meta($order_meta_id, $key, $value);

                            /* Start: Create Inventory info */
                            rbfw_create_inventory_meta($ticket_info, $i);
                            /* End: Create Inventory info */

                            $i++;
                        }
                    }
                }

                return $order;
            }
        }

        public function rbfw_mps_user_signin_signup_form_submit(){
            check_ajax_referer( 'rbfw_mps_user_submit_request', 'rbfw_mps_user_submit_request_nonce' );
    
            $request = isset($_POST['rbfw_mps_user_submit_request']) ? strip_tags($_POST['rbfw_mps_user_submit_request']) : '';
            $email = isset($_POST['rbfw_mps_user_login_email']) ? filter_var($_POST['rbfw_mps_user_login_email'], FILTER_SANITIZE_EMAIL) : '';
            $password = isset($_POST['rbfw_mps_user_password']) ? strip_tags($_POST['rbfw_mps_user_password']) : '';

            if($request == 'signin'){
                
                $errors = '';

                if(empty($email)):
                $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;

                if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)):
                $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is not valid!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;

                if(empty($password)):
                $errors .= '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Password is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;

                if(empty($errors)){
                    $creds = array(
                        'user_login'    => $email,
                        'user_password' => $password,
                        'remember'      => true
                    );
                 
                    $user = wp_signon( $creds, false );
                 
                    if ( is_wp_error( $user ) ) {
                        $msg = '<p class="mps_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('The username or password is incorrect!','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;
                    }else{
                        wp_set_current_user($user->ID);
                        wp_set_auth_cookie($user->ID);
                        $msg = '<p class="mps_alert_login_success"><i class="fa-solid fa-circle-check"></i> '.__('Login successful, redirecting...','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;
                    }
                }
                else{
                    echo $errors;
                }

            }else{
                // Display Something is wrong
            }


            wp_die();
        }

        public function rbfw_mps_user_login(){
            global $rbfw;
     
            $post_id = strip_tags($_POST['post_id']);
            $checkout_account = $rbfw->get_option('rbfw_mps_checkout_account', 'rbfw_basic_payment_settings','on');
            $payment_gateway = $rbfw->get_option('rbfw_mps_payment_gateway', 'rbfw_basic_payment_settings','offline');
            $payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

            $content = '';
            $rent_type = strip_tags($_POST['rent_type']);


            /* Start Tax Calculations */
            $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
            $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
            $mps_tax_percentage = !empty(get_post_meta($post_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($post_id, 'rbfw_mps_tax_percentage', true)) : '';
            $percent = 0;
            $tax_status = '';
            /* End Tax Calculations */

            /* Start Discount Calculations */
            $discount_switch = !empty(get_post_meta($post_id, 'rbfw_enable_discount', true)) ? get_post_meta($post_id, 'rbfw_enable_discount', true) : 'no';
            $discount_over_days = !empty(get_post_meta($post_id, 'rbfw_discount_over_days', true)) ? get_post_meta($post_id, 'rbfw_discount_over_days', true) : '';
            $discount_type = !empty(get_post_meta($post_id, 'rbfw_discount_type', true)) ? get_post_meta($post_id, 'rbfw_discount_type', true) : '';
            $discount_number = !empty(get_post_meta($post_id, 'rbfw_discount_number', true)) ? get_post_meta($post_id, 'rbfw_discount_number', true) : '';
            /* End Discount Calculations */

            $rbfw_regf_info = !empty($_POST['rbfw_regf_info']) ? $_POST['rbfw_regf_info'] : [];
            $rbfw_regf_checkboxes[0] = !empty($_POST['rbfw_regf_checkboxes']) ? $_POST['rbfw_regf_checkboxes'] : [];
            $rbfw_regf_radio[0] = !empty($_POST['rbfw_regf_radio']) ? $_POST['rbfw_regf_radio'] : [];
            $rbfw_regf_info = array_merge($rbfw_regf_info, $rbfw_regf_checkboxes, $rbfw_regf_radio);
            $rbfw_regf_info = array_reduce($rbfw_regf_info, 'array_merge', array());

            if(!empty($rbfw_regf_info)){
                $errors = '';

                foreach ($rbfw_regf_info as $field_name => $field_value) {
                    if(class_exists('Rbfw_Reg_Form')){
                        $ClassRegForm = new Rbfw_Reg_Form();
                        $required = $ClassRegForm->rbfw_check_regf_field_required_by_name($post_id, $field_name);
                        $field_label = $ClassRegForm->rbfw_get_regf_field_label_by_name($post_id, $field_name);

                        if($required == 1 && $field_value == ''){
                            $errors .= '<p class="rbfw_alert_warning">'.$field_label.' is required.'.'</p>';
                        }
                    }
                }

                if(!empty($errors)){
                    $content .= '<div class="rbfw_regf_warning_wrap">';
                    $content .= $errors;
                    $content .= '</div>';
                    echo json_encode(['rbfw_regf_warning'=> $content]);
                    wp_die();
                }


            }

            $content .= '<div class="rbfw_mps_user_order_summary">';

            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'):
                $selected_date = isset($_POST['selected_date']) ? strip_tags($_POST['selected_date']) : '';
                $date_to_string = new DateTime($selected_date);
                $selected_date = $date_to_string->format('F j, Y');
                $selected_time = isset($_POST['selected_time']) ? strip_tags($_POST['selected_time']) : '';
                $type_info = !empty($_POST['type_info']) ? $_POST['type_info'] : [];
                $service_info = !empty($_POST['service_info']) ? $_POST['service_info'] : [];
                $g_rent_types = get_post_meta($post_id,'rbfw_bike_car_sd_data',true);
                $g_rent_types = array_column($g_rent_types, 'price', 'rent_type');

                $g_services = get_post_meta($post_id,'rbfw_extra_service_data',true);
                $g_services = !empty($g_services) ? array_column($g_services, 'service_price', 'service_name') : [];
                $total_amount = 0;



                $content .= '<div class="rbfw_mps_user_order_header">'.rbfw_string_return('rbfw_text_order_summary',__('Order Summary','booking-and-rental-manager-for-woocommerce')).'</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_start_date', 'rbfw_basic_translation_settings', __('Start Date','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($selected_date).'</div>';
                $content .= '</div>';

                if(!empty($selected_time)){
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_start_time', 'rbfw_basic_translation_settings', __('Start Time','booking-and-rental-manager-for-woocommerce')).'</div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($selected_time).'</div>';                
                    $content .= '</div>';
                }

                if(!empty($type_info)){
                    foreach ($type_info as $type_arr) {
                        foreach ($type_arr as $type_name => $type_qty) {
                            foreach ($g_rent_types as $g_rent_type => $g_rent_type_price) {
                               if($type_name == $g_rent_type){
                                    $price = (float)$g_rent_type_price * (float)$type_qty;
                                    $total_amount += $price;
                               }
                            }

                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($type_name).' x '.esc_html($type_qty).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($price).'</div>';                
                            $content .= '</div>';
                        }
                    }
                }

                if(!empty($service_info)){
                    foreach ($service_info as $service_arr) {
                        foreach ($service_arr as $service_name => $service_qty) {
                            foreach ($g_services as $g_service_name => $g_service_price) {
                               if($service_name == $g_service_name){
                                    $price = (float)$g_service_price * (float)$service_qty;
                                    $total_amount += $price;
                               }
                            }
                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($service_name).' x '.esc_html($service_qty).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($price).'</div>';                
                            $content .= '</div>';
                        }
                    }
                }

                /* Start Tax Calculations */
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_amount;
                    $total_amount = $total_amount + $percent;
                }
    
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){
                    $tax_status = '('.__('Includes','booking-and-rental-manager-for-woocommerce').' '.rbfw_mps_price($percent).' '.__('Tax','booking-and-rental-manager-for-woocommerce').')';
                }
                /* End Tax Calculations */

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($percent).'</div>';                
                    $content .= '</div>';
                }

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.rbfw_string_return('rbfw_text_total',__('Total','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($total_amount).' '.$tax_status.'</div>';                
                $content .= '</div>';

            endif;

            if($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'):

                $start_date = isset($_POST['start_date']) ? strip_tags(rbfw_date_format($_POST['start_date'])) : '';
                $start_time = isset($_POST['start_time']) ? strip_tags($_POST['start_time']) : '00:00:00';
                $end_date = isset($_POST['end_date']) ? strip_tags(rbfw_date_format($_POST['end_date'])) : '';
                $end_time = isset($_POST['end_time']) ? strip_tags($_POST['end_time']) : '24:00:00';
                $pickup_point = isset($_POST['pickup_point']) ? $_POST['pickup_point'] : '';
                $dropoff_point = isset($_POST['dropoff_point']) ? $_POST['dropoff_point'] : '';
                $item_quantity = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : 1;

                $service_info = !empty($_POST['service_info']) ? $_POST['service_info'] : [];
                $variation_info = !empty($_POST['variation_info']) ? $_POST['variation_info'] : [];
                $g_services = get_post_meta($post_id,'rbfw_extra_service_data',true);
                $g_services = !empty($g_services) ? array_column($g_services, 'service_price', 'service_name') : [];
                $total_amount = 0;
                $start_datetime = $start_date.' '.$start_time;
                $end_datetime = $end_date.' '.$end_time;
                $duration_cost = rbfw_price_calculation( $post_id, $start_datetime, $end_datetime, $start_date);
                $duration_cost = $duration_cost * (float)$item_quantity;
                $service_cost = 0;
                $total_amount += $duration_cost + $service_cost;



                $content .= '<div class="rbfw_mps_user_order_header">'.rbfw_string_return('rbfw_text_order_summary',__('Order Summary','rbfw-pro')).'</div>';

                if(!empty($pickup_point)):
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_pickup_point', 'rbfw_basic_translation_settings', __('Pick-up Point','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($pickup_point).'</div>';
                $content .= '</div>';
                endif;

                if(!empty($dropoff_point)):
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_dropoff_point', 'rbfw_basic_translation_settings', __('Drop-off Point','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($dropoff_point).'</div>';
                $content .= '</div>';
                endif;

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_start_date', 'rbfw_basic_translation_settings', __('Start Date','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($start_date).'</div>';
                $content .= '</div>';

                if(!empty($start_time)):
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_start_time', 'rbfw_basic_translation_settings', __('Start Time','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($start_time).'</div>';                
                $content .= '</div>';
                endif;

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_end_date', 'rbfw_basic_translation_settings', __('End Date','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($end_date).'</div>';
                $content .= '</div>';

                if(!empty($end_time)):
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_end_time', 'rbfw_basic_translation_settings', __('End Time','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($end_time).'</div>';                
                $content .= '</div>';
                endif;

                if(!empty($variation_info)):
                    foreach ($variation_info as $key => $value) {
         
                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($value['field_label']).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($value['field_value']).'</div>';                
                            $content .= '</div>';
                        
                    }
                endif;

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.esc_html($item_quantity).'</div>';                
                $content .= '</div>';

                if(!empty($service_info)){
                    foreach ($service_info as $service_arr) {
                        foreach ($service_arr as $service_name => $service_qty) {
                            foreach ($g_services as $g_service_name => $g_service_price) {
                               if($service_name == $g_service_name){
                                    $price = (float)$g_service_price * (float)$service_qty;
                                    $total_amount += $price;
                                    $service_cost += $price;
                               }
                            }
                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($service_name).' x '.esc_html($service_qty).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($price).'</div>';                
                            $content .= '</div>';
                        }
                    }
                }
            

                /* Start Tax Calculations */
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_amount;
                    $total_amount = $total_amount + $percent;
                }
    
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){
                    $tax_status = '('.__('Includes','booking-and-rental-manager-for-woocommerce').' '.rbfw_mps_price($percent).' '.__('Tax','booking-and-rental-manager-for-woocommerce').')';
                }
                /* End Tax Calculations */

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($duration_cost).'</div>';                
                $content .= '</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($service_cost).'</div>';                
                $content .= '</div>';

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($percent).'</div>';                
                    $content .= '</div>';
                }

                /* Start Discount Calculations */

                if(function_exists('rbfw_get_discount_array')){

                    $discount_arr = rbfw_get_discount_array($post_id, $start_datetime, $end_datetime, $total_amount);
            
                } else {
            
                    $discount_arr = [];
                }                

                if(!empty($discount_arr)){
                    $total_amount = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                    $discount_desc = $discount_arr['discount_desc'];
                
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.$discount_desc.'</div>';                
                    $content .= '</div>';                    
                }
                /* End Discount Calculations */
                
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.rbfw_string_return('rbfw_text_total',__('Total','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($total_amount).' '.$tax_status.'</div>';                
                $content .= '</div>';

            endif;

            if($rent_type == 'resort'):
                
                $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
                $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

                $package = isset($_POST['package']) ? strip_tags($_POST['package']) : '';

                if($package == 'daylong'){
                    $g_price = 'rbfw_room_daylong_rate';
                }
                if($package == 'daynight'){
                    $g_price = 'rbfw_room_daynight_rate';
                }

                $type_info = !empty($_POST['type_info']) ? $_POST['type_info'] : [];
                $type_info_merged_array = array_reduce($type_info, 'array_merge', array());
                $service_info = !empty($_POST['service_info']) ? $_POST['service_info'] : [];
                $service_info_merged_array = array_reduce($service_info, 'array_merge', array());
                $g_rent_types = get_post_meta($post_id,'rbfw_resort_room_data',true);
                $g_rent_types = array_column($g_rent_types, $g_price, 'room_type');

                $g_services = get_post_meta($post_id,'rbfw_extra_service_data',true);
                $g_services = !empty($g_services) ? array_column($g_services, 'service_price', 'service_name') : [];
                
                $resortClass = new RBFW_Resort_Function();
                $duration_cost = $resortClass->rbfw_resort_price_calculation($post_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_duration_price');
                $service_cost  = $resortClass->rbfw_resort_price_calculation($post_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_service_price');
                $total_cost    = $resortClass->rbfw_resort_price_calculation($post_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_room_total_price');
                $percent    = $resortClass->rbfw_resort_price_calculation($post_id,$start_date,$end_date,$package,$type_info_merged_array,$service_info_merged_array,'rbfw_tax_price');

                
                /* Start Tax Calculations */
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){
                    $tax_status = '('.__('Includes','booking-and-rental-manager-for-woocommerce').' '.rbfw_mps_price($percent).' '.__('Tax','booking-and-rental-manager-for-woocommerce').')';
                }
                /* End Tax Calculations */

                $content .= '<div class="rbfw_mps_user_order_header">'.rbfw_string_return('rbfw_text_order_summary',__('Order Summary','rbfw-pro')).'</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_date_format($start_date).'</div>';
                $content .= '</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_date_format($end_date).'</div>';                
                $content .= '</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head">'.$rbfw->get_option('rbfw_text_package', 'rbfw_basic_translation_settings', __('Package','booking-and-rental-manager-for-woocommerce')).'</div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.$package.'</div>';                
                $content .= '</div>';
                
                if(!empty($type_info)){
                    foreach ($type_info as $type_arr) {
                        foreach ($type_arr as $type_name => $type_qty) {
                            foreach ($g_rent_types as $g_rent_type => $g_rent_type_price) {
                               if($type_name == $g_rent_type){
                                    $price = (float)$g_rent_type_price * (float)$type_qty;
                               }
                            }
                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($type_name).' x '.esc_html($type_qty).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($price).'</div>';                
                            $content .= '</div>';
                        }
                    }
                }

                if(!empty($service_info)){
                    foreach ($service_info as $service_arr) {
                        foreach ($service_arr as $service_name => $service_qty) {
                            foreach ($g_services as $g_service_name => $g_service_price) {
                               if($service_name == $g_service_name){
                                    $price = (float)$g_service_price * (float)$service_qty;
                               }
                            }
                            $content .= '<div class="rbfw_mps_user_order_row">';
                            $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($service_name).' x '.esc_html($service_qty).'</div>';
                            $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($price).'</div>';                
                            $content .= '</div>';
                        }
                    }
                }
            
                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($duration_cost).'</div>';                
                $content .= '</div>';

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($service_cost).'</div>';                
                $content .= '</div>';

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($percent).'</div>';                
                    $content .= '</div>';
                }
                
                /* Start Discount Calculations */

                if(function_exists('rbfw_get_discount_array')){

                    $discount_arr = rbfw_get_discount_array($post_id, $start_date, $end_date, $total_cost);
            
                } else {
            
                    $discount_arr = [];
                }                 

                if(!empty($discount_arr)){
                    $total_cost = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                    $discount_desc = $discount_arr['discount_desc'];
                
                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head"><strong>'.$rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.$discount_desc.'</div>';                
                    $content .= '</div>';                    
                }
                /* End Discount Calculations */

                $content .= '<div class="rbfw_mps_user_order_row">';
                $content .= '<div class="rbfw_mps_user_order_head"><strong>'.rbfw_string_return('rbfw_text_total',__('Total','booking-and-rental-manager-for-woocommerce')).'</strong></div>';
                $content .= '<div class="rbfw_mps_user_order_data">'.rbfw_mps_price($total_cost).' '.$tax_status.'</div>';                
                $content .= '</div>';

            endif;
            /* End Resort Condition Here */

            $content .= '</div>';

            if(!empty($rbfw_regf_info)){

                $content .= '<div class="rbfw_mps_user_order_summary">';
                $content .= '<div class="rbfw_mps_user_order_header">'.rbfw_string_return('rbfw_text_customer_information',__('Customer Information','booking-and-rental-manager-for-woocommerce')).'</div>';

                foreach ($rbfw_regf_info as $field_name => $field_value) {

                    if(is_array($field_value) && !empty($field_value)){

                        $new_value = '';
                        $i = 1;
                        $count_value = count($field_value);

                        foreach ($field_value as $val) {

                            if($i < $count_value){
                                $new_value .= $val.', ';
                            } else {
                                $new_value .= $val;
                            }
                            $i++;
                        }
                        $field_value = $new_value;
                    }

                    /* Start: Get Registration Field Label By Name */

                    if(class_exists('Rbfw_Reg_Form')){
                        $ClassRegForm = new Rbfw_Reg_Form();
                        $field_label = $ClassRegForm->rbfw_get_regf_field_label_by_name($post_id, $field_name);
                    }
                    /* End: Get Registration Field Label By Name */

                    $content .= '<div class="rbfw_mps_user_order_row">';
                    $content .= '<div class="rbfw_mps_user_order_head">'.esc_html($field_label).'</div>';
                    $content .= '<div class="rbfw_mps_user_order_data">'.$field_value.'</div>';
                    $content .= '</div>';
                }

                $content .= '</div>';
            }


            if(is_user_logged_in()){
                $current_user = wp_get_current_user();

                $selected_date = isset($_POST['selected_date']) ? strip_tags($_POST['selected_date']) : '';
                $selected_time = isset($_POST['selected_time']) ? strip_tags($_POST['selected_time']) : '';
                $type_info = !empty($_POST['type_info']) ? $_POST['type_info'] : [];
                $service_info = !empty($_POST['service_info']) ? $_POST['service_info'] : [];

                $content .= '<div class="rbfw_mps_user_form_wrap">';

                        $content .= '<div class="rbfw_mps_checkout_form_wrap" data-id="checkout">';
    
                        $content .= '<div class="rbfw_mps_form_header">';
                        $content .= rbfw_string_return('rbfw_text_checkout',__('Checkout','booking-and-rental-manager-for-woocommerce'));
                        $content .= '</div>';
    
                        $content .= '<div class="rbfw_mps_input_group">';
                        $content .= '<label for="rbfw_mps_user_fname">'.rbfw_string_return('rbfw_text_first_name',__('First Name','booking-and-rental-manager-for-woocommerce')).'</label>';
                        $content .= '<input type="text" name="rbfw_mps_user_fname" id="rbfw_mps_user_fname" class="rbfw_mps_user_input" value="'.esc_html( $current_user->user_firstname ).'"/>';
                        $content .= '</div>';
    
                        $content .= '<div class="rbfw_mps_input_group">';
                        $content .= '<label for="rbfw_mps_user_lname">'.rbfw_string_return('rbfw_text_last_name',__('Last Name','booking-and-rental-manager-for-woocommerce')).'</label>';
                        $content .= '<input type="text" name="rbfw_mps_user_lname" id="rbfw_mps_user_lname" class="rbfw_mps_user_input" value="'.esc_html( $current_user->user_lastname ).'"/>';
                        $content .= '</div>';
                        
                        $content .= '<div class="rbfw_mps_input_group">';
                        $content .= '<label for="rbfw_mps_user_email">'.rbfw_string_return('rbfw_text_email_address',__('Email Address','booking-and-rental-manager-for-woocommerce')).'</label>';
                        $content .= '<input type="email" name="rbfw_mps_user_email" id="rbfw_mps_user_email" class="rbfw_mps_user_input" value="'.esc_html( $current_user->user_email ).'" readonly/>';
                        $content .= '</div>';

                        $content .= '<div class="rbfw_mps_input_group">';
                        $content .= '<label for="rbfw_mps_user_payment_method">'.rbfw_string_return('rbfw_text_pay_with',__('Pay With','booking-and-rental-manager-for-woocommerce')).'</label>';
                        $content .= '<div class="rbfw_mps_radio_group">';

                        if (array_key_exists('offline',$payment_gateway)){
                            $content .= '<label for="rbfw_mps_user_payment_method_offline"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_offline" class="rbfw_mps_user_payment_method" value="offline"/>'.rbfw_string_return('rbfw_text_offline_payment',__('Offline Payment','booking-and-rental-manager-for-woocommerce')).'</label>';
                        }
                        
                        if (array_key_exists('paypal',$payment_gateway) && rbfw_check_pro_active() == true){
                            $content .= '<label for="rbfw_mps_user_payment_method_paypal"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_paypal" class="rbfw_mps_user_payment_method" value="paypal"/><span class="rbfw_mps_user_payment_method_title">'.rbfw_string_return('rbfw_text_paypal',__('Paypal','booking-and-rental-manager-for-woocommerce')).'</span> <img src="'. RBMW_PRO_PLUGIN_URL .'images/paypal_badge5.png"/></label>';
                        }
                        
                        if (array_key_exists('stripe',$payment_gateway) && rbfw_check_pro_active() == true){
                            $content .= '<label for="rbfw_mps_user_payment_method_stripe"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_stripe" class="rbfw_mps_user_payment_method" value="stripe"/><span class="rbfw_mps_user_payment_method_title">'.rbfw_string_return('rbfw_text_stripe',__('Stripe','booking-and-rental-manager-for-woocommerce')).'</span> <img src="'. RBMW_PRO_PLUGIN_URL .'images/stripe_badge6.png"/></label>';
                        }
                        
                        $content .= '</div>';
                        $content .= '</div>';                        
    
                        $content .= '<div class="rbfw_mps_button_group">';
                        $content .= '<button id="rbfw_mps_pay_now_button" class="rbfw_mps_pay_now_button"  disabled>'.rbfw_string_return('rbfw_text_place_order',__('Place Order','booking-and-rental-manager-for-woocommerce')).' <i class="fas fa-spin"></i></button>';
                        $content .= '</div>';
    
                        $content .= '<div class="rbfw_mps_user_form_result"></div>';
                        ob_start();
                        wp_nonce_field( 'rbfw_mps_place_order_form_submit', 'rbfw_mps_order_place_nonce' );
                        $content .= ob_get_clean();
                        $content .= '<input type="hidden" name="rbfw_mps_payment_method" value=""/>';
                        $content .= '<input type="hidden" name="rbfw_mps_user_submit_request" value="checkout"/>';

                        if($payment_system == 'mps'){
                            $content .= '<input type="hidden" name="rbfw_mps_checkout" value=""/>';
                            $content .= '<input type="hidden" name="rbfw_mps_post_id" value="'.$post_id.'"/>';
                        }
                        
                        $content .= '</div>';
                    
                $content .= '</div>';

                $content .= '<div class="rbfw_mps_payment_form_wrap"></div>';
                $content .= '<div class="rbfw_mps_payment_form_notice"></div>';

            }else{
                $content .= '<div class="rbfw_mps_user_form_wrap">';

                    /* Sign In Form Wrap */
                    $content .= '<div class="rbfw_mps_signin_form_wrap rbfw_mps_form_wrap" data-id="login">';
                    $content .= '<form class="rbfw_mps_signin_form" method="POST">';

                    $content .= '<div class="rbfw_mps_form_header">';
                    $content .= rbfw_string_return('rbfw_text_sign_in',__('Sign In','booking-and-rental-manager-for-woocommerce'));
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_login_email">'.rbfw_string_return('rbfw_text_email_address',__('Email Address','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<input type="email" name="rbfw_mps_user_login_email" id="rbfw_mps_user_login_email" class="rbfw_mps_user_input"/>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_password">'.rbfw_string_return('rbfw_text_password',__('Password','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<input type="password" name="rbfw_mps_user_password" id="rbfw_mps_user_password" class="rbfw_mps_user_input"/>';
                    $content .= '</div>'; 

                    $content .= '<a class="rbfw_mps_forgot_password_link" href="'.esc_url(wp_lostpassword_url()).'" target="_blank">'.rbfw_string_return('rbfw_text_forget_password',__('Forgot password?','booking-and-rental-manager-for-woocommerce')).'</a>';

                    $content .= '<div class="rbfw_mps_button_group">';
                    $content .= '<button type="submit" id="rbfw_mps_user_signin_button" class="rbfw_mps_user_button">'.rbfw_string_return('rbfw_text_log_in',__('Log In','booking-and-rental-manager-for-woocommerce')).' <i class="fas fa-spin"></i></button>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_user_form_result"></div>';

                    ob_start();
                    wp_nonce_field( 'rbfw_mps_user_submit_request', 'rbfw_mps_user_submit_request_nonce' );
                    $content .= ob_get_clean();

                    $content .= '<input type="hidden" name="action" value="rbfw_mps_user_signin_signup_form_submit"/>';
                    $content .= '<input type="hidden" name="rbfw_mps_user_submit_request" value="signin"/>';
                    $content .= '</form>';
                    $content .= '</div>';
                    /* End Sign In Form Wrap */

                    /* Checkout Form Wrap */
                    
                    $content .= '<div class="rbfw_mps_checkout_form_wrap" data-id="checkout">';
                    
                    $content .= '<div class="rbfw_mps_form_header">';
                    $content .= '<div class="rbfw_mps_form_header_top">'.rbfw_string_return('rbfw_text_registration_information',__('Registration Information','booking-and-rental-manager-for-woocommerce')).'</div>';
                    $content .= '<div class="rbfw_mps_form_header_bottom">';
                    $content .= rbfw_string_return('rbfw_text_already_have_account_with_us',__('Do you already have an account with us?','booking-and-rental-manager-for-woocommerce'));
                    $content .= '<a class="rbfw_mps_header_action_link" data-id="login">'.rbfw_string_return('rbfw_text_sign_in',__('Sign-In','booking-and-rental-manager-for-woocommerce')).'</a>';
                    if($checkout_account != 'on'):
                    $content .= esc_html__('Or','booking-and-rental-manager-for-woocommerce');
                    $content .= '<a class="rbfw_mps_header_action_link" data-id="signup">'.rbfw_string_return('rbfw_text_sign_up',__('Sign-Up','booking-and-rental-manager-for-woocommerce')).'</a>';
                    endif;

                    $content .= '</div>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_fname">'.rbfw_string_return('rbfw_text_first_name',__('First Name','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<input type="text" name="rbfw_mps_user_fname" id="rbfw_mps_user_fname" class="rbfw_mps_user_input"/>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_lname">'.rbfw_string_return('rbfw_text_last_name',__('Last Name','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<input type="text" name="rbfw_mps_user_lname" id="rbfw_mps_user_lname" class="rbfw_mps_user_input"/>';
                    $content .= '</div>';
                    
                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_email">'.rbfw_string_return('rbfw_text_email_address',__('Email Address','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<input type="email" name="rbfw_mps_user_email" id="rbfw_mps_user_email" class="rbfw_mps_user_input"/>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_input_group">';
                    $content .= '<label for="rbfw_mps_user_payment_method">'.rbfw_string_return('rbfw_text_pay_with',__('Pay With','booking-and-rental-manager-for-woocommerce')).'</label>';
                    $content .= '<div class="rbfw_mps_radio_group">';

                    if (array_key_exists('offline',$payment_gateway)){
                        $content .= '<label for="rbfw_mps_user_payment_method_offline"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_offline" class="rbfw_mps_user_payment_method" value="offline"/>'.rbfw_string_return('rbfw_text_offline_payment',__('Offline Payment','booking-and-rental-manager-for-woocommerce')).'</label>';
                    }
                    
                    if (array_key_exists('paypal',$payment_gateway) && rbfw_check_pro_active() == true){
                        $content .= '<label for="rbfw_mps_user_payment_method_paypal"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_paypal" class="rbfw_mps_user_payment_method" value="paypal"/><span class="rbfw_mps_user_payment_method_title">'.rbfw_string_return('rbfw_text_paypal',__('Paypal','booking-and-rental-manager-for-woocommerce')).'</span> <img src="'. RBMW_PRO_PLUGIN_URL .'images/paypal_badge5.png"/></label>';
                    }
                    
                    if (array_key_exists('stripe',$payment_gateway) && rbfw_check_pro_active() == true){
                        $content .= '<label for="rbfw_mps_user_payment_method_stripe"><input type="radio" name="rbfw_mps_user_payment_method" id="rbfw_mps_user_payment_method_stripe" class="rbfw_mps_user_payment_method" value="stripe"/><span class="rbfw_mps_user_payment_method_title">'.rbfw_string_return('rbfw_text_stripe',__('Stripe','booking-and-rental-manager-for-woocommerce')).'</span> <img src="'. RBMW_PRO_PLUGIN_URL .'images/stripe_badge6.png"/></label>';
                    }

                    $content .= '</div>';
                    $content .= '</div>';                        

                    $content .= '<div class="rbfw_mps_button_group">';
                    $content .= '<button id="rbfw_mps_pay_now_button" class="rbfw_mps_pay_now_button" data-payment="" disabled>'.rbfw_string_return('rbfw_text_place_order',__('Place Order','booking-and-rental-manager-for-woocommerce')).' <i class="fas fa-spin"></i></button>';
                    $content .= '</div>';

                    $content .= '<div class="rbfw_mps_user_form_result"></div>';
                    ob_start();
                    wp_nonce_field( 'rbfw_mps_place_order_form_submit', 'rbfw_mps_order_place_nonce' );
                    $content .= ob_get_clean();
                    $content .= '<input type="hidden" name="rbfw_mps_payment_method" value=""/>';
                    $content .= '<input type="hidden" name="rbfw_mps_user_submit_request" value="checkout"/>';

                    if($payment_system == 'mps'){
                        $content .= '<input type="hidden" name="rbfw_mps_checkout" value=""/>';
                        $content .= '<input type="hidden" name="rbfw_mps_post_id" value="'.$post_id.'"/>';
                    }

                    $content .= '</div>';
                    
                    /* End Checkout Form Wrap */

                $content .= '</div>';

                $content .= '<div class="rbfw_mps_payment_form_wrap"></div>';
                $content .= '<div class="rbfw_mps_payment_form_notice"></div>';
            }
            echo json_encode(['rbfw_content'=> $content]);
            wp_die();
        }
        
    }
    new RBFW_MPS_Function();
}