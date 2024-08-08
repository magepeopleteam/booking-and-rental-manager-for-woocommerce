<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
$rbfw_id = $post_id ? $post_id : get_the_ID();

$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;
$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'no';
$rbfw_enable_daywise_price = get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) : 'no';

$availabe_time = rbfw_get_available_times($rbfw_id);
$off_dates_list = get_post_meta($rbfw_id, 'rbfw_off_dates', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_off_dates', true)) : [];

$location_switch = !empty(get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true)) ? get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true) : '';
$pickup_location = get_post_meta($rbfw_id, 'rbfw_pickup_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_pickup_data', true)) : [];
$dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

$extra_service_list = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];

// sunday rate
$hourly_rate_sun = get_post_meta($rbfw_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sun_hourly_rate', true) : 0;
$daily_rate_sun = get_post_meta($rbfw_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sun_daily_rate', true) : 0;
$enabled_sun = get_post_meta($rbfw_id, 'rbfw_enable_sun_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_sun_day', true) : 'yes';

// monday rate
$hourly_rate_mon = get_post_meta($rbfw_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_mon_hourly_rate', true) : 0;
$daily_rate_mon = get_post_meta($rbfw_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_mon_daily_rate', true) : 0;
$enabled_mon = get_post_meta($rbfw_id, 'rbfw_enable_mon_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_mon_day', true) : 'yes';

// tuesday rate
$hourly_rate_tue = get_post_meta($rbfw_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_tue_hourly_rate', true) : 0;
$daily_rate_tue = get_post_meta($rbfw_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_tue_daily_rate', true) : 0;
$enabled_tue = get_post_meta($rbfw_id, 'rbfw_enable_tue_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_tue_day', true) : 'yes';

// wednesday rate
$hourly_rate_wed = get_post_meta($rbfw_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_wed_hourly_rate', true) : 0;
$daily_rate_wed = get_post_meta($rbfw_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_wed_daily_rate', true) : 0;
$enabled_wed = get_post_meta($rbfw_id, 'rbfw_enable_wed_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_wed_day', true) : 'yes';

// thursday rate
$hourly_rate_thu = get_post_meta($rbfw_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_thu_hourly_rate', true) : 0;
$daily_rate_thu = get_post_meta($rbfw_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_thu_daily_rate', true) : 0;
$enabled_thu = get_post_meta($rbfw_id, 'rbfw_enable_thu_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_thu_day', true) : 'yes';

// friday rate
$hourly_rate_fri = get_post_meta($rbfw_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_fri_hourly_rate', true) : 0;
$daily_rate_fri = get_post_meta($rbfw_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_fri_daily_rate', true) : 0;
$enabled_fri = get_post_meta($rbfw_id, 'rbfw_enable_fri_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_fri_day', true) : 'yes';

// saturday rate
$hourly_rate_sat = get_post_meta($rbfw_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sat_hourly_rate', true) : 0;
$daily_rate_sat = get_post_meta($rbfw_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sat_daily_rate', true) : 0;
$enabled_sat = get_post_meta($rbfw_id, 'rbfw_enable_sat_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_sat_day', true) : 'yes';

$enable_service_price =  get_post_meta($post_id, 'rbfw_enable_category_service_price', true) ? get_post_meta($post_id, 'rbfw_enable_category_service_price', true) : 'off';

$current_day = date_i18n('D');

if($current_day == 'Sun' && $enabled_sun == 'yes'){
    $hourly_rate = $hourly_rate_sun;
    $daily_rate = $daily_rate_sun;
}elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
    $hourly_rate = $hourly_rate_mon;
    $daily_rate = $daily_rate_mon;
}elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
    $hourly_rate = $hourly_rate_tue;
    $daily_rate = $daily_rate_tue;
}elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
    $hourly_rate = $hourly_rate_wed;
    $daily_rate = $daily_rate_wed;
}elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
    $hourly_rate = $hourly_rate_thu;
    $daily_rate = $daily_rate_thu;
}elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
    $hourly_rate = $hourly_rate_fri;
    $daily_rate = $daily_rate_fri;
}elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
    $hourly_rate = $hourly_rate_sat;
    $daily_rate = $daily_rate_sat;
}else{
    $hourly_rate = $hourly_rate;
    $daily_rate = $daily_rate;
}

$current_date = date_i18n('Y-m-d');
$rbfw_sp_prices = get_post_meta( $rbfw_id, 'rbfw_seasonal_prices', true );
if(!empty($rbfw_sp_prices)){
    $sp_array = [];
    $i = 0;
    foreach ($rbfw_sp_prices as $value) {
        $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
        $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
        $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
        $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
        $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
        $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
        $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
        $i++;
    }

    foreach ($sp_array as $sp_arr) {
        if (in_array($current_date,$sp_arr['sp_dates'])){
            $hourly_rate = $sp_arr['sp_hourly_rate'];
            $daily_rate  = $sp_arr['sp_daily_rate'];
        }
    }
}

global $rbfw;
$currency_symbol = rbfw_mps_currency_symbol();
$rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
if($rbfw_payment_system == 'mps'){
    $rbfw_payment_system = 'mps_enabled';
}else{
    $rbfw_payment_system = 'wps_enabled';
}

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
	$rbfw_event_start_time  = date('h:i a', strtotime($rbfw_event_start_time));
	$rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
	$rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
	$rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
	$rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
    $rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));

	$expire = 'no';
	if($rbfw_enable_start_end_date=='no'){
		if($rbfw_event_last_date<$rbfw_todays_date){
			$expire = 'yes';
		}
	}

?>

<?php if($expire == 'no'){ ?>
    <div class="rbfw-single-container" data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">
        <div class="rbfw-single-right-container">
            <form action="" method='post' class="mp_rbfw_ticket_form">
                <div class="rbfw_bike_car_md_item_wrapper">
                    <div class="rbfw_bike_car_md_item_wrapper_inner">
                        <?php do_action('rbfw_discount_ad', $rbfw_id); ?>
                        <div class="item">
                            <div class="item-content pricing-content">
                                <div class="section-header">
                                    <div class="rbfw-single-right-heading rbfw_pricing_info_heading">
                                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pricing_info', 'rbfw_basic_translation_settings', __('Pricing Info','booking-and-rental-manager-for-woocommerce'))); ?>
                                    </div>
                                </div>
                                <?php $rbfw_pricing_info_display = rbfw_get_option('rbfw_pricing_info_display','rbfw_basic_gen_settings'); ?>
                                <div class="price-item-container <?php echo ($rbfw_pricing_info_display=='yes')?'open':'' ?>" style="display: <?php echo ($rbfw_pricing_info_display=='yes')?'block':'none' ?>">
                                    <?php if (($enable_daily_rate == 'yes' || $rbfw_enable_daywise_price == 'yes') && !empty($daily_rate)) : ?>
                                        <div class="price-type">
                                            <p><?php echo esc_html($rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily Rate','booking-and-rental-manager-for-woocommerce'))); ?>:</p>
                                            <p><?php echo rbfw_mps_price($daily_rate); ?> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_day', 'rbfw_basic_translation_settings', __('day','booking-and-rental-manager-for-woocommerce'))); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (($enable_hourly_rate == 'yes'  || $rbfw_enable_daywise_price == 'yes') && !empty($hourly_rate)) : ?>
                                        <div class="price-type">
                                            <p><?php echo esc_html($rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly Rate','booking-and-rental-manager-for-woocommerce'))); ?>:</p>
                                            <p><?php echo rbfw_mps_price($hourly_rate); ?> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_hour', 'rbfw_basic_translation_settings', __('hour','booking-and-rental-manager-for-woocommerce'))); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                            <div class="item">
                                <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_location', 'rbfw_basic_translation_settings', __('Pickup Location','booking-and-rental-manager-for-woocommerce'))); ?></div>
                                <div class="item-content rbfw-location">
                                    <select class="rbfw-select" name="rbfw_pickup_point" required>
                                        <option value=""><?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_pickup_location', 'rbfw_basic_translation_settings', __('Choose pickup location','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                        <?php foreach ($pickup_location as $pickup) : ?>
                                            <option value="<?php echo mep_esc_html($pickup['loc_pickup_name']); ?>"><?php echo mep_esc_html($pickup['loc_pickup_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($location_switch == 'yes' && !empty($dropoff_location)) : ?>
                            <div class="item">
                                <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_location', 'rbfw_basic_translation_settings', __('Drop-off Location','booking-and-rental-manager-for-woocommerce'))); ?></div>
                                <div class="item-content rbfw-location">
                                    <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                        <option value=""><?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_dropoff_location', 'rbfw_basic_translation_settings', __('Choose drop-off location','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                        <?php foreach ($dropoff_location as $dropoff) : ?>
                                            <option value="<?php echo mep_esc_html($dropoff['loc_dropoff_name']); ?>"><?php echo mep_esc_html($dropoff['loc_dropoff_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>


                        <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo rbfw_off_days($post_id); ?>'>
                        <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo rbfw_off_dates($post_id); ?>'>

                        <?php if($rbfw_enable_start_end_date == 'yes'){ ?>
                            <div class="item">
                                <div class="item-content rbfw-datetime">
                                    <div class="<?php echo ($enable_hourly_rate == 'yes' && !empty($availabe_time))?'left':'' ?> date">
                                        <div class="rbfw-single-right-heading">
                                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings', __('Pickup Date','booking-and-rental-manager-for-woocommerce'))); ?>
                                        </div>
                                        <div class="rbfw-p-relative">
                                            <span class="calendar"><i class="fa-solid fa-calendar-days"></i></span>
                                            <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_pickup_start_date" id="pickup_date" placeholder="<?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date', 'rbfw_basic_translation_settings', __('Pickup date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        </div>
                                    </div>
                                    <?php if($enable_hourly_rate == 'yes' && !empty($availabe_time)){ ?>
                                        <div class="right time">
                                            <div class="rbfw-single-right-heading">
                                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings', __('Pickup Time','booking-and-rental-manager-for-woocommerce'))); ?>
                                            </div>
                                            <div class="rbfw-p-relative">
                                                    <span class="clock">
                                                        <i class="fa-regular fa-clock"></i>
                                                    </span>
                                                <select class="rbfw-select rbfw-time-price" name="rbfw_pickup_start_time" id="pickup_time" required>
                                                    <option value="" disabled selected><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_time', 'rbfw_basic_translation_settings', __('Pickup time','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                                    <?php foreach ($availabe_time as $key => $time) : ?>
                                                        <option value="<?php echo mep_esc_html($key); ?>"><?php echo mep_esc_html($time); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>


                            <div class="item">
                                <div class="item-content rbfw-datetime">
                                    <div class="<?php if($enable_hourly_rate == 'yes' && !empty($availabe_time)){ echo 'left'; }?> date">
                                        <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_date', 'rbfw_basic_translation_settings', __('Return Date','booking-and-rental-manager-for-woocommerce'))); ?></div>
                                        <div class="rbfw-p-relative">
                                            <span class="calendar"><i class="fa-solid fa-calendar-days"></i></span>
                                            <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_pickup_end_date" id="dropoff_date" placeholder="<?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_date', 'rbfw_basic_translation_settings', __('Return date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        </div>
                                    </div>
                                    <?php if($enable_hourly_rate == 'yes' && !empty($availabe_time)){ ?>
                                        <input name="rbfw_available_time"  id="rbfw_available_time" value="yes" type="hidden">
                                        <div class="right time">
                                            <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_time', 'rbfw_basic_translation_settings', __('Return Time','booking-and-rental-manager-for-woocommerce'))); ?></div>
                                            <div class="rbfw-p-relative">
                                                <span class="clock"><i class="fa-regular fa-clock"></i></span>
                                                <select class="rbfw-select rbfw-time-price" name="rbfw_pickup_end_time" id="dropoff_time" required>
                                                    <option value="" disabled selected><?php echo esc_html($rbfw->get_option_trans('rbfw_text_return_time', 'rbfw_basic_translation_settings', __('Return time','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                                    <?php foreach ($availabe_time as $key => $time) : ?>
                                                        <option value="<?php echo mep_esc_html($key); ?>"><?php echo mep_esc_html($time); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
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
                            <input type="hidden"  name="rbfw_pickup_start_date" id="pickup_date" value="<?php echo $rbfw_event_start_date; ?>"/>
                            <input type="hidden"  name="rbfw_pickup_start_time" id="pickup_time" value="<?php echo $rbfw_event_start_time; ?>"/>
                            <input type="hidden"  name="rbfw_pickup_end_date" id="dropoff_date" value="<?php echo $rbfw_event_end_date; ?>"/>
                            <input type="hidden"  name="rbfw_pickup_end_time" id="dropoff_time" value="<?php echo $rbfw_event_end_time; ?>"/>
                        <?php } ?>


                        <?php if ($rbfw_enable_md_type_item_qty == 'yes' && $item_stock_quantity > 0) { ?>
                            <div class="item">
                                <div class="rbfw-single-right-heading">
                                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?>
                                </div>
                                <div class="item-content rbfw-quantity">
                                    <select class="rbfw-select" name="rbfw_item_quantity" id="rbfw_item_quantity">
                                        <option value="0"><?php rbfw_string('rbfw_text_choose_number_of_qty',__('Choose number of quantity','booking-and-rental-manager-for-woocommerce')); ?></option>
                                        <?php for ($qty = 1; $qty <= $item_stock_quantity; $qty++) { ?>
                                            <option value="<?php echo mep_esc_html($qty); ?>" <?php if($qty == 1){ echo 'selected'; } ?>><?php echo mep_esc_html($qty); ?></option>
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
                                    $selected_value = !empty($data_arr_one['selected_value']) ? $data_arr_one['selected_value'] : '';
                                    ?>
                                    <div class="item">
                                        <div class="rbfw-single-right-heading"><?php echo esc_html($data_arr_one['field_label']); ?></div>
                                        <div class="item-content rbfw-p-relative">
                                            <?php if(!empty($data_arr_one['value'])){  ?>
                                                <select class="rbfw-select rbfw_variation_field" name="<?php echo esc_attr($data_arr_one['field_id']); ?>" id="<?php echo esc_attr($data_arr_one['field_id']); ?>" data-field="<?php echo esc_attr($data_arr_one['field_label']); ?>">
                                                    <?php if(empty($selected_value)){ ?>
                                                        <option value=""><?php echo rbfw_string('rbfw_text_choose',__('Choose','booking-and-rental-manager-for-woocommerce')).' '.$data_arr_one['field_label']; ?></option>
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

                        <?php if(!empty($option_value) && ($enable_service_price=='on')){  ?>

                            <div class="multi-service-category-section">
                                <div class="rbfw-single-right-heading">
                                    <?php esc_html_e( 'Category wise service price', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </div>
                                <?php foreach ($option_value as $cat=>$item){ ?>
                                    <?php if($item['cat_title']){ ?>
                                        <div class="servise-item">
                                            <p>
                                                <?php echo $item['cat_title'] ?>
                                            </p>
                                            <input type="hidden" name="rbfw_service_price_data[<?php echo $cat ?>][cat_title]" value="<?php echo $item['cat_title'] ?>">
                                            <?php foreach ($item['cat_services'] as $serkey=>$service){ ?>
                                                <?php if($service['title']){ ?>
                                                    <div class="service-price-item" style="display: flex;gap:20px">
                                                        <div style="display: none" class="rbfw-sold-out">
                                                            Sold Out
                                                        </div>

                                                        <div class="rbfw-checkbox">
                                                            <label class="switch">
                                                                <input type="checkbox" class="rbfw_service_price_data item_<?php echo $cat.$serkey ?>" name="rbfw_service_price_data[<?php echo $cat ?>][<?php echo $serkey ?>][main_cat_name]" data-service_price_type="<?php echo $service['service_price_type'] ?>" data-price="<?php echo $service['price'] ?>" data-quantity="1" data-rbfw_enable_md_type_item_qty="<?php echo $rbfw_enable_extra_service_qty ?>" data-item="<?php echo $cat.$serkey ?>">
                                                                <span class="slider round"></span>
                                                            </label>
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo $cat ?>][<?php echo $serkey ?>][name]" value="<?php echo $service['title'] ?>">
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo $cat ?>][<?php echo $serkey ?>][service_price_type]" value="<?php echo $service['service_price_type'] ?>">
                                                            <input type="hidden" name="rbfw_service_price_data[<?php echo $cat ?>][<?php echo $serkey ?>][price]" value="<?php echo $service['price'] ?>">
                                                        </div>

                                                        <div class="title">
                                                            <?php echo $service['title'] ?> <span class="remaining_stock"></span>
                                                        </div>



                                                        <div  class="rbfw_qty_input rbfw_service_quantity item_<?php echo $cat.$serkey ?>" style="display: none">
                                                            <a class="rbfw_service_quantity_minus" data-item="<?php echo $cat.$serkey ?>"><i class="fa-solid fa-minus"></i></a>
                                                            <input type="number"  name="rbfw_service_price_data[<?php echo $cat ?>][<?php echo $serkey ?>][quantity]" min="0" max="" value="1" class="rbfw_service_qty rbfw_service_info_stock" data-cat="service" data-price="20" data-item="<?php echo $cat.$serkey ?>" data-name="ddd" autocomplete="off">
                                                            <a class="rbfw_service_quantity_plus" data-item="<?php echo $cat.$serkey ?>"><i class="fa-solid fa-plus"></i></a>
                                                        </div>

                                                        <div class="title"><?php echo wc_price($service['price']) ?></div>

                                                        <div class="title"><?php echo ($service['service_price_type']=='day_wise')?esc_html__('Day Wise','booking-and-rental-manager-for-woocommerce'):esc_html__('One Time','booking-and-rental-manager-for-woocommerce') ?></div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        <?php } ?>


                        <?php if(!empty($extra_service_list)){ ?>

                            <div class="item">
                                <div class="rbfw-single-right-heading">
                                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_resources', 'rbfw_basic_translation_settings', __('Resources','booking-and-rental-manager-for-woocommerce'))); ?>
                                </div>
                                <div class="item-content rbfw-resource">
                                    <table class="rbfw_bikecarmd_es_table">
                                        <tbody>
                                        <?php
                                        $c = 0;
                                        foreach ($extra_service_list as $key=>$extra) { ?>
                                            <?php if($extra['service_qty'] > 0){ ?>
                                                <tr>
                                                    <td class="w_20 rbfw_bikecarmd_es_hidden_input_box">
                                                        <div class="label">
                                                            <input type="hidden" name="rbfw_service_info[<?php echo $c; ?>][service_name]" value="<?php echo mep_esc_html($extra['service_name']); ?>">
                                                            <input type="hidden" name="rbfw_service_info[<?php echo $c; ?>][service_qty]" class="rbfw-resource-qty" value="">
                                                            <input type="hidden" name="rbfw_service_info[<?php echo $c; ?>][service_price]"  value="<?php echo $extra['service_price']; ?>">
                                                            <label class="switch">
                                                                <input type="checkbox" max="4"  class="rbfw-resource-price rbfw-resource-price-multiple-qty key_value_<?php echo $key+1 ?>" data-status="0" value="1" data-cat="service"  data-price="<?php echo $extra['service_price']; ?>" data-name="<?php echo mep_esc_html($extra['service_name']); ?>">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td class="resource-title-qty">
                                                        <?php echo mep_esc_html($extra['service_name']); ?>
                                                        <i class="resource-qty"><?php _e('Available Qty ','booking-and-rental-manager-for-woocommerce') ?><span class="es_stock"><?php echo esc_html('('.$extra['service_qty'].')'); ?></span></i>
                                                    </td>
                                                    <td class="w_20"><?php echo rbfw_mps_price($extra['service_price']); ?></td>
                                                    <?php if($rbfw_enable_extra_service_qty == 'yes'){ ?>
                                                        <td class="rbfw_bikecarmd_es_input_box" style="display:none">
                                                            <div class="rbfw_qty_input">
                                                                <a class="rbfw_qty_minus rbfw_bikecarmd_es_qty_minus"><i class="fa-solid fa-minus"></i></a>
                                                                <input type="number" min="0" max="" value="1" class="rbfw_bikecarmd_es_qty"  data-cat="service" data-price="<?php echo $extra['service_price']; ?>" data-name="<?php echo mep_esc_html($extra['service_name']); ?>"/>
                                                                <a class="rbfw_qty_plus rbfw_bikecarmd_es_qty_plus" data-key_value="<?php echo $key+1 ?>"><i class="fa-solid fa-plus"></i></a>
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


                    <div class="rbfw_bikecarmd_price_result">
                        <div class="item-content rbfw-costing">
                            <ul class="rbfw-ul">
                                <li class="duration-costing rbfw-cond">
                                    <?php echo $rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')) ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>
                                <li class="resource-costing rbfw-cond">
                                    <?php echo $rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')) ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>
                                <li class="subtotal">
                                    <?php echo $rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')) ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>


                                <li class="discount" style="display:none;">
                                    <?php echo $rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')) ?>

                                    <span></span>
                                </li>
                                <li class="security_deposit" style="display:none;">

                                    <?php echo (!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit'); ?>

                                    <span></span>
                                </li>
                                <li class="total">
                                    <?php echo $rbfw->get_option_trans('rbfw_text_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce')) ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>
                            </ul>
                            <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                        </div>
                    </div>

                    <?php if(rbfw_chk_regf_fields_exist($rbfw_id) === true){ ?>
                        <div class="item">
                            <div class="rbfw_reg_form_rb" style="display: none">
                                <?php
                                $reg_form = new Rbfw_Reg_Form();
                                echo $reg_form->rbfw_generate_regf_fields($post_id);
                                ?>
                            </div>

                            <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>

                            <button type="submit" name="add-to-cart" value="<?php echo mep_esc_html($rbfw_product_id); ?>" class="rbfw_mps_book_now_btn_regf_____ mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn <?php echo esc_attr($rbfw_payment_system); ?>" disabled >
                                <?php rbfw_string('rbfw_text_book_now',__('Book Now','booking-and-rental-manager-for-woocommerce')); ?>
                            </button>
                        </div>
                    <?php } else{ ?>
                        <div class="item">
                            <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                            <button type="submit" name="add-to-cart" value="<?php echo mep_esc_html($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn <?php echo esc_attr($rbfw_payment_system); ?>" disabled <?php if( $rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date ) { echo 'style="display:none"'; }?>>
                                <?php rbfw_string('rbfw_text_book_now',__('Book Now','booking-and-rental-manager-for-woocommerce')); ?>
                            </button>
                        </div>

                    <?php } ?>

                    <?php if($rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date) {
                        echo '<div class="mps_alert_warning">'.rbfw_string_return('rbfw_text_booking_expired',__('Booking Time Expired!','booking-and-rental-manager-for-woocommerce')).'</div>';
                    } ?>
                </div>

                <div class="rbfw-bikecarmd-result-wrap">
                    <div class="rbfw-bikecarmd-result-loader">
                    </div>
                    <div class="rbfw-bikecarmd-result">
                    </div>
                </div>
                <input type="hidden" name="rbfw_service_price" id="rbfw_service_price"  value="0">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="bike_car_md">
                <input type="hidden" id="rbfw_post_id"  value="<?php echo $rbfw_id; ?>">
                <input type="hidden" name="rbfw_enable_variations" id="rbfw_enable_variations"  value="<?php echo $rbfw_enable_variations; ?>">
                <input type="hidden" id="rbfw_input_stock_quantity" name="rbfw_input_stock_quantity" value="<?php echo $input_stock_quantity ?>">
                <input type="hidden" id="rbfw_enable_time_slot" name="rbfw_enable_time_slot" value="<?php echo !empty(get_post_meta($rbfw_id, 'rbfw_time_slot_switch', true)) ? get_post_meta($rbfw_id, 'rbfw_time_slot_switch', true) : 'on'; ?>">
                <input type="hidden" name="total_days" value="0">
                <input type="hidden" name="countable_time" value="0">
            </form>
        </div>
    </div>
<?php }else{ ?>
    <h3><?php esc_html_e( 'Date Expired !', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
<?php } ?>

