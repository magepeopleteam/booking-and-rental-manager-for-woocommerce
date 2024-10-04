(function($) {
    $(document).ready(function() {
        const service_id = $('.rbfw-single-container').attr('data-service-id');
        // DatePicker
        let rbfw_today_booking_enable = jQuery('.rbfw_today_booking_enable').val();

        $('body').on('focusin', '.pickup_date', function(e) {
            $(this).datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                beforeShowDay: function(date)
                {
                    return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
                }
            });
        });



        jQuery('body').on('change', '.pickup_date', function(e) {

            let selected_date = jQuery(this).val();
            const [gYear, gMonth, gDay] = selected_date.split('-');

            jQuery(".dropoff_date").datepicker("destroy");



            jQuery('.dropoff_date').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: new Date(gYear, gMonth - 1, gDay),
                beforeShowDay: function(date)
                {
                    return rbfw_off_day_dates(date,'md',rbfw_today_booking_enable);
                }
            });
        });

        $('.pickup_time').change(function(e) {
            let pickup_date = $('.pickup_date').val();
            let dropoff_date = $('.dropoff_date').val();

            if (pickup_date == dropoff_date) {
                let selected_time = $(this).val();
                selected_time = rbfw_convertTo24HrsFormat(selected_time);
                $(".dropoff_time").val("").trigger("change");

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

        $('.dropoff_date').change(function(e) {
            $(".pickup_time").trigger("change");

        });


        $('.dropoff_date').click(function(e) {
            let pickup_date = $('.pickup_date').val();
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
        $('#rbfw_left_filter_popup_close_btn').on('click', function() {
            $('#rbfw_left_filter_popup_wrapper').hide();
            $('#rbfw_left_filter_popup_content').empty(); // Clear the content when closed
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

//Left Filtering
        $('.rbfw_toggle-content').hide();
        $('.rbfw_toggle-header').on('click', function() {
            var content = $(this).next('.rbfw_toggle-content');
            content.slideToggle();
            var icon = $(this).find('.rbfw_toggle-icon');
            if (icon.text() === '+') {
                icon.text('−');
            } else {
                icon.text('+');
            }
        });
        function get_left_filter_data( filter_date ){
            // console.log( filter_date );

            if ($('#rbfw_rent_list_wrapper').hasClass('rbfw_rent_list_style_grid')) {
                var rbfw_item_style = 'grid';
            } else {
                rbfw_item_style = 'list';
            }
            $(".rbfw_left_filter_button").text('Filtering...');
            $(".rbfw_left_filter_button").css('background-color', '#c3b9bd');
            // $('#rbfw_rent_list_wrapper').html('<div class="rbfw_filter_item_loadeing_text">Loading Data ...</div>');
            $('#rbfw_rent_list_pagination').hide();
            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action' : 'rbfw_get_left_side_filter_data',
                    'filter_date': filter_date,
                    'rbfw_nomce': rbfw_vars.rbfw_nonce,
                    'rbfw_item_style': rbfw_item_style,
                },
                success: function (response) {
                    if( response.success ){
                        let text_display = response.data.show_text;
                        $('#rbfw_rent_list_wrapper').html( response.data.display_date );
                        $('#rbfw_shoe_result_text').html('<span >'+text_display+'</span>');
                        $(".rbfw_left_filter_button").text('Filter');
                        $(".rbfw_left_filter_button").css('background-color', '#e71d73');
                    }else{
                        $('#rbfw_shoe_result_text').html('<div class="rbfw_search_result_empty" data-placeholder="" style="display: block;">No Match Result Found!</div>');
                    }

                },
            });
        }

        var selectedLocation = [];
        var selectedcategory = [];
        var selectedType = [];
        var selectedFeatures = [];
        var get_filters = {
            location: [],
            category: [],
            type: [],
            price: { start: 0, end: 0 },
            title_text: '',
        };
        $(document).on('change', '.rbfw_location', function() {
            var value = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedLocation.includes(value)) {
                    selectedLocation.push(value);
                }
            } else {
                selectedLocation = selectedLocation.filter(function(item) {
                    return item !== value;
                });
            }
            get_filters.location = selectedLocation;

            get_left_filter_data(get_filters);
        });

        $(document).on('change', '.rbfw_category', function() {
            var value = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedcategory.includes(value)) {
                    selectedcategory.push(value);
                }
            } else {
                selectedcategory = selectedcategory.filter(function(item) {
                    return item !== value;
                });
            }
            get_filters.category = selectedcategory;

            get_left_filter_data(get_filters);
        });

        $(document).on('change', '.rbfw_rent_type', function() {
            var value = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedType.includes(value)) {
                    selectedType.push(value);
                }
            } else {
                selectedType = selectedType.filter(function(item) {
                    return item !== value;
                });
            }
            get_filters.type = selectedType;

            get_left_filter_data(get_filters);
        });

        $(document).on('change', '.rbfw_rent_feature', function() {
            var value = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedFeatures.includes(value)) {
                    selectedFeatures.push(value);
                }
            } else {
                selectedFeatures = selectedFeatures.filter(function(item) {
                    return item !== value;
                });
            }
            get_filters.feature = selectedFeatures;

            get_left_filter_data(get_filters);
        });

        // Price slider handling
        var start_val = 0;
        var end_val = 0;
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 10000,
            values: [0, 0], // Default values
            slide: function(event, ui) {
                // Continuously update the displayed value while sliding
                $("#price").val("$" + ui.values[0] + " - $" + ui.values[1]);
            },
            stop: function(event, ui) {
                // Only update the get_filters when the mouse is released (stop sliding)
                start_val = ui.values[0];
                end_val = ui.values[1];

                // Update the price in get_filters object
                get_filters.price.start = start_val;
                get_filters.price.end = end_val;

                get_left_filter_data(get_filters);
            }
        });

        $("#price").val("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1));
        get_filters.price.start = $("#slider-range").slider("values", 0);
        get_filters.price.end = $("#slider-range").slider("values", 1);

        $(document).on('click', '.rbfw_left_filter_button', function() {
            // get_left_filter_data(get_filters); // Assuming get_filters is a function or a variable
        });
        $(document).on('click', '.rbfw_left_filter_search_btn', function() {
            // get_left_filter_data(get_filters); // Assuming get_filters is a function or a variable
            let filter_title_text = $("input[name='rbfw_search_by_title']").val();
            get_filters.title_text = filter_title_text.trim();
            get_left_filter_data(get_filters);
            // console.log( get_filters );
        });


        $(document).on('click', '.rbfw_left_filter_more_feature_loaders', function(e) {
            e.preventDefault();
            let clickedId = $(this).attr('id');
            $("#rbfw_left_filter_popup_wrapper").show();
            $("#rbfw_left_filter_popup_content").html('<div class="rbfw_loader">Loading....</div>');

            let category = 'feature';
            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action' : 'rbfw_get_rent_item_left_filter_more_data_popup',
                    'data_category': category,
                    'rbfw_nonce': rbfw_vars.rbfw_nonce,
                },
                success: function (response) {
                    console.log( response );
                    $('#rbfw_left_filter_popup_content').html( response.data );
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