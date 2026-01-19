

document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('.rbfw_muiti_items_qty');
    const summaryDiv = document.getElementById('rbfw-items-summary');

    function updateSummary() {
        let summaryHtml = '';
        let hasItems = false;
        var durationType = jQuery('#durationType').val();
        var durationQty = parseInt(jQuery('#durationQty').val()) || 1;

        qtyInputs.forEach(input => {
            const qty = parseInt(input.value);
            if (qty > 0) {
                const itemName = input.dataset.name;
                const pricePerUnit = parseFloat(input.dataset[`price${durationType.charAt(0).toUpperCase() + durationType.slice(1)}`]) || 0;

                const total = (qty * durationQty *  pricePerUnit).toFixed(2);

                summaryHtml += `<li class="item-type"><span>${itemName}</span> <span>${qty} x ${durationQty} x <span>${rbfw_js_variables.currency}${pricePerUnit}</span> = <span>${rbfw_js_variables.currency}${total}</span></li>`;

                hasItems = true;
            }
        });


        // summaryDiv.innerHTML = hasItems ? summaryHtml : '';
    }

    // Attach event listeners to quantity inputs
    qtyInputs.forEach(input => { 
        input.addEventListener('input', updateSummary);
    });

    // Also trigger update when clicking plus or minus buttons
    const plusButtons = document.querySelectorAll('.rbfw_qty_plus');
    const minusButtons = document.querySelectorAll('.rbfw_qty_minus');
    const durationType = document.querySelectorAll('#durationType');

    [...plusButtons, ...minusButtons].forEach(btn => {
        btn.addEventListener('click', function () {
            // Slight delay to ensure value updates first
            setTimeout(updateSummary, 50);
        });
    });

    [...durationType].forEach(btn => {
        btn.addEventListener('change', function () {
            // Slight delay to ensure value updates first
            setTimeout(updateSummary, 50);
        });
    });
});

 // pricing table show/hide
jQuery(document).on('click', '.pricing-info-view', function (e) {
    e.preventDefault();
    jQuery(this).closest('.rbfw-pricing-info-heading').toggleClass('open');
    jQuery(this).closest('.mp_rbfw_ticket_form').toggleClass('overlay');
    jQuery('.price-item-container').stop(true, true).toggleClass('open').fadeToggle();
});
jQuery(document).on('click', '.pricing-content-container .close-price-container', function (e) {
    e.preventDefault();
    parent = jQuery(this).closest('.pricing-content-container');
    parent.find('.price-item-container').removeClass('open').hide();
    parent.find('.rbfw-pricing-info-heading').removeClass('open');
    parent.closest('.mp_rbfw_ticket_form').removeClass('overlay');
});

jQuery('body').on('focusin', '.pickup_date', function(e) {
    jQuery(this).datepicker({
        dateFormat: js_date_format,
        minDate: '',
        beforeShowDay: function(date)
        {
            // Use enhanced inventory checking that considers return date
            return rbfw_enhanced_pickup_beforeShowDay(date);
        },
        onSelect: function (dateString, data) {
            let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_pickup_start_date"]').val(date_ymd).trigger('change');

            let post_id = jQuery('#rbfw_post_id').val();
            let rbfw_enable_time_slot = jQuery('#rbfw_enable_time_slot').val();

            let rbfw_minimum_booking_day = parseInt(jQuery('#rbfw_minimum_booking_day').val());
            let rbfw_maximum_booking_day = parseInt(jQuery('#rbfw_maximum_booking_day').val());


            let selected_date_array = date_ymd.split('-');
            let gYear = selected_date_array[0];
            let gMonth = selected_date_array[1];
            let gDay = selected_date_array[2];

            let minDate = new Date(gYear,  gMonth - 1, gDay );
            minDate.setDate(minDate.getDate() + rbfw_minimum_booking_day);

            jQuery(".dropoff_date").datepicker("option", "minDate", minDate);


            if(rbfw_minimum_booking_day){
                let maxDate = new Date(gYear,  gMonth - 1, gDay - 1 );
                maxDate.setDate(maxDate.getDate() + rbfw_maximum_booking_day);
                jQuery(".dropoff_date").datepicker("option", "maxDate", maxDate );
            }

            if(rbfw_enable_time_slot=='yes'){
               // particular_time_date_dependent_ajax(post_id,date_ymd,'time_enable',rbfw_enable_time_slot,'.rbfw-select.rbfw-time-price.pickup_time');
                let rbfw_particulars_data = jQuery('#rbfw_particulars_data').val();
                let rdfw_available_time = jQuery('#rdfw_available_time').val();
                getAvailableTimes(rbfw_particulars_data , date_ymd,rdfw_available_time,'pickup_time');



            }
            
            // Trigger calendar refresh to apply enhanced inventory checking
            setTimeout(function() {
                rbfw_refresh_calendar_with_inventory_check();
            }, 200);
        },
    });

   jQuery(document).on("mousemove", ".ui-datepicker-calendar td", function() {
        let $this = jQuery(this);
        console.log($this.attr('title'));
        if ($this.find(".date-label").length === 0) {
            let dateText = $this.attr('title');
          
            if (dateText) {
                $this.append(`<span class='date-label'>`+dateText+`</span>`);
            }
        }
    });
});






jQuery('body').on('change', 'input[name="rbfw_pickup_start_date"]', function(e) {


    //const endDate = getURLParameter('rbfw_end_date');

    jQuery(".dropoff_date").val('');


    jQuery('.dropoff_date').datepicker({
        dateFormat: js_date_format,
        onSelect: function (dateString, data) {
            let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_pickup_end_date"]').val(date_ymd).trigger('change');

            let post_id = jQuery('#rbfw_post_id').val();
            let rbfw_enable_time_slot = jQuery('#rbfw_enable_time_slot').val();

            if(rbfw_enable_time_slot=='yes'){
                let rbfw_particulars_data = jQuery('#rbfw_particulars_data').val();
                let rdfw_available_time = jQuery('#rdfw_available_time').val();
                getAvailableTimes(rbfw_particulars_data , date_ymd,rdfw_available_time,'dropoff_time');
               // particular_time_date_dependent_ajax(post_id,date_ymd_drop,'',rbfw_enable_time_slot,'.rbfw-select.rbfw-time-price.dropoff_time');
            }
            
            // Trigger calendar refresh to apply enhanced inventory checking
            rbfw_refresh_calendar_with_inventory_check();
        },
        beforeShowDay: function(date)
        {
            return rbfw_off_day_dates(date,'md',rbfw_js_variables.rbfw_today_booking_enable,'yes');
        }
    });
});




jQuery('.dropoff_date').click(function(e) {
    let pickup_date = jQuery('[name="rbfw_pickup_start_date"]').val();
    if (pickup_date == '') {
        alert(rbfw_translation.select_pickup_date);
    }
});


jQuery('body').on('change', '.pickup_date', function(e) {
    jQuery(".dropoff_date").val('');
});
jQuery('body').on('change', '#hidden_pickup_date, #hidden_dropoff_date, .pickup_time, .dropoff_time', function (e) {

    let pickup_date = jQuery('#pickup_date').val();
    let dropoff_date = jQuery('#dropoff_date').val();
    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let dropoff_time = jQuery('#dropoff_time').find(':selected').val();
    let rbfw_available_time = jQuery('#rbfw_available_time').val();

    if (!dropoff_date) {
        //jQuery('.mps_alert_warning').remove();
        //jQuery('<div class="rbfw_nia_notice mps_alert_warning">Please enter drop off date!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
    }


    if(rbfw_available_time=='yes'){
        if(pickup_date && dropoff_date && pickup_time && dropoff_time){
            rbfw_bikecarmd_ajax_price_calculation();
        }
    }else{
        if(pickup_date && dropoff_date){
            rbfw_bikecarmd_ajax_price_calculation();
        }
    }
});




jQuery('body').on('change', '#rbfw_search_type', function (e) {
    var selectedOption = jQuery(this).find('option:selected');
    var post_id = selectedOption.data('post_id');

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarmd_ajax_min_max_and_offdays_info',
            'post_id': post_id,
            'nonce' : rbfw_ajax_front.nonce_bikecarmd_ajax_min_max_and_offdays_info

        },
        beforeSend: function() {
            jQuery('.rbfw_bike_car_md_item_wrapper').addClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {

            jQuery('#rbfw_minimum_booking_day').attr('value',response.rbfw_minimum_booking_day);
            jQuery('#rbfw_maximum_booking_day').attr('value',response.rbfw_maximum_booking_day);
            jQuery('#rbfw_off_days').attr('value',response.rbfw_off_days);
            jQuery('#rbfw_offday_range').attr('value',response.rbfw_offday_range);

            jQuery('.rbfw_bike_car_md_item_wrapper').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper i.fa-spinner').remove();

        },
        error : function(response){
            console.log(response);
        }
    });
})


jQuery('body').on('focusin', '.pickup_date_search', function(e) {


    jQuery(this).datepicker({
        dateFormat: js_date_format,
        minDate: '',
        beforeShowDay: function(date)
        {
            // Use enhanced inventory checking for search calendar as well
            return rbfw_enhanced_pickup_beforeShowDay(date);
        },
        onSelect: function (dateString, data) {
            let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_pickup_date_search"]').val(date_ymd).trigger('change');

            let post_id = jQuery('#rbfw_post_id').val();


            let rbfw_minimum_booking_day = parseInt(jQuery('#rbfw_minimum_booking_day').val());
            let rbfw_maximum_booking_day = parseInt(jQuery('#rbfw_maximum_booking_day').val());
            let selected_date_array = date_ymd.split('-');
            let gYear = selected_date_array[0];
            let gMonth = selected_date_array[1];
            let gDay = selected_date_array[2];

            let minDate = new Date(gYear,  gMonth - 1, gDay );
            minDate.setDate(minDate.getDate() + rbfw_minimum_booking_day);
            jQuery(".dropoff_date_search").datepicker("option", "minDate", minDate);
        },
    });

});


jQuery('body').on('change', 'input[name="rbfw_pickup_date_search"]', function(e) {
    jQuery('.dropoff_date_search').datepicker({
        dateFormat: js_date_format,
        onSelect: function (dateString, data) {
            let date_ymd_drop = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_dropoff_date_search"]').val(date_ymd_drop).trigger('change');
        },
        beforeShowDay: function(date)
        {
            // Use enhanced inventory checking for search dropoff calendar as well
            return rbfw_enhanced_pickup_beforeShowDay(date);
        }

    });
});


jQuery(window).on('load', function() {

    if(jQuery('#body-class').val()=='single-rbfw_item'){
        jQuery('body').addClass('single-rbfw_item');
    }


    let pickup_date = jQuery('#pickup_date').val();
    let dropoff_date = jQuery('#dropoff_date').val();
    if(pickup_date && dropoff_date){

        const date = new Date(pickup_date);

        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2); // Months are 0-based
        const day = ('0' + date.getDate()).slice(-2);
        const date_ymd = `${year}-${month}-${day}`;
        jQuery('input[name="rbfw_pickup_start_date"]').val(date_ymd).trigger('change');


        rbfw_bikecarmd_ajax_price_calculation();
    }
})



jQuery(document).on('change', '#rbfw_item_quantity_md', function(e) {
    let that = jQuery(this);
    var total_days = jQuery('[name="total_days"]').val();

    if(total_days){
        rbfw_service_price_calculation(total_days);
    }
});



/*Extra service start*/



jQuery(document).ready(function () {


    const durationTypeSelect = jQuery('#durationType');
    const pickupDateInput = jQuery('#pickupDate');
    const pickupTimeInput = jQuery('#pickupTime');
    const qtyLabel = jQuery('#qtyLabel');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    pickupDateInput.attr('min', today).val(today);

    // Set default time to current time + 1 hour
    const now = new Date();
    now.setHours(now.getHours() + 1);
    pickupTimeInput.val(now.toTimeString().slice(0, 5));

    // Change quantity functions
    window.changeQty = function (inputId, delta) {
        const input = jQuery('#' + inputId);
        const currentValue = parseInt(input.val()) || 1;
        const newValue = Math.max(1, currentValue + delta);
        input.val(newValue);
        jQuery(input).trigger('change');
    }

    window.changeItemQty = function (itemType, delta) {
        const input = jQuery(`[data-item="${itemType}"]`);
        const currentValue = parseInt(input.val()) || 0;
        const newValue = Math.max(0, currentValue + delta);
        input.val(newValue);

    }

    function updateQtyLabel() {
        const durationType = durationTypeSelect.val();
        if (durationType) {
            const typeMap = {
                hourly: rbfw_translation.hours,
                daily: rbfw_translation.days,
                weekly: rbfw_translation.weeks,
                monthly: rbfw_translation.months
            };
            qtyLabel.text(rbfw_translation.number_of+ ` ${typeMap[durationType]}`);
        } else {
            qtyLabel.text(rbfw_translation.number_of+' '+rbfw_translation.duration);
        }
    }

    // Add event listener
    durationTypeSelect.on('change', function () {
        updateQtyLabel();
    });

    // Initial label update
    updateQtyLabel();
});




jQuery('body').on('change', '#hidden_pickup_date, .pickup_time, #durationType, #durationQty', function (e) {

    let pickup_date = jQuery('#pickup_date').val();
    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let durationType = jQuery('#durationType').val();
    let durationQty = jQuery('#durationQty').val();
    let rbfw_enable_time_slot = jQuery('#rbfw_enable_time_slot').val();

    const changedElement = e.target.id || e.target.className;

    if(changedElement == 'durationType'){
        jQuery('.item-price').children('span').hide();
        jQuery('.item-price .rbfw_'+durationType+'_price').show();
    }

    if (!pickup_date) {
        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
    }

    if(pickup_date  && durationType && durationQty){
        if(rbfw_enable_time_slot=='no'){
            calculateTotalMultipleItems();
        }else{
            if(pickup_time){
                calculateTotalMultipleItems();
            }
        }

    }

});




// Handle quantity change multiple items

jQuery(document).on('input', '.rbfw_muiti_items_qty', function () {
    let $input = jQuery(this);
    let val = parseInt($input.val()) || 0;
    const max = parseInt($input.attr('max'));
    if (val > max) {
        val = max;
        jQuery(this).val(val); // Set it back to max
    }
    calculateTotalMultipleItems(true);
});

    // Handle plus button
jQuery(document).on('click', '.rbfw_multi_items_qty_plus', function () {
    const row = jQuery(this).closest('.rbfw-resource-item');
    const input = row.find('.rbfw_muiti_items_qty');
    const max = parseInt(input.attr('max')) || 100;
    let value = parseInt(input.val()) || 0;
    if (value < max) {
        input.val(value + 1).trigger('input');
    }

});

    // Handle minus button
jQuery(document).on('click', '.rbfw_multi_items_qty_minus', function () {
    const row = jQuery(this).closest('.rbfw-resource-item');
    const input = row.find('.rbfw_muiti_items_qty');
    let value = parseInt(input.val()) || 0;
    if (value > 0) {
        input.val(value - 1).trigger('input');
    }
});

    // Initial calculation on page load

// Checkbox change
jQuery(document).on('change', '.rbfw_service_price_data', function() {
    let itemClass = '.rbfw_service_quantity.item_' + jQuery(this).data('item');
    if (jQuery(this).is(':checked')) {
        jQuery(itemClass).show();
    } else {
        jQuery(itemClass).hide();
    }
    calculateTotalSingleItem();
});

// Quantity change
jQuery(document).on('input change', '.rbfw_service_qty', function() {
    calculateTotalSingleItem();
});

// Plus button
jQuery(document).on('click', '.rbfw_service_quantity_plus', function(e) {
    e.preventDefault();
    let input = jQuery(this).siblings('input');
    let val = parseInt(input.val()) || 0;
    let max = parseInt(input.attr('max')) || 9999;
    if (val < max) {
        input.val(val + 1).trigger('change');
    }
});

// Minus button
jQuery(document).on('click', '.rbfw_service_quantity_minus', function(e) {
    e.preventDefault();
    let input = jQuery(this).siblings('input');
    let val = parseInt(input.val()) || 0;
    let min = parseInt(input.attr('min')) || 0;
    if (val > min) {
        input.val(val - 1).trigger('change');
    }
});




// Checkbox toggle
jQuery(document).on('change', '.rbfw-resource-price', function() {
    let inputBox = jQuery(this).closest('tr').find('.rbfw_bikecarmd_es_input_box');

    if (jQuery(this).is(':checked')) {
        inputBox.show();
    } else {
        inputBox.hide();
    }

    var $checkbox = jQuery(this);
    var $qtyInput = jQuery('.rbfw_bikecarmd_es_qty[data-name="' + $checkbox.data('name') + '"]');

    if ($checkbox.is(':checked')) {
        $qtyInput.val(1); // Set value to 1 when checked
    } else {
        $qtyInput.val(0); // Optional: reset to 0 when unchecked
    }

    calculateTotalExtraService();
});




// Quantity changes
jQuery(document).on('input change', '.rbfw_bikecarmd_es_qty', function() {
    calculateTotalExtraService();
});

// Plus button click
jQuery(document).on('click', '.rbfw_bikecarmd_es_qty_plus', function(e) {
    e.preventDefault();
    let input = jQuery(this).siblings('input');
    console.log('input',input);
    let val = parseInt(input.val()) || 0;
    let max = parseInt(input.attr('max')) || 9999;
    if (val < max) {
        input.val(val + 1).trigger('change');
    }
});

// Minus button click
jQuery(document).on('click', '.rbfw_bikecarmd_es_qty_minus', function(e) {
    e.preventDefault();
    let input = jQuery(this).siblings('input');
    let val = parseInt(input.val()) || 0;
    let min = parseInt(input.attr('min')) || 0;
    if (val > min) {
        input.val(val - 1).trigger('change');
    }
});



jQuery(document).on('input', '.rbfw_muiti_items_additional_service_qty', function () {
    let $input = jQuery(this);
    let val = parseInt($input.val()) || 0;
    const max = parseInt($input.attr('max'));
    if (val > max) {
        val = max;
        jQuery(this).val(val); // Set it back to max
    }
    calculateAdditional(true);
});

// Handle plus button
jQuery(document).on('click', '.rbfw_additional_service_qty_plus', function () {
    const row = jQuery(this).closest('.service-price-item');
    const input = row.find('.rbfw_muiti_items_additional_service_qty');
    const max = parseInt(input.attr('max')) || 100;
    let value = parseInt(input.val()) || 0;
    if (value < max) {
        input.val(value + 1).trigger('input');
    }
    calculateAdditional(true);
});

// Handle minus button
jQuery(document).on('click', '.rbfw_additional_service_qty_minus', function () {

    const row = jQuery(this).closest('.service-price-item');
    const input = row.find('.rbfw_muiti_items_additional_service_qty');
    let value1 = parseInt(input.val()) || 0;

    if (value1 > 0) {
        input.val(value1 -1).trigger('input');
    }
    calculateAdditional(true);
});

// Initial calculation on page load



jQuery(document).on('change', '.rbfw-management-price', function() {

    if (jQuery(this).hasClass('rbfw-fee-required')) {
        jQuery(this).prop('checked', true);
        e.preventDefault(); // prevent change
    }


    var $checkbox = jQuery(this);
    var $parent = $checkbox.closest('.rbfw-checkbox'); // find the parent container
    var $managementQty = $parent.find('.rbfw-management-qty'); // hidden input to set yes/no

    if ($checkbox.is(':checked')) {
        $managementQty.val('yes'); // Mark as selected
    } else {
        $managementQty.val('no'); // Mark as not selected
    }

    calculateTotalManagementPrice();
});



function rbfw_multi_items_ajax_price_calculation(){

    let post_id = jQuery('[data-service-id]').data('service-id');
    let date_format = jQuery('#wp_date_format').val();
    let rbfw_available_time = jQuery('#rbfw_available_time').val();
    let rbfw_duration_price = jQuery('#rbfw_duration_price').val();
    let rbfw_service_category_price = jQuery('#rbfw_service_category_price').val();
    let pickup_date = jQuery('#pickup_date').val();
    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let durationType = jQuery('#durationType').val();
    let durationQty = jQuery('#durationQty').val();

    if(pickup_date == ''){
        return false;
    }

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_multi_items_ajax_price_calculation',
            'post_id': post_id,
            'date_format': date_format,
            'pickup_date': pickup_date,
            'pickup_time': pickup_time,
            'durationType': durationType,
            'durationQty': durationQty,
            'rbfw_duration_price': rbfw_duration_price,
            'rbfw_service_category_price': rbfw_service_category_price,
            'rbfw_available_time': rbfw_available_time,
            'nonce' : rbfw_ajax_front.nonce_multi_items_ajax_price_calculation
        },
        beforeSend: function() {
            jQuery('.rbfw_multi_items_wrapper_inner').addClass('rbfw_loader_in');
            jQuery('.rbfw_multi_items_wrapper_inner').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_multi_items_wrapper_inner').removeClass('rbfw_loader_in');
            jQuery('.rbfw_multi_items_wrapper_inner i.fa-spinner').remove();

            jQuery('[name="total_days"]').val(response.total_days);

            //alert(response.rbfw_management_price_html);

            let rbfw_management_price = 0;
            jQuery('.rbfw-management-price:checked').each(function() {
                let price_type = jQuery(this).data('price_type');
                let price = parseFloat(jQuery(this).data('price')) || 0;
                if(price_type == 'percentage'){
                    let sub_total_price = response.duration_price_number + response.service_cost;
                    rbfw_management_price += ( price/100 ) * sub_total_price;
                }else{
                    rbfw_management_price += price;
                }
            });







            jQuery('.management-costing .price-figure').text( wc_price_rbfw(rbfw_management_price));


            jQuery('.resource-costing .price-figure').html(response.service_cost_html);


            jQuery('.subtotal .price-figure').html(response.sub_total_price_html);

            jQuery('.rbfw_pricing_applied').hide();


            if(response.duration_price_number){
                jQuery('.duration-costing').show();
                jQuery('.duration-costing .price-figure').html(response.duration_price_html);
            }else{
                jQuery('.duration-costing').hide();
            }

            if(response.security_deposit_amount){
                jQuery('.security_deposit').show();
                jQuery('.security_deposit span').html(response.security_deposit_desc);
            }else{
                jQuery('.security_deposit').hide();
            }

            jQuery('.total .price-figure').text( wc_price_rbfw(rbfw_management_price + response.total_price));

            jQuery('.rbfw-duration').show();
            jQuery('.rbfw-duration .item-content').html(response.total_duration);
            jQuery('.rbfw-duration .rbfw_duration_md').val(response.total_duration);


            jQuery('.rbfw_quantity_md').show();

            jQuery('.rbfw-variations-content-wrapper').show();
            jQuery('.rbfw_resourse_md').show();
            jQuery('.rbfw_bikecarmd_price_result').show();
            jQuery('.rbfw_reg_form_rb').show();

            /*multiple service price day wise*/
            jQuery(".service-price-item").each(function(index, value) {
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show().addClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").hide();
                    jQuery(this).find(".rbfw_service_price_data").data('quantity',0);
                }else{
                    jQuery(this).find(".rbfw-sold-out").hide().removeClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").show();
                }
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw_muiti_items_additional_service_qty").attr('value',response.max_available_qty.service_stock[index]);
                }
                jQuery(this).find(".rbfw_muiti_items_additional_service_qty").attr('max',response.max_available_qty.service_stock[index]);

            });

            /*extra service */

            jQuery(".rbfw_muiti_items_qty").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).val(0);
                }
                jQuery(this).attr('max',response.max_available_qty.extra_service_instock[index]);
            });

            calculateAdditional(true);


        },
        error : function(response){
            console.log(response);
        }
    });
}

function calculateAdditional() {
    let additional_price = 0;
    jQuery('.rbfw_muiti_items_additional_service_qty').each(function () {
        const price = parseFloat(jQuery(this).data('price')) || 0;
        const service_price_type = jQuery(this).data('service_price_type');
        const quantity = jQuery(this).val() || 0;
        if(service_price_type=='day_wise'){
            const rbfw_total_days = jQuery('#rbfw_total_days').val();
            additional_price += price * quantity * rbfw_total_days;
        }else{
            additional_price += price * quantity;
        }
    });

    if(additional_price){
        jQuery('#AddonsPrice span').text(wc_price_rbfw(additional_price));
        jQuery('#AddonsPrice').show();
    }else{
        jQuery('#AddonsPrice').hide();
    }

    jQuery('#rbfw_service_category_price').val(additional_price.toFixed(2));

    var sub_total_price = additional_price + parseInt(jQuery('#rbfw_management_price').val()) + parseInt(jQuery('#rbfw_duration_price').val());



    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }
    var total_price = sub_total_price  + parseFloat(rbfw_security_deposit_actual_amount);
    if(rbfw_security_deposit_actual_amount){
        jQuery('.security_deposit').show();
        jQuery('.security_deposit span').html(wc_price_rbfw(parseFloat(rbfw_security_deposit_actual_amount)));
    }


    jQuery('.subtotal .price-figure').text(wc_price_rbfw(sub_total_price));
    jQuery('.total .price-figure').text(wc_price_rbfw(total_price));

}

function calculateTotalExtraService() {
    let extra_service_price = 0;

    // Loop through all checked services
    jQuery('.rbfw-resource-price:checked').each(function() {
        let price = parseFloat(jQuery(this).data('price')) || 0;

        // Find the corresponding quantity input
        let qtyInput = jQuery(this).closest('tr').find('.rbfw_bikecarmd_es_qty');
        let qty = parseInt(qtyInput.val()) || 1;

        extra_service_price += price * qty;
    });



    // Show total in a container with id="total_price"
    var rbfw_service_price = jQuery('#rbfw_service_price').val();
    jQuery('#rbfw_es_service_price').val(extra_service_price.toFixed(2));

    var resourse_cost = parseFloat(rbfw_service_price) + parseFloat(extra_service_price);

    jQuery('.resource-costing span').text(wc_price_rbfw(resourse_cost));

    let sub_total_price = resourse_cost + parseFloat(jQuery('#rbfw_duration_price').val());

    let rbfw_management_price = 0;
    let quantity = parseFloat(jQuery('#rbfw_item_quantity_md').val()) || 1;
    let total_days = parseFloat(jQuery('#rbfw_total_days').val()) || 1;

    jQuery('.rbfw-management-price:checked').each(function() {
        let price_type = jQuery(this).data('price_type');
        let price = parseFloat(jQuery(this).data('price')) || 0;
        let frequency = jQuery(this).data('frequency');

        if (price_type === 'percentage') {
            rbfw_management_price += ((price / 100) * sub_total_price);
        } else {
            if (frequency === 'one-time') {
                rbfw_management_price += price * quantity;
            } else {
                rbfw_management_price += price * quantity * total_days;
            }
        }
    });

    jQuery('#rbfw_management_price').val(rbfw_management_price.toFixed(2));
    jQuery('.management-costing span').text(wc_price_rbfw(rbfw_management_price));



    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }


    var total_price = sub_total_price + rbfw_management_price +  parseFloat(rbfw_security_deposit_actual_amount);
    jQuery('.security_deposit span').html(wc_price_rbfw(parseFloat(rbfw_security_deposit_actual_amount)));


    jQuery('.subtotal .price-figure').html(wc_price_rbfw(sub_total_price));
    jQuery('.total .price-figure').html(wc_price_rbfw(total_price));

}

function calculateTotalManagementPrice() {


    let rbfw_duration_price = parseFloat(jQuery('#rbfw_duration_price').val()) || 0;
    let rbfw_service_price = parseFloat(jQuery('#rbfw_service_price').val()) || 0;
    let extra_service_price = parseFloat(jQuery('#rbfw_es_service_price').val()) || 0;
    let quantity = parseFloat(jQuery('#rbfw_item_quantity_md').val()) || 1;
    let total_days = parseFloat(jQuery('#rbfw_total_days').val()) || 1;

    let sub_total_price = rbfw_duration_price + rbfw_service_price + extra_service_price;

    let rbfw_management_price = fee_management(sub_total_price,total_days,quantity);


    jQuery('#rbfw_management_price').val(rbfw_management_price.toFixed(2));
    jQuery('.management-costing span').text(wc_price_rbfw(rbfw_management_price));

    let rbfw_security_deposit_actual_amount = 0;

    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }

    let total_price = sub_total_price + rbfw_management_price + parseFloat(rbfw_security_deposit_actual_amount);



    jQuery('.security_deposit span').html(wc_price_rbfw(rbfw_security_deposit_actual_amount));
    jQuery('.subtotal .price-figure').html(wc_price_rbfw(sub_total_price));
    jQuery('.total .price-figure').html(wc_price_rbfw(total_price));

}

function calculateTotalSingleItem() {
    let service_price = 0;

    // Loop through all checked services
    jQuery('.rbfw_service_price_data:checked').each(function() {
        let price = parseFloat(jQuery(this).data('price')) || 0;
        let qtyInput = jQuery('input[name="' + jQuery(this).attr('name').replace('[main_cat_name]', '[quantity]') + '"]');
        let service_price_type = jQuery(this).data('service_price_type');
        let qty = parseInt(qtyInput.val()) || 1;


        if(service_price_type=='day_wise'){
            const rbfw_total_days = jQuery('#rbfw_total_days').val();
            service_price += price * qty * rbfw_total_days;
        }else{
            service_price += price * qty;
        }

    });





    // Show total in an element with id="total_price"
    jQuery('#rbfw_service_price').val(service_price.toFixed(2));

    var resourse_cost = service_price + parseInt(jQuery('#rbfw_es_service_price').val());

    jQuery('.resource-costing span').text(wc_price_rbfw(resourse_cost));

    var sub_total_price = resourse_cost + parseFloat(jQuery('#rbfw_duration_price').val());

    let rbfw_management_price = 0;
    let quantity = parseFloat(jQuery('#rbfw_item_quantity_md').val()) || 1;
    let total_days = parseFloat(jQuery('#rbfw_total_days').val()) || 1;

    jQuery('.rbfw-management-price:checked').each(function() {
        let price_type = jQuery(this).data('price_type');
        let price = parseFloat(jQuery(this).data('price')) || 0;
        let frequency = jQuery(this).data('frequency');

        if (price_type === 'percentage') {
            rbfw_management_price += ((price / 100) * sub_total_price);
        } else {
            if (frequency === 'one-time') {
                rbfw_management_price += price * quantity;
            } else {
                rbfw_management_price += price * quantity * total_days;
            }
        }
    });

    jQuery('#rbfw_management_price').val(rbfw_management_price.toFixed(2));
    jQuery('.management-costing span').text(wc_price_rbfw(rbfw_management_price));


    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
             rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
        }
    }
    var total_price = sub_total_price + rbfw_management_price + parseFloat(rbfw_security_deposit_actual_amount);
    jQuery('.security_deposit span').html(wc_price_rbfw(parseFloat(rbfw_security_deposit_actual_amount)));

    jQuery('.subtotal .price-figure').html(wc_price_rbfw(sub_total_price));
    jQuery('.total .price-figure').html(wc_price_rbfw(total_price));

}


function calculateTotalMultipleItems(only_calculation=false) {

    var durationType = jQuery('#durationType').val();
    var durationQty = parseInt(jQuery('#durationQty').val()) || 1;
    var item_total_price = 0;
    jQuery('.rbfw_muiti_items_qty').each(function () {
        var $qtyInput = jQuery(this);
        var pricePerUnit = parseFloat($qtyInput.data('price-' + durationType)) || 0;
        var quantity = parseInt($qtyInput.val()) || 0;

        var itemTotal = pricePerUnit * quantity * durationQty;
        $qtyInput
            .closest('.rbfw_qty_input')
            .find('.rbfw_item_peice')
            .val(pricePerUnit.toFixed(2));

        item_total_price += itemTotal;
    });


    if(item_total_price){
        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
        jQuery('.multi-service-category-section').show();
    }else{
        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
        jQuery('.multi-service-category-section').hide();
    }


    jQuery('#rbfw_duration_price').val(item_total_price.toFixed(2));

    var sub_total_price = item_total_price + parseInt(jQuery('#rbfw_service_category_price').val());

    let rbfw_management_price = fee_management(sub_total_price,1,1);



    if(only_calculation){

        let rbfw_security_deposit_actual_amount = 0;
        if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
            let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
            if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
                rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
            }else{
                rbfw_security_deposit_actual_amount = rbfw_security_deposit_amount;
            }
        }
        var total_price = sub_total_price + rbfw_management_price + parseFloat(rbfw_security_deposit_actual_amount);

        if(rbfw_security_deposit_actual_amount){
            jQuery('.security_deposit').show();
            jQuery('.security_deposit span').html(wc_price_rbfw(parseFloat(rbfw_security_deposit_actual_amount)));
        }

        jQuery('.subtotal .price-figure').text(wc_price_rbfw(sub_total_price));
        jQuery('.total .price-figure').text(wc_price_rbfw(total_price));


    }else{
        rbfw_multi_items_ajax_price_calculation();
    }
}

/**
 * Refresh calendar to apply enhanced inventory checking after return date selection
 */
function rbfw_refresh_calendar_with_inventory_check() {
    // Small delay to ensure the return date is properly set
    setTimeout(function() {
        var pickup_date = jQuery('[name="rbfw_pickup_start_date"]').val();
        var return_date = jQuery('[name="rbfw_pickup_end_date"]').val();
        
        if(pickup_date && return_date) {
            // Refresh the datepicker to trigger beforeShowDay callback
            jQuery('.pickup_date').datepicker('refresh');
            jQuery('.dropoff_date').datepicker('refresh');
            
            // Also update any other calendar instances that might exist
            jQuery('.pickup_date_search').datepicker('refresh');
            jQuery('.dropoff_date_search').datepicker('refresh');
            
            console.log('Calendar refreshed with enhanced inventory checking for range:', pickup_date, 'to', return_date);
        }
    }, 100);
}

/**
 * Enhanced beforeShowDay callback for pickup date - just use normal logic
 */
function rbfw_enhanced_pickup_beforeShowDay(date) {
    // For pickup dates, use normal inventory checking
    return rbfw_off_day_dates(date, 'md', rbfw_js_variables.rbfw_today_booking_enable, false);
}



function rbfw_bikecarmd_ajax_price_calculation(stock_no_effect){


    let post_id = jQuery('[data-service-id]').data('service-id');
    let date_format = jQuery('#wp_date_format').val();
    let pickup_date = jQuery('#hidden_pickup_date').val();
    let dropoff_date = jQuery('#hidden_dropoff_date').val();
    let rbfw_available_time = jQuery('#rbfw_available_time').val();
    let pickup_time = jQuery('.pickup_time').find(':selected').val();
    let dropoff_time = jQuery('.dropoff_time').find(':selected').val();

    let item_quantity = jQuery('#rbfw_item_quantity_md').find(':selected').val();
    let rbfw_enable_variations = jQuery('#rbfw_enable_variations').val();

    var rbfw_input_stock_quantity = jQuery('#rbfw_input_stock_quantity').val();

    if(typeof item_quantity === "undefined"){
        item_quantity = jQuery("[name='rbfw_item_quantity']").val();
    }

    let rbfw_service_price = jQuery('#rbfw_service_price').val();
    let rbfw_es_service_price = jQuery('#rbfw_es_service_price').val();

    let rbfw_management_price = jQuery('#rbfw_management_price').val();

    let rbfw_enable_time_slot = jQuery('#rbfw_enable_time_slot').val();

    if(pickup_date == '' || dropoff_date == ''){
        return false;
    }

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarmd_ajax_price_calculation',
            'post_id': post_id,
            'date_format': date_format,
            'pickup_date': pickup_date,
            'pickup_time': pickup_time,
            'dropoff_date': dropoff_date,
            'dropoff_time': dropoff_time,
            'item_quantity': item_quantity,
            'rbfw_service_price': rbfw_service_price,
            'rbfw_enable_variations': rbfw_enable_variations,
            'rbfw_es_service_price': rbfw_es_service_price,
            'rbfw_management_price': rbfw_management_price,
            'rbfw_available_time': rbfw_available_time,
            'rbfw_enable_time_slot': rbfw_enable_time_slot,
            'nonce' : rbfw_ajax_front.nonce_bikecarmd_ajax_price_calculation
        },
        beforeSend: function() {
            jQuery('.rbfw_bike_car_md_item_wrapper').addClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_bike_car_md_item_wrapper').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper i.fa-spinner').remove();

            jQuery('[name="total_days"]').val(response.total_days);

            let rbfw_management_price = 0;
            jQuery('.rbfw-management-price:checked').each(function() {
                let price_type = jQuery(this).data('price_type');
                let price = parseFloat(jQuery(this).data('price')) || 0;
                if(price_type == 'percentage'){
                    let sub_total_price = response.duration_price_number + response.service_cost;
                    rbfw_management_price += ( price/100 ) * sub_total_price;
                }else{
                    let frequency = jQuery(this).data('frequency');
                    if(frequency == 'one-time' ){
                        rbfw_management_price += price * response.ticket_item_quantity;
                    }else{
                        rbfw_management_price += price * response.ticket_item_quantity * response.total_days
                    }
                }
            });


            jQuery('.resource-costing .price-figure').html(response.service_cost_html);

            jQuery('.management-costing .price-figure').text( wc_price_rbfw(rbfw_management_price));

            jQuery('.subtotal .price-figure').html(response.sub_total_price_html);

            jQuery('.rbfw_pricing_applied').hide();

            if(response.pricing_applied != 'No'){
                jQuery('.rbfw_pricing_applied.'+response.pricing_applied).show();
            }


            if(response.duration_price_number){
                jQuery('.duration-costing').show();
                jQuery('.duration-costing .price-figure').html(response.duration_price_html);
            }else{
                jQuery('.duration-costing').hide();
            }


            if(response.discount){
                jQuery('.discount').show();
                jQuery('.discount span').html(response.discount_html);
                jQuery('.discount').show();
            }else{
                jQuery('.discount').hide();
            }

            if(response.security_deposit_amount){
                jQuery('.security_deposit').show();
                jQuery('.security_deposit span').html(response.security_deposit_desc);
            }else{
                jQuery('.security_deposit').hide();
            }

            jQuery('.total .price-figure').text( wc_price_rbfw(rbfw_management_price + response.total_price));
            jQuery('.rbfw-duration').show();
            jQuery('.rbfw-duration .item-content').html(response.total_duration);
            jQuery('.rbfw-duration .item-price').html(response.duration_price_html);
            jQuery('.rbfw-duration .rbfw_duration_md').val(response.total_duration);
            jQuery('#rbfw_duration_price').val(response.duration_price);


            var remaining_stock =  response.max_available_qty.remaining_stock;
            var ticket_item_quantity =  response.ticket_item_quantity;

            var  quantity_options = '';
            for (let i = 0; i <= remaining_stock; i++) {
                var selected = '';
                if(ticket_item_quantity == i){
                    var selected = 'selected';
                }
                if(i==0){
                    quantity_options += "<option "+selected+" value="+i+">Choose number of quantity</option>";
                }else{
                    quantity_options += "<option "+selected+" value="+i+">"+i+"</option>";
                }
            }

            jQuery('#rbfw_item_quantity_md').html(quantity_options);


            jQuery('.rbfw_quantity_md').show();
            jQuery('.multi-service-category-section').show();
            jQuery('.rbfw-variations-content-wrapper').show();
            jQuery('.rbfw_resourse_md').show();
            jQuery('.rbfw_bikecarmd_price_result').show();
            jQuery('.rbfw_reg_form_rb').show();

            /*multiple service price day wise*/
            jQuery(".service-price-item").each(function(index, value) {
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show().addClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").hide();
                    jQuery(this).find(".rbfw_service_price_data").data('quantity',0);
                }else{
                    jQuery(this).find(".rbfw-sold-out").hide().removeClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").show();
                }
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw_service_info_stock").attr('value',response.max_available_qty.service_stock[index]);
                }
                jQuery(this).find(".rbfw_service_info_stock").attr('max',response.max_available_qty.service_stock[index]);

                jQuery(this).find(".remaining_stock").text('('+response.max_available_qty.service_stock[index]+')');
            });

            /*extra service */

            jQuery(".rbfw_bikecarmd_es_qty").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).val(0);
                }
                jQuery(this).attr('max',response.max_available_qty.extra_service_instock[index]);
            });
            jQuery(".es_stock").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show().addClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").hide();
                }
                jQuery(this).text(response.max_available_qty.extra_service_instock[index]);
            });
            jQuery(".rbfw_bikecarmd_es_hidden_input_box").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show().addClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").hide();
                }else{
                    jQuery(this).find(".rbfw-sold-out").hide().removeClass("rbfw_sold_out");
                    jQuery(this).find(".rbfw-checkbox").show();
                }
            });


            if(response.rbfw_enable_variations == 'yes'){
                var total_variation_stock = 0;
                jQuery(".rbfw_variant").each(function(index, value) {
                    var variant_text = jQuery(this).val();
                    if(response.max_available_qty.variant_instock[index]<response.ticket_item_quantity){
                        jQuery(this).attr("disabled", 'disabled');
                        jQuery(this).text(variant_text+' (stock out)' + ' available quantity: ' + response.max_available_qty.variant_instock[index]);
                        if(jQuery(this).is(':selected')){
                            jQuery(this).removeAttr("selected");
                        }
                    }else{
                        total_variation_stock = 1;
                        jQuery(this).removeAttr("disabled");
                        jQuery(this).text(variant_text + ' (available quantity: ' +' '+ response.max_available_qty.variant_instock[index] +')');
                    }
                });

                if((total_variation_stock == 0)) {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('<div class="rbfw_nia_notice mps_alert_warning">' + rbfw_translation.no_items_available + '</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                }else {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                }
            }else{

                if((rbfw_input_stock_quantity == 'no_has_value')) {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                }else{
                    if((response.max_available_qty.remaining_stock <= 0)) {
                        jQuery('.rbfw_nia_notice').remove();
                        jQuery('<div class="rbfw_nia_notice mps_alert_warning">' + rbfw_translation.no_items_available + '</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                    } else {
                        jQuery('.rbfw_nia_notice').remove();
                        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                    }
                }
            }

        },
        error : function(response){
            console.log(response);
        }
    });
}


function rbfw_service_price_calculation(total_days){
    jQuery(".rbfw_service_price_data").val(0);
    jQuery('.rbfw_service_quantity').css( "display", "none" );
    jQuery('.available-stock').css('display','none');
    var total = 0;
    jQuery(".rbfw_service_price_data:checked").each(function() {
        var item_no = jQuery(this).data('item');
        jQuery(this).val(1);
        var service_price_type =  jQuery(this).data('service_price_type');
        var service_quantity = jQuery(this).data('quantity');
        var rbfw_enable_md_type_item_qty = jQuery(this).data('rbfw_enable_md_type_item_qty');
        if(rbfw_enable_md_type_item_qty=='yes'){
            jQuery('.item_'+item_no).removeAttr('style');
            jQuery('.available-stock'+'.item_'+item_no).css('display','block');
        }
        if(service_price_type=='day_wise'){
            total +=  jQuery(this).data('price')*service_quantity*total_days;
        }else{
            total +=  jQuery(this).data('price')*service_quantity;
        }
    });
    jQuery('#rbfw_service_price').val(total);
    rbfw_bikecarmd_ajax_price_calculation();
}

function wc_price_rbfw(price) {
    if(rbfw_js_variables.currency_format=='left'){
        return rbfw_js_variables.currency + price.toFixed(rbfw_js_variables.price_decimals)
    }else{
        return price.toFixed(rbfw_js_variables.price_decimals) + rbfw_js_variables.currency;
    }
}







