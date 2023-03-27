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
		rbfw_location_config($rbfw_id);
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
			<div class="mp_tab_item_sub_sec">

		<div class="rbfw_switch_wrapper rbfw_switch_pickup_location">
			<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<div class="rbfw_switch">
				<label for="rbfw_enable_pick_point_on" class="<?php if ( $rbfw_enable_pick_point == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_pick_point" class="rbfw_enable_pick_point" value="yes" id="rbfw_enable_pick_point_on" <?php if ( $rbfw_enable_pick_point == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_pick_point_off" class="<?php if ( $rbfw_enable_pick_point != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_pick_point" class="rbfw_enable_pick_point" value="no" id="rbfw_enable_pick_point_off" <?php if ( $rbfw_enable_pick_point != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
			</div>
		</div>

			<div class='rbfw-pickup-location-section' style="display: <?php if ( $rbfw_item_type != 'resort' && $rbfw_enable_pick_point == 'yes' ) {
			echo esc_attr( 'block' );
		} else {
			echo esc_attr( 'none' );
		} ?>;">
				<h3><?php esc_html_e( 'Pick-up Location Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
					<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'Please add the pickup locations.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
				</h3>
				
				<table id="repeatable-fieldset-one-pickup" class='form-table rbfw_pricing_table'>
					<thead>
					<tr>
						<th><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th></th>
					</tr>
					</thead>
					<tbody class="mp_event_type_sortable">
					<?php
						if ( sizeof( $rbfw_pickup_data ) > 0 ) :
							foreach ( $rbfw_pickup_data as $field ) {
								$location_name = array_key_exists( 'loc_pickup_name', $field ) ? esc_attr( $field['loc_pickup_name'] ) : '';
								?>
								<tr>
									<td>
										<?php rbfw_get_location_dropdown( 'loc_pickup_name[]', $location_name ); ?>
									</td>
									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row-size" type="button"><span class="dashicons dashicons-trash" ></span></button>
											<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
										</div>
									</td>
								</tr>
								<?php
							}
						else :
						endif;
					?>
					<tr class="empty-row screen-reader-text-pickup" id='pickup-hidden-row'>
						<td><?php rbfw_get_location_dropdown( 'loc_pickup_name[]' ); ?></td>
						<td>
						<div class="mp_event_remove_move">
							<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button>
							<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
						</div>	
						</td>
					</tr>
					</tbody>
				</table>
				<p>
					<button id="add-row-pickup" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				</p>
			</div>
			</div>
			<div class="mp_tab_item_sub_sec">

		<div class="rbfw_switch_wrapper rbfw_switch_dropoff_location">
			<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<div class="rbfw_switch">
				<label for="rbfw_enable_dropoff_point_on" class="<?php if ( $rbfw_enable_dropoff_point == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_dropoff_point" class="rbfw_enable_dropoff_point" value="yes" id="rbfw_enable_dropoff_point_on" <?php if ( $rbfw_enable_dropoff_point == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_dropoff_point_off" class="<?php if ( $rbfw_enable_dropoff_point != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_dropoff_point" class="rbfw_enable_dropoff_point" value="no" id="rbfw_enable_dropoff_point_off" <?php if ( $rbfw_enable_dropoff_point != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
			</div>
		</div>

				<div class='rbfw-dropoff-location-section' style="display: <?php if ( $rbfw_item_type != 'resort' && $rbfw_enable_dropoff_point == 'yes' ) {
				echo esc_attr( 'block' );
			} else {
				echo esc_attr( 'none' );
			} ?>;">
				<h3><?php esc_html_e( 'Drop-off Location Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
					<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'Please add the dropoff locations.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>			
				</h3>
				
				<table id="repeatable-fieldset-one-dropoff" class='form-table rbfw_pricing_table'>
					<thead>
					<tr>
						<th><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
						<th></th>
					</tr>
					</thead>
					<tbody class="mp_event_type_sortable">
					<?php
						if ( sizeof( $rbfw_dropoff_data ) > 0 ) :
							foreach ( $rbfw_dropoff_data as $field ) {
								$location_name = array_key_exists( 'loc_dropoff_name', $field ) ? esc_attr( $field['loc_dropoff_name'] ) : '';
								?>
								<tr>
									<td><?php rbfw_get_location_dropdown( 'loc_dropoff_name[]', $location_name ); ?></td>
									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row-size" type="button"><span class="dashicons dashicons-trash" ></span></button>
											<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
										</div>
									</td>
								</tr>
								<?php
							}
						else :
						endif;
					?>
					<tr class="empty-row screen-reader-text-dropoff" id='dropoff-hidden-row'>
						<td><?php rbfw_get_location_dropdown( 'loc_dropoff_name[]' ); ?></td>
						<td>
						<div class="mp_event_remove_move">
							<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button>
							<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
						</div>
						</td>
					</tr>
					</tbody>
				</table>
				<p>
					<button id="add-row-dropoff" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				</p>
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
			<td><?php esc_html_e( $day_name, '' ); ?></td>
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
		?>
		<div class='rbfw-item-type mp_tab_item_sub_sec'>
			<h3><?php echo esc_html_e( 'Rent Item Type :', 'booking-and-rental-manager-for-woocommerce' ); ?>
				<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
					<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'Please select the rent type of the item.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</div>		
			</h3>
			
			<table class="form-table">
				<tr>
					<td>
					<label for="rbfw_item_type"><strong><?php echo esc_html_e( 'Select Rent Type:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
					<select name="rbfw_item_type" id="rbfw_item_type" class='rbfw_item_type'>

						<option value="bike_car_sd" <?php if ( $rbfw_item_type == 'bike_car_sd' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Bike/Car for single day', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="bike_car_md" <?php if ( $rbfw_item_type == 'bike_car_md' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Bike/Car for multiple day', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="resort" <?php if ( $rbfw_item_type == 'resort' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Resort', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="equipment" <?php if ( $rbfw_item_type == 'equipment' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Equipment', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="dress" <?php if ( $rbfw_item_type == 'dress' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Dress', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="appointment" <?php if ( $rbfw_item_type == 'appointment' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Appointment', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

						<option value="others" <?php if ( $rbfw_item_type == 'others' ) {
							echo esc_attr( 'Selected' );
						} ?>><?php esc_html_e( 'Others', 'booking-and-rental-manager-for-woocommerce' ); ?></option>

					</select>
					</td>
				</tr>

				<tr class="rbfw_switch_md_type_item_qty rbfw_item_stock_quantity_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' || $rbfw_enable_variations == 'yes') { echo 'style="display:none"'; } ?>>
					<td>
						<label for="rbfw_item_stock_quantity"><strong><?php esc_html_e( 'Stock Quantity:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
						<input type="number" name="rbfw_item_stock_quantity" id="rbfw_item_stock_quantity" value="<?php echo esc_attr($rbfw_item_stock_quantity); ?>">
					</td>
				</tr>

				<tr class="rbfw_switch_sd_appointment_row rbfw_appointment_ondays_row" <?php if ( $rbfw_item_type != 'appointment') { echo 'style="display:none"'; } ?>>
					<td>
						<label class="rbfw_appointment_ondays_label">
							<?php esc_html_e( 'Appointment Ondays', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</label>
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
					</td>
				</tr>
				<tr class="rbfw_switch_sd_appointment_row" <?php if ( $rbfw_item_type != 'appointment') { echo 'style="display:none"'; } ?>>
					<td>
						<div>
							<label class="rbfw_sd_appointment_max_qty_per_session">
								<strong>
									<?php esc_html_e( 'Maximum Allowed Quantity Per Session/Time Slot:', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</strong>
							</label>
							<input type="number" name="rbfw_sd_appointment_max_qty_per_session" id="rbfw_sd_appointment_max_qty_per_session" value="<?php echo esc_attr($rbfw_sd_appointment_max_qty_per_session); ?>">
						</div>
					</td>
				</tr>
				
				<?php echo do_action('rbfw_after_rent_item_type_table_row'); ?>
			</table>
			
		</div>

		<!-- Bike and Car Single Date Configuration -->
		<div class="rbfw_bike_car_sd_wrapper mp_tab_item_sub_sec" style="display: <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;">
			<h3><?php echo esc_html_e( 'Price Configuration:', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
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
								<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
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
								<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
							</div>					
						</td>
					</tr>
				<?php endif; ?>						
				</tbody>
			</table>
			<p class="rbfw_bike_car_sd_price_table_add_new_type_btn_wrap" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
				<button id="add-bike-car-sd-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
			</p>			
		</div>
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
				let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
				
				let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
			}
			else{
				let bike_car_sd_type_type_new_data_key = 0;
				let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
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
		<!-- Bike and Car Single Date Configuration -->
		
		<!-- Start Resort Configuration -->
		<div class="rbfw_resort_price_config_wrapper mp_tab_item_sub_sec" style="display: <?php if ( $rbfw_item_type == 'resort' ) {
			echo esc_attr( 'block' );
		} else {
			echo esc_attr( 'none' );
		} ?>;">
		<h3><?php echo esc_html_e( 'Resort Price Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>

		<div class="rbfw_switch_wrapper rbfw_switch_resort_daylong_price">
			<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Day-long Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<div class="rbfw_switch">
				<label for="rbfw_enable_resort_daylong_price_on" class="<?php if ( $rbfw_enable_resort_daylong_price == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_resort_daylong_price" class="rbfw_enable_resort_daylong_price" value="yes" id="rbfw_enable_resort_daylong_price_on" <?php if ( $rbfw_enable_resort_daylong_price == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_resort_daylong_price_off" class="<?php if ( $rbfw_enable_resort_daylong_price != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_resort_daylong_price" class="rbfw_enable_resort_daylong_price" value="no" id="rbfw_enable_resort_daylong_price_off" <?php if ( $rbfw_enable_resort_daylong_price != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
			</div>
		</div>

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
					<a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a>
					<input type="hidden" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_image]" value="<?php echo esc_attr($value['rbfw_room_image']); ?>" class="rbfw_room_image"/>
				</td>
				<td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daylong_rate]" value="<?php echo esc_attr( $value['rbfw_room_daylong_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>	
				<td><input type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daynight_rate]" value="<?php echo esc_attr( $value['rbfw_room_daynight_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
				<td><input type="text" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_desc]" value="<?php echo esc_attr( $value['rbfw_room_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
				<td><input type="number" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_available_qty]" value="<?php echo esc_attr( $value['rbfw_room_available_qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>				
				<td>
				<div class="mp_event_remove_move">
					<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
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
					<a class="rbfw_room_type_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_room_type_image_btn"><i class="fa-solid fa-circle-minus"></i></a>
					<input type="hidden" name="rbfw_resort_room_data[0][rbfw_room_image]" value="" class="rbfw_room_image"/>
				</td>
				<td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" name="rbfw_resort_room_data[0][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>	
				<td><input type="number" name="rbfw_resort_room_data[0][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
				<td><input type="text" name="rbfw_resort_room_data[0][rbfw_room_desc]" value="" placeholder="<?php esc_attr_e( "Short Description", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
				<td><input type="number" name="rbfw_resort_room_data[0][rbfw_room_available_qty]" value="" placeholder="<?php esc_attr_e( "Stock Quantity", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
				<td>
				<div class="mp_event_remove_move">
					<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
				</div>					
				</td>
			</tr>				
			<?php endif; ?>
			</tbody>
		</table>	
		<p>
			<button id="add-resort-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Resort Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
		</p>					
		<?php do_action( 'rbfw_after_resort_price_table' ); ?>
		</div>
		<!-- End Resort Configuration -->

		<div class="rbfw_general_price_config_wrapper" style="display: <?php if ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment') {
			echo esc_attr( 'block' );
		} else {
			echo esc_attr( 'none' );
		} ?>;">

		<div class="mp_tab_item_sub_sec">		
		<h3><?php echo esc_html_e( 'General Price Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
		<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
		<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'General Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
		</div> 
		</h3>
		
		<?php do_action( 'rbfw_before_general_price_table' ); ?>
		<table class='form-table'>
			<?php do_action( 'rbfw_before_general_price_table_row' ); ?>
			<tr>
				<th scope="row">
				<div class="rbfw_switch_wrapper">
					<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?>

					</div>
					<div class="rbfw_switch rbfw_switch_daily_rate">
						<label for="rbfw_enable_daily_rate_on" class="<?php if ( $rbfw_enable_daily_rate == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_daily_rate" class="rbfw_enable_daily_rate" value="yes" id="rbfw_enable_daily_rate_on" <?php if ( $rbfw_enable_daily_rate == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_daily_rate_off" class="<?php if ( $rbfw_enable_daily_rate != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_daily_rate" class="rbfw_enable_daily_rate" value="no" id="rbfw_enable_daily_rate_off" <?php if ( $rbfw_enable_daily_rate != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Enable/Disable daily price functionality.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				</th>
				<td><input type="number" name='rbfw_daily_rate' value="<?php echo esc_html( $daily_rate ); ?>" placeholder="<?php esc_html_e( 'Daily Price', '' ); ?>" class="<?php if ( $rbfw_enable_daily_rate == 'no' ) { echo 'rbfw_d_none'; } ?> rbfw_daily_rate_input"></td>
			</tr>
			<tr>
				<th scope="row">
				<div class="rbfw_switch_wrapper">
					<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</div>
					<div class="rbfw_switch rbfw_switch_hourly_rate">
						<label for="rbfw_enable_hourly_rate_on" class="<?php if ( $rbfw_enable_hourly_rate == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_hourly_rate" class="rbfw_enable_hourly_rate" value="yes" id="rbfw_enable_hourly_rate_on" <?php if ( $rbfw_enable_hourly_rate == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_hourly_rate_off" class="<?php if ( $rbfw_enable_hourly_rate != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_hourly_rate" class="rbfw_enable_hourly_rate" value="no" id="rbfw_enable_hourly_rate_off" <?php if ( $rbfw_enable_hourly_rate != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Enable/Disable the time slot functionality.', 'booking-and-rental-manager-for-woocommerce' ); ?> To add time slot, go to <a class="rbfw_open_date_time_tab">Date & Time Tab</a></div>
				</th>
				<td><input type="number" name='rbfw_hourly_rate' value="<?php echo esc_html( $hourly_rate ); ?>" placeholder="<?php esc_html_e( 'Hourly Price', '' ); ?>" class="<?php if ( $rbfw_enable_hourly_rate == 'no' ) { echo 'rbfw_d_none'; } ?> rbfw_hourly_rate_input"></td>
			</tr>
			<tr>
				<th scope="row">
					<div class="rbfw_switch_wrapper rbfw_switch_daywise_price">
						<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Day-wise Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
						<div class="rbfw_switch">
							<label for="rbfw_enable_daywise_price_on" class="<?php if ( $rbfw_enable_daywise_price == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_daywise_price" class="rbfw_enable_daywise_price" value="yes" id="rbfw_enable_daywise_price_on" <?php if ( $rbfw_enable_daywise_price == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_daywise_price_off" class="<?php if ( $rbfw_enable_daywise_price != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_daywise_price" class="rbfw_enable_daywise_price" value="no" id="rbfw_enable_daywise_price_off" <?php if ( $rbfw_enable_daywise_price != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
						</div>
					</div>
				</th>
				<hr>
			</tr>
			<?php do_action( 'rbfw_after_general_price_table_row' ); ?>
		</table>
		</div>
		<?php do_action( 'rbfw_after_general_price_table' ); ?>

		<div class="mp_tab_item_sub_sec rbfw-mt-min-15px rbfw-bt-none">
		<script>
			jQuery(document).ready(function(){

				jQuery('.rbfw_switch label').click(function(e) {
					e.stopImmediatePropagation();
					e.preventDefault();
					let $this = jQuery(this);
					let target = jQuery(this).parents('.rbfw_switch').find('label');
					target.removeClass('active');
					target.find('input').prop('checked', false);
					target.find('input').removeAttr('checked');
					$this.addClass('active');
					$this.find('input').prop('checked', true);

				});

			});
		</script>
		<div class="rbfw_week_table" style="display: <?php if (($rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment') && $rbfw_enable_daywise_price == 'yes') {
			echo esc_attr( 'block' );
		} else {
			echo esc_attr( 'none' );
		} ?>;">
				
		<h3><?php echo esc_html_e( 'Day-wise Price Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
		<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
		<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'Daywise price configuration for the item. It will override the general price configuration.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
		</div> 
		</h3>
		
		<table class='form-table'>
			<?php do_action( 'rbfw_before_week_price_table_row' ); ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Day Name:', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
				<th scope="row"><?php esc_html_e( 'Hourly Price:', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
				<th scope="row"><?php esc_html_e( 'Daily Price:', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
				<th scope="row"><?php esc_html_e( 'Service Enable', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
			</tr>
			<?php
				rbfw_day_row( __( 'Sunday', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
				rbfw_day_row( __( 'Monday', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
				rbfw_day_row( __( 'Tuesday', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
				rbfw_day_row( __( 'Wednesday', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
				rbfw_day_row( __( 'Thursday', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
				rbfw_day_row( __( 'Friday', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
				rbfw_day_row( __( 'Saturday', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );
				do_action( 'rbfw_after_week_price_table_row' );
			?>
		</table>
		</div>
		</div>
		</div>

		<?php do_action( 'rbfw_after_week_price_table',$post_id ); ?>

		<div class="rbfw_es_price_config_wrapper mp_tab_item_sub_sec" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>		
	
		<h3><?php echo esc_html_e( 'Extra Service Price Configuration :', 'booking-and-rental-manager-for-woocommerce' ); ?>
			<div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
			<span class="rbfw_tooltiptext"><?php echo esc_html_e( 'Please add the extra services for the item.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
			</div>	
		</h3>
			
		<div class="rbfw_form_group">
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
							<a class="rbfw_service_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_service_image_btn"><i class="fa-solid fa-circle-minus"></i></a>
							<input type="hidden" name="service_img[]" value="<?php echo esc_attr( $service_img ); ?>" class="rbfw_service_image"/>
						</td>

							<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_html( $service_name ); ?>"/></td>
							<td><input type="number" step="0.01" class="mp_formControl" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_html( $service_price ); ?>"/></td>

							<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value="<?php echo esc_html( $service_desc ); ?>"/></td>

							<td><input type="number" class="mp_formControl" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_html( $service_qty ); ?>"/></td>							
							<td>
								<div class="mp_event_remove_move">
									<button class="button remove-row" type="button"><span class="dashicons dashicons-trash"></span></button>
									<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
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
					<a class="rbfw_service_image_btn"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?></a><a class="rbfw_remove_service_image_btn"><i class="fa-solid fa-circle-minus"></i></a>
					<input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
				</td>
				<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap"/></td>
				<td><input type="number" class="mp_formControl" step="0.01" name="service_price[]" placeholder="Ex: 10" value=""/></td>
				<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value=""/></td>
				<td><input type="number" class="mp_formControl" name="service_qty[]" placeholder="Ex: 100" value=""/></td>
				<td>
						<div class="mp_event_remove_move">
							<button class="button remove-row"><span class="dashicons dashicons-trash"></span></button>
							<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
						</div>
				</td>
			</tr>
			</tbody>
		</table>
		<p>
			<button id="add-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Extra Service', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
		</p>
		</div>
		</div>

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
	}
	
	add_action( 'rbfw_meta_box_tab_name', 'rbfw_add_meta_box_tab_faq', 20 );
	function rbfw_add_meta_box_tab_faq( $rbfw_id ) {
		?>
		<li data-target-tabs="#rbfw_faq"><i class="fa-solid fa-circle-question"></i><?php esc_html_e( ' FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
		<?php
		
	}
	
	add_action( 'rbfw_meta_box_tab_content', 'rbfw_add_meta_box_tab_faq_content', 10 );
	function rbfw_add_meta_box_tab_faq_content( $rbfw_id ) {
		$rbfw_enable_faq_content  = get_post_meta( $rbfw_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_faq_content', true ) : 'no';
		?>
		<div class="mpStyle mp_tab_item mp_tab_item_sub_sec" data-tab-item="#rbfw_faq">
			<h3><?php esc_html_e( 'FAQ Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>


		<div class="rbfw_switch_wrapper rbfw_switch_faq">
			<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off FAQ Content', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<div class="rbfw_switch">
				<label for="rbfw_enable_faq_content_on" class="<?php if ( $rbfw_enable_faq_content == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_faq_content" class="rbfw_enable_faq_content" value="yes" id="rbfw_enable_faq_content_on" <?php if ( $rbfw_enable_faq_content == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_faq_content_off" class="<?php if ( $rbfw_enable_faq_content != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_faq_content" class="rbfw_enable_faq_content" value="no" id="rbfw_enable_faq_content_off" <?php if ( $rbfw_enable_faq_content != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
			</div>
		</div>

			<div class="rbfw_faq_content_wrapper" style="display: <?php if ($rbfw_enable_faq_content == 'yes' ) {
				echo esc_attr( 'block' );
			} else {
				echo esc_attr( 'none' );
			} ?>;">
			<?php
				$faqs = RBFW_Function::get_post_info( $rbfw_id, 'mep_event_faq', array() );
				if ( sizeof( $faqs ) > 0 ) {
					foreach ( $faqs as $faq ) {
						$id = 'rbfw_faq_content_' . uniqid();
						echo rbfw_repeated_item( $id, 'mep_event_faq', $faq );
					}
				}
			?>
			<button type="button" class=" rbfw_add_faq_content ppof-button">
				<i class="fa-solid fa-circle-plus"></i>
				<?php esc_html_e( 'Add New FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?>
			</button>
			</div>
		</div>
		<script>
			jQuery(document).ready(function(){  
				jQuery('.rbfw_enable_faq_switch_label').click(function (e) {
					let checked_attr = jQuery('.rbfw_enable_faq_switch_label input').attr('checked');
					if(typeof checked_attr !== 'undefined' && checked_attr !== false){
						jQuery('.rbfw_enable_faq_switch_label input').removeAttr('checked');
						jQuery('.rbfw_faq_content_wrapper').hide();
					}
					else{
						jQuery('.rbfw_enable_faq_switch_label input').attr('checked',true);
						jQuery('.rbfw_faq_content_wrapper').show();
					}	
				});
			});                
        </script> 		
		<?php
	}

/*********************************
 * Start: Variations Tab 
 * ******************************/
add_action( 'rbfw_meta_box_tab_name', 'rbfw_add_variations_tab_name' , 11);

function rbfw_add_variations_tab_name($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	?>
	<li data-target-tabs="#rbfw_variations" <?php if($rbfw_enable_variations == 'no' || $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; }?>><i class="fa-solid fa-table-cells-large"></i><?php esc_html_e( ' Variations', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
	<?php
	
}

add_action( 'rbfw_meta_box_tab_content', 'rbfw_add_variations_tab_content' , 11);

function rbfw_add_variations_tab_content($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';

	$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	$rbfw_variations_data = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];
	?>
	
	<div class="mp_tab_item mp_tab_item_sub_sec" data-tab-item="#rbfw_variations" <?php if($rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
		<h3><?php esc_html_e( 'Variations Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
		<table class="form-table">
			<tr class="rbfw_switch_md_type_item_qty rbfw_switch_md_type_variation_switch_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' ) { echo 'style="display:none"'; } ?>>
				<td>
				<div class="rbfw_switch_wrapper rbfw_enable_variations_switch">
				<div class="rbfw_switch_label"><?php esc_html_e( 'On/Off Item Variations', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</div>
				<div class="rbfw_switch rbfw_switch_variations">
					<label for="rbfw_enable_variations_on" class="<?php if ( $rbfw_enable_variations == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="yes" id="rbfw_enable_variations_on" <?php if ( $rbfw_enable_variations == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_variations_off" class="<?php if ( $rbfw_enable_variations != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="no" id="rbfw_enable_variations_off" <?php if ( $rbfw_enable_variations != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
				</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Enable/Disable Variations. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				</td>
			</tr>
		</table>


		<div class="rbfw_variations_table_wrap" <?php if($rbfw_enable_variations == 'no'){ echo 'style="display:none"'; }?>>
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
											<div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div>
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
								<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
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
											<div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div>
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
								<div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
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
				let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
				let rbfw_variations_table_add_new_row = jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
			}
			else{
				let rbfw_variations_table_new_data_key = 0;
				let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
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
					let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
					let rbfw_variations_value_table_add_new_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);

				}else{

					let rbfw_variations_value_table_new_data_key = 0;
					let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash"></span></button><div class="button rbfw_variations_value_table_row_sortable"><span class="dashicons dashicons-move"></span></div></div></td></tr>';
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

 /*********************************
 * Start: Front-end Display Tab
 * ******************************/
add_action( 'rbfw_meta_box_tab_name', 'rbfw_frontend_display_tab_name' , 11);

function rbfw_frontend_display_tab_name($rbfw_id){

	?>
	<li data-target-tabs="#rbfw_frontend_display"><i class="fa-solid fa-display"></i><?php esc_html_e( ' Front-end Display', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
	<?php

}

add_action( 'rbfw_meta_box_tab_content', 'rbfw_frontend_display_tab_content' , 11);

function rbfw_frontend_display_tab_content($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	$rbfw_available_qty_info_switch = get_post_meta( $rbfw_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $rbfw_id, 'rbfw_available_qty_info_switch', true ) : 'no';
	$rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
	$rbfw_enable_md_type_item_qty = get_post_meta( $rbfw_id, 'rbfw_enable_md_type_item_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_md_type_item_qty', true ) : 'no';
	?>
	<div class="mp_tab_item mp_tab_item_sub_sec" data-tab-item="#rbfw_frontend_display">
		<h3><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
		<table class="form-table">
		<tr>
			<td>
				<div class="rbfw_switch_wrapper rbfw_m_0">
					<div class="rbfw_switch_label"><?php esc_html_e( 'Enable the Available Item Quantity Display on Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</div>
					<div class="rbfw_switch">
						<label for="rbfw_available_qty_info_switch_on" class="<?php if ( $rbfw_available_qty_info_switch == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_available_qty_info_switch" class="rbfw_available_qty_info_switch" value="yes" id="rbfw_available_qty_info_switch_on" <?php if ( $rbfw_available_qty_info_switch == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_available_qty_info_switch_off" class="<?php if ( $rbfw_available_qty_info_switch != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_available_qty_info_switch" class="rbfw_available_qty_info_switch" value="no" id="rbfw_available_qty_info_switch_off" <?php if ( $rbfw_available_qty_info_switch != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'It displays available quantity information in item details page.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			</td>
		</tr>
		<tr class="rbfw_switch_md_type_item_qty" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' ) { echo 'style="display:none"'; } ?>>
			<td>
				<div class="rbfw_switch_wrapper rbfw_m_0">
					<div class="rbfw_switch_label"><?php esc_html_e( 'Enable Multiple Item Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</div>
					<div class="rbfw_switch">
						<label for="rbfw_enable_md_type_item_qty_on" class="<?php if ( $rbfw_enable_md_type_item_qty == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_md_type_item_qty" class="rbfw_enable_md_type_item_qty" value="yes" id="rbfw_enable_md_type_item_qty_on" <?php if ( $rbfw_enable_md_type_item_qty == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_md_type_item_qty_off" class="<?php if ( $rbfw_enable_md_type_item_qty != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_md_type_item_qty" class="rbfw_enable_md_type_item_qty" value="no" id="rbfw_enable_md_type_item_qty_off" <?php if ( $rbfw_enable_md_type_item_qty != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'It enables the multiple item quantity selection option. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			</td>
		</tr>

		<tr class="rbfw_switch_md_type_item_qty" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ||  $rbfw_item_type == 'resort' ) { echo 'style="display:none"'; } ?>>
			<td>
				<div class="rbfw_switch_wrapper rbfw_switch_extra_service_qty" <?php if ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display: none"'; } ?>>
					<div class="rbfw_switch_label"><?php esc_html_e( 'Enable Multiple Extra Service Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?><br>
					</div>
					<div class="rbfw_switch">
						<label for="rbfw_enable_extra_service_qty_on" class="<?php if ( $rbfw_enable_extra_service_qty == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_extra_service_qty" class="rbfw_enable_extra_service_qty" value="yes" id="rbfw_enable_extra_service_qty_on" <?php if ( $rbfw_enable_extra_service_qty == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_extra_service_qty_off" class="<?php if ( $rbfw_enable_extra_service_qty != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_extra_service_qty" class="rbfw_enable_extra_service_qty" value="no" id="rbfw_enable_extra_service_qty_off" <?php if ( $rbfw_enable_extra_service_qty != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
				<hr>
				<div class="rbfw_alert_info"><i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Enable/Disable multiple service quantity selection. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			</td>
		</tr>

		</table>
	</div>
	<?php
}

/*********************************
 * End: Front-end Display Tab
 * ******************************/

	function rbfw_repeated_item( $id, $meta_key, $data = array() ) {
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
	<div class='dLayout rbfw_remove_area'>
		<label>
			<span class="min_200"><?php echo esc_html( $title ); ?></span>
			<input type="text" class="formControl" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
		</label>
		<div class="dFlex">
			<span class="min_200"><?php echo esc_html( $image_title ); ?></span>
			<div class="rbfw_multi_image_area">
				<input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php esc_attr_e( $images ); ?>"/>
				<div class="rbfw_multi_image">
					<?php
						$all_images = explode( ',', $images );
						if ( $images && sizeof( $all_images ) > 0 ) {
							foreach ( $all_images as $image ) {
								?>
								<div class="rbfw_multi_image_item" data-image-id="<?php esc_attr_e( $image ); ?>">
									<span class="dashicons dashicons-no-alt circleIcon_xs rbfw_close_multi_image_item"></span>
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
		</div>
		<label>
			<span class="min_200"><?php echo esc_html( $content_title ); ?></span>
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
		</label>
		<span class="dashicons dashicons-no-alt circleIcon_xs rbfw_item_remove"></span>
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


	add_action( 'save_post', 'rbfw_save_meta_box_data', 99 );
	function rbfw_save_meta_box_data( $post_id ) {
		global $wpdb;
		if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( $_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce' ) ) {
			return;
		}
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		if ( get_post_type( $post_id ) == 'rbfw_item' ) {
			
			$hourly_rate = isset( $_POST['rbfw_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_hourly_rate'] ) : 0;
			$daily_rate  = isset( $_POST['rbfw_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_daily_rate'] ) : 0;
			
			//sun
			$hourly_rate_sun = isset( $_POST['rbfw_sun_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_hourly_rate'] ) : '';
			$daily_rate_sun  = isset( $_POST['rbfw_sun_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_daily_rate'] ) : '';
			$enabled_sun     = isset( $_POST['rbfw_enable_sun_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sun_day'] ) : 'no';
			//mon
			$hourly_rate_mon = isset( $_POST['rbfw_mon_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_hourly_rate'] ) : '';
			$daily_rate_mon  = isset( $_POST['rbfw_mon_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_daily_rate'] ) : '';
			$enabled_mon     = isset( $_POST['rbfw_enable_mon_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_mon_day'] ) : 'no';
			//tue
			$hourly_rate_tue = isset( $_POST['rbfw_tue_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_hourly_rate'] ) : '';
			$daily_rate_tue  = isset( $_POST['rbfw_tue_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_daily_rate'] ) : '';
			$enabled_tue     = isset( $_POST['rbfw_enable_tue_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_tue_day'] ) : 'no';
			//wed
			$hourly_rate_wed = isset( $_POST['rbfw_wed_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_hourly_rate'] ) : '';
			$daily_rate_wed  = isset( $_POST['rbfw_wed_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_daily_rate'] ) : '';
			$enabled_wed     = isset( $_POST['rbfw_enable_wed_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_wed_day'] ) : 'no';
			//thu
			$hourly_rate_thu = isset( $_POST['rbfw_thu_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_hourly_rate'] ) : '';
			$daily_rate_thu  = isset( $_POST['rbfw_thu_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_daily_rate'] ) : '';
			$enabled_thu     = isset( $_POST['rbfw_enable_thu_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_thu_day'] ) : 'no';
			//fri
			$hourly_rate_fri = isset( $_POST['rbfw_fri_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_hourly_rate'] ) : '';
			$daily_rate_fri  = isset( $_POST['rbfw_fri_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_daily_rate'] ) : '';
			$enabled_fri     = isset( $_POST['rbfw_enable_fri_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_fri_day'] ) : 'no';
			//sat
			$hourly_rate_sat         = isset( $_POST['rbfw_sat_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_hourly_rate'] ) : '';
			$daily_rate_sat          = isset( $_POST['rbfw_sat_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_daily_rate'] ) : '';
			$enabled_sat             = isset( $_POST['rbfw_enable_sat_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sat_day'] ) : 'no';
			$rbfw_item_type          = isset( $_POST['rbfw_item_type'] ) ? rbfw_array_strip( $_POST['rbfw_item_type'] ) : 'others';
			$rbfw_enable_pick_point  = isset( $_POST['rbfw_enable_pick_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_pick_point'] ) : 'no';
			$rbfw_enable_dropoff_point  = isset( $_POST['rbfw_enable_dropoff_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_dropoff_point'] ) : 'no';
			$rbfw_enable_daywise_price  = isset( $_POST['rbfw_enable_daywise_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daywise_price'] ) : 'no';
			$rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? $_POST['rbfw_available_qty_info_switch']  : 'no';
			$rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';
			$rbfw_enable_daily_rate  = isset( $_POST['rbfw_enable_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daily_rate'] ) : 'no';
			$rbfw_enable_hourly_rate = isset( $_POST['rbfw_enable_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_hourly_rate'] ) : 'no';
			$rbfw_enable_faq_content  = isset( $_POST['rbfw_enable_faq_content'] ) ? rbfw_array_strip( $_POST['rbfw_enable_faq_content'] ) : 'no';
			$rbfw_enable_variations  = isset( $_POST['rbfw_enable_variations'] ) ? rbfw_array_strip( $_POST['rbfw_enable_variations'] ) : 'no';
			$rbfw_enable_md_type_item_qty  = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? $_POST['rbfw_enable_md_type_item_qty'] : 'no';
			

			$rbfw_item_stock_quantity = isset( $_POST['rbfw_item_stock_quantity'] ) ? $_POST['rbfw_item_stock_quantity'] : 0;
			
			$rbfw_enable_resort_daylong_price  = isset( $_POST['rbfw_enable_resort_daylong_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_resort_daylong_price'] ) : 'no';
			// getting resort value
			$rbfw_resort_room_data 	 = isset( $_POST['rbfw_resort_room_data'] ) ? rbfw_array_strip( $_POST['rbfw_resort_room_data'] ) : 0;
			// End getting resort value

			// getting bike/car single day value
			$rbfw_bike_car_sd_data 	 = isset( $_POST['rbfw_bike_car_sd_data'] ) ? rbfw_array_strip( $_POST['rbfw_bike_car_sd_data'] ) : 0;
			// End getting resort value

			// getting bike/car single day value
			$rbfw_variations_data 	 = isset( $_POST['rbfw_variations_data'] ) ? rbfw_array_strip( $_POST['rbfw_variations_data'] ) : [];
			// End getting resort value			
			


			// getting appointment days
			$rbfw_sd_appointment_ondays 	 = isset( $_POST['rbfw_sd_appointment_ondays'] ) ? rbfw_array_strip( $_POST['rbfw_sd_appointment_ondays'] ) : [];
			$rbfw_sd_appointment_max_qty_per_session 	 = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ?  $_POST['rbfw_sd_appointment_max_qty_per_session'] : '';
			
			// End getting appointment days

			update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
			update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
			update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );
			update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $rbfw_enable_dropoff_point );
			update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );
			update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );
			update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );
			update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );
			update_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', $rbfw_enable_md_type_item_qty );
		
			update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );
			
			update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
			
			update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );
			update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );
			
			// sun
			update_post_meta( $post_id, 'rbfw_sun_hourly_rate', $hourly_rate_sun );
			update_post_meta( $post_id, 'rbfw_sun_daily_rate', $daily_rate_sun );
			update_post_meta( $post_id, 'rbfw_enable_sun_day', $enabled_sun );
			// mon
			update_post_meta( $post_id, 'rbfw_mon_hourly_rate', $hourly_rate_mon );
			update_post_meta( $post_id, 'rbfw_mon_daily_rate', $daily_rate_mon );
			update_post_meta( $post_id, 'rbfw_enable_mon_day', $enabled_mon );
			// tue
			update_post_meta( $post_id, 'rbfw_tue_hourly_rate', $hourly_rate_tue );
			update_post_meta( $post_id, 'rbfw_tue_daily_rate', $daily_rate_tue );
			update_post_meta( $post_id, 'rbfw_enable_tue_day', $enabled_tue );
			// wed
			update_post_meta( $post_id, 'rbfw_wed_hourly_rate', $hourly_rate_wed );
			update_post_meta( $post_id, 'rbfw_wed_daily_rate', $daily_rate_wed );
			update_post_meta( $post_id, 'rbfw_enable_wed_day', $enabled_wed );
			// thu
			update_post_meta( $post_id, 'rbfw_thu_hourly_rate', $hourly_rate_thu );
			update_post_meta( $post_id, 'rbfw_thu_daily_rate', $daily_rate_thu );
			update_post_meta( $post_id, 'rbfw_enable_thu_day', $enabled_thu );
			// fri
			update_post_meta( $post_id, 'rbfw_fri_hourly_rate', $hourly_rate_fri );
			update_post_meta( $post_id, 'rbfw_fri_daily_rate', $daily_rate_fri );
			update_post_meta( $post_id, 'rbfw_enable_fri_day', $enabled_fri );
			// sat
			update_post_meta( $post_id, 'rbfw_sat_hourly_rate', $hourly_rate_sat );
			update_post_meta( $post_id, 'rbfw_sat_daily_rate', $daily_rate_sat );
			update_post_meta( $post_id, 'rbfw_enable_sat_day', $enabled_sat );

			// saving resort
			update_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', $rbfw_enable_resort_daylong_price );
			update_post_meta( $post_id, 'rbfw_resort_room_data', $rbfw_resort_room_data );
			// end saving resort

			// saving bike/car single day data
			update_post_meta( $post_id, 'rbfw_bike_car_sd_data', $rbfw_bike_car_sd_data );
			// end saving bike/car single day data

			// saving variations data
			update_post_meta( $post_id, 'rbfw_variations_data', $rbfw_variations_data );
			// end saving variations data			

			// saving FAQ switch
			update_post_meta( $post_id, 'rbfw_enable_faq_content', $rbfw_enable_faq_content );
			// end FAQ switch

			// saving Appointment ondays
			update_post_meta( $post_id, 'rbfw_sd_appointment_ondays', $rbfw_sd_appointment_ondays );
			update_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', $rbfw_sd_appointment_max_qty_per_session );
			
			// end Appointment ondays

			$old_extra_service = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
			$new_extra_service = array();
			
			$service_img     = !empty($_POST['service_img']) ? rbfw_array_strip( $_POST['service_img'] ) : array();
			$names    = $_POST['service_name'] ? rbfw_array_strip( $_POST['service_name'] ) : array();
			$urls     = $_POST['service_price'] ? rbfw_array_strip( $_POST['service_price'] ) : array();
			$service_desc     = $_POST['service_desc'] ? rbfw_array_strip( $_POST['service_desc'] ) : array();
			$qty      = $_POST['service_qty'] ? rbfw_array_strip( $_POST['service_qty'] ) : array();
			$qty_type = !empty($_POST['service_qty_type']) ? rbfw_array_strip( $_POST['service_qty_type'] ) : array();
			$count    = count( $names );
			for ( $i = 0; $i < $count; $i ++ ) {
				
				if (!empty($service_img[ $i ])) :
					$new_extra_service[ $i ]['service_img'] = stripslashes( strip_tags( $service_img[ $i ] ) );
				endif;
				
				if ( $names[ $i ] != '' ) :
					$new_extra_service[ $i ]['service_name'] = stripslashes( strip_tags( $names[ $i ] ) );
				endif;
				
				if ( $urls[ $i ] != '' ) :
					$new_extra_service[ $i ]['service_price'] = stripslashes( strip_tags( $urls[ $i ] ) );
				endif;

				if ( $service_desc[ $i ] != '' ) :
					$new_extra_service[ $i ]['service_desc'] = stripslashes( strip_tags( $service_desc[ $i ] ) );
				endif;

				if ( $qty[ $i ] != '' ) :
					$new_extra_service[ $i ]['service_qty'] = stripslashes( strip_tags( $qty[ $i ] ) );
				endif;
				
				if ( !empty($qty_type[ $i ]) && $qty_type[ $i ] != '' ) :
					$new_extra_service[ $i ]['service_qty_type'] = stripslashes( strip_tags( $qty_type[ $i ] ) );
				endif;
			}
			
			$extra_service_data_arr = apply_filters( 'rbfw_extra_service_arr_save', $new_extra_service );
			
			if ( ! empty( $extra_service_data_arr ) && $extra_service_data_arr != $old_extra_service ) {
				update_post_meta( $post_id, 'rbfw_extra_service_data', $extra_service_data_arr );
			} elseif ( empty( $extra_service_data_arr ) && $old_extra_service ) {
				delete_post_meta( $post_id, 'rbfw_extra_service_data', $old_extra_service );
			}
			
			// Saving Pickup Location Data
			$old_rbfw_pickup_data = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
			$new_rbfw_pickup_data = array();
			$names                = $_POST['loc_pickup_name'] ? rbfw_array_strip( $_POST['loc_pickup_name'] ) : array();
			$count                = count( $names );
			for ( $i = 0; $i < $count; $i ++ ) {
				if ( $names[ $i ] != '' ) :
					$new_rbfw_pickup_data[ $i ]['loc_pickup_name'] = stripslashes( strip_tags( $names[ $i ] ) );
				endif;
			}
			$pickup_data_arr = apply_filters( 'rbfw_pickup_arr_save', $new_rbfw_pickup_data );
			if ( ! empty( $pickup_data_arr ) && $pickup_data_arr != $old_rbfw_pickup_data ) {
				update_post_meta( $post_id, 'rbfw_pickup_data', $pickup_data_arr );
			} elseif ( empty( $pickup_data_arr ) && $old_rbfw_pickup_data ) {
				delete_post_meta( $post_id, 'rbfw_pickup_data', $old_rbfw_pickup_data );
			}
			// Saving Dropoff Data
			$old_rbfw_dropoff_data = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
			$new_rbfw_dropoff_data = array();
			$names                 = $_POST['loc_dropoff_name'] ? rbfw_array_strip( $_POST['loc_dropoff_name'] ) : array();
			$count                 = count( $names );
			for ( $i = 0; $i < $count; $i ++ ) {
				if ( $names[ $i ] != '' ) :
					$new_rbfw_dropoff_data[ $i ]['loc_dropoff_name'] = stripslashes( strip_tags( $names[ $i ] ) );
				endif;
			}
			$dropoff_data_arr = apply_filters( 'rbfw_dropoff_arr_save', $new_rbfw_dropoff_data );
			if ( ! empty( $dropoff_data_arr ) && $dropoff_data_arr != $old_rbfw_dropoff_data ) {
				update_post_meta( $post_id, 'rbfw_dropoff_data', $dropoff_data_arr );
			} elseif ( empty( $dropoff_data_arr ) && $old_rbfw_dropoff_data ) {
				delete_post_meta( $post_id, 'rbfw_dropoff_data', $old_rbfw_dropoff_data );
			}
			save_rbfw_repeated_setting( $post_id, 'mep_event_faq' );
		}
	}
	
	
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
							'title'       => __( 'Add To Cart Form Shortcode', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'add_to_cart_shortcode',
							'placeholder' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
						),

						array(
							'id'          => 'rbfw_feature_category',
							'title'       => __( 'Features Category', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'feature_category',
							'placeholder' => __( 'Features Name', 'booking-and-rental-manager-for-woocommerce' ),
						),
						array(
							'id'          => 'rbfw_releted_rbfw',
							'title'       => __( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please add related items', 'booking-and-rental-manager-for-woocommerce' ),

							'type'        => 'select2',
							'multiple'	  => true,	
							'title_field' => 'rbfw_releted_rbfw_id',
							'btn_text'    => __( 'Add New ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce' ),
							'args'        => 'CPT_%rbfw_item%',
						),

						array(
							'id'          => 'rbfw_single_template',
							'title'       => __( 'Template', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'select',
							'args' => RBFW_Function::all_details_template(),
						),
						array(
							'id'      => 'rbfw_dt_sidebar_switch',
							'title'   => __( 'On/Off Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ),
							'details' => __( 'It displays a sidebar beside the registration form.', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'switch',
							'args' => array(
								'on' => 'On', 
								'off' => 'Off',
							),
							'default' => 'off',
						),

						array(
							'id'          => 'rbfw_dt_sidebar_testimonials',
							'title'       => __( 'Donut Template Sidebar Testimonials', 'booking-and-rental-manager-for-woocommerce' ),
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
							'title'       => __( 'Donut Template Sidebar Content', 'booking-and-rental-manager-for-woocommerce' ),
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
			'meta_box_title' => '<i class="fa-solid fa-circle-info"></i>' . __( 'General Info', 'booking-and-rental-manager-for-woocommerce' ),
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
		new RMFWAddMetaBox( $rbfw_gen_info_boxs_args );

		
		
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
							'title'       => __( 'Gallery Images ', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please upload images for gallery', 'booking-and-rental-manager-for-woocommerce' ),
							'placeholder' => 'https://via.placeholder.com/1000x500',
							'type'        => 'media_multi',
						),
						array(
							'id'          => 'rbfw_gallery_images_additional',
							'title'       => __( 'Additional Gallery Images ', 'booking-and-rental-manager-for-woocommerce' ),
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
		new RMFWAddMetaBox( $rbfw_gallery_meta_boxs_args );
		
		do_action('rbfw_tax_meta_boxs');
				
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
		new RMFWAddMetaBox( $rbfw_pricing_meta_boxs_args );

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
		new RMFWAddMetaBox( $rbfw_location_meta_boxs_args );

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
							'title'   => __( 'On/Off Time Slot', 'booking-and-rental-manager-for-woocommerce' ),
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
		new RMFWAddMetaBox( $rbfw_date_time_meta_boxs_args );

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
		$current_payment_system = rbfw_get_option( 'rbfw_payment_system', 'rbfw_basic_payment_settings');
		$mps_tax_switch = rbfw_get_option( 'rbfw_mps_tax_switch', 'rbfw_basic_payment_settings');

		if ('mps' == $current_payment_system) {
			new RMFWAddMetaBox( $rbfw_mps_tax_meta_boxs_args );
		}
	}