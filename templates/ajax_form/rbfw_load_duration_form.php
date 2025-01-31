<?php
$manage_inventory_as_timely = $manage_inventory_as_timely??'';
$enable_specific_duration = $enable_specific_duration??'';
$total_row = $total_row??'';
?>

<tr class="rbfw_bike_car_sd_price_table_row" data-key="">
    <td>
        <input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][rent_type]" value="" placeholder="<?php echo esc_attr__('Type name', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>
    <td>
        <input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][short_desc]" value="" placeholder="<?php echo esc_attr__('Short Description', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>
    <td>
        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][price]" step=".01" value="" placeholder="<?php echo esc_attr__('Price', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>

    <td class="rbfw_without_time_inventory <?php echo esc_attr(($manage_inventory_as_timely == 'on') ? 'rbfw_hide' : ''); ?>">
        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][qty]" placeholder="<?php echo esc_attr__('Stock Quantity', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>

    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr(($manage_inventory_as_timely == 'off') ? 'rbfw_hide' : (($manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off') ? 'rbfw_hide' : '')); ?>">
        <?php rbfw_time_slot_select('start_time', $total_row, ''); ?>
    </td>

    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr(($manage_inventory_as_timely == 'off') ? 'rbfw_hide' : (($manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off') ? 'rbfw_hide' : '')); ?>">
        <?php rbfw_time_slot_select('end_time', $total_row, ''); ?>
    </td>

    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr(($manage_inventory_as_timely == 'off') ? 'rbfw_hide' : (($manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on') ? 'rbfw_hide' : '')); ?>">
        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][duration]" value="" placeholder="<?php echo esc_attr__('Duration', 'booking-and-rental-manager-for-woocommerce'); ?>">
    </td>

    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr(($manage_inventory_as_timely == 'off') ? 'rbfw_hide' : (($manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on') ? 'rbfw_hide' : '')); ?>">
        <select class="medium" name="rbfw_bike_car_sd_data[<?php echo esc_attr($total_row); ?>][d_type]">
            <option><?php echo esc_html__('Hours', 'booking-and-rental-manager-for-woocommerce'); ?></option>
            <option><?php echo esc_html__('Days', 'booking-and-rental-manager-for-woocommerce'); ?></option>
            <option><?php echo esc_html__('Weeks', 'booking-and-rental-manager-for-woocommerce'); ?></option>
        </select>
    </td>
    <td>
        <div class="mp_event_remove_move">
            <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
        </div>
    </td>
</tr>
