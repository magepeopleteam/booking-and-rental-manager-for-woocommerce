<?php
	/*
   * @Author 		mage people
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Pricing' ) ) {
		class RBFW_Pricing {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
				add_action( 'wp_ajax_rbfw_load_duration_form', [ $this, 'rbfw_load_duration_form' ] );
				add_action( 'wp_ajax_nopriv_rbfw_load_duration_form', [ $this, 'rbfw_load_duration_form' ] );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#travel_pricing"><i class="fas fa-pager"></i><?php esc_html_e( 'Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function rbfw_load_duration_form() {
				if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
					return;
				}
				// Check and sanitize inputs
				$manage_inventory_as_timely = isset( $_POST['manage_inventory_as_timely'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_inventory_as_timely'] ) ) : '';
				$enable_specific_duration   = isset( $_POST['enable_specific_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_specific_duration'] ) ) : '';
				$total_row                  = isset( $_POST['total_row'] ) ? sanitize_text_field( wp_unslash( $_POST['total_row'] ) ) : '';
				include( RBFW_Function::get_template_path( 'ajax_form/rbfw_load_duration_form.php' ) );
				wp_die();
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure price.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label><?php echo esc_html( $title ); ?></label>
                        <p><?php echo esc_html( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function rent_type( $post_id ) {
				?>
				<?php $this->panel_header( 'Price Settings', 'Price Settings' ); ?>
                <section>
                    <div>
                        <label for="">
							<?php esc_html_e( 'Rent Types', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <p><?php esc_html_e( 'Price will be changed based on this type selection', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
					<?php $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd'; ?>
					<?php $item_type = [
						'bike_car_sd' => 'Rent item for single day',
						'bike_car_md' => 'Rent item for multiple day',
						'resort'      => 'Resort',
						'equipment'   => 'Equipment',
						'dress'       => 'Dress',
						'appointment' => 'Appointment',
						'others'      => 'Others',
					]; ?>
                    <select name="rbfw_item_type" id="rbfw_item_type">
						<?php foreach ( $item_type as $kay => $value ): ?>
                            <option <?php echo esc_attr( $kay == $rbfw_item_type ? 'selected' : '' ); ?> value="<?php echo esc_attr( $kay ); ?>"> <?php echo esc_html( $value ); ?> </option>
						<?php endforeach; ?>
                    </select>
                </section>
				<?php
			}

			public function bike_car_single_day( $post_id ) {
				$rbfw_item_type                  = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_bike_car_sd_data           = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
				$manage_inventory_as_timely      = get_post_meta( $post_id, 'manage_inventory_as_timely', true );
				$manage_inventory_as_timely      = $manage_inventory_as_timely ? $manage_inventory_as_timely : 'off';
				$rbfw_item_stock_quantity_timely = get_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', true ) ? get_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', true ) : 'off';
				$enable_specific_duration        = get_post_meta( $post_id, 'enable_specific_duration', true ) ? get_post_meta( $post_id, 'enable_specific_duration', true ) : 'off';
				$enable_specific_duration        = $enable_specific_duration ? $enable_specific_duration : 'off';
				?>
                <div class="rbfw_bike_car_sd_wrapper <?php echo esc_attr( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) ? 'show' : 'hide'; ?>">
                    <section class="manage_inventory_as_timely ">
                        <div>
                            <label>
								<?php esc_html_e( 'Manage a single-item inventory on an hourly basis.', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php esc_html_e( 'Enabling this allows you to manage a shared inventory for rental items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="manage_inventory_as_timely" value="<?php echo esc_attr( $manage_inventory_as_timely ); ?>" <?php echo esc_attr( $manage_inventory_as_timely == 'on' ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <div class="rbfw_time_inventory rbfw_item_stock_quantity <?php echo esc_html( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : '' ?>">
                        <section class="rbfw_item_quantiry_duration">
                            <div>
                                <label><?php esc_html_e( 'Rent Item Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                <p><?php esc_html_e( 'Add stock quantity that you want allow to rent, add total stock', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <div class="item_stock_quantity">
                                <input type="number" name="rbfw_item_stock_quantity_timely" id="rbfw_item_stock_quantity" value="<?php echo esc_attr( $rbfw_item_stock_quantity_timely ) ?>" placeholder="<?php esc_html_e( 'Ex: 10', '' ); ?>">
                            </div>
                        </section>
                        <section class="rbfw_item_quantiry_duration">
                            <div>
                                <label><?php esc_html_e( 'Enable duration-based rental items.', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                <p><?php esc_html_e( 'Enable this option to set a specific time duration.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" class="enable_specific_duration" name="enable_specific_duration" value="<?php echo esc_attr( $enable_specific_duration ); ?>" <?php echo esc_attr( ( $enable_specific_duration == 'on' ) ? 'checked' : '' ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </section>
                    </div>
                    <section>
                        <div class="w-100">
                            <div style="overflow-x: auto;">
                                <table class='form-table rbfw_bike_car_sd_price_table'>
                                    <thead>
                                    <tr>
                                        <th>
											<?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </th>
                                        <th>
											<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </th>
                                        <th>
											<?php echo wp_kses( sprintf( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ), array( 'b' => array( 'class' => array() ), ) ); ?>
                                        </th>
                                        <th class="rbfw_without_time_inventory <?php echo esc_attr( $manage_inventory_as_timely == 'on' ) ? 'rbfw_hide' : '' ?>">
											<?php $text = sprintf( __( 'Stock/Day <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ) );
												echo wp_kses( $text, array( 'b' => array( 'class' => array(), ), ) ); ?>
                                        </th>
                                        <th class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off' ) ? 'rbfw_hide' : '' ) ?>">
											<?php esc_html_e( 'Start Time', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="required">*</b>
                                        </th>
                                        <th class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off' ) ? 'rbfw_hide' : '' ) ?>">
											<?php esc_html_e( 'End Time', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="required">*</b>
                                        </th>
                                        <th class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on' ) ? 'rbfw_hide' : '' ) ?>">
											<?php esc_html_e( 'Duration', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="required">*</b>
                                        </th>
                                        <th class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on' ) ? 'rbfw_hide' : '' ) ?>">
											<?php esc_html_e( 'Duration Type', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="required">*</b>
                                        </th>
                                        <th class="rbfw_bike_car_sd_price_table_action_column">
											<?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="rbfw_bike_car_sd_price_table_body">
									<?php
										if ( ! empty( $rbfw_bike_car_sd_data ) ) :
											$i = 0;
											foreach ( $rbfw_bike_car_sd_data as $key => $value ):
												?>
                                                <tr class="rbfw_bike_car_sd_price_table_row" data-key="<?php echo esc_attr( $i ); ?>">
                                                    <td><input type="text" class="rbfw_type_title" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php echo esc_attr( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][price]" step=".01" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td class="rbfw_without_time_inventory <?php echo esc_attr( $manage_inventory_as_timely == 'on' ? 'rbfw_hide' : '' ); ?>">
                                                        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </td>
                                                    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off' ) ? 'rbfw_hide' : '' ) ?>">
														<?php rbfw_time_slot_select( 'start_time', $i, isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>
                                                    </td>
                                                    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'off' ) ? 'rbfw_hide' : '' ) ?>">
														<?php rbfw_time_slot_select( 'end_time', $i, isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>
                                                    </td>
                                                    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on' ) ? 'rbfw_hide' : '' ) ?>">
                                                        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][duration]" value="<?php echo esc_attr( isset( $value['duration'] ) ? $value['duration'] : '' ); ?>" placeholder="<?php echo esc_attr( 'Duration', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </td>
                                                    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on' ) ? 'rbfw_hide' : '' ) ?>">
                                                        <select class="medium" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][d_type]">
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Hours' ) ? 'selected' : ''; ?> value="Hours">Hours</option>
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Days' ) ? 'selected' : ''; ?> value="Days">Days</option>
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Weeks' ) ? 'selected' : ''; ?> value="Weeks">Weeks</option>
                                                        </select>
                                                    </td>
                                                    <td class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
														echo 'style="display:none"';
													} ?>>
                                                        <div class="mp_event_remove_move">
                                                            <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                        </div>
                                                    </td>
                                                </tr>
												<?php
												$i ++;
											endforeach;
										else:
											?>
                                            <tr class="rbfw_bike_car_sd_price_table_row" data-key="0">
                                                <td>
                                                    <input type="text" class="rbfw_type_title" name="rbfw_bike_car_sd_data[0][rent_type]" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" placeholder="<?php echo esc_attr( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][price]" step=".01" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="rbfw_without_time_inventory">
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" placeholder="<?php echo esc_attr( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable rbfw_hide">
													<?php rbfw_time_slot_select( 'start_time', 0, isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable rbfw_hide">
													<?php rbfw_time_slot_select( 'end_time', 0, isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable rbfw_hide">
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][duration]" " placeholder="<?php echo esc_attr( 'Duration', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable rbfw_hide">
                                                    <select class="medium" name="rbfw_bike_car_sd_data[0][d_type]">
                                                        <option value="Hours">Hours</option>
                                                        <option value="Days">Days</option>
                                                        <option value="Weeks">Weeks</option>
                                                    </select>
                                                </td>
                                                <td class="rbfw_bike_car_sd_price_table_action_column"<?php if ( $rbfw_item_type == 'appointment' ) {
													echo 'style="display:none"';
												} ?>>
                                                    <div class="mp_event_remove_move">
                                                        <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                        <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                    </div>
                                                </td>
                                            </tr>
										<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-2 <?php echo esc_attr( $rbfw_item_type == 'appointment' ? 'hide' : 'show' ); ?>">
                                <span id="add-bike-car-sd-type-row" data-post_id="<?php echo esc_attr( $post_id ) ?>" class="ppof-button" >
                                    <i class="fas fa-circle-plus"></i>
                                    <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </span>

                                <?php if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) { ?>
                                    <span id="sync-with-sessional-price-sd" data-post_id="<?php echo esc_attr( $post_id ) ?>" class="ppof-button sync-with-sessional-price-sd" >
                                        <?php esc_html_e( 'Sync with sessional price', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    </span>
                                <?php } ?>
                            </p>
                        </div>
                    </section>
                </div>
				<?php
			}

			public function rbfw_appointment( $post_id ) {
				$rbfw_item_type        = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
				?>
                <div class="rbfw_bike_car_sd_wrapper <?php echo esc_attr( $rbfw_item_type == 'appointment' ) ? 'show' : 'hide'; ?>">
                    <section>
                        <div class="w-100">
                            <div style="overflow-x: auto;">
                                <table class='form-table rbfw_bike_car_sd_price_table'>
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
											echo 'style="display:none"';
										} ?>><?php esc_html_e( 'Stock/Day <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
											echo 'style="display:none"';
										} ?>><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="rbfw_bike_car_sd_price_table_body">
									<?php
										if ( ! empty( $rbfw_bike_car_sd_data ) ) :
											$i = 0;
											foreach ( $rbfw_bike_car_sd_data as $key => $value ):
												?>
                                                <tr class="rbfw_bike_car_sd_price_table_row" data-key="<?php echo esc_attr( $i ); ?>">
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php echo esc_attr( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][price]" step=".01" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td class="rbfw_bike_car_sd_price_table_action_column" <?php echo ( $rbfw_item_type == 'appointment' )?'style="display:none"':''; ?>>
                                                        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php echo esc_attr( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </td>
                                                    <td class="rbfw_bike_car_sd_price_table_action_column" <?php echo ( $rbfw_item_type == 'appointment' )?'style="display:none"':''; ?>>
                                                        <div class="mp_event_remove_move">
                                                            <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                        </div>
                                                    </td>
                                                </tr>
												<?php
												$i ++;
											endforeach;
										else:
											?>
                                            <tr class="rbfw_bike_car_sd_price_table_row" data-key="0">
                                                <td><input type="text" name="rbfw_bike_car_sd_data[0][rent_type]" value="" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td><input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" value="" placeholder="<?php echo esc_attr( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td><input type="number" name="rbfw_bike_car_sd_data[0][price]" step=".01" value="" placeholder="<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
													echo 'style="display:none"';
												} ?> ><input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" value="" placeholder="<?php echo esc_attr( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td class="rbfw_bike_car_sd_price_table_action_column"<?php if ( $rbfw_item_type == 'appointment' ) {
													echo 'style="display:none"';
												} ?>>
                                                    <div class="mp_event_remove_move">
                                                        <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                        <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                    </div>
                                                </td>
                                            </tr>
										<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-2 <?php echo esc_attr( $rbfw_item_type == 'appointment' ? 'show' : 'show' ); ?>">
                                <button id="add-bike-car-sd-type-row" class="ppof-button" <?php if ( $rbfw_item_type == 'appointment' ) {
									echo 'style="display:none"';
								} ?>><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            </p>
                        </div>
                    </section>
                </div>
				<?php
			}

			public function resort_price_config( $post_id ) {
				$rbfw_enable_resort_daylong_price = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';
				$rbfw_item_type                   = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_resort_room_data            = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
				?>
                <div class="rbfw_resort_price_config_wrapper " style="display: <?php if ( $rbfw_item_type == 'resort' ) {
					echo esc_attr( 'block' );
				} else {
					echo esc_attr( 'none' );
				} ?>;">
					<?php $this->panel_header( 'Resort Price Configuration', 'Here you can set price for resort.' ); ?>
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Day-long Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php echo esc_html__( 'If you like to set price for same day check-in/check-out this option can be used.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_resort_daylong_price" value="<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? $rbfw_enable_resort_daylong_price : 'no' ); ?>" <?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <section>
                        <div class="w-100">
							<?php do_action( 'rbfw_before_resort_price_table' ); ?>
                            <div style="overflow-x:auto;">
                                <table class='form-table rbfw_resort_price_table w-100'>
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th class="resort_day_long_price" style="display:<?php echo esc_attr( ( $rbfw_enable_resort_daylong_price == 'yes' ) ? 'table-cell' : 'none' ); ?>"><?php esc_html_e( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th colspan="2"><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="rbfw_resort_price_table_body">
									<?php
										if ( ! empty( $rbfw_resort_room_data ) ) :
											$i = 0;
											foreach ( $rbfw_resort_room_data as $key => $value ):
												$img_url = wp_get_attachment_url( $value['rbfw_room_image'] );
												?>
                                                <tr class="rbfw_resort_price_table_row" data-key="<?php echo esc_attr( $i ); ?>">
                                                    <td>
                                                        <input class="rbfw_room_title" type="text" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][room_type]" value="<?php echo esc_attr( $value['room_type'] ); ?>" placeholder="<?php echo esc_attr( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="rbfw_room_type_image_preview">
															<?php if ( $img_url ): ?>
                                                                <img src="<?php echo esc_url( $img_url ); ?>">
															<?php endif; ?>
                                                        </div>
                                                        <a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a>
                                                        <input type="hidden" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_image]" value="<?php echo esc_attr( $value['rbfw_room_image'] ); ?>" class="rbfw_room_image"/>
                                                    </td>
                                                    <td class="resort_day_long_price" style="display: <?php if ( ( $rbfw_item_type == 'resort' ) && $rbfw_enable_resort_daylong_price == 'yes' ) {
														echo esc_attr( 'table-cell' );
													} else {
														echo esc_attr( 'none' );
													} ?>;"><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_daylong_rate]" step=".01" value="<?php echo esc_attr( $value['rbfw_room_daylong_rate'] ); ?>" placeholder="<?php echo esc_attr( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                                                    <td><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_daynight_rate]" step=".01" value="<?php echo esc_attr( $value['rbfw_room_daynight_rate'] ); ?>" placeholder="<?php echo esc_attr( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                                                    <td><input type="text" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_desc]" value="<?php echo esc_attr( $value['rbfw_room_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                                                    <td><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_available_qty]" step=".01" value="<?php echo esc_attr( $value['rbfw_room_available_qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                                                    <td>
                                                        <div class="mp_event_remove_move">
                                                            <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                            <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                        </div>
                                                    </td>
                                                </tr>
												<?php
												$i ++;
											endforeach;
										else:
											?>
                                            <tr class="rbfw_resort_price_table_row" data-key="0">
                                                <td>
                                                    <input type="text" class="rbfw_room_title" name="rbfw_resort_room_data[0][room_type]" value="" placeholder="<?php echo esc_attr( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="text-center">
                                                    <div class="rbfw_room_type_image_preview"></div>
                                                    <a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i> </a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a>
                                                    <input type="hidden" name="rbfw_resort_room_data[0][rbfw_room_image]" value="" class="rbfw_room_image"/>
                                                </td>
                                                <td class="resort_day_long_price"
                                                    style="display: <?php echo ( $rbfw_item_type === 'resort' && $rbfw_enable_resort_daylong_price === 'yes' )
													    ? esc_attr( 'block' )
													    : esc_attr( 'none' ); ?>;">
                                                    <input
                                                        type="number"
                                                        class="medium"
                                                        name="rbfw_resort_room_data[0][rbfw_room_daylong_rate]"
                                                        step=".01"
                                                        value="<?php echo esc_attr( '' ); ?>"
                                                        placeholder="<?php esc_attr_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        class="medium"
                                                        name="rbfw_resort_room_data[0][rbfw_room_daynight_rate]"
                                                        step=".01"
                                                        value="<?php echo esc_attr( '' ); ?>"
                                                        placeholder="<?php esc_attr_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="rbfw_resort_room_data[0][rbfw_room_desc]"
                                                        value="<?php echo esc_attr( '' ); ?>"
                                                        placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        class="medium"
                                                        name="rbfw_resort_room_data[0][rbfw_room_available_qty]"
                                                        step=".01"
                                                        value="<?php echo esc_attr( '' ); ?>"
                                                        placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                </td>
                                                <td>
                                                    <div class="mp_event_remove_move">
                                                        <button class="button remove-row"><i class="fas fa-trash-can"></i></button>
                                                        <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                                    </div>
                                                </td>
                                            </tr>
										<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <p class="mt-2">
                                <span id="add-resort-type-row" class="ppof-button"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Resort Type', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <?php if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) && is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' ) ) { ?>
                                    <span id="sync-with-sessional-price" class="ppof-button sync-with-sessional-price"><?php esc_html_e( 'Sync with sessional price and multi day saver', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <?php }elseif(is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' )){ ?>
                                    <span id="sync-with-sessional-price" class="ppof-button sync-with-sessional-price"><?php esc_html_e( 'Sync with sessional price', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <?php }elseif(is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' )){ ?>
                                    <span id="sync-with-sessional-price" class="ppof-button sync-with-sessional-price"><?php esc_html_e( 'Sync multi day saver', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <?php } ?>
                            </p>

							<?php do_action( 'rbfw_after_resort_price_table' ); ?>



                        </div>
                    </section>
                </div>
				<?php
			}

			public function rbfw_day_row( $day_name, $day_slug ) {
				$hourly_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) : '';
				$daily_rate  = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) : '';
				$enable      = ! empty( get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) ) ? get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) : '';
				?>
                <tr>
                    <th><?php echo esc_html( $day_name ); ?></th>
                    <td>
                        <input
                            type="number"
                            name="rbfw_<?php echo esc_attr( $day_slug ); ?>_hourly_rate"
                            value="<?php echo esc_attr( $hourly_rate ); ?>"
                            placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                    </td>
                    <td>
                        <input
                            type="number"
                            name="rbfw_<?php echo esc_attr( $day_slug ); ?>_daily_rate"
                            value="<?php echo esc_attr( $daily_rate ); ?>"
                            placeholder="<?php esc_attr_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                    </td>
                    <td>
                        <input
                            type="checkbox"
                            name="rbfw_enable_<?php echo esc_attr( $day_slug ); ?>_day"
                            value="yes"
							<?php checked( $enable, 'yes' ); ?>>
                    </td>
                </tr>
				<?php
			}

			public function appointment( $post_id ) {
				$rbfw_item_type                          = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_sd_appointment_ondays_data         = get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) : [];
				$rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) : 'appointment';
				?>
                <div class="rbfw_switch_sd_appointment_row <?php echo esc_attr( $rbfw_item_type != 'appointment' ) ? 'hide' : 'show'; ?>">
                    <section>
                        <label>
							<?php esc_html_e( 'Maximum Allowed Quantity Per Session/Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <input type="number" name="rbfw_sd_appointment_max_qty_per_session" id="rbfw_sd_appointment_max_qty_per_session" value="<?php echo esc_attr( $rbfw_sd_appointment_max_qty_per_session ); ?>">
                    </section>
                </div>
                <section class="hide">
                    <label class="w-30">
						<?php esc_html_e( 'Appointment Ondays', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </label>
                    <div class="rbfw_appointment_ondays_wrap">
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Sunday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Sunday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Sunday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Monday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Monday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Monday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Tuesday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Tuesday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Tuesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Wednesday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Wednesday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Wednesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Thursday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Thursday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Thursday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Friday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Friday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Friday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw_appointment_ondays_value">
                            <input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Saturday" <?php if ( ! empty( $rbfw_sd_appointment_ondays_data ) && in_array( 'Saturday', $rbfw_sd_appointment_ondays_data ) ) {
								echo 'checked';
							} ?>>
                            <span><?php esc_html_e( 'Saturday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                    </div>
                </section>
				<?php
			}

			public function general_price_config( $post_id ) {
				$rbfw_enable_daily_rate    = get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) : 'yes';

                $rbfw_enable_time_picker    = get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) ? get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) : 'no';
                $rbfw_enable_hourly_rate   = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';

                $rbfw_hourly_threshold   = get_post_meta( $post_id, 'rbfw_hourly_threshold', true ) ? get_post_meta( $post_id, 'rbfw_hourly_threshold', true ) : '0';
                $rbfw_enable_hourly_threshold    = get_post_meta( $post_id, 'rbfw_enable_hourly_threshold', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_threshold', true ) : 'no';


                $rbfw_daily_rate           = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
				$rbfw_hourly_rate          = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
				$rbfw_item_type            = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$mdedo                     = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment' ) ? 'block' : 'none';
				$rbfw_enable_daywise_price = get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) : 'no';
				?>
                <div class="rbfw_general_price_config_wrapper " style="display: <?php echo esc_attr( $mdedo ) ?>;">

                    <?php $this->panel_header( 'General Price Configuration', 'General Price Configuration' ); ?>

                    <div class="rbfw_multi_day_price_conf">
                        <!-- Daily Price -->
                        <div class="item">
                            <div class="item-left">
                                <div class="label">Daily Price</div>
                                <div class="description">Pricing will be calculated based on number of day.</div>
                            </div>
                            <div class="item-right">
                                <div class="toggle daily-price-toggle <?php echo esc_attr( $rbfw_enable_daily_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <input type="number" name="rbfw_daily_rate" step="0.01" value="<?php echo esc_attr( $rbfw_daily_rate ); ?>" placeholder="<?php esc_attr_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_daily_rate == 'no' ? 'disabled' : '' ); ?> id="daily-price-input" class="price-input">
                                <input type="hidden" name="rbfw_enable_daily_rate" id="rbfw_enable_daily_rate" value="<?php echo esc_attr( $rbfw_enable_daily_rate ); ?>">
                            </div>
                        </div>


                        <!-- Time Picker Toggle -->
                        <div class="item">
                            <div class="item-left">
                                <div class="label">Enable Time Picker</div>
                                <div class="description">
                                    Toggle to enable time selection for more precise rental periods.
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="toggle time-picker-toggle <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <input type="hidden" name="rbfw_enable_time_picker" class="rbfw_enable_time_picker" value="<?php echo esc_attr( $rbfw_enable_time_picker ); ?>">
                            </div>
                        </div>

                        <!-- Hourly Price (conditional) -->
                        <div class="item hourly-price-item" style="display: <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'flex' : 'none' ); ?>;">
                            <div class="item-left">
                                <div class="label">Hourly Price</div>
                                <div class="description">Pricing will be calculated as per hour.</div>
                            </div>
                            <div class="item-right">
                                <div class="toggle hourly-price-toggle <?php echo esc_attr( $rbfw_enable_hourly_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <input type="number" name="rbfw_hourly_rate" step="0.01" value="<?php echo esc_attr( $rbfw_hourly_rate ); ?>" placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_hourly_rate == 'no' ? 'disabled' : '' ); ?> id="hourly-price-input" class="price-input">
                                <input type="hidden" name="rbfw_enable_hourly_rate" id="rbfw_enable_hourly_rate" value="<?php echo esc_attr( $rbfw_enable_hourly_rate ); ?>">
                            </div>
                        </div>

                        <!-- Hour Threshold (conditional) -->
                        <div class="item hour-threshold-item" style="display: <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'flex' : 'none' ); ?>;">
                            <div class="item-left">
                                <div class="label">Hour Threshold</div>
                                <div class="description">
                                    If total hours are more than <span id="hour-threshold-display">6</span>, count as full day. If less, day will not count.
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="toggle hour-threshold-toggle <?php echo esc_attr( $rbfw_enable_hourly_threshold == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <input type="number" name="rbfw_hourly_threshold" step="0.01" value="<?php echo esc_attr( $rbfw_hourly_threshold ); ?>" placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_hourly_threshold == 'no' ? 'disabled' : '' ); ?> id="hour-threshold-input" class="price-input">
                                <input type="hidden" name="rbfw_enable_hourly_threshold" id="rbfw_enable_hourly_threshold" value="<?php echo esc_attr( $rbfw_enable_hourly_threshold ); ?>">
                            </div>
                        </div>

                        <!-- Time Slots (conditional) -->

                        <?php $this->multiple_time_slot_with_particular( $post_id, $rbfw_enable_time_picker,'md' ); ?>




                    </div>





                    <?php do_action( 'rbfw_before_general_price_table' ); ?>

                    <?php do_action( 'rbfw_before_general_price_table_row' ); ?>


					<?php $this->panel_header( 'Day-wise Price Configuration ', 'Day-wise Price Configuration lets you set different prices for each day of the week' ); ?>
                    <section>
                        <div>
                            <label>
								<?php esc_html_e( 'Enable Day-wise Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <P>
								<?php esc_html_e( 'Enabling this will set prices based on the day of the week, overriding the general daily price', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </P>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_daywise_price" value="<?php echo esc_attr( $rbfw_enable_daywise_price ); ?>" <?php echo esc_attr( ( $rbfw_enable_daywise_price == 'yes' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <section class="day-wise-price-configuration <?php echo esc_attr( ( $rbfw_enable_daywise_price == 'yes' ) ? 'show' : 'hide' ); ?>">
                        <table class='form-table'>
							<?php do_action( 'rbfw_before_week_price_table_row' ); ?>
                            <thead>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Day Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row"><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row"><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row"><?php esc_html_e( 'Enable/Disable', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
								$this->rbfw_day_row( esc_html__( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
								$this->rbfw_day_row( esc_html__( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
								$this->rbfw_day_row( esc_html__( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
								$this->rbfw_day_row( esc_html__( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
								$this->rbfw_day_row( esc_html__( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
								$this->rbfw_day_row( esc_html__( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
								$this->rbfw_day_row( esc_html__( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );
								//do_action( 'rbfw_after_week_price_table_row' );
							?>
                            </tbody>
                        </table>
                    </section>
                    <br>

					<?php do_action( 'rbfw_after_general_price_table_row' ); ?>

					<?php do_action( 'rbfw_after_general_price_table', $post_id ); ?>
                </div>
                <?php do_action( 'rbfw_after_rent_item_type_table_row' ); ?>

				<?php do_action( 'rbfw_after_week_price_table', $post_id ); ?>


                <?php do_action( 'rbfw_after_room_type_price_saver_price_table', $post_id ); ?>
				<?php do_action( 'rbfw_after_extra_service_table' ); ?>


                <div class="rbfw_multi_day_price_conf rbfw_bike_car_sd_wrapper <?php echo esc_attr( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) ? 'show' : 'hide'; ?>">
                    <div class="item">
                        <div class="item-left">
                            <div class="label">Enable Time Picker</div>
                            <div class="description">
                                Toggle to enable time selection for more precise rental periods.
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="toggle time-picker-toggle <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'active' : '' ); ?>">
                                <div class="toggle-knob"></div>
                            </div>
                            <input type="hidden" name="rbfw_enable_time_picker" class="rbfw_enable_time_picker" value="<?php echo esc_attr( $rbfw_enable_time_picker ); ?>">
                        </div>
                    </div>





                    <!-- Time Slots (conditional) -->

                    <?php $this->multiple_time_slot_with_particular( $post_id, $rbfw_enable_time_picker,'sd' ); ?>


                </div>

				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
					<?php $this->section_header(); ?>
					<?php $this->rent_type( $post_id ); ?>
					<?php $this->appointment( $post_id ); ?>
					<?php $this->bike_car_single_day( $post_id ); ?>
					<?php //$this->rbfw_appointment($post_id); ?>
                    <?php $this->resort_price_config( $post_id ); ?>
					<?php $this->general_price_config( $post_id ); ?>


                </div>

				<?php
			}

            public function multiple_time_slot_with_particular($post_id, $rbfw_enable_time_picker,$type='sd')
            {
                ?>
                <div class="time-slots-section" style="display: <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'block' : 'none' ); ?>;">
                    <div class="section">
                        <h2><?php echo esc_html__( 'Time Slots Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                        <p><?php echo esc_html__( 'Configure available 30-minute time slots for booking', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>

                    <div class="time-slots-container">
                        <div class="time-slots" id="time-slots-container">
                            <?php
                            $rdfw_available_time        = get_post_meta( $post_id, 'rdfw_available_time', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rdfw_available_time', true ) ) : [];
                            $array_dimension = RBFW_Frontend::count_array_dimensions($rdfw_available_time);
                            if($array_dimension == 1){
                                $i = 1;
                                $result = [];
                                foreach ($rdfw_available_time as $time) {
                                    $result[] = ['id'=>$i, 'time'=>$time, 'status'=>'enabled'];
                                    $i++;
                                }
                                $rdfw_available_time = $result;
                            }
                            $i = 0;
                            foreach ($rdfw_available_time as $key => $item) { if(is_array($item)){
                                ?>
                                <div class="time-slot <?php echo $item['status'] ?>" data-id="<?php echo $i ?>">
                                    <span class="time-slot-time"><?php echo $item['time'] ?></span>
                                    <?php if($type=='md'){ ?>
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][id]" value="<?php echo $i ?>">
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][time]" value="<?php echo $item['time'] ?>">
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][status]" value="<?php echo $item['status'] ?>">
                                    <?php }else{ ?>
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][id]" value="<?php echo $i ?>">
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][time]" value="<?php echo $item['time'] ?>">
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][status]" value="<?php echo $item['status'] ?>">
                                    <?php } ?>
                                    <div class="time-slot-indicator" title="Click to disable"></div>
                                    <div class="time-slot-remove" title="Remove time slot">×</div>
                                </div>
                                <?php $i++; } } ?>
                        </div>
                    </div>

                    <div class="add-slot-container">
                        <div class="label"><?php echo esc_html__( 'Add New Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                        <div class="add-slot-form">
                            <div>
                                <label for="new-slot-time"><?php echo esc_html__( 'Time (30 min slot)', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                <input type="time" class="new-slot-time">
                            </div>
                            <button class="add-slot-btn" data-name_attr="rdfw_available_time" data-rent_type="<?php echo $type ?>" disabled><?php echo esc_html__( 'Add Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        </div>
                    </div>


                    <?php $particulars_data = get_post_meta( $post_id, 'rbfw_particulars_data', true );


                    ?>
                    <div class="mpStyle">
                        <?php $this->panel_header( 'Particular Settings', 'Here you can set Particulars' ); ?>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Particular date time slots', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <p><?php echo esc_html__( 'It enables/disables the particulars for selection.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <?php $rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off'; ?>
                            <label class="switch">
                                <input type="checkbox" name="rbfw_particular_switch" value="<?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? $rbfw_particular_switch : 'off' ); ?>" <?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'checked' : '' ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </section>
                        <!-- Multiple Particular Section -->
                        <div class="available-particular <?php  echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'show' : 'hide' ); ?>">
                                 <div class="">
                                    <div class="d-flex justify-content-between row header">
                                        <div><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div><?php esc_html_e( 'Available Time Slots', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div><?php esc_html_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </div>
                                    <div class="rbfw_pdwt_insert">
                                        <?php $i=0;  foreach ( $particulars_data as $index => $particular ){ if( $particular['start_date'] && $particular['end_date'] && isset($particular['available_time'])){ ?>
                                            <div class="rbfw_pdwt_row d-flex justify-content-between">
                                                <?php if($type=='md'){ ?>
                                                    <div>
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars[<?php echo esc_attr( $i ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                                    </div>
                                                    <div>
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars[<?php echo esc_attr( $i ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                                    </div>
                                                    <?php } else{ ?>
                                                    <div>
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_sd[<?php echo esc_attr( $i ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                                    </div>
                                                    <div>
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_sd[<?php echo esc_attr( $i ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                                    </div>
                                                <?php } ?>

                                                <div>
                                                    <div class="time-slots-container">
                                                        <div class="time-slots" id="time-slots-container">

                                                            <?php
                                                            $particular_available_time        = $particular['available_time'];
                                                            $array_dimension = RBFW_Frontend::count_array_dimensions($particular_available_time);
                                                            if($array_dimension == 1){
                                                                $k = 0;
                                                                $result = [];
                                                                foreach ($particular_available_time as $time) {
                                                                    $result[] = ['id'=>$k, 'time'=>$time, 'status'=>'enabled'];
                                                                    $k++;
                                                                }
                                                                $particular_available_time = $result;
                                                            }

                                                            $j = 0;

                                                            foreach ($particular_available_time as $key => $item) { if(is_array($item)){

                                                                ?>

                                                                <div class="time-slot <?php echo $item['status'] ?>" data-id="<?php echo $i ?>">
                                                                    <span class="time-slot-time"><?php echo $item['time'] ?></span>
                                                                    <?php if($type=='md'){ ?>
                                                                        <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][id]" value="<?php echo $i ?>">
                                                                        <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][time]" value="<?php echo $item['time'] ?>">
                                                                        <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][status]" value="<?php echo $item['status'] ?>">
                                                                    <?php }else{ ?>
                                                                        <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][id]" value="<?php echo $i ?>">
                                                                        <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][time]" value="<?php echo $item['time'] ?>">
                                                                        <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][status]" value="<?php echo $item['status'] ?>">
                                                                    <?php } ?>
                                                                    <div class="time-slot-indicator" title="Click to disable"></div>
                                                                    <div class="time-slot-remove" title="Remove time slot">×</div>
                                                                </div>

                                                                <?php $j++; } } ?>

                                                        </div>
                                                    </div>

                                                    <div class="add-slot-container">
                                                        <div class="label"><?php echo esc_html__( 'Add New Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                                        <div class="add-slot-form">
                                                            <div>
                                                                <label for="new-slot-time"><?php echo esc_html__( 'Time (30 min slot)', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                                                <input type="time" class="new-slot-time">
                                                            </div>
                                                            <button class="add-slot-btn" data-name_attr="rbfw_particulars" data-rent_type="<?php echo $type ?>" data-particular_id="<?php echo $i ?>" disabled><?php echo esc_html__( 'Add Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button type="button" class="remove-row button"><?php echo esc_html__( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                </div>
                                            </div>
                                            <?php $i++; } } ?>
                                    </div>
                                    <div>
                                        <button type="button" id="add-particular-row" data-rent_type="<?php echo $type ?>" class="ppof-button">
                                            <i class="fa-solid fa-circle-plus"></i>&nbsp;
                                            <?php echo esc_html__( 'Add Another', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </button>
                                    </div>
                                </div>



                            <div class="mp_hidden_content">
                                <div class="mp_hidden_item" >
                                    <div class="rbfw_pdwt_row d-flex justify-content-between">
                                        <div>
                                            <input type="text" class="rbfw_start_date date_type rbfw_particulars_date">
                                        </div>
                                        <div>
                                            <input type="text" class="rbfw_end_date date_type rbfw_particulars_date">
                                        </div>
                                        <div>
                                            <div class="time-slots-container">
                                                <div class="time-slots" id="time-slots-container">


                                                </div>
                                            </div>

                                            <div class="add-slot-container">
                                                <div class="label"><?php echo esc_html__( 'Add New Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                                <div class="add-slot-form">
                                                    <div>
                                                        <label for="new-slot-time"><?php echo esc_html__( 'Time (30 min slot)', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                                        <input type="time" class="new-slot-time">
                                                    </div>
                                                    <button class="add-slot-btn" data-name_attr="rbfw_particulars" data-rent_type="<?php echo $type ?>" disabled=""><?php echo esc_html__( 'Add Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                </div>
                                            </div>

                                        </div>
                                        <div><button class="remove-row button"><?php echo esc_html__( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?></button></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <script>


                    jQuery(document).ready(function () {
                        const dailyPriceToggle = jQuery('.daily-price-toggle');
                        const hourThresholdToggle = jQuery('.hour-threshold-toggle');
                        const timePickerToggle = jQuery('.time-picker-toggle');
                        const hourlyPriceToggle = jQuery('.hourly-price-toggle');
                        const hourlyPriceItem = jQuery('.hourly-price-item');
                        const hourThresholdItem = jQuery('.hour-threshold-item');
                        const timeSlotsSection = jQuery('.time-slots-section');


                        const dailyPriceInput = jQuery('#daily-price-input');
                        const hourlyPriceInput = jQuery('#hourly-price-input');
                        const hourThresholdInput = jQuery('#hour-threshold-input');
                        const hourThresholdDisplay = jQuery('#hour-threshold-display');
                        const rbfw_enable_daily_rate = jQuery('#rbfw_enable_daily_rate');
                        const rbfw_enable_time_picker = jQuery('#rbfw_enable_time_picker');
                        const rbfw_enable_hourly_rate = jQuery('#rbfw_enable_hourly_rate');
                        const rbfw_enable_hourly_threshold = jQuery('#rbfw_enable_hourly_threshold');

                        // State
                        let dailyPriceEnabled = rbfw_enable_daily_rate.val() === 'yes';
                        let timePickerEnabled = rbfw_enable_time_picker.val() === 'yes';
                        let hourlyPriceEnabled = rbfw_enable_time_picker.val() === 'yes';
                        let hourThresholdEnabled = rbfw_enable_time_picker.val() === 'yes';
                        let timeSlots = [];

                        // Toggle functions
                        function toggleDailyPrice() {
                            dailyPriceEnabled = !dailyPriceEnabled;
                            dailyPriceToggle.toggleClass('active', dailyPriceEnabled);
                            dailyPriceInput.prop('disabled', !dailyPriceEnabled);
                            rbfw_enable_daily_rate.val(dailyPriceEnabled ? 'yes' : 'no');
                        }

                        function toggleHourThreshold() {
                            hourThresholdEnabled = !hourThresholdEnabled;
                            hourThresholdToggle.toggleClass('active', hourThresholdEnabled);
                            hourThresholdInput.prop('disabled', !hourThresholdEnabled);
                            rbfw_enable_hourly_threshold.val(hourThresholdEnabled ? 'yes' : 'no');
                        }

                        jQuery('.time-picker-toggle').on('click', function() {
                            timePickerEnabled = !timePickerEnabled;
                            timePickerToggle.toggleClass('active', timePickerEnabled);
                            hourlyPriceItem.css('display', timePickerEnabled ? 'flex' : 'none');
                            hourThresholdItem.css('display', timePickerEnabled ? 'flex' : 'none');
                            timeSlotsSection.css('display', timePickerEnabled ? 'block' : 'none');

                            const $toggle = jQuery(this);
                            const $input = jQuery('.rbfw_enable_time_picker');
                            if ($toggle.hasClass('active')) {
                                $input.val('yes');
                            } else {
                                $input.val('no');
                            }

                        })




                        function toggleHourlyPrice() {
                            hourlyPriceEnabled = !hourlyPriceEnabled;
                            hourlyPriceToggle.toggleClass('active', hourlyPriceEnabled);
                            hourlyPriceInput.prop('disabled', !hourlyPriceEnabled);
                            rbfw_enable_hourly_rate.val(hourlyPriceEnabled ? 'yes' : 'no');
                        }

                        // Input change handlers
                        dailyPriceInput.on('change', function () {
                            const value = parseFloat(jQuery(this).val());
                            if (isNaN(value) || value < 0) {
                                jQuery(this).val(0);
                            }
                        });

                        hourlyPriceInput.on('change', function () {
                            const value = parseFloat(jQuery(this).val());
                            if (isNaN(value) || value < 0) {
                                jQuery(this).val(0);
                            }
                        });

                        hourThresholdInput.on('change', function () {
                            const value = parseFloat(jQuery(this).val());
                            if (isNaN(value) || value < 0) {
                                jQuery(this).val(0);
                            }
                            hourThresholdDisplay.text(jQuery(this).val());
                        });

                        // Event listeners for toggles
                        dailyPriceToggle.on('click', toggleDailyPrice);
                        hourThresholdToggle.on('click', toggleHourThreshold);
                        hourlyPriceToggle.on('click', toggleHourlyPrice);
                    });


                </script>

                <?php

            }


            public function multiple_time_slot_select_for_particular_date( $rbfw_time_slots, $available_times, $index, $post_id ) {
                global $RBFW_Timeslots_Page;
                $rbfw_time_slots = $RBFW_Timeslots_Page->rbfw_format_time_slot( $rbfw_time_slots );
                asort( $rbfw_time_slots );
                $rdfw_available_time_update = [];
                foreach ( $available_times as $single ) {
                    $rdfw_available_time_update[] = gmdate( 'H:i', strtotime( $single ) );
                }
                ?>
                <select name="rbfw_particulars[<?php echo esc_attr( $index ); ?>][available_time][]" multiple class="select2-hidden-accessible">
                    <?php foreach ( $rbfw_time_slots as $key => $value ): ?>
                        <?php if ( get_the_title( $post_id ) == 'Auto Draft' ) { ?>
                            <option selected value="<?php echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); ?>"> <?php echo esc_attr( $key ); ?> </option>
                        <?php } else { ?>
                            <option <?php echo esc_attr( in_array( gmdate( 'H:i', strtotime( $value ) ), $rdfw_available_time_update ) ) ? 'selected' : '' ?> value="<?php echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); ?>"> <?php echo esc_html( gmdate( 'H:i', strtotime( $value ) ) ); ?> </option>
                        <?php } ?>
                    <?php endforeach; ?>
                </select>
                <?php
            }

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$input_data_sabitized = RBFW_Function::data_sanitize( $_POST );
					$rbfw_item_type = isset( $_POST['rbfw_item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_item_type'] ) ) : [];
					$rbfw_enable_daily_rate             = isset( $_POST['rbfw_enable_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_daily_rate'] ) ) : 'no';
					$daily_rate                         = isset( $_POST['rbfw_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_daily_rate'] ) ) : 0;

                    $rbfw_enable_time_picker            = isset( $_POST['rbfw_enable_time_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_time_picker'] ) ) : 'no';

                    $rbfw_hourly_threshold       = isset( $_POST['rbfw_hourly_threshold'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_hourly_threshold'] ) ) : '0';
                    $rbfw_enable_hourly_threshold       = isset( $_POST['rbfw_enable_hourly_threshold'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_hourly_threshold'] ) ) : 'no';

                    $rbfw_enable_hourly_rate            = isset( $_POST['rbfw_enable_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_hourly_rate'] ) ) : 'no';
                    $hourly_rate                        = isset( $_POST['rbfw_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_hourly_rate'] ) ) : 0;

                    $rbfw_enable_daywise_price          = isset( $_POST['rbfw_enable_daywise_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_daywise_price'] ) ) : 'no';

                    $rbfw_item_type          = isset( $_POST['rbfw_item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_item_type'] ) ) : '';

                    if($rbfw_item_type=='bike_car_md' || $rbfw_item_type=='equipment' || $rbfw_item_type=='dress' || $rbfw_item_type=='others'){
                        $rdfw_available_time              = isset( $input_data_sabitized['rdfw_available_time'] ) ? $input_data_sabitized['rdfw_available_time'] : [];
                        $particulars_data           = isset( $_POST['rbfw_particulars'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_particulars'] ) : [];

                    }else{
                        $rdfw_available_time              = isset( $input_data_sabitized['rdfw_available_time_sd'] ) ? $input_data_sabitized['rdfw_available_time_sd'] : [];
                        $particulars_data           = isset( $_POST['rbfw_particulars_sd'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_particulars_sd'] ) : [];
                    }

                    $rbfw_bike_car_sd_data              = isset( $input_data_sabitized['rbfw_bike_car_sd_data'] ) ? $input_data_sabitized['rbfw_bike_car_sd_data'] : [];
					$rbfw_enable_resort_daylong_price = isset( $_POST['rbfw_enable_resort_daylong_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_resort_daylong_price'] ) ) : 'no';
					$rbfw_resort_room_data = isset( $input_data_sabitized['rbfw_resort_room_data'] ) ? $input_data_sabitized['rbfw_resort_room_data'] : [];
					$rbfw_sd_appointment_max_qty_per_session = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ) : '';
					$rbfw_sd_appointment_ondays              = isset( $input_data_sabitized['rbfw_sd_appointment_ondays'] ) ? $input_data_sabitized['rbfw_sd_appointment_ondays'] : [];
					$rbfw_item_stock_quantity_timely = isset( $_POST['rbfw_item_stock_quantity_timely'] ) ? intval( wp_unslash( $_POST['rbfw_item_stock_quantity_timely'] ) ) : 1;
					// daywise configureation============================
					//sun
					$hourly_rate_sun = isset( $_POST['rbfw_sun_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sun_hourly_rate'] ) ) : '';
					$daily_rate_sun  = isset( $_POST['rbfw_sun_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sun_daily_rate'] ) ) : '';
					$enabled_sun     = isset( $_POST['rbfw_enable_sun_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_sun_day'] ) ) : 'no';
					//mon
					$hourly_rate_mon = isset( $_POST['rbfw_mon_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mon_hourly_rate'] ) ) : '';
					$daily_rate_mon  = isset( $_POST['rbfw_mon_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mon_daily_rate'] ) ) : '';
					$enabled_mon     = isset( $_POST['rbfw_enable_mon_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_mon_day'] ) ) : 'no';
					//tue
					$hourly_rate_tue = isset( $_POST['rbfw_tue_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_tue_hourly_rate'] ) ) : '';
					$daily_rate_tue  = isset( $_POST['rbfw_tue_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_tue_daily_rate'] ) ) : '';
					$enabled_tue     = isset( $_POST['rbfw_enable_tue_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_tue_day'] ) ) : 'no';
					//wed
					$hourly_rate_wed = isset( $_POST['rbfw_wed_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_wed_hourly_rate'] ) ) : '';
					$daily_rate_wed  = isset( $_POST['rbfw_wed_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_wed_daily_rate'] ) ) : '';
					$enabled_wed     = isset( $_POST['rbfw_enable_wed_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_wed_day'] ) ) : 'no';
					//thu
					$hourly_rate_thu = isset( $_POST['rbfw_thu_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_thu_hourly_rate'] ) ) : '';
					$daily_rate_thu  = isset( $_POST['rbfw_thu_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_thu_daily_rate'] ) ) : '';
					$enabled_thu     = isset( $_POST['rbfw_enable_thu_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_thu_day'] ) ) : 'no';
					//fri
					$hourly_rate_fri = isset( $_POST['rbfw_fri_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_fri_hourly_rate'] ) ) : '';
					$daily_rate_fri  = isset( $_POST['rbfw_fri_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_fri_daily_rate'] ) ) : '';
					$enabled_fri     = isset( $_POST['rbfw_enable_fri_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_fri_day'] ) ) : 'no';
					//sat
					$hourly_rate_sat            = isset( $_POST['rbfw_sat_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sat_hourly_rate'] ) ) : '';
					$daily_rate_sat             = isset( $_POST['rbfw_sat_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sat_daily_rate'] ) ) : '';
					$enabled_sat                = isset( $_POST['rbfw_enable_sat_day'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_sat_day'] ) ) : 'no';
					$manage_inventory_as_timely = isset( $_POST['manage_inventory_as_timely'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_inventory_as_timely'] ) ) : 'off';
					$enable_specific_duration = isset( $_POST['enable_specific_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_specific_duration'] ) ) : 'off';

                    $rbfw_particular_switch     = isset( $_POST['rbfw_particular_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_particular_switch'] ) ) : 'off';



					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );

                    update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
					update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );

					update_post_meta( $post_id, 'rbfw_enable_time_picker', $rbfw_enable_time_picker );

                    update_post_meta( $post_id, 'rbfw_hourly_threshold', $rbfw_hourly_threshold );
                    update_post_meta( $post_id, 'rbfw_enable_hourly_threshold', $rbfw_enable_hourly_threshold );


                    update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
                    update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );



					update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );


                    update_post_meta( $post_id, 'rbfw_particular_switch', $rbfw_particular_switch );
                    update_post_meta( $post_id, 'rdfw_available_time', $rdfw_available_time );
                    update_post_meta( $post_id, 'rbfw_particulars_data', $particulars_data );

                    update_post_meta( $post_id, 'rbfw_bike_car_sd_data', $rbfw_bike_car_sd_data );
					update_post_meta( $post_id, 'rbfw_resort_room_data', $rbfw_resort_room_data );
					update_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', $rbfw_enable_resort_daylong_price );
					update_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', $rbfw_sd_appointment_max_qty_per_session );
					update_post_meta( $post_id, 'rbfw_sd_appointment_ondays', $rbfw_sd_appointment_ondays );
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
					update_post_meta( $post_id, 'manage_inventory_as_timely', $manage_inventory_as_timely );
					update_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', $rbfw_item_stock_quantity_timely );
					update_post_meta( $post_id, 'enable_specific_duration', $enable_specific_duration );
				}
			}
		}
		new RBFW_Pricing();
	}
	
	