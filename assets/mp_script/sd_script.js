(function($) {
    $(document).ready(function() {

        let rbfw_today_booking_enable = $('.rbfw_today_booking_enable').val();

        datepicker_inline();

        function datepicker_inline(){
            jQuery('.rbfw-bikecarsd-calendar').datepicker({
                dateFormat: js_date_format,
                minDate: 0,
                firstDay : start_of_week,
                showOtherMonths: true,
                selectOtherMonths: true,
                beforeShowDay: function(date)
                {
                    return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
                },
                onSelect: function (dateString, data) {
                    let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                    jQuery('input[name="rbfw_bikecarsd_selected_date"]').val(date_ymd).trigger('change');
                },
            });
        }



        jQuery('body').on('change', 'input[name="rbfw_bikecarsd_selected_date"]', function(e) {

            let manage_inventory_as_timely = $('#manage_inventory_as_timely').val();

            if(manage_inventory_as_timely=='on'){
                return;
            }

            let post_id = jQuery('.rbfw_post_id').val();
            let is_muffin_template = jQuery('.rbfw_muffin_template').length;

            var time_slot_switch = jQuery('#time_slot_switch').val();
            var selected_date = jQuery(this).val();


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
                    'selected_date': selected_date,
                    'is_muffin_template': is_muffin_template,
                    'time_slot_switch': time_slot_switch,
                },
                beforeSend: function() {
                    jQuery('.rbfw-bikecarsd-result').empty();
                    jQuery('.rbfw_bikecarsd_time_table_container').remove();

                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');

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
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

                    jQuery('.rbfw-bikecarsd-result').append(response);
                    var time_slot_switch = jQuery('#time_slot_switch').val();

                    if(time_slot_switch != 'on'){
                        rbfw_mps_book_now_btn_action();
                        jQuery('.rbfw_back_step_btn').attr('back-step','1');
                        jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();
                    }


                },
                complete:function(data) {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                    }, 100);
                }
            });

        });


        /*start single day hourly inventory managed*/



        jQuery('body').on('focusin', '.pickup_date_timely', function(e) {
            jQuery(this).datepicker({
                dateFormat: js_date_format,
                minDate: 0,
                beforeShowDay: function(date)
                {
                    return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
                },
                onSelect: function (dateString, data) {
                    let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                    jQuery('input[name="rbfw_bikecarsd_selected_date"]').val(date_ymd).trigger('change');

                    let rbfw_time_slot_switch = jQuery('#rbfw_time_slot_switch').val();

                    if(rbfw_time_slot_switch=='on'){
                        let post_id = jQuery('.rbfw_post_id').val();
                        jQuery.ajax({
                            type: 'POST',
                            dataType:'json',
                            url: rbfw_ajax.rbfw_ajaxurl,
                            data: {
                                'action'  : 'particular_time_date_dependent',
                                'post_id': post_id,
                                'selected_date': date_ymd,
                            },
                            beforeSend: function() {
                                jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
                            },
                            success: function (response) {
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

                                var quantity_options = "<option value=''>Pickup Time</option>";

                                $.each(response, function(i, item) {
                                    quantity_options += "<option "+ item[0] +" value="+i+">"+item[1]+"</option>";
                                });
                                jQuery('.rbfw-select.rbfw-time-price.pickup_time').html(quantity_options);
                            }
                        });
                    }
                },
            });
        });


        jQuery('body').on('click', '.rbfw_service_type .single-type-timely', function(e) {

            let post_id = jQuery('.rbfw_post_id').val();
            let rbfw_bikecarsd_selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
            let pickup_time = jQuery('.rbfw-select.rbfw-time-price.pickup_time').val();
            let rbfw_time_slot_switch = jQuery('#rbfw_time_slot_switch').val();
            let service_price = jQuery(this).data('price');
            let duration = jQuery(this).data('duration');
            let d_type = jQuery(this).data('d_type');
            let available_quantity = jQuery(this).data('available_quantity');
            let service_type = jQuery(this).text();

            if(rbfw_bikecarsd_selected_date==''){
                alert("please enter pickup date");
                return;
            }

            if(rbfw_time_slot_switch == 'on'){
                console.log('pickup_time',pickup_time);
                if(!pickup_time){
                    alert("please enter pickup time");
                    return;
                }
            }

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action'  : 'rbfw_timely_variation_price_calculation',
                    'post_id': post_id,
                    'rbfw_bikecarsd_selected_date': rbfw_bikecarsd_selected_date,
                    'pickup_time': pickup_time,
                    'service_price': service_price,
                    'duration': duration,
                    'd_type': d_type,
                    'available_quantity': available_quantity,
                    'service_type': service_type,
                },
                beforeSend: function() {
                    jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function (response) {
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
                    jQuery('.rbfw_bikecarsd_price_summary').html(response);
                    jQuery('button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
                    jQuery(' button.rbfw_bikecarsd_book_now_btn').removeClass('rbfw_disabled_button');
                }
            });
        })

        jQuery('body').on('change', '#pickup_time',function (e) {

            let post_id = jQuery('.rbfw_post_id').val();
            let rbfw_bikecarsd_selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
            let pickup_time = jQuery('.rbfw-select.rbfw-time-price.pickup_time').val();
            let rbfw_time_slot_switch = jQuery('#rbfw_time_slot_switch').val();

            jQuery('#rbfw_start_time').val(pickup_time);

            if(rbfw_time_slot_switch == 'on'){
                if(pickup_time==null || rbfw_bikecarsd_selected_date==''){
                    alert("please enter date");
                    return;
                }
            }else{
                if(rbfw_bikecarsd_selected_date==''){
                    alert("please enter date");
                    return;
                }
            }


            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action'  : 'rbfw_service_type_timely_stock',
                    'post_id': post_id,
                    'rbfw_bikecarsd_selected_date': rbfw_bikecarsd_selected_date,
                    'pickup_time': pickup_time,
                },
                beforeSend: function() {
                    jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function (response) {
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
                    jQuery('.rbfw_service_type_timely').html(response);

                    jQuery('button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
                    jQuery('button.rbfw_bikecarsd_book_now_btn').addClass('rbfw_disabled_button');

                    //jQuery('button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
                    //jQuery(' button.rbfw_bikecarsd_book_now_btn').removeClass('rbfw_disabled_button');

                }
            });
        })




        let es_service_price = 0;

        jQuery('body').on('click','.rbfw_timely_es_qty_plus',function (e) {

            e.preventDefault();
            var service_quantity = parseInt(jQuery(this).prev('input').val());
            var max_value = parseInt(jQuery(this).prev('input').attr('max'));

            if(max_value > service_quantity){
                let actual_quantity = service_quantity + 1
                jQuery(this).prev('input').val(actual_quantity );

                var total = 0;
                jQuery(".rbfw_timely_es_qty").each(function() {
                    var price = jQuery(this).data('price');
                    var quantity = jQuery(this).val();
                    total +=  price * quantity;
                });
                jQuery('#rbfw_es_service_price').val(total);

                rbfw_timely_price_calculation();


            }
        });

        jQuery('body').on('click','.rbfw_timely_es_qty_minus',function (e) {

            e.preventDefault();
            var service_quantity = parseInt(jQuery(this).next('input').val());
            var max_value = parseInt(jQuery(this).next('input').attr('max'));

            if(max_value >= service_quantity && service_quantity > 0 ){
                let actual_quantity = service_quantity - 1
                jQuery(this).next('input').val(actual_quantity);
                var total = 0;
                jQuery(".rbfw_timely_es_qty").each(function() {
                    var price = jQuery(this).data('price');
                    var quantity = jQuery(this).val();
                    total +=  price * quantity;
                });
                jQuery('#rbfw_es_service_price').val(total);

                rbfw_timely_price_calculation();
            }
        });




        jQuery('body').on('change','.rbfw_timely_es_qty',function (e) {

            e.preventDefault();
            let service_quantity = parseInt(jQuery(this).val());
            let max_value = parseInt(jQuery(this).attr('max'));

            if(service_quantity > max_value){
                jQuery(this).val(max_value);
                service_quantity = max_value;
            }
            var total = 0;
            jQuery(".rbfw_timely_es_qty").each(function() {
                var price = jQuery(this).data('price');
                var quantity = jQuery(this).val();
                total +=  price * quantity;
            });
            jQuery('#rbfw_es_service_price').val(total);

            rbfw_timely_price_calculation();

        });

        jQuery('body').on('change','#rbfw_item_quantity',function (e) {
            let is_selected = jQuery('.single-type-timely.selected').length;
            if(is_selected){
                rbfw_timely_price_calculation();
            }
        })

    });
})(jQuery)




function rbfw_timely_price_calculation(){
    let post_id = jQuery('.rbfw_post_id').val();
    let duration_price = jQuery('.radio-button.selected').data('price') * jQuery('#rbfw_item_quantity').val();

    let es_service_price = jQuery('#rbfw_es_service_price').val();

    jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'  : 'rbfw_timely_price_calculation',
            'post_id': post_id,
            'es_service_price': es_service_price,
            'duration_price': duration_price,
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
            jQuery('.rbfw_bikecarsd_price_summary_only').html(response);

        }
    });

}

