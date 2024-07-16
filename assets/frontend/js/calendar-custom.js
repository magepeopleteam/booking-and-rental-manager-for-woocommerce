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
        let todayDate= new Date;
        let yearLimitYear = todayDate.getFullYear()+1;
        let yearLimitMonth =todayDate.getMonth();
        let yearLimitDay =todayDate.getDate();
        // console.log(yearLimitYear+'-'+yearLimitMonth+'-'+yearLimitDay);

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
            disable:function (date) {
                return this.disableDate.bind(date);
            }
            
            startOnMonday: false,
            prevButton: '<i class="fa-solid fa-circle-chevron-left"></i>',
            nextButton: '<i class="fa-solid fa-circle-chevron-right"></i>',
        }
        return config;
        
    }

    dateSelected(date){
        jQuery('#rbfw-single-day-booking').updateCalendarOptions({date});
    }
}


