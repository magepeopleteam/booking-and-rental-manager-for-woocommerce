<?php
$manage_inventory_as_timely = $manage_inventory_as_timely??'';
?>

<tr class="rbfw_bike_car_sd_price_table_row" data-key="2">
    <td><input type="text" name="rbfw_bike_car_sd_data_duration[2][rent_type]" value="" placeholder="Type name"></td>
    <td><input type="text" name="rbfw_bike_car_sd_data_duration[2][short_desc]" value="" step=".01" placeholder="Short Description"></td>
    <td><input type="number" name="rbfw_bike_car_sd_data_duration[2][price]" step=".01" value="" placeholder="Price"></td>
    <td class="rbfw_without_time_inventory <?php echo ($manage_inventory_as_timely=='on')?'rbfw_hide':'' ?>"><input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" value="0" placeholder="Stock Quantity"></td>
    <td class="rbfw_time_inventory <?php echo ($manage_inventory_as_timely=='off')?'rbfw_hide':'' ?>"><input class="medium" type="number" name="rbfw_bike_car_sd_data_duration[2][qty]" value="" placeholder="Duration"></td>
    <td class="rbfw_time_inventory <?php echo ($manage_inventory_as_timely=='off')?'rbfw_hide':''  ?>"><select name="rbfw_bike_car_sd_data_duration[2][d_type]"><option>Hours</option><option>Days</option><option>Weeks</option></select></td>
    <td>
        <div class="mp_event_remove_move"><button class="button remove-row 1731030574387"><i class="fa-solid fa-trash-can"></i></button>
            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
        </div>
    </td>
</tr>