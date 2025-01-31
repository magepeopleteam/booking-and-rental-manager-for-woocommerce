<?php
	/**
	 * @author Shahadat hossain <raselsha@gmail.com>
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Related' ) ) {
		class RBFW_Related {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', [ $this, 'settings_save' ], 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_related"><i class="fas fa-plug"></i><?php esc_html_e( 'Related', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure related items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label><?php echo esc_html( $title ); ?></label>
                        <span><?php echo esc_html( $description ); ?></span>
                    </div>
                </section>
				<?php
			}

			public function related_items( $post_id ) {
				?>
                <section>
                    <div id="rbfw_releted_rbfw" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rbfw_releted_rbfw w-100">
                        <select name="rbfw_releted_rbfw[]" id="rbfw_releted_rbfw" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
							<?php
								$releted_post_id = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ) : [];
								$the_query       = new WP_Query( array(
									'post_type'      => 'rbfw_item',
									'posts_per_page' => - 1,
								) );
							?>
							<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
								<option 
									<?php echo ( in_array( get_the_ID(), $releted_post_id ) ) ? 'selected' : ''; ?> 
									value="<?php echo esc_attr( get_the_ID() ); ?>"> 
									<?php echo esc_html( get_the_title() ); ?> 
								</option>
							<?php endwhile; ?>
                        </select>
                    </div>
                </section>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_related">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Related Items', 'Related Items' ); ?>
					<?php $this->related_items( $post_id ); ?>
                </div>
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
                    $rules = [
                        'name'        => 'sanitize_text_field',
                        'email'       => 'sanitize_email',
                        'age'         => 'absint',
                        'preferences' => [
                            'color'         => 'sanitize_text_field',
                            'notifications' => function ( $value ) {
                                return $value === 'yes' ? 'yes' : 'no';
                            }
                        ]
                    ];
                    $input_data_sabitized = sanitize_post_array( $_POST, $rules );
					// Use wp_unslash to remove slashes and then sanitize array items using rbfw_array_strip
					$related_categories = isset( $input_data_sabitized['rbfw_releted_rbfw'] ) ?  $input_data_sabitized['rbfw_releted_rbfw']  : [];
					// Update the post meta
					update_post_meta( $post_id, 'rbfw_releted_rbfw', $related_categories );
				}
			}
		}
		new RBFW_Related();
	}