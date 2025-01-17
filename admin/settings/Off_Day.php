<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Off_Day')) {
        class RBFW_Off_Day{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#travel_off_days"><i class="fa-regular fa-calendar-xmark"></i><?php esc_html_e('Off Days', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Off Day Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure off day Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
                <?php
            }


			public function panel_header($title,$description){
                ?>
                    <section class="bg-light mt-5">
                        <div>
                            <label>
                                <?php echo esc_html($title ); ?>
                            </label>
                            <span><?php echo esc_html($description ); ?></span>
                        </div>
                    </section>
                <?php
            }

            public function rbfw_off_days_config( $post_id ) {
                $rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
                $rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
                $rbfw_offday_range  = get_post_meta( $post_id, 'rbfw_offday_range', true ) ? get_post_meta( $post_id, 'rbfw_offday_range', true ) : [];
                ?>
                <div class="form-table rbfw_item_type_table off_date_range">
                    <?php foreach ($rbfw_offday_range as $single){ ?>
                        <section class="off_date_range_child" >
                            <div class="d-flex justify-content-between w-40">
                                <label for=""><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $single['from_date'] ); ?>" readonly>
                            </div>
                            <div class="d-flex justify-content-between w-40 ms-5">
                                <label for=""><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]"  class="rbfw_off_days_range" value="<?php echo esc_attr( $single['to_date'] ); ?>" readonly>
                            </div>
                            <div class="component mp_event_remove_move">
                                <button class="button remove-row ms-2"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </section>
                    <?php } ?>
                </div>

                <div class="off_date_range_content hidden">
                    <section class="off_date_range_child">
                        <div class="d-flex justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD"  class="rbfw_off_days_range rbfw_off_days_range_start" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                        </div>
                        <div class="d-flex ms-5 justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD"   class="rbfw_off_days_range rbfw_off_days_range_end" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                        </div>
                        <div class="component mp_event_remove_move">
                            <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </section>
                </div>
                <div class="d-flex justify-content-center mt-2">
                    <button id="add-date-range-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Another Range', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                </div>

                <?php
            }

			public function add_tabs_content( $post_id ) {
                $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
                $rbfw_off_days  = get_post_meta( $post_id, 'rbfw_off_days', true ) ? get_post_meta( $post_id, 'rbfw_off_days', true ) : '';
                $rbfw_item_type = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : 'bike_car_sd'; 
                $off_day_array = $rbfw_off_days?explode(',', $rbfw_off_days):[];    
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#travel_off_days">
                    <?php $this->section_header(); ?>

                    <?php $this->panel_header('Off Day Settings','Off Day Settings'); ?> 
                    <section class="rbfw_off_days justify-content-center">
                        <div class="groupCheckBox">
                            <input type="hidden" name="rbfw_off_days" value="<?php echo esc_attr($rbfw_off_days) ?>">
                            <?php foreach ($days as $day){ ?>
                                <label class="customCheckboxLabel">
    <input type="checkbox" <?php echo in_array($day, $off_day_array) ? 'checked' : ''; ?> data-checked="<?php echo esc_attr($day); ?>">
    <span class="customCheckbox"><?php echo esc_html(ucfirst($day)); ?></span>
</label>

                            <?php } ?>
                        </div>
                    </section>
                    <?php $this->panel_header('Off Date Settings','Off Date Settings'); ?> 
                    <?php $this->rbfw_off_days_config( $post_id ); ?>               
                </div>
                
            <?php
            }

            public function settings_save($post_id) {
	            
	            if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) ) {
		            return;
	            }
	            $nonce = sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) );
	            if ( ! wp_verify_nonce( $nonce, 'rbfw_ticket_type_nonce' ) ) {
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

	                $rbfw_off_days = isset( $input_data_sabitized['rbfw_off_days'] ) ? $input_data_sabitized['rbfw_off_days'] : '';
	                $off_days_start = isset( $input_data_sabitized['off_days_start'] ) ? $input_data_sabitized['off_days_start']  : '';
	                $off_days_end = isset( $input_data_sabitized['off_days_end'] ) ? $input_data_sabitized['off_days_end'] : '';
	                
	                update_post_meta( $post_id, 'rbfw_off_days', $rbfw_off_days );

                    $off_schedules = [];
                    $from_dates = $off_days_start;
                    $to_dates = $off_days_end;

                    if(is_countable($from_dates)){
                        if ( sizeof($from_dates) > 0) {
                            foreach ($from_dates as $key => $from_date) {
                                if ($from_date && $to_dates[$key]) {
                                    $off_schedules[] = [
                                        'from_date' => $from_date,
                                        'to_date' => $to_dates[$key],
                                    ];
                                }
                            }
                        }
                        else{
                            $from_dates = [];
                        }
                    }
                    update_post_meta($post_id, 'rbfw_offday_range', $off_schedules);
                }
            }
        }
        new RBFW_Off_Day();
    }