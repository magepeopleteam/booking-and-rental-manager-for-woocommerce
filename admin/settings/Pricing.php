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

			public function field_service_price( $option ) {
				$id = isset( $option['id'] ) ? $option['id'] : "";
				if ( empty( $id ) ) {
					return;
				}
				$field_name  = isset( $option['field_name'] ) ? $option['field_name'] : $id;
				$conditions  = isset( $option['conditions'] ) ? $option['conditions'] : array();
				$placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
				$remove_text = isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-trash-can"></i>';
				$sortable    = isset( $option['sortable'] ) ? $option['sortable'] : true;
				$default     = isset( $option['default'] ) ? $option['default'] : array();
				$values      = isset( $option['value'] ) ? $option['value'] : array();
				$values      = ! empty( $values ) ? $values : $default;
				$limit       = ! empty( $option['limit'] ) ? $option['limit'] : '';
				$field_id    = $id;
				$field_name  = ! empty( $field_name ) ? $field_name : $id;
				ob_start();
				?>
                <table class="form-table rbfw_service_category_table">
                    <tbody class="sortable_tr">
					<?php
						if ( ! empty( $values ) ):
							$i = 0;
							foreach ( $values as $value ):?>
                                <tr data-cat="<?php echo esc_attr( $i ); ?>">
                                    <td>
                                        <div class="services_category_wrapper">
                                            <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                                <div class="service_category_inner_wrap">
                                                    <section class="service_category_title sss ">
                                                        <label class=" mb-1">
															<?php echo esc_html__( 'Service Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        </label>
                                                        <input type="text" value="<?php echo esc_attr( $value['cat_title'] ); ?>" name="rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_title]" data-key="<?php echo esc_attr( $i ); ?>" placeholder="<?php echo esc_attr__( 'Service Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                    </section>
                                                    <div class="service_category_inner_item_wrap sortable">
														<?php
															if ( ! empty( $value['cat_services'] ) ) {
																$c = 0;
																foreach ( $value['cat_services'] as $service ) {
																	$icon               = $service['icon'];
																	$title              = $service['title'];
																	$price              = $service['price'];
																	$stock_quantity     = isset( $service['stock_quantity'] ) ? $service['stock_quantity'] : '';
																	$service_price_type = $service['service_price_type'] ?? '';
																	?>
                                                                    <div class="item">
                                                                        <a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="<?php echo esc_attr( $c ); ?>"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                                                                        <div class="rbfw_service_icon_preview p-1" data-key="<?php echo esc_attr( $c ); ?>">
																			<?php if ( $icon ) {
																				echo '<i class="' . esc_attr( $icon ) . '"></i>';
																			} ?>
                                                                        </div>
                                                                        <input type='hidden' name='rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='<?php echo esc_attr( $icon ); ?>' data-key="<?php echo esc_attr( $c ); ?>" class="rbfw_service_icon"/>
                                                                        <input type='text' name='rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value="<?php echo esc_attr( $title ); ?>" data-key="<?php echo esc_attr( $c ); ?>"/>
                                                                        <input type='text' class="medium" name='rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][price]' placeholder='<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='<?php echo esc_attr( $price ); ?>' data-key="0"/>
                                                                        <input type='text' class="medium" name='rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][stock_quantity]' placeholder='<?php echo esc_attr( 'Stock', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='<?php echo esc_attr( $stock_quantity ); ?>' data-key="0"/>

                                                                        <label class="" for="rbfw_dt_sidebar_switch-on">
                                                                            <input name="rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][service_price_type]" type="radio" <?php echo esc_attr( $service_price_type == 'one_time' ? 'checked' : '' ); ?> id="rbfw_dt_sidebar_switch-on" value="one_time">
                                                                            <span class="sw-button"><?php echo esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?> </span>
                                                                        </label>
                                                                        <label class="checked" for="rbfw_dt_sidebar_switch-off">
                                                                            <input name="rbfw_service_category_price[<?php echo esc_attr( $i ); ?>][cat_services][<?php echo esc_attr( $c ); ?>][service_price_type]" type="radio" <?php echo esc_attr( $service_price_type == 'day_wise' ? 'checked' : '' ); ?> id="rbfw_dt_sidebar_switch-off" value="day_wise">
                                                                            <span class="sw-button"><?php echo esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ); ?> </span>
                                                                        </label>
                                                                        <div>
																			<?php if ( $sortable ): ?>
                                                                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																			<?php endif; ?>
                                                                            <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?></span>
                                                                        </div>
                                                                    </div>
																	<?php
																	$c ++;
																}
															}
														?>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="ppof-button add-new-service">
											<i class="fas fa-circle-plus"></i>
											<?php echo esc_html__( 'Add New Service', 'booking-and-rental-manager-for-woocommerce' ); ?>
										</span>
                                        </div>
                                    </td>
                                    <td>
										<?php if ( $sortable ): ?>
                                            <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
										<?php endif; ?>
                                        <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?></span>
                                    </td>
                                </tr>
								<?php
								$i ++;
							endforeach;
						else:
							?>
                            <tr data-cat="0">
                                <td>
                                    <div class="services_category_wrapper">
                                        <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                            <div class="service_category_inner_wrap">
                                                <section class="service_category_title sss">
                                                    <label>
														<?php echo esc_html__( 'Service Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                    </label>
                                                    <input type="text" name="rbfw_service_category_price[0][cat_title]" value="Service Type" data-key="0" placeholder="<?php echo esc_attr__( 'Service Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                </section>
                                                <div class="service_category_inner_item_wrap sortable">
                                                    <div class="item">
                                                        <a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0">
                                                            <i class="fas fa-circle-plus"></i>
															<?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        </a>
                                                        <div class="rbfw_service_icon_preview p-1" data-key="0"></div>
                                                        <input type='hidden' name='rbfw_service_category_price[0][cat_services][0][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' data-key="0" class="rbfw_service_icon"/>
                                                        <input type='text' name='rbfw_service_category_price[0][cat_services][0][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value='' data-key="0"/>
                                                        <input type='text' class="medium" name='rbfw_service_category_price[0][cat_services][0][price]' placeholder='<?php echo esc_attr( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='' data-key="0"/>
                                                        <input type='text' class="medium" name='rbfw_service_category_price[0][cat_services][0][stock_quantity]' placeholder='<?php echo esc_attr( 'Stock', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='' data-key="0"/>
                                                        <label class="" for="rbfw_dt_sidebar_switch-on">
                                                            <input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time">
                                                            <span class="sw-button"><?php echo esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?> </span>
                                                        </label>
                                                        <label class="checked" for="rbfw_dt_sidebar_switch-off">
                                                            <input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked="">
                                                            <span class="sw-button"><?php echo esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ); ?> </span>
                                                        </label>
                                                        <div>
															<?php if ( $sortable ): ?>
                                                                <span class="button sort">
																<i class="fas fa-arrows-alt"></i>
															</span>
															<?php endif; ?>
                                                            <span class="button remove" onclick="jQuery(this).parent().parent().remove()">
															<?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?>
														</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="ppof-button add-new-service">
										<i class="fas fa-circle-plus"></i>
										<?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?>
									</span>
                                    </div>
                                </td>
                                <td>
									<?php if ( $sortable ): ?>
                                        <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
									<?php endif; ?>
                                    <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()">
									<?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?>
								</span>
                                </td>
                            </tr>
						<?php endif; ?>
                    </tbody>
                </table>
                <span class="ppof-button add-service-category mt-1">
					<i class="fas fa-circle-plus"></i>
					<?php echo esc_html__( 'Add New Service Category', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</span>
				<?php
				return ob_get_clean();
			}

			public function category_service_price( $post_id ) {
				$rbfw_item_type       = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$enable_service_price = get_post_meta( $post_id, 'rbfw_enable_category_service_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_category_service_price', true ) : 'off';
				$section_visibility   = ( $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment' && $rbfw_item_type != 'resort' ) ? 'show' : 'hide';
				?>
                <div class="rbfw_general_price_config_wrapper <?php echo esc_attr( $section_visibility ); ?>">
					<?php $this->panel_header( 'Service item price settings ', 'Service price settings with category.' ); ?>
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Enable Category service price ', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php echo esc_html__( 'You can enable/disable this section switching this button.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_category_service_price" value="<?php echo esc_attr( ( $enable_service_price == 'on' ) ? $enable_service_price : 'off' ); ?>" <?php echo esc_attr( ( $enable_service_price == 'on' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <div id='field-wrapper-<?php echo esc_attr( 'rbfw_service_category_price' ); ?>' class="field-wrapper field-text-multi-wrapper field-text-multi-wrapper-rbfw_service_category_price <?php echo esc_attr( ( $enable_service_price == 'on' ) ? 'show' : 'hide' ) ?>">
						<?php
							$options          = array(
								'id'          => 'rbfw_service_category_price',
								'type'        => 'md_service_category_price',
								'placeholder' => 'Service Name',
							);
							$option_value     = get_post_meta( $post_id, $options['id'], true );
							$options['value'] = is_serialized( $option_value ) ? unserialize( $option_value ) : $option_value;
							$id = isset( $options['id'] ) ? $options['id'] : "";
							if ( empty( $id ) ) {
								return;
							}
							$field_name  = isset( $option['field_name'] ) ? $option['field_name'] : $id;
							$conditions  = isset( $option['conditions'] ) ? $option['conditions'] : array();
							$placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
							$remove_text = isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-trash-can"></i>';
							$sortable    = isset( $option['sortable'] ) ? $option['sortable'] : true;
							$default     = isset( $option['default'] ) ? $option['default'] : array();
							$values      = isset( $option['value'] ) ? $option['value'] : array();
							$values      = ! empty( $values ) ? $values : $default;
							$limit       = ! empty( $option['limit'] ) ? $option['limit'] : '';
							$field_id    = $id;
							$field_name  = ! empty( $field_name ) ? $field_name : $id;
						?>
                        <script>
                            jQuery(document).on('click', '.tr_remove', function (e) {
                                e.stopImmediatePropagation();
                                jQuery(this).closest("tr").remove(); // Remove the entire row for categories
                            });
                            jQuery(document).on('click', '.remove-item', function () {
                                jQuery(this).closest('.item').remove(); // Remove the specific service item
                            });
                            jQuery(document).on('click', '.add-service-category', function (e) {
                                e.stopImmediatePropagation();
                                let dataCat = jQuery('.rbfw_service_category_table tbody tr:last-child').attr('data-cat');
                                let nextCat = parseInt(dataCat) + 1;
                                let html = '<tr data-cat="' + nextCat + '"><td><div class="services_category_wrapper"><div class="field-list rbfw_service_category_price"><div class="service_category_inner_wrap"><section class="service_category_title sss "><label><?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label><input type="text" class="rbfw_service_category_title" name="rbfw_service_category_price[' + nextCat + '][cat_title]" data-cat="' + nextCat + '" value="Service Type" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"></section>';
                                html += '<div class="service_category_inner_item_wrap sortable"><div class="item"><a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a><div class="rbfw_service_icon_preview p-1" data-key="0"></div><input type="hidden" name="rbfw_service_category_price[' + nextCat + '][cat_services][0][icon]" placeholder="<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="0" class="rbfw_service_icon">';
                                html += '<input type="text" name="rbfw_service_category_price[' + nextCat + '][cat_services][0][title]" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="" data-key="0">';
                                html += '<input type="text" class="medium" name="rbfw_service_category_price[' + nextCat + '][cat_services][0][price]" placeholder="Price" value="" data-key="0">';
                                html += '<input type="text" class="medium" name="rbfw_service_category_price[' + nextCat + '][cat_services][0][stock_quantity]" placeholder="Stock" value="" data-key="0">';
                                html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price[' + nextCat + '][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"><?php echo esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?></span> </label>';
                                html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price[' + nextCat + '][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"><?php echo esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ); ?> </span> </label>';
                                html += '<div><?php if($sortable):?><span class="button sort"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?></span></div></div></div></div></div><span class="ppof-button add-new-service"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></td><td> <?php if($sortable):?> <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button tr_remove"><?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?></span></td></tr>';
                                jQuery('.rbfw_service_category_table tbody').append(html);
                                jQuery(".sortable_tr").sortable({handle: '.tr_sort_handler'});
                                jQuery('.tr_remove').click(function (e) {
                                    jQuery(this).closest("tr").remove();
                                });
                            });
                            jQuery(document).on('click', '.add-new-service', function (e) {
                                e.stopImmediatePropagation();
                                let data_key = jQuery(this).siblings(".rbfw_service_category_price").find("div.item:last-child input").attr('data-key');
                                let i = parseInt(data_key);
                                let c = i + 1;
                                let theTarget = jQuery(this).siblings('.rbfw_service_category_price').find('.service_category_inner_wrap .service_category_inner_item_wrap');
                                jQuery(".sortable").sortable({handle: '.sort'});
                                let dataCat = jQuery(this).closest('tr').attr('data-cat');
                                html = '<div class="item">';
                                html += '<a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="' + c + '"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>';
                                html += '<div class="rbfw_service_icon_preview p-1" data-key="' + c + '"></div>';
                                html += '<input type="hidden" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][icon]" placeholder="<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="' + c + '" class="rbfw_service_icon"/>';
                                html += '<input type="text" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][title]" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-key="' + c + '"/>';
                                html += '<input type="text" class="medium" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][price]" placeholder="Price" data-key="' + c + '"/>';
                                html += '<input type="text" class="medium" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][stock_quantity]" placeholder="Stock" data-key="' + c + '"/>';
                                html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"> <?php echo esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?> </span> </label>';
                                html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"><?php echo esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ); ?> </span> </label>';

								<?php if($sortable):?>
                                html += '<div>';
                                html += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
								<?php endif; ?>

                                html += '<span class="button remove" onclick="jQuery(this).parent().parent().remove()' +
                                    '"><?php echo wp_kses( $remove_text, rbfw_allowed_html() ); ?></span>';
                                html += '</div></div>';
                                theTarget.append(html);
                                // Bind remove button event
                                jQuery('.remove-item').on('click', function () {
                                    jQuery(this).parent().parent().remove();
                                });
                            });
                            // Features Icon Popup
                            // Features Icon Popup
                            jQuery(document).on('click', '.rbfw_service_icon_btn', function (e) {
                                e.stopImmediatePropagation();
                                let remove_exist_data_key = jQuery("#rbfw_features_icon_list_wrapper").removeAttr('data-key');
                                let remove_active_label = jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                                let data_key = jQuery(this).attr('data-key');
                                let data_cat = jQuery(this).parents('tr').attr('data-cat');
                                jQuery('#rbfw_services_search_icon').val('');
                                jQuery('.rbfw_services_icon_list_body label').show();
                                jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', esc_attr(data_key));
                                jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', esc_attr(data_cat));
                                jQuery("#rbfw_features_icon_list_wrapper").mage_modal({
                                    escapeClose: false,
                                    clickClose: false,
                                    showClose: false
                                });
                                // Selected Feature Icon Action
                                jQuery(document).on('click', '.rbfw_features_icon_list_wrapper_modal label', function (e) {
                                    e.stopImmediatePropagation();
                                    let selected_label = jQuery(this);
                                    let selected_val = jQuery('input', this).val();
                                    let selected_data_key = jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
                                    let selected_data_cat = jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');
                                    jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                                    jQuery('.rbfw_service_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_service_icon_preview[data-key="' + selected_data_key + '"]').empty();
                                    jQuery(selected_label).addClass('selected');
                                    jQuery('.rbfw_service_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_service_icon[data-key="' + selected_data_key + '"]').val(selected_val);
                                    jQuery('.rbfw_service_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_service_icon_preview[data-key="' + selected_data_key + '"]').append('<i class="' + selected_val + '"></i>');
                                });
                                // Icon Filter
                                jQuery('#rbfw_services_search_icon').keyup(function (e) {
                                    let value = jQuery(this).val().toLowerCase();
                                    jQuery(".rbfw_services_icon_list_body label[data-id]").show().filter(function () {
                                        jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
                                    }).hide();
                                });
                            });
                        </script>
						<?php
							echo wp_kses( $this->field_service_price( $options ), rbfw_allowed_html() );
						?>
                    </div>
                </div>
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
                                                    <td><input type="text" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
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
                                                    <input type="text" name="rbfw_bike_car_sd_data[0][rent_type]" placeholder="<?php echo esc_attr( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
                            <p class="mt-2 <?php echo esc_attr( $rbfw_item_type == 'appointment' ? 'show' : 'show' ); ?>">
                                <button id="add-bike-car-sd-type-row" data-post_id="<?php echo esc_attr( $post_id ) ?>" class="ppof-button" <?php if ( $rbfw_item_type == 'appointment' ) {
									echo 'style="display:none"';
								} ?>><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
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
                                                    <td class="rbfw_bike_car_sd_price_table_action_column" <?php if ( $rbfw_item_type == 'appointment' ) {
														echo 'style="display:none"';
													} ?>><input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo esc_attr( $i ); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php echo esc_attr( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></td>
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
                                                        <input type="text" name="rbfw_resort_room_data[<?php echo esc_attr( $i ); ?>][room_type]" value="<?php echo esc_attr( $value['room_type'] ); ?>" placeholder="<?php echo esc_attr( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
                                                    <input type="text" name="rbfw_resort_room_data[0][room_type]" value="" placeholder="<?php echo esc_attr( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
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
				$rbfw_enable_hourly_rate   = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';
				$rbfw_enable_daily_rate    = get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) : 'yes';
				$rbfw_daily_rate           = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
				$rbfw_hourly_rate          = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
				$rbfw_item_type            = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$mdedo                     = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment' ) ? 'block' : 'none';
				$rbfw_enable_daywise_price = get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) : 'no';
				?>
                <div class="rbfw_general_price_config_wrapper " style="display: <?php echo esc_attr( $mdedo ) ?>;">
					<?php do_action( 'rbfw_before_general_price_table' ); ?>
					<?php $this->panel_header( 'General Price Configuration', 'General Price Configuration' ); ?>
					<?php do_action( 'rbfw_before_general_price_table_row' ); ?>
                    <section>
                        <div>
                            <label for=""><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p for=""><?php esc_html_e( 'Pricing will be calculated based on number of day.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="rbfw_enable_daily_rate" value="<?php echo esc_attr( $rbfw_enable_daily_rate ); ?>" <?php echo esc_attr( ( $rbfw_enable_daily_rate == 'yes' ) ? 'checked' : '' ); ?>>
                                <span class="slider round"></span>
                            </label>
                            <span class="rbfw_daily_rate_input ms-2">
							<input
                                type="number"
                                name="rbfw_daily_rate"
                                step="0.01"
                                value="<?php echo esc_attr( $rbfw_daily_rate ); ?>"
                                placeholder="<?php esc_attr_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"
                            <?php echo esc_attr( $rbfw_enable_daily_rate == 'no' ? 'disabled' : '' ); ?>>

						</span>
                        </div>
                    </section>
                    <section>
                        <div>
                            <label for=""><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'Pricing will be calculated as per hour.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="rbfw_enable_hourly_rate" value="<?php echo esc_attr( $rbfw_enable_hourly_rate ); ?>" <?php echo esc_attr( ( $rbfw_enable_hourly_rate == 'yes' ) ? 'checked' : '' ); ?>>
                                <span class="slider round"></span>
                            </label>
                            <span class="rbfw_hourly_rate ms-2">
							<input
                                type="number"
                                name="rbfw_hourly_rate"
                                step="0.01"
                                value="<?php echo esc_attr( $rbfw_hourly_rate ); ?>"
                                placeholder="<?php esc_attr_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"
                                <?php echo esc_attr( $rbfw_enable_hourly_rate == 'no' ? 'disabled' : '' ); ?>>
						</span>
                        </div>
                    </section>
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
					<?php do_action( 'rbfw_after_rent_item_type_table_row' ); ?>

					<?php do_action( 'rbfw_after_general_price_table_row' ); ?>

					<?php do_action( 'rbfw_after_general_price_table', $post_id ); ?>
                </div>
				<?php do_action( 'rbfw_after_week_price_table', $post_id ); ?>
				<?php do_action( 'rbfw_after_extra_service_table' ); ?>
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
					<?php $this->general_price_config( $post_id ); ?>
					<?php $this->resort_price_config( $post_id ); ?>
					<?php $this->category_service_price( $post_id ); ?>
                </div>
                <script>
                
                    // Handle extra service image upload
                    jQuery(document).ready(function () {
                        function rbfw_service_image_addup() {
                            // Onclick for extra service add image button
                            jQuery('.rbfw_service_image_btn').click(function () {
                                let target = jQuery(this).parents('tr');
                                let send_attachment_bkp = wp.media.editor.send.attachment;
                                wp.media.editor.send.attachment = function (props, attachment) {
                                    target.find('.rbfw_service_image_preview img').remove();
                                    // Escape URL before appending it to the DOM
                                    target.find('.rbfw_service_image_preview').append('<img src="' + esc_url(attachment.url) + '"/>');
                                    target.find('.rbfw_service_image').val(esc_attr(attachment.id)); // Escape the attachment ID
                                    wp.media.editor.send.attachment = send_attachment_bkp;
                                }
                                wp.media.editor.open(jQuery(this));
                                return false;
                            });
                            // Onclick for extra service remove image button
                            jQuery('.rbfw_remove_service_image_btn').click(function () {
                                let target = jQuery(this).parents('tr');
                                target.find('.rbfw_service_image_preview img').remove();
                                target.find('.rbfw_service_image').val('');
                            });
                        }
                        rbfw_service_image_addup();
                    });
                    
                    
                    
                    
                    // Resort
                    jQuery('#add-resort-type-row').click(function (e) {
                        e.preventDefault();
                        let current_time = jQuery.now();
                        if (jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row').length) {
                            let resort_last_row = jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row:last-child()');
                            let resort_type_last_data_key = parseInt(resort_last_row.attr('data-key'));
                            let resort_type_new_data_key = resort_type_last_data_key + 1;
                            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td>'
                                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                                + '<td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="Day-long Rate"></td>'
                                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="Day-night Rate"></td>'
                                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td>'
                                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td>'
                                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                                + '</tr>';
                            jQuery('.rbfw_resort_price_table').append(resort_type_row);
                        } else {
                            let resort_type_new_data_key = 0;
                            let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '">'
                                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td>'
                                + '<td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fas fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td>'
                                + '<td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" step=".01" value="" placeholder="Day-long Rate"></td>'
                                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" step=".01" value="" placeholder="Day-night Rate"></td>'
                                + '<td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td>'
                                + '<td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td>'
                                + '<td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fas fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td>'
                                + '</tr>';
                            jQuery('.rbfw_resort_price_table').append(resort_type_row);
                        }
                        jQuery('.remove-row.' + current_time + '').on('click', function () {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                                jQuery(this).parents('tr').remove();
                            } else {
                                return false;
                            }
                        });
                        jQuery(".rbfw_resort_price_table_body").sortable();
                        rbfw_room_type_image_addup();
                        var daylong_price_label_val = jQuery('.rbfw_resort_daylong_price_switch label.active').find('input').val();
                        if (daylong_price_label_val === 'yes') {
                            jQuery('.resort_day_long_price').show();
                        } else {
                            jQuery('.resort_day_long_price').hide();
                        }
                    });
                    // Image handling for room type
                    function rbfw_room_type_image_addup() {
                        jQuery('.rbfw_room_type_image_btn').click(function () {
                            let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
                            let send_attachment_bkp = wp.media.editor.send.attachment;
                            wp.media.editor.send.attachment = function (props, attachment) {
                                let image_url = esc_url(attachment.url); // Escape URL
                                jQuery('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview img').remove();
                                jQuery('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview').append('<img src="' + image_url + '"/>');
                                jQuery('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_image').val(attachment.id);
                                wp.media.editor.send.attachment = send_attachment_bkp;
                            }
                            wp.media.editor.open(jQuery(this));
                            return false;
                        });
                        jQuery('.rbfw_remove_room_type_image_btn').click(function () {
                            let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
                            jQuery('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_type_image_preview img').remove();
                            jQuery('.rbfw_resort_price_table_row[data-key=' + parent_data_key + '] .rbfw_room_image').val('');
                        });
                    }
                    rbfw_room_type_image_addup();
                    jQuery(".rbfw_resort_price_table_body").sortable();
                </script>
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
					$rbfw_enable_hourly_rate            = isset( $_POST['rbfw_enable_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_hourly_rate'] ) ) : 'no';
					$hourly_rate                        = isset( $_POST['rbfw_hourly_rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_hourly_rate'] ) ) : 0;
					$rbfw_enable_daywise_price          = isset( $_POST['rbfw_enable_daywise_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_daywise_price'] ) ) : 'no';
					$rbfw_enable_category_service_price = isset( $_POST['rbfw_enable_category_service_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_category_service_price'] ) ) : 'off';
					$rbfw_service_category_price        = isset( $input_data_sabitized['rbfw_service_category_price'] ) ? $input_data_sabitized['rbfw_service_category_price'] : [];
					$rbfw_bike_car_sd_data              = isset( $input_data_sabitized['rbfw_bike_car_sd_data'] ) ? $input_data_sabitized['rbfw_bike_car_sd_data'] : [];
					// echo '<pre>';print_r($rbfw_bike_car_sd_data );echo '<pre>';exit;
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
					update_post_meta( $post_id, 'rbfw_enable_category_service_price', $rbfw_enable_category_service_price );
					update_post_meta( $post_id, 'rbfw_service_category_price', $rbfw_service_category_price );
					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
					update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
					update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );
					update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
					update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );
					update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );
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
	
	