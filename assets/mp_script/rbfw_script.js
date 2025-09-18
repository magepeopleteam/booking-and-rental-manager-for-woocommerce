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



/**
 * Enhanced inventory checking for return date selection
 * Sequential check: finds first sold out date from pickup, then disables all dates after it
 */
function rbfw_check_return_date_inventory(pickup_date, return_date, current_date, day_wise_inventory) {
    try {
        var pickup_dt = new Date(pickup_date);
        var return_dt = new Date(return_date);
        var current_dt = new Date(current_date.getFullYear(), current_date.getMonth(), current_date.getDate());
        
        // Find the first sold out date starting from pickup date
        var check_date = new Date(pickup_dt);
        var first_sold_out_date = null;
        
        // Check each date sequentially from pickup date onwards
        while (check_date.getTime() <= current_dt.getTime() + (365 * 24 * 60 * 60 * 1000)) { // Check up to 1 year ahead
            var curr_date_str = ("0" + check_date.getDate()).slice(-2) + "-" + 
                               ("0" + (check_date.getMonth() + 1)).slice(-2) + "-" + 
                               check_date.getFullYear();
            
            // If we find a sold out date, mark it and break
            if (day_wise_inventory[curr_date_str] === 0) {
                first_sold_out_date = new Date(check_date);
                break;
            }
            
            check_date.setDate(check_date.getDate() + 1);
        }
        
        // If no sold out date found, check current date's own inventory
        if (first_sold_out_date === null) {
            var current_date_str = ("0" + current_dt.getDate()).slice(-2) + "-" + 
                                  ("0" + (current_dt.getMonth() + 1)).slice(-2) + "-" + 
                                  current_dt.getFullYear();
            
            if (day_wise_inventory[current_date_str] === 0) {
                return { available: false, message: 'Sold Out' };
            }
            
            return { available: true, message: '' };
        }
        
        // If we found a sold out date, disable all dates after it (including available ones)
        if (current_dt >= first_sold_out_date) {
            return { available: false, message: 'Unavailable - Blocked due to sold out date in sequence' };
        }
        
        return { available: true, message: '' };
        
    } catch (error) {
        console.log('Error in rbfw_check_return_date_inventory:', error);
        return { available: true, message: '' }; // Default to available on error
    }
}

function rbfw_off_day_dates(date,type='',today_enable='no',md_drop_off=null){


    var curr_date = ("0" + (date.getDate())).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    var date_in = curr_date+"-"+curr_month+"-"+curr_year;

    let ajax = 'no';
    

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
                        return [false, "notav", 'Sold Out'];
                    }
                    
                    // Enhanced inventory checking for return date selection
                    // This triggers when selecting return date (dropoff)
                    if(md_drop_off === true && typeof rbfw_check_return_date_inventory === 'function'){
                        var pickup_date = jQuery('[name="rbfw_pickup_start_date"]').val();
                        
                        if(pickup_date){
                            var inventory_check_result = rbfw_check_return_date_inventory(pickup_date, null, date, day_wise_inventory);
                            if(!inventory_check_result.available){
                                return [false, "notav", inventory_check_result.message];
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

    var scheduleJson = JSON.parse(schedule);
    var rdfw_available_timeJson = JSON.parse(rdfw_available_time);
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
            item.available_time.forEach(timeObj => {
                if (timeObj.status === "enabled") {

                    let now = new Date();
                    let currentDateStr = now.toISOString().split("T")[0]; // YYYY-MM-DD
                    let selectedDateStr = selectedDate.toISOString().split("T")[0];

                    if (selectedDateStr === currentDateStr) {
                        // Parse available_time into a Date object for comparison
                        let [hours, minutes] = timeObj.time.split(":").map(Number);
                        let timeDate = new Date();
                        timeDate.setHours(hours, minutes, 0, 0);

                        if (timeDate <= now) {
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
                        a.className = "rbfw_bikecarsd_time";
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

    if(sapecific_date_time==false){
        rdfw_available_timeJson.forEach(timeObj => {
            if (timeObj.status === "enabled") {

                let now = new Date();
                let currentDateStr = now.toISOString().split("T")[0]; // YYYY-MM-DD
                let selectedDateStr = selectedDate.toISOString().split("T")[0];

                if (selectedDateStr === currentDateStr) {
                    // Parse available_time into a Date object for comparison
                    let [hours, minutes] = timeObj.time.split(":").map(Number);
                    let timeDate = new Date();
                    timeDate.setHours(hours, minutes, 0, 0);

                    if (timeDate <= now) {
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
                    a.className = "rbfw_bikecarsd_time";
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

function particular_time_date_dependent_ajax(post_id,date_ymd,type='',rbfw_enable_time_slot='',selector){





    jQuery.ajax({
        type: 'POST',
        dataType:'json',
        url: rbfw_ajax_front.rbfw_ajaxurl,
        data: {
            'action'  : 'particular_time_date_dependent',
            'post_id': post_id,
            'selected_date': date_ymd,
            'type': type,
            'selector': selector,
            'nonce' : rbfw_ajax_front.nonce_particular_time_date_dependent
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();

            if(type=='sd'){

                let quantity_options = '';

                jQuery.each(response[0], function(i, item) {
                        quantity_options += `
                            <a data-time="${item[1]}" class="rbfw_bikecarsd_time">
                                <span class="rbfw_bikecarsd_time_span">${item[1]}</span>
                            </a>
                        `;
                });
                jQuery(response[1]).html(quantity_options);

            } else {
                if (response[1] == ".rbfw-select.rbfw-time-price.dropoff_time") {
                    var quantity_options = "<option value=''>" + rbfw_translation.return_time + "</option>";
                } else {
                    var quantity_options = "<option value=''>" + rbfw_translation.pickup_time + "</option>";
                }

                jQuery.each(response[0], function(i, item) {
                    quantity_options += "<option "+ item[0] +" value="+i+">"+item[1]+"</option>";
                });
                jQuery(response[1]).html(quantity_options);
            }






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


