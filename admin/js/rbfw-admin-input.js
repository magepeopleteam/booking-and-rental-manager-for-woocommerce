(function($) {
    "use strict";

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
            $('.rbfw_time_inventory_enable.durstion_disable').hide();
            $('.rbfw_time_inventory_enable.duration_enable').show()
        }
        if($('.enable_specific_duration').val()==='off'){
            $('.rbfw_time_inventory_enable.durstion_disable').show();
            $('.rbfw_time_inventory_enable.duration_enable').hide()
        }
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
                'nonce': rbfw_ajax.nonce
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




}(jQuery));



