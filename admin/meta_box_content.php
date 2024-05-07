<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	// rbfw_meta_box_tab_content
	add_action( 'rbfw_meta_box_tab_content', 'rbfw_add_meta_box_tab_content', 10 );
	function rbfw_add_meta_box_tab_content( $rbfw_id ) {
		global $rbfw;
		$cpt_label  = $rbfw->get_name();
		$rbfw_type   = get_post_meta( $rbfw_id, 'rbfw_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_type', true ) : 'general';
		$gen_class   = $rbfw_type == 'general'  ? 'rbfw_show' : 'rbfw_hide';
		?>

        <?php do_action( 'rbfw_item_datetime_config_before', $rbfw_id ); ?>


        <?php do_action( 'rbfw_item_datetime_config_after', $rbfw_id ); ?>

		<div class="mp_tab_item active" data-tab-item="#travel_pricing">
			<div class='rbfw_general_rbfw_sec <?php echo esc_attr( $gen_class ); ?>' id='rbfw_general_rbfw_sec'>
				<?php do_action( 'rbfw_item_pricing_after', $rbfw_id ); ?>
			</div>
			<?php do_action( 'rbfw_item_exs_pricing_before', $rbfw_id ); ?>
			<?php rbfw_extra_service_config( $rbfw_id ); ?>
			<?php do_action( 'rbfw_item_exs_pricing_after', $rbfw_id ); ?>
		</div>

		<?php
		do_action( 'rbfw_location_config_before', $rbfw_id );
		//rbfw_location_config($rbfw_id);
		do_action( 'rbfw_location_config_after', $rbfw_id );
	}

	function rbfw_location_config($rbfw_id){
		$rbfw_enable_pick_point  = get_post_meta( $rbfw_id, 'rbfw_enable_pick_point', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_pick_point', true ) : 'yes';
		$rbfw_enable_dropoff_point  = get_post_meta( $rbfw_id, 'rbfw_enable_dropoff_point', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_dropoff_point', true ) : 'no';
		$rbfw_enable_daywise_price  = get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) : 'no';
		$rbfw_pickup_data        = get_post_meta( $rbfw_id, 'rbfw_pickup_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_pickup_data', true ) : [];
		$rbfw_dropoff_data       = get_post_meta( $rbfw_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_dropoff_data', true ) : [];
		$rbfw_item_type          = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';

		?>
		<?php if ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) { ?>
		<style>
			.mp_tab_menu li[data-target-tabs="#rbfw_location_config"]{ display:none; }
		</style>
		<?php } ?>

		<div class="mp_tab_item" data-tab-item="#rbfw_location_config" style="display: <?php if ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment') {
					echo esc_attr( 'none' );
				}?>;">
		<div class="rbfw_form_group rbfw_location_switch" >

		<div class='rbfw-location-attributes-section location-section'>
		<h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php esc_html_e( 'Pick-up Location Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
		<div class="mp_tab_item_sub_sec_location">
			<section class="component d-flex justify-content-between align-items-center mb-2"  >
				<label scope="row" class="w-50"><?php esc_html_e( 'Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
				<div class="d-flex flex-column w-50">
					<div class="rbfw_switch_wrapper rbfw_switch_pickup_location">
							<div class="rbfw_switch">
								<label for="rbfw_enable_pick_point_on" class="<?php if ( $rbfw_enable_pick_point == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_pick_point" class="rbfw_enable_pick_point" value="yes" id="rbfw_enable_pick_point_on" <?php if ( $rbfw_enable_pick_point == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_pick_point_off" class="<?php if ( $rbfw_enable_pick_point != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_pick_point" class="rbfw_enable_pick_point" value="no" id="rbfw_enable_pick_point_off" <?php if ( $rbfw_enable_pick_point != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
							</div>
						</div>
					</td>			
				</div>
			</section>
		<div class='rbfw-pickup-location-section' style="display: <?php if ( $rbfw_item_type != 'resort' && $rbfw_enable_pick_point == 'yes' ) {
			echo esc_attr( 'block' );
		} else {
			echo esc_attr( 'none' );
		} ?>;">
				
		<section class="component mb-2" >
			<table id="repeatable-fieldset-one-pickup" class='form-table rbfw_pricing_table'>
				<thead>
				<tr>
					<th colspan="2"><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
				</tr>
				</thead>
				<tbody class="mp_event_type_sortable">
					<?php
						if ( sizeof( $rbfw_pickup_data ) > 0 ) :
							foreach ( $rbfw_pickup_data as $field ) {
								$location_name = array_key_exists( 'loc_pickup_name', $field ) ? esc_attr( $field['loc_pickup_name'] ) : '';
								?>
								<tr class="location-pick-up-row">
									<td>
										<?php rbfw_get_location_dropdown( 'loc_pickup_name[]', $location_name ); ?>
									</td>
									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row-size" type="button"><i class="fa-solid fa-trash-can"></i></button>
											<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
										</div>
									</td>
								</tr>
								<?php
							}
						else :
						endif;
					?>
				<tr class="empty-row screen-reader-text-pickup location-pick-up-row" id='pickup-hidden-row'>
					<td><?php rbfw_get_location_dropdown( 'loc_pickup_name[]' ); ?></td>
					<td>
						<div class="mp_event_remove_move">
							<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
							<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
			<div class="add-row-pickup-btn">
				<button id="add-row-pickup" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
			</div>
		</section>
				
			</div>
		</div>

		<div class="mp_tab_item_sub_sec_location">
			<h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php esc_html_e( 'Drop-off Location Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
			<section class="component d-flex justify-content-between align-items-center mb-2">
				<div class="w-50 d-flex justify-content-between align-items-center">
					<label class=""><?php esc_html_e( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
				</div>
				<div class="w-50 d-flex justify-content-between align-items-center">
					<div class="rbfw_switch_wrapper rbfw_switch_dropoff_location">
						<div class="rbfw_switch">
							<label for="rbfw_enable_dropoff_point_on" class="<?php if ( $rbfw_enable_dropoff_point == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_dropoff_point" class="rbfw_enable_dropoff_point" value="yes" id="rbfw_enable_dropoff_point_on" <?php if ( $rbfw_enable_dropoff_point == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_dropoff_point_off" class="<?php if ( $rbfw_enable_dropoff_point != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_dropoff_point" class="rbfw_enable_dropoff_point" value="no" id="rbfw_enable_dropoff_point_off" <?php if ( $rbfw_enable_dropoff_point != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
						</div>
					</div>
				</div>
			</section>

		<div class='rbfw-dropoff-location-section component mb-2' style="display: <?php if ( $rbfw_item_type != 'resort' && $rbfw_enable_dropoff_point == 'yes' ) {
				echo esc_attr( 'block' );
			} else {
				echo esc_attr( 'none' );
			} ?>;">
				

				<table id="repeatable-fieldset-one-dropoff" class='form-table rbfw_pricing_table'>
					<thead>
					<tr>
						<th colspan="2"><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
					</tr>
					</thead>
					<tbody class="mp_event_type_sortable">
					<?php
						if ( sizeof( $rbfw_dropoff_data ) > 0 ) :
							foreach ( $rbfw_dropoff_data as $field ) {
								$location_name = array_key_exists( 'loc_dropoff_name', $field ) ? esc_attr( $field['loc_dropoff_name'] ) : '';
								?>
								<tr class="location-drop-off-row">
									<td><?php rbfw_get_location_dropdown( 'loc_dropoff_name[]', $location_name ); ?></td>
									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row-size" type="button"><i class="fa-solid fa-trash-can"></i></button>
											<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
										</div>
									</td>
								</tr>
								<?php
							}
						else :
						endif;
					?>
					<tr class="empty-row screen-reader-text-dropoff location-drop-off-row" id='dropoff-hidden-row'>
						<td><?php rbfw_get_location_dropdown( 'loc_dropoff_name[]' ); ?></td>
						<td>
						<div class="mp_event_remove_move">
							<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
							<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
						</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div class="add-row-dropoff-btn">
					<button id="add-row-dropoff" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				</div>
				</div>

				</div>
			</div>
		</div>
	</div>
<?php
	}

	function rbfw_day_row( $day_name, $day_slug ) {
		$hourly_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) : '';
		$daily_rate  = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) : '';
		$enable      = !empty(get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true )) ? get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) : '';
		?>
		<tr>
			<th><?php esc_html_e( $day_name, '' ); ?></th>
			<td><input type="number" name='rbfw_<?php echo mep_esc_html($day_slug); ?>_hourly_rate' value="<?php echo esc_html( $hourly_rate ); ?>" placeholder="<?php esc_html_e( 'Hourly Price', '' ); ?>"></td>
			<td><input type="number" name='rbfw_<?php echo mep_esc_html($day_slug); ?>_daily_rate' value="<?php echo esc_html( $daily_rate ); ?>" placeholder="<?php esc_html_e( 'Daily Price', '' ); ?>"></td>
			<td><input type="checkbox" name='rbfw_enable_<?php echo mep_esc_html($day_slug); ?>_day' value='yes' <?php if ( $enable == 'yes' ) {
					echo 'checked';
				} ?> ></td>
		</tr>
		<?php
	}


	function rbfw_extra_service_config( $post_id ) {
		$rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
		$rbfw_size_data          = get_post_meta( $post_id, 'rbfw_size_data', true ) ? get_post_meta( $post_id, 'rbfw_size_data', true ) : [];
		$rbfw_pickup_data        = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
		$rbfw_dropoff_data       = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
		wp_nonce_field( 'rbfw_ticket_type_nonce', 'rbfw_ticket_type_nonce' );
		$hourly_rate             = get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) : '';
		$daily_rate              = get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) : '';
		$rbfw_item_type          = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : 'bike_car_sd';
		$rbfw_enable_pick_point  = get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) : 'no';
		$rbfw_enable_dropoff_point  = get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) : 'no';
		$rbfw_enable_daywise_price  = get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) : 'no';
		$rbfw_enable_hourly_rate = get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) : 'no';
		$rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'yes';
		$rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
		$rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
		$rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';


		$rbfw_item_stock_quantity = !empty(get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true )) ? get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true ) : 0;
		$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';

		$rbfw_sd_appointment_ondays_data = get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) : [];
		$rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) : '';

		$rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
		$rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
		$rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
		$rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
		$rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';
		?>
		<h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php echo ''.esc_html__( 'Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
		
		<section class="component d-flex justify-content-between align-items-center mb-2"  >
			<label scope="row" class="w-50">
				<?php echo esc_html_e( 'Select Rent Type:', 'booking-and-rental-manager-for-woocommerce' ); ?><i class="fas fa-question-circle tool-tips"><span></span></i>
			</label>
			
			<div class="d-flex justify-content-end w-50">
				<select name="rbfw_item_type" id="rbfw_item_type" class='rbfw_item_type'>
					<option value="bike_car_sd" <?php echo ( $rbfw_item_type == 'bike_car_sd' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Bike/Car for single day', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="bike_car_md" <?php echo ( $rbfw_item_type == 'bike_car_md' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Bike/Car for multiple day', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="resort" <?php echo ( $rbfw_item_type == 'resort' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Resort', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="equipment" <?php echo ( $rbfw_item_type == 'equipment' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Equipment', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="dress" <?php echo ( $rbfw_item_type == 'dress' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Dress', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="appointment" <?php echo ( $rbfw_item_type == 'appointment' )?'Selected':'' ?>>
                        <?php esc_html_e( 'Appointment', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>

					<option value="others" <?php echo ( $rbfw_item_type == 'others' ) ?'Selected':'' ?>>
                        <?php esc_html_e( 'Others', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </option>
				</select>
			</div>
		</section>

        <div class='rbfw-item-type '>
			<div class="rbfw_form_group" data-table="rbfw_item_type_table">
				<table class="form-table rbfw_item_type_table">
                    <!--this section start only for appontment-->
                    <tr class="rbfw_switch_sd_appointment_row " <?php if ( $rbfw_item_type != 'appointment') { echo 'style="display:none"'; } ?>>
						<td>
							<section class="component d-flex justify-content-between align-items-center mb-2" >
								<label class="w-50">
									<?php esc_html_e( 'Appointment Ondays:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span></span></i>
								</label>
								<div class="d-flex flex-column w-50 ">
									<div class="rbfw_appointment_ondays_wrap">
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Sunday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Sunday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Sunday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Monday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Monday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Monday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Tuesday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Tuesday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Tuesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Wednesday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Wednesday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Wednesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Thursday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Thursday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Thursday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Friday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Friday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Friday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
										<div class="rbfw_appointment_ondays_value">
											<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Saturday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Saturday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
											<span><?php esc_html_e( 'Saturday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
									</div>
								</div>
							</section>
						</td>
					</tr>

					<tr class="rbfw_switch_sd_appointment_row" <?php if ( $rbfw_item_type != 'appointment') { echo 'style="display:none"'; } ?>>
						<td>
							<section class="component d-flex justify-content-between align-items-center mb-2" >
								<label class="w-50">
									<?php esc_html_e( 'Maximum Allowed Quantity Per Session/Time Slot:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span></span></i>
								</label>
								<div class="d-flex flex-column w-50 ">
									<input type="number" name="rbfw_sd_appointment_max_qty_per_session" id="rbfw_sd_appointment_max_qty_per_session" value="<?php echo esc_attr($rbfw_sd_appointment_max_qty_per_session); ?>">
								</div>
							</section>
						</td>
					</tr>

                    <!--this section end only for appontment-->

					<?php echo do_action('rbfw_after_rent_item_type_table_row'); ?>
				</table>
			</div>
		</div>

        <!--this section start for bike_car_sd,appointment-->

		<div class="rbfw_bike_car_sd_wrapper" style="display: <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;">
			<!-- <h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php echo esc_html_e( 'Price Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2> -->
			<section class="component d-flex flex-column justify-content-between align-items-start mb-2">
				<table class='form-table rbfw_bike_car_sd_price_table'>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody class="rbfw_bike_car_sd_price_table_body">
					<?php
					if(! empty($rbfw_bike_car_sd_data)) :
					$i = 0;
					foreach ($rbfw_bike_car_sd_data as $key => $value):
					?>
						<tr class="rbfw_bike_car_sd_price_table_row" data-key="<?php echo mep_esc_html($i); ?>">
							<td><input type="text" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td><input type="text" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td><input type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][price]" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><input type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
								<div class="mp_event_remove_move">
									<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
								</div>
							</td>
						</tr>
					<?php
					$i++;
					endforeach;
					else:
					?>
						<tr class="rbfw_bike_car_sd_price_table_row" data-key="0">
							<td><input type="text" name="rbfw_bike_car_sd_data[0][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td><input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td><input type="number" name="rbfw_bike_car_sd_data[0][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><input type="number" name="rbfw_bike_car_sd_data[0][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

							<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
								<div class="mp_event_remove_move">
									<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
								</div>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
				<p class="mt-2" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
					<button id="add-bike-car-sd-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				</p>
			</section>

		</div>

        <!--this section end for bike_car_sd,appointment-->

		<script>
		jQuery(document).ready(function(){

		// onclick add-bike-car-sd-type-row action
		jQuery('#add-bike-car-sd-type-row').click(function (e) {
			e.preventDefault();
			let current_time = jQuery.now();
			if(jQuery('.rbfw_bike_car_sd_price_table .rbfw_bike_car_sd_price_table_row').length){
				let bike_car_sd_type_last_row = jQuery('.rbfw_bike_car_sd_price_table .rbfw_bike_car_sd_price_table_row:last-child()');
				let bike_car_sd_type_type_last_data_key = parseInt(bike_car_sd_type_last_row.attr('data-key'));
				let bike_car_sd_type_type_new_data_key = bike_car_sd_type_type_last_data_key + 1;
				let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';

				let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
			}
			else{
				let bike_car_sd_type_type_new_data_key = 0;
				let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
				let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
			}
			jQuery('.remove-row.'+current_time+'').on('click', function () {
				e.preventDefault();
				e.stopImmediatePropagation();
				if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
					jQuery(this).parents('tr').remove();
				} else {
					return false;
				}
			});

			jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

		});

		jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

		// end add-bike_car_sd_type-type-btn action

		});
		</script>

        <!--this section start only for resort -->

		<div class="rbfw_resort_price_config_wrapper " style="display: <?php if ( $rbfw_item_type == 'resort' ) { echo esc_attr( 'block' );} else {echo esc_attr( 'none' );} ?>;">
            <h2 class="h5 text-white bg-primary mb-1 rounded-top">
                <?php echo ''. esc_html__( 'Resort Price Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
            </h2>
            <section class="component d-flex justify-content-between align-items-center mb-2">
                <label scope="row" class="w-50">
                    <?php esc_html_e( 'Day-long Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?><i class="fas fa-question-circle tool-tips"><span></span></i>
                </label>
                <div class="d-flex justify-content-end w-50">
                    <div class="rbfw_switch_wrapper rbfw_switch_resort_daylong_price">
                        <div class="rbfw_switch rbfw_resort_daylong_price_switch">
                            <label for="rbfw_enable_resort_daylong_price_on" class="<?php if ( $rbfw_enable_resort_daylong_price == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_resort_daylong_price" class="rbfw_enable_resort_daylong_price" value="yes" id="rbfw_enable_resort_daylong_price_on" <?php if ( $rbfw_enable_resort_daylong_price == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_resort_daylong_price_off" class="<?php if ( $rbfw_enable_resort_daylong_price != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_resort_daylong_price" class="rbfw_enable_resort_daylong_price" value="no" id="rbfw_enable_resort_daylong_price_off" <?php if ( $rbfw_enable_resort_daylong_price != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="component mb-2">
                <?php do_action( 'rbfw_before_resort_price_table' ); ?>
                <table class='form-table rbfw_resort_price_table'>
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        <th class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><?php esc_html_e( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        <th colspan="2"><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="rbfw_resort_price_table_body">
                    <?php
                    if(! empty($rbfw_resort_room_data)) :
                    $i = 0;
                    foreach ($rbfw_resort_room_data as $key => $value):
                        $img_url = wp_get_attachment_url($value['rbfw_room_image']);
                    ?>
                    <tr class="rbfw_resort_price_table_row" data-key="<?php echo mep_esc_html($i); ?>">
                        <td>
                            <input type="text" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][room_type]" value="<?php echo esc_attr($value['room_type']); ?>" placeholder="<?php esc_html_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
                        </td>
                        <td>
                            <div class="rbfw_room_type_image_preview">
                            <?php if($img_url): ?>
                                <img src="<?php echo esc_url($img_url); ?>">
                            <?php endif; ?>
                            </div>
                            <a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
                            <input type="hidden" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_image]" value="<?php echo esc_attr($value['rbfw_room_image']); ?>" class="rbfw_room_image"/>
                        </td>
                        <td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daylong_rate]" value="<?php echo esc_attr( $value['rbfw_room_daylong_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td><input type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daynight_rate]" value="<?php echo esc_attr( $value['rbfw_room_daynight_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td><input type="text" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_desc]" value="<?php echo esc_attr( $value['rbfw_room_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td><input class="small" type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_available_qty]" value="<?php echo esc_attr( $value['rbfw_room_available_qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td>
                        <div class="mp_event_remove_move">
                            <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                        </div>
                        </td>
                    </tr>
                    <?php
                    $i++;
                    endforeach;
                    else:
                    ?>
                    <tr class="rbfw_resort_price_table_row" data-key="0">
                        <td>
                            <input type="text" name="rbfw_resort_room_data[0][room_type]" value="" placeholder="<?php esc_html_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
                        </td>
                        <td>
                            <div class="rbfw_room_type_image_preview"></div>
                            <a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
                            <input type="hidden" name="rbfw_resort_room_data[0][rbfw_room_image]" value="" class="rbfw_room_image"/>
                        </td>
                        <td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data[0][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td><input type="number" name="rbfw_resort_room_data[0][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                        <td><input type="text" name="rbfw_resort_room_data[0][rbfw_room_desc]" value="" placeholder="<?php esc_attr_e( "Short Description", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
                        <td><input class="small" type="number" name="rbfw_resort_room_data[0][rbfw_room_available_qty]" value="" placeholder="<?php esc_attr_e( "Stock Quantity", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
                        <td>
                        <div class="mp_event_remove_move">
                            <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                        </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <p class="mt-2">
                    <button id="add-resort-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Resort Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                </p>
                <?php do_action( 'rbfw_after_resort_price_table' ); ?>
            </section>

		</div>

        <!--this section end only for resort -->




        <!--this section start  for bike_car_md,equipment,dress and others -->

       <?php include(dirname(__FILE__).'/templates/mdedo_price.php'); ?>

        <!--this section end for bike_car_md,equipment,dress and others -->

		<?php do_action( 'rbfw_after_week_price_table',$post_id ); ?>


        <!--this section start for only appointment -->

        <div class="rbfw_es_price_config_wrapper " <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
			<h2 class="h5 text-white bg-primary mb-1 rounded-top">
                <?php echo ''. esc_html__( 'Extra Service Price Settings', 'booking-and-rental-manager-for-woocommerce' ); ?>
            </h2>
            <section class="component d-flex flex-column justify-content-between align-items-start mb-2">
                <table id="repeatable-fieldset-one" class='rbfw_pricing_table form-table'>
                    <thead>
					<tr>
						<th><?php esc_html_e( 'Service Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Service Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Service Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Service Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<!--<th><?php esc_html_e( 'Qty Box', 'booking-and-rental-manager-for-woocommerce' ); ?></th>-->
						<th></th>
					</tr>
					</thead>
					<tbody class="mp_event_type_sortable">
					<?php
						if ( !empty($rbfw_extra_service_data) ) :
							foreach ( $rbfw_extra_service_data as $field ) {

								if(!empty($field['service_img'])){

									$service_img = !empty($field['service_img']) ? esc_attr( $field['service_img'] ) : '';
									$img_url = wp_get_attachment_url($service_img);

								} else {

									$service_img = '';
									$img_url = '';
								}


								$service_name     = array_key_exists( 'service_name', $field ) ? esc_attr( $field['service_name'] ) : '';
								$service_price    = array_key_exists( 'service_price', $field ) ? esc_attr( $field['service_price'] ) : '';

								$service_desc    = array_key_exists( 'service_desc', $field ) ? esc_attr( $field['service_desc'] ) : '';

								$service_qty      = array_key_exists( 'service_qty', $field ) ? esc_attr( $field['service_qty'] ) : '';
								$service_qty_type = array_key_exists( 'service_qty_type', $field ) ? esc_attr( $field['service_qty_type'] ) : 'inputbox';
								?>
								<tr>
								<td class="rbfw_service_image_wrap">
									<div class="rbfw_service_image_preview">
									<?php if($img_url): ?>
										<img src="<?php echo esc_url($img_url); ?>">
									<?php endif; ?>
									</div>
									<a class="rbfw_service_image_btn button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_service_image_btn btn"><i class="fa-solid fa-circle-minus"></i></a>
									<input type="hidden" name="service_img[]" value="<?php echo esc_attr( $service_img ); ?>" class="rbfw_service_image"/>
								</td>

									<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_html( $service_name ); ?>"/></td>
									<td><input type="number" step="0.01" class="mp_formControl" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_html( $service_price ); ?>"/></td>

									<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value="<?php echo esc_html( $service_desc ); ?>"/></td>

									<td><input type="number" class="small" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_html( $service_qty ); ?>"/></td>
									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row" type="button"><i class="fa-solid fa-trash-can"></i></button>
											<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
										</div>
									</td>
								</tr>
								<?php
							}

						endif;
					?>
					<!-- empty hidden one for jQuery -->
					<tr class="empty-row screen-reader-text">
						<td class="rbfw_service_image_wrap">
							<div class="rbfw_service_image_preview"></div>
							<a class="rbfw_service_image_btn button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_service_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
							<input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
						</td>
						<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap"/></td>
						<td><input type="number" class="mp_formControl" step="0.01" name="service_price[]" placeholder="Ex: 10" value=""/></td>
						<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value=""/></td>
						<td><input type="number" class="small" name="service_qty[]" placeholder="Ex: 100" value=""/></td>
						<td>
								<div class="mp_event_remove_move">
									<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
									<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
								</div>
						</td>
					</tr>
					</tbody>
				</table>
				<p class="mt-2">
					<button id="add-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Extra Service', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				</p>
			</section>
		</div>

        <!--this section end for only appointment -->

		<?php do_action('rbfw_after_extra_service_table'); ?>

		<script>
			jQuery(document).ready(function(){

					// extra service add image button and remove image button function
					function rbfw_service_image_addup(){
					// onclick extra service add image button action
					jQuery('.rbfw_service_image_btn').click(function() {
						let target = jQuery(this).parents('tr');
						let send_attachment_bkp = wp.media.editor.send.attachment;
						wp.media.editor.send.attachment = function(props, attachment) {
							target.find('.rbfw_service_image_preview img').remove();
							target.find('.rbfw_service_image_preview').append('<img src="'+attachment.url+'"/>');
							target.find('.rbfw_service_image').val(attachment.id);
							wp.media.editor.send.attachment = send_attachment_bkp;
						}
						wp.media.editor.open(jQuery(this));
						return false;
					});
					// end onclick extra service add image button action

					// onclick extra service remove image button action
					jQuery('.rbfw_remove_service_image_btn').click(function() {
						let target = jQuery(this).parents('tr');
						target.find('.rbfw_service_image_preview img').remove();
						target.find('.rbfw_service_image').val('');
					});
					// end onclick extra service remove image button action
				}
				rbfw_service_image_addup();
				// End extra service add image button and remove image button function

			});

		</script>
		<?php
		$resort_function = new RBFW_Resort_Function();
		$resort_function->rbfw_resort_admin_scripts(get_the_ID());
	}



function rbfw_off_days_config( $post_id ) {
    $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
    $rbfw_size_data          = get_post_meta( $post_id, 'rbfw_size_data', true ) ? get_post_meta( $post_id, 'rbfw_size_data', true ) : [];
    $rbfw_pickup_data        = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
    $rbfw_dropoff_data       = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
    wp_nonce_field( 'rbfw_ticket_type_nonce', 'rbfw_ticket_type_nonce' );
    $hourly_rate             = get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) : '';
    $daily_rate              = get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) : '';
    $rbfw_item_type          = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : 'bike_car_sd';
    $rbfw_enable_pick_point  = get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) : 'no';
    $rbfw_enable_dropoff_point  = get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) : 'no';
    $rbfw_enable_daywise_price  = get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) : 'no';
    $rbfw_enable_hourly_rate = get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) : 'no';
    $rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'yes';
    $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
    $rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
    $rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';


    $rbfw_item_stock_quantity = !empty(get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true )) ? get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true ) : 0;
    $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';

    $rbfw_sd_appointment_ondays_data = get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) : [];
    $rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) : '';

    $rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
    $rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
    $rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
    $rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
    $rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';

    $rbfw_off_days  = get_post_meta( $post_id, 'rbfw_off_days', true ) ? get_post_meta( $post_id, 'rbfw_off_days', true ) : '';

    $off_day_array = $rbfw_off_days?explode(',', $rbfw_off_days):[];

    $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');


    $rbfw_offday_range  = get_post_meta( $post_id, 'rbfw_offday_range', true ) ? get_post_meta( $post_id, 'rbfw_offday_range', true ) : '';


    ?>


    <h2 class="h5 text-white bg-primary mb-1 rounded-top">
        <?php echo ''.esc_html__( 'Off Day Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
    </h2>


    <div class='rbfw-item-type '>
        <div class="rbfw_form_group" data-table="rbfw_item_type_table">
			<div style=" <?php echo ($rbfw_item_type == 'appointment')?'display:none':'' ?>" class="component d-flex justify-content-start rbfw_off_days">
				<label for=""><?php esc_html_e( 'Off Day', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
				<div class="groupCheckBox d-flex justify-content-between align-items-center ms-5">
					<input type="hidden" name="rbfw_off_days" value="<?php echo $rbfw_off_days ?>">
					<?php foreach ($days as $day){ ?>
						<label class="customCheckboxLabel ">
							<input style="margin-right:3px;" type="checkbox" <?php echo in_array($day,$off_day_array)?'checked':'' ?>  data-checked="<?php echo $day ?>">
							<span class="customCheckbox pe-2"><?php echo ucfirst($day) ?></span>
						</label>
					<?php } ?>
				</div>
			</div>


            <div class="form-table rbfw_item_type_table off_date_range">
                <?php if(empty($rbfw_offday_range)){ ?>
                <div class="off_date_range_child component d-flex justify-content-between">
                    <section class="d-flex justify-content-between w-50">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class="ms-5 d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                            </div>
                        </div>
                    </section>
                    <section class="ms-1 d-flex justify-content-between w-50">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class="ms-5 d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]"  class="rbfw_off_days_range" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                            </div>
                        </div>
                    </section>
                </div>

                <?php } else {  ?>
                    <?php foreach ($rbfw_offday_range as $single){ ?>
                        <div class="off_date_range_child  component d-flex justify-content-between" >
                            <section class="component d-flex justify-content-between w-50">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                    <div class=" d-flex justify-content-between align-items-center">
                                        <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $single['from_date'] ); ?>" readonly>
                                    </div>
                                </div>
                            </section>
                            <section class="component d-flex justify-content-between w-50">
                                <div class="ms-1 d-flex justify-content-between align-items-center">
                                    <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                    <div class=" d-flex justify-content-between align-items-center">
                                        <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]"  class="rbfw_off_days_range" value="<?php echo esc_attr( $single['to_date'] ); ?>" readonly>
                                    </div>
                                </div>
                            </section>
                            <div class="component mp_event_remove_move">
                                <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <div class="off_date_range_content hidden">
                <div class="off_date_range_child component d-flex justify-content-between">
                    <section class=" d-flex justify-content-between w-50">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class="ms-5 d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD"  class="rbfw_off_days_range rbfw_off_days_range_start" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                            </div>
                        </div>
                    </section>
                    <section class="ms-5 d-flex justify-content-between w-50">
                        <div class="ms-5 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class="ms-5 d-flex justify-content-between align-items-center">
                                <input type="text" placeholder="YYYY-MM-DD"   class="rbfw_off_days_range rbfw_off_days_range_end" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                            </div>
                        </div>
                    </section>
                    <div class="component mp_event_remove_move">
                        <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="rbfw_bike_car_sd_wrapper">
        <section class="component d-flex flex-column justify-content-between align-items-start mb-2">
            <p class="mt-2">
                <button id="add-date-range-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Another Range', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
            </p>
        </section>
    </div>

    <?php
}






/*********************************
 * Start: Variations Tab
 * ******************************/
add_action( 'rbfw_meta_box_tab_name', 'rbfw_add_variations_tab_name' , 80);

function rbfw_add_variations_tab_name($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	?>
    <li class="<?php echo $rbfw_enable_variations ?>" data-target-tabs="#rbfw_variations" <?php if($rbfw_enable_variations == 'no' || $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; }?>>
        <i class="fa-solid fa-table-cells-large"></i>
        <?php esc_html_e( ' Variations', 'booking-and-rental-manager-for-woocommerce' ); ?>
    </li>
	<?php
}

add_action( 'rbfw_meta_box_tab_content', 'rbfw_add_variations_tab_content' , 80);

function rbfw_add_variations_tab_content($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';

	$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	$rbfw_variations_data = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];
	?>

	<div class="mp_tab_item fffff" data-tab-item="#rbfw_variations" <?php if($rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
		<h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php esc_html_e( 'Variations Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
		<section class="component d-flex justify-content-between align-items-center mb-2" data-row="rbfw_time_slot_switch">
			<label scope="row" class="w-50"><?php esc_html_e( 'Item Variations', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e( 'Enable/Disable Variations. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i>										</label>
										
			<div class="d-flex flex-column w-50">
				<div class="rbfw_switch rbfw_switch_variations">
					<label for="rbfw_enable_variations_on" class="<?php if ( $rbfw_enable_variations == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="yes" id="rbfw_enable_variations_on" <?php if ( $rbfw_enable_variations == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_variations_off" class="<?php if ( $rbfw_enable_variations != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="no" id="rbfw_enable_variations_off" <?php if ( $rbfw_enable_variations != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
				</div>
			</div>
		</section>

		<div class="rbfw_variations_table_wrap component mb-2" <?php if($rbfw_enable_variations == 'no'){ echo 'style="display:none"'; }?>>
			<table class="form-table rbfw_variations_table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Value(s)', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody class="rbfw_variations_table_body ui-sortable">
				<?php
				if(! empty($rbfw_variations_data)) :
				$i = 0;
				foreach ($rbfw_variations_data as $key => $value):
					$selected_value = !empty($value['selected_value']) ? $value['selected_value'] : '';
				?>
					<tr class="rbfw_variations_table_row" data-key="<?php echo esc_attr($i); ?>">

						<td>
							<input type="text" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_label]" value="<?php echo esc_attr( $value['field_label'] ); ?>" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<input type="hidden" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_id]" value="rbfw_variation_id_<?php echo esc_attr($i); ?>">
						</td>
						<td>
							<table class="rbfw_variations_value_table">
								<thead>
									<th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th>
										<?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?>
										<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
											<span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
									</th>
									<th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</thead>
								<tbody class="rbfw_variations_value_table_tbody">
									<?php
									$c = 0;
									foreach ($rbfw_variations_data[$i]['value'] as $key => $value):
									?>
									<tr class="rbfw_variations_value_table_row" data-key="<?php echo esc_attr($c); ?>">
										<td>
											<input type="text" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][value][<?php echo esc_attr($c); ?>][name]" value="<?php echo esc_attr($value['name']); ?>" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value">
										</td>
										<td>
											<input type="number" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][value][<?php echo esc_attr($c); ?>][quantity]" value="<?php echo esc_attr($value['quantity']); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">
										</td>
										<td>
											<input type="checkbox" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][selected_value]" value="<?php echo esc_attr($value['name']); ?>" class="rbfw_variation_selected_value" <?php if($value['name'] == $selected_value){ echo 'checked'; } ?>>
										</td>
										<td>
										<div class="mp_event_remove_move">
											<button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
											<div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div>
										</div>
										</td>
									</tr>
									<?php
									$c++;
									endforeach;
									?>
								</tbody>
							</table>
							<hr>
							<button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</td>
						<td>
							<div class="mp_event_remove_move">
								<button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
								<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
							</div>
						</td>
					</tr>
				<?php
				$i++;
				endforeach;
				else:
				?>
					<tr class="rbfw_variations_table_row" data-key="0">
						<td>
							<input type="text" name="rbfw_variations_data[0][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<input type="hidden" name="rbfw_variations_data[0][field_id]" value="rbfw_variation_id_0">
						</td>
						<td>
							<table class="rbfw_variations_value_table">
								<thead>
									<th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th>
										<?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?>
										<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
											<span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										</div>
									</th>
									<th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</thead>
								<tbody class="rbfw_variations_value_table_tbody">
									<tr class="rbfw_variations_value_table_row" data-key="0">
										<td>
											<input type="text" name="rbfw_variations_data[0][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value">
										</td>
										<td>
											<input type="number" name="rbfw_variations_data[0][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">
										</td>
										<td>
											<input type="checkbox" name="rbfw_variations_data[0][selected_value]"  class="rbfw_variation_selected_value">
										</td>
										<td>
										<div class="mp_event_remove_move">
											<button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
											<div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div>
										</div>
										</td>
									</tr>
								</tbody>
							</table>
							<hr>
							<button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</td>
						<td>
							<div class="mp_event_remove_move">
								<button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
								<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
							</div>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>

			<button id="add-new-variation" class="ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Variation', 'booking-and-rental-manager-for-woocommerce' ); ?></button>

		</div>
	</div>
	<script>
	jQuery(document).ready(function(){

		jQuery('#add-new-variation').click(function (e) {
			e.preventDefault();

			if(jQuery('.rbfw_variations_table .rbfw_variations_table_row').length > 0){
				let rbfw_variations_table_last_row = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child()');
				let rbfw_variations_table_last_data_key = parseInt(rbfw_variations_table_last_row.attr('data-key'));
				let rbfw_variations_table_new_data_key = rbfw_variations_table_last_data_key + 1;
				let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
				let rbfw_variations_table_add_new_row = jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
			}
			else{
				let rbfw_variations_table_new_data_key = 0;
				let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
				let rbfw_variations_table_add_new_row = jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
			}

			rbfw_variation_table_action_btns_func();
			rbfw_add_new_variation_value();
			rbfw_variation_selected_value_func();
		});

		/* Start: Add New Variation Value */

		rbfw_add_new_variation_value();
		function rbfw_add_new_variation_value(){

			jQuery('.add-new-variation-value').click(function (e) {
				let this_btn = jQuery(this);
				e.preventDefault();
				e.stopImmediatePropagation();


				let c = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child()');
				c = parseInt(c.attr('data-key'));


				if(jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row').length > 0){

					let rbfw_variations_value_table_last_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row:last-child()');
					let rbfw_variations_value_table_last_data_key = parseInt(rbfw_variations_value_table_last_row.attr('data-key'));
					let rbfw_variations_value_table_new_data_key = rbfw_variations_value_table_last_data_key + 1;
					let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
					let rbfw_variations_value_table_add_new_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);

				}else{

					let rbfw_variations_value_table_new_data_key = 0;
					let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
					let rbfw_variations_value_table_add_new_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);
				}

				rbfw_variation_table_action_btns_func();

				rbfw_variation_selected_value_func();
			});
		}

		/* End: Add New Variation Value */

		/* Start: Variation Default Value: Note: It works for frontend select box */
		rbfw_variation_selected_value_func();
		function rbfw_variation_selected_value_func(){

			jQuery('.rbfw_variation_selected_value').on('change', function() {
				jQuery(this).parents('.rbfw_variations_value_table_tbody').find('.rbfw_variation_selected_value').not(this).prop('checked', false);
			});

			jQuery('.rbfw_variation_value').keyup(function() {
				let	this_field = jQuery(this);
				let	this_val = jQuery(this).val();
				jQuery(this_field).parent('td').siblings('td').find('.rbfw_variation_selected_value').val(this_val);
			});
		}
		/* End: Variation Default Value */

		/* Start: variation table action buttons function */
		rbfw_variation_table_action_btns_func();
		function rbfw_variation_table_action_btns_func(){
			jQuery('.remove-rbfw_variations_table_row').on('click', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
					jQuery(this).parents('tr').remove();
				} else {
					return false;
				}
			});

			jQuery('.remove-rbfw_variations_value_table_row').on('click', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
					jQuery(this).parents('tr.rbfw_variations_value_table_row').remove();
				} else {
					return false;
				}
			});

			jQuery( ".rbfw_variations_table_body" ).sortable({ handle: ".mp_event_type_sortable_button" });
			jQuery( ".rbfw_variations_value_table_tbody" ).sortable({ handle: ".rbfw_variations_value_table_row_sortable" });
		}
		/* End: variation table action buttons function */

	});
	</script>
	<?php
}

 /*********************************
 * End: Variations Tab
 * ******************************/


function rbfw_repeated_item($id, $meta_key, $data = array() ){
	ob_start();
	$array = get_rbfw_repeated_setting_array( $meta_key );

	$title       = $array['title'];
	$title_name  = $array['title_name'];
	$title_value = array_key_exists( $title_name, $data ) ? html_entity_decode( $data[ $title_name ] ) : '';

	$image_title = $array['img_title'];
	$image_name  = $array['img_name'];
	$images      = array_key_exists( $image_name, $data ) ? $data[ $image_name ] : '';

	$content_title = $array['content_title'];
	$content_name  = $array['content_name'];
	$content       = array_key_exists( $content_name, $data ) ? html_entity_decode( $data[ $content_name ] ) : '';

	?>
	<div class='rbfw_remove_area mt-5'>
		<section class="bg-light">
			<div>
				<p class=""><?php echo esc_html( $title ); ?></p>
			</div>
			<div >
				<input type="text" class="formControl" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
			</div>
		</section>
		<section >
			<div class="rbfw_multi_image_area">
				<input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php esc_attr_e( $images ); ?>"/>
				<div class="rbfw_multi_image">
					<?php
						$all_images = explode( ',', $images );
						if ( $images && sizeof( $all_images ) > 0 ) {
							foreach ( $all_images as $image ) {
								?>
								<div class="rbfw_multi_image_item" data-image-id="<?php esc_attr_e( $image ); ?>">
									<span class="rbfw_close_multi_image_item"><i class="fa-solid fa-trash-can"></i></span>
									<img src="<?php echo wp_get_attachment_image_url( $image, 'medium' ) ?>" alt="<?php esc_attr_e( $image ); ?>'"/>
								</div>
								<?php
							}
						}
					?>
				</div>
				<button type="button" class=" add_multi_image ppof-button">
					<i class="fa-solid fa-circle-plus"></i>
					<?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</button>
			</div>
		</section>
		<section>
			<div class="w-100">
				<?php
					$settings = array(
						'wpautop'       => false,
						'media_buttons' => false,
						'textarea_name' => $content_name . '[]',
						'tabindex'      => '323',
						'editor_height' => 200,
						'editor_css'    => '',
						'editor_class'  => '',
						'teeny'         => false,
						'dfw'           => false,
						'tinymce'       => true,
						'quicktags'     => true
					);
					wp_editor( $content, $id, $settings );
				?>
			</div>
		</section>
		<span class="rbfw_item_remove"><i class="fa-solid fa-trash-can"></i></span>
	</div>
	<?php
	return ob_get_clean();
}
	function get_rbfw_repeated_setting_array( $meta_key ): array {
	$array = [
		'mep_event_faq'        => [
			'title'         => esc_html__( ' FAQ Title', 'booking-and-rental-manager-for-woocommerce' ),
			'title_name'    => 'rbfw_faq_title',
			'img_title'     => esc_html__( ' FAQ Details image', 'booking-and-rental-manager-for-woocommerce' ),
			'img_name'      => 'rbfw_faq_img',
			'content_title' => esc_html__( ' FAQ Details Content', 'booking-and-rental-manager-for-woocommerce' ),
			'content_name'  => 'rbfw_faq_content',
		]
	];

	return $array[ $meta_key ];
	}

	add_action( 'wp_ajax_get_rbfw_add_faq_content', 'get_rbfw_add_faq_content' );
	add_action( 'wp_ajax_nopriv_get_rbfw_add_faq_content', 'get_rbfw_add_faq_content');
	function get_rbfw_add_faq_content() {
		$id = RBFW_Function::data_sanitize( $_POST['id'] );
		echo rbfw_repeated_item( $id, 'mep_event_faq' );
		die();
	}
	
	function save_rbfw_repeated_setting( $rbfw_id, $meta_key ) {
		$array        = get_rbfw_repeated_setting_array( $meta_key );
		$title_name   = $array['title_name'];
		$image_name   = $array['img_name'];
		$content_name = $array['content_name'];
		if ( get_post_type( $rbfw_id ) == 'rbfw_item' ) {
			$old_data = RBFW_Function::get_post_info( $rbfw_id, $meta_key, array() );
			$new_data = array();
			$title    = RBFW_Function::get_submit_info( $title_name, array() );
			$images   = RBFW_Function::get_submit_info( $image_name, array() );
			$content  = RBFW_Function::get_submit_info( $content_name, array() );
			$count    = !empty($title) ? count( $title ) : 0;
			if ( $count > 0 ) {
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $title[ $i ] != '' ) {
						$new_data[ $i ][ $title_name ] = stripslashes( strip_tags( $title[ $i ] ) );
						if ( $images[ $i ] != '' ) {
							$new_data[ $i ][ $image_name ] = stripslashes( strip_tags( $images[ $i ] ) );
						}
						if ( $content[ $i ] != '' ) {
							$new_data[ $i ][ $content_name ] = htmlentities( $content[ $i ] );
						}
					}
				}

				if ( ! empty( $new_data ) && $new_data != $old_data ) {
					update_post_meta( $rbfw_id, $meta_key, $new_data );
				} elseif ( empty( $new_data ) && $old_data ) {
					delete_post_meta( $rbfw_id, $meta_key, $old_data );
				}
			}
		}
	}

	// ===================Generel info box ===================

	add_action( 'admin_init', 'rbfw_fw_meta_boxs' );

	function rbfw_fw_meta_boxs() {
		global $rbfw;
		$cpt_label = $rbfw->get_name();

		$rbfw_gen_info_boxs = array(
			'page_nav' => __( 'General Info', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'General Information', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
                    'options'     => array(
						array(
							'id'          => 'rbfw_add_to_cart_shortcode',
							'title'       => __( 'Add To Cart Form Shortcode:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'If you want to display this item add-to-cart form on any post or page of your website, copy the shortcode and paste it where desired.', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'add_to_cart_shortcode',
							'placeholder' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
						),

                        array(
                            'id'          => 'rbfw_add_category',
                            'title'       => __( 'Select Categories:', 'booking-and-rental-manager-for-woocommerce' ),
                            'details'     => __( 'If you want to display this item add-to-cart form on any post or page of your website, copy the shortcode and paste it where desired.', 'booking-and-rental-manager-for-woocommerce' ),
                            'type'        => 'rbfw_add_category',
                            'placeholder' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
                        ),

						array(
							'id'          => 'rbfw_feature_category',
							'title'       => __( 'Features Category:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'feature_category',
							'placeholder' => __( 'Features Name', 'booking-and-rental-manager-for-woocommerce' ),
						),
						array(
							'id'          => 'rbfw_releted_rbfw',
							'title'       => __( 'Related Items:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please add related items', 'booking-and-rental-manager-for-woocommerce' ),

							'type'        => 'select2',
							'multiple'	  => true,
							'title_field' => 'rbfw_releted_rbfw_id',
							'btn_text'    => __( 'Add New ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce' ),
							'args'        => 'CPT_%rbfw_item%',
						),

						array(
							'id'      => 'rbfw_dt_sidebar_switch',
							'title'   => __( 'Donut Template Sidebar:', 'booking-and-rental-manager-for-woocommerce' ),
							'details' => __( 'It displays a sidebar beside the registration form.', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'switch',
							'args' => array(
								'on' => 'On',
								'off' => 'Off',
							),
							'default' => 'off',
						),

                        array(
                            'id'      => 'shipping_enable',
                            'title'   => __( 'Is Shipping Enable', 'booking-and-rental-manager-for-woocommerce' ),
                            'details' => __( 'To enable shipping enable, then keep on', 'booking-and-rental-manager-for-woocommerce' ),
                            'type'    => 'switch',
                            'args' => array(
                                'on' => 'On',
                                'off' => 'Off',
                            ),
                            'default' => 'off',
                        ),

						array(
							'id'          => 'rbfw_dt_sidebar_testimonials',
							'title'       => __( 'Donut Template Sidebar Testimonials:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'collapsible' => false,
							'type'        => 'repeatable',
							'title_field' => 'rbfw_dt_sidebar_testimonial_title',
							'btn_text'    => 'Add New Testimonial',
							'fields'      => array(
								array(
									'type'    => 'textarea',
									'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
									'args'    => '',
									'item_id' => 'rbfw_dt_sidebar_testimonial_text',
									'name'    => 'Text',
								)
							),
						),

						array(
							'id'          => 'rbfw_dt_sidebar_content',
							'title'       => __( 'Donut Template Sidebar Content:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'wp_editor',
							'default' => '<img src="'.esc_url('https://booking.mage-people.com/wp-content/uploads/2022/08/sidebar-img.png').'"/>',
						),
					)
				),

			),
		);

		

		$rbfw_gen_info_boxs_args = array(
			'meta_box_id'    => 'rbfw_travel_basic_info_meta_boxes',
			'meta_box_title' => '<i class="fas fa-tools"></i>' . __( 'General Info', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'high',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_basic_meta_boxs' => $rbfw_gen_info_boxs
			)
		);

		//$RMFWAddMetaBox = new RMFWAddMetaBox( $rbfw_gen_info_boxs_args );


		// ==================== end Generel info box ===================

        $rbfw_pricing_meta_boxs_args = array(
			'meta_box_id'    => 'travel_pricing',
			'meta_box_title' => '<i class="fa-solid fa-dollar-sign"></i>' .__( 'Pricing', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",

		);
		//new RMFWAddMetaBox( $rbfw_pricing_meta_boxs_args );


		
		do_action('rbfw_tax_meta_boxs');

		$rbfw_tax_meta_boxs = array(
			'page_nav' => __( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Tax Configuration for Mage Payment System', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(
						array(
							'id'      => 'rbfw_mps_tax_percentage',
							'title'   => __( 'Tax percentage', 'booking-and-rental-manager-for-woocommerce' ),
							'details' => __( 'Please enter the number of tax percentage. Example: 10', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'text',
							'default' => '',
						),
					)
				),

			),
		);

		$rbfw_mps_tax_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_tax_settings_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-percent"></i>' .__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_tax_meta_boxs' => $rbfw_tax_meta_boxs
			),
		);


		$rbfw_date_time_meta_boxs = array(
			'page_nav' => __( 'Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Date Configuration', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(
						array(
							'id'      => 'rbfw_time_slot_switch',
							'title'   => __( 'Time Slot', 'booking-and-rental-manager-for-woocommerce' ),
							'details' => __( 'It enables/disables the time slot for Bike/Car Single Day and Appointment rent type.', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'switch',
							'args' => array(
								'on' => 'On',
								'off' => 'Off',
							),
							'default' => 'on',
						),
						array(
							'id'       => 'rdfw_available_time',
							'title'    => __( 'Available Time Slot', 'booking-and-rental-manager-for-woocommerce' ),
							'details'  => __( 'Please select the availabe time slots', 'booking-and-rental-manager-for-woocommerce' ),
							'type'     => 'time_slot',
							'multiple' => true,
							'default'  => '',
							'args'     => rbfw_get_available_time_slots()
						),

					)
				),

			),
		);

		$rbfw_date_time_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_date_settings_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-calendar-days"></i>' .__( 'Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_date_meta_boxs' => $rbfw_date_time_meta_boxs
			),
		);
		//new RMFWAddMetaBox( $rbfw_date_time_meta_boxs_args );


        $rbfw_off_days_meta_boxs_args = array(
            'meta_box_id'    => 'travel_off_days',
            'meta_box_title' => '<i class="fa-regular fa-calendar-xmark"></i>' .__( 'Off Days', 'booking-and-rental-manager-for-woocommerce' ),
            'screen'         => array( 'rbfw_item' ),
            'context'        => 'normal',
            'priority'       => 'low',
            'callback_args'  => array(),
            'nav_position'   => 'none',
            'item_name'      => "MagePeople",
            'item_version'   => "2.0",

        );
       // new RMFWAddMetaBox( $rbfw_off_days_meta_boxs_args );


       $rbfw_tax_meta_boxs_args = array(
            'meta_box_id'    => 'rbfw_tax',
            'meta_box_title' => '<i class="fa-solid fa-dollar-sign"></i>' .__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
            'screen'         => array( 'rbfw_item' ),
            'context'        => 'normal',
            'priority'       => 'low',
            'callback_args'  => array(),
            'nav_position'   => 'none',
            'item_name'      => "MagePeople",
            'item_version'   => "2.0",
        );
       // new RMFWAddMetaBox( $rbfw_tax_meta_boxs_args );




		$rbfw_location_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_location_config',
			'meta_box_title' => '<i class="fa-solid fa-location-dot"></i>' .__( 'Location', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",

		);
		//new RMFWAddMetaBox( $rbfw_location_meta_boxs_args );

		$rbfw_template_info_boxs = array(
			'page_nav' => __( 'Template', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Template Settings', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(
						array(
							'id'          => 'rbfw_single_template',
							'title'       => __( 'Template:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'select',
							'args' => RBFW_Function::all_details_template(),
						),
					)
				),
			),
		);


		$rbfw_template_info_boxs_args = array(
			'meta_box_id'    => 'rbfw_template_settings_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-pager"></i>' . __( 'Template', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'high',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_basic_meta_boxs' => $rbfw_template_info_boxs
			)
		);
		// new RMFWAddMetaBox( $rbfw_template_info_boxs_args );

		$rbfw_gallery_meta_boxs = array(
			'page_nav' => __( 'Gallery', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Image Gallery', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(

						array(
							'id'          => 'rbfw_gallery_images',
							'title'       => __( 'Gallery Images:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please upload images for gallery', 'booking-and-rental-manager-for-woocommerce' ),
							'placeholder' => 'https://via.placeholder.com/1000x500',
							'type'        => 'media_multi',
						),
						array(
							'id'          => 'rbfw_gallery_images_additional',
							'title'       => __( 'Additional Gallery Images:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please upload images for gallery', 'booking-and-rental-manager-for-woocommerce' ),
							'placeholder' => 'https://via.placeholder.com/1000x500',
							'type'        => 'media_multi',
						),

					)
				),

			),
		);

		$rbfw_gallery_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_gallery_images_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-images"></i>' .__( 'Gallery', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none', // right, top, left, none
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_gallery_meta_boxs' => $rbfw_gallery_meta_boxs
			),
		);
		//new RMFWAddMetaBox( $rbfw_gallery_meta_boxs_args );


		$current_payment_system = rbfw_get_option( 'rbfw_payment_system', 'rbfw_basic_payment_settings');
		global $rbfw;
		$mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');

		if ('mps' == $current_payment_system) {
			new RMFWAddMetaBox( $rbfw_mps_tax_meta_boxs_args );
		}
	}