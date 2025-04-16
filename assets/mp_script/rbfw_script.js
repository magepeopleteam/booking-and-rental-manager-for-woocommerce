/*start single day and appointment pricing booking*/

/* Start Calendar Script */
let bikecarsd_price_arr = {};
let service_price_arr = {};


jQuery(document).on('click','.rbfw_back_step_btn',function (e) {
    let back_step = jQuery(this).attr('back-step');
    let current_step = jQuery(this).attr('data-step');
    jQuery('.rbfw-bikecarsd-step[data-step="'+current_step+'"]').hide();
    jQuery('.rbfw-bikecarsd-step[data-step="'+back_step+'"]').show();
});


jQuery(document).on('click','.rbfw_bikecarsd_time:not(.rbfw_bikecarsd_time.disabled)',function (e) {

    let gTime = jQuery(this).attr('data-time');

    let selected_date = jQuery('[name="rbfw_bikecarsd_selected_date"]').val();
    let post_id = jQuery('#rbfw_post_id').val();
    let rent_type = jQuery('#rbfw_rent_type').val();
    let is_muffin_template = jQuery('.rbfw_muffin_template').length;

    jQuery('.rbfw_bikecarsd_time').removeClass('selected');
    jQuery(this).addClass('selected');
    jQuery('#rbfw_start_time').val(gTime);

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
                'is_muffin_template': is_muffin_template,
                'nonce' : rbfw_ajax.nonce
            },
            beforeSend: function() {

                jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');

                if( rent_type == 'appointment' ){
                    jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                }
            },
            success: function (response) {

                jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

                if( rent_type == 'bike_car_sd' ){
                    jQuery('.rbfw-bikecarsd-step[data-step="2"]').hide();
                }
                jQuery('.rbfw_bikecarsd_pricing_table_container').remove();
                jQuery('.rbfw-bikecarsd-result').append(response);

                if( rent_type == 'appointment' ){
                    jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_back_step_btn').hide();
                    jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_step_selected_date').hide();
                    jQuery('#rbfw_bikecarsd_selected_time').val();
                    jQuery('.rbfw-bikecarsd-step[data-step="2"] .rbfw_step_selected_date span.rbfw_selected_time').remove();
                }

                jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();




            },
            complete:function(response) {
                jQuery('html, body').animate({
                    scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                }, 100);
            }
        });
});



// update input value onclick and onchange


jQuery(document).on('click','.rbfw_bikecarsd_qty_plus,.rbfw_servicesd_qty_plus, .rbfw_service_qty_plus',function (e) {

    let quantity_fixed = jQuery(this).data('quantity_fixed');

    if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_servicesd_qty_plus")){
        jQuery(this).attr('class','rbfw_servicesd_qty_minus');
    }else if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_servicesd_qty_minus")){
        jQuery(this).attr('class','rbfw_servicesd_qty_plus');
    }

    let target_input = jQuery(this).siblings("input[type=number]");
    let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
    let max_value = parseInt(jQuery(this).siblings("input[type=number]").attr('max'));
    let update_value = current_value + 1;

    if(update_value <= max_value){
        jQuery(target_input).val(update_value);
        jQuery(target_input).attr('value',update_value);
    }else{
            //let notice = "<?php rbfw_string('rbfw_text_available_qty_is',__('Available Quantity is: ','booking-and-rental-manager-for-woocommerce')); ?>";
        let notice = "Available Quantity is ";
        tippy(this, {content: notice + max_value, theme: 'blue',placement: 'top',trigger: 'click'});
    }
});

jQuery(document).on('click','.rbfw_bikecarsd_qty_minus,.rbfw_servicesd_qty_minus, .rbfw_service_qty_minus',function (e) {

    let quantity_fixed = jQuery(this).data('quantity_fixed');

    if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_servicesd_qty_plus")){
        jQuery(this).attr('class','rbfw_servicesd_qty_minus');
    }else if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_servicesd_qty_minus")){
        jQuery(this).attr('class','rbfw_servicesd_qty_plus');
    }

        let target_input = jQuery(this).siblings("input[type=number]");
        let current_value = parseInt(jQuery(this).siblings("input[type=number]").val());
        let update_value = current_value - 1;
        if(current_value > 0){
            jQuery(target_input).val(update_value);
            jQuery(target_input).attr('value',update_value);
        }
});

jQuery(document).on('change','.rbfw_bikecarsd_qty',function (e) {
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



/********For single day services************/

jQuery(document).on('click','.rbfw_bikecarsd_qty_plus,.rbfw_bikecarsd_qty_minus,.rbfw_servicesd_qty_minus,.rbfw_servicesd_qty_plus',function (e) {



    let data_cat = jQuery(this).siblings('input[type=number]').attr('data-cat');
        let post_id = jQuery('#rbfw_post_id').val();
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

        var currentRequest = null;
 
        currentRequest = jQuery.ajax({
            type: 'POST',
            url: rbfw_ajax.rbfw_ajaxurl,
            data: {
                'action'        : 'rbfw_bikecarsd_ajax_price_calculation',
                'post_id': post_id,
                'bikecarsd_price_arr': bikecarsd_price_arr,
                'service_price_arr': service_price_arr,
                'nonce' : rbfw_ajax.nonce
            },

            beforeSend: function() {
                if(currentRequest != null) {
                    currentRequest.abort();
                }
                jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
                },
            success: function (response) {
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
                jQuery(response).insertAfter('.rbfw_bikecarsd_price_summary.old');
                jQuery('.rbfw_bikecarsd_price_summary.old').remove();

            }
        });
    });


       /***********for single day extra services**********/
jQuery(document).on('change','.rbfw_bikecarsd_qty, .rbfw_servicesd_qty',function (e) {
        let data_cat         = jQuery(this).attr('data-cat');
        let post_id = jQuery('#rbfw_post_id').val();

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
            let data_qty         = jQuery(this).val();
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
                'action'  : 'rbfw_bikecarsd_ajax_price_calculation',
                'post_id': post_id,
                'bikecarsd_price_arr': bikecarsd_price_arr,
                'service_price_arr': service_price_arr,
                'nonce' : rbfw_ajax.nonce
            },
            beforeSend: function() {
                jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
                jQuery(response).insertAfter('.rbfw_bikecarsd_price_summary.old');
                jQuery('.rbfw_bikecarsd_price_summary.old').remove();
            }
        });
    });


jQuery(document).on('click','.rbfw_bikecarsd_qty_plus, .rbfw_bikecarsd_qty_minus',function (e) {

    let quantity_fixed = jQuery(this).data('quantity_fixed');

    if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_bikecarsd_qty_plus")){
        jQuery(this).attr('class','rbfw_bikecarsd_qty_minus');
    }else if(quantity_fixed=='checkbox' && jQuery(this).hasClass("rbfw_bikecarsd_qty_minus")){
        jQuery(this).attr('class','rbfw_bikecarsd_qty_plus');
    }


        let count = jQuery('.rbfw_bikecarsd_rt_price_table tbody tr').length;
        let total_qty = 0;
        for (let index = 1; index <= count; index++) {
            let qty = jQuery('input[name="rbfw_bikecarsd_info['+index+'][qty]"]').val();
            if(jQuery.isNumeric( qty )){
                total_qty += parseInt(qty);
            }
        }
        if(total_qty > 0){
            jQuery('.rbfw_bikecarsd_es_price_table').show();
            jQuery('.rbfw_regf_wrap').show();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').show();
            jQuery('button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
            jQuery(' button.rbfw_bikecarsd_book_now_btn').removeClass('rbfw_disabled_button');
        }else{
            jQuery('.rbfw_servicesd_qty').val('0');
            jQuery('.rbfw_servicesd_qty').trigger('change');
            jQuery('.rbfw_bikecarsd_es_price_table').hide();
            jQuery('.rbfw_regf_wrap').hide();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').hide();
            jQuery('button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
            jQuery('button.rbfw_bikecarsd_book_now_btn').addClass('rbfw_disabled_button');
        }
    });


jQuery(document).on('change','.rbfw_bikecarsd_qty',function (e) {

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
            jQuery('.rbfw_servicesd_qty').val('0');
            jQuery('.rbfw_servicesd_qty').trigger('change');
            jQuery('.rbfw_bikecarsd_es_price_table').hide();
            jQuery('.rbfw_bike_car_sd_available_es_qty_notice').hide();
        }
    });


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

function rbfw_off_day_dates(date,type='',today_enable='no'){


    var curr_date = ("0" + (date.getDate())).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    var date_in = curr_date+"-"+curr_month+"-"+curr_year;

    var date_today = new Date();
    if(today_enable=='yes'){
        date_today.setDate(date_today.getDate() - 1);
    }

    var weekday = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
    var day_in = weekday[date.getDay()];
    var rbfw_off_days = JSON.parse(jQuery("#rbfw_off_days").val());

    var rbfw_offday_range = JSON.parse(jQuery("#rbfw_offday_range").val());

  


    if(jQuery.inArray( day_in, rbfw_off_days )>= 0 || jQuery.inArray( date_in, rbfw_offday_range )>= 0 || (date <  date_today) ){

        if(type=='md'){
            return [false, "notav", 'Off'];
        }else{
            return   true;
        }
    }else{

        if(type=='md'){

            const  day_wise_inventory = JSON.parse(jQuery('#rbfw_month_wise_inventory').val()); 


           if(day_wise_inventory[date_in]==0){
                return [false, "notav", 'Sold Out'];
            }
            
            return [true, "av", ""];
        }else{
            return false;
        }
    }
}

function particular_time_date_dependent_ajax(post_id,date_ymd,type='',rbfw_enable_time_slot='',selector){

    jQuery.ajax({
        type: 'POST',
        dataType:'json',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'  : 'particular_time_date_dependent',
            'post_id': post_id,
            'selected_date': date_ymd,
            'type': type,
            'selector': selector,
            'nonce' : rbfw_ajax.nonce
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

            if(response[1] == ".rbfw-select.rbfw-time-price.dropoff_time"){
                var quantity_options = "<option value=''>Return Time</option>";
            }else{
                var quantity_options = "<option value=''>Pickup Time</option>";
            }

            jQuery.each(response[0], function(i, item) {
                quantity_options += "<option "+ item[0] +" value="+i+">"+item[1]+"</option>";
            });
            jQuery(response[1]).html(quantity_options);



            let pickup_date = jQuery('#hidden_pickup_date').val();
            let dropoff_date = jQuery('#hidden_dropoff_date').val();

            console.log('pickup_date',pickup_date)
            console.log('dropoff_date',dropoff_date)


            if (pickup_date == dropoff_date) {
                let selected_time = jQuery('.pickup_time').val();
                selected_time = new Date (pickup_date +' '+ selected_time);
                jQuery(".dropoff_time").val("").trigger("change");

                jQuery("#dropoff_time option").each(function() {
                    var thisOptionValue = jQuery(this).val();
                    thisOptionValue = new Date(pickup_date +' '+ thisOptionValue);


                    if (thisOptionValue <= selected_time) {
                        jQuery(this).attr('disabled', true);
                    } else {
                        jQuery(this).attr('disabled', false);
                    }
                });

            } else {
                jQuery("#dropoff_time option").each(function() {
                    var thisOptionValue = jQuery(this).val();
                    if (thisOptionValue != '') {
                        jQuery(this).attr('disabled', false);
                    } else {
                        jQuery(this).attr('disabled', true);
                    }
                });
            }



        }
    });
}



function rbfw_today_date() {
    var default_d = new Date();
    var new_d = changeTimezone(default_d, rbfw_calendar_object.default_timezone);
    return new_d;
}


jQuery(document).on('click', '#add-date-range-row',function(e){
    e.preventDefault();
    var off_date_range_content = jQuery('.off_date_range_content').clone(true);

    jQuery('.off_date_range').append(off_date_range_content);

    off_date_range_content.find('.rbfw_off_days_range_start').attr('name','off_days_start[]');
    off_date_range_content.find('.rbfw_off_days_range_end').attr('name','off_days_end[]');
    off_date_range_content.removeClass('off_date_range_content hidden');
    off_date_range_content.addClass('off_date_range_remove');
    off_date_range_content.insertBefore(".off_date_range_content");
    return false;

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
        dateFormat: 'dd-mm-yy',
        minDate: 0
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


