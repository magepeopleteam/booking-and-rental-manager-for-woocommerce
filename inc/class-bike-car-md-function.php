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

            $service_cost = 0;
            $post_id = $_POST['post_id'];
            $start_date = $_POST['pickup_date'];
            $end_date = $_POST['dropoff_date'];
            $star_time = isset($_POST['pickup_time'])?$_POST['pickup_time']:'';
            $end_time = isset($_POST['dropoff_time'])?$_POST['dropoff_time']:'';

            if (empty($star_time) && empty($end_time)) {
                $pickup_datetime = date('Y-m-d', strtotime($start_date . ' ' . '00:00:00'));
                $dropoff_datetime = date('Y-m-d', strtotime($end_date . ' ' . rbfw_end_time()));
            } else {
                $pickup_datetime = date('Y-m-d H:i', strtotime($start_date . ' ' . $star_time));
                $dropoff_datetime = date('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));
            }
            $item_quantity = $_POST['item_quantity'];
            $rbfw_enable_variations = $_POST['rbfw_enable_variations'];
            $rbfw_service_price = $_POST['rbfw_service_price']*$item_quantity;
            $service_price_arr = !empty($_POST['service_price_arr']) ? $_POST['service_price_arr'] : [];

            $diff = date_diff(new DateTime($pickup_datetime), new DateTime($dropoff_datetime));
            $total_days = $diff->days;

            $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date);
            $duration_price = rbfw_md_duration_price_calculation($post_id,$pickup_datetime,$dropoff_datetime,$start_date,$star_time,$end_time)*$item_quantity;

            $rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

            if(!empty($service_price_arr)){
                foreach ($service_price_arr as $data_name => $values) {
                    if($item_quantity > 1 && (int)$values['data_qty'] == 1 && $rbfw_enable_extra_service_qty != 'yes'){
                        $service_cost += $item_quantity * (float)$values['data_price'];
                    } else {
                        $service_cost += (int)$values['data_qty'] * (float)$values['data_price'];
                    }
                }
            }

            $sub_total_price = $duration_price + $service_cost+$rbfw_service_price;

            $total_price = $sub_total_price;

            $discount_desc = 0;

            if (is_plugin_active('booking-and-rental-manager-discount-over-x-days/rent-discount-over-x-days.php')){


                if(empty($star_time) && empty($end_time)){
                    $pickup_datetime  = date( 'Y-m-d', strtotime( $start_date.' '.'00:00:00' ) );
                    $dropoff_datetime = date( 'Y-m-d', strtotime( $end_date.' '.rbfw_end_time() ) );
                } else {
                    $pickup_datetime  = date( 'Y-m-d H:i', strtotime( $start_date . ' ' . $star_time ) );
                    $dropoff_datetime = date( 'Y-m-d H:i', strtotime( $end_date . ' ' . $end_time ) );


                }
                $pickup_datetime  = new DateTime( $pickup_datetime );
                $dropoff_datetime = new DateTime( $dropoff_datetime );

                if(function_exists('rbfw_get_discount_array')){
                    $discount_arr = rbfw_get_discount_array($post_id, $pickup_datetime, $dropoff_datetime, $sub_total_price);
                } else {
                    $discount_arr = [];
                }

                if(!empty($discount_arr)){
                    $total_price = $discount_arr['total_amount'];
                    $discount_type = $discount_arr['discount_type'];
                    $discount_amount = $discount_arr['discount_amount'];
                    $discount_desc = $discount_arr['discount_desc'];
                }
            }

            $hours    = 0;
            $duration = '';
            if ( $diff ) {
                $days    = $diff->days;
                $hours   += $diff->h;
                if ( $days > 0 ) {
                    $duration .= $days > 1 ? $days.' '.rbfw_string_return('rbfw_text_days',__('Days','booking-and-rental-manager-for-woocommerce')).' ' : $days.' '.rbfw_string_return('rbfw_text_day',__('Day','booking-and-rental-manager-for-woocommerce')).' ';
                }
                if ( $hours > 0 ) {
                    $duration .= $hours > 1 ? $hours.' '.rbfw_string_return('rbfw_text_hours',__('Hours','booking-and-rental-manager-for-woocommerce')) : $hours.' '.rbfw_string_return('rbfw_text_hour',__('Hour','booking-and-rental-manager-for-woocommerce'));
                }
            }

            echo json_encode( array(
                'duration_price' => $duration_price,
                'duration_price_html' => wc_price($duration_price),
                'rbfw_service_price' => $rbfw_service_price,
                'rbfw_service_price_html' => wc_price($rbfw_service_price),
                'service_cost' => $service_cost+$rbfw_service_price,
                'service_cost_html' => wc_price($service_cost+$rbfw_service_price),
                'sub_total_price_html' => wc_price($sub_total_price),
                'discount' => $discount_desc,
                'total_price' => $total_price,
                'total_price_html' => wc_price($total_price),
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

                            beforeShowDay: function(date)
                            {
                                return rbfw_off_day_dates(date,'md','yes');
                            }
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


                }

                // On change quantity value calculate price
                


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


                    if(typeof pickup_time !== 'undefined' && typeof dropoff_time !== 'undefined'){

                        the_html += '<div class="rbfw_muff_selected_date_col"><label><i class="fa-solid fa-clock"></i> <?php echo rbfw_string_return('rbfw_text_pickup_time',__('Pickup time','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+pickup_time+'</span></div>'
                        the_html +='<div class="rbfw_muff_selected_date_col"><label><i class="fa-solid fa-clock"></i> <?php echo rbfw_string_return('rbfw_text_dropoff_time',__('Drop-off time','booking-and-rental-manager-for-woocommerce')); ?></label><span class="rbfw_muff_selected_date_value">'+dropoff_time+'</span></div>';

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