<?php
global $rbfw;


check_ajax_referer( 'rbfw_room_types_with_sd_price_action', 'nonce' );

if(isset($_POST['post_id'])){
    $post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
    $rbfw_sd_types = isset($_POST['rbfw_sd_types']) ? sanitize_text_field(wp_unslash($_POST['rbfw_sd_types'])) : '';


    $rbfw_bike_car_sd_data_sp           = get_post_meta( $post_id, 'rbfw_bike_car_sd_data_sp', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data_sp', true ) : [];
    $rbfw_bike_car_sd_data           = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];


    $roomTypes  = json_decode($rbfw_sd_types);

    $filteredRooms = array_filter($rbfw_bike_car_sd_data, function ($room) use ($roomTypes) {
        return in_array($room['room_type'], $roomTypes);
    });


    $filteredRooms = array_values($filteredRooms);


    $existingTypes = array_column($filteredRooms, 'rent_type');

    foreach ($roomTypes as $type) {
        if (!in_array($type, $existingTypes)) {
            $filteredRooms[] = [
                "rent_type" => $type,
                "rbfw_room_image" => "",
                "rbfw_room_daylong_rate" => "",
                "rbfw_room_daynight_rate" => "",
                "rbfw_room_desc" => "",
                "rbfw_room_available_qty" => ""
            ];
        }
    }

    $rbfw_bike_car_sd_data = $filteredRooms;



    ?>
    <section>
        <div class="w-100">
            <div class="rbfw_item_insert_sd ">
                <?php

                foreach ($rbfw_bike_car_sd_data_sp as $key=>$single_item){
                    if(isset($single_item['start_date']) && isset($single_item['end_date'])){
                        ?>
                        <section class="bg-light" style="border: 1px solid #f0f0f0; margin: 10px">
                            <div class="w-100 me-5">
                                <div class=" d-flex justify-content-between mb-2">
                                    <div class="w-50 d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'Start Date', 'rbfw-sp' ); ?></label>
                                        <div class=" d-flex justify-content-between align-items-center">
                                            <input class="formControl date_type" name="rbfw_bike_car_sd_data_sp[<?php echo $key ?>][start_date]" value="<?php echo isset($single_item['start_date'])?$single_item['start_date']:'' ?>"  placeholder="<?php echo current_time( 'Y-m-d' ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'End Date', 'rbfw-sp' ); ?></label>
                                        <div class=" d-flex justify-content-between align-items-center">
                                            <input class="formControl date_type" name="rbfw_bike_car_sd_data_sp[<?php echo $key ?>][end_date]" value="<?php echo isset($single_item['end_date'])?$single_item['end_date']:'' ?>"  placeholder="<?php echo current_time( 'Y-m-d' ); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <table class='form-table rbfw_bike_car_sd_price_table_sp' id="sp-row-<?php echo $key ?>">
                                        <thead>
                                        <tr>
                                            <th>
                                                <?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                            </th>
                                            <th>
                                                <?php echo wp_kses( sprintf( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody class="rbfw_bike_car_sd_price_table_body">
                                        <?php

                                        foreach ( $rbfw_bike_car_sd_data as $key_1=>$value ){  ?>

                                            <tr class="rbfw_bike_car_sd_price_table_row">
                                                <td>
                                                    <input class="medium"  type="text" name="rbfw_bike_car_sd_data_sp[<?php echo $key ?>][type_price][<?php echo $key_1 ?>][type]"  value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data_sp[<?php echo $key ?>][type_price][<?php echo $key_1 ?>][price]" step=".01" value="<?php echo esc_attr( $single_item['type_price'][$key_1]['price'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                            </tr>

                                        <?php } ?>


                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <span class="button " onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>
                        </section>
                    <?php } } ?>

            </div>
            <p>
                <span class="ppof-button mp_add_item_sessional_sd">
                    <i class="fa-solid fa-circle-plus"></i>&nbsp;
                    <?php esc_html_e( 'Add New Seasonal Pricing', 'rbfw-sp' ); ?>
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
                                    <input class="formControl sp_start_date date_type" placeholder="<?php echo current_time( 'Y-m-d' ); ?>"/>
                                </div>
                            </div>
                            <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                                <label for=""><?php esc_html_e( 'End Date', 'rbfw-sp' ); ?></label>
                                <div class=" d-flex justify-content-between align-items-center">
                                    <input class="formControl sp_end_date date_type"   placeholder="<?php echo current_time( 'Y-m-d' ); ?>"/>
                                </div>
                            </div>
                        </div>
                        <div>
                            <table class='form-table rbfw_bike_car_sd_price_table_sp'>
                                <thead>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    </th>
                                    <th>
                                        <?php echo wp_kses( sprintf( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                    </th>
                                </tr>
                                </thead>

                                <tbody class="rbfw_bike_car_sd_price_table_body" id="sp-price">

                                <?php foreach ( $rbfw_bike_car_sd_data as $key_1=>$value ){  ?>

                                    <tr class="rbfw_bike_car_sd_price_table_row">
                                        <td>
                                            <input  class="medium rent_type_<?php echo $key_1 ?>" type="text" name="rbfw_bike_car_sd_data_sp[][<?php echo $key_1 ?>][type]"  value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                        </td>
                                        <td>
                                            <input  class="medium price_<?php echo $key_1 ?>" type="number" name="rbfw_bike_car_sd_data_sp[][<?php echo $key_1 ?>][price]" step=".01"  placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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