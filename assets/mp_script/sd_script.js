(function($) {
    $(document).ready(function() {

        const service_id = $('.rbfw-single-container').attr('data-service-id');
        // DatePicker

        $('#rbfw-bikecarsd-calendar').datepicker({
            dateFormat: js_date_format,
            minDate: 0,
            firstDay : start_of_week,
            beforeShowDay: function(date)
            {
                return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
            },
            onSelect: function (dateString, data) {
                let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                $('input[name="selected_date"]').val(date_ymd).trigger('change');
            },
        });

        jQuery('body').on('change', 'input[name="selected_date"]', function(e) {

            let post_id = jQuery('#rbfw_post_id').val();
            let is_muffin_template = jQuery('.rbfw_muffin_template').length;

            var time_slot_switch = jQuery('#time_slot_switch').val();
            var selected_date = jQuery('[name="selected_date"]').val();

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
                    jQuery('.rbfw-bikecarsd-step[data-step="1"]').addClass('rbfw_loader_in');
                    jQuery('.rbfw-bikecarsd-step[data-step="1"]').append('<i class="fas fa-spinner fa-spin"></i>');
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
                    jQuery('.rbfw-bikecarsd-step[data-step="1"]').removeClass('rbfw_loader_in');
                    jQuery('.rbfw-bikecarsd-step[data-step="1"] i.fa-spinner').remove();
                    jQuery('.rbfw-bikecarsd-result').append(response);
                    var time_slot_switch = jQuery('#time_slot_switch').val();
                    
                    if(time_slot_switch != 'on'){
                        rbfw_bikecarsd_without_time_func();
                    }
                },
                complete:function(data) {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                    }, 100);
                }
            });

        });




        function rbfw_convertTo24HrsFormat(time) {
            const slicedTime = time.split(/(PM|AM)/gm)[0];

            let [hours, minutes] = slicedTime.split(':');

            if (hours === '12') {
                hours = '00';
            }

            let updateHourAndMin;

            function addition(hoursOrMin = '') {
                updateHourAndMin =
                    hoursOrMin.length < 2 ?
                        (hoursOrMin = `${0}${hoursOrMin}`) :
                        hoursOrMin;

                return updateHourAndMin;
            }

            if (time.endsWith('PM')) {
                hours = parseInt(hours, 10) + 12;
            }

            return `${addition(hours)}:${addition(minutes)}`;
        }

        // Toggle Action
        $(document).on('click','.rbfw-toggle-btn,.rbfw_pricing_info_heading',function() {
            const $this = $(this);
            const target = $('.price-item-container');
            if (target.hasClass('open')) {
                target.removeClass('open').slideUp();
                $this.find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
            } else {
                target.addClass('open').slideDown();
                $this.find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
            }
        });

    });
    });