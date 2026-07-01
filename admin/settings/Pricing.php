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
				add_action( 'wp_ajax_rbfw_load_duration_form', [ $this, 'rbfw_load_duration_form' ] );
				add_action( 'wp_ajax_nopriv_rbfw_load_duration_form', [ $this, 'rbfw_load_duration_form' ] );
                add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
                add_action( 'admin_notices', array( $this, 'render_pricing_save_errors_notice' ) );
			}

			/**
			 * Show the pricing validation errors saved by settings_save() when a
			 * classic-editor save was rejected. The modern editor surfaces the same
			 * errors through its AJAX response, so this notice is for the classic
			 * meta-box edit screen only.
			 */
			public function render_pricing_save_errors_notice() {
				if ( ! function_exists( 'get_current_screen' ) ) {
					return;
				}
				$screen = get_current_screen();
				if ( ! $screen || 'rbfw_item' !== $screen->post_type || 'post' !== $screen->base ) {
					return;
				}
				$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
				if ( ! $post_id && isset( $GLOBALS['post']->ID ) ) {
					$post_id = (int) $GLOBALS['post']->ID;
				}
				if ( ! $post_id ) {
					return;
				}
				$errors = get_transient( 'rbfw_pricing_save_errors_' . $post_id );
				if ( empty( $errors ) || ! is_array( $errors ) ) {
					return;
				}
				delete_transient( 'rbfw_pricing_save_errors_' . $post_id );
				echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Pricing was not saved. Please fix the following and save again:', 'booking-and-rental-manager-for-woocommerce' ) . '</strong></p><ul style="list-style:disc;margin:4px 0 4px 22px;">';
				foreach ( $errors as $err ) {
					echo '<li>' . esc_html( $err ) . '</li>';
				}
				echo '</ul></div>';
			}

			public function add_tab_menu() {
                ?>
                <li data-target-tabs="#travel_pricing">
                    <i class="fas fa-pager"></i><?php esc_html_e( 'Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </li>
				<?php
			}

            public function add_tabs_content( $post_id ) {
                ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
                    <?php $this->section_header(); ?>
                    <?php $this->rent_type( $post_id ); ?>
                    <?php $this->appointment( $post_id ); ?>
                    <?php $this->bike_car_single_day( $post_id ); ?>
                    <?php $this->resort_price_config( $post_id ); ?>
                    <?php $this->multiple_items( $post_id ); ?>
                    <?php $this->md_price_config( $post_id ); ?>
                </div>

                <?php
            }

			public function rbfw_load_duration_form() {

                check_ajax_referer( 'rbfw_duration_form_action', 'nonce' );

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
                        <label>
                            <?php echo esc_html( $title ); ?>
                        </label>
                        <p>
                            <?php echo esc_html( $description ); ?>
                        </p>
                    </div>
                </section>
				<?php
			}

			public function rent_type( $post_id ) {
				?>
				<?php $this->panel_header( 'Price Settings', 'Price Settings' ); ?>
                <section class="rent-type-area">
                    <div>
                        <label for="">
							<?php esc_html_e( 'Rent Types', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <p><?php esc_html_e( 'Price will be changed based on this type selection', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    
					<?php 
                        $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true );
                        $rbfw_item_type = $rbfw_item_type?$rbfw_item_type:'bike_car_sd';
                        $rbfw_item_type = ($rbfw_item_type=='equipment' || $rbfw_item_type=='dress' || $rbfw_item_type=='others')?"bike_car_md":$rbfw_item_type;
                        $item_type = [
                            'bike_car_sd'     =>[
                                                    'name' =>__( 'Single day', 'booking-and-rental-manager-for-woocommerce' ) ,
                                                    'desc' => 'Best for items rented within a single day. Customers can choose specific time slots like hourly, morning, evening, or full-day booking. Ideal for <b>bikes</b>, <b>boats</b>, <b>kayaks</b>, and similar short-use rentals.',
                                                    'icon' => 'fa fa-calendar-day'
                                                ],
                            'bike_car_md'     =>[
                                                    'name' => __( 'Multiple day', 'booking-and-rental-manager-for-woocommerce' ),
                                                    'desc' => 'Suitable for items rented for more than one day. Customers select a start and end date, and pricing can be set per hour, per day, or even for weekends. Perfect for <b>cars</b>, <b>equipment</b>, <b>dresses</b>, or <b>sports gear</b>.',   
                                                    'icon' => 'fa fa-calendar-alt'
                                                ],
                            'resort'          =>[
                                                    'name' => __( 'Resort', 'booking-and-rental-manager-for-woocommerce' ),
                                                    'desc' => 'Designed for <b>hotels</b>, <b>resorts</b>, and stays. Pricing is automatically calculated based on nights or full-day stay. You can set <b>day-night</b> or <b>per-night</b> rates for seamless booking.',                       
                                                    'icon' => 'fa fa-hotel'
                                                ],
                            'appointment'     =>[
                                                    'name' => __( 'Appointment', 'booking-and-rental-manager-for-woocommerce' ),
                                                    'desc' => 'Used for time-based services instead of physical items. Customers book a time slot for services like <b>barber</b>, <b>spa</b>, <b>yoga</b>, <b>consultation</b>, or <b>coaching</b>. Pricing depends on the selected service or duration.',                   
                                                    'icon' => 'fa fa-calendar-check'
                                                ],  
                            'multiple_items'  =>[
                                                    'name' => __( 'Multiple day for multiple items', 'booking-and-rental-manager-for-woocommerce' ),
                                                    'desc' => 'Best for renting several items together over multiple days. Customers can select multiple products in one booking and choose rental duration (<b>hourly</b>, <b>daily</b>, <b>weekly</b>, or <b>monthly</b>). Ideal for <b>bundles</b> or <b>group rentals</b>.',
                                                    'icon' => 'fa fa-layer-group'
                                                ],
                        ];
                    ?>

                        <div class="rbfw-tent-types">
                            <input type="hidden" name="rbfw_item_type" id="rbfw_item_type" value="<?php echo esc_attr($rbfw_item_type); ?>">
                            <?php foreach ( $item_type as $key => $value ): ?>
                                <div class="rbfw-rent-type <?php echo esc_attr( $key == $rbfw_item_type ? 'selected' : '' ); ?>" data-rent-type="<?php echo esc_attr( $key ); ?>" data-rent-type-desc="<?php echo esc_html( $value['desc'] ); ?>">
                                    <div class="icon"><i class="<?php echo esc_html( $value['icon'] ); ?>"></i></div>
                                    <?php echo esc_html( $value['name'] ); ?>
                                </div>

                            <?php endforeach; ?>
                            <div class="rbfw-rent-type-desc"></div>
                        </div>
                    </div>
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
					<?php if ( $rbfw_item_type === 'appointment' ) : ?>
						<input type="hidden" name="manage_inventory_as_timely" value="off">
					<?php endif; ?>
                    <section class="manage_inventory_as_timely <?php echo esc_attr( $rbfw_item_type === 'appointment' ? 'rbfw_hide hide' : '' ); ?>"<?php echo $rbfw_item_type === 'appointment' ? ' style="display:none !important;"' : ''; ?>>
                        <div>
                            <label>
								<?php esc_html_e( 'Manage a single-item inventory on an hourly basis.', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php esc_html_e( 'Enabling this allows you to manage a shared inventory for rental items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="manage_inventory_as_timely" value="<?php echo esc_attr( $manage_inventory_as_timely ); ?>" <?php checked( $manage_inventory_as_timely, 'on' ); ?> <?php disabled( $rbfw_item_type === 'appointment', true ); ?>>
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
                                <input type="number" min="0" name="rbfw_item_stock_quantity_timely" id="rbfw_item_stock_quantity" value="<?php echo esc_attr( $rbfw_item_stock_quantity_timely ) ?>" placeholder="<?php esc_html_e( 'Ex: 10', '' ); ?>">
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
											<?php esc_html_e( 'Rental option name', 'booking-and-rental-manager-for-woocommerce' ); ?>
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
                                                    <td><input type="text" class="rbfw_type_title" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php esc_attr_e( '1 hour bike rent', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][price]" step=".01" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php esc_attr_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
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
                                                        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][duration]" value="<?php echo esc_attr( isset( $value['duration'] ) ? $value['duration'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Duration', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </td>
                                                    <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable <?php echo esc_attr( $manage_inventory_as_timely == 'off' ) ? 'rbfw_hide' : ( ( $manage_inventory_as_timely == 'on' && $enable_specific_duration == 'on' ) ? 'rbfw_hide' : '' ) ?>">
                                                        <select class="medium" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][d_type]">
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Hours' ) ? 'selected' : ''; ?> value="Hours">Hours</option>
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Days' ) ? 'selected' : ''; ?> value="Days">Days</option>
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Weeks' ) ? 'selected' : ''; ?> value="Weeks">Weeks</option>
                                                            <option <?php echo esc_attr( isset( $value['d_type'] ) && $value['d_type'] == 'Months' ) ? 'selected' : ''; ?> value="Months">Months</option>
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
                                                    <input type="text" class="rbfw_type_title" name="rbfw_bike_car_sd_data[0][rent_type]" placeholder="<?php esc_attr_e( '1 hour bike rent', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td>
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][price]" step=".01" placeholder="<?php esc_attr_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="rbfw_without_time_inventory">
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable rbfw_hide">
													<?php rbfw_time_slot_select( 'start_time', 0, isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_enable rbfw_hide">
													<?php rbfw_time_slot_select( 'end_time', 0, isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable rbfw_hide">
                                                    <input class="medium" type="number" name="rbfw_bike_car_sd_data[0][duration]" " placeholder="<?php esc_attr_e( 'Duration', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
                                                </td>
                                                <td class="rbfw_time_inventory rbfw_time_inventory_enable duration_disable rbfw_hide">
                                                    <select class="medium" name="rbfw_bike_car_sd_data[0][d_type]">
                                                        <option value="Hours">Hours</option>
                                                        <option value="Days">Days</option>
                                                        <option value="Weeks">Weeks</option>
                                                        <option value="Months">Months</option>
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
                            <div class="mt-2 sd-add-type-and-sessional  <?php echo esc_attr( $rbfw_item_type == 'appointment' ? 'hide' : 'show' ); ?>">
                                <span id="add-bike-car-sd-type-row" data-post_id="<?php echo esc_attr( $post_id ) ?>" class="ppof-button" >
                                    <i class="fas fa-circle-plus"></i>
                                    <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </span>
                            </div>
                        </div>
                    </section>
                </div>
				<?php
			}



            public function multiple_items( $post_id ) {
                $rbfw_item_type                  = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
                $rbfw_enable_time_picker    = get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) ? get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) : 'yes';
                $enable_specific_duration   = get_post_meta( $post_id, 'enable_specific_duration', true ) ?: 'off';
                $rbfw_mi_hourly_to_half_day_pivot  = get_post_meta( $post_id, 'rbfw_mi_hourly_to_half_day_pivot', true ) ? get_post_meta( $post_id, 'rbfw_mi_hourly_to_half_day_pivot', true ) : '';
                $rbfw_mi_half_day_to_daily_pivot   = get_post_meta( $post_id, 'rbfw_mi_half_day_to_daily_pivot', true ) ? get_post_meta( $post_id, 'rbfw_mi_half_day_to_daily_pivot', true ) : '';
                $rbfw_mi_daily_to_weekly_pivot     = get_post_meta( $post_id, 'rbfw_mi_daily_to_weekly_pivot', true ) ? get_post_meta( $post_id, 'rbfw_mi_daily_to_weekly_pivot', true ) : '';
                $rbfw_mi_weekly_to_monthly_pivot   = get_post_meta( $post_id, 'rbfw_mi_weekly_to_monthly_pivot', true ) ? get_post_meta( $post_id, 'rbfw_mi_weekly_to_monthly_pivot', true ) : '';

                $pricing_types           = get_post_meta( $post_id, 'pricing_types', true ) ? get_post_meta( $post_id, 'pricing_types', true ) : [];
                $multiple_items_info           = get_post_meta( $post_id, 'multiple_items_info', true ) ? get_post_meta( $post_id, 'multiple_items_info', true ) : [];

                $checked = (get_the_title($post_id)=='Auto Draft')?'checked':'';
                $checked_item = (get_the_title($post_id)=='Auto Draft')?true:false;
                $pricing_types_initialized = isset( $pricing_types['_initialized'] );
                $enabled_price_types = [
                    'hourly'  => !$pricing_types_initialized || ( isset( $pricing_types['hourly'] ) && $pricing_types['hourly'] == 'on' ),
                    'daily'   => !$pricing_types_initialized || ( isset( $pricing_types['daily'] ) && $pricing_types['daily'] == 'on' ),
                    'weekly'  => !$pricing_types_initialized || ( isset( $pricing_types['weekly'] ) && $pricing_types['weekly'] == 'on' ),
                    'monthly' => !$pricing_types_initialized || ( isset( $pricing_types['monthly'] ) && $pricing_types['monthly'] == 'on' ),
                ];
                $multiple_items_rows = ! empty( $multiple_items_info ) ? $multiple_items_info : [
                    [
                        'item_name'     => '',
                        'available_qty' => 1,
                        'hourly_price'  => '',
                        'daily_price'   => '',
                        'weekly_price'  => '',
                        'monthly_price' => '',
                    ],
                ];

                ?>

                <section class="rbfw_multiple_items <?php echo esc_attr( $rbfw_item_type == 'multiple_items') ? 'show' : 'hide'; ?>">
                    <div class="rbfw-mi-pricing-shell">
                        <div class="rbfw-mi-card rbfw-mi-price-types-card">
                            <div class="rbfw-mi-pt-header">
                                <h3 class="rbfw-mi-pt-title"><?php esc_html_e( 'Enable Price Types', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                            </div>
                            <div class="rbfw-mi-pt-divider"></div>
                            <div class="pricing-toggles">
                                <label class="price-toggle" for="enableHourly">
                                    <input type="checkbox" name="pricing_types[hourly]" id="enableHourly" <?php checked( $enabled_price_types['hourly'] ); ?> onchange="toggleGlobalPricing('hourly')">
                                    <span class="rbfw-mi-pt-option-body">
                                        <span class="rbfw-mi-pt-option-name"><?php esc_html_e( 'Hourly', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                        <span class="rbfw-mi-pt-option-hint"><?php esc_html_e( 'Min. 1 Hour', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </span>
                                </label>

                                <label class="price-toggle" for="enableDaily">
                                    <input type="checkbox" name="pricing_types[daily]" id="enableDaily" <?php checked( $enabled_price_types['daily'] ); ?> onchange="toggleGlobalPricing('daily')">
                                    <span class="rbfw-mi-pt-option-body">
                                        <span class="rbfw-mi-pt-option-name"><?php esc_html_e( 'Daily', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                        <span class="rbfw-mi-pt-option-hint"><?php esc_html_e( '24 Hours', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </span>
                                </label>

                                <label class="price-toggle" for="enableWeekly">
                                    <input type="checkbox" name="pricing_types[weekly]" id="enableWeekly" <?php checked( $enabled_price_types['weekly'] ); ?> onchange="toggleGlobalPricing('weekly')">
                                    <span class="rbfw-mi-pt-option-body">
                                        <span class="rbfw-mi-pt-option-name"><?php esc_html_e( 'Weekly', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                        <span class="rbfw-mi-pt-option-hint"><?php esc_html_e( '7+ Days', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </span>
                                </label>

                                <label class="price-toggle" for="enableMonthly">
                                    <input type="checkbox" name="pricing_types[monthly]" id="enableMonthly" <?php checked( $enabled_price_types['monthly'] ); ?> onchange="toggleGlobalPricing('monthly')">
                                    <span class="rbfw-mi-pt-option-body">
                                        <span class="rbfw-mi-pt-option-name"><?php esc_html_e( 'Monthly', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                        <span class="rbfw-mi-pt-option-hint"><?php esc_html_e( '30+ Days', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="rbfw-mi-card rbfw-mi-items-card">
                            <div class="rbfw-mi-items-head item-row">
                                <div><?php esc_html_e( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div><?php esc_html_e( 'Qty', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="hourly-field <?php echo esc_attr( $enabled_price_types['hourly'] ? '' : 'disabled-field' ); ?>"><?php esc_html_e( 'Hourly ($)', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="daily-field <?php echo esc_attr( $enabled_price_types['daily'] ? '' : 'disabled-field' ); ?>"><?php esc_html_e( 'Daily ($)', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="weekly-field <?php echo esc_attr( $enabled_price_types['weekly'] ? '' : 'disabled-field' ); ?>"><?php esc_html_e( 'Weekly ($)', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="monthly-field <?php echo esc_attr( $enabled_price_types['monthly'] ? '' : 'disabled-field' ); ?>"><?php esc_html_e( 'Monthly ($)', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw-mi-action-head"></div>
                            </div>

                            <div id="itemRows" class="rbfw-mi-item-rows">
                                <?php $i=0; foreach ($multiple_items_rows as $key=>$item_price){ ?>
                                    <div class="item-row">
                                        <div class="form-group">
                                            <label><?php esc_html_e('Item Name','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="text" value="<?php echo esc_attr( $item_price['item_name'] ?? '' ); ?>" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][item_name]" class="item-name-input" placeholder="<?php esc_attr_e( 'Enter item name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label><?php esc_html_e('Qty','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="number" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][available_qty]" class="qty-input" min="0" value="<?php echo esc_attr( $item_price['available_qty'] ?? 1 ); ?>" placeholder="1">
                                        </div>

                                        <div class="form-group hourly-field <?php echo esc_attr( $enabled_price_types['hourly'] ? '' : 'disabled-field' ); ?>">
                                            <label><?php esc_html_e('Hourly Price','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="number" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][hourly_price]" class="hourly-price-input" step="0.01" min="0" value="<?php echo esc_attr( $item_price['hourly_price'] ?? '' ); ?>" placeholder="0.00">
                                        </div>

                                        <div class="form-group daily-field <?php echo esc_attr( $enabled_price_types['daily'] ? '' : 'disabled-field' ); ?>">
                                            <label><?php esc_html_e('Daily Price','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="number" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][daily_price]" class="daily-price-input" step="0.01" min="0" value="<?php echo esc_attr( $item_price['daily_price'] ?? '' ); ?>" placeholder="0.00">
                                        </div>

                                        <div class="form-group weekly-field <?php echo esc_attr( $enabled_price_types['weekly'] ? '' : 'disabled-field' ); ?>">
                                            <label><?php esc_html_e('Weekly Price','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="number" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][weekly_price]" class="weekly-price-input" step="0.01" min="0" value="<?php echo esc_attr( $item_price['weekly_price'] ?? '' ); ?>" placeholder="0.00">
                                        </div>

                                        <div class="form-group monthly-field <?php echo esc_attr( $enabled_price_types['monthly'] ? '' : 'disabled-field' ); ?>">
                                            <label><?php esc_html_e('Monthly Price','booking-and-rental-manager-for-woocommerce'); ?></label>
                                            <input type="number" name="multiple_items_info[<?php echo esc_attr( $i ); ?>][monthly_price]" class="monthly-price-input" step="0.01" min="0" value="<?php echo esc_attr( $item_price['monthly_price'] ?? '' ); ?>" placeholder="0.00">
                                        </div>

                                        <div class="form-group rbfw-mi-row-action">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger" onclick="removeItemRow(this)" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php $i++; } ?>
                            </div>

                            <div class="rbfw-mi-card-footer">
                                <button type="button" class="ppof-button add-more-btn" onclick="addItemRow()">
                                    <i class="fas fa-circle-plus"></i> <?php esc_html_e('Add More Item','booking-and-rental-manager-for-woocommerce'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="rbfw-mi-card rbfw_multi_day_price_conf" id="rbfw-pricing-thresholds-card" style="display: <?php echo esc_attr( ( ( $enabled_price_types['hourly'] && $enabled_price_types['daily'] ) || ( $enabled_price_types['daily'] && $enabled_price_types['weekly'] ) || ( $enabled_price_types['weekly'] && $enabled_price_types['monthly'] ) ) ? 'block' : 'none' ); ?>;">
                            <div class="rbfw-mi-card-heading">
                                <span><?php esc_html_e( 'Pricing Automation & Thresholds', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <p><?php esc_html_e( 'Define the triggers for when a rental duration automatically upgrades to the next price tier.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>

                            <div class="rbfw-mi-threshold-grid">
                                <div class="item" id="rbfw-pivot-hourly-daily" style="display: <?php echo esc_attr( ( $enabled_price_types['hourly'] && $enabled_price_types['daily'] ) ? 'flex' : 'none' ); ?>;">
                                    <div class="rbfw-mi-threshold-icon"><i class="far fa-clock"></i></div>
                                    <div class="item-left">
                                        <div class="label"><?php esc_html_e( 'Hourly to Day Pivot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div class="description"><?php esc_html_e( 'Apply daily rate after X hours', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </div>
                                    <div class="item-right">
                                        <input type="number" name="rbfw_mi_hourly_to_half_day_pivot" class="price-input" step="0.01" min="0" value="<?php echo esc_attr( $rbfw_mi_hourly_to_half_day_pivot ); ?>" placeholder="0">
                                        <span><?php esc_html_e( 'Hours', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </div>
                                </div>

                                <div class="item" id="rbfw-pivot-daily-weekly" style="display: <?php echo esc_attr( ( $enabled_price_types['daily'] && $enabled_price_types['weekly'] ) ? 'flex' : 'none' ); ?>;">
                                    <div class="rbfw-mi-threshold-icon"><i class="far fa-calendar-alt"></i></div>
                                    <div class="item-left">
                                        <div class="label"><?php esc_html_e( 'Daily to Weekly Pivot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div class="description"><?php esc_html_e( 'Apply weekly rate after X days', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </div>
                                    <div class="item-right">
                                        <input type="number" name="rbfw_mi_daily_to_weekly_pivot" class="price-input" step="0.01" min="0" value="<?php echo esc_attr( $rbfw_mi_daily_to_weekly_pivot ); ?>" placeholder="0">
                                        <span><?php esc_html_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </div>
                                </div>

                                <div class="item" id="rbfw-pivot-weekly-monthly" style="display: <?php echo esc_attr( ( $enabled_price_types['weekly'] && $enabled_price_types['monthly'] ) ? 'flex' : 'none' ); ?>;">
                                    <div class="rbfw-mi-threshold-icon"><i class="far fa-calendar-check"></i></div>
                                    <div class="item-left">
                                        <div class="label"><?php esc_html_e( 'Weekly to Monthly Pivot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                        <div class="description"><?php esc_html_e( 'Apply monthly rate after X weeks', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </div>
                                    <div class="item-right">
                                        <input type="number" name="rbfw_mi_weekly_to_monthly_pivot" class="price-input" step="0.01" min="0" value="<?php echo esc_attr( $rbfw_mi_weekly_to_monthly_pivot ); ?>" placeholder="0">
                                        <span><?php esc_html_e( 'Weeks', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="rbfw-mi-info-note">
                                <i class="fas fa-info-circle"></i>
                                <span><?php esc_html_e( 'Auto-Pivot Logic: The system will calculate the cheapest combination of rates unless the duration exceeds these thresholds. Once a threshold is hit, the higher rate becomes the cap for that period.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>

                        </div>

                        <div class="rbfw-mi-time-settings-wrap rbfw_multi_day_price_conf" style="display:block;">
                            <div class="rbfw-mi-ts-header">
                                <span class="rbfw-mi-ts-title"><?php esc_html_e( 'Time Slots Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                <div class="rbfw-mi-ts-toggle-group">
                                    <span class="rbfw-mi-ts-toggle-label"><?php esc_html_e( 'Enable Time Picker', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    <div class="toggle time-picker-toggle <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'active' : '' ); ?>">
                                        <div class="toggle-knob"></div>
                                    </div>
                                    <input type="hidden" name="rbfw_enable_time_picker" class="rbfw_enable_time_picker" value="<?php echo esc_attr( $rbfw_enable_time_picker ); ?>">
                                </div>
                            </div>

                            <?php $this->multiple_time_slot_with_particular( $post_id, $rbfw_enable_time_picker, 'mi', 'mi' ); ?>
                        </div>
                    </div>
                    <script>
                        let rowCounter = <?php echo esc_js( count( $multiple_items_rows ) ); ?>;
                        let enabledPriceTypes = <?php echo wp_json_encode( $enabled_price_types ); ?>;
                        const priceTypeLabels = {
                            hourly: '<?php echo esc_js( __( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ) ); ?>',
                            daily: '<?php echo esc_js( __( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ) ); ?>',
                            weekly: '<?php echo esc_js( __( 'Weekly Price', 'booking-and-rental-manager-for-woocommerce' ) ); ?>',
                            monthly: '<?php echo esc_js( __( 'Monthly Price', 'booking-and-rental-manager-for-woocommerce' ) ); ?>'
                        };

                        function toggleGlobalPricing(priceType) {
                            const checkbox = document.getElementById(`enable${priceType.charAt(0).toUpperCase() + priceType.slice(1)}`);
                            enabledPriceTypes[priceType] = checkbox.checked;

                            document.querySelectorAll(`.rbfw_multiple_items .${priceType}-field`).forEach(field => {
                                field.classList.toggle('disabled-field', !checkbox.checked);

                                if (!checkbox.checked) {
                                    const input = field.querySelector('input');
                                    if (input) {
                                        input.value = '';
                                    }
                                }
                            });

                            updateRowGridLayout();
                            updatePivotVisibility();
                            if (typeof window.rbfwSpSyncMiSeasonalPriceFields === 'function') {
                                window.rbfwSpSyncMiSeasonalPriceFields();
                            }
                        }

                        function updatePivotVisibility() {
                            const pivotHourlyDaily   = document.getElementById('rbfw-pivot-hourly-daily');
                            const pivotDailyWeekly   = document.getElementById('rbfw-pivot-daily-weekly');
                            const pivotWeeklyMonthly = document.getElementById('rbfw-pivot-weekly-monthly');
                            const thresholdsCard     = document.getElementById('rbfw-pricing-thresholds-card');

                            const showHourlyDaily   = !!(enabledPriceTypes.hourly  && enabledPriceTypes.daily);
                            const showDailyWeekly   = !!(enabledPriceTypes.daily   && enabledPriceTypes.weekly);
                            const showWeeklyMonthly = !!(enabledPriceTypes.weekly  && enabledPriceTypes.monthly);
                            const showCard          = showHourlyDaily || showDailyWeekly || showWeeklyMonthly;

                            if (pivotHourlyDaily)   pivotHourlyDaily.style.display   = showHourlyDaily   ? 'flex' : 'none';
                            if (pivotDailyWeekly)   pivotDailyWeekly.style.display   = showDailyWeekly   ? 'flex' : 'none';
                            if (pivotWeeklyMonthly) pivotWeeklyMonthly.style.display = showWeeklyMonthly ? 'flex' : 'none';
                            if (thresholdsCard)     thresholdsCard.style.display     = showCard          ? 'block' : 'none';
                        }

                        function updateRowGridLayout() {
                            const rateCount = Math.max(Object.values(enabledPriceTypes).filter(Boolean).length, 1);
                            document.querySelectorAll('.rbfw_multiple_items .item-row').forEach(row => {
                                row.style.setProperty('--rbfw-mi-rate-count', rateCount);
                            });
                        }

                        function buildPriceField(priceType, itemIndex) {
                            const disabledClass = enabledPriceTypes[priceType] ? '' : ' disabled-field';
                            return `
                                <div class="form-group ${priceType}-field${disabledClass}">
                                    <label>${priceTypeLabels[priceType]}</label>
                                    <input type="number" name="multiple_items_info[${itemIndex}][${priceType}_price]" class="${priceType}-price-input" step="0.01" min="0" placeholder="0.00">
                                </div>
                            `;
                        }

                        function generateItemRowHTML(itemIndex) {
                            return `
                                <div class="form-group">
                                    <label><?php echo esc_js( __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                                    <input type="text" name="multiple_items_info[${itemIndex}][item_name]" class="item-name-input" placeholder="<?php echo esc_js( __( 'Enter item name', 'booking-and-rental-manager-for-woocommerce' ) ); ?>">
                                </div>

                                <div class="form-group">
                                    <label><?php echo esc_js( __( 'Qty', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                                    <input type="number" name="multiple_items_info[${itemIndex}][available_qty]" class="qty-input" min="0" value="1" placeholder="1">
                                </div>

                                ${buildPriceField('hourly', itemIndex)}
                                ${buildPriceField('daily', itemIndex)}
                                ${buildPriceField('weekly', itemIndex)}
                                ${buildPriceField('monthly', itemIndex)}

                                <div class="form-group rbfw-mi-row-action">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger" onclick="removeItemRow(this)" title="<?php echo esc_js( __( 'Delete', 'booking-and-rental-manager-for-woocommerce' ) ); ?>">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            `;
                        }

                        function addItemRow() {
                            const itemRows = document.getElementById('itemRows');
                            const newRow = document.createElement('div');
                            newRow.className = 'item-row';
                            newRow.innerHTML = generateItemRowHTML(rowCounter);
                            rowCounter++;

                            itemRows.appendChild(newRow);
                            updateRowGridLayout();
                            updateRemoveButtons();
                            newRow.querySelector('.item-name-input').focus();
                            if (typeof window.rbfwSpScheduleMiSeasonalSync === 'function') {
                                var root = document.querySelector('.rbfw-me-wrap') || document.getElementById('rbfw_add_meta_box') || document.body;
                                window.rbfwSpScheduleMiSeasonalSync(jQuery(root), true);
                            }
                        }

                        function removeItemRow(button) {
                            const row = button.closest('.item-row');
                            row.remove();
                            updateRemoveButtons();
                            if (typeof window.rbfwSpScheduleMiSeasonalSync === 'function') {
                                var root = document.querySelector('.rbfw-me-wrap') || document.getElementById('rbfw_add_meta_box') || document.body;
                                window.rbfwSpScheduleMiSeasonalSync(jQuery(root), true);
                            }
                        }

                        function updateRemoveButtons() {
                            const rows = document.querySelectorAll('#itemRows .item-row');
                            rows.forEach(row => {
                                const removeBtn = row.querySelector('.btn-danger');
                                if (removeBtn) {
                                    removeBtn.style.visibility = rows.length === 1 ? 'hidden' : 'visible';
                                }
                            });
                        }

                        updateRowGridLayout();
                        updateRemoveButtons();
                    </script>

                </section>



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
                                        <th><?php esc_html_e( 'Rental option name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
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
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php esc_attr_e( '1 hour bike rent', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td><input type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][price]" step=".01" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php esc_attr_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                    <td class="rbfw_bike_car_sd_price_table_action_column" <?php echo ( $rbfw_item_type == 'appointment' )?'style="display:none"':''; ?>>
                                                        <input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
                                                <td><input type="text" name="rbfw_bike_car_sd_data[0][rent_type]" value="" placeholder="<?php esc_attr_e( '1 hour bike rent', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td><input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" value="" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td><input type="number" name="rbfw_bike_car_sd_data[0][price]" step=".01" value="" placeholder="<?php esc_attr_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
                                                <td class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
													echo 'style="display:none"';
												} ?> ><input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" value="" placeholder="<?php esc_attr_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
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
                    <section class="bg-light mt-5">
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
                                                        <input class="rbfw_room_title" type="text" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][room_type]" value="<?php echo esc_attr( $value['room_type'] ); ?>" placeholder="<?php esc_attr_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
													} ?>;"><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_daylong_rate]" step=".01" value="<?php echo esc_attr( $value['rbfw_room_daylong_rate'] ); ?>" placeholder="<?php esc_attr_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
                                                    <td><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][rbfw_room_daynight_rate]" step=".01" value="<?php echo esc_attr( $value['rbfw_room_daynight_rate'] ); ?>" placeholder="<?php esc_attr_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
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
                                                    <input type="text" class="rbfw_room_title" name="rbfw_resort_room_data[0][room_type]" value="" placeholder="<?php esc_attr_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
                            </p>

							<?php do_action( 'rbfw_after_resort_price_table' ); ?>



                        </div>
                    </section>
                </div>
				<?php
			}

			public function rbfw_day_row( $day_name, $day_slug, $show_hourly_col = true, $show_halfday_col = true, $show_daily_col = true ) {
				$hourly_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) : '';
				$half_day_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_half_day_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_half_day_rate', true ) : '';
				$daily_rate  = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) : '';
				$enable      = ! empty( get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) ) ? get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) : '';
				$hourly_col_style  = $show_hourly_col  ? '' : ' style="display:none;"';
				$halfday_col_style = $show_halfday_col ? '' : ' style="display:none;"';
				$daily_col_style   = $show_daily_col   ? '' : ' style="display:none;"';
				?>
                <tr>
                    <th><?php echo esc_html( $day_name ); ?></th>
                    <td class="rbfw-daywise-hourly-col"<?php echo $hourly_col_style; ?>>
                        <input
                            type="number"
                            name="rbfw_<?php echo esc_attr( $day_slug ); ?>_hourly_rate"
                            value="<?php echo esc_attr( $hourly_rate ); ?>"
                            placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                    </td>
                    <td class="rbfw-daywise-halfday-col"<?php echo $halfday_col_style; ?>>
                        <input
                                type="number"
                                name="rbfw_<?php echo esc_attr( $day_slug ); ?>_half_day_rate"
                                value="<?php echo esc_attr( $half_day_rate ); ?>"
                                placeholder="<?php esc_attr_e( 'Half Day Price', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                    </td>
                    <td class="rbfw-daywise-dailyprice-col"<?php echo $daily_col_style; ?>>
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
				$rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true );
				$rbfw_sd_appointment_max_qty_per_session = ( $rbfw_sd_appointment_max_qty_per_session !== '' && is_numeric( $rbfw_sd_appointment_max_qty_per_session ) ) ? $rbfw_sd_appointment_max_qty_per_session : '1';
				?>
                <div class="rbfw_switch_sd_appointment_row <?php echo esc_attr( $rbfw_item_type != 'appointment' ) ? 'hide' : 'show'; ?>">
                    <div class="md-price-card">
                        <div class="item">
                            <div class="item-left">
                                <div class="label"><?php esc_html_e( 'Maximum Allowed Quantity Per Session/Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="description"><?php esc_html_e( 'Set the maximum number of bookings allowed per session or time slot.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                            </div>
                            <div class="item-right">
                                <div class="md-threshold-input-wrap">
                                    <input type="number" name="rbfw_sd_appointment_max_qty_per_session" id="rbfw_sd_appointment_max_qty_per_session" min="1" step="1" value="<?php echo esc_attr( $rbfw_sd_appointment_max_qty_per_session ); ?>">
                                    <span><?php esc_html_e( 'QTY', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="appointment-onday <?php echo esc_attr( $rbfw_item_type != 'appointment' ? 'hide' : '' ); ?>">
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

			public function md_price_config( $post_id ) {

                $rbfw_enable_monthly_rate           = get_post_meta( $post_id, 'rbfw_enable_monthly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_monthly_rate', true ) : 'no';
                $rbfw_monthly_rate           = get_post_meta( $post_id, 'rbfw_monthly_rate', true ) ? get_post_meta( $post_id, 'rbfw_monthly_rate', true ) : 0;

                $rbfw_enable_day_threshold_for_monthly   = get_post_meta( $post_id, 'rbfw_enable_day_threshold_for_monthly', true ) ? get_post_meta( $post_id, 'rbfw_enable_day_threshold_for_monthly', true ) : 'no';
                $rbfw_day_threshold_for_monthly   = get_post_meta( $post_id, 'rbfw_day_threshold_for_monthly', true ) ? get_post_meta( $post_id, 'rbfw_day_threshold_for_monthly', true ) : '0';

                $rbfw_enable_weekly_rate           = get_post_meta( $post_id, 'rbfw_enable_weekly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_weekly_rate', true ) : 'no';
                $rbfw_weekly_rate           = get_post_meta( $post_id, 'rbfw_weekly_rate', true ) ? get_post_meta( $post_id, 'rbfw_weekly_rate', true ) : '0';

                $rbfw_enable_day_threshold_for_weekly   = get_post_meta( $post_id, 'rbfw_enable_day_threshold_for_weekly', true ) ? get_post_meta( $post_id, 'rbfw_enable_day_threshold_for_weekly', true ) : 'no';
                $rbfw_day_threshold_for_weekly   = get_post_meta( $post_id, 'rbfw_day_threshold_for_weekly', true ) ? get_post_meta( $post_id, 'rbfw_day_threshold_for_weekly', true ) : '0';



                $rbfw_daily_rate           = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
                $rbfw_enable_daily_rate    = get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) : 'yes';

                $rbfw_enable_time_picker    = get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) ? get_post_meta( $post_id, 'rbfw_enable_time_picker', true ) : 'no';
                $rbfw_enable_hourly_rate   = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';
                $rbfw_hourly_rate          = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;

                $rbfw_enable_half_day_rate   = get_post_meta( $post_id, 'rbfw_enable_half_day_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_half_day_rate', true ) : 'no';
                $rbfw_half_day_rate          = get_post_meta( $post_id, 'rbfw_half_day_rate', true ) ? get_post_meta( $post_id, 'rbfw_half_day_rate', true ) : 0;

                $half_day_hour_threshold_start   = get_post_meta( $post_id, 'half_day_hour_threshold_start', true ) ? get_post_meta( $post_id, 'half_day_hour_threshold_start', true ) : 'no';
                $half_day_hour_threshold_end   = get_post_meta( $post_id, 'half_day_hour_threshold_end', true ) ? get_post_meta( $post_id, 'half_day_hour_threshold_end', true ) : 'no';

                $rbfw_hourly_threshold   = get_post_meta( $post_id, 'rbfw_hourly_threshold', true ) ? get_post_meta( $post_id, 'rbfw_hourly_threshold', true ) : '0';
                $rbfw_enable_hourly_threshold    = get_post_meta( $post_id, 'rbfw_enable_hourly_threshold', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_threshold', true ) : 'no';


				$rbfw_item_type            = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$mdedo                     = ( $rbfw_item_type == 'bike_car_md' || $rbfw_item_type == 'equipment' || $rbfw_item_type == 'dress' || $rbfw_item_type == 'others') ? 'block' : 'none';
				$rbfw_enable_daywise_price = get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) : 'no';
				$manage_inventory_as_timely = get_post_meta( $post_id, 'manage_inventory_as_timely', true ) ?: 'off';
				$enable_specific_duration   = get_post_meta( $post_id, 'enable_specific_duration', true ) ?: 'off';
				?>
                <div class="rbfw_general_price_config_wrapper " style="display: <?php echo esc_attr( $mdedo ) ?>;">

                    <div class="rbfw_multi_day_price_conf">

                        <!-- DURATION RATES Card -->
                        <div class="md-price-card">
                            <div class="md-card-header">Duration Rates</div>

                            <!-- Monthly Price -->
                            <div class="item">
                                <div class="toggle monthly-price-toggle <?php echo esc_attr( $rbfw_enable_monthly_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Monthly Price</div>
                                    <div class="description">Pricing will be calculated based on number of Month.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-price-input-wrap">
                                        <span>$</span>
                                        <input type="number" name="rbfw_monthly_rate" step="0.01" value="<?php echo esc_attr( $rbfw_monthly_rate ); ?>" placeholder="<?php esc_attr_e( 'Monthly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_monthly_rate == 'no' ? 'disabled' : '' ); ?> id="monthly-price-input" class="price-input">
                                    </div>
                                    <input type="hidden" name="rbfw_enable_monthly_rate" id="rbfw_enable_monthly_rate" value="<?php echo esc_attr( $rbfw_enable_monthly_rate ); ?>">
                                </div>
                            </div>

                            <!-- Monthly threshold (conditional) -->
                            <div class="item day-threshold-item-for-month" style="display: <?php echo esc_attr( $rbfw_enable_monthly_rate == 'yes' ? 'flex' : 'none' ); ?>;">
                                <div class="toggle day-threshold-toggle-for-month <?php echo esc_attr( $rbfw_enable_day_threshold_for_monthly == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Monthly Threshold</div>
                                    <div class="description">Number of days to consider as a month. If total days exceed this threshold it will calculate as month.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-threshold-input-wrap">
                                        <input type="number" name="rbfw_day_threshold_for_monthly" step="0.01" value="<?php echo esc_attr( $rbfw_day_threshold_for_monthly ); ?>" placeholder="<?php esc_attr_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_day_threshold_for_monthly == 'no' ? 'disabled' : '' ); ?> id="day-threshold-input-for-monthly" class="price-input">
                                        <span>days</span>
                                    </div>
                                    <input type="hidden" name="rbfw_enable_day_threshold_for_monthly" id="rbfw_enable_day_threshold_for_monthly" value="<?php echo esc_attr( $rbfw_enable_day_threshold_for_monthly ); ?>">
                                </div>
                            </div>

                            <!-- Weekly Price -->
                            <div class="item">
                                <div class="toggle weekly-price-toggle <?php echo esc_attr( $rbfw_enable_weekly_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Weekly Price</div>
                                    <div class="description">Pricing will be calculated based on number of week.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-price-input-wrap">
                                        <span>$</span>
                                        <input type="number" name="rbfw_weekly_rate" step="0.01" value="<?php echo esc_attr( $rbfw_weekly_rate ); ?>" placeholder="<?php esc_attr_e( 'Weekly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_weekly_rate == 'no' ? 'disabled' : '' ); ?> id="weekly-price-input" class="price-input">
                                    </div>
                                    <input type="hidden" name="rbfw_enable_weekly_rate" id="rbfw_enable_weekly_rate" value="<?php echo esc_attr( $rbfw_enable_weekly_rate ); ?>">
                                </div>
                            </div>

                            <!-- Weekly threshold (conditional) -->
                            <div class="item day-threshold-item-for-week" style="display: <?php echo esc_attr( $rbfw_enable_weekly_rate == 'yes' ? 'flex' : 'none' ); ?>;">
                                <div class="toggle day-threshold-toggle-for-week <?php echo esc_attr( $rbfw_enable_day_threshold_for_weekly == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Weekly Threshold</div>
                                    <div class="description">If total hours are more than <span id="hour-threshold-display">x</span>, count as full day. If less, day will not count.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-threshold-input-wrap">
                                        <input type="number" name="rbfw_day_threshold_for_weekly" step="0.01" value="<?php echo esc_attr( $rbfw_day_threshold_for_weekly ); ?>" placeholder="<?php esc_attr_e( 'Days', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_day_threshold_for_weekly == 'no' ? 'disabled' : '' ); ?> id="day-threshold-input-for-weekly" class="price-input">
                                        <span>days</span>
                                    </div>
                                    <input type="hidden" name="rbfw_enable_day_threshold_for_weekly" id="rbfw_enable_day_threshold_for_weekly" value="<?php echo esc_attr( $rbfw_enable_day_threshold_for_weekly ); ?>">
                                </div>
                            </div>

                            <!-- Daily Price -->
                            <div class="item">
                                <div class="toggle daily-price-toggle <?php echo esc_attr( $rbfw_enable_daily_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Daily Price</div>
                                    <div class="description">Pricing will be calculated based on number of day.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-price-input-wrap">
                                        <span>$</span>
                                        <input type="number" name="rbfw_daily_rate" step="0.01" value="<?php echo esc_attr( $rbfw_daily_rate ); ?>" placeholder="<?php esc_attr_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_daily_rate == 'no' ? 'disabled' : '' ); ?> id="daily-price-input" class="price-input">
                                    </div>
                                    <input type="hidden" name="rbfw_enable_daily_rate" id="rbfw_enable_daily_rate" value="<?php echo esc_attr( $rbfw_enable_daily_rate ); ?>">
                                </div>
                            </div>

                        </div><!-- /.md-price-card Duration Rates -->

                        <!-- TIME CONFIGURATION Card -->
                        <div class="md-price-card">
                            <div class="md-card-header">Time Configuration</div>

                            <div class="item md-time-toggle-row">
                                <div class="item-left">
                                    <span class="dashicons dashicons-clock"></span>
                                    <div>
                                        <div class="label">Enable Time Picker</div>
                                        <div class="description">Toggle to enable time selection for more precise rental periods.</div>
                                    </div>
                                </div>
                                <div class="item-right">
                                    <div class="toggle time-picker-toggle <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'active' : '' ); ?>">
                                        <div class="toggle-knob"></div>
                                    </div>
                                    <input type="hidden" name="rbfw_enable_time_picker" id="rbfw_enable_time_picker" class="rbfw_enable_time_picker" value="<?php echo esc_attr( $rbfw_enable_time_picker ); ?>">
                                </div>
                            </div>

                            <!-- Half-Day Price (conditional on time picker) -->
                            <div class="item hourly-price-item" style="display: flex;">
                                <div class="toggle half-day-price-toggle <?php echo esc_attr( $rbfw_enable_half_day_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Half-Day Price</div>
                                    <div class="description">Pricing will be calculated as half-day when rental hours fall within the specified range.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-price-input-wrap">
                                        <span>$</span>
                                        <input type="number" name="rbfw_half_day_rate" step="0.01" value="<?php echo esc_attr( $rbfw_half_day_rate ); ?>" placeholder="<?php esc_attr_e( 'Half-Day Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_half_day_rate == 'no' ? 'disabled' : '' ); ?> id="half-day-price-input" class="price-input">
                                    </div>
                                    <input type="hidden" name="rbfw_enable_half_day_rate" id="rbfw_enable_half_day_rate" value="<?php echo esc_attr( $rbfw_enable_half_day_rate ); ?>">
                                </div>
                            </div>

                            <!-- Half-Day Hour Threshold (conditional) -->
                            <div class="item half-day-price-item" style="display: <?php echo esc_attr( ( $rbfw_enable_half_day_rate === 'yes' && $rbfw_enable_time_picker === 'yes' ) ? 'flex' : 'none' ); ?>;">
                                <div class="item-left">
                                    <div class="label">Half-Day Hour Threshold</div>
                                    <div class="description">Define the hour range for half-day pricing. Rentals within this range will be charged as half-day.</div>
                                </div>
                                <div class="item-right">
                                    <div class="threshold-inputs">
                                        <span>From</span>
                                        <input type="number" name="half_day_hour_threshold_start" class="input-field" value="<?php echo esc_attr( $half_day_hour_threshold_start ); ?>" min="1" max="24">
                                        <span>to</span>
                                        <input type="number" name="half_day_hour_threshold_end" class="input-field" value="<?php echo esc_attr( $half_day_hour_threshold_end ); ?>" min="1" max="24">
                                        <span>hours</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Hourly Price (conditional) -->
                            <div class="item hourly-price-item" style="display: flex;">
                                <div class="toggle hourly-price-toggle <?php echo esc_attr( $rbfw_enable_hourly_rate == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Hourly Price</div>
                                    <div class="description">Pricing will be calculated as per hour.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-price-input-wrap">
                                        <span>$</span>
                                        <input type="number" name="rbfw_hourly_rate" step="0.01" value="<?php echo esc_attr( $rbfw_hourly_rate ); ?>" placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_hourly_rate == 'no' ? 'disabled' : '' ); ?> id="hourly-price-input" class="price-input">
                                    </div>
                                    <input type="hidden" name="rbfw_enable_hourly_rate" id="rbfw_enable_hourly_rate" value="<?php echo esc_attr( $rbfw_enable_hourly_rate ); ?>">
                                </div>
                            </div>

                            <!-- Hour Threshold (conditional) -->
                            <div class="item hour-threshold-item" style="display: <?php echo esc_attr( ( $rbfw_enable_hourly_rate === 'yes' && $rbfw_enable_time_picker === 'yes' ) ? 'flex' : 'none' ); ?>;">
                                <div class="toggle hour-threshold-toggle <?php echo esc_attr( $rbfw_enable_hourly_threshold == 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <div class="item-left">
                                    <div class="label">Hour Threshold</div>
                                    <div class="description">If total hours are more than <span id="hour-threshold-display">X</span>, count as full day. If less, day will not count.</div>
                                </div>
                                <div class="item-right">
                                    <div class="md-threshold-input-wrap">
                                        <input type="number" name="rbfw_hourly_threshold" step="0.01" value="<?php echo esc_attr( $rbfw_hourly_threshold ); ?>" placeholder="<?php esc_attr_e( 'Hours', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_attr( $rbfw_enable_hourly_threshold == 'no' ? 'disabled' : '' ); ?> id="hour-threshold-input" class="price-input">
                                        <span>hours</span>
                                    </div>
                                    <input type="hidden" name="rbfw_enable_hourly_threshold" id="rbfw_enable_hourly_threshold" value="<?php echo esc_attr( $rbfw_enable_hourly_threshold ); ?>">
                                </div>
                            </div>
                        </div><!-- /.md-price-card Time Configuration -->

                        <!-- Time Slots (conditional) -->

                        <?php $this->multiple_time_slot_with_particular( $post_id, $rbfw_enable_time_picker,'md' ); ?>
                    </div>


                    <?php do_action( 'rbfw_before_general_price_table' ); ?>

                    <?php do_action( 'rbfw_before_general_price_table_row' ); ?>

					<?php
						$_daywise_visible = (
							$rbfw_enable_daily_rate   === 'yes' ||
							( $rbfw_enable_time_picker === 'yes' && ( $rbfw_enable_hourly_rate === 'yes' || $rbfw_enable_half_day_rate === 'yes' ) )
						);
					?>
                    <div id="rbfw-daywise-config-wrapper" style="<?php echo $_daywise_visible ? '' : 'display:none;'; ?>">
                    <div class="md-price-card">
                        <div class="md-card-header"><?php esc_html_e( 'Day-wise Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                        <div class="item md-time-toggle-row">
                            <div class="item-left">
                                <div class="label"><?php esc_html_e( 'Enable Day-wise Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="description"><?php esc_html_e( 'Enabling this will set prices based on the day of the week, overriding the general daily price', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                            </div>
                            <div class="item-right">
                                <div class="toggle daywise-price-toggle <?php echo esc_attr( $rbfw_enable_daywise_price === 'yes' ? 'active' : '' ); ?>">
                                    <div class="toggle-knob"></div>
                                </div>
                                <input type="hidden" name="rbfw_enable_daywise_price" value="<?php echo esc_attr( $rbfw_enable_daywise_price ); ?>">
                            </div>
                        </div>
                    </div>
                    <section class="day-wise-price-configuration <?php echo esc_attr( ( $rbfw_enable_daywise_price == 'yes' ) ? 'show' : 'hide' ); ?>">
                        <table class='form-table'>
							<?php do_action( 'rbfw_before_week_price_table_row' ); ?>
                            <thead>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Day Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row" class="rbfw-daywise-hourly-col" style="<?php echo ( $rbfw_enable_time_picker === 'yes' && $rbfw_enable_hourly_rate === 'yes' ) ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row" class="rbfw-daywise-halfday-col" style="<?php echo ( $rbfw_enable_time_picker === 'yes' && $rbfw_enable_half_day_rate === 'yes' ) ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Half Day Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row" class="rbfw-daywise-dailyprice-col" style="<?php echo $rbfw_enable_daily_rate === 'yes' ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th scope="row"><?php esc_html_e( 'Enable/Disable', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
								$_show_hourly_col  = ( $rbfw_enable_time_picker === 'yes' && $rbfw_enable_hourly_rate === 'yes' );
								$_show_halfday_col = ( $rbfw_enable_time_picker === 'yes' && $rbfw_enable_half_day_rate === 'yes' );
								$_show_daily_col   = ( $rbfw_enable_daily_rate === 'yes' );
								$this->rbfw_day_row( esc_html__( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								$this->rbfw_day_row( esc_html__( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat', $_show_hourly_col, $_show_halfday_col, $_show_daily_col );
								//do_action( 'rbfw_after_week_price_table_row' );
							?>
                            </tbody>
                        </table>
                    </section>
                    <br>
                    </div>

					<?php do_action( 'rbfw_after_general_price_table_row' ); ?>

					<?php do_action( 'rbfw_after_general_price_table', $post_id ); ?>
					<?php do_action( 'rbfw_after_general_price_table_tier_pricing', $post_id ); ?>
					<?php do_action( 'rbfw_after_rent_item_type_table_row' ); ?>
                </div>

				<?php do_action( 'rbfw_after_week_price_table', $post_id ); ?>


                <?php do_action( 'rbfw_after_room_type_price_saver_price_table', $post_id ); ?>
				<?php do_action( 'rbfw_after_extra_service_table' ); ?>


                <div class="rbfw_multi_day_price_conf rbfw_bike_car_sd_wrapper <?php echo esc_attr( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' ) ? 'show' : 'hide'; ?>"<?php echo ( $rbfw_item_type === 'bike_car_sd' && $manage_inventory_as_timely === 'on' && $enable_specific_duration === 'on' ) ? ' style="display:none"' : ''; ?>>
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



            public function multiple_time_slot_with_particular($post_id, $rbfw_enable_time_picker,$type='sd' , $mi='')
            {
                ?>
                <div class="time-slots-section" style="display: <?php echo esc_attr( $rbfw_enable_time_picker == 'yes' ? 'block' : 'none' ); ?>;">
                    <?php if ( $mi !== 'mi' ) : ?>
                    <div class="section">
                        <div class="label"><?php echo esc_html__( 'Time Slots Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                        <p><?php echo esc_html__( 'Configure available 30-minute time slots for booking', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ( $mi === 'mi' ) : ?>
                    <div class="rbfw-mi-ts-active-label"><?php echo esc_html__( 'Active Booking Slots (30-min increments)', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <?php endif; ?>

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
                                <div class="time-slot time-slot-indicator <?php echo $item['status'] ?>" data-id="<?php echo $i ?>">

                                    <span class="time-slot-time"><?php echo $item['time'] ?></span>
                                    <?php if($type=='md'){ ?>
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][id]" value="<?php echo $i ?>">
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][time]" value="<?php echo $item['time'] ?>">
                                        <input type="hidden" name="rdfw_available_time[<?php echo $i ?>][status]" value="<?php echo $item['status'] ?>">
                                    <?php }elseif($type=='mi'){ ?>
                                        <input type="hidden" name="rdfw_available_time_mi[<?php echo $i ?>][id]" value="<?php echo $i ?>">
                                        <input type="hidden" name="rdfw_available_time_mi[<?php echo $i ?>][time]" value="<?php echo $item['time'] ?>">
                                        <input type="hidden" name="rdfw_available_time_mi[<?php echo $i ?>][status]" value="<?php echo $item['status'] ?>">
                                    <?php }else{ ?>
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][id]" value="<?php echo $i ?>">
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][time]" value="<?php echo $item['time'] ?>">
                                        <input type="hidden" name="rdfw_available_time_sd[<?php echo $i ?>][status]" value="<?php echo $item['status'] ?>">
                                    <?php } ?>

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
                            <button class="add-slot-btn" data-name_attr="rdfw_available_time" data-rent_type="<?php echo $type ?>" disabled><?php echo $mi === 'mi' ? esc_html__( '+ Add Slot', 'booking-and-rental-manager-for-woocommerce' ) : esc_html__( 'Add Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        </div>
                    </div>


                    <?php $particulars_data = get_post_meta( $post_id, 'rbfw_particulars_data', true );


                    ?>
                    <div class="mpStyle">
                        <section class="particulare-date-time-slot">
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Particular date time slots', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <p><?php echo esc_html__( 'It enables/disables the particulars for selection.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <?php $rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off'; ?>
                            <label class="switch">
                                <?php if($mi=='mi'){ ?>
                                    <input type="checkbox" name="rbfw_particular_switch_mi" class="rbfw_particular_switch" value="<?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? $rbfw_particular_switch : 'off' ); ?>" <?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'checked' : '' ); ?>>
                                <?php }else{ ?>
                                    <input type="checkbox" name="rbfw_particular_switch_<?php echo esc_attr($type) ?>" class="rbfw_particular_switch" value="<?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? $rbfw_particular_switch : 'off' ); ?>" <?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'checked' : '' ); ?>>
                                <?php } ?>
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
                                        <?php if ( ! empty( $particulars_data ) ){ ?>
                                            <?php $i=0;  foreach ( $particulars_data as $index => $particular ){
                                                if( $particular['start_date'] && $particular['end_date'] && isset($particular['available_time'])){ ?>
                                                    <div class="rbfw_pdwt_row d-flex justify-content-between">
                                                <?php if($type=='md'){ ?>
                                                    <div class="rbfw-particular-date">
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars[<?php echo esc_attr( $i ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                                    </div>
                                                    <div class="rbfw-particular-date">
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars[<?php echo esc_attr( $i ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                                    </div>
                                                <?php } elseif($type=='mi'){ ?>
                                                        <div class="rbfw-particular-date">
                                                            <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_mi[<?php echo esc_attr( $i ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                                        </div>
                                                        <div class="rbfw-particular-date">
                                                            <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_mi[<?php echo esc_attr( $i ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                                        </div>
                                                <?php }else{ ?>
                                                    <div class="rbfw-particular-date">
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_sd[<?php echo esc_attr( $i ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                                    </div>
                                                    <div class="rbfw-particular-date">
                                                        <input type="text" class="date_type rbfw_particulars_date" name="rbfw_particulars_sd[<?php echo esc_attr( $i ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                                    </div>
                                                <?php } ?>

                                                <div class="rbfw-time-slots-wrapper">
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

                                                            foreach ($particular_available_time as $key => $item) {
                                                                if(is_array($item)){
                                                                    ?>
                                                                    <div class="time-slot time-slot-indicator <?php echo $item['status'] ?>" data-id="<?php echo $i ?>">
                                                                        <span class="time-slot-time"><?php echo $item['time'] ?></span>
                                                                        <?php if($type=='md'){ ?>
                                                                            <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][id]" value="<?php echo $i ?>">
                                                                            <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][time]" value="<?php echo $item['time'] ?>">
                                                                            <input type="hidden" name="rbfw_particulars[<?php echo $i ?>][available_time][<?php echo $j ?>][status]" value="<?php echo $item['status'] ?>">
                                                                        <?php }elseif($type=='mi'){ ?>
                                                                            <input type="hidden" name="rbfw_particulars_mi[<?php echo $i ?>][available_time][<?php echo $j ?>][id]" value="<?php echo $i ?>">
                                                                            <input type="hidden" name="rbfw_particulars_mi[<?php echo $i ?>][available_time][<?php echo $j ?>][time]" value="<?php echo $item['time'] ?>">
                                                                            <input type="hidden" name="rbfw_particulars_mi[<?php echo $i ?>][available_time][<?php echo $j ?>][status]" value="<?php echo $item['status'] ?>">
                                                                        <?php }else{ ?>
                                                                            <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][id]" value="<?php echo $i ?>">
                                                                            <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][time]" value="<?php echo $item['time'] ?>">
                                                                            <input type="hidden" name="rbfw_particulars_sd[<?php echo $i ?>][available_time][<?php echo $j ?>][status]" value="<?php echo $item['status'] ?>">
                                                                        <?php } ?>
                                                                        <div class="time-slot-remove" title="Remove time slot">×</div>
                                                                    </div>
                                                                    <?php $j++;  ?>
                                                                <?php  } ?>
                                                            <?php  } ?>
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
                                                <div class="rbfw-particular-time-action">
                                                    <button type="button" class="remove-row button"><?php echo esc_html__( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                </div>
                                            </div>
                                                <?php $i++; }  ?>
                                            <?php } ?>
                                        <?php } ?>
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
                                            <input type="text" class="rbfw_start_date rbfw_particulars_date" placeholder="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div>
                                            <input type="text" class="rbfw_end_date  rbfw_particulars_date" placeholder="<?php echo date('Y-m-d'); ?>">
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

            public function get_pricing_validation_errors( $item_type, $post_data ) {
                $errors = [];
                $item_type = sanitize_text_field( (string) $item_type );

                if ( in_array( $item_type, [ 'bike_car_sd', 'appointment' ], true ) ) {
                    $rows = ( isset( $post_data['rbfw_bike_car_sd_data'] ) && is_array( $post_data['rbfw_bike_car_sd_data'] ) )
                        ? $post_data['rbfw_bike_car_sd_data']
                        : [];
                    $timely = isset( $post_data['manage_inventory_as_timely'] ) && $post_data['manage_inventory_as_timely'] === 'on';
                    $require_qty = $item_type === 'appointment' || ! $timely;
                    $has_valid_row = false;

                    // "Manage a single-item inventory on an hourly basis" needs the time
                    // picker enabled so slots can be selected — enforce that pairing.
                    $time_picker_on = isset( $post_data['rbfw_enable_time_picker'] ) && $post_data['rbfw_enable_time_picker'] === 'yes';
                    if ( $timely && ! $time_picker_on ) {
                        $errors[] = __( 'Please enable "Enable Time Picker" — it is required when "Manage a single-item inventory on an hourly basis" is enabled.', 'booking-and-rental-manager-for-woocommerce' );
                    }

                    if ( empty( $rows ) ) {
                        $errors[] = __( 'At least one rental option row is required.', 'booking-and-rental-manager-for-woocommerce' );
                        return $errors;
                    }

                    foreach ( $rows as $index => $row ) {
                        if ( ! is_array( $row ) ) {
                            continue;
                        }

                        $rent_type = trim( (string) ( $row['rent_type'] ?? '' ) );
                        $price     = trim( (string) ( $row['price'] ?? '' ) );
                        $qty       = trim( (string) ( $row['qty'] ?? '' ) );

                        if ( $rent_type === '' && $price === '' && $qty === '' ) {
                            continue;
                        }

                        $row_num = (int) $index + 1;

                        if ( $rent_type === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Rental option name is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( $price === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Price is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( $require_qty && $qty === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Stock/Day is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }

                        if ( $rent_type !== '' && $price !== '' && ( ! $require_qty || $qty !== '' ) ) {
                            $has_valid_row = true;
                        }
                    }

                    if ( ! $has_valid_row ) {
                        $errors[] = __( 'At least one complete rental option row is required (name, price, stock/day).', 'booking-and-rental-manager-for-woocommerce' );
                    }

                    return $errors;
                }

                if ( $item_type === 'resort' ) {
                    $rows = ( isset( $post_data['rbfw_resort_room_data'] ) && is_array( $post_data['rbfw_resort_room_data'] ) )
                        ? $post_data['rbfw_resort_room_data']
                        : [];
                    $has_valid_row = false;

                    if ( empty( $rows ) ) {
                        return [
                            __( 'At least one resort room type row is required.', 'booking-and-rental-manager-for-woocommerce' ),
                        ];
                    }

                    foreach ( $rows as $index => $row ) {
                        if ( ! is_array( $row ) ) {
                            continue;
                        }

                        $room_type = trim( (string) ( $row['room_type'] ?? '' ) );
                        $daynight  = trim( (string) ( $row['rbfw_room_daynight_rate'] ?? '' ) );
                        $qty       = trim( (string) ( $row['rbfw_room_available_qty'] ?? '' ) );

                        if ( $room_type === '' && $daynight === '' && $qty === '' ) {
                            continue;
                        }

                        $row_num = (int) $index + 1;

                        if ( $room_type === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Room type is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( $daynight === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Day-night price is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( $qty === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Stock quantity is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }

                        if ( $room_type !== '' && $daynight !== '' && $qty !== '' ) {
                            $has_valid_row = true;
                        }
                    }

                    if ( ! $has_valid_row ) {
                        $errors[] = __( 'At least one complete resort room row is required (room type, day-night price, stock quantity).', 'booking-and-rental-manager-for-woocommerce' );
                    }

                    return $errors;
                }

                if ( $item_type === 'multiple_items' ) {
                    $rows = ( isset( $post_data['multiple_items_info'] ) && is_array( $post_data['multiple_items_info'] ) )
                        ? $post_data['multiple_items_info']
                        : [];
                    $pricing_types = ( isset( $post_data['pricing_types'] ) && is_array( $post_data['pricing_types'] ) )
                        ? $post_data['pricing_types']
                        : [];
                    $enabled_types = [
                        'hourly'  => ( $pricing_types['hourly'] ?? '' ) === 'on',
                        'daily'   => ( $pricing_types['daily'] ?? '' ) === 'on',
                        'weekly'  => ( $pricing_types['weekly'] ?? '' ) === 'on',
                        'monthly' => ( $pricing_types['monthly'] ?? '' ) === 'on',
                    ];
                    $has_valid_row = false;

                    if ( empty( $rows ) ) {
                        return [
                            __( 'At least one item row is required for Multiple Items type.', 'booking-and-rental-manager-for-woocommerce' ),
                        ];
                    }

                    foreach ( $rows as $index => $row ) {
                        if ( ! is_array( $row ) ) {
                            continue;
                        }

                        $item_name = trim( (string) ( $row['item_name'] ?? '' ) );
                        $qty       = trim( (string) ( $row['available_qty'] ?? '' ) );
                        $has_price = false;

                        foreach ( $enabled_types as $type => $enabled ) {
                            if ( ! $enabled ) {
                                continue;
                            }
                            $price_key = $type . '_price';
                            if ( trim( (string) ( $row[ $price_key ] ?? '' ) ) !== '' ) {
                                $has_price = true;
                                break;
                            }
                        }

                        if ( $item_name === '' && $qty === '' && ! $has_price ) {
                            continue;
                        }

                        $row_num = (int) $index + 1;

                        if ( $item_name === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Item name is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( $qty === '' ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: Quantity is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }
                        if ( ! $has_price ) {
                            $errors[] = sprintf(
                                /* translators: %d: row number */
                                __( 'Row %d: At least one enabled price is required.', 'booking-and-rental-manager-for-woocommerce' ),
                                $row_num
                            );
                        }

                        if ( $item_name !== '' && $qty !== '' && $has_price ) {
                            $has_valid_row = true;
                        }
                    }

                    if ( ! $has_valid_row ) {
                        $errors[] = __( 'At least one complete item row is required (item name, quantity, and price).', 'booking-and-rental-manager-for-woocommerce' );
                    }
                }

                return $errors;
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
                    $rbfw_item_type          = isset( $_POST['rbfw_item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_item_type'] ) ) : '';

                    $pricing_validation_errors = $this->get_pricing_validation_errors( $rbfw_item_type, wp_unslash( $_POST ) );
                    if ( ! empty( $pricing_validation_errors ) ) {
                        set_transient( 'rbfw_pricing_save_errors_' . $post_id, $pricing_validation_errors, 30 );
                        return;
                    }

                    delete_transient( 'rbfw_pricing_save_errors_' . $post_id );

                    $rbfw_enable_monthly_rate                  = isset( $_POST['rbfw_enable_monthly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_monthly_rate'] ) ) : 'no';
                    $rbfw_monthly_rate                         = isset( $_POST['rbfw_monthly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_monthly_rate'] ) ) : 0;

                    $rbfw_enable_day_threshold_for_monthly     = isset( $_POST['rbfw_enable_day_threshold_for_monthly'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_day_threshold_for_monthly'] ) ) : 'no';
                    $rbfw_day_threshold_for_monthly            = isset( $_POST['rbfw_day_threshold_for_monthly'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_day_threshold_for_monthly'] ) ) : 0;

                    $rbfw_enable_weekly_rate                   = isset( $_POST['rbfw_enable_weekly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_weekly_rate'] ) ) : 'no';
                    $rbfw_weekly_rate                          = isset( $_POST['rbfw_weekly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_weekly_rate'] ) ) : 0;

                    $rbfw_enable_day_threshold_for_weekly      = isset( $_POST['rbfw_enable_day_threshold_for_weekly'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_day_threshold_for_weekly'] ) ) : 'no';
                    $rbfw_day_threshold_for_weekly             = isset( $_POST['rbfw_day_threshold_for_weekly'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_day_threshold_for_weekly'] ) ) : 0;


                    $rbfw_enable_daily_rate             = isset( $_POST['rbfw_enable_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_daily_rate'] ) ) : 'no';
					$daily_rate                         = isset( $_POST['rbfw_daily_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_daily_rate'] ) ) : 0;

                    $rbfw_enable_time_picker            = isset( $_POST['rbfw_enable_time_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_time_picker'] ) ) : 'no';
                    $rbfw_mi_hourly_to_half_day_pivot   = isset( $_POST['rbfw_mi_hourly_to_half_day_pivot'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mi_hourly_to_half_day_pivot'] ) ) : '';
                    $rbfw_mi_half_day_to_daily_pivot    = isset( $_POST['rbfw_mi_half_day_to_daily_pivot'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mi_half_day_to_daily_pivot'] ) ) : '';
                    $rbfw_mi_daily_to_weekly_pivot      = isset( $_POST['rbfw_mi_daily_to_weekly_pivot'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mi_daily_to_weekly_pivot'] ) ) : '';
                    $rbfw_mi_weekly_to_monthly_pivot    = isset( $_POST['rbfw_mi_weekly_to_monthly_pivot'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_mi_weekly_to_monthly_pivot'] ) ) : '';

                    $rbfw_hourly_threshold       = isset( $_POST['rbfw_hourly_threshold'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_hourly_threshold'] ) ) : '0';
                    $rbfw_enable_hourly_threshold       = isset( $_POST['rbfw_enable_hourly_threshold'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_hourly_threshold'] ) ) : 'no';

                    $rbfw_enable_hourly_rate            = isset( $_POST['rbfw_enable_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_hourly_rate'] ) ) : 'no';
                    $hourly_rate                        = isset( $_POST['rbfw_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_hourly_rate'] ) ) : 0;



                    $rbfw_enable_half_day_rate           = isset( $_POST['rbfw_enable_half_day_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_half_day_rate'] ) ) : 0;
                    $rbfw_half_day_rate                  = isset( $_POST['rbfw_half_day_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_half_day_rate'] ) ) : 'no';
                    $half_day_hour_threshold_start       = isset( $_POST['half_day_hour_threshold_start'] ) ? sanitize_text_field( wp_unslash( $_POST['half_day_hour_threshold_start'] ) ) : 'no';
                    $half_day_hour_threshold_end         = isset( $_POST['half_day_hour_threshold_end'] ) ? sanitize_text_field( wp_unslash( $_POST['half_day_hour_threshold_end'] ) ) : 0;


                    $rbfw_enable_daywise_price          = isset( $_POST['rbfw_enable_daywise_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_daywise_price'] ) ) : 'no';

                    if($rbfw_item_type=='bike_car_md' || $rbfw_item_type=='equipment' || $rbfw_item_type=='dress' || $rbfw_item_type=='others'){
                        $rdfw_available_time              = isset( $input_data_sabitized['rdfw_available_time'] ) ? $input_data_sabitized['rdfw_available_time'] : [];
                        $particulars_data           = isset( $_POST['rbfw_particulars'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_particulars'] ) : [];
                    }elseif ($rbfw_item_type=='multiple_items'){
                        $rdfw_available_time              = isset( $input_data_sabitized['rdfw_available_time_mi'] ) ? $input_data_sabitized['rdfw_available_time_mi'] : [];
                        $particulars_data           = isset( $_POST['rbfw_particulars_mi'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_particulars_mi'] ) : [];
                    }
                    else{
                        $rdfw_available_time              = isset( $input_data_sabitized['rdfw_available_time_sd'] ) ? $input_data_sabitized['rdfw_available_time_sd'] : [];
                        $particulars_data           = isset( $_POST['rbfw_particulars_sd'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_particulars_sd'] ) : [];
                    }

                    $rbfw_bike_car_sd_data              = isset( $input_data_sabitized['rbfw_bike_car_sd_data'] ) ? $input_data_sabitized['rbfw_bike_car_sd_data'] : [];

                    $pricing_types                    = isset( $input_data_sabitized['pricing_types'] ) ? $input_data_sabitized['pricing_types'] : [];
                    $pricing_types['_initialized']    = '1';
                    $multiple_items_info              = isset( $input_data_sabitized['multiple_items_info'] ) ? $input_data_sabitized['multiple_items_info'] : [];
                    if ( is_array( $multiple_items_info ) ) {
                        $multiple_items_info = array_values( array_filter( $multiple_items_info, function ( $item ) {
                            $item_name     = isset( $item['item_name'] ) ? trim( (string) $item['item_name'] ) : '';
                            $hourly_price  = isset( $item['hourly_price'] ) ? trim( (string) $item['hourly_price'] ) : '';
                            $daily_price   = isset( $item['daily_price'] ) ? trim( (string) $item['daily_price'] ) : '';
                            $weekly_price  = isset( $item['weekly_price'] ) ? trim( (string) $item['weekly_price'] ) : '';
                            $monthly_price = isset( $item['monthly_price'] ) ? trim( (string) $item['monthly_price'] ) : '';

                            return $item_name !== '' || $hourly_price !== '' || $daily_price !== '' || $weekly_price !== '' || $monthly_price !== '';
                        } ) );
                    }


                    $rbfw_enable_resort_daylong_price = isset( $_POST['rbfw_enable_resort_daylong_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_resort_daylong_price'] ) ) : 'no';
					$rbfw_resort_room_data = isset( $input_data_sabitized['rbfw_resort_room_data'] ) ? $input_data_sabitized['rbfw_resort_room_data'] : [];
					$rbfw_sd_appointment_max_qty_per_session = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ) : '';
					$rbfw_sd_appointment_ondays              = isset( $input_data_sabitized['rbfw_sd_appointment_ondays'] ) ? $input_data_sabitized['rbfw_sd_appointment_ondays'] : [];
					$rbfw_item_stock_quantity_timely = isset( $_POST['rbfw_item_stock_quantity_timely'] ) ? intval( wp_unslash( $_POST['rbfw_item_stock_quantity_timely'] ) ) : 1;
					// daywise configureation============================
					//sun

                    $manage_inventory_as_timely = isset( $_POST['manage_inventory_as_timely'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_inventory_as_timely'] ) ) : 'off';
					if ( $rbfw_item_type === 'appointment' ) {
						$manage_inventory_as_timely = 'off';
					}
					$enable_specific_duration = isset( $_POST['enable_specific_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_specific_duration'] ) ) : 'off';

                    $rbfw_particular_switch = 'off';
                    if($rbfw_item_type=='bike_car_md' || $rbfw_item_type=='equipment' || $rbfw_item_type=='dress' || $rbfw_item_type=='others') {
                        $rbfw_particular_switch = isset($_POST['rbfw_particular_switch_md']) ? sanitize_text_field(wp_unslash($_POST['rbfw_particular_switch_md'])) : 'off';
                    }elseif ($rbfw_item_type=='multiple_items'){
                        $rbfw_particular_switch = isset($_POST['rbfw_particular_switch_mi']) ? sanitize_text_field(wp_unslash($_POST['rbfw_particular_switch_mi'])) : 'off';
                    }elseif ($rbfw_item_type=='bike_car_sd' || $rbfw_item_type=='appointment'){
                        $rbfw_particular_switch = isset($_POST['rbfw_particular_switch_sd']) ? sanitize_text_field(wp_unslash($_POST['rbfw_particular_switch_sd'])) : 'off';
                    }


                    update_post_meta( $post_id, 'rbfw_enable_monthly_rate', $rbfw_enable_monthly_rate );
                    update_post_meta( $post_id, 'rbfw_monthly_rate', $rbfw_monthly_rate );
                    update_post_meta( $post_id, 'rbfw_enable_day_threshold_for_monthly', $rbfw_enable_day_threshold_for_monthly );
                    update_post_meta( $post_id, 'rbfw_day_threshold_for_monthly', $rbfw_day_threshold_for_monthly );
                    update_post_meta( $post_id, 'rbfw_enable_weekly_rate', $rbfw_enable_weekly_rate );
                    update_post_meta( $post_id, 'rbfw_weekly_rate', $rbfw_weekly_rate );
                    update_post_meta( $post_id, 'rbfw_enable_day_threshold_for_weekly', $rbfw_enable_day_threshold_for_weekly );
                    update_post_meta( $post_id, 'rbfw_day_threshold_for_weekly', $rbfw_day_threshold_for_weekly );


                    update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );

                    update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
					update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );

					update_post_meta( $post_id, 'rbfw_enable_time_picker', $rbfw_enable_time_picker );
                    update_post_meta( $post_id, 'rbfw_mi_hourly_to_half_day_pivot', $rbfw_mi_hourly_to_half_day_pivot );
                    update_post_meta( $post_id, 'rbfw_mi_half_day_to_daily_pivot', $rbfw_mi_half_day_to_daily_pivot );
                    update_post_meta( $post_id, 'rbfw_mi_daily_to_weekly_pivot', $rbfw_mi_daily_to_weekly_pivot );
                    update_post_meta( $post_id, 'rbfw_mi_weekly_to_monthly_pivot', $rbfw_mi_weekly_to_monthly_pivot );

                    update_post_meta( $post_id, 'rbfw_hourly_threshold', $rbfw_hourly_threshold );
                    update_post_meta( $post_id, 'rbfw_enable_hourly_threshold', $rbfw_enable_hourly_threshold );


                    update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
                    update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );


                    update_post_meta( $post_id, 'rbfw_enable_half_day_rate', $rbfw_enable_half_day_rate );
                    update_post_meta( $post_id, 'rbfw_half_day_rate', $rbfw_half_day_rate );
                    update_post_meta( $post_id, 'half_day_hour_threshold_start', $half_day_hour_threshold_start );
                    update_post_meta( $post_id, 'half_day_hour_threshold_end', $half_day_hour_threshold_end );



					update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );

                   // echo '<pre>';print_r($particulars_data);echo '<pre>';exit;


                    update_post_meta( $post_id, 'rbfw_particular_switch', $rbfw_particular_switch );
                    update_post_meta( $post_id, 'rdfw_available_time', $rdfw_available_time );
                    update_post_meta( $post_id, 'rbfw_particulars_data', $particulars_data );

                    update_post_meta( $post_id, 'rbfw_bike_car_sd_data', $rbfw_bike_car_sd_data );

                    update_post_meta( $post_id, 'pricing_types', $pricing_types );
                    update_post_meta( $post_id, 'multiple_items_info', $multiple_items_info );


					update_post_meta( $post_id, 'rbfw_resort_room_data', $rbfw_resort_room_data );
					update_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', $rbfw_enable_resort_daylong_price );
					update_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', $rbfw_sd_appointment_max_qty_per_session );
					update_post_meta( $post_id, 'rbfw_sd_appointment_ondays', $rbfw_sd_appointment_ondays );

                    $days = [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];

                    foreach ( $days as $day ) {
                        // sanitize and get posted values
                        $hourly_rate = isset( $_POST["rbfw_{$day}_hourly_rate"] ) ? sanitize_text_field( wp_unslash( $_POST["rbfw_{$day}_hourly_rate"] ) ) : '';
                        $half_day_rate = isset( $_POST["rbfw_{$day}_half_day_rate"] ) ? sanitize_text_field( wp_unslash( $_POST["rbfw_{$day}_half_day_rate"] ) ) : '';
                        $daily_rate  = isset( $_POST["rbfw_{$day}_daily_rate"] ) ? sanitize_text_field( wp_unslash( $_POST["rbfw_{$day}_daily_rate"] ) ) : '';
                        $enabled     = isset( $_POST["rbfw_enable_{$day}_day"] ) ? sanitize_text_field( wp_unslash( $_POST["rbfw_enable_{$day}_day"] ) ) : 'no';

                        // update post meta
                        update_post_meta( $post_id, "rbfw_{$day}_hourly_rate", $hourly_rate );
                        update_post_meta( $post_id, "rbfw_{$day}_half_day_rate", $half_day_rate );
                        update_post_meta( $post_id, "rbfw_{$day}_daily_rate", $daily_rate );
                        update_post_meta( $post_id, "rbfw_enable_{$day}_day", $enabled );
                    }


                    /*$hourly_rate_sun = isset( $_POST['rbfw_sun_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_sun_hourly_rate'] ) ) : '';
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
					update_post_meta( $post_id, 'rbfw_enable_sat_day', $enabled_sat );*/

					update_post_meta( $post_id, 'manage_inventory_as_timely', $manage_inventory_as_timely );
					update_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', $rbfw_item_stock_quantity_timely );
					update_post_meta( $post_id, 'enable_specific_duration', $enable_specific_duration );
				}
			}

		/**
		 * Render all pricing panels for the modern editor without
		 * re-registering hooks (uses reflection to skip __construct).
		 */
		public static function render_for_modern_editor( int $post_id ): void {
			global $wp_filter;

			$renderer = ( new \ReflectionClass( static::class ) )->newInstanceWithoutConstructor();
			$renderer->rent_type( $post_id );
			$renderer->bike_car_single_day( $post_id );
			$renderer->appointment( $post_id );
			$renderer->resort_price_config( $post_id );
			$renderer->multiple_items( $post_id );

			// md_price_config() fires addon hooks internally. The modern editor view
			// renders those addons in dedicated cards after this call, so suppress them
			// here to prevent duplicate classic markup inside the pricing card.
			$saved_extra       = $wp_filter['rbfw_after_extra_service_table'] ?? null;
			$saved_seasonal    = $wp_filter['rbfw_after_week_price_table'] ?? null;
			$saved_mds_md      = $wp_filter['rbfw_after_general_price_table'] ?? null;
			$saved_mds_resort  = $wp_filter['rbfw_after_room_type_price_saver_price_table'] ?? null;
			unset( $wp_filter['rbfw_after_extra_service_table'] );
			unset( $wp_filter['rbfw_after_week_price_table'] );
			unset( $wp_filter['rbfw_after_general_price_table'] );
			unset( $wp_filter['rbfw_after_room_type_price_saver_price_table'] );

			$renderer->md_price_config( $post_id );

			if ( $saved_extra !== null ) {
				$wp_filter['rbfw_after_extra_service_table'] = $saved_extra;
			}
			if ( $saved_seasonal !== null ) {
				$wp_filter['rbfw_after_week_price_table'] = $saved_seasonal;
			}
			if ( $saved_mds_md !== null ) {
				$wp_filter['rbfw_after_general_price_table'] = $saved_mds_md;
			}
			if ( $saved_mds_resort !== null ) {
				$wp_filter['rbfw_after_room_type_price_saver_price_table'] = $saved_mds_resort;
			}
		}

		}
		new RBFW_Pricing();
	}
	
	



