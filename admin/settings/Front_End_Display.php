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
				$rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
				$rbfw_available_qty_info_switch = get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) : 'no';
				$rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
				$rbfw_enable_md_type_item_qty = get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) : 'no';
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
				
				<section >
					<div>
						<label><?php _e( 'Enable Multiple Item Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'It enables the multiple item quantity selection option. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_enable_md_type_item_qty" value="<?php echo esc_attr($rbfw_enable_md_type_item_qty); ?>" <?php echo esc_attr(($rbfw_enable_md_type_item_qty=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>

				<section >
					<div>
						<label><?php _e( 'Enable Multiple Extra Service Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'Enable/Disable multiple service quantity selection. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_enable_extra_service_qty" value="<?php echo esc_attr($rbfw_enable_extra_service_qty); ?>" <?php echo esc_attr(($rbfw_enable_extra_service_qty=='yes')?'checked':''); ?>>
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
					jQuery('input[name=rbfw_enable_md_type_item_qty]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
						}
					});
					jQuery('input[name=rbfw_enable_extra_service_qty]').click(function(){
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
		}

		new RBFW_Front_End_Display();
	}
