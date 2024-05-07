<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Pricing')) {
        class RBFW_Pricing{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#travel_pricing"><i class="fa-solid fa-pager"></i><?php esc_html_e('Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure template Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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
            ?>
				<div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
					<?php $this->section_header(); ?>
                    <?php $this->panel_header('Template Settings','Template Settings'); ?>
					<section>
						<div>
							<label for="">
								<?php _e('Rent Types', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<span><?php _e('Select Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<?php  $rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : ['bike_car_sd']; ?>
						<?php $item_type = [
							'bike_car_sd' => 'Bike/Car for single day',
							'bike_car_md' => 'Bike/Car for multiple day',
							'resort' => 'Resort',
							'equipment' => 'Equipment',
							'dress' => 'Dress',
							'appointment' => 'Appointment',
							'others' => 'Others',
						]; ?>
						<select name="rbfw_item_type" id="rbfw_item_type">
							<?php foreach($item_type as $kay => $value): ?>
								<option <?php echo ($kay==$rbfw_item_type)?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
							<?php endforeach; ?>
						</select>
					</section>
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
                    $rbfw_item_type = isset( $_POST['rbfw_item_type'] ) ? rbfw_array_strip( $_POST['rbfw_item_type'] ) : [];
					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
                }
            }
		}
		new RBFW_Pricing();
	}