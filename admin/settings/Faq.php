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
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content_accordion'] );
				add_action( 'wp_ajax_get_rbfw_add_faq_content', [$this,'get_rbfw_add_faq_content'] );
				add_action( 'wp_ajax_nopriv_get_rbfw_add_faq_content', [$this,'get_rbfw_add_faq_content']);

                add_action('save_post', array($this, 'settings_save'), 99, 1);
				
				add_action( 'wp_ajax_rbfw_save_faq_data', [$this,'rbfw_save_faq_data'] );
				add_action( 'wp_ajax_nopriv_rbfw_save_faq_data', [$this,'rbfw_save_faq_data']);
			}


			public function get_rbfw_add_faq_content() {
				$id = RBFW_Function::data_sanitize( $_POST['id'] );
				$count = RBFW_Function::data_sanitize( $_POST['count'] );
				$count = (int)$count;
				echo $this->rbfw_repeated_item($id, 'mep_event_faq', [], $count);
				wp_die();
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
			public function rbfw_repeated_item($id, $meta_key, $data = array(), $i = null ){
				ob_start();
				$array = $this->get_rbfw_repeated_setting_array( $meta_key );

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
				<div class='rbfw_remove_area mt-5 rbfw_faq_item' data-id="<?php echo esc_attr($i); ?>">
					<section class="bg-light">
						<div>
							<p class=""><?php echo esc_html( $title ); ?></p>
						</div>
						<div >
							<input type="text" class="formControl rbfw_faq_title_input" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
						</div>
						<div>
							<span class="rbfw_item_remove"><i class="fa-solid fa-trash-can"></i></span>
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
				</div>
				<?php
				return ob_get_clean();
			}

			public function rbfw_repeated_item_accordion($id, $meta_key, $data = array(), $i = null ){
				ob_start();
				$array = $this->get_rbfw_repeated_setting_array( $meta_key );

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
				<div class='rbfw_remove_area rbfw_faq_item' data-id="<?php echo esc_attr($i); ?>">
					<div class="rbfw_faq_header">
						<div class="rbfw_faq_accordion_icon"><i class="fas fa-plus"></i></div> 
						<div class="rbfw_faq_header_title">
							<?php echo esc_html( $title_value ); ?>
						</div>
						<div class="rbfw_faq_header_title2">
							<input type="text" class="formControl rbfw_faq_title_input" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
						</div>
						<div class="rbfw_faq_action_btns">
							<span class="rbfw_faq_item_edit" data-id="<?php echo esc_attr($i); ?>"><i class='far fa-edit'></i></span>
							<span class="rbfw_item_remove"><i class="fa-solid fa-trash-can"></i></span>
						</div>
					</div>
					<div class="rbfw_faq_content_wrapper">
						<div class="rbfw_multi_image_area">
							<input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php esc_attr_e( $images ); ?>"/>
							<div class="rbfw_multi_image rbfw_faq_img">
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
							<div class="rbfw_faq_img_add_btn_wrap">	
								<button type="button" class=" add_multi_image ppof-button">
									<i class="fa-solid fa-circle-plus"></i>
									<?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</button>
							</div>
						</div>
							<div class="rbfw_faq_desc">
								<?php echo $content; ?>
							</div>
							<div class="rbfw_faq_desc2">
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
	
					</div>
				</div>
				<?php
				return ob_get_clean();
			}
			public function rbfw_save_faq_data(){
				$postID = $_POST['postID'];
				$jsObj = !empty($_POST['data']) ? $_POST['data'] : [];
				$jsObj = json_decode(stripslashes($jsObj),TRUE);
				update_post_meta($postID,'mep_event_faq',$jsObj);
				wp_die();
			}
			public function add_tabs_content_accordion( $post_id ) {
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

				<div class="rbfw-faq-content-wrapper-main <?php echo esc_attr(($rbfw_enable_faq_content=='yes')?'show':'hide'); ?>">
					<?php
							$faqs = RBFW_Function::get_post_info( $post_id, 'mep_event_faq', array() );
							if ( sizeof( $faqs ) > 0 ) {
								$i = 0;
								foreach ( $faqs as $faq ) {
									$id = 'rbfw_faq_content_' . $i;
									echo $this->rbfw_repeated_item_accordion( $id, 'mep_event_faq', $faq, $i );
									$i++;
								}
							}
						?>
				</div>
				<div class="rbfw_faq_content_btn_wrap">
					<button type="button" class="rbfw_add_faq_content ppof-button">
						<i class="fa-solid fa-circle-plus"></i>
						<?php esc_html_e( 'Add New FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
					
					<button type="button" class="rbfw_save_faq_content_btn ppof-button"> <?php esc_html_e( 'Save', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fa-solid fa-circle-notch fa-spin"></i>
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
			<script>
				jQuery(document).ready(function($){

					$('.rbfw_faq_item_edit').click(function (e) { 
						e.preventDefault();
						let dataId = $(this).data('id');
						let parent = $('.rbfw_faq_item[data-id='+dataId+']')
						let title1Wrap = parent.find('.rbfw_faq_header_title');
						let title2Wrap = parent.find('.rbfw_faq_header_title2');
						let desc1Wrap = parent.find('.rbfw_faq_desc');
						let desc2Wrap = parent.find('.rbfw_faq_desc2');
						let imgbtnWrap = parent.find('.rbfw_faq_img_add_btn_wrap');
						let contentWrap = parent.find('.rbfw_faq_content_wrapper');
						
						title1Wrap.hide();
						title2Wrap.show();
						desc1Wrap.hide();
						desc2Wrap.show();
						imgbtnWrap.show();
					});

					jQuery('.rbfw_faq_header').click(function(e){
						e.preventDefault();
						let parent = $(this).parent('.rbfw_faq_item');
						parent.find('.rbfw_faq_content_wrapper').slideToggle();
						parent.find('.rbfw_faq_accordion_icon i').toggleClass('fa-plus fa-minus');
					});
				});
			</script>
			<script>
				jQuery(document).ready(function ($) {
					$('.rbfw_save_faq_content_btn').click(function (e) { 
						e.preventDefault();
						let count = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item').length;
						let theDataArr = [];
						let postID = $('#post_ID').val();
						for (let i = 1; i <= count; i++) {
							let rbfw_faq_title = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item:nth-child('+i+') [name="rbfw_faq_title[]"]').val();
							let rbfw_faq_img = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item:nth-child('+i+') [name="rbfw_faq_img[]"]').val();
							let rbfw_faq_content = $('.rbfw-faq-content-wrapper-main .rbfw_faq_item:nth-child('+i+') [name="rbfw_faq_content[]"]').val();
							
							theDataArr.push({rbfw_faq_title : rbfw_faq_title, rbfw_faq_img: rbfw_faq_img, rbfw_faq_content: rbfw_faq_content});

						}
				
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								'action': 'rbfw_save_faq_data',
								'data': JSON.stringify(theDataArr),
								'postID': postID
							},
							beforeSend: function() {
								jQuery('.rbfw_save_faq_content_btn i').show();
								$('.rbfw_faq_content_btn_wrap .faq_notice').remove();
							},
							success: function(response) {

								jQuery('.rbfw_save_faq_content_btn i').hide();
								$('.rbfw_faq_content_btn_wrap').append('<span class="faq_notice"><?php esc_html_e('Saved!','booking-and-rental-manager-for-woocommerce'); ?></span>');
								$('.rbfw_faq_header_title2').hide();
								$('.rbfw_faq_header_title').show();
								$('.rbfw_faq_desc2').hide();
								$('.rbfw_faq_desc').show();
								$('.rbfw_faq_img_add_btn_wrap').hide();
								setTimeout(function() {
									$('.rbfw_faq_content_btn_wrap .faq_notice').remove();
								}, 5000);
							},
						});
					});

					$('[name="rbfw_faq_title[]"]').keyup(function (e) { 
						let thisValue = $(this).val();
						$(this).attr('value',thisValue);
						$(this).parents('.rbfw_faq_header').find('.rbfw_faq_header_title').html(thisValue);
					});
				});
			</script>
			<style>
				.rbfw-faq-content-wrapper-main .mce-widget button:hover{
					background-color:inherit;
					color:inherit;
				}
				.rbfw_faq_content_btn_wrap span.faq_notice{
					margin-top: 20px;
					color: #f32828;
				}
				.rbfw_faq_content_btn_wrap{
					display: -webkit-box;
					display: -webkit-flex;
					display: -ms-flexbox;
					display: flex;
					align-content: center;
					align-items: center;
				}
				.rbfw-faq-content-wrapper-main section.bg-light div:nth-child(2){
					width: 85%;
				}
				#rbfw_add_meta_box .rbfw-faq-content-wrapper-main  .rbfw_faq_content_btn_wrap section.bg-light  input[type=text]{
					width: 100%;
				}

				.rbfw_add_faq_content.ppof-button{
					margin:0;
				}
				.rbfw_faq_content_btn_wrap button.ppof-button{
					display: inline-block;
					line-height: 20px;
					margin-top: 20px;
					margin-right: 15px;
				}
				.rbfw_save_faq_content_btn{
					position: relative;
					width: 100px;
				}
				.rbfw_save_faq_content_btn i{
					position: absolute;
					right: 15px;
					margin-right: 0;
					display: none;
					top: 11px;
				}

				#rbfw_add_meta_box input[type=text].rbfw_faq_title_input,
				div.mpStyle .formControl.rbfw_faq_title_input{
					width: 100%;
				}

				.rbfw_faq_header_title2,
				.rbfw_faq_desc2,
				.rbfw_faq_img_add_btn_wrap{
					display:none;
				}
		
				.rbfw-faq-content-wrapper-main .rbfw_faq_header_title,
				.rbfw-faq-content-wrapper-main .rbfw_faq_header_title2{
					width:85%;
					padding-right: 10px;
				}
				.rbfw-faq-content-wrapper-main div.rbfw_remove_area .rbfw_item_remove,.rbfw-faq-content-wrapper-main .rbfw_faq_item_edit{
					height: 30px;
					width: 30px;
					text-align: center;
					line-height: 30px;
					display: inline-block;
				}
				.rbfw-faq-content-wrapper-main div.rbfw_remove_area .rbfw_item_remove{
					position: inherit;
					border-radius: 0px;
				}
				.rbfw-faq-content-wrapper-main .rbfw_faq_item_edit{
					cursor: pointer;
					background-color: var(--mage-primary);
					color: #fff;
					border-radius: 0px;
				}
				.rbfw-faq-content-wrapper-main .rbfw_faq_img {
					display: flex;
				}

				.rbfw-faq-content-wrapper-main .rbfw_faq_img img {
					width: 100%;
					margin-right: 10px;
					margin-bottom: 10px;
				}

				.rbfw-faq-content-wrapper-main .rbfw_faq_header {
					background: transparent;
					font-size: 15px;
					font-weight: 600;
					text-align: left;
					padding: 14px 15px;
					display: flex;
					justify-content: flex-start;
					margin-bottom: 0;
					line-height: 30px;
					background: var(--mage-light);
					color: var(--default-color);
				}
				

				.rbfw-faq-content-wrapper-main .rbfw_faq_header .rbfw_faq_accordion_icon i{
					color: var(--rbfw_color_secondary);
					background-color: #fff;
					height: 30px;
					width: 30px;
					text-align: center;
					line-height: 30px;
					margin-right: 10px;
				}

				.rbfw-faq-content-wrapper-main .rbfw_faq_header:hover {
					cursor: pointer;
				}

				.rbfw-faq-content-wrapper-main .rbfw_faq_content_wrapper {
					display: none;
					padding: 15px 15px 20px;
					border-top: 0;
				}
				.rbfw-faq-content-wrapper-main div.rbfw_faq_item{
					box-shadow: none;
					border: 1px solid var(--mage-light);
					margin-top: 10px;
    				margin-bottom: 0;
				}
			</style>
			<?php 
			}

            public function add_tabs_content( $post_id ) {
				$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
			?>
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_faq">
			
				<?php $this->section_header(); ?>
                
				<?php $this->panel_header('FAQ Settings','FAQ Settings'); ?>
				<section>
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
								$i = 0;
								foreach ( $faqs as $faq ) {
									$id = 'rbfw_faq_content_' . $i;
									echo $this->rbfw_repeated_item( $id, 'mep_event_faq', $faq, $i );
									$i++;
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

