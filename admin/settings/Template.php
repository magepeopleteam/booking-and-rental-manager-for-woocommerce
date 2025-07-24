<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Template' ) ) {
		class RBFW_Template {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_template_settings_meta_boxes"><i class="fas fa-pager"></i><?php esc_html_e( 'Template', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure template Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
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

			public function sidebar_template_enable( $post_id ) {
				$template = get_post_meta( $post_id, 'rbfw_single_template', true ) ? get_post_meta( $post_id, 'rbfw_single_template', true ) : 'Default';
				?>
                <div class="donut-template-sidebar-switch <?php echo $template == 'Donut' ? 'show' : 'hide' ?>">
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php echo esc_html__( 'Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
						<?php $dt_sidebar_switch = get_post_meta( $post_id, 'rbfw_dt_sidebar_switch', true ); ?>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_dt_sidebar_switch" value="<?php echo esc_attr( ( $dt_sidebar_switch == 'on' ) ? $dt_sidebar_switch : 'off' ); ?>" <?php echo esc_attr( ( $dt_sidebar_switch == 'on' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                </div>
				<?php
			}

			public function select_template( $post_id ) {
                $template = get_post_meta( $post_id, 'rbfw_single_template', true ) ;
                $_current_template = $template?$template: 'Default';
				?>
                <section>
                    <input type="hidden" name="rbfw_single_template" id="rbfw_single_template" value="<?php echo esc_attr($_current_template); ?>" />
                    <?php $templates = RBFW_Function::get_all_template(); ?>
                    <?php foreach ( $templates as $key => $value ):  ?>
                        <?php 
                            $image = RBFW_Function::get_template_file_url('screenshot/').strtolower($key);
                        ?>
                        <div class="rbfw-single-template <?php echo $_current_template == $key?'active':''; ?>" data-rbfw-template="<?php echo $key; ?>">
                            <img src="<?php echo $image.'.webp'; ?>" >
                            <h5><?php echo $value; ?></h5>
                        </div>
                    <?php endforeach; ?>
                </section>
				<?php
			}

			public function sidebar_testimonial( $post_id ) {
				$template = get_post_meta( $post_id, 'rbfw_single_template', true ) ? get_post_meta( $post_id, 'rbfw_single_template', true ) : 'Default';
				?>
                <div class="sidebar-testimonial-settigns <?php echo $template == 'Donut' ? 'show' : 'hide' ?>">
					<?php $this->panel_header( 'Sidebar Testimonial settigns', 'Sidebar Testimonial settigns' ); ?>
                    <section>
                        <div class="w-100 text-center">
                            <div class="testimonials">
								<?php
									$sidebar_testimonials = get_post_meta( $post_id, 'rbfw_dt_sidebar_testimonials', true ) ? get_post_meta( $post_id, 'rbfw_dt_sidebar_testimonials', true ) : [];
									foreach ( $sidebar_testimonials as $key => $data ): ?>
                                        <div class="testimonial">
                                            <button onclick="jQuery(this).parent().remove()"><i class="fas fa-trash"></i></button>
                                            <textarea class="testimonial-field" name="rbfw_dt_sidebar_testimonials[<?php echo esc_attr( $key ); ?>]['rbfw_dt_sidebar_testimonial_text']" cols="30" rows="10"><?php echo esc_html( current( $data ) ); ?></textarea>
                                        </div>
									<?php endforeach; ?>
                                <div class="testimonial-clone">
                                    <button onclick="jQuery(this).parent().remove()"><i class="fas fa-trash"></i></button>
                                    <textarea class="testimonial-field" name="" cols="30" rows="10"></textarea>
                                </div>
                            </div>
                            <div class="ppof-button add-item" onclick="createTestimonial()"><i class="fas fa-plus-square"></i><?php esc_html_e( 'Add New Testimonial', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                        </div>
                    </section>
                </div>
				<?php
			}

			public function template_sidebar_content( $post_id ) {
				$template = get_post_meta( $post_id, 'rbfw_single_template', true ) ? get_post_meta( $post_id, 'rbfw_single_template', true ) : 'Default';
				?>
                <div class="donut-template-sidebar-content <?php echo $template == 'Donut' ? 'show' : 'hide' ?>">
					<?php $this->panel_header( 'Donut Template Sidebar Content', 'Donut Template Sidebar Content' ); ?>
                    <section>
                        <div class="w-100">
							<?php
								$sidebar_content = get_post_meta( $post_id, 'rbfw_dt_sidebar_content', true );
								$sidebar_content = $sidebar_content ? $sidebar_content : '';
								$settings        = array(
									'textarea_rows' => '10',
									'media_buttons' => true,
									'textarea_name' => 'rbfw_dt_sidebar_content',
								);
								wp_editor( $sidebar_content, 'rbfw_dt_sidebar_content', $settings );
							?>
                        </div>
                    </section>
                </div>
				<?php
			}

			public function additional_gallery( $post_id ) {
				$template           = get_post_meta( $post_id, 'rbfw_single_template', true ) ? get_post_meta( $post_id, 'rbfw_single_template', true ) : 'Default';
				$additional_gallary = get_post_meta( $post_id, 'rbfw_enable_additional_gallary', true );
				$additional_gallary = $additional_gallary ?: 'off';
				?>
                <div class="additional-gallery <?php echo $template == 'Muffin' ? 'show' : 'hide' ?>">
					<?php $this->panel_header( 'Additional Gallery', 'Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.' ); ?>
                    <section>
                        <div>
                            <label><?php esc_html_e( 'Enable Additional Gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'Enable/Disable Additional Gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_additional_gallary" value="<?php echo esc_attr( $additional_gallary ); ?>" <?php echo esc_attr( ( $additional_gallary == 'on' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <section class="additional-gallary-image <?php echo $additional_gallary == 'on' ? 'show' : 'hide' ?>">
                        <div id="field-wrapper-<?php echo esc_attr( $post_id ); ?>" class="<?php if ( ! empty( $depends ) ) {
							echo 'dependency-field';
						} ?> field-wrapper field-media-multi-wrapper field-media-multi-wrapper-<?php echo esc_attr( $post_id ); ?>">
                            <div class='button upload' id='rbfw_gallery_images_additional_<?php echo esc_attr( $post_id ); ?>'>
								<?php echo esc_html__( 'Upload', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </div>
                            <div class='button clear' id='media_clear_additional_<?php echo esc_attr( $post_id ); ?>'>
								<?php echo esc_html__( 'Clear', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </div>
                            <div class="gallery-images media-list-additional-<?php echo esc_attr( $post_id ); ?> ">
								<?php
									$gallery_images = get_post_meta( $post_id, 'rbfw_gallery_images_additional', true );
									$gallery_images = $gallery_images ?: [];
									if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ):
										foreach ( $gallery_images as $image ):
											$media_url = wp_get_attachment_url( $image );
											?>
                                            <div class=" gallery-image">
                                                <span class="remove" onclick="jQuery(this).parent().remove()"><i class="fas fa-trash-can"></i></span>
                                                <img id="media_preview_<?php echo esc_attr( $post_id ); ?>" src="<?php echo esc_url( $media_url ); ?>"/>
                                                <input type='hidden' name='rbfw_gallery_images_additional[]' value='<?php echo esc_attr( $image ); ?>'/>
                                            </div>
										<?php
										endforeach;
									endif;
								?>
                            </div>
                        </div>
                    </section>
                </div>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_template_settings_meta_boxes">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Template Settings', 'Template Settings' ); ?>
					<?php $this->select_template( $post_id ); ?>
					<?php $this->sidebar_template_enable( $post_id ); ?>
					<?php $this->sidebar_testimonial( $post_id ); ?>
					<?php $this->template_sidebar_content( $post_id ); ?>
					<?php $this->additional_gallery( $post_id ); ?>
                </div>
                <script>
                    jQuery('#rbfw_single_template').on('change', function () {
                        var template = jQuery(this).val();
                        if (template === 'Donut') {
                            jQuery('.donut-template-sidebar-switch').slideDown();
                            jQuery('.sidebar-testimonial-settigns').slideDown();
                            jQuery('.donut-template-sidebar-content').slideDown();
                        } else {
                            jQuery('.donut-template-sidebar-switch').slideUp();
                            jQuery('.sidebar-testimonial-settigns').slideUp();
                            jQuery('.donut-template-sidebar-content').slideUp();
                            jQuery('.additional-gallery').slideUp();
                        }
                        if (template === 'Muffin') {
                            jQuery('.additional-gallery').slideDown();
                        } else {
                            jQuery('.additional-gallery').slideUp();
                        }
                    });
                    
                    jQuery(document).ready(function ($) {
                        // Ensure the post ID is properly escaped for JavaScript context
                        var post_id = <?php echo esc_js( $post_id ); ?>;
                        $('#rbfw_gallery_images_additional_' + post_id).click(function () {
                            wp.media.editor.send.attachment = function (props, attachment) {
                                var attachment_id = parseInt(attachment.id, 10); // Ensure it's an integer
                                var attachment_url = attachment.url; // Escape the URL properly
                                
                                // Create the gallery image HTML with properly escaped attributes
                                var html = '<div class="gallery-image">';
                                html += '<span class="remove" onclick="jQuery(this).parent().remove()"><i class="fas fa-trash-can"></i></span>';
                                html += '<img src="' + attachment_url + '" style="width:100%"/>';
                                html += '<input type="hidden" name="rbfw_gallery_images_additional[]" value="' + attachment_id + '" />';
                                html += '</div>';
                                // Append the new HTML with sanitized values
                                $('.media-list-additional-' + post_id).append(html);
                                console.log(html);
                            };
                            wp.media.editor.open($(this));
                            return false;
                        });
                        // Clear gallery images
                        $('#media_clear_additional_' + post_id).click(function () {
                            $('.media-list-additional-' + post_id + ' .gallery-image').remove();
                        });
                    });
                </script>
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
					$rbfw_single_template_original = isset( $_POST['rbfw_single_template'] ) ? sanitize_file_name( wp_unslash( $_POST['rbfw_single_template'] ) ) : 'Default';
					$dt_sidebar_switch             = isset( $_POST['rbfw_dt_sidebar_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_dt_sidebar_switch'] ) ) : '';
					$testimonials                  = isset( $_POST['rbfw_dt_sidebar_testimonials'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_dt_sidebar_testimonials'] ) : [];
					$sidebar_content               = isset( $_POST['rbfw_dt_sidebar_content'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_dt_sidebar_content'] ) : [];
					$gallery_images                = isset( $_POST['rbfw_gallery_images_additional'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_gallery_images_additional'] ) : [];
					$enable_additional_gallary = isset( $_POST['rbfw_enable_additional_gallary'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_additional_gallary'] ) ) : 'off';
					update_post_meta( $post_id, 'rbfw_dt_sidebar_switch', $dt_sidebar_switch );
					update_post_meta( $post_id, 'rbfw_single_template', $rbfw_single_template_original );
					update_post_meta( $post_id, 'rbfw_dt_sidebar_testimonials', $testimonials );
					update_post_meta( $post_id, 'rbfw_dt_sidebar_content', $sidebar_content );
					update_post_meta( $post_id, 'rbfw_enable_additional_gallary', $enable_additional_gallary );
					update_post_meta( $post_id, 'rbfw_gallery_images_additional', $gallery_images );
				}
			}
		}
		new RBFW_Template();
	}