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
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure price.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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
									<th class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><?php esc_html_e( '(Quantity,Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
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

									<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

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

									<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" value="" placeholder="<?php esc_html_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

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
					<?php $this->panel_header('Extra Service Price Settings','Extra Service Price Settings'); ?>
					<section class="component d-flex flex-column justify-content-between align-items-start mb-2">
						<table id="repeatable-fieldset-one" class='rbfw_pricing_table form-table'>
							<thead>
							<tr>
								<th><?php esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
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
											<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value="<?php echo esc_html( $service_desc ); ?>"/></td>
											<td><input type="number" class="medium" step="0.01" class="mp_formControl" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_html( $service_price ); ?>"/></td>
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
								<td class="">
									<div class="rbfw_service_image_preview"></div>
									<a class="rbfw_service_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
									<input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
								</td>
								<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap"/></td>
								<td><input type="text"  class="mp_formControl " name="service_desc[]" placeholder="Service Description" value=""/></td>
								<td><input type="number" class="mp_formControl medium" step="0.01" name="service_price[]" placeholder="Ex: 10" value=""/></td>
								<td><input type="number" class="medium" name="service_qty[]" placeholder="Ex: 100" value=""/></td>
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

			public function regular_fixed_date($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd'];
				$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
				$rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
				$rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
				$rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
				$rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
				$rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';
				$rbfw_item_stock_quantity = !empty(get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true )) ? get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true ) : 0;
				
				?>
				<section class="rbfw_enable_start_end_date_switch_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort') { echo 'style="display:none"'; } ?>>
					<div>
						<label>
							<?php esc_html_e( 'Rent Specific day', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</label>
						<span>
							<?php _e('Do you want to offer this for specific day', 'booking-and-rental-manager-for-woocommerce'); ?>
						</span>
					</div>
					<div class="rbfw_switch rbfw_switch_return_date">
						<label for="rbfw_enable_start_end_date_on" data-value="on" class="rbfw_enable_start_end_date_label <?php if ( $rbfw_enable_start_end_date == 'yes' ) { echo 'active'; } ?>">
							<input type="radio" name="rbfw_enable_start_end_date" class="rbfw_enable_start_end_date" value="yes" id="rbfw_enable_start_end_date_on" <?php if ( $rbfw_enable_start_end_date == 'yes' ) { echo 'Checked'; } ?>>
							<span>Regular Date</span>
						</label>
						<label data-value="off" for="rbfw_enable_start_end_date_off" class="rbfw_enable_start_end_date_label <?php if ( $rbfw_enable_start_end_date != 'yes' ) { echo 'active'; } ?> off">
							<input type="radio" name="rbfw_enable_start_end_date" class="rbfw_enable_start_end_date" value="no" id="rbfw_enable_start_end_date_off" <?php if ( $rbfw_enable_start_end_date != 'yes' ) { echo 'Checked'; } ?>>
							<span>Fixed Date</span>
						</label>
					</div>
				</section>

				<div class="rbfw_enable_start_end_date_field_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_enable_start_end_date == 'yes') { echo 'style="display:none"'; } ?>>
					
						<section>
							<div class="w-50 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
								<div class=" d-flex justify-content-between align-items-center">
									<input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_start_date" id="rbfw_event_start_date" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
								</div>
							</div>
							<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e( 'Start Time:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
								<div class=" d-flex justify-content-between align-items-center">
									<input type="time" name="rbfw_event_start_time" id="rbfw_event_start_time" value="<?php echo esc_attr( $rbfw_event_start_time ); ?>">

								</div>
							</div>
						</section>

						<section>
							<div class="w-50 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
								<div class=" d-flex justify-content-between align-items-center">
									<input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_end_date" id="rbfw_event_end_date" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
								</div>
							</div>
							<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e( 'End Time:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
								<div class=" d-flex justify-content-between align-items-center">
									<input type="time" name="rbfw_event_end_time" id="rbfw_event_end_time" value="<?php echo esc_attr( $rbfw_event_end_time ); ?>">
								</div>
							</div>
						</section>
					
				</div>

				<div class="rbfw_switch_md_type_item_qty rbfw_item_stock_quantity_row" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' || $rbfw_enable_variations == 'yes') { echo 'style="display:none"'; } ?>>
					
						<section>
							<label>
								<?php esc_html_e( 'Stock Quantity:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span></span></i>
							</label>
							<div>
								<input type="number" name="rbfw_item_stock_quantity" id="rbfw_item_stock_quantity" value="<?php echo esc_attr($rbfw_item_stock_quantity); ?>">
							</div>
						</section>
					
				</div>
				<div class='rbfw-item-type '>
					<div class="rbfw_form_group" data-table="rbfw_item_type_table">
						<table class="form-table rbfw_item_type_table">
							
							<?php echo do_action('rbfw_after_rent_item_type_table_row'); ?>
						</table>
					</div>
				</div>
				<?php
			}

			public function add_tabs_content( $post_id ) {
			?>
				<div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
					<?php $this->section_header(); ?>
					<?php $this->panel_header('Price Settings','Price Settings'); ?>
					<?php $this->rent_type($post_id); ?>
					<?php $this->single_day_table($post_id); ?>
					<?php $this->resort_price_config($post_id); ?>
					<?php $this->regular_fixed_date($post_id); ?>
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
							let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';

							let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
						}
						else{
							let bike_car_sd_type_type_new_data_key = 0;
							let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
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
                    $rbfw_enable_start_end_date = isset( $_POST['rbfw_enable_start_end_date'] ) ? rbfw_array_strip( $_POST['rbfw_enable_start_end_date'] ) : 'no';
					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
					update_post_meta( $post_id, 'rbfw_enable_start_end_date', $rbfw_enable_start_end_date );
				}
            }
		}
		new RBFW_Pricing();
	}
	
	