<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_FAQ')) {
        class RBFW_FAQ{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
				add_action( 'wp_ajax_get_rbfw_add_faq_content', [$this,'get_rbfw_add_faq_content'] );
				add_action( 'wp_ajax_nopriv_get_rbfw_add_faq_content', [$this,'get_rbfw_add_faq_content']);

                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

			public function get_rbfw_add_faq_content() {
				$id = RBFW_Function::data_sanitize( $_POST['id'] );
				echo $this->rbfw_repeated_item( $id, 'mep_event_faq' );
				die();
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_faq"><i class="fa-solid fa-circle-question"></i><?php esc_html_e( ' FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

            public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php _e('Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php _e('Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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
			public function rbfw_repeated_item($id, $meta_key, $data = array() ){
				ob_start();
				$array = get_rbfw_repeated_setting_array( $meta_key );

				$title       = $array['title'];
				$title_name  = $array['title_name'];
				$title_value = array_key_exists( $title_name, $data ) ? html_entity_decode( $data[ $title_name ] ) : '';

				$image_title = $array['img_title'];
				$image_name  = $array['img_name'];
				$images      = array_key_exists( $image_name, $data ) ? $data[ $image_name ] : '';

				$content_title = $array['content_title'];
				$content_name  = $array['content_name'];
				$content       = array_key_exists( $content_name, $data ) ? html_entity_decode( $data[ $content_name ] ) : '';

				?>
				<div class='rbfw_remove_area mt-5'>
					<section class="bg-light">
						<div>
							<p class=""><?php echo esc_html( $title ); ?></p>
						</div>
						<div >
							<input type="text" class="formControl" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
						</div>
					</section>
					<section >
						<div class="rbfw_multi_image_area">
							<input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php esc_attr_e( $images ); ?>"/>
							<div class="rbfw_multi_image">
								<?php
									$all_images = explode( ',', $images );
									if ( $images && sizeof( $all_images ) > 0 ) {
										foreach ( $all_images as $image ) {
											?>
											<div class="rbfw_multi_image_item" data-image-id="<?php esc_attr_e( $image ); ?>">
												<span class="rbfw_close_multi_image_item"><i class="fa-solid fa-trash-can"></i></span>
												<img src="<?php echo wp_get_attachment_image_url( $image, 'medium' ) ?>" alt="<?php esc_attr_e( $image ); ?>'"/>
											</div>
											<?php
										}
									}
								?>
							</div>
							<button type="button" class=" add_multi_image ppof-button">
								<i class="fa-solid fa-circle-plus"></i>
								<?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</button>
						</div>
					</section>
					<section>
						<div class="w-100">
							<?php
								$settings = array(
									'wpautop'       => false,
									'media_buttons' => false,
									'textarea_name' => $content_name . '[]',
									'tabindex'      => '323',
									'editor_height' => 200,
									'editor_css'    => '',
									'editor_class'  => '',
									'teeny'         => false,
									'dfw'           => false,
									'tinymce'       => true,
									'quicktags'     => true
								);
								wp_editor( $content, $id, $settings );
							?>
						</div>
					</section>
					<span class="rbfw_item_remove"><i class="fa-solid fa-trash-can"></i></span>
				</div>
				<?php
				return ob_get_clean();
			}
            public function add_tabs_content( $post_id ) {
				$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
			?>
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_faq">
			
				<?php $this->section_header(); ?>
                
				<?php $this->panel_header('FAQ Settings','FAQ Settings'); ?>
				<section >
					<div>
						<label><?php _e( 'FAQ Content', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'FAQ Content turn on/off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_enable_faq_content" value="<?php echo esc_attr($rbfw_enable_faq_content); ?>" <?php echo esc_attr(($rbfw_enable_faq_content=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>
				
				<div class="rbfw-faq-content <?php echo esc_attr(($rbfw_enable_faq_content=='yes')?'show':'hide'); ?>" >
					<?php
							$faqs = RBFW_Function::get_post_info( $post_id, 'mep_event_faq', array() );
							if ( sizeof( $faqs ) > 0 ) {
								foreach ( $faqs as $faq ) {
									$id = 'rbfw_faq_content_' . uniqid();
									echo $this->rbfw_repeated_item( $id, 'mep_event_faq', $faq );
								}
							}
						?>
						<button type="button" class="rbfw_add_faq_content ppof-button mt-2">
							<i class="fa-solid fa-circle-plus"></i>
							<?php esc_html_e( 'Add New FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
				</div>
			</div>
			<script>
					jQuery('input[name=rbfw_enable_faq_content]').click(function(){
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw-faq-content').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw-faq-content').slideDown().removeClass('hide').addClass('show');
						}
					});
			</script>
			<?php 
			}

			public function get_rbfw_repeated_setting_array( $meta_key ): array {
				$array = [
					'mep_event_faq'        => [
						'title'         => esc_html__( ' FAQ Title', 'booking-and-rental-manager-for-woocommerce' ),
						'title_name'    => 'rbfw_faq_title',
						'img_title'     => esc_html__( ' FAQ Details image', 'booking-and-rental-manager-for-woocommerce' ),
						'img_name'      => 'rbfw_faq_img',
						'content_title' => esc_html__( ' FAQ Details Content', 'booking-and-rental-manager-for-woocommerce' ),
						'content_name'  => 'rbfw_faq_content',
					]
				];
			
				return $array[ $meta_key ];
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
					$meta_key	  = 'mep_event_faq';
                    $array        = $this->get_rbfw_repeated_setting_array( $meta_key );
					$title_name   = $array['title_name'];
					$image_name   = $array['img_name'];
					$content_name = $array['content_name'];
					if ( get_post_type( $post_id ) == 'rbfw_item' ) {
						$old_data = RBFW_Function::get_post_info( $post_id, $meta_key, array() );
						$new_data = array();
						$title    = RBFW_Function::get_submit_info( $title_name, array() );
						$images   = RBFW_Function::get_submit_info( $image_name, array() );
						$content  = RBFW_Function::get_submit_info( $content_name, array() );
						$count    = !empty($title) ? count( $title ) : 0;
						if ( $count > 0 ) {
							for ( $i = 0; $i < $count; $i ++ ) {
								if ( $title[ $i ] != '' ) {
									$new_data[ $i ][ $title_name ] = stripslashes( strip_tags( $title[ $i ] ) );
									if ( $images[ $i ] != '' ) {
										$new_data[ $i ][ $image_name ] = stripslashes( strip_tags( $images[ $i ] ) );
									}
									if ( $content[ $i ] != '' ) {
										$new_data[ $i ][ $content_name ] = htmlentities( $content[ $i ] );
									}
								}
							}

							if ( ! empty( $new_data ) && $new_data != $old_data ) {
								update_post_meta( $post_id, $meta_key, $new_data );
							} elseif ( empty( $new_data ) && $old_data ) {
								delete_post_meta( $post_id, $meta_key, $old_data );
							}
						}
						$rbfw_enable_faq_content  = isset( $_POST['rbfw_enable_faq_content'] ) ? rbfw_array_strip( $_POST['rbfw_enable_faq_content'] ) : 'no';
						update_post_meta( $post_id, 'rbfw_enable_faq_content', $rbfw_enable_faq_content );
					}
 
                }
            }
		}
		new RBFW_FAQ();
	}

