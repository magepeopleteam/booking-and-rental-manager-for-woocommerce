<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	global $rbfw;
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	$rbfw_rent_type = RBFW_Frontend::get_rent_type($rbfw_id);
	$rbfw_product_id = RBFW_Frontend::get_wc_product_id($rbfw_id);
	$rbfw_payment_system =  RBFW_Frontend::get_payment_system_type();
	$rbfw_regf_info = RBFW_Frontend::get_regi_form_info();
	$time_slot_switch = RBFW_Frontend::get_time_slot_switch();
	$appointment_days = RBFW_Frontend::get_appointment_days();
?>
	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">

	<!--    Right Side-->
		<div class="rbfw-single-right-container">
			<form action="" method='post' class="mp_rbfw_ticket_form">

				<!-- Header -->
				<div class="rbfw-bikecarsd-calendar-header">
					<div class="rbfw-bikecarsd-calendar-header-title"><?php rbfw_string('rbfw_text_book_online',__('Book online','booking-and-rental-manager-for-woocommerce')); ?></div>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fa-solid fa-clock"></i> <?php rbfw_string('rbfw_text_real_time_availability',__('Real-time availability','booking-and-rental-manager-for-woocommerce')); ?></div>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fa-solid fa-bolt"></i> <?php rbfw_string('rbfw_text_instant_confirmation',__('Instant confirmation','booking-and-rental-manager-for-woocommerce')); ?></div>
				</div>
				<!-- End Header -->

				<!-- ITEM -->
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
					<button type="submit" name="add-to-cart" value="<?php echo $rbfw_product_id; ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn <?php echo $rbfw_payment_system; ?>" disabled>
                        <?php
                        echo $rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));
                        ?>
					</button>
				</div>
				
				<!-- Button End -->
                <input type="hidden" name="rbfw_bikecarsd_selected_date" id="rbfw_bikecarsd_selected_date">
                <input type="hidden" name="rbfw_bikecarsd_selected_time" id="rbfw_bikecarsd_selected_time">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="<?php echo esc_attr($rbfw_rent_type); ?>">
                <input type="hidden" name="rbfw_regf_info" id="rbfw_regf_info"  value='<?php echo json_encode($rbfw_regf_info); ?>'>
                <input type="hidden" name="time_slot_switch" id="time_slot_switch"  value='<?php echo $time_slot_switch; ?>'>
                <input type="hidden" name="appointment_days" id="appointment_days"  value='<?php echo $appointment_days; ?>'>
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo rbfw_off_days($post_id); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo rbfw_off_dates($post_id); ?>'>
                <input type="hidden" id="rbfw_post_id"  value="<?php echo $rbfw_id; ?>">
			</form>
		</div>
		<!--    Right Side END-->
	</div>
	<!--    Main Layout END-->
