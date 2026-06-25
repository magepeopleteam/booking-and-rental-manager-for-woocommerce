<?php
	/*
	* @Author 	:	MagePeople Team
	* Copyright	: 	mage-people.com
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}  // if direct access
	if ( ! class_exists( 'MageRBFWClass' ) ) {
		class MageRBFWClass {
			private $settings_api;
			private $posts_per_page = 10;

			public function __construct() {
				$this->settings_api = new RBFW_Setting_API;
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box_func' ) );
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'rbfw_admin_enqueue_scripts' ) );
				/* WooCommerce Action and Filter */
				add_filter( 'woocommerce_order_status_changed', array( $this, 'rbfw_wc_status_update' ), 10, 4 );
				/* End WooCommerce Action and Filter */
			}

			function admin_init() {
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );
				$this->settings_api->admin_init();
			}

			function admin_menu() {
				add_submenu_page( 'edit.php?post_type=rbfw_item', __( 'Time Slots', 'booking-and-rental-manager-for-woocommerce' ), __( 'Time Slots', 'booking-and-rental-manager-for-woocommerce' ), 'manage_options', 'rbfw_time_slots', array( $this, 'rbfw_time_slots' ) );
				add_submenu_page( 'edit.php?post_type=rbfw_item', __( 'Order List', 'booking-and-rental-manager-for-woocommerce' ), __( 'Order List', 'booking-and-rental-manager-for-woocommerce' ), 'manage_options', 'rbfw_order', array( $this, 'rbfw_order_list' ) );
				add_submenu_page( 'edit.php?post_type=rbfw_item', __( 'Inventory', 'booking-and-rental-manager-for-woocommerce' ), __( 'Inventory', 'booking-and-rental-manager-for-woocommerce' ), 'manage_options', 'rbfw_inventory', array( $this, 'rbfw_inventory_list' ) );
				do_action( 'rbfw_admin_menu_after_inventory' );
				add_submenu_page( 'edit.php?post_type=rbfw_item', __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ), __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ), 'manage_options', 'rbfw_settings_page', array( $this, 'plugin_page' ) );
				do_action( 'rbfw_admin_menu_after_settings' );
				// If PRO plugin is activated
				if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php' ) ) {
					/* Add Pro Submenu */
					add_submenu_page( 'edit.php?post_type=rbfw_item', __( 'Get PRO', 'booking-and-rental-manager-for-woocommerce' ), '<span class="rbfw_plugin_pro_menu">' . __( 'Get PRO', 'booking-and-rental-manager-for-woocommerce' ) . '</span>', 'manage_options', 'rbfw_go_pro_page', array( $this, 'rbfw_go_pro_page' ) );
				}
				// End PRO plugin is activated
			}

			public function rbfw_admin_enqueue_scripts( $hook ) {
				// The Order List page is fully styled by the modern admin/css/rbfw_order.css
				// (scoped under .rbfw_ol). The legacy rbfw-order-list-modern.css was retired:
				// it wrapped the page in a centered max-width card (margin:0 auto -> side gaps)
				// and shipped a global "*"/"body" reset that leaked into the rest of wp-admin.
			}

			public function rbfw_time_slots() {
				$time_slots_page = new RBFW_Timeslots_Page();
				$time_slots_page->rbfw_time_slots_page();
			}

			public function rbfw_order_list() {
				// Hide admin notices on order list page
				add_action('admin_notices', array($this, 'rbfw_hide_admin_notices_on_order_page'), 1);
				add_action('all_admin_notices', array($this, 'rbfw_hide_admin_notices_on_order_page'), 1);

				$args                 = array(
					'post_type'      => 'rbfw_order',
					'order'          => 'DESC',
					'posts_per_page' => - 1,
					'post_status'    => array('publish', 'private', 'draft', 'pending', 'future', 'inherit')
				);
				$query                = new WP_Query( $args );
				
				// Calculate stats
				$total_orders = 0;
				$completed_orders = 0;
				$cancelled_orders = 0;
				$pending_orders = 0;
				$refunded_orders = 0;
				$total_amount = 0;
				$completed_amount = 0;
				$cancelled_amount = 0;
				$pending_amount = 0;
				$refunded_amount = 0;
				
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						global $post;
						$post_id = $post->ID;
						$wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
						$order = wc_get_order($wc_order_id);
						
						if ($order) {
							$status = $order->get_status();
							
							// Skip orders with trash status
							if ($status === 'trash' || $status === 'wc-trash') {
								continue;
							}
							
							$total_orders++;
							$order_total = $order->get_total();
							$total_amount += $order_total;
							
							// Normalize status by removing 'wc-' prefix if present
							$normalized_status = str_replace('wc-', '', $status);
							
							// Count orders and amounts by normalized status
							switch($normalized_status) {
								case 'completed':
									$completed_orders++;
									$completed_amount += $order_total;
									break;
								case 'cancelled':
								case 'canceled':
								case 'failed':
									$cancelled_orders++;
									$cancelled_amount += $order_total;
									break;
								case 'refunded':
									$refunded_orders++;
									$refunded_amount += $order_total;
									break;
								case 'pending':
								case 'on-hold':
								case 'processing':
									$pending_orders++;
									$pending_amount += $order_total;
									break;
							}
						}
					}
					wp_reset_postdata();
				}
				?>
                <div class="rbfw_ol rental-order-list-dashboard wrap">
                    <div class="rbfw_ol_header">
                        <div class="rbfw_ol_title">
                            <span class="rbfw_ol_title_icon"><?php echo rbfw_inv_icon( 'clipboard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <h1><?php esc_html_e( 'Order List', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
                            <span class="rbfw_ol_badge_count"><?php
                                /* translators: %s: number of orders. */
                                echo esc_html( sprintf( _n( '%s Order', '%s Orders', $total_orders, 'booking-and-rental-manager-for-woocommerce' ), number_format_i18n( $total_orders ) ) );
                            ?></span>
                        </div>
                    </div>
                    <hr class="wp-header-end">

                    <!-- Stat cards -->
                    <div class="rbfw_ol_stats">
                        <div class="rbfw_ol_stat" data-stat="total">
                            <div class="rbfw_ol_stat_icon purple"><?php echo rbfw_inv_icon( 'file' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_ol_stat_info">
                                <div class="rbfw_ol_stat_num"><?php echo esc_html( number_format_i18n( $total_orders ) ); ?></div>
                                <div class="rbfw_ol_stat_lbl"><?php esc_html_e( 'Total Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_ol_stat_amt <?php echo $total_amount > 0 ? 'pos' : 'zero'; ?>"><?php echo wp_kses_post( wc_price( $total_amount ) ); ?></div>
                            </div>
                        </div>
                        <div class="rbfw_ol_stat" data-stat="cancelled">
                            <div class="rbfw_ol_stat_icon red"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_ol_stat_info">
                                <div class="rbfw_ol_stat_num"><?php echo esc_html( number_format_i18n( $cancelled_orders ) ); ?></div>
                                <div class="rbfw_ol_stat_lbl"><?php esc_html_e( 'Cancelled', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_ol_stat_amt <?php echo $cancelled_amount > 0 ? 'pos' : 'zero'; ?>"><?php echo wp_kses_post( wc_price( $cancelled_amount ) ); ?></div>
                            </div>
                        </div>
                        <div class="rbfw_ol_stat" data-stat="completed">
                            <div class="rbfw_ol_stat_icon green"><?php echo rbfw_inv_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_ol_stat_info">
                                <div class="rbfw_ol_stat_num"><?php echo esc_html( number_format_i18n( $completed_orders ) ); ?></div>
                                <div class="rbfw_ol_stat_lbl"><?php esc_html_e( 'Completed', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_ol_stat_amt <?php echo $completed_amount > 0 ? 'pos' : 'zero'; ?>"><?php echo wp_kses_post( wc_price( $completed_amount ) ); ?></div>
                            </div>
                        </div>
                        <div class="rbfw_ol_stat" data-stat="pending">
                            <div class="rbfw_ol_stat_icon amber"><?php echo rbfw_inv_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_ol_stat_info">
                                <div class="rbfw_ol_stat_num"><?php echo esc_html( number_format_i18n( $pending_orders ) ); ?></div>
                                <div class="rbfw_ol_stat_lbl"><?php esc_html_e( 'Pending', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_ol_stat_amt <?php echo $pending_amount > 0 ? 'pos' : 'zero'; ?>"><?php echo wp_kses_post( wc_price( $pending_amount ) ); ?></div>
                            </div>
                        </div>
                        <div class="rbfw_ol_stat" data-stat="refunded">
                            <div class="rbfw_ol_stat_icon blue"><?php echo rbfw_inv_icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_ol_stat_info">
                                <div class="rbfw_ol_stat_num"><?php echo esc_html( number_format_i18n( $refunded_orders ) ); ?></div>
                                <div class="rbfw_ol_stat_lbl"><?php esc_html_e( 'Refunded', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_ol_stat_amt <?php echo $refunded_amount > 0 ? 'pos' : 'zero'; ?>"><?php echo wp_kses_post( wc_price( $refunded_amount ) ); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <div class="rbfw_ol_filterbar">
                        <div class="rbfw_ol_filter_field rbfw_ol_fb_by">
                            <span class="rbfw_ol_filter_ico"><?php echo rbfw_inv_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <select id="rbfw_ol_fb_field" class="rbfw_ol_filter_select" aria-label="<?php esc_attr_e( 'Filter by field', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                <option value="name"><?php esc_html_e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="order"><?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="phone"><?php esc_html_e( 'Phone', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="email"><?php esc_html_e( 'Email', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="item"><?php esc_html_e( 'Item', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            </select>
                        </div>
                        <div class="rbfw_ol_search" id="rbfw_ol_fb_textwrap">
                            <?php echo rbfw_inv_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                            <input type="text" id="search" class="rbfw_ol_search_input" placeholder="<?php esc_attr_e( 'Search by name...', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                        </div>
                        <div class="rbfw_ol_filter_field rbfw_ol_fb_item_wrap" id="rbfw_ol_fb_itemwrap" style="display:none;">
                            <span class="rbfw_ol_filter_ico"><?php echo rbfw_inv_icon( 'box' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <select id="rbfw_ol_fb_item" class="rbfw_ol_filter_select" aria-label="<?php esc_attr_e( 'Filter by item', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                <option value=""><?php esc_html_e( 'All Items', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <?php
                                $rbfw_filter_items = get_posts( array( 'post_type' => 'rbfw_item', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish' ) );
                                foreach ( $rbfw_filter_items as $rbfw_filter_item ) : ?>
                                    <option value="<?php echo esc_attr( $rbfw_filter_item->ID ); ?>"><?php echo esc_html( get_the_title( $rbfw_filter_item->ID ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="rbfw_ol_filter_field">
                            <span class="rbfw_ol_filter_ico"><?php echo rbfw_inv_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <select id="rbfw_ol_filter_status" class="rbfw_ol_filter_select" aria-label="<?php esc_attr_e( 'Filter by status', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                <option value=""><?php esc_html_e( 'All Statuses', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <?php if ( function_exists( 'wc_get_order_statuses' ) ) :
                                    foreach ( wc_get_order_statuses() as $rbfw_fs_key => $rbfw_fs_label ) : ?>
                                        <option value="<?php echo esc_attr( str_replace( 'wc-', '', $rbfw_fs_key ) ); ?>"><?php echo esc_html( $rbfw_fs_label ); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <div class="rbfw_ol_filter_field rbfw_ol_filter_dates">
                            <span class="rbfw_ol_filter_ico"><?php echo rbfw_inv_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                            <input type="text" id="rbfw_ol_filter_from" class="rbfw_ol_filter_date" placeholder="<?php esc_attr_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Booking start from', 'booking-and-rental-manager-for-woocommerce' ); ?>" readonly>
                            <span class="rbfw_ol_filter_sep">&ndash;</span>
                            <input type="text" id="rbfw_ol_filter_to" class="rbfw_ol_filter_date" placeholder="<?php esc_attr_e( 'To', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Booking start to', 'booking-and-rental-manager-for-woocommerce' ); ?>" readonly>
                        </div>
                        <button type="button" id="rbfw_ol_filter_reset" class="rbfw_ol_filter_reset">
                            <?php echo rbfw_inv_icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                            <span><?php esc_html_e( 'Reset', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="rbfw_ol_card">
                        <div class="rbfw_ol_table_scroll">
                            <table class="rbfw_ol_table">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Billing Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Order Created', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Booking Start', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Booking End', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    <th class="rbfw_ol_th_action"><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="order-list">
                                <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
                                    global $post;
                                    $post_id      = $post->ID;
                                    $billing_name = get_post_meta( $post_id, 'rbfw_billing_name', true );
                                    $wc_order_id  = get_post_meta( $post_id, 'rbfw_order_id', true );
                                    $order        = wc_get_order( $wc_order_id );
                                    if ( ! $order ) { continue; }
                                    if ( $order->get_status() === 'trash' ) { continue; }
                                    $status      = $order->get_status();
                                    $total_price = $order->get_total();
                                    $billing_phone = $order->get_billing_phone();
                                    $billing_email = $order->get_billing_email();
                                    $ticket_infos      = get_post_meta( $post_id, 'rbfw_ticket_info', true );
                                    $ticket_info_array = maybe_unserialize( $ticket_infos );
                                    $rbfw_start_datetime = '';
                                    $rbfw_end_datetime   = '';
                                    $rbfw_row_item_ids   = array();
                                    if ( ! empty( $ticket_info_array ) && is_array( $ticket_info_array ) ) {
                                        foreach ( $ticket_info_array as $ticket_info ) {
                                            $rbfw_start_datetime = isset( $ticket_info['rbfw_start_datetime'] ) ? $ticket_info['rbfw_start_datetime'] : '';
                                            $rbfw_end_datetime   = isset( $ticket_info['rbfw_end_datetime'] ) ? $ticket_info['rbfw_end_datetime'] : '';
                                            if ( ! empty( $ticket_info['rbfw_id'] ) ) {
                                                $rbfw_row_item_ids[] = (string) $ticket_info['rbfw_id'];
                                            }
                                        }
                                    }
                                    $rbfw_is_pro = function_exists( 'rbfw_pro_tab_menu_list' );
                                    ?>
                                    <tr class="order-row rbfw_ol_row" data-order="<?php echo esc_attr( strtolower( (string) $wc_order_id ) ); ?>" data-name="<?php echo esc_attr( strtolower( (string) $billing_name ) ); ?>" data-phone="<?php echo esc_attr( strtolower( (string) $billing_phone ) ); ?>" data-email="<?php echo esc_attr( strtolower( (string) $billing_email ) ); ?>" data-item="<?php echo esc_attr( implode( ' ', array_unique( $rbfw_row_item_ids ) ) ); ?>" data-status="<?php echo esc_attr( $status ); ?>" data-start="<?php echo esc_attr( ! empty( $rbfw_start_datetime ) ? gmdate( 'Y-m-d', strtotime( $rbfw_start_datetime ) ) : '' ); ?>">
                                        <td class="rbfw_ol_td_order" data-th="<?php esc_attr_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?>">#<?php echo esc_html( $wc_order_id ); ?></td>
                                        <td class="rbfw_ol_td_name" data-th="<?php esc_attr_e( 'Billing Name', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo esc_html( $billing_name ); ?></td>
                                        <td class="rbfw_ol_td_date" data-th="<?php esc_attr_e( 'Order Created', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) . ' ' . get_the_time() ); ?></td>
                                        <td class="rbfw_ol_td_date" data-th="<?php esc_attr_e( 'Booking Start', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo esc_html( ! empty( $rbfw_start_datetime ) ? date_i18n( 'F j, Y g:i a', strtotime( $rbfw_start_datetime ) ) : '—' ); ?></td>
                                        <td class="rbfw_ol_td_date" data-th="<?php esc_attr_e( 'Booking End', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo esc_html( ! empty( $rbfw_end_datetime ) ? date_i18n( 'F j, Y g:i a', strtotime( $rbfw_end_datetime ) ) : '—' ); ?></td>
                                        <td data-th="<?php esc_attr_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?>"><span class="rbfw_ol_badge rbfw_ol_badge_<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span></td>
                                        <td class="rbfw_ol_td_total" data-th="<?php esc_attr_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo wp_kses_post( wc_price( $total_price ) ); ?></td>
                                        <td class="rbfw_ol_td_action" data-th="<?php esc_attr_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                            <div class="rbfw_ol_actions">
                                                <?php if ( $rbfw_is_pro ) { ?>
                                                    <a href="javascript:void(0);" class="rbfw_ol_act rbfw_ol_act_view rbfw_order_view_btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'View Details', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                                    <a href="javascript:void(0);" class="rbfw_ol_act rbfw_ol_act_edit rbfw_order_status_edit_btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-order-no="<?php echo esc_attr( $wc_order_id ); ?>" data-status="<?php echo esc_attr( $status ); ?>" title="<?php esc_attr_e( 'Edit Status', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                                    <a href="javascript:void(0);" class="rbfw_ol_act rbfw_ol_act_delete rbfw_order_delete_btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-order-no="<?php echo esc_attr( $wc_order_id ); ?>" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                                <?php } else { ?>
                                                    <a href="javascript:void(0);" class="rbfw_ol_act rbfw_ol_act_view pro-overlay" title="<?php esc_attr_e( 'View Details', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                                    <a href="javascript:void(0);" class="rbfw_ol_act rbfw_ol_act_edit pro-overlay" title="<?php esc_attr_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="order-details-<?php echo esc_attr( $post_id ); ?>" class="order-details rbfw_ol_detail_row" style="display: none;">
                                        <td colspan="8"><div class="rbfw_ol_detail_anim"><div class="order-details-content"></div></div></td>
                                    </tr>
                                <?php endwhile; else : ?>
                                    <tr class="rbfw_ol_empty_tr">
                                        <td colspan="8" class="rbfw_ol_empty"><?php esc_html_e( 'No orders found.', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                                    </tr>
                                <?php endif;
                                    wp_reset_postdata(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="rbfw_ol_footer">
                            <span class="rbfw_ol_rowinfo" id="row-info"></span>
                            <div class="rbfw_ol_pager" id="rbfw_ol_pager"></div>
                        </div>
                    </div>

                    <div id="loader" class="rbfw_ol_loader" style="display: none;"><span class="rbfw_ol_spinner"></span></div>

                    <?php if ( function_exists( 'rbfw_pro_tab_menu_list' ) ) {
                        $rbfw_wc_statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
                        ?>
                        <div id="rbfw_ol_status_modal" class="rbfw_ol_status_modal" style="display:none;" aria-hidden="true">
                            <div class="rbfw_ol_status_modal_overlay"></div>
                            <div class="rbfw_ol_status_modal_box" role="dialog" aria-modal="true" aria-labelledby="rbfw_ol_status_modal_title">
                                <div class="rbfw_ol_status_modal_head">
                                    <span class="rbfw_ol_status_modal_ic"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                                    <h2 id="rbfw_ol_status_modal_title"><?php esc_html_e( 'Update Order Status', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                                    <button type="button" class="rbfw_ol_status_modal_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></button>
                                </div>
                                <div class="rbfw_ol_status_modal_body">
                                    <p class="rbfw_ol_status_modal_order"><?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong>#<span id="rbfw_ol_status_order_no"></span></strong></p>
                                    <label class="rbfw_ol_status_label" for="rbfw_ol_status_select"><?php esc_html_e( 'Order Status', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                    <select id="rbfw_ol_status_select" class="rbfw_ol_status_select">
                                        <?php foreach ( $rbfw_wc_statuses as $rbfw_st_key => $rbfw_st_label ) : ?>
                                            <option value="<?php echo esc_attr( str_replace( 'wc-', '', $rbfw_st_key ) ); ?>"><?php echo esc_html( $rbfw_st_label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="rbfw_ol_status_msg" id="rbfw_ol_status_msg" style="display:none;"></p>
                                </div>
                                <div class="rbfw_ol_status_modal_foot">
                                    <button type="button" class="rbfw_ol_status_cancel"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                    <button type="button" class="rbfw_ol_status_save"><?php esc_html_e( 'Save Changes', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                </div>
                            </div>
                        </div>

                        <div id="rbfw_ol_delete_modal" class="rbfw_ol_status_modal rbfw_ol_delete_modal" style="display:none;" aria-hidden="true">
                            <div class="rbfw_ol_status_modal_overlay"></div>
                            <div class="rbfw_ol_status_modal_box" role="dialog" aria-modal="true" aria-labelledby="rbfw_ol_delete_modal_title">
                                <div class="rbfw_ol_status_modal_head rbfw_ol_delete_head">
                                    <span class="rbfw_ol_status_modal_ic"><?php echo rbfw_inv_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                                    <h2 id="rbfw_ol_delete_modal_title"><?php esc_html_e( 'Delete Order', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                                    <button type="button" class="rbfw_ol_status_modal_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></button>
                                </div>
                                <div class="rbfw_ol_status_modal_body">
                                    <p class="rbfw_ol_delete_text"><?php esc_html_e( 'This will move the booking and its linked WooCommerce order to Trash. You can restore them later from the Trash.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                    <p class="rbfw_ol_status_modal_order"><?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong>#<span id="rbfw_ol_delete_order_no"></span></strong></p>
                                    <p class="rbfw_ol_status_msg" id="rbfw_ol_delete_msg" style="display:none;"></p>
                                </div>
                                <div class="rbfw_ol_status_modal_foot">
                                    <button type="button" class="rbfw_ol_status_cancel rbfw_ol_delete_cancel"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                    <button type="button" class="rbfw_ol_delete_confirm"><?php esc_html_e( 'Move to Trash', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                </div>
                            </div>
                        </div>

                        <div id="rbfw_ol_edit_modal" class="rbfw_ol_status_modal rbfw_ol_edit_modal" style="display:none;" aria-hidden="true">
                            <div class="rbfw_ol_status_modal_overlay"></div>
                            <div class="rbfw_ol_status_modal_box rbfw_ol_edit_box" role="dialog" aria-modal="true" aria-labelledby="rbfw_ol_edit_modal_title">
                                <div class="rbfw_ol_status_modal_head">
                                    <span class="rbfw_ol_status_modal_ic"><?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                                    <h2 id="rbfw_ol_edit_modal_title"><?php esc_html_e( 'Edit Order', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                                    <button type="button" class="rbfw_ol_status_modal_close rbfw_ol_edit_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"><?php echo rbfw_inv_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></button>
                                </div>
                                <div class="rbfw_ol_status_modal_body rbfw_ol_edit_body" id="rbfw_ol_edit_body">
                                    <div class="rbfw_ol_edit_loading"><span class="rbfw_ol_spinner"></span></div>
                                </div>
                                <p class="rbfw_ol_status_msg rbfw_ol_edit_msg" id="rbfw_ol_edit_msg" style="display:none;"></p>
                                <div class="rbfw_ol_status_modal_foot">
                                    <button type="button" class="rbfw_ol_status_cancel rbfw_ol_edit_cancel"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                    <button type="button" class="rbfw_ol_edit_save"><?php esc_html_e( 'Save Changes', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ( ! function_exists( 'rbfw_pro_tab_menu_list' ) ) { ?>
                    <script>
                        document.querySelectorAll('.pro-overlay').forEach(function (button) {
                            button.addEventListener('click', function (event) {
                                event.preventDefault();
                                window.open('<?php echo esc_js( esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/' ) ); ?>', '_blank');
                            });
                        });
                    </script>
                    <?php } ?>
                </div>
				<?php
			}

			public function rbfw_inventory_list() {
				rbfw_inventory_page();
			}

			public function get_cpt_name() {
				return 'rbfw_item';
			}

			public function get_name() {
				return $this->get_option_trans( 'rbfw_rent_label', 'rbfw_basic_gen_settings', 'Rent Item' );
			}

			public function get_slug() {
				return $this->get_option_trans( 'rbfw_rent_slug', 'rbfw_basic_gen_settings', 'rent' );
			}

			public function get_icon() {
				return $this->get_option_trans( 'rbfw_rent_icon', 'rbfw_basic_gen_settings', 'dashicons-clipboard' );
			}

			function rbfw_go_pro_page() {
				$RBFWProPage = new RBFWProPage();
				$RBFWProPage->rbfw_go_pro_page();
			}

			function get_settings_sections() {
				$sections = array();

				return apply_filters( 'rbfw_settings_sec_reg', $sections );
			}

			function get_settings_fields() {
				$settings_fields = array();

				return apply_filters( 'rbfw_settings_sec_fields', $settings_fields );
			}

			function plugin_page() {
				echo '<div class="wrap">';
				settings_errors();
				echo '</div>';
				echo '<div class="rbfw_settings_wrapper">';
				echo '<div class="rbfw_settings_inner_wrapper">';
				echo '<div class="rbfw_settings_panel_header">';
				echo esc_html( RBFW_Rent_Manager::get_plugin_data( 'Name' ) );
				echo '<small>' . esc_html( RBFW_Rent_Manager::get_plugin_data( 'Version' ) ) . '</small>';
				echo '</div>';
				echo '<div class="mage_settings_panel_wrap rbfw_settings_panel">';
				$this->settings_api->show_navigation();
				$this->settings_api->show_forms();
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}

			function get_pages() {
				$pages         = get_pages();
				$pages_options = array();
				if ( $pages ) {
					foreach ( $pages as $page ) {
						$pages_options[ $page->ID ] = $page->post_title;
					}
				}

				return $pages_options;
			}

			function get_option_trans( $option = 'text', $section = 'rbfw_basic_gen_settings', $default = '' ) {
				$options = get_option( $section );
				if ( ! empty( $options[ $option ] ) ) {
					if ( is_array( $options[ $option ] ) ) {
						return $options[ $option ];
					} else {
						return esc_html( $options[ $option ] );
					}
				}

				return $default;
			}

			function get_option( $option = 'text', $section = 'rbfw_basic_gen_settings', $default = '' ) {
				$options = get_option( $section );
				if ( ! empty( $options[ $option ] ) ) {
					if ( is_array( $options[ $option ] ) ) {
						return $options[ $option ];
					} else {
						return esc_html( $options[ $option ] );
					}
				}

				return $default;
			}

			public function send_email( $sent_email, $rbfw_id = '', $email_sub = '', $content = '', $order_id = '', $attathment_file_url = '' ) {
				$global_email_text       = $this->get_option_trans( 'mep_confirmation_email_text', 'email_setting_sec', '' );
				$global_email_form_email = $this->email_from_email();
				$global_email_form       = $this->email_from_name();
				$global_email_sub        = $this->get_option_trans( 'mep_email_subject', 'email_setting_sec', '' );
				$admin_email             = get_option( 'admin_email' );
				$site_name               = get_option( 'blogname' );
				$attachments             = array();
				if ( empty( $email_sub ) ) {
					if ( $global_email_sub ) {
						$email_sub = $global_email_sub;
					} else {
						$email_sub = 'Confirmation Email';
					}
				}
				if ( $global_email_form ) {
					$form_name = $global_email_form;
				} else {
					$form_name = $site_name;
				}
				if ( $global_email_form_email ) {
					$form_email = $global_email_form_email;
				} else {
					$form_email = $admin_email;
				}
				if ( ! empty( $content ) ) {
					$email_body = $content;
				} else {
					$email_body = $global_email_text;
				}
				$headers[] = "From: $form_name <$form_email>";
				if ( ! empty( $attathment_file_url ) && ! is_wp_error( $attathment_file_url ) ) {
					$attachments[] = $attathment_file_url;
				}
				if ( $email_body ) {
					$confirmation_email_text = apply_filters( 'rbfw_send_email_content_text', $email_body, $rbfw_id, $order_id );
					wp_mail( $sent_email, $email_sub, nl2br( $confirmation_email_text ), $headers, $attachments );
				}
			}

			public function add_meta_box_func() {
				$cpt_label = $this->get_option_trans( 'rbfw_rent_label', 'rbfw_basic_gen_settings', 'Rent' );
				add_meta_box( 'rbfw_add_meta_box', $cpt_label . __( " Settings : ", 'booking-and-rental-manager-for-woocommerce' ) . get_the_title( get_the_id() ), array( $this, 'mp_event_all_in_tab' ), 'rbfw_item', 'normal', 'high' );
			}

			public function mp_event_all_in_tab() {
				$post_id        = get_the_id();
				$rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				wp_nonce_field( 'rbfw_ticket_type_nonce', 'rbfw_ticket_type_nonce' );
				?>
                <script>
                (function () {
                    var box = document.getElementById('rbfw_add_meta_box');
                    if (box) {
                        box.setAttribute('data-item-type', <?php echo wp_json_encode( $rbfw_item_type ); ?>);
                    }
                    document.querySelectorAll('.rbfw_seasonal_price_config_wrapper').forEach(function (el) {
                        el.setAttribute('data-item-type', <?php echo wp_json_encode( $rbfw_item_type ); ?>);
                    });
                })();
                </script>
                <div class="mp_event_tab_area">
                    <aside class="mp_tab_menu">
                        <ul>
							<?php do_action( 'rbfw_meta_box_tab_name', $post_id ); ?>
                        </ul>
                    </aside>
                    <div class="mp_tab_details">
						<?php do_action( 'rbfw_meta_box_tab_content', $post_id ); ?>
                    </div>
                </div>
				<?php
			}

			function get_datetime( $date, $type ) {
				$date_format    = get_option( 'date_format' );
				$time_format    = get_option( 'time_format' );
				$wpdatesettings = $date_format . '  ' . $time_format;
				$timezone       = wp_timezone_string();
				$timestamp      = strtotime( $date . ' ' . $timezone );
				if ( $type == 'date' ) {
					return wp_date( $date_format, $timestamp );
				}
				if ( $type == 'date-time' ) {
					return wp_date( $wpdatesettings, $timestamp );
				}
				if ( $type == 'date-text' ) {
					return wp_date( $date_format, $timestamp );
				}
				if ( $type == 'date-time-text' ) {
					return wp_date( $wpdatesettings, $timestamp, wp_timezone() );
				}
				if ( $type == 'time' ) {
					return wp_date( $time_format, $timestamp, wp_timezone() );
				}
				if ( $type == 'day' ) {
					return wp_date( 'd', $timestamp );
				}
				if ( $type == 'month' ) {
					return wp_date( 'M', $timestamp );
				}
			}

			function rbfw_add_order_data( $meta_data = array(), $ticket_info = array(), $rbfw_service_price_data_actual = array() ) {


                $title               = $meta_data['rbfw_billing_name'];
				$cpt_name            = 'rbfw_order';

                $rbfw_id          = $meta_data['rbfw_id'];
                $wc_order_id      = $meta_data['rbfw_order_id'];
                $order_tax        = ! empty( get_post_meta( $wc_order_id, '_order_tax', true ) ) ? get_post_meta( $wc_order_id, '_order_tax', true ) : 0;
                $is_tax_inclusive = get_option( 'woocommerce_prices_include_tax', true );
                $args   = array(
                        'post_title'   => $title,
						'post_content' => '',
						'post_status'  => 'publish',
						'post_type'    => $cpt_name
                );

                $meta_query       = array(
                    'meta_query' => array(
                        'meta_value' => array(
                            'key'     => 'rbfw_order_id',
                            'value'   => $wc_order_id,
                            'compare' => '==',
                        )
                    )
                );
					$args             = array_merge( $args, $meta_query );
					$query            = new WP_Query( $args );
					/* If Order already created, update the order */
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							global $post;
							$post_id                 = $post->ID;
							$current_ticket_info     = get_post_meta( $post_id, 'rbfw_ticket_info', true );
							$merged_ticket_info      = array_merge( $ticket_info, $current_ticket_info );
							$rbfw_ticket_total_price = get_post_meta( $post_id, 'rbfw_ticket_total_price', true );

                            $duration_cost = 0;
                            $service_cost = 0;

                            


							if ( $is_tax_inclusive == 'yes' ) {
								$total_price = $rbfw_ticket_total_price;
							} else {
								$total_price = $rbfw_ticket_total_price + $order_tax;
							}
							if ( sizeof( $meta_data ) > 0 ) {
								foreach ( $meta_data as $key => $value ) {
									update_post_meta( $post_id, $key, $value );
								}
								wp_update_post( array( 'ID' => $post_id, 'post_title' => '#' . $wc_order_id . ' ' . $title ) );
							}
							update_post_meta( $post_id, 'rbfw_ticket_info', $merged_ticket_info );
							update_post_meta( $post_id, 'rbfw_ticket_total_price', $total_price );
							if ( ! empty( $order_tax ) ) {
								update_post_meta( $post_id, 'rbfw_order_tax', $order_tax );
							}
						}
					} else {
                        $rbfw_ticket_total_price               = $meta_data['rbfw_ticket_total_price'];
						$args    = array(
							'post_title'   => $title,
							'post_content' => '',
							'post_status'  => 'publish',
							'post_type'    => $cpt_name
						);
						$post_id = wp_insert_post( $args );
						if ( sizeof( $meta_data ) > 0 ) {
							foreach ( $meta_data as $key => $value ) {
								update_post_meta( $post_id, $key, $value );
							}
							wp_update_post( array( 'ID' => $post_id, 'post_title' => '#' . $wc_order_id . ' ' . $title ) );
						}
						$rbfw_pin = $meta_data['rbfw_user_id'] . $meta_data['rbfw_order_id'] . $post_id;
						update_post_meta( $post_id, 'rbfw_pin', $rbfw_pin );
						update_post_meta( $wc_order_id, '_rbfw_link_order_id', $post_id );
						if ( ! empty( $order_tax ) ) {
							update_post_meta( $post_id, 'rbfw_order_tax', $order_tax );
						}

						if ( $is_tax_inclusive != 'yes' ) {
							$total_price = $rbfw_ticket_total_price + $order_tax;
						}
						update_post_meta( $post_id, 'ticket_name', $wc_order_id );
						update_post_meta( $post_id, 'rbfw_ticket_total_price', $total_price );
						update_post_meta( $post_id, 'rbfw_link_order_id', $wc_order_id );
					}
					wp_reset_postdata();


				return $post_id;
			}

			function email_from_name() {
				return get_option( 'woocommerce_email_from_name' );
			}

			function email_from_email() {
				return get_option( 'woocommerce_email_from_address' );
			}

			public function rbfw_wc_status_update( $order_id, $from_status, $to_status, $order ) {
				$order        = wc_get_order( $order_id );
				$order_status = $order->get_status();
				foreach ( $order->get_items() as $item_values ) {
					$rbfw_id = $item_values->get_meta( '_rbfw_id' );
					if ( get_post_type( $rbfw_id ) == $this->get_cpt_name() ) {
						if ( $order->has_status( 'processing' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'pending' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'on-hold' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'completed' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'cancelled' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'refunded' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
						if ( $order->has_status( 'failed' ) ) {
							do_action( 'rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id );
						}
					}
				}
			}

			function get_wc_raw_price( $post_id, $price, $args = array() ) {
				$args     = wp_parse_args(
					$args,
					array(
						'qty'   => '',
						'price' => '',
					)
				);
				$_product = get_post_meta( $post_id, 'link_wc_product', true ) ? get_post_meta( $post_id, 'link_wc_product', true ) : $post_id;
				// $price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
				$qty            = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
				$product        = wc_get_product( $_product );
				$tax_with_price = get_option( 'woocommerce_tax_display_shop' );
				if ( '' === $price ) {
					return '';
				} elseif ( empty( $qty ) ) {
					return 0.0;
				}
				$line_price   = $price * $qty;
				$return_price = $line_price;
				if ( $product->is_taxable() ) {
					if ( ! wc_prices_include_tax() ) {
						$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
						$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );
						if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
							$taxes_total = array_sum( $taxes );
						} else {
							$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
						}
						$return_price = $tax_with_price == 'excl' ? round( $line_price, wc_get_price_decimals() ) : round( $line_price + $taxes_total, wc_get_price_decimals() );
					} else {
						$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
						$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
						/**
						 * If the customer is excempt from VAT, remove the taxes here.
						 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
						 */
						if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
							$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$remove_taxes_total = array_sum( $remove_taxes );
							} else {
								$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
							}
							// $return_price = round( $line_price, wc_get_price_decimals() );
							$return_price = round( $line_price - $remove_taxes_total, wc_get_price_decimals() );
							/**
							 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
							 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
							 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
							 */
						} else {
							$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
							$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$base_taxes_total   = array_sum( $base_taxes );
								$modded_taxes_total = array_sum( $modded_taxes );
							} else {
								$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
								$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
							}
							$return_price = $tax_with_price == 'excl' ? round( $line_price - $base_taxes_total, wc_get_price_decimals() ) : round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() );
						}
					}
				}

				return apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $product );
			}

			function all_tax_list() {
				$tax_list = [];
				if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					$tax_classes   = WC_Tax::get_tax_classes();
					$tax_classes[] = 'Standard';
					foreach ( $tax_classes as $tax_class ) {
						$slug              = $tax_class === 'Standard' ? 'standard' : sanitize_title( $tax_class );
						$tax_list[ $slug ] = $tax_class;
					}
				}

				return $tax_list;
			}

			/**
			 * Hide admin notices on RBFW order list page
			 */
			public function rbfw_hide_admin_notices_on_order_page() {
				// Check if we're on the order list page
				if (isset($_GET['page']) && $_GET['page'] === 'rbfw_order') {
					// Remove all admin notices
					remove_all_actions('admin_notices');
					remove_all_actions('all_admin_notices');
					
					// Add custom CSS to hide any remaining notices
					echo '<style>
						.notice, .error, .updated, .update-nag, 
						.notice-error, .notice-warning, .notice-success, .notice-info,
						div.error, div.updated, div.notice {
							display: none !important;
						}
					</style>';
				}
			}
		}
	}
	$rbfw = new MageRBFWClass();
