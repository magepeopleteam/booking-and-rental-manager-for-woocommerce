<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Date_Time')) {
        class RBFW_Date_Time{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_date_settings_meta_boxes"><i class="fa-solid fa-calendar-days"></i><?php esc_html_e('Date & Time', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Date Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure date.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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

			public function multiple_time_slot_select($post_id){
                $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
                $rdfw_available_time = get_post_meta($post_id,'rdfw_available_time',true) ? maybe_unserialize(get_post_meta($post_id, 'rdfw_available_time', true)) : [];
                ?>
                <div id="field-wrapper-rdfw_available_time" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rdfw_available_time">
					<select name="rdfw_available_time[]" id="rdfw_available_time" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
						<?php foreach($rbfw_time_slots as $kay => $value): ?>
							<option <?php echo (in_array($value,$rdfw_available_time))?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
						<?php endforeach; ?>
					</select>
            	</div>
                <?php
            }

			public function add_tabs_content( $post_id ) {
            ?>
				<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_date_settings_meta_boxes">
					<?php $this->section_header(); ?>
                    <?php $this->panel_header('Date & Time Settings','Here you can set Date & Time'); ?>
					<section>
						<div>
							<label>
								<?php echo esc_html__( 'Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<span><?php echo esc_html__('It enables/disables the time slot for Bike/Car Single Day and Appointment rent type.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<?php $rbfw_time_slot_switch = get_post_meta($post_id,'rbfw_time_slot_switch',true);?>
						<label class="switch">
							<input type="checkbox" name="rbfw_time_slot_switch" value="<?php echo esc_attr(($rbfw_time_slot_switch=='on')?$rbfw_time_slot_switch:'off'); ?>" <?php echo esc_attr(($rbfw_time_slot_switch=='on')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>
					</section>
					<!-- time slot -->
					<section>
						<div>
							<label>
								<?php _e( 'Available Time Slot', 'booking-and-rental-manager-for-woocommerce' ) ?>
							</label>
							<span><?php _e( 'Please select the availabe time slots', 'booking-and-rental-manager-for-woocommerce' ) ?></span>
						</div>
						<div class="w-50">
							<?php $this->multiple_time_slot_select($post_id); ?>
						</div>
					</section>
			 	</div>
				<script>
                        jQuery('input[name=rbfw_time_slot_switch]').click(function(){
                            var status = jQuery(this).val();
                            if(status == 'on') {
                                jQuery(this).val('off') 
                            }  
                            if(status == 'off') {
                                jQuery(this).val('on');  
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
                    $rbfw_time_slot = isset( $_POST['rbfw_time_slot_switch'] ) ? rbfw_array_strip( $_POST['rbfw_time_slot_switch'] ) : '';
                    $rdfw_available_time = isset( $_POST['rdfw_available_time'] ) ? rbfw_array_strip( $_POST['rdfw_available_time'] ) : [];
                    update_post_meta( $post_id, 'rbfw_time_slot_switch', $rbfw_time_slot );
                    update_post_meta( $post_id, 'rdfw_available_time', $rdfw_available_time );
                }
            }
		}
		new RBFW_Date_Time();
	}