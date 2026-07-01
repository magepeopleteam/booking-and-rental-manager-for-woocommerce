<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

    $rbfw_id = $post_id ??0;
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

    $rbfw_minimum_booking_day = 0;
    $rbfw_maximum_booking_day = 0;
    if(rbfw_check_min_max_booking_day_active()){
        $rbfw_minimum_booking_day = (int)get_post_meta($post_id, 'rbfw_minimum_booking_day', true);
        if(get_post_meta($post_id, 'rbfw_maximum_booking_day', true)){
            $rbfw_maximum_booking_day = '+'.get_post_meta($post_id, 'rbfw_maximum_booking_day', true).'d';
        }
    }
    $rbfw_enable_security_deposit = get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) ? get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) : 'no';
    $rbfw_security_deposit_type = get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) : 'percentage';
    $rbfw_security_deposit_amount = get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) : 0;

?>
	<?php
	// Minimum price across all room types (daylong + daynight)
	$_rbfw_resort_prices = [];
	if ( ! empty( $rbfw_resort_room_data ) ) {
		foreach ( $rbfw_resort_room_data as $_room ) {
			if ( ! empty( $_room['rbfw_room_daylong_rate'] ) && (float) $_room['rbfw_room_daylong_rate'] > 0 ) {
				$_rbfw_resort_prices[] = (float) $_room['rbfw_room_daylong_rate'];
			}
			if ( ! empty( $_room['rbfw_room_daynight_rate'] ) && (float) $_room['rbfw_room_daynight_rate'] > 0 ) {
				$_rbfw_resort_prices[] = (float) $_room['rbfw_room_daynight_rate'];
			}
		}
	}
	$_rbfw_resort_min_price = ! empty( $_rbfw_resort_prices ) ? min( $_rbfw_resort_prices ) : 0;
	?>

	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">

		<div class="rbfw-single-right-container">
			<div class="rbfw-sd-rate-box">
				<?php rbfw_fd_summary_badges(); ?>
				<?php rbfw_fd_summary_title(); ?>
				<?php rbfw_fd_summary_desc(); ?>
				<?php if ( $_rbfw_resort_min_price > 0 ) : ?>
				<div class="rbfw-sd-rate-box-price-row">
					<span class="rbfw-sd-rate-box-label"><?php esc_html_e( 'Starting from', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<div class="rbfw-sd-rate-box-price">
						<?php echo wp_kses( wc_price( $_rbfw_resort_min_price ), rbfw_allowed_html() ); ?>
						<span class="rbfw-sd-rate-per">/ <?php esc_html_e( 'Night', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
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

			<form action="" method='post' class="mp_rbfw_ticket_form">
                <?php do_action('rbfw_discount_ad', $rbfw_id); ?>
                <div class="rbfw_resort_item_wrapper">
                    <div class="item pricing-content-container">
                        <?php do_action('rbfw_pricing_info_header'); ?>
                        <div class="price-item-container">
                            <span class="close-price-container"><i class="mi mi-x"></i></span>
                            <div class="mpStyle">
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
                                                            <td style="display: <?php ($rbfw_enable_resort_daylong_price == 'yes')?'block':'none'  ?>"><?php echo wp_kses(wc_price( $value['rbfw_room_daylong_rate'] ),rbfw_allowed_html()); ?></td>
                                                        <?php } ?>

                                                        <td><?php echo wp_kses(wc_price( $value['rbfw_room_daynight_rate'] ),rbfw_allowed_html()); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            endforeach;
                                        endif;
                                        ?>

                                        </tbody>
                                    </table>
                                </div>
                                <?php

                                if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php') || is_plugin_active('multi-day-price-saver-addon-for-wprently/additional-day-price.php') || is_plugin_active('tiered-pricing-addon-wprently/tiered-pricing-addon.php') ) {

                                    $rbfw_resort_data_mds = get_post_meta($post_id, 'rbfw_resort_data_mds', true) ? get_post_meta($post_id, 'rbfw_resort_data_mds', true) : [];
                                    $rbfw_resort_data_sp = get_post_meta($post_id, 'rbfw_resort_data_sp', true) ? get_post_meta($post_id, 'rbfw_resort_data_sp', true) : [];
                                    $rbfw_resort_data_tp = get_post_meta($post_id, 'rbfw_resort_data_tp', true) ? get_post_meta($post_id, 'rbfw_resort_data_tp', true) : [];



                                    if(is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' ) && !empty($rbfw_resort_data_mds)){
                                        ?>

                                        <?php foreach ($rbfw_resort_data_mds as $mds_single){ ?>
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td colspan="2"><?php esc_html_e('From','booking-and-rental-manager-for-woocommerce'); ?> <strong><?php echo esc_html($mds_single['start_day']) ?></strong> <?php esc_html_e('Days','booking-and-rental-manager-for-woocommerce'); ?> </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="rbfw_day_wise_price">
                                                            <table>
                                                                <tbody>
                                                                <?php foreach ($mds_single['room_price'] as $room_price){  ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php echo esc_html($room_price['room_type']) ?>
                                                                        </td>
                                                                        <td style="display: <?php ($rbfw_enable_resort_daylong_price == 'yes')?'block':'none'  ?>">
                                                                            <?php echo wc_price($room_price['day_long_price']) ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php echo wc_price($room_price['price']) ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        <?php } ?>
                                        <?php
                                    }elseif(is_plugin_active( 'tiered-pricing-addon-wprently/tiered-pricing-addon.php') && !empty($rbfw_resort_data_tp)){
                                        ?>
                                        <?php foreach ($rbfw_resort_data_tp as $tp_single){ ?>
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td>From <strong><?php echo esc_html($tp_single['start_day']) ?></strong> To  <strong><?php echo esc_html($tp_single['end_day']) ?></strong> </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="rbfw_day_wise_price">
                                                            <table>
                                                                <tbody>
                                                                <?php foreach ($tp_single['room_price'] as $tp_single){  ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php echo esc_html($tp_single['room_type']) ?>
                                                                        </td>
                                                                        <td style="display: <?php echo ($rbfw_enable_resort_daylong_price == 'yes')?'block':'none'  ?>">
                                                                            <?php echo wc_price($tp_single['day_long_price']) ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php echo wc_price($tp_single['price']) ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        <?php } ?>
                                        <?php
                                    }elseif(is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php') && !empty($rbfw_resort_data_sp)){
                                        ?>
                                        <?php foreach ($rbfw_resort_data_sp as $sp_single){ ?>
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td>From <strong><?php echo esc_html( rbfw_date_format($sp_single['start_date'])) ?></strong> To  <strong><?php echo esc_html( rbfw_date_format($sp_single['end_date'])) ?></strong> </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="rbfw_day_wise_price">
                                                            <table>
                                                                <tbody>
                                                                <?php foreach ($sp_single['room_price'] as $room_price){  ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php echo esc_html($room_price['room_type']) ?>
                                                                        </td>
                                                                        <td style="display: <?php ($rbfw_enable_resort_daylong_price == 'yes')?'block':'none'  ?>">
                                                                            <?php echo wc_price($room_price['day_long_price']) ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php echo wc_price($room_price['price']) ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        <?php } ?>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>


                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_attr($post_id); ?>">
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_attr(rbfw_off_days($post_id)); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo esc_attr(rbfw_off_dates($post_id)); ?>'>
                <input type="hidden" id="rbfw_minimum_booking_day" value="<?php echo esc_attr($rbfw_minimum_booking_day); ?>">
                <input type="hidden" id="rbfw_maximum_booking_day" value="<?php echo esc_attr($rbfw_maximum_booking_day); ?>">

                <input type="hidden" name="rbfw_security_deposit_enable" id="rbfw_security_deposit_enable"  value="<?php echo esc_attr($rbfw_enable_security_deposit); ?>">
                <input type="hidden" name="rbfw_security_deposit_type" id="rbfw_security_deposit_type"  value="<?php echo esc_attr($rbfw_security_deposit_type); ?>">
                <input type="hidden" name="rbfw_security_deposit_amount" id="rbfw_security_deposit_amount"  value="<?php echo esc_attr($rbfw_security_deposit_amount); ?>">



                <?php do_action('rbfw_ticket_feature_info'); ?>
                <div class="item rbfw-checkin-checkout-card">
                    <div class="rbfw-single-right-heading mb-08">
                        <span><?php esc_html_e('Check-In & Check-Out Date','booking-and-rental-manager-for-woocommerce'); ?></span>
                        <i class="fas fa-calendar-alt rbfw-srh-cal-icon"></i>
                    </div>
                    <div class="item-content rbfw-datetime">
                        <div class="left date">
                            <span class="calendar"><i class="fas fa-calendar-alt"></i></span>
                            <input type="hidden" name="rbfw_start_datetime" id="hidden_checkin_date">
                            <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_start" id="checkin_date" placeholder="<?php esc_attr_e('Check-In Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly>
                            <button type="button" class="rbfw-date-clear-btn" data-clears="checkin_date" aria-label="<?php esc_attr_e('Clear check-in date','booking-and-rental-manager-for-woocommerce'); ?>"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="right date">
                            <span class="calendar"><i class="fas fa-calendar-alt"></i></span>
                            <input type="hidden" name="rbfw_end_datetime" id="hidden_checkout_date">
                            <input class="rbfw-input rbfw-time-price" type="text" name="rbfw_end" id="checkout_date" placeholder="<?php esc_attr_e('Check-Out Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly>
                            <button type="button" class="rbfw-date-clear-btn" data-clears="checkout_date" aria-label="<?php esc_attr_e('Clear check-out date','booking-and-rental-manager-for-woocommerce'); ?>"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>

                <div class="item">
                    <a class="rbfw_chk_availability_btn rbfw-avail-btn-disabled"
                       title="<?php esc_attr_e('Please select check-in and check-out dates','booking-and-rental-manager-for-woocommerce'); ?>">
                        <?php esc_html_e('Check Availability','booking-and-rental-manager-for-woocommerce'); ?>
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
				<input type="hidden" name="rbfw_enable_resort_daylong_price" id="rbfw_enable_resort_daylong_price"  value="<?php echo esc_attr($rbfw_enable_resort_daylong_price); ?>">
			</form>
		</div>
    </div>

<script>
jQuery(function($){

    /* ---- Check Availability button: enable only when both dates picked ---- */
    var $availBtn     = $('.rbfw_chk_availability_btn');
    var disabledTitle = $availBtn.attr('title');

    function rbfwUpdateAvailBtn() {
        var hasCheckin  = $('#hidden_checkin_date').val();
        var hasCheckout = $('#hidden_checkout_date').val();
        if (hasCheckin && hasCheckout) {
            $availBtn.removeClass('rbfw-avail-btn-disabled').removeAttr('title');
        } else {
            $availBtn.addClass('rbfw-avail-btn-disabled').attr('title', disabledTitle);
        }
    }

    /* ---- Clear buttons: show/hide X when date field has a value ---- */
    function rbfwSyncDateClear() {
        $('[data-clears="checkin_date"]').toggleClass('rbfw-date-clear-visible',  !!$('#hidden_checkin_date').val());
        $('[data-clears="checkout_date"]').toggleClass('rbfw-date-clear-visible', !!$('#hidden_checkout_date').val());
    }

    /* resort_script.js triggers change on hidden inputs inside datepicker onSelect */
    $(document).on('change', '#hidden_checkin_date, #hidden_checkout_date', function() {
        rbfwUpdateAvailBtn();
        rbfwSyncDateClear();
    });

    /* Clear button click: wipe visible + hidden inputs, re-evaluate state */
    var rbfwHiddenMap = { 'checkin_date': 'hidden_checkin_date', 'checkout_date': 'hidden_checkout_date' };
    $(document).on('click', '.rbfw-date-clear-btn', function(e) {
        e.preventDefault();
        var visibleId = $(this).data('clears');
        var hiddenId  = rbfwHiddenMap[visibleId];
        $('#' + visibleId).val('');
        if (hiddenId) { $('#' + hiddenId).val('').trigger('change'); }
    });

    /* ---- Book Now button: enable only when at least one qty > 0 ---- */
    function rbfwUpdateBookNowBtn() {
        var hasQty = false;
        $('.rbfw_room_qty, .rbfw_service_qty_resort').each(function() {
            if (parseInt($(this).val()) > 0) {
                hasQty = true;
                return false; /* break each */
            }
        });
        var $bookBtn = $('button.rbfw_resort_book_now_btn');
        if (hasQty) {
            $bookBtn.prop('disabled', false).removeClass('rbfw_disabled_button');
        } else {
            $bookBtn.prop('disabled', true).addClass('rbfw_disabled_button');
        }
    }

    /* Delegated — tables are AJAX-loaded after page ready */
    $(document).on('input', '.rbfw_room_qty, .rbfw_service_qty_resort', rbfwUpdateBookNowBtn);
});
</script>



