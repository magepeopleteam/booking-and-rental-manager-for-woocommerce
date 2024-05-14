<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Front_End_Display')) {
        class RBFW_Front_End_Display{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_frontend_display"><i class="fa-solid fa-display"></i><?php esc_html_e( ' Front-end Display', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
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

            public function add_tabs_content( $post_id ) {
				$rbfw_available_qty_info_switch = get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) : 'no';
			?>
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_frontend_display">
					
				<?php $this->section_header(); ?>
                <?php $this->panel_header('Front-end Display Settings ','Front-end Display Settings'); ?>

				<section >
					<div>
						<label><?php _e( 'Enable the Available Item Quantity Display on Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'It displays available quantity information in item details page.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_available_qty_info_switch" value="<?php echo esc_attr($rbfw_available_qty_info_switch); ?>" <?php echo esc_attr(($rbfw_available_qty_info_switch=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>

				<script>
					jQuery('input[name=rbfw_available_qty_info_switch]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
						}
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
					$rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? $_POST['rbfw_available_qty_info_switch']  : 'no';
					
					update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );
					
					
                }
            }
		}

		new RBFW_Front_End_Display();
	}
