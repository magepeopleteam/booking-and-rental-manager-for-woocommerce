<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Date_Time' ) ) {
		class RBFW_Date_Time {
			public function __construct() {
				//add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				//add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				//add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_particular_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu($rbfw_id) {
                $rbfw_item_type         = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				?>
                <!--<li data-target-tabs="#rbfw_date_settings_meta_boxes" <?php /*echo ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' )?'style="display:none"':'' */?>>
                    <i class="fas fa-calendar-days"></i><?php /*esc_html_e( 'Date & Time', 'booking-and-rental-manager-for-woocommerce' ); */?>
                </li>-->
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Date Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure date.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label><?php echo esc_html( $title ); ?></label>
                        <p><?php echo wp_kses_post( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function multiple_time_slot_select( $post_id ) {
				$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
				global $RBFW_Timeslots_Page;
				$rbfw_time_slots = $RBFW_Timeslots_Page->rbfw_format_time_slot( $rbfw_time_slots );
				asort( $rbfw_time_slots );
				$rdfw_available_time        = get_post_meta( $post_id, 'rdfw_available_time', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rdfw_available_time', true ) ) : [];

                //echo '<pre>';print_r($rdfw_available_time);echo '<pre>';
                $rdfw_available_time_update = [];
				foreach ( $rdfw_available_time as $single ) {
					//$rdfw_available_time_update[] = gmdate( 'H:i', strtotime( $single ) );
				}
				?>
                <!--<div id="field-wrapper-rdfw_available_time" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rdfw_available_time">
                    <select name="rdfw_available_time_old[]" id="rdfw_available_time" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
						<?php /*foreach ( $rbfw_time_slots as $key => $value ): */?>
                            <option <?php /*echo esc_attr( in_array( gmdate( 'H:i', strtotime( $value ) ), $rdfw_available_time_update ) ) ? 'selected' : '' */?> value="<?php /*echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); */?>"> <?php /*echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); */?> </option>
						<?php /*endforeach; */?>
                    </select>
                </div>-->
				<?php
			}

			public function multiple_time_slot_select_for_particular_date( $rbfw_time_slots, $available_times, $index, $post_id ) {
				global $RBFW_Timeslots_Page;
				$rbfw_time_slots = $RBFW_Timeslots_Page->rbfw_format_time_slot( $rbfw_time_slots );
				asort( $rbfw_time_slots );
				$rdfw_available_time_update = [];
				foreach ( $available_times as $single ) {
					$rdfw_available_time_update[] = gmdate( 'H:i', strtotime( $single ) );
				}
				?>
                <select name="rbfw_particulars[<?php echo esc_attr( $index ); ?>][available_time][]" multiple class="select2-hidden-accessible">
					<?php foreach ( $rbfw_time_slots as $key => $value ): ?>
						<?php if ( get_the_title( $post_id ) == 'Auto Draft' ) { ?>
                            <option selected value="<?php echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); ?>"> <?php echo esc_attr( $key ); ?> </option>
						<?php } else { ?>
                            <option <?php echo esc_attr( in_array( gmdate( 'H:i', strtotime( $value ) ), $rdfw_available_time_update ) ) ? 'selected' : '' ?> value="<?php echo esc_attr( gmdate( 'H:i', strtotime( $value ) ) ); ?>"> <?php echo esc_html( gmdate( 'H:i', strtotime( $value ) ) ); ?> </option>
						<?php } ?>
					<?php endforeach; ?>
                </select>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_date_settings_meta_boxes">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Date & Time Settings', 'Here you can set Date & Time' ); ?>
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php echo esc_html__( 'It enables/disables the time slot for Bike/Car Single Day and Appointment rent type.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
						<?php $rbfw_time_slot_switch = get_post_meta( $post_id, 'rbfw_time_slot_switch', true ) ? get_post_meta( $post_id, 'rbfw_time_slot_switch', true ) : 'off'; ?>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_time_slot_switch" value="<?php echo esc_attr( ( $rbfw_time_slot_switch == 'on' ) ? $rbfw_time_slot_switch : 'off' ); ?>" <?php echo esc_attr( ( $rbfw_time_slot_switch == 'on' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <!-- time slot -->
                    <div class="available-time-slot <?php echo esc_attr( ( $rbfw_time_slot_switch == 'on' ) ? 'show' : 'hide' ); ?>">
                        <section>
                            <div>
                                <label>
									<?php esc_html_e( 'Available Time Slot', 'booking-and-rental-manager-for-woocommerce' ) ?>
                                </label>
                                <p><?php esc_html_e( 'Please select the availabe time slots', 'booking-and-rental-manager-for-woocommerce' ) ?></p>
                            </div>
                            <div class="w-70">
								<?php $this->multiple_time_slot_select( $post_id ); ?>
                            </div>
                        </section>
                    </div>
                </div>
                <script>
                    jQuery(function ($) {
                        "use strict";
                        // Ensure 'on' and 'off' values are safe to use in JavaScript (though they are static here, it's a good practice for dynamic values)
                        $('input[name=rbfw_time_slot_switch]').click(function () {
                            var status = $(this).val();
                            if (status === 'on') {
                                $(this).val('off');
                                $('.available-time-slot').slideUp().removeClass('show').addClass('hide');
                            }
                            if (status === 'off') {
                                $(this).val('on');
                                $('.available-time-slot').slideDown().removeClass('hide').addClass('show');
                            }
                        });
                        // Ensure values like 'yes' and 'no' are safe when used in JavaScript
                        $('input[name=rbfw_enable_start_end_date]').click(function () {
                            var status = $(this).val();
                            $('.rbfw_enable_start_end_date_ggg').val(status);
                            if (status === 'yes') {
                                $(this).val('no');
                                $('.rbfw-fixed-date').slideUp().removeClass('show').addClass('hide');
                            }
                            if (status === 'no') {
                                $(this).val('yes');
                                $('.rbfw-fixed-date').slideDown().removeClass('hide').addClass('show');
                            }
                        });
                    });
                </script>
				<?php
			}

			public function add_particular_tabs_content( $post_id ) {
				$particulars_data = get_post_meta( $post_id, 'rbfw_particulars_data', true );
				$particulars_data = ! empty( $particulars_data ) && is_array( $particulars_data ) ? $particulars_data : [ [] ];
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_date_settings_meta_boxes">
					<?php $this->panel_header( 'Particular Settings', 'Here you can set Particulars' ); ?>
                    <section>
                        <div>
                            <label>
								<?php echo esc_html__( 'Particular date time slots', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p><?php echo esc_html__( 'It enables/disables the particulars for selection.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
						<?php $rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off'; ?>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_particular_switch" value="<?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? $rbfw_particular_switch : 'off' ); ?>" <?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'checked' : '' ); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                    <!-- Multiple Particular Section -->
                    <div class="available-particular <?php echo esc_attr( ( $rbfw_particular_switch == 'on' ) ? 'show' : 'hide' ); ?>">
                        <section>
                            <table class="form-table" id="particulars-table">
                                <tr>
                                    <th><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Available Time Slots', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                </tr>
								<?php foreach ( $particulars_data as $index => $particular ): ?>
                                    <tr class="particular-row">
                                        <td>
                                            <input type="text" name="rbfw_particulars[<?php echo esc_attr( $index ); ?>][start_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['start_date'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="rbfw_particulars[<?php echo esc_attr( $index ); ?>][end_date]" class="rbfw_days_range" value="<?php echo esc_attr( $particular['end_date'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <div class="w-100">
												<?php
													$available_times = isset( $particular['available_time'] ) ? $particular['available_time'] : [];
													$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
													$this->multiple_time_slot_select_for_particular_date( $rbfw_time_slots, $available_times, $index, $post_id );
												?>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="remove-row button"><?php echo esc_html__( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                            </table>
                        </section>
                        <button type="button" id="add-particular-row" class="button ss"><?php echo esc_html__( 'Add Another', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                    </div>
                </div>

				<?php
			}

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$rbfw_time_slot      = isset( $_POST['rbfw_time_slot_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_time_slot_switch'] ) ) : 'off';
					$rdfw_available_time = isset( $_POST['rdfw_available_time'] ) && is_array( $_POST['rdfw_available_time'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['rdfw_available_time'] ) ) : [];
					//echo '<pre>';print_r($rdfw_available_time );echo '</pre>';
					// echo '<pre>';print_r($_POST['rdfw_available_time'] );echo '</pre>';die();
					$rbfw_enable_start_end_date = isset( $_POST['rbfw_enable_start_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_start_end_date'] ) ) : 'yes';
					$rbfw_event_start_date      = isset( $_POST['rbfw_event_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_event_start_date'] ) ) : '';
					$rbfw_event_start_time      = isset( $_POST['rbfw_event_start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_event_start_time'] ) ) : '';
					$rbfw_event_end_date        = isset( $_POST['rbfw_event_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_event_end_date'] ) ) : '';
					$rbfw_event_end_time        = isset( $_POST['rbfw_event_end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_event_end_time'] ) ) : '';



					update_post_meta( $post_id, 'rbfw_time_slot_switch', $rbfw_time_slot );
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