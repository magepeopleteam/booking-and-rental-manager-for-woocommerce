<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Pricing')) {
        class RBFW_Pricing{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#travel_pricing"><i class="fa-solid fa-pager"></i><?php esc_html_e('Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure template Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
                <?php
            }


			public function panel_header($title,$description){
                ?>
                    <section class="bg-light mt-5">
                        <div>
                            <label>
                                <?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $title ); ?>
                            </label>
                            <span><?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $description ); ?></span>
                        </div>
                    </section>
                <?php
            }


			public function rent_type($post_id){
				?>
					<section>
						<div>
							<label for="">
								<?php _e('Rent Types', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<span><?php _e('Select Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<?php  $rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd']; ?>
						<?php $item_type = [
							'bike_car_sd' => 'Bike/Car for single day',
							'bike_car_md' => 'Bike/Car for multiple day',
							'resort' => 'Resort',
							'equipment' => 'Equipment',
							'dress' => 'Dress',
							'appointment' => 'Appointment',
							'others' => 'Others',
						]; ?>
						<select name="rbfw_item_type" id="rbfw_item_type">
							<?php foreach($item_type as $kay => $value): ?>
								<option <?php echo ($kay==$rbfw_item_type)?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
							<?php endforeach; ?>
						</select>
					</section>
			<?php
			}

			public function single_day_table($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd'];
			?>
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
			<?php
			}

			public function extra_service_table($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd'];
			?>
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
				<?php
			}

			public function resort_price_config($post_id){
				$rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd'];
				?>
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
				<?php
			}
			public function rbfw_day_row( $day_name, $day_slug ) {
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
			public function add_tabs_content( $post_id ) {
			?>
				<div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
					<?php $this->section_header(); ?>
					<?php $this->panel_header('Template Settings','Template Settings'); ?>
					<?php $this->rent_type($post_id); ?>
					<?php $this->single_day_table($post_id); ?>
					<?php $this->resort_price_config($post_id); ?>
					<?php include(dirname(__FILE__).'/../templates/mdedo_price.php'); ?>
					<?php $this->extra_service_table($post_id); ?>
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
					// variation start
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

			public function settings_save($post_id) {
                
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
                    $rbfw_item_type = isset( $_POST['rbfw_item_type'] ) ? rbfw_array_strip( $_POST['rbfw_item_type'] ) : [];
					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
                }
            }
		}
		new RBFW_Pricing();
	}
	
	