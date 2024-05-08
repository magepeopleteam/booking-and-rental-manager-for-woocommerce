<?php     $rbfw_enable_hourly_rate = get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) : 'no'; ?>
<?php $rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'yes';?>
<?php $rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';?>
<?php $rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd']; ?>
<?php $mdedo = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment')?'block':'none'; ?>
<?php $mdedo_eekday = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment' && $rbfw_enable_daywise_price=='yes')?'block':'none'; ?>
<?php $rbfw_enable_daywise_price  = get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) : 'no'; ?>
<?php 
$rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
$rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
$rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
$rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
$rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';
?>
<div class='rbfw-item-type '>
    <div class="rbfw_form_group" data-table="rbfw_item_type_table">
        <table class="form-table rbfw_item_type_table">
            <tr class="rbfw_enable_start_end_date_switch_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort') { echo 'style="display:none"'; } ?>>
                <td>
                    <section class="component d-flex justify-content-between align-items-center mb-2" >
                        <label class="w-50">
                            <?php esc_html_e( 'Start & End Date/Time:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span></span></i>
                        </label>
                        <div class="d-flex justify-content-end w-50 ">
                            <div class="rbfw_switch rbfw_switch_return_date">
                                <label for="rbfw_enable_start_end_date_on" data-value="on" class="rbfw_enable_start_end_date_label <?php if ( $rbfw_enable_start_end_date == 'yes' ) { echo 'active'; } ?>">
                                    <input type="radio" name="rbfw_enable_start_end_date" class="rbfw_enable_start_end_date" value="yes" id="rbfw_enable_start_end_date_on" <?php if ( $rbfw_enable_start_end_date == 'yes' ) { echo 'Checked'; } ?>>
                                    <span>Regular Date</span>
                                </label>
                                <label data-value="off" for="rbfw_enable_start_end_date_off" class="rbfw_enable_start_end_date_label <?php if ( $rbfw_enable_start_end_date != 'yes' ) { echo 'active'; } ?> off">
                                    <input type="radio" name="rbfw_enable_start_end_date" class="rbfw_enable_start_end_date" value="no" id="rbfw_enable_start_end_date_off" <?php if ( $rbfw_enable_start_end_date != 'yes' ) { echo 'Checked'; } ?>>
                                    <span>Fixed Date</span>
                                </label>
                            </div>
                        </div>
                    </section>
                </td>
            </tr>

            <tr class="rbfw_enable_start_end_date_field_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_enable_start_end_date == 'yes') { echo 'style="display:none"'; } ?>>
                <td>
                    <section class="component d-flex justify-content-between mb-2">
                        <div class="w-50 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class=" d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_start_date" id="rbfw_event_start_date" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                            </div>
                        </div>
                        <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'Start Time:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class=" d-flex justify-content-between align-items-center">
                                <input type="time" name="rbfw_event_start_time" id="rbfw_event_start_time" value="<?php echo esc_attr( $rbfw_event_start_time ); ?>">

                            </div>
                        </div>
                    </section>

                    <section class="component d-flex justify-content-between mb-2">
                        <div class="w-50 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class=" d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_end_date" id="rbfw_event_end_date" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                            </div>
                        </div>
                        <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'End Time:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class=" d-flex justify-content-between align-items-center">
                                <input type="time" name="rbfw_event_end_time" id="rbfw_event_end_time" value="<?php echo esc_attr( $rbfw_event_end_time ); ?>">
                            </div>
                        </div>
                    </section>
                </td>
            </tr>

            <tr class="rbfw_switch_md_type_item_qty rbfw_item_stock_quantity_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' || $rbfw_enable_variations == 'yes') { echo 'style="display:none"'; } ?>>
                <td>
                    <section class="component d-flex justify-content-between align-items-center mb-2" >
                        <label class="w-50">
                            <?php esc_html_e( 'Stock Quantity:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span></span></i>
                        </label>
                        <div class="d-flex justify-content-end w-50 ">
                            <input type="number" name="rbfw_item_stock_quantity" id="rbfw_item_stock_quantity" value="<?php echo esc_attr($rbfw_item_stock_quantity); ?>">
                        </div>
                    </section>
                </td>
            </tr>
            <?php echo do_action('rbfw_after_rent_item_type_table_row'); ?>
        </table>
    </div>
</div>

<div class="rbfw_general_price_config_wrapper" style="display:<?php echo $mdedo ?> ">
    <h2 class="h5 text-white bg-primary mb-1 rounded-top">
        <?php echo esc_html_e( 'Category service price', 'booking-and-rental-manager-for-woocommerce' ); ?>
    </h2>
    <?php
    $options = array(
        'id'          => 'rbfw_service_category_price',
        'type'        => 'md_service_category_price',
        'placeholder'        => 'Service Name',
    );
    $option_value         = get_post_meta($post_id, $options['id'], true);
    $options['value']      = is_serialized($option_value) ? unserialize($option_value) : $option_value;
    echo rbfw_field_generator( 'md_service_category_price', $options );
    ?>
</div>

<br>

<div class="rbfw_general_price_config_wrapper" style="display: <?php echo $mdedo ?>;">
    <?php do_action( 'rbfw_before_general_price_table' ); ?>
    <div class="">
        <h2 class="h5 text-white bg-primary mb-1 rounded-top">
            <?php echo esc_html_e( 'General Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
        </h2>
        <?php do_action( 'rbfw_before_general_price_table_row' ); ?>

        <section class="component d-flex justify-content-between align-items-center mb-2">
            <div class="w-50 d-flex justify-content-between align-items-center">
                <label for=""><?php esc_html_e( 'Daily Price:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
            </div>
            <div class="w-50 d-flex justify-content-end align-items-center">
                <div class="rbfw_switch rbfw_switch_daily_rate">
                    <label for="rbfw_enable_daily_rate_on" class="<?php if ( $rbfw_enable_daily_rate == 'yes' ) { echo 'active'; } ?>">
                        <input type="radio" name="rbfw_enable_daily_rate" class="rbfw_enable_daily_rate" value="yes" id="rbfw_enable_daily_rate_on" <?php if ( $rbfw_enable_daily_rate == 'yes' ) { echo 'Checked'; } ?>>
                        <span>On</span>
                    </label>
                    <label for="rbfw_enable_daily_rate_off" class="<?php if ( $rbfw_enable_daily_rate != 'yes' ) { echo 'active'; } ?> off">
                        <input type="radio" name="rbfw_enable_daily_rate" class="rbfw_enable_daily_rate" value="no" id="rbfw_enable_daily_rate_off" <?php if ( $rbfw_enable_daily_rate != 'yes' ) { echo 'Checked'; } ?>>
                        <span>Off</span>
                    </label>
                </div>
                <div class="rbfw_daily_rate_input ms-2 <?php if ( $rbfw_enable_daily_rate == 'no' ) { echo 'rbfw_d_none'; } ?>">
                    <input type="number" name='rbfw_daily_rate' value="<?php echo esc_html( $daily_rate ); ?>" placeholder="<?php esc_html_e( 'Daily Price', '' ); ?>" class=" ">
                </div>
            </div>
        </section>

        <section class="component d-flex justify-content-between align-items-center mb-2">
            <div class="w-50 d-flex justify-content-between align-items-center">
                <label for=""><?php esc_html_e( 'Hourly Price:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php _e( 'Enable/Disable the time slot functionality.To add time slot, go to <a class="rbfw_open_date_time_tab">Date & Time Tab</a>', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i></label>
            </div>
            <div class="w-50 ms-5 d-flex justify-content-end align-items-center">
                <div class="rbfw_switch rbfw_switch_hourly_rate">
                    <label for="rbfw_enable_hourly_rate_on" class="<?php if ( $rbfw_enable_hourly_rate == 'yes' ) { echo 'active'; } ?>">
                        <input type="radio" name="rbfw_enable_hourly_rate" class="rbfw_enable_hourly_rate" value="yes" id="rbfw_enable_hourly_rate_on" <?php if ( $rbfw_enable_hourly_rate == 'yes' ) { echo 'Checked'; } ?>>
                        <span>On</span>
                    </label>
                    <label for="rbfw_enable_hourly_rate_off" class="<?php if ( $rbfw_enable_hourly_rate != 'yes' ) { echo 'active'; } ?> off">
                        <input type="radio" name="rbfw_enable_hourly_rate" class="rbfw_enable_hourly_rate" value="no" id="rbfw_enable_hourly_rate_off" <?php if ( $rbfw_enable_hourly_rate != 'yes' ) { echo 'Checked'; } ?>>
                        <span>Off</span>
                    </label>
                </div>
                <div  class="ms-2 <?php if ( $rbfw_enable_hourly_rate == 'no' ) { echo 'rbfw_d_none'; } ?> rbfw_hourly_rate_input">
                    <input type="number" name='rbfw_hourly_rate' value="<?php echo esc_html( $hourly_rate ); ?>" placeholder="<?php esc_html_e( 'Hourly Price', '' ); ?>" class="<?php if ( $rbfw_enable_hourly_rate == 'no' ) { echo 'rbfw_d_none'; } ?> rbfw_hourly_rate_input">
                </div>
            </div>
        </section>

        <section class="component d-flex justify-content-between align-items-center mb-2">
            <div class="w-50 d-flex justify-content-between align-items-center">
                <label for="">
                    <?php esc_html_e( 'Day-wise Price Configuration:', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    <i class="fas fa-question-circle tool-tips"></i>
                </label>
            </div>
            <div class="w-50 ms-5 d-flex justify-content-end align-items-center">
                <div class="rbfw_switch_wrapper rbfw_switch_daywise_price">
                    <div class="rbfw_switch">
                        <label for="rbfw_enable_daywise_price_on" class="<?php if ( $rbfw_enable_daywise_price == 'yes' ) { echo 'active'; } ?>">
                            <input type="radio" name="rbfw_enable_daywise_price" class="rbfw_enable_daywise_price" value="yes" id="rbfw_enable_daywise_price_on" <?php if ( $rbfw_enable_daywise_price == 'yes' ) { echo 'Checked'; } ?>>
                            <span>On</span>
                        </label>
                        <label for="rbfw_enable_daywise_price_off" class="<?php if ( $rbfw_enable_daywise_price != 'yes' ) { echo 'active'; } ?> off">
                            <input type="radio" name="rbfw_enable_daywise_price" class="rbfw_enable_daywise_price" value="no" id="rbfw_enable_daywise_price_off" <?php if ( $rbfw_enable_daywise_price != 'yes' ) { echo 'Checked'; } ?>>
                            <span>Off</span>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <div class="day-wise-price-configuration">


            <div class="rbfw_week_table" style="display: <?php echo $mdedo_eekday ?>">
                <h2 class="h5 text-white bg-primary mb-1 rounded-top">
                    <?php echo esc_html_e( 'Day-wise Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </h2>
                <section class="component d-flex justify-content-between align-items-center mb-2">
                    <table class='form-table'>
                        <?php do_action( 'rbfw_before_week_price_table_row' ); ?>
                        <thead>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Day Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th scope="row"><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th scope="row"><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th scope="row"><?php esc_html_e( 'Enable/Disable', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $this->rbfw_day_row( __( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
                        $this->rbfw_day_row( __( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
                        $this->rbfw_day_row( __( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
                        $this->rbfw_day_row( __( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
                        $this->rbfw_day_row( __( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
                        $this->rbfw_day_row( __( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
                        $this->rbfw_day_row( __( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );
                        do_action( 'rbfw_after_week_price_table_row' );
                        ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>

        <?php do_action( 'rbfw_after_general_price_table_row' ); ?>
    </div>
    <?php do_action( 'rbfw_after_general_price_table' ); ?>
</div>


