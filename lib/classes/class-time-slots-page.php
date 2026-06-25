<?php
	/*
	* Author 	:	MagePeople Team
	* Copyright	: 	mage-people.com
	* Developer :   Ariful
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( ! class_exists( 'RBFW_Timeslots_Page' ) ) {
		class RBFW_Timeslots_Page {
			public function __construct() {
				add_action( 'admin_footer', array( $this, 'rbfw_time_slots_script' ) );
				add_action( 'wp_ajax_rbfw_insert_time_slot', array( $this, 'rbfw_insert_time_slot' ) );
				add_action( 'wp_ajax_rbfw_delete_time_slot', array( $this, 'rbfw_delete_time_slot' ) );
				add_action( 'wp_ajax_rbfw_update_time_slot', array( $this, 'rbfw_update_time_slot' ) );
			}

			public function rbfw_time_slots_page() {
				$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
				$total_slots     = is_array( $rbfw_time_slots ) ? count( $rbfw_time_slots ) : 0;
				?>
                <div class="rbfw_ts rbfw_time_slots_page_wrap wrap">
                    <div class="rbfw_ts_header">
                        <div class="rbfw_ts_title">
                            <span class="rbfw_ts_title_icon"><?php echo rbfw_inv_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <h1><?php esc_html_e( 'Time Slots', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
                            <span class="rbfw_ts_badge"><?php
                                /* translators: %s: number of time slots. */
                                echo esc_html( sprintf( _n( '%s Slot', '%s Slots', $total_slots, 'booking-and-rental-manager-for-woocommerce' ), number_format_i18n( $total_slots ) ) );
                            ?></span>
                        </div>
                    </div>
                    <hr class="wp-header-end">
					<?php
						$this->rbfw_time_slots_form();
						$this->rbfw_time_slots_table( $rbfw_time_slots );
					?>
                </div>
				<?php
			}

			public function rbfw_time_slots_form() {
				?>
                <div class="rbfw_ts_card rbfw_ts_form_card">
                    <div class="rbfw_ts_card_label"><?php echo rbfw_inv_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e( 'Add New Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <div class="rbfw_ts_form rbfw_time_slot_page_form">
                        <div class="rbfw_ts_field rbfw_time_slot_form_input_group">
                            <label><?php esc_html_e( 'Slot Label', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <input type="text" class="rbfw_time_slot_label" placeholder="<?php esc_attr_e( 'e.g. Morning', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                        </div>
                        <div class="rbfw_ts_field rbfw_time_slot_form_input_group">
                            <label><?php esc_html_e( 'Slot Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <input type="time" class="rbfw_time_slot_time"/>
                        </div>
                        <div class="rbfw_ts_form_actions">
                            <button type="button" class="rbfw_ts_btn rbfw_ts_btn_primary rbfw_time_slot_add_btn"><?php echo rbfw_inv_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e( 'Add Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            <button type="button" class="rbfw_ts_btn rbfw_ts_btn_ghost rbfw_time_slot_reset_btn"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e( 'Reset', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            <button type="button" class="rbfw_ts_btn rbfw_ts_btn_refresh rbfw_time_slot_refresh_btn"><?php echo rbfw_inv_icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e( 'Refresh', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function rbfw_format_time_slot( $time_slots_arr ) {
				$arr = [];
				if ( empty( $time_slots_arr ) ) {
					return $arr;
				}
				foreach ( $time_slots_arr as $key => $value ) {
					$arr[ $key ] = gmdate( 'H:i', strtotime( $value ) );
				}

				return $arr;
			}

			public function rbfw_time_slots_table( $rbfw_time_slots = null ) {
				if ( null === $rbfw_time_slots ) {
					$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
				}
				?>
                <div class="rbfw_ts_card">
                    <div class="rbfw_ts_table_scroll">
                        <table class="rbfw_ts_table">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Slot Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th><?php esc_html_e( 'Slot Time', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th class="rbfw_ts_th_right"><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
								if ( ! empty( $rbfw_time_slots ) ) {
									$rbfw_time_slots = $this->rbfw_format_time_slot( $rbfw_time_slots );
									asort( $rbfw_time_slots );
									foreach ( $rbfw_time_slots as $key => $value ) {
										?>
                                        <tr class="rbfw_ts_row">
                                            <td class="rbfw_ts_td_label" data-th="<?php esc_attr_e( 'Slot Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><span class="rbfw_ts_slot_name"><?php echo esc_html( $key ); ?></span></td>
                                            <td data-th="<?php esc_attr_e( 'Slot Time', 'booking-and-rental-manager-for-woocommerce' ); ?>"><span class="rbfw_ts_time_pill"><?php echo rbfw_inv_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php echo esc_html( wp_strip_all_tags( $value ) ); ?></span></td>
                                            <td class="rbfw_ts_td_action" data-th="<?php esc_attr_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                <a href="#" class="rbfw_ts_action rbfw_ts_action_edit rbfw_time_slot_edit_btn" data-label="<?php echo esc_attr( $key ); ?>"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <span><?php esc_html_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?></span></a>
                                                <a href="#" class="rbfw_ts_action rbfw_ts_action_del rbfw_time_slot_remove_btn" data-time="<?php echo esc_attr( $value ); ?>" data-label="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                            </td>
                                        </tr>
										<?php
									}
								} else {
									?>
                                    <tr class="rbfw_ts_empty_tr">
                                        <td colspan="3" class="rbfw_ts_empty"><?php esc_html_e( 'No time slots yet — add your first one above.', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                                    </tr>
									<?php
								}
							?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rbfw_time_slot_edit_form">
                    <div class="rbfw_ts_modal_head">
                        <div class="rbfw_ts_modal_icon"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                        <div class="rbfw_ts_modal_title_wrap">
                            <div class="rbfw_ts_modal_title"><?php esc_html_e( 'Edit Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                            <div class="rbfw_ts_modal_sub"><?php esc_html_e( 'Rename this slot label', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                        </div>
                        <a href="#" class="rbfw_ts_modal_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                    </div>
                    <div class="rbfw_ts_modal_body">
                        <div class="rbfw_ts_field">
                            <label><?php esc_html_e( 'Slot Label', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <input type="text" class="rbfw_time_slot_edit_slot_label"/>
                        </div>
                        <div class="rbfw_ts_modal_footer">
                            <button type="button" class="rbfw_ts_btn rbfw_ts_btn_primary rbfw_time_slot_edit_form_save"><?php echo rbfw_inv_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?> <?php esc_html_e( 'Save Changes', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            <input type="hidden" class="rbfw_time_slot_edit_slot_label_current_value"/>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function rbfw_insert_time_slot() {

                check_ajax_referer( 'rbfw_time_slot_action', 'nonce' );
                if (!current_user_can('manage_options')) {
                    wp_send_json_error(['message' => 'Unauthorized access'], 403);
                    wp_die();
                }
                $status = '';
                if ( isset( $_POST['ts_label'] ) && isset( $_POST['ts_time'] ) ) {
                    $rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
                    $ts_label        = sanitize_text_field( wp_unslash( $_POST['ts_label'] ) );
                    $ts_time         = sanitize_text_field( wp_unslash( $_POST['ts_time'] ) );
                    $ts_time         = gmdate( 'H:i', strtotime( $ts_time ) );
                    if ( ! array_key_exists( $ts_label, $rbfw_time_slots ) ) {
                        $rbfw_time_slots[ $ts_label ] = $ts_time;
                        update_option( 'rbfw_time_slots', $rbfw_time_slots );
                        $status = 'inserted';
                    } else {
                        $status = 'exist';
                    }
                }
                echo wp_json_encode( array(
					'status' => $status,
				) );
				wp_die();
            }

			public function rbfw_delete_time_slot() {
                if (!current_user_can('manage_options')) {
                    wp_send_json_error(['message' => 'Unauthorized access'], 403);
                    wp_die();
                }
				$status = '';

                check_ajax_referer( 'rbfw_delete_time_slot_action', 'nonce' );

					if ( isset( $_POST['ts_time'] ) && isset( $_POST['ts_label'] ) ) {
						$rbfw_time_slots = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
						$ts_label        = sanitize_text_field( wp_unslash( $_POST['ts_label'] ) );
						if ( array_key_exists( $ts_label, $rbfw_time_slots ) ) {
							unset( $rbfw_time_slots[ $ts_label ] );
							update_option( 'rbfw_time_slots', $rbfw_time_slots );
							$status = 'deleted';
						}
					}

				echo wp_json_encode( array(
					'status' => $status,
				) );
				wp_die();
			}

			public function rbfw_replace_key( $arr, $oldkey, $newkey ) {
				if ( array_key_exists( $oldkey, $arr ) ) {
					$keys                                   = array_keys( $arr );
					$keys[ array_search( $oldkey, $keys ) ] = $newkey;

					return array_combine( $keys, $arr );
				}

				return $arr;
			}

			public function rbfw_update_time_slot() {

                if (!current_user_can('manage_options')) {
                    wp_send_json_error(['message' => 'Unauthorized access'], 403);
                    wp_die();
                }

                /*if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }*/

                check_ajax_referer( 'rbfw_update_time_slot_action', 'nonce' );



				if ( isset( $_POST['new_ts_label'] ) && isset( $_POST['current_ts_label'] ) ) {
					$rbfw_time_slots  = ! empty( get_option( 'rbfw_time_slots' ) ) ? get_option( 'rbfw_time_slots' ) : [];
					$new_ts_label     = sanitize_text_field( wp_unslash( $_POST['new_ts_label'] ) );
					$current_ts_label = sanitize_text_field( wp_unslash( $_POST['current_ts_label'] ) );
					if ( array_key_exists( $current_ts_label, $rbfw_time_slots ) ) {
						$rbfw_time_slots = $this->rbfw_replace_key( $rbfw_time_slots, $current_ts_label, $new_ts_label );
						update_option( 'rbfw_time_slots', $rbfw_time_slots );
						$status = 'updated';
						echo wp_json_encode( array(
							'status' => $status,
						) );
					}
				}
				wp_die();
			}

			public function rbfw_time_slots_script() {
				?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery('.rbfw_time_slot_add_btn').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            let ts_label = jQuery('.rbfw_time_slot_label').val();
                            let ts_time = jQuery('.rbfw_time_slot_time').val();
                            if (ts_label === '' || ts_time === '') {
                                return;
                            }
                            jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax_admin.rbfw_ajaxurl,
                                data: {
                                    'action': 'rbfw_insert_time_slot',
                                    'ts_label': ts_label,
                                    'ts_time': ts_time,
                                    'nonce': rbfw_ajax_admin.nonce_time_slot
                                },
                                beforeSend: function () {
                                    jQuery('.rbfw_time_slot_add_btn').append('<i class="fas fa-spinner fa-spin"></i>');
                                },
                                success: function (response) {
                                    jQuery('.rbfw_time_slot_add_btn i').remove();
                                    response = JSON.parse(response);
                                    if (response.status === 'inserted') {
                                        jQuery('.rbfw_time_slot_label').val('');
                                        jQuery('.rbfw_time_slot_time').val('');
                                        alert('Good job! Time slot added!');
                                        window.location.reload();
                                    } else if (response.status === 'exist') {
                                        alert('Sorry! Time slot label exist!');
                                    }
                                }
                            });
                        });
                        jQuery('.rbfw_time_slot_refresh_btn').click(function () {
                            window.location.reload();
                        });
                        jQuery('.rbfw_time_slot_reset_btn').click(function () {
                            jQuery('.rbfw_time_slot_label').val('');
                            jQuery('.rbfw_time_slot_time').val('');
                        });
                        jQuery('.rbfw_time_slot_remove_btn').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            let ts_time = jQuery(this).attr('data-time');
                            let ts_label = jQuery(this).attr('data-label');
                            let this_btn = jQuery(this);
                            if (confirm('Are you sure? You won\'t be able to revert this!')) {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: rbfw_ajax_admin.rbfw_ajaxurl,
                                    data: {
                                        'action': 'rbfw_delete_time_slot',
                                        'ts_time': ts_time,
                                        'ts_label': ts_label,
                                        'nonce': rbfw_ajax_admin.nonce_delete_time_slot
                                    },
                                    beforeSend: function () {
                                        this_btn.append('<i class="fas fa-spinner fa-spin"></i>');
                                    },
                                    success: function (response) {
                                        jQuery('.rbfw_time_slot_remove_btn i.fa-spinner').remove();
                                        var response = JSON.parse(response);
                                        if (response.status === 'deleted') {
                                            alert('Done! Time slot deleted!');
                                            window.location.reload();
                                        }
                                    }
                                });
                            }
                        });
                        jQuery('.rbfw_time_slot_edit_btn').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            let ts_label = jQuery(this).attr('data-label');
                            jQuery('.rbfw_time_slot_edit_slot_label').val(ts_label);
                            jQuery('.rbfw_time_slot_edit_slot_label_current_value').val(ts_label);
                            jQuery(".rbfw_time_slot_edit_form").mage_modal({
                                escapeClose: true,
                                clickClose: true,
                                showClose: false
                            });
                        });
                        function rbfwTsCloseModal() {
                            if (jQuery.mage_modal && typeof jQuery.mage_modal.isActive === 'function' && jQuery.mage_modal.isActive()) {
                                jQuery.mage_modal.close();
                            }
                        }
                        jQuery(document).on('click', '.rbfw_ts_modal_close', function (e) {
                            e.preventDefault();
                            rbfwTsCloseModal();
                        });
                        jQuery(document).on('click', '.mage_blocker', function (e) {
                            if (e.target === this) { rbfwTsCloseModal(); }
                        });
                        jQuery('.rbfw_time_slot_edit_form_save').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            let current_ts_label = jQuery('.rbfw_time_slot_edit_slot_label_current_value').val();
                            let new_ts_label = jQuery('.rbfw_time_slot_edit_slot_label').val();
                            if (new_ts_label === '' || current_ts_label === '') {
                                return;
                            }
                            jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax_url,
                                data: {
                                    'action': 'rbfw_update_time_slot',
                                    'new_ts_label': new_ts_label,
                                    'current_ts_label': current_ts_label,
                                    'nonce': rbfw_ajax_admin.nonce_update_time_slot
                                },
                                beforeSend: function () {
                                    jQuery('.rbfw_time_slot_edit_form_save').append('<i class="fas fa-spinner fa-spin"></i>');
                                },
                                success: function (response) {
                                    jQuery('.rbfw_time_slot_edit_form_save i').remove();
                                    var response = JSON.parse(response);
                                    if (response.status === 'updated') {
                                        jQuery('.rbfw_time_slot_edit_form').append('<p class="rbfw_alert_success">Time slot updated! redirecting...</p>');
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    });
                </script>
				<?php
			}
		}
		$RBFW_Timeslots_Page = new RBFW_Timeslots_Page();
	}