(function($) {
    "use strict";
    //=========Remove Setting Item ==============//
    jQuery(document).on('click', '.rbfw_item_remove:not(.rbfw-faq-content-wrapper-main .rbfw_item_remove)', function() {
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            jQuery(this).closest('.rbfw_remove_area').slideUp(250, function() {
                jQuery(this).remove();
            });
        } else {
            return false;
        }
    });
    jQuery(document).on('click', '.rbfw_close_multi_image_item', function() {
        let parent = jQuery(this).closest('.rbfw_multi_image_area');
        let current_parent = jQuery(this).closest('.rbfw_multi_image_item');
        let img_id = current_parent.data('image-id');
        let grandParent = jQuery(this).parents('.rbfw_faq_item');
        jQuery('.rbfw_multi_image_item[data-image-id=' + img_id + ']').remove();
        let all_img_ids = parent.find('.rbfw_multi_image_value').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.rbfw_multi_image_value').val(all_img_ids);
        if (all_img_ids == '') {
            grandParent.find('.rbfw_upload_img_notice').show();
        }

    });
    jQuery(document).on('click', '.add_multi_image,.rbfw_upload_img_notice', function() {
        let parent = jQuery(this).closest('.rbfw_multi_image_area');
        let grandParent = jQuery(this).parents('.rbfw_faq_item');
        wp.media.editor.send.attachment = function(props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="rbfw_multi_image_item" data-image-id="' + attachment_id + '"><span class="rbfw_close_multi_image_item"><i class="fa-solid fa-trash-can"></i></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';


            if (attachment_id != '') {
                grandParent.find('.rbfw_upload_img_notice').hide();
            }

            parent.find('.rbfw_multi_image').append(html);
            grandParent.find('.rbfw_faq_content_wrapper .rbfw_multi_image').append(html);
            grandParent.find('.rbfw_faq_content_wrapper .rbfw_multi_image .rbfw_close_multi_image_item').remove();
            let value = parent.find('.rbfw_multi_image_value').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.rbfw_multi_image_value').val(value);
        }
        wp.media.editor.open(jQuery(this));
        return false;
    });
    //*********Add F.A.Q Item************//
    jQuery(document).ready(function() {

        function rbfw_faq_actions_func() {
            jQuery('.rbfw_faq_item_edit').click(function(e) {
                e.preventDefault();
                let dataId = $(this).data('id');
                let parent = $('.rbfw_faq_item[data-id=' + dataId + ']');
                let all_img_ids = parent.find('.rbfw_multi_image_value').val();
                jQuery("body").css("overflow", "hidden");
                $('.rbfw_faq_slide_actionlinks .faq_notice').remove();
                $('.interface-interface-skeleton__sidebar .interface-complementary-area__fill').css('width', '0');
                $('.components-button').removeClass('is-pressed').attr('aria-expanded', 'false');
                parent.find(".rbfw_faq_slide_wrap").fadeIn();
                parent.find(".rbfw_faq_slide_overlay").show("slide", { direction: "right" }, 0);
                if (all_img_ids == '') {
                    parent.find('.rbfw_upload_img_notice').show();
                }

            });
            $('.rbfw_faq_slide_close').click(function(e) {
                e.preventDefault();
                let dataId = $(this).parents('.rbfw_faq_item').data('id');
                let parent = $('.rbfw_faq_item[data-id=' + dataId + ']');


                parent.find(".rbfw_faq_slide_overlay").hide("slide", { direction: "right" }, 0);
                setTimeout(function() {
                    parent.find(".rbfw_faq_slide_wrap").fadeOut();
                    jQuery("body").css("overflow", "visible");
                }, 0);

                if (parent.data('status') != 'saved') {
                    parent.remove();
                    return false;
                }
            });

            $('.rbfw_save_faq_content_btn').click(function(e) {
                e.preventDefault();
                let count = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item').length;
                let theDataArr = [];
                let postID = $('#post_ID').val();
                let getThisParent = jQuery(this).parents('.rbfw_faq_item');
                let getThisDataID = getThisParent.data('id');
                let getThisTextID = jQuery('.rbfw_faq_item[data-id=' + getThisDataID + '] textarea[name="rbfw_faq_content[]"]').attr('id');

                tinyMCE.triggerSave();
                let getThisTitle = getThisParent.find('[name="rbfw_faq_title[]"]').val();
                let getThisContent = tinymce.get(getThisTextID).getContent();

                if (getThisTitle == '') {
                    alert('Title is required!');
                    return false;
                }
                let rbfw_faq_title = '';
                let rbfw_faq_img = '';
                let getID = '';
                let rbfw_faq_content = '';
                for (let i = 1; i <= count; i++) {
                    rbfw_faq_title = $('.rbfw_faq_item:nth-child(' + i + ') [name="rbfw_faq_title[]"]').val();
                    rbfw_faq_img = $('.rbfw_faq_item:nth-child(' + i + ') [name="rbfw_faq_img[]"]').val();

                    getID = jQuery('.rbfw_faq_item:nth-child(' + i + ') textarea[name="rbfw_faq_content[]"]').attr('id');

                    rbfw_faq_content = tinymce.get(getID).getContent();

                    theDataArr.push({ rbfw_faq_title: rbfw_faq_title, rbfw_faq_img: rbfw_faq_img, rbfw_faq_content: rbfw_faq_content });
                }


                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'rbfw_save_faq_data',
                        'data': JSON.stringify(theDataArr),
                        'postID': postID
                    },
                    beforeSend: function() {
                        jQuery('.rbfw_save_faq_content_btn i').show();
                        $('.rbfw_faq_slide_actionlinks .faq_notice').remove();
                    },
                    success: function(response) {
                        jQuery('.rbfw_save_faq_content_btn i').hide();

                        getThisParent.find('.rbfw_faq_desc').html(getThisContent);
                        getThisParent.find('.rbfw_faq_header').find('.rbfw_faq_header_title').html(getThisTitle);
                        getThisParent.find('.rbfw_faq_new_accordion_wrapper').show();
                        getThisParent.attr('data-status', 'saved');
                        getThisParent.find('.rbfw_faq_slide_close').trigger('click');

                    },
                });
            });

            jQuery('input[name=rbfw_enable_faq_content]').click(function() {
                var status = jQuery(this).val();
                if (status == 'yes') {
                    jQuery(this).val('no');
                    jQuery('.rbfw-faq-content-wrapper-main,.rbfw_faq_content_btn_wrap').slideUp().removeClass('show').addClass('hide');
                }
                if (status == 'no') {
                    jQuery(this).val('yes');
                    jQuery('.rbfw-faq-content-wrapper-main,.rbfw_faq_content_btn_wrap').slideDown().removeClass('hide').addClass('show');
                }
            });

        }
        rbfw_faq_actions_func();

        jQuery(document).on('click', '.rbfw_faq_accordion_icon,.rbfw_faq_header_title', function(e) {
            e.preventDefault();
            let dataID = jQuery(this).closest('.rbfw_faq_item').data('id');
            let theParent = jQuery('.rbfw-faq-content-wrapper-main .rbfw_faq_item[data-id=' + dataID + ']');

            if (theParent.hasClass("active")) {
                theParent.removeClass('active');
                theParent.find('.rbfw_faq_content_wrapper').hide();
            } else {
                theParent.addClass('active');
                theParent.find('.rbfw_faq_content_wrapper').show();
            }

            theParent.find('.rbfw_faq_accordion_icon i').toggleClass('fa-plus fa-minus');
        });

        jQuery(document).on('click', '.rbfw-faq-content-wrapper-main .rbfw_item_remove', function() {
            if (confirm('Are you sure you want to delete this item? You will not be able to undo this action. \n\n 1. Ok : To Delete . \n 2. Cancel : To Cancel .')) {
                jQuery(this).closest('.rbfw_remove_area').slideUp(250, function() {
                    jQuery(this).remove();
                    let count = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item').length;
                    let theDataArr = [];
                    let postID = $('#post_ID').val();
                    let rbfw_faq_title = '';
                    let rbfw_faq_img = '';
                    let getID = '';
                    let rbfw_faq_content = '';

                    for (let i = 1; i <= count; i++) {

                        if ($('.rbfw_faq_item:nth-child(' + i + ')').length) {
                            rbfw_faq_title = $('.rbfw_faq_item:nth-child(' + i + ') [name="rbfw_faq_title[]"]').val();
                            rbfw_faq_img = $('.rbfw_faq_item:nth-child(' + i + ') [name="rbfw_faq_img[]"]').val();

                            getID = jQuery('.rbfw_faq_item:nth-child(' + i + ') textarea[name="rbfw_faq_content[]"]').attr('id');

                            rbfw_faq_content = tinymce.get(getID).getContent();

                            theDataArr.push({ rbfw_faq_title: rbfw_faq_title, rbfw_faq_img: rbfw_faq_img, rbfw_faq_content: rbfw_faq_content });

                        }
                    }

                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            'action': 'rbfw_save_faq_data',
                            'data': JSON.stringify(theDataArr),
                            'postID': postID
                        },
                        beforeSend: function() {
                            jQuery('button.rbfw_add_faq_content').addClass('rbfw-pointer-not-allowed');
                        },
                        success: function(response) {
                            alert('FAQ item has been removed!');
                            jQuery('button.rbfw_add_faq_content').removeClass('rbfw-pointer-not-allowed');
                        },
                    });

                });
            } else {
                return false;
            }
        });

        jQuery(document).on('click', '.rbfw_add_faq_content', function() {
            let theCount = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item').length;
            let lastDataID = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item:last-child').data('id');
            if (lastDataID === undefined) {
                lastDataID = 0;
            }
            let i = parseInt(lastDataID) + 1;
            let theID = 'rbfw_faq_content_' + i;
            let theLoader = jQuery('.rbfw_add_faq_content i');
            $('.rbfw_faq_slide_actionlinks .faq_notice').remove();

            $('.interface-interface-skeleton__sidebar .interface-complementary-area__fill').css('width', '0');
            $('.components-button').removeClass('is-pressed').attr('aria-expanded', 'false');
            $.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                data: { "action": "get_rbfw_add_faq_content", "id": theID, 'count': lastDataID },
                beforeSend: function() {

                    theLoader.css('display', 'inline-block');
                },
                success: function(data) {
                    $('.rbfw-faq-content-wrapper-main').append(data);
                    let getID = jQuery('.rbfw_faq_item[data-id=' + i + '] textarea[name="rbfw_faq_content[]"]').attr('id');

                    tinymce.init({ selector: '#' + getID });
                    rbfw_faq_actions_func();
                    theLoader.hide();
                    $('.rbfw_faq_item_edit[data-id=' + i + ']').trigger('click');
                },
                error: function(response) {
                    console.log(response);
                }
            });

            return false;
        });
    });
    //*********End F.A.Q Item************//
    jQuery(window).load(function() {
        jQuery('.mp_tab_menu').each(function() {
            jQuery(this).find('ul li:first-child').trigger('click');
        });
        if (jQuery('[name="mep_org_address"]').val() > 0) {
            jQuery('.mp_event_address').slideUp(250);
        }
    });
    jQuery(document).on('click', '[data-target-tabs]', function() {
        if (!jQuery(this).hasClass('active')) {
            let tabsTarget = jQuery(this).attr('data-target-tabs');
            let targetParent = jQuery(this).closest('.mp_event_tab_area').find('.mp_tab_details').first();
            targetParent.children('.mp_tab_item:visible').slideUp('fast');
            targetParent.children('.mp_tab_item[data-tab-item="' + tabsTarget + '"]').slideDown(250);
            jQuery(this).siblings('li.active').removeClass('active');
            jQuery(this).addClass('active');
        }
        return false;
    });
    jQuery(document).on('click', 'label.mp_event_virtual_type_des_switch input', function() {
        if (jQuery(this).is(":checked")) {
            jQuery(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').slideDown(200);
        } else {
            jQuery(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').val('').slideUp(200);
        }
    });
    jQuery(document).ready(function() {
        jQuery('#add-row-t').on('click', function() {
            var row = jQuery('.empty-row-t.screen-reader-text').clone(true);
            row.removeClass('empty-row-t screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
            jQuery('#mep_ticket_type_empty option[value=inputbox]').attr('selected', 'selected');
            jQuery('.empty-row-t #mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
            return false;
        });

        jQuery('.remove-row-t').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
                jQuery('#mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
                jQuery('#mep_ticket_type_empty option[value=dropdown]').removeAttr('selected');
            } else {
                return false;
            }
        });
        jQuery(document).find('.mp_event_type_sortable').sortable({
            handle: jQuery(this).find('.mp_event_type_sortable_button')
        });


        jQuery('#add-row').on('click', function() {
            var row = jQuery('.empty-row.screen-reader-text').clone(true);
            row.removeClass('empty-row screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
            return false;
        });

        jQuery('.remove-row').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });

        jQuery('#add-row-size').on('click', function() {
            var row = jQuery('#size-hidden-row').clone(true);
            row.removeClass('empty-row screen-reader-text-size');
            row.insertBefore('#repeatable-fieldset-one-size tbody>tr:last');
            return false;
        });

        jQuery('.remove-row-size,.remove-rbfw_variations_table_row').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });

        jQuery('.remove-rbfw_variations_value_table_row').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr.rbfw_variations_value_table_row').remove();
            } else {
                return false;
            }
        });

        jQuery('#add-row-dropoff').on('click', function() {
            var row = jQuery('#dropoff-hidden-row').clone(true);
            row.removeClass('empty-row screen-reader-text-dropoff');
            row.insertBefore('#repeatable-fieldset-one-dropoff tbody>tr:last');
            return false;
        });

        jQuery('.remove-row-dropoff').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });



        jQuery('.rbfw_switch_pickup_location label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw-pickup-location-section').show();
            } else {
                jQuery('.rbfw-pickup-location-section').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_dropoff_location label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw-dropoff-location-section').show();
            } else {
                jQuery('.rbfw-dropoff-location-section').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_faq label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw_faq_content_wrapper').show();
            } else {
                jQuery('.rbfw_faq_content_wrapper').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_daywise_price label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw_week_table').show();
            } else {
                jQuery('.rbfw_week_table').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_hourly_rate label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw_hourly_rate_input').show();
            } else {
                jQuery('.rbfw_hourly_rate_input').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_daily_rate label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw_daily_rate_input').show();
            } else {
                jQuery('.rbfw_daily_rate_input').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_variations label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_variations"]').show();
                jQuery('.rbfw_variations_table_wrap').show();
                jQuery('.rbfw_item_stock_quantity_row').hide();
                jQuery('.rbfw_variation_tab_notice').hide();
            } else {
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_variations_table_wrap').hide();
                jQuery('.rbfw_item_stock_quantity_row').show();
                jQuery('.rbfw_variation_tab_notice').show();
            }

            return false;
        });

        jQuery('#field-wrapper-rbfw_time_slot_switch label').click(function(e) {
            let this_attr = jQuery(this).attr('for');

            if (jQuery(this).hasClass('checked') && this_attr == 'rbfw_time_slot_switch-on') {
                jQuery('tr[data-row=rdfw_available_time]').show();
            } else if (jQuery(this).hasClass('checked') && this_attr == 'rbfw_time_slot_switch-off') {
                jQuery('tr[data-row=rdfw_available_time]').hide();
            }

        });

        var current_item_type = jQuery('#rbfw_item_type').val();
        if (current_item_type != 'bike_car_sd' && current_item_type != 'appointment' && current_item_type != 'resort') {
            jQuery('.rbfw_seasonal_price_config_wrapper').show();
        } else {
            jQuery('.rbfw_seasonal_price_config_wrapper').hide();
        }

        if (current_item_type == 'bike_car_sd' || current_item_type == 'appointment') {
            jQuery('tr[data-row=rbfw_time_slot_switch]').show();
        } else {
            jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
            jQuery('tr[data-row=rdfw_available_time]').show();
        }

        jQuery('#rbfw_item_type').on('change', function() {
            var item_type = jQuery(this).val();

            if (item_type == 'bike_car_sd') {
                jQuery('.rbfw_bike_car_sd_wrapper').show();
                jQuery('.rbfw_general_price_config_wrapper').addClass('rbfw-d-none');
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('.rbfw_seasonal_price_config_wrapper').hide();
                jQuery('.rbfw_switch_sd_appointment_row').hide();
                jQuery('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').show();
                jQuery('.rbfw_es_price_config_wrapper').show();
                jQuery('.rbfw_discount_price_config_wrapper').hide();
                jQuery('.rbfw_min_max_booking_day_row').hide();
                jQuery('tr[data-row=rbfw_time_slot_switch]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('.regular_fixed_date').hide();
                jQuery('.rbfw_off_days').show();
                jQuery('.wervice_quantity_input_box').hide();
                jQuery('#add-bike-car-sd-type-row').show();

            } else if (item_type == 'appointment') {
                jQuery('.rbfw_bike_car_sd_wrapper').show();
                jQuery('.rbfw_general_price_config_wrapper').addClass('rbfw-d-none');
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]').hide();
                jQuery('.mp_tab_item[data-target-tabs="#rbfw_location_config"]').hide();
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('.rbfw_seasonal_price_config_wrapper').hide();
                jQuery('.rbfw_switch_sd_appointment_row').show();
                jQuery('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').hide();
                jQuery('.rbfw_es_price_config_wrapper').hide();
                jQuery('.rbfw_discount_price_config_wrapper').hide();
                jQuery('.rbfw_min_max_booking_day_row').hide();
                jQuery('tr[data-row=rbfw_time_slot_switch]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('[name="rbfw_off_days"]').val('');
                jQuery('.rbfw_off_days input').prop('checked', false);
                jQuery('.rbfw_off_days').show();
                jQuery('.regular_fixed_date').hide();
                jQuery('#add-bike-car-sd-type-row').hide();

                let this_table_row_length = jQuery('.rbfw_bike_car_sd_price_table_row').length;

                for (let index = 0; index < this_table_row_length; index++) {
                    if (index > 0) {
                        jQuery('.rbfw_bike_car_sd_price_table_row[data-key="' + index + '"]').remove();
                    }

                }


            } else if (item_type == 'resort') {
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]').hide();
                jQuery('.mp_tab_item[data-target-tabs="#rbfw_location_config"]').hide();
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').hide();
                jQuery('.rbfw_bike_car_sd_wrapper').hide();
                jQuery('.rbfw_general_price_config_wrapper').hide();
                jQuery('.rbfw_seasonal_price_config_wrapper').hide();
                jQuery('.rbfw_resort_price_config_wrapper').show();
                jQuery('.rbfw_location_switch').hide();
                jQuery('.rbfw_switch_sd_appointment_row').hide();
                jQuery('.rbfw_es_price_config_wrapper').show();
                jQuery('.rbfw_discount_price_config_wrapper').show();
                jQuery('.rbfw_min_max_booking_day_row').show();
                jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
                jQuery('tr[data-row=rdfw_available_time]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('.rbfw_off_days').show();
            } else {
                jQuery('.rbfw_bike_car_sd_wrapper').hide();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('.rbfw_general_price_config_wrapper').removeClass('rbfw-d-none');
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.mp_tab_item[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.rbfw_switch_extra_service_qty').show();
                jQuery('li[data-target-tabs="#rbfw_variations"]').show();
                jQuery('.rbfw_switch_md_type_item_qty').show();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_location_switch').show();
                jQuery('.rbfw_general_price_config_wrapper').show();
                jQuery('.rbfw_seasonal_price_config_wrapper').show();
                jQuery('.rbfw_switch_sd_appointment_row').hide();
                jQuery('.rbfw_es_price_config_wrapper').show();
                jQuery('.rbfw_discount_price_config_wrapper').show();
                jQuery('.rbfw_min_max_booking_day_row').show();
                jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
                jQuery('tr[data-row=rdfw_available_time]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').show();
                jQuery('.regular_fixed_date').show();
                //jQuery('tr.rbfw_enable_start_end_date_field_row').show();
                jQuery('.rbfw_off_days').show();
                jQuery('.wervice_quantity_input_box').show();


            }

            return false;
        });

        jQuery('#add-row-pickup').on('click', function() {
            var row = jQuery('#pickup-hidden-row').clone(true);
            row.removeClass('empty-row screen-reader-text-pickup');
            row.insertBefore('#repeatable-fieldset-one-pickup tbody>tr:last');
            return false;

        });

        jQuery('.remove-row-pickup').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });


        jQuery('#add-new-date-row').on('click', function() {
            var row = jQuery('.empty-row-d.screen-reader-text').clone(true);
            row.removeClass('empty-row-d screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one-d tbody>tr:last');
            return false;
        });

        jQuery('.remove-row-d').on('click', function() {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });


        jQuery('.field-select2-wrapper select, .rbfw_select2').select2({
            // placeholder: 'Select an option'
        });

        jQuery("ul.select2-selection__rendered").sortable({
            containment: 'parent'
        });

        jQuery('.rbfw_payment_system').on('change', function() {
            let this_value = jQuery(this).val();
            let this_parent = jQuery(this).parents('tr');

            if (this_value == 'mps') {
                jQuery(this_parent).siblings('tr').show();
                jQuery(this_parent).siblings('tr.rbfw_wps_add_to_cart_redirect').hide();

            } else if (this_value == 'wps') {
                jQuery(this_parent).siblings('tr').hide();
                jQuery(this_parent).siblings('tr.rbfw_wps_add_to_cart_redirect').show();

            }
        });


        jQuery('.rbfw_switch_resort_daylong_price label').on('click', function() {

            var daylong_price_label_val = jQuery(this).find('input').val();

            if (daylong_price_label_val == 'yes') {
                jQuery('.resort_day_long_price').show();
            } else {
                jQuery('.resort_day_long_price').hide();
            }

            return false;
        });

        jQuery('.rbfw_switch_appointment label').on('click', function() {

            var item_type = jQuery(this).find('input').val();

            if (item_type == 'yes') {
                jQuery('.rbfw_appointment_ondays_row').show();
            } else {
                jQuery('.rbfw_appointment_ondays_row').hide();
            }

            return false;
        });

        jQuery('.rbfw_open_date_time_tab').on('click', function() {
            jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').trigger('click');
        });

        jQuery('.rbfw_inventory_filter_date').datepicker({
            dateFormat: 'dd-mm-yy'
        });

        jQuery('#rbfw_sd_appointment_max_qty_per_session').change(function(e) {
            let this_value = jQuery('#rbfw_sd_appointment_max_qty_per_session').val();
            let target = jQuery('input[name="rbfw_bike_car_sd_data[0][qty]"]');
            let selected_time_slots = jQuery('#rdfw_available_time').find(':selected');
            let updated_value = this_value * selected_time_slots.length;
            target.val(updated_value);
        });

        /* Template Options On Load Document */
        var this_value = jQuery('select#rbfw_single_template').val();
        if (this_value == 'Default' || this_value == 'Muffin') {

            jQuery('tr[data-row="rbfw_dt_sidebar_switch"]').hide();
            jQuery('tr[data-row="rbfw_dt_sidebar_testimonials"]').hide();
            jQuery('tr[data-row="rbfw_dt_sidebar_content"]').hide();

        } else if (this_value == 'Donut') {

            jQuery('tr[data-row="rbfw_dt_sidebar_switch"]').show();
            jQuery('tr[data-row="rbfw_dt_sidebar_testimonials"]').show();
            jQuery('tr[data-row="rbfw_dt_sidebar_content"]').show();
        }

        jQuery('select#rbfw_single_template').on('change', function() {

            var this_value = jQuery(this).val();

            if (this_value == 'Default' || this_value == 'Muffin') {

                jQuery('tr[data-row="rbfw_dt_sidebar_switch"]').hide();
                jQuery('tr[data-row="rbfw_dt_sidebar_testimonials"]').hide();
                jQuery('tr[data-row="rbfw_dt_sidebar_content"]').hide();

            } else if (this_value == 'Donut') {

                jQuery('tr[data-row="rbfw_dt_sidebar_switch"]').show();
                jQuery('tr[data-row="rbfw_dt_sidebar_testimonials"]').show();
                jQuery('tr[data-row="rbfw_dt_sidebar_content"]').show();
            }

            return false;
        });
        /* End: Template Options On Load Document */
        jQuery('#rbfw_event_start_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });

        jQuery('#rbfw_event_start_date').change(function(e) {

            let selected_date = jQuery(this).val();
            const [gYear, gMonth, gDay] = selected_date.split('-');
            jQuery("#rbfw_event_end_date").datepicker("destroy");
            jQuery("#rbfw_event_end_date").val('');
            jQuery('#rbfw_event_end_date').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: new Date(gYear, gMonth - 1, gDay)
            });
        });

        jQuery('#rbfw_event_end_date').click(function(e) {
            let event_start_date = jQuery('#rbfw_event_start_date').val();
            if (event_start_date == '') {
                alert('Please select the event start date!');
            }

        });

        jQuery('#rbfw_event_end_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });

        jQuery('.rbfw_switch_return_date label').click(function(e) {
            let data_value = jQuery(this).attr('data-value');
            console.log(data_value);
            if (data_value == 'on') {
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
            }
            if (data_value == 'off') {
                jQuery('.rbfw_enable_start_end_date_field_row').show();
            }
        });


        jQuery('.rbfw_switch label').click(function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            let $this = jQuery(this);
            let target = jQuery(this).parents('.rbfw_switch').find('label');
            target.removeClass('active');
            target.find('input').prop('checked', false);
            target.find('input').removeAttr('checked');
            $this.addClass('active');
            $this.find('input').prop('checked', true);

        });






        $('.category2').select2({
            placeholder: 'This is my placeholder',
            allowClear: true
        });


        jQuery('[name="rbfw_order_status"]').change(function(e) {

            let selected_status = jQuery(this).val();

            console.log('selected_status', selected_status);

            if (selected_status == 'picked') {
                jQuery('.rbfw_return_note').hide();
                jQuery('.rbfw_return_security_deposit_amount').hide();
                jQuery('.rbfw_pickup_note').show();
                console.log('oooooo');

            } else if (selected_status == 'returned') {
                jQuery('.rbfw_pickup_note').hide();
                jQuery('.rbfw_return_note').show();
                jQuery('.rbfw_return_security_deposit_amount').show();
            } else {
                jQuery('.rbfw_pickup_note').hide();
                jQuery('.rbfw_return_note').hide();
                jQuery('.rbfw_return_security_deposit_amount').hide();
            }


        });

        let rbfw_today_booking_enable = jQuery('.rbfw_today_booking_enable').val();


        $('#rbfw-bikecarsd-calendar').datepicker({
            dateFormat: js_date_format,
            minDate: 0,
            firstDay: start_of_week,
            beforeShowDay: function(date) {
                return rbfw_off_day_dates(date, 'md', rbfw_today_booking_enable);
            },
            onSelect: function(dateString, data) {
                let date_ymd = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                $('input[name="rbfw_bikecarsd_selected_date"]').val(date_ymd).trigger('change');
            },
        });

        jQuery('body').on('change', 'input[name="rbfw_bikecarsd_selected_date"]', function(e) {

            let post_id = jQuery('#rbfw_post_id').val();
            let is_muffin_template = jQuery('.rbfw_muffin_template').length;

            var time_slot_switch = jQuery('#time_slot_switch').val();
            var selected_date = jQuery(this).val();

            if (is_muffin_template > 0) {
                is_muffin_template = '1';
            } else {
                is_muffin_template = '0';
            }

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax.rbfw_ajaxurl,
                data: {
                    'action': 'rbfw_bikecarsd_time_table',
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
                    if (rent_type == 'appointment') {
                        let rbfw_date_element_arr = [];
                        let rbfw_date_element = jQuery('.rbfw-date-element');
                        let rbfw_calendar_weekday = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                        let appointment_days = jQuery('#appointment_days').val();
                        jQuery(rbfw_date_element).each(function($i) {
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
                success: function(response) {

                    jQuery('.rbfw-bikecarsd-step[data-step="1"]').hide();
                    jQuery('.rbfw-bikecarsd-step[data-step="1"]').removeClass('rbfw_loader_in');
                    jQuery('.rbfw-bikecarsd-step[data-step="1"] i.fa-spinner').remove();
                    jQuery('.rbfw-bikecarsd-result').append(response);
                    var time_slot_switch = jQuery('#time_slot_switch').val();


                    if (time_slot_switch != 'on') {
                        rbfw_bikecarsd_without_time_func();
                    }


                },
                complete: function(data) {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".rbfw-bikecarsd-calendar-header").offset().top
                    }, 100);
                }
            });

        });



    });
}(jQuery));