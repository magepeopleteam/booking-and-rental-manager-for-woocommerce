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
			<div class="single-day-booking-area">
				<div class="calender">
					<div id="rbfw-single-day-booking" data-start-weekday=''></div>
				</div>
				<div class="timeslot" >
					<ul class="items">
						
						<?php 
							$timslot = RBFW_Frontend::get_time_slots();
							foreach( $timslot as $key => $value): ?>
								<li class="times" onclick="RBFW_Single_Day_Booking.selectTimeSlot(this)"><?php echo $value; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</form>
	</div>
	<!--    Main Layout END 	-->
