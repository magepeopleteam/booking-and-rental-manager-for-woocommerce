(function($) {
    "use strict";

    jQuery(document).on('click','input[name=manage_inventory_as_timely]',function(){
        var status = jQuery(this).val();
        console.log(status);
        if(status == 'on') {
            jQuery(this).val('off');
            jQuery('.rbfw_without_time_inventory').show('slow');
            jQuery('.rbfw_time_inventory').hide('slow');

            if(jQuery('.enable_specific_duration').val()=='on'){ 
                jQuery('.rbfw_time_inventory_enable.durstion_disable').hide('slow')
            }else{
                jQuery('.rbfw_time_inventory_enable.durstion_disable').show('slow')
            }
        }
        if(status == 'off') {
            jQuery(this).val('on');
            jQuery('.rbfw_without_time_inventory').hide('slow');
            jQuery('.rbfw_time_inventory').show('slow');

            if(jQuery('.enable_specific_duration').val()=='off'){
                jQuery('.rbfw_time_inventory_enable.durstion_disable').show('slow')
            }else{
                jQuery('.rbfw_time_inventory_enable.durstion_disable').hide('slow')
            }
        }


    });

    jQuery(document).on('click','input[name=enable_specific_duration]',function(){
        var status = jQuery(this).val();
        console.log(status);
        if(status == 'on') {
            jQuery(this).val('off');
        }
        if(status == 'off') {
            jQuery(this).val('on');
        }
    });


    let current_time = jQuery.now();
    jQuery(document).on('click','#add-bike-car-sd-type-row',function (e) {
        let manage_inventory_as_timely = jQuery('input[name=manage_inventory_as_timely]').val();
        let total_row = $('.rbfw_bike_car_sd_price_table_row').length;
        e.preventDefault();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'rbfw_load_duration_form',
                'manage_inventory_as_timely': manage_inventory_as_timely,
                'total_row': total_row
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

    jQuery(document).on('click','.remove-row', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            jQuery(this).parents('tr').remove();
        } else {
            return false;
        }
    });

}(jQuery));



