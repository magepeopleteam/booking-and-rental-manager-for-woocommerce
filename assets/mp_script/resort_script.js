function rbfwResortDepositQuantity() {
    if (jQuery('#rbfw_security_deposit_per_quantity').val() !== 'yes') return 1;
    var rooms = 0;
    jQuery('.rbfw_room_qty').each(function () { rooms += Math.max(0, parseInt(jQuery(this).val(), 10) || 0); });
    return rooms;
}

/* Stock cap reader, shared with md_script.js (which loads first). Defined here
   defensively so this file still reads a cap correctly on its own: an absent or
   blank max means the row does not track stock (no cap), while a literal max="0"
   is a real sold-out and must hold at 0. Reading it as
   `parseInt(input.attr('max')) || N` turned that 0 into the fallback N, letting
   the +/- buttons raise the value past a cap the browser still enforced — the
   native form validation then refused the booking with "The value must be 0." */
if (typeof rbfwStepMax !== 'function') {
    window.rbfwStepMax = function ($input) {
        var raw = $input.attr('max');
        if (raw === undefined || raw === null || raw === '') {
            return Infinity;
        }
        var n = parseInt(raw, 10);
        return isNaN(n) ? Infinity : n;
    };
}


let room_prices_arr = {};
let service_prices_arr = {};

jQuery('body').on('focusin', '#checkin_date', function(e) {
    jQuery(this).datepicker({
        dateFormat: js_date_format,
        minDate: 0,
        beforeShowDay: function(date)
        {
            return rbfwDayStockWrap(date, rbfw_off_day_dates(date,'md',rbfw_today_booking_enable()));
        },
        onSelect: function (dateString, data) {
            let date_ymd_drop = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
            jQuery('input[name="rbfw_start_datetime"]').val(date_ymd_drop).trigger('change');

            /* Min & Max Booking Day addon: global limits, overridden by the first
               date-wise / seasonal range holding the check-in date — the rule the
               multi-day calendar and the add-to-cart check use (rbfwEffectiveMinMax). */
            let rbfw_eff = (typeof rbfwEffectiveMinMax === 'function')
                ? rbfwEffectiveMinMax(date_ymd_drop, jQuery('#rbfw_minimum_booking_day').val(), jQuery('#rbfw_maximum_booking_day').val())
                : { min: parseInt(jQuery('#rbfw_minimum_booking_day').val(), 10) || 0, max: parseInt(jQuery('#rbfw_maximum_booking_day').val(), 10) || 0 };
            let rbfw_minimum_booking_day = rbfw_eff.min;
            let rbfw_maximum_booking_day = rbfw_eff.max;
            // Without a day-long package the stay needs at least one night.
            let rbfw_min_nights = (jQuery('#rbfw_enable_resort_daylong_price').val() == 'no') ? 1 : 0;

            let selected_date_array = date_ymd_drop.split('-');
            let gYear = selected_date_array[0];
            let gMonth = selected_date_array[1];
            let gDay = selected_date_array[2];

            let minDate = new Date(gYear,  gMonth - 1, gDay );
            minDate.setDate(minDate.getDate() + Math.max(rbfw_minimum_booking_day, rbfw_min_nights));

            jQuery("#checkout_date").datepicker("option", "minDate", minDate);

            // The maximum stands on its own (0 = no cap). It used to apply only when a
            // minimum was also set, counted from the day BEFORE check-in, so "min 2,
            // no max" left no selectable return date at all.
            if(rbfw_maximum_booking_day){
                let maxDate = new Date(gYear,  gMonth - 1, gDay );
                maxDate.setDate(maxDate.getDate() + rbfw_maximum_booking_day);
                jQuery("#checkout_date").datepicker("option", "maxDate", maxDate );
            } else {
                jQuery("#checkout_date").datepicker("option", "maxDate", null );
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
            // Checkout calendar: on top of the normal off-day disable, block
            // check-in→checkout ranges spanning an off day (rule 3) when the
            // item's off-day blocking toggle is enabled.
            var base = rbfw_off_day_dates(date,'md',rbfw_today_booking_enable());
            if (Array.isArray(base) && !base[0]) {
                return base;
            }
            var checkin_iso = jQuery('#hidden_checkin_date').val();
            if (rbfw_offday_blocking_enabled() && checkin_iso && rbfw_range_contains_off_day(checkin_iso, date)) {
                var offLabel = (typeof rbfw_translation !== 'undefined' && rbfw_translation.off_label) ? rbfw_translation.off_label : 'Off';
                return [false, 'notav', offLabel];
            }
            return rbfwDayStockWrap(date, base);
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
    let max = rbfwStepMax(input);
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
    let max = rbfwStepMax(input);
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
    let resort_total_days = parseFloat(jQuery('#resort_total_days').val()) || 0;
    jQuery('.rbfw_room_qty').each(function() {
        let qty = parseInt(jQuery(this).val()) || 0;
        let price = parseFloat(jQuery(this).data('price')) || 0;
        // Exact stay total of one room (seasonal / multi-day rates included), so the
        // figure is not rebuilt from a rounded per-night average.
        let stay_total = parseFloat(jQuery(this).data('stay_total'));
        room_duration_price += qty * (isNaN(stay_total) ? resort_total_days * price : stay_total);
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

    jQuery('#rbfw_room_duration_price').val(room_duration_price.toFixed(2));
    jQuery('.duration-costing .price-figure').text(wc_price_rbfw(room_duration_price));

    rbfwResortRenderSummary();
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

    // Value AND visibility together: the row used to sit there reading "Resource Cost 0.00"
    // whenever no add-on was chosen. Scoped to the resort summary so it cannot reach the
    // identically-named row in the single-day / multi-day templates.
    var $rbfw_resort_resource = jQuery('.rbfw_room_price_summary').find('li.resource-costing');
    $rbfw_resort_resource.find('.price-figure').text(wc_price_rbfw(resort_extra_service));
    if (resort_extra_service > 0) {
        $rbfw_resort_resource.show();
    } else {
        $rbfw_resort_resource.hide();
    }

    rbfwResortRenderSummary();
}


/**
 * Discount Over X Days on the resort summary.
 *
 * The tier for this stay length is resolved server-side into
 * #rbfw_resort_discount_type / #rbfw_resort_discount_number. Like the cart
 * (rbfw_resort_discount()), it comes off rooms + extra services only — never off
 * fees or the refundable deposit — and a fixed amount is per booking.
 *
 * @param {number} rental_subtotal Rooms + extra services.
 * @return {number}
 */
function rbfwResortDiscount(rental_subtotal) {
    var type = jQuery('#rbfw_resort_discount_type').val();
    var number = parseFloat(jQuery('#rbfw_resort_discount_number').val()) || 0;
    if (!type || number <= 0 || rental_subtotal <= 0) {
        return 0;
    }
    var amount = (type === 'percentage') ? rental_subtotal * number / 100 : number;
    return Math.min(Math.max(0, amount), rental_subtotal);
}


/**
 * Repaint the resort summary from the room and service figures already worked
 * out: fees, discount, security deposit, tax and total. One implementation for
 * every control that changes the price, so they cannot drift apart.
 */
function rbfwResortRenderSummary() {
    let sub_total_price = (parseFloat(jQuery('#rbfw_room_duration_price').val()) || 0) + (parseFloat(jQuery('#rbfw_extra_service_price').val()) || 0);
    let resort_total_days = parseFloat(jQuery('#resort_total_days').val()) || 0;

    let rbfw_management_price = 0;
    jQuery('.rbfw-management-price-resort:checked').each(function() {
        let price_type = jQuery(this).data('price_type');
        let price = parseFloat(jQuery(this).data('price')) || 0;
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
    jQuery('.management-costing span').text(wc_price_rbfw(rbfw_management_price));

    let discount_price = rbfwResortDiscount(sub_total_price);
    let $discount_row = jQuery('.rbfw_room_price_summary li.discount');
    if (discount_price > 0) {
        $discount_row.find('.price-figure').text(wc_price_rbfw(discount_price));
        $discount_row.show();
    } else {
        $discount_row.hide();
    }

    let rbfw_security_deposit_actual_amount = 0;
    if(jQuery('#rbfw_security_deposit_enable').val() == 'yes'){
        let rbfw_security_deposit_amount  = jQuery('#rbfw_security_deposit_amount').val();
        if (jQuery('#rbfw_security_deposit_type').val() == 'percentage'){
            rbfw_security_deposit_actual_amount = (rbfw_security_deposit_amount / 100) * sub_total_price;
        }else{
            rbfw_security_deposit_actual_amount = (parseFloat(rbfw_security_deposit_amount) || 0) * rbfwResortDepositQuantity();
        }
    }

    var total_price = sub_total_price + rbfw_management_price - discount_price + parseFloat(rbfw_security_deposit_actual_amount);
    // Tax line (shared helper; refundable deposit excluded from the taxable base).
    total_price += rbfwTaxLine(total_price - parseFloat(rbfw_security_deposit_actual_amount || 0));
    if(rbfw_security_deposit_actual_amount){
        jQuery('.security_deposit').show();
        jQuery('.security_deposit span').html(wc_price_rbfw(parseFloat(rbfw_security_deposit_actual_amount)));
    }


    jQuery('.subtotal .price-figure').text(wc_price_rbfw(sub_total_price));
    jQuery('.total .price-figure').text(wc_price_rbfw(total_price));
}


jQuery(document).on('change', '.rbfw-management-price-resort', function() {

    // A required fee cannot be unticked. (This used to call e.preventDefault() on an
    // undefined `e` and throw, so the summary never refreshed.)
    if (jQuery(this).hasClass('rbfw-fee-required')) {
        jQuery(this).prop('checked', true);
    }

    var $checkbox = jQuery(this);
    var $parent = $checkbox.closest('.rbfw-checkbox'); // find the parent container
    var $managementQty = $parent.find('.rbfw-management-qty'); // hidden input to set yes/no

    if ($checkbox.is(':checked')) {
        $managementQty.val('yes'); // Mark as selected
    } else {
        $managementQty.val('no'); // Mark as not selected
    }

    calculateTotalManagementPriceResort();
});



function calculateTotalManagementPriceResort() {
    rbfwResortRenderSummary();
}
