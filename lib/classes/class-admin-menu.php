<?php
	/*
	* @Author 	:	MagePeople Team
	* Copyright	: 	mage-people.com
	* Developer :   Mahin and Ariful
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
				// Only load on our order list page
				if ( $hook === 'rbfw_item_page_rbfw_order' ) {
					wp_enqueue_style( 
						'rbfw-order-list-modern', 
						plugin_dir_url( __FILE__ ) . '../../assets/admin/css/rbfw-order-list-modern.css', 
						array(), 
						'1.0.0' 
					);
				}
			}

			public function rbfw_time_slots() {
				$time_slots_page = new RBFW_Timeslots_Page();
				$time_slots_page->rbfw_time_slots_page();
			}

			public function rbfw_order_list() {

				$args                 = array(
					'post_type'      => 'rbfw_order',
					'order'          => 'DESC',
					'posts_per_page' => - 1
				);
				$query                = new WP_Query( $args );
				
				// Calculate stats
				$total_orders = $query->found_posts;
				$completed_orders = 0;
				$cancelled_orders = 0;
				$pending_orders = 0;
				$total_amount = 0;
				
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						global $post;
						$post_id = $post->ID;
						$wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
						$order = wc_get_order($wc_order_id);
						
						if ($order) {
							$status = $order->get_status();
							$total_amount += $order->get_total();
							
							switch($status) {
								case 'completed':
									$completed_orders++;
									break;
								case 'cancelled':
									$cancelled_orders++;
									break;
								case 'pending':
								case 'on-hold':
									$pending_orders++;
									break;
							}
						}
					}
					wp_reset_postdata();
				}
				?>
                <div class="rental-order-list-dashboard">
                    <div class="rental-order-list-header">
                        <h1><?php esc_html_e( 'ORDER LIST', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="rental-order-list-stats-grid">
                        <div class="rental-order-list-stat-card rental-order-list-total">
                            <div class="rental-order-list-stat-icon">📊</div>
                            <div class="rental-order-list-stat-info">
                                <div class="rental-order-list-stat-number"><?php echo esc_html( $total_orders ); ?></div>
                                <div class="rental-order-list-stat-label"><?php esc_html_e( 'Total Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rental-order-list-stat-amount"><?php echo wp_kses_post( wc_price( $total_amount ) ); ?></div>
                            </div>
                        </div>
                        <div class="rental-order-list-stat-card rental-order-list-cancelled">
                            <div class="rental-order-list-stat-icon">❌</div>
                            <div class="rental-order-list-stat-info">
                                <div class="rental-order-list-stat-number"><?php echo esc_html( $cancelled_orders ); ?></div>
                                <div class="rental-order-list-stat-label"><?php esc_html_e( 'Cancelled Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rental-order-list-stat-amount"><?php echo wp_kses_post( wc_price( 0 ) ); ?></div>
                            </div>
                        </div>
                        <div class="rental-order-list-stat-card rental-order-list-completed">
                            <div class="rental-order-list-stat-icon">✅</div>
                            <div class="rental-order-list-stat-info">
                                <div class="rental-order-list-stat-number"><?php echo esc_html( $completed_orders ); ?></div>
                                <div class="rental-order-list-stat-label"><?php esc_html_e( 'Completed Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rental-order-list-stat-amount"><?php echo wp_kses_post( wc_price( 0 ) ); ?></div>
                            </div>
                        </div>
                        <div class="rental-order-list-stat-card rental-order-list-pending">
                            <div class="rental-order-list-stat-icon">⏳</div>
                            <div class="rental-order-list-stat-info">
                                <div class="rental-order-list-stat-number"><?php echo esc_html( $pending_orders ); ?></div>
                                <div class="rental-order-list-stat-label"><?php esc_html_e( 'Pending Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rental-order-list-stat-amount"><?php echo wp_kses_post( wc_price( 0 ) ); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rental-order-list-search-container">
                        <div class="rental-order-list-search-icon">🔍</div>
                        <input type="text" id="search" class="rental-order-list-search-input" placeholder="<?php esc_attr_e( 'Search by order id or customer name..', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                    </div>
                    
                    <div class="rental-order-list-table-container">
                        <table class="rental-order-list-table">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Billing Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Order Created Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Booking Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Booking End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        </tr>
                        </thead>
                        <tbody id="order-list">
						<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
							global $post;
							$post_id             = $post->ID;

							$billing_name        = get_post_meta( $post_id, 'rbfw_billing_name', true );
							$wc_order_id       = get_post_meta( $post_id, 'rbfw_order_id', true );

                            $order = wc_get_order($wc_order_id);
                            if (!$order) {
                                continue;
                            }

							$status              = ( $order && $order->get_status() === 'trash')? $order->get_status() : get_post_meta( $post_id, 'rbfw_order_status', true );
							$total_price         = $order->get_total();
							$ticket_infos        = get_post_meta( $post_id, 'rbfw_ticket_info', true );
							$ticket_info_array   = maybe_unserialize( $ticket_infos );
							$rbfw_start_datetime = '';
							$rbfw_end_datetime   = '';
							if ( ! empty( $ticket_info_array ) && is_array( $ticket_info_array ) ) {
								foreach ( $ticket_info_array as $ticket_info ) {
									$rbfw_start_datetime = isset( $ticket_info['rbfw_start_datetime'] ) ? $ticket_info['rbfw_start_datetime'] : '';
									$rbfw_end_datetime   = isset( $ticket_info['rbfw_end_datetime'] ) ? $ticket_info['rbfw_end_datetime'] : '';
								}
							}
							?>
                            <tr class="order-row">
                                <td><?php echo esc_html( $wc_order_id ); ?></td>
                                <td><?php echo esc_html( $billing_name ); ?></td>
                                <td><?php echo esc_html( get_the_date( 'F j, Y' ) . ' ' . get_the_time() ); ?></td>
                                <td><?php echo esc_html( ! empty( $rbfw_start_datetime ) ? date_i18n( 'F j, Y g:i a', strtotime( $rbfw_start_datetime ) ) : '' ); ?></td>
                                <td><?php echo esc_html( ! empty( $rbfw_end_datetime ) ? date_i18n( 'F j, Y g:i a', strtotime( $rbfw_end_datetime ) ) : '' ); ?></td>
                                <td><span class="rental-order-list-status-badge rental-order-list-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst($status) ); ?></span></td>
                                <td><span class="rental-order-list-price"><?php echo wp_kses_post( wc_price( $total_price ) ); ?></span></td>
								<?php if ( function_exists( 'rbfw_pro_tab_menu_list' ) ) { ?>
                                    <td>
                                        <div class="rental-order-list-action-buttons">
                                            <a href="javascript:void(0);" class="rental-order-list-btn rental-order-list-btn-view rbfw_order_view_btn" data-post-id="<?php echo esc_attr( $post_id ); ?>">
                                                👁️
                                            </a>
                                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>" class="rental-order-list-btn rental-order-list-btn-edit">
                                                ✏️
                                            </a>
                                        </div>
                                    </td>
								<?php
									} else {
								?>
                                    <td>
                                        <div class="rental-order-list-action-buttons">
                                            <a href="javascript:void(0);" class="rental-order-list-btn rental-order-list-btn-view pro-overlay">
                                                👁️
                                            </a>
                                            <a href="javascript:void(0);" class="rental-order-list-btn rental-order-list-btn-edit pro-overlay">
                                                ✏️
                                            </a>
                                        </div>
                                    </td>
                                    <script>
										document.querySelectorAll('.pro-overlay').forEach(function (button) {
											button.replaceWith(button.cloneNode(true));
										});

										document.querySelectorAll('.pro-overlay').forEach(function (button) {
											button.addEventListener('click', function (event) {
												event.preventDefault(); // Prevent default link behavior
												window.open('<?php echo esc_js( esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/' ) ); ?>', '_blank');
											});
										});
									</script>
									<?php
								}
								?>
                            </tr>
                            <tr id="order-details-<?php echo esc_attr( $post_id ); ?>" class="order-details" style="display: none;">
                                <td colspan="12">
                                    <div class="order-details-content"></div>
                                </td>
                            </tr>
						<?php endwhile; else : ?>
                            <tr>
                                <td colspan="12"><?php esc_html_e( 'Sorry, No data found!', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                            </tr>
						<?php endif;
							wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                    </div>
                    
                    <div id="loader" style="display: none;">
                        <div class="loader"></div> <!-- Loader element -->
                    </div>
                    
                    <div class="rental-order-list-load-more-container">
                        <button id="load-more-btn" class="rental-order-list-load-more-btn" style="display: <?php echo ($total_orders > 10) ? 'block' : 'none'; ?>;"><?php esc_html_e( 'Load More Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const rows = document.querySelectorAll('.order-row');
                        let rowsPerPage = 10;
                        let currentlyShown = rowsPerPage;
                        let filteredRows = Array.from(rows);
                        
                        function displayRows(rowsToShow = filteredRows) {
                            // Hide all rows first
                            rows.forEach(row => row.style.display = 'none');
                            
                            // Show only the rows we want to display
                            rowsToShow.slice(0, currentlyShown).forEach(row => {
                                row.style.display = '';
                            });
                            
                            // Update load more button
                            const loadMoreBtn = document.getElementById('load-more-btn');
                            if (currentlyShown >= rowsToShow.length) {
                                loadMoreBtn.style.display = 'none';
                            } else {
                                loadMoreBtn.style.display = 'block';
                            }
                        }
                        
                        // Load more functionality
                        document.getElementById('load-more-btn').addEventListener('click', function() {
                            currentlyShown += rowsPerPage;
                            displayRows(filteredRows);
                        });
                        
                        // Search functionality
                        document.getElementById('search').addEventListener('keyup', function () {
                            const filter = this.value.toLowerCase();
                            filteredRows = Array.from(rows).filter(row => {
                                const orderId = row.cells[0].textContent.toLowerCase();
                                const billingName = row.cells[1].textContent.toLowerCase();
                                return orderId.includes(filter) || billingName.includes(filter);
                            });
                            currentlyShown = rowsPerPage; // Reset to initial load
                            displayRows(filteredRows);
                        });
                        
                        // Initial setup
                        displayRows(filteredRows);
                    });
                </script>
                <script>
					document.addEventListener('DOMContentLoaded', function () {
						document.querySelectorAll('.rbfw_order_view_btn').forEach(function (button) {
							button.addEventListener('click', function () {
								const postId = this.getAttribute('data-post-id'); // Ensure post_id is sanitized on the server side
								const orderDetailsRow = document.getElementById(`order-details-${postId}`);
								const loader = document.getElementById('loader');
								
								if (orderDetailsRow.style.display === 'none') {
									// Show the loader
									loader.style.display = 'flex';
									// Make an AJAX request to fetch the order details
									fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
										method: 'POST',
										headers: {
											'Content-Type': 'application/x-www-form-urlencoded',
										},
										body: new URLSearchParams({
											action: 'fetch_order_details',
											post_id: postId,
                                            'nonce': rbfw_ajax.nonce
										})
									})
										.then(response => response.text())
										.then(data => {
											orderDetailsRow.querySelector('.order-details-content').innerHTML = data;
											orderDetailsRow.style.display = 'table-row';
											// Hide the loader
											loader.style.display = 'none';
										})
										.catch(error => {
											console.error('Error:', error);
											// Hide the loader in case of an error
											loader.style.display = 'none';
										});
								} else {
									orderDetailsRow.style.display = 'none';
								}
							});
						});
					});
				</script>
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
				$post_id = get_the_id();
				wp_nonce_field( 'rbfw_ticket_type_nonce', 'rbfw_ticket_type_nonce' );
				?>
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
		}
	}
	$rbfw = new MageRBFWClass();
