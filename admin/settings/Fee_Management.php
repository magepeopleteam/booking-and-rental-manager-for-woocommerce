<?php
	/*
	 * @Author 		Shahnur Alam
	 * Fee Management Settings for Booking and Rental Manager
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Fee_Management' ) ) {
		class RBFW_Fee_Management {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
				//add_action( 'wp_ajax_rbfw_add_fee_row', array( $this, 'ajax_add_fee_row' ) );
				//add_action( 'wp_ajax_rbfw_delete_fee_row', array( $this, 'ajax_delete_fee_row' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			}

			public function add_tab_menu() {
				?>
				<li data-target-tabs="#rbfw_fee_management"><i class="fas fa-money-bill-wave"></i><?php esc_html_e( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
				<h2 class="mp_tab_item_title"><?php echo esc_html__( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
				<p class="mp_tab_item_description"><?php echo esc_html__( 'Configure multiple fees with different calculation types and frequencies', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
				<section class="bg-light mt-5">
					<div>
						<label><?php echo esc_html( $title ); ?></label>
						<p><?php echo esc_html( $description ); ?></p>
					</div>
				</section>
				<?php
			}

			public function enqueue_admin_scripts( $hook ) {
				global $post;
				if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
					if ( 'rbfw_item' == $post->post_type ) {
						// Enqueue fee management CSS on frontend
						wp_enqueue_style( 'rbfw-fee-management', RBFW_PLUGIN_URL . '/css/fee-management.css', array(), '1.0.0' );
						
						wp_localize_script( 'jquery', 'rbfw_fee_ajax', array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'nonce'    => wp_create_nonce( 'rbfw_fee_nonce' )
						) );
					}
				}
			}

			public function enqueue_frontend_scripts() {
				// Enqueue CSS on single rental item pages
				if ( is_singular( 'rbfw_item' ) || is_cart() || is_checkout() ) {
					wp_enqueue_style( 'rbfw-fee-management-frontend', RBFW_PLUGIN_URL . '/css/fee-management.css', array(), '1.0.0' );
				}
			}

			/**
			 * Add fee management content tab
			 * @param int $post_id
			 * @since 1.0.0
			 */
			public function add_tabs_content( $post_id ) {
				?>
				<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_fee_management">
					<?php $this->section_header(); ?>
					<?php $this->fee_management_table( $post_id ); ?>
				</div>
				<?php
			}

			/**
			 * Generate fee management table
			 * @param int $post_id
			 * @since 1.0.0
			 */
			public function fee_management_table( $post_id ) {
				$rbfw_fee_data = get_post_meta( $post_id, 'rbfw_fee_data', true );
				$rbfw_fee_data = is_array( $rbfw_fee_data ) ? $rbfw_fee_data : array();
				?>
				<div class="rbfw_fee_management_wrapper">
					<?php $this->panel_header( 'Fee Configuration Settings', 'Configure additional fees for your rental items' ); ?>
					<?php $this->render_fee_management_styles(); ?>
					<?php $this->render_fee_table( $rbfw_fee_data ); ?>
					<?php $this->render_fee_management_scripts(); ?>
				</div>
				<?php
			}

			private function render_fee_management_styles() {
				?>
				<!-- Fee Management CSS - Written by Shahnur Alam -->
				<style>
					.wprently_fee-container { max-width: 100%; margin: 0 auto; }
					.wprently_fee-table-wrap { 
						background: white; 
						border-radius: 8px; 
						box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
						overflow-x: auto; 
						overflow-y: visible;
						max-width: 100%;
						position: relative;
					}
					.wprently_fee-table { 
						width: 100%; 
						border-collapse: collapse; 
						min-width: 1140px;
						table-layout: fixed;
					}
					.wprently_fee-table thead { background: #f7fafc; border-bottom: 2px solid #e2e8f0; }
					.wprently_fee-table th { 
						padding: 12px 8px; 
						text-align: left; 
						font-size: 11px; 
						font-weight: 700; 
						color: #4a5568; 
						text-transform: uppercase;
						white-space: nowrap;
						position: sticky;
						top: 0;
						z-index: 10;
					}
					.wprently_fee-table td { 
						padding: 12px 8px; 
						border-bottom: 1px solid #e2e8f0;
						vertical-align: top;
					}
					.wprently_fee-table tr:last-child td { border-bottom: none; }
					.wprently_fee-table tbody tr:hover { background: #f7fafc; }
					.wprently_fee-type { display: flex; align-items: center; gap: 8px; }
					.wprently_fee-icon { 
						width: 32px; 
						height: 32px; 
						border-radius: 6px; 
						display: flex; 
						align-items: center; 
						justify-content: center; 
						font-size: 16px; 
						flex-shrink: 0; 
					}
					.wprently_fee-icon.security { background: #fee; }
					.wprently_fee-icon.insurance { background: #e3f2fd; }
					.wprently_fee-icon.cleaning { background: #f3e5f5; }
					.wprently_fee-icon.pet { background: #fff3e0; }
					.wprently_fee-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1; }
					.wprently_fee-input { 
						padding: 6px 8px; 
						border: 1px solid #e2e8f0; 
						border-radius: 4px; 
						font-size: 12px; 
						color: #1a202c; 
						width: 100%; 
						background: white;
						min-width: 0;
					}
					.wprently_fee-input:focus { outline: none; border-color: #e91e63; }
					.wprently_fee-amount { display: flex; align-items: center; gap: 6px; }
					.wprently_fee-amount input { width: 60px; min-width: 60px; }
					.wprently_fee-amount span { font-size: 12px; color: #718096; }
					.wprently_fee-badge { 
						display: inline-flex; 
						align-items: center; 
						padding: 3px 8px; 
						border-radius: 10px; 
						font-size: 10px; 
						font-weight: 600; 
						text-transform: uppercase;
						white-space: nowrap;
					}
					.wprently_fee-badge.refundable { background: #d4edda; color: #155724; }
					.wprently_fee-badge.non-refundable { background: #f8d7da; color: #721c24; }
					.wprently_fee-badge.taxable { background: #fff3cd; color: #856404; }
					.wprently_fee-badges { display: flex; gap: 4px; flex-wrap: wrap; }
					.wprently_fee-options { display: flex; flex-direction: column; gap: 8px; }
					.wprently_fee-option-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
					.wprently_fee-option-label { font-size: 11px; font-weight: 600; color: #4a5568; white-space: nowrap; }
					.wprently_fee-priority { display: flex; flex-direction: column; gap: 6px; align-items: center; }
					.wprently_fee-priority-select { width: 100%; max-width: 100px; }
					.wprently_fee-priority-badge { 
						display: inline-flex; 
						align-items: center; 
						justify-content: center;
						padding: 3px 8px; 
						border-radius: 10px; 
						font-size: 10px; 
						font-weight: 700; 
						text-transform: uppercase;
						white-space: nowrap;
						min-width: 45px;
					}
					.wprently_fee-priority-badge.required { background: #fee2e2; color: #991b1b; }
					.wprently_fee-priority-badge.optional { background: #d1fae5; color: #065f46; }
					.wprently_fee-toggle { position: relative; width: 40px; height: 22px; }
					.wprently_fee-toggle input { opacity: 0; width: 0; height: 0; }
					.wprently_fee-slider { 
						position: absolute; 
						cursor: pointer; 
						inset: 0; 
						background: #cbd5e0; 
						transition: .3s; 
						border-radius: 22px; 
					}
					.wprently_fee-slider:before { 
						content: ""; 
						position: absolute; 
						height: 16px; 
						width: 16px; 
						left: 3px; 
						bottom: 3px; 
						background: white; 
						transition: .3s; 
						border-radius: 50%; 
					}
					.wprently_fee-toggle input:checked + .wprently_fee-slider { background: #10b981; }
					.wprently_fee-toggle input:checked + .wprently_fee-slider:before { transform: translateX(18px); }
					.wprently_fee-status { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
					.wprently_fee-status-badge { 
						display: inline-flex; 
						align-items: center; 
						padding: 4px 8px; 
						border-radius: 4px; 
						font-size: 10px; 
						font-weight: 600; 
						gap: 4px; 
						white-space: nowrap; 
					}
					.wprently_fee-status-badge.active { background: #d1fae5; color: #065f46; }
					.wprently_fee-status-badge.inactive { background: #fee2e2; color: #991b1b; }
					.wprently_fee-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
					.wprently_fee-actions { display: flex; gap: 4px; align-items: center; justify-content: flex-end; }
					.wprently_fee-btn-icon { 
						background: none; 
						border: none; 
						cursor: pointer; 
						padding: 4px; 
						color: #718096; 
						font-size: 16px; 
						width: 28px; 
						height: 28px; 
						display: flex; 
						align-items: center; 
						justify-content: center; 
						border-radius: 4px; 
					}
					.wprently_fee-btn-icon:hover { background: #f7fafc; color: #e91e63; }
					.wprently_fee-add-wrap { display: flex; justify-content: center; margin-top: 16px; }
					.wprently_fee-add-btn { 
						width: 200px; 
						padding: 10px 20px; 
						background: #e91e63; 
						border: none; 
						border-radius: 6px; 
						color: white; 
						font-size: 14px; 
						font-weight: 600; 
						cursor: pointer; 
						display: flex; 
						align-items: center; 
						justify-content: center; 
						gap: 6px; 
					}
					.wprently_fee-add-btn:hover { background: #c2185b; }
					
					/* Table column specific widths for better layout */
					.wprently_fee-table th:nth-child(1) { width: 260px; min-width: 260px; }
					.wprently_fee-table th:nth-child(2) { width: 160px; min-width: 160px; }
					.wprently_fee-table th:nth-child(3) { width: 130px; min-width: 130px; }
					.wprently_fee-table th:nth-child(4) { width: 120px; min-width: 120px; }
					.wprently_fee-table th:nth-child(5) { width: 170px; min-width: 170px; }
					.wprently_fee-table th:nth-child(6) { width: 110px; min-width: 110px; }
					.wprently_fee-table th:nth-child(7) { width: 90px; min-width: 90px; }
					
					.wprently_fee-table td:nth-child(1) { width: 260px; min-width: 260px; }
					.wprently_fee-table td:nth-child(2) { width: 160px; min-width: 160px; }
					.wprently_fee-table td:nth-child(3) { width: 130px; min-width: 130px; }
					.wprently_fee-table td:nth-child(4) { width: 120px; min-width: 120px; }
					.wprently_fee-table td:nth-child(5) { width: 170px; min-width: 170px; }
					.wprently_fee-table td:nth-child(6) { width: 110px; min-width: 110px; }
					.wprently_fee-table td:nth-child(7) { width: 90px; min-width: 90px; }
					
					/* Scrollbar styling for better UX */
					.wprently_fee-table-wrap::-webkit-scrollbar {
						height: 8px;
					}
					.wprently_fee-table-wrap::-webkit-scrollbar-track {
						background: #f1f1f1;
						border-radius: 4px;
					}
					.wprently_fee-table-wrap::-webkit-scrollbar-thumb {
						background: #c1c1c1;
						border-radius: 4px;
					}
					.wprently_fee-table-wrap::-webkit-scrollbar-thumb:hover {
						background: #a8a8a8;
					}
					
					/* Responsive improvements */
					@media (max-width: 1400px) { 
						.wprently_fee-container {
							max-width: 100%;
							padding: 0 10px;
						}
						.wprently_fee-table-wrap { 
							overflow-x: auto;
							max-width: 100%;
						}
						.wprently_fee-table { 
							min-width: 1140px;
						}
					}
					
					@media (max-width: 1200px) { 
						.wprently_fee-container {
							max-width: 100%;
							padding: 0 10px;
						}
						.wprently_fee-table-wrap { 
							overflow-x: auto;
							max-width: calc(100vw - 40px);
						}
						.wprently_fee-table { 
							min-width: 1120px;
						}
					}
				</style>
				<?php
			}

			private function render_fee_table( $rbfw_fee_data ) {
				?>
				<div class="wprently_fee-container">
					<div class="rbfw-scroll-hint" style="text-align: center; margin-bottom: 10px; color: #666; font-size: 12px;">
						<em><?php echo esc_html__( '← Scroll horizontally to view all columns →', 'booking-and-rental-manager-for-woocommerce' ); ?></em>
					</div>
					<div class="wprently_fee-table-wrap">
						<table class="wprently_fee-table">
							<thead>
								<tr>
									<th><?php echo esc_html__( 'Fee Type & Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Calculation', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Frequency', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Priority', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Options', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php echo esc_html__( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody id="wprently_fee_body">
								<?php
								if ( ! empty( $rbfw_fee_data ) ) {
									foreach ( $rbfw_fee_data as $index => $fee ) {
										$this->render_fee_row( $index, $fee );
									}
								} else {
									// Default empty row
									$this->render_fee_row( 0, array() );
								}
								?>
							</tbody>
						</table>
					</div>

					<div class="wprently_fee-add-wrap">
						<button type="button" class="wprently_fee-add-btn" onclick="rbfwAddFeeRow()">
							<span style="font-size: 18px;">+</span><?php echo esc_html__( 'Add Fee', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
				<?php
			}

			/**
			 * Render individual fee row
			 * @param int $index
			 * @param array $fee
			 * @since 1.0.0
			 */
			private function render_fee_row( $index, $fee = array() ) {
				$label = isset( $fee['label'] ) ? $fee['label'] : '';
				$description = isset( $fee['description'] ) ? $fee['description'] : '';
				$calculation_type = isset( $fee['calculation_type'] ) ? $fee['calculation_type'] : 'fixed';
				$amount = isset( $fee['amount'] ) ? $fee['amount'] : '0';
				$frequency = isset( $fee['frequency'] ) ? $fee['frequency'] : 'one-time';
				$priority = isset( $fee['priority'] ) ? $fee['priority'] : 'optional'; // Added by Shahnur Alam - Priority field
				$refundable = isset( $fee['refundable'] ) ? $fee['refundable'] : 'no';
				$taxable = isset( $fee['taxable'] ) ? $fee['taxable'] : 'no';
				$status = isset( $fee['status'] ) ? $fee['status'] : 'active';
				$icon = isset( $fee['icon'] ) ? $fee['icon'] : '💰';
				$color = isset( $fee['color'] ) ? $fee['color'] : 'security';
				?>
				<tr>
					<td>
						<div class="wprently_fee-type">
							<div class="wprently_fee-icon <?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></div>
							<div class="wprently_fee-info">
								<input type="text" class="wprently_fee-input" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php echo esc_attr__( 'Fee label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								<input type="text" class="wprently_fee-input" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $description ); ?>" placeholder="<?php echo esc_attr__( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							</div>
						</div>
					</td>
					<td>
						<div class="wprently_fee-amount">
							<select class="wprently_fee-input" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][calculation_type]" onchange="rbfwUpdateCurrencySymbol(this)">
								<option value="percentage" <?php selected( $calculation_type, 'percentage' ); ?>><?php echo esc_html__( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="fixed" <?php selected( $calculation_type, 'fixed' ); ?>><?php echo esc_html__( 'Fixed', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
							</select>
							<input type="number" class="wprently_fee-input" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][amount]" value="<?php echo esc_attr( $amount ); ?>" step="0.01" min="0">
							<span><?php echo ( $calculation_type === 'percentage' ) ? '%' : '$'; ?></span>
						</div>
					</td>
					<td>
						<select class="wprently_fee-input" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][frequency]">
							<option value="one-time" <?php selected( $frequency, 'one-time' ); ?>><?php echo esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
							<option value="per-day" <?php selected( $frequency, 'per-day' ); ?>><?php echo esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
						</select>
					</td>
					<td>
						<div class="wprently_fee-priority">
							<select class="wprently_fee-input wprently_fee-priority-select" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][priority]" onchange="rbfwUpdatePriorityBadge(this)">
								<option value="optional" <?php selected( $priority, 'optional' ); ?>><?php echo esc_html__( 'Optional', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="required" <?php selected( $priority, 'required' ); ?>><?php echo esc_html__( 'Required', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
							</select>
							<span class="wprently_fee-priority-badge <?php echo esc_attr( $priority ); ?>" id="priority-badge-<?php echo esc_attr( $index ); ?>">
								<?php 
								$priority_labels = array(
									'required' => __( 'Required', 'booking-and-rental-manager-for-woocommerce' ),
									'optional' => __( 'Optional', 'booking-and-rental-manager-for-woocommerce' )
								);
								echo esc_html( $priority_labels[$priority] ?? $priority_labels['optional'] );
								?>
							</span>
						</div>
					</td>
					<td>
						<div class="wprently_fee-options">
							<div class="wprently_fee-option-item">
								<label class="wprently_fee-option-label"><?php echo esc_html__( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<label class="wprently_fee-toggle">
									<input type="checkbox" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][refundable]" value="<?php echo esc_attr( $refundable ); ?>" <?php checked( $refundable, 'yes' ); ?> onchange="rbfwUpdateRefundableStatus(this)">
									<span class="wprently_fee-slider"></span>
								</label>
							</div>
							<div class="wprently_fee-badges">
								<span class="wprently_fee-badge <?php echo ( $refundable === 'yes' ) ? 'refundable' : 'non-refundable'; ?>" id="refundable-badge-<?php echo esc_attr( $index ); ?>">
									<?php echo ( $refundable === 'yes' ) ? esc_html__( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ) : esc_html__( 'Non-refund', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</span>
								<?php if ( $taxable === 'yes' ) : ?>
									<span class="wprently_fee-badge taxable"><?php echo esc_html__( 'Taxable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<input type="hidden" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][taxable]" value="<?php echo esc_attr( $taxable ); ?>">
						<input type="hidden" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $icon ); ?>">
						<input type="hidden" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][color]" value="<?php echo esc_attr( $color ); ?>">
					</td>
					<td>
						<div class="wprently_fee-status">
							<label class="wprently_fee-toggle">
								<input type="checkbox" name="rbfw_fee_data[<?php echo esc_attr( $index ); ?>][status]" value="<?php echo esc_attr( $status ); ?>" <?php checked( $status, 'active' ); ?> onchange="rbfwUpdateStatus(this)">
								<span class="wprently_fee-slider"></span>
							</label>
							<div class="wprently_fee-status-badge <?php echo esc_attr( $status ); ?>">
								<span class="wprently_fee-status-dot"></span><?php echo esc_html( ucfirst( $status ) ); ?>
							</div>
						</div>
					</td>
					<td>
						<div class="wprently_fee-actions">
							<button type="button" class="wprently_fee-btn-icon" onclick="rbfwDuplicateFeeRow(this)" title="<?php echo esc_attr__( 'Duplicate', 'booking-and-rental-manager-for-woocommerce' ); ?>">⎘</button>
							<button type="button" class="wprently_fee-btn-icon" onclick="rbfwDeleteFeeRow(this)" title="<?php echo esc_attr__( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>">✕</button>
						</div>
					</td>
				</tr>
				<?php
			}

			private function render_fee_management_scripts() {
				?>
				<!-- Fee Management JavaScript - Written by Shahnur Alam -->
				<script type="text/javascript">
					const rbfwFeeIcons = ['🔒', '🛡️', '🧹', '🐾', '💰'];
					const rbfwFeeColors = ['security', 'insurance', 'cleaning', 'pet'];

					/**
					 * Add new fee row
					 * @since 1.0.0
					 */
					function rbfwAddFeeRow() {
						const tbody = document.getElementById('wprently_fee_body');
						const rowCount = tbody.rows.length;
						const icon = rbfwFeeIcons[Math.floor(Math.random() * rbfwFeeIcons.length)];
						const color = rbfwFeeColors[Math.floor(Math.random() * rbfwFeeColors.length)];
						
						const row = tbody.insertRow();
						row.innerHTML = `
							<td>
								<div class="wprently_fee-type">
									<div class="wprently_fee-icon ${color}">${icon}</div>
									<div class="wprently_fee-info">
										<input type="text" class="wprently_fee-input" name="rbfw_fee_data[${rowCount}][label]" placeholder="<?php echo esc_attr__( 'Fee label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
										<input type="text" class="wprently_fee-input" name="rbfw_fee_data[${rowCount}][description]" placeholder="<?php echo esc_attr__( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-amount">
									<select class="wprently_fee-input" name="rbfw_fee_data[${rowCount}][calculation_type]" onchange="rbfwUpdateCurrencySymbol(this)">
										<option value="percentage"><?php echo esc_html__( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
										<option value="fixed" selected><?php echo esc_html__( 'Fixed', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									</select>
									<input type="number" class="wprently_fee-input" name="rbfw_fee_data[${rowCount}][amount]" value="0" step="0.01" min="0">
									<span>$</span>
								</div>
							</td>
							<td>
								<select class="wprently_fee-input" name="rbfw_fee_data[${rowCount}][frequency]">
									<option value="one-time" selected><?php echo esc_html__( 'One-time', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									<option value="per-day"><?php echo esc_html__( 'Per day', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									<option value="per-night"><?php echo esc_html__( 'Per night', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								</select>
							</td>
							<td>
								<div class="wprently_fee-priority">
									<select class="wprently_fee-input wprently_fee-priority-select" name="rbfw_fee_data[${rowCount}][priority]" onchange="rbfwUpdatePriorityBadge(this)">
										<option value="optional" selected><?php echo esc_html__( 'Optional', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
										<option value="required"><?php echo esc_html__( 'Required', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									</select>
									<span class="wprently_fee-priority-badge optional" id="priority-badge-${rowCount}"><?php echo esc_html__( 'Optional', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								</div>
							</td>
							<td>
								<div class="wprently_fee-options">
									<div class="wprently_fee-option-item">
										<label class="wprently_fee-option-label"><?php echo esc_html__( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
										<label class="wprently_fee-toggle">
											<input type="checkbox" name="rbfw_fee_data[${rowCount}][refundable]" value="no" onchange="rbfwUpdateRefundableStatus(this)">
											<span class="wprently_fee-slider"></span>
										</label>
									</div>
									<div class="wprently_fee-badges">
										<span class="wprently_fee-badge non-refundable" id="refundable-badge-${rowCount}"><?php echo esc_html__( 'Non-refund', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
									</div>
								</div>
								<input type="hidden" name="rbfw_fee_data[${rowCount}][taxable]" value="no">
								<input type="hidden" name="rbfw_fee_data[${rowCount}][icon]" value="${icon}">
								<input type="hidden" name="rbfw_fee_data[${rowCount}][color]" value="${color}">
							</td>
							<td>
								<div class="wprently_fee-status">
									<label class="wprently_fee-toggle">
										<input type="checkbox" name="rbfw_fee_data[${rowCount}][status]" value="active" checked onchange="rbfwUpdateStatus(this)">
										<span class="wprently_fee-slider"></span>
									</label>
									<div class="wprently_fee-status-badge active">
										<span class="wprently_fee-status-dot"></span><?php echo esc_html__( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?>
									</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-actions">
									<button type="button" class="wprently_fee-btn-icon" onclick="rbfwDuplicateFeeRow(this)" title="<?php echo esc_attr__( 'Duplicate', 'booking-and-rental-manager-for-woocommerce' ); ?>">⎘</button>
									<button type="button" class="wprently_fee-btn-icon" onclick="rbfwDeleteFeeRow(this)" title="<?php echo esc_attr__( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>">✕</button>
								</div>
							</td>
						`;
					}

					/**
					 * Delete fee row
					 * @param {HTMLElement} btn
					 * @since 1.0.0
					 */
					function rbfwDeleteFeeRow(btn) {
						if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this fee?', 'booking-and-rental-manager-for-woocommerce' ) ); ?>')) {
							btn.closest('tr').remove();
							rbfwReindexFeeRows();
						}
					}

					/**
					 * Duplicate fee row
					 * @param {HTMLElement} btn
					 * @since 1.0.0
					 */
					function rbfwDuplicateFeeRow(btn) {
						const row = btn.closest('tr');
						const newRow = row.cloneNode(true);
						row.parentNode.insertBefore(newRow, row.nextSibling);
						rbfwReindexFeeRows();
					}

					/**
					 * Update priority badge when priority changes
					 * @param {HTMLElement} select
					 * @since 1.0.0
					 * Written by Shahnur Alam
					 */
					function rbfwUpdatePriorityBadge(select) {
						const priority = select.value;
						const row = select.closest('tr');
						const tbody = row.parentElement;
						const rowIndex = Array.from(tbody.children).indexOf(row);
						const badge = document.getElementById(`priority-badge-${rowIndex}`);
						
						if (badge) {
							badge.className = `wprently_fee-priority-badge ${priority}`;
							
							const priorityLabels = {
								'required': '<?php echo esc_js( __( 'Required', 'booking-and-rental-manager-for-woocommerce' ) ); ?>',
								'optional': '<?php echo esc_js( __( 'Optional', 'booking-and-rental-manager-for-woocommerce' ) ); ?>'
							};
							
							badge.textContent = priorityLabels[priority] || priorityLabels['optional'];
						}
					}
					
					/**
					 * Update refundable status
					 * @param {HTMLElement} checkbox
					 * @since 1.0.0
					 */
					function rbfwUpdateRefundableStatus(checkbox) {
						// Find the row index to locate the badge
						const row = checkbox.closest('tr');
						const tbody = row.parentElement;
						const rowIndex = Array.from(tbody.children).indexOf(row);
						const badge = document.getElementById(`refundable-badge-${rowIndex}`);
						
						if (checkbox.checked) {
							checkbox.value = 'yes';
							if (badge) {
								badge.className = 'wprently_fee-badge refundable';
								badge.textContent = '<?php echo esc_js( __( 'Refundable', 'booking-and-rental-manager-for-woocommerce' ) ); ?>';
							}
						} else {
							checkbox.value = 'no';
							if (badge) {
								badge.className = 'wprently_fee-badge non-refundable';
								badge.textContent = '<?php echo esc_js( __( 'Non-refund', 'booking-and-rental-manager-for-woocommerce' ) ); ?>';
							}
						}
					}

					/**
					 * Update status display
					 * @param {HTMLElement} checkbox
					 * @since 1.0.0
					 */
					function rbfwUpdateStatus(checkbox) {
						const badge = checkbox.closest('td').querySelector('.wprently_fee-status-badge');
						if (checkbox.checked) {
							badge.className = 'wprently_fee-status-badge active';
							badge.innerHTML = '<span class="wprently_fee-status-dot"></span><?php echo esc_js( __( 'Active', 'booking-and-rental-manager-for-woocommerce' ) ); ?>';
							checkbox.value = 'active';
						} else {
							badge.className = 'wprently_fee-status-badge inactive';
							badge.innerHTML = '<span class="wprently_fee-status-dot"></span><?php echo esc_js( __( 'Inactive', 'booking-and-rental-manager-for-woocommerce' ) ); ?>';
							checkbox.value = 'inactive';
						}
					}

					/**
					 * Update currency symbol based on calculation type
					 * @param {HTMLElement} select
					 * @since 1.0.0
					 */
					function rbfwUpdateCurrencySymbol(select) {
						const span = select.parentElement.querySelector('span');
						span.textContent = select.value === 'percentage' ? '%' : '$';
					}

					/**
					 * Reindex all fee rows after add/delete operations
					 * @since 1.0.0
					 */
					function rbfwReindexFeeRows() {
						const tbody = document.getElementById('wprently_fee_body');
						const rows = tbody.querySelectorAll('tr');
						
						rows.forEach((row, index) => {
							const inputs = row.querySelectorAll('input, select');
							inputs.forEach(input => {
								if (input.name) {
									input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
								}
							});
							
							// Update badge IDs
							const refundableBadge = row.querySelector('[id^="refundable-badge-"]');
							if (refundableBadge) {
								refundableBadge.id = `refundable-badge-${index}`;
							}
													
							// Update priority badge IDs - Added by Shahnur Alam
							const priorityBadge = row.querySelector('[id^="priority-badge-"]');
							if (priorityBadge) {
								priorityBadge.id = `priority-badge-${index}`;
							}
						});
					}

					// Initialize existing rows on page load
					document.addEventListener('DOMContentLoaded', function() {
						const tbody = document.getElementById('wprently_fee_body');
						const existingRows = tbody.querySelectorAll('tr');
						
						existingRows.forEach(row => {
							const calcSelect = row.querySelector('select[name*="[calculation_type]"]');
							if (calcSelect) {
								rbfwUpdateCurrencySymbol(calcSelect);
							}
						});
					});
				</script>
				<?php
			}

			/**
			 * Save fee management settings
			 * @param int $post_id
			 * @since 1.0.0
			 */
			public function settings_save( $post_id ) {
				// Verify nonce - for security check
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}

				// Check if it's an autosave
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}

				// Check user permissions
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				// Only save if it's rbfw_item post type
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					// Process and save fee data
					if ( isset( $_POST['rbfw_fee_data'] ) && is_array( $_POST['rbfw_fee_data'] ) ) {
						$fee_data = array();
						
						foreach ( $_POST['rbfw_fee_data'] as $index => $fee ) {
							$clean_fee = array(
								'label'            => isset( $fee['label'] ) ? sanitize_text_field( wp_unslash( $fee['label'] ) ) : '',
								'description'      => isset( $fee['description'] ) ? sanitize_text_field( wp_unslash( $fee['description'] ) ) : '',
								'calculation_type' => isset( $fee['calculation_type'] ) ? sanitize_text_field( wp_unslash( $fee['calculation_type'] ) ) : 'fixed',
								'amount'           => isset( $fee['amount'] ) ? floatval( $fee['amount'] ) : 0,
								'frequency'        => isset( $fee['frequency'] ) ? sanitize_text_field( wp_unslash( $fee['frequency'] ) ) : 'one-time',
								'priority'         => isset( $fee['priority'] ) ? sanitize_text_field( wp_unslash( $fee['priority'] ) ) : 'optional', // Added by Shahnur Alam - Priority field saving
								'refundable'       => isset( $fee['refundable'] ) ? sanitize_text_field( wp_unslash( $fee['refundable'] ) ) : 'no',
								'taxable'          => isset( $fee['taxable'] ) ? sanitize_text_field( wp_unslash( $fee['taxable'] ) ) : 'no',
								'status'           => isset( $fee['status'] ) ? sanitize_text_field( wp_unslash( $fee['status'] ) ) : 'active',
								'icon'             => isset( $fee['icon'] ) ? sanitize_text_field( wp_unslash( $fee['icon'] ) ) : '💰',
								'color'            => isset( $fee['color'] ) ? sanitize_text_field( wp_unslash( $fee['color'] ) ) : 'security'
							);
							
							// Only save if label is not empty
							if ( ! empty( $clean_fee['label'] ) ) {
								$fee_data[] = $clean_fee;
							}
						}
						
						// Update post meta with sanitized fee data
						update_post_meta( $post_id, 'rbfw_fee_data', $fee_data );
					} else {
						// If no fee data, delete the meta
						delete_post_meta( $post_id, 'rbfw_fee_data' );
					}
				}
			}

			/**
			 * AJAX handler for adding fee row
			 * @since 1.0.0
			 */
			public function ajax_add_fee_row() {
				// Verify nonce for security
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_fee_nonce' ) ) {
					wp_die( 'Security check failed' );
				}

				$index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : 0;
				
				ob_start();
				$this->render_fee_row( $index, array() );
				$output = ob_get_clean();

				wp_send_json_success( $output );
			}

			/**
			 * AJAX handler for deleting fee row
			 * @since 1.0.0
			 */
			public function ajax_delete_fee_row() {
				// Verify nonce for security
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_fee_nonce' ) ) {
					wp_die( 'Security check failed' );
				}

				wp_send_json_success( 'Fee row deleted successfully' );
			}
		}
		new RBFW_Fee_Management();
	}
