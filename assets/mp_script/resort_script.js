let rbfw_today_booking_enable = jQuery('.rbfw_today_booking_enable').val();
let room_prices_arr = {};
let service_prices_arr = {};

jQuery('body').on('focusin', '#checkin_date', function(e) {
    jQuery(this).datepicker({
        dateFormat: js_date_format,
        minDate: 0,
        beforeShowDay: function(date)
        {
            return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
        },
        onSelect: function (dateString, data) {
            let date_ymd_drop = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_start_datetime"]').val(date_ymd_drop).trigger('change');
        },
    });
});

jQuery('body').on('change', '#hidden_checkin_date', function(e) {
    let selected_date = jQuery(this).val();
    const [gYear, gMonth, gDay] = selected_date.split('-');
    let rbfw_enable_resort_daylong_price = jQuery('#rbfw_enable_resort_daylong_price').val();

    if(rbfw_enable_resort_daylong_price=='no'){
        var extra_day = 1;
    }else {
        var extra_day = 0;
    }

    jQuery("#checkout_date").datepicker("destroy");
    jQuery("#checkout_date").val('');
    jQuery("#checkout_date").attr('value', '');
    jQuery('#checkout_date').datepicker({
        dateFormat: js_date_format,
        minDate: new Date(gYear, gMonth - 1 , parseInt(gDay) + extra_day),
        beforeShowDay: function(date)
        {
            return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
        },
        onSelect: function (dateString, data) {
            let date_ymd_drop = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_end_datetime"]').val(date_ymd_drop).trigger('change');
        },
    });
});


// end check-in date picker

// resort check availability ajax
jQuery(document).on('click','.rbfw_chk_availability_btn',function(e) {
    e.preventDefault();
    let checkin_date_notice 	= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_checkin_date', 'rbfw_basic_translation_settings', __('Please Choose Check-In Date','booking-and-rental-manager-for-woocommerce'))); ?>";
    let checkout_date_notice 	= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_checkout_date', 'rbfw_basic_translation_settings', __('Please Choose Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>";
    let checkin_date 			= jQuery('#hidden_checkin_date').val();
    let checkout_date 			= jQuery('#hidden_checkout_date').val();
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
                'rbfw_enable_resort_daylong_price': rbfw_enable_resort_daylong_price,
                'nonce' : rbfw_ajax.nonce
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

function rbfw_resort_get_price_table(){
    let active_tab_value = jQuery('.rbfw_room_price_category_tabs').attr('data-active');
    let post_id 		 = jQuery('#rbfw_post_id').val();
    let checkin_date     = jQuery('#hidden_checkin_date').val();
    let checkout_date    = jQuery('#hidden_checkout_date').val();
    jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'        : 'rbfw_get_active_price_table',
            'post_id'       : post_id,
            'active_tab'    : active_tab_value,
            'checkin_date'  : checkin_date,
            'checkout_date' : checkout_date,
            'nonce' : rbfw_ajax.nonce
        },
        beforeSend: function() {
            jQuery('.rbfw_room_price_category_details').empty();
            jQuery('.rbfw_room_price_category_details_loader').css("display","block");
        },
        success: function (response) {
            jQuery('.rbfw_room_price_category_details_loader').hide();
            jQuery('.rbfw_room_price_category_details').html(response);
            rbfw_mps_book_now_btn_action();
            rbfw_display_resort_es_box_onchange_onclick();
            jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();
        }
    });
}

jQuery(document).on('change','.rbfw_room_qty,.rbfw_service_qty',function (e) {

    let checkin_date     = jQuery('#hidden_checkin_date').val();
    let checkout_date    = jQuery('#hidden_checkout_date').val();
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
            'service_price_arr': service_prices_arr,
            'nonce' : rbfw_ajax.nonce
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
    let checkin_date     = jQuery('#hidden_checkin_date').val();
    let checkout_date    = jQuery('#hidden_checkout_date').val();
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
            'service_price_arr': service_prices_arr,
            'nonce' : rbfw_ajax.nonce
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


function rbfw_mps_checkout_header_link(){
    jQuery('.rbfw_mps_header_action_link').click(function (e) {
        e.preventDefault();
        jQuery('.rbfw_mps_user_form_result').empty();
        jQuery('.rbfw_mps_form_wrap').hide();
        let this_data_id = jQuery(this).attr('data-id');
        jQuery('.rbfw_mps_form_wrap[data-id="'+this_data_id+'"]').show();
    });
}