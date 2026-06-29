<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   * Dummy Import with beautiful popup — same UX as MPWEM
   */
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.

// The WordPress media-sideload stack is heavy; it is loaded on demand in
// load_media_stack() only while an import is actually running.

if (!class_exists('RbfwImportDemo')) {
	class RbfwImportDemo {

		/** Option that stores the resumable import progress while it runs. */
		const STATE_OPTION = 'rbfw_import_state';

		public function __construct() {
			add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
			add_action('admin_footer', array($this, 'render_popup'));
			// New chunked endpoint — the browser calls this once per small unit of work.
			add_action('wp_ajax_rbfw_import_dummy_step', array($this, 'ajax_import_step'));
			// Back-compat endpoint (runs every remaining chunk in one request).
			add_action('wp_ajax_rbfw_import_dummy_data', array($this, 'ajax_import_dummy_data'));
			add_action('wp_ajax_rbfw_dismiss_dummy_import', array($this, 'ajax_dismiss_dummy_import'));
		}

		/**
		 * Check if dummy import is eligible.
		 */
		public function is_eligible() {
			if (get_option('rbfw_sample_rent_items') === 'yes') {
				return false;
			}

			// WooCommerce is optional (Standalone mode). The sample import only
			// creates rbfw_item posts; the backing WooCommerce product is created
			// lazily by RBFW_Hidden_Product, which itself no-ops without Woo. So
			// the import is safe to offer whether or not WooCommerce is active and
			// must not be gated behind it.
			if (self::check_plugin('booking-and-rental-manager-for-woocommerce', 'rent-manager.php') != 1) {
				return false;
			}

			// An import that started but did not finish (timeout, refresh, etc.)
			// can always be resumed, even if items already partially exist.
			if (is_array(get_option(self::STATE_OPTION))) {
				return true;
			}

			$count_posts    = wp_count_posts('rbfw_item');
			$count_existing = isset($count_posts->publish) ? (int) $count_posts->publish : 0;
			return $count_existing === 0;
		}

		/**
		 * Check if the popup should auto-show (not dismissed).
		 */
		private function should_auto_show_popup() {
			if (!$this->is_eligible()) {
				return false;
			}
			$dismissed = get_option('rbfw_dummy_import_dismissed');
			if ($dismissed == 'yes') {
				return false;
			}
			return true;
		}

		/**
		 * Enqueue CSS for popup (reuses the woo installer CSS).
		 */
		public function enqueue_assets() {
			if (!$this->is_eligible()) {
				return;
			}
			wp_enqueue_style(
				'rbfw-dummy-installer',
				RBFW_PLUGIN_URL . '/assets/admin/css/rbfw_woo_installer.css',
				array(),
				filemtime(RBFW_PLUGIN_DIR . '/assets/admin/css/rbfw_woo_installer.css')
			);
		}

		/**
		 * Render the dummy import popup in admin footer.
		 */
		public function render_popup() {
			if (!$this->is_eligible()) {
				return;
			}
			// If a previous import was interrupted, resume it automatically.
			$resume        = is_array(get_option(self::STATE_OPTION)) ? 1 : 0;
			$display_style = ($resume || $this->should_auto_show_popup()) ? '' : 'display: none;';
			?>
			<!-- RBFW Dummy Import Popup Overlay -->
			<div id="rbfw-woo-overlay" class="rbfw-woo-overlay rbfw-dummy-overlay" data-resume="<?php echo esc_attr($resume); ?>" style="<?php echo esc_attr($display_style); ?>">
				<div class="rbfw-woo-popup">
					<div class="rbfw-woo-header">
						<div class="rbfw-woo-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="rbfw-woo-header-text"><?php esc_html_e('Booking & Rental Manager', 'booking-and-rental-manager-for-woocommerce'); ?></span>
					</div>

					<div class="rbfw-woo-icon-wrapper">
						<div class="rbfw-woo-icon">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
								<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
					</div>

					<div class="rbfw-woo-content">
						<h2 class="rbfw-woo-title"><?php esc_html_e('Import Sample Rental Items?', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
						<p class="rbfw-woo-desc">
							<?php esc_html_e('Would you like to import sample rental items with categories, pricing, and settings to see how Booking & Rental Manager works?', 'booking-and-rental-manager-for-woocommerce'); ?>
						</p>
					</div>

					<div id="rbfw-woo-progress" class="rbfw-woo-progress" style="display:none;">
						<div class="rbfw-woo-progress-bar">
							<div id="rbfw-woo-progress-fill" class="rbfw-woo-progress-fill"></div>
						</div>
						<p id="rbfw-woo-status-text" class="rbfw-woo-status-text"></p>
					</div>

					<div class="rbfw-woo-actions">
						<button type="button" id="rbfw-dummy-install-btn" class="rbfw-woo-btn rbfw-woo-btn-primary">
							<span class="rbfw-woo-btn-icon">
								<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
									<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
							<span class="rbfw-woo-btn-text"><?php esc_html_e('Yes, Import Data', 'booking-and-rental-manager-for-woocommerce'); ?></span>
						</button>
						<button type="button" id="rbfw-dummy-dismiss-btn" class="rbfw-woo-btn rbfw-woo-btn-secondary">
							<?php esc_html_e('No, Skip', 'booking-and-rental-manager-for-woocommerce'); ?>
						</button>
					</div>
				</div>
			</div>

			<script>
			(function($) {
				$(document).ready(function() {
					var $overlay = $('#rbfw-woo-overlay.rbfw-dummy-overlay');
					if (!$overlay.length) return;

					var $popup      = $overlay.find('.rbfw-woo-popup');
					var $btn        = $('#rbfw-dummy-install-btn');
					var $dismissBtn = $('#rbfw-dummy-dismiss-btn');
					var $progress   = $('#rbfw-woo-progress');
					var $fill       = $('#rbfw-woo-progress-fill');
					var $status     = $('#rbfw-woo-status-text');
					var $actions    = $overlay.find('.rbfw-woo-actions');
					var isWorking   = false;

					var importNonce  = '<?php echo esc_js(wp_create_nonce("rbfw_import_dummy")); ?>';
					var dismissNonce = '<?php echo esc_js(wp_create_nonce("rbfw_dismiss_dummy")); ?>';
					var redirectUrl  = '<?php echo esc_js(admin_url("edit.php?post_type=rbfw_item")); ?>';
					var i18n = {
						working: '<?php echo esc_js(__("Importing sample data. This may take a moment...", "booking-and-rental-manager-for-woocommerce")); ?>',
						failed:  '<?php echo esc_js(__("Failed to import. Please try again.", "booking-and-rental-manager-for-woocommerce")); ?>',
						success: '<?php echo esc_js(__("Success!", "booking-and-rental-manager-for-woocommerce")); ?>',
						done:    '<?php echo esc_js(__("Sample data imported successfully. Redirecting to Rental List...", "booking-and-rental-manager-for-woocommerce")); ?>'
					};

					// Manual trigger from other pages — just open the popup; the
					// user confirms with "Yes, Import Data".
					$(document).on('click', '#rbfw-trigger-dummy-import-btn', function(e) {
						e.preventDefault();
						$overlay.css('display', 'flex').hide().fadeIn(300);
					});

					$btn.on('click', function(e) {
						e.preventDefault();
						startImport();
					});

					$dismissBtn.on('click', function(e) {
						e.preventDefault();
						if (isWorking) return;
						isWorking = true;
						$overlay.css('opacity', '0.5');
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: { action: 'rbfw_dismiss_dummy_import', nonce: dismissNonce },
							complete: function() {
								$overlay.fadeOut(300, function() { $(this).remove(); });
							}
						});
					});

					function startImport() {
						if (isWorking) return;
						isWorking = true;
						$btn.prop('disabled', true);
						$dismissBtn.prop('disabled', true);
						$actions.slideUp(250);
						$progress.slideDown(300);
						$status.text(i18n.working).removeClass('rbfw-success rbfw-error');
						runStep();
					}

					// Each call processes ONE small chunk on the server. PHP frees all
					// its memory when the request ends, so the next chunk starts fresh —
					// this is what keeps the import safe on tiny memory limits.
					function runStep() {
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: { action: 'rbfw_import_dummy_step', nonce: importNonce },
							success: function(response) {
								if (response && response.success && response.data) {
									var d = response.data;
									$fill.css('width', (d.progress || 0) + '%');
									if (d.message) { $status.text(d.message); }
									if (d.done) {
										finishImport();
									} else {
										runStep();
									}
								} else {
									showError(response && response.data && response.data.message ? response.data.message : i18n.failed);
								}
							},
							error: function() {
								showError(i18n.failed);
							}
						});
					}

					function finishImport() {
						$fill.css('width', '100%');
						$status.addClass('rbfw-success');
						$popup.addClass('rbfw-state-success');
						$popup.find('.rbfw-woo-icon').html(
							'<svg width="40" height="40" viewBox="0 0 24 24" fill="none">' +
							'<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>' +
							'<path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
							'</svg>'
						);
						$popup.find('.rbfw-woo-title').text(i18n.success);
						$popup.find('.rbfw-woo-desc').text(i18n.done);
						setTimeout(function() { window.location.href = redirectUrl; }, 1500);
					}

					function showError(message) {
						isWorking = false;
						$popup.addClass('rbfw-state-error');
						$status.text(message).addClass('rbfw-error');
						$btn.prop('disabled', false);
						$dismissBtn.prop('disabled', false);
						$actions.slideDown(250);
						// The import is resumable — clicking again continues where it stopped.
						setTimeout(function() {
							$popup.removeClass('rbfw-state-error');
						}, 4000);
					}

					// Resume an import that was interrupted on a previous page load.
					if ($overlay.data('resume') === 1) {
						startImport();
					}
				});
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * AJAX: process a single import chunk and report progress.
		 * The browser calls this repeatedly until "done" is true, so every
		 * request stays tiny and finishes well within any memory/time limit.
		 */
		public function ajax_import_step() {
			check_ajax_referer('rbfw_import_dummy', 'nonce');
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => __('Permission denied.', 'booking-and-rental-manager-for-woocommerce')));
			}
			if (get_option('rbfw_sample_rent_items') === 'yes') {
				wp_send_json_success(array(
					'done'     => true,
					'progress' => 100,
					'stage'    => 'done',
					'message'  => __('Import complete!', 'booking-and-rental-manager-for-woocommerce'),
				));
			}
			$state = $this->process_step();
			wp_send_json_success($this->progress_payload($state));
		}

		/**
		 * AJAX (back-compat): run every remaining chunk in one request.
		 * Still hardened — images download once, limits are raised, and the
		 * persisted state lets the popup resume if this request is cut short.
		 */
		public function ajax_import_dummy_data() {
			check_ajax_referer('rbfw_import_dummy', 'nonce');
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => __('Permission denied.', 'booking-and-rental-manager-for-woocommerce')));
			}
			$this->run_full_import();
			wp_send_json_success(array('done' => true, 'progress' => 100));
		}

		/**
		 * AJAX: Dismiss dummy import popup.
		 */
		public function ajax_dismiss_dummy_import() {
			check_ajax_referer('rbfw_dismiss_dummy', 'nonce');
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => 'Permission denied.'));
			}
			update_option('rbfw_dummy_import_dismissed', 'yes');
			wp_send_json_success();
		}

		/**
		 * Public entry point for Quick Setup. Runs the import to completion with
		 * raised limits; if the request is cut short, the saved state lets the
		 * dummy-import popup resume the remaining chunks on the next page load.
		 */
		public function rbfw_import_demo_function() {
			$this->run_full_import();
		}

		public static function check_plugin($plugin_dir_name, $plugin_file): int {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
			if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
				return 1;
			} elseif (is_dir($plugin_dir)) {
				return 2;
			} else {
				return 0;
			}
		}

		/**
		 * Remote sample images. Downloaded ONCE during the import and then
		 * reused for both the thumbnail and the gallery of every item.
		 *
		 * @return string[]
		 */
		private static function image_urls() {
			$base = 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/';
			$urls = array();
			for ($i = 1; $i <= 10; $i++) {
				$urls[] = $base . 'image' . $i . '.jpeg';
			}
			return $urls;
		}

		/**
		 * Raise memory & time limits as far as the host allows. These are safe
		 * no-ops where disabled, so a locked-down host simply keeps its limit
		 * and relies on each chunk staying small.
		 */
		private function raise_limits() {
			if (function_exists('wp_raise_memory_limit')) {
				wp_raise_memory_limit('admin');
			}
			if (function_exists('set_time_limit')) {
				@set_time_limit(0);
			}
			@ignore_user_abort(true);
		}

		/**
		 * Load the WordPress media-sideload stack only while importing.
		 */
		private function load_media_stack() {
			if (!function_exists('media_sideload_image')) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}
		}

		/**
		 * Read (or initialise) the persisted import state.
		 *
		 * @return array
		 */
		private function get_state() {
			$state = get_option(self::STATE_OPTION);
			if (!is_array($state)) {
				$state = array(
					'stage'       => 'images',
					'image_index' => 0,
					'image_ids'   => array(),
					'post_index'  => 0,
					'post_ids'    => array(),
				);
			}
			return $state;
		}

		/**
		 * Advance the import by exactly one small unit of work and persist it.
		 * Stages: images (one download each) → posts (one item each) →
		 * finalize (cross-link) → done.
		 *
		 * @return array The updated state.
		 */
		public function process_step() {
			$this->raise_limits();
			$state = $this->get_state();

			switch ($state['stage']) {
				case 'images':
					$this->load_media_stack();
					$urls = self::image_urls();
					if (isset($urls[$state['image_index']])) {
						$id = media_sideload_image($urls[$state['image_index']], 0, 'Sample Rental Image', 'id');
						if (!is_wp_error($id) && $id) {
							$state['image_ids'][] = (int) $id;
						}
						$state['image_index']++;
					}
					if ($state['image_index'] >= count($urls)) {
						$state['stage'] = 'posts';
					}
					break;

				case 'posts':
					$data = $this->retnal_data();
					if (isset($data[$state['post_index']])) {
						$item    = $this->remap_image_refs($data[$state['post_index']], $state['image_ids']);
						$post_id = $this->insert_single_post($item, 'rbfw_item');
						if ($post_id) {
							$state['post_ids'][] = $post_id;
							$this->assign_images($post_id, $state['image_ids'], $state['post_index']);
						}
						$state['post_index']++;
					}
					if ($state['post_index'] >= count($data)) {
						$state['stage'] = 'finalize';
					}
					break;

				case 'finalize':
					$this->set_related_products($state['post_ids']);
					$state['stage'] = 'done';
					break;
			}

			if ($state['stage'] === 'done') {
				update_option('rbfw_sample_rent_items', 'yes');
				delete_option(self::STATE_OPTION);
			} else {
				// Do not autoload — this option only matters during an import.
				update_option(self::STATE_OPTION, $state, false);
			}

			// Release anything this request accumulated before it ends.
			if (function_exists('gc_collect_cycles')) {
				gc_collect_cycles();
			}

			return $state;
		}

		/**
		 * Run every remaining chunk within the current request (Quick Setup path).
		 * The guard is just a safety stop; the state machine always terminates.
		 */
		public function run_full_import() {
			if (get_option('rbfw_sample_rent_items') === 'yes') {
				return;
			}
			$guard = 0;
			do {
				$state = $this->process_step();
				$guard++;
			} while (isset($state['stage']) && $state['stage'] !== 'done' && $guard < 200);
		}

		/**
		 * Build the JSON payload (progress %, status message) for one state.
		 *
		 * @param array $state
		 * @return array
		 */
		private function progress_payload($state) {
			$total_images = count(self::image_urls());
			$total_posts  = count($this->retnal_data());
			$total        = $total_images + $total_posts + 1; // +1 for the finalize step.
			$done_units   = min($state['image_index'], $total_images)
				+ min($state['post_index'], $total_posts)
				+ ($state['stage'] === 'done' ? 1 : 0);
			$progress = $total > 0 ? (int) round(($done_units / $total) * 100) : 100;

			switch ($state['stage']) {
				case 'images':
					$message = sprintf(
						/* translators: 1: current image number, 2: total images. */
						__('Downloading images (%1$d of %2$d)...', 'booking-and-rental-manager-for-woocommerce'),
						min($state['image_index'] + 1, $total_images),
						$total_images
					);
					break;
				case 'posts':
					$message = sprintf(
						/* translators: 1: current item number, 2: total items. */
						__('Creating rental items (%1$d of %2$d)...', 'booking-and-rental-manager-for-woocommerce'),
						min($state['post_index'] + 1, $total_posts),
						$total_posts
					);
					break;
				case 'done':
					$message = __('Import complete!', 'booking-and-rental-manager-for-woocommerce');
					break;
				default:
					$message = __('Finishing up...', 'booking-and-rental-manager-for-woocommerce');
			}

			return array(
				'done'     => $state['stage'] === 'done',
				'progress' => min(100, max(0, $progress)),
				'stage'    => $state['stage'],
				'message'  => $message,
			);
		}

		/**
		 * Insert one rental item with its meta.
		 *
		 * @return int Post ID on success, 0 on failure.
		 */
		private function insert_single_post($data, $post_type) {
			$post_id = wp_insert_post(array(
				'post_type'    => $post_type,
				'post_title'   => isset($data['title']) ? $data['title'] : '',
				'post_content' => isset($data['content']) ? $data['content'] : '',
				'post_status'  => 'publish',
			), true);

			if (is_wp_error($post_id) || !$post_id) {
				return 0;
			}

			$meta_data = isset($data['postmeta']) ? $data['postmeta'] : array();
			if (is_array($meta_data)) {
				foreach ($meta_data as $meta_key => $meta_value) {
					update_post_meta($post_id, $meta_key, $meta_value);
				}
			}
			return (int) $post_id;
		}

		/**
		 * Point the hardcoded image references in the sample data
		 * (resort room images, extra-service images) at the freshly imported
		 * attachments, cycling through them so each gets a distinct picture.
		 *
		 * @param array $data      One item from retnal_data().
		 * @param int[] $image_ids Attachment IDs downloaded in the images stage.
		 * @return array
		 */
		private function remap_image_refs($data, $image_ids) {
			$image_ids = array_values(array_map('intval', (array) $image_ids));
			$count     = count($image_ids);
			if ($count === 0 || empty($data['postmeta']) || !is_array($data['postmeta'])) {
				return $data;
			}

			$pick = 0;

			if (!empty($data['postmeta']['rbfw_extra_service_data']) && is_array($data['postmeta']['rbfw_extra_service_data'])) {
				foreach ($data['postmeta']['rbfw_extra_service_data'] as &$service) {
					if (is_array($service) && isset($service['service_img']) && $service['service_img'] !== '') {
						$service['service_img'] = $image_ids[$pick % $count];
						$pick++;
					}
				}
				unset($service);
			}

			if (!empty($data['postmeta']['rbfw_resort_room_data']) && is_array($data['postmeta']['rbfw_resort_room_data'])) {
				foreach ($data['postmeta']['rbfw_resort_room_data'] as &$room) {
					if (is_array($room) && isset($room['rbfw_room_image']) && $room['rbfw_room_image'] !== '') {
						$room['rbfw_room_image'] = $image_ids[$pick % $count];
						$pick++;
					}
				}
				unset($room);
			}

			return $data;
		}

		/**
		 * Attach the shared sample images to one item (thumbnail + gallery),
		 * reusing the IDs downloaded during the images stage.
		 */
		private function assign_images($post_id, $image_ids, $index) {
			$image_ids = array_values(array_map('intval', (array) $image_ids));
			if (empty($image_ids)) {
				return;
			}
			$thumb_id = $image_ids[$index % count($image_ids)];
			set_post_thumbnail($post_id, $thumb_id);
			update_post_meta($post_id, 'rbfw_gallery_images', $image_ids);
			update_post_meta($post_id, 'rbfw_gallery_images_additional', $image_ids);
		}

		/**
		 * Cross-link every imported item as a related product.
		 */
		private function set_related_products($post_ids) {
			$post_ids = array_values(array_map('intval', (array) $post_ids));
			if (empty($post_ids)) {
				$post_ids = get_posts(array(
					'fields'      => 'ids',
					'post_type'   => 'rbfw_item',
					'numberposts' => -1,
					'post_status' => 'publish',
				));
			}
			foreach ($post_ids as $id) {
				update_post_meta($id, 'rbfw_releted_rbfw', $post_ids);
			}
		}

		public function retnal_data() {
			return [
				[
					'title'   => 'Bike/Car For Single Day Multiple Slot - Classic Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...',
					'postmeta' => [
						'rdfw_available_time' => [
							'00:00','00:30','01:00','06:00','08:00','08:30','09:00','09:30',
							'10:00','10:30','11:30','12:00','12:30','18:00','20:00','20:30',
							'21:00','21:30','22:00','22:30','23:30'
						],
						'rbfw_item_type' => 'bike_car_sd',
						'rbfw_extra_service_data' => [
							['service_name' => 'Tie', 'service_price' => '10', 'service_qty' => '100'],
							['service_name' => 'Shoes', 'service_price' => '10', 'service_qty' => '100'],
						],
						'rbfw_bike_car_sd_data' => [
							['rent_type' => 'Morning Session', 'short_desc' => '9 am to 12pm', 'price' => '10', 'qty' => '100', 'start_time' => '09:00', 'end_time' => '12:00', 'duration' => '6', 'd_type' => 'Hours'],
							['rent_type' => 'Afternoon Session', 'short_desc' => '3 pm to 6pm', 'price' => '10', 'qty' => '', 'start_time' => '15:00', 'end_time' => '18:00', 'duration' => '6', 'd_type' => 'Hours'],
							['rent_type' => 'Full Day', 'short_desc' => '6 am to 12 pm', 'price' => '18', 'qty' => '', 'start_time' => '09:00', 'end_time' => '18:00', 'duration' => '24', 'd_type' => 'Hours'],
						],
						'rbfw_time_format' => '12',
						'rbfw_off_dates'   => [],
						'rbfw_enable_hourly_rate' => 'yes',
						'rbfw_enable_daily_rate'  => 'yes',
						'rbfw_hourly_rate' => '10',
						'rbfw_daily_rate'  => '100',
						'rbfw_item_stock_quantity' => '10',
						'rbfw_time_slot_switch' => 'on',
						'rbfw_feature_category' => [
							['cat_title' => 'Bike Features', 'cat_features' => [
								['title' => 'Disc Brakes'], ['title' => 'Shock Absorbers'], ['title' => 'Headlight and Taillight'], ['title' => 'Bottle Holder'], ['title' => 'Electric Horn'],
							]]
						],
						'rbfw_inventory' => [],
						'rbfw_gallery_images' => [],
						'rbfw_single_template' => 'Default',
					],
				],
				[
					'title'   => 'Resort - Muffin Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
					'postmeta' => [
						'rdfw_available_time' => ['10:00','11:00','12:00','13:00','14:00','15:00','14:00','17:00','21:00'],
						'rbfw_item_type' => 'resort',
						'rbfw_extra_service_data' => [
							['service_img' => '1340', 'service_name' => 'BBQ-party', 'service_price' => '10.99', 'service_qty' => '10'],
							['service_img' => '1339', 'service_name' => 'Casino Royal', 'service_price' => '10.99', 'service_qty' => '10'],
							['service_img' => '1341', 'service_name' => 'Spa and Cure', 'service_price' => '10.99', 'service_qty' => '10'],
						],
						'rbfw_resort_room_data' => [
							['room_type' => 'Single', 'rbfw_room_image' => '1335', 'rbfw_room_daylong_rate' => '10.99', 'rbfw_room_daynight_rate' => '40.99', 'rbfw_room_desc' => 'Max. person: 2', 'rbfw_room_available_qty' => '10'],
							['room_type' => 'Delux', 'rbfw_room_image' => '1336', 'rbfw_room_daylong_rate' => '20.99', 'rbfw_room_daynight_rate' => '50.99', 'rbfw_room_desc' => 'Max. person: 2', 'rbfw_room_available_qty' => '10'],
							['room_type' => 'King', 'rbfw_room_image' => '1334', 'rbfw_room_daylong_rate' => '30.99', 'rbfw_room_daynight_rate' => '60.99', 'rbfw_room_desc' => 'Max. person: 2', 'rbfw_room_available_qty' => '10'],
						],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'],
						],
						'rbfw_enable_dropoff_point' => 'no',
						'rbfw_enable_daywise_price'  => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12',
						'rbfw_off_dates'   => '',
						'rbfw_enable_hourly_rate' => 'no',
						'rbfw_enable_daily_rate'  => 'no',
						'rbfw_enable_pick_point'  => 'no',
						'rbfw_hourly_rate' => '',
						'rbfw_daily_rate'  => '',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_feature_category' => [
							['cat_title' => 'Room Services', 'cat_features' => [
								['title' => 'Air Cooling'], ['title' => 'Wi-Fi'], ['title' => 'Smart Ironing'],
								['title' => '24/7 Room Service'], ['title' => 'Garden Balcony'], ['title' => 'Swimming Pool'], ['title' => 'Bath Tab'],
							]],
							['cat_title' => 'Hotel Services', 'cat_features' => [
								['title' => 'Breakfast Included'], ['title' => 'Spa Center'], ['title' => 'Hill View'],
								['title' => 'BBQ zone'], ['title' => 'Large Swimming Pool'], ['title' => 'Easy to Travel'], ['title' => 'Parking'],
							]],
						],
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on',
						'rbfw_available_qty_info_switch' => 'no',
						'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no',
						'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '0',
						'rbfw_enable_resort_daylong_price' => 'yes',
					],
				],
				[
					'title'   => 'Doctor Appointment - Muffin Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
					'postmeta' => [
						'rdfw_available_time' => ['10:00 AM','10:00 PM','10:30 AM','10:30 PM','11:00 AM','11:30 AM','11:30 PM','12:00 PM','12:30 PM'],
						'rbfw_item_type' => 'appointment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'],
						],
						'rbfw_enable_dropoff_point' => 'no',
						'rbfw_enable_daywise_price'  => 'no',
						'rbfw_bike_car_sd_data' => [
							['rent_type' => '30 Minute', 'short_desc' => 'Consult for 30 minutes', 'price' => '100', 'qty' => '90'],
						],
						'rbfw_time_format' => '12',
						'rbfw_off_dates'   => '',
						'rbfw_enable_hourly_rate' => 'no', 'rbfw_enable_daily_rate' => 'no', 'rbfw_enable_pick_point' => 'no',
						'rbfw_hourly_rate' => '', 'rbfw_daily_rate' => '',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_feature_category' => [
							['cat_title' => 'Bike Features', 'cat_features' => [
								['title' => 'Disc Brakes'], ['title' => 'Shock Absorbers'], ['title' => 'Headlight and Taillight'], ['title' => 'Bottle Holder'], ['title' => 'Electric Horn'],
							]]
						],
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '0', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_sd_appointment_ondays' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
						'rbfw_sd_appointment_max_qty_per_session' => '10',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
					],
				],
				[
					'title'   => 'Equipment - Muffin Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'postmeta' => [
						'rdfw_available_time' => ['10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','21:00'],
						'rbfw_item_type' => 'equipment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '30 Minute', 'short_desc' => 'Consult for 30 minutes', 'price' => '100', 'qty' => '90']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => '',
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes', 'rbfw_enable_pick_point' => 'no',
						'rbfw_hourly_rate' => '10', 'rbfw_daily_rate' => '100',
						'rbfw_feature_category' => [
							['cat_title' => 'Highlighted Features', 'cat_features' => [
								['title' => 'Brand: Bosch'], ['title' => 'Power Source: Corded Electric'],
								['title' => 'Item Dimensions: 39.5 x 12 x 33 Centimeters'], ['title' => 'Weight: 4.4 Kilograms'],
							]]
						],
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '10', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_sd_appointment_ondays' => [], 'rbfw_sd_appointment_max_qty_per_session' => '',
					],
				],
				[
					'title'   => 'Bike/Car For Multiple Day - Muffin Template',
					'content' => 'A bike rental or bike hire business rents out bicycles for short periods of time, usually for a few hours.',
					'postmeta' => [
						'rdfw_available_time' => ['10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','21:00'],
						'rbfw_item_type' => 'bike_car_md',
						'rbfw_enable_start_end_date' => 'yes',
						'rbfw_extra_service_data' => [
							['service_name' => 'Extra Tire', 'service_price' => '5', 'service_qty' => '10'],
							['service_name' => 'Helmet', 'service_price' => '5', 'service_qty' => '10'],
							['service_name' => 'Extra engine Oil', 'service_price' => '2', 'service_qty' => '10'],
							['service_name' => 'Tool Box', 'service_price' => '2', 'service_qty' => '10'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '10', 'rbfw_daily_rate' => '100',
						'rbfw_enable_pick_point' => 'no',
						'rbfw_item_stock_quantity' => '10',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'yes',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_feature_category' => [
							['cat_title' => 'Bike Features', 'cat_features' => [
								['title' => 'Disc Brakes'], ['title' => 'Shock Absorbers'], ['title' => 'Headlight and Taillight'], ['title' => 'Bottle Holder'], ['title' => 'Electric Horn'],
							]]
						],
						'rbfw_dt_sidebar_switch' => 'off',
						'rbfw_single_template' => 'Muffin',
					],
				],
				[
					'title'   => 'Dress - Muffin Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'postmeta' => [
						'rdfw_available_time' => ['10:00','11:00','12:00','13:00','14:00','3:00 PM','16:00','5:00 PM','21:00'],
						'rbfw_item_type' => 'dress',
						'rbfw_extra_service_data' => [
							['service_name' => 'Tie', 'service_price' => '10', 'service_qty' => '100'],
							['service_name' => 'Shoes', 'service_price' => '10', 'service_qty' => '100'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '10', 'rbfw_daily_rate' => '100',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_list_thumbnail' => '', 'rbfw_theme_file' => '',
						'rbfw_available_qty_info_switch' => 'no',
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'yes', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '10', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_variations_data' => [
							['field_label' => 'Color', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => 'Red', 'quantity' => '5'], ['name' => 'Blue', 'quantity' => '5']]],
							['field_label' => 'Size', 'field_id' => 'rbfw_variation_id_1', 'value' => [['name' => 'Small', 'quantity' => '5'], ['name' => 'Medium', 'quantity' => '5']]],
						],
						'rbfw_sd_appointment_ondays' => [], 'rbfw_sd_appointment_max_qty_per_session' => '',
						'rbfw_feature_category' => [
							['cat_title' => 'Dress Features', 'cat_features' => [
								['title' => 'Very High Quality Product'], ['title' => 'Various Sizeable'], ['title' => 'High Quality Fabric'],
								['title' => 'Attractive To See'], ['title' => 'Well Fitting'],
							]]
						],
						'rbfw_dt_sidebar_switch' => 'off',
					],
				],
				[
					'title'   => 'Bike/Car For Single Day - Classic Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'postmeta' => [
						'rdfw_available_time' => [
							'10:00 AM','10:00 PM','10:30 AM','10:30 PM','11:30 AM','11:30 PM',
							'12:00 AM','12:00 PM','12:30 AM','12:30 PM','1:00 AM','6:00 AM',
							'6:00 PM','8:00 AM','8:00 PM','8:30 AM','8:30 PM','9:00 AM',
							'9:00 PM','9:30 AM','9:30 PM'
						],
						'rbfw_item_type' => 'bike_car_sd',
						'rbfw_extra_service_data' => [
							['service_name' => 'Tie', 'service_price' => '10', 'service_qty' => '100'],
							['service_name' => 'Shoes', 'service_price' => '10', 'service_qty' => '100'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [
							['rent_type' => 'One Hour Rentals', 'short_desc' => 'Up to 1 hours', 'price' => '7.99', 'qty' => '100'],
							['rent_type' => 'Two Hour Rentals', 'short_desc' => 'Up to 2 hours', 'price' => '15.98', 'qty' => '100'],
							['rent_type' => 'Three Hour Rentals', 'short_desc' => 'Up to 3 hours', 'price' => '19.97', 'qty' => '100'],
							['rent_type' => 'Four Hour Rentals', 'short_desc' => 'Up to 4 hours', 'price' => '24.76', 'qty' => '100'],
							['rent_type' => 'Five Hour Rentals', 'short_desc' => 'Up to 5 hours', 'price' => '28.75', 'qty' => '100'],
							['rent_type' => 'Half Day Rental', 'short_desc' => 'Up to 6 hours', 'price' => '29.99', 'qty' => '100'],
							['rent_type' => 'All Day Rentals', 'short_desc' => 'Up to 10 Hours', 'price' => '38.99', 'qty' => '100'],
						],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '10', 'rbfw_daily_rate' => '100',
						'rbfw_enable_pick_point' => 'no',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_list_thumbnail' => '', 'rbfw_theme_file' => '',
						'rbfw_available_qty_info_switch' => 'no', 'rbfw_single_template' => 'Default',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '10', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_sd_appointment_ondays' => [], 'rbfw_sd_appointment_max_qty_per_session' => '',
						'rbfw_feature_category' => [
							['cat_title' => 'Bike Features', 'cat_features' => [
								['title' => 'Disc Brakes'], ['title' => 'Shock Absorbers'], ['title' => 'Headlight and Taillight'], ['title' => 'Bottle Holder'], ['title' => 'Electric Horn'],
							]]
						],
						'rbfw_dt_sidebar_switch' => 'off',
					],
				],
				[
					'title'   => 'Bike/Car For Single Day multi hour - Classic Template',
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'postmeta' => [
						'rdfw_available_time' => [
							'00:00','00:30','01:00','06:00','08:00','08:30','09:00','09:30',
							'10:00','10:30','11:30','12:00','12:30','18:00','20:00','20:30',
							'21:00','21:30','22:00','22:30','23:30'
						],
						'rbfw_item_type' => 'bike_car_sd',
						'rbfw_extra_service_data' => [
							['service_name' => 'Tie', 'service_price' => '10', 'service_qty' => '100'],
							['service_name' => 'Shoes', 'service_price' => '10', 'service_qty' => '100'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
							['rbfw_faq_title' => 'Lorem ipsum dolor sit amet', 'rbfw_faq_content' => 'Lorem ipsum dolor sit amet...'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [
							['rent_type' => '1 Hour Rent', 'short_desc' => 'Rent for 1 hour', 'price' => '10', 'qty' => '100', 'start_time' => '09:00', 'end_time' => '12:00', 'duration' => '1', 'd_type' => 'Hours'],
							['rent_type' => '2 Hour Rent', 'short_desc' => 'Rent for 2 hour', 'price' => '15', 'qty' => '', 'start_time' => '15:00', 'end_time' => '18:00', 'duration' => '2', 'd_type' => 'Hours'],
							['rent_type' => '4 Hour Rent', 'short_desc' => 'Rent for 4 hour', 'price' => '25', 'qty' => '', 'start_time' => '09:00', 'end_time' => '18:00', 'duration' => '4', 'd_type' => 'Hours'],
							['rent_type' => '6 Hour Rent', 'short_desc' => 'Rent for 6 hour', 'price' => '30', 'qty' => '', 'start_time' => '', 'end_time' => '', 'duration' => '6', 'd_type' => 'Hours'],
							['rent_type' => 'Full Day Rent', 'short_desc' => 'Rent for full day', 'price' => '40', 'qty' => '', 'start_time' => '', 'end_time' => '', 'duration' => '24', 'd_type' => 'Hours'],
						],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_enable_pick_point' => 'no', 'rbfw_hourly_rate' => '10', 'rbfw_daily_rate' => '100',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_available_qty_info_switch' => 'no', 'rbfw_single_template' => 'Default',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '10',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_feature_category' => [
							['cat_title' => 'Bike Features', 'cat_features' => [
								['title' => 'Disc Brakes'], ['title' => 'Shock Absorbers'], ['title' => 'Headlight and Taillight'], ['title' => 'Bottle Holder'], ['title' => 'Electric Horn'],
							]]
						],
						'rbfw_dt_sidebar_switch' => 'off',
						'rbfw_gallery_images' => [], 'rbfw_gallery_images_additional' => [],
						'rbfw_categories' => [], 'rbfw_inventory' => [],
						'rbfw_single_template' => 'Default',
					],
				]
			];
		}
	}
	new RbfwImportDemo();
}
