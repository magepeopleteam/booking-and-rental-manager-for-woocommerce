(function($) {
    $(document).ready(function() {
        const service_id = $('.rbfw-single-container').attr('data-service-id');
        // DatePicker
        $('#pickup_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            beforeShowDay: function(date)
            {
                return rbfw_off_day_dates(date,'md');
            }
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
                return text === rbfw_ajaxurl.view_more_feature_btn_text ? rbfw_ajaxurl.hide_more_feature_btn_text : rbfw_ajaxurl.view_more_feature_btn_text;
            })
        });


        jQuery(document).on('click', '.rbfw_view_more_offers', function(e) {
            let this_parent = jQuery(this).closest('.rbfw_muff_discount_feature_wrap');
            let this_target = this_parent.find('.rbfw_muff_discount_feature[data-status="extra"]');
            this_target.slideToggle();

            jQuery(this).text(function(i, text) {
                return text === rbfw_ajaxurl.view_more_offers_btn_text ? rbfw_ajaxurl.hide_more_offers_btn_text : rbfw_ajaxurl.view_more_offers_btn_text;
            })
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