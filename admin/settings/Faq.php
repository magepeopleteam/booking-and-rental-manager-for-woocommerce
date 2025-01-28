<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_FAQ' ) ) {
		class RBFW_FAQ {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content_accordion' ] );
				add_action( 'wp_ajax_get_rbfw_add_faq_content', [ $this, 'get_rbfw_add_faq_content' ] );
				add_action( 'wp_ajax_nopriv_get_rbfw_add_faq_content', [ $this, 'get_rbfw_add_faq_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
				add_action( 'wp_ajax_rbfw_save_faq_data', [ $this, 'rbfw_save_faq_data' ] );
				add_action( 'wp_ajax_nopriv_rbfw_save_faq_data', [ $this, 'rbfw_save_faq_data' ] );
			}

			public function get_rbfw_add_faq_content() {
				check_ajax_referer( 'rbfw_add_faq_nonce', 'security' );
				if ( isset( $_POST['count'] ) ) {
					$count = absint( wp_unslash( $_POST['count'] ) );
					$count = $count + 1;
					$id    = 'id' . uniqid();
					echo wp_kses( $this->rbfw_repeated_item_addnew( $id, 'mep_event_faq', [], $count ), rbfw_allowed_html() );
				} else {
					wp_send_json_error( 'Missing count parameter' );
				}
				wp_die();
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_faq"><i class="fas fa-circle-question"></i><?php esc_html_e( ' FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php echo esc_html( $title ); ?>
                        </label>
                        <span><?php echo wp_kses( $description ,rbfw_allowed_html() ); ?></span>
                    </div>
                </section>
				<?php
			}

			public function rbfw_repeated_item_addnew( $id, $meta_key, $data = array(), $i = null ) {
				ob_start();
				$array = $this->get_rbfw_repeated_setting_array( $meta_key );
				$title_name  = $array['title_name'];
				$title_value = array_key_exists( $title_name, $data ) ? html_entity_decode( $data[ $title_name ] ) : '';
				$image_name   = $array['img_name'];
				$images       = array_key_exists( $image_name, $data ) ? $data[ $image_name ] : '';
				$content_name = $array['content_name'];
				$content      = array_key_exists( $content_name, $data ) ? html_entity_decode( $data[ $content_name ] ) : '';
				?>
                <div class='rbfw_remove_area rbfw_faq_item' data-id="<?php echo esc_attr( $i ); ?>" data-status="">
                    <div class="rbfw_faq_new_accordion_wrapper">
                        <div class="rbfw_faq_header">
                            <div class="rbfw_faq_accordion_icon"><i class="fas fa-plus"></i></div>
                            <div class="rbfw_faq_header_title">
								<?php echo esc_html( $title_value ); ?>
                            </div>
                            <div class="rbfw_faq_action_btns">
                                <span class="rbfw_faq_item_edit" data-id="<?php echo esc_attr( $i ); ?>"><i class='far fa-edit'></i></span>
                                <span class="rbfw_item_remove"><i class="fas fa-trash-can"></i></span>
                            </div>
                        </div>
                        <div class="rbfw_faq_content_wrapper">
                            <div class="rbfw_multi_image_area">
                                <div class="rbfw_multi_image rbfw_faq_img">
									<?php
										$all_images = explode( ',', $images );
										if ( $images && sizeof( $all_images ) > 0 ) {
											foreach ( $all_images as $image ) {
												?>
                                                <div class="rbfw_multi_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
												<img src="<?php echo esc_url( wp_get_attachment_image_url( $image, 'medium' ) ); ?>" alt="<?php echo esc_attr( $image ); ?>"/>
                                                </div>
												<?php
											}
										}
									?>
                                </div>
                            </div>
                            <div class="rbfw_faq_desc">
								<?php echo wp_kses_post( $content ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="rbfw_faq_slide_wrap">
                        <div class="rbfw_faq_slide_overlay">
                            <div class="rbfw_faq_slide_header">
                                <div class="rbfw_faq_slide_actionlinks">
                                    <span class="rbfw_faq_slide_close"><i class="fa fa-times" aria-hidden="true"></i></span>
                                </div>
                            </div>
                            <div class="rbfw_faq_slide_body">
                                <div class="rbfw_faq_header_title2">
                                    <label><?php esc_html_e( 'Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                    <input type="text" class="formControl rbfw_faq_title_input" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
                                </div>
                                <div class="rbfw_multi_image_area">
                                    <input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php echo esc_attr( $images ); ?>"/>
                                    <div class="rbfw_multi_image rbfw_faq_img">
										<?php
											$all_images = explode( ',', $images );
											if ( $images && sizeof( $all_images ) > 0 ) {
												foreach ( $all_images as $image ) {
													?>
                                                    <div class="rbfw_multi_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
                                                        <span class="rbfw_close_multi_image_item"><i class="fas fa-trash-can"></i></span>
                                                        <img src="<?php echo esc_attr( wp_get_attachment_image_url( $image, 'medium' ) ) ?>" alt="<?php echo esc_attr( $image ); ?>'"/>
                                                    </div>
													<?php
												}
												?>
                                                <div class="rbfw_upload_img_notice">
                                                    <span class="rbfw_upload_img_icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span>
                                                    <span class="rbfw_upload_img_text"><?php esc_html_e( 'Upload your images here', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                </div>
												<?php
											} else {
												?>
                                                <div class="rbfw_upload_img_notice">
                                                    <span class="rbfw_upload_img_icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span>
                                                    <span class="rbfw_upload_img_text"><?php esc_html_e( 'Upload your images here', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                </div>
												<?php
											}
										?>
                                    </div>
                                    <div class="rbfw_faq_img_add_btn_wrap">
                                        <button type="button" class=" add_multi_image ppof-button">
                                            <i class="fas fa-circle-plus"></i>
											<?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="rbfw_faq_desc2">
                                    <label><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
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
                                <div class="rbfw_faq_slide_footer">
                                    <div class="rbfw_faq_slide_actionlinks">
                                        <button type="button" class="rbfw_save_faq_content_btn ppof-button">
											<?php esc_html_e( 'Save & Close', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-circle-notch fa-spin"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			}

			public function rbfw_repeated_item_accordion( $id, $meta_key, $data = array(), $i = null ) {
				ob_start();
				$array        = $this->get_rbfw_repeated_setting_array( $meta_key );
				$title_name   = $array['title_name'];
				$title_value  = array_key_exists( $title_name, $data ) ? html_entity_decode( $data[ $title_name ] ) : '';
				$image_name   = $array['img_name'];
				$images       = array_key_exists( $image_name, $data ) ? $data[ $image_name ] : '';
				$content_name = $array['content_name'];
				$content      = array_key_exists( $content_name, $data ) ? html_entity_decode( $data[ $content_name ] ) : '';
				?>
                <div class='rbfw_remove_area rbfw_faq_item' data-id="<?php echo esc_attr( $i ); ?>" data-status="saved">
                    <div class="rbfw_faq_header">
                        <div class="rbfw_faq_accordion_icon"><i class="fas fa-plus"></i></div>
                        <div class="rbfw_faq_header_title">
							<?php echo esc_html( $title_value ); ?>
                        </div>
                        <div class="rbfw_faq_action_btns">
                            <span class="rbfw_faq_item_edit" data-id="<?php echo esc_attr( $i ); ?>"><i class='far fa-edit'></i></span>
                            <span class="rbfw_item_remove"><i class="fas fa-trash-can"></i></span>
                        </div>
                    </div>
                    <div class="rbfw_faq_content_wrapper">
                        <div class="rbfw_multi_image_area">
                            <div class="rbfw_multi_image rbfw_faq_img">
								<?php
									$all_images = explode( ',', $images );
									if ( $images && sizeof( $all_images ) > 0 ) {
										foreach ( $all_images as $image ) {
											?>
                                            <div class="rbfw_multi_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
                                                <img src="<?php echo esc_attr( wp_get_attachment_image_url( $image, 'medium' ) ) ?>" alt="<?php echo esc_attr( $image ); ?>'"/>
                                            </div>
											<?php
										}
									}
								?>
                            </div>
                        </div>
                        <div class="rbfw_faq_desc">
							<?php echo wp_kses_post( $content ); ?>
                        </div>
                    </div>
                    <div class="rbfw_faq_slide_wrap">
                        <div class="rbfw_faq_slide_overlay">
                            <div class="rbfw_faq_slide_header">
                                <div class="rbfw_faq_slide_actionlinks">
                                    <span class="rbfw_faq_slide_close"><i class="fa fa-times" aria-hidden="true"></i></span>
                                </div>
                            </div>
                            <div class="rbfw_faq_slide_body">
                                <div class="rbfw_faq_header_title2">
                                    <label><?php esc_html_e( 'Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                    <input type="text" class="formControl rbfw_faq_title_input" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
                                </div>
                                <div class="rbfw_multi_image_area">
                                    <input type="hidden" class="rbfw_multi_image_value" name="<?php echo esc_attr( $image_name ); ?>[]" value="<?php echo esc_attr( $images ); ?>"/>
                                    <div class="rbfw_multi_image rbfw_faq_img">
										<?php
											$all_images = explode( ',', $images );
											if ( $images && sizeof( $all_images ) > 0 ) {
												foreach ( $all_images as $image ) {
													?>
                                                    <div class="rbfw_multi_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
                                                        <span class="rbfw_close_multi_image_item"><i class="fas fa-trash-can"></i></span>
                                                        <img src="<?php echo esc_url( wp_get_attachment_image_url( $image, 'medium' ) ); ?>" alt="<?php echo esc_attr( $image ); ?>"/>
                                                    </div>
													<?php
												}
												?>
                                                <div class="rbfw_upload_img_notice">
                                                    <span class="rbfw_upload_img_icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span>
                                                    <span class="rbfw_upload_img_text"><?php esc_html_e( 'Upload your images here', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                </div>
												<?php
											} else {
												?>
                                                <div class="rbfw_upload_img_notice">
                                                    <span class="rbfw_upload_img_icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span>
                                                    <span class="rbfw_upload_img_text"><?php esc_html_e( 'Upload your images here', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                </div>
												<?php
											}
										?>
                                    </div>
                                    <div class="rbfw_faq_img_add_btn_wrap">
                                        <button type="button" class=" add_multi_image ppof-button">
                                            <i class="fas fa-circle-plus"></i>
											<?php esc_html_e( 'Add Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="rbfw_faq_desc2">
                                    <label><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
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
                                <div class="rbfw_faq_slide_footer">
                                    <div class="rbfw_faq_slide_actionlinks">
                                        <button type="button" class="rbfw_save_faq_content_btn ppof-button">
											<?php esc_html_e( 'Save & Close', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-circle-notch fa-spin"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			}

			public function rbfw_save_faq_data() {

                if (!current_user_can('manage_options')) {
                    wp_send_json_error(['message' => 'Unauthorized access'], 403);
                    wp_die();
                }

				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_save_faq_data_nonce' ) ) {
					die( 'Permission denied' );
				}
				$postID = isset( $_POST['postID'] ) ? sanitize_text_field( wp_unslash( $_POST['postID'] ) ) : 0;
				$jsObj  = isset( $_POST['data'] ) ? json_decode( sanitize_textarea_field( wp_unslash( $_POST['data'] ) ), true ) : [];
				update_post_meta( $postID, 'mep_event_faq', $jsObj );
				wp_die();
			}

			public function add_tabs_content_accordion( $post_id ) {
				$rbfw_enable_faq_content = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_faq">
					<?php $this->section_header(); ?>

					<?php $this->panel_header( 'FAQ Settings', 'FAQ Settings' ); ?>
                    <section>
                        <div>
                            <label><?php esc_html_e( 'FAQ Content', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <span><?php esc_html_e( 'FAQ Content turn on/off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_faq_content" value="<?php echo esc_attr( $rbfw_enable_faq_content ); ?>" <?php echo esc_attr( ( $rbfw_enable_faq_content == 'yes' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <div class="rbfw-faq-content-wrapper-main <?php echo esc_attr( ( $rbfw_enable_faq_content == 'yes' ) ? 'show' : 'hide' ); ?>">
						<?php
							$faqs = RBFW_Function::get_post_info( $post_id, 'mep_event_faq', array() );
							if ( sizeof( $faqs ) > 0 ) {
								$i = 0;
								foreach ( $faqs as $faq ) {
									$id = 'rbfw_faq_content_' . $i;
									echo wp_kses( $this->rbfw_repeated_item_accordion( $id, 'mep_event_faq', $faq, $i ) , rbfw_allowed_html() );
									$i ++;
								}
							}
						?>
                    </div>
                    <div class="rbfw_faq_content_btn_wrap <?php echo esc_attr( ( $rbfw_enable_faq_content == 'yes' ) ? 'show' : 'hide' ); ?>">
                        <button type="button" class="rbfw_add_faq_content ppof-button">
							<?php esc_html_e( 'Add FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            <i class="fas fa-spinner fa-pulse"></i>
                        </button>
                    </div>
                </div>
                <style>
					.rbfw-pointer-not-allowed {
						cursor: not-allowed;
						pointer-events: none;
						opacity: 0.7;
					}
					.rbfw_faq_new_accordion_wrapper {
						display: none;
					}
                    <?php
					if(use_block_editor_for_post_type('rbfw_item')){
						?>
					.rbfw_faq_slide_overlay {
						padding-top: 60px !important;
					}
					.rbfw_faq_slide_body {
						padding-bottom: 50px !important;
					}
                    <?php
				}
				?>
					.rbfw-faq-content-wrapper-main .rbfw_faq_desc2 {
						margin-top: 20px;
					}
					#rbfw_add_meta_box .rbfw-faq-content-wrapper-main .rbfw_faq_desc2 label {
						margin-bottom: -15px;
						font-weight: 500;
					}
					.rbfw_upload_img_icon {
						font-size: 20px;
					}
					.rbfw_upload_img_notice {
						text-align: center;
						width: 100%;
						cursor: pointer;
						display: none;
					}
					.rbfw_upload_img_notice span {
						display: block;
					}
					.rbfw_faq_slide_body .rbfw_faq_img {
						margin-top: 15px;
						background-color: #f7f7f7;
						padding: 10px;
						border: 3px dashed #bdbdbd;
					}
					.rbfw_faq_slide_body div.rbfw_multi_image_item {
						width: 141.70px;
					}
					.rbfw_faq_slide_body {
						padding: 25px;
						overflow: scroll;
						height: 100%;
					}
					.rbfw_faq_slide_header {
						border-bottom: 1px solid #efefef;
						padding: 10px;
					}
					.rbfw_faq_slide_actionlinks {
						display: -webkit-box;
						display: -webkit-flex;
						display: -ms-flexbox;
						display: flex;
						justify-content: flex-end;
					}
					.rbfw_faq_slide_footer .rbfw_faq_slide_actionlinks {
						justify-content: flex-start;
					}
					.rbfw_faq_slide_footer {
						display: block;
						margin-top: 20px;
					}
					.rbfw_faq_slide_actionlinks .faq_notice {
						line-height: 35px;
						margin-right: 10px;
						color: #c10c0c;
						font-weight: bold;
					}
					.rbfw_faq_slide_close {
						font-size: 25px;
						color: #8b8b8b;
						cursor: pointer;
						background-color: #f7f7f7;
						line-height: 25px;
						padding: 5px;
						width: 40px;
						text-align: center;
						border-radius: 3px;
					}
					.rbfw_faq_slide_overlay {
						background-color: #ffffff;
						width: 700px;
						height: 100%;
						top: 0;
						right: 0;
						position: absolute;
						display: none;
						padding-top: 30px;
						padding-bottom: 40px;
					}
					div.rbfw_remove_area {
						position: inherit;
					}
					.rbfw_faq_slide_wrap {
						background: rgb(0 0 0 / 49%);
						display: none;
						position: fixed;
						width: 100%;
						height: 100%;
						top: 0;
						right: 0;
						z-index: 9999;
						padding: 40px 30px;
					}
					.rbfw-faq-content-wrapper-main .mce-widget button:hover {
						background-color: inherit;
						color: inherit;
					}
					.rbfw_faq_content_btn_wrap {
						display: -webkit-box;
						display: -webkit-flex;
						display: -ms-flexbox;
						display: flex;
						align-content: center;
						align-items: center;
					}
					.rbfw-faq-content-wrapper-main section.bg-light div:nth-child(2) {
						width: 85%;
					}
					#rbfw_add_meta_box .rbfw-faq-content-wrapper-main .rbfw_faq_content_btn_wrap section.bg-light input[type=text] {
						width: 100%;
					}
					.rbfw_add_faq_content.ppof-button {
						margin: 0;
					}
					.rbfw_add_faq_content i {
						display: none;
					}
					.rbfw_faq_content_btn_wrap button.ppof-button {
						display: inline-block;
						line-height: 20px;
						margin-top: 20px;
						margin-right: 15px;
						width: 100px;
					}
					.mpStyle button.rbfw_save_faq_content_btn {
						position: relative;
						width: 100px;
						margin-right: 10px;
						overflow: hidden;
					}
					.rbfw_save_faq_content_btn i {
						position: absolute;
						right: -4px;
						margin-right: 0;
						display: none;
						top: -41px;
						background: rgb(70 69 68 / 38%);
						padding: 50px 50px;
					}
					#rbfw_add_meta_box input[type=text].rbfw_faq_title_input,
					div.mpStyle .formControl.rbfw_faq_title_input {
						width: 100%;
					}
					.rbfw-faq-content-wrapper-main .rbfw_item_drag {
						cursor: pointer;
						background-color: var(--mage-primary);
						color: #fff;
						border-radius: 0px;
						height: 30px;
						width: 30px;
						text-align: center;
						line-height: 30px;
						display: inline-block;
					}
					.rbfw-faq-content-wrapper-main .rbfw_faq_header_title {
						width: 84%;
						padding-right: 10px;
					}
					.rbfw-faq-content-wrapper-main .rbfw_faq_header_title2 {
						width: 100%;
					}
					#rbfw_add_meta_box .rbfw-faq-content-wrapper-main .rbfw_faq_header_title2 label {
						font-weight: 500;
						margin-bottom: 5px;
					}
					.rbfw-faq-content-wrapper-main div.rbfw_remove_area .rbfw_item_remove, .rbfw-faq-content-wrapper-main .rbfw_faq_item_edit {
						height: 30px;
						width: 30px;
						text-align: center;
						line-height: 30px;
						display: inline-block;
					}
					.rbfw-faq-content-wrapper-main div.rbfw_remove_area .rbfw_item_remove {
						position: inherit;
						border-radius: 0px;
					}
					.rbfw-faq-content-wrapper-main .rbfw_faq_item_edit {
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
					.rbfw-faq-content-wrapper-main .rbfw_faq_header .rbfw_faq_accordion_icon i {
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
					.rbfw-faq-content-wrapper-main div.rbfw_faq_item {
						box-shadow: none;
						border: 1px solid var(--mage-light);
						margin-top: 10px;
						margin-bottom: 0;
					}
                </style>
				<?php
			}

			public function get_rbfw_repeated_setting_array( $meta_key ): array {
				$array = [
					'mep_event_faq' => [
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
					$meta_key     = 'mep_event_faq';
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
						$count    = ! empty( $title ) ? count( $title ) : 0;
						if ( $count > 0 ) {
							for ( $i = 0; $i < $count; $i ++ ) {
								if ( $title[ $i ] != '' ) {
									$new_data[ $i ][ $title_name ] = stripslashes( wp_strip_all_tags( $title[ $i ] ) );
									if ( $images[ $i ] != '' ) {
										$new_data[ $i ][ $image_name ] = stripslashes( wp_strip_all_tags( $images[ $i ] ) );
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
						$rbfw_enable_faq_content = isset( $_POST['rbfw_enable_faq_content'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_faq_content'] ) ) : 'no';
						update_post_meta( $post_id, 'rbfw_enable_faq_content', $rbfw_enable_faq_content );
					}
				}
			}
		}
		new RBFW_FAQ();
	}

