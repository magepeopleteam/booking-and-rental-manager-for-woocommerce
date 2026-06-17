<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Off_Day' ) ) {
		class RBFW_Off_Day {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#travel_off_days"><i class="fa-regular fa-calendar-xmark"></i><?php esc_html_e( 'Off Days', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Off Day Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure off day Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php echo esc_html( $title ); ?>
                        </label>
                        <p><?php echo esc_html( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function rbfw_off_days_config( $post_id ) {
				$rbfw_event_start_date = get_post_meta( $post_id, 'rbfw_event_start_date', true ) ? get_post_meta( $post_id, 'rbfw_event_start_date', true ) : '';
				$rbfw_event_end_date   = get_post_meta( $post_id, 'rbfw_event_end_date', true ) ? get_post_meta( $post_id, 'rbfw_event_end_date', true ) : '';
				$rbfw_offday_range     = get_post_meta( $post_id, 'rbfw_offday_range', true ) ? get_post_meta( $post_id, 'rbfw_offday_range', true ) : [];
				?>
                <div class="form-table rbfw_item_type_table off_date_range">
					<?php foreach ( $rbfw_offday_range as $single ) { ?>
                        <section class="off_date_range_child">
                            <div class="d-flex justify-content-between w-40">
                                <label for=""><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $single['from_date'] ); ?>" readonly>
                            </div>
                            <div class="d-flex justify-content-between w-40 ms-5">
                                <label for=""><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                                <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]" class="rbfw_off_days_range" value="<?php echo esc_attr( $single['to_date'] ); ?>" readonly>
                            </div>
                            <div class="component mp_event_remove_move">
                                <button class="button remove-row-off-days ms-2"><i class="fas fa-trash-can"></i></button>
                            </div>
                        </section>
					<?php } ?>
                </div>
				<?php if ( empty( $rbfw_offday_range ) ) : ?>
                <div class="off_date_range_remove">
                    <section class="off_date_range_child">
                        <div class="d-flex justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD" name="off_days_start[]" class="rbfw_off_days_range rbfw_off_days_range_start" value="" readonly>
                        </div>
                        <div class="d-flex ms-5 justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD" name="off_days_end[]" class="rbfw_off_days_range rbfw_off_days_range_end" value="" readonly>
                        </div>
                        <div class="component mp_event_remove_move">
                            <button class="button remove-row-off-days"><i class="fas fa-trash-can"></i></button>
                        </div>
                    </section>
                </div>
				<?php endif; ?>
                <div class="off_date_range_content hidden">
                    <section class="off_date_range_child">
                        <div class="d-flex justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD" class="rbfw_off_days_range rbfw_off_days_range_start" value="<?php echo esc_attr( $rbfw_event_start_date ); ?>" readonly>
                        </div>
                        <div class="d-flex ms-5 justify-content-between w-40">
                            <label for=""><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?> </label>
                            <input type="text" placeholder="YYYY-MM-DD" class="rbfw_off_days_range rbfw_off_days_range_end" value="<?php echo esc_attr( $rbfw_event_end_date ); ?>" readonly>
                        </div>
                        <div class="component mp_event_remove_move">
                            <button class="button remove-row-off-days"><i class="fas fa-trash-can"></i></button>
                        </div>
                    </section>
                </div>
                <div class="d-flex justify-content-center mt-2">
                    <button id="add-date-range-row" class="ppof-button"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add Another Range', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                </div>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				$days           = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
				$rbfw_off_days  = get_post_meta( $post_id, 'rbfw_off_days', true ) ? get_post_meta( $post_id, 'rbfw_off_days', true ) : '';
				$rbfw_item_type = get_post_meta( get_the_id(), 'rbfw_item_type', true ) ? get_post_meta( get_the_id(), 'rbfw_item_type', true ) : 'bike_car_sd';
				$off_day_array  = $rbfw_off_days ? explode( ',', $rbfw_off_days ) : [];

                $rbfw_buffer_time  = get_post_meta( $post_id, 'rbfw_buffer_time', true ) ? get_post_meta( $post_id, 'rbfw_buffer_time', true ) : '';

                $rbfw_buffer_time_after  = get_post_meta( $post_id, 'rbfw_buffer_time_after', true ) ? get_post_meta( $post_id, 'rbfw_buffer_time_after', true ) : 0;

                ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#travel_off_days">
					<?php $this->section_header(); ?>

					<?php $this->panel_header( 'Off Day Settings', 'Off Day Settings' ); ?>

                    <section class="rbfw_off_days justify-content-center">
                        <div class="groupCheckBox">
                            <input type="hidden" name="rbfw_off_days" value="<?php echo esc_attr( $rbfw_off_days ) ?>">
							<?php foreach ( $days as $day ) { ?>
                                <label class="customCheckboxLabel">
                                    <input type="checkbox" <?php echo in_array( $day, $off_day_array ) ? 'checked' : ''; ?> data-checked="<?php echo esc_attr( $day ); ?>">
                                    <span class="customCheckbox"><?php echo esc_html( ucfirst( $day ) ); ?></span>
                                </label>
							<?php } ?>
                        </div>
                    </section>

                    <section>
                        <div>
                            <label>
                                <?php esc_html_e( 'Buffer Time Before', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p>
                                <?php esc_html_e( 'Buffer Time Before (Hours)', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </p>
                        </div>
                        <div class="item_stock_quantity">
                            <input type="number" name="rbfw_buffer_time" id="rbfw_item_stock_quantity" value="<?php echo esc_attr( $rbfw_buffer_time ); ?>">
                        </div>
                    </section>

                    <section>
                        <div>
                            <label>
                                <?php esc_html_e( 'Buffer Time After', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <p>
                                <?php esc_html_e( 'Buffer Time After (Hours)', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </p>
                        </div>
                        <div class="item_stock_quantity">
                            <input type="number" name="rbfw_buffer_time_after" id="rbfw_buffer_time_after" value="<?php echo esc_attr( $rbfw_buffer_time_after ); ?>">
                        </div>
                    </section>

					<?php $this->panel_header( 'Off Date Settings', 'Off Date Settings' ); ?>
					<?php $this->rbfw_off_days_config( $post_id ); ?>
                </div>
				<?php
			}

			public static function render_for_modern_editor( int $post_id ): void {
			$days                   = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
			$rbfw_off_days          = get_post_meta( $post_id, 'rbfw_off_days', true ) ?: '';
			$off_day_array          = $rbfw_off_days ? explode( ',', $rbfw_off_days ) : [];
			$rbfw_buffer_time       = get_post_meta( $post_id, 'rbfw_buffer_time', true ) ?: '';
			$rbfw_buffer_time_after = get_post_meta( $post_id, 'rbfw_buffer_time_after', true ) ?: 0;
			$rbfw_offday_range      = get_post_meta( $post_id, 'rbfw_offday_range', true ) ?: [];
			?>

			<!-- Off Day Settings -->
			<div class="rbfw-me-card">
				<div class="rbfw-me-card__head">
					<h2><?php esc_html_e( 'Off Day Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					<p><?php esc_html_e( 'Select the days that are unavailable for booking.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
				<div class="rbfw-me-card__body">
					<div class="rbfw-me-offday-days">
						<input type="hidden" name="rbfw_off_days" class="rbfw-me-offday-hidden" value="<?php echo esc_attr( $rbfw_off_days ); ?>">
						<?php foreach ( $days as $day ) : ?>
							<label class="rbfw-me-offday-label">
								<input type="checkbox" class="rbfw-me-offday-checkbox" data-day="<?php echo esc_attr( $day ); ?>" <?php checked( in_array( $day, $off_day_array, true ) ); ?>>
								<span><?php echo esc_html( ucfirst( $day ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>

					<div class="rbfw-me-row rbfw-me-row--2 rbfw-me-offday-buffer">
						<div class="rbfw-me-field">
							<label class="rbfw-me-label"><?php esc_html_e( 'Buffer Time Before (Hours)', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input type="number" name="rbfw_buffer_time" class="rbfw-me-input" min="0" value="<?php echo esc_attr( $rbfw_buffer_time ); ?>">
						</div>
						<div class="rbfw-me-field">
							<label class="rbfw-me-label"><?php esc_html_e( 'Buffer Time After (Hours)', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input type="number" name="rbfw_buffer_time_after" class="rbfw-me-input" min="0" value="<?php echo esc_attr( $rbfw_buffer_time_after ); ?>">
						</div>
					</div>
				</div>
			</div>

			<!-- Off Date Settings -->
			<div class="rbfw-me-card">
				<div class="rbfw-me-card__head">
					<h2><?php esc_html_e( 'Off Date Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					<p><?php esc_html_e( 'Define specific date ranges that are unavailable for booking.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
				<div class="rbfw-me-card__body">
					<div class="rbfw-me-offdate-list">
						<?php if ( ! empty( $rbfw_offday_range ) ) : ?>
							<?php foreach ( $rbfw_offday_range as $single ) : ?>
								<div class="rbfw-me-offdate-row">
									<div class="rbfw-me-field">
										<label class="rbfw-me-label"><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
										<input type="date" name="off_days_start[]" class="rbfw-me-input" value="<?php echo esc_attr( $single['from_date'] ); ?>">
									</div>
									<div class="rbfw-me-field">
										<label class="rbfw-me-label"><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
										<input type="date" name="off_days_end[]" class="rbfw-me-input" value="<?php echo esc_attr( $single['to_date'] ); ?>">
									</div>
									<button type="button" class="rbfw-me-offdate-remove" title="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="rbfw-me-offdate-row">
								<div class="rbfw-me-field">
									<label class="rbfw-me-label"><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<input type="date" name="off_days_start[]" class="rbfw-me-input">
								</div>
								<div class="rbfw-me-field">
									<label class="rbfw-me-label"><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<input type="date" name="off_days_end[]" class="rbfw-me-input">
								</div>
								<button type="button" class="rbfw-me-offdate-remove" title="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						<?php endif; ?>
					</div>
					<div class="rbfw-me-offdate-actions">
						<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-offdate-add">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Add Another Range', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
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
					$rbfw_off_days  = isset( $_POST['rbfw_off_days'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_off_days'] ) : '';
					$rbfw_buffer_time  = isset( $_POST['rbfw_buffer_time'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_buffer_time'] ) : 0;
					$rbfw_buffer_time_after  = isset( $_POST['rbfw_buffer_time_after'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_buffer_time_after'] ) : 0;

                    $off_days_start = isset( $_POST['off_days_start'] ) ? RBFW_Function::data_sanitize( $_POST['off_days_start'] ) : '';
					$off_days_end   = isset( $_POST['off_days_end'] ) ? RBFW_Function::data_sanitize( $_POST['off_days_end'] ) : '';

                    update_post_meta( $post_id, 'rbfw_off_days', $rbfw_off_days );
                    update_post_meta( $post_id, 'rbfw_buffer_time', $rbfw_buffer_time );
                    update_post_meta( $post_id, 'rbfw_buffer_time_after', $rbfw_buffer_time_after );

                    $off_schedules = [];
					$from_dates    = $off_days_start;
					$to_dates      = $off_days_end;
					if ( is_countable( $from_dates ) ) {
						if ( sizeof( $from_dates ) > 0 ) {
							foreach ( $from_dates as $key => $from_date ) {
								if ( $from_date && $to_dates[ $key ] ) {
									$off_schedules[] = [
										'from_date' => $from_date,
										'to_date'   => $to_dates[ $key ],
									];
								}
							}
						} else {
							$from_dates = [];
						}
					}
					update_post_meta( $post_id, 'rbfw_offday_range', $off_schedules );
				}
			}
		}
		new RBFW_Off_Day();
	}