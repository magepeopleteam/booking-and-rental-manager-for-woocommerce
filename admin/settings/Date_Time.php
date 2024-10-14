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
            public function regular_fixed_date($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';

				$rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
                

                $rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
				$rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
				$rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
				$rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';
				$rbfw_item_stock_quantity = !empty(get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true )) ? get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true ) : 0;
				
				?>
				<div class="regular_fixed_date <?php echo esc_attr(($rbfw_item_type=='bike_car_sd')?'hide':'show'); ?>">
					<section>
						<div>
							<label>
								<?php esc_html_e( 'Rent Specific day', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<span>
								<?php _e('with this option you can offer any item to rent specific day.', 'booking-and-rental-manager-for-woocommerce'); ?>
							</span>
						</div>
						
						<label class="switch">
							<input type="checkbox" name="rbfw_enable_start_end_date" value="<?php echo esc_attr(($rbfw_enable_start_end_date=='yes')?'no':'yes'); ?>" <?php echo esc_attr(($rbfw_enable_start_end_date=='no')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>


					</section>

					<div class="rbfw-fixed-date <?php echo esc_attr(($rbfw_enable_start_end_date=='no')?'show':'hide'); ?>">
						
							<section>
								<div class="w-50 d-flex justify-content-between align-items-center">
									<label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<div class=" d-flex justify-content-between align-items-center">
										<input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_start_date" id="rbfw_event_start_date" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" >
									</div>
								</div>
								<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
									<label for=""><?php esc_html_e( 'Start Time:', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<div class=" d-flex justify-content-between align-items-center">
										<input type="time" name="rbfw_event_start_time" id="rbfw_event_start_time" value="<?php echo esc_attr( $rbfw_event_start_time ); ?>">

									</div>
								</div>
							</section>

							<section>
								<div class="w-50 d-flex justify-content-between align-items-center">
									<label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<div class=" d-flex justify-content-between align-items-center">
										<input type="text" placeholder="YYYY-MM-DD" name="rbfw_event_end_date" id="rbfw_event_end_date" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" >
									</div>
								</div>
								<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
									<label for=""><?php esc_html_e( 'End Time:', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<div class=" d-flex justify-content-between align-items-center">
										<input type="time" name="rbfw_event_end_time" id="rbfw_event_end_time" value="<?php echo esc_attr( $rbfw_event_end_time ); ?>">
									</div>
								</div>
							</section>
						
					</div>
				</div>

                <input type="hidden" name="rbfw_enable_start_end_date" class="rbfw_enable_start_end_date_ggg" value="<?php echo $rbfw_enable_start_end_date ?>">


                <div class='rbfw-item-type '>
					<div class="rbfw_form_group" data-table="rbfw_item_type_table">
						<table class="form-table rbfw_item_type_table">
							
							<?php echo do_action('rbfw_after_rent_item_type_table_row'); ?>
						</table>
					</div>
				</div>
				<?php
			}
			public function multiple_time_slot_select($post_id){
                $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];

                global  $RBFW_Timeslots_Page;

                $rbfw_time_slots = $RBFW_Timeslots_Page->rbfw_format_time_slot($rbfw_time_slots);

                asort($rbfw_time_slots);




                $rdfw_available_time = get_post_meta($post_id,'rdfw_available_time',true) ? maybe_unserialize(get_post_meta($post_id, 'rdfw_available_time', true)) : [];



                $rdfw_available_time_update = [];

                foreach ($rdfw_available_time as $single){
                    if(strlen($single)==7){
                        $rdfw_available_time_update[] = '0'.$single;
                    }else{
                        $rdfw_available_time_update[] = $single;
                    }
                }


                ?>
                <div id="field-wrapper-rdfw_available_time" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rdfw_available_time">
                    <select name="rdfw_available_time[]" id="rdfw_available_time" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                        <?php foreach($rbfw_time_slots as $key => $value): ?>
                            <?php if(get_the_title( $post_id ) == 'Auto Draft'){ ?>
                                <option selected value="<?php echo date('h:i A', strtotime($value)); ?>"> <?php echo $key; ?> </option>
                            <?php }else{ ?>
                                <option <?php echo (in_array(date('h:i A', strtotime($value)),$rdfw_available_time_update))?'selected':'' ?> value="<?php echo date('h:i A', strtotime($value)); ?>"> <?php echo $key; ?> </option>
                            <?php } ?>

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
						<?php $rbfw_time_slot_switch = get_post_meta($post_id,'rbfw_time_slot_switch',true)? get_post_meta($post_id,'rbfw_time_slot_switch',true) : 'off';?>
						<label class="switch">
							<input type="checkbox" name="rbfw_time_slot_switch" value="<?php echo esc_attr(($rbfw_time_slot_switch=='on')?$rbfw_time_slot_switch:'off'); ?>" <?php echo esc_attr(($rbfw_time_slot_switch=='on')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>
					</section>

					<!-- time slot -->
					<div class="available-time-slot <?php echo esc_attr(($rbfw_time_slot_switch=='on')?'show':'hide'); ?>">
						<section>
							<div >
								<label>
									<?php _e( 'Available Time Slot', 'booking-and-rental-manager-for-woocommerce' ) ?>
								</label>
								<span><?php _e( 'Please select the availabe time slots', 'booking-and-rental-manager-for-woocommerce' ) ?></span>
							</div>


							<div class="w-70">
								<?php $this->multiple_time_slot_select($post_id); ?>
							</div>
						</section>
					</div>
                    <?php $this->regular_fixed_date($post_id); ?>
			 	</div>
				<script>
                    jQuery('input[name=rbfw_time_slot_switch]').click(function(){
                        var status = jQuery(this).val();
                        if(status == 'on') {
                            jQuery(this).val('off');
							jQuery('.available-time-slot').slideUp().removeClass('show').addClass('hide');
                        }  
                        if(status == 'off') {
                            jQuery(this).val('on'); 
							jQuery('.available-time-slot').slideDown().removeClass('hide').addClass('show'); 
                        }
                    });
                    jQuery('input[name=rbfw_enable_start_end_date]').click(function(){
                        var status = jQuery(this).val();

                        jQuery('.rbfw_enable_start_end_date_ggg').val(status);
                        if(status == 'yes') {
                            jQuery(this).val('no');
                            jQuery('.rbfw-fixed-date').slideUp().removeClass('show').addClass('hide'); 
                        }  
                        if(status == 'no') {
                            jQuery(this).val('yes');
                            jQuery('.rbfw-fixed-date').slideDown().removeClass('hide').addClass('show');  
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
                    $rbfw_time_slot = isset( $_POST['rbfw_time_slot_switch'] ) ? rbfw_array_strip( $_POST['rbfw_time_slot_switch'] ) : 'off';

                    $rdfw_available_time = isset( $_POST['rdfw_available_time'] ) ? rbfw_array_strip( $_POST['rdfw_available_time'] ) : [];

            /*        $rbfw_enable_start_end_date = 'yes';
                    if(isset( $_POST['rbfw_enable_start_end_date'] )){
                          if(rbfw_array_strip( $_POST['rbfw_enable_start_end_date'])=='yes'){
                              $rbfw_enable_start_end_date = 'no';
                          }
                    }*/

                    $rbfw_enable_start_end_date  = isset( $_POST['rbfw_enable_start_end_date'] ) ? rbfw_array_strip( $_POST['rbfw_enable_start_end_date'] ) : 'yes';


                    $rbfw_event_start_date  = isset( $_POST['rbfw_event_start_date'] ) ? rbfw_array_strip( $_POST['rbfw_event_start_date'] ) : '';
       	 			$rbfw_event_start_time  = isset( $_POST['rbfw_event_start_time'] ) ? rbfw_array_strip( $_POST['rbfw_event_start_time'] ) : '';
					$rbfw_event_end_date  = isset( $_POST['rbfw_event_end_date'] ) ? rbfw_array_strip( $_POST['rbfw_event_end_date'] ) : '';
					$rbfw_event_end_time  = isset( $_POST['rbfw_event_end_time'] ) ? rbfw_array_strip( $_POST['rbfw_event_end_time'] ) : '';
				
					update_post_meta( $post_id, 'rbfw_time_slot_switch', $rbfw_time_slot );
                    update_post_meta( $post_id, 'rdfw_available_time', $rdfw_available_time );
					update_post_meta( $post_id, 'rbfw_enable_start_end_date', $rbfw_enable_start_end_date );
					update_post_meta( $post_id, 'rbfw_event_start_date', $rbfw_event_start_date );
					update_post_meta( $post_id, 'rbfw_event_start_time', $rbfw_event_start_time );
					update_post_meta( $post_id, 'rbfw_event_end_date', $rbfw_event_end_date );
        			update_post_meta( $post_id, 'rbfw_event_end_time', $rbfw_event_end_time );
                }
            }
		}
		new RBFW_Date_Time();
	}