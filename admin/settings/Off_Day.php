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
                            <input type="hidden" name="rbfw_off_days" value="<?php echo $rbfw_off_days ?>">
                            <?php foreach ($days as $day){ ?>
                                <label class="customCheckboxLabel ">
                                    <input type="checkbox" <?php echo in_array($day,$off_day_array)?'checked':'' ?>  data-checked="<?php echo $day ?>">
                                    <span class="customCheckbox"><?php echo ucfirst($day) ?></span>
                                </label>
                            <?php } ?>
                        </div>
                    </section>
                    <?php $this->panel_header('Off Date Settings','Off Date Settings'); ?> 
                    <?php $this->rbfw_off_days_config( $post_id ); ?> 
                    <?php  $this->panel_header('Particular Dates with Time Slots Off Booking', 'Add specific dates and their time slots.'); ?> 
                    <?php $this->add_particular_time_slots_section($post_id); ?>             
                </div>
                
            <?php
            }

            public function add_particular_time_slots_section($post_id) {
    $particular_dates = get_post_meta($post_id, 'rbfw_particular_dates', true) ?: [];

    ?>
     <div class="particular-dates-section">
        <h3><?php esc_html_e('Particular Dates with Time Slots Off Booking', 'booking-and-rental-manager-for-woocommerce'); ?></h3>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                    <th><?php esc_html_e('Start & End Time', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                    
                    <th><?php esc_html_e('Action', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                </tr>
            </thead>
            <tbody class="particular-date-list">
                <?php foreach ($particular_dates as $index => $date_data) { ?>
                    <tr class="date-item">
                        <td>
                            <input type="date" name="particular_dates[<?php echo $index; ?>][date]" value="<?php echo esc_attr($date_data['date']); ?>" required>
                        </td>
                        <td>
                            <div class="time-slots">
                                <?php foreach ($date_data['time_slots'] as $time_index => $slot) { ?>
                                    <div class="time-slot-item">
                                        <input type="time" name="particular_dates[<?php echo $index; ?>][time_slots][<?php echo $time_index; ?>][start]" value="<?php echo esc_attr($slot['start']); ?>" required>
                                        <input type="time" name="particular_dates[<?php echo $index; ?>][time_slots][<?php echo $time_index; ?>][end]" value="<?php echo esc_attr($slot['end']); ?>" required>
                                        <button class="button remove-time-slot"><?php esc_html_e('Remove', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                        <td>
                        <button class="button add-time-slot"><?php esc_html_e('Add Time Slot', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                            <button class="button remove-date"><?php esc_html_e('Remove Date', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button class="button add-particular-date"><?php esc_html_e('Add', 'booking-and-rental-manager-for-woocommerce'); ?></button>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateListContainer = document.querySelector('.particular-date-list');
        const addParticularDateButton = document.querySelector('.add-particular-date');

        addParticularDateButton.addEventListener('click', function (e) {
            e.preventDefault();
            const newIndex = dateListContainer.children.length; // Use the children length to index
            const newDateHTML = `
                <div class="date-item">
                    <input type="date" name="particular_dates[${newIndex}][date]" required>
                    <div class="time-slots">
                        <div class="time-slot-item">
                            <input type="time" name="particular_dates[${newIndex}][time_slots][0][start]" required>
                            <input type="time" name="particular_dates[${newIndex}][time_slots][0][end]" required>
                            <button class="button remove-time-slot"><?php esc_html_e('Remove Time Slot', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                        </div>
                    </div>
                    <button class="button add-time-slot">Add Time Slot</button>
                    <button class="button remove-date">Remove Date</button>
                </div>
            `;
            dateListContainer.insertAdjacentHTML('beforeend', newDateHTML);
        });

        dateListContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-time-slot')) {
                e.preventDefault();
                const dateItem = e.target.closest('.date-item');
                const timeSlotsContainer = dateItem.querySelector('.time-slots');
                const timeSlotsCount = timeSlotsContainer.children.length; // Get current number of time slots per date
                const newTimeSlotHTML = `
                    <div class="time-slot-item">
                        <input type="time" name="particular_dates[${Array.from(dateListContainer.children).indexOf(dateItem)}][time_slots][${timeSlotsCount}][start]" required>
                        <input type="time" name="particular_dates[${Array.from(dateListContainer.children).indexOf(dateItem)}][time_slots][${timeSlotsCount}][end]" required>
                        <button class="button remove-time-slot"><?php esc_html_e('Remove Time Slot', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                    </div>
                `;
                timeSlotsContainer.insertAdjacentHTML('beforeend', newTimeSlotHTML);
            }

            if (e.target.classList.contains('remove-time-slot')) {
                e.target.closest('.time-slot-item').remove();
            }

            if (e.target.classList.contains('remove-date')) {
                e.target.closest('.date-item').remove();
            }
        });
    });
</script>

    <?php 
}


public function settings_save($post_id) {
    if (!isset($_POST['rbfw_ticket_type_nonce']) || !wp_verify_nonce($_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) == 'rbfw_item') {
        // Saving off days
        $rbfw_off_days = isset($_POST['rbfw_off_days']) ? rbfw_array_strip($_POST['rbfw_off_days']) : '';
        update_post_meta($post_id, 'rbfw_off_days', $rbfw_off_days);

        // Saving the off day range
        $off_days_start = isset($_POST['off_days_start']) ? rbfw_array_strip($_POST['off_days_start']) : [];
        $off_days_end = isset($_POST['off_days_end']) ? rbfw_array_strip($_POST['off_days_end']) : [];
        
        // prepare the off day schedule
        $off_schedules = [];
        foreach ($off_days_start as $key => $from_date) {
            if ($from_date && isset($off_days_end[$key])) {
                $off_schedules[] = [
                    'from_date' => $from_date,
                    'to_date' => $off_days_end[$key],
                ];
            }
        }
        update_post_meta($post_id, 'rbfw_offday_range', $off_schedules);

        // Saving particular dates with time slots
        $particular_dates = isset($_POST['particular_dates']) ? $_POST['particular_dates'] : [];
        $formatted_dates = [];
        
        foreach ($particular_dates as $date_data) {
            $date = isset($date_data['date']) ? sanitize_text_field($date_data['date']) : '';
            if ($date) {
                $time_slots = array_map(function($slot) {
                    return [
                        'start' => sanitize_text_field($slot['start']),
                        'end' => sanitize_text_field($slot['end']),
                    ];
                }, $date_data['time_slots'] ?? []);
                
                $formatted_dates[] = [
                    'date' => $date,
                    'time_slots' => $time_slots,
                ];
            }
        }
        
        update_post_meta($post_id, 'rbfw_particular_dates', $formatted_dates);
    }
}
        }
        new RBFW_Off_Day();
    }