<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Terms_Settings' ) ) {
		class RBFW_Terms_Settings {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'term_tab' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'term_tab_content' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'custom_editor_enqueue' ] );

				// save term data
				add_action( 'wp_ajax_rbfw_term_data_save', [ $this, 'save_term_data_settings' ] );
				// update term data
				add_action( 'wp_ajax_rbfw_term_data_update', [ $this, 'term_data_update' ] );
				// rbfw_delete_term_data
				add_action( 'wp_ajax_rbfw_term_delete_item', [ $this, 'term_delete_item' ] );

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

			public function term_tab() {
				?>
                <li data-target-tabs="#rbfw_term_meta">
                    <i class="far fa-question-circle"></i><?php esc_html_e( 'Term', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </li>
				<?php
			}

			public function term_tab_content( $post_id ) {
				$enable_term = get_post_meta( $post_id, 'rbfw_enable_term_content', true );
				$enable_term = $enable_term ? $enable_term : 'yes';
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_term_meta">
                    <h2 class="mp_tab_item_title"><?php esc_html_e( 'term Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php esc_html_e( 'term Settings will be here.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    <section class="bg-light mt-5">
                        <div>
                            <label><?php esc_html_e( 'term Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'term Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                    </section>
                    <section>
                        <div>
                            <label><?php esc_html_e( 'term Settings Enable', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <p><?php esc_html_e( 'term Settings Enable', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_term_content" value="<?php echo esc_attr( ( $enable_term == 'yes' ) ? $enable_term : 'no' ); ?>" <?php echo esc_attr( ( $enable_term == 'yes' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
					<div class="">
						<section class="rbfw-term-section" style="display:<?php echo esc_attr( ( $enable_term == 'no' ) ? 'none' : 'block' ); ?>">
							<div class="rbfw-term-items mB">
								<?php
									$this->show_term_data( $post_id );
								?>
							</div>
							<button class="button rbfw-term-item-new" data-modal="rbfw-term-item-new" type="button"><?php _e( 'Add term', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</section>
					</div>
                    <!-- sidebar collapse open -->
                    <div class="rbfw-modal-container" data-modal-target="rbfw-term-item-new">
                        <div class="rbfw-modal-content">
                            <span class="rbfw-modal-close"><i class="fas fa-times"></i></span>
                            <div class="title">
                                <h3 id="rbfw-modal-title"><?php _e( 'Add Term.', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                <div id="rbfw-term-msg"></div>
                            </div>
                            <div class="content">

                                <label class="switch">
                                    <input type="checkbox" name="rbfw_term_condition_required">
                                    <span class="slider round"></span>
                                </label>

                                <label>
									<?php _e( 'Add Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    <input type="hidden" name="rbfw_post_id" value="<?php echo $post_id; ?>">
                                    <input type="text" name="rbfw_term_title">
                                    <input type="hidden" name="rbfw_term_item_id">
                                </label>

                                <label>
                                    <?php _e( 'Add Description Url', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    <input type="hidden" name="rbfw_post_id" value="<?php echo $post_id; ?>">
                                    <input type="text" name="rbfw_term_url">
                                    <input type="hidden" name="rbfw_term_item_id">
                                </label>


                                <div class="mT"></div>
                                <div class="rbfw_term_save_buttons m-1">
                                    <p>
                                        <button id="rbfw_term_save" class="button button-primary button-large"><?php _e( 'Save', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                        <button id="rbfw_term_save_close" class="button button-primary button-large">save close</button>
                                    <p>
                                </div>
                                <div class="rbfw_term_update_buttons m-1" style="display: none;">
                                    <p>
                                        <button id="rbfw_term_update" class="button button-primary button-large"><?php _e( 'Update and Close', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
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
					$enable_term_content = isset( $_POST['rbfw_enable_term_content'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_term_content'] ) ) : 'no';
					update_post_meta( $post_id, 'rbfw_enable_term_content', $enable_term_content );
				}
			}

			public function show_term_data( $post_id ) {
				$rbfw_term = get_post_meta( $post_id, 'mep_event_term', true );
				if ( ! empty( $rbfw_term ) ):
					foreach ( $rbfw_term as $key => $value ) :
						?>
                        <div class="rbfw-term-item" data-id="<?php echo esc_attr( $key ); ?>">
                            <section class="term-header" data-collapse-target="#term-content-<?php echo esc_attr( $key ); ?>">
                                <div>
                                    <p class="term_title">hhh<?php echo esc_html( $value['rbfw_term_title'] ); ?></p>
                                    <p class="term_url"><?php echo esc_html( $value['rbfw_term_url'] ); ?></p>
                                    <p style="display: none">Required: <span class="mep-term-required"><?php echo esc_html( $value['rbfw_term_required'] ); ?></span></p>
                                    <div class="term-action">
                                        <span class=""><i class="fas fa-eye"></i></span>
                                        <span class="rbfw-term-item-edit" data-modal="rbfw-term-item-new"><i class="fas fa-edit"></i></span>
                                        <span class="rbfw-term-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </div>
                            </section>
                        </div>
					<?php
					endforeach;
				endif;
			}

			public function term_data_update() {
				// Ensure required POST fields are present
				if (
					! isset( $_POST['nonce'], $_POST['rbfw_term_postID'], $_POST['rbfw_term_itemID'],$_POST['rbfw_term_required'], $_POST['rbfw_term_title'], $_POST['rbfw_term_url'] )
				) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Verify nonce
			/*	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
					wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}*/
                check_ajax_referer( 'rbfw_term_data_update_action', 'nonce' );

			
				// Sanitize and validate inputs
				$post_id      = intval( $_POST['rbfw_term_postID'] );
				$term_item_id  = intval( $_POST['rbfw_term_itemID'] );
			
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
			
				// Fetch existing terms
				$rbfw_term = get_post_meta( $post_id, 'mep_event_term', true );
				$rbfw_term = is_array( $rbfw_term ) ? $rbfw_term : [];
			
				// Validate item exists
				if ( ! isset( $rbfw_term[ $term_item_id ] ) ) {
					wp_send_json_error( [ 'message' => __( 'term item not found.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Sanitize and update data
				$new_data = [
					'rbfw_term_required'   => sanitize_text_field( $_POST['rbfw_term_required'] ),
					'rbfw_term_title'   => sanitize_text_field( $_POST['rbfw_term_title'] ),
					'rbfw_term_url' => wp_kses_post( $_POST['rbfw_term_url'] ),
				];
			
				$rbfw_term[ $term_item_id ] = $new_data;
			
				// Update post meta
				$result = update_post_meta( $post_id, 'mep_event_term', $rbfw_term );
			
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data updated successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_term_data( $post_id );
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
			


			public function save_term_data_settings() {
				// Check required POST fields exist
				if (! isset( $_POST['nonce'], $_POST['rbfw_term_postID'], $_POST['rbfw_term_title'], $_POST['rbfw_term_url'] )) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}

                check_ajax_referer( 'rbfw_term_data_save_action', 'nonce' );
			
				// Sanitize and validate post ID
				$post_id = intval( $_POST['rbfw_term_postID'] );
			
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
					'rbfw_term_required'   => sanitize_text_field( $_POST['rbfw_term_required'] ),
					'rbfw_term_title'   => sanitize_text_field( $_POST['rbfw_term_title'] ),
					'rbfw_term_url'   => sanitize_text_field( $_POST['rbfw_term_url'] ),
                ];
			
				// Retrieve existing term meta
				$rbfw_term = get_post_meta( $post_id, 'mep_event_term', true );
				$rbfw_term = is_array( $rbfw_term ) ? $rbfw_term : [];
			
				// Append new term
				$rbfw_term[] = $new_data;
			
				// Save back to post meta
				$result = update_post_meta( $post_id, 'mep_event_term', $rbfw_term );
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data added successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_term_data( $post_id );
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
			

			public function term_delete_item() {
				// Check required fields
				if (! isset( $_POST['nonce'], $_POST['rbfw_term_postID'], $_POST['itemId'] )) {
					wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}			
				// Verify nonce

                check_ajax_referer( 'rbfw_term_delete_item_action', 'nonce' );


				// Sanitize and validate inputs
				$post_id  = intval( $_POST['rbfw_term_postID'] );
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
			
				// Fetch and validate term data
				$rbfw_term = get_post_meta( $post_id, 'mep_event_term', true );
				$rbfw_term = is_array( $rbfw_term ) ? $rbfw_term : [];
			
				if ( ! isset( $rbfw_term[ $item_id ] ) ) {
					wp_send_json_error( [ 'message' => __( 'term item not found.', 'booking-and-rental-manager-for-woocommerce' ) ] );
					wp_die();
				}
			
				// Remove the item and reindex the array
				unset( $rbfw_term[ $item_id ] );
				$rbfw_term = array_values( $rbfw_term );
			
				// Update the post meta
				$result = update_post_meta( $post_id, 'mep_event_term', $rbfw_term );
			
				if ( $result ) {
					ob_start();
					$resultMessage = __( 'Data deleted successfully.', 'booking-and-rental-manager-for-woocommerce' );
					$this->show_term_data( $post_id );
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
		new RBFW_Terms_Settings();
	}