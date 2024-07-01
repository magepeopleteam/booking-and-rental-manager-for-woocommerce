<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	global $rbfw;
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	$rbfw_rent_type = !empty(get_post_meta( $rbfw_id, 'rbfw_item_type', true )) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : 'bike_car_sd';
	$rbfw_product_id = get_post_meta( $rbfw_id, "link_wc_product", true ) ? get_post_meta( $rbfw_id, "link_wc_product", true ) : $rbfw_id;
	$rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

	if($rbfw_payment_system == 'mps'){
        $rbfw_payment_system = 'mps_enabled';
    }else{
        $rbfw_payment_system = 'wps_enabled';
	}
?>
	<!--    Main Layout 	-->
	<div data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">
		<form action="" method='post' class="mp_rbfw_ticket_form">
			<div class="rbfw-bikecarsd-booking">
				<div class="booking-calender-view">
					<div id="rbfw-bikecarsd-calendar"></div>
				</div>
				<div class="booking-timeslot-view">
					<div class="rbfw-bikecarsd-result-wrap">
						<div class="rbfw-bikecarsd-result-loader"></div>
						<div class="rbfw-bikecarsd-result"></div>
						
					</div>
				</div>
			</div>
			<div class="rbfw-bikecarsd-result-order-details"></div>
			<!-- Button -->
			<div class="item rbfw_bikecarsd_book_now_btn_wrap">
				<button type="submit" name="add-to-cart" value="<?php echo $rbfw_product_id; ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn <?php echo $rbfw_payment_system; ?>" disabled>
				<?php
					echo $rbfw->get_option('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));
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
			<input type="hidden" id="rbfw_post_id"  value="<?php echo $rbfw_id; ?>">
		</form>
	</div>
	<!--    Main Layout END 	-->
