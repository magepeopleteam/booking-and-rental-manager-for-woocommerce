<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
$rbfw_id = $post_id ??0;
global $frontend;
global $submit_name;
$frontend = $frontend??0;
$submit_name=$submit_name??'admin-purchase';
$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;
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


$rbfw_time_slot_switch = get_post_meta($rbfw_id, 'rbfw_time_slot_switch', true) ? get_post_meta($rbfw_id, 'rbfw_time_slot_switch', true) : 'off';

$expire = 'no';
if($rbfw_enable_start_end_date=='no'){
    if($rbfw_event_last_date<$rbfw_todays_date){
        $expire = 'yes';
    }
}

$time_picker = 'off';
if($rbfw_time_slot_switch == 'on' && !empty($availabe_time) && $enable_hourly_rate == 'yes' ){
    $time_picker = 'on';
}



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
                    <div class="item">
                    <div class="item-content pricing-content">
    <div class="section-header">
        <div class="rbfw-single-right-heading rbfw_pricing_info_heading">
            <?php 
            echo esc_html($rbfw->get_option_trans('rbfw_text_pricing_info', 'rbfw_basic_translation_settings', __('Pricing Info', 'booking-and-rental-manager-for-woocommerce'))); 
            ?>
        </div>
    </div>
    <?php $rbfw_pricing_info_display = rbfw_get_option('rbfw_pricing_info_display', 'rbfw_basic_gen_settings'); ?>
</div>

                        <div class="price-item-container pricing-content_dh  mpStyle  <?php echo ($rbfw_pricing_info_display=='yes')?'open':'' ?>" style="display: <?php echo ($rbfw_pricing_info_display=='yes')?'block':'none' ?>">
                            <?php if($rbfw_enable_daywise_price == 'yes'){ ?>
                                <?php

                                $sunday = rbfw_day_row( __( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
                                $monday = rbfw_day_row( __( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
                                $tueday = rbfw_day_row( __( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
                                $wedday = rbfw_day_row( __( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
                                $thuday = rbfw_day_row( __( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
                                $friday = rbfw_day_row( __( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
                                $satday = rbfw_day_row( __( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );

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
                <td><strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily Rate', 'booking-and-rental-manager-for-woocommerce'))); ?></strong></td>
                <td><?php echo wp_kses_post(wc_price($daily_rate)); ?> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_day', 'rbfw_basic_translation_settings', __('day', 'booking-and-rental-manager-for-woocommerce'))); ?></td>
            </tr>
        <?php } ?>
        <?php if ($enable_hourly_rate == 'yes') { ?>
            <tr>
                <td><strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly Rate', 'booking-and-rental-manager-for-woocommerce'))); ?></strong></td>
                <td><?php echo wp_kses_post(wc_price($hourly_rate)); ?> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_hour', 'rbfw_basic_translation_settings', __('hour', 'booking-and-rental-manager-for-woocommerce'))); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

                            <?php } ?>

                            <?php
                            function rbfw_day_row( $day_name, $day_slug ) {
                                $hourly_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) : '';
                                $daily_rate  = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) : '';
                                $enable      = !empty(get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true )) ? get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) : '';
                                return array('enable'=>$enable,'day_name'=>$day_name,'daily_rate'=>$daily_rate,'hourly_rate'=>$hourly_rate);
                            }
                            ?>
                            <?php

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
                                                                <td colspan="2"><?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong><?php echo esc_html( rbfw_date_format($start_date) ); ?></strong><?php esc_html_e( 'To', 'booking-and-rental-manager-for-woocommerce' ); ?>  <strong><?php echo esc_html( rbfw_date_format($end_date) ); ?></strong> </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong><?php esc_html_e( 'Daily Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong> <?php echo  wp_kses(wc_price($sp_price_d) , rbfw_allowed_html()); ?></td>
                                                                <td><strong><?php esc_html_e( 'Hourly Rate:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>  <?php echo  wp_kses(wc_price($sp_price_h) , rbfw_allowed_html()); ?></td>
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

                        </div>
                    </div>

                    <div class="pickup_date "></div>

                    <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                        <div class="item">
                            <div class="rbfw-single-right-heading"><?php echo esc_attr($rbfw->get_option_trans('rbfw_text_pickup_location', 'rbfw_basic_translation_settings', __('Pickup Location','booking-and-rental-manager-for-woocommerce'))); ?></div>
                            <div class="item-content rbfw-location">
                                <select class="rbfw-select" name="rbfw_pickup_point" required>
                                    <option value=""><?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_pickup_location', 'rbfw_basic_translation_settings', __('Choose pickup location','booking-and-rental-manager-for-woocommerce'))); ?></option>
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
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_location', 'rbfw_basic_translation_settings', __('Drop-off Location','booking-and-rental-manager-for-woocommerce'))); ?>
                            </div>
                            <div class="item-content rbfw-location">
                                <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                    <option value=""><?php echo esc_attr($rbfw->get_option_trans('rbfw_text_choose_dropoff_location', 'rbfw_basic_translation_settings', __('Choose drop-off location','booking-and-rental-manager-for-woocommerce'))); ?></option>
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
                        <div class="item">
                            <div class="item-content rbfw-datetime">
                                <div class="<?php echo esc_attr(($time_picker == 'on')?'left':''); ?> date">
                                    <div class="rbfw-single-right-heading">
                                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings')); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                        <input type="hidden" id="hidden_pickup_date" name="rbfw_pickup_start_date">
                                        <input class="rbfw-input rbfw-time-price pickup_date" type="text"  id="pickup_date" placeholder="<?php echo esc_attr($rbfw->get_option_trans('rbfw_text_pickup_date', 'rbfw_basic_translation_settings', __('Pickup date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>
                                <?php if($time_picker == 'on'){ ?>
                                    <div class="right time">
                                        <div class="rbfw-single-right-heading">
                                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings')); ?>
                                        </div>
                                        <div class="rbfw-p-relative">
                                            <span class="clock">
                                                <i class="fa-regular fa-clock"></i>
                                            </span>
                                            <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_pickup_start_time" id="pickup_time" required>
                                                <option value="" disabled selected><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_time', 'rbfw_basic_translation_settings', __('Pickup time','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                                <?php foreach ($availabe_time as $key => $time) : ?>
                                                    <option value="<?php echo esc_attr($time); ?>"><?php echo esc_html(gmdate(get_option('time_format'), strtotime($time))); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="input-picker-icon"></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>


                        <div class="item">
                            <div class="item-content rbfw-datetime">
                                <div class="<?php echo ($time_picker == 'on')?'left':'' ?> date">
                                    <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_date', 'rbfw_basic_translation_settings')); ?></div>
                                    <div class="rbfw-p-relative">
                                        <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                        <input type="hidden" id="hidden_dropoff_date" name="rbfw_pickup_end_date">
                                        <input class="rbfw-input rbfw-time-price dropoff_date" type="text" id="dropoff_date" placeholder="<?php echo esc_attr($rbfw->get_option_trans('rbfw_text_return_date', 'rbfw_basic_translation_settings', __('Return date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>
                                <?php if($time_picker == 'on'){ ?>
                                    <input name="rbfw_available_time"  id="rbfw_available_time" value="yes" type="hidden">
                                    <div class="right time">
                                        <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_time', 'rbfw_basic_translation_settings')); ?></div>
                                        <div class="rbfw-p-relative">
                                            <span class="clock"><i class="fa-regular fa-clock"></i></span>
                                            <select class="rbfw-select rbfw-time-price dropoff_time" name="rbfw_pickup_end_time" id="dropoff_time" required>
                                                <option value="" disabled selected><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_time', 'rbfw_basic_translation_settings', __('Return time','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                                <?php foreach ($availabe_time as $key => $time) : ?>
                                                    <option value="<?php echo esc_attr($time); ?>"><?php echo esc_html(gmdate(get_option('time_format'), strtotime($time))); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="input-picker-icon"></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="item rbfw-duration">
                            <div class="rbfw-single-right-heading">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration', 'rbfw_basic_translation_settings', __('Duration','booking-and-rental-manager-for-woocommerce'))); ?>
                            </div>
                            <div class="item-content"></div>
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
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?>
                            </div>
                            <div class="item-content rbfw-quantity">
                                <select class="rbfw-select" name="rbfw_item_quantity" id="rbfw_item_quantity">
                                    <option value="0"><?php rbfw_string('rbfw_text_choose_number_of_qty',__('Choose number of quantity','booking-and-rental-manager-for-woocommerce')); ?></option>
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
                                                    <option value=""><?php echo esc_html(rbfw_string('rbfw_text_choose',__('Choose','booking-and-rental-manager-for-woocommerce')).' '.$data_arr_one['field_label']); ?></option>
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
                                                                           data-service_price_type="<?php echo esc_attr(isset($service['service_price_type']) && $service['service_price_type']); ?>"
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
                                                                <?php echo esc_html($service['title']); ?>
                                                                <i class="available-stock item_<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <?php esc_html_e('Available Qty ', 'booking-and-rental-manager-for-woocommerce'); ?><span class="remaining_stock"></span>
                                                                </i>
                                                            </div>
                                                        </td>
                                                        <td class="w_20">
                                                            <div class="title"><?php echo wp_kses(wc_price($service['price']),rbfw_allowed_html()); ?></div>
                                                            <span class="day-time-wise"><?php echo (isset($service['service_price_type'] ) && $service['service_price_type'] === 'day_wise') ? esc_html__('Day Wise', 'booking-and-rental-manager-for-woocommerce') : esc_html__('One Time', 'booking-and-rental-manager-for-woocommerce'); ?></span>
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
                                <?php esc_html_e('Additional Services You may like.','booking-and-rental-manager-for-woocommerce'); ?>
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
                                                        Sold Out
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
                                                    <i class="resource-qty"><?php esc_html_e('Available Qty ','booking-and-rental-manager-for-woocommerce') ?><span class="es_stock"><?php echo '('.esc_html($extra['service_qty']).')'; ?></span></i>
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
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                                <span class="price-figure" data-price="">
                                    </span>
                            </li>
                            <li class="resource-costing rbfw-cond">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                                <span class="price-figure" data-price="">
                                    </span>
                            </li>
                            <li class="subtotal">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce'))); ?>
                                <span class="price-figure" data-price="">
                                    </span>
                            </li>


                            <li class="discount" style="display:none;">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'))); ?>
                                <span></span>
                            </li>
                            <li class="security_deposit" style="display:none;">

                                <?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>

                                <span></span>
                            </li>
                            <li class="total">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce'))); ?>
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

                ?>

                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>

                <input type="hidden" name="rbfw_service_price" id="rbfw_service_price"  value="0">
                <input type="hidden" name="rbfw_es_service_price" id="rbfw_es_service_price"  value="0">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="bike_car_md">
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">
                <input type="hidden" name="rbfw_enable_variations" id="rbfw_enable_variations"  value="<?php echo esc_attr($rbfw_enable_variations); ?>">
                <input type="hidden" name="rbfw_input_stock_quantity" id="rbfw_input_stock_quantity"  value="<?php echo esc_attr($input_stock_quantity); ?>">
                <input type="hidden" name="rbfw_enable_time_slot" id="rbfw_enable_time_slot"  value="<?php echo esc_attr($time_picker); ?>">
                <input type="hidden" name="total_days" value="0">
                <input type="hidden" id="rbfw_minimum_booking_day" value="<?php echo esc_attr($rbfw_minimum_booking_day); ?>">
                <input type="hidden" id="rbfw_maximum_booking_day" value="<?php echo esc_attr($rbfw_maximum_booking_day); ?>">




                <?php if(rbfw_chk_regf_fields_exist($rbfw_id) === true){ ?>
                    <div class="item">
                        <div class="rbfw_reg_form_rb" style="display: none">
                            <?php
                            $reg_form = new Rbfw_Reg_Form();
                            echo wp_kses($reg_form->rbfw_generate_regf_fields($post_id),rbfw_allowed_html());
                            ?>
                        </div>

                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>

                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="rbfw_mps_book_now_btn_regf_____ mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn"  disabled >
                            <?php rbfw_string('rbfw_text_book_now',__('Book Now','booking-and-rental-manager-for-woocommerce')); ?>
                        </button>
                    </div>
                <?php } else{ ?>
                    <div class="item">
                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn" disabled <?php if( $rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date ) { echo 'style="display:none"'; }?>>
                            <?php rbfw_string('rbfw_text_book_now',__('Book Now','booking-and-rental-manager-for-woocommerce')); ?>
                        </button>
                    </div>

                <?php } ?>

                <?php if($rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date) {
                    echo '<div class="mps_alert_warning">'.esc_html(rbfw_string_return('rbfw_text_booking_expired',__('Booking Time Expired!','booking-and-rental-manager-for-woocommerce'))).'</div>';
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


