let rbfw_today_booking_enable = jQuery('.rbfw_today_booking_enable').val();
let rbfw_enable_resort_daylong_price = jQuery('#rbfw_enable_resort_daylong_price').val();


let room_prices_arr = {};
let service_prices_arr = {};

jQuery('#checkin_date').datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: 0,
    beforeShowDay: function(date)
    {
        return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
    }
});

jQuery('#checkin_date').change(function(e) {

    let selected_date = jQuery(this).val();
    const [gYear, gMonth, gDay] = selected_date.split('-');
    if(rbfw_enable_resort_daylong_price=='no'){
         var extra_day = 1;
    }else {
        var extra_day = 0;
    }

    jQuery("#checkout_date").datepicker("destroy");
    jQuery("#checkout_date").val('');
    jQuery("#checkout_date").attr('value', '');
    jQuery('#checkout_date').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: new Date(gYear, gMonth - 1, parseInt(gDay) + extra_day),
        beforeShowDay: function(date)
        {
            return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
        }
    });
});

// end check-in date picker

// resort check availability ajax
jQuery(document).on('click','.rbfw_chk_availability_btn',function(e) {
    e.preventDefault();
    let checkin_date_notice 	= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_checkin_date', 'rbfw_basic_translation_settings', __('Please Choose Check-In Date','booking-and-rental-manager-for-woocommerce'))); ?>";
    let checkout_date_notice 	= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_checkout_date', 'rbfw_basic_translation_settings', __('Please Choose Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>";
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

    let rbfw_enable_resort_daylong_price = jQuery('#rbfw_enable_resort_daylong_price').val();
    let is_muffin_template = jQuery('.rbfw_muffin_template').length;
    if(is_muffin_template > 0){
        is_muffin_template = '1';
    } else {
        is_muffin_template = '0';
    }

        jQuery.ajax({
            type: 'POST',
            url: rbfw_ajax.rbfw_ajaxurl,
            data: {
                'action' 		: 'rbfw_check_resort_availibility',
                'post_id' 		: post_id,
                'checkin_date' 	: checkin_date,
                'checkout_date' : checkout_date,
                'is_muffin_template': is_muffin_template,
                'rbfw_enable_resort_daylong_price': rbfw_enable_resort_daylong_price
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
            }
        });
});



jQuery(document).on('click','.rbfw_room_price_label',function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let active_value = jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label.active .rbfw_room_price_category').val();
    jQuery('.rbfw_room_price_category_tabs').attr('data-active',active_value);
    tippy('.rbfw_room_price_label.disabled', {content: 'Not Available!',theme: 'blue',placement: 'top'});
    let target_label = jQuery(this);
    let target_value = jQuery(this).find('.rbfw_room_price_category').val();
    if(jQuery(target_label).hasClass('disabled')){
        return false;
    }
    jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label').removeClass('active');
    jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label .rbfw_room_price_category').prop('checked',false);
    jQuery(this).addClass('active');
    jQuery('.rbfw_room_price_category_tabs .rbfw_room_price_label i').remove();
    jQuery(this).append('<i class="fa-solid fa-check"></i>');
    jQuery(this).find('.rbfw_room_price_category').prop('checked',true);
    jQuery('.rbfw_room_price_category_tabs').attr('data-active',target_value);
    rbfw_resort_get_price_table();
});
    // end onclick resort price button





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
            //rbfw_update_input_value_onchange_onclick();
           // rbfw_room_price_calculation();
            rbfw_mps_book_now_btn_action();
            rbfw_display_resort_es_box_onchange_onclick();
            jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();
        }
    });
}

jQuery(document).on('change','.rbfw_room_qty,.rbfw_service_qty',function (e) {

    let checkin_date     = jQuery('#checkin_date').val();
    let checkout_date    = jQuery('#checkout_date').val();
    let data_cat         = jQuery(this).attr('data-cat');
    console.log('data_cat',data_cat);
    if(data_cat == 'room'){
        let data_qty         = jQuery(this).val();
        let data_price       = jQuery(this).attr('data-price');
        let data_type        = jQuery(this).attr('data-type');
        if(data_qty == 0){
            delete room_prices_arr[data_type];
        }
        else{
            room_prices_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
        }
    }

    if(data_cat == 'service'){
        let data_qty         = jQuery(this).val();
        let data_price       = jQuery(this).attr('data-price');
        let data_type        = jQuery(this).attr('data-type');
        if(data_qty == 0){
            delete service_prices_arr[data_type];
        } else{
            service_prices_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
        }
    }

    jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'        : 'rbfw_room_price_calculation',
            'checkin_date'  : checkin_date,
            'checkout_date' : checkout_date,
            'room_price_arr': room_prices_arr,
            'service_price_arr': service_prices_arr
        },
        beforeSend: function() {
            jQuery('.rbfw_room_price_summary').empty();
            jQuery('.rbfw_room_price_category_details').addClass('rbfw_loader_in');
            jQuery('.rbfw_room_price_category_details').append('<i class="fas fa-spinner fa-spin"></i>');
            },
        success: function (response) {


            jQuery('.rbfw_room_price_category_details').removeClass('rbfw_loader_in');
            jQuery('.rbfw_room_price_category_details i.fa-spinner').remove();

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

jQuery(document).on('click','.rbfw_room_qty_plus',function (e) {
    e.preventDefault();
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

jQuery(document).on('click','.rbfw_room_qty_minus,.rbfw_service_qty_minus',function (e) {
    let target_input = jQuery(this).siblings("input[type=number]");
    let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
    let update_value = current_value - 1;
    if(current_value > 0){
        jQuery(target_input).val(update_value);
        jQuery(target_input).attr('value',update_value);
    }
});

jQuery(document).on('click','.rbfw_room_qty_plus,.rbfw_room_qty_minus,.rbfw_service_qty_minus,.rbfw_service_qty_plus',function (e) {

    e.preventDefault();

    let post_id = jQuery('#rbfw_post_id').val();
    let checkin_date     = jQuery('#checkin_date').val();
    let checkout_date    = jQuery('#checkout_date').val();
    let data_cat         = jQuery(this).siblings('input[type=number]').attr('data-cat');
    console.log('ffff',data_cat)
    if(data_cat == 'room'){
        let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
        let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
        let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
        if(data_qty == 0){
            delete room_prices_arr[data_type];
        }
        else{
            room_prices_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
        }
    }
    if(data_cat == 'service'){
        let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
        let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
        let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
        if(data_qty == 0){
            delete service_prices_arr[data_type];
        }
        else{
            service_prices_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
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
            'room_price_arr': room_prices_arr,
            'service_price_arr': service_prices_arr
        },
        beforeSend: function() {
            jQuery('.rbfw_room_price_summary').empty();
            jQuery('.rbfw_room_price_category_details').addClass('rbfw_loader_in');
            jQuery('.rbfw_room_price_category_details').append('<i class="fas fa-spinner fa-spin"></i>');
           // jQuery('.rbfw_room_price_summary').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');
        },
        success: function (response) {


            jQuery('.rbfw_room_price_category_details').removeClass('rbfw_loader_in');
            jQuery('.rbfw_room_price_category_details i.fa-spinner').remove();
            //jQuery('.rbfw_rp_loader').hide();

            jQuery('.rbfw_room_price_summary').html(response);
            let get_total_price = jQuery('.rbfw_room_price_summary .duration-costing .price-figure').attr('data-price');
            if(get_total_price > 0){
                jQuery(' button.rbfw_resort_book_now_btn').removeAttr('disabled');
            }else{
                jQuery(' button.rbfw_resort_book_now_btn').attr('disabled',true);
            }

        }
    });
});

jQuery(document).on('change','.rbfw_service_qty',function (e) {
    let get_value = jQuery(this).val();
    let max_value = parseInt(jQuery(this).attr('max'));

    if(get_value <= max_value){
        jQuery(this).val(get_value);
        jQuery(this).attr('value',get_value);
    }else{
        jQuery(this).val(max_value);
        jQuery(this).attr('value',max_value);
        let notice = "Available Quantity is ";
        tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top'});
    }
});




    jQuery(document).on('click','.rbfw_room_qty_plus,.rbfw_room_qty_minus',function (e) {
        e.preventDefault();
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

// end update input value onclick and onchange

// display extra services box onclick and onchange
function rbfw_display_resort_es_box_onchange_onclick(){



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

        /*   <?php if(!empty($rbfw_regf_info)){ ?>
               let rbfw_regf_fields = <?php echo $rbfw_regf_info; ?>;
           <?php } else { ?>
               let rbfw_regf_fields = {};
           <?php } ?>*/

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
                'start_date': start_date,
                'end_date': end_date,
                'package': package,
                'type_info[]': type_array,
                'service_info[]': service_array,
                'rbfw_regf_info[]' : rbfw_regf_info,
                'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                'rbfw_regf_radio': rbfw_regf_radio

            },
            beforeSend: function() {

                jQuery('.rbfw_resort_book_now_btn.mps_enabled').append('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function (response) {

                jQuery('.rbfw_resort_book_now_btn.mps_enabled i').remove();

                var returnedData = JSON.parse(response);

                if(returnedData.hasOwnProperty('rbfw_regf_warning') && returnedData.rbfw_regf_warning != ''){
                    jQuery('.rbfw_resort_item_wrapper').show();
                    jQuery('.rbfw_regf_warning_wrap').remove();
                    jQuery('.rbfw-resort-result').append(returnedData.rbfw_regf_warning);
                }

                if(returnedData.hasOwnProperty('rbfw_content') && returnedData.rbfw_content != ''){
                    jQuery('.rbfw_resort_item_wrapper').hide();
                    jQuery('.rbfw_regf_warning_wrap').remove();
                    jQuery('.rbfw-resort-result').append(returnedData.rbfw_content);
                }

                rbfw_on_submit_user_form_action(post_id,rent_type,start_date,end_date,package,type_array,service_array,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio);
                rbfw_mps_checkout_header_link();
            }
        });

    });
}

function rbfw_on_submit_user_form_action(post_id,rent_type,start_date,end_date,package,type_array,service_array,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio){
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
                    'submit_request' : submit_request,
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
                    'submit_request' : submit_request,
                    'rbfw_regf_info[]' : rbfw_regf_info,
                    'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                    'rbfw_regf_radio': rbfw_regf_radio
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