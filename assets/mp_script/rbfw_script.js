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

// Item variations (Single Day): when the customer changes a size after a time slot is
// already chosen, reload the rate list so its per-rate quantities reflect the new size.
jQuery(document).on('change', '.rbfw_variation_field', function () {
    let $selectedTime = jQuery('.rbfw_bikecarsd_time.selected').not('.disabled');
    if ($selectedTime.length) {
        $selectedTime.trigger('click');
    }
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

    // Item variations (Single Day): send the chosen per-value quantities so the
    // rate list can cap each rate's available quantity by the selected size's
    // remaining stock and preserve the visitor's choices across the reload.
    let rbfw_variation_qty = {};
    jQuery('.rbfw-variation-qty-input').each(function () {
        let $input = jQuery(this);
        let fieldId = $input.attr('data-field-id');
        let valueName = $input.attr('data-value');
        let qty = parseInt($input.val(), 10) || 0;
        if (fieldId && valueName && qty > 0) {
            if (!rbfw_variation_qty[fieldId]) rbfw_variation_qty[fieldId] = {};
            rbfw_variation_qty[fieldId][valueName] = qty;
        }
    });

    jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarsd_type_list',
            'post_id': post_id,
            'selected_time': gTime,
            'selected_date': selected_date,
            'is_muffin_template': is_muffin_template,
            'rbfw_variation_qty': rbfw_variation_qty,
            'nonce' : rbfw_ajax_front.nonce_bikecarsd_type_list
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
            // Guard: element is absent on some templates (multi-hour/timely). An
            // unguarded .offset().top throw here aborts the complete sequence before
            // the global ajaxComplete fires, killing the variation-surcharge recalc.
            var $hdr = jQuery(".rbfw-bikecarsd-calendar-header");
            if ($hdr.length && $hdr.offset()) {
                jQuery('html, body').animate({
                    scrollTop: $hdr.offset().top
                }, 100);
            }
        }
    });
});



/**
 * Per-item "Block Booking If Date Range Contains Off Days" flag, printed as a
 * hidden input by the booking form templates. Items saved before the flag
 * existed have no input / an empty value — both count as enabled.
 *
 * The flag gates ONLY rule 3 (a pickup→return range may not span an off day).
 * Off days / off dates themselves stay unselectable as pickup or return
 * regardless of the flag — that is the plugin's normal off-day behavior.
 */
function rbfw_offday_blocking_enabled() {
    var $flag = jQuery('#rbfw_block_offday_booking');
    // Opt-in: interior-range blocking only when the admin explicitly turned it
    // on. A missing flag or any non-'on' value means blocking stays off.
    return $flag.length > 0 && $flag.val() === 'on';
}

/**
 * Rule 3 of the off-day blocking feature: true when any weekly off day or off
 * date range falls strictly BETWEEN the selected pickup date and a candidate
 * return date. The endpoints themselves are covered by rules 1–2 (the normal
 * off-day disable in rbfw_off_day_dates), so only the interior is scanned.
 *
 * @param {string} pickup_iso Selected pickup date, YYYY-MM-DD.
 * @param {Date}   end_date   Candidate return date from beforeShowDay.
 */
function rbfw_range_contains_off_day(pickup_iso, end_date) {
    if (!pickup_iso) return false;

    var off_days = [], offday_range = [];
    try { off_days     = JSON.parse(jQuery('#rbfw_off_days').val())     || []; } catch (e) {}
    try { offday_range = JSON.parse(jQuery('#rbfw_offday_range').val()) || []; } catch (e) {}
    if (!off_days.length && !offday_range.length) return false;

    var weekday = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
    var d = new Date(pickup_iso + 'T00:00:00');
    if (isNaN(d.getTime())) return false;
    d.setDate(d.getDate() + 1); // interior only — start the day after pickup

    var end = new Date(end_date.getFullYear(), end_date.getMonth(), end_date.getDate());
    var guard = 0; // hard cap so a corrupt date can never loop forever
    while (d < end && guard++ < 1100) {
        if (jQuery.inArray(weekday[d.getDay()], off_days) >= 0) return true;
        var ddmmyyyy = ("0" + d.getDate()).slice(-2) + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + d.getFullYear();
        if (jQuery.inArray(ddmmyyyy, offday_range) >= 0) return true;
        d.setDate(d.getDate() + 1);
    }
    return false;
}

function rbfw_off_day_dates(date,type='',today_enable='no',dropoff=null){



    var curr_date = ("0" + (date.getDate())).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    var date_in = curr_date+"-"+curr_month+"-"+curr_year;
    var date_today = new Date();
    var rbfw_buffer_time = parseInt(jQuery("#rbfw_buffer_time").val()) || 0;

    // Buffer (lead time) and "today booking enabled" are independent settings and
    // must compose. Previously a non-zero buffer always took the -1 day branch,
    // which silently ignored today_enable='no' and left today bookable. The -1 day
    // is what allows today, so it must depend on today_enable alone.
    // Mirrors the composition already used in md_script.js.
    if(rbfw_buffer_time){
        date_today.setHours(date_today.getHours() + rbfw_buffer_time);
    }
    if(today_enable=='yes'){
        date_today.setDate(date_today.getDate() - 1);
    }

    //alert(date_today);

   /* if(today_enable=='yes'){
        date_today.setDate(date_today.getDate() - 1);
    }*/

    var weekday = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
    var day_in = weekday[date.getDay()];
    var rbfw_off_days = JSON.parse(jQuery("#rbfw_off_days").val());

    var rbfw_offday_range = JSON.parse(jQuery("#rbfw_offday_range").val());




    if(jQuery.inArray( day_in, rbfw_off_days )>= 0 || jQuery.inArray( date_in, rbfw_offday_range )>= 0 || (date <  date_today) ){

        if(type=='md'){
            if((date <  date_today)){
                return [false, "notav", ''];
            }else{
                return [false, "notav", rbfw_translation.off_label];
            }

        }else{
            return   true;
        }
    }else{

        if(type=='md'){

            let rbfw_rent_type = jQuery("#rbfw_rent_type").val();

            if(rbfw_rent_type == 'bike_car_md' || rbfw_rent_type == 'bike_car_sd' || rbfw_rent_type == 'appointment'){
                if(jQuery('#rbfw_month_wise_inventory').val()){
                    const  day_wise_inventory = JSON.parse(jQuery('#rbfw_month_wise_inventory').val());

                    if(day_wise_inventory[date_in]==0){
                        return [false, "notav rbfw-soldout-day", rbfw_translation.sold_out];
                    }


                    if(dropoff){
                        // Additional check for return date selection
                        // If pickup date is already selected and we're selecting return date
                        let pickup_date = jQuery('input[name="rbfw_pickup_start_date"]').val();
                        if(pickup_date && pickup_date !== '') {
                            // Check if this is for return date calendar by checking if current date is after pickup date
                            let pickup_date_obj = new Date(pickup_date);
                            if(date > pickup_date_obj) {
                                // Check for first sold-out date in the sequence from pickup to current date
                                let current_check_date = new Date(pickup_date_obj);
                                current_check_date.setDate(current_check_date.getDate() + 1); // Start from day after pickup

                                while(current_check_date <= date) {
                                    let check_curr_date = ("0" + current_check_date.getDate()).slice(-2);
                                    let check_curr_month = ("0" + (current_check_date.getMonth() + 1)).slice(-2);
                                    let check_curr_year = current_check_date.getFullYear();
                                    let check_date_in = check_curr_date+"-"+check_curr_month+"-"+check_curr_year;

                                    // If we find a sold-out date in the sequence, disable all subsequent dates
                                    if(day_wise_inventory[check_date_in] == 0) {
                                        return [false, "notav", ''];
                                    }

                                    current_check_date.setDate(current_check_date.getDate() + 1);
                                }
                            }
                        }
                    }





                }

            }


            return [true, "av", ""];
        }else{
            return false;
        }
    }
}


// Convert a time string ("10:00 am", "2:30 PM" or 24h "14:30") to minutes-of-day
// so time options can be ordered clock-wise instead of by stored/string order.
function rbfwTimeToMinutes(t) {
    if (t === undefined || t === null) return 0;
    var s = String(t).trim();
    var ampm = s.match(/(am|pm)\s*$/i);
    var clean = s.replace(/\s*(am|pm)\s*$/i, '').trim();
    var parts = clean.split(':');
    var h = parseInt(parts[0], 10);
    var m = parseInt(parts[1], 10);
    if (isNaN(h)) h = 0;
    if (isNaN(m)) m = 0;
    if (ampm) {
        var mod = ampm[1].toLowerCase();
        if (mod === 'pm' && h !== 12) h += 12;
        if (mod === 'am' && h === 12) h = 0;
    }
    return h * 60 + m;
}

function getAvailableTimes(schedule, givenDate,rdfw_available_time,pickup_time_particular,is_calendar=null) {

    // Fall back to 0 when the buffer field is absent/empty: NaN here would make
    // setHours() below produce an Invalid Date, silently disabling the past-time check.
    var rbfw_buffer_time = parseInt(jQuery("#rbfw_buffer_time").val()) || 0;


    var scheduleJson = [];
    try {
        var parsedAvailable = (typeof schedule === 'string') ? JSON.parse(schedule) : schedule;
        if (Array.isArray(parsedAvailable)) {
            scheduleJson = parsedAvailable;
        } else if (parsedAvailable && typeof parsedAvailable === 'object') {
            scheduleJson = Object.values(parsedAvailable);
        } else {
            scheduleJson = [];
        }
    } catch (e) {
        scheduleJson = [];
    }
    // Safely parse and normalize rdfw_available_time into an array
    var rdfw_available_timeJson = [];
    try {
        var parsedAvailable = (typeof rdfw_available_time === 'string') ? JSON.parse(rdfw_available_time) : rdfw_available_time;
        if (Array.isArray(parsedAvailable)) {
            rdfw_available_timeJson = parsedAvailable;
        } else if (parsedAvailable && typeof parsedAvailable === 'object') {
            rdfw_available_timeJson = Object.values(parsedAvailable);
        } else {
            rdfw_available_timeJson = [];
        }
    } catch (e) {
        rdfw_available_timeJson = [];
    }
    // Order the general available times clock-wise.
    rdfw_available_timeJson.sort(function (a, b) {
        return rbfwTimeToMinutes(a && a.time) - rbfwTimeToMinutes(b && b.time);
    });
    let  sapecific_date_time = false;
    let  time_enable = false;
    let past_time = ''

    const selectedDate = new Date(givenDate);

    const timeSelect = document.getElementById(pickup_time_particular);


    if(is_calendar=='calendar'){
        timeSelect.innerHTML = '';
    }else{
        // This function fills both #pickup_time and #dropoff_time, so the placeholder
        // must follow the field being filled — otherwise Return Time reads "Pickup Time".
        var rbfw_time_placeholder = (pickup_time_particular === 'dropoff_time')
            ? (rbfw_translation.return_time || rbfw_translation.pickup_time)
            : rbfw_translation.pickup_time;
        timeSelect.innerHTML = '<option value="">'+ rbfw_time_placeholder +'</option>'; // reset options
    }


    // loop through data
    Object.values(scheduleJson).forEach(item => {
        const start = new Date(item.start_date);
        const end = new Date(item.end_date);

        // check if selected date is within range
        if (selectedDate >= start && selectedDate <= end) {


            var specific_available_time = [];
            try {
                var parsedAvailable = (typeof item.available_time === 'string') ? JSON.parse(item.available_time) : item.available_time;
                if (Array.isArray(parsedAvailable)) {
                    specific_available_time = parsedAvailable;
                } else if (parsedAvailable && typeof parsedAvailable === 'object') {
                    specific_available_time = Object.values(parsedAvailable);
                } else {
                    specific_available_time = [];
                }
            } catch (e) {
                specific_available_time = [];
            }

            // Order the date-specific available times clock-wise.
            specific_available_time.sort(function (a, b) {
                return rbfwTimeToMinutes(a && a.time) - rbfwTimeToMinutes(b && b.time);
            });

            specific_available_time.forEach(timeObj => {
                if (timeObj.status === "enabled") {

                    let current_date_time = new Date(rbfw_js_variables.currentDateTime.replace(" ", "T"));// new Date();
                    let actual_booking_date_time_format = new Date(current_date_time);
                    actual_booking_date_time_format.setHours(current_date_time.getHours() + rbfw_buffer_time);

                    let actual_booking_date_time = new Date(actual_booking_date_time_format);
                    let actual_booking_date = actual_booking_date_time.toLocaleDateString('en-CA');

                    let selectedDateStr = selectedDate.toISOString().split("T")[0];

                    if (selectedDateStr === actual_booking_date) {
                        // Parse available_time into a Date object for comparison
                        let [hours, minutes] = timeObj.time.split(":").map(Number);
                        //et timeDate = new Date(rbfw_js_variables.currentDateTime.replace(" ", "T"));
                        actual_booking_date_time_format.setHours(hours, minutes, 0, 0);

                        console.log('actual_booking_date_time_format',actual_booking_date_time_format);
                        // console.log('timeDate',timeDate);

                        if (actual_booking_date_time >= actual_booking_date_time_format) {
                            time_enable = true;
                            past_time = 'Past time';
                        }else{
                            time_enable = false;
                            past_time = '';
                        }
                    }


                    let myTime = timeObj.time;

                    // Split into hours and minutes
                    let [hours, minutes] = myTime.split(":").map(Number);

                    // Create a JS Date object for formatting
                    let date = new Date();
                    date.setHours(hours);
                    date.setMinutes(minutes);

                    sapecific_date_time = true;

                    if(is_calendar=='calendar'){

                        const a = document.createElement("a");
                        if(time_enable){
                            a.className = "rbfw_bikecarsd_time_disable";
                            a.title = "Past Time";
                        }else{
                            a.className = "rbfw_bikecarsd_time";
                        }

                        a.setAttribute("data-time", timeObj.time);

                        const span = document.createElement("span");
                        span.className = "rbfw_bikecarsd_time_span";
                        span.textContent = formatTime(date, rbfw_js_variables.timeFormat); timeObj.time;;

                        a.appendChild(span);
                        timeSelect.appendChild(a);

                    }else{
                        const option = document.createElement("option");
                        option.value = timeObj.time;
                        option.textContent = formatTime(date, rbfw_js_variables.timeFormat); timeObj.time;
                        option.disabled = time_enable;
                        option.title = past_time;
                        timeSelect.appendChild(option);
                    }
                }
            });
        }
    });

    if(sapecific_date_time==false && Array.isArray(rdfw_available_timeJson)){
        rdfw_available_timeJson.forEach(timeObj => {
            if (timeObj.status === "enabled") {


                let current_date_time = new Date(rbfw_js_variables.currentDateTime.replace(" ", "T"));// new Date();
                let actual_booking_date_time_format = new Date(current_date_time);
                actual_booking_date_time_format.setHours(current_date_time.getHours() + rbfw_buffer_time);

                let actual_booking_date_time = new Date(actual_booking_date_time_format);
                let actual_booking_date = actual_booking_date_time.toLocaleDateString('en-CA');

                let selectedDateStr = selectedDate.toISOString().split("T")[0];

                if (selectedDateStr === actual_booking_date) {
                    // Parse available_time into a Date object for comparison
                    let [hours, minutes] = timeObj.time.split(":").map(Number);
                    //et timeDate = new Date(rbfw_js_variables.currentDateTime.replace(" ", "T"));
                    actual_booking_date_time_format.setHours(hours, minutes, 0, 0);

                    console.log('actual_booking_date_time_format',actual_booking_date_time_format);
                   // console.log('timeDate',timeDate);

                    if (actual_booking_date_time >= actual_booking_date_time_format) {
                        time_enable = true;
                        past_time = 'Past time';
                    }else{
                        time_enable = false;
                        past_time = '';
                    }
                }


                let myTime = timeObj.time;  // 2:30 PM

                let [time, modifier] = myTime.split(" ");   // "2:30" and "PM"
                let [hours, minutes] = time.split(":").map(Number);

                if (modifier === "PM" && hours !== 12) {
                    hours += 12;
                }
                if (modifier === "AM" && hours === 12) {
                    hours = 0;
                }


                let date = new Date();

                const h = parseInt(hours, 10);
                const m = parseInt(minutes, 10);

                if (!isNaN(h) && !isNaN(m)) {
                    date.setHours(h);
                    date.setMinutes(m);
                    date.setSeconds(0);
                } else {
                    console.error("Invalid hours or minutes:", hours, minutes);
                }

                if (isNaN(date.getTime())) {
                    console.error("Invalid Date generated:", date);
                } else {
                    sapecific_date_time = true;

                    if (is_calendar === 'calendar') {
                        const a = document.createElement("a");
                        if (time_enable) {
                            a.className = "rbfw_bikecarsd_time_disable";
                            a.title = "Past Time";
                        } else {
                            a.className = "rbfw_bikecarsd_time";
                        }
                        a.setAttribute("data-time", timeObj.time);

                        const span = document.createElement("span");
                        span.className = "rbfw_bikecarsd_time_span";

                        console.log('date', formatTime(date, rbfw_js_variables.timeFormat));

                        span.textContent = formatTime(date, rbfw_js_variables.timeFormat);

                        a.appendChild(span);
                        timeSelect.appendChild(a);
                    }else{
                        const option = document.createElement("option");
                        option.value = timeObj.time;
                        option.textContent = formatTime(date, rbfw_js_variables.timeFormat); timeObj.time;
                        option.disabled = time_enable;
                        option.title = past_time;
                        timeSelect.appendChild(option);
                    }
                }







                /*let date = new Date();
                date.setHours(hours);
                date.setMinutes(minutes);
                sapecific_date_time = true;

                if(is_calendar=='calendar'){

                    const a = document.createElement("a");
                    if(time_enable){
                        a.className = "rbfw_bikecarsd_time_disable";
                        a.title = "Past Time";
                    }else{
                        a.className = "rbfw_bikecarsd_time";
                    }
                    a.setAttribute("data-time", timeObj.time);

                    const span = document.createElement("span");
                    span.className = "rbfw_bikecarsd_time_span";
                    span.textContent = formatTime(date, rbfw_js_variables.timeFormat); timeObj.time;;

                    a.appendChild(span);
                    timeSelect.appendChild(a);


                }else{
                    const option = document.createElement("option");
                    option.value = timeObj.time;
                    option.textContent = formatTime(date, rbfw_js_variables.timeFormat); timeObj.time;
                    option.disabled = time_enable;
                    option.title = past_time;
                    timeSelect.appendChild(option);
                }*/


            }
        })
    }

    let pickup_date = jQuery('#hidden_pickup_date').val();
    let dropoff_date = jQuery('#hidden_dropoff_date').val();
    let selected_time = jQuery('.pickup_time').val();


    // Only validate if both dates are selected and they are the same day
    if (pickup_date && dropoff_date && pickup_date == dropoff_date && selected_time) {
        // Convert pickup time to comparable format (HH:MM)
        let pickup_time_parts = selected_time.split(':');
        let pickup_hours = parseInt(pickup_time_parts[0]);
        let pickup_minutes = parseInt(pickup_time_parts[1]);
        let pickup_time_minutes = pickup_hours * 60 + pickup_minutes;

        // Clear current return time selection
        jQuery(".dropoff_time").val("").trigger("change");

        // Update return time options
        jQuery("#dropoff_time option").each(function() {
            var thisOptionValue = jQuery(this).val();
            if (thisOptionValue && thisOptionValue !== '') {
                // Convert return time to comparable format (HH:MM)
                let return_time_parts = thisOptionValue.split(':');
                let return_hours = parseInt(return_time_parts[0]);
                let return_minutes = parseInt(return_time_parts[1]);
                let return_time_minutes = return_hours * 60 + return_minutes;

                // Disable return times that are earlier than or equal to pickup time
                if (return_time_minutes <= pickup_time_minutes) {
                    jQuery(this).attr('disabled', true);
                } else {
                    jQuery(this).attr('disabled', false);
                }
            } else {
                jQuery(this).attr('disabled', true);
            }
        });
    }

    // After rendering the time slots, ask the server which ones are fully sold out
    // and disable them so customers cannot select an unavailable slot.
    rbfwDisableSoldOutTimes( jQuery( timeSelect ), givenDate, is_calendar );

}

/**
 * Fetch sold-out time slots for the selected date and disable them in the UI.
 */
function rbfwDisableSoldOutTimes( $container, selectedDate, isCalendar ) {
    if ( typeof rbfw_ajax_front === 'undefined' || ! rbfw_ajax_front.nonce_bikecarsd_sold_out_times ) {
        return;
    }

    // Timely single-day rentals are checked by rbfwFetchPickupSoldOut(), which
    // evaluates each option's real [pickup, pickup + duration) window. The legacy
    // endpoint below is date-based for bike_car_sd and would therefore disable
    // every pickup time after any booking exhausts the shared stock on that date.
    if ( jQuery( '#rbfw_rent_type' ).val() === 'bike_car_sd' && jQuery( '#manage_inventory_as_timely' ).val() === 'on' ) {
        return;
    }

    var postId = jQuery( '.rbfw_post_id' ).val();
    if ( ! postId || ! selectedDate ) {
        return;
    }

    var times = [];
    if ( isCalendar === 'calendar' ) {
        $container.find( 'a.rbfw_bikecarsd_time' ).each( function () {
            var t = jQuery( this ).attr( 'data-time' );
            if ( t ) times.push( t );
        } );
    } else {
        $container.find( 'option' ).each( function () {
            var t = jQuery( this ).val();
            if ( t ) times.push( t );
        } );
    }

    if ( ! times.length ) {
        return;
    }

    jQuery.post( rbfw_ajax_front.rbfw_ajaxurl, {
        action: 'rbfw_bikecarsd_sold_out_times',
        nonce: rbfw_ajax_front.nonce_bikecarsd_sold_out_times,
        post_id: postId,
        selected_date: selectedDate,
        times: times
    }, function ( resp ) {
        if ( resp && resp.success && resp.data && resp.data.sold_out_times && resp.data.sold_out_times.length ) {
            var soldOut = resp.data.sold_out_times;
            if ( isCalendar === 'calendar' ) {
                $container.find( 'a.rbfw_bikecarsd_time' ).each( function () {
                    var $a = jQuery( this );
                    if ( soldOut.indexOf( $a.attr( 'data-time' ) ) !== -1 ) {
                        $a.addClass( 'disabled rbfw-soldout-time' )
                          .removeClass( 'selected' )
                          .attr( 'title', ( typeof rbfw_translation !== 'undefined' && rbfw_translation.sold_out ) ? rbfw_translation.sold_out : 'Sold Out' );
                    }
                } );
            } else {
                $container.find( 'option' ).each( function () {
                    var $opt = jQuery( this );
                    if ( soldOut.indexOf( $opt.val() ) !== -1 ) {
                        $opt.prop( 'disabled', true ).addClass( 'rbfw-soldout-time' );
                    }
                } );
            }
        }
    } );
}


function formatTime(date, format) {
    // Build the time string directly from the WordPress "Time Format"
    // setting (Settings > General > Time Format) so the frontend always
    // shows English AM/PM instead of the browser locale (e.g. Hungarian de./du.).
    format = format || 'g:i a';

    let hours   = date.getHours();
    let minutes = date.getMinutes();
    let mm      = minutes < 10 ? '0' + minutes : '' + minutes;

    // 12-hour format when the WP format contains 'a' (am/pm) or 'A' (AM/PM)
    if (format.includes('a') || format.includes('A')) {
        let ampm = hours >= 12 ? 'PM' : 'AM';
        if (format.includes('a')) {
            ampm = ampm.toLowerCase(); // lowercase "am/pm" if WP uses lowercase 'a'
        }

        let h12 = hours % 12;
        if (h12 === 0) {
            h12 = 12;
        }

        // 'h' = leading zero (01-12), 'g' = no leading zero (1-12)
        let hh = format.includes('h') ? (h12 < 10 ? '0' + h12 : '' + h12) : '' + h12;

        return hh + ':' + mm + ' ' + ampm;
    }

    // 24-hour format: 'H' = leading zero (00-23), 'G' = no leading zero (0-23)
    let hh = format.includes('H') ? (hours < 10 ? '0' + hours : '' + hours) : '' + hours;

    return hh + ':' + mm;
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


/*jQuery(document).on('click', '.remove-row',function(e){
    if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
        jQuery(this).parents('.off_date_range_child').remove();
    } else {
        return false;
    }
});*/

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

// Real-time validation for pickup time change
jQuery(document).on('change', '.pickup_time', function() {
    let pickup_date = jQuery('#hidden_pickup_date').val();
    let dropoff_date = jQuery('#hidden_dropoff_date').val();
    let selected_time = jQuery('.pickup_time').val();


    // Only validate if both dates are selected and they are the same day
    if (pickup_date && dropoff_date && pickup_date == dropoff_date && selected_time) {
        // Convert pickup time to comparable format (HH:MM)
        let pickup_time_parts = selected_time.split(':');
        let pickup_hours = parseInt(pickup_time_parts[0]);
        let pickup_minutes = parseInt(pickup_time_parts[1]);
        let pickup_time_minutes = pickup_hours * 60 + pickup_minutes;

        // Clear current return time selection
        jQuery(".dropoff_time").val("").trigger("change");

        // Update return time options
        jQuery("#dropoff_time option").each(function() {
            var thisOptionValue = jQuery(this).val();
            if (thisOptionValue && thisOptionValue !== '') {
                // Convert return time to comparable format (HH:MM)
                let return_time_parts = thisOptionValue.split(':');
                let return_hours = parseInt(return_time_parts[0]);
                let return_minutes = parseInt(return_time_parts[1]);
                let return_time_minutes = return_hours * 60 + return_minutes;

                // Disable return times that are earlier than or equal to pickup time
                if (return_time_minutes <= pickup_time_minutes) {
                    jQuery(this).attr('disabled', true);
                } else {
                    jQuery(this).attr('disabled', false);
                }
            } else {
                jQuery(this).attr('disabled', true);
            }
        });
    }
});

function fee_management(sub_total_price,total_days=1,quantity=1){
    let rbfw_management_price = 0;
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
    return rbfw_management_price;
}


/*
 * Mobile layout reorder for the default single-rental template.
 *
 * Desktop: .mp_left_section (content) and .mp_right_section (booking form) sit
 * side by side as the two columns of .mp_details_page. On mobile (<=792px)
 * .mp_details_page becomes a single column, which would otherwise push the
 * booking form to the very bottom (below Description / FAQ / Related Items).
 *
 * The booking form and the features header (.rbfw-header-container) live at
 * different DOM depths, so they cannot be reordered with pure CSS without
 * flattening several wrappers that carry layout styles. We instead relocate the
 * whole .mp_right_section node (preserving its bound state) to sit directly
 * under .rbfw-header-container on mobile, and restore it as the last column on
 * larger screens. Guarded so it only runs where all three nodes exist (the
 * "default" template family); it no-ops on the muffin template and elsewhere.
 */
(function () {
    var rbfwBookingMq = window.matchMedia('(max-width: 792px)');

    function rbfwArrangeBookingForm() {
        var detailsPage = document.querySelector('.mp_details_page');
        if (!detailsPage) {
            return;
        }
        var rightSection = detailsPage.querySelector('.mp_right_section');
        var header = detailsPage.querySelector('.rbfw-header-container');
        if (!rightSection || !header) {
            return;
        }

        if (rbfwBookingMq.matches) {
            // Mobile: place the booking form right after the features header.
            if (header.nextElementSibling !== rightSection) {
                header.insertAdjacentElement('afterend', rightSection);
            }
        } else {
            // Desktop / tablet: keep the booking form as the last column.
            if (detailsPage.lastElementChild !== rightSection) {
                detailsPage.appendChild(rightSection);
            }
        }
    }

    // Re-run whenever the breakpoint is crossed (e.g. device rotation, resize).
    if (typeof rbfwBookingMq.addEventListener === 'function') {
        rbfwBookingMq.addEventListener('change', rbfwArrangeBookingForm);
    } else if (typeof rbfwBookingMq.addListener === 'function') {
        // Safari < 14 / legacy fallback.
        rbfwBookingMq.addListener(rbfwArrangeBookingForm);
    }

    // This script is enqueued in the footer, so the template markup above it is
    // already parsed: run immediately to avoid a visible jump, then re-run on
    // DOMContentLoaded as a safety net if the nodes were not ready yet.
    rbfwArrangeBookingForm();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', rbfwArrangeBookingForm);
    }
})();

/* ── Pickup-location chooser cards (Location Inventory & Price) ──
 * The cards block (templates/forms/location-cards.php) is the first child of
 * the booking form; CSS gates every later sibling until a card is chosen.
 * Choosing a card writes the slug into the form's rbfw_pickup_point field
 * (kept two-way in sync with the classic dropdown) and caps the quantity
 * controls to the location's remaining stock. The location charge itself is
 * applied server-side at add-to-cart (rbfw_apply_location_charge), so
 * nothing here affects the authoritative price.
 */
function rbfw_loc_cards_init($wrap) {
    var $ = jQuery;
    if (!$wrap || !$wrap.length || $wrap.data('rbfw-loc-inited')) return;
    var $form = $wrap.closest('form.mp_rbfw_ticket_form');
    if (!$form.length) return;
    $wrap.data('rbfw-loc-inited', true);

    function rbfwLocPointField() {
        var $select = $form.find('select[name="rbfw_pickup_point"]');
        if ($select.length) return $select;
        var $hidden = $form.find('input[name="rbfw_pickup_point"]');
        if (!$hidden.length) {
            $hidden = $('<input>', { type: 'hidden', name: 'rbfw_pickup_point' }).appendTo($form);
        }
        return $hidden;
    }

    function rbfwLocCapQuantity(stock) {
        if (isNaN(stock) || stock < 1) return;
        // select-based quantity (md and friends)
        $form.find('#rbfw_item_quantity_md, select[name="rbfw_item_quantity"]').each(function () {
            var $sel = $(this);
            $sel.find('option').each(function () {
                var v = parseInt($(this).val(), 10);
                if (!isNaN(v)) $(this).prop('disabled', v > stock);
            });
            var cur = parseInt($sel.val(), 10);
            if (!isNaN(cur) && cur > stock) $sel.val(String(stock)).trigger('change');
        });
        // input-based quantity
        $form.find('input[name="rbfw_item_quantity"]').each(function () {
            var $inp = $(this);
            $inp.attr('max', stock);
            var cur = parseInt($inp.val() || '1', 10);
            if (cur > stock) $inp.val(stock).trigger('change');
        });
    }

    $wrap.on('click', '.rbfw_loc_card', function () {
        var $card = $(this);
        if ($wrap.hasClass('rbfw_loc_waitdates')) return; // dates first — stock is date-wise
        if ($card.is(':disabled') || $card.hasClass('rbfw_loc_card_soldout')) return;

        $wrap.find('.rbfw_loc_card').removeClass('rbfw_loc_card_selected');
        $card.addClass('rbfw_loc_card_selected');
        $wrap.removeClass('rbfw_loc_pending');

        var loc = String($card.data('loc'));
        var $field = rbfwLocPointField();
        if ($field.is('select') && !$field.find('option[value="' + loc + '"]').length) {
            $field.append($('<option>', { value: loc, text: $card.find('.rbfw_loc_card_name').text() }));
        }
        if ($field.val() !== loc) $field.val(loc).trigger('change');

        rbfwLocCapQuantity(parseInt($card.data('stock'), 10));
    });

    // Keep the cards in sync if the classic dropdown is used directly.
    $form.on('change', 'select[name="rbfw_pickup_point"]', function () {
        var $card = $wrap.find('.rbfw_loc_card[data-loc="' + String($(this).val()) + '"]');
        if ($card.length && !$card.hasClass('rbfw_loc_card_selected')) $card.trigger('click');
    });

    /* Date-aware stock refresh: whenever the customer picks/changes booking
     * dates, re-fetch each location's remaining stock for that exact range
     * and update the card badges. A selected card that becomes sold out is
     * deselected and the form re-gated. */
    function rbfwLocApplyStock($card, left) {
        left = parseInt(left, 10);
        if (isNaN(left)) return;
        var txtAvail = $wrap.data('txt-available') || '%d unit(s) available';
        var txtSold  = $wrap.data('txt-soldout') || 'Sold out';
        var $stockEl = $card.find('.rbfw_loc_card_stock');

        $card.attr('data-stock', left).data('stock', left);
        if (left <= 0) {
            $card.addClass('rbfw_loc_card_soldout').prop('disabled', true);
            $stockEl.text(txtSold);
            if ($card.hasClass('rbfw_loc_card_selected')) {
                $card.removeClass('rbfw_loc_card_selected');
                $wrap.addClass('rbfw_loc_pending');
                rbfwLocPointField().val('').trigger('change');
            }
        } else {
            $card.removeClass('rbfw_loc_card_soldout').prop('disabled', false);
            $stockEl.text(txtAvail.replace('%d', left));
            if ($card.hasClass('rbfw_loc_card_selected')) {
                rbfwLocCapQuantity(left);
            }
        }
    }

    var rbfwLocStockTimer = null;
    function rbfwLocRefreshStocks() {
        if (typeof rbfw_ajax_front === 'undefined' || !rbfw_ajax_front.nonce_location_stock_info) return;
        var post_id = $wrap.data('post-id');
        if (!post_id) return;

        var start = $form.find('input[name="rbfw_pickup_start_date"]').val()
            || $form.find('input[name="rbfw_start_datetime"]').val()
            || $form.find('input[name="rbfw_bikecarsd_selected_date"]').val() || '';
        var end = $form.find('input[name="rbfw_pickup_end_date"]').val()
            || $form.find('input[name="rbfw_end_datetime"]').val() || start;
        if (!start) return;

        // Duration-based forms (multiple_items): no end-date field — derive it
        // from the chosen duration so availability covers the whole rental.
        if (end === start && $form.find('[name="durationType"]').length) {
            var dType = String($form.find('[name="durationType"]').val() || 'daily');
            var dQty  = parseInt($form.find('[name="durationQty"]').val(), 10) || 1;
            var dDays = dType === 'hourly' ? 0 : (dType === 'daily' ? dQty : (dType === 'weekly' ? dQty * 7 : dQty * 30));
            var dEnd  = new Date(String(start).slice(0, 10) + 'T00:00:00');
            if (!isNaN(dEnd.getTime()) && dDays > 0) {
                dEnd.setDate(dEnd.getDate() + dDays);
                end = dEnd.getFullYear() + '-' + ('0' + (dEnd.getMonth() + 1)).slice(-2) + '-' + ('0' + dEnd.getDate()).slice(-2);
            }
        }

        clearTimeout(rbfwLocStockTimer);
        rbfwLocStockTimer = setTimeout(function () {
            jQuery.post(rbfw_ajax_front.rbfw_ajaxurl, {
                action: 'rbfw_location_stock_info',
                post_id: post_id,
                start_date: start,
                end_date: end,
                nonce: rbfw_ajax_front.nonce_location_stock_info
            }, function (res) {
                if (!res || !res.success || !res.data) return;
                // Dates are known now — activate the cards with date-exact stock.
                if ($wrap.hasClass('rbfw_loc_waitdates')) {
                    $wrap.removeClass('rbfw_loc_waitdates');
                    var chooseTxt = $wrap.data('txt-note-choose');
                    if (chooseTxt) $wrap.find('.rbfw_loc_cards_note').text(chooseTxt);
                }
                $wrap.removeClass('rbfw_loc_cards_error');
                jQuery.each(res.data, function (slug, left) {
                    var $card = $wrap.find('.rbfw_loc_card[data-loc="' + slug + '"]');
                    if ($card.length) rbfwLocApplyStock($card, left);
                });
            });
        }, 250);
    }

    $form.on('change',
        'input[name="rbfw_pickup_start_date"], input[name="rbfw_pickup_end_date"], ' +
        'input[name="rbfw_start_datetime"], input[name="rbfw_end_datetime"], ' +
        'input[name="rbfw_bikecarsd_selected_date"], ' +
        'select[name="durationType"], input[name="durationQty"]',
        rbfwLocRefreshStocks
    );

    /* Some pricing modes arrive with dates already known — fixed event
     * start/end (multi-day items with the date picker disabled render them
     * as hidden inputs) or dates carried over from the search page. Activate
     * the cards immediately in that case; rbfwLocRefreshStocks() is a no-op
     * when no start date exists yet. */
    rbfwLocRefreshStocks();

    // Booking without a location: block the submit and point at the cards.
    $form.on('submit', function (e) {
        if (!$wrap.find('.rbfw_loc_card_selected').length) {
            e.preventDefault();
            e.stopImmediatePropagation(); // AJAX submitters must not fire either
            $wrap.addClass('rbfw_loc_cards_error');
            $wrap[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function () { $wrap.removeClass('rbfw_loc_cards_error'); }, 2500);
        }
    });
}

jQuery(function ($) {
    rbfw_loc_cards_init($('#rbfw_loc_cards_wrap'));
});

/* ── Per-variant quantity steppers (Size S/M/L… with per-value stock + price) ──
 * Phase 4. Replaces the single variation <select>: every value gets a −[n]+ stepper
 * capped at its per-date "N left" (the input's max attr, absent = unlimited). The
 * SUM of all stepper quantities drives the booking Quantity — the standalone Quantity
 * row is hidden while steppers are present — and each unit adds its per-value
 * surcharge (data-price) on top of the duration rate for the live SD/MD total. The
 * authoritative stock/price is re-checked server-side at add-to-cart
 * (rbfw_check_rental_availability + per-variant pricing); this is display/UX only.
 *
 * Delegated handlers survive the date-change AJAX that re-renders the steppers with
 * fresh "N left" counts. SD recalc is pure DOM math (no AJAX), so re-running it from
 * ajaxComplete cannot loop; MD recalc is scheduled via rbfwScheduleMdPriceCalculation().
 */
(function () {
    var $ = jQuery;

    function rbfwStepperMax($input) {
        var m = $input.attr('max');
        if (m === undefined || m === '') return Infinity; // unlimited stock
        var n = parseInt(m, 10);
        return isNaN(n) ? Infinity : n;
    }

    // Reflect value against fresh max: clamp down if availability dropped, then set
    // the −/+ disabled states. Never triggers change/AJAX, so it is loop-safe.
    function rbfwSyncStepper($stepper) {
        var $input = $stepper.find('.rbfw-variation-qty-input');
        if (!$input.length) return;
        var max = rbfwStepperMax($input);
        var val = parseInt($input.val(), 10) || 0;
        if (val > max) { val = max < 0 ? 0 : max; $input.val(val); }
        var soldOut = $input.prop('disabled');
        $stepper.find('.rbfw-qty-minus').prop('disabled', soldOut || val <= 0);
        $stepper.find('.rbfw-qty-plus').prop('disabled', soldOut || val >= max);
    }

    function rbfwSyncStepperScope($scope) {
        $($scope || document).find('.rbfw-variation-stepper').each(function () {
            rbfwSyncStepper($(this));
        });
    }

    function rbfwVariationRecalc($form) {
        if (!$form || !$form.length) $form = $(document);
        var $steppers = $form.find('.rbfw-variation-qty-input');
        if (!$steppers.length) return; // no variations on this form → inert

        var totalQty = 0, surcharge = 0;
        $steppers.each(function () {
            var q = parseInt($(this).val(), 10) || 0;
            var p = parseFloat($(this).attr('data-price')) || 0;
            totalQty += q;
            surcharge += q * p;
        });

        // Steppers own the Quantity: hide the standalone Quantity rows (their hidden
        // duration-rate inputs stay in the DOM and readable).
        $form.find('.timely_quqntity_table').hide();
        $form.find('.rbfw_quantity_md').hide();

        // Single-day variations charge the base rental rate ONCE: a value's price is
        // added separately as a surcharge, so its quantity must NOT multiply the
        // duration rate. Keep the submitted base quantity at 1 for the timely
        // single-day form; multi-day still lets the steppers own the quantity.
        var isSdTimely = $form.find('.rbfw_quantiry_area_sd').length > 0;
        var qtyToSet   = isSdTimely ? 1 : totalQty;

        // Mirror the base quantity into whichever quantity field the form submits so
        // the server sees it. Add the option when it is a <select>.
        var $qty = $form.find('#rbfw_item_quantity, #rbfw_item_quantity_md').first();
        if ($qty.length) {
            if ($qty.is('select') && !$qty.find('option[value="' + qtyToSet + '"]').length) {
                $qty.append($('<option>', { value: qtyToSet, text: qtyToSet }));
            }
            $qty.val(String(qtyToSet));
        }

        // Book button reflects whether anything is selected.
        var $btn = $form.find('button.rbfw_bikecarsd_book_now_btn, button.rbfw_book_now_btn');
        if (totalQty > 0) $btn.prop('disabled', false).removeClass('rbfw_disabled_button');
        else $btn.prop('disabled', true).addClass('rbfw_disabled_button');

        if (isSdTimely) {
            // Timely single-day: #rbfw_service_price holds the duration cost ONLY, and
            // the base rental is charged once (rate × 1). The per-value surcharge is
            // summed and rendered as its own line by rbfw_price_calculation_sd().
            var rate = parseFloat($form.find('.rbfw_sd_price_input').val()) || 0;
            $form.find('#rbfw_service_price').val(rate.toFixed(2));
            if (typeof rbfw_price_calculation_sd === 'function') rbfw_price_calculation_sd();
        } else if ($form.find('#rbfw_item_quantity_md').length) {
            // Multi-day: schedule the AJAX price recalculation so the variation
            // surcharge is included in the live subtotal/total.
            if (typeof rbfwScheduleMdPriceCalculation === 'function') {
                rbfwScheduleMdPriceCalculation();
            }
        } else if (typeof rbfw_price_calculation_sd === 'function') {
            // Calendar single-day: the duration cost is already in #rbfw_service_price
            // (set by calculateTotal). Just re-run the summary assembler so the
            // variation surcharge is added to subtotal/total.
            rbfw_price_calculation_sd();
        }
    }

    // Stepper −/+ (delegated so it survives date-change re-renders).
    $(document).on('click', '.rbfw-variation-steppers .rbfw-qty-plus, .rbfw-variation-steppers .rbfw-qty-minus', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $stepper = $btn.closest('.rbfw-variation-stepper');
        var $input = $stepper.find('.rbfw-variation-qty-input');
        if (!$input.length || $input.prop('disabled')) return;

        var val = parseInt($input.val(), 10) || 0;
        var max = rbfwStepperMax($input);
        if ($btn.hasClass('rbfw-qty-plus')) { if (val < max) val++; }
        else { if (val > 0) val--; }
        $input.val(val);

        rbfwSyncStepper($stepper);
        rbfwVariationRecalc($input.closest('form'));
    });

    // After any AJAX (notably the date-change re-render that returns fresh "N left"
    // steppers): re-clamp/refresh buttons everywhere, and recompute SD totals — SD
    // recalc is pure DOM so it cannot re-enter the AJAX cycle. MD is refreshed by its
    // own date-change success path, so it is skipped here to avoid a loop.
    $(document).ajaxComplete(function () {
        rbfwSyncStepperScope(document);
        $('form').each(function () {
            var $f = $(this);
            if (!$f.find('.rbfw-variation-qty-input').length) return;
            // Skip multi-day forms: their totals are driven by rbfwScheduleMdPriceCalculation().
            if ($f.find('.rbfw_bike_car_md_item_wrapper').length || $f.find('#rbfw_item_quantity_md').length) return;
            rbfwVariationRecalc($f);
        });
    });

    // Optional Add-ons check-list visual state: highlight the card when qty > 0.
    function rbfwSyncExtraServiceRows(scope) {
        $(scope || document).find('.rbfw_bikecarsd_es_price_table .rbfw_servicesd_qty').each(function () {
            var $row = $(this).closest('tr');
            if (parseInt($(this).val(), 10) > 0) {
                $row.addClass('rbfw-es-selected');
            } else {
                $row.removeClass('rbfw-es-selected');
            }
        });
    }

    $(document).on('input change', '.rbfw_bikecarsd_es_price_table .rbfw_servicesd_qty', function () {
        rbfwSyncExtraServiceRows(this);
    });

    // Non-quantity mode uses a hidden input toggled by a checkbox.
    $(document).on('change', '.rbfw_bikecarsd_es_price_table .rbfw_extra_service_sd_checkbox', function () {
        $(this).closest('tr').toggleClass('rbfw-es-selected', $(this).is(':checked'));
    });

    $(document).ajaxComplete(function () {
        rbfwSyncExtraServiceRows(document);
    });

    // Initial paint: sync buttons and run a one-off recalc so any server-rendered
    // default quantities feed into the summary immediately. MD forms are skipped in
    // ajaxComplete to avoid loops, but the initial call is safe (empty dates abort).
    $(function () {
        rbfwSyncStepperScope(document);
        rbfwSyncExtraServiceRows(document);
        $('form').each(function () {
            var $f = $(this);
            if (!$f.find('.rbfw-variation-qty-input').length) return;
            rbfwVariationRecalc($f);
        });
    });

    // Exposed so a date-change success handler can force a resync/recalc explicitly.
    window.rbfwVariationRecalc = rbfwVariationRecalc;
    window.rbfwSyncStepperButtons = rbfwSyncStepperScope;
})();
