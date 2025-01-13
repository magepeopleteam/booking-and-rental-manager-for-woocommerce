<?php
	/**
	 * @author Rabiul Islam <rabiul0420@gmail.com>
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Security_Deposit' ) ) {
		class RBFW_Security_Deposit {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', [ $this, 'settings_save' ], 99 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_security_deposit"><i class="fas fa-plug"></i><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure security deposit.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $post_id ) {
				$rbfw_enable_security_deposit = get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) ? get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) : 'no';
				?>
                <section>
                    <div>
                        <label><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                        <span><?php esc_html_e( 'Turn on/off security deposit by switching this button.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="rbfw_enable_security_deposit" value="<?php echo esc_attr( ( $rbfw_enable_security_deposit == 'yes' ) ? $rbfw_enable_security_deposit : 'no' ); ?>" <?php echo esc_attr( ( $rbfw_enable_security_deposit == 'yes' ) ? 'checked' : '' ); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
				<?php
			}

			public function related_items( $post_id ) {
				$rbfw_enable_security_deposit = get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) ? get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) : 'no';
				$rbfw_security_deposit_type   = get_post_meta( $post_id, 'rbfw_security_deposit_type', true ) ? get_post_meta( $post_id, 'rbfw_security_deposit_type', true ) : 'percentage';
				$rbfw_security_deposit_amount = get_post_meta( $post_id, 'rbfw_security_deposit_amount', true ) ? get_post_meta( $post_id, 'rbfw_security_deposit_amount', true ) : 0;
				?>
                <div class="rbfw_security_deposit_table <?php echo esc_attr( ( $rbfw_enable_security_deposit == 'yes' ) ? 'show' : 'hide' ); ?>">
                    <section>
                        <div class="w-100">
                            <table class="form-table rbfw_discount_table">
                                <thead>
                                <tr>
                                    <th>
                                        <div class="rbfw_td_title"><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </th>
                                    <th>
                                        <div class="rbfw_td_title"><?php esc_html_e( 'Number of percentage/fixed amount', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="mp_event_type_sortable">
                                <tr>
                                    <td>
                                        <section>
                                            <label for="">Security Deposit Label</label>
                                            <input type="text" name="rbfw_security_deposit_label" value="<?php echo esc_attr( get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) ? get_post_meta( $post_id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ) ?>" placeholder="">
                                        </section>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <select class="rbfw_security_deposit_type" name="rbfw_security_deposit_type">
                                            <option value="percentage" <?php if ( $rbfw_security_deposit_type == 'percentage' ) {
												echo 'selected';
											} ?>><?php esc_html_e( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                            <option value="fixed_amount" <?php if ( $rbfw_security_deposit_type == 'fixed_amount' ) {
												echo 'selected';
											} ?>><?php esc_html_e( 'Fixed Amount', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="rbfw_security_deposit_amount" value="<?php echo esc_attr( $rbfw_security_deposit_amount ); ?>"/></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <script>
                    jQuery('input[name=rbfw_enable_security_deposit]').click(function () {
                        var status = jQuery(this).val();
                        if (status === 'yes') {
                            jQuery(this).val('no');
                            jQuery('.rbfw_security_deposit_table').slideUp().removeClass('show').addClass('hide');
                        }
                        if (status === 'no') {
                            jQuery(this).val('yes');
                            jQuery('.rbfw_security_deposit_table').slideDown().removeClass('hide').addClass('show');
                        }
                    });
                </script>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_security_deposit">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( $post_id ); ?>
					<?php $this->related_items( $post_id ); ?>
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
					$rbfw_enable_security_deposit = isset( $_POST['rbfw_enable_security_deposit'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_security_deposit'] ) ) : 'no';
					$rbfw_security_deposit_type   = isset( $_POST['rbfw_security_deposit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_security_deposit_type'] ) ) : 'percentage';
					$rbfw_security_deposit_amount = isset( $_POST['rbfw_security_deposit_amount'] ) ? intval( wp_unslash( $_POST['rbfw_security_deposit_amount'] ) ) : 0;
					$rbfw_security_deposit_label  = isset( $_POST['rbfw_security_deposit_label'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_security_deposit_label'] ) ) : 'Security Deposit';
					update_post_meta( $post_id, 'rbfw_enable_security_deposit', $rbfw_enable_security_deposit );
					update_post_meta( $post_id, 'rbfw_security_deposit_label', $rbfw_security_deposit_label );
					update_post_meta( $post_id, 'rbfw_security_deposit_type', $rbfw_security_deposit_type );
					update_post_meta( $post_id, 'rbfw_security_deposit_amount', $rbfw_security_deposit_amount );
				}
			}
		}
		new RBFW_Security_Deposit();
	}