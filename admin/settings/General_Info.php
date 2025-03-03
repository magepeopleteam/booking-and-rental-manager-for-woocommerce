<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_General_Info' ) ) {
		class RBFW_General_Info {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_gen_info"><i class="fas fa-tools"></i><?php esc_html_e( 'General Info', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'General Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure basic information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
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

			public function select_category( $post_id ) {
				$rbfw_categories = get_post_meta( $post_id, 'rbfw_categories', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_categories', true ) ) : [];
				global $rbfw;
				$label = $rbfw->get_name();
				?>
                <section>
                    <div>
                        <label>
							<?php
								echo esc_html__( 'Select ', 'booking-and-rental-manager-for-woocommerce' ) .
								     esc_html( $label ) .
								     esc_html__( ' Type', 'booking-and-rental-manager-for-woocommerce' );
							?>
                        </label>
                        <p><?php esc_html_e( 'Choose a type that is related with this item', 'booking-and-rental-manager-for-woocommerce' ) ?></p>
                    </div>
                    <div class="w-50">
                        <select name="rbfw_categories[]" multiple class="category2">
							<?php
								$terms = get_terms( array(
									'taxonomy'   => 'rbfw_item_caregory',
									'hide_empty' => false,
								) );
								foreach ( $terms as $key => $value ) {
									?>
                                    <option <?php echo esc_attr( in_array( $value->name, $rbfw_categories ) ) ? 'selected' : '' ?> value="<?php echo esc_attr( $value->name ) ?>"> <?php echo esc_html( $value->name ) ?> </option>
									<?php
								}
							?>
                        </select>
                    </div>
                </section>
				<?php
			}

			public function field_feature_category( $option ) {
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
				$values = isset( $option['value'] ) ? $option['value'] : array();
				$values = ! empty( $values ) ? $values : $default;
				$limit = ! empty( $option['limit'] ) ? $option['limit'] : '';
				$field_id   = $id;
				$field_name = ! empty( $field_name ) ? $field_name : $id;
				ob_start();
				?>
                <div id="field-wrapper-<?php echo esc_attr( $id ); ?>" class="field-wrapper field-text-multi-wrapper field-text-multi-wrapper-<?php echo esc_attr( $field_id ); ?>">
                    <section>
                        <div class="w-100">
                            <table class="form-table rbfw_feature_category_table">
                                <tbody class="sortable_tr">
								<?php
									if ( ! empty( $values ) ):
										$i = 0;
										foreach ( $values as $value ):?>
                                            <tr data-cat="<?php echo esc_attr( $i ); ?>">
                                                <td>
                                                    <div class="features_category_wrapper text-center">
                                                        <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                                            <div class="feature_category_inner_wrap">
                                                                <div class="feature_category_title">
                                                                    <label class=" mb-1">
																		<?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                                    </label>
                                                                    <input type="text" value="<?php echo esc_attr( $value['cat_title'] ); ?>" name="rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_title]" data-key="<?php echo esc_attr( $i ); ?>" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                                </div>
                                                                <div class="feature_category_inner_item_wrap sortable">
																	<?php
																		if ( ! empty( $value['cat_features'] ) ) {
																			$c = 0;
																			foreach ( $value['cat_features'] as $feature ) {
																				$icon  = $feature['icon'];
																				$title = $feature['title'];
																				?>
                                                                                <div class="item">
                                                                                    <a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="<?php echo esc_attr( $c ); ?>"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                                                                                    <div class="rbfw_feature_icon_preview" data-key="<?php echo esc_attr( $c ); ?>"><?php if ( $icon ) {
																							echo '<i class="' . wp_kses_post( $icon ) . '"></i>';
																						} ?></div>
                                                                                    <input type='hidden' name='rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='<?php echo esc_attr( $icon ); ?>' data-key="<?php echo esc_attr( $c ); ?>" class="rbfw_feature_icon"/>
                                                                                    <input type='text' name='rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value="<?php echo esc_attr( $title ); ?>" data-key="<?php echo esc_attr( $c ); ?>"/>
                                                                                    <div>
																						<?php if ( $sortable ): ?>
                                                                                            <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																						<?php endif; ?>
                                                                                        <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
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
                                                        <span class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                    </div>
                                                </td>
                                                <td>
													<?php if ( $sortable ): ?>
                                                        <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
													<?php endif; ?>
                                                    <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                                </td>
                                            </tr>
											<?php
											$i ++;
										endforeach;
									else:
										?>
                                        <tr data-cat="0">
                                            <td>
                                                <div class="features_category_wrapper text-center">
                                                    <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                                        <div class="feature_category_inner_wrap">
                                                            <div class="feature_category_title"><label><?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label><input type="text" name="rbfw_feature_category[0][cat_title]" data-key="0" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></div>
                                                            <div class="feature_category_inner_item_wrap sortable">
                                                                <div class="item">
                                                                    <a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                                                                    <div class="rbfw_feature_icon_preview p-1" data-key="0"></div>
                                                                    <input type='hidden' name='rbfw_feature_category[0][cat_features][0][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' data-key="0" class="rbfw_feature_icon"/>
                                                                    <input type='text' name='rbfw_feature_category[0][cat_features][0][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value='' data-key="0"/>
                                                                    <div>
																		<?php if ( $sortable ): ?>
                                                                            <span class="button sort"><i class="fas fa-arrows-alt"></i></span><?php endif; ?><span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                            </td>
                                            <td>
												<?php if ( $sortable ): ?>
                                                    <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
												<?php endif; ?>
                                                <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                            </td>
                                        </tr>
									<?php
									endif;
								?>
                                </tbody>
                            </table>
                            <span class="ppof-button add-feature-category mt-1 w-30"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature Category', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                    </section>
                </div>
				<?php
				return ob_get_clean();
			}

			public function features_category( $post_id ) {
				?>
				<?php $this->panel_header( 'Item Features Settings', 'Here you can add all features as category if needed.' ); ?>
				<?php
				$options          = array(
					'id'          => 'rbfw_feature_category',
					'type'        => 'feature_category',
					'placeholder' => 'Features Name',
				);
				$option_value     = get_post_meta( $post_id, $options['id'], true );
				$options['value'] = is_serialized( $option_value ) ? unserialize( $option_value ) : $option_value;
				$id = isset( $option['id'] ) ? $option['id'] : "";
				$field_name  = isset( $options['field_name'] ) ? $options['field_name'] : $id;
				$conditions  = isset( $options['conditions'] ) ? $options['conditions'] : array();
				$placeholder = isset( $options['placeholder'] ) ? $options['placeholder'] : "";
				$remove_text = isset( $options['remove_text'] ) ? $options['remove_text'] : '<i class="fas fa-trash-can"></i>';
				$sortable    = isset( $options['sortable'] ) ? $options['sortable'] : true;
				$default     = isset( $options['default'] ) ? $options['default'] : array();
				$values = isset( $option['value'] ) ? $option['value'] : array();
				$values = ! empty( $values ) ? $values : $default;
				$limit = ! empty( $option['limit'] ) ? $option['limit'] : '';
				$field_id   = $id;
				$field_name = ! empty( $field_name ) ? $field_name : $id;
				?>
                <script type="text/javascript">
                    jQuery(".sortable_tr").sortable({handle: '.tr_sort_handler'});
                    jQuery('.tr_remove').click(function (e) {
                        jQuery(this).closest("tr").remove();
                    });
                    jQuery(document).on('click', '.add-feature-category', function (e) {
                        e.stopImmediatePropagation();
                        let dataCat = jQuery('.rbfw_feature_category_table tbody tr:last-child').attr('data-cat');
                        let nextCat = parseInt(dataCat) + 1;
                        let html = '<tr data-cat="' + nextCat + '"><td><div class="features_category_wrapper text-center"><div class="field-list rbfw_feature_category"><div class="feature_category_inner_wrap"><div class="feature_category_title"><label><?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label><input type="text" class="rbfw_feature_category_title" name="rbfw_feature_category[' + nextCat + '][cat_title]" data-cat="' + nextCat + '" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"></div><div class="feature_category_inner_item_wrap sortable"><div class="item"><a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a><div class="rbfw_feature_icon_preview" data-key="0"></div><input type="hidden" name="rbfw_feature_category[' + nextCat + '][cat_features][0][icon]" placeholder="<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="0" class="rbfw_feature_icon"> <input type="text" name="rbfw_feature_category[' + nextCat + '][cat_features][0][title]" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="" data-key="0"><div><?php if($sortable):?> <span class="button sort"><i class="fas fa-arrows-alt"></i></span><?php endif; ?><span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span></div></div></div></div></div><span class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></td><td> <?php if($sortable):?> <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button tr_remove"><?php echo wp_kses_post( $remove_text ); ?></span></td></tr>';
                        jQuery('.rbfw_feature_category_table tbody').append(html);
                        jQuery(".sortable_tr").sortable({handle: '.tr_sort_handler'});
                        jQuery('.tr_remove').click(function (e) {
                            jQuery(this).closest("tr").remove();
                        });
                    });
                    jQuery(document).on('click', '.add-new-feature', function (e) {
                        e.stopImmediatePropagation();
                        let data_key = jQuery(this).siblings(".rbfw_feature_category").find("div.item:last-child input").attr('data-key');
                        let i = parseInt(data_key) || 0; // Ensuring it is a number
                        let c = i + 1;
                        let theTarget = jQuery(this).siblings('.rbfw_feature_category').find('.feature_category_inner_wrap .feature_category_inner_item_wrap');
                        jQuery(".sortable").sortable({handle: '.sort'});
                        let dataCat = jQuery(this).closest('tr').attr('data-cat');
                        let html = '<div class="item">';
                        html += '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="' + c + '">';
                        html += '<i class="fas fa-circle-plus"></i> ' + <?php echo json_encode( esc_html__( 'Add Icon', 'booking-and-rental-manager-for-woocommerce' ) ); ?>;
                        html += '</a>';
                        html += '<div class="rbfw_feature_icon_preview" data-key="' + c + '"></div>';
                        html += '<input type="hidden" name="rbfw_feature_category[' + dataCat + '][cat_features][' + c + '][icon]" placeholder="';
                        html += <?php echo json_encode( esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ) ); ?> +'" data-key="' + c + '" class="rbfw_feature_icon"/>';
                        html += '<input type="text" name="rbfw_feature_category[' + dataCat + '][cat_features][' + c + '][title]" placeholder="';
                        html += <?php echo json_encode( esc_attr( $placeholder ) ); ?> +'" data-key="' + c + '"/>';
                        html += '<div>';

						<?php if ($sortable): ?>
                        html += ' <span class="button sort"><i class="fas fa-arrows-alt"></i></span>';
						<?php endif; ?>

                        html += '<span class="button remove" onclick="jQuery(this).parent().parent().remove()">';
                        html += <?php echo json_encode( wp_kses_post( $remove_text ) ); ?>;
                        html += '</span>';
                        html += '</div>';
                        html += '</div>';
                        theTarget.append(html);
                    });
                    // Features Icon Popup
                    jQuery(document).on('click', '.rbfw_feature_icon_btn', function (e) {
                        e.stopImmediatePropagation();
                        let remove_exist_data_key = jQuery("#rbfw_features_icon_list_wrapper").removeAttr('data-key');
                        let remove_active_label = jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                        let data_key = jQuery(this).attr('data-key');
                        let data_cat = jQuery(this).parents('tr').attr('data-cat');
                        jQuery('#rbfw_features_search_icon').val('');
                        jQuery('.rbfw_features_icon_list_body label').show();
                        jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', data_key);
                        jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', data_cat);
                        jQuery("#rbfw_features_icon_list_wrapper").mage_modal({
                            escapeClose: false,
                            clickClose: false,
                            showClose: false
                        });
                        // Selected Feature Icon Action
                        jQuery(document).on('click', '.rbfw_features_icon_list_wrapper_modal label', function (e) {
                            alert(123);
                            e.stopImmediatePropagation();
                            let selected_label = jQuery(this);
                            let selected_val = jQuery('input', this).val();
                            let selected_data_key = jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
                            let selected_data_cat = jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');
                            alert(selected_data_key);
                            jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon_preview[data-key="' + selected_data_key + '"]').empty();
                            jQuery(selected_label).addClass('selected');
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon[data-key="' + selected_data_key + '"]').val(selected_val);
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon_preview[data-key="' + selected_data_key + '"]').append('<i class="' + selected_val + '"></i>');
                        });
                        // Icon Filter
                        jQuery('#rbfw_features_search_icon').keyup(function (e) {
                            let value = jQuery(this).val().toLowerCase();
                            jQuery(".rbfw_features_icon_list_body label[data-id]").show().filter(function () {
                                jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
                            }).hide();
                        });
                        // End Icon Filter
                    });
                    // End Features Icon Popup
                </script>
				<?php
				echo wp_kses( $this->field_feature_category( $options ), rbfw_allowed_html() );
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_gen_info">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Category Settings', 'Here you can assign categories ot each items' ); ?>
					<?php $this->select_category( $post_id ); ?>
					<?php $this->features_category( $post_id ); ?>
                </div>
			<?php }

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
					$rbfw_categories = isset( $_POST['rbfw_categories'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_categories'] ) : [];
					wp_set_object_terms( $post_id, $rbfw_categories, 'rbfw_item_caregory' );
					$feature_category = isset( $_POST['rbfw_feature_category'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_feature_category'] ) : [];
					update_post_meta( $post_id, 'rbfw_categories', $rbfw_categories );
					update_post_meta( $post_id, 'rbfw_feature_category', $feature_category );
				}
			}
		}
		new RBFW_General_Info();
	}

       


