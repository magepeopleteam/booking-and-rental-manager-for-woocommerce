<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Inventory' ) ) {
		class RBFW_Inventory {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu( $rbfw_id ) {
				$rbfw_item_type         = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
				?>
                <li data-target-tabs="#rbfw_variations" <?php echo in_array( $rbfw_item_type, [ 'resort', 'appointment', 'multiple_items' ], true ) ? 'style="display:none"' : ''; ?>>
                    <i class="fas fa-table-cells-large"></i><?php esc_html_e( 'Inventory', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Inventory Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure Inventory Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php echo esc_html( $title ); ?>
                        </label>
                        <p><?php echo esc_html( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function variation_settings( $post_id ) {
				$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
				$rbfw_variations_data   = get_post_meta( $post_id, 'rbfw_variations_data', true ) ? get_post_meta( $post_id, 'rbfw_variations_data', true ) : [];

              //  echo '<pre>';print_r($rbfw_variations_data);echo '<pre>';

                ?>
                <section class="rbfw_variations_table_wrap <?php echo esc_attr( ( $rbfw_enable_variations == 'yes' ) ? 'show' : 'hide' ); ?>">
                    <label class="md-inv-variations-title"><?php esc_html_e( 'Item Variations', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                    <div class="form-table rbfw_variations_table">
                        <div class="rbfw_variations_table_body ui-sortable">
						<?php
							if ( ! empty( $rbfw_variations_data ) ) {
								$i = 0;
								foreach ( $rbfw_variations_data as $key => $value ) {
									$selected_value = ! empty( $value['selected_value'] ) ? $value['selected_value'] : '';
									?>
                                    <div class="rbfw_variations_table_row rbfw-var-group" data-key="<?php echo esc_attr( $i ); ?>">
                                        <!-- Field Label / "remove group" are gone (there's only ever this one group
                                             now, no "+ Add Variation" left to replace it with); these hidden fields
                                             just carry the existing values forward unchanged on save. -->
                                        <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][field_label]" value="<?php echo esc_attr( $value['field_label'] ); ?>">
                                        <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][field_id]" value="rbfw_variation_id_<?php echo esc_attr( $i ); ?>">
                                        <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][selected_value]" value="<?php echo esc_attr( $selected_value ); ?>">
                                        <div class=variations-inner-table>
                                            <div class="rbfw_variations_value_table rbfw_variations_value_table_tbody rbfw-var-chip-list">
											<?php
												$c = 0;
												foreach ( $rbfw_variations_data[ $i ]['value'] as $key => $value ):
													?>
                                                    <div class="rbfw_variations_value_table_row rbfw-var-chip" data-key="<?php echo esc_attr( $c ); ?>">
                                                        <input type="text" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][value][<?php echo esc_attr( $c ); ?>][name]" value="<?php echo esc_attr( $value['name'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Red', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value rbfw-var-chip-name">
                                                        <span class="rbfw-var-chip-dash">—</span>
                                                        <input type="number" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][value][<?php echo esc_attr( $c ); ?>][quantity]" value="<?php echo esc_attr( $value['quantity'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 3', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw-var-chip-qty">
                                                        <span class="rbfw-var-chip-unit"><?php esc_html_e( 'in stock', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                        <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr( $i ); ?>][value][<?php echo esc_attr( $c ); ?>][price]" value="<?php echo esc_attr( isset( $value['price'] ) ? $value['price'] : '' ); ?>">
                                                        <button type="button" class="button remove-rbfw_variations_value_table_row rbfw-var-chip-remove" title="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
                                                    </div>
													<?php
													$c ++;
												endforeach;
											?>
                                            </div>
                                            <div class="rbfw-var-add-wrap">
                                                <div class="rbfw-var-add-label"><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                                <div class="rbfw-var-add-form">
                                                    <input type="text" class="rbfw-var-add-name" placeholder="<?php esc_attr_e( 'Value name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                    <input type="number" min="0" class="rbfw-var-add-qty" placeholder="<?php esc_attr_e( 'Qty in stock', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                    <button type="button" class="rbfw-var-add-btn" data-key="<?php echo esc_attr( $i ); ?>" disabled><?php esc_html_e( '+ Add Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php
									$i ++;
								}
							} else {
								?>
                                <div class="rbfw_variations_table_row rbfw-var-group" data-key="0">
                                    <input type="hidden" name="rbfw_variations_data[0][field_id]" value="rbfw_variation_id_0">
                                    <input type="hidden" name="rbfw_variations_data[0][selected_value]" value="">
                                    <div class="variations-inner-table">
                                        <div class="rbfw_variations_value_table rbfw_variations_value_table_tbody rbfw-var-chip-list">
											<?php
											// Brand-new items start with a few ready-made example values (editable/
											// removable like any other chip) instead of one blank row, so there's
											// something to see and tweak rather than an empty "+ Add New Value" loop.
											$rbfw_default_variation_examples = array( 'Red', 'Green', 'Blue' );
											foreach ( $rbfw_default_variation_examples as $rbfw_default_c => $rbfw_default_name ) :
												?>
                                                <div class="rbfw_variations_value_table_row rbfw-var-chip" data-key="<?php echo esc_attr( $rbfw_default_c ); ?>">
                                                    <input type="text" name="rbfw_variations_data[0][value][<?php echo esc_attr( $rbfw_default_c ); ?>][name]" value="<?php echo esc_attr( $rbfw_default_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Red', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value rbfw-var-chip-name">
                                                    <span class="rbfw-var-chip-dash">—</span>
                                                    <input type="number" name="rbfw_variations_data[0][value][<?php echo esc_attr( $rbfw_default_c ); ?>][quantity]" value="3" placeholder="<?php esc_attr_e( 'e.g. 3', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw-var-chip-qty">
                                                    <span class="rbfw-var-chip-unit"><?php esc_html_e( 'in stock', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                    <input type="hidden" name="rbfw_variations_data[0][value][<?php echo esc_attr( $rbfw_default_c ); ?>][price]" value="">
                                                    <button type="button" class="button remove-rbfw_variations_value_table_row rbfw-var-chip-remove" title="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
                                                </div>
											<?php endforeach; ?>
                                        </div>
                                        <div class="rbfw-var-add-wrap">
                                            <div class="rbfw-var-add-label"><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                            <div class="rbfw-var-add-form">
                                                <input type="text" class="rbfw-var-add-name" placeholder="<?php esc_attr_e( 'Value name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                <input type="number" min="0" class="rbfw-var-add-qty" placeholder="<?php esc_attr_e( 'Qty in stock', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                <button type="button" class="rbfw-var-add-btn" data-key="0" disabled><?php esc_html_e( '+ Add Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>
                        </div>
                    </div>
                </section>
				<?php
			}

			public function stock_settings( $post_id ) {
				$rbfw_item_stock_quantity    = get_post_meta( $post_id, 'rbfw_item_stock_quantity', true ) ? get_post_meta( $post_id, 'rbfw_item_stock_quantity', true ) : '';
				$rbfw_enable_variations      = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
				$stock_manage_on_return_date = get_post_meta( $post_id, 'stock_manage_on_return_date', true ) ? get_post_meta( $post_id, 'stock_manage_on_return_date', true ) : 'no';

				// Return-date inventory release only applies to date-range (multi-day) rentals.
				// Hide it for Single Day and Appointment, which have no scheduled return date.
				$rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$hide_return    = in_array( $rbfw_item_type, array( 'bike_car_sd', 'appointment' ), true );

				// Single Day's own stock field, moved here from the Pricing tab. Only
				// relevant when "Manage a single-item inventory on an hourly basis" is
				// on (one shared pool across all rate rows); when it's off, stock is
				// per rate row instead (the "Stock/Day" column on the Pricing tab), and
				// Appointment always forces this toggle off, so this never applies there.
				$manage_inventory_as_timely  = get_post_meta( $post_id, 'manage_inventory_as_timely', true ) ? get_post_meta( $post_id, 'manage_inventory_as_timely', true ) : 'off';
				$rbfw_item_stock_quantity_timely = get_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', true ) ? get_post_meta( $post_id, 'rbfw_item_stock_quantity_timely', true ) : '';
				$show_timely_stock           = ( $rbfw_item_type === 'bike_car_sd' && $manage_inventory_as_timely === 'on' );

				// How many units today's confirmed bookings are holding against this stock number —
				// same blocking-status + overlap rule the availability engine itself uses
				// (rbfw_count_overlapping_booked_qty), just evaluated for "right now".
				$booked_now = 0;
				if ( function_exists( 'rbfw_count_overlapping_booked_qty' ) ) {
					$today      = current_time( 'Y-m-d' );
					$booked_now = (int) rbfw_count_overlapping_booked_qty( $post_id, $today . ' 00:00:00', $today . ' 23:59:59' );
				}
				?>
                <section class="rbfw_stock_quantity_section">
                    <label class="md-inv-stock-label">
						<?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        <span class="md-inv-badge"><?php esc_html_e( 'Per day', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                    </label>
                    <div class="md-inv-stock-row">
                        <div class="item_stock_quantity<?php echo esc_attr( $rbfw_enable_variations === 'yes' ? ' is-stock-disabled' : '' ); ?>">
                            <input type="number" name="rbfw_item_stock_quantity" id="rbfw_item_stock_quantity" value="<?php echo esc_attr( $rbfw_item_stock_quantity ); ?>" placeholder="<?php esc_attr_e( 'e.g. 10', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_html( $rbfw_enable_variations == 'yes' ) ? 'disabled' : ''; ?>>
                        </div>
                        <label class="md-inv-return-check rbfw_stock_return_date_section"<?php echo $hide_return ? ' style="display:none"' : ''; ?>>
                            <input type="checkbox" name="stock_manage_on_return_date" value="<?php echo esc_attr( $stock_manage_on_return_date ); ?>" <?php echo esc_attr( ( $stock_manage_on_return_date == 'yes' ) ? 'checked' : '' ); ?>>
							<?php esc_html_e( 'Track return-date availability', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                    </div>
                    <p class="md-inv-stock-hint"><?php esc_html_e( "Total units available for whole-day bookings. Turning on Single day's Time Picker switches this to per-time-slot stock automatically.", 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					<?php if ( $booked_now > 0 ) : ?>
                    <div class="md-inv-info-banner">
                        <span class="dashicons dashicons-info-outline"></span>
                        <span>
							<?php
							printf(
								esc_html(
									_n(
										'%d booking is currently using this stock.',
										'%d bookings are currently using this stock.',
										$booked_now,
										'booking-and-rental-manager-for-woocommerce'
									)
								),
								(int) $booked_now
							);
							?>
							<?php esc_html_e( 'Lowering the number only limits new bookings — it never cancels or shrinks reservations customers already confirmed.', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </span>
                    </div>
					<?php endif; ?>
                </section>
                <section class="rbfw_timely_stock_quantity_section"<?php echo $show_timely_stock ? '' : ' style="display:none"'; ?>>
                    <div>
                        <label>
							<?php esc_html_e( 'Rent Item Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            <span class="rbfw-me-required-mark" title="<?php esc_attr_e( 'Required', 'booking-and-rental-manager-for-woocommerce' ); ?>"<?php echo ( $show_timely_stock && 'yes' !== $rbfw_enable_variations ) ? '' : ' style="display:none"'; ?>>*</span>
                        </label>
                        <p><?php esc_html_e( 'Add stock quantity that you want allow to rent, add total stock', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <div class="item_stock_quantity<?php echo esc_attr( $rbfw_enable_variations === 'yes' ? ' is-stock-disabled' : '' ); ?>">
                        <input type="number" min="0" name="rbfw_item_stock_quantity_timely" id="rbfw_item_stock_quantity_timely" value="<?php echo esc_attr( $rbfw_item_stock_quantity_timely ); ?>" placeholder="<?php esc_attr_e( 'Ex: 10', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-label="<?php esc_attr_e( 'Rent Item Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>" <?php echo esc_html( $rbfw_enable_variations === 'yes' ) ? 'disabled' : ''; ?> <?php echo ( $show_timely_stock && 'yes' !== $rbfw_enable_variations ) ? 'required' : ''; ?>>
                    </div>
                </section>
				<?php
			}

			/**
			 * Multiple Item Choosing no longer has a visible toggle (removed per
			 * request), but existing items may already have it set to 'yes' and
			 * the frontend templates (multi-day/multi-items registration) still
			 * read it. settings_save()/the modern editor's AJAX save both default
			 * an absent POST field to 'no' and unconditionally update_post_meta()
			 * — without this hidden input, the very next save of ANY field on an
			 * item that currently has it enabled would silently flip it off.
			 * This just carries the existing value through untouched.
			 */
			public function quantity_box_toggle( $post_id ) {
				$rbfw_enable_md_type_item_qty = get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) : 'no';
				?>
                <input type="hidden" name="rbfw_enable_md_type_item_qty" value="<?php echo esc_attr( $rbfw_enable_md_type_item_qty ); ?>">
				<?php
			}

			public function variation_table_switch_on_off( $post_id ) {
				$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
				?>
                <section>
                    <div>
                        <label><?php esc_html_e( 'Item variation', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                        <p><?php esc_html_e( 'Enable/Disable Variations. It will work when the type is Single Day, Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="rbfw_enable_variations" value="<?php echo esc_attr( $rbfw_enable_variations ); ?>" <?php echo esc_attr( ( $rbfw_enable_variations == 'yes' ) ? 'checked' : '' ); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
				<?php
			}

			/**
			 * Render the Inventory section for the modern editor.
			 *
			 * Mirrors the RBFW_Pricing / RBFW_Off_Day reuse pattern: the modern
			 * editor lacked any inventory UI, so its AJAX save read empty values
			 * and silently reset stock to 1000, disabled variations and wiped the
			 * variation rows on every save. Reusing the existing classic render
			 * methods (via a constructor-less instance, so no hooks re-register)
			 * surfaces the exact same fields/markup the save handler expects, and
			 * the already-loaded mkb-admin.js drives the variations repeater and
			 * the enable/return-date toggles unchanged.
			 *
			 * @param int $post_id Current rental item ID.
			 * @return void
			 */
			public static function render_for_modern_editor( int $post_id ): void {
				$renderer = ( new \ReflectionClass( static::class ) )->newInstanceWithoutConstructor();
				$renderer->stock_settings( $post_id );
				$renderer->quantity_box_toggle( $post_id );
				$renderer->variation_table_switch_on_off( $post_id );
				$renderer->variation_settings( $post_id );
			}

			public function add_tabs_content( $post_id ) {
				$rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_variations" data-tab-item="#rbfw_variations" <?php if ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'appointment' ) {
					echo 'style="display:none"';
				} ?>>
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Inventory Settings', 'Inventory Settings' ); ?>
					<?php $this->stock_settings( $post_id ); ?>
					<?php $this->quantity_box_toggle( $post_id ); ?>
					<?php $this->variation_table_switch_on_off( $post_id ); ?>
					<?php $this->variation_settings( $post_id ); ?>
                </div>
				<?php
			}

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$rbfw_enable_variations   = isset( $_POST['rbfw_enable_variations'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_variations'] ) ) : 'no';
					$rbfw_item_stock_quantity = isset( $_POST['rbfw_item_stock_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_item_stock_quantity'] ) ) : '';
					$stock_manage_on_return_date = isset( $_POST['stock_manage_on_return_date'] ) ? sanitize_text_field( wp_unslash( $_POST['stock_manage_on_return_date'] ) ) : '';
					$rbfw_enable_md_type_item_qty = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_md_type_item_qty'] ) ) : 'no';
					$rbfw_variations_data = isset( $_POST['rbfw_variations_data'] ) ? rbfw_clean_variations_data( RBFW_Function::data_sanitize( $_POST['rbfw_variations_data'] ) ) : [];

                    $rbfw_item_stock_quantity = ($rbfw_item_stock_quantity)?$rbfw_item_stock_quantity:1000;

					update_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', $rbfw_enable_md_type_item_qty );
					update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );
					update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );
					update_post_meta( $post_id, 'stock_manage_on_return_date', $stock_manage_on_return_date );
					update_post_meta( $post_id, 'rbfw_variations_data', $rbfw_variations_data );
				}
			}
		}
		new RBFW_Inventory();
	}