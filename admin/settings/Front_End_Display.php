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
                <li data-target-tabs="#rbfw_frontend_display"><i class="fa-solid fa-gear"></i><?php esc_html_e( ' Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

            public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php _e('Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
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

            public function shipping_enable($post_id){
                ?>
                <section>
                    <div>
                        <label>
                            <?php echo esc_html__( 'Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <span><?php echo esc_html__('Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                    </div>
                    <?php $shipping_enable_switch = get_post_meta($post_id,'shipping_enable',true);?>
                    <label class="switch">
                        <input type="checkbox" name="shipping_enable" value="<?php echo esc_attr(($shipping_enable_switch=='on')?$shipping_enable_switch:'off'); ?>" <?php echo esc_attr(($shipping_enable_switch=='on')?'checked':''); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
                <?php
            }

            public function quantity_display($post_id){
				$rbfw_available_qty_info_switch = get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) : 'no';

                ?>
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
                <?php
            }
            public function shortcode($post_id){
                ?>
                    <section>
                        <div>
                            <label>
                                <?php echo esc_html__( 'Add To Cart Form Shortcode', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <span><?php echo esc_html__('This short code you can put anywhere in your content.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <code class="rbfw_add_to_cart_shortcode_code">[rent-add-to-cart  id='<?php echo $post_id; ?>']</code>
                    </section>
                <?php
            }

            public function add_tabs_content( $post_id ) {
			?>
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_frontend_display">
					
				<?php $this->section_header(); ?>
                <?php $this->panel_header('Front-end Display Settings ','Front-end Display Settings'); ?>
                <?php $this->quantity_display($post_id); ?>
                <?php $this->shipping_enable($post_id); ?>
                <?php $this->shortcode($post_id); ?>

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
					
                    jQuery('input[name=shipping_enable]').click(function(){  
                        var status = jQuery(this).val();
                        if(status == 'on') {
                            jQuery(this).val('off') 
                        }  
                        if(status == 'off') {
                            jQuery(this).val('on');  
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
                    $shipping_enable 	 = isset( $_POST['shipping_enable'] ) ? rbfw_array_strip( $_POST['shipping_enable'] ) : '';
					update_post_meta( $post_id, 'shipping_enable', $shipping_enable );
					update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );
					
					
                }
            }
		}

		new RBFW_Front_End_Display();
	}
