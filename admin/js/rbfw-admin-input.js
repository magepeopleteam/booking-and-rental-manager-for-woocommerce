(function($) {
    "use strict";
    jQuery('#add-bike-car-sd-type-row-duration').click(function (e) {
        e.preventDefault();
        let current_time = jQuery.now();
        if(jQuery('.rbfw_bike_car_sd_price_table_duration .rbfw_bike_car_sd_price_table_row').length){
            let bike_car_sd_type_last_row = jQuery('.rbfw_bike_car_sd_price_table_duration .rbfw_bike_car_sd_price_table_row:last-child()');
            let bike_car_sd_type_type_last_data_key = parseInt(bike_car_sd_type_last_row.attr('data-key'));
            let bike_car_sd_type_type_new_data_key = bike_car_sd_type_type_last_data_key + 1;
            let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'">';
            bike_car_sd_type_type_row += '<td><input type="text" name="rbfw_bike_car_sd_data_duration['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>';
            bike_car_sd_type_type_row += '<td><input type="text" name="rbfw_bike_car_sd_data_duration['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" step=".01" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>';
            bike_car_sd_type_type_row += '<td><input type="number" name="rbfw_bike_car_sd_data_duration['+bike_car_sd_type_type_new_data_key+'][price]" step=".01"  value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>';
            bike_car_sd_type_type_row += '<td><input class="medium"  type="number" name="rbfw_bike_car_sd_data_duration['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>';
            bike_car_sd_type_type_row += '<td><select name="rbfw_bike_car_sd_data_duration['+bike_car_sd_type_type_new_data_key+'][d_type]"><option>Hours</option><option>Days</option><option>Weeks</option></select></td>';
            bike_car_sd_type_type_row += '<td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>';
            bike_car_sd_type_type_row += '</tr>';
            jQuery('.rbfw_bike_car_sd_price_table_duration').append(bike_car_sd_type_type_row);
        }
        else{
            let bike_car_sd_type_type_new_data_key = 0;
            let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>';
            bike_car_sd_type_type_row += '<td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" step=".01" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>';
            bike_car_sd_type_type_row += '<td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" step=".01" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>';
            bike_car_sd_type_type_row += '</tr>';
            jQuery('.rbfw_bike_car_sd_price_table_duration').append(bike_car_sd_type_type_row);
        }
        jQuery('.remove-row.'+current_time+'').on('click', function () {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                jQuery(this).parents('tr').remove();
            } else {
                return false;
            }
        });

        jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

    });

}(jQuery));



