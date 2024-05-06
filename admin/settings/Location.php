<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Location')) {
        class RBFW_Location{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_location_config"><i class="fa-solid fa-location-dot"></i><?php esc_html_e('Location', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

             public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Location Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure locatoin', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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

			public function pickup_location_config($post_id){
				$rbfw_enable_pick_point  = get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) ? get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) : 'yes';
			?>
			<section >
				<div>
					<label><?php _e( 'Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
					<span><?php esc_html_e( 'Turn Pick-up Location On/Off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</div>
				<label class="switch">
					<input type="checkbox" name="rbfw_enable_pick_point" value="<?php echo esc_attr($rbfw_enable_pick_point); ?>" <?php echo esc_attr(($rbfw_enable_pick_point=='yes')?'checked':''); ?>>
					<span class="slider round"></span>
				</label>
			</section>
			<section class="rbfw-pickup-location <?php echo esc_attr(($rbfw_enable_pick_point=='yes')?'show':'hide'); ?>" >
				<div>
					<div>
						<input type="text">
					</div>
					<div class="add-row-pickup-btn">
						<button id="add-row-pickup" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> Add New Pick-up Location</button>
					</div>
				</div>
			</section>
			<?php
			}

            public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_location_config">
					<?php $this->section_header(); ?>
                    <?php $this->panel_header('Pick-up Location Configuration','Here you can set location.'); ?>

					<?php
						do_action( 'rbfw_location_config_before', $post_id );

						$this->pickup_location_config($post_id);

						do_action( 'rbfw_location_config_after', $post_id );
					?>
				</div>
				<script>
					jQuery('input[name=rbfw_enable_pick_point]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw-pickup-location').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw-pickup-location').slideDown().removeClass('hide').addClass('show');
						}
					});
				</script>
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
                    $rbfw_enable_pick_point  = isset( $_POST['rbfw_enable_pick_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_pick_point'] ) : 'no';
					
					update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );
 
                }
            }
		}
		new RBFW_Location();
	}