/*start single day and appointment pricing booking*/

/* Start Calendar Script */
jQuery(function(){

    var defaultConfig = {
        weekDayLength: 1,
        onClickDate: onclick_cal_date,
        showYearDropdown: true,
        startOnMonday: true,
        showTodayButton: false,
        highlightSelectedWeekday: false,
        highlightSelectedWeek: false,
        prevButton: '<i class="fa-solid fa-circle-chevron-left"></i>',
        nextButton: '<i class="fa-solid fa-circle-chevron-right"></i>',
        disable: function (date) {
            return rbfw_off_day_dates(date);

        },
        customDateProps: (date) => ({
            classes: 'rbfw-date-element',
            data: {
                type: 'date',
                form: 'date-object'
            }
        })
    };

    var calendar = jQuery('#rbfw-bikecarsd-calendar').calendar(defaultConfig);

    let rent_type = jQuery('#rbfw_rent_type').val();
    // Start: Calendar script
    if(rent_type == 'appointment'){
        let rbfw_date_element_arr = [];
        let rbfw_date_element = jQuery('.rbfw-date-element');
        let rbfw_calendar_weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
        let appointment_days = jQuery('#appointment_days').val();


        jQuery(rbfw_date_element).each(function($i){
            let this_data = jQuery(this);
            let this_date_data = jQuery(this).attr('data-date');
            let this_calendar_date = new Date(this_date_data);
            let this_calendar_day_name = rbfw_calendar_weekday[this_calendar_date.getDay()];
            if (appointment_days.indexOf(this_calendar_day_name) < 0) {
                this_data.attr('disabled', true);

            }
        });
    }
    /* End Calendar Script */
});

function onclick_cal_date(date) {

    jQuery('#rbfw-bikecarsd-calendar').updateCalendarOptions({
        date: date
    });
    let d = new Date(date);
    let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
    let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(d);
    let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);
    let s_Date = ye+'-'+mo+'-'+da;
    jQuery('#rbfw_bikecarsd_selected_date').val(s_Date);
    let post_id = jQuery('#rbfw_post_id').val();
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
            'action' : 'rbfw_bikecarsd_time_table',
            'post_id': post_id,
            'selected_date': s_Date,
            'is_muffin_template': is_muffin_template
        },
        beforeSend: function() {

            jQuery('.rbfw-bikecarsd-result').empty();
            jQuery('.rbfw_bikecarsd_time_table_container').remove();
            jQuery('.rbfw-bikecarsd-step[data-step="1"]').addClass('rbfw_loader_in');
            jQuery('.rbfw-bikecarsd-step[data-step="1"]').append('<i class="fas fa-spinner fa-spin"></i>');

            var rent_type = jQuery('#rbfw_rent_type').val();

            // Start: Calendar script
           if(rent_type == 'appointment'){
                let rbfw_date_element_arr = [];
                let rbfw_date_element = jQuery('.rbfw-date-element');
                let rbfw_calendar_weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                let appointment_days = jQuery('#appointment_days').val();
                jQuery(rbfw_date_element).each(function($i){
                    let this_data = jQuery(this);
                    let this_date_data = jQuery(this).attr('data-date');
                    let this_calendar_date = new Date(this_date_data);
                    let this_calendar_day_name = rbfw_calendar_weekday[this_calendar_date.getDay()];
                    if (appointment_days.indexOf(this_calendar_day_name) < 0) {
                        this_data.attr('disabled', true);
                    }
                });
           }
            /* End Calendar Script */
        },
        success: function (response) {
            jQuery('.rbfw-bikecarsd-step[data-step="1"]').hide();
            jQuery('.rbfw-bikecarsd-step[data-step="1"]').removeClass('rbfw_loader_in');
            jQuery('.rbfw-bikecarsd-step[data-step="1"] i.fa-spinner').remove();

            jQuery('.rbfw-bikecarsd-result').append(response);

            rbfw_step_func();

            var time_slot_switch = jQuery('#time_slot_switch').val();

            if(time_slot_switch == 'on'){
                rbfw_bikecarsd_time_click_func();
            }

            if(time_slot_switch == 'off'){
                rbfw_bikecarsd_without_time_func();
            }
        },
        complete:function(data) {
            jQuery('html, body').animate({
                scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
            }, 100);
        }
    });

}


function rbfw_step_func(){
    jQuery('.rbfw_back_step_btn').click(function (e) {

        let back_step = jQuery(this).attr('back-step');
        let current_step = jQuery(this).attr('data-step');
        jQuery('.rbfw-bikecarsd-step[data-step="'+current_step+'"]').hide();
        jQuery('.rbfw-bikecarsd-step[data-step="'+back_step+'"]').show();
    });
}

function rbfw_bikecarsd_time_click_func(){
    jQuery('.rbfw_bikecarsd_time:not(.rbfw_bikecarsd_time.disabled)').click(function (e) {
        jQuery('.rbfw_bikecarsd_time').removeClass('selected');
        jQuery(this).addClass('selected');
        let gTime = jQuery(this).attr('data-time');
        jQuery('#rbfw_bikecarsd_selected_time').val(gTime);
        let selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
        let post_id = jQuery('#rbfw_post_id').val();
        let rent_type = jQuery('#rbfw_rent_type').val();
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
                'action' : 'rbfw_bikecarsd_type_list',
                'post_id': post_id,
                'selected_time': gTime,
                'selected_date': selected_date,
                'is_muffin_template': is_muffin_template
            },
            beforeSend: function() {

                jQuery('.rbfw_bikecarsd_time_table_wrap').addClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_time_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');

                if( rent_type == 'appointment' ){

                    jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                    jQuery('.rbfw_bikecarsd_price_summary.old').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_price_summary.old').append('<i class="fas fa-spinner fa-spin"></i>');
                }
            },
            success: function (response) {

                if( rent_type == 'bike_car_sd' ){

                    jQuery('.rbfw-bikecarsd-step[data-step="2"]').hide();
                }

                jQuery('.rbfw_bikecarsd_time_table_wrap').removeClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_time_table_wrap i.fa-spinner').remove();
                jQuery('.rbfw_bikecarsd_pricing_table_container').remove();
                jQuery('.rbfw-bikecarsd-result').append(response);

                if( rent_type == 'appointment' ){

                    jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_back_step_btn').hide();
                    jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_step_selected_date').hide();
                    let selected_time = jQuery('#rbfw_bikecarsd_selected_time').val();
                    jQuery('.rbfw-bikecarsd-step[data-step="2"] .rbfw_step_selected_date span.rbfw_selected_time').remove();
                    jQuery('.rbfw-bikecarsd-step[data-step="2"] .rbfw_step_selected_date').append('<span class="rbfw_selected_time"> '+selected_time+'</span>');
                }

                rbfw_update_input_value_onchange_onclick();

                rbfw_bikecarsd_ajax_price_calculation();
                rbfw_step_func();
                rbfw_display_es_box_onchange_onclick();

                rbfw_mps_book_now_btn_action();
                rbfw_mps_direct_checkout();

                jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();
            },
            complete:function(response) {
                jQuery('html, body').animate({
                    scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                }, 100);
            }
        });
    });
}

function rbfw_bikecarsd_without_time_func(){

    let selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
    let post_id = jQuery('#rbfw_post_id').val();

    jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarsd_type_list',
            'post_id': post_id,
            'selected_date': selected_date
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarsd_pricing_table_container').remove();
            jQuery('.rbfw-bikecarsd-result-loader').show().html('<i class="fas fa-spinner fa-spin"></i>');
            jQuery('.rbfw-bikecarsd-step[data-step="2"]').hide();

        },
        success: function (response) {
            jQuery('.rbfw-bikecarsd-result-loader').hide();

            jQuery('.rbfw-bikecarsd-result').append(response);
            rbfw_update_input_value_onchange_onclick();

            rbfw_bikecarsd_ajax_price_calculation();
            rbfw_step_func();
            rbfw_display_es_box_onchange_onclick();
            rbfw_mps_book_now_btn_action();


            jQuery('.rbfw_back_step_btn').attr('back-step','1');

            jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();

        },
        complete:function(response) {
            jQuery('html, body').animate({
                scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
            }, 100);
        }
    });

}

// update input value onclick and onchange
function rbfw_update_input_value_onchange_onclick(){
    jQuery('.rbfw_bikecarsd_qty_plus,.rbfw_service_qty_plus').click(function (e) {
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
    jQuery('.rbfw_bikecarsd_qty_minus,.rbfw_service_qty_minus').click(function (e) {
        let target_input = jQuery(this).siblings("input[type=number]");
        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
        let update_value = current_value - 1;
        if(current_value > 0){
            jQuery(target_input).val(update_value);
            jQuery(target_input).attr('value',update_value);
        }
    });
    jQuery('.rbfw_bikecarsd_qty,.rbfw_service_qty').change(function (e) {
        let get_value = jQuery(this).val();
        let max_value = parseInt(jQuery(this).attr('max'));

        if(get_value <= max_value){
            jQuery(this).val(get_value);
            jQuery(this).attr('value',get_value);
        }else{
            jQuery(this).val(max_value);
            jQuery(this).attr('value',max_value);
            let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
            tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top'});
        }
    });
}

// On change quantity value calculate price
function rbfw_bikecarsd_ajax_price_calculation(){
    let bikecarsd_price_arr = {};
    let service_price_arr = {};

    jQuery('.rbfw_bikecarsd_qty_plus,.rbfw_bikecarsd_qty_minus,.rbfw_service_qty_minus,.rbfw_service_qty_plus').click(function (e) {



        let data_cat = jQuery(this).siblings('input[type=number]').attr('data-cat');
        if(data_cat == 'bikecarsd'){
            let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
            let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
            let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
            if(data_qty == 0){
                delete bikecarsd_price_arr[data_type];
            }
            else{
                bikecarsd_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
            }
        }
        if(data_cat == 'service'){
            let data_qty         = jQuery(this).siblings('input[type=number]').attr('value');
            let data_price       = jQuery(this).siblings('input[type=number]').attr('data-price');
            let data_type        = jQuery(this).siblings('input[type=number]').attr('data-type');
            if(data_qty == 0){
                delete service_price_arr[data_type];
            }
            else{
                service_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
            }
        }
        let post_id = jQuery('#rbfw_post_id').val();
        var currentRequest = null;
 
            currentRequest = jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action'        : 'rbfw_bikecarsd_ajax_price_calculation',
                    'post_id': post_id,
                    'bikecarsd_price_arr': bikecarsd_price_arr,
                    'service_price_arr': service_price_arr
                },
                beforeSend: function() {
                    if(currentRequest != null) {
                        currentRequest.abort();
                    }
                    jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                    jQuery('.rbfw_bikecarsd_price_summary.old').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_price_summary.old').append('<i class="fas fa-spinner fa-spin"></i>');
                    jQuery(' button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
                },
                success: function (response) {

                    jQuery(response).insertAfter('.rbfw_bikecarsd_price_summary.old');
                    jQuery('.rbfw_bikecarsd_price_summary.old').remove();
                    let get_total_price = jQuery('.rbfw_bikecarsd_price_summary .duration-costing .price-figure').attr('data-price');
                    if(get_total_price > 0){
                        jQuery(' button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
                    }
                    else{
                        jQuery(' button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
                    }
                }
            });


    });
    jQuery('.rbfw_bikecarsd_qty,.rbfw_service_qty').change(function (e) {
        let data_cat         = jQuery(this).attr('data-cat');
        if(data_cat == 'bikecarsd'){
            let data_qty         = jQuery(this).attr('value');
            let data_price       = jQuery(this).attr('data-price');
            let data_type        = jQuery(this).attr('data-type');
            if(data_qty == 0){
                delete bikecarsd_price_arr[data_type];
            }
            else{
                bikecarsd_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
            }
        }
        if(data_cat == 'service'){
            let data_qty         = jQuery(this).attr('value');
            let data_price       = jQuery(this).attr('data-price');
            let data_type        = jQuery(this).attr('data-type');
            if(data_qty == 0){
                delete service_price_arr[data_type];
            }
            else{
                service_price_arr[data_type]  = {'data_qty' : data_qty,'data_price' : data_price,'data_type' : data_type};
            }
        }
        jQuery.ajax({
            type: 'POST',
            url: rbfw_ajax.rbfw_ajaxurl,
            data: {
                'action'        : 'rbfw_bikecarsd_ajax_price_calculation',
                'bikecarsd_price_arr': bikecarsd_price_arr,
                'service_price_arr': service_price_arr
            },
            beforeSend: function() {
                jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                jQuery('.rbfw_bikecarsd_price_summary.old').addClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_price_summary.old').append('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                jQuery(response).insertAfter('.rbfw_bikecarsd_price_summary.old');
                jQuery('.rbfw_bikecarsd_price_summary.old').remove();
                let get_total_price = jQuery('.rbfw_bikecarsd_price_summary .duration-costing .price-figure').attr('data-price');
                if(get_total_price > 0){
                    jQuery(' button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
                }
                else{
                    jQuery(' button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
                }
            }
        });
    });

}


// display extra services box onclick and onchange
function rbfw_display_es_box_onchange_onclick(){

    jQuery('.rbfw_bikecarsd_qty_plus,.rbfw_bikecarsd_qty_minus').click(function (e) {

        let count = jQuery('.rbfw_bikecarsd_rt_price_table tbody tr').length;
        let total_qty = 0;
        for (let index = 1; index <= count; index++) {
            let qty = jQuery('input[name="rbfw_bikecarsd_info['+index+'][qty]"]').val();
            total_qty += parseInt(qty);
        }

        if(total_qty > 0){
            jQuery('.rbfw_bikecarsd_es_price_table').show();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').show();

        }else{
            jQuery('.rbfw_service_qty').val('0');
            jQuery('.rbfw_service_qty').trigger('change');
            jQuery('.rbfw_bikecarsd_es_price_table').hide();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').hide();
        }

    });

    jQuery('.rbfw_bikecarsd_qty').change(function (e) {
        let count = jQuery('.rbfw_bikecarsd_rt_price_table tbody tr').length;

        let total_qty = 0;
        for (let index = 1; index <= count; index++) {
            let qty = jQuery('input[name="rbfw_bikecarsd_info['+index+'][qty]"]').val();
            total_qty += parseInt(qty);
        }

        if(total_qty > 0){

            jQuery('.rbfw_bikecarsd_es_price_table').show();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').show();
        }else{
            jQuery('.rbfw_service_qty').val('0');
            jQuery('.rbfw_service_qty').trigger('change');
            jQuery('.rbfw_bikecarsd_es_price_table').hide();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').hide();
        }
    });
}

function rbfw_mps_book_now_btn_action(){
    jQuery('button.rbfw_bikecarsd_book_now_btn.mps_enabled').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
        let selected_time = jQuery('#rbfw_bikecarsd_selected_time').val();
        let rbfw_rent_type = jQuery('#rbfw_rent_type').val();
        let type_length = jQuery('.rbfw_bikecarsd_rt_price_table tbody tr').length;
        let service_length = jQuery('.rbfw_bikecarsd_es_price_table tbody tr').length;
        let type_array = {};
        let service_array = {};
        let post_id = jQuery('#rbfw_post_id').val();

        let index_start = 1;

        if(rbfw_rent_type == 'appointment'){

            index_start = 1;

        }

        for (let index = index_start; index <= type_length; index++) {

            let qty = jQuery('input[name="rbfw_bikecarsd_info['+index+'][qty]"]').val();
            let data_type = jQuery('input[name="rbfw_bikecarsd_info['+index+'][qty]"]').attr('data-type');
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


        var rbfw_regf_fields =  jQuery.parseJSON(jQuery('#rbfw_regf_info').val());

        console.log('kkkkkk',rbfw_regf_fields);

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
                'rent_type': rbfw_rent_type,
                'selected_date': selected_date,
                'selected_time': selected_time,
                'type_info[]': type_array,
                'service_info[]': service_array,
                'rbfw_regf_info[]' : rbfw_regf_info,
                'rbfw_regf_checkboxes' : rbfw_regf_checkboxes,
                'rbfw_regf_radio': rbfw_regf_radio
            },
            beforeSend: function() {
                jQuery('.rbfw-bikecarsd-result-loader').show();
                jQuery('.rbfw-bikecarsd-result-order-details').empty();
                jQuery('.rbfw_bikecarsd_book_now_btn.mps_enabled').append('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                jQuery('.rbfw-bikecarsd-result-loader').hide();
                jQuery('.rbfw_bikecarsd_book_now_btn.mps_enabled i').remove();

                var returnedData = JSON.parse(response);

                if(returnedData.hasOwnProperty('rbfw_regf_warning') && returnedData.rbfw_regf_warning != ''){
                    jQuery('.rbfw_bikecarsd_book_now_btn_wrap').show();
                    jQuery('.rbfw_bikecarsd_pricing_table_container').show();
                    jQuery('.rbfw_regf_warning_wrap').remove();
                    jQuery('.rbfw-bikecarsd-result-order-details').append(returnedData.rbfw_regf_warning);
                }

                if(returnedData.hasOwnProperty('rbfw_content') && returnedData.rbfw_content != ''){
                    jQuery('.rbfw_bikecarsd_book_now_btn_wrap').hide();
                    jQuery('.rbfw_bikecarsd_pricing_table_container').hide();
                    jQuery('.rbfw_regf_warning_wrap').remove();
                    jQuery('.rbfw-bikecarsd-result-order-details').append(returnedData.rbfw_content);
                }

                rbfw_on_submit_user_form_action(post_id,rbfw_rent_type,selected_date,selected_time,type_array,service_array,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio);
                rbfw_mps_checkout_header_link();
            },
            complete:function(response) {
                jQuery('html, body').animate({
                    scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                }, 100);
            }
        });
    });

}

function rbfw_mps_direct_checkout(){

    let rbfw_rent_type = jQuery('#rbfw_rent_type').val();

    if(rbfw_rent_type == 'appointment'){

        let type_length = jQuery('.rbfw_bikecarsd_rt_price_table tbody tr').length;

        if(type_length == 1){

            let max_qty = parseInt(jQuery('.rbfw_bikecarsd_qty').attr('max'));

            if(max_qty >= 1){

                jQuery('.rbfw_bikecarsd_qty_plus').trigger('click');
                jQuery('.rbfw_bikecarsd_rt_price_table').hide();

                //jQuery('button.rbfw_bikecarsd_book_now_btn.mps_enabled').removeAttr('disabled').trigger('click');
            }

        }
    }

}

function rbfw_on_submit_user_form_action(post_id,rent_type,selected_date,selected_time,type_array,service_array,rbfw_regf_info,rbfw_regf_checkboxes,rbfw_regf_radio){
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
                    jQuery('button.rbfw_bikecarsd_book_now_btn.mps_enabled').trigger('click');
                }
            }
        });
    });

    jQuery('.rbfw_mps_user_payment_method').click(function (e) {
        let this_value = jQuery(this).val();
        let item_number = jQuery('#rbfw_post_id').val();
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
                    'start_date': selected_date,
                    'start_time': selected_time,
                    'end_date': selected_date,
                    'end_time': '',
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

            let first_name = target.find('input[name="rbfw_mps_user_fname"]').val();
            let last_name = target.find('input[name="rbfw_mps_user_lname"]').val();

            let submit_request = target.find('input[name="rbfw_mps_user_submit_request"]').val();
            let email = target.find('input[name="rbfw_mps_user_email"]').val();

            let security = target.find('input[name="rbfw_mps_order_place_nonce"]').val();

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action' : 'rbfw_mps_place_order_form_submit',
                    'post_id': post_id,
                    'rent_type': rent_type,
                    'start_date': selected_date,
                    'start_time': selected_time,
                    'end_date': selected_date,
                    'end_time': '',
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


/*start resort pricing booking*/


/*start multiple day pricing booking*/

function rbfw_off_day_dates(date){
    var weekday = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
    var day_in = weekday[date.getDay()];
    var rbfw_off_days = JSON.parse(jQuery("#rbfw_off_days").val());

    var curr_date = ("0" + (date.getDate())).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    var date_in = curr_date+"-"+curr_month+"-"+curr_year;
    var rbfw_offday_range = JSON.parse(jQuery("#rbfw_offday_range").val());

    if(jQuery.inArray( day_in, rbfw_off_days )>= 0){
        return true;
    }else{
        if(jQuery.inArray( date_in, rbfw_offday_range )>= 0){
            return true;
        }else{
            return false;
        }
    }
}


jQuery(document).on('click', '#add-date-range-row',function(e){
    e.preventDefault();
    var off_date_range_content = jQuery('.off_date_range_content').html();
    console.log('hhhh',off_date_range_content);
    jQuery('.off_date_range').append(off_date_range_content);
});


jQuery(document).on('click', '.remove-row',function(e){
    if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
        jQuery(this).parents('.off_date_range_child').remove();
    } else {
        return false;
    }
});

jQuery(document).on("click", ".rbfw_off_days_range", function (e) {
    jQuery(this).datepicker({
        dateFormat: 'dd-mm-yy'
    }).datepicker( "show" );
});


jQuery(document).on('click', '.groupCheckBox .customCheckboxLabel', function () {
    let parent = jQuery(this).closest('.groupCheckBox');
    let value = '';
    let separator = ',';
    parent.find(' input[type="checkbox"]').each(function () {
        if (jQuery(this).is(":checked")) {
            let currentValue = jQuery(this).attr('data-checked');
            value = value + (value ? separator : '') + currentValue;
        }
    }).promise().done(function () {
        parent.find('input[type="hidden"]').val(value);
    });
});