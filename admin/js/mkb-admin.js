(function($) {
    "use strict";

    /**
     * Localised admin string with an English fallback.
     * Values come from wp_localize_script( 'rbfw_script', 'rbfw_translation', ... ).
     */
    function rbfw_admin_i18n(key, fallback) {
        return (window.rbfw_translation && window.rbfw_translation[key]) ? window.rbfw_translation[key] : fallback;
    }
    // Also expose globally: the variation repeater handlers live OUTSIDE this IIFE
    // (below, after `}(jQuery));`) and call rbfw_admin_i18n() — without this they throw
    // "rbfw_admin_i18n is not defined" on click and the Add buttons silently do nothing.
    window.rbfw_admin_i18n = rbfw_admin_i18n;

    /**
     * Some legacy click handlers below flip a checkbox's `value` attribute
     * instead of relying on the native `checked` state. That was fine for
     * the classic editor, but the modern editor (.rbfw-me-wrap) reads the
     * value attribute when serialising form data — so the legacy handlers
     * would flip the value to the OPPOSITE of what the user just clicked
     * and the AJAX save would persist the inverse of the user's intent.
     * Helper to skip those handlers inside the modern editor wrap.
     */
    var rbfwIsLegacyEditorTarget = function (target) {
        return ! $(target).closest('.rbfw-me-wrap').length;
    };

    /**
     * Extra Service admin sections — only one visible at a time.
     *
     * Category 1 (.rbfw_es_price_config_wrapper):
     *   bike_car_sd, appointment, multiple_items
     *
     * Category 2 (.additional-service-item-price):
     *   bike_car_md, resort
     *
     * @param {string} itemType  Current rbfw_item_type value.
     * @param {jQuery} [$context] Optional DOM root (defaults to document).
     */
    window.rbfwUpdateExtraServiceSectionVisibility = function(itemType, $context) {
        var $root = ($context && $context.length) ? $context : jQuery(document);
        var $category1 = $root.find('.rbfw_es_price_config_wrapper');
        var $category2 = $root.find('.additional-service-item-price');

        // Always hide both first so they are never visible together.
        $category1.hide();
        $category2.hide();

        var showCategory1 = ['bike_car_sd', 'appointment', 'multiple_items'];
        var showCategory2 = ['bike_car_md', 'resort'];

        if (showCategory1.indexOf(itemType) !== -1) {
            $category1.show();
        } else if (showCategory2.indexOf(itemType) !== -1) {
            $category2.show();
        }
    };

    window.rbfwSetTimelyInventorySection = function (scope, showSection) {
        var $root = scope ? jQuery(scope) : jQuery('#rbfw_add_meta_box');
        if (!$root.length) {
            $root = jQuery('.rbfw-me-panel[data-panel="pricing"]');
        }
        if (!$root.length) {
            $root = jQuery(document);
        }

        var $section = $root.find('section.manage_inventory_as_timely');
        if (!$section.length) {
            return;
        }

        var $wrapper = $section.closest('.rbfw_bike_car_sd_wrapper');

        if (showSection) {
            $section.removeClass('rbfw_hide hide').removeAttr('style').css('display', 'flex');
            $root.find('input[type="hidden"][name="manage_inventory_as_timely"]').remove();
            $section.find('[name="manage_inventory_as_timely"]').prop('disabled', false);
        } else {
            $section.addClass('rbfw_hide hide').attr('style', 'display:none !important;');
            $section.find('[name="manage_inventory_as_timely"]').prop('disabled', true);
            if (!$root.find('input[type="hidden"][name="manage_inventory_as_timely"]').length) {
                jQuery('<input>', {
                    type: 'hidden',
                    name: 'manage_inventory_as_timely',
                    value: 'off'
                }).prependTo($wrapper.length ? $wrapper : $section.parent());
            }
        }
    };

    var rbfwPostId = (window.location.search.match(/[?&]post=(\d+)/) || [])[1] || '';
    var rbfwTabStorageKey = 'rbfw_active_tab_' + rbfwPostId;

    var rbfwClassicTabLoaderDone = false;

    function rbfwHideClassicTabLoader() {
        jQuery('.rbfw-tab-loader').fadeOut(280, function() { jQuery(this).remove(); });
    }

    function rbfwInitClassicTabLoader() {
        if (rbfwClassicTabLoaderDone || !jQuery('.mp_tab_details').length) {
            return;
        }
        rbfwClassicTabLoaderDone = true;

        try {
            jQuery('.mp_tab_menu').each(function() {
                var savedTab = rbfwPostId ? localStorage.getItem(rbfwTabStorageKey) : null;
                var $menu = jQuery(this);
                var $target = savedTab ? $menu.find('ul li[data-target-tabs="' + savedTab + '"]') : jQuery();
                // Strip active from all tabs so the !hasClass('active') guard never blocks restore
                $menu.find('ul li').removeClass('active');
                if ($target.length && $target.is(':visible')) {
                    $target.trigger('click');
                } else if ($target.length) {
                    // target exists but is hidden (e.g. hidden by item-type logic) — fall back to first visible tab
                    var $firstVisible = $menu.find('ul li:visible').first();
                    ($firstVisible.length ? $firstVisible : $menu.find('ul li:first-child')).trigger('click');
                } else {
                    $menu.find('ul li:first-child').trigger('click');
                }
            });
            if (jQuery('[name="mep_org_address"]').val() > 0) {
                jQuery('.mp_event_address').slideUp(250);
            }
        } finally {
            rbfwHideClassicTabLoader();
        }
    }

    // Inject skeleton loader as soon as the DOM is ready
    jQuery(document).ready(function() {
        var $tabDetails = jQuery('.mp_tab_details');
        if (!$tabDetails.length) {
            return;
        }

        var skeletonRows = '';
        for (var s = 0; s < 7; s++) {
            var widths = [55, 75, 40, 85, 60, 70, 50];
            skeletonRows += '<div class="rbfw-sk-row">'
                + '<div class="rbfw-sk rbfw-sk-icon"></div>'
                + '<div class="rbfw-sk rbfw-sk-label" style="width:' + widths[s] + '%"></div>'
                + '<div class="rbfw-sk rbfw-sk-input"></div>'
                + '</div>';
        }
        $tabDetails.prepend(
            '<div class="rbfw-tab-loader">'
            +   '<div class="rbfw-sk rbfw-sk-title"></div>'
            +   '<div class="rbfw-sk rbfw-sk-sub"></div>'
            +   skeletonRows
            + '</div>'
        );

        jQuery(window).on('load.rbfwClassicTabLoader', rbfwInitClassicTabLoader);
        setTimeout(rbfwInitClassicTabLoader, 6000);

        if (document.readyState === 'complete') {
            setTimeout(rbfwInitClassicTabLoader, 150);
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
        if (rbfwPostId) {
            localStorage.setItem(rbfwTabStorageKey, jQuery(this).attr('data-target-tabs'));
        }
        return false;
    });

    jQuery(document).ready(function() {

        jQuery(document).find('.mp_event_type_sortable').sortable({
            handle: jQuery(this).find('.mp_event_type_sortable_button')
        });

        jQuery('#add-row').on('click', function() {
            var row = jQuery('.empty-row.screen-reader-text').clone(true);
            row.removeClass('empty-row screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
            return false;
        });

        jQuery(document).on('click', '.remove-row',function(e){
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
                jQuery(this).parents('tr').remove();
                jQuery(this).parents('.rbfw_pdwt_row').remove();
            } else {
                return false;
            }
        });



        jQuery('.remove-row-size,.remove-rbfw_variations_table_row').on('click', function() {
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });

        jQuery('.remove-rbfw_variations_value_table_row').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
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
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
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



        jQuery('#field-wrapper-rbfw_time_slot_switch label').click(function(e) {
            let this_attr = jQuery(this).attr('for');
            if (jQuery(this).hasClass('checked') && this_attr == 'rbfw_time_slot_switch-on') {
                jQuery('tr[data-row=rdfw_available_time]').show();
            } else if (jQuery(this).hasClass('checked') && this_attr == 'rbfw_time_slot_switch-off') {
                jQuery('tr[data-row=rdfw_available_time]').hide();
            }
        });

        var current_item_type = jQuery('#rbfw_item_type').val();

        jQuery('#rbfw_add_meta_box').attr('data-item-type', current_item_type);
        jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').attr('data-item-type', current_item_type);

        if (typeof window.rbfwSpSyncSeasonalPanelForRentType === 'function') {
            window.rbfwSpSyncSeasonalPanelForRentType(current_item_type, jQuery('#rbfw_add_meta_box'));
        }

        // Extra service sections: one category per rental type (initial load).
        window.rbfwUpdateExtraServiceSectionVisibility(current_item_type);

        if (current_item_type == 'bike_car_sd' || current_item_type == 'appointment') {
            jQuery('tr[data-row=rbfw_time_slot_switch]').show();
        }else if(current_item_type == 'multiple_items'){
            jQuery('.rbfw_min_max_booking_day_row').hide();
        } else {
            jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
            jQuery('tr[data-row=rdfw_available_time]').show();
        }

        if (current_item_type == 'appointment') {
            jQuery('section.appointment-onday').removeClass('hide').show();
            window.rbfwSetTimelyInventorySection('#rbfw_add_meta_box', false);
            jQuery('.rbfw_time_inventory').hide();
            jQuery('.rbfw_item_stock_quantity').hide();
        } else {
            jQuery('section.appointment-onday').addClass('hide').hide();
            if (current_item_type == 'bike_car_sd') {
                window.rbfwSetTimelyInventorySection('#rbfw_add_meta_box', true);
            }
        }


        var rbfwLegacyEsHasData = !!parseInt(jQuery('.rbfw_es_price_config_wrapper').data('has-legacy-data'), 10);

        function rbfwUpdateRentTypeDesc($card) {
            if (!$card.length) return;
            var type_desc = $card.data('rent-type-desc');
            var name = $card.clone().find('.icon').remove().end().text().trim();
            var $desc = jQuery('.rbfw-rent-type-desc');
            if (!$desc.length) return;
            $desc.html('<strong class="rbfw-rent-type-desc-name">' + name + '</strong>' + type_desc);

            // Position the ::before arrow relative to the desc box's own left edge
            var descOffset = $desc.offset();
            var cardOffset = $card.offset();
            if (descOffset && cardOffset) {
                var cardCenter = cardOffset.left - descOffset.left + $card.outerWidth() / 2;
                $desc[0].style.setProperty('--rbfw-arrow-left', cardCenter + 'px');
            }
        }

        rbfwUpdateRentTypeDesc(jQuery('.rbfw-rent-type.selected'));

        jQuery('.rbfw-rent-type').on('click', function() {
            // The modern editor reuses these very rent-type cards but drives the
            // per-type sections from its own delegated handler on the pricing panel.
            // This legacy handler is bound DIRECTLY to the card and ends in
            // `return false` (= preventDefault + stopPropagation), so leaving it
            // active here swallowed the event before it could bubble: the modern
            // applyType() never ran, `data-item-type` was never updated, and every
            // section keyed off that attribute — the Discount Over x-days addon card
            // among them — stayed stuck on the previously saved rent type.
            if ( ! rbfwIsLegacyEditorTarget(this) ) return;

            var item_type = jQuery(this).data('rent-type');
            jQuery('#rbfw_item_type').val(item_type);
            jQuery('#rbfw_add_meta_box').attr('data-item-type', item_type);
            jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').attr('data-item-type', item_type);
            jQuery('.rbfw-rent-type').removeClass('selected');
            jQuery(this).addClass('selected');
            rbfwUpdateRentTypeDesc(jQuery(this));

            if (item_type == 'bike_car_sd') {
                jQuery('.rbfw_bike_car_sd_wrapper').show();
                if ( jQuery('[name="manage_inventory_as_timely"]').val() === 'on' && jQuery('[name="enable_specific_duration"]').val() === 'on' ) {
                    jQuery('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').hide();
                }
                jQuery('.rbfw_general_price_config_wrapper').hide();
                jQuery('.rbfw_switch_extra_service_qty').hide();
                // Single Day now supports item variations: keep the Inventory/Variations tab visible.
                jQuery('li[data-target-tabs="#rbfw_variations"]').show();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').show();
                jQuery('.rbfw_switch_sd_appointment_row').addClass('hide').removeClass('show').hide();
                jQuery('section.appointment-onday').addClass('hide').hide();
                jQuery('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').show();
                jQuery('.rbfw_discount_price_config_wrapper').hide();
                jQuery('.rbfw_min_max_booking_day_row').hide();
                jQuery('tr[data-row=rbfw_time_slot_switch]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('.regular_fixed_date').hide();
                jQuery('.rbfw_off_days').show();
                jQuery('.wervice_quantity_input_box').show();

                jQuery('.sd-add-type-and-sessional').show();

                window.rbfwSetTimelyInventorySection('#rbfw_add_meta_box', true);

                syncTimelyColumns();
                jQuery('.rbfw_multiple_items').hide();

                jQuery('table.wprently_fee-table th:nth-child(3)').hide();
                jQuery('table.wprently_fee-table td:nth-child(3)').hide();

            } else if (item_type == 'appointment') {
                jQuery('.rbfw_bike_car_sd_wrapper').show();
                jQuery('.rbfw_general_price_config_wrapper').addClass('rbfw-d-none');
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.mp_tab_item[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').hide();
                jQuery('.rbfw_switch_sd_appointment_row').removeClass('hide').addClass('show').show();
                jQuery('section.appointment-onday').removeClass('hide').show();
                jQuery('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').hide();
                jQuery('.rbfw_discount_price_config_wrapper').hide();
                jQuery('.rbfw_min_max_booking_day_row').hide();
                jQuery('tr[data-row=rbfw_time_slot_switch]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('[name="rbfw_off_days"]').val('');
                jQuery('.rbfw_off_days input').prop('checked', false);
                jQuery('.rbfw_off_days').show();
                jQuery('.regular_fixed_date').hide();

                jQuery('.sd-add-type-and-sessional').hide();

                window.rbfwSetTimelyInventorySection('#rbfw_add_meta_box', false);
                jQuery('.rbfw_time_inventory').hide();
                jQuery('.rbfw_without_time_inventory').show();
                jQuery('.rbfw_item_stock_quantity').hide();

                let this_table_row_length = jQuery('.rbfw_bike_car_sd_price_table_row').length;

                for (let index = 0; index < this_table_row_length; index++) {
                    if (index > 0) {
                        jQuery('.rbfw_bike_car_sd_price_table_row[data-key="' + index + '"]').remove();
                    }
                }
                jQuery('.rbfw_multiple_items').hide();

                jQuery('table.wprently_fee-table th:nth-child(3)').hide();
                jQuery('table.wprently_fee-table td:nth-child(3)').hide();

            } else if (item_type == 'resort') {
                jQuery('.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.mp_tab_item[data-target-tabs="#rbfw_location_config"]').show();
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').hide();
                jQuery('.rbfw_bike_car_sd_wrapper').hide();
                jQuery('.rbfw_general_price_config_wrapper').hide();
                jQuery('.rbfw_resort_price_config_wrapper').show();
                jQuery('.rbfw_location_switch').hide();
                jQuery('.rbfw_switch_sd_appointment_row').addClass('hide').removeClass('show').hide();
                jQuery('section.appointment-onday').addClass('hide').hide();
                jQuery('.rbfw_discount_price_config_wrapper').show();
                jQuery('.rbfw_min_max_booking_day_row').show();
                jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
                jQuery('tr[data-row=rdfw_available_time]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('.rbfw_off_days').show();
                jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').show();

                jQuery('.rbfw_multiple_items').hide();

                jQuery('table.wprently_fee-table th:nth-child(3)').show();
                jQuery('table.wprently_fee-table td:nth-child(3)').show();

            }else if (item_type == 'multiple_items') {

                jQuery('.rbfw_bike_car_sd_wrapper').hide();
                jQuery('.rbfw_general_price_config_wrapper').addClass('rbfw-d-none');
                jQuery('.rbfw_switch_extra_service_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_variations"]').hide();
                jQuery('.rbfw_switch_md_type_item_qty').hide();
                jQuery('li[data-target-tabs="#rbfw_date_settings_meta_boxes"]').show();
                jQuery('.rbfw_resort_price_config_wrapper').hide();
                jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').show();
                jQuery('.rbfw_switch_sd_appointment_row').addClass('hide').removeClass('show').hide();
                jQuery('section.appointment-onday').addClass('hide').hide();
                jQuery('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').show();
                jQuery('.rbfw_discount_price_config_wrapper').hide();
                jQuery('.rbfw_min_max_booking_day_row').hide();
                jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
                jQuery('.rbfw_enable_start_end_date_switch_row').hide();
                jQuery('.rbfw_enable_start_end_date_field_row').hide();
                jQuery('.regular_fixed_date').hide();
                jQuery('.rbfw_off_days').show();
                jQuery('.wervice_quantity_input_box').show();
                jQuery('#add-bike-car-sd-type-row').show();

                window.rbfwSetTimelyInventorySection('#rbfw_add_meta_box', true);

                syncTimelyColumns();

                jQuery('.rbfw_multiple_items').show();

                ['hourly', 'daily', 'weekly', 'monthly'].forEach(function(type) {
                    var cb = document.getElementById('enable' + type.charAt(0).toUpperCase() + type.slice(1));
                    if (cb && !cb.checked) {
                        cb.checked = true;
                        if (typeof toggleGlobalPricing === 'function') {
                            toggleGlobalPricing(type);
                        }
                    }
                });

                jQuery('table.wprently_fee-table th:nth-child(3)').hide();
                jQuery('table.wprently_fee-table td:nth-child(3)').hide();


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
                jQuery('#rbfw_add_meta_box .rbfw_seasonal_price_config_wrapper').show();
                jQuery('.rbfw_switch_sd_appointment_row').addClass('hide').removeClass('show').hide();
                jQuery('section.appointment-onday').addClass('hide').hide();

                jQuery('.rbfw_discount_price_config_wrapper').show();
                jQuery('.rbfw_min_max_booking_day_row').show();
                jQuery('tr[data-row=rbfw_time_slot_switch]').hide();
                jQuery('tr[data-row=rdfw_available_time]').show();
                jQuery('.rbfw_enable_start_end_date_switch_row').show();
                jQuery('.regular_fixed_date').show();
                //jQuery('tr.rbfw_enable_start_end_date_field_row').show();
                jQuery('.rbfw_off_days').show();
                jQuery('.wervice_quantity_input_box').show();

                jQuery('.rbfw_multiple_items').hide();

                jQuery('table.wprently_fee-table th:nth-child(3)').show();
                jQuery('table.wprently_fee-table td:nth-child(3)').show();

            }

            // Return-date release applies only to date-range rentals; hide it for
            // Single Day and Appointment. (The multiple-item section is handled per
            // branch above via .rbfw_switch_md_type_item_qty.)
            jQuery('.rbfw_stock_return_date_section').toggle(item_type !== 'bike_car_sd' && item_type !== 'appointment');

            // Extra service sections: one category per rental type (on type change).
            window.rbfwUpdateExtraServiceSectionVisibility(item_type);

            if (typeof window.rbfwSpSyncSeasonalPanelForRentType === 'function') {
                window.rbfwSpSyncSeasonalPanelForRentType(item_type, jQuery('#rbfw_add_meta_box'));
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
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
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
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
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
                alert(rbfw_admin_i18n('select_event_start_date', 'Please select the event start date!'));
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
                alert(rbfw_admin_i18n('select_the_date', 'Please select the date'));
                return;
            }
            if(start_date && !end_date){
                alert(rbfw_admin_i18n('select_end_time', 'Please select the end time'));
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
                    'nonce' : rbfw_ajax_admin.nonce_get_stock_by_filter
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
                    'nonce' : rbfw_ajax_admin.nonce_get_stock_by_filter
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
                    'nonce' : rbfw_ajax_admin.nonce_get_stock_details
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

        /* Edit Stock: opens the same modal shell with an editable form,
           tailored per rent type (flat qty / variations / per-rent-type /
           per-room), and saves back to the exact meta the item editor uses. */
        jQuery(document).on('click', '.rbfw_stock_edit_details', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            jQuery("#rbfw_stock_view_result_wrap").mage_modal({
                escapeClose: false,
                clickClose: false,
                showClose: true
            });

            let data_id = jQuery(this).attr('data-id');

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                data: {
                    'action' : 'rbfw_get_stock_edit_form',
                    'data_id' : data_id,
                    'nonce' : rbfw_ajax_admin.nonce_get_stock_edit_form
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

        jQuery(document).on('submit', '.rbfw_inv_edit_stock_form', function (e) {
            e.preventDefault();

            let $form = jQuery(this);
            let $msg = $form.find('.rbfw_inv_edit_stock_msg');
            let $save = $form.find('.rbfw_inv_edit_stock_save');
            let saveLabel = $save.html();
            let post_id = $form.data('post-id');

            $msg.text('').removeClass('rbfw_inv_msg_error rbfw_inv_msg_success');
            $save.prop('disabled', true);

            jQuery.ajax({
                type: 'POST',
                url: rbfw_ajax_url,
                dataType: 'json',
                data: $form.serialize() + '&action=rbfw_update_inventory_stock&post_id=' + encodeURIComponent(post_id) + '&nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_update_inventory_stock),
                success: function (response) {
                    if (response && response.success) {
                        /* Make the save unmistakable: the button itself turns into a
                           green "Saved" state (not just a small text line easy to miss
                           right before the modal auto-closes), and the row's pill(s)
                           flash briefly once the modal is gone. */
                        let savedLabel = (typeof rbfwInvI18n !== 'undefined' && rbfwInvI18n.stock_saved) ? rbfwInvI18n.stock_saved : 'Saved!';
                        $msg.text(savedLabel).addClass('rbfw_inv_msg_success');
                        $save.addClass('rbfw_inv_modal_btn_saved').html(
                            '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-11"/></svg> ' + savedLabel
                        );

                        let $row = jQuery('.rbfw_stock_view_details[data-id="' + post_id + '"]').closest('tr.rbfw_inv_row');
                        let $flashTargets = jQuery();

                        function refreshPill($pill, $soldBadge, newTotal) {
                            if (newTotal === null || !$pill.length) { return; }
                            let soldQty = parseFloat($soldBadge.text()) || 0;
                            let remaining = newTotal - soldQty;

                            $pill.removeClass('full zero');
                            if (newTotal <= 0 || remaining <= 0) {
                                $pill.addClass('zero');
                            } else if (remaining >= newTotal) {
                                $pill.addClass('full');
                            }
                            $pill.text(remaining + '/' + newTotal);
                            $flashTargets = $flashTargets.add($pill);
                        }

                        if ($row.length) {
                            let newTotal = response.data && typeof response.data.total !== 'undefined' ? parseFloat(response.data.total) : null;
                            refreshPill(
                                $row.find('.rbfw_inv_stock_wrap .rbfw_inv_pill').first(),
                                $row.find('.rbfw_inv_qty_badge').first(),
                                newTotal
                            );

                            let newEsTotal = response.data && typeof response.data.es_total !== 'undefined' ? parseFloat(response.data.es_total) : null;
                            refreshPill(
                                $row.find('.rbfw_inv_td_es_stock .rbfw_inv_pill').first(),
                                $row.find('.rbfw_inv_td_es_sold .rbfw_inv_qty_badge').first(),
                                newEsTotal
                            );
                        }

                        setTimeout(function () {
                            if (jQuery.mage_modal && typeof jQuery.mage_modal.isActive === 'function' && jQuery.mage_modal.isActive()) {
                                jQuery.mage_modal.close();
                            }
                            $flashTargets.addClass('rbfw_inv_pill_flash');
                            setTimeout(function () { $flashTargets.removeClass('rbfw_inv_pill_flash'); }, 1600);
                        }, 1300);
                    } else {
                        let errMsg = (response && response.data && typeof response.data === 'string') ? response.data : 'Something went wrong. Please try again.';
                        $msg.text(errMsg).addClass('rbfw_inv_msg_error');
                        $save.prop('disabled', false).html(saveLabel);
                    }
                },
                error: function () {
                    $msg.text('Something went wrong. Please try again.').addClass('rbfw_inv_msg_error');
                    $save.prop('disabled', false).html(saveLabel);
                }
            });
        });
        /* end inventory filter and view details */


        const monthlyPriceToggle = jQuery('.monthly-price-toggle');
        const weeklyPriceToggle = jQuery('.weekly-price-toggle');
        const dailyPriceToggle = jQuery('.daily-price-toggle');

        const rbfw_enable_monthly_rate = jQuery('#rbfw_enable_monthly_rate');
        const rbfw_enable_weekly_rate = jQuery('#rbfw_enable_weekly_rate');
        const rbfw_enable_daily_rate = jQuery('#rbfw_enable_daily_rate');

        let monthlyPriceEnabled = rbfw_enable_monthly_rate.val() === 'yes';
        let weeklyPriceEnabled = rbfw_enable_weekly_rate.val() === 'yes';
        let dailyPriceEnabled = rbfw_enable_daily_rate.val() === 'yes';

        const monthlyPriceItem = jQuery('.day-threshold-item-for-month');
        const weeklyPriceItem = jQuery('.day-threshold-item-for-week');
        const hourlyPriceItem = jQuery('.hourly-price-item');
        const halfDayPriceItem = jQuery('.half-day-price-item');

        const monthlyPriceInput = jQuery('#monthly-price-input');
        const weeklyPriceInput = jQuery('#weekly-price-input');
        const dailyPriceInput = jQuery('#daily-price-input');


        monthlyPriceToggle.on('click', toggleMonthlyPrice);
        weeklyPriceToggle.on('click', toggleWeeklyPrice);
        dailyPriceToggle.on('click', toggleDailyPrice);



        const monthThresholdToggle = jQuery('.day-threshold-toggle-for-month');
        const weekThresholdToggle = jQuery('.day-threshold-toggle-for-week');
        const hourThresholdToggle = jQuery('.hour-threshold-toggle');

        const rbfw_enable_day_threshold_for_monthly = jQuery('#rbfw_enable_day_threshold_for_monthly');
        const rbfw_enable_day_threshold_for_weekly = jQuery('#rbfw_enable_day_threshold_for_weekly');
        const rbfw_enable_time_picker = jQuery('#rbfw_enable_time_picker');

        let monthThresholdEnabled = rbfw_enable_day_threshold_for_monthly.val() === 'yes';
        let weekThresholdEnabled = rbfw_enable_day_threshold_for_weekly.val() === 'yes';
        let hourThresholdEnabled = rbfw_enable_time_picker.val() === 'yes';

        const monthlyThresholdInput = jQuery('#day-threshold-input-for-monthly');
        const weeklyThresholdInput = jQuery('#day-threshold-input-for-weekly');
        const hourThresholdDisplay = jQuery('#hour-threshold-display');



        monthThresholdToggle.on('click', toggleMonthThreshold);
        weekThresholdToggle.on('click', toggleWeekThreshold);
        hourThresholdToggle.on('click', toggleHourThreshold);


        const timePickerToggle = jQuery('.time-picker-toggle');
        const hourlyPriceToggle = jQuery('.hourly-price-toggle');
        const halfDayPriceToggle = jQuery('.half-day-price-toggle');


        const hourlyPriceInput = jQuery('#hourly-price-input');
        const halfDayPriceInput = jQuery('#half-day-price-input');
        const hourThresholdInput = jQuery('#hour-threshold-input');

        hourlyPriceToggle.on('click', toggleHourlyPrice);
        halfDayPriceToggle.on('click', toggleHalfDayPrice);



        const hourThresholdItem = jQuery('.hour-threshold-item');
        const timeSlotsSection = jQuery('.time-slots-section');




        const rbfw_enable_hourly_rate = jQuery('#rbfw_enable_hourly_rate');
        const rbfw_enable_half_day_rate = jQuery('#rbfw_enable_half_day_rate');
        const rbfw_enable_hourly_threshold = jQuery('#rbfw_enable_hourly_threshold');

        // State


        let timePickerEnabled = rbfw_enable_time_picker.val() === 'yes';
        let hourlyPriceEnabled = rbfw_enable_hourly_rate.val() === 'yes';
        let halfDayPriceEnabled = rbfw_enable_half_day_rate.val() === 'yes';



        let timeSlots = [];

        // Toggle functions
        function toggleMonthlyPrice() {
            monthlyPriceEnabled = !monthlyPriceEnabled;
            monthlyPriceToggle.toggleClass('active', monthlyPriceEnabled);
            monthlyPriceInput.prop('disabled', !monthlyPriceEnabled);
            rbfw_enable_monthly_rate.val(monthlyPriceEnabled ? 'yes' : 'no');
            monthlyPriceItem.css('display', monthlyPriceEnabled ? 'flex' : 'none');
        }

        function toggleWeeklyPrice() {
            weeklyPriceEnabled = !weeklyPriceEnabled;
            weeklyPriceToggle.toggleClass('active', weeklyPriceEnabled);
            weeklyPriceInput.prop('disabled', !weeklyPriceEnabled);
            rbfw_enable_weekly_rate.val(weeklyPriceEnabled ? 'yes' : 'no');
            weeklyPriceItem.css('display', weeklyPriceEnabled ? 'flex' : 'none');
        }

        function toggleDailyPrice() {
            dailyPriceEnabled = !dailyPriceEnabled;
            dailyPriceToggle.toggleClass('active', dailyPriceEnabled);
            dailyPriceInput.prop('disabled', !dailyPriceEnabled);
            rbfw_enable_daily_rate.val(dailyPriceEnabled ? 'yes' : 'no');
            jQuery('.rbfw-daywise-dailyprice-col').css('display', dailyPriceEnabled ? '' : 'none');
            updateDaywisePricingVisibility();
        }

        function toggleMonthThreshold() {
            monthThresholdEnabled = !monthThresholdEnabled;
            monthThresholdToggle.toggleClass('active', monthThresholdEnabled);
            monthlyThresholdInput.prop('disabled', !monthThresholdEnabled);
            rbfw_enable_day_threshold_for_monthly.val(monthThresholdEnabled ? 'yes' : 'no');
        }

        function toggleWeekThreshold() {
            weekThresholdEnabled = !weekThresholdEnabled;
            weekThresholdToggle.toggleClass('active', weekThresholdEnabled);
            weeklyThresholdInput.prop('disabled', !weekThresholdEnabled);
            rbfw_enable_day_threshold_for_weekly.val(weekThresholdEnabled ? 'yes' : 'no');
        }

        function toggleHourThreshold() {
            hourThresholdEnabled = !hourThresholdEnabled;
            hourThresholdToggle.toggleClass('active', hourThresholdEnabled);
            hourThresholdInput.prop('disabled', !hourThresholdEnabled);
            rbfw_enable_hourly_threshold.val(hourThresholdEnabled ? 'yes' : 'no');
        }


        jQuery('.time-picker-toggle').on('click', function() {
            timePickerEnabled = !timePickerEnabled;
            timePickerToggle.toggleClass('active', timePickerEnabled);
            hourlyPriceItem.css('display', timePickerEnabled ? 'flex' : 'none');
            timeSlotsSection.css('display', timePickerEnabled ? 'block' : 'none');
            jQuery('.rbfw-daywise-hourly-col').css('display', (timePickerEnabled && hourlyPriceEnabled) ? '' : 'none');
            jQuery('.rbfw-daywise-halfday-col').css('display', (timePickerEnabled && halfDayPriceEnabled) ? '' : 'none');
            updateDaywisePricingVisibility();

            const $toggle = jQuery(this);
            const $input = jQuery('.rbfw_enable_time_picker');
            if ($toggle.hasClass('active')) {
                $input.val('yes');
            } else {
                $input.val('no');
            }

        })

        jQuery('.daywise-price-toggle').on('click', function () {
            if (jQuery(this).closest('.rbfw-me-wrap').length) {
                return;
            }
            var $toggle  = jQuery(this);
            var $wrapper = $toggle.closest('#rbfw-daywise-config-wrapper');
            var $input   = $wrapper.find('input[name="rbfw_enable_daywise_price"]');
            var $panel   = $wrapper.children('.day-wise-price-configuration');
            var enabled  = !$toggle.hasClass('active');
            $toggle.toggleClass('active', enabled);
            $input.val(enabled ? 'yes' : 'no');
            if (enabled) {
                $panel.stop(true, true).slideDown().removeClass('hide').addClass('show');
            } else {
                $panel.stop(true, true).slideUp().removeClass('show').addClass('hide');
            }
        });

        // Hide "Enable Time Picker" row and force it off when enable_specific_duration is on
        function syncTimePickerWithSpecificDuration() {
            var specificDurationOn = jQuery('.enable_specific_duration').is(':checked');

            if ( specificDurationOn && timePickerEnabled ) {
                timePickerEnabled = false;
                timePickerToggle.removeClass('active');
                hourlyPriceItem.css('display', 'none');
                timeSlotsSection.css('display', 'none');
                jQuery('.rbfw_enable_time_picker').val('no');
            }
        }

        syncTimePickerWithSpecificDuration();
        jQuery('.enable_specific_duration').on('change', function () {
            jQuery(this).val(this.checked ? 'on' : 'off');
            syncTimePickerWithSpecificDuration();
            syncTimelyColumns();
        });

        // Sync value attribute and all related UI when timely inventory is toggled
        jQuery(document).on('change', '[name="manage_inventory_as_timely"]', function () {
            var isTimely = this.checked;
            jQuery(this).val(isTimely ? 'on' : 'off');
            // Direct DOM traversal: stock-quantity section is the immediate next sibling
            var $stockSection = jQuery(this).closest('section').next('.rbfw_item_stock_quantity');
            if (isTimely) {
                $stockSection.removeClass('rbfw_hide').css('display', 'block');
                $stockSection.find('.rbfw_item_quantiry_duration').css('display', '');
            } else {
                $stockSection.addClass('rbfw_hide').css('display', 'none');
                $stockSection.find('.rbfw_item_quantiry_duration').css('display', 'none');
            }
            syncTimelyColumns();
        });

        // Syncs all timely-dependent columns based on both toggle states:
        // .duration_enable  — start/end time cols   (timely=on AND specific=on)
        // .duration_disable — duration/d_type cols  (timely=on AND specific=off)
        // .rbfw_item_stock_quantity section          (timely=on)
        // .rbfw_without_time_inventory col           (timely=off)
        function syncTimelyColumns() {
            var isTimely   = jQuery('[name="manage_inventory_as_timely"]').is(':checked');
            var isSpecific = jQuery('[name="enable_specific_duration"]').is(':checked');

            var $stockSection = jQuery('.rbfw_time_inventory.rbfw_item_stock_quantity');
            if (isTimely) {
                $stockSection.removeClass('rbfw_hide').css('display', 'block');
                jQuery('.rbfw_item_quantiry_duration').css('display', '');
            } else {
                $stockSection.addClass('rbfw_hide').css('display', 'none');
                jQuery('.rbfw_item_quantiry_duration').css('display', 'none');
            }

            if (isTimely) { jQuery('.rbfw_without_time_inventory').hide(); }
            else          { jQuery('.rbfw_without_time_inventory').show(); }

            if (isTimely && isSpecific)  { jQuery('.rbfw_time_inventory.duration_enable').show(); }
            else                          { jQuery('.rbfw_time_inventory.duration_enable').hide(); }

            if (isTimely && !isSpecific) { jQuery('.rbfw_time_inventory.duration_disable').show(); }
            else                          { jQuery('.rbfw_time_inventory.duration_disable').hide(); }

            // Only toggle the single-day Enable Time Picker for bike_car_sd / appointment.
            // For multiple_items the .rbfw_bike_car_sd_wrapper is already hidden by the rent-type switch.
            var _currentType = jQuery('#rbfw_item_type').val();
            if ( _currentType === 'bike_car_sd' || _currentType === 'appointment' ) {
                if (isTimely && isSpecific)  { jQuery('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').hide(); }
                else                          { jQuery('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').show(); }
            }
        }


        function updateDaywisePricingVisibility() {
            const atLeastOneEnabled = dailyPriceEnabled || (timePickerEnabled && (hourlyPriceEnabled || halfDayPriceEnabled));
            jQuery('#rbfw-daywise-config-wrapper').css('display', atLeastOneEnabled ? '' : 'none');
        }

        function toggleHourlyPrice() {
            hourlyPriceEnabled = !hourlyPriceEnabled;
            hourlyPriceToggle.toggleClass('active', hourlyPriceEnabled);
            hourlyPriceInput.prop('disabled', !hourlyPriceEnabled);
            rbfw_enable_hourly_rate.val(hourlyPriceEnabled ? 'yes' : 'no');
            jQuery('.rbfw-daywise-hourly-col').css('display', (hourlyPriceEnabled && timePickerEnabled) ? '' : 'none');
            hourThresholdItem.css('display', hourlyPriceEnabled ? 'flex' : 'none');
            updateDaywisePricingVisibility();
        }

        function toggleHalfDayPrice() {
            halfDayPriceEnabled = !halfDayPriceEnabled;
            halfDayPriceToggle.toggleClass('active', halfDayPriceEnabled);
            halfDayPriceInput.prop('disabled', !halfDayPriceEnabled);
            rbfw_enable_half_day_rate.val(halfDayPriceEnabled ? 'yes' : 'no');
            halfDayPriceItem.css('display', halfDayPriceEnabled ? 'flex' : 'none');
            jQuery('.rbfw-daywise-halfday-col').css('display', (halfDayPriceEnabled && timePickerEnabled) ? '' : 'none');
            updateDaywisePricingVisibility();
        }

        // Input change handlers
        dailyPriceInput.on('change', function () {
            const value = parseFloat(jQuery(this).val());
            if (isNaN(value) || value < 0) {
                jQuery(this).val(0);
            }
        });

        hourlyPriceInput.on('change', function () {
            const value = parseFloat(jQuery(this).val());
            if (isNaN(value) || value < 0) {
                jQuery(this).val(0);
            }
        });

        hourThresholdInput.on('change', function () {
            const value = parseFloat(jQuery(this).val());
            if (isNaN(value) || value < 0) {
                jQuery(this).val(0);
            }
            hourThresholdDisplay.text(jQuery(this).val());
        });

        // Event listeners for toggles




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

		var isConfirmed = confirm(rbfw_admin_i18n('confirm_delete_row', 'Are you sure you want to delete this row?'));
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
		var content;
        if ($("#wp-rbfw_faq_content-wrap").hasClass('html-active')){
            content = $('#rbfw_faq_content').val()
        } else {
            content = tinyMCE.get('rbfw_faq_content').getContent();
        }
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
                'nonce' : rbfw_ajax_admin.nonce_faq_data_update
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
        var content;
        if ($("#wp-rbfw_faq_content-wrap").hasClass('html-active')){
            content = $('#rbfw_faq_content').val()
        } else {
            content = tinyMCE.get('rbfw_faq_content').getContent();
        }
		var postID  = $('input[name="rbfw_post_id"]');
		$.ajax({
			url: rbfw_ajax_url,
			type: 'POST',
			data: {
				action: 'rbfw_faq_data_save',
				rbfw_faq_title:title.val(),
				rbfw_faq_content:content,
				rbfw_faq_postID:postID.val(),
                'nonce' : rbfw_ajax_admin.nonce_faq_data_save
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
                'nonce' : rbfw_ajax_admin.nonce_faq_delete_item
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


    // ================ Term. ===================================
    $(document).on('click', '.rbfw-term-item-new', function (e) {
        $('#rbfw-term-msg').html('');
        $('.rbfw_term_save_buttons').show();
        $('.rbfw_term_update_buttons').hide();
        empty_term_form();
    });

    function close_sidebar_modal(e){
        e.preventDefault();
        e.stopPropagation();
        $('.rbfw-modal-container').removeClass('open');
    }

    $(document).on('click', 'input[name=rbfw_enable_term_content]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no')
            $('.rbfw-term-section').slideUp();
        }
        if (status === 'no') {
            $(this).val('yes');
            $('.rbfw-term-section').slideDown();
        }
    });

    $(document).on('click', '.rbfw-term-item-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#rbfw-term-msg').html('');
        $('.rbfw_term_save_buttons').hide();
        $('.rbfw_term_update_buttons').show();
        var itemId = $(this).closest('.rbfw-term-item').data('id');
        var parent = $(this).closest('.rbfw-term-item');
        var headerTextRequired = parent.find('.term-header .mep-term-required').text().trim();
        var headerText = parent.find('.term-header p.term_title').text().trim();
        var headerTexturl = parent.find('.term-header p.term_url').text().trim();
        $('input[name="rbfw_term_condition_required"]').val(headerTextRequired);

        if(headerTextRequired=='yes'){
            $('input[name="rbfw_term_condition_required"]').prop('checked', true);
        }


        $('input[name="rbfw_term_title"]').val(headerText);
        $('input[name="rbfw_term_url"]').val(headerTexturl);
        $('input[name="rbfw_term_item_id"]').val(itemId);

    });

    $(document).on('click', '.rbfw-term-item-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.rbfw-term-item').data('id');

        var isConfirmed = confirm(rbfw_admin_i18n('confirm_delete_row', 'Are you sure you want to delete this row?'));
        if (isConfirmed) {
            delete_term_item(itemId);
        } else {
            console.log('Deletion canceled.'+itemId);
        }
    });


    function empty_term_form(){
        $('input[name="rbfw_term_title"]').val('');
        $('input[name="rbfw_term_url"]').val('');
        $('input[name="rbfw_term_item_id"]').val('');
    }


    $(document).on('click', '#rbfw_term_update', function (e) {
        e.preventDefault();
        update_term();
    });

    $(document).on('click', '#rbfw_term_save', function (e) {
        e.preventDefault();
        save_term();
    });

    $(document).on('click', '#rbfw_term_save_close', function (e) {
        e.preventDefault();
        save_term();
        close_sidebar_modal(e);
    });

    function update_term(){
        var is_required   = $('input[name="rbfw_term_condition_required"]');
        var title   = $('input[name="rbfw_term_title"]');
        var url   = $('input[name="rbfw_term_url"]');
        var url   = $('input[name="rbfw_term_url"]');

        var postID  = $('input[name="rbfw_post_id"]');
        var itemId = $('input[name="rbfw_term_item_id"]');
        $.ajax({
            url: rbfw_ajax_url,
            type: 'POST',
            data: {
                action: 'rbfw_term_data_update',
                rbfw_term_required:is_required.val(),
                rbfw_term_title:title.val(),
                rbfw_term_url:url.val(),
                rbfw_term_postID:postID.val(),
                rbfw_term_itemID:itemId.val(),
                'nonce' : rbfw_ajax_admin.nonce_term_data_update
            },
            success: function(response) {
                $('#rbfw-term-msg').html(response.data.message);
                $('.rbfw-term-items').html('');
                $('.rbfw-term-items').append(response.data.html);
                setTimeout(function(){
                    $('.rbfw-modal-container').removeClass('open');
                    empty_term_form();
                },1000);

            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    }

    function save_term(){
        var is_required   = $('input[name="rbfw_term_condition_required"]');
        var title   = $('input[name="rbfw_term_title"]');
        var url   = $('input[name="rbfw_term_url"]');
        var postID  = $('input[name="rbfw_post_id"]');
        $.ajax({
            url: rbfw_ajax_url,
            type: 'POST',
            data: {
                action: 'rbfw_term_data_save',
                rbfw_term_title:title.val(),
                rbfw_term_required:is_required.val(),
                rbfw_term_url:url.val(),
                rbfw_term_postID:postID.val(),
                'nonce' : rbfw_ajax_admin.nonce_term_data_save
            },
            success: function(response) {
                $('#rbfw-term-msg').html(response.data.message);
                $('.rbfw-term-items').html('');
                $('.rbfw-term-items').append(response.data.html);
                empty_term_form();
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    }

    function delete_term_item(itemId){
        var postID  = $('input[name="rbfw_post_id"]');
        $.ajax({
            url: rbfw_ajax_url,
            type: 'POST',
            data: {
                action: 'rbfw_term_delete_item',
                rbfw_term_postID:postID.val(),
                itemId:itemId,
                'nonce' : rbfw_ajax_admin.nonce_term_delete_item
            },
            success: function(response) {
                $('.rbfw-term-items').html('');
                $('.rbfw-term-items').append(response.data.html);
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    }


    $(document).on('click', 'input[name=rbfw_term_condition_required]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no')
        }
        if (status === 'no') {
            $(this).val('yes');
        }
    });



   
    // ================toggle switch, ===================
    /**
     * it should move from internal script to here
     * then all should in one function
     */
     // Toggle visibility for category service price
    $(document).on('click', 'input[name=rbfw_enable_category_service_price]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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




    $(document).on('click', 'input[name=rbfw_enable_extra_service_qty]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no');
        }
        if (status === 'no') {
            $(this).val('yes');
        }

    });


    // Legacy "value-flip" click handlers. These were written for the classic
    // editor, where the checkbox's value attribute (not its checked state) was
    // the source of truth. They run on every admin page and would also fire on
    // the modern editor's toggles, flipping the value attribute to the OPPOSITE
    // of what the user just clicked — so collectFormData() would read the wrong
    // value and the AJAX save would write the inverse of the user's intent.
    // The modern editor manages these toggles through rbfw-modern-editor.js
    // (initToggles() + collectFormData()) and does not need these handlers, so
    // skip them whenever the click target lives inside the modern editor wrap.
    $(document).on('click', 'input[name=shipping_enable]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
        var status = $(this).val();
        if (status === 'yes') {
            $(this).val('no')
        }
        if (status === 'no') {
            $(this).val('yes');
        }
    });
    $(document).on('click', 'input[name=rbfw_enable_faq_content]', function (e) {
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
        if ( ! rbfwIsLegacyEditorTarget(this) ) return;
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
            let resort_last_row = $('.rbfw_resort_price_table .rbfw_resort_price_table_row:last-child');
            let resort_type_last_data_key = parseInt(resort_last_row.attr('data-key'));
            let resort_type_new_data_key = resort_type_last_data_key + 1;
            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                + '<td><input class="rbfw_room_title" type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="' + rbfw_admin_i18n('room_type', 'Room type') + '"></td>'
                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                + '<td class="resort_day_long_price"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="' + rbfw_admin_i18n('day_long_rate', 'Day-long Rate') + '"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="' + rbfw_admin_i18n('day_night_rate', 'Day-night Rate') + '"></td>'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="' + rbfw_admin_i18n('short_description', 'Short Description') + '"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="' + rbfw_admin_i18n('available_qty', 'Available Qty') + '"></td>'
                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                + '</tr>';
                $('.rbfw_resort_price_table').append(resort_type_row);
        } else {
            let resort_type_new_data_key = 0;
            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="' + rbfw_admin_i18n('room_type', 'Room type') + '"></td>'
                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                + '<td class="resort_day_long_price"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="' + rbfw_admin_i18n('day_long_rate', 'Day-long Rate') + '"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="' + rbfw_admin_i18n('day_night_rate', 'Day-night Rate') + '"></td>'
                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="' + rbfw_admin_i18n('short_description', 'Short Description') + '"></td>'
                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="' + rbfw_admin_i18n('available_qty', 'Available Qty') + '"></td>'
                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                + '</tr>';
                $('.rbfw_resort_price_table').append(resort_type_row);
        }
        $('.remove-row.' + current_time + '').on('click', function () {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
                $(this).parents('tr').remove();
            } else {
                return false;
            }
        });
        $(".rbfw_resort_price_table_body").sortable();

        if (typeof window.rbfwSpScheduleResortSeasonalSync === 'function') {
            var $root = jQuery('#rbfw_add_meta_box').first();
            if (!$root.length) {
                $root = jQuery('.rbfw-me-wrap').first();
            }
            window.rbfwSpScheduleResortSeasonalSync($root.length ? $root : jQuery(document), true);
        }

        var daylong_price_label_val = $('input[name="rbfw_enable_resort_daylong_price"]').val();

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

    jQuery(document).on('click', '#add-particular-row', function() {
        let parent = jQuery(this).closest('.available-particular');
        let item = parent.find('.mp_hidden_content').first().find('.mp_hidden_item').html();
        let total_element = jQuery(".rbfw_pdwt_insert").children().length;
        const rent_type = $(this).data('rent_type');

        let tempDiv = jQuery(item);
        if(rent_type=='md'){
            tempDiv.find(".rbfw_start_date").attr({"name": "rbfw_particulars["+total_element+"][start_date]"});
            tempDiv.find(".rbfw_end_date").attr({"name": "rbfw_particulars["+total_element+"][end_date]"});
            tempDiv.find(".add-slot-btn").attr({"data-particular_id": total_element});
        }else if(rent_type=='mi'){
            tempDiv.find(".rbfw_start_date").attr({"name": "rbfw_particulars_mi["+total_element+"][start_date]"});
            tempDiv.find(".rbfw_end_date").attr({"name": "rbfw_particulars_mi["+total_element+"][end_date]"});
            tempDiv.find(".add-slot-btn").attr({"data-particular_id": total_element});
        }else{
            tempDiv.find(".rbfw_start_date").attr({"name": "rbfw_particulars_sd["+total_element+"][start_date]"});
            tempDiv.find(".rbfw_end_date").attr({"name": "rbfw_particulars_sd["+total_element+"][end_date]"});
            tempDiv.find(".add-slot-btn").attr({"data-particular_id": total_element});
        }


        tempDiv.find(".rbfw_particulars_date").datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });


        parent.find(".rbfw_pdwt_insert").first().append(tempDiv);

        jQuery(".date_type").datepicker({

        })

    });

    $(document).on('click', '.time-slot-remove', function () {
        $(this).closest('.time-slot').remove();
    });

    $(document).on('click', '.time-slot-indicator', function () {
        const $indicator = $(this);
        const $timeSlot = $indicator.closest('.time-slot');
        const $statusInput = $timeSlot.find('input[name*="[status]"]');

        // Toggle active class
        $indicator.toggleClass('active');

        // Set input value based on class presence
        if ($indicator.hasClass('active')) {
            $statusInput.val('enabled');
            $timeSlot.removeClass('disabled').addClass('enabled');
        } else {
            $statusInput.val('');
            $timeSlot.removeClass('enabled').addClass('disabled');
        }
    });

    $(document).on('change', '.new-slot-time', function () {
        const timeValue = $(this).val();
        if (timeValue) {
            $(this).closest('.add-slot-form').find('.add-slot-btn').prop('disabled', false);
        } else {
            $(this).closest('.add-slot-form').find('.add-slot-btn').prop('disabled', true);
        }
    });


    $(document).on('click', '.add-slot-btn', function (e) {

        e.preventDefault(); // prevent form submission if inside form

        const $btn = $(this);
        const rawTime = $btn.closest('.add-slot-form').find('.new-slot-time').val();
        if (!rawTime) return;

        // Convert HH:MM (24-hour from <input type="time">) to 12-hour AM/PM
        const [_h, _m] = rawTime.split(':').map(Number);
        const _period = _h >= 12 ? 'PM' : 'AM';
        const _h12 = _h % 12 || 12;
        const time = `${_h12}:${String(_m).padStart(2, '0')} ${_period}`;

        const name_attr = $btn.data('name_attr');
        const rent_type = $btn.data('rent_type');

        // Get a unique index (based on existing slots)
        const $timeSlotsContainer = $btn.closest('.add-slot-container').prevAll('.time-slots-container').first().find('.time-slots');

        const isDuplicate = $timeSlotsContainer.find('.time-slot-time').filter(function () {
            return $(this).text() === time;
        }).length > 0;
        if (isDuplicate) {
            $btn.closest('.add-slot-form').find('.rbfw-slot-duplicate-warning').remove();
            const $warning = $('<span class="rbfw-slot-duplicate-warning" style="display:block;color:#c0392b;font-size:12px;margin-top:4px;"><span class="dashicons dashicons-warning"></span> This time slot already exists.</span>');
            $btn.after($warning);
            setTimeout(function () { $warning.remove(); }, 3000);
            return;
        }

        const index = $timeSlotsContainer.children('.time-slot').length;
        const dataId = $('.rbfw_pdwt_insert').children('.time-slot').length; // Use your actual ID logic here

        // Build time slot HTML
        let newSlot = '';
        if(name_attr == 'rdfw_available_time'){

            if(rent_type=='md'){
                newSlot = `
        <div class="time-slot enabled" data-id="${index}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="${name_attr}[${index}][id]" value="${dataId}">
          <input type="hidden" name="${name_attr}[${index}][time]" value="${time}">
          <input type="hidden" name="${name_attr}[${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
      `;
            }else if(rent_type=='mi'){
                newSlot = `
        <div class="time-slot enabled" data-id="${index}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="rdfw_available_time_mi[${index}][id]" value="${dataId}">
          <input type="hidden" name="rdfw_available_time_mi[${index}][time]" value="${time}">
          <input type="hidden" name="rdfw_available_time_mi[${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
      `;
            }else{
                newSlot = `
        <div class="time-slot enabled" data-id="${index}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="rdfw_available_time_sd[${index}][id]" value="${dataId}">
          <input type="hidden" name="rdfw_available_time_sd[${index}][time]" value="${time}">
          <input type="hidden" name="rdfw_available_time_sd[${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
      `;
            }




        }else{
            const dataId = $(this).data('particular_id');
            if(rent_type=='md'){
                newSlot = `
        <div class="time-slot enabled" data-id="${dataId}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="${name_attr}[${dataId}][available_time][${index}][id]" value="${dataId}">
          <input type="hidden" name="${name_attr}[${dataId}][available_time][${index}][time]" value="${time}">
          <input type="hidden" name="${name_attr}[${dataId}][available_time][${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
           `;
            }else if(rent_type=='mi'){
                newSlot = `
        <div class="time-slot enabled" data-id="${dataId}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="rbfw_particulars_mi[${dataId}][available_time][${index}][id]" value="${dataId}">
          <input type="hidden" name="rbfw_particulars_mi[${dataId}][available_time][${index}][time]" value="${time}">
          <input type="hidden" name="rbfw_particulars_mi[${dataId}][available_time][${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
           `;
            }else{
                newSlot = `
        <div class="time-slot enabled" data-id="${dataId}">
          <span class="time-slot-time">${time}</span>
          <input type="hidden" name="rbfw_particulars_sd[${dataId}][available_time][${index}][id]" value="${dataId}">
          <input type="hidden" name="rbfw_particulars_sd[${dataId}][available_time][${index}][time]" value="${time}">
          <input type="hidden" name="rbfw_particulars_sd[${dataId}][available_time][${index}][status]" value="enabled">

          <div class="time-slot-remove" title="${rbfw_admin_i18n('remove_time_slot', 'Remove time slot')}">×</div>
        </div>
           `;
            }

        }
        // Append to container
        $timeSlotsContainer.append(newSlot);



        var $slots = $timeSlotsContainer.children('.time-slot');
        $slots.sort(function(a, b) {
            var timeA = $(a).find('.time-slot-time').text();
            var timeB = $(b).find('.time-slot-time').text();
            return timeA.localeCompare(timeB);
        });




        $timeSlotsContainer.html($slots);


        // Clear input & disable button
        $('.new-slot-time').val('');
        $('.add-slot-btn').prop('disabled', true);
    });


    $(document).on('click', '.rbfw_particular_switch', function (e) {
        if ($(this).closest('.rbfw-me-wrap').length) {
            return;
        }
        var status = $(this).val();
        if (status === 'on') {
            $(this).val('off');
            $('.available-particular').slideUp().removeClass('show').addClass('hide');
        }
        if (status === 'off') {
            $(this).val('on');
            $('.available-particular').slideDown().removeClass('hide').addClass('show');
        }
    });
    
    $(document).on('click', '.rbfw-single-template', function (e) {
        var currentTemplate = $(this).data('rbfw-template');
        $('#rbfw_single_template').val(currentTemplate);
        $('.rbfw-single-template').removeClass('active')
        $(this).addClass('active');

        $('.additional-gallery').slideUp();
        if(currentTemplate=='Muffin'){
            $('.additional-gallery').slideDown();
        }
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


function getPostIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('post'); // returns the post ID as a string
}

jQuery(document).ready(function () {

    jQuery('input[name=rbfw_enable_variations]').click(function () {
        var status = jQuery(this).val();
        if (status == 'yes') {
            jQuery(this).val('no');
            jQuery('.rbfw_variations_table_wrap').slideUp().removeClass('show').addClass('hide');
            jQuery('.item_stock_quantity input').removeAttr("disabled");
        }
        if (status == 'no') {
            jQuery(this).val('yes');
            jQuery('.rbfw_variations_table_wrap').slideDown().removeClass('hide').addClass('show');
            jQuery('.item_stock_quantity input').attr("disabled", true);
        }
    });
    jQuery('input[name=rbfw_enable_md_type_item_qty]').click(function () {
        var status = jQuery(this).val();
        if (status == 'yes') {
            jQuery(this).val('no');
        }
        if (status == 'no') {
            jQuery(this).val('yes');
        }
    });

    jQuery('input[name=stock_manage_on_return_date]').click(function () {
        var status = jQuery(this).val();
        if (status == 'yes') {
            jQuery(this).val('no');
        }
        if (status == 'no') {
            jQuery(this).val('yes');
        }
    });



    jQuery(document).on('click', '#add-new-variation', function (e) {
        e.preventDefault();
        if (jQuery('.rbfw_variations_table .rbfw_variations_table_row').length > 0) {
            let rbfw_variations_table_last_row = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child');
            let rbfw_variations_table_last_data_key = parseInt(rbfw_variations_table_last_row.attr('data-key'));
            let rbfw_variations_table_new_data_key = rbfw_variations_table_last_data_key + 1;
            let rbfw_variations_table_row = '<div class=rbfw_variations_table_row data-key="' + rbfw_variations_table_new_data_key + '"><header><label for="">' + rbfw_admin_i18n('filed_label', 'Field Label') + '</label><div><input type="text" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][field_label]"placeholder="' + rbfw_translation.filed_label + '"> <input name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][field_id]"type=hidden value="rbfw_variation_id_' + rbfw_variations_table_new_data_key + '"></div></header><div class=variations-inner-table><table class="rbfw_variations_value_table form-table w-100"><thead><th>' + rbfw_translation.variation_name + '<th>' + rbfw_translation.stock_quantity + '<b class="required">*</b><th>' + rbfw_translation.is_default + '<th>' + rbfw_translation.actions + '<tbody class=rbfw_variations_value_table_tbody><tr class=rbfw_variations_value_table_row data-key=0><td><input type="text" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][name]"placeholder="' + rbfw_translation.variation_name + '" class=rbfw_variation_value><td><input name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][quantity]"placeholder="' + rbfw_translation.stock_quantity + '" type=number><td><input type="number" step="0.01" min="0" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][price]" placeholder="' + rbfw_admin_i18n('price', 'Price') + '"></td><td><input name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][selected_value]"type=checkbox class=rbfw_variation_selected_value><td><div class=mp_event_remove_move><button class="button remove-rbfw_variations_value_table_row"type=button><i class="fas fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fa-arrows-alt fas"></i></div></div></table><button class="add-new-variation-value mt-2 ppof-button"><i class="fas fa-circle-plus"></i>' + rbfw_translation.add_new_value + '</button></div><div class=mp_event_remove_move><button class=remove-rbfw_variations_table_row type=button><i class="fas fa-trash-can"></i></button></div></div>';
            jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
        } else {
            let rbfw_variations_table_new_data_key = 0;
            let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="' + rbfw_variations_table_new_data_key + '"><td><input type="text" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][field_label]" placeholder="' + rbfw_translation.filed_label + '"><input type="hidden" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][field_id]" value="rbfw_variation_id_' + rbfw_variations_table_new_data_key + '"></td><td><table class="rbfw_variations_value_table"><thead><th>' + rbfw_translation.stock_quantity + '</th><th>' + rbfw_translation.stock_quantity + '<b class="required">*</b></th><th> ' + rbfw_translation.is_default + '</th><th>' + rbfw_translation.actions + '</th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][name]" placeholder="' + rbfw_translation.variation_name + '" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][quantity]" placeholder="' + rbfw_translation.stock_quantity + '"></td><td><input type="number" step="0.01" min="0" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][value][0][price]" placeholder="' + rbfw_admin_i18n('price', 'Price') + '"></td><td><input type="checkbox" name="rbfw_variations_data[' + rbfw_variations_table_new_data_key + '][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fas fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fas fa-circle-plus"></i>' + rbfw_translation.add_new_value + '</button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
            jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
        }
        initVariationSortables();
    });
    /* Add New Variation Value — delegated so buttons added later (modern editor / new
       variations) fire too. Previously a direct .click() that never bound in the SPA. */
    jQuery(document).on('click', '.add-new-variation-value', function (e) {
            let this_btn = jQuery(this);
            e.preventDefault();
            e.stopImmediatePropagation();
            let c = parseInt(this_btn.attr('data-key'));
            if (jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row').length > 0) {
                let rbfw_variations_value_table_last_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row:last-child');
                let rbfw_variations_value_table_last_data_key = parseInt(rbfw_variations_value_table_last_row.attr('data-key'));
                let rbfw_variations_value_table_new_data_key = rbfw_variations_value_table_last_data_key + 1;
                let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="' + rbfw_variations_value_table_new_data_key + '"><td><input type="text" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][name]" placeholder="' + rbfw_translation.variation_name + '" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][quantity]" placeholder="' + rbfw_translation.stock_quantity + '"></td><td><input type="number" step="0.01" min="0" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][price]" placeholder="' + rbfw_admin_i18n('price', 'Price') + '"></td><td><input type="checkbox" name="rbfw_variations_data[' + c + '][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fas fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);
            } else {
                let rbfw_variations_value_table_new_data_key = 0;
                let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="' + rbfw_variations_value_table_new_data_key + '"><td><input type="text" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][name]" placeholder="' + rbfw_translation.variation_name + '" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][quantity]" placeholder="' + rbfw_translation.stock_quantity + '"></td><td><input type="number" step="0.01" min="0" name="rbfw_variations_data[' + c + '][value][' + rbfw_variations_value_table_new_data_key + '][price]" placeholder="' + rbfw_admin_i18n('price', 'Price') + '"></td><td><input type="checkbox" name="rbfw_variations_data[' + c + '][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fas fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);
            }
            initVariationSortables();
    });
    /* Variation Default Value (delegated). Note: It works for frontend select box */
    jQuery(document).on('change', '.rbfw_variation_selected_value', function () {
        jQuery(this).closest('.rbfw_variations_value_table_tbody').find('.rbfw_variation_selected_value').not(this).prop('checked', false);
    });
    jQuery(document).on('keyup', '.rbfw_variation_value', function () {
        let this_val = jQuery(this).val();
        jQuery(this).closest('td').siblings('td').find('.rbfw_variation_selected_value').val(this_val);
    });
    /* Variation remove buttons (delegated so newly-added rows work too). */
    jQuery(document).on('click', '.remove-rbfw_variations_table_row', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
            jQuery(this).closest('.rbfw_variations_table_row').remove();
        } else {
            return false;
        }
    });
    jQuery(document).on('click', '.remove-rbfw_variations_value_table_row', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (confirm(rbfw_admin_i18n('confirm_remove_row', 'Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .'))) {
            jQuery(this).closest('tr.rbfw_variations_value_table_row').remove();
        } else {
            return false;
        }
    });
    /* Sortable handles — (re)initialised for existing and newly-added variation tables. */
    function initVariationSortables() {
        if (!jQuery.fn.sortable) { return; }
        jQuery(".rbfw_variations_table_body").sortable({ handle: ".mp_event_type_sortable_button" });
        jQuery(".rbfw_variations_value_table_tbody").sortable({ handle: ".rbfw_variations_value_table_row_sortable" });
    }
    initVariationSortables();
});

