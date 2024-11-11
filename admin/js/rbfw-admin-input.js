(function($) {
    "use strict";


    jQuery(document).on('click','input[name=manage_inventory_as_timely]',function(){
        var status = jQuery(this).val();
        if(status == 'on') {
            jQuery(this).val('off');
            jQuery('.rbfw_without_time_inventory').hide();
            jQuery('.rbfw_time_inventory').show();
           // jQuery('.available-time-slot').slideUp().removeClass('show').addClass('hide');
        }
        if(status == 'off') {
            jQuery(this).val('on');
            jQuery('.rbfw_without_time_inventory').show();
            jQuery('.rbfw_time_inventory').hide();
           // jQuery('.available-time-slot').slideDown().removeClass('hide').addClass('show');
        }
    });


    let current_time = jQuery.now();
    jQuery(document).on('click','#add-bike-car-sd-type-row',function (e) {
        let manage_inventory_as_timely = jQuery('input[name=manage_inventory_as_timely]').val();
        e.preventDefault();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'rbfw_load_duration_form',
                'manage_inventory_as_timely': manage_inventory_as_timely
            },
            success: function(response) {
                jQuery('.rbfw_bike_car_sd_price_table').append(response);
            },
        });
        jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();
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



