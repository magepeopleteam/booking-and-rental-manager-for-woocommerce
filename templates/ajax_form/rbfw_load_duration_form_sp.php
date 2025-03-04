<?php
$manage_inventory_as_timely = $manage_inventory_as_timely??'';
$enable_specific_duration = $enable_specific_duration??'';
$total_row = $total_row??'';
$post_id          = get_the_id();
$rbfw_bike_car_sd_data           = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
?>

<tr class="rbfw_bike_car_sd_price_table_row" data-key="">
    <td>
        <select class="medium" name="rbfw_bike_car_sd_data_sp[<?php echo esc_attr($total_row); ?>][d_type]">
            <?php foreach ( $rbfw_bike_car_sd_data as $key => $value ){ ?>
                <option><?php echo esc_attr( $value['rent_type'] ); ?></option>
            <?php } ?>
        </select>
    </td>

    <td>
        <input class="medium" type="number" name="rbfw_bike_car_sd_data_sp[<?php echo esc_attr($total_row); ?>][price]" step=".01" value="" placeholder="<?php echo esc_attr__('Price', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>

    <td>
        <div class="mp_event_remove_move">
            <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
        </div>
    </td>
</tr>
