(function($) {
    "use strict";

    function syncSdTimeConf() {
        var itemType = jQuery('#rbfw_item_type').val();
        var isTimely = jQuery('input[name=manage_inventory_as_timely]').val() === 'on';
        var isSpecific = jQuery('input[name=enable_specific_duration]').val() === 'on';
        if (itemType === 'bike_car_sd' && isTimely && isSpecific) {
            jQuery('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').hide();
        } else if (itemType === 'bike_car_sd' || itemType === 'appointment') {
            jQuery('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').show();
        }
    }

    $(document).on('click','input[name=manage_inventory_as_timely]',function(){
        
        var status = $(this).val();
        if(status == 'on') {
            $(this).val('off');
            $('.rbfw_without_time_inventory').slideDown();
            $('.rbfw_time_inventory').slideUp();
        }
        if(status == 'off') {
            $(this).val('on');
            $('.rbfw_without_time_inventory').slideUp();
            $('.rbfw_time_inventory').slideDown();
        }

        if($('.enable_specific_duration').val()==='on'){
            $('.rbfw_time_inventory_enable.duration_disable').hide();
            $('.rbfw_time_inventory_enable.duration_enable').show()
        }
        if($('.enable_specific_duration').val()==='off'){
            $('.rbfw_time_inventory_enable.duration_disable').show();
            $('.rbfw_time_inventory_enable.duration_enable').hide()
        }
        syncSdTimeConf();
    });

    $(document).on('click','input[name=enable_specific_duration]',function(){
        var status = $(this).val();
        if(status == 'on') {
            $(this).val('off');
            $('.rbfw_time_inventory_enable.duration_disable').show();
            $('.rbfw_time_inventory_enable.duration_enable').hide();
        }
        if(status == 'off') {
            $(this).val('on');
            $('.rbfw_time_inventory_enable.duration_disable').hide();
            $('.rbfw_time_inventory_enable.duration_enable').show();
        }
        syncSdTimeConf();
    });


    let current_time = jQuery.now();
    jQuery(document).on('click','#add-bike-car-sd-type-row',function (e) {
        let manage_inventory_as_timely = jQuery('input[name=manage_inventory_as_timely]').val();
        let enable_specific_duration = jQuery('input[name=enable_specific_duration]').val();
        let total_row = $('.rbfw_bike_car_sd_price_table_row').length;
        e.preventDefault();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'rbfw_load_duration_form',
                'manage_inventory_as_timely': manage_inventory_as_timely,
                'enable_specific_duration': enable_specific_duration,
                'total_row': total_row,
                'nonce': rbfw_ajax_admin.nonce_duration_form
            },
            success: function(response) {
                jQuery('.rbfw_bike_car_sd_price_table tbody').append(response);

                jQuery('.rbfw_bike_car_sd_price_table_body').sortable({
                    // You can add other options as needed
                    update: function(event, ui) {
                        // Optionally handle the update event
                        console.log('List updated!');
                    }
                });

                if (typeof window.rbfwSpScheduleSdSeasonalSync === 'function') {
                    var $root = jQuery('.rbfw-me-wrap').first();
                    if (!$root.length) {
                        $root = jQuery('#rbfw_add_meta_box').first();
                    }
                    window.rbfwSpScheduleSdSeasonalSync($root.length ? $root : jQuery(document), true);
                }
            },
        });
    })

    jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

    jQuery(document).on('click','.remove-row-off-days', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            jQuery(this).parents('section.off_date_range_child').remove();
            jQuery(this).parents('.off_date_range_remove').remove();
        } else {
            return false;
        }
    });

    $(document).ready(syncSdTimeConf);

}(jQuery));



