(function($) {
    $(document).ready(function() {

        const service_id = $('.rbfw-single-container').attr('data-service-id');
        // DatePicker
        let rbfw_today_booking_enable = jQuery('.rbfw_today_booking_enable').val();


        $('#rbfw-bikecarsd-calendar').datepicker({
            dateFormat: js_date_format,
            minDate: 0,
            firstDay : start_of_week,
            showOtherMonths: true,
            selectOtherMonths: true,
            beforeShowDay: function(date)
            {
                return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
            },
            onSelect: function (dateString, data) {
                let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                $('input[name="rbfw_bikecarsd_selected_date"]').val(date_ymd).trigger('change');
            },
        });

        jQuery('body').on('change', 'input[name="rbfw_bikecarsd_selected_date"]', function(e) {

            let post_id = jQuery('#rbfw_post_id').val();
            let is_muffin_template = jQuery('.rbfw_muffin_template').length;

            var time_slot_switch = jQuery('#time_slot_switch').val();
            var selected_date = jQuery(this).val();

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

        // rbfw TAB
        $('.rbfw-tab-a').click(function(e) {
            e.preventDefault();
            $('.rbfw-tab-a').removeClass('active-a')
            $(this).addClass('active-a')

            $(".rbfw-tab").removeClass('rbfw-tab-active');
            $(".rbfw-tab[data-id='" + $(this).attr('data-id') + "']").addClass("rbfw-tab-active");
            $(this).parent().find(".tab-a").addClass('active-a');
        });

        //Donut Template FAQ/Review Tab
        jQuery('.rbfw_dt_heading_tab').click(function(e) {
            jQuery('.rbfw_dt_heading_tab').removeClass('active');
            jQuery('.rbfw_dt_faq_tab_content').removeClass('active');
            let this_tab = jQuery(this).attr('data-tab');
            jQuery(this).addClass('active');
            jQuery('.rbfw_dt_faq_tab_content[data-content="' + this_tab + '"]').addClass('active');
        });

        //Muffin Template FAQ/Review Tab
        jQuery('.rbfw_muff_heading_tab').click(function(e) {
            jQuery('.rbfw_muff_heading_tab').removeClass('active');
            jQuery('.rbfw_muff_faq_tab_content').removeClass('active');
            let this_tab = jQuery(this).attr('data-tab');
            jQuery(this).addClass('active');
            jQuery('.rbfw_muff_faq_tab_content[data-content="' + this_tab + '"]').addClass('active');
        });

        //Grid List Auto Height Function
        rbfw_grid_list_auto_height_func();

        function rbfw_grid_list_auto_height_func(extra_height = null) {
            let rbfw_rent_list_height_arr = [];
            let elemlength = jQuery(".rbfw_rent_list_style_grid .rbfw_rent_list_col").length;

            for (let index = 1; index <= elemlength; index++) {
                rbfw_rent_list_height_arr.push(jQuery(".rbfw_rent_list_style_grid .rbfw_grid_list_col_" + index).height());
            }

            let max_value = parseInt(Math.max.apply(Math, rbfw_rent_list_height_arr));

            if (extra_height != '') {
                max_value = max_value + extra_height;
            }

            jQuery(".rbfw_rent_list_style_grid .rbfw_rent_list_col .rbfw_rent_list_inner_wrapper").css({ "min-height": max_value });
            //jQuery('.rbfw_rent_list_style_grid .rbfw_rent_list_button_wrap').css({ "position": "absolute", "bottom": "20px" });
        }


        jQuery('.rbfw_grid_view_more_features_btn').click(function(e) {
            jQuery(this).siblings('.rbfw_absolute_list').find('ul').slideToggle().show();
            jQuery(this).text(function(i, text) {
                return text === rbfw_ajaxurl.view_more_feature_btn_text ? rbfw_ajaxurl.hide_more_feature_btn_text : rbfw_ajaxurl.view_more_feature_btn_text;
            })
        });

        //End: Grid List Auto Height Function



        jQuery('.rbfw_muff_lmf_btn').click(function(e) {
            let this_parent = jQuery(this).closest('ul');
            let this_target = this_parent.find('li[data-status="extra"]');
            this_target.slideToggle();

            jQuery(this).text(function(i, text) {
                return text === rbfw_ajaxurl.view_more_feature_btn_text ? rbfw_ajaxurl.view_more_feature_btn_text : rbfw_ajaxurl.hide_more_feature_btn_text;
            })
        });


        jQuery(document).on('click', '.rbfw_view_more_offers', function(e) {
            let this_parent = jQuery(this).closest('.rbfw_muff_discount_feature_wrap');
            let this_target = this_parent.find('.rbfw_muff_discount_feature[data-status="extra"]');
            this_target.slideToggle();

            jQuery(this).text(function(i, text) {
                return text === rbfw_ajaxurl.view_more_offers_btn_text ? rbfw_ajaxurl.view_more_offers_btn_text : rbfw_ajaxurl.hide_more_offers_btn_text;
            })
        });


        function setCookie( name, value, days ) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // Convert days to milliseconds
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            let cookieArr = document.cookie.split(';');
            for (let i = 0; i < cookieArr.length; i++) {
                let cookiePair = cookieArr[i].split('=');
                if (name === cookiePair[0].trim()) {
                    return decodeURIComponent(cookiePair[1]);
                }
            }
            return null;
        }
        function deleteCookie( name ) {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }

        $(document).on( 'click', '.rbfw_rent_items_list_grid', function(){

            let clickedId = $(this).attr('id');

            $("#"+clickedId).siblings().removeClass('selected_list_grid');
            $("#"+clickedId).addClass('selected_list_grid');

            var wrapperElement = document.getElementById('rbfw_rent_list_wrapper');

            /*if( 'Grid '){
                var image = 'rbfw_rent_list_grid_view_top';
                var info = 'rbfw_inner_details';
                var list_info = 'rbfw_rent_list_info';
            }else{
                image = 'rbfw_rent_list_lists_images';
                info = 'rbfw_rent_list_lists_info';
                list_info = 'rbfw_rent_item_content_list_bottom';
            }*/

            var minHeightValue = '';

            if( clickedId === 'rbfw_rent_items_grid' ){
               wrapperElement.classList.replace(wrapperElement.classList[wrapperElement.classList.length - 1], 'rbfw_rent_list_style_grid');
               let $element = $('#rbfw_rent_list_wrapper').find('.rbfw_rent_list_lists_images');
               let $element1 = $('#rbfw_rent_list_wrapper').find('.rbfw_rent_list_lists_info');
               let $element2 = $('#rbfw_rent_list_wrapper').find('.rbfw_rent_item_content_list_bottom');

               $element.removeClass('rbfw_rent_list_lists_images').addClass('rbfw_rent_list_grid_view_top');
               $element1.removeClass('rbfw_rent_list_lists_info').addClass('rbfw_inner_details');
               $element2.removeClass('rbfw_rent_item_content_list_bottom').addClass('rbfw_rent_list_info');

                $(".rbfw_rent_item_description_text").css("display", "none");

               setCookie( 'rbfw_rent_item_list_grid', 'rbfw_rent_item_grid', 1 );

           } else{

               wrapperElement.classList.replace(wrapperElement.classList[wrapperElement.classList.length - 1], 'rbfw_rent_list_style_list');
               let $element = $('#rbfw_rent_list_wrapper').find('.rbfw_rent_list_grid_view_top');
               let $element1 = $('#rbfw_rent_list_wrapper').find('.rbfw_inner_details');
               let $element2 = $('#rbfw_rent_list_wrapper').find('.rbfw_rent_list_info');

               $element.removeClass('rbfw_rent_list_grid_view_top').addClass('rbfw_rent_list_lists_images');
               $element1.removeClass('rbfw_inner_details').addClass('rbfw_rent_list_lists_info');
               $element2.removeClass('rbfw_rent_list_info').addClass('rbfw_rent_item_content_list_bottom');
               let inner_wrapper = $('.rbfw_rent_list_inner_wrapper');
               inner_wrapper.css('min-height', '' );

                setCookie( 'rbfw_rent_item_list_grid', 'rbfw_rent_item_list', 1 );

                $(".rbfw_rent_item_description_text").css("display", "-webkit-box");

           }

        });

        function rbfw_pick_date_from_flatpicker(){

            let today = new Date();
            let tomorrow = new Date();
            tomorrow.setDate(today.getDate() + 1); // Add 1 day to get tomorrow

            // Format the dates as "m-d-Y"
            let todayFormatted = flatpickr.formatDate(today, "d-m-Y");

            // Initialize Flatpickr with range mode, showing 2 months, blocking previous days, and defaulting to today & tomorrow
            let calendar = flatpickr("#rbfw_rent_item_search_calendar_icon", {
                dateFormat: "d-m-Y",  // Display format in the calendar
                defaultDate: todayFormatted,  // Preselect today
                minDate: "today",  // Block previous days
                showMonths: 1,  // Show current and next month
                onChange: function(selectedDates, dateStr, instance) {
                    // Update the input field with the selected single date
                    if (selectedDates.length === 1) {
                        // Format the selected date as 'January 10, 2024'
                        let selectedDate = flatpickr.formatDate(selectedDates[0], "F j, Y");
                        $("#rbfw_rent_item_search_pickup_date").val(selectedDate);  // Set the input value to the formatted date
                    }
                }
            });

            // Open the calendar when the calendar icon is clicked
            $('#rbfw_rent_item_search_calendar_icon').on('click', function() {
                calendar.open(); // Trigger the calendar to open
            });

            $("#rbfw_rent_item_search_pickup_date").on('focus', function (){
                calendar.open();
            });
        }
        rbfw_pick_date_from_flatpicker();

        /*$('.rbfw_see_more_category').hover(
            function() {
                let hoverId = $(this).attr('id')
                let idNumber = hoverId.split('-').pop();
                $('#rbfw_show_all_cat_features-'+idNumber).show();
            },
            function() {
                let hoverId = $(this).attr('id');
                let idNumber = hoverId.split('-').pop();
                $('#rbfw_show_all_cat_features-'+idNumber).hide();
            }
        );*/
        $('#rbfw_popup_close_btn').on('click', function() {
            $('#rbfw_popup_wrapper').hide();
            $('#rbfw_popup_content').empty(); // Clear the content when closed
        });

        $('body').on( 'click', '.rbfw_see_more_category', function(e){
            e.preventDefault();
            let clickedId = $(this).attr('id');
            let item_number = clickedId.split('-').pop();
            $("#rbfw_popup_wrapper").show();
            $("#rbfw_popup_content").html('<div class="rbfw_loader">Loading....</div>')

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action' : 'rbfw_get_rent_item_category_info',
                    'post_id': item_number,
                },
                success: function (response) {
                    $('#rbfw_popup_content').html( response.data );
                },
            });
        });

    });
})(jQuery)

/* Additional Gallary Images */
// Open the Modal
function rbfw_aig_openModal() {
    document.getElementById("rbfw_aig_Modal").style.display = "block";
}

// Close the Modal
function rbfw_aig_closeModal() {
    document.getElementById("rbfw_aig_Modal").style.display = "none";
}

var slideIndex = 1;
rbfw_aig_showSlides(slideIndex);

// Next/rbfw_aig_previous controls
function rbfw_aig_plusSlides(n) {
    rbfw_aig_showSlides(slideIndex += n);
}

// Thumbnail image controls
function rbfw_aig_currentSlide(n) {
    rbfw_aig_showSlides(slideIndex = n);
}

function rbfw_aig_showSlides(n) {
    var i;
    var slides = document.getElementsByClassName("rbfw_aig_slides");

    if (slides.length == 0) {
        return;
    }

    var dots = document.getElementsByClassName("rbfw_aig_img_thumb");
    var captionText = document.getElementById("rbfw_aig_caption-caption");
    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
    captionText.innerHTML = dots[slideIndex - 1].alt;
}
/* End: Additional Gallary Images */
// using muffin tempalte descriptoin show hide 
jQuery(document).ready(function($) {
    $('.rbfw-read-more').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $postContent = $this.closest('.rbfw_muff_post_content');
        $postContent.find('.trimmed-content').toggle();
        $postContent.find('.full-content').toggle();
        
    });
});