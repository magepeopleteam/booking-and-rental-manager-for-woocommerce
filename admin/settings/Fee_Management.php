<?php
/*
 * Fee Management for Booking and Rental Manager
 * @Author 		mage people
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
			
			// Enqueue scripts and styles
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			
			// AJAX handlers
			add_action( 'wp_ajax_rbfw_save_fee_data', [ $this, 'ajax_save_fee_data' ] );
		}

		public function enqueue_scripts( $hook ) {
			global $post_type;
			
			// Check if we're on the right page
			if ( $post_type == 'rbfw_item' || strpos( $hook, 'rbfw_item' ) !== false ) {
				wp_enqueue_script( 'jquery' );
				
				// Enqueue CSS
				wp_enqueue_style( 
					'rbfw-fee-management', 
					plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/fee-management.css', 
					array(), 
					'1.0.0' 
				);
				
				// Enqueue JavaScript
				wp_enqueue_script( 
					'rbfw-fee-management', 
					plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/fee-management.js', 
					array( 'jquery' ), 
					'1.0.0', 
					true 
				);
				
				// Add inline CSS as backup
				wp_add_inline_style( 'rbfw-fee-management', '
					.wprently_fee-container { max-width: 1400px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
					.wprently_fee-table { width: 100%; border-collapse: collapse; }
					.wprently_fee-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #4a5568; text-transform: uppercase; background: #f7fafc; }
					.wprently_fee-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
					.wprently_fee-input { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 13px; width: 100%; }
					.wprently_fee-input:focus { outline: none; border-color: #e91e63; }
					.wprently_fee-toggle { position: relative; width: 44px; height: 24px; }
					.wprently_fee-toggle input { opacity: 0; width: 0; height: 0; }
					.wprently_fee-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e0; transition: .3s; border-radius: 24px; }
					.wprently_fee-slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .3s; border-radius: 50%; }
					.wprently_fee-toggle input:checked + .wprently_fee-slider { background: #10b981; }
					.wprently_fee-toggle input:checked + .wprently_fee-slider:before { transform: translateX(20px); }
					.wprently_fee-add-btn { width: 200px; padding: 10px 20px; background: #e91e63; border: none; border-radius: 6px; color: white; font-size: 14px; font-weight: 600; cursor: pointer; }
					.wprently_fee-add-btn:hover { background: #c2185b; }
				' );
			}
		}

		public function add_tab_menu() {
			?>
			<li data-target-tabs="#rbfw_fee_management">
				<i class="fas fa-dollar-sign"></i><?php esc_html_e( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?>
			</li>
			<?php
		}

		public function add_tabs_content( $post_id ) {
			$fees = get_post_meta( $post_id, 'rbfw_fees', true );
			$fees = is_array( $fees ) ? $fees : [];
			
			?>
			<style>
			/* Fee Management Styles - Direct CSS */
			.wprently_fee-container { max-width: 100%; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
			.wprently_fee-header { background: white; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
			.wprently_fee-header h1 { font-size: 24px; color: #1a202c; margin-bottom: 4px; }
			.wprently_fee-header p { color: #718096; font-size: 14px; }
			.wprently_fee-table-wrap { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto; overflow-y: visible; position: relative; }
			.wprently_fee-table-wrap::-webkit-scrollbar { height: 8px; }
			.wprently_fee-table-wrap::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
			.wprently_fee-table-wrap::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
			.wprently_fee-table-wrap::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
			.wprently_fee-table { min-width: 1400px; width: 100%; border-collapse: collapse; }
			.wprently_fee-table thead { background: #f7fafc; border-bottom: 2px solid #e2e8f0; }
			.wprently_fee-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #4a5568; text-transform: uppercase; white-space: nowrap; }
			.wprently_fee-table th:nth-child(1) { width: 280px; min-width: 280px; }
			.wprently_fee-table th:nth-child(2) { width: 180px; min-width: 180px; }
			.wprently_fee-table th:nth-child(3) { width: 150px; min-width: 150px; }
			.wprently_fee-table th:nth-child(4) { width: 140px; min-width: 140px; }
			.wprently_fee-table th:nth-child(5) { width: 120px; min-width: 120px; }
			.wprently_fee-table th:nth-child(6) { width: 140px; min-width: 140px; }
			.wprently_fee-table th:nth-child(7) { width: 120px; min-width: 120px; }
			.wprently_fee-table th:nth-child(8) { width: 100px; min-width: 100px; }
			.wprently_fee-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
			.wprently_fee-table tr:last-child td { border-bottom: none; }
			.wprently_fee-table tbody tr:hover { background: #f7fafc; }
			.wprently_fee-type { display: flex; align-items: center; gap: 12px; }
			.wprently_fee-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
			.wprently_fee-icon.security { background: #fee; }
			.wprently_fee-icon.insurance { background: #e3f2fd; }
			.wprently_fee-icon.cleaning { background: #f3e5f5; }
			.wprently_fee-icon.pet { background: #fff3e0; }
			.wprently_fee-info { display: flex; flex-direction: column; }
			.wprently_fee-input { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 13px; color: #1a202c; width: 100%; background: white; }
			.wprently_fee-input:focus { outline: none; border-color: #e91e63; }
			.wprently_fee-amount { display: flex; align-items: center; gap: 8px; }
			.wprently_fee-amount input { width: 80px; }
			.wprently_fee-amount span { font-size: 13px; color: #718096; }
			.wprently_fee-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
			.wprently_fee-badge.refundable { background: #d4edda; color: #155724; }
			.wprently_fee-badge.non-refundable { background: #f8d7da; color: #721c24; }
			.wprently_fee-badge.taxable { background: #fff3cd; color: #856404; }
			.wprently_fee-badges { display: flex; gap: 6px; flex-wrap: wrap; }
			.wprently_fee-toggle { position: relative; width: 44px; height: 24px; }
			.wprently_fee-toggle input { opacity: 0; width: 0; height: 0; }
			.wprently_fee-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e0; transition: .3s; border-radius: 24px; }
			.wprently_fee-slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .3s; border-radius: 50%; }
			.wprently_fee-toggle input:checked + .wprently_fee-slider { background: #10b981; }
			.wprently_fee-toggle input:checked + .wprently_fee-slider:before { transform: translateX(20px); }
			.wprently_fee-status { display: flex; flex-direction: column; gap: 8px; align-items: flex-start; }
			.wprently_fee-status-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; gap: 6px; white-space: nowrap; }
			.wprently_fee-status-badge.active { background: #d1fae5; color: #065f46; }
			.wprently_fee-status-badge.inactive { background: #fee2e2; color: #991b1b; }
			.wprently_fee-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
			.wprently_fee-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
			.wprently_fee-btn-icon { background: none; border: none; cursor: pointer; padding: 6px; color: #718096; font-size: 18px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
			.wprently_fee-btn-icon:hover { background: #f7fafc; color: #e91e63; }
			.wprently_fee-add-wrap { display: flex; justify-content: center; margin-top: 16px; }
			.wprently_fee-add-btn { width: 200px; padding: 10px 20px; background: #e91e63; border: none; border-radius: 6px; color: white; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
			.wprently_fee-add-btn:hover { background: #c2185b; }
			.wprently_fee-save-btn:hover { background: #059669; }
			.wprently_fee-saved-btn { background: #10b981 !important; }
			.wprently_fee-priority { display: flex; align-items: center; gap: 8px; }
			.wprently_fee-priority-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
			.wprently_fee-priority-badge.required { background: #fee2e2; color: #dc2626; }
			.wprently_fee-priority-badge.optional { background: #dbeafe; color: #2563eb; }
			.wprently_fee-input { font-size: 13px !important; color: #1a202c !important; }
			.wprently_fee-table td { font-size: 13px; color: #1a202c; }
			.wprently_fee-options { display: flex; flex-direction: column; gap: 8px; }
			.wprently_fee-option-item { display: flex; align-items: center; gap: 8px; }
			.wprently_fee-option-label { font-size: 12px; color: #4a5568; font-weight: 500; min-width: 60px; }
			.wprently_fee-option-item:not(:last-child) { margin-bottom: 4px; }
			</style>
			
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_fee_management">
				<input type="hidden" id="rbfw_fees_data" name="rbfw_fees" value="">
				<div class="wprently_fee-container">
					<div class="wprently_fee-header">
						<h1><?php echo esc_html__( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
						<p><?php echo esc_html__( 'Configure multiple fees with different calculation types and frequencies', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>

					<div class="wprently_fee-table-wrap">
						<div style="padding: 8px 16px; background: #f7fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; color: #718096; text-align: center;">
							← Scroll horizontally to see all columns →
						</div>
						<table class="wprently_fee-table">
							<thead>
								<tr>
									<th style="width: 280px;"><?php esc_html_e( 'Fee Type & Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 180px;"><?php esc_html_e( 'Calculation', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 150px;"><?php esc_html_e( 'Frequency', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 140px;"><?php esc_html_e( 'When to Apply', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 120px;"><?php esc_html_e( 'Priority', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 140px;"><?php esc_html_e( 'Options', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 120px;"><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody id="wprently_fee_body">
								<?php if ( ! empty( $fees ) ) : ?>
									<?php foreach ( $fees as $index => $fee ) : ?>
										<?php $this->render_fee_row( $index, $fee ); ?>
									<?php endforeach; ?>
								<?php else : ?>
									<tr id="no-fees-message">
										<td colspan="8" style="text-align: center; padding: 40px; color: #718096; font-style: italic;">
											No fees configured yet. Click "Add Fee" to create your first fee.
										</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<div class="wprently_fee-add-wrap">
						<button class="wprently_fee-add-btn" id="add-fee-row">
							<span style="font-size: 18px;">+</span><?php esc_html_e( 'Add Fee', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
						<button type="button" class="wprently_fee-save-btn" id="save-fees-btn" style="margin-left: 10px; background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
							💾 <?php esc_html_e( 'Save Fees', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
			</div>
			
			<script>
			// Fee Management JavaScript - Direct JS
			jQuery(document).ready(function($) {
				// Add new fee row
				$('#add-fee-row').on('click', function() {
					// Hide the "no fees" message if it exists
					$('#no-fees-message').remove();
					
					var newRow = `
						<tr data-fee-id="${Date.now()}">
							<td>
								<div class="wprently_fee-type">
									<div class="wprently_fee-icon security">🔒</div>
									<div class="wprently_fee-info">
										<input type="text" class="wprently_fee-input fee-label" placeholder="Fee label">
										<input type="text" class="wprently_fee-input fee-description" placeholder="Description">
									</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-amount">
									<select class="wprently_fee-input fee-calculation-type">
										<option value="percentage">Percentage</option>
										<option value="fixed" selected>Fixed</option>
									</select>
									<input type="number" class="wprently_fee-input fee-amount" value="0">
									<span>$</span>
								</div>
							</td>
							<td>
								<select class="wprently_fee-input fee-frequency">
									<option value="one-time" selected>One-time</option>
									<option value="per-day">Per day</option>
									<option value="per-night">Per night</option>
								</select>
							</td>
							<td>
								<select class="wprently_fee-input fee-apply-when">
									<option value="at-booking" selected>At booking</option>
									<option value="at-check-in">At check-in</option>
								</select>
							</td>
							<td>
								<div class="wprently_fee-priority">
									<select class="wprently_fee-input fee-priority">
										<option value="optional" selected>Optional</option>
										<option value="required">Required</option>
									</select>
									<div class="wprently_fee-priority-badge optional">Optional</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-options">
									<div class="wprently_fee-option-item">
										<label class="wprently_fee-toggle">
											<input type="checkbox" class="fee-refundable" checked>
											<span class="wprently_fee-slider"></span>
										</label>
										<span class="wprently_fee-option-label">Refundable</span>
									</div>
									<div class="wprently_fee-option-item">
										<label class="wprently_fee-toggle">
											<input type="checkbox" class="fee-taxable" checked>
											<span class="wprently_fee-slider"></span>
										</label>
										<span class="wprently_fee-option-label">Taxable</span>
									</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-status">
									<label class="wprently_fee-toggle">
										<input type="checkbox" class="fee-status" checked onchange="updateStatus(this)">
										<span class="wprently_fee-slider"></span>
									</label>
									<div class="wprently_fee-status-badge active">
										<span class="wprently_fee-status-dot"></span>Active
									</div>
								</div>
							</td>
							<td>
								<div class="wprently_fee-actions">
									<button class="wprently_fee-btn-icon duplicate-fee">⎘</button>
									<button class="wprently_fee-btn-icon delete-fee">✕</button>
								</div>
							</td>
						</tr>
					`;
					$('#wprently_fee_body').append(newRow);
				});

				// Delete fee row
				$(document).on('click', '.delete-fee', function() {
					if (confirm('Are you sure you want to delete this fee?')) {
						$(this).closest('tr').remove();
						
						// Show "no fees" message if no rows left
						if ($('#wprently_fee_body tr').length === 0) {
							$('#wprently_fee_body').append(`
								<tr id="no-fees-message">
									<td colspan="8" style="text-align: center; padding: 40px; color: #718096; font-style: italic;">
										No fees configured yet. Click "Add Fee" to create your first fee.
									</td>
								</tr>
							`);
						}
					}
				});

				// Duplicate fee row
				$(document).on('click', '.duplicate-fee', function() {
					var row = $(this).closest('tr');
					var newRow = row.clone();
					newRow.attr('data-fee-id', Date.now());
					row.after(newRow);
				});

				// Update status
				window.updateStatus = function(checkbox) {
					var statusBadge = $(checkbox).closest('.wprently_fee-status').find('.wprently_fee-status-badge');
					if (checkbox.checked) {
						statusBadge.removeClass('inactive').addClass('active').html('<span class="wprently_fee-status-dot"></span>Active');
					} else {
						statusBadge.removeClass('active').addClass('inactive').html('<span class="wprently_fee-status-dot"></span>Inactive');
					}
				};

				// Initialize status on page load
				$('.fee-status').each(function() {
					updateStatus(this);
				});

				// Update calculation type
				$(document).on('change', '.fee-calculation-type', function() {
					var span = $(this).closest('.wprently_fee-amount').find('span');
					if ($(this).val() === 'percentage') {
						span.text('%');
					} else {
						span.text('$');
					}
				});

				// Initialize calculation type on page load
				$('.fee-calculation-type').each(function() {
					var span = $(this).closest('.wprently_fee-amount').find('span');
					if ($(this).val() === 'percentage') {
						span.text('%');
					} else {
						span.text('$');
					}
				});

				// Update option labels visibility
				$(document).on('change', '.fee-refundable', function() {
					var label = $(this).closest('.wprently_fee-option-item').find('.wprently_fee-option-label');
					if ($(this).is(':checked')) {
						label.css('opacity', '1');
					} else {
						label.css('opacity', '0.5');
					}
				});

				$(document).on('change', '.fee-taxable', function() {
					var label = $(this).closest('.wprently_fee-option-item').find('.wprently_fee-option-label');
					if ($(this).is(':checked')) {
						label.css('opacity', '1');
					} else {
						label.css('opacity', '0.5');
					}
				});

				// Function to collect all fee data
				function collectFeeData() {
					var fees = [];
					$('#wprently_fee_body tr').each(function() {
						var $row = $(this);
						
						// Only collect data if there's actual content
						var label = $row.find('.fee-label').val();
						var amount = $row.find('.fee-amount').val();
						
						// Skip completely empty rows
						if (!label && !amount) {
							return;
						}
						
						// Only include rows with meaningful data
						if (label.trim() !== '' || parseFloat(amount) > 0) {
							var fee = {
								label: label || '',
								description: $row.find('.fee-description').val() || '',
								calculation_type: $row.find('.fee-calculation-type').val() || 'fixed',
								amount: amount || '0',
								frequency: $row.find('.fee-frequency').val() || 'one-time',
								apply_when: $row.find('.fee-apply-when').val() || 'at-booking',
								priority: $row.find('.fee-priority').val() || 'optional',
								refundable: $row.find('.fee-refundable').is(':checked') ? true : false,
								taxable: $row.find('.fee-taxable').is(':checked') ? true : false,
								status: $row.find('.fee-status').is(':checked') ? 'active' : 'inactive',
								icon: '🔒',
								color: 'security'
							};
							fees.push(fee);
						}
					});
					return fees;
				}

				// Update hidden field whenever data changes
				function updateHiddenField() {
					var fees = collectFeeData();
					$('#rbfw_fees_data').val(JSON.stringify(fees));
				}

				// Update hidden field on any change
				$(document).on('change input', '.wprently_fee-input, .fee-refundable, .fee-taxable, .fee-status, .fee-priority', function() {
					updateHiddenField();
				});

				// Update priority badge
				$(document).on('change', '.fee-priority', function() {
					var $badge = $(this).closest('.wprently_fee-priority').find('.wprently_fee-priority-badge');
					var value = $(this).val();
					$badge.removeClass('optional required').addClass(value).text(value.charAt(0).toUpperCase() + value.slice(1));
				});

				// Update hidden field when adding new row
				$(document).on('click', '#add-fee-row', function() {
					setTimeout(updateHiddenField, 100);
				});

				// Update hidden field when deleting or duplicating
				$(document).on('click', '.delete-fee, .duplicate-fee', function() {
					setTimeout(updateHiddenField, 100);
				});

				// Initialize hidden field on page load
				updateHiddenField();

				// Also save on WordPress form submission
				$('#post').on('submit', function() {
					updateHiddenField();
				});

				// Save button functionality
				$('#save-fees-btn').on('click', function() {
					var $btn = $(this);
					var originalText = $btn.html();
					
					$btn.html('⏳ Saving...').prop('disabled', true);
					
					// Update hidden field before saving
					updateHiddenField();
					
					// Get the current post ID
					var postId = $('#post_ID').val();
					
					// Make AJAX call to save data
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'rbfw_save_fee_data',
							post_id: postId,
							fees: $('#rbfw_fees_data').val(),
							nonce: '<?php echo wp_create_nonce("rbfw_fee_nonce"); ?>'
						},
						success: function(response) {
							if (response.success) {
								$btn.html('✅ Saved!').removeClass('wprently_fee-save-btn').addClass('wprently_fee-saved-btn');
								setTimeout(function() {
									$btn.html(originalText).removeClass('wprently_fee-saved-btn').addClass('wprently_fee-save-btn').prop('disabled', false);
								}, 2000);
							} else {
								$btn.html('❌ Error').prop('disabled', false);
								setTimeout(function() {
									$btn.html(originalText).removeClass('wprently_fee-saved-btn').addClass('wprently_fee-save-btn');
								}, 2000);
							}
						},
						error: function() {
							$btn.html('❌ Error').prop('disabled', false);
							setTimeout(function() {
								$btn.html(originalText).removeClass('wprently_fee-saved-btn').addClass('wprently_fee-save-btn');
							}, 2000);
						}
					});
				});

			});
			</script>
			<?php
		}

		public function render_fee_row( $index, $fee ) {
			$fee = wp_parse_args( $fee, $this->get_default_fee_data() );
			?>
			<tr>
				<td>
					<div class="wprently_fee-type">
						<div class="wprently_fee-icon <?php echo esc_attr( $fee['icon_class'] ); ?>"><?php echo esc_html( $fee['icon'] ); ?></div>
						<div class="wprently_fee-info">
							<input type="text" class="wprently_fee-input fee-label" value="<?php echo esc_attr( $fee['label'] ); ?>" placeholder="<?php esc_attr_e( 'Fee label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<input type="text" class="wprently_fee-input fee-description" value="<?php echo esc_attr( $fee['description'] ); ?>" placeholder="<?php esc_attr_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						</div>
					</div>
				</td>
				<td>
					<div class="wprently_fee-amount">
						<select class="wprently_fee-input fee-calculation-type" onchange="updateCalculationType(this)">
							<option value="percentage" <?php selected( $fee['calculation_type'], 'percentage' ); ?>>Percentage</option>
							<option value="fixed" <?php selected( $fee['calculation_type'], 'fixed' ); ?>>Fixed</option>
						</select>
						<input type="number" class="wprently_fee-input fee-amount" value="<?php echo esc_attr( $fee['amount'] ); ?>">
						<span><?php echo $fee['calculation_type'] === 'percentage' ? '%' : '$'; ?></span>
					</div>
				</td>
				<td>
					<select class="wprently_fee-input fee-frequency">
						<option value="one-time" <?php selected( $fee['frequency'], 'one-time' ); ?>>One-time</option>
						<option value="per-day" <?php selected( $fee['frequency'], 'per-day' ); ?>>Per day</option>
						<option value="per-night" <?php selected( $fee['frequency'], 'per-night' ); ?>>Per night</option>
					</select>
				</td>
				<td>
					<select class="wprently_fee-input fee-apply-when">
						<option value="at-booking" <?php selected( $fee['apply_when'], 'at-booking' ); ?>>At booking</option>
						<option value="at-check-in" <?php selected( $fee['apply_when'], 'at-check-in' ); ?>>At check-in</option>
					</select>
				</td>
				<td>
					<div class="wprently_fee-priority">
						<select class="wprently_fee-input fee-priority">
							<option value="optional" <?php selected( $fee['priority'], 'optional' ); ?>>Optional</option>
							<option value="required" <?php selected( $fee['priority'], 'required' ); ?>>Required</option>
						</select>
						<div class="wprently_fee-priority-badge <?php echo esc_attr( $fee['priority'] ); ?>">
							<?php echo esc_html( ucfirst( $fee['priority'] ) ); ?>
						</div>
					</div>
				</td>
				<td>
					<div class="wprently_fee-options">
						<div class="wprently_fee-option-item">
							<label class="wprently_fee-toggle">
								<input type="checkbox" class="fee-refundable" <?php checked( $fee['refundable'], true ); ?>>
								<span class="wprently_fee-slider"></span>
							</label>
							<span class="wprently_fee-option-label">Refundable</span>
						</div>
						<div class="wprently_fee-option-item">
							<label class="wprently_fee-toggle">
								<input type="checkbox" class="fee-taxable" <?php checked( $fee['taxable'], true ); ?>>
								<span class="wprently_fee-slider"></span>
							</label>
							<span class="wprently_fee-option-label">Taxable</span>
						</div>
					</div>
				</td>
				<td>
					<div class="wprently_fee-status">
						<label class="wprently_fee-toggle">
							<input type="checkbox" class="fee-status" <?php checked( $fee['status'], 'active' ); ?> onchange="updateStatus(this)">
							<span class="wprently_fee-slider"></span>
						</label>
						<div class="wprently_fee-status-badge <?php echo esc_attr( $fee['status'] ); ?>">
							<span class="wprently_fee-status-dot"></span><?php echo esc_html( ucfirst( $fee['status'] ) ); ?>
						</div>
					</div>
				</td>
				<td>
					<div class="wprently_fee-actions">
						<button class="wprently_fee-btn-icon duplicate-fee">⎘</button>
						<button class="wprently_fee-btn-icon delete-fee">✕</button>
					</div>
				</td>
			</tr>
			<?php
		}

		public function get_default_fee_data() {
			return [
				'label' => '',
				'description' => '',
				'calculation_type' => 'fixed',
				'amount' => '0',
				'frequency' => 'one-time',
				'apply_when' => 'at-booking',
				'priority' => 'optional',
				'refundable' => false,
				'taxable' => false,
				'status' => 'active',
				'icon' => '🔒',
				'icon_class' => 'security'
			];
		}

		public function settings_save( $post_id ) {
			// Check if this is an autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			
			// Check if user has permission to edit this post
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			
			// Check if this is the correct post type
			if ( get_post_type( $post_id ) != 'rbfw_item' ) {
				return;
			}
			
			// Save fee data if it exists
			if ( isset( $_POST['rbfw_fees'] ) && ! empty( $_POST['rbfw_fees'] ) ) {
				$fees_data = sanitize_text_field( wp_unslash( $_POST['rbfw_fees'] ) );
				$fees_data = json_decode( $fees_data, true );
				
				if ( is_array( $fees_data ) ) {
					update_post_meta( $post_id, 'rbfw_fees', $fees_data );
				}
			}
		}

		public function ajax_save_fee_data() {
			// Verify nonce
			if ( ! wp_verify_nonce( $_POST['nonce'], 'rbfw_fee_nonce' ) ) {
				wp_send_json_error( 'Invalid nonce' );
				return;
			}

			$post_id = intval( $_POST['post_id'] );
			$fees_data = sanitize_text_field( wp_unslash( $_POST['fees'] ) );
			
			// Check if user can edit this post
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( 'No permission to edit this post' );
				return;
			}

			// Decode JSON data
			$fees_array = json_decode( $fees_data, true );
			
			if ( is_array( $fees_array ) ) {
				update_post_meta( $post_id, 'rbfw_fees', $fees_array );
				wp_send_json_success( 'Fee data saved successfully' );
			} else {
				wp_send_json_error( 'Invalid fee data format' );
			}
		}
	}
	new RBFW_Fee_Management();
}
