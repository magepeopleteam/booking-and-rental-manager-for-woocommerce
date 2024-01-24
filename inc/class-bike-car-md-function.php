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
            add_action('wp_ajax_rbfw_bikecarmd_ajax_price_calculation', array($this, 'rbfw_bikecarmd_ajax_price_calculation'));
            add_action('wp_ajax_nopriv_rbfw_bikecarmd_ajax_price_calculation', array($this,'rbfw_bikecarmd_ajax_price_calculation'));
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

        public function rbfw_bikecarmd_ajax_price_calculation(){
            global $rbfw;
            $post_id   = $_POST['post_id'];
            $pickup_date  = $_POST['pickup_date'];
            $start_date = $pickup_date;
            $pickup_time  = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : '';
            $dropoff_date = $_POST['dropoff_date'];
            $end_date = $dropoff_date;
            $dropoff_time = !empty($_POST['dropoff_time']) ? $_POST['dropoff_time'] : '';
            $item_quantity = $_POST['item_quantity'];
            $service_price_arr = !empty($_POST['service_price_arr']) ? $_POST['service_price_arr'] : [];
            $reload_es = $_POST['reload_es'];

            if(empty($pickup_time) && empty($dropoff_time)){
                $pickup_datetime  = date( 'Y-m-d', strtotime( $pickup_date.' '.'00:00:00' ) );
                $dropoff_datetime = date( 'Y-m-d', strtotime( $dropoff_date.' '.rbfw_end_time() ) );
                $start_datetime_raw = $pickup_date.' '.'00:00:00';
                $end_datetime_raw = $dropoff_date.' '.rbfw_end_time();
            } else {
                $pickup_datetime  = date( 'Y-m-d H:i', strtotime( $pickup_date . ' ' . $pickup_time ) );
                $dropoff_datetime = date( 'Y-m-d H:i', strtotime( $dropoff_date . ' ' . $dropoff_time ) );
                $start_datetime_raw = $pickup_date.' '.$pickup_time;
                $end_datetime_raw = $dropoff_date.' '.$dropoff_time;
            }
            

            $pickup_datetime  = new DateTime( $pickup_datetime );
            $dropoff_datetime = new DateTime( $dropoff_datetime );

            $daily_rate  = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
            $hourly_rate = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
            
            // sunday rate
            $hourly_rate_sun = get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) : 0;
            $daily_rate_sun = get_post_meta($post_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sun_daily_rate', true) : 0;
            $enabled_sun = get_post_meta($post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($post_id, 'rbfw_enable_sun_day', true) : 'yes';

            // monday rate
            $hourly_rate_mon = get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) : 0;
            $daily_rate_mon = get_post_meta($post_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($post_id, 'rbfw_mon_daily_rate', true) : 0;
            $enabled_mon = get_post_meta($post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($post_id, 'rbfw_enable_mon_day', true) : 'yes';

            // tuesday rate
            $hourly_rate_tue = get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) : 0;
            $daily_rate_tue = get_post_meta($post_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($post_id, 'rbfw_tue_daily_rate', true) : 0;
            $enabled_tue = get_post_meta($post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($post_id, 'rbfw_enable_tue_day', true) : 'yes';

            // wednesday rate
            $hourly_rate_wed = get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) : 0;
            $daily_rate_wed = get_post_meta($post_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($post_id, 'rbfw_wed_daily_rate', true) : 0;
            $enabled_wed = get_post_meta($post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($post_id, 'rbfw_enable_wed_day', true) : 'yes';

            // thursday rate
            $hourly_rate_thu = get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) : 0;
            $daily_rate_thu = get_post_meta($post_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($post_id, 'rbfw_thu_daily_rate', true) : 0;
            $enabled_thu = get_post_meta($post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($post_id, 'rbfw_enable_thu_day', true) : 'yes';

            // friday rate
            $hourly_rate_fri = get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) : 0;
            $daily_rate_fri = get_post_meta($post_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($post_id, 'rbfw_fri_daily_rate', true) : 0;
            $enabled_fri = get_post_meta($post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($post_id, 'rbfw_enable_fri_day', true) : 'yes';	

            // saturday rate
            $hourly_rate_sat = get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) : 0;
            $daily_rate_sat = get_post_meta($post_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sat_daily_rate', true) : 0;
            $enabled_sat = get_post_meta($post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($post_id, 'rbfw_enable_sat_day', true) : 'yes';

            //$current_day = date('D');

            $current_day = date('D', strtotime($pickup_date));
            
            if($current_day == 'Sun' && $enabled_sun == 'yes'){
                $hourly_rate = $hourly_rate_sun;
                $daily_rate = $daily_rate_sun;
            }elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
                $hourly_rate = $hourly_rate_mon;
                $daily_rate = $daily_rate_mon;
            }elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
                $hourly_rate = $hourly_rate_tue;
                $daily_rate = $daily_rate_tue;
            }elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
                $hourly_rate = $hourly_rate_wed;
                $daily_rate = $daily_rate_wed;
            }elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
                $hourly_rate = $hourly_rate_thu;
                $daily_rate = $daily_rate_thu;
            }elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
                $hourly_rate = $hourly_rate_fri;
                $daily_rate = $daily_rate_fri;
            }elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
                $hourly_rate = $hourly_rate_sat;
                $daily_rate = $daily_rate_sat;
            }else{
                $hourly_rate = $hourly_rate;
                $daily_rate = $daily_rate;		
            }
            
            
            $current_date = date_i18n('Y-m-d');
            $rbfw_sp_prices = get_post_meta( $post_id, 'rbfw_seasonal_prices', true );

            if(!empty($rbfw_sp_prices)){
     
                $sp_array = [];
                $i = 0;
                foreach ($rbfw_sp_prices as $value) {
                    $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
                    $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
                    $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
                    $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
                    $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
                    $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
                    $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
                    $i++;
                }
        
                foreach ($sp_array as $sp_arr) {
                    if (in_array($pickup_date,$sp_arr['sp_dates'])){
                        $hourly_rate = $sp_arr['sp_hourly_rate'];
                        $daily_rate  = $sp_arr['sp_daily_rate'];
                    }
                }
            }

            $rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

            $diff = date_diff( $pickup_datetime, $dropoff_datetime );
            $days     = 0;
            $hours    = 0;
            $price    = 0;
            $duration = '';
            $duration_cost = 0;
            $service_cost = 0;
            $total_price = 0;
            if ( $diff ) {
                $days    = $diff->days;
                $hours   += $diff->h;
                $minutes = $diff->i;
                if ( $days > 0 ) {
                    $price    += (int)$days * (float)$daily_rate;

                    $duration .= $days > 1 ? $days.' '.rbfw_string_return('rbfw_text_days',__('Days','booking-and-rental-manager-for-woocommerce')).' ' : $days.' '.rbfw_string_return('rbfw_text_day',__('Day','booking-and-rental-manager-for-woocommerce')).' ';
                }
                if ( $hours > 0 ) {
                    $price    += (int)$hours * (float)$hourly_rate;

                    $duration .= $hours > 1 ? $hours.' '.rbfw_string_return('rbfw_text_hours',__('Hours','booking-and-rental-manager-for-woocommerce')) : $hours.' '.rbfw_string_return('rbfw_text_hour',__('Hour','booking-and-rental-manager-for-woocommerce'));
                }
            }

            $duration_cost += $price * $item_quantity;

            if(!empty($service_price_arr)){
                foreach ($service_price_arr as $data_name => $values) {
                    if($item_quantity > 1 && (int)$values['data_qty'] == 1 && $rbfw_enable_extra_service_qty != 'yes'){
                        $service_cost += $item_quantity * (float)$values['data_price'];
                    } else {
                        $service_cost += (int)$values['data_qty'] * (float)$values['data_price'];
                    }

                }
            }


            $subtotal_price = $duration_cost + $service_cost;


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
                $percent = $percentInDecimal * $subtotal_price;
                $total_price = $subtotal_price + $percent;
            }else{
                $total_price = $subtotal_price;
            }

            if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'including_tax'){
                $tax_status = '('.rbfw_string_return('rbfw_text_includes',__('Includes','booking-and-rental-manager-for-woocommerce')).' '.rbfw_mps_price($percent).' '.rbfw_string_return('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')).')';
            }

            /* End Tax Calculations */

            $content = '';
            $content.= '<div class="item rbfw_bikecarmd_price_summary">
                <div class="item-content rbfw-costing">
                    <ul class="rbfw-ul">
                        <li class="duration-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$duration_cost.'">'.rbfw_mps_price($duration_cost).'</span></li>
                        <li class="resource-costing rbfw-cond">'.$rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')).' <span class="price-figure" data-price="'.$service_cost.'">'.rbfw_mps_price($service_cost).'</span></li>
                        <li class="subtotal">'.$rbfw->get_option('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$subtotal_price.'">'.rbfw_mps_price($subtotal_price).'</span></li>';

                        if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($mps_tax_percentage) && $mps_tax_format == 'excluding_tax'){

                            $content.= '<li class="tax">'.$rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')).'<span class="price-figure" data-price="'.$percent.'">'.rbfw_mps_price($percent).'</span></li>';
                        }

                    /* Start Discount Calculations */

                    if(rbfw_check_discount_over_days_plugin_active() === true){

                        if(function_exists('rbfw_get_discount_array')){

                            $discount_arr = rbfw_get_discount_array($post_id, $start_datetime_raw, $end_datetime_raw, $total_price);
                    
                        } else {
                    
                            $discount_arr = [];
                        }

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

                    }

                        $content.='<li class="total"><strong>'.$rbfw->get_option('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')).'</strong> <span class="price-figure" data-price="'.$total_price.'">'.rbfw_mps_price($total_price).' '.$tax_status.'</span></li>
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

            $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $pickup_date, $dropoff_date);
            $item_quantity_box = '';

            $item_quantity_box .= '<select class="rbfw-select" name="rbfw_item_quantity" id="rbfw_item_quantity">
                                    <option value="0">'.rbfw_string_return('rbfw_text_choose_number_of_qty',__('Choose number of quantity','booking-and-rental-manager-for-woocommerce')).'</option>';
                                        
                                        for ($qty = 1; $qty <= $max_available_qty; $qty++) { 
                                            
                                            $item_quantity_box .= '<option value="'.mep_esc_html($qty).'"'; 
                                            
                                            if($qty == 1){ 
                                                $item_quantity_box .= 'selected'; 
                                            } 

                                            $item_quantity_box .= '>'.mep_esc_html($qty).'</option>';   
                                        }
                                        
            $item_quantity_box .= '</select>';

            $available_qty_info_switch = get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) : 'no';
            
            if($available_qty_info_switch == 'yes'){

                $item_quantity_box .= '<div class="rbfw_available_qty_notice">'.$max_available_qty.' '.rbfw_string_return('rbfw_text_left_qty',__('Left','booking-and-rental-manager-for-woocommerce')).'</div>';
            }

            

            $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
            $rbfw_variations_data = get_post_meta( $post_id, 'rbfw_variations_data', true ) ? get_post_meta( $post_id, 'rbfw_variations_data', true ) : [];

            $variation_content = '';

        if($reload_es == 1){

            if($rbfw_enable_variations == 'yes' && !empty($rbfw_variations_data)){

                foreach ($rbfw_variations_data as $data_arr_one) {

                    $selected_value = !empty($data_arr_one['selected_value']) ? $data_arr_one['selected_value'] : '';

                    $variation_content .= '<div class="item">
                                            <div class="rbfw-single-right-heading">'.esc_html($data_arr_one['field_label']).'</div>
                                            <div class="item-content rbfw-p-relative">';

                                                if(!empty($data_arr_one['value'])){
                                                    $variation_content .='<select class="rbfw-select rbfw_variation_field" name="'.esc_attr($data_arr_one['field_id']).'" id="'.esc_attr($data_arr_one['field_id']).'" data-field="'.esc_attr($data_arr_one['field_label']).'">';

                                                    if(empty($selected_value)){
                                                        $variation_content .= '<option value="">'.rbfw_string_return('rbfw_text_choose',__('Choose','booking-and-rental-manager-for-woocommerce')).' '.$data_arr_one['field_label'].'</option>';
                                                    }

                                                    foreach ($data_arr_one['value'] as $data_arr_two) {

                                                        $variation_available_qty = rbfw_get_multiple_date_variations_available_qty($post_id, $pickup_date, $dropoff_date, $data_arr_two['name']);

                                                        if($variation_available_qty == 0){
                                                            
                                                            $notice = '('.rbfw_string_return('rbfw_text_out_of_stock',__('Out of stock','booking-and-rental-manager-for-woocommerce')).')';
                                                            $disabled_attr = 'disabled';

                                                        } else {

                                                            $notice = '';
                                                            $disabled_attr = '';
                                                        }

                                                        $variation_content .= '<option value="'.esc_attr($data_arr_two['name']).'"'; 
                                                        if($data_arr_two['name'] == $selected_value){ echo 'selected'; } 
                                                        $variation_content .= $disabled_attr.'>'.esc_html($data_arr_two['name']).' '.$notice.'</option>';
                                                    }

                                                    $variation_content .= '</select>';
                                                }

                                                $variation_content .= '</div>
                                        </div>';
                }
            } 
        }
            
            $extra_service_list = get_post_meta($post_id, 'rbfw_extra_service_data', true) ? get_post_meta($post_id, 'rbfw_extra_service_data', true) : [];

            $extra_service_content = '';

            if($reload_es == 1){

            $extra_service_content .= '<table class="rbfw_bikecarmd_es_table">
                                        <tbody>';
                                        
                                        $c = 0;
                                        foreach ($extra_service_list as $extra) :

                                        $max_es_available_qty = rbfw_get_multiple_date_es_available_qty($post_id, $pickup_date, $dropoff_date, $extra['service_name']);

                                        if($max_es_available_qty == 0){

                                            $is_disabled = 'disabled';

                                        } else {

                                            $is_disabled = '';
                                        }

                                        if($extra['service_qty'] > 0){

                                            $extra_service_content .= '<tr>
                                            <td class="w_20 rbfw_bikecarmd_es_hidden_input_box">
                                            <div class="label">
                                                <input type="hidden" name="rbfw_service_info['.$c.'][service_name]" value="'.mep_esc_html($extra['service_name']).'">
                                                <input type="hidden" name="rbfw_service_info['.$c.'][service_qty]" class="rbfw-resource-qty" value="">
                                                <input type="hidden" name="rbfw_service_info['.$c.'][service_price]"  value="'.$extra['service_price'].'">
                                                <label class="switch">
                                                    <input type="checkbox"  class="rbfw-resource-price rbfw-resource-price-multiple-qty" data-status="0" value="1" data-cat="service" data-price="'.$extra['service_price'].'" data-name="'.mep_esc_html($extra['service_name']).'" '.$is_disabled.'>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            </td>
                                            <td>'.mep_esc_html($extra['service_name']).'</td>
                                            <td class="w_20">'.rbfw_mps_price($extra['service_price']).'</td>';

                                            if($rbfw_enable_extra_service_qty != 'yes' &&  $available_qty_info_switch == 'yes'){

                                                $extra_service_content .= '<td>';   
                                                $extra_service_content .= '<div class="rbfw_available_qty_notice">'.$max_es_available_qty.' '.rbfw_string_return('rbfw_text_left_qty',__('Left','booking-and-rental-manager-for-woocommerce')).'</div>';
                                                $extra_service_content .= '</td>';
                                            }

                                            if($rbfw_enable_extra_service_qty == 'yes'){

                                                $extra_service_content .= '<td class="rbfw_bikecarmd_es_input_box" style="display:none">
                                                <div class="rbfw_qty_input">
                                                    <a class="rbfw_qty_minus rbfw_bikecarmd_es_qty_minus"><i class="fa-solid fa-minus"></i></a>
                                                    <input type="number" min="0" max="'.esc_attr($max_es_available_qty).'" value="1" class="rbfw_bikecarmd_es_qty" data-cat="service" data-price="'.$extra['service_price'].'" data-name="'.mep_esc_html($extra['service_name']).'"/>
                                                    <a class="rbfw_qty_plus rbfw_bikecarmd_es_qty_plus"><i class="fa-solid fa-plus"></i></a>
                                                </div>';

                                                if($available_qty_info_switch == 'yes'){

                                                    $extra_service_content .= '<div class="rbfw_available_qty_notice">'.$max_es_available_qty.' '.rbfw_string_return('rbfw_text_left_qty',__('Left','booking-and-rental-manager-for-woocommerce')).'</div>';
                                                }

                                                $extra_service_content .='</td>';

                                            }

                                            $extra_service_content .= '</tr>';
                                        }
                                        
                                        $c++;
                                        endforeach; 
                                        
                                        $extra_service_content .= '</tbody>	
                                    </table>';
                                    $extra_service_content .= '<script>rbfw_bikecarmd_es_update_input_value_onchange_onclick(); rbfw_bikecarmd_es_price_multiple_qty_onchange();</script>';
            }
            
            $rbfw_minimum_booking_day = get_post_meta( $post_id, 'rbfw_minimum_booking_day', true );
            $rbfw_maximum_booking_day = get_post_meta( $post_id, 'rbfw_maximum_booking_day', true );
            $min_max_day_notice = '';

            if(!empty($rbfw_minimum_booking_day) && $days < $rbfw_minimum_booking_day){
                $min_max_day_notice .= rbfw_string_return('rbfw_text_min_number_days_have_to_book',__('Minimum number of days have to book is','booking-and-rental-manager-for-woocommerce')). ': '.$rbfw_minimum_booking_day;
            }

            if(!empty($rbfw_maximum_booking_day) && $days > $rbfw_maximum_booking_day){
                $min_max_day_notice .= rbfw_string_return('rbfw_text_max_number_days_have_to_book',__('Maximum number of days can book is','booking-and-rental-manager-for-woocommerce')). ': '.$rbfw_maximum_booking_day;
            }

            if(!empty($min_max_day_notice) && rbfw_check_min_max_booking_day_active() === true){

                echo json_encode( array(
                    'duration' => $min_max_day_notice,
                    'reload_es' => 0,
                    'max_available_qty' => $max_available_qty
                ) );    

            } else {

                echo json_encode( array(
                    'duration'   => $duration,
                    'content'    => $content,
                    'item_quantity_box'    => $item_quantity_box,
                    'variation_content'    => $variation_content,
                    'extra_service_content'    => $extra_service_content,
                    'reload_es'    => $reload_es,
                    'max_available_qty' => $max_available_qty
                ) );
            }
            
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


            ?>
            <script>
            jQuery(document).ready(function() {

                <?php if($rbfw_enable_start_end_date == 'no'){ ?>

                    jQuery('#pickup_date').trigger('change');

                <?php } ?>

                jQuery('#pickup_date').change(function(e) {

                    let selected_date = jQuery(this).val();
                    const [gYear, gMonth, gDay] = selected_date.split('-');
                    jQuery("#dropoff_date").datepicker("destroy");
                    jQuery('#dropoff_date').datepicker({
                        dateFormat: 'yy-mm-dd',
                        minDate: new Date(gYear, gMonth - 1, gDay)
                    });
                });
            });

                // update input value onclick and onchange
                rbfw_bikecarmd_es_update_input_value_onchange_onclick();

                function rbfw_bikecarmd_es_update_input_value_onchange_onclick() {

                    jQuery('.rbfw_bikecarmd_es_qty_plus').click(function(e) {
                        let target_input = jQuery(this).siblings("input[type=number]");
                        let target_input2 = jQuery(this).parents('td').siblings('.rbfw_bikecarmd_es_hidden_input_box').find('.rbfw-resource-qty');
                        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
                        let max_value = parseInt(jQuery(this).siblings("input[type=number]").attr('max'));
                        let update_value = current_value + 1;

                        if(update_value <= max_value){
                            jQuery(target_input).val(update_value);
                            jQuery(target_input).attr('value', update_value);
                            jQuery(target_input2).val(update_value);
                            jQuery(target_input2).attr('value', update_value);
                        }else{
                            let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
                            tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top',trigger: 'click'});
                        }
                        
                    });
                    jQuery('.rbfw_bikecarmd_es_qty_minus').click(function(e) {
                        let target_input = jQuery(this).siblings("input[type=number]");
                        let target_input2 = jQuery(this).parents('td').siblings('.rbfw_bikecarmd_es_hidden_input_box').find('.rbfw-resource-qty');
                        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
                        let update_value = current_value - 1;
                        if (current_value > 0) {
                            jQuery(target_input,target_input2).val(update_value);
                            jQuery(target_input,target_input2).attr('value', update_value);
                            jQuery(target_input2).val(update_value);
                            jQuery(target_input2).attr('value', update_value);
                        }
                    });
                    jQuery('.rbfw_bikecarmd_es_qty').change(function(e) {
                        let get_value = jQuery(this).val();
                        let max_value = parseInt(jQuery(this).attr('max'));

                        if(get_value <= max_value){
                            jQuery(this).val(get_value);
                            jQuery(this).attr('value', get_value);
                        }else{
                            jQuery(this).val(max_value);
                            jQuery(this).attr('value',max_value);
                            let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
                            tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top'});
                        }
                    });
                    
                }
                // end update input value onclick and onchange

                let service_price_arr = {};

                rbfw_bikecarmd_es_price_multiple_qty_onchange();

                function rbfw_bikecarmd_es_price_multiple_qty_onchange(){
                    
                    jQuery('.rbfw-resource-price-multiple-qty').change(function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    let that = jQuery(this);
                    let this_checkbox = jQuery(this);
                    let this_checkbox_status = this_checkbox.attr('data-status');

                    if (this_checkbox_status.length > 0) {
                        if (this_checkbox_status == '0') {
                            jQuery(this_checkbox).attr('data-status', '1');
                            jQuery(this_checkbox).attr('checked', true);
                            jQuery(this_checkbox).prop('checked', true);
                            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').find('.rbfw_bikecarmd_es_qty').val('1').attr('value','1');
                            jQuery(this_checkbox).val('1');
                            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').show();
                            jQuery(this_checkbox).parent('.switch').siblings('.rbfw-resource-qty').val('1').attr('value','1');
                            

                        } else {
                            jQuery(this_checkbox).attr('data-status', '0');
                            jQuery(this_checkbox).removeAttr('checked');
                            jQuery(this_checkbox).prop('checked', false);
                            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').find('.rbfw_bikecarmd_es_qty').val('0').attr('value','0');
                            jQuery(this_checkbox).val('0');
                            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').hide();
                            jQuery(this_checkbox).parent('.switch').siblings('.rbfw-resource-qty').val('').attr('value','');
                            
                        }
                    }

                    let status = this_checkbox.attr('data-status');
                    let data_name = jQuery(this_checkbox).attr('data-name');
                    
                    if(status == '1'){
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                    }else{
                        delete service_price_arr[data_name];
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                    }

                    });

                    jQuery('.rbfw_bikecarmd_es_qty_minus,.rbfw_bikecarmd_es_qty_plus').click(function (e) {
                        let that = jQuery(this).siblings('.rbfw_bikecarmd_es_qty');
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                    });

                    jQuery('#pickup_date,#dropoff_date,#pickup_time,#dropoff_time').change(function (e) {
                        let that = jQuery(this);
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                        service_price_arr = {};
                    });

                    jQuery('.rbfw_bikecarmd_es_qty').change(function (e) {
                        let that = jQuery(this);
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                    });

                    jQuery(document).on('change', '#rbfw_item_quantity', function(e) {
                        let that = jQuery(this);
                        rbfw_bikecarmd_ajax_price_calculation(that, 0);
                    });
                }

                // On change quantity value calculate price
                
                function rbfw_bikecarmd_ajax_price_calculation(that, reload_es){
                    
                    if (typeof reload_es === 'undefined' || reload_es === null) {
                        reload_es = 1;
                    }

                    let post_id = jQuery('[data-service-id]').data('service-id');
                    let pickup_date = jQuery('#pickup_date').val();
                    let dropoff_date = jQuery('#dropoff_date').val();
                    
                    let pickup_time = jQuery('#pickup_time').find(':selected').val();
                    let dropoff_time = jQuery('#dropoff_time').find(':selected').val();
                    let item_quantity = jQuery('#rbfw_item_quantity').find(':selected').val();

                    if(pickup_date == '' || dropoff_date == ''){

                        return false;
                    }
                    
                    if (typeof item_quantity === "undefined" || item_quantity == '') {

                        item_quantity = 1;
                    }

                    if((pickup_date == dropoff_date) && (typeof pickup_time === "undefined" || pickup_time == '')){
                        
                        pickup_time = '00:00';
                    }

                    if((pickup_date == dropoff_date) && (typeof dropoff_time === "undefined" || dropoff_time == '')){
                        
                        dropoff_time = rbfw_end_time();
                    } 

                    let data_cat = that.attr('data-cat');

                    if(data_cat == 'service'){

                        let data_qty         = that.attr('value');
                        let data_price        = that.attr('data-price');
                        let data_name        = that.attr('data-name');

                        if(data_qty == 0){

                            delete service_price_arr[data_name];
                        }
                        else{

                            service_price_arr[data_name]  = {'data_qty' : data_qty, 'data_price' : data_price};
                        }
                    }  



                    jQuery.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: rbfw_ajax.rbfw_ajaxurl,
                        data: {
                            'action' : 'rbfw_bikecarmd_ajax_price_calculation',
                            'post_id': post_id,
                            'pickup_date': pickup_date,
                            'pickup_time': pickup_time,
                            'dropoff_date': dropoff_date,
                            'dropoff_time': dropoff_time,
                            'item_quantity': item_quantity,
                            'service_price_arr': service_price_arr,
                            'reload_es': reload_es
                        },
                        beforeSend: function() {
                            jQuery('.rbfw_bikecarmd_price_result').empty();
                            jQuery('.rbfw_bikecarmd_price_result').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');

                            if(reload_es === 1){
                                jQuery('.rbfw-resource').empty();
                            }
                            
                            
                        },		
                        success: function (response) {

                
                            if (response.duration) {
                                jQuery('.rbfw-duration').slideDown('fast').find('.item-content').text(response.duration);
                            } else {
                                jQuery('.rbfw-duration').slideUp('fast');
                            }
                            
                            if(Object.keys(response.reload_es).length !== 0){
                                jQuery('.rbfw-quantity').slideDown('fast').html(response.item_quantity_box);
                                jQuery('#rbfw_item_quantity option[value="'+item_quantity+'"]').attr('selected','selected');
                            }

                            if (response.variation_content && Object.keys(response.variation_content).length !== 0) {
                                jQuery('.rbfw-variations-content-wrapper').slideDown('fast').html(response.variation_content);
                            }

                            if (response.extra_service_content && Object.keys(response.extra_service_content).length !== 0) {
                                jQuery('.rbfw-resource').slideDown('fast').html(response.extra_service_content);
                            }
                            
                            jQuery('.rbfw_rp_loader').hide();
                            jQuery('.rbfw_bikecarmd_price_result').html(response.content);
                            let get_total_price = jQuery('.rbfw_bikecarmd_price_summary .duration-costing .price-figure').attr('data-price');

                            if(get_total_price > 0){
                                jQuery(' button.rbfw_bikecarmd_book_now_btn').removeAttr('disabled');
                                jQuery('.rbfw_next_btn').removeAttr('disabled');
                            }
                            else{
                                jQuery(' button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                            }

                            if((response.max_available_qty == 0)) {
                                jQuery('.rbfw_nia_notice').remove();
                                jQuery('<div class="rbfw_nia_notice mps_alert_warning"><?php rbfw_string('rbfw_text_no_items_available',__('No Items Available!','booking-and-rental-manager-for-woocommerce')); ?></div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                            } else {
                                jQuery('.rbfw_nia_notice').remove();
                                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                            }
                                
                        },
                        error : function(response){
                            console.log(response);
                        }
                    });
                }

                /* End */
                <?php
                /* Start: Get Registration Form Info */
                $rbfw_regf_info = [];

                if(class_exists('Rbfw_Reg_Form')){
                    $ClassRegForm = new Rbfw_Reg_Form();
                    $rbfw_regf_info = $ClassRegForm->rbfw_get_regf_all_fields_name($post_id);
                    $rbfw_regf_info = json_encode($rbfw_regf_info);
                }
                /* End: Get Registration Form Info */
                ?>
                rbfw_mps_book_now_btn_action();
                function rbfw_mps_book_now_btn_action(){
                    jQuery('button.rbfw_bikecarmd_book_now_btn.mps_enabled').click(function (e) {
                        e.preventDefault();

                        let pickup_date = jQuery('#pickup_date').val();
                        let pickup_time = jQuery('#pickup_time').val();
                        let dropoff_date = jQuery('#dropoff_date').val();
                        let dropoff_time = jQuery('#dropoff_time').val();
                        let pickup_point = jQuery('select[name="rbfw_pickup_point"]').val();
                        let dropoff_point = jQuery('select[name="rbfw_dropoff_point"]').val();
                        let item_quantity = jQuery('select#rbfw_item_quantity').find(':selected').val();

                        let variation_fields = jQuery('.rbfw_variation_field');
                        let variation_info = {};


                        for (let index = 0; index < variation_fields.length; index++) {
                            let field_label = jQuery('select[name="rbfw_variation_id_'+index+'"]').attr('data-field');
                            let field_id = 'rbfw_variation_id_'+index;
                            let field_value = jQuery('select[name="rbfw_variation_id_'+index+'"]').val();                           
                            let data = {};
                            data['field_id'] = field_id; 
                            data['field_label'] = field_label; 
                            data['field_value'] = field_value;
                            variation_info[index] = data;
                        }

                        if (typeof item_quantity === "undefined" || item_quantity == '') {
                            item_quantity = 1;
                        }

                        if((pickup_date == dropoff_date) && (typeof pickup_time === "undefined" || pickup_time == '')){
                            pickup_time = '00:00';
                        }

                        if((pickup_date == dropoff_date) && (typeof dropoff_time === "undefined" || dropoff_time == '')){
                            dropoff_time = rbfw_end_time();
                        } 

                        let rent_type = jQuery('#rbfw_rent_type').val();
                        let post_id = jQuery('#rbfw_post_id').val();

                        let service_length = jQuery('.rbfw_bikecarmd_es_table tbody tr').length;
                        let service_array = {};

                        for (let index = 0; index < service_length; index++) {
                            let qty = jQuery('input[name="rbfw_service_info['+index+'][service_qty]"]').val();
                            let data_type = jQuery('input[name="rbfw_service_info['+index+'][service_name]"]').val();
                            if(qty > 0){
                                service_array[data_type] = qty;
                            }
                        }

                        <?php if(!empty($rbfw_regf_info)){ ?>
                        let rbfw_regf_fields = <?php echo $rbfw_regf_info; ?>;
                        <?php } else { ?>
                        let rbfw_regf_fields = {};
                        <?php } ?>
                        var rbfw_regf_info = {};

                        var rbfw_regf_checkboxes = {};
                        var rbfw_regf_radio = {};
                        var this_checkbox_arr = [];
                        var this_radio_arr = [];

                        if(rbfw_regf_fields.length > 0){
                            rbfw_regf_fields.forEach((field_name, index) => {

                                let this_field_type = jQuery('[name="'+field_name+'"]').attr('type');
                                let this_value = jQuery('[name="'+field_name+'"]').val();

                                if (typeof this_field_type === 'undefined') {

                                    this_field_type = jQuery('[name="'+field_name+'[]"]').attr('type');

                                    if(this_field_type == 'checkbox'){

                                        jQuery('.'+field_name+':checked').each(function(i){
                                            this_checkbox_arr.push(jQuery(this).val());
                                        });

                                        rbfw_regf_checkboxes[field_name] = this_checkbox_arr;
                                    }

                                    if(this_field_type == 'radio'){

                                        jQuery('.'+field_name+':checked').each(function(d){
                                            this_radio_arr.push(jQuery(this).val());
                                        });

                                        rbfw_regf_radio[field_name] = this_radio_arr;
                                    }
                                }

                                rbfw_regf_info[field_name] = this_value;
                            });
                        }

                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action' : 'rbfw_mps_user_login',
                                'post_id': post_id,
                                'rent_type': rent_type,
                                'start_date': pickup_date,
                                'start_time': pickup_time,
                                'end_date': dropoff_date,
                                'end_time': dropoff_time,
                                'pickup_point': pickup_point,
                                'dropoff_point': dropoff_point,
                                'item_quantity': item_quantity,                                
                                'service_info[]': service_array,
                                'variation_info': variation_info,
                                'rbfw_regf_info[]' : rbfw_regf_info,
                                'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                                'rbfw_regf_radio': rbfw_regf_radio
                            },
                            beforeSend: function() {

                                jQuery('.rbfw_bikecarmd_book_now_btn.mps_enabled').append('<i class="fas fa-spinner fa-spin"></i>');
                                jQuery('.rbfw_bikecarmd_backstep1_btn').remove();
                            },		
                            success: function (response) {

                                jQuery('.rbfw_bikecarmd_book_now_btn.mps_enabled i').remove();

                                var returnedData = JSON.parse(response);

                                if(returnedData.hasOwnProperty('rbfw_regf_warning') && returnedData.rbfw_regf_warning != ''){

                                    jQuery('.rbfw_regf_warning_wrap').remove();
                                    jQuery('.rbfw_bike_car_md_item_wrapper').show();
                                    jQuery('.rbfw-bikecarmd-result').append(returnedData.rbfw_regf_warning);
                                }

                                if(returnedData.hasOwnProperty('rbfw_content') && returnedData.rbfw_content != ''){

                                    jQuery('.rbfw_regf_warning_wrap').remove();
                                    jQuery('.rbfw_bike_car_md_item_wrapper').hide();

                                    jQuery('.rbfw-bikecarmd-result').append('<a class="rbfw_bikecarmd_backstep1_btn"><img src="<?php echo RBFW_PLUGIN_URL . '/assets/images/muff_edit_icon.png'; ?>"/> <?php rbfw_string('rbfw_text_change',__('Change','booking-and-rental-manager-for-woocommerce')); ?></a>');
                                    jQuery('.rbfw_bikecarmd_backstep1_btn').show();

                                    jQuery('.rbfw-bikecarmd-result').append(returnedData.rbfw_content);
                                }

                                rbfw_on_submit_user_form_action(post_id,rent_type,pickup_date,pickup_time,dropoff_date,dropoff_time,pickup_point,dropoff_point,service_array,item_quantity,variation_info,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio);
                            },
                            complete:function(response) {
                                jQuery('html, body').animate({
                                    scrollTop: jQuery(".rbfw-bikecarmd-result-wrap").offset().top
                                }, 100);   
                            }
                        });
                    });
                }

                function rbfw_on_submit_user_form_action(post_id,rent_type,pickup_date,pickup_time,dropoff_date,dropoff_time,pickup_point,dropoff_point,service_array,item_quantity,variation_info,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio){

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
                                jQuery('button.rbfw_bikecarmd_book_now_btn.mps_enabled').trigger('click');
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
                                'start_date': pickup_date,
                                'start_time': pickup_time,
                                'end_date': dropoff_date,
                                'end_time': dropoff_time,
                                'pickup_point': pickup_point,
                                'dropoff_point': dropoff_point,
                                'item_quantity': item_quantity,
                                'service_info[]': service_array,
                                'security' : security,
                                'first_name' : first_name,
                                'last_name' : last_name,
                                'email' : email,
                                'payment_method' : payment_method,
                                'submit_request' : submit_request,
                                'variation_info' : variation_info,
                                'rbfw_regf_info[]' : rbfw_regf_info,
                                'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                                'rbfw_regf_radio': rbfw_regf_radio
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
                                'start_date': pickup_date,
                                'start_time': pickup_time,
                                'end_date': dropoff_date,
                                'end_time': dropoff_time,
                                'pickup_point': pickup_point,
                                'dropoff_point': dropoff_point,
                                'item_quantity': item_quantity,
                                'service_info[]': service_array,
                                'security' : security,
                                'first_name' : first_name,
                                'last_name' : last_name,
                                'email' : email,
                                'payment_method' : payment_method,
                                'submit_request' : submit_request,
                                'variation_info' : variation_info,
                                'rbfw_regf_info[]' : rbfw_regf_info,
                                'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                                'rbfw_regf_radio': rbfw_regf_radio
                            },
                            beforeSend: function(response) {
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
                    

                    jQuery('.rbfw_mps_header_action_link').click(function (e) { 
                        e.preventDefault();
                        jQuery('.rbfw_mps_user_form_result').empty();
                        jQuery('.rbfw_mps_form_wrap').hide();
                        let this_data_id = jQuery(this).attr('data-id');
                        jQuery('.rbfw_mps_form_wrap[data-id="'+this_data_id+'"]').show();
                    });
                    
                }

                jQuery(document).on('click', '.rbfw_next_btn:not(.rbfw_next_btn[disabled]), .rbfw_prev_btn', function(e) {
                    e.preventDefault();

                    let pickup_date = jQuery('#pickup_date').val();
                    let dropoff_date = jQuery('#dropoff_date').val();
                    let pickup_time = jQuery('#pickup_time').val();
                    let dropoff_time = jQuery('#dropoff_time').val();
                    let step = 3;

                    if(typeof pickup_time === 'undefined' && typeof dropoff_time === 'undefined'){
                        step = 2;
                    } else {
                        step = 3;
                    }
                    jQuery('.rbfw_muff_selected_date').remove();
                    let the_html = '';
                    the_html += '<div class="rbfw_step_selected_date rbfw_muff_selected_date" step="'+step+'" data-type="bike_car_md">';

                    the_html += '<div class="rbfw_muff_selected_date_col"><label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_calendar_icon2.png"/><?php echo rbfw_string_return('rbfw_text_pickup_date',__('Pickup date','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+pickup_date+'</span> <label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_calendar_icon2.png"/><?php echo rbfw_string_return('rbfw_text_dropoff_date',__('Drop-off date','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+dropoff_date+'</span></div>';

                    if(typeof pickup_time !== 'undefined' && typeof dropoff_time !== 'undefined'){

                        the_html += '<div class="rbfw_muff_selected_date_col"><label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_clock_icon2.png"/><?php echo rbfw_string_return('rbfw_text_pickup_time',__('Pickup time','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+pickup_time+'</span> <label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_clock_icon2.png"/><?php echo rbfw_string_return('rbfw_text_dropoff_time',__('Drop-off time','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+dropoff_time+'</span></div>';

                    }

                    the_html += '</div>';
                    console.log(the_html);
                    jQuery('.rbfw_bikecarmd_price_result').prepend(the_html);
                    jQuery(".rbfw_bike_car_md_item_wrapper_inner").slideToggle();
                    jQuery(".rbfw_bikecarmd_price_summary").slideToggle();
                    jQuery(".rbfw_regf_wrap").slideToggle();
                    jQuery(".rbfw_next_btn").slideToggle();
                    jQuery(".rbfw_prev_btn").toggleClass('rbfw_d_block');
                    jQuery(".rbfw_muff_registration_wrapper .rbfw_mps_book_now_btn_regf").slideToggle();
                    jQuery(".rbfw_regf_warning_wrap").remove();
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw_muff_registration_wrapper .rbfw_muff_heading").offset().top
                    }, 5);
                });

                jQuery(document).on('click', '.rbfw_bikecarmd_backstep1_btn', function(e) {
                    e.preventDefault();

                    jQuery(".rbfw_bike_car_md_item_wrapper").slideToggle();
                    jQuery(".rbfw_bike_car_md_item_wrapper_inner").slideToggle();
                    jQuery(".rbfw_bikecarmd_price_summary").slideToggle();
                    jQuery(".rbfw_regf_wrap").hide();
                    jQuery(".rbfw_next_btn").slideToggle();
                    jQuery(".rbfw_prev_btn").toggleClass('rbfw_d_block');
                    jQuery(".rbfw_muff_registration_wrapper .rbfw_mps_book_now_btn_regf").slideToggle();
                    jQuery(".rbfw_regf_warning_wrap").remove();
                    jQuery(".rbfw-bikecarmd-result").empty();
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw_muff_registration_wrapper .rbfw_muff_heading").offset().top
                    }, 5);
                });
            </script>
            <?php
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