<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
$rbfw_id = $post_id ??0;
global $frontend;
global $submit_name;
$frontend = $frontend??0;
$cart_backend = $cart_backend??'';

if($cart_backend){
    $submit_name = 'admin-purchase';
}else{
    $submit_name = 'add-to-cart';
}

$rbfw_monthly_rate = get_post_meta($rbfw_id, 'rbfw_monthly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_monthly_rate', true) : 0;
$rbfw_weekly_rate = get_post_meta($rbfw_id, 'rbfw_weekly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_weekly_rate', true) : 0;
$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;

$rbfw_enable_monthly_rate           = get_post_meta( $rbfw_id, 'rbfw_enable_monthly_rate', true ) ;
$rbfw_enable_weekly_rate           = get_post_meta( $rbfw_id, 'rbfw_enable_weekly_rate', true );
$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'no';
$rbfw_enable_daywise_price = get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) : 'no';

//$availabe_time = rbfw_get_available_times($rbfw_id);
$availabe_time = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];

$off_dates_list = get_post_meta($rbfw_id, 'rbfw_off_dates', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_off_dates', true)) : [];

$location_switch = !empty(get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true)) ? get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true) : '';
$pickup_location = get_post_meta($rbfw_id, 'rbfw_pickup_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_pickup_data', true)) : [];
$dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

$extra_service_list = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];

$enable_service_price =  get_post_meta($post_id, 'rbfw_enable_category_service_price', true) ? get_post_meta($post_id, 'rbfw_enable_category_service_price', true) : 'off';

$current_day = date_i18n('D');

$current_date = date_i18n('Y-m-d');

global $rbfw;


$rbfw_enable_md_type_item_qty = get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) ? get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) : 'no';

//echo $rbfw_enable_md_type_item_qty;exit;

$rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
$rbfw_variations_data = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];

//echo '<pre>';print_r($rbfw_variations_data);echo '<pre>';exit;

$input_stock_quantity = '';
if($rbfw_enable_variations == 'yes'){
    $item_stock_quantity = rbfw_get_variations_stock($rbfw_id);
} else {
    $item_stock_quantity = !empty(get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true)) ? get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true) : 0;
    if(empty($item_stock_quantity)){
        $input_stock_quantity = 'no_has_value';
    }
}


$rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';
$rbfw_event_start_date  = get_post_meta( $rbfw_id, 'rbfw_event_start_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_start_date', true ) : '';
$rbfw_event_start_time  = get_post_meta( $rbfw_id, 'rbfw_event_start_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_start_time', true ) : '';
$rbfw_event_start_time  = gmdate('h:i a', strtotime($rbfw_event_start_time));
$rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
$rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
$rbfw_event_end_time  = gmdate('h:i a', strtotime($rbfw_event_end_time));
$rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
$rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));
$referal_page = '';
$rbfw_enable_time_picker = get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) ? get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) : 'no';
if(isset($_GET['rbfw_start_date']) && $_GET['rbfw_start_date'] && isset($_GET['rbfw_end_date']) && $_GET['rbfw_end_date']){
    $rbfw_enable_time_picker = 'no';
    $referal_page = 'search';
}
$expire = 'no';
if($rbfw_enable_start_end_date=='no'){
    if($rbfw_event_last_date<$rbfw_todays_date){
        $expire = 'yes';
    }
}
$available_qty_info_switch = get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) : 'no';

$rbfw_enable_security_deposit = get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) ? get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) : 'no';
$rbfw_security_deposit_type = get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) : 'percentage';
$rbfw_security_deposit_amount = get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) : 0;


?>
<?php if($expire == 'yes'){ ?>
    <h3><?php esc_html_e( 'Date Expired !', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
    <?php die;  ?>
<?php } ?>

<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">
    <div class="rbfw-single-right-container">
        <form action="" method='post' class="mp_rbfw_ticket_form">
            <div class="rbfw_bike_car_md_item_wrapper">
                <div class="rbfw_bike_car_md_item_wrapper_inner">
                    <?php do_action('rbfw_discount_ad', $rbfw_id); ?>
                    <div class="item pricing-content-collapse">
                        <div class="item-content pricing-content">
                            <div class="section-header">
                                <div class="rbfw-single-right-heading rbfw_pricing_info_heading">
                                    <?php esc_html_e('Pricing Info', 'booking-and-rental-manager-for-woocommerce'); ?>
                                </div>
                            </div>
                            <?php $rbfw_pricing_info_display = rbfw_get_option('rbfw_pricing_info_display', 'rbfw_basic_gen_settings'); ?>
                        </div>

                        <div class="price-item-container pricing-content_dh  mpStyle  <?php echo ($rbfw_pricing_info_display=='yes')?'open':'' ?>" style="display: <?php echo ($rbfw_pricing_info_display=='yes')?'block':'none' ?>">
                            <?php if($rbfw_enable_monthly_rate=='yes'){ ?>
                                <div class="rbfw_day_wise_price">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Monthly Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                            <td><?php echo wp_kses_post(wc_price($rbfw_monthly_rate)); ?> / <?php esc_html_e('Month', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                        </tr>
                                        <?php if($rbfw_enable_weekly_rate=='yes'){ ?>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Weekly Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                            <td><?php echo wp_kses_post(wc_price($rbfw_weekly_rate)); ?> / <?php esc_html_e('week', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if ($enable_daily_rate == 'yes') { ?>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Daily Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                            <td><?php echo wp_kses_post(wc_price($daily_rate)); ?> / <?php esc_html_e('Day', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if ($enable_hourly_rate == 'yes') { ?>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Hourly rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                            <td><?php echo wp_kses_post(wc_price($hourly_rate)); ?> / <?php esc_html_e('Hour', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                        </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php }elseif ($rbfw_enable_weekly_rate=='yes'){ ?>

                                <div class="rbfw_day_wise_price">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td><strong><?php esc_html_e( 'Weekly Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                            <td><?php echo wp_kses_post(wc_price($rbfw_weekly_rate)); ?> / <?php esc_html_e('week', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                        </tr>
                                        <?php if ($enable_daily_rate == 'yes') { ?>
                                            <tr>
                                                <td><strong><?php esc_html_e( 'Daily Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                                <td><?php echo wp_kses_post(wc_price($daily_rate)); ?> / <?php esc_html_e('Day', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($enable_hourly_rate == 'yes') { ?>
                                            <tr>
                                                <td><strong><?php esc_html_e( 'Hourly rate', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                                <td>rbfw_translation.currency +rbfw_translation.currency + / <?php esc_html_e('Hour', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php }else{
                                if($rbfw_enable_daywise_price == 'yes'){
                                    $sunday = rbfw_day_row_md( __( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
                                    $monday = rbfw_day_row_md( __( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
                                    $tueday = rbfw_day_row_md( __( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
                                    $wedday = rbfw_day_row_md( __( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
                                    $thuday = rbfw_day_row_md( __( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
                                    $friday = rbfw_day_row_md( __( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
                                    $satday = rbfw_day_row_md( __( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );
                                    ?>
                                    <div class="rbfw_day_wise_price">
                                        <table>
                                            <tr>
                                                <th><?php esc_html_e( 'Rate', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'S', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'M', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'T', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'W', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'T', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'F', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                <th><?php esc_html_e( 'S', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                            </tr>
                                            <?php if ($enable_daily_rate == 'yes') { ?>
                                                <tr>
                                                    <td><?php echo esc_html($rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily','booking-and-rental-manager-for-woocommerce'))); ?>(<?php echo esc_html(get_woocommerce_currency_symbol()); ?>)</td>
                                                    <td><?php echo esc_html(($sunday['enable'] =='yes' && $sunday['daily_rate'])? $sunday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($monday['enable'] =='yes' && $monday['daily_rate'])? $monday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($tueday['enable'] =='yes' && $tueday['daily_rate'])? $tueday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($wedday['enable'] =='yes' && $wedday['daily_rate'])? $wedday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($thuday['enable'] =='yes' && $thuday['daily_rate'])? $thuday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($friday['enable'] =='yes' && $friday['daily_rate'])? $friday['daily_rate'] :$daily_rate); ?></td>
                                                    <td><?php echo esc_html(($satday['enable'] =='yes' && $satday['daily_rate'])? $satday['daily_rate'] :$daily_rate); ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if ($enable_hourly_rate == 'yes') { ?>
                                                <tr>
                                                    <td><?php echo esc_html($rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly','booking-and-rental-manager-for-woocommerce'))); ?>(<?php echo esc_html(get_woocommerce_currency_symbol()); ?>)</td>
                                                    <td><?php echo esc_html(($sunday['enable'] =='yes' && $sunday['hourly_rate'])? $sunday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($monday['enable'] =='yes' && $monday['hourly_rate'])? $monday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($tueday['enable'] =='yes' && $tueday['hourly_rate'])? $tueday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($wedday['enable'] =='yes' && $wedday['hourly_rate'])? $wedday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($thuday['enable'] =='yes' && $thuday['hourly_rate'])? $thuday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($friday['enable'] =='yes' && $friday['hourly_rate'])? $friday['hourly_rate'] :$hourly_rate); ?></td>
                                                    <td><?php echo esc_html(($satday['enable'] =='yes' && $satday['hourly_rate'])? $satday['hourly_rate'] :$hourly_rate); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                <?php }else{ ?>
                                    <div class="rbfw_day_wise_price">
                                        <table>
                                            <tbody>
                                            <?php if ($enable_daily_rate == 'yes') { ?>
                                                <tr>
                                                    <td><strong><?php esc_html_e('Daily Rate', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                                    <td><?php echo wp_kses_post(wc_price($daily_rate)); ?> / <?php esc_html_e('day', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if ($rbfw_enable_time_picker == 'yes') { ?>
                                                <tr>
                                                    <td><strong><?php esc_html_e('Hourly Rate', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                                    <td><?php echo wp_kses_post(wc_price($hourly_rate)); ?> / <?php esc_html_e('hour', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <?php
                                $rbfw_md_data_mds = get_post_meta( $post_id, 'rbfw_md_data_mds', true ) ? get_post_meta( $post_id, 'rbfw_md_data_mds', true ) : [];
                                if (is_plugin_active('multi-day-price-saver-addon-for-wprently/additional-day-price.php') && (!(empty($rbfw_md_data_mds)))) {
                                    foreach ($rbfw_md_data_mds as $item){
                                        ?>
                                        <div class="mp_item_insert ">
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td <?php echo ($rbfw_enable_time_picker == 'yes')?'colspan="2"':'' ?>>Over <strong><?php echo esc_html($item['rbfw_start_day']) ?></strong> Days </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong><?php esc_html_e( 'Daily Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><?php echo wc_price($item['rbfw_daily_price']) ?>
                                                    </td>
                                                    <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                                    <td>
                                                        <strong><?php esc_html_e( 'Hourly Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><?php echo wc_price($item['rbfw_hourly_price']) ?>
                                                    </td>
                                                    <?php } ?>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                <?php }else{
                                    $seasonal_prices = [];
                                    if (is_plugin_active('booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php')){
                                        $seasonal_prices = get_post_meta( $post_id, 'rbfw_seasonal_prices', true ) ? get_post_meta( $post_id, 'rbfw_seasonal_prices', true ) : [];
                                    }
                                    if(!empty($seasonal_prices)){
                                        ?>
                                        <div class="mp_settings_area mpStyle rbfw_seasonal_price_config_wrapper rbfw_seasonal_price_info">
                                            <section>
                                                <div class="w-100">
                                                    <div class="mp_item_insert ">
                                                        <table>
                                                            <?php
                                                            if ( sizeof( $seasonal_prices ) > 0 ) {
                                                                foreach ( $seasonal_prices as $sp ) {
                                                                    $start_date = array_key_exists( 'rbfw_sp_start_date', $sp ) ? $sp['rbfw_sp_start_date'] : '';
                                                                    $end_date   = array_key_exists( 'rbfw_sp_end_date', $sp ) ? $sp['rbfw_sp_end_date'] : '';
                                                                    $sp_price_h = array_key_exists( 'rbfw_sp_price_h', $sp ) ? $sp['rbfw_sp_price_h'] : '0';
                                                                    $sp_price_d = array_key_exists( 'rbfw_sp_price_d', $sp ) ? $sp['rbfw_sp_price_d'] : '0';
                                                                    ?>
                                                                    <tr>
                                                                        <td <?php echo ($rbfw_enable_time_picker == 'yes')?'colspan="2"':'' ?>><?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong><?php echo esc_html( rbfw_date_format($start_date) ); ?></strong> <?php esc_html_e( 'To', 'booking-and-rental-manager-for-woocommerce' ); ?>  <strong><?php echo esc_html( rbfw_date_format($end_date) ); ?></strong> </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong><?php esc_html_e( 'Daily Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong> <?php echo  wp_kses(wc_price($sp_price_d) , rbfw_allowed_html()); ?></td>
                                                                        <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                                                            <td><strong><?php esc_html_e( 'Hourly Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>  <?php echo  wp_kses(wc_price($sp_price_h) , rbfw_allowed_html()); ?></td>
                                                                        <?php } ?>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </table>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="pickup_date "></div>

                    <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                        <div class="item">
                            <div class="rbfw-single-right-heading"><?php esc_html_e('Pickup Location','booking-and-rental-manager-for-woocommerce'); ?></div>
                            <div class="item-content rbfw-location">
                                <select class="rbfw-select" name="rbfw_pickup_point" required>
                                    <option value=""><?php esc_html_e('Choose pickup location','booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <?php foreach ($pickup_location as $pickup) : ?>
                                        <option value="<?php echo esc_attr($pickup['loc_pickup_name']); ?>"><?php echo esc_html($pickup['loc_pickup_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($location_switch == 'yes' && !empty($dropoff_location)) : ?>
                        <div class="item">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Drop-off Location','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-location">
                                <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                    <option value=""><?php esc_html_e('Choose drop-off location','booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <?php foreach ($dropoff_location as $dropoff) : ?>
                                        <option value="<?php echo esc_attr($dropoff['loc_dropoff_name']); ?>"><?php echo esc_html($dropoff['loc_dropoff_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>


                    <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_attr(rbfw_off_days($post_id)); ?>'>
                    <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo esc_attr(rbfw_off_dates($post_id)); ?>'>

                    <?php if($rbfw_enable_start_end_date == 'yes'){ ?>
                        <div class="rbfw-multiple-date-time">
                            <div class="item">
                                <div class="item-content rbfw-datetime">
                                    <div class="<?php echo esc_attr(($rbfw_enable_time_picker == 'yes')?'left':''); ?> date">
                                        <div class="rbfw-single-right-heading">
                                            <?php esc_html_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>
                                        </div>
                                        <div class="rbfw-p-relative">
                                            <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                            <?php if($referal_page == 'search'){ ?>
                                                <input type="hidden" id="hidden_pickup_date" value="<?php echo $_GET['rbfw_start_date']  ?>" name="rbfw_pickup_start_date">
                                                <input class="rbfw-input rbfw-time-price pickup_date" type="text" value="<?php echo rbfw_date_format($_GET['rbfw_start_date'])  ?>"  id="pickup_date" placeholder="<?php esc_attr_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                            <?php }else{ ?>
                                                <input type="hidden" id="hidden_pickup_date" name="rbfw_pickup_start_date">
                                                <input class="rbfw-input rbfw-time-price pickup_date" type="text"  id="pickup_date" placeholder="<?php esc_attr_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                            <?php } ?>
                                            <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                        </div>
                                    </div>
                                    <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                        <div class="right time">
                                            <div class="rbfw-single-right-heading">
                                                <?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?>
                                            </div>
                                            <div class="rbfw-p-relative">
                                                <span class="clock">
                                                    <i class="fa-regular fa-clock"></i>
                                                </span>
                                                <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_pickup_start_time" id="pickup_time" required>
                                                    <option value="" disabled selected><?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                                </select>
                                                <span class="input-picker-icon"></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="item">
                                <div class="item-content rbfw-datetime">
                                    <div class="<?php echo ($rbfw_enable_time_picker == 'yes')?'left':'' ?> date">
                                        <div class="rbfw-single-right-heading"><?php esc_html_e('Return Date','booking-and-rental-manager-for-woocommerce'); ?></div>
                                        <div class="rbfw-p-relative">
                                            <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                            <?php if($referal_page == 'search'){ ?>
                                                <input type="hidden" id="hidden_dropoff_date" value="<?php echo $_GET['rbfw_end_date'] ?>" name="rbfw_pickup_end_date">
                                                <input class="rbfw-input rbfw-time-price dropoff_date" type="text" value="<?php echo rbfw_date_format($_GET['rbfw_end_date'])  ?>" id="dropoff_date" placeholder="<?php esc_attr_e('Return date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                            <?php }else{ ?>
                                                <input type="hidden" id="hidden_dropoff_date" name="rbfw_pickup_end_date">
                                                <input class="rbfw-input rbfw-time-price dropoff_date" type="text" id="dropoff_date" placeholder="<?php esc_attr_e('Return date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                            <?php } ?>
                                            <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                        </div>
                                    </div>
                                    <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                        <input name="rbfw_available_time"  id="rbfw_available_time" value="yes" type="hidden">
                                        <div class="right time">
                                            <div class="rbfw-single-right-heading"><?php esc_html_e('Return Time','booking-and-rental-manager-for-woocommerce'); ?></div>
                                            <div class="rbfw-p-relative">
                                                <span class="clock"><i class="fa-regular fa-clock"></i></span>
                                                <select class="rbfw-select rbfw-time-price dropoff_time" name="rbfw_pickup_end_time" id="dropoff_time" required>
                                                    <option value="" disabled selected><?php esc_html_e('Return time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                                </select>
                                                <span class="input-picker-icon"></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="item rbfw-duration">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Duration','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="item-content"></span>
                                <span class="item-price"></span>
                            </div>
                            
                            <input type="hidden" class="rbfw_duration_md" name="rbfw_duration_md">
                        </div>

                    <?php } else { ?>
                        <input type="hidden"  name="rbfw_pickup_start_date" id="pickup_date" value="<?php echo esc_html($rbfw_event_start_date); ?>"/>
                        <input type="hidden"  name="rbfw_pickup_start_time" id="pickup_time" value="<?php echo esc_html($rbfw_event_start_time); ?>"/>
                        <input type="hidden"  name="rbfw_pickup_end_date" id="dropoff_date" value="<?php echo esc_html($rbfw_event_end_date); ?>"/>
                        <input type="hidden"  name="rbfw_pickup_end_time" id="dropoff_time" value="<?php echo esc_html($rbfw_event_end_time); ?>"/>
                    <?php } ?>


                    <?php if ($rbfw_enable_md_type_item_qty == 'yes' && $item_stock_quantity > 0) { ?>
                        <div class="item rbfw_quantity_md" style="display: none">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Quantity','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-quantity">
                                <select class="rbfw-select" name="rbfw_item_quantity" id="rbfw_item_quantity">
                                    <option value="0"><?php esc_html_e('Choose number of quantity','booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <?php for ($qty = 1; $qty <= $item_stock_quantity; $qty++) { ?>
                                        <option value="<?php echo esc_attr($qty); ?>" <?php if($qty == 1){ echo 'selected'; } ?>><?php echo esc_html($qty); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php }elseif ($item_stock_quantity > 0){ ?>
                        <input type="hidden" name="rbfw_item_quantity" value="1">
                    <?php } elseif($input_stock_quantity == 'no_has_value'){ ?>
                        <input type="hidden" name="rbfw_item_quantity" value="1">
                    <?php }else{ ?>
                        <input type="hidden" name="rbfw_item_quantity" value="0">
                    <?php } ?>

                    <?php if($rbfw_enable_variations == 'yes' && !empty($rbfw_variations_data)){ ?>
                        <div class="rbfw-variations-content-wrapper" style="display: none">
                            <?php foreach ($rbfw_variations_data as $data_arr_one) {
                                $selected_value = !empty($data_arr_one['selected_value']) ? $data_arr_one['selected_value'] : '';
                                ?>
                                <div class="item">
                                    <div class="rbfw-single-right-heading"><?php echo esc_html($data_arr_one['field_label']); ?></div>
                                    <div class="item-content rbfw-p-relative">
                                        <?php if(!empty($data_arr_one['value'])){  ?>
                                            <select class="rbfw-select rbfw_variation_field" required name="<?php echo esc_attr($data_arr_one['field_id']); ?>" id="<?php echo esc_attr($data_arr_one['field_id']); ?>" data-field="<?php echo esc_attr($data_arr_one['field_label']); ?>">
                                                <?php if(empty($selected_value)){ ?>
                                                    <option value=""><?php echo esc_html(__('Choose','booking-and-rental-manager-for-woocommerce').' '.$data_arr_one['field_label']); ?></option>
                                                <?php } ?>
                                                <?php foreach ($data_arr_one['value'] as $data_arr_two) { ?>
                                                    <option class="rbfw_variant" value="<?php echo esc_attr($data_arr_two['name']); ?>" <?php if($data_arr_two['name'] == $selected_value){ echo 'selected'; } ?> ><?php echo esc_html($data_arr_two['name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php
                    $option_value  = get_post_meta($post_id, 'rbfw_service_category_price', true);
                    $option_value  = is_serialized($option_value) ? unserialize($option_value) : $option_value;
                    ?>

                    <?php if (!empty($option_value) && $enable_service_price === 'on') { ?>
                        <div class="multi-service-category-section" style="display: none">
                            <?php foreach ($option_value as $cat => $item) { ?>
                                <div class="servise-item">
                                    <div class="rbfw-single-right-heading"><?php echo esc_html($item['cat_title']); ?></div>
                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][cat_title]" value="<?php echo esc_attr($item['cat_title']); ?>">
                                    <div class="item-content rbfw-resource">
                                        <table class="rbfw_bikecarmd_es_table">
                                            <tbody>
                                            <?php foreach ($item['cat_services'] as $serkey => $service) { ?>

                                                <?php if (!empty($service['title'])) { ?>
                                                    <tr class="service-price-item">
                                                        <td class="w_20">
                                                            <div style="display: none;" class="rbfw-sold-out">
                                                                <?php esc_html_e('Sold Out', 'booking-and-rental-manager-for-woocommerce'); ?>
                                                            </div>
                                                            <div class="rbfw-checkbox">
                                                                <label class="switch">
                                                                    <input type="checkbox"
                                                                           class="rbfw_service_price_data item_<?php echo esc_attr($cat . $serkey); ?>"
                                                                           name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][main_cat_name]"
                                                                           data-service_price_type="<?php echo esc_attr(isset($service['service_price_type'])?$service['service_price_type']:''); ?>"
                                                                           data-price="<?php echo esc_attr($service['price']); ?>"
                                                                           data-quantity="1"
                                                                           data-rbfw_enable_md_type_item_qty="<?php echo esc_attr($rbfw_enable_extra_service_qty); ?>"
                                                                           data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                                <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][name]" value="<?php echo esc_attr($service['title']); ?>">
                                                                <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][service_price_type]" value="<?php echo esc_attr(isset($service['service_price_type'])?$service['service_price_type']:''); ?>">
                                                                <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][price]" value="<?php echo esc_attr($service['price']); ?>">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            
                                                        <div class="title">
                                                            <?php if($service['icon']){ ?>
                                                                <i class="sc-icon <?php echo esc_attr($service['icon']); ?>"></i>
                                                            <?php } ?>
                                                            <?php echo esc_html($service['title']); ?>

                                                    <?php if($available_qty_info_switch == 'yes'){ ?>
                                                                <i class="available-stock item_<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <?php esc_html_e('Available Qty ', 'booking-and-rental-manager-for-woocommerce'); ?><span class="remaining_stock"></span>
                                                                </i>
                                                        <?php } ?>
                                                            </div>
                                                        </td>
                                                        <td class="w_20">
                                                            <div class="title"><?php echo wp_kses(wc_price($service['price']),rbfw_allowed_html()); ?></div>
                                                            <span class="day-time-wise"><?php echo (isset($service['service_price_type'] ) && $service['service_price_type'] === 'day_wise') ? ($rbfw->get_option_trans('rbfw_text_day_wise', 'rbfw_basic_translation_settings') && want_loco_translate()=='no' ? esc_html($rbfw->get_option_trans('rbfw_text_day_wise', 'rbfw_basic_translation_settings')) : esc_html__('Day Wise', 'booking-and-rental-manager-for-woocommerce')) : ($rbfw->get_option_trans('rbfw_text_one_time', 'rbfw_basic_translation_settings') && want_loco_translate()=='no' ? esc_html($rbfw->get_option_trans('rbfw_text_one_time', 'rbfw_basic_translation_settings')) : esc_html__('One Time', 'booking-and-rental-manager-for-woocommerce')); ?></span>
                                                        </td>
                                                        <td class="rbfw_service_quantity item_<?php echo esc_attr($cat . $serkey); ?>" style="display: none;">
                                                            <div class="rbfw_qty_input">
                                                                <a class="rbfw_service_quantity_minus" data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <i class="fas fa-minus"></i>
                                                                </a>
                                                                <input type="number"
                                                                       name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][quantity]"
                                                                       min="0"
                                                                       value="1"
                                                                       class="rbfw_service_qty rbfw_service_info_stock"
                                                                       data-cat="service"
                                                                       data-price="<?php echo esc_attr($service['price']); ?>"
                                                                       data-item="<?php echo esc_attr($cat . $serkey); ?>"
                                                                       autocomplete="off">
                                                                <a class="rbfw_service_quantity_plus" data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <i class="fas fa-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if(!empty($extra_service_list)){ ?>
                        <div class="item rbfw_resourse_md" style="display: none">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Optional Add-ons','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-resource">

                                <table class="rbfw_bikecarmd_es_table">
                                    <tbody>
                                    <?php
                                    $c = 0;
                                    foreach ($extra_service_list as $key=>$extra) { ?>
                                        <?php if(isset($extra['service_qty']) && $extra['service_qty'] > 0){ ?>
                                            <tr>
                                                <td class="w_20 rbfw_bikecarmd_es_hidden_input_box">
                                        <div style="display: none" class="rbfw-sold-out">
                                            <?php esc_html_e('Sold Out', 'booking-and-rental-manager-for-woocommerce'); ?>
                                        </div>
                                                    <div class="label rbfw-checkbox">
                                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_name]" value="<?php echo esc_attr($extra['service_name']); ?>">
                                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_qty]" class="rbfw-resource-qty key_value_cart_<?php echo esc_attr($key+1); ?>" value="">
                                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_price]"  value="<?php echo esc_attr($extra['service_price']); ?>">

                                                        <label class="switch">
                                                            <input type="checkbox" max="4"  class="rbfw-resource-price rbfw-resource-price-multiple-qty key_value_<?php echo esc_attr($key+1); ?>" data-status="0" value="1" data-cat="service"  data-quantity="1"  data-price="<?php echo esc_attr($extra['service_price']); ?>" data-name="<?php echo esc_attr($extra['service_name']); ?>">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="resource-title-qty">
                                                    <?php echo esc_html($extra['service_name']); ?>
                                            <?php if($available_qty_info_switch == 'yes'){ ?>
                                                    <i class="resource-qty"><?php esc_html_e('Available Qty ','booking-and-rental-manager-for-woocommerce') ?><span class="es_stock"><?php echo '('.esc_html($extra['service_qty']).')'; ?></span></i>
                                                <?php } ?>
                                                </td>
                                                <td class="w_20"><?php echo wp_kses(wc_price($extra['service_price']),rbfw_allowed_html()); ?></td>
                                                <?php if($rbfw_enable_extra_service_qty == 'yes'){ ?>
                                                    <td class="rbfw_bikecarmd_es_input_box" style="display:none">
                                                        <div class="rbfw_qty_input">
                                                            <a class="rbfw_qty_minus rbfw_bikecarmd_es_qty_minus" data-item="<?php echo esc_attr($key+1); ?>"><i class="fas fa-minus"></i></a>
                                                            <input type="number" min="0" max="" value="1" class="rbfw_bikecarmd_es_qty"  data-cat="service" data-item="<?php echo esc_attr($key+1); ?>" data-price="<?php echo esc_attr($extra['service_price']); ?>" data-name="<?php echo esc_attr($extra['service_name']); ?>"/>
                                                            <a class="rbfw_qty_plus rbfw_bikecarmd_es_qty_plus" data-item="<?php echo esc_attr($key+1); ?>"><i class="fas fa-plus"></i></a>
                                                        </div>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                        <?php $c++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                </div>


                <div class="rbfw_bikecarmd_price_result" style="display: none">
                    <div class="item-content rbfw-costing">
                        <ul class="rbfw-ul">

                            <li class="duration-costing rbfw-cond">
                                <span>
                                    <?php esc_html_e('Duration Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                    <span class="rbfw_pricing_applied sessional">
                                        (<?php esc_html_e( 'Sessional pricing applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                                    </span>
                                    <span class="rbfw_pricing_applied mds">
                                        (<?php esc_html_e( 'Multi day pricing saver applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                                    </span>
                                </span>
                                <span class="price-figure" data-price="">
                                </span>
                            </li>
                            <li class="resource-costing rbfw-cond">
                                <?php esc_html_e('Resource Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="price-figure" data-price="">
                                    </span>
                            </li>
                            <li class="subtotal">
                                <?php esc_html_e('Subtotal','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="price-figure" data-price="">
                                    </span>
                            </li>


                            <li class="discount" style="display:none;">
                                <?php esc_html_e('Discount','booking-and-rental-manager-for-woocommerce'); ?>
                                <span></span>
                            </li>

                            <li class="security_deposit" style="display:none;">
                                <?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : __('Security Deposit','booking-and-rental-manager-for-woocommerce'))); ?>
                                <span></span>
                            </li>
                            <li class="total">
                                <?php esc_html_e('Price','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="price-figure" data-price="">
                                </span>
                            </li>
                        </ul>
                        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                    </div>
                </div>
                
                <?php

                $rbfw_minimum_booking_day = 0;
                $rbfw_maximum_booking_day = 0;
                if(rbfw_check_min_max_booking_day_active()){
                    $rbfw_minimum_booking_day = (int)get_post_meta($post_id, 'rbfw_minimum_booking_day', true);
                    if(get_post_meta($post_id, 'rbfw_maximum_booking_day', true)){
                        $rbfw_maximum_booking_day = '+'.get_post_meta($post_id, 'rbfw_maximum_booking_day', true).'d';
                    }
                }

                $day_wise_imventory = '';

                if($rbfw_enable_time_picker != 'yes') {

                    $year = Date('Y');
                    $month = Date('n');

                    for ($i = 0; $i <= 1; $i++) {

                        if ($i == 0) {
                            $total_days_month = 30;
                            if (function_exists('cal_days_in_month')) {
                                $total_days_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            }
                            $day_wise_imventory_1 = rbfw_day_wise_sold_out_check_by_month($post_id, $year, $month, $total_days_month);
                        }

                        if ($i == 1) {
                            $date = new DateTime("$year-$month-01");
                            $date->modify('+1 month');
                            $year = $date->format('Y');
                            $month = $month + 1;

                            $total_days_month = 30;
                            if (function_exists('cal_days_in_month')) {
                                $total_days_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            }

                            $day_wise_imventory_2 = rbfw_day_wise_sold_out_check_by_month($post_id, $year, $month, $total_days_month);
                        }
                        if ($i == 2) {
                            $date = new DateTime("$year-$month-01");
                            $date->modify('+2 month');
                            $year = $date->format('Y');
                            $month = $month + 1;
                            $total_days_month = 30;
                            if (function_exists('cal_days_in_month')) {
                                $total_days_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            }


                            $day_wise_imventory_3 = rbfw_day_wise_sold_out_check_by_month($post_id, $year, $month, $total_days_month);
                        }
                    }
                    $day_wise_imventory = wp_json_encode(array_merge($day_wise_imventory_1, $day_wise_imventory_2));
                }

            

                ?>

                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>

                <input type="hidden" name="rbfw_duration_price" id="rbfw_duration_price"  value="0">
                <input type="hidden" name="rbfw_service_price" id="rbfw_service_price"  value="0">
                <input type="hidden" name="rbfw_es_service_price" id="rbfw_es_service_price"  value="0">

                <input type="hidden" name="rbfw_security_deposit_enable" id="rbfw_security_deposit_enable"  value="<?php echo esc_attr($rbfw_enable_security_deposit); ?>">
                <input type="hidden" name="rbfw_security_deposit_type" id="rbfw_security_deposit_type"  value="<?php echo esc_attr($rbfw_security_deposit_type); ?>">
                <input type="hidden" name="rbfw_security_deposit_amount" id="rbfw_security_deposit_amount"  value="<?php echo esc_attr($rbfw_security_deposit_amount); ?>">

                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="bike_car_md">
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">
                <input type="hidden" name="rbfw_enable_variations" id="rbfw_enable_variations"  value="<?php echo esc_attr($rbfw_enable_variations); ?>">
                <input type="hidden" name="rbfw_input_stock_quantity" id="rbfw_input_stock_quantity"  value="<?php echo esc_attr($input_stock_quantity); ?>">
                <input type="hidden" name="rbfw_enable_time_slot" id="rbfw_enable_time_slot"  value="<?php echo esc_attr($rbfw_enable_time_picker); ?>">
                <input type="hidden" name="total_days" id="rbfw_total_days" value="0">
                <input type="hidden" id="rbfw_minimum_booking_day" value="<?php echo esc_attr($rbfw_minimum_booking_day); ?>">
                <input type="hidden" id="rbfw_maximum_booking_day" value="<?php echo esc_attr($rbfw_maximum_booking_day); ?>">
                <input type="hidden" id="rbfw_month_wise_inventory" value="<?php echo esc_attr($day_wise_imventory); ?>">



                
                <?php if(rbfw_chk_regf_fields_exist($rbfw_id) === true){ ?>
                    <div class="item">
                        <div class="rbfw_reg_form_rb" style="display: none">
                            <?php
                            $reg_form = new Rbfw_Reg_Form();
                            echo wp_kses($reg_form->rbfw_generate_regf_fields($post_id),rbfw_allowed_html());
                            ?>
                        </div>

                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <?php do_action('rbfw_ticket_feature_info'); ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="rbfw_mps_book_now_btn_regf_____ mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn"  disabled >
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>
                <?php } else{ ?>
                    <div class="item">
                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn" disabled <?php if( $rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date ) { echo 'style="display:none"'; }?>>
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>

                <?php } ?>

                <?php if($rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date) {
                    echo '<div class="mps_alert_warning">'.(($rbfw->get_option_trans('rbfw_text_booking_time_expired', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_booking_time_expired', 'rbfw_basic_translation_settings')) : esc_html__('Booking Time Expired!','booking-and-rental-manager-for-woocommerce')).'</div>';
                } ?>
            </div>
                
            <div class="rbfw-bikecarmd-result-wrap">
                <div class="rbfw-bikecarmd-result-loader">
                </div>
                <div class="rbfw-bikecarmd-result">
                </div>
            </div>

        </form>
    </div>
</div>


