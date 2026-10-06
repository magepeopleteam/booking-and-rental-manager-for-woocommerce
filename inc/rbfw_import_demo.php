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
			// Non-blocking corner widget: only ever surfaces on the plugin's own admin
			// screens, never while the admin is working elsewhere in wp-admin.
			if (!$this->is_plugin_screen()) {
				return;
			}
			// An interrupted import (state persisted) always resumes; otherwise respect an
			// explicit dismissal so the auto-import can be opted out of.
			$resume = is_array(get_option(self::STATE_OPTION)) ? 1 : 0;
			if (!$resume && get_option('rbfw_dummy_import_dismissed') === 'yes') {
				return;
			}

			$import_nonce  = wp_create_nonce('rbfw_import_dummy');
			$dismiss_nonce = wp_create_nonce('rbfw_dismiss_dummy');
			?>
			<!-- RBFW auto sample-data import — non-blocking circular-progress widget -->
			<div id="rbfw-import-widget" class="rbfw-iw" role="status" aria-live="polite" data-resume="<?php echo esc_attr($resume); ?>">
				<button type="button" class="rbfw-iw-close" aria-label="<?php esc_attr_e('Dismiss', 'booking-and-rental-manager-for-woocommerce'); ?>">&times;</button>
				<div class="rbfw-iw-ring">
					<svg viewBox="0 0 44 44" width="44" height="44" aria-hidden="true">
						<circle class="rbfw-iw-track" cx="22" cy="22" r="19"></circle>
						<circle class="rbfw-iw-bar" cx="22" cy="22" r="19"></circle>
					</svg>
					<span class="rbfw-iw-pct">0%</span>
					<span class="rbfw-iw-check" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none"><path d="M5 12l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
				</div>
				<div class="rbfw-iw-body">
					<strong class="rbfw-iw-title"><?php esc_html_e('Setting up sample data', 'booking-and-rental-manager-for-woocommerce'); ?></strong>
					<span class="rbfw-iw-status"><?php esc_html_e('Preparing…', 'booking-and-rental-manager-for-woocommerce'); ?></span>
				</div>
			</div>
			<style>
				#rbfw-import-widget.rbfw-iw{position:fixed;right:24px;bottom:24px;z-index:99998;display:flex;align-items:center;gap:14px;width:300px;max-width:calc(100vw - 32px);padding:16px 18px;background:#fff;border:1px solid #ececf0;border-radius:14px;box-shadow:0 16px 40px rgba(16,24,40,.18);box-sizing:border-box;}
				#rbfw-import-widget .rbfw-iw-close{position:absolute;top:7px;right:9px;border:none;background:none;font-size:17px;line-height:1;color:#9ca3af;cursor:pointer;padding:2px 4px;}
				#rbfw-import-widget .rbfw-iw-close:hover{color:#4b5563;}
				#rbfw-import-widget .rbfw-iw-ring{position:relative;flex:0 0 auto;width:44px;height:44px;}
				#rbfw-import-widget .rbfw-iw-ring svg:first-child{transform:rotate(-90deg);display:block;}
				#rbfw-import-widget .rbfw-iw-track{fill:none;stroke:#f3e1ea;stroke-width:4;}
				#rbfw-import-widget .rbfw-iw-bar{fill:none;stroke:#F12971;stroke-width:4;stroke-linecap:round;stroke-dasharray:119.38;stroke-dashoffset:119.38;transition:stroke-dashoffset .4s ease;}
				#rbfw-import-widget .rbfw-iw-pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#9d174d;}
				#rbfw-import-widget .rbfw-iw-check{position:absolute;inset:0;display:none;align-items:center;justify-content:center;color:#16a34a;}
				#rbfw-import-widget .rbfw-iw-body{display:flex;flex-direction:column;gap:2px;min-width:0;}
				#rbfw-import-widget .rbfw-iw-title{font-size:13px;font-weight:700;color:#111827;line-height:1.35;}
				#rbfw-import-widget .rbfw-iw-status{font-size:12px;color:#6b7280;line-height:1.4;overflow-wrap:break-word;}
				#rbfw-import-widget.is-done .rbfw-iw-pct{display:none;}
				#rbfw-import-widget.is-done .rbfw-iw-check{display:flex;}
				#rbfw-import-widget.is-done .rbfw-iw-bar{stroke:#16a34a;}
				#rbfw-import-widget.is-error .rbfw-iw-bar{stroke:#dc2626;}
				#rbfw-import-widget.is-error .rbfw-iw-status{color:#dc2626;}
				#rbfw-import-widget.is-error{cursor:pointer;}
				@media (max-width:600px){#rbfw-import-widget.rbfw-iw{right:12px;left:12px;bottom:12px;width:auto;}}
				@media (prefers-reduced-motion:reduce){#rbfw-import-widget .rbfw-iw-bar{transition:none;}}
			</style>
			<script>
			(function($){
				$(function(){
					var $w = $('#rbfw-import-widget');
					if (!$w.length) { return; }
					var $bar    = $w.find('.rbfw-iw-bar');
					var $pct    = $w.find('.rbfw-iw-pct');
					var $status = $w.find('.rbfw-iw-status');
					var $title  = $w.find('.rbfw-iw-title');

					var CIRC = 119.38; // 2·π·r, r=19
					var running = false, stopped = false, errCount = 0;
					var MAX_ERR = 5, STEP_GAP = 180; // gentle pacing between chunks (ms)

					var importNonce  = <?php echo wp_json_encode($import_nonce); ?>;
					var dismissNonce = <?php echo wp_json_encode($dismiss_nonce); ?>;
					var i18n = {
						done:   <?php echo wp_json_encode(__('Sample data ready', 'booking-and-rental-manager-for-woocommerce')); ?>,
						ready:  <?php echo wp_json_encode(__('Refreshing your rental list…', 'booking-and-rental-manager-for-woocommerce')); ?>,
						failed: <?php echo wp_json_encode(__('Import paused', 'booking-and-rental-manager-for-woocommerce')); ?>,
						retry:  <?php echo wp_json_encode(__('Click to resume the import.', 'booking-and-rental-manager-for-woocommerce')); ?>
					};

					function setProgress(p){
						p = Math.max(0, Math.min(100, p));
						$bar.css('stroke-dashoffset', CIRC * (1 - p / 100));
						$pct.text(Math.round(p) + '%');
					}

					// Each request processes ONE small unit and frees its memory when it ends,
					// so the import stays safe on tiny memory limits and never blocks the page.
					function step(){
						if (stopped) { return; }
						$.ajax({
							url: ajaxurl, type: 'POST', dataType: 'json',
							data: { action: 'rbfw_import_dummy_step', nonce: importNonce }
						}).done(function(res){
							if (stopped) { return; }
							if (res && res.success && res.data) {
								errCount = 0;
								var d = res.data;
								setProgress(d.progress || 0);
								if (d.message) { $status.text(d.message); }
								if (d.done) { finish(); }
								else { setTimeout(step, STEP_GAP); }
							} else {
								onError((res && res.data && res.data.message) ? res.data.message : i18n.failed);
							}
						}).fail(function(){ onError(i18n.failed); });
					}

					function finish(){
						setProgress(100);
						$w.addClass('is-done');
						$title.text(i18n.done);
						$status.text(i18n.ready);
						setTimeout(function(){ window.location.reload(); }, 1600);
					}

					function onError(msg){
						if (stopped) { return; }
						errCount++;
						if (errCount <= MAX_ERR) {
							// Transient hiccup — back off and resume (the import is resumable).
							$status.text(msg + ' (' + errCount + '/' + MAX_ERR + ')');
							setTimeout(step, 3000);
						} else {
							running = false;
							$w.addClass('is-error');
							$title.text(i18n.failed);
							$status.text(i18n.retry);
						}
					}

					function start(){
						if (running || stopped) { return; }
						running = true; errCount = 0;
						$w.removeClass('is-error');
						step();
					}

					// Dismiss: stop the loop for good and remember the choice.
					$w.on('click', '.rbfw-iw-close', function(e){
						e.stopPropagation();
						stopped = true;
						$.post(ajaxurl, { action: 'rbfw_dismiss_dummy_import', nonce: dismissNonce });
						$w.fadeOut(200, function(){ $(this).remove(); });
					});

					// In the paused/error state, clicking the widget resumes.
					$w.on('click', function(){
						if ($w.hasClass('is-error')) { $w.removeClass('is-error'); running = false; start(); }
					});

					start(); // auto-run (fresh import, or resume an interrupted one)
				});
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * Only surface the auto-import widget on the plugin's own admin screens, so it
		 * never starts an import while the admin is working elsewhere in wp-admin.
		 */
		private function is_plugin_screen() {
			$screen = function_exists('get_current_screen') ? get_current_screen() : null;
			if ($screen && strpos($screen->id, 'rbfw_item') !== false) {
				return true;
			}
			$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen gate, no state change.
			if (strpos($page, 'rbfw') === 0) {
				return true;
			}
			$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen gate, no state change.
			return 'rbfw_item' === $post_type;
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

		/**
		 * Backfill rbfw_item_caregory terms onto sample items that were created
		 * by an older version of the importer, before per-item categories existed.
		 * Matches purely on the exact post_title from retnal_data(), touches only
		 * rbfw_item posts that currently have zero terms in the taxonomy, and
		 * never creates or duplicates a post.
		 *
		 * @return int Number of items that received categories.
		 */
		public function backfill_missing_categories() {
			$by_title = array();
			foreach ($this->retnal_data() as $item) {
				if (!empty($item['title']) && !empty($item['categories'])) {
					$by_title[$item['title']] = $item['categories'];
				}
			}
			if (empty($by_title)) {
				return 0;
			}

			$post_ids = get_posts(array(
				'post_type'   => 'rbfw_item',
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
			));

			$updated = 0;
			foreach ($post_ids as $post_id) {
				$existing = wp_get_post_terms($post_id, 'rbfw_item_caregory', array('fields' => 'ids'));
				if (!is_wp_error($existing) && !empty($existing)) {
					continue; // Already has categories — leave it alone.
				}
				$title = get_post_field('post_title', $post_id, 'raw');
				if (isset($by_title[$title])) {
					$this->assign_categories($post_id, $by_title[$title]);
					$updated++;
				}
			}
			return $updated;
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
		 * One dedicated, on-topic photo per known rbfw_item_caregory rent type
		 * (the 9 seeded by insert_dummy_taxonomy_terms() in taxonomy_register.php,
		 * plus the extra rent types introduced by the sample items' own
		 * 'categories' entries below) — replaces the old behaviour of cycling
		 * every category through the same shared pool of 10 generic item
		 * photos regardless of name.
		 *
		 * Each URL is a free, keyword-matched Creative Commons photo from
		 * loremflickr.com/Flickr; the `lock` parameter pins it to one specific,
		 * hand-picked image rather than a random one so re-imports stay
		 * consistent. Every entry here was reviewed by hand for relevance and
		 * to avoid photos whose subject is a real, identifiable person.
		 *
		 * @return array<string,string> Rent type name => image URL.
		 */
		private static function category_image_urls() {
			$base = 'https://loremflickr.com/640/480/';
			return array(
				'Appointment'        => $base . 'clinic?lock=2',
				'Bike'               => $base . 'bicycle?lock=3',
				'Car'                => $base . 'car?lock=1',
				'Consultation'       => $base . 'businessmeeting?lock=1',
				'Dress'              => $base . 'weddingdress?lock=1',
				'Equipment'          => $base . 'constructiontools?lock=1',
				'Healthcare Service' => $base . 'hospital?lock=1',
				'Helicopter'         => $base . 'helicopter?lock=1',
				'Hotel & Stay'       => $base . 'hotel?lock=4',
				'Resort'             => $base . 'beachresort?lock=1',
				'Tent'               => $base . 'camping?lock=1',
				'Tools & Gear'       => $base . 'handtools?lock=1',
				'Vacation Package'   => $base . 'tropicalbeach?lock=1',
			);
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
					'stage'                 => 'images',
					'image_index'           => 0,
					'image_ids'             => array(),
					'post_index'            => 0,
					'post_ids'              => array(),
					'category_image_index'  => 0,
				);
			}
			return $state;
		}

		/**
		 * Advance the import by exactly one small unit of work and persist it.
		 * Stages: images (one download each) → posts (one item each) →
		 * category_images (one dedicated category photo each) → finalize
		 * (cross-link + fallback images for any unmapped category) → done.
		 *
		 * @return array The updated state.
		 */
		public function process_step() {
			$this->raise_limits();
			$state = $this->get_state();
			// Back-compat: a state persisted by an older version of this file (mid-import
			// at the moment this code was updated) won't have this key yet.
			if (!isset($state['category_image_index'])) {
				$state['category_image_index'] = 0;
			}

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
						$state['stage'] = 'category_images';
					}
					break;

				case 'category_images':
					$this->load_media_stack();
					$cat_urls  = self::category_image_urls();
					$cat_names = array_keys($cat_urls);
					if (isset($cat_names[$state['category_image_index']])) {
						$name = $cat_names[$state['category_image_index']];
						$this->assign_one_category_image($name, $cat_urls[$name]);
						$state['category_image_index']++;
					}
					if ($state['category_image_index'] >= count($cat_names)) {
						$state['stage'] = 'finalize';
					}
					break;

				case 'finalize':
					$this->set_related_products($state['post_ids']);
					// Any category the site owner added that isn't one of our known
					// rent types (so category_image_urls() has nothing for it) still
					// gets a picture, reusing the sample pool already downloaded above.
					$this->assign_category_images($state['image_ids']);
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
			$total_images   = count(self::image_urls());
			$total_posts    = count($this->retnal_data());
			$total_cat_imgs = count(self::category_image_urls());
			$total          = $total_images + $total_posts + $total_cat_imgs + 1; // +1 for the finalize step.
			$done_units     = min($state['image_index'], $total_images)
				+ min($state['post_index'], $total_posts)
				+ min($state['category_image_index'], $total_cat_imgs)
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
				case 'category_images':
					$message = sprintf(
						/* translators: 1: current category number, 2: total categories. */
						__('Adding category images (%1$d of %2$d)...', 'booking-and-rental-manager-for-woocommerce'),
						min($state['category_image_index'] + 1, $total_cat_imgs),
						$total_cat_imgs
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

			if (!empty($data['categories']) && is_array($data['categories'])) {
				$this->assign_categories($post_id, $data['categories']);
			}

			return (int) $post_id;
		}

		/**
		 * Assign 2-3 sample rbfw_item_caregory terms to an imported item, creating
		 * any term that doesn't exist yet (same pattern as insert_dummy_taxonomy_terms()
		 * in admin/taxonomy_register.php).
		 *
		 * @param int      $post_id
		 * @param string[] $category_names
		 */
		private function assign_categories($post_id, $category_names) {
			$term_ids = array();
			foreach ($category_names as $name) {
				$name = trim((string) $name);
				if ($name === '') {
					continue;
				}
				$term = term_exists($name, 'rbfw_item_caregory');
				if (!$term) {
					$term = wp_insert_term($name, 'rbfw_item_caregory');
				}
				if (!is_wp_error($term) && isset($term['term_id'])) {
					$term_ids[] = (int) $term['term_id'];
				}
			}
			if (!empty($term_ids)) {
				wp_set_object_terms($post_id, $term_ids, 'rbfw_item_caregory');
				update_post_meta($post_id, 'rbfw_categories', $category_names);
			}
		}

		/**
		 * Give every rbfw_item_caregory term a real category image (term meta
		 * `rentiva_category_image_id`, read by RBFW_Category_Manager and the
		 * Rentiva theme's category grid/hero templates), reusing the same
		 * sample images already downloaded for the items instead of fetching
		 * anything new. Cycles through the pool so terms get distinct pictures
		 * and never overwrites a category that already has one (e.g. set by
		 * hand in the Categories admin page).
		 *
		 * @param int[] $image_ids Attachment IDs downloaded in the images stage.
		 */
		private function assign_category_images($image_ids) {
			$image_ids = array_values(array_map('intval', (array) $image_ids));
			$count     = count($image_ids);
			if ($count === 0) {
				return;
			}

			$terms = get_terms(array('taxonomy' => 'rbfw_item_caregory', 'hide_empty' => false));
			if (is_wp_error($terms) || empty($terms)) {
				return;
			}

			$i = 0;
			foreach ($terms as $term) {
				if (get_term_meta($term->term_id, 'rentiva_category_image_id', true)) {
					continue; // Already has an image — leave it alone.
				}
				update_term_meta($term->term_id, 'rentiva_category_image_id', $image_ids[$i % $count]);
				$i++;
			}
		}

		/**
		 * Download and assign ONE category's dedicated image (category_image_urls()),
		 * one call per process_step() chunk — same "tiny unit of work" pattern as
		 * the images stage. Skips the term entirely if it doesn't exist (a rent
		 * type from a previous run of retnal_data() that's since been renamed) or
		 * already has an image (never overwrites a hand-picked one).
		 *
		 * @param string $name Rent type / term name.
		 * @param string $url  Image URL to download.
		 */
		private function assign_one_category_image($name, $url) {
			$term = term_exists($name, 'rbfw_item_caregory');
			if (!$term) {
				return;
			}
			$term_id = (int) (is_array($term) ? $term['term_id'] : $term);
			if (get_term_meta($term_id, 'rentiva_category_image_id', true)) {
				return; // Already has an image — leave it alone.
			}

			// media_sideload_image() requires the URL's path to literally end in an
			// image extension (.jpg/.png/...); these category photo URLs don't carry
			// one (loremflickr.com/WxH/keyword?lock=N — a real JPEG, just an
			// extensionless path), so it always rejects them with "Invalid image URL".
			// Fetch and attach by hand instead, the same way WP's own uploader does.
			$response = wp_remote_get($url, array('timeout' => 20));
			if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
				return;
			}
			$body = wp_remote_retrieve_body($response);
			if ('' === $body) {
				return;
			}

			$upload = wp_upload_bits(sanitize_title($name) . '-rent-type.jpg', null, $body);
			if (!empty($upload['error'])) {
				return;
			}

			$filetype   = wp_check_filetype($upload['file'], null);
			$attachment = array(
				'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
				'post_title'     => $name . ' — Rent Type',
				'post_content'   => '',
				'post_status'    => 'inherit',
			);
			$attach_id = wp_insert_attachment($attachment, $upload['file']);
			if (is_wp_error($attach_id) || !$attach_id) {
				return;
			}
			$attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
			wp_update_attachment_metadata($attach_id, $attach_data);

			update_term_meta($term_id, 'rentiva_category_image_id', (int) $attach_id);
		}

		/**
		 * Backfill category images for a site whose dummy items were already
		 * imported before category images existed. Reuses the sample image
		 * pool from any already-imported item's gallery, since the import
		 * state (and its freshly-downloaded image_ids) no longer exists once
		 * the import has finished.
		 *
		 * @return int Number of categories that received an image.
		 */
		public function backfill_category_images() {
			$sample = get_posts(array(
				'post_type'   => 'rbfw_item',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
			));
			if (empty($sample)) {
				return 0;
			}
			$image_ids = get_post_meta($sample[0], 'rbfw_gallery_images', true);
			if (empty($image_ids) || !is_array($image_ids)) {
				return 0;
			}

			$terms = get_terms(array('taxonomy' => 'rbfw_item_caregory', 'hide_empty' => false));
			if (is_wp_error($terms) || empty($terms)) {
				return 0;
			}

			$before = 0;
			foreach ($terms as $term) {
				if (get_term_meta($term->term_id, 'rentiva_category_image_id', true)) {
					$before++;
				}
			}

			$this->assign_category_images($image_ids);

			$after = 0;
			foreach ($terms as $term) {
				if (get_term_meta($term->term_id, 'rentiva_category_image_id', true)) {
					$after++;
				}
			}
			return $after - $before;
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
					'title'      => 'Bike/Car For Single Day Multiple Slot - Classic Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...',
					'categories' => ['Bike', 'Car'],
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
					'title'      => 'Resort - Muffin Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
					'categories' => ['Resort', 'Hotel & Stay', 'Vacation Package'],
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
					'title'      => 'Doctor Appointment - Muffin Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
					'categories' => ['Appointment', 'Healthcare Service', 'Consultation'],
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
					'title'      => 'Equipment - Muffin Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'categories' => ['Equipment', 'Tools & Gear'],
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
					'title'      => 'Bike/Car For Multiple Day - Muffin Template',
					'content'    => 'A bike rental or bike hire business rents out bicycles for short periods of time, usually for a few hours.',
					'categories' => ['Bike', 'Car'],
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
					'title'      => 'Dress - Muffin Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'categories' => ['Dress'],
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
					'title'      => 'Bike/Car For Single Day - Classic Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'categories' => ['Bike', 'Car'],
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
					'title'      => 'Bike/Car For Single Day multi hour - Classic Template',
					'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					'categories' => ['Bike', 'Car'],
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
				],
				// Every rent type has at least 2 sample items below (Bike/Car Single Day
				// and the two above already cover that on their own) so a fresh install
				// has something real to click through and book for every type.
				[
					'title'      => 'Beachside Resort - Classic Template',
					'content'    => 'A relaxing beachfront resort offering a range of room types for every budget, from cozy singles to spacious family suites.',
					'categories' => ['Resort', 'Hotel & Stay'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00'],
						'rbfw_item_type' => 'resort',
						'rbfw_extra_service_data' => [
							['service_img' => '1', 'service_name' => 'Airport Pickup', 'service_price' => '15.00', 'service_qty' => '20'],
							['service_img' => '1', 'service_name' => 'Breakfast Buffet', 'service_price' => '8.50', 'service_qty' => '50'],
						],
						'rbfw_resort_room_data' => [
							['room_type' => 'Standard Twin', 'rbfw_room_image' => '1', 'rbfw_room_daylong_rate' => '15.00', 'rbfw_room_daynight_rate' => '45.00', 'rbfw_room_desc' => 'Max. person: 2', 'rbfw_room_available_qty' => '8'],
							['room_type' => 'Ocean View Suite', 'rbfw_room_image' => '1', 'rbfw_room_daylong_rate' => '35.00', 'rbfw_room_daynight_rate' => '95.00', 'rbfw_room_desc' => 'Max. person: 4', 'rbfw_room_available_qty' => '4'],
						],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'What time is check-in?', 'rbfw_faq_content' => 'Check-in starts at 2 PM and check-out is by 12 PM.'],
							['rbfw_faq_title' => 'Is breakfast included?', 'rbfw_faq_content' => 'Breakfast is available as an add-on service during booking.'],
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
							['cat_title' => 'Resort Amenities', 'cat_features' => [
								['title' => 'Private Beach Access'], ['title' => 'Infinity Pool'], ['title' => 'Free Wi-Fi'], ['title' => 'On-site Restaurant'],
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
					'title'      => 'Hair Salon Styling Session - Classic Template',
					'content'    => 'Book a one-on-one styling session with one of our professional hair stylists.',
					'categories' => ['Appointment', 'Consultation'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00 AM','09:30 AM','10:00 AM','10:30 AM','11:00 AM','02:00 PM','02:30 PM','03:00 PM'],
						'rbfw_item_type' => 'appointment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Do I need to bring anything?', 'rbfw_faq_content' => 'Just yourself — we provide everything needed for the session.'],
							['rbfw_faq_title' => 'Can I reschedule?', 'rbfw_faq_content' => 'Yes, appointments can be rescheduled up to 24 hours in advance.'],
						],
						'rbfw_enable_dropoff_point' => 'no',
						'rbfw_enable_daywise_price'  => 'no',
						'rbfw_bike_car_sd_data' => [
							['rent_type' => '45 Minute Styling', 'short_desc' => 'Full styling session', 'price' => '35', 'qty' => '40'],
						],
						'rbfw_time_format' => '12',
						'rbfw_off_dates'   => '',
						'rbfw_enable_hourly_rate' => 'no', 'rbfw_enable_daily_rate' => 'no', 'rbfw_enable_pick_point' => 'no',
						'rbfw_hourly_rate' => '', 'rbfw_daily_rate' => '',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_feature_category' => [
							['cat_title' => 'Service Highlights', 'cat_features' => [
								['title' => 'Certified Stylists'], ['title' => 'Premium Hair Products'], ['title' => 'Relaxing Atmosphere'],
							]],
						],
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '0', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_sd_appointment_ondays' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
						'rbfw_sd_appointment_max_qty_per_session' => '5',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
					],
				],
				[
					'title'      => 'Pressure Washer - Muffin Template',
					'content'    => 'A powerful electric pressure washer, perfect for cleaning driveways, patios, and vehicles.',
					'categories' => ['Equipment', 'Tools & Gear'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','13:00','14:00','15:00','16:00'],
						'rbfw_item_type' => 'equipment',
						'rbfw_extra_service_data' => [
							['service_name' => 'Extra Nozzle Set', 'service_price' => '5', 'service_qty' => '20'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Is a hose included?', 'rbfw_faq_content' => 'Yes, a standard hose and nozzle set are included with every rental.'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes', 'rbfw_enable_pick_point' => 'no',
						'rbfw_hourly_rate' => '8', 'rbfw_daily_rate' => '40',
						'rbfw_feature_category' => [
							['cat_title' => 'Highlighted Features', 'cat_features' => [
								['title' => 'Power: 2000 PSI'], ['title' => 'Corded Electric'], ['title' => 'Includes 3 Nozzle Tips'],
							]],
						],
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '6', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_sd_appointment_ondays' => [], 'rbfw_sd_appointment_max_qty_per_session' => '',
					],
				],
				[
					'title'      => 'Car For Multiple Day Rental - Muffin Template',
					'content'    => 'An economy car available for multi-day rental, ideal for road trips and longer stays.',
					'categories' => ['Car'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'],
						'rbfw_item_type' => 'bike_car_md',
						'rbfw_enable_start_end_date' => 'yes',
						'rbfw_extra_service_data' => [
							['service_name' => 'GPS Navigation', 'service_price' => '3', 'service_qty' => '10'],
							['service_name' => 'Child Seat', 'service_price' => '4', 'service_qty' => '10'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Is insurance included?', 'rbfw_faq_content' => 'Basic insurance is included; additional coverage is available at pickup.'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'no', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '', 'rbfw_daily_rate' => '45',
						'rbfw_enable_pick_point' => 'no',
						'rbfw_item_stock_quantity' => '5',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_feature_category' => [
							['cat_title' => 'Car Features', 'cat_features' => [
								['title' => 'Automatic Transmission'], ['title' => 'Air Conditioning'], ['title' => '5 Seats'], ['title' => 'Bluetooth Audio'],
							]],
						],
						'rbfw_dt_sidebar_switch' => 'off',
						'rbfw_single_template' => 'Muffin',
					],
				],
				[
					'title'      => 'Evening Gown - Muffin Template',
					'content'    => 'An elegant evening gown available for rent, perfect for galas, proms, and formal events.',
					'categories' => ['Dress'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','13:00','14:00','15:00','16:00'],
						'rbfw_item_type' => 'dress',
						'rbfw_extra_service_data' => [
							['service_name' => 'Matching Clutch', 'service_price' => '8', 'service_qty' => '20'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Is dry cleaning included?', 'rbfw_faq_content' => 'Yes, the rental price includes professional dry cleaning after return.'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'no', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '', 'rbfw_daily_rate' => '35',
						'rbfw_enable_sun_day' => 'no', 'rbfw_enable_mon_day' => 'no', 'rbfw_enable_tue_day' => 'no',
						'rbfw_enable_wed_day' => 'no', 'rbfw_enable_thu_day' => 'no', 'rbfw_enable_fri_day' => 'no', 'rbfw_enable_sat_day' => 'no',
						'rbfw_list_thumbnail' => '', 'rbfw_theme_file' => '',
						'rbfw_available_qty_info_switch' => 'no',
						'rbfw_single_template' => 'Muffin',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'yes', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_item_stock_quantity' => '6', 'rbfw_enable_resort_daylong_price' => 'no',
						'rbfw_variations_data' => [
							['field_label' => 'Size', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => 'Small', 'quantity' => '2'], ['name' => 'Medium', 'quantity' => '2'], ['name' => 'Large', 'quantity' => '2']]],
						],
						'rbfw_sd_appointment_ondays' => [], 'rbfw_sd_appointment_max_qty_per_session' => '',
						'rbfw_feature_category' => [
							['cat_title' => 'Dress Features', 'cat_features' => [
								['title' => 'Premium Satin Fabric'], ['title' => 'Floor Length'], ['title' => 'Dry Clean Included'],
							]],
						],
						'rbfw_dt_sidebar_switch' => 'off',
					],
				],
				[
					'title'      => 'Water Sports Package - Classic Template',
					'content'    => 'A bundle of water sports gear — rent any combination of kayaks, paddleboards, and life jackets for your beach day.',
					'categories' => ['Equipment', 'Tools & Gear'],
					'postmeta' => [
						'rbfw_item_type' => 'multiple_items',
						'pricing_types' => ['hourly' => 'on', 'daily' => 'on', 'weekly' => 'off', 'monthly' => 'off', '_initialized' => '1'],
						'multiple_items_info' => [
							['item_name' => 'Kayak', 'available_qty' => '6', 'hourly_price' => '8', 'daily_price' => '35', 'weekly_price' => '', 'monthly_price' => ''],
							['item_name' => 'Paddleboard', 'available_qty' => '8', 'hourly_price' => '7', 'daily_price' => '30', 'weekly_price' => '', 'monthly_price' => ''],
							['item_name' => 'Life Jacket', 'available_qty' => '20', 'hourly_price' => '2', 'daily_price' => '8', 'weekly_price' => '', 'monthly_price' => ''],
						],
						'rbfw_mi_hourly_to_half_day_pivot' => '4',
						'rbfw_mi_daily_to_weekly_pivot' => '7',
						'rbfw_mi_weekly_to_monthly_pivot' => '4',
						'rbfw_enable_time_picker' => 'no',
						'rdfw_available_time_mi' => [],
						'rbfw_extra_service_data' => [],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Can I mix and match items?', 'rbfw_faq_content' => 'Yes, choose any quantity of each item for your booking.'],
						],
						'rbfw_enable_dropoff_point' => 'no',
						'rbfw_single_template' => 'Muffin',
						'rbfw_feature_category' => [
							['cat_title' => 'Package Highlights', 'cat_features' => [
								['title' => 'Beginner Friendly'], ['title' => 'Life Jackets Included'], ['title' => 'Beach Delivery Available'],
							]],
						],
					],
				],
				[
					'title'      => 'Camping Gear Bundle - Classic Template',
					'content'    => 'Everything you need for a weekend in the outdoors — tents, sleeping bags, and camp stoves available individually or together.',
					'categories' => ['Tent', 'Tools & Gear'],
					'postmeta' => [
						'rbfw_item_type' => 'multiple_items',
						'pricing_types' => ['hourly' => 'off', 'daily' => 'on', 'weekly' => 'on', 'monthly' => 'off', '_initialized' => '1'],
						'multiple_items_info' => [
							['item_name' => '4-Person Tent', 'available_qty' => '5', 'hourly_price' => '', 'daily_price' => '20', 'weekly_price' => '110', 'monthly_price' => ''],
							['item_name' => 'Sleeping Bag', 'available_qty' => '15', 'hourly_price' => '', 'daily_price' => '6', 'weekly_price' => '30', 'monthly_price' => ''],
							['item_name' => 'Camp Stove', 'available_qty' => '8', 'hourly_price' => '', 'daily_price' => '8', 'weekly_price' => '40', 'monthly_price' => ''],
						],
						'rbfw_mi_hourly_to_half_day_pivot' => '',
						'rbfw_mi_daily_to_weekly_pivot' => '7',
						'rbfw_mi_weekly_to_monthly_pivot' => '4',
						'rbfw_enable_time_picker' => 'no',
						'rdfw_available_time_mi' => [],
						'rbfw_extra_service_data' => [],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Is setup included?', 'rbfw_faq_content' => 'Tents come with setup instructions; on-site setup help can be arranged on request.'],
						],
						'rbfw_enable_dropoff_point' => 'no',
						'rbfw_single_template' => 'Muffin',
						'rbfw_feature_category' => [
							['cat_title' => 'Package Highlights', 'cat_features' => [
								['title' => 'Weatherproof Gear'], ['title' => 'Easy Setup'], ['title' => 'Cleaned After Every Rental'],
							]],
						],
					],
				],
				[
					'title'      => 'Portable Generator - Muffin Template',
					'content'    => 'A reliable portable generator for outdoor events, job sites, or power outages.',
					'categories' => ['Tools & Gear'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','13:00','14:00','15:00'],
						'rbfw_item_type' => 'others',
						'rbfw_enable_start_end_date' => 'yes',
						'rbfw_extra_service_data' => [
							['service_name' => 'Extra Fuel Can', 'service_price' => '6', 'service_qty' => '15'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'How long does a full tank last?', 'rbfw_faq_content' => 'Approximately 8 hours under a moderate load.'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'no', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '', 'rbfw_daily_rate' => '55',
						'rbfw_enable_pick_point' => 'no',
						'rbfw_item_stock_quantity' => '4',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_feature_category' => [
							['cat_title' => 'Highlighted Features', 'cat_features' => [
								['title' => '3000W Output'], ['title' => 'Quiet Operation'], ['title' => 'Electric Start'],
							]],
						],
						'rbfw_dt_sidebar_switch' => 'off',
						'rbfw_single_template' => 'Muffin',
					],
				],
				[
					'title'      => 'Projector & Screen Set - Muffin Template',
					'content'    => 'A portable projector and screen set, perfect for outdoor movie nights, presentations, and events.',
					'categories' => ['Tools & Gear'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00','10:00','11:00','13:00','14:00','15:00','16:00'],
						'rbfw_item_type' => 'others',
						'rbfw_enable_start_end_date' => 'yes',
						'rbfw_extra_service_data' => [
							['service_name' => 'Bluetooth Speaker', 'service_price' => '10', 'service_qty' => '10'],
						],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'What size screen is included?', 'rbfw_faq_content' => 'A 100-inch portable screen is included with stand.'],
						],
						'rbfw_enable_dropoff_point' => 'no', 'rbfw_enable_daywise_price' => 'no',
						'rbfw_bike_car_sd_data' => [['rent_type' => '', 'short_desc' => '', 'price' => '', 'qty' => '']],
						'rbfw_time_format' => '12', 'rbfw_off_dates' => [],
						'rbfw_enable_hourly_rate' => 'yes', 'rbfw_enable_daily_rate' => 'yes',
						'rbfw_hourly_rate' => '12', 'rbfw_daily_rate' => '60',
						'rbfw_enable_pick_point' => 'no',
						'rbfw_item_stock_quantity' => '3',
						'rbfw_time_slot_switch' => 'on', 'rbfw_enable_extra_service_qty' => 'yes',
						'rbfw_enable_variations' => 'no', 'rbfw_enable_md_type_item_qty' => 'no',
						'rbfw_variations_data' => [['field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => [['name' => '', 'quantity' => '']], 'selected_value' => '']],
						'rbfw_feature_category' => [
							['cat_title' => 'Highlighted Features', 'cat_features' => [
								['title' => 'HD 1080p Projector'], ['title' => '100-inch Screen'], ['title' => 'HDMI + Bluetooth Input'],
							]],
						],
						'rbfw_dt_sidebar_switch' => 'off',
						'rbfw_single_template' => 'Muffin',
					],
				]
			];
		}
	}
	new RbfwImportDemo();
}
