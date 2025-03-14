(function($) {
    $(document).ready(function() {

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
        //rbfw_grid_list_auto_height_func();

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
            let todayFormatted = flatpickr.formatDate(today, "d-m-Y");
            let calendar = flatpickr(".rbfw_flatpicker", {
                disableMobile: "true",
                dateFormat: "d-m-Y",
                defaultDate: todayFormatted,
                minDate: "today",
                showMonths: 1,
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 1) {
                        let selectedDate = flatpickr.formatDate(selectedDates[0], "F j, Y");
                        $("#rbfw_rent_item_search_pickup_date").val(selectedDate);
                    }
                }
            });

            $("#rbfw_rent_item_search_pickup_date").on('focus', function (){
                calendar.open();
            });
        }
        rbfw_pick_date_from_flatpicker();

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
                    'nonce' : rbfw_ajax.nonce
                },
                success: function (response) {
                    $('#rbfw_popup_content').html( response.data );
                },
            });
        });

//Left Filtering
        $('.rbfw_toggle-content').show();
        $('.rbfw_toggle-icon').on('click', function() {
            var content = $(this).next('.rbfw_toggle-content');
            content.slideToggle();
            var icon = $(this);
            if (icon.text() === '+') {
                icon.text('−');
            } else {
                icon.text('+');
            }
        });
        function get_left_filter_data( filter_date ){

            filter_date = JSON.stringify(filter_date);

            $("#rbfw_left_filter_clearButton").show();
            $("#rbfw_left_filter_cover").show();

            if ($('#rbfw_rent_list_wrapper').hasClass('rbfw_rent_list_style_grid')) {
                var rbfw_item_style = 'grid';
            } else {
                rbfw_item_style = 'list';
            }
            $(".rbfw_left_filter_button").text('Filtering...');
            $('#rbfw_rent_list_pagination').hide();

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action' : 'rbfw_get_left_side_filter_data',
                    'filter_date': filter_date,
                    'rbfw_nonce': rbfw_vars.rbfw_nonce,
                    'rbfw_item_style': rbfw_item_style,
                    'nonce' : rbfw_ajax.nonce
                },
                success: function (response) {
                    if( response.success ){

                        let text_display = response.data.show_text;

                        $("#rbfw_left_filter_cover").hide();

                        // $('#rbfw_rent_list_wrapper').html( response.data.display_date );
                        $('#rbfw_rent_list_wrapper').fadeOut(200, function () {
                            $(this).html(response.data.display_date).fadeIn(300);
                        });

                        $('#rbfw_shoe_result_text').html('<span >'+text_display+'</span>');
                        $(".rbfw_left_filter_button").text('Filter');
                    }else{
                        alert('ok');
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
            price: {},
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
        /*if ($('#slider-range').length > 0) {
            var start_val = 0;
            var end_val = 0;
            $("#slider-range").slider({
                range: true,
                min: 0,
                max: 10000,
                values: [0, 0], // Default values
                slide: function(event, ui) {
                    $("#rbfw_left_filter_price").val("" + ui.values[0] + " - " + ui.values[1]);
                },
                stop: function(event, ui) {
                    start_val = ui.values[0];
                    end_val = ui.values[1];

                    get_filters.price.start = start_val;
                    get_filters.price.end = end_val;

                    get_left_filter_data(get_filters);
                }
            });

            $("#rbfw_left_filter_price").val("" + $("#slider-range").slider("values", 0) + " - " + $("#slider-range").slider("values", 1));
            get_filters.price.start = $("#slider-range").slider("values", 0);
            get_filters.price.end = $("#slider-range").slider("values", 1);
        }*/

        $('.rbfw_price_start_end').on('focusout', function () {

            let startPrice = $("#rbfw_price_start").val();
            let endPrice = $("#rbfw_price_end").val();

            if( startPrice === '' && endPrice === '' ){
                get_filters.price = {};
            }else{
                if( startPrice === '' ){
                    startPrice =  0;
                }
                if( endPrice === '' ){
                    endPrice =  100000;
                }
                get_filters.price.start = parseInt( startPrice );
                get_filters.price.end = parseInt( endPrice );
            }

            get_left_filter_data( get_filters );

        });

        $(document).on('click', '.rbfw_left_filter_search_btn', function() {

            let filter_title_text = $("input[name='rbfw_search_by_title']").val();
            get_filters.title_text = filter_title_text.trim();
            get_left_filter_data(get_filters);

        });

        $(document).on('click', '.rbfw_left_filter_more_feature_loaders', function(e) {

            e.preventDefault();
            let filter_type = $(this).attr('id');
            // alert( filter_type );
            $("#rbfw_left_filter_popup_wrapper").show();

            let category = 'feature';

            let appendId = filter_type+'_popup_content';
            $('#'+appendId).siblings().hide();
            $('#'+appendId).show();

            if ($('#' + appendId + ' input[type="checkbox"]').length > 0) {
                $('.rbfw_loader').hide();
            } else {
                $("#"+appendId).append('<div class="rbfw_loader" id="rbfw_left_filter_loader">Loading....</div>');
                jQuery.ajax({
                    type: 'POST',
                    url: rbfw_ajax.rbfw_ajaxurl,
                    data: {
                        'action': 'rbfw_get_rent_item_left_filter_more_data_popup',
                        'filter_type': filter_type,
                        'rbfw_nonce': rbfw_vars.rbfw_nonce,
                        'nonce' : rbfw_ajax.nonce
                    },
                    success: function (response) {
                        $('#' + appendId).append(response.data);
                        $('.rbfw_loader').hide();
                    },
                });
            }

        });

        $(document).on('click', '.rbfw_left_filter_clearButton',function() {
            $('.rbfw_location, .rbfw_category, .rbfw_rent_type, .rbfw_rent_feature').prop('checked', false);
            get_filters = {
                location: [],
                category: [],
                type: [],
                price: {},
                title_text: '',

            };
            get_left_filter_data( get_filters );
            $("#rbfw_price_start").val('');
            $("#rbfw_price_end").val('');
            $("#rbfw_left_filter_clearButton").hide();
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