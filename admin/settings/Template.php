<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Template')) {
        class RBFW_Template{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_template_settings_meta_boxes"><i class="fa-solid fa-pager"></i><?php esc_html_e('Template', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
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


			public function sidebar_template_enable( $post_id ) {
                $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; 
            ?>
                <div class="donut-template-sidebar-switch <?php echo $template=='Donut'?'show':'hide' ?>">
                    <section>
                        <div>
                            <label>
                                <?php echo esc_html__( 'Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <span><?php echo esc_html__('Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <?php $dt_sidebar_switch = get_post_meta($post_id,'rbfw_dt_sidebar_switch',true);?>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_dt_sidebar_switch" value="<?php echo esc_attr(($dt_sidebar_switch=='on')?$dt_sidebar_switch:'off'); ?>" <?php echo esc_attr(($dt_sidebar_switch=='on')?'checked':''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                </div>
                <?php
            }
			public function select_template( $post_id ) {
                ?>
                <section>
						<label for=""><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<?php  $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; ?>
						<?php $the_template = RBFW_Function::get_all_template(); ?>
						<select name="rbfw_single_template" id="rbfw_single_template">
							<?php foreach($the_template as $kay => $value): ?>
								<option <?php echo ($kay==$template)?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
							<?php endforeach; ?>
						</select>
					</section>
                <?php
            }
			public function sidebar_testimonial( $post_id ) {
                $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; 
                ?>
                <div class="sidebar-testimonial-settigns <?php echo $template=='Donut'?'show':'hide' ?>">
                    <?php $this->panel_header('Sidebar Testimonial settigns','Sidebar Testimonial settigns'); ?>
                    <section>
                        <div class="w-100 text-center">
                            <div class="testimonials">
                                <?php 
                                    $sidebar_testimonials = get_post_meta($post_id,'rbfw_dt_sidebar_testimonials',true)? get_post_meta($post_id,'rbfw_dt_sidebar_testimonials',true) : [];
                                    foreach($sidebar_testimonials as $key => $data): ?>
                                    <div class="testimonial">
                                        <button onclick="jQuery(this).parent().remove()"> <i class="fas fa-trash"></i></button>
                                        <textarea class="testimonial-field" name="rbfw_dt_sidebar_testimonials[<?php echo  $key; ?>]['rbfw_dt_sidebar_testimonial_text']" cols="30" rows="10"><?php echo esc_html(current($data)); ?></textarea>
                                    </div>
                                <?php endforeach; ?>

                                <div class="testimonial-clone">
                                    <button onclick="jQuery(this).parent().remove()"> <i class="fas fa-trash"></i> </button>
                                    <textarea class="testimonial-field" name=""  cols="30" rows="10"></textarea>
                                </div>
                            </div>
                        
                            <div class="ppof-button add-item" onclick="createTestimonial()"><i class="fas fa-plus-square"></i><?php _e('Add New Testimonial','booking-and-rental-manager-for-woocommerce'); ?></div>
                        </div>
                    </section>
                </div>
                <?php
            }
			
			public function template_sidebar_content( $post_id ) {
                $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; 

                ?>
                <div class="donut-template-sidebar-content <?php echo $template=='Donut'?'show':'hide' ?>">
                    <?php $this->panel_header('Donut Template Sidebar Content','Donut Template Sidebar Content'); ?>
                    <section>
                        <div class="w-100">
                            <?php 
                                $sidebar_content = get_post_meta($post_id,'rbfw_dt_sidebar_content',true);
                                $sidebar_content = $sidebar_content ? $sidebar_content : '';
                                $settings = array(
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
                $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; 
                ?>
                <div class="additional-gallery <?php echo $template=='Muffin'?'show':'hide' ?>">
                    <?php $this->panel_header('Additional Gallery','Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.'); ?>
                    <section>
                        <div  id="field-wrapper-<?php echo esc_attr($post_id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper field-media-multi-wrapper-<?php echo esc_attr($post_id); ?>">
                            <div class='button upload' id='rbfw_gallery_images_additional_<?php echo esc_attr($post_id); ?>'>
                                <?php echo __('Upload','pickplugins-options-framework');?>
                            </div>
                            <div class='button clear' id='media_clear_additional_<?php echo $post_id; ?>'>
                                <?php echo __('Clear','pickplugins-options-framework');?>
                            </div>
                            <div class="gallery-images media-list-additional-<?php echo esc_attr($post_id); ?> ">
                                <?php
                                $gallery_images = get_post_meta($post_id,'rbfw_gallery_images_additional',true);
                                $gallery_images = $gallery_images ? $gallery_images : [];
                                
                                if(!empty($gallery_images) && is_array($gallery_images)):
                                    foreach ($gallery_images as $image ):
                                        $media_url	= wp_get_attachment_url( $image );
                                        $media_type	= get_post_mime_type( $image );
                                        $media_title= get_the_title( $image );
                                        ?>
                                        <div class=" gallery-image">
                                            <span class="remove" onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>
                                            
                                            <img id='media_preview_<?php echo esc_attr($post_id); ?>' src='<?php echo esc_attr($media_url); ?>' />
                                            <input type='hidden' name='rbfw_gallery_images_additional[]' value='<?php echo esc_attr($image); ?>' />
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
                    <?php $this->panel_header('Template Settings','Template Settings'); ?>
                    <?php $this->select_template( $post_id ); ?>
                    <?php $this->sidebar_template_enable( $post_id ); ?>
                    <?php $this->sidebar_testimonial( $post_id ); ?>
                    <?php $this->template_sidebar_content( $post_id ); ?>
                    <?php $this->additional_gallery( $post_id ); ?>
				</div>

                <script>
                    jQuery('input[name=rbfw_dt_sidebar_switch]').click(function(){
                        var status = jQuery(this).val();
                        if(status == 'on') {
                            jQuery(this).val('off') 
                        }  
                        if(status == 'off') {
                            jQuery(this).val('on');  
                        }
                    });

                    jQuery('#rbfw_single_template').on('change',function(){
                        var template = jQuery(this).val();
                        if(template == 'Donut') {
                            jQuery('.donut-template-sidebar-switch').slideDown(); 
                            jQuery('.sidebar-testimonial-settigns').slideDown(); 
                            jQuery('.donut-template-sidebar-content').slideDown(); 
                        }
                        else{
                            jQuery('.donut-template-sidebar-switch').slideUp();
                            jQuery('.sidebar-testimonial-settigns').slideUp();
                            jQuery('.donut-template-sidebar-content').slideUp();
                            jQuery('.additional-gallery').slideUp();
                        }
                        if(template == 'Muffin') {
                            jQuery('.additional-gallery').slideDown();
                        }else{
                            jQuery('.additional-gallery').slideUp();
                        }
                    });

                    function createTestimonial(){
                        now = jQuery.now();
                        jQuery(".testimonial-clone").clone().appendTo(".testimonials")
                        .removeClass('testimonial-clone').addClass('testimonial')
                        .children('.testimonial-field').attr('name','rbfw_dt_sidebar_testimonials['+now+'][rbfw_dt_sidebar_testimonial_text]');
                    };

                    jQuery(document).ready(function($){
							$('#rbfw_gallery_images_additional_<?php echo esc_attr($post_id); ?>').click(function() {
								//var send_attachment_bkp = wp.media.editor.send.attachment;
								wp.media.editor.send.attachment = function(props, attachment) {
									attachment_id = attachment.id;
									attachment_url = attachment.url;
									html = '<div class=" gallery-image">';
									html += '<span class="remove" onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>';
									html += '<img src="'+attachment_url+'" style="width:100%"/>';
									html += '<input type="hidden" name="rbfw_gallery_images_additional[]" value="'+attachment_id+'" />';
									html += '</div>';
									$('.media-list-additional-<?php echo esc_attr($post_id); ?>').append(html);
									//wp.media.editor.send.attachment = send_attachment_bkp;
								}
								wp.media.editor.open($(this));
								return false;
							});
							$('#media_clear_additional_<?php echo esc_attr($post_id); ?>').click(function() {
								$('.media-list-additional-<?php echo esc_attr($post_id); ?> .gallery-image').remove();
							})
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

                    $rbfw_single_template = isset( $_POST['rbfw_single_template'] ) ? rbfw_array_strip( $_POST['rbfw_single_template'] ) : 'Default';
                    $rbfw_single_template = is_dir(RBFW_Function::get_template_path('single/'.strtolower($rbfw_single_template)))? $rbfw_single_template : 'Default';
                    
                    
                    $dt_sidebar_switch 	 = isset( $_POST['rbfw_dt_sidebar_switch'] ) ? rbfw_array_strip($_POST['rbfw_dt_sidebar_switch']) : '';
                    $testimonials 	 = isset( $_POST['rbfw_dt_sidebar_testimonials'] ) ? rbfw_array_strip( $_POST['rbfw_dt_sidebar_testimonials'] ) : [];
                    $sidebar_content 	 = isset( $_POST['rbfw_dt_sidebar_content'] ) ? rbfw_array_strip( $_POST['rbfw_dt_sidebar_content'] ) : [];
                    $gallery_images = isset( $_POST['rbfw_gallery_images_additional'] ) ? rbfw_array_strip( $_POST['rbfw_gallery_images_additional'] ) : [];

                    update_post_meta( $post_id, 'rbfw_dt_sidebar_switch', $dt_sidebar_switch );
                    update_post_meta( $post_id, 'rbfw_single_template', $rbfw_single_template );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_testimonials', $testimonials );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_content', $sidebar_content );
					update_post_meta($post_id, 'rbfw_gallery_images_additional', $gallery_images);

					
                }
            }
		}
		new RBFW_Template();
	}