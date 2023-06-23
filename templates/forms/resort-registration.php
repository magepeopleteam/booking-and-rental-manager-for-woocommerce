<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	global $rbfw;
	$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
	$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;
	$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
	$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'yes';
	$time_format = get_post_meta($rbfw_id, 'rbfw_time_format', true) ? get_post_meta($rbfw_id, 'rbfw_time_format', true) : '12';


	$checkin_location = get_post_meta($rbfw_id, 'rbfw_checkin_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_checkin_data', true)) : [];
	$dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

	$extra_service_list = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];
	$rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
	$rbfw_enable_resort_daylong_price  = get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', true ) : 'no';
	$rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
?>
	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">
		<!--    Left Side-->

		<!--    Left Side END-->

		<!--    Right Side-->
		<div class="rbfw-single-right-container">
			<form action="" method='post' class="mp_rbfw_ticket_form">

				<!-- Header -->
				<?php do_action('rbfw_discount_ad', $rbfw_id); ?>
				<!-- End Header -->

				<!--    ITEM        -->
				<div class="rbfw_resort_item_wrapper">
				<div class="item">
					<div class="item-content pricing-content">
						<div class="section-header">
							<div class="rbfw-single-right-heading rbfw_pricing_info_heading"><?php echo $rbfw->get_option('rbfw_text_pricing_info', 'rbfw_basic_translation_settings', __('Pricing Info','booking-and-rental-manager-for-woocommerce')); ?></div>
							
						</div>
						<div class="price-item-container">
							<table class="price-item-container-table">
								<tr>
									<th><?php rbfw_string('rbfw_text_room_type',__('Room Type','booking-and-rental-manager-for-woocommerce')); ?></th>
									<th style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><?php rbfw_string('rbfw_text_daylong_price',__('Day-long price','booking-and-rental-manager-for-woocommerce')); ?></th>
									<th><?php rbfw_string('rbfw_text_daynight_price',__('Day-night price','booking-and-rental-manager-for-woocommerce')); ?></th>
								</tr>
								<?php 
								if(! empty($rbfw_resort_room_data)) :			
								$i = 0;
								foreach ($rbfw_resort_room_data as $key => $value):
								?>
								<tr>
									<td><?php echo esc_attr($value['room_type']); ?></td>

									<?php if(!empty($value['rbfw_room_daylong_rate'])){ ?>
										<td style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><?php echo rbfw_mps_price( $value['rbfw_room_daylong_rate'] ); ?></td>
									<?php } ?>

									<td><?php echo rbfw_mps_price( $value['rbfw_room_daynight_rate'] ); ?></td>
								</tr>
								<?php 
								endforeach;
								endif; 
								?>
							</table>
						</div>
					</div>
				</div>
				<!--    ITEM END        -->
				<!-- ITEM -->
				<div class="item">
					<div class="rbfw-single-right-heading mb-08"><?php echo esc_html($rbfw->get_option('rbfw_text_checkin_checkout_date', 'rbfw_basic_translation_settings', __('Check-In & Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?></div>
					<div class="item-content rbfw-datetime">
						<div class="left date">
							<span class="calendar"><i class="fas fa-calendar-alt"></i></span>
							<input class="rbfw-input rbfw-time-price" type="text" name="rbfw_start_datetime" id="checkin_date" placeholder="<?php echo esc_html($rbfw->get_option('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly>
						</div>
						<div class="right date">
							<span class="calendar"><i class="fas fa-calendar-alt"></i></span>
							<input class="rbfw-input rbfw-time-price" type="text" name="rbfw_end_datetime" id="checkout_date" placeholder="<?php echo esc_html($rbfw->get_option('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly>
						</div>						
					</div>
				</div>
				<!-- ITEM END -->


				<!-- ITEM -->
				<div class="item">
					<a class="rbfw_chk_availability_btn">
					<?php echo esc_html($rbfw->get_option('rbfw_text_check_availability', 'rbfw_basic_translation_settings', __('Check Availability','booking-and-rental-manager-for-woocommerce'))); ?>
					</a>
				</div>
				<!-- ITEM END -->

								
				<!-- ITEM  -->
				<div class="rbfw-availability-loader"><i class="fas fa-spinner fa-spin"></i></div>
				<div class="rbfw-availability-result">
					<div class="rbfw_room_price_category_tabs"></div>
					<div class="rbfw_room_price_category_details_loader"><i class="fas fa-spinner fa-spin"></i></div>
					<div class="rbfw_room_price_category_details"></div>
				</div>
				<!-- ITEM END  -->
				</div>
				<div class="rbfw-resort-result-wrap">
					<div class="rbfw-resort-result-loader"></div>
					<div class="rbfw-resort-result"></div>
				</div>
				<input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="resort">
				<input type="hidden" id="rbfw_post_id"  value="<?php echo $rbfw_id; ?>">
			</form>

		</div>
		<!--    Right Side END-->
	</div>
	<!--    Main Layout END-->
