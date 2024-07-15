jQuery(document).ready(function($){
    if($('#rbfw-single-day-booking-calendar').length){
        let config = {
            weekDayLength:1,
            // onClickDate: onclick_cal_date,
            showYearDropdown: true,
            startOnMonday: false,
            showTodayButton: false,
            highlightSelectedWeekday: false,
            highlightSelectedWeek: false,
            prevButton: '<i class="fa-solid fa-circle-chevron-left"></i>',
            nextButton: '<i class="fa-solid fa-circle-chevron-right"></i>',
        }
        $('#rbfw-single-day-booking-calendar').calendar(config);
    }
    
});
