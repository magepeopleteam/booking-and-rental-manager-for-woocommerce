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
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Extra Services ', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
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

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#extra_service">
					<?php $this->section_header(); ?>
					<?php $this->extra_service_table( $post_id ); ?>
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
                </script>
				<?php
			}

			public function extra_service_table( $post_id ) {
				$rbfw_item_type                = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				$rbfw_extra_service_data       = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
				$rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
				?>
                <div class="rbfw_es_price_config_wrapper " <?php if ( $rbfw_item_type == 'appointment' ) {
					echo 'style="display:none"';
				} ?>>
					<?php $this->panel_header( 'Extra Service Price Settings', 'Extra Service Price Settings' ); ?>
                    <section>
                        <div class="w-100">
                            <div style="overflow-x: auto;">
                                <table class='rbfw_pricing_table form-table w-100' id="repeatable-fieldset-one">
                                    <thead>
                                    <tr>
                                        <!-- <th><?php // esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th> -->
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
                                                    <!-- <td>
                                                        <div class="rbfw_service_image_wrap text-center">
                                                            <div class="rbfw_service_image_preview">
																<?php // if ( $img_url ): ?>
                                                                    <img src="<?php //echo esc_url( $img_url ); ?>">
																<?php // endif; ?>
                                                            </div>
                                                            <div class="service_image_add_remove">
                                                                <a class="rbfw_service_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn btn"><i class="fas fa-circle-minus"></i></a>
                                                                <input type="hidden" name="service_img[]" value="<?php echo esc_attr( $service_img ); ?>" class="rbfw_service_image"/>
                                                            </div>
                                                        </div>
                                                    </td> -->
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
                                        <!-- <td>
                                            <div class="rbfw_service_image_wrap text-center">
                                                <div class="rbfw_service_image_preview"></div>
                                                <div class="service_image_add_remove">
                                                    <a class="rbfw_service_image_btn button"><i class="fas fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn button"><i class="fas fa-circle-minus"></i></a>
                                                    <input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
                                                </div>
                                            </div>
                                        </td> -->
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
                    <div class="wervice_quantity_input_box">
                        <section>
                            <div>
                                <label><?php esc_html_e( 'Enable Service Quantity Box', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                <p><?php esc_html_e( 'If you Enable this customer can select number of quantity in front-end.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="rbfw_enable_extra_service_qty" value="<?php echo esc_attr( $rbfw_enable_extra_service_qty ); ?>" <?php echo esc_attr( ( $rbfw_enable_extra_service_qty == 'yes' ) ? 'checked' : '' ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </section>
                    </div>
                </div>
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
					
					// save extra service data==========================================
					$old_extra_service = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
					$new_extra_service = array();
					$service_img       = ! empty( $_POST['service_img'] ) ? sanitize_text_field( wp_unslash( $_POST['service_img'] ) ) : [];
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
				}
			}
		}
		new RBFW_Extra_Service();
	}
	
	