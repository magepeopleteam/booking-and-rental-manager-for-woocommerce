jQuery(document).ready(function($){

    if($('#rbfw-single-day-booking-calendar').length){
        let todayDate= new Date;
        let yearLimitYear = todayDate.getFullYear()+1;
        let yearLimitMonth =todayDate.getMonth();
        let yearLimitDay =todayDate.getDate();
        console.log(yearLimitYear+'-'+yearLimitMonth+'-'+yearLimitDay);
        let config = {
            date: null,
            weekDayLength:1,
            onClickDate: onclick_cal_date,
            monthYearSeparator:' | ',
            showThreeMonthsInARow: true,
            enableMonthChange: true,
            enableYearView: true,
            showTodayButton: false,
            highlightSelectedWeekday: false,
            highlightSelectedWeek: false,
            todayButtonContent: "Today",
            showYearDropdown: true,
            min:  new Date,
            max: yearLimitYear+'-'+yearLimitMonth+'-'+yearLimitDay,
            disable: function (date) {},
            startOnMonday: false,
            prevButton: '<i class="fa-solid fa-circle-chevron-left"></i>',
            nextButton: '<i class="fa-solid fa-circle-chevron-right"></i>',

        }
        $('#rbfw-single-day-booking-calendar').calendar(config);
    }
    
});
