(function($) {
    
    $(document).ready(function() {

        let rbfw_today_booking_enable = $('.rbfw_today_booking_enable').val();

        datepicker_inline();

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
                },
            });
        });



        jQuery('body').on('change', '#rbfw_bikecarsd_selected_date', function(e) {


            let post_id = jQuery('.rbfw_post_id').val();
            let manage_inventory_as_timely = $('#manage_inventory_as_timely').val();
            let enable_specific_duration = $('#enable_specific_duration').val();
            let time_slot_switch = jQuery('#rbfw_time_slot_switch').val();
            let start_date_ymd = jQuery('#rbfw_bikecarsd_selected_date').val();



            if(manage_inventory_as_timely=='on'){
                if(enable_specific_duration=='on'){
                    /*enable specific time and time slot not factor*/
                    rbfw_service_type_timely_stock_ajax(post_id,start_date_ymd,'','on');
                }else{
                    if(time_slot_switch=='no'){
                        /*disable specific time and time slot disable*/
                        rbfw_service_type_timely_stock_ajax(post_id,start_date_ymd,'',enable_specific_duration);
                    }else{
                        /*disable specific time and time slot enable*/
                        particular_time_date_dependent_ajax(post_id,start_date_ymd,'time_enable',time_slot_switch,'');

                        jQuery('body').on('change',  '.rbfw_bikecarsd_pricing_table_wrap #pickup_time',function (e) {

                            let post_id = jQuery('.rbfw_post_id').val();
                            let start_date = jQuery('#rbfw_bikecarsd_selected_date').val();
                            let start_time = jQuery(this).val();
                            let rbfw_time_slot_switch = jQuery('#rbfw_time_slot_switch').val();

                            jQuery('#rbfw_start_time').val(start_time);

                            if(rbfw_time_slot_switch == 'yes'){
                                if(start_date=='' || start_time==''){
                                    alert("please enter date");
                                    return;
                                }
                            }else{
                                if(start_date==''){
                                    alert("please enter date");
                                    return;
                                }
                            }
                            rbfw_service_type_timely_stock_ajax(post_id,start_date,start_time)
                        })

                    }
                }

            }else{
                if(time_slot_switch=='yes'){
                    particular_time_date_dependent_ajax(post_id,start_date_ymd,'sd','yes','.rbfw_bikecarsd_time_table_wrap');
                }else{

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
                                'selected_date': start_date_ymd,
                                'is_muffin_template': is_muffin_template,
                                'nonce' : rbfw_ajax.nonce
                            },
                            beforeSend: function() {
                                jQuery('.rbfw-bikecarsd-result').empty();
                                jQuery('.rbfw_bikecarsd_time_table_container').remove();
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
                                var rent_type = jQuery('#rbfw_rent_type').val();
                            },
                            success: function (response) {

                                jQuery('.rbfw-bikecarsd-step[data-step="1"]').hide();
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
                                jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

                                jQuery('.rbfw-bikecarsd-result').append(response);
                                var time_slot_switch = jQuery('#time_slot_switch').val();

                                jQuery('.rbfw_back_step_btn').attr('back-step','1');
                                jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();

                            },
                            complete:function(data) {
                                jQuery('html, body').animate({
                                    scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                                }, 100);
                            }
                    });


                }
            }
        });


        /*start single day hourly inventory managed*/

        jQuery(document).on('click', '.rbfw_service_type .single-type-timely', function(e) {
            let rbfw_bikecarsd_selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
            if(rbfw_bikecarsd_selected_date==''){
                alert("please enter pickup date");
                return;
            }
            let start_date = jQuery('#rbfw_bikecarsd_selected_date').val();
            var start_time = jQuery(this).data('start_time');
            let enable_specific_duration = jQuery('#enable_specific_duration').val();
            var end_time = jQuery(this).data('end_time');
            let available_quantity = jQuery(this).data('available_quantity');
            let service_type = jQuery(this).data('text');
            let service_price = jQuery(this).data('price');

            var  quantity_options = '';
            for (let i = 1; i <= available_quantity; i++) {
                quantity_options += "<option value="+i+">"+i+"</option>";
            }
            jQuery('#rbfw_item_quantity').html(quantity_options);
            jQuery('.rbfw_quantiry_area_sd .rbfw_sd_price_input').val(service_price);
            jQuery('.rbfw_quantiry_area_sd .rbfw_sd_price').text(rbfw_translation.currency + service_price.toFixed(2));
            jQuery(".rbfw_quantiry_area_sd").show();
            jQuery(".rbfw_extra_service_sd").show();
            var rbfw_service_price = jQuery('#rbfw_item_quantity').val() * service_price;
            jQuery('#rbfw_service_price').val(rbfw_service_price);
            jQuery('#rbfw_start_time').val(start_time);

            jQuery('.single-type-timely').each(function(index, element) {
                jQuery('.single-type-timely').removeClass('selected');
            });
            if(start_date!=''){
                jQuery(this).addClass('selected');
            }
            jQuery('button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
            jQuery(' button.rbfw_bikecarsd_book_now_btn').removeClass('rbfw_disabled_button');
            rbfw_price_calculation_sd();
        });

        jQuery('body').on('change','#rbfw_item_quantity',function (e) {
            var rbfw_service_price = jQuery('#rbfw_item_quantity').val() * jQuery(".rbfw_sd_price_input").val();
            jQuery('#rbfw_service_price').val(rbfw_service_price);
            rbfw_price_calculation_sd();
        });




        // When quantity changes manually
        jQuery(document).on("input", ".rbfw_bikecarsd_qty", function() {
            calculateTotal();
        });

        // When plus button clicked
        jQuery(document).on("click", ".rbfw_qty_plus", function(e) {
            e.preventDefault();
            let $input = jQuery(this).siblings(".rbfw_bikecarsd_qty");
            let max = parseInt($input.attr("max")) || 999;
            let value = parseInt($input.val()) || 0;
            if (value < max) {
                $input.val(value + 1).trigger("input");
            }
        });

        // When minus button clicked
        jQuery(document).on("click", ".rbfw_qty_minus", function(e) {
            e.preventDefault();
            let $input = $(this).siblings(".rbfw_bikecarsd_qty");
            let min = parseInt($input.attr("min")) || 0;
            let value = parseInt($input.val()) || 0;
            if (value > min) {
                $input.val(value - 1).trigger("input");
            }
        });

        function calculateServiceTotal() {
            let total = 0;

            jQuery(".rbfw_servicesd_qty").each(function() {
                let qty = parseInt(jQuery(this).val()) || 0;
                let price = parseFloat(jQuery(this).data("price")) || 0;
                total += qty * price;
            });

            jQuery("#rbfw_es_service_price").val(total);
            rbfw_price_calculation_sd();
        }

        // Plus button click
        $(document).on("click", ".rbfw_servicesd_qty_plus", function(e) {
            e.preventDefault();
            let $input = $(this).siblings(".rbfw_servicesd_qty");
            let max = parseInt($input.attr("max")) || 999;
            let value = parseInt($input.val()) || 0;
            if (value < max) {
                $input.val(value + 1).trigger("input");
            }
        });

        // Minus button click
        $(document).on("click", ".rbfw_servicesd_qty_minus", function(e) {
            e.preventDefault();
            let $input = $(this).siblings(".rbfw_servicesd_qty");
            let min = parseInt($input.attr("min")) || 0;
            let value = parseInt($input.val()) || 0;
            if (value > min) {
                $input.val(value - 1).trigger("input");
            }
        });

        // Input change
        $(document).on("input", ".rbfw_servicesd_qty", function() {
            calculateServiceTotal();
        });





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
                rbfw_price_calculation_sd();
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
                rbfw_price_calculation_sd();
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
            rbfw_price_calculation_sd();
        });

    });

})(jQuery)



function calculateTotal() {
    let total = 0;
    let hasQty = false;
    // Loop through each qty input
    jQuery(".rbfw_bikecarsd_qty").each(function() {
        let qty = parseInt(jQuery(this).val()) || 0;
        let price = parseFloat(jQuery(this).data("price")) || 0;
        total += qty * price;
        if (qty > 0) {
            hasQty = true; // mark that we found one
        }
    });
    if (hasQty) {
        jQuery('.rbfw_bikecarsd_es_price_table').show();
        jQuery('button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');
        jQuery(' button.rbfw_bikecarsd_book_now_btn').removeClass('rbfw_disabled_button');
    }else{
        jQuery('.rbfw_bikecarsd_es_price_table').hide();
        jQuery('button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
        jQuery('button.rbfw_bikecarsd_book_now_btn').addClass('rbfw_disabled_button');
    }
    // Display total somewhere (create #total_price element if needed)
    jQuery("#rbfw_service_price").val(total.toFixed(2));
    rbfw_price_calculation_sd();
}


function rbfw_price_calculation_sd(){
    let rbfw_service_price = parseInt(jQuery('#rbfw_service_price').val());
    var rbfw_es_service_price = parseInt(jQuery('#rbfw_es_service_price').val());
    var total_price = rbfw_service_price + rbfw_es_service_price;

    jQuery('.duration-costing span').text(rbfw_translation.currency + rbfw_service_price.toFixed(2));
    jQuery('.extra_service_cost span').text(rbfw_translation.currency + rbfw_es_service_price.toFixed(2));
    jQuery('.subtotal span').text(rbfw_translation.currency + total_price.toFixed(2));
    jQuery('.total span').text(rbfw_translation.currency + total_price.toFixed(2));
}

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


function rbfw_service_type_timely_stock_ajax(post_id,start_date,start_time='',enable_specific_duration = 'off'){
    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'  : 'rbfw_service_type_timely_stock',
            'post_id': post_id,
            'rbfw_bikecarsd_selected_date': start_date,
            'pickup_time': start_time,
            'enable_specific_duration': enable_specific_duration,
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

            var service_info = response.service_info;
            var extra_service_info = response.extra_service_info;

            jQuery('.single-type-timely').each(function() {
                var $el = jQuery(this);

                // Get the type from the data-text attribute
                var type = $el.data('text'); // e.g., "type 1"

                if (service_info[type]) {
                    // Update attributes
                    $el.attr('data-price', service_info[type].price);
                    $el.attr('data-available_quantity', service_info[type].stock);
                    // (Optional) Update displayed price text
                    $el.find('.price').text('€' + service_info[type].price);
                }
            });

            //jQuery('.rbfw_service_type_timely').html(response);
            jQuery('button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
            jQuery('button.rbfw_bikecarsd_book_now_btn').addClass('rbfw_disabled_button');
        }
    });
}


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
            jQuery('.rbfw_bikecarsd_price_summary_only').html(response);

        }
    });
}

