<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

    $rbfw_id = $post_id ??0;
    global $frontend;
    $frontend = $frontend??0;
	global $rbfw;

	$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
	$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;
	$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
	$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'yes';
	$time_format = get_post_meta($rbfw_id, 'rbfw_time_format', true) ? get_post_meta($rbfw_id, 'rbfw_time_format', true) : '12';
	$availabe_time = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];
	$off_dates_list = get_post_meta($rbfw_id, 'rbfw_off_dates', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_off_dates', true)) : [];

	$checkin_location = get_post_meta($rbfw_id, 'rbfw_checkin_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_checkin_data', true)) : [];
	$dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

	$extra_service_list = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];
	$rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
	$rbfw_enable_resort_daylong_price  = get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) : 'no';
    $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
?>
	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo esc_html($rbfw_id); ?>">

		<div class="rbfw-single-right-container">
			<form action="" method='post' class="mp_rbfw_ticket_form">
                <?php do_action('rbfw_discount_ad', $rbfw_id); ?>
                <div class="rbfw_resort_item_wrapper">
                    <div class="item">
                        <div class="item-content pricing-content">
                            <div class="section-header">
                                <div class="rbfw-single-right-heading rbfw_pricing_info_heading">
                                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pricing_info', 'rbfw_basic_translation_settings', __('Pricing Info','booking-and-rental-manager-for-woocommerce'))); ?>
                                </div>
                            </div>
                        </div>
                        <?php $rbfw_pricing_info_display = rbfw_get_option('rbfw_pricing_info_display','rbfw_basic_gen_settings'); ?>
                        <div class="price-item-container pricing-content_dh  mpStyle  <?php echo ($rbfw_pricing_info_display=='yes')?'open':'' ?>" style="display: <?php echo ($rbfw_pricing_info_display=='yes')?'block':'none' ?>">
                            <div class="rbfw_day_wise_price">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td><strong><?php rbfw_string('rbfw_text_room_type',__('Room Type','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                                        <td style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>"><?php rbfw_string('rbfw_text_daylong_price',__('Day-long price','booking-and-rental-manager-for-woocommerce')); ?></td>
                                        <td><strong><?php rbfw_string('rbfw_text_daynight_price',__('Day-night price','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                                    </tr>

                                    <?php
                                    if(! empty($rbfw_resort_room_data)) :
                                        $i = 0;
                                        foreach ($rbfw_resort_room_data as $key => $value):
                                            if(!empty($value['room_type'])){
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_attr($value['room_type']); ?></td>

                                                    <?php if(!empty($value['rbfw_room_daylong_rate'])){ ?>
                                                        <td style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><?php echo esc_html(rbfw_mps_price( $value['rbfw_room_daylong_rate'] )); ?></td>
                                                    <?php } ?>

                                                    <td><?php echo esc_html(rbfw_mps_price( $value['rbfw_room_daynight_rate'] )); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        endforeach;
                                    endif;
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_html($post_id); ?>">
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_html(rbfw_off_days($post_id)); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo esc_html(rbfw_off_dates($post_id)); ?>'>

                <div class="item">
                        <div class="rbfw-single-right-heading mb-08"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_checkin_checkout_date', 'rbfw_basic_translation_settings')); ?></div>
                        <div class="item-content rbfw-datetime">
                            <div class="left date">
                                <span class="calendar"><i class="fas fa-calendar-alt"></i></span>
                                <input type="hidden" name="rbfw_start_datetime" id="hidden_checkin_date">
                                <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_start" id="checkin_date" placeholder="<?php echo esc_html($rbfw->get_option_trans('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly>
                            </div>
                            <div class="right date">
                                <span class="calendar"><i class="fas fa-calendar-alt"></i></span>
                                <input type="hidden" name="rbfw_end_datetime" id="hidden_checkout_date">
                                <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_end" id="checkout_date" placeholder="<?php echo esc_html($rbfw->get_option_trans('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly>
                            </div>
                        </div>
                    </div>

                <div class="item">
                        <a class="rbfw_chk_availability_btn">
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_check_availability', 'rbfw_basic_translation_settings', __('Check Availability','booking-and-rental-manager-for-woocommerce'))); ?>
                        </a>
                    </div>
                <div class="rbfw-availability-loader">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="rbfw-availability-result">
                    <div class="rbfw_room_price_category_tabs"></div>
                    <div class="rbfw_room_price_category_details_loader"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="rbfw_room_price_category_details"></div>
                </div>

                <div class="rbfw-resort-result-wrap">
                    <div class="rbfw-resort-result-loader"></div>
                    <div class="rbfw-resort-result"></div>
                </div>
                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="resort">
				<input type="hidden" name="rbfw_enable_resort_daylong_price" id="rbfw_enable_resort_daylong_price"  value="<?php echo esc_html($rbfw_enable_resort_daylong_price); ?>">
			</form>
		</div>
    </div>



