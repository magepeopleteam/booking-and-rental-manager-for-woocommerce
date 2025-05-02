<?php
global $rbfw;

if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
    return;
}
if(isset($_POST['post_id'])){

    $post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
    $rbfw_room_types = isset($_POST['rbfw_room_types']) ? sanitize_text_field(wp_unslash($_POST['rbfw_room_types'])) : '';
    $rbfw_resort_data_mds          = get_post_meta( $post_id, 'rbfw_resort_data_mds', true ) ? get_post_meta( $post_id, 'rbfw_resort_data_mds', true ) : [];
    $rbfw_resort_data           = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
    $rbfw_enable_resort_daylong_price = get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) : 'no';

    $roomTypes  = json_decode($rbfw_room_types);

    $filteredRooms = array_filter($rbfw_resort_data, function ($room) use ($roomTypes) {
        return in_array($room['room_type'], $roomTypes);
    });

    $filteredRooms = array_values($filteredRooms);
    $existingTypes = array_column($filteredRooms, 'room_type');

    foreach ($roomTypes as $type) {
        if (!in_array($type, $existingTypes)) {
            $filteredRooms[] = [
                "room_type" => $type,
                "rbfw_room_image" => "",
                "rbfw_room_daylong_rate" => "",
                "rbfw_room_daynight_rate" => "",
                "rbfw_room_desc" => "",
                "rbfw_room_available_qty" => ""
            ];
        }
    }
    $rbfw_resort_data = $filteredRooms;


    ?>
    <section>
        <div class="w-100">
            <div class="rbfw_item_insert_resort_mds">
                <?php
                $i = 0;
                foreach ($rbfw_resort_data_mds as $key=>$single_item){
                    if(isset($single_item['start_day']) && isset($single_item['end_day'])){
                        ?>
                        <section class="bg-light" style="border: 1px solid #f0f0f0; margin: 10px">
                            <div class="w-100 me-5">
                                <div class=" d-flex justify-content-between mb-2">
                                    <div class="w-50 d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'Start Date', 'rbfw-sp' ); ?></label>
                                        <div class=" d-flex justify-content-between align-items-center">
                                            <input class="formControl" name="rbfw_resort_data_mds[<?php echo $i ?>][start_day]" value="<?php echo isset($single_item['start_day'])?$single_item['start_day']:'' ?>"/>
                                        </div>
                                    </div>
                                    <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'End Date', 'rbfw-sp' ); ?></label>
                                        <div class=" d-flex justify-content-between align-items-center">
                                            <input class="formControl" name="rbfw_resort_data_mds[<?php echo $i ?>][end_day]" value="<?php echo isset($single_item['end_day'])?$single_item['end_day']:'' ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <table class='form-table rbfw_resort_price_table_sp' id="sp-row-<?php echo $i ?>">
                                        <thead>
                                        <tr>
                                            <th>
                                                <?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                            </th>
                                            <th class="resort_day_long_price" style="display:<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'table-cell' : 'none' ); ?>">
                                                <?php echo wp_kses( sprintf( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                            </th>
                                            <th>
                                                <?php echo wp_kses( sprintf( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody class="rbfw_resort_price_table_body">

                                        <?php
                                        foreach ( $rbfw_resort_data as $key_1=>$value ){ ?>
                                            <tr class="rbfw_resort_price_table_row">
                                                <td>
                                                    <input class="medium" type="text" name="rbfw_resort_data_mds[<?php echo $i ?>][room_price][<?php echo $key_1 ?>][room_type]"  value="<?php echo esc_attr( $value['room_type'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="resort_day_long_price" style="display:<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'table-cell' : 'none' ); ?>">
                                                    <input class="medium" type="number" name="rbfw_resort_data_mds[<?php echo $i ?>][room_price][<?php echo $key_1 ?>][day_long_price]" step=".01" value="<?php echo esc_attr( $single_item['room_price'][$key_1]['day_long_price'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input class="medium" type="number" name="rbfw_resort_data_mds[<?php echo $i ?>][room_price][<?php echo $key_1 ?>][price]" step=".01" value="<?php echo esc_attr( $single_item['room_price'][$key_1]['price'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <span class="button " onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>
                        </section>
                    <?php } $i++; } ?>

            </div>
            <p>
                <span class="ppof-button rbfw_add_item_mds_resort">
                    <i class="fa-solid fa-circle-plus"></i>&nbsp;
                    <?php esc_html_e( 'Add New Multi Day Saver', 'rbfw-sp' ); ?>
                </span>
            </p>
        </div>

        <div class="mp_hidden_content">
            <div class="mp_hidden_item">

                <section class="bg-light" style="border: 1px solid #f0f0f0; margin: 10px">
                    <div class="w-100 me-5">
                        <div class=" d-flex justify-content-between mb-2">
                            <div class="w-50 d-flex justify-content-between align-items-center">
                                <label for=""><?php esc_html_e( 'Start Date', 'rbfw-sp' ); ?></label>
                                <div class=" d-flex justify-content-between align-items-center">
                                    <input class="formControl sp_start_date"/>
                                </div>
                            </div>
                            <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                                <label for=""><?php esc_html_e( 'End Date', 'rbfw-sp' ); ?></label>
                                <div class=" d-flex justify-content-between align-items-center">
                                    <input class="formControl sp_end_date"/>
                                </div>
                            </div>
                        </div>
                        <div>
                            <table class='form-table rbfw_resort_price_table_sp rbfw_hidden_sp'>
                                <thead>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    </th>
                                    <th class="resort_day_long_price" style="display:<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'table-cell' : 'none' ); ?>">
                                        <?php echo wp_kses( sprintf( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                    </th>
                                    <th>
                                        <?php echo wp_kses( sprintf( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="rbfw_resort_price_table_body" id="sp-price">


                                <?php foreach ( $rbfw_resort_data as $key_1=>$value ){  ?>

                                    <tr class="rbfw_resort_price_table_row">
                                        <td>
                                            <input class="medium room_type_<?php echo $key_1 ?>" type="text" name="rbfw_resort_data_mds[][<?php echo $key_1 ?>][room_type]"  value="<?php echo esc_attr( $value['room_type'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                        </td>
                                        <td class="resort_day_long_price" style="display:<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'table-cell' : 'none' ); ?>">
                                            <input class="medium day_long_price_<?php echo $key_1 ?>" type="number" name="rbfw_resort_data_mds[][<?php echo $key_1 ?>][day_long_price]" step=".01" value="" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                        </td>
                                        <td>
                                            <input class="medium price_<?php echo $key_1 ?>" type="number" name="rbfw_resort_data_mds[][<?php echo $key_1 ?>][price]" step=".01" value="" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                        </td>
                                    </tr>

                                <?php } ?>

                                </tbody>
                            </table>


                        </div>
                    </div>
                    <span class="button " onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>
                </section>
            </div>
        </div>
    </section>
<?php } ?>