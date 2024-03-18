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
        public function __construct(){
            add_action('wp_footer', array($this, 'rbfw_bike_car_sd_frontend_scripts'));
            add_action('wp_ajax_rbfw_bikecarsd_time_table', array($this, 'rbfw_bikecarsd_time_table'));
            add_action('wp_ajax_nopriv_rbfw_bikecarsd_time_table', array($this,'rbfw_bikecarsd_time_table')); 
            add_action('wp_ajax_rbfw_bikecarsd_type_list', array($this, 'rbfw_bikecarsd_type_list'));
            add_action('wp_ajax_nopriv_rbfw_bikecarsd_type_list', array($this,'rbfw_bikecarsd_type_list'));
            add_action('wp_ajax_rbfw_bikecarsd_ajax_price_calculation', array($this, 'rbfw_bikecarsd_ajax_price_calculation'));
            add_action('wp_ajax_nopriv_rbfw_bikecarsd_ajax_price_calculation', array($this,'rbfw_bikecarsd_ajax_price_calculation'));               
        }

        public function rbfw_get_bikecarsd_rent_array_reorder($product_id, $rent_info){
            
            $main_array = [];

            if(!empty($rent_info)){
                $rent_info = array_column($rent_info,'qty','rent_type');
                $i = 0;
                foreach ($rent_info as $key => $value):
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

        public function rbfw_get_bikecarsd_service_array_reorder($product_id, $service_info){
            
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

        public function rbfw_get_bikecarsd_rent_info($product_id, $rent_info){
            $rent_price         = 0;
            $main_array = [];
            $rbfw_rent_data = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();

            if(!empty($rbfw_rent_data)):
                $rent_types = array_column($rbfw_rent_data,'price','rent_type'); 
            else:
                $rent_types = array();
            endif;

            if(!empty($rent_info)){

                    foreach ($rent_info as $key => $value) {
                        $rent_type = $key; //Type1
                        if($value > 0){
                            if(array_key_exists($rent_type, $rent_types)){ // if Type1 exist in array
                                $rent_price += (float)$rent_types[$rent_type] * (float)$value; // addup price
                                $main_array[$rent_type] = '('.rbfw_mps_price($rent_types[$rent_type]) .' x '. (float)$value.') = '.rbfw_mps_price((float)$rent_types[$rent_type] * (float)$value); // type = quantity
                            }
                        }

                    }

            }

            return $main_array;
        }

        public function rbfw_get_bikecarsd_service_info($product_id, $service_info){
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

        public function rbfw_bikecarsd_ticket_info($product_id, $rbfw_start_datetime = null, $rbfw_end_datetime = null, $rbfw_type_info = array(), $rbfw_service_info = array(), $selected_time = null, $rbfw_regf_info = array()){
            global $rbfw;
            if( !empty($product_id) && !empty($rbfw_type_info) ):
                $rent_price         = 0;
                $service_price      = 0;
                $total_rent_price   = 0;
                $total_service_price = 0;
                $subtotal_price     = 0;
                $total_price        = 0;
                $title = get_the_title($product_id);
                $main_array = array();
                $rbfw_rent_type 		= get_post_meta( $product_id, 'rbfw_item_type', true );
                $rbfw_rent_data = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();
                $rbfw_end_datetime = rbfw_get_datetime($rbfw_end_datetime, 'date-text');
                
                if(!empty($rbfw_rent_data)):
                    $rent_types = array_column($rbfw_rent_data,'price','rent_type'); 
                else:
                    $rent_types = array();
                endif;

                $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : array();

                if(! empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                    $extra_service_qty = array_column($rbfw_extra_service_data,'service_qty','service_name');
                else:
                    $extra_services = array();
                endif;
               
                foreach ($rbfw_type_info as $key => $value):
                    $rent_type = $key; //Type1
                    if(array_key_exists($rent_type, $rent_types)){ // if Type1 exist in array
                        $rent_price += (float)$rent_types[$rent_type] * (float)$value; // addup price
                    }
             
                endforeach;

                
                if($rent_price > 0):
                    $total_rent_price = (float)$rent_price;
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
                
                if($total_rent_price > 0 || $total_service_price > 0):
                    $subtotal_price = (float)$total_rent_price + (float)$total_service_price;
                endif;
    
                if($subtotal_price > 0):
                    $total_price = (float)$subtotal_price;
                endif;

                /* Start Tax Calculations */
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_percentage = !empty(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) : '';
                $percent = 0;
               
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_price;
                    $total_price = $total_price + $percent;
                }

                /* End Tax Calculations */

                $main_array[0]['ticket_name'] = $title;
                $main_array[0]['ticket_price'] = $total_price;
                $main_array[0]['ticket_qty'] = 1;
                $main_array[0]['rbfw_start_date'] = $rbfw_start_datetime;
                $main_array[0]['rbfw_start_time'] = $selected_time;
                $main_array[0]['rbfw_end_date'] = $rbfw_end_datetime;
                $main_array[0]['rbfw_end_time'] = '';
                $main_array[0]['rbfw_start_datetime'] = $rbfw_start_datetime.' '.$selected_time;
                $main_array[0]['rbfw_end_datetime'] = $rbfw_end_datetime;
                $main_array[0]['rbfw_type_info'] = [];
                $main_array[0]['rbfw_service_info'] = [];
                $main_array[0]['rbfw_rent_type'] = $rbfw_rent_type;
                $main_array[0]['rbfw_id'] = $product_id;
                if(!empty($rbfw_type_info)){
                    foreach ($rbfw_type_info as $key => $value):
                        $rent_type = $key; //Type
                        if($value > 0){
                            if(array_key_exists($rent_type, $rent_types)){ // if Type exist in array
                                $main_array[0]['rbfw_type_info'][$rent_type] = $value; // type = quantity
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
                $main_array[0]['duration_cost'] = $total_rent_price;
                $main_array[0]['service_cost'] = $total_service_price;
                $main_array[0]['rbfw_regf_info'] = $rbfw_regf_info;

                return $main_array;

            else:
                return false;
            endif; 
        }

        public function rbfw_bikecarsd_price_calculation($product_id, $rbfw_bikecarsd_info, $rbfw_service_info = null, $rbfw_request = null){
            global $rbfw;
            if( !empty($product_id) && !empty($rbfw_bikecarsd_info) ):
                $rent_price         = 0;
                $service_price      = 0;
                $total_rent_price   = 0;
                $total_service_price = 0;
                $subtotal_price     = 0;
                $total_price        = 0;

                $rbfw_rent_data = get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $product_id, 'rbfw_bike_car_sd_data', true ) : array();

                if(!empty($rbfw_rent_data)):
                    $rent_types = array_column($rbfw_rent_data,'price','rent_type'); 
                else:
                    $rent_types = array();
                endif;

                $rbfw_extra_service_data = get_post_meta( $product_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $product_id, 'rbfw_extra_service_data', true ) : '';
                if(! empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
                else:
                    $extra_services = array();
                endif;
               
                foreach ($rbfw_bikecarsd_info as $key => $value):
                    $rent_type = $key; //Type1
                    if(array_key_exists($rent_type, $rent_types)){ // if Type1 exist in array
                        $rent_price += (float)$rent_types[$rent_type] * (float)$value; // addup price
                    }
             
                endforeach;

                
                if($rent_price > 0):
                    $total_rent_price = (float)$rent_price;
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
                
                if($total_rent_price > 0 || $total_service_price > 0):
                    $subtotal_price = (float)$total_rent_price + (float)$total_service_price;
                endif;
    
                if($subtotal_price > 0):
                    $total_price = (float)$subtotal_price;
                endif;
                
                /* Start Tax Calculations */
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
                $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_percentage = !empty(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($product_id, 'rbfw_mps_tax_percentage', true)) : '';
                $percent = 0;
      

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_price;
                    $total_price = $total_price + $percent;
                }

                /* End Tax Calculations */

                if($rbfw_request == 'rbfw_bikecarsd_total_price'):
                    return $total_price;
                elseif($rbfw_request == 'rbfw_bikecarsd_duration_price'):
                    return $total_rent_price;
                elseif($rbfw_request == 'rbfw_bikecarsd_service_price'):
                    return $total_service_price;
                else:
                    return $total_price;
                endif;

            else:
                return false;
            endif;            
        }

        public function rbfw_get_time_slot_by_label($ts_label){
            $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
            $ts_time = '';

            if(!empty($rbfw_time_slots)){
                foreach ($rbfw_time_slots as $key => $value) {
                    if ($key == $ts_label) {
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
        public function rbfw_get_time_booking_status($post_id, $selected_date, $time){

            if(empty($post_id) || empty($selected_date) || empty($time)){
                return false;
            }

            $rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );

            if($rbfw_rent_type != 'appointment'){
                return false;
            }

            $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);

            // Start: Get Date Range
            $date_range = [];
            $selected_date = strtotime($selected_date);


            for ($currentDate = $selected_date; $currentDate <= $selected_date; 

                $currentDate += (86400)) {
                                                
                $date = date('d-m-Y', $currentDate);

                $date_range[] = $date;

            }
            // End: Get Date Range

            $total_qty = 0;
            $appointment_max_qty_per_session = get_post_meta($post_id, 'rbfw_sd_appointment_max_qty_per_session', true);

            if(!empty($rbfw_inventory)){

                foreach ($date_range as $key => $range_date) {

                    foreach ($rbfw_inventory as $key => $inventory) {

                        $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
                        $rbfw_start_time = !empty($inventory['rbfw_start_time']) ? $inventory['rbfw_start_time'] : '';
                        $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];

                        if ( in_array($range_date, $booked_dates) && ($time == $rbfw_start_time) ) {

                            foreach ($rbfw_type_info as $type_name => $type_qty) {
						
                                $total_qty += $type_qty; 
                            }
                        }
                    }
                }
            }

            $remaining_stock = $appointment_max_qty_per_session - $total_qty;
            $remaining_stock = max(0, $remaining_stock);

            if($remaining_stock > 0){

                return false;

            } else{

                return true;
            }

            return false;
            
        }

        public function rbfw_bikecarsd_time_table(){
            if(isset($_POST['post_id'])){
                $id = $_POST['post_id'];
                $selected_date = $_POST['selected_date'];
                $is_muffin_template = $_POST['is_muffin_template'];
                $available_times = rbfw_get_available_times($id);

                $default_timezone = wp_timezone_string();
                $date = new DateTime("now", new DateTimeZone($default_timezone) );
                $nowTime  = $date->format('H:i');
                $nowDate  = $date->format('Y-m-d');

                $date_to_string = new DateTime($selected_date);
                $result = $date_to_string->format('F j, Y');

                ob_start();
                $content  = '';
                $content .= '<div class="rbfw_bikecarsd_time_table_container rbfw-bikecarsd-step" data-step="2">';
                $content .= '<a class="rbfw_back_step_btn" back-step="1" data-step="2"><i class="fa-solid fa-circle-left"></i> '.rbfw_string_return('rbfw_text_back_to_previous_step',__('Back to Previous Step','booking-and-rental-manager-for-woocommerce')).'</a>';

                if($is_muffin_template == 0){
                    $content .= '<div class="rbfw_step_selected_date"><i class="fa-solid fa-calendar-check"></i> '.rbfw_string_return('rbfw_text_you_selected',__('You selected','booking-and-rental-manager-for-woocommerce')).': '.$result.'</div>';
                }

                if($is_muffin_template == 1){
                    $content .= '<div class="rbfw_step_selected_date rbfw_muff_selected_date">';
                    $content .= '<div class="rbfw_muff_selected_date_col"><span class="rbfw_muff_selected_date_value">'.$result.'</span></div>';
                    $content .= '</div>';
                }

                $content .= '<div class="rbfw_bikecarsd_time_table_wrap">';

                
                foreach ($available_times as $value) {
                    $converted_time =  date("H:i", strtotime($value)); 
                    $ts_time = $this->rbfw_get_time_slot_by_label($value);
                   
                    $is_booked = $this->rbfw_get_time_booking_status($id, $selected_date, $ts_time);
                    
                    $disabled = '';

                    if((($nowDate == $selected_date) && ($converted_time < $nowTime)) || ($is_booked === true)){
                        $disabled = 'disabled';
                    }
                    $content .= '<a data-time="'.$ts_time.'" class="rbfw_bikecarsd_time '.$disabled.'"><span class="rbfw_bikecarsd_time_span">'.$value.'</span>';

                    if($is_booked === true){
                        $content .= '<span class="rbfw_bikecarsd_time_booked">'.rbfw_string_return('rbfw_text_booked',__('Booked','booking-and-rental-manager-for-woocommerce')).'</span>';
                    }

                    $content .= '</a>';
                }
                
                $content .= '</div>';
                $content .= '</div>';
                echo $content;
                $output = ob_get_clean();
                echo $output;
            }
            
            wp_die();
        }

        public function rbfw_bikecarsd_type_list(){
            global $rbfw;
            if(isset($_POST['post_id'])){
                $id = $_POST['post_id'];
                $selected_time = !empty($_POST['selected_time']) ? $_POST['selected_time'] : '';
                $is_muffin_template = $_POST['is_muffin_template'];
                $rbfw_bike_car_sd_data = get_post_meta($id, 'rbfw_bike_car_sd_data', true) ? get_post_meta($id, 'rbfw_bike_car_sd_data', true) : [];
                $rbfw_extra_service_data = get_post_meta( $id, 'rbfw_extra_service_data', true ) ? get_post_meta( $id, 'rbfw_extra_service_data', true ) : [];
                $rbfw_product_id = get_post_meta( $id, "link_wc_product", true ) ? get_post_meta( $id, "link_wc_product", true ) : $id;

                $selected_date = $_POST['selected_date'];
                $available_times = rbfw_get_available_times($id);
                $default_timezone = wp_timezone_string();
                $date = new DateTime("now", new DateTimeZone($default_timezone) );
                $nowTime  = $date->format('H:i');
                $nowDate  = $date->format('Y-m-d');

                $date_to_string = new DateTime($selected_date);
                $result = $date_to_string->format('F j, Y');
                $currency_symbol = rbfw_mps_currency_symbol();
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

                if($rbfw_payment_system == 'mps'){
                    $rbfw_payment_system = 'mps_enabled';
                }else{
                    $rbfw_payment_system = 'wps_enabled'; 
                }

                
                $available_qty_info_switch = get_post_meta($id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($id, 'rbfw_available_qty_info_switch', true) : 'no';



                $content  = '';
                $content .= '<div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step" data-step="3">';
                $content .= '<a class="rbfw_back_step_btn" back-step="2" data-step="3"><i class="fa-solid fa-circle-left"></i> '.rbfw_string_return('rbfw_text_back_to_previous_step',__('Back to Previous Step','booking-and-rental-manager-for-woocommerce')).'</a>';

                if($is_muffin_template == 0){
                    $content .= '<div class="rbfw_step_selected_date" data-time="'.$selected_time.'"><i class="fa-solid fa-calendar-check"></i> '.rbfw_string_return('rbfw_text_you_selected',__('You selected','booking-and-rental-manager-for-woocommerce')).': '.$result.' '.$selected_time.'</div>';
                }

                if($is_muffin_template == 1){
                    $content .= '<div class="rbfw_step_selected_date rbfw_muff_selected_date" step="3">';
                    $content .= '<div class="rbfw_muff_selected_date_col"><label><i class="fa-regular fa-calendar-days"></i> '.rbfw_string_return('rbfw_text_date',__('Date','booking-and-rental-manager-for-woocommerce')).'</label><span class="rbfw_muff_selected_date_value">'.$result.'</span></div>';
                    $content .= '<div class="rbfw_muff_selected_date_col"><label><i class="fa-regular fa-clock"></i> '.rbfw_string_return('rbfw_text_time',__('Time','booking-and-rental-manager-for-woocommerce')).'</label><span class="rbfw_muff_selected_date_value">'.$selected_time.'</span></div>';
                    $content .= '</div>';
                }

                $content .= '<div class="rbfw_bikecarsd_pricing_table_wrap">';
                $content .= '<table class="rbfw_bikecarsd_price_table rbfw_bikecarsd_rt_price_table">';
                $content .= '<thead>';
                $content .= '<tr>';
                $content .= '<th class="w_50_pc">'.rbfw_string_return('rbfw_text_rent_type',__('Type','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content .= '<th class="w_30_pc">'.rbfw_string_return('rbfw_text_price',__('Price','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content .= '<th class="w_20_pc">'.rbfw_string_return('rbfw_text_quantity',__('Quantity','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content .= '</tr>';
                $content .= '</thead>';
                $content .= '<tbody>';
                $i = 1;
                foreach ($rbfw_bike_car_sd_data as $value) {

                   $max_available_qty = rbfw_get_bike_car_sd_available_qty($id, $selected_date, $value['rent_type'], $selected_time);

                    if($value['qty'] > 0){

                        $content .= '<tr>';
                        $content .= '<td class="w_50_pc">';
                        $content .= '<span class="rbfw_bikecarsd_type_title">'.$value['rent_type'].'</span><small class="rbfw_bikecarsd_type_desc">'.$value['short_desc'].'</small>';

                        if($available_qty_info_switch == 'yes'){
                            $content .= '<small class="rbfw_available_qty_notice">('.rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce')).$max_available_qty.')</small>';
                        }

                        

                        $content .= '<input type="hidden" name="rbfw_bikecarsd_info['.$i.'][rent_type]" value="'.$value['rent_type'].'"/>';
                        $content .= '<input type="hidden" name="rbfw_bikecarsd_info['.$i.'][short_desc]" value="'.$value['short_desc'].'"/>';
                        $content .= '</td>';

                        $content .= '<td class="w_30_pc"><span class="rbfw_bikecarsd_type_price">'.rbfw_mps_price($value['price']).'</span></td>';
                        $content .= '<input type="hidden" name="rbfw_bikecarsd_info['.$i.'][price]" value="'.$value['price'].'"/>';
                        $content .= '<td class="w_20_pc">';
                        $content .= '<div class="rbfw_service_price_wrap">';
                        $content .= '<div class="rbfw_qty_input">';
                        $content .= '<a class="rbfw_qty_minus rbfw_bikecarsd_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                        $content .= '<input type="number" min="0" max="'.$max_available_qty.'" value="0" name="rbfw_bikecarsd_info['.$i.'][qty]" class="rbfw_bikecarsd_qty" data-price="'.$value['price'].'" data-type="'.$value['rent_type'].'" data-cat="bikecarsd" />';
                        $content .= '<a class="rbfw_qty_plus rbfw_bikecarsd_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                        $content .= '</div>';


                        $content .= '</div>';
                        $content .= '</td>';
                        $content .= '</tr>';
                        $i++;
                    }


                }

                $content .= '</tbody>';
                $content .= '</table>';

                if(!empty($rbfw_extra_service_data)){

                $content   .= '<table class="rbfw_bikecarsd_price_table rbfw_bikecarsd_es_price_table">';
                $content   .= '<thead>';
                $content   .= '<tr>';
                $content   .= '<th class="w_50_pc">'.$rbfw->get_option('rbfw_text_service_name', 'rbfw_basic_translation_settings', __('Service Name','booking-and-rental-manager-for-woocommerce')).'</th>';
                $content   .= '<th class="w_30_pc">'.$rbfw->get_option('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce')).'</th>';                
                $content   .= '<th class="w_20_pc">'.$rbfw->get_option('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')).'</th>';                
                $content   .= '</tr>';             
                $content   .= '</thead>';
                $content   .= '<tbody>';

                $c = 0;

                foreach ($rbfw_extra_service_data as $value):

                $img_url    = !empty($value['service_img']) ? wp_get_attachment_url($value['service_img']) : '';
                $uniq_id    = rand();    
                if($img_url):
                    $img    = '<a href="#rbfw_service_img_'.$uniq_id.'" rel="mage_modal:open"><img src="'.esc_url($img_url).'"/></a>';
                    $img   .= '<div id="rbfw_service_img_'.$uniq_id.'" class="mage_modal"><img src="'.esc_url($img_url).'"/></div>';
                else:
                    $img    = '';
                endif;
                
                $max_es_available_qty = rbfw_get_bike_car_sd_es_available_qty($id, $selected_date, $value['service_name']);

                if($value['service_qty'] > 0){

                    $content   .= '<tr>';
                    $content   .= '<td class="w_50_pc">';
                    $content   .= '<div>';
                    $content   .= $img;
                    $content   .= '</div>';
                    $content   .= '<div>';
                    $content   .= '<span class="rbfw_bikecarsd_type_title">'.$value['service_name'].'</span>';
                    
                    if(!empty($value['service_desc'])){
                        $content   .= '<small class="rbfw_bikecarsd_type_desc">'.$value['service_desc'].'</small>';
                    }

                    if($available_qty_info_switch == 'yes'){

                        $content .= '<small class="rbfw_available_qty_notice">('.rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce')).$max_es_available_qty.')</small>';
                    }
                    
                    $content   .= '<input type="hidden" name="rbfw_service_info['.$c.'][service_name]" value="'.$value['service_name'].'"/>';
                    $content   .= '</div>';           
                    $content   .= '</td>';                 
                    $content   .= '<td class="w_30_pc">';
                    $content   .= rbfw_mps_price($value['service_price']);
                    $content   .= '</td>'; 
                    $content   .= '<td class="w_20_pc">';
                    $content   .= '<div class="rbfw_service_price_wrap">';
                    $content   .= '<input type="hidden" name="rbfw_service_info['.$c.'][service_price]" value="'.$value['service_price'].'"/>';
                    $content   .= '<div class="rbfw_qty_input">';
                    $content   .= '<a class="rbfw_qty_minus rbfw_service_qty_minus"><i class="fa-solid fa-minus"></i></a>';
                    $content   .= '<input type="number" min="0" max="'.esc_attr($max_es_available_qty).'" value="0" name="rbfw_service_info['.$c.'][service_qty]" class="rbfw_service_qty" data-price="'.$value['service_price'].'" data-type="'.$value['service_name'].'" data-cat="service"/>';
                    $content   .= '<a class="rbfw_qty_plus rbfw_service_qty_plus"><i class="fa-solid fa-plus"></i></a>';
                    $content   .= '</div>';
                    

                    $content   .= '</div>';
                    $content   .= '</td>';              
                    $content   .= '</tr>';
                }

                $c++;
                endforeach;
                $content   .= '</tbody>';                 
                $content   .= '</table>';

                }

                    $content   .= '<div class="item rbfw_bikecarsd_price_summary">
                                    <div class="item-content rbfw-costing">
                                        <ul class="rbfw-ul">
                                            <li class="duration-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>';

                                            if(!empty($rbfw_extra_service_data)){

                                                $content   .= '<li class="resource-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span>'.$currency_symbol.'<span class="price-figure" data-price="0">0</span></span></li>';
                                            }

                                            $content   .= '<li class="subtotal">'.$rbfw->get_option('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                            <li class="total"><strong>'.$rbfw->get_option('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span>'.$currency_symbol.'<span class="price-figure">0.00</span></span></li>
                                        </ul>
                                        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                    </div>
                                </div>';
                $content .= '</div>';

                /* Include Custom Registration Form */
                if(class_exists('Rbfw_Reg_Form')){
                    $reg_form = new Rbfw_Reg_Form();
                    $reg_fields = $reg_form->rbfw_generate_regf_fields($id);
                    $content.= $reg_fields;
                }
                /* End: Include Custom Registration Form */

                $content .= '</div>';

                echo $content;

            }
            wp_die();
        }

        public function rbfw_bikecarsd_ajax_price_calculation(){

                global $rbfw;
                $content            = '';          
                $bikecarsd_price_arr     = !empty($_POST['bikecarsd_price_arr']) ? $_POST['bikecarsd_price_arr'] : [];
                $service_price_arr  = !empty($_POST['service_price_arr']) ? $_POST['service_price_arr'] : [];
                $post_id = !empty($_POST['post_id']) ? strip_tags($_POST['post_id']) : '';
                $bikecarsd_price         = 0;
                $service_price      = 0;
                $total_bikecarsd_price   = 0;
                $total_service_price = 0;
                $subtotal_price     = 0;
                $total_price        = 0;
    
                foreach ($bikecarsd_price_arr as $key => $value):
                    $bikecarsd_price += (float)$value['data_qty'] * (float)$value['data_price'];
                endforeach;

                $total_bikecarsd_price = (float)$bikecarsd_price;

                if(!empty($service_price_arr)){
                    foreach ($service_price_arr as $key => $value):
                        $service_price += (float)$value['data_qty'] * (float)$value['data_price'];
                    endforeach;
                }


                if($service_price > 0):
                    $total_service_price = (float)$service_price;
                endif;
    
                if($total_bikecarsd_price > 0 || $total_service_price > 0):
                    $subtotal_price = (float)$total_bikecarsd_price + (float)$total_service_price;
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
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage)){
                    //Convert our percentage value into a decimal.
                    $percentInDecimal = $mps_tax_percentage / 100;
                    //Get the result.
                    $percent = $percentInDecimal * $total_price;
                    $total_price = $total_price + $percent;
                }

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){

                    $tax_status = '('.rbfw_string_return('rbfw_text_includes',__('Includes','booking-and-rental-manager-for-woocommerce')).' '.rbfw_mps_price($percent).' '.rbfw_string_return('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')).')';
                }

                /* End Tax Calculations */

                $content.= '<div class="item rbfw_bikecarsd_price_summary">
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_bikecarsd_price.'">'.rbfw_mps_price($total_bikecarsd_price).'</span></li>';

                                        if(!empty($service_price_arr)){
                                            $content.= '<li class="resource-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$total_service_price.'">'.rbfw_mps_price($total_service_price).'</span></li>';
                                        }

                                        $content.= '<li class="subtotal">'.$rbfw->get_option('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$subtotal_price.'">'.rbfw_mps_price($subtotal_price).'</span></li>';

                                        if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){

                                            $content.= '<li class="tax">'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$percent.'">'.rbfw_mps_price($percent).'</span></li>';
                                        }

                                        $content.='<li class="total"><strong>'.$rbfw->get_option('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span class="price-figure" data-price="'.$total_price.'">'.rbfw_mps_price($total_price).' '.$tax_status.'</span></li>
                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>';

                echo $content;
       
            wp_die();
        }

        public function rbfw_bike_car_sd_frontend_scripts($rbfw_post_id){
            global $post;
            $post_id = !empty($post->ID) ? $post->ID : '';

            if(!empty($rbfw_post_id)){
                $post_id = $rbfw_post_id;
            }

            if(empty($post_id)){
                return;
            }

            $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);

            if($rent_type != 'bike_car_sd' && $rent_type != 'appointment'  && ( is_a( $post, 'WP_Post' ) && ! has_shortcode( $post->post_content, 'rent-add-to-cart') )):
                return;
            endif; 

            $time_slot_switch = !empty(get_post_meta($post_id, 'rbfw_time_slot_switch', true)) ? get_post_meta($post_id, 'rbfw_time_slot_switch', true) : 'on';
            ?>

            <?php
        }
    }
    new RBFW_BikeCarSd_Function();
}