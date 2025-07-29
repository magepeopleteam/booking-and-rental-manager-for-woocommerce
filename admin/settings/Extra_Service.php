<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Extra_Service' ) ) {
		class RBFW_Extra_Service {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#extra_service"><i class="far fa-star"></i><?php esc_html_e( 'Extra Service', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Additional Extra Services ', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure additional extra service with rent item.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
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

			public function add_tabs_content( $post_id ) {
				$rbfw_item_type                = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_extra_service_data       = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#extra_service">
					<?php $this->section_header(); ?>
					<?php $this->extra_service_table( $post_id ); ?>
					<?php $this->category_service_price( $post_id ); ?>
                </div>
                
				<?php
			}

			public function category_service_price( $post_id ) {
				$enable_service_price = get_post_meta( $post_id, 'rbfw_enable_category_service_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_category_service_price', true ) : 'off';
                ?>
                <div class="rbfw_general_price_config_wrapper">
					<?php $this->panel_header( 'Additional Service item price settings ', 'Additional Service item and price settings' ); ?>
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Enable Additional service.', 'booking-and-rental-manager-for-woocommerce' ); ?>
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
                                html += '<input type="hidden" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][icon]" placeholder="<?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="' + c + '" class="rbfw_service_icon"/>';
                                html += '<input type="text" name="rbfw_service_category_price[' + dataCat + '][cat_services][' + c + '][title]" placeholder="<?php echo esc_html__('Service Name', 'booking-and-rental-manager-for-woocommerce'); ?>" data-key="' + c + '"/>';
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
                                jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', data_key);
                                jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', data_cat);
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

			public function field_service_price( $option ) {
				$rbfw_item_type = get_post_meta( get_the_ID() , 'rbfw_item_type', true );
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
				<div class="rbfw-service-category-table">
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
																<?php echo esc_html__( 'Additional Service Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
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
															<input type='text' class="medium" name='rbfw_service_category_price[0][cat_services][0][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value='' data-key="0"/>
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
				</div>
                <span class="ppof-button add-service-category mt-1">
					<i class="fas fa-circle-plus"></i>
					<?php echo esc_html__( 'Add New Service Category', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</span>
				<?php
				return ob_get_clean();
			}

			public function extra_service_table( $post_id ) {
				$rbfw_item_type                = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_extra_service_data       = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
				
				?>
				<div class="rbfw_es_price_config_wrapper" <?php if ( $rbfw_item_type == 'appointment' ) { echo 'style="display:none"'; } ?>">
					<?php $this->show_extra_service( $post_id ) ?>
				</div>
				<?php
				
			}
			public function show_extra_service( $post_id ){
				$rbfw_item_type                = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_extra_service_data       = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
				
				?>
				<section class="bg-light mt-5">
							<div>
								<label><?php echo esc_html__('Extra Service Price Settings','booking-and-rental-manager-for-woocommerce'); ?></label>
								<p><?php echo esc_html__( 'Here you can set extra service price.','booking-and-rental-manager-for-woocommerce'); ?></p>
							</div>
						</section>
						<section>
							<div class="w-100">
								<div style="overflow-x: auto;">
									<table class='rbfw_pricing_table form-table w-100' id="repeatable-fieldset-one">
										<thead>
										<tr>
											<th><?php // esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th><?php echo wp_kses_post( 'Price <b class="required">*</b>' ); ?></th>
											<th><?php echo wp_kses_post( 'Stock Quantity <b class="required">*</b>' ); ?></th>
											<!--<th><?php esc_html_e( 'Qty Box', 'booking-and-rental-manager-for-woocommerce' ); ?></th>-->
											<th></th>
										</tr>
										</thead>
										<tbody class="mp_event_type_sortable">
										<?php
									
											if ( ! empty( $rbfw_extra_service_data ) ) :
												foreach ( $rbfw_extra_service_data as $field ) {
													if ( ! empty( $field['service_img'] ) ) {
														$service_img = ! empty( $field['service_img'] ) ? esc_attr( $field['service_img'] ) : '';
														$img_url     = wp_get_attachment_url( $service_img );
													} else {
														$service_img = '';
														$img_url     = '';
													}
													$service_name  = array_key_exists( 'service_name', $field ) ? esc_attr( $field['service_name'] ) : '';
													$service_price = array_key_exists( 'service_price', $field ) ? esc_attr( $field['service_price'] ) : '';
													$service_desc  = array_key_exists( 'service_desc', $field ) ? esc_attr( $field['service_desc'] ) : '';
													$service_qty   = array_key_exists( 'service_qty', $field ) ? esc_attr( $field['service_qty'] ) : '';
													?>
													<tr>
														<td>
															<div class="rbfw_service_image_wrap text-center">
																<div class="rbfw_service_image_preview">
																	<?php  if ( $img_url ): ?>
																		<img src="<?php echo esc_url( $img_url ); ?>">
																	<?php  endif; ?>
																</div>
																<div class="service_image_add_remove">
																	<a class="rbfw_service_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn btn"><i class="fas fa-circle-minus"></i></a>
																	<input type="hidden" name="service_img[]" value="<?php echo esc_attr( $service_img ); ?>" class="rbfw_service_image"/>
																</div>
															</div>
														</td> 
														<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_attr( $service_name ); ?>"/></td>
														<td><input type="text" class="mp_formControl" name="service_desc[]" placeholder="Service Description" value="<?php echo esc_attr( $service_desc ); ?>"/></td>
														<td><input type="number" class="medium" step="0.01" class="mp_formControl" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_attr( $service_price ); ?>"/></td>
														<td><input type="number" class="medium" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_attr( $service_qty ); ?>"/></td>
														<td>
															<div class="mp_event_remove_move">
																<button class="button remove-row" type="button"><i class="fas fa-trash-can"></i></button>
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
											<td>
												<div class="rbfw_service_image_wrap text-center">
													<div class="rbfw_service_image_preview"></div>
													<div class="service_image_add_remove">
														<a class="rbfw_service_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn button"><i class="fas fa-circle-minus"></i></a>
														<input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
													</div>
												</div>
											</td>
											<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap"/></td>
											<td><input type="text" class="mp_formControl " name="service_desc[]" placeholder="Service Description" value=""/></td>
											<td><input type="number" class="mp_formControl medium" step="0.01" name="service_price[]" placeholder="Ex: 10" value=""/></td>
											<td><input type="number" class="medium" name="service_qty[]" placeholder="Ex: 100" value=""/></td>
											<td>
												<div class="mp_event_remove_move">
													<button class="button remove-row"><i class="fas fa-trash-can"></i></button>
													<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
												</div>
											</td>
										</tr>
										</tbody>
									</table>
								</div>
								<p class="mt-2">
									<button id="add-row" class="ppof-button"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Extra Service', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
								</p>
							</div>
						</section>
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
					$input_data_sabitized          = RBFW_Function::data_sanitize( $_POST );
					$rbfw_enable_category_service_price = isset( $_POST['rbfw_enable_category_service_price'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_category_service_price'] ) ) : 'off';
					$rbfw_service_category_price        = isset( $input_data_sabitized['rbfw_service_category_price'] ) ? $input_data_sabitized['rbfw_service_category_price'] : [];
					// save extra service data==========================================
					$old_extra_service = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
					$new_extra_service = array();
				
					$service_img             = isset( $input_data_sabitized['service_img'] ) ? $input_data_sabitized['service_img'] : array();
					
					$names             = isset( $input_data_sabitized['service_name'] ) ? $input_data_sabitized['service_name'] : array();
					$urls              = isset( $input_data_sabitized['service_price'] ) ? $input_data_sabitized['service_price'] : array();
					$service_desc      = isset( $input_data_sabitized['service_desc'] ) ? $input_data_sabitized['service_desc'] : array();
					$qty               = isset( $input_data_sabitized['service_qty'] ) ? $input_data_sabitized['service_qty'] : array();
					$qty_type          = ! empty( $input_data_sabitized['service_qty_type'] ) ? $input_data_sabitized['service_qty_type'] : array();
					$count = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( ! empty( $service_img[ $i ] ) ) :
							$new_extra_service[ $i ]['service_img'] = stripslashes( wp_strip_all_tags( $service_img[ $i ] ) );
						endif;
						if ( $names[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_name'] = stripslashes( wp_strip_all_tags( $names[ $i ] ) );
						endif;
						if ( $urls[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_price'] = stripslashes( wp_strip_all_tags( $urls[ $i ] ) );
						endif;
						if ( $service_desc[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_desc'] = stripslashes( wp_strip_all_tags( $service_desc[ $i ] ) );
						endif;
						if ( $qty[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_qty'] = stripslashes( wp_strip_all_tags( $qty[ $i ] ) );
						endif;
						if ( ! empty( $qty_type[ $i ] ) && $qty_type[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_qty_type'] = stripslashes( wp_strip_all_tags( $qty_type[ $i ] ) );
						endif;
					}
					$extra_service_data_arr = apply_filters( 'rbfw_extra_service_arr_save', $new_extra_service );
					if ( ! empty( $extra_service_data_arr ) && $extra_service_data_arr != $old_extra_service ) {
						update_post_meta( $post_id, 'rbfw_extra_service_data', $extra_service_data_arr );
					} elseif ( empty( $extra_service_data_arr ) && $old_extra_service ) {
						delete_post_meta( $post_id, 'rbfw_extra_service_data', $old_extra_service );
					}
					// =====extra service cateogry=============
					update_post_meta( $post_id, 'rbfw_service_category_price', $rbfw_service_category_price );
					update_post_meta( $post_id, 'rbfw_enable_category_service_price', $rbfw_enable_category_service_price );
				}
			}
		}
		new RBFW_Extra_Service();
	}
	
	