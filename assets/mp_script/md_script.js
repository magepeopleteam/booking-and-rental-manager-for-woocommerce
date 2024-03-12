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
    es_service_price()
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
    es_service_price()
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




/*
let service_price_arr = {};

jQuery('.rbfw-resource-price-multiple-qty').change(function(e) { alert(22);
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

});*/

jQuery('#pickup_date,#dropoff_date,#pickup_time,#dropoff_time').change(function (e) {

    let pickup_date = jQuery('#pickup_date').val();
    let drop_off_date = jQuery('#dropoff_date').val();
    if(pickup_date && drop_off_date){
        rbfw_bikecarmd_ajax_price_calculation();
    }
});

jQuery('.rbfw_bikecarmd_es_qty').change(function (e) {
    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
});

jQuery(document).on('change', '#rbfw_item_quantity', function(e) {
    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
});









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
    var rbfw_regf_fields =  jQuery.parseJSON(jQuery('#rbfw_regf_info').val());

     if(rbfw_regf_fields){
         let rbfw_regf_fields = {};
     }
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

                jQuery('.rbfw-bikecarmd-result').append('<a class="rbfw_bikecarmd_backstep1_btn"><img src=" RBFW_PLUGIN_URL/assets/images/muff_edit_icon.png"/> Change</a>');
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

        the_html += '<div class="rbfw_muff_selected_date_col"><label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_clock_icon2.png"/>Pickup time</label><span class="rbfw_muff_selected_date_value">'+pickup_time+'</span> <label><img src="<?php echo RBFW_PLUGIN_URL ?>/assets/images/muff_clock_icon2.png"/>Drop-off time</label><span class="rbfw_muff_selected_date_value">'+dropoff_time+'</span></div>';

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

jQuery("body").on('click','input.rbfw-resource-price-multiple-qty', function (e) {
    es_service_price();
});
function es_service_price(){
    var service_price = 0;
    jQuery('.rbfw_bikecarmd_es_table tbody tr').each(function(index) {
        if (jQuery(this).find('input.rbfw-resource-price-multiple-qty').is(':checked')) {
            var qty = Number(jQuery(this).find('input.rbfw_bikecarmd_es_qty').val());
            var ind_price = Number(jQuery(this).find('input.rbfw-resource-price-multiple-qty').data('price'))
            service_price = (ind_price*qty)+service_price;
        }
    });
    jQuery('input[name="es_service_price"]').val(service_price);

    let pickup_date = jQuery('#pickup_date').val();
    let drop_off_date = jQuery('#dropoff_date').val();
    if(pickup_date && drop_off_date){
        rbfw_bikecarmd_ajax_price_calculation();
    }
}


function rbfw_bikecarmd_ajax_price_calculation(pickup_date,drop_off_date){


    if (typeof reload_es === 'undefined' || reload_es === null) {
        reload_es = 1;
    }

    let post_id = jQuery('[data-service-id]').data('service-id');

    let es_service_price = jQuery('#es_service_price').val();

    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let dropoff_time = jQuery('#dropoff_time').find(':selected').val();
    let item_quantity = jQuery('#rbfw_item_quantity').find(':selected').val();

    if(pickup_date && drop_off_date){

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




    jQuery.ajax({
        type: 'POST',
        
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarmd_ajax_price_calculation',
            'post_id': post_id,
            'pickup_date': pickup_date,
            'pickup_time': pickup_time,
            'drop_off_date': drop_off_date,
            'dropoff_time': dropoff_time,
            'item_quantity': item_quantity,
            'es_service_price': es_service_price,
            'reload_es': reload_es
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarmd_price_result').empty();
            jQuery('.rbfw_bikecarmd_price_result').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');
        },
        success: function (response) {


            jQuery('.regf_enable .rbfw_next_btn').removeAttr('disabled');

            jQuery('.rbfw_bikecarmd_price_result').html(response);


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
                jQuery('<div class="rbfw_nia_notice mps_alert_warning">No Items Available!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
            } else {
                jQuery('.rbfw_nia_notice').remove();
                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
            }

        },
        error : function(response){  alert(233)
            console.log(response);
        }
    });
}