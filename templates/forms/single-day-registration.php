<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	global $rbfw;
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	$rbfw_rent_type = !empty(get_post_meta( $rbfw_id, 'rbfw_item_type', true )) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : 'bike_car_sd';
	$rbfw_product_id = get_post_meta( $rbfw_id, "link_wc_product", true ) ? get_post_meta( $rbfw_id, "link_wc_product", true ) : $rbfw_id;
	$rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

	if($rbfw_payment_system == 'mps'){
        $rbfw_payment_system = 'mps_enabled';
    }else{
        $rbfw_payment_system = 'wps_enabled';
	}
    $location_switch = !empty(get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true)) ? get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true) : '';
    $pickup_location = get_post_meta($rbfw_id, 'rbfw_pickup_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_pickup_data', true)) : [];
    $dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];
?>

	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">
        <!--    Right Side-->
		<div class="rbfw-single-right-container">
			<form action="" method='post' class="mp_rbfw_ticket_form">
                <!-- Header -->
				<div class="rbfw-bikecarsd-calendar-header">
					<h3 class="rbfw-bikecarsd-calendar-header-title"><?php rbfw_string('rbfw_text_book_online',__('Book online','booking-and-rental-manager-for-woocommerce')); ?></h3>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fa-solid fa-clock"></i> <?php rbfw_string('rbfw_text_real_time_availability',__('Real-time availability','booking-and-rental-manager-for-woocommerce')); ?></div>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fa-solid fa-bolt"></i> <?php rbfw_string('rbfw_text_instant_confirmation',__('Instant confirmation','booking-and-rental-manager-for-woocommerce')); ?></div>
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
                        <div class="rbfw-single-right-heading">
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_location', 'rbfw_basic_translation_settings', __('Drop-off Location','booking-and-rental-manager-for-woocommerce'))); ?>
                        </div>
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

				<div class="item rbfw-bikecarsd-step" data-step="1">
					<div id="rbfw-bikecarsd-calendar"></div>
					<div class="rbfw-bikecarsd-calendar-footer">
                        <i class="fa-solid fa-circle-info"></i>
                        <?php rbfw_string('rbfw_text_click_date_to_browse_availability',__('Click a date to browse availability','booking-and-rental-manager-for-woocommerce')); ?>
                    </div>
				</div>
				<!-- ITEM END -->

				<div class="rbfw-bikecarsd-result-wrap">
					<div class="rbfw-bikecarsd-result-loader"></div>
					<div class="rbfw-bikecarsd-result"></div>
					<div class="rbfw-bikecarsd-result-order-details"></div>
				</div>


				<!-- Button -->
				
				<div class="item rbfw_bikecarsd_book_now_btn_wrap">
					<button type="submit" name="add-to-cart" value="<?php echo $rbfw_product_id; ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn rbfw_disabled_button <?php echo $rbfw_payment_system; ?>" disabled>
					<?php
						echo $rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));
					?>	
					</button>
				</div>

                <?php
                $rbfw_regf_info = [];
                if(class_exists('Rbfw_Reg_Form')){
                    $ClassRegForm = new Rbfw_Reg_Form();
                    $rbfw_regf_info = $ClassRegForm->rbfw_get_regf_all_fields_name($post_id);
                }
                $time_slot_switch = !empty(get_post_meta($post_id, 'rbfw_time_slot_switch', true)) ? get_post_meta($post_id, 'rbfw_time_slot_switch', true) : 'on';
                $available_times = get_post_meta($post_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($post_id, 'rdfw_available_time', true)) : [];
                

                if($time_slot_switch == 'on' && !empty($available_times)){
                    $time_slot_switch = 'on';
                }else{
                    $time_slot_switch = 'off';
                }

                $appointment_days = json_encode(get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true));
                ?>

                <input type="hidden" name="rbfw_bikecarsd_selected_date" id="rbfw_bikecarsd_selected_date">
                <input type="hidden" name="rbfw_bikecarsd_selected_time" id="rbfw_bikecarsd_selected_time">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="<?php echo esc_attr($rbfw_rent_type); ?>">
                <input type="hidden" name="rbfw_regf_info" id="rbfw_regf_info"  value='<?php echo json_encode($rbfw_regf_info); ?>'>
                <input type="hidden" name="time_slot_switch" id="time_slot_switch"  value='<?php echo $time_slot_switch; ?>'>
                <input type="hidden" name="appointment_days" id="appointment_days"  value='<?php echo $appointment_days; ?>'>
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo rbfw_off_days($post_id); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range" class="llll"  value='<?php echo rbfw_off_dates($post_id); ?>'>
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo $rbfw_id; ?>">
			</form>
		</div>
		<!--    Right Side END-->
	</div>
	<!--    Main Layout END-->
<?php


?>