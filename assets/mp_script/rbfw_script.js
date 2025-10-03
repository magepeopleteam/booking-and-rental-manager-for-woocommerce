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
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarsd_type_list',
            'post_id': post_id,
            'selected_time': gTime,
            'selected_date': selected_date,
            'is_muffin_template': is_muffin_template,
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
            jQuery('html, body').animate({
                scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
            }, 100);
        }
    });
});



function rbfw_off_day_dates(date,type='',today_enable='no',dropoff=null){



    var curr_date = ("0" + (date.getDate())).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    var date_in = curr_date+"-"+curr_month+"-"+curr_year;
    var date_today = new Date();
    var rbfw_buffer_time = parseInt(jQuery("#rbfw_buffer_time").val());


    if(rbfw_buffer_time){
        date_today = new Date(date_today);
        date_today.setHours(date_today.getHours() + rbfw_buffer_time);
        date_today.setDate(date_today.getDate() - 1);
    }else{
        if(today_enable=='yes'){
            date_today.setDate(date_today.getDate() - 1);
        }
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
                return [false, "notav", 'Off'];
            }

        }else{
            return   true;
        }
    }else{

        if(type=='md'){

            let rbfw_rent_type = jQuery("#rbfw_rent_type").val();

            if(rbfw_rent_type == 'bike_car_md'){
                if(jQuery('#rbfw_month_wise_inventory').val()){
                    const  day_wise_inventory = JSON.parse(jQuery('#rbfw_month_wise_inventory').val());

                    if(day_wise_inventory[date_in]==0){
                        return [false, "notav", rbfw_translation.sold_out];
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


function getAvailableTimes(schedule, givenDate,rdfw_available_time,pickup_time_particular,is_calendar=null) {

    var rbfw_buffer_time = parseInt(jQuery("#rbfw_buffer_time").val());


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
    let  sapecific_date_time = false;
    let  time_enable = false;
    let past_time = ''

    const selectedDate = new Date(givenDate);

    const timeSelect = document.getElementById(pickup_time_particular);


    if(is_calendar=='calendar'){
        timeSelect.innerHTML = '';
    }else{
        timeSelect.innerHTML = '<option value="">'+ rbfw_translation.pickup_time +'</option>'; // reset options
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


}


function formatTime(date, format) {
    // Map WP PHP formats to JS Intl options
    let options = {};
    if (format.includes('a') || format.includes('A')) {
        options.hour12 = true;
    } else {
        options.hour12 = false;
    }
    options.hour = 'numeric';
    options.minute = '2-digit';

    return new Intl.DateTimeFormat([], options).format(date);
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


