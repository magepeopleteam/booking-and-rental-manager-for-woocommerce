<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
$rbfw_id = $post_id ??0;

global $submit_name;
global $rbfw;
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

$rbfw_enable_md_type_item_qty = get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) ? get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) : 'no';


$rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
$rbfw_variations_data = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];

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

if ( isset( $_GET['rbfw_start_date'], $_GET['rbfw_end_date'] ) ) {
    $rbfw_start_date = sanitize_text_field( wp_unslash( $_GET['rbfw_start_date'] ) );
    $rbfw_end_date   = sanitize_text_field( wp_unslash( $_GET['rbfw_end_date'] ) );
    if ( $rbfw_start_date && $rbfw_end_date ) {
        $rbfw_enable_time_picker = 'no';
        $referal_page = 'search';
    }
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

$rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off';


if($rbfw_particular_switch=='off'){
    $particulars_data = [];
}else{
    $particulars_data = get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ) : [];
}

$rdfw_available_time = get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ) : [];

$rbfw_buffer_time = get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ) : 0;


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
                    <div class="item pricing-content-container">
                        <?php do_action('rbfw_pricing_info_header'); ?>
                        <div class="price-item-container">
                            <span class="close-price-container"><i class="mi mi-x"></i></span>
                            <div class="mpStyle"  >
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
                                                    <td><?php echo wp_kses_post(wc_price($hourly_rate)); ?> / <?php esc_html_e('Hour', 'booking-and-rental-manager-for-woocommerce'); ?></td>
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
                                                        <td>
                                                            <?php
                                                            if($rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                                                echo esc_html($rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings'));
                                                            }else{
                                                                echo esc_html__('Daily','booking-and-rental-manager-for-woocommerce');
                                                            }
                                                            ?>

                                                            (<?php echo esc_html(get_woocommerce_currency_symbol()); ?>)
                                                        </td>
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
                                                        <td>
                                                            <?php
                                                            if($rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                                                echo esc_html($rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings'));
                                                            }else{
                                                                echo esc_html__('Hourly','booking-and-rental-manager-for-woocommerce');
                                                            }
                                                            ?>
                                                            (<?php echo esc_html(get_woocommerce_currency_symbol()); ?>)</td>
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
                                                        <td><strong>
                                                                <?php esc_html_e('Daily Rate', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                                        <td><?php echo wp_kses_post(wc_price($daily_rate)); ?> / <?php esc_html_e('day', 'booking-and-rental-manager-for-woocommerce'); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($rbfw_enable_time_picker == 'yes' && $enable_hourly_rate=='yes') { ?>
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
                                    $rbfw_tiered_pricing = get_post_meta($post_id, 'rbfw_tiered_pricing', true);
                                    if (is_plugin_active('multi-day-price-saver-addon-for-wprently/additional-day-price.php') && (!(empty($rbfw_md_data_mds)))) {
                                        foreach ($rbfw_md_data_mds as $item){
                                            ?>
                                            <div class="mp_item_insert ">
                                                <table>
                                                    <tbody>
                                                    <tr>
                                                        <td <?php echo ($rbfw_enable_time_picker == 'yes' &&  $enable_hourly_rate=='yes')?'colspan="2"':'' ?>>
                                                            <?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                            <strong><?php echo esc_html($item['rbfw_start_day']) ?></strong>
                                                            <?php esc_html_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <strong><?php esc_html_e( 'Daily Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><?php echo wc_price($item['rbfw_daily_price']) ?>
                                                        </td>
                                                        <?php if($rbfw_enable_time_picker == 'yes'  && $enable_hourly_rate=='yes'){ ?>
                                                        <td>
                                                            <strong><?php esc_html_e( 'Hourly Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><?php echo wc_price($item['rbfw_hourly_price']) ?>
                                                        </td>
                                                        <?php } ?>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    <?php }elseif(is_plugin_active('tiered-pricing-addon-wprently/tiered-pricing-addon.php') && (!(empty($rbfw_tiered_pricing)))){
                                    foreach ($rbfw_tiered_pricing as $item){
                                    ?>
                                        <div class="mp_item_insert ">
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        <strong><?php echo esc_html($item['rbfw_start_day_tiered']) ?></strong>
                                                        <?php esc_html_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        <?php esc_html_e( 'To', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        <strong><?php echo esc_html($item['rbfw_end_day_tiered']) ?></strong>
                                                        <?php esc_html_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong><?php esc_html_e( 'Daily Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><?php echo wc_price($item['rbfw_daily_price_tiered']) ?>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                    <?php } else{ ?>
                                       <?php $seasonal_prices = [];
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
                    </div>

                    <?php
                    // Determine the lowest-unit "starting from" price and period label
                    $_rbfw_md_start = 0;
                    $_rbfw_md_per   = '';
                    if ( $enable_hourly_rate == 'yes' && $hourly_rate > 0 ) {
                        $_rbfw_md_start = $hourly_rate;
                        $_rbfw_md_per   = __( 'Hour', 'booking-and-rental-manager-for-woocommerce' );
                    } elseif ( $enable_daily_rate == 'yes' && $daily_rate > 0 ) {
                        $_rbfw_md_start = $daily_rate;
                        $_rbfw_md_per   = __( 'Day', 'booking-and-rental-manager-for-woocommerce' );
                    } elseif ( $rbfw_enable_weekly_rate == 'yes' && $rbfw_weekly_rate > 0 ) {
                        $_rbfw_md_start = $rbfw_weekly_rate;
                        $_rbfw_md_per   = __( 'Week', 'booking-and-rental-manager-for-woocommerce' );
                    } elseif ( $rbfw_enable_monthly_rate == 'yes' && $rbfw_monthly_rate > 0 ) {
                        $_rbfw_md_start = $rbfw_monthly_rate;
                        $_rbfw_md_per   = __( 'Month', 'booking-and-rental-manager-for-woocommerce' );
                    }
                    ?>
                    <div class="rbfw-sd-rate-box">
                        <?php rbfw_fd_summary_badges(); ?>
                        <?php rbfw_fd_summary_title(); ?>
                        <?php rbfw_fd_summary_desc(); ?>
                        <?php if ( $_rbfw_md_start > 0 ) : ?>
                        <div class="rbfw-sd-rate-box-price-row">
                            <span class="rbfw-sd-rate-box-label"><?php esc_html_e( 'Starting from', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            <div class="rbfw-sd-rate-box-price">
                                <?php echo wp_kses( wc_price( $_rbfw_md_start ), rbfw_allowed_html() ); ?>
                                <span class="rbfw-sd-rate-per">/ <?php echo esc_html( $_rbfw_md_per ); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="rbfw-sd-trust-grid">
                            <div class="rbfw-sd-trust-item">
                                <i class="far fa-check-circle"></i>
                                <span><?php esc_html_e( 'Instant confirmation', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div class="rbfw-sd-trust-item">
                                <i class="fas fa-lock"></i>
                                <span><?php esc_html_e( 'Secure payment', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div class="rbfw-sd-trust-item">
                                <i class="far fa-calendar-times"></i>
                                <span><?php esc_html_e( 'Free cancellation', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="pickup_date "></div>

                    <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                        <div class="item">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Pickup Location','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
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
                    <input type="hidden" id="rbfw_block_offday_booking" value="<?php echo esc_attr(rbfw_block_offday_range_booking($post_id)); ?>">

                    <?php if($rbfw_enable_start_end_date == 'yes'){ ?>
                        <div class="rbfw-drp-wrapper">
                            <div class="rbfw-drp-row">

                                <div class="rbfw-drp-col">
                                    <span class="rbfw-drp-label"><?php esc_html_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?></span>
                                    <div class="rbfw-drp-field">
                                        <span class="rbfw-drp-icon"><i class="fas fa-calendar-days"></i></span>
                                        <?php if($referal_page == 'search'){ ?>
                                            <input type="hidden" id="hidden_pickup_date" value="<?php echo esc_attr($rbfw_start_date); ?>" name="rbfw_pickup_start_date">
                                            <input class="rbfw-input rbfw-time-price pickup_date" type="text" value="<?php echo esc_attr(rbfw_date_format($rbfw_start_date)); ?>" id="pickup_date" placeholder="<?php esc_attr_e('Select date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="">
                                        <?php }else{ ?>
                                            <input type="hidden" id="hidden_pickup_date" name="rbfw_pickup_start_date">
                                            <input class="rbfw-input rbfw-time-price pickup_date" type="text" id="pickup_date" placeholder="<?php esc_attr_e('Select date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="">
                                        <?php } ?>
                                        <span class="rbfw-drp-chevron"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>

                                <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                <div class="rbfw-drp-col rbfw-drp-col--time">
                                    <span class="rbfw-drp-label"><?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?></span>
                                    <div class="rbfw-drp-field rbfw-drp-field--time">
                                        <span class="rbfw-drp-icon"><i class="fa-regular fa-clock"></i></span>
                                        <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_pickup_start_time" id="pickup_time" required>
                                            <option value="" disabled selected><?php esc_html_e('Select time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                        </select>
                                        <span class="rbfw-drp-chevron"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="rbfw-drp-col">
                                    <span class="rbfw-drp-label"><?php esc_html_e('Return Date','booking-and-rental-manager-for-woocommerce'); ?></span>
                                    <div class="rbfw-drp-field">
                                        <span class="rbfw-drp-icon"><i class="fas fa-calendar-days"></i></span>
                                        <?php if($referal_page == 'search'){ ?>
                                            <input type="hidden" id="hidden_dropoff_date" value="<?php echo esc_attr($rbfw_end_date); ?>" name="rbfw_pickup_end_date">
                                            <input class="rbfw-input rbfw-time-price dropoff_date" type="text" value="<?php echo esc_attr(rbfw_date_format($rbfw_end_date)); ?>" id="dropoff_date" placeholder="<?php esc_attr_e('Select date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="">
                                        <?php }else{ ?>
                                            <input type="hidden" id="hidden_dropoff_date" name="rbfw_pickup_end_date">
                                            <input class="rbfw-input rbfw-time-price dropoff_date" type="text" id="dropoff_date" placeholder="<?php esc_attr_e('Select date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="">
                                        <?php } ?>
                                        <span class="rbfw-drp-chevron"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>

                                <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                    <input name="rbfw_available_time" id="rbfw_available_time" value="yes" type="hidden">
                                    <div class="rbfw-drp-col rbfw-drp-col--time">
                                        <span class="rbfw-drp-label"><?php esc_html_e('Return Time','booking-and-rental-manager-for-woocommerce'); ?></span>
                                        <div class="rbfw-drp-field rbfw-drp-field--time">
                                            <span class="rbfw-drp-icon"><i class="fa-regular fa-clock"></i></span>
                                            <select class="rbfw-select rbfw-time-price dropoff_time" name="rbfw_pickup_end_time" id="dropoff_time" required>
                                                <option value="" disabled selected><?php esc_html_e('Select time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            </select>
                                            <span class="rbfw-drp-chevron"><i class="fas fa-chevron-down"></i></span>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                        <div class="item rbfw-duration">
                            <div class="rbfw-single-right-heading">
                                <span class="rbfw-duration-left">
                                    <i class="fa-regular fa-clock rbfw-duration-icon"></i>
                                    <span class="rbfw-duration-label"><?php esc_html_e('Duration:','booking-and-rental-manager-for-woocommerce'); ?></span>
                                    <span class="item-content"></span>
                                </span>
                                <span class="item-price"></span>
                            </div>
                            <input type="hidden" class="rbfw_duration_md" name="rbfw_duration_md">
                        </div>

                    <?php } else { ?>
                        <input type="hidden" name="rbfw_pickup_start_date" id="pickup_date" value="<?php echo esc_html($rbfw_event_start_date); ?>"/>
                        <input type="hidden" name="rbfw_pickup_start_time" id="pickup_time" value="<?php echo esc_html($rbfw_event_start_time); ?>"/>
                        <input type="hidden" name="rbfw_pickup_end_date" id="dropoff_date" value="<?php echo esc_html($rbfw_event_end_date); ?>"/>
                        <input type="hidden" name="rbfw_pickup_end_time" id="dropoff_time" value="<?php echo esc_html($rbfw_event_end_time); ?>"/>
                    <?php } ?>


                    <?php if ($rbfw_enable_md_type_item_qty == 'yes' && $item_stock_quantity > 0) { ?>
                        <div class="item rbfw_quantity_md">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Quantity','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-quantity">
                                <select class="rbfw-select" name="rbfw_item_quantity" id="rbfw_item_quantity_md">
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
                        <div class="rbfw-variations-content-wrapper">
                            <?php foreach ($rbfw_variations_data as $data_arr_one) {
                                // Some saved/legacy variation rows may omit one or more keys; default
                                // them so the template never raises "Undefined array key" notices.
                                $field_label    = isset($data_arr_one['field_label']) ? $data_arr_one['field_label'] : '';
                                $field_id       = isset($data_arr_one['field_id']) ? $data_arr_one['field_id'] : '';
                                $field_values   = !empty($data_arr_one['value']) && is_array($data_arr_one['value']) ? $data_arr_one['value'] : array();
                                $selected_value = !empty($data_arr_one['selected_value']) ? $data_arr_one['selected_value'] : '';
                                ?>
                                <div class="item">
                                    <div class="rbfw-single-right-heading"><?php echo esc_html($field_label); ?></div>
                                    <div class="item-content rbfw-p-relative">
                                        <?php if(!empty($field_values)){  ?>
                                            <select class="rbfw-select rbfw_variation_field" required name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" data-field="<?php echo esc_attr($field_label); ?>">
                                                <?php if(empty($selected_value)){ ?>
                                                    <option value=""><?php echo esc_html(__('Choose','booking-and-rental-manager-for-woocommerce').' '.$field_label); ?></option>
                                                <?php } ?>
                                                <?php foreach ($field_values as $data_arr_two) {
                                                    $variant_name = isset($data_arr_two['name']) ? $data_arr_two['name'] : '';
                                                    ?>
                                                    <option class="rbfw_variant" value="<?php echo esc_attr($variant_name); ?>" <?php if($variant_name !== '' && $variant_name == $selected_value){ echo 'selected'; } ?> ><?php echo esc_html($variant_name); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php

                    $rbfw_service_category_price  = get_post_meta($post_id, 'rbfw_service_category_price', true);

                    if(!is_array($rbfw_service_category_price)){
                        $rbfw_service_category_price  = json_decode($rbfw_service_category_price, true);
                    }
                    $option_value  = is_serialized($rbfw_service_category_price) ? unserialize($rbfw_service_category_price) : $rbfw_service_category_price;
                    $has_valid_services = false;
                    if ( is_array( $option_value ) ) {
                        foreach ( $option_value as $_cat_item ) {
                            if ( ! empty( $_cat_item['cat_services'] ) && is_array( $_cat_item['cat_services'] ) ) {
                                foreach ( $_cat_item['cat_services'] as $_svc ) {
                                    if ( ! empty( $_svc['title'] ) ) {
                                        $has_valid_services = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    ?>

                    <?php if ( $has_valid_services ) { ?>
                        <div class="multi-service-category-section">
                            <?php foreach ($option_value as $cat => $item) { ?>
                                <div class="servise-item">
                                    <div class="rbfw-single-right-heading"><?php echo esc_html($item['cat_title']); ?></div>
                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][cat_title]" value="<?php echo esc_attr($item['cat_title']); ?>">
                                    <div class="item-content rbfw-resource">
                                        <table class="rbfw_bikecarmd_es_table">
                                            <tbody>
                                            <?php foreach ( ( isset( $item['cat_services'] ) && is_array( $item['cat_services'] ) ) ? $item['cat_services'] : [] as $serkey => $service ) { ?>

                                                <?php if (!empty($service['title'])) { ?>
                                                    <tr class="service-price-item">
                                                        <td>
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][name]" value="<?php echo esc_attr($service['title']); ?>">
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][service_price_type]" value="<?php echo esc_attr(isset($service['service_price_type'])?$service['service_price_type']:''); ?>">
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][price]" value="<?php echo esc_attr($service['price']); ?>">
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
                                                            <div class="title"><?php echo wp_kses(wc_price($service['price']),rbfw_allowed_html()); ?></div>
                                                            <span class="day-time-wise"><?php echo (isset($service['service_price_type']) && $service['service_price_type'] === 'day_wise') ? esc_html__('Day Wise', 'booking-and-rental-manager-for-woocommerce') : esc_html__('One Time', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            <span class="rbfw_service_day_calc" style="display:none;"></span>
                                                        </td>
                                                        <td class="rbfw_service_quantity item_<?php echo esc_attr($cat . $serkey); ?>">
                                                            <div class="rbfw_qty_input">
                                                                <a class="rbfw_service_quantity_minus" data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <i class="fas fa-minus"></i>
                                                                </a>
                                                                <input type="number"
                                                                       name="rbfw_service_price_data[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][quantity]"
                                                                       min="0"
                                                                       value="0"
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




                    <?php $rbfw_fee_data = get_post_meta( $post_id, 'rbfw_fee_data', true ); ?>
                    <?php if(!empty($rbfw_fee_data)){ ?>
                        <div class="item rbfw_resourse_md">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Fee Management','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-resource">
                                <table class="rbfw_bikecarmd_es_table">
                                    <tbody>
                                    <?php
                                    $c = 0;
                                    //echo '<pre>';print_r($rbfw_fee_data);echo '<pre>';
                                    $rbfw_management_price = 0;
                                    foreach ($rbfw_fee_data as $key=>$fee) { ?>
                                        <?php if(isset($fee['label'])){ ?>
                                            <tr>
                                                <td class="w_20">
                                                    <div class="label rbfw-checkbox">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][label]" value="<?php echo esc_attr($fee['label']); ?>">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][is_checked]" class="rbfw-management-qty" value="<?php echo (esc_attr($fee['priority'])=='required')?'yes':'' ?>">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][amount]"  value="<?php echo esc_attr($fee['amount']); ?>">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][calculation_type]"  value="<?php echo esc_attr($fee['calculation_type']); ?>">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][frequency]"  value="<?php echo esc_attr($fee['frequency']); ?>">
                                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][refundable]"  value="<?php echo esc_attr($fee['refundable']); ?>">
                                                        <label class="switch">
                                                            <input type="checkbox" <?php echo (esc_attr($fee['priority'])=='required')?'checked':'' ?>   class="rbfw-management-price <?php echo (esc_attr($fee['priority'])=='required')?'rbfw-fee-required':'' ?> key_value_<?php echo esc_attr($key+1); ?>"   data-price="<?php echo esc_attr($fee['amount']); ?>" data-name="<?php echo esc_attr($fee['label']); ?>" data-price_type="<?php echo esc_attr($fee['calculation_type']); ?>" data-frequency="<?php echo esc_attr($fee['frequency']); ?>">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="resource-title-qty">
                                                    <?php echo esc_html($fee['label']); ?>
                                                    <span class="rbfw-refundable">
                                                        (<?php
                                                            if($fee['refundable']=='yes'){
                                                                esc_html_e('Refundable','booking-and-rental-manager-for-woocommerce');
                                                            }else{
                                                                esc_html_e('Non refundable','booking-and-rental-manager-for-woocommerce');
                                                            }
                                                            ?>
                                                        -
                                                            <?php
                                                            if($fee['frequency']=='one-time'){
                                                                esc_html_e('One Time','booking-and-rental-manager-for-woocommerce');
                                                            }else{
                                                                esc_html_e('Day Wise','booking-and-rental-manager-for-woocommerce');
                                                            }
                                                        ?>)
                                                    </span>

                                                </td>
                                                <td class="w_20">
                                                    <?php if($fee['calculation_type']=='fixed'){
                                                        echo wp_kses(wc_price($fee['amount']),rbfw_allowed_html());
                                                   }else{
                                                       echo $fee['amount'].'%';
                                                   }
                                                   ?>
                                                </td>
                                                <?php
                                                if(esc_attr($fee['priority'])=='required'){
                                                    $rbfw_management_price +=  $fee['amount'];
                                                }
                                                ?>
                                            </tr>
                                        <?php } ?>
                                        <?php $c++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>


                </div>


                <div class="rbfw_bikecarmd_price_result">
                    <div class="item-content rbfw-costing">
                        <ul class="rbfw-ul">

                            <li class="duration-costing rbfw-cond" style="display: none">
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

                            <li class="resource-costing rbfw-cond" style="display:none;">
                                <?php esc_html_e('Resource Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="price-figure" data-price="">
                                </span>
                            </li>

                            <li class="subtotal">
                                <?php esc_html_e('Subtotal','booking-and-rental-manager-for-woocommerce'); ?>
                                <span class="price-figure" data-price=""><?php echo wp_kses( wc_price(0), rbfw_allowed_html() ); ?></span>
                            </li>

                            <li class="management-costing rbfw-cond" style="display:none;">
                                <?php esc_html_e('Management Cost','booking-and-rental-manager-for-woocommerce'); ?>
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
                                <span class="price-figure" data-price=""><?php echo wp_kses( wc_price(0), rbfw_allowed_html() ); ?></span>
                            </li>
                        </ul>
                        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                    </div>
                </div>
                
                <?php

                $rbfw_minimum_booking_day = 0;
                $rbfw_maximum_booking_day = 0;
                $rbfw_datewise_minmax     = array();
                if(rbfw_check_min_max_booking_day_active()){
                    $rbfw_minimum_booking_day = (int)get_post_meta($post_id, 'rbfw_minimum_booking_day', true);
                    if(get_post_meta($post_id, 'rbfw_maximum_booking_day', true)){
                        $rbfw_maximum_booking_day = '+'.get_post_meta($post_id, 'rbfw_maximum_booking_day', true).'d';
                    }
                    if(get_post_meta($post_id, 'rbfw_enable_datewise_minmax', true) === 'yes'){
                        $dw = get_post_meta($post_id, 'rbfw_datewise_minmax', true);
                        $rbfw_datewise_minmax = is_array($dw) ? array_values($dw) : array();
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
                    }
                    $day_wise_imventory = wp_json_encode($day_wise_imventory_1);
                }

            

                ?>

                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>

                <input type="hidden" name="rbfw_duration_price" id="rbfw_duration_price"  value="0">
                <input type="hidden" name="rbfw_service_price" id="rbfw_service_price"  value="0">
                <input type="hidden" name="rbfw_es_service_price" id="rbfw_es_service_price"  value="0">
                <input type="hidden" name="rbfw_management_price" id="rbfw_management_price"  value="0">

                <input type="hidden" name="rbfw_security_deposit_enable" id="rbfw_security_deposit_enable"  value="<?php echo esc_attr($rbfw_enable_security_deposit); ?>">
                <input type="hidden" name="rbfw_security_deposit_type" id="rbfw_security_deposit_type"  value="<?php echo esc_attr($rbfw_security_deposit_type); ?>">
                <input type="hidden" name="rbfw_security_deposit_amount" id="rbfw_security_deposit_amount"  value="<?php echo esc_attr($rbfw_security_deposit_amount); ?>">

                <input type="hidden" name="rbfw_discount_number" id="rbfw_discount_number"  value="">
                <input type="hidden" name="rbfw_discount_type" id="rbfw_discount_type"  value="">
                


                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="bike_car_md">
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">
                <input type="hidden" name="rbfw_enable_variations" id="rbfw_enable_variations"  value="<?php echo esc_attr($rbfw_enable_variations); ?>">
                <input type="hidden" name="rbfw_input_stock_quantity" id="rbfw_input_stock_quantity"  value="<?php echo esc_attr($input_stock_quantity); ?>">
                <input type="hidden" name="rbfw_enable_time_slot" id="rbfw_enable_time_slot"  value="<?php echo esc_attr($rbfw_enable_time_picker); ?>">
                <input type="hidden" name="total_days" id="rbfw_total_days" value="0">
                <input type="hidden" id="rbfw_minimum_booking_day" value="<?php echo esc_attr($rbfw_minimum_booking_day); ?>">
                <input type="hidden" id="rbfw_maximum_booking_day" value="<?php echo esc_attr($rbfw_maximum_booking_day); ?>">
                <input type="hidden" id="rbfw_datewise_minmax" value="<?php echo esc_attr( wp_json_encode( $rbfw_datewise_minmax ) ); ?>">
                <input type="hidden" id="rbfw_month_wise_inventory" value="<?php echo esc_attr($day_wise_imventory); ?>">

                <input type="hidden" name="rbfw_particular_switch" id="rbfw_particular_switch"  value='<?php echo esc_attr($rbfw_particular_switch); ?>'>
                <input type="hidden" name="rbfw_particulars_data" id="rbfw_particulars_data"  value='<?php echo esc_attr(wp_json_encode($particulars_data)); ?>'>
                <input type="hidden" name="rdfw_available_time" id="rdfw_available_time"  value='<?php echo esc_attr(wp_json_encode($rdfw_available_time)); ?>'>

                <input type="hidden" name="rbfw_buffer_time" id="rbfw_buffer_time"  value='<?php echo esc_attr($rbfw_buffer_time); ?>'>




                <?php if(rbfw_chk_regf_fields_exist($rbfw_id) === true){ ?>
                    <div class="item">
                        <div class="rbfw_reg_form_rb" style="display: none;">
                            <?php
                            $reg_form = new Rbfw_Reg_Form();
                            echo wp_kses($reg_form->rbfw_generate_regf_fields($post_id),rbfw_allowed_html());
                            ?>
                        </div>

                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <?php do_action('rbfw_ticket_feature_info'); ?>
                        <?php do_action('rbfw_add_term_condition',$rbfw_id) ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="rbfw_mps_book_now_btn_regf_____ mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn"  disabled >
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>
                <?php } else{ ?>
                    <div class="item">
                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <?php do_action('rbfw_add_term_condition',$rbfw_id) ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn" disabled <?php if( $rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date ) { echo 'style="display:none"'; }?>>
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>
                <?php } ?>

                <?php if($rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date) {
                    echo '<div class="mps_alert_warning">'.esc_html__('Booking Time Expired!','booking-and-rental-manager-for-woocommerce').'</div>';
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


