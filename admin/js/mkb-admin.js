(function($) {
    "use strict";
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
        if ( current_item_type != 'appointment') {
            jQuery('.rbfw_seasonal_price_config_wrapper').show();
            if(current_item_type == 'bike_car_sd'){

                jQuery('.sessional_price_single_day').show();
                jQuery('.sessional_price_multi_day').hide();
                jQuery('.sessional_price_resort').hide();
                jQuery('.mds_price_resort').hide();

            }else if(current_item_type == 'resort'){

                jQuery('.sessional_price_resort').show();
                jQuery('.mds_price_resort').show();
                jQuery('.sessional_price_multi_day').hide();
                jQuery('.sessional_price_single_day').hide();

            }else{

                jQuery('.sessional_price_multi_day').show();
                jQuery('.sessional_price_single_day').hide();
                jQuery('.sessional_price_resort').hide();
                jQuery('.mds_price_resort').hide();

            }
        } else {
            jQuery('.rbfw_seasonal_price_config_wrapper').hide();
        }

        if (current_item_type == 'bike_car_sd' || current_item_type == 'appointment') {
            jQuery('tr[data-row=rbfw_time_slot_switch]').show();
        } else {
            jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
            jQuery('tr[data-row=rdfw_available_time]').show();
        }

        var status = $('.rbfw_es_price_config_wrapper').data('status');

        
        if(status=='no' && current_item_type == 'bike_car_md'){
            $('.rbfw_es_price_config_wrapper').hide();
        }else{
            $('.rbfw_es_price_config_wrapper').show();
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
                jQuery('.rbfw_seasonal_price_config_wrapper').show();
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
                jQuery('.wervice_quantity_input_box').show();
                jQuery('#add-bike-car-sd-type-row').show();

                jQuery('.manage_inventory_as_timely').show();

                jQuery('.sessional_price_single_day').show();
                jQuery('.sessional_price_multi_day').hide();
                jQuery('.sessional_price_resort').hide();
                jQuery('.mds_price_resort').hide();

                if(jQuery('[name="manage_inventory_as_timely"]').val()=='on'){
                    jQuery('.rbfw_time_inventory').show();
                    jQuery('.rbfw_without_time_inventory').hide();
                }else{
                    jQuery('.rbfw_time_inventory').hide();
                    jQuery('.rbfw_without_time_inventory').show();
                }
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

                jQuery('.manage_inventory_as_timely').hide();
                jQuery('.rbfw_time_inventory').hide();
                jQuery('.rbfw_without_time_inventory').show();
                jQuery('.rbfw_item_stock_quantity').hide();

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

                jQuery('.sessional_price_resort').show();
                jQuery('.mds_price_resort').show();
                jQuery('.sessional_price_multi_day').hide();
                jQuery('.sessional_price_single_day').hide();

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

                jQuery('.sessional_price_multi_day').show();
                jQuery('.sessional_price_single_day').hide();

                var status = $('.rbfw_es_price_config_wrapper').data('status');
                if(status=='yes'){
                    $('.rbfw_es_price_config_wrapper').show();
                }else{
                    $('.rbfw_es_price_config_wrapper').hide();
                }
                jQuery('.sessional_price_multi_day').show();
                jQuery('.sessional_price_single_day').hide();
                jQuery('.sessional_price_resort').hide();
                jQuery('.mds_price_resort').hide();

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
            jQuery(this_parent).siblings('tr').hide();
            jQuery(this_parent).siblings('tr.rbfw_wps_add_to_cart_redirect').show();
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


      /* start inventory filter and view details */

        jQuery('.rbfw_inventory_filter_btn').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            let selected_date = jQuery('.rbfw_inventory_filter_date').val();
            let start_date = jQuery('#rbfw_inventory_event_start_time').val();
            let end_date = jQuery('#rbfw_inventory_event_end_time').val();
            let placeholder_loader = jQuery('.rbfw-inventory-page-ph').clone();

            if(selected_date == ''){
                alert('Please select the date');
                return;
            }
            if(start_date && !end_date){
                alert('Please select the end time');
                return;
            }

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                data: {
                    'action' : 'rbfw_get_stock_by_filter',
                    'selected_date' : selected_date,
                    'start_date' : start_date,
                    'end_date' : end_date,
                    'nonce' : rbfw_ajax.nonce
                },
                beforeSend: function() {
                    jQuery('.rbfw_inventory_page_table_wrap').empty();
                    jQuery('.rbfw_inventory_page_table_wrap').html(placeholder_loader);
                    jQuery('.rbfw_inventory_page_table_wrap .rbfw-inventory-page-ph').show();
                },
                success: function (response) {
                    jQuery('.rbfw_inventory_page_table_wrap').html(response);
                }
            });
        });

        jQuery('.rbfw_inventory_reset_btn').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            jQuery('.rbfw_inventory_filter_date').val('');
            jQuery('#rbfw_inventory_event_start_time').val('');
            jQuery('#rbfw_inventory_event_end_time').val('');
            let selected_date = '';
            let placeholder_loader = jQuery('.rbfw-inventory-page-ph').clone();

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                data: {
                    'action' : 'rbfw_get_stock_by_filter',
                    'selected_date' : selected_date,
                    'nonce' : rbfw_ajax.nonce
                },
                beforeSend: function() {
                    jQuery('.rbfw_inventory_page_table_wrap').empty();
                    jQuery('.rbfw_inventory_page_table_wrap').html(placeholder_loader);
                    jQuery('.rbfw_inventory_page_table_wrap .rbfw-inventory-page-ph').show();
                },
                success: function (response) {
                    jQuery('.rbfw_inventory_page_table_wrap').html(response);
                }
            });
        });

        jQuery('.rbfw_inventory_refresh_btn').click(function (e) {
            window.location.reload();
        });

        jQuery(document).on('click','.rbfw_stock_view_details',function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            jQuery("#rbfw_stock_view_result_wrap").mage_modal({
                escapeClose: false,
                clickClose: false,
                showClose: true
            });

            let data_request = jQuery(this).attr('data-request');
            let data_date = jQuery(this).attr('data-date');
            let data_id = jQuery(this).attr('data-id');

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                data: {
                    'action' : 'rbfw_get_stock_details',
                    'data_request' : data_request,
                    'data_date' : data_date,
                    'data_id' : data_id,
                    'nonce' : rbfw_ajax.nonce
                },
                beforeSend: function() {
                    jQuery('#rbfw_stock_view_result_inner_wrap').empty();
                    jQuery('#rbfw_stock_view_result_inner_wrap').html('<i class="fas fa-spinner fa-spin rbfw_rp_loader"></i>');
                },
                success: function (response) {
                    jQuery('#rbfw_stock_view_result_inner_wrap').html(response);
                }
            });
        });
        /* end inventory filter and view details */
    });
    
    // =====================sidebar modal open close=============
	$(document).on('click', '[data-modal]', function (e) {
		const modalTarget = $(this).data('modal');
		$(`[data-modal-target="${modalTarget}"]`).addClass('open');
	});

	$(document).on('click', '[data-modal-target] .rbfw-modal-close', function (e) {
		$(this).closest('[data-modal-target]').removeClass('open');
	});
	
// ================ F.A.Q. ===================================
	$(document).on('click', '.rbfw-faq-item-new', function (e) {
		$('#rbfw-faq-msg').html('');
		$('.rbfw_faq_save_buttons').show();
		$('.rbfw_faq_update_buttons').hide();
		empty_faq_form();
	});

	function close_sidebar_modal(e){
		e.preventDefault();
		e.stopPropagation();
		$('.rbfw-modal-container').removeClass('open');
	}

	$(document).on('click', '.rbfw-faq-item-edit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$('#rbfw-faq-msg').html('');
		$('.rbfw_faq_save_buttons').hide();
		$('.rbfw_faq_update_buttons').show();
		var itemId = $(this).closest('.rbfw-faq-item').data('id');
		var parent = $(this).closest('.rbfw-faq-item');
		var headerText = parent.find('.faq-header p').text().trim();
		var faqContentId = parent.find('.faq-content').html().trim();
		var editorId = 'rbfw_faq_content';
		$('input[name="rbfw_faq_title"]').val(headerText);
		$('input[name="rbfw_faq_item_id"]').val(itemId);
		if (tinymce.get(editorId)) {
			tinymce.get(editorId).setContent(faqContentId);
		} else {
			$('#' + editorId).val(faqContentId);
		}
	});

	$(document).on('click', '.rbfw-faq-item-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var itemId = $(this).closest('.rbfw-faq-item').data('id');

		var isConfirmed = confirm('Are you sure you want to delete this row?');
		if (isConfirmed) {
			delete_faq_item(itemId);
		} else {
			console.log('Deletion canceled.'+itemId);
		}
	});
	

	function empty_faq_form(){
		$('input[name="rbfw_faq_title"]').val('');
		tinyMCE.get('rbfw_faq_content').setContent('');
		$('input[name="rbfw_faq_item_id"]').val('');
	}
	

	$(document).on('click', '#rbfw_faq_update', function (e) {
		e.preventDefault();
		update_faq();
	});

	$(document).on('click', '#rbfw_faq_save', function (e) {
		e.preventDefault();
		save_faq();
	});

	$(document).on('click', '#rbfw_faq_save_close', function (e) {
		e.preventDefault();
		save_faq();
		close_sidebar_modal(e);
	});

	function update_faq(){
		var title   = $('input[name="rbfw_faq_title"]');
		var content = tinyMCE.get('rbfw_faq_content').getContent();
		var postID  = $('input[name="rbfw_post_id"]');
		var itemId = $('input[name="rbfw_faq_item_id"]');
		$.ajax({
			url: rbfw_ajax_url,
			type: 'POST',
			data: {
				action: 'rbfw_faq_data_update',
				rbfw_faq_title:title.val(),
				rbfw_faq_content:content,
				rbfw_faq_postID:postID.val(),
				rbfw_faq_itemID:itemId.val(),
                'nonce' : rbfw_ajax.nonce
			},
			success: function(response) {
				$('#rbfw-faq-msg').html(response.data.message);
				$('.rbfw-faq-items').html('');
				$('.rbfw-faq-items').append(response.data.html);
				setTimeout(function(){
					$('.rbfw-modal-container').removeClass('open');
					empty_faq_form();
				},1000);
				
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}

	function save_faq(){
		var title   = $('input[name="rbfw_faq_title"]');
		var content = tinyMCE.get('rbfw_faq_content').getContent();
		var postID  = $('input[name="rbfw_post_id"]');
		$.ajax({
			url: rbfw_ajax_url,
			type: 'POST',
			data: {
				action: 'rbfw_faq_data_save',
				rbfw_faq_title:title.val(),
				rbfw_faq_content:content,
				rbfw_faq_postID:postID.val(),
                'nonce' : rbfw_ajax.nonce
			},
			success: function(response) {
				$('#rbfw-faq-msg').html(response.data.message);
				$('.rbfw-faq-items').html('');
				$('.rbfw-faq-items').append(response.data.html);
				empty_faq_form();
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}

	function delete_faq_item(itemId){
		var postID  = $('input[name="rbfw_post_id"]');
		$.ajax({
			url: rbfw_ajax_url,
			type: 'POST',
			data: {
				action: 'rbfw_faq_delete_item',
				rbfw_faq_postID:postID.val(),
				itemId:itemId,
                'nonce' : rbfw_ajax.nonce
			},
			success: function(response) {
				$('.rbfw-faq-items').html('');
				$('.rbfw-faq-items').append(response.data.html);
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}
   
    // ================toggle switch, ===================
    /**
     * it should move from internal script to here
     * then all should in one function
     */
     // Toggle visibility for category service price
    $(document).on('click', 'input[name=rbfw_enable_category_service_price]', function (e) {
        var status = $(this).val();
        if (status === 'on') {
            $(this).val('off')
            $('#field-wrapper-rbfw_service_category_price').slideUp().removeClass('show').addClass('hide');
        }
        if (status === 'off') {
            $(this).val('on');
            $('#field-wrapper-rbfw_service_category_price').slideDown().removeClass('hide').addClass('show');
        }
    });
    // Daywise price
    $(document).on('click', 'input[name=rbfw_enable_daywise_price]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
            $('.day-wise-price-configuration').slideUp().removeClass('show').addClass('hide');
        }
        if (status === 'no') {
            $(this).val('yes');
            $('.day-wise-price-configuration').slideDown().removeClass('hide').addClass('show');
        }
    });

    $(document).on('click', 'input[name=rbfw_enable_extra_service_qty]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
        }
        if (status === 'no') {
            $(this).val('yes');
        }
    });
    $(document).on('click', 'input[name=rbfw_available_qty_info_switch]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
        }
        if (status === 'no') {
            $(this).val('yes');
        }
    });
    $(document).on('click', 'input[name=shipping_enable]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no')
        }
        if (status === 'no') {
            $(this).val('yes');
        }
    });
    $(document).on('click', 'input[name=rbfw_enable_faq_content]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no')
            $('.rbfw-faq-section').slideUp();
        }
        if (status === 'no') {
            $(this).val('yes');
            $('.rbfw-faq-section').slideDown();
        }
    });

    $(document).on('click', 'input[name=rbfw_enable_additional_gallary]', function (e) {
        var status = $(this).val();
        if (status === 'on') {
            $(this).val('off');
            $('.additional-gallary-image').slideUp().removeClass('show').addClass('hide');
        }
        if (status === 'off') {
            $(this).val('on');
            $('.additional-gallary-image').slideDown().removeClass('hide').addClass('show');
        }
    });
    $(document).on('click', 'input[name=rbfw_dt_sidebar_switch]', function (e) {
        var status = $(this).val();
        if (status === 'on') {
            $(this).val('off')
        }
        if (status === 'off') {
            $(this).val('on');
        }
    });
    // Daily price
    $(document).on('click', 'input[name=rbfw_enable_daily_rate]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
            $('.rbfw_daily_rate_input input').attr("disabled", true);
        }
        if (status === 'no') {
            $(this).val('yes');
            $('.rbfw_daily_rate_input input').removeAttr("disabled");
        }
    });
    // Hourly price
    $(document).on('click', 'input[name=rbfw_enable_hourly_rate]', function (e) {
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
            $('.rbfw_hourly_rate input').attr("disabled", true);
            if ($('input[name=rbfw_time_slot_switch]').val() == 'on') {
                $('input[name=rbfw_time_slot_switch]').trigger("click");
            }
        }
        if (status === 'no') {
            $(this).val('yes');
            $('.rbfw_hourly_rate input').removeAttr("disabled");
            if ($('input[name=rbfw_time_slot_switch]').val() == 'off') {
                $('input[name=rbfw_time_slot_switch]').trigger("click");
            }
        }
    });
    // Day long price
    $(document).on('click', 'input[name=rbfw_enable_resort_daylong_price]', function (e) {
        var status = jQuery(this).val();
        if (status === 'yes') {
            jQuery(this).val('no');
            jQuery('.resort_day_long_price').hide();
        }
        if (status === 'no') {
            jQuery(this).val('yes');
            jQuery('.resort_day_long_price').show();
        }
    });
    // ================toggle switch===================

    // ============== Resort type in price ===================
    $(document).on('click', '#add-resort-type-row', function (e) {
        e.preventDefault();
        let current_time = jQuery.now();
        if ($('.rbfw_resort_price_table .rbfw_resort_price_table_row').length) {
            let resort_last_row = $('.rbfw_resort_price_table .rbfw_resort_price_table_row:last-child()');
            let resort_type_last_data_key = parseInt(resort_last_row.attr('data-key'));
            let resort_type_new_data_key = resort_type_last_data_key + 1;
            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                + '<td><input class="rbfw_room_title" type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td>'
                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                + '<td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="Day-long Rate"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="Day-night Rate"></td>'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td>'
                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                + '</tr>';
                $('.rbfw_resort_price_table').append(resort_type_row);
        } else {
            let resort_type_new_data_key = 0;
            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td>'
                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                + '<td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="Day-long Rate"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="Day-night Rate"></td>'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td>'
                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                + '</tr>';
                $('.rbfw_resort_price_table').append(resort_type_row);
        }
        $('.remove-row.' + current_time + '').on('click', function () {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                $(this).parents('tr').remove();
            } else {
                return false;
            }
        });
        $(".rbfw_resort_price_table_body").sortable();

        var daylong_price_label_val = $('.rbfw_resort_daylong_price_switch label.active').find('input').val();
        if (daylong_price_label_val === 'yes') {
            $('.resort_day_long_price').show();
        } else {
            $('.resort_day_long_price').hide();
        }
    });

    // Image handling for room type
    $(document).on('click', '.rbfw_room_type_image_btn', function (e) {
        let parent_data_key = $(this).closest('.rbfw_resort_price_table_row').attr('data-key');
        let send_attachment_bkp = wp.media.editor.send.attachment;
        wp.media.editor.send.attachment = function (props, attachment) {
            let image_url = attachment.url;
            $('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview img').remove();
            $('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview').append('<img src="' + image_url + '"/>');
            $('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_image').val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        }
        wp.media.editor.open($(this));
        return false;
    });

    $(document).on('click', '.rbfw_remove_room_type_image_btn', function (e) {
        let parent_data_key =  $(this).closest('.rbfw_resort_price_table_row').attr('data-key');
        $('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview img').remove();
        $('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_image').val('');
    });
    
    // ===========resort===========
}(jQuery));

 // testimonial
 function createTestimonial() {
    now = jQuery.now();
    jQuery(".testimonial-clone").clone().appendTo(".testimonials")
        .removeClass('testimonial-clone').addClass('testimonial')
        .children('.testimonial-field').attr('name', 'rbfw_dt_sidebar_testimonials[' + now + '][rbfw_dt_sidebar_testimonial_text]');
}

// Handle extra service image upload
jQuery(document).ready(function () {
    function rbfw_service_image_addup() {
        // Onclick for extra service add image button
        jQuery('.rbfw_service_image_btn').click(function () {
            let target = jQuery(this).parents('tr');
            let send_attachment_bkp = wp.media.editor.send.attachment;
            wp.media.editor.send.attachment = function (props, attachment) {
                target.find('.rbfw_service_image_preview img').remove();
                // Escape URL before appending it to the DOM
                target.find('.rbfw_service_image_preview').append('<img src="' + attachment.url + '"/>');
                target.find('.rbfw_service_image').val(attachment.id); // Escape the attachment ID
                wp.media.editor.send.attachment = send_attachment_bkp;
            }
            wp.media.editor.open(jQuery(this));
            return false;
        });
        // Onclick for extra service remove image button
        jQuery('.rbfw_remove_service_image_btn').click(function () {
            let target = jQuery(this).parents('tr');
            target.find('.rbfw_service_image_preview img').remove();
            target.find('.rbfw_service_image').val('');
        });
    }
    rbfw_service_image_addup();
});


