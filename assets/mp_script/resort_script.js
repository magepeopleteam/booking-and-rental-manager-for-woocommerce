
let room_prices_arr = {};
let service_prices_arr = {};

jQuery('body').on('focusin', '#checkin_date', function(e) {
    jQuery(this).datepicker({
        dateFormat: js_date_format,
        minDate: 0,
        beforeShowDay: function(date)
        {
            return rbfw_off_day_dates(date,'md',rbfw_js_variables.rbfw_today_booking_enable);
        },
        onSelect: function (dateString, data) {
            let date_ymd_drop = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_start_datetime"]').val(date_ymd_drop).trigger('change');

            let rbfw_minimum_booking_day = parseInt(jQuery('#rbfw_minimum_booking_day').val());
            let rbfw_maximum_booking_day = parseInt(jQuery('#rbfw_maximum_booking_day').val());




            let selected_date_array = date_ymd_drop.split('-');
            let gYear = selected_date_array[0];
            let gMonth = selected_date_array[1];
            let gDay = selected_date_array[2];

            let minDate = new Date(gYear,  gMonth - 1, gDay );
            minDate.setDate(minDate.getDate() + rbfw_minimum_booking_day);



            jQuery("#checkout_date").datepicker("option", "minDate", minDate);


            if(rbfw_minimum_booking_day){
                let maxDate = new Date(gYear,  gMonth - 1, gDay - 1 );
                maxDate.setDate(maxDate.getDate() + rbfw_maximum_booking_day);
                jQuery("#checkout_date").datepicker("option", "maxDate", maxDate );
            }
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
            return rbfw_off_day_dates(date,'md',rbfw_js_variables.rbfw_today_booking_enable);
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
            url: rbfw_ajax_front.rbfw_ajaxurl,
            data: {
                'action' 		: 'rbfw_check_resort_availibility',
                'post_id' 		: post_id,
                'checkin_date' 	: checkin_date,
                'checkout_date' : checkout_date,
                'is_muffin_template': is_muffin_template,
                'rbfw_enable_resort_daylong_price': rbfw_enable_resort_daylong_price,
                'nonce' : rbfw_ajax_front.nonce_check_resort_availibility
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





/*jQuery('.rbfw_room_qty').on('input', function() {*/
jQuery(document).on('input','.rbfw_room_qty',function(e) {
    calculateTotalDurationPrice();
});

// Plus button
/*jQuery('.rbfw_room_qty_plus').on('click', function() {*/
jQuery(document).on('click','.rbfw_room_qty_plus',function(e) {
    let input = jQuery(this).siblings('input[type="number"]');
    let max = parseInt(input.attr('max')) || 9999;
    let current = parseInt(input.val()) || 0;
    if (current < max) {
        input.val(current + 1).trigger('input');
    }
});

// Minus button
/*jQuery('.rbfw_room_qty_minus').on('click', function() {*/
jQuery(document).on('click','.rbfw_room_qty_minus',function(e) {
    let input = jQuery(this).siblings('input[type="number"]');
    let min = parseInt(input.attr('min')) || 0;
    let current = parseInt(input.val()) || 0;
    if (current > min) {
        input.val(current - 1).trigger('input');
    }
});


jQuery(document).on('input','.rbfw_service_qty_resort',function(e) {
    calculateTotalResortExtraService();
});


jQuery(document).on('click','.rbfw_service_qty_plus',function(e) {
    let input = jQuery(this).siblings('input[type="number"]');
    let max = parseInt(input.attr('max')) || 9999;
    let current = parseInt(input.val()) || 0;
    if (current < max) {
        input.val(current + 1).trigger('input');
    }
});

jQuery(document).on('click','.rbfw_service_qty_minus',function(e) {
    let input = jQuery(this).siblings('input[type="number"]');
    let min = parseInt(input.attr('min')) || 0;
    let current = parseInt(input.val()) || 0;
    if (current > min) {
        input.val(current - 1).trigger('input');
    }
});



function calculateTotalDurationPrice() {
    let room_duration_price = 0;
    let hasQty = false;
    jQuery('.rbfw_room_qty').each(function() {
        let qty = parseInt(jQuery(this).val()) || 0;
        let price = parseFloat(jQuery(this).data('price')) || 0;
        let resort_total_days = jQuery('#resort_total_days').val();
        room_duration_price += qty * resort_total_days * price;
        if (qty > 0) {
            hasQty = true; // mark that we found one
        }
    });

    if (hasQty) {
        jQuery('.rbfw_resort_es_price_table').show();
        jQuery('button.rbfw_resort_book_now_btn').removeAttr('disabled');
    }else{
        jQuery('.rbfw_resort_es_price_table').hide();
        jQuery('button.rbfw_resort_book_now_btn').attr('disabled',true);
    }


    // You can update this in a DOM element, console, or wherever you want
    jQuery('#rbfw_room_duration_price').val(room_duration_price.toFixed(2));

    var sub_total_price = room_duration_price + parseFloat(jQuery('#rbfw_extra_service_price').val());


    let rbfw_management_price = 0;
    jQuery('.rbfw-management-price-resort:checked').each(function() {  
        let price_type = jQuery(this).data('price_type');
        let price = parseFloat(jQuery(this).data('price')) || 0;

        let resort_total_days = parseFloat(jQuery('#resort_total_days').val()) || 0;

        if(price_type == 'percentage'){
            rbfw_management_price += ( price/100 ) * sub_total_price;
        }else{
            let frequency = jQuery(this).data('frequency');
            if(frequency == 'one-time' ){
                rbfw_management_price += price;
            }else{
                rbfw_management_price += price * resort_total_days
            }
        }
    });


    jQuery('#rbfw_management_price_resort').val(rbfw_management_price.toFixed(2));
    jQuery('.management-costing span').text(rbfw_translation.currency + rbfw_management_price.toFixed(2));



    jQuery('.duration-costing .price-figure').text(rbfw_translation.currency + room_duration_price.toFixed(2));



    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }

    var total_price = sub_total_price + parseFloat(rbfw_security_deposit_actual_amount);
    if(rbfw_security_deposit_actual_amount){
        jQuery('.security_deposit').show();
        jQuery('.security_deposit span').html(rbfw_translation.currency + parseFloat(rbfw_security_deposit_actual_amount).toFixed(2));
    }


    jQuery('.subtotal .price-figure').text(rbfw_translation.currency + sub_total_price.toFixed(2));
    jQuery('.total .price-figure').text(rbfw_translation.currency + total_price.toFixed(2));

}



function calculateTotalResortExtraService() {
    let resort_extra_service = 0;


    // Calculate services
    jQuery('.rbfw_service_qty_resort').each(function() {
        let qty = parseInt(jQuery(this).val()) || 0;
        let price = parseFloat(jQuery(this).data('price')) || 0;
        resort_extra_service += qty * price;
    });

    // Output total
    jQuery('#rbfw_extra_service_price').val(resort_extra_service.toFixed(2));
    jQuery('.resource-costing .price-figure').text(rbfw_translation.currency + resort_extra_service.toFixed(2));

    let sub_total_price = parseFloat(jQuery('#rbfw_room_duration_price').val()) + resort_extra_service;


    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }

    var total_price = sub_total_price + parseFloat(rbfw_security_deposit_actual_amount);
    if(rbfw_security_deposit_actual_amount){
        jQuery('.security_deposit').show();
        jQuery('.security_deposit span').html(rbfw_translation.currency + parseFloat(rbfw_security_deposit_actual_amount).toFixed(2));
    }


    jQuery('.subtotal .price-figure').text(rbfw_translation.currency + sub_total_price.toFixed(2));
    jQuery('.total .price-figure').text(rbfw_translation.currency + total_price.toFixed(2));
}
