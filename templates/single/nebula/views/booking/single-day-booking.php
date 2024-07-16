<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	global $rbfw;
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	$rbfw_rent_type = RBFW_Frontend::get_rent_type($rbfw_id);
	$rbfw_product_id = RBFW_Frontend::get_wc_product_id($rbfw_id);
	$rbfw_payment_system =  RBFW_Frontend::get_payment_system_type();
?>
	<!--    Main Layout 	-->
	<div data-service-id="<?php echo mep_esc_html($rbfw_id); ?>">
		<form action="" method='post' class="mp_rbfw_ticket_form">
			<div class="booking-area">
				<div class="calender">
					<div id="rbfw-single-day-booking" data-start-weekday=''></div>
				</div>
				<div class="timeslot">

				</div>
			</div>
		</form>
	</div>
	<!--    Main Layout END 	-->
