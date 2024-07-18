/**
 * @author shahadat Hossain <raselsha@gmail.com>
 * @version 1.0.0
 * @since   2.0.5
 */

jQuery(document).ready(function($){
    
    if($('#rbfw-single-day-booking').length){
        new RBFW_Single_Day_Booking($);
    }
    
});


class RBFW_Single_Day_Booking{

    constructor($) {
        let config = this.getCalendarConfig($);
        $('#rbfw-single-day-booking').calendar(config);
        
    }

    getCalendarConfig($){
        let config = {
            date: null,
            weekDayLength:1,
            onClickDate: this.dateSelected.bind(this),
            monthYearSeparator:' | ',
            showThreeMonthsInARow: true,
            enableMonthChange: true,
            enableYearView: true,
            showTodayButton: false,
            highlightSelectedWeekday: false,
            highlightSelectedWeek: false,
            todayButtonContent: "Today",
            showYearDropdown: true,
            min: null,
            max: null,
            disable:function(date){
                return RBFW_Single_Day_Booking.disableDate(date,'','no');
            },
            startOnMonday: false,
            prevButton: '<i class="fa-solid fa-circle-chevron-left"></i>',
            nextButton: '<i class="fa-solid fa-circle-chevron-right"></i>',
        }
        return config;
        
    }
     // when calendar date selected
    dateSelected(date){
        jQuery('#rbfw-single-day-booking').updateCalendarOptions({date});
        let d = new Date(date);
        let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
        let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(d);
        let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);
        let s_Date = ye+'-'+mo+'-'+da;
        jQuery('#rbfw_bikecarsd_selected_date').val(s_Date);
    }
    // disable date lists
    static disableDate(date,type='',today_enable='no'){
        var curr_date = ("0" + (date.getDate())).slice(-2);
        var curr_month = ("0" + (date.getMonth() + 1)).slice(-2);
        var curr_year = date.getFullYear();
        var date_in = curr_date+"-"+curr_month+"-"+curr_year;    
        var date_today = new Date();
    
        if(today_enable=='yes'){
            var month = date_today.getMonth()-1;
            var day = date_today.getDate();
            var date_today = date_today.getFullYear() + '/' +
                (month<10 ? '0' : '') + month + '/' +
                (day<10 ? '0' : '') + day;
        }
    
        var weekday = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
        var day_in = weekday[date.getDay()];
        var rbfw_off_days = JSON.parse(jQuery("#rbfw_off_days").val());
    
        var rbfw_offday_range = JSON.parse(jQuery("#rbfw_offday_range").val());
    
    
        if(jQuery.inArray( day_in, rbfw_off_days )>= 0 || jQuery.inArray( date_in, rbfw_offday_range )>= 0 || (date <  date_today) ){
            if(type=='md'){
                return [false, "notav", 'Not Available'];
            }else{
                return true;
            }
        }else{
    
            if(type=='md'){
                return [true, "av", "available"];
            }else{
                return false;
            }
        }
    }

    static selectTimeSlot(element){

        jQuery('.rbfw_bikecarsd_time:not(.rbfw_bikecarsd_time.disabled)').click(function (e) { 
            jQuery('.rbfw_bikecarsd_time').removeClass('selected');
            jQuery(this).addClass('selected');
            let gTime = jQuery(this).attr('data-time');
            jQuery('#rbfw_bikecarsd_selected_time').val(gTime);
            let post_id = jQuery('#rbfw_post_id').val();
            let selected_date = jQuery('#rbfw_bikecarsd_selected_date').val();
            let rent_type = jQuery('#rbfw_rent_type').val();

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
                    'action' : 'rbfw_bikecarsd_type_list',
                    'post_id': post_id,
                    'selected_time': gTime,
                    'selected_date': selected_date,
                    'is_muffin_template': is_muffin_template
                },
                beforeSend: function() {
                    jQuery('.single-day-booking-result').empty();
                    jQuery('.rbfw_bikecarsd_time_table_wrap').addClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_time_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
    
                    if( rent_type == 'appointment' ){
    
                        jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
                        jQuery('.rbfw_bikecarsd_price_summary.old').addClass('rbfw_loader_in');
                        jQuery('.rbfw_bikecarsd_price_summary.old').append('<i class="fas fa-spinner fa-spin"></i>');
                    }
                },
                success: function (response) {
                    
                    if( rent_type == 'bike_car_sd' ){
                        
                        jQuery('.rbfw-bikecarsd-step[data-step="3"]').hide();
                    }
    
                    jQuery('.rbfw_bikecarsd_time_table_wrap').removeClass('rbfw_loader_in');
                    jQuery('.rbfw_bikecarsd_time_table_wrap i.fa-spinner').remove();
                    jQuery('.rbfw_bikecarsd_pricing_table_container').remove();
                    jQuery('.single-day-booking-result').append(response);
    
                    if( rent_type == 'appointment' ){
    
                        jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_back_step_btn').hide();
                        jQuery('.rbfw-bikecarsd-step[data-step="3"] .rbfw_step_selected_date').hide();
                        let selected_time = jQuery('#rbfw_bikecarsd_selected_time').val();
                        jQuery('.rbfw-bikecarsd-step[data-step="2"] .rbfw_step_selected_date span.rbfw_selected_time').remove();
                        // jQuery('.rbfw-bikecarsd-step[data-step="2"] .rbfw_step_selected_date').append('<span class="rbfw_selected_time"> '+selected_time+'</span>');
                    }
    
                    rbfw_update_input_value_onchange_onclick();
    
                    rbfw_bikecarsd_ajax_price_calculation();
                    rbfw_step_func();
                    rbfw_display_es_box_onchange_onclick();
    
                    rbfw_mps_book_now_btn_action();
                    rbfw_mps_direct_checkout();
    
                    jQuery('.rbfw_muff_registration_wrapper .rbfw_regf_wrap').show();
                },
                complete:function(response) {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                    }, 100);
                }
            });
        });
    }

}


