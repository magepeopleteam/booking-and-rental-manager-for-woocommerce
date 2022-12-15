(function($) {
    $(document).ready(function() {
        const service_id = $('.rbfw-single-container').attr('data-service-id');
        // DatePicker
        $('#pickup_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });

        $('#pickup_time').change(function(e) {
            let pickup_date = $('#pickup_date').val();
            let dropoff_date = $('#dropoff_date').val();

            if (pickup_date == dropoff_date) {
                let selected_time = $(this).val();
                selected_time = rbfw_convertTo24HrsFormat(selected_time);
                $("#dropoff_time").val("").trigger("change");

                $("#dropoff_time option").each(function() {
                    var thisOptionValue = $(this).val();
                    thisOptionValue = rbfw_convertTo24HrsFormat(thisOptionValue);
                    if ((thisOptionValue == selected_time) || (thisOptionValue < selected_time)) {
                        $(this).attr('disabled', true);
                    } else {
                        $(this).attr('disabled', false);
                    }
                });

            } else {
                $("#dropoff_time option").each(function() {
                    var thisOptionValue = $(this).val();
                    if (thisOptionValue != '') {
                        $(this).attr('disabled', false);
                    } else {
                        $(this).attr('disabled', true);
                    }
                });
            }
        });

        $('#dropoff_date').change(function(e) {
            $("#pickup_time").trigger("change");

        });


        $('#dropoff_date').click(function(e) {
            let pickup_date = $('#pickup_date').val();
            if (pickup_date == '') {
                alert('Please select the pickup date!');
            }

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
        $('.rbfw-toggle-btn,.rbfw_pricing_info_heading').click(function() {
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

        // rbfw TAB
        $('.rbfw-tab-a').click(function(e) {
            e.preventDefault();
            $('.rbfw-tab-a').removeClass('active-a')
            $(this).addClass('active-a')

            $(".rbfw-tab").removeClass('rbfw-tab-active');
            $(".rbfw-tab[data-id='" + $(this).attr('data-id') + "']").addClass("rbfw-tab-active");
            $(this).parent().find(".tab-a").addClass('active-a');
        });
    });
})(jQuery)