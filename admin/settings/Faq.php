<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Faq_Settings' ) ) {
		class RBFW_Faq_Settings {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'faq_tab' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'faq_tab_content' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'custom_editor_enqueue' ] );

				// save faq data
				add_action( 'wp_ajax_rbfw_faq_data_save', [ $this, 'save_faq_data_settings' ] );
				// update faq data
				add_action( 'wp_ajax_rbfw_faq_data_update', [ $this, 'faq_data_update' ] );
				// rbfw_delete_faq_data
				add_action( 'wp_ajax_rbfw_faq_delete_item', [ $this, 'faq_delete_item' ] );

				add_action( 'save_post', array( $this, 'settings_save' ), 99 );
			}

			public function custom_editor_enqueue() {
				// Enqueue necessary scripts
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'editor' );
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
			}

			public function faq_tab() {
				?>
                <li data-target-tabs="#rbfw_faq_meta">
                    <i class="far fa-question-circle"></i><?php esc_html_e( 'F.A.Q', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </li>
				<?php
			}

			public function faq_tab_content( $post_id ) {
				$enable_faq = get_post_meta( $post_id, 'rbfw_enable_faq_content', true );
				$enable_faq = $enable_faq ? $enable_faq : 'yes';
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_faq_meta">
                    <h2 class="mp_tab_item_title"><?php esc_html_e( 'FAQ Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php esc_html_e( 'FAQ Settings will be here.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    <section class="bg-light mt-5">
                        <div>
                            <label><?php esc_html_e( 'FAQ Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'FAQ Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                    </section>
                    <section>
                        <div>
                            <label><?php esc_html_e( 'FAQ Settings Enable', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'FAQ Settings Enable', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_faq_content" value="<?php echo esc_attr( ( $enable_faq == 'yes' ) ? $enable_faq : 'no' ); ?>" <?php echo esc_attr( ( $enable_faq == 'yes' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
					<div class="">
						<section class="rbfw-faq-section" style="display:<?php echo esc_attr( ( $enable_faq == 'no' ) ? 'none' : 'block' ); ?>">
							<div class="rbfw-faq-items mB">
								<?php
									$this->show_faq_data( $post_id );
								?>
							</div>
							<button class="button rbfw-faq-item-new" data-modal="rbfw-faq-item-new" type="button"><?php _e( 'Add FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</section>
					</div>
                    <!-- sidebar collapse open -->
                    <div class="rbfw-modal-container" data-modal-target="rbfw-faq-item-new">
                        <div class="rbfw-modal-content">
                            <span class="rbfw-modal-close"><i class="fas fa-times"></i></span>
                            <div class="title">
                                <h3 id="rbfw-modal-title"><?php _e( 'Add F.A.Q.', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                <div id="rbfw-faq-msg"></div>
                            </div>
                            <div class="content">
                                <label>
									<?php _e( 'Add Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    <input type="hidden" name="rbfw_post_id" value="<?php echo $post_id; ?>">
                                    <input type="text" name="rbfw_faq_title">
                                    <input type="hidden" name="rbfw_faq_item_id">
                                </label>
                                <label>
									<?php _e( 'Add Content', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
								<?php
									$content   = '';
									$editor_id = 'rbfw_faq_content';
									$settings  = array(
										'textarea_name' => 'rbfw_faq_content',
										'media_buttons' => true,
										'textarea_rows' => 10,
									);
									wp_editor( $content, $editor_id, $settings );
								?>
                                <div class="mT"></div>
                                <div class="rbfw_faq_save_buttons m-1">
                                    <p>
                                        <button id="rbfw_faq_save" class="button button-primary button-large"><?php _e( 'Save', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                        <button id="rbfw_faq_save_close" class="button button-primary button-large">save close</button>
                                    <p>
                                </div>
                                <div class="rbfw_faq_update_buttons m-1" style="display: none;">
                                    <p>
                                        <button id="rbfw_faq_update" class="button button-primary button-large"><?php _e( 'Update and Close', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                    <p>
                                </div>
                            </div>
                        </div>
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
					$enable_faq_content = isset( $_POST['rbfw_enable_faq_content'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_faq_content'] ) ) : 'no';
					update_post_meta( $post_id, 'rbfw_enable_faq_content', $enable_faq_content );
				}
			}

			public function show_faq_data( $post_id ) {
				$rbfw_faq = get_post_meta( $post_id, 'mep_event_faq', true );
				if ( ! empty( $rbfw_faq ) ):
					foreach ( $rbfw_faq as $key => $value ) :
						?>
                        <div class="rbfw-faq-item" data-id="<?php echo esc_attr( $key ); ?>">
                            <section class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr( $key ); ?>">
                                <div>
                                    <p><?php echo esc_html( $value['rbfw_faq_title'] ); ?></p>
                                    <div class="faq-action">
                                        <span class=""><i class="fas fa-eye"></i></span>
                                        <span class="rbfw-faq-item-edit" data-modal="rbfw-faq-item-new"><i class="fas fa-edit"></i></span>
                                        <span class="rbfw-faq-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </div>
                            </section>
                            <section class="faq-content mB" data-collapse="#faq-content-<?php echo esc_attr( $key ); ?>">
								<?php echo wpautop( wp_kses_post( $value['rbfw_faq_content'] ) ); ?>
                            </section>
                        </div>
					<?php
					endforeach;
				endif;
			}

			public function faq_data_update() {
				// Ensure required POST fields are present
				if (
					! isset( $_POST['nonce'], $_POST['rbfw_faq_postID'], $_POST['rbfw_faq_itemID'], $_POST['rbfw_faq_title'], $_POST['rbfw_faq_content'] )
				) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Verify nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
					wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Sanitize and validate inputs
				$post_id      = intval( $_POST['rbfw_faq_postID'] );
				$faq_item_id  = intval( $_POST['rbfw_faq_itemID'] );
			
				// Check user capability
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Optional: Ensure correct post type
				if ( get_post_type( $post_id ) !== 'rbfw_item' ) {
					wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Fetch existing FAQs
				$rbfw_faq = get_post_meta( $post_id, 'mep_event_faq', true );
				$rbfw_faq = is_array( $rbfw_faq ) ? $rbfw_faq : [];
			
				// Validate item exists
				if ( ! isset( $rbfw_faq[ $faq_item_id ] ) ) {
					wp_send_json_error( [ 'message' => __( 'FAQ item not found.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Sanitize and update data
				$new_data = [
					'rbfw_faq_title'   => sanitize_text_field( $_POST['rbfw_faq_title'] ),
					'rbfw_faq_content' => wp_kses_post( $_POST['rbfw_faq_content'] ),
				];
			
				$rbfw_faq[ $faq_item_id ] = $new_data;
			
				// Update post meta
				$result = update_post_meta( $post_id, 'mep_event_faq', $rbfw_faq );
			
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data updated successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_faq_data( $post_id );
					$html_output = ob_get_clean();
			
					wp_send_json_success( [
						'message' => $resultMessage,
						'html'    => $html_output,
					] );
				} else {
					wp_send_json_error( [ 'message' => __( 'Update failed.', 'booking-and-rental-manager-for-woocommerce' ) ] );
				}
			
				wp_die(); // safer than die
			}
			


			public function save_faq_data_settings() {
				// Check required POST fields exist
				if (
					! isset( $_POST['nonce'], $_POST['rbfw_faq_postID'], $_POST['rbfw_faq_title'], $_POST['rbfw_faq_content'] )
				) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Verify nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
					wp_send_json_error( [ 'message' => __( 'Nonce verification failed.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Sanitize and validate post ID
				$post_id = intval( $_POST['rbfw_faq_postID'] );
			
				// Check user capability
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => __( 'You do not have permission to perform this action.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Ensure correct post type (optional, adjust as needed)
				if ( get_post_type( $post_id ) !== 'rbfw_item' ) {
					wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Sanitize user inputs
				$new_data = [
					'rbfw_faq_title'   => sanitize_text_field( $_POST['rbfw_faq_title'] ),
					'rbfw_faq_content' => wp_kses_post( $_POST['rbfw_faq_content'] ),
				];
			
				// Retrieve existing FAQ meta
				$rbfw_faq = get_post_meta( $post_id, 'mep_event_faq', true );
				$rbfw_faq = is_array( $rbfw_faq ) ? $rbfw_faq : [];			
			
				// Append new FAQ
				$rbfw_faq[] = $new_data;
			
				// Save back to post meta
				$result = update_post_meta( $post_id, 'mep_event_faq', $rbfw_faq );			
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data added successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_faq_data( $post_id );
					$html_output = ob_get_clean();
			
					wp_send_json_success( [
						'message' => $resultMessage,
						'html'    => $html_output,
					] );
				} else {
					wp_send_json_error( [
						'message' => __( 'Failed to save data.', 'booking-and-rental-manager-for-woocommerce' ),
					] );
				}			
				wp_die(); // safer than die;
			}
			

			public function faq_delete_item() {
				// Check required fields
				if (! isset( $_POST['nonce'], $_POST['rbfw_faq_postID'], $_POST['itemId'] )) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}			
				// Verify nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
					wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}

				// Sanitize and validate inputs
				$post_id  = intval( $_POST['rbfw_faq_postID'] );
				$item_id  = intval( $_POST['itemId'] );
			
				// Check user capabilities
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Optional: Ensure correct post type
				if ( get_post_type( $post_id ) !== 'rbfw_item' ) {
					wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Fetch and validate FAQ data
				$rbfw_faq = get_post_meta( $post_id, 'mep_event_faq', true );
				$rbfw_faq = is_array( $rbfw_faq ) ? $rbfw_faq : [];
			
				if ( ! isset( $rbfw_faq[ $item_id ] ) ) {
					wp_send_json_error( [ 'message' => __( 'FAQ item not found.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Remove the item and reindex the array
				unset( $rbfw_faq[ $item_id ] );
				$rbfw_faq = array_values( $rbfw_faq );
			
				// Update the post meta
				$result = update_post_meta( $post_id, 'mep_event_faq', $rbfw_faq );
			
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data deleted successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_faq_data( $post_id );
					$html_output = ob_get_clean();
			
					wp_send_json_success( [
						'message' => $resultMessage,
						'html'    => $html_output,
					] );
				} else {
					wp_send_json_error( [
						'message' => __( 'Failed to delete data.', 'booking-and-rental-manager-for-woocommerce' ),
					] );
				}			
				wp_die(); // safer alternative to die
			}
			
		}
		new RBFW_Faq_Settings();
	}