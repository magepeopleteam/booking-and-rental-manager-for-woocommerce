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
                                <?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $title ); ?>
                            </label>
                            <span><?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $description ); ?></span>
                        </div>
                    </section>
                <?php
            }

            public function rbfw_off_days_config( $post_id ) {
                $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
                $rbfw_size_data          = get_post_meta( $post_id, 'rbfw_size_data', true ) ? get_post_meta( $post_id, 'rbfw_size_data', true ) : [];
                $rbfw_pickup_data        = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
                $rbfw_dropoff_data       = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
                wp_nonce_field( 'rbfw_ticket_type_nonce', 'rbfw_ticket_type_nonce' );
                $hourly_rate             = get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_hourly_rate', true ) : '';
                $daily_rate              = get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_daily_rate', true ) : '';
                $rbfw_item_type          = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : 'bike_car_sd';
                $rbfw_enable_pick_point  = get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_pick_point', true ) : 'no';
                $rbfw_enable_dropoff_point  = get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_dropoff_point', true ) : 'no';
                $rbfw_enable_daywise_price  = get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daywise_price', true ) : 'no';
                $rbfw_enable_hourly_rate = get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_hourly_rate', true ) : 'no';
                $rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'yes';
                $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
                $rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true ) : [];
                $rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';


                $rbfw_item_stock_quantity = !empty(get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true )) ? get_post_meta( get_the_id(), 'rbfw_item_stock_quantity', true ) : 0;
                $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';

                $rbfw_sd_appointment_ondays_data = get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_ondays', true ) : [];
                $rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) : '';

                $rbfw_enable_start_end_date  = get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $post_id, 'rbfw_enable_start_end_date', true ) : 'yes';
                $rbfw_event_start_date  = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
                $rbfw_event_start_time  = get_post_meta( $post_id, 'rbfw_event_start_time', true ) ? get_post_meta( $post_id, 'rbfw_event_start_time', true ) : '';
                $rbfw_event_end_date  = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
                $rbfw_event_end_time  = get_post_meta( $post_id, 'rbfw_event_end_time', true ) ? get_post_meta( $post_id, 'rbfw_event_end_time', true ) : '';

                $rbfw_off_days  = get_post_meta( $post_id, 'rbfw_off_days', true ) ? get_post_meta( $post_id, 'rbfw_off_days', true ) : '';

                $off_day_array = $rbfw_off_days?explode(',', $rbfw_off_days):[];

                $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');


                $rbfw_offday_range  = get_post_meta( $post_id, 'rbfw_offday_range', true ) ? get_post_meta( $post_id, 'rbfw_offday_range', true ) : '';


                ?>


                <h2 class="h5 text-white bg-primary mb-1 rounded-top">
                    <?php echo ''.esc_html__( 'Off Day Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </h2>


                <div class='rbfw-item-type '>
                    <div class="rbfw_form_group" data-table="rbfw_item_type_table">
                        <div style=" <?php echo ($rbfw_item_type == 'appointment')?'display:none':'' ?>" class="component d-flex justify-content-start rbfw_off_days">
                            <label for=""><?php esc_html_e( 'Off Day', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                            <div class="groupCheckBox d-flex justify-content-between align-items-center ms-5">
                                <input type="hidden" name="rbfw_off_days" value="<?php echo $rbfw_off_days ?>">
                                <?php foreach ($days as $day){ ?>
                                    <label class="customCheckboxLabel ">
                                        <input style="margin-right:3px;" type="checkbox" <?php echo in_array($day,$off_day_array)?'checked':'' ?>  data-checked="<?php echo $day ?>">
                                        <span class="customCheckbox pe-2"><?php echo ucfirst($day) ?></span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>


                        <div class="form-table rbfw_item_type_table off_date_range">
                            <?php if(empty($rbfw_offday_range)){ ?>
                            <div class="off_date_range_child component d-flex justify-content-between">
                                <section class="d-flex justify-content-between w-50">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                        <div class="ms-5 d-flex justify-content-between align-items-center">
                                            <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                                        </div>
                                    </div>
                                </section>
                                <section class="ms-1 d-flex justify-content-between w-50">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                        <div class="ms-5 d-flex justify-content-between align-items-center">
                                            <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]"  class="rbfw_off_days_range" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <?php } else {  ?>
                                <?php foreach ($rbfw_offday_range as $single){ ?>
                                    <div class="off_date_range_child  component d-flex justify-content-between" >
                                        <section class="component d-flex justify-content-between w-50">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                                <div class=" d-flex justify-content-between align-items-center">
                                                    <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $single['from_date'] ); ?>" readonly>
                                                </div>
                                            </div>
                                        </section>
                                        <section class="component d-flex justify-content-between w-50">
                                            <div class="ms-1 d-flex justify-content-between align-items-center">
                                                <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                                <div class=" d-flex justify-content-between align-items-center">
                                                    <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]"  class="rbfw_off_days_range" value="<?php echo esc_attr( $single['to_date'] ); ?>" readonly>
                                                </div>
                                            </div>
                                        </section>
                                        <div class="component mp_event_remove_move">
                                            <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="off_date_range_content hidden">
                            <div class="off_date_range_child component d-flex justify-content-between">
                                <section class=" d-flex justify-content-between w-50">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                        <div class="ms-5 d-flex justify-content-between align-items-center">
                                            <input type="text" placeholder="YYYY-MM-DD"  class="rbfw_off_days_range rbfw_off_days_range_start" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                                        </div>
                                    </div>
                                </section>
                                <section class="ms-5 d-flex justify-content-between w-50">
                                    <div class="ms-5 d-flex justify-content-between align-items-center">
                                        <label for=""><?php esc_html_e( 'End Date:', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"></i></label>
                                        <div class="ms-5 d-flex justify-content-between align-items-center">
                                            <input type="text" placeholder="YYYY-MM-DD"   class="rbfw_off_days_range rbfw_off_days_range_end" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                                        </div>
                                    </div>
                                </section>
                                <div class="component mp_event_remove_move">
                                    <button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="rbfw_bike_car_sd_wrapper">
                    <section class="component d-flex flex-column justify-content-between align-items-start mb-2">
                        <p class="mt-2">
                            <button id="add-date-range-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add Another Range', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        </p>
                    </section>
                </div>

                <?php
            }

			public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#travel_off_days">
                    <?php $this->section_header(); ?>
                    <?php $this->panel_header('Off Day Settings','Off Day Settings'); ?>  
                    <?php $this->rbfw_off_days_config( $post_id ); ?>               
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
                    
                }
            }
        }
        new RBFW_Off_Day();
    }