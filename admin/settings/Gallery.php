<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Gallery')) {
        class RBFW_Gallery{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_gallery_images_meta_boxes"><i class="fa-solid fa-images"></i><?php esc_html_e('Gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

             public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Gallery Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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

            public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_gallery_images_meta_boxes">
                    <?php $this->section_header(); ?>
                    <?php $this->panel_header('Gallery ','Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.'); ?>
					<section>
					<div  id="field-wrapper-<?php echo esc_attr($post_id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper field-media-multi-wrapper-<?php echo esc_attr($post_id); ?>">
						<div class='button upload' id='media_upload_<?php echo esc_attr($post_id); ?>'>
								<?php echo __('Upload','pickplugins-options-framework');?>
							</div>
							<div class='button clear' id='media_clear_<?php echo $post_id; ?>'>
								<?php echo __('Clear','pickplugins-options-framework');?>
							</div>
							<div class="gallery-images media-list-<?php echo esc_attr($post_id); ?> ">
								<?php
								$gallery_images = get_post_meta($post_id,'rbfw_gallery_images',true);
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
											<input type='hidden' name='rbfw_gallery_images[]' value='<?php echo esc_attr($image); ?>' />
										</div>
									<?php
									endforeach;
								endif;
								?>
							</div>
						</div>
					</section>
					<script>
						jQuery(document).ready(function($){
							$('#media_upload_<?php echo esc_attr($post_id); ?>').click(function() {
								//var send_attachment_bkp = wp.media.editor.send.attachment;
								wp.media.editor.send.attachment = function(props, attachment) {
									attachment_id = attachment.id;
									attachment_url = attachment.url;
									html = '<div class=" gallery-image">';
									html += '<span class="remove" onclick="jQuery(this).parent().remove()"><i class="fa-solid fa-trash-can"></i></span>';
									html += '<img src="'+attachment_url+'" style="width:100%"/>';
									html += '<input type="hidden" name="rbfw_gallery_images[]" value="'+attachment_id+'" />';
									html += '</div>';
									$('.media-list-<?php echo esc_attr($post_id); ?>').append(html);
									//wp.media.editor.send.attachment = send_attachment_bkp;
								}
								wp.media.editor.open($(this));
								return false;
							});
							$('#media_clear_<?php echo esc_attr($post_id); ?>').click(function() {
								$('.media-list-<?php echo esc_attr($post_id); ?> .gallery-image').remove();
							})
						});
					</script>
                </div>
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

					//$gallery_images = get_post_meta( $post_id, 'rbfw_gallery_images', true ) ? get_post_meta( $post_id, 'rbfw_gallery_images', true ) : [];
					$gallery_images = isset( $_POST['rbfw_gallery_images'] ) ? rbfw_array_strip( $_POST['rbfw_gallery_images'] ) : [];
					
					update_post_meta($post_id, 'rbfw_gallery_images', $gallery_images);

					

                }
            }
        }
        new RBFW_Gallery();
    }
