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
		 * Give every imported item exactly 3 related products (never itself),
		 * rotating through the other imported items so each one gets a
		 * different set of neighbors rather than every item linking to the
		 * same fixed group. Deterministic (not random) so re-running the
		 * importer produces the same cross-links every time.
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
			foreach ($post_ids as $i => $id) {
				$others = array_values(array_diff($post_ids, array($id)));
				if (empty($others)) {
					continue;
				}
				$total    = count($others);
				$related  = array();
				$count    = min(3, $total);
				for ($n = 0; $n < $count; $n++) {
					$related[] = $others[($i + $n) % $total];
				}
				update_post_meta($id, 'rbfw_releted_rbfw', $related);
			}
		}

		public function retnal_data() {
			return [
				[
					'title'      => 'Bike/Car For Single Day Multiple Slot - Classic Template',
					'content'    => 'Get around town quickly and affordably with our single-day bike and car rentals, available in flexible morning, afternoon, and full-day session blocks so you only pay for the hours you actually need. Whether you\'re commuting across the city for a few hours, running weekend errands, or need reliable wheels for a full day of sightseeing, this listing gives you a straightforward way to book exactly the slot that fits your schedule without committing to a multi-day contract.

Each bike and car in our single-day fleet is inspected and serviced before every rental, so you can expect dependable brakes, properly inflated tires, and a clean interior or frame regardless of which session you choose. The Morning Session is ideal for early errands or a half-day excursion, the Afternoon Session suits post-lunch meetings or leisure rides, and the Full Day option covers everything from a long commute to an all-day adventure with friends. Pricing is transparent from the start, with no hidden add-on fees unless you choose optional extras like a tie-down strap or a spare pair of shoes for messier rides.

Booking takes just a couple of minutes: pick your date on the calendar, choose an available time slot, select the session length that works for you, and confirm. Our real-time availability calendar means you\'ll never accidentally double-book a slot someone else already reserved, and off days are clearly marked so you know at a glance when the fleet is unavailable. Every bike comes equipped with disc brakes, shock absorbers, a headlight and taillight for low-light riding, a bottle holder for longer sessions, and an electric horn for busy streets.

If your plans change, our support team is easy to reach, and the booking confirmation email includes a direct link to manage or reschedule your reservation. Whether you\'re a daily commuter or just need a bike for a single afternoon, this listing is built to make single-day rentals effortless.',
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
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'What\'s included with the Morning and Afternoon Session options?', 'rbfw_faq_content' => 'Each session includes the bike or car for the stated time window, disc brakes, lights, and a bottle holder; a tie or spare shoes can be added as optional extras at checkout.'],
							['rbfw_faq_title' => 'Can I switch between Morning, Afternoon, and Full Day after booking?', 'rbfw_faq_content' => 'Yes, contact us before your pickup time and we\'ll adjust your session type, subject to availability for the new slot.'],
							['rbfw_faq_title' => 'Is a security deposit required?', 'rbfw_faq_content' => 'Some locations require a refundable deposit at pickup; check your booking confirmation for location-specific details.'],
							['rbfw_faq_title' => 'What happens if I return the bike or car late?', 'rbfw_faq_content' => 'A grace period of 15 minutes is allowed; charges beyond that are billed at the applicable hourly rate for the next full hour.'],
							['rbfw_faq_title' => 'Are helmets provided with bike rentals?', 'rbfw_faq_content' => 'A standard helmet is included with every bike; additional sizes can be requested as an add-on when booking.'],
							['rbfw_faq_title' => 'Can I cancel my single-day booking?', 'rbfw_faq_content' => 'Free cancellation is available up until 24 hours before your scheduled start time.'],
							['rbfw_faq_title' => 'Do I need a reservation for a specific time slot?', 'rbfw_faq_content' => 'Yes, time slots are limited and shown on a real-time calendar, so booking in advance guarantees availability.'],
							['rbfw_faq_title' => 'Is fuel included for car rentals?', 'rbfw_faq_content' => 'Cars are provided with a full tank; please return with the same fuel level to avoid a refueling charge.'],
						],
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
					'content'    => 'Escape to a full-service resort designed for travelers who want both comfort and choice. This property offers three distinct room categories, Single, Deluxe, and King, so couples, solo travelers, and small families can all find a room sized and priced for their trip, with daylong and day-and-night rate options depending on how long you plan to stay.

Every room comes with the amenities guests expect from a proper resort stay: reliable air cooling, in-room Wi-Fi, smart ironing facilities, and round-the-clock room service for anything you need during your visit. Step outside your room and you\'ll find a garden balcony area, a full swimming pool, and comfortable bath tub fittings in every bathroom. On the hotel side, included breakfast service starts your day right, while the on-site spa center offers a relaxing break between activities. Guests who prefer the outdoors can take in the hill view from several vantage points around the property, enjoy the dedicated BBQ zone for an evening cookout, or spend the afternoon at the larger of our two swimming pools.

Beyond the rooms themselves, you can enhance your stay with optional add-on experiences booked directly alongside your reservation: a lively BBQ party evening, an in-house Casino Royale night for guests looking for some entertainment, or a full spa and cure package for anyone who wants to unwind. The property is easy to reach and offers convenient parking for guests arriving by car, making it a practical choice whether you\'re here for a weekend getaway or a longer vacation.

Booking is simple: pick your dates on the calendar, choose your preferred room type from the three available categories, and add any extra services you\'d like included. Real-time availability means the room and dates you see are guaranteed the moment you confirm, so there\'s no uncertainty about whether your stay will be ready when you arrive.',
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
							['rbfw_faq_title' => 'What room types are available?', 'rbfw_faq_content' => 'We offer Single, Deluxe, and King rooms, each accommodating up to 2 guests comfortably.'],
							['rbfw_faq_title' => 'Is breakfast included in the room rate?', 'rbfw_faq_content' => 'Breakfast is available as part of our Hotel Services and can be included during booking.'],
							['rbfw_faq_title' => 'Can I book spa or entertainment add-ons in advance?', 'rbfw_faq_content' => 'Yes, the BBQ Party, Casino Royale night, and Spa and Cure package can all be added at checkout.'],
							['rbfw_faq_title' => 'What is the difference between the daylong and day-and-night rate?', 'rbfw_faq_content' => 'The daylong rate covers a single day\'s stay, while the day-and-night rate covers a full overnight stay with the room held through the next morning.'],
							['rbfw_faq_title' => 'Is parking available on-site?', 'rbfw_faq_content' => 'Yes, on-site parking is available for all registered guests.'],
							['rbfw_faq_title' => 'What time is check-in and check-out?', 'rbfw_faq_content' => 'Check-in starts at 2 PM and check-out is by 12 PM, though early check-in may be arranged based on availability.'],
							['rbfw_faq_title' => 'Is the swimming pool open to all guests?', 'rbfw_faq_content' => 'Yes, both swimming pools are available to every guest regardless of room type.'],
							['rbfw_faq_title' => 'Can I modify my room type after booking?', 'rbfw_faq_content' => 'Yes, contact our team before your check-in date and we\'ll update your room type subject to availability.'],
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
					'content'    => 'Skip the waiting room and book a consultation with our doctor at a time that actually works for you. This listing covers quick, focused 30-minute consultation sessions, available Monday through Friday, so you can get professional medical advice without rearranging your entire day around a walk-in clinic.

Each session is scheduled in a dedicated time slot shown on our real-time booking calendar, which means you\'ll always see genuinely available appointment windows rather than guessing whether a time works. Slots run from early morning through midday, with both AM and PM options so patients with work schedules, school pickups, or other commitments can still find something convenient. Every booking is capped at a maximum number of patients per session to keep wait times short and ensure the doctor has adequate time with each person.

Consultations are suited for general check-ins, follow-up discussions, prescription reviews, or addressing a new concern that doesn\'t require an emergency visit. If a longer or more specialized session is needed, our team can advise you after the initial consultation on how to proceed, whether that means a referral, a follow-up booking, or additional testing. Appointments are confirmed instantly once booked, and a reminder is sent ahead of your scheduled time so you don\'t need to track the date yourself.

Our booking system is built specifically around appointment-style scheduling, which means there\'s no ambiguity about duration or availability. If your plans change, appointments can be rescheduled with reasonable notice through the same booking link used to confirm your original session. We\'ve kept the process simple because getting medical advice shouldn\'t be complicated, and we want patients to be able to book confidently and get on with their day.',
					'categories' => ['Appointment', 'Healthcare Service', 'Consultation'],
					'postmeta' => [
						'rdfw_available_time' => ['10:00 AM','10:00 PM','10:30 AM','10:30 PM','11:00 AM','11:30 AM','11:30 PM','12:00 PM','12:30 PM'],
						'rbfw_item_type' => 'appointment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'How long is each consultation?', 'rbfw_faq_content' => 'Each session is a focused 30-minute consultation.'],
							['rbfw_faq_title' => 'Which days are appointments available?', 'rbfw_faq_content' => 'Monday through Friday, with both morning and afternoon slots.'],
							['rbfw_faq_title' => 'How many patients are seen per session slot?', 'rbfw_faq_content' => 'Sessions are capped to keep wait times short and ensure adequate time per patient.'],
							['rbfw_faq_title' => 'Can I reschedule my appointment?', 'rbfw_faq_content' => 'Yes, reschedule requests are accepted with reasonable advance notice through your booking confirmation link.'],
							['rbfw_faq_title' => 'Do I need to bring any documents?', 'rbfw_faq_content' => 'Please bring any relevant medical history or prior prescriptions relevant to your visit.'],
							['rbfw_faq_title' => 'Is this suitable for a first-time consultation?', 'rbfw_faq_content' => 'Yes, this session type works well for both new concerns and follow-up visits.'],
							['rbfw_faq_title' => 'What happens after the consultation if I need further care?', 'rbfw_faq_content' => 'The doctor will advise on next steps, which may include a referral, follow-up booking, or additional testing.'],
							['rbfw_faq_title' => 'Will I get a reminder before my appointment?', 'rbfw_faq_content' => 'Yes, a reminder is sent ahead of your scheduled time.'],
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
					'content'    => 'Get the job done right with a professional-grade power tool built for both everyday homeowners and serious DIY projects. This corded electric unit from Bosch is compact enough to store easily but powerful enough to handle demanding tasks, making it a practical rental whether you need it for a single afternoon project or a longer home renovation.

Measuring a manageable footprint and weighing just over four kilograms, this tool is easy to maneuver without sacrificing the power output you need for consistent, professional-quality results. Being corded electric means you never have to worry about battery charge running out mid-project, which is especially useful for longer jobs where a battery-powered alternative might leave you stuck halfway through.

Rentals are available by the hour or by the day, so you can choose the option that best matches your project timeline. Every unit is inspected and tested between rentals to confirm it\'s in full working order before it reaches you, and our stock quantity is tracked in real time so the booking calendar always reflects genuine availability rather than outdated numbers.

If you\'ve never used this specific model before, basic operating guidance is available on request at pickup, and our team is happy to answer any questions about the correct settings or accessories for your particular task. We built this rental option to make professional-grade equipment accessible without the upfront cost of buying a tool you might only need once or twice.',
					'categories' => ['Equipment', 'Tools & Gear'],
					'postmeta' => [
						'rdfw_available_time' => ['10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','21:00'],
						'rbfw_item_type' => 'equipment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'Is this tool corded or battery-powered?', 'rbfw_faq_content' => 'It\'s corded electric, so there\'s no battery to charge or run out mid-project.'],
							['rbfw_faq_title' => 'Can I rent it for just a few hours?', 'rbfw_faq_content' => 'Yes, hourly rentals are available alongside the daily rate option.'],
							['rbfw_faq_title' => 'Is the tool tested before each rental?', 'rbfw_faq_content' => 'Yes, every unit is inspected and tested between rentals to confirm it\'s fully functional.'],
							['rbfw_faq_title' => 'Do you provide instructions for first-time users?', 'rbfw_faq_content' => 'Basic operating guidance is available on request at pickup.'],
							['rbfw_faq_title' => 'What\'s the weight of this unit?', 'rbfw_faq_content' => 'It weighs approximately 4.4 kilograms, making it easy to handle for most users.'],
							['rbfw_faq_title' => 'Can I extend my rental if my project takes longer than expected?', 'rbfw_faq_content' => 'Yes, extensions are possible subject to availability; contact us before your return time.'],
							['rbfw_faq_title' => 'Is a case or carrying bag included?', 'rbfw_faq_content' => 'The unit is provided ready to use; ask at pickup about any available accessories for transport.'],
							['rbfw_faq_title' => 'What if the tool malfunctions during my rental?', 'rbfw_faq_content' => 'Contact us immediately and we\'ll arrange a replacement or resolve the issue as quickly as possible.'],
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
					'content'    => 'Planning a trip that needs more than a single day\'s rental? Our multi-day bike and car option is built exactly for that: book a start and end date up front, and your rate automatically reflects the full length of your rental without you needing to calculate anything manually. It\'s the straightforward choice for road trips, extended visits, or any situation where a single-day booking just wouldn\'t cover what you need.

Both hourly and daily rate structures are available, which gives you flexibility depending on how you plan to use the vehicle during your rental period. Every bike and car in this fleet is prepared the same way regardless of rental length: full safety and maintenance check before handover, disc brakes, shock absorbers for a smoother ride, and working headlights and taillights so you\'re covered for any time of day.

To make longer trips more comfortable, a set of optional extras is available alongside your booking: an extra tire for peace of mind on longer routes, a spare helmet, additional engine oil, and a compact tool box in case you need to handle a minor fix on the road. These add-ons are entirely optional, so you only pay for what you actually think you\'ll need for your specific trip.

Our calendar shows real availability across your full requested date range, so once you\'ve selected your start and end dates you can book with confidence that the vehicle will be reserved for the entire period. If your travel plans shift, reach out as early as possible so we can adjust your dates where availability allows. Multi-day rentals are one of our most popular options for travelers who want the independence of their own transportation without the commitment of a long-term lease.',
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
							['rbfw_faq_title' => 'How is the price calculated for multi-day rentals?', 'rbfw_faq_content' => 'The system automatically calculates your total based on the start and end dates you select, using the hourly or daily rate as applicable.'],
							['rbfw_faq_title' => 'Can I add extras like a spare tire or tool box after booking?', 'rbfw_faq_content' => 'Yes, as long as your rental hasn\'t started, you can add optional extras through your booking confirmation.'],
							['rbfw_faq_title' => 'What happens if I need to extend my multi-day rental?', 'rbfw_faq_content' => 'Contact us before your scheduled return date and we\'ll extend it if the vehicle is still available.'],
							['rbfw_faq_title' => 'Is roadside support available during a multi-day rental?', 'rbfw_faq_content' => 'Yes, our support line is available for any issues that come up during your rental period.'],
							['rbfw_faq_title' => 'Are helmets included with bike rentals?', 'rbfw_faq_content' => 'A standard helmet is included, with a spare available as an optional add-on.'],
							['rbfw_faq_title' => 'Can I return the vehicle early?', 'rbfw_faq_content' => 'Yes, though no partial refund is given for days not used unless arranged in advance.'],
							['rbfw_faq_title' => 'Is there a minimum rental length?', 'rbfw_faq_content' => 'Multi-day rentals typically start at 2 days; check the calendar for the shortest available range.'],
							['rbfw_faq_title' => 'What condition should the vehicle be returned in?', 'rbfw_faq_content' => 'Please return it in the same condition it was received, with a similar fuel level for cars.'],
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
					'content'    => 'Looking for the right outfit for a special occasion without the cost of buying something you\'ll only wear once? This dress is available to rent by the hour or by the day, and comes in a range of colors and sizes so you can pick exactly what fits your event and your figure, whether that\'s a wedding, a formal dinner, or a photoshoot.

Every piece in our dress collection is chosen for genuine quality: high-grade fabric, careful stitching, and an attractive, well-fitting cut that photographs and wears well across a full evening. Sizes range from Small to Medium, and colors currently include Red and Blue, giving you flexibility to match your event\'s color scheme or your personal preference. Each dress is professionally cleaned and inspected between every rental, so what arrives is treated with the same care as a brand-new purchase.

To complete your look, optional add-ons are available alongside the dress itself: a matching tie for a partner\'s outfit, or a pair of shoes if you need footwear to match. These extras are entirely optional and can be selected during the booking process, so you can put together a complete outfit in one transaction rather than sourcing pieces separately.

Booking is simple: choose your rental dates, select your preferred color and size from the available variations, and add any extras you need. Our stock quantities are tracked per size and color, so the options shown to you reflect genuine availability rather than a general estimate. If your event date changes or you\'re unsure which size will fit best, reach out before booking and we\'ll help you find the right option for your event.',
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
							['rbfw_faq_title' => 'What sizes and colors are available?', 'rbfw_faq_content' => 'Available options include Small and Medium sizes in Red and Blue, shown as selectable variations during booking.'],
							['rbfw_faq_title' => 'Is dry cleaning included in the rental?', 'rbfw_faq_content' => 'Yes, every dress is professionally cleaned and inspected before it reaches you.'],
							['rbfw_faq_title' => 'Can I rent this by the hour instead of the full day?', 'rbfw_faq_content' => 'Yes, both hourly and daily rental rates are available.'],
							['rbfw_faq_title' => 'What if the dress doesn\'t fit when it arrives?', 'rbfw_faq_content' => 'Contact us as soon as possible and we\'ll help arrange an exchange, subject to availability.'],
							['rbfw_faq_title' => 'Are shoes or accessories included?', 'rbfw_faq_content' => 'Shoes and a matching tie are available as optional add-ons at checkout.'],
							['rbfw_faq_title' => 'What happens if the dress is damaged during my rental?', 'rbfw_faq_content' => 'Please report any damage promptly; normal wear is expected, but excessive damage may incur a fee.'],
							['rbfw_faq_title' => 'How far in advance should I book for an event?', 'rbfw_faq_content' => 'We recommend booking at least a week ahead to guarantee your preferred size and color.'],
							['rbfw_faq_title' => 'Can I extend my rental if my event runs long?', 'rbfw_faq_content' => 'Yes, extensions are available on request, subject to the dress being available for the extra time.'],
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
					'content'    => 'Need a bike or car for just a few hours, or the whole day? This listing offers a full range of single-day rental packages, from a quick One Hour Rental up through Two, Three, Four, and Five Hour options, a Half Day Rental, and a full All Day Rental, so you only pay for the exact amount of time you expect to need.

Every tier is priced individually and clearly displayed before you book, so there\'s no guesswork about what a longer session will cost compared to a shorter one. Shorter rentals are ideal for quick errands, a short test ride, or a brief meeting across town, while the longer options, Half Day and All Day, suit anyone planning a longer outing, a full day of sightseeing, or simply wanting the flexibility of having the vehicle available without a strict time limit.

Each bike and car is maintained to the same standard regardless of which rental tier you choose: disc brakes, shock absorbers, working lights for low-visibility conditions, a bottle holder, and an electric horn for busy roads. Stock availability is tracked and shown live on the booking calendar, so the tier and time slot you see at checkout is exactly what will be ready for you at pickup.

Optional add-ons, including a tie and a spare pair of shoes, are available if you need them for a more formal outing. Booking takes just a minute: select your date, choose your preferred rental tier, and confirm. If your plans run longer than expected, contact us before your scheduled return time and we\'ll do our best to extend your booking to the next available tier.',
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
							['rbfw_faq_title' => 'What\'s the difference between the rental tiers?', 'rbfw_faq_content' => 'Each tier, from One Hour through All Day, covers a different length of time at its own fixed price, so you only pay for the duration you choose.'],
							['rbfw_faq_title' => 'Can I upgrade to a longer tier after booking?', 'rbfw_faq_content' => 'Yes, contact us before your scheduled return time and we\'ll upgrade you if availability allows.'],
							['rbfw_faq_title' => 'Is there a discount for the All Day Rental compared to booking multiple shorter tiers?', 'rbfw_faq_content' => 'Yes, the All Day Rental is priced lower per hour than stacking several shorter tiers.'],
							['rbfw_faq_title' => 'What\'s included with every rental regardless of tier?', 'rbfw_faq_content' => 'Every bike and car includes a full safety check, working lights, and the standard feature set listed on the item page.'],
							['rbfw_faq_title' => 'Can I add a tie or shoes to any tier?', 'rbfw_faq_content' => 'Yes, optional add-ons are available at checkout regardless of which tier you choose.'],
							['rbfw_faq_title' => 'Is a deposit required?', 'rbfw_faq_content' => 'Some pickup locations require a refundable deposit; this will be noted in your booking confirmation.'],
							['rbfw_faq_title' => 'What happens if I return late?', 'rbfw_faq_content' => 'A short grace period is allowed; charges beyond that are billed at the next tier\'s rate.'],
							['rbfw_faq_title' => 'Can I book the same bike for multiple separate single-day sessions?', 'rbfw_faq_content' => 'Yes, simply book each date individually through the calendar.'],
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
					'content'    => 'This single-day rental option gives you a choice of fixed-duration blocks, 1 Hour, 2 Hour, 4 Hour, 6 Hour, and Full Day, each scheduled against a specific start and end time so you know exactly when your rental begins and ends before you even arrive. It\'s a good fit for anyone who prefers to plan their day around a known time window rather than an open-ended booking.

Because each duration is tied to a defined start and end time, our calendar can show you precisely which blocks are available on your chosen date, taking the guesswork out of scheduling around other bookings. The shorter blocks, 1 and 2 Hour, work well for quick trips or brief appointments, the 4 and 6 Hour options suit half-day plans or longer errands, and the Full Day Rent covers anyone who wants the vehicle available for an extended period without needing to track a strict return time.

Every vehicle is inspected before each handover and comes equipped with the same reliable features across all duration options: disc brakes, shock absorbers, a headlight and taillight combination, a bottle holder, and an electric horn. Stock levels are tracked live, so the time blocks displayed on the booking calendar are a genuine reflection of what\'s actually available rather than a static estimate.

If you need a tie or a spare pair of shoes for a more formal trip, these are available as optional add-ons during checkout. Booking is quick: select your date, choose the duration block that fits your schedule, and confirm your reservation. Should your plans shift, reach out as early as possible and we\'ll do what we can to move you to a different time block or duration, subject to availability.',
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
							['rbfw_faq_title' => 'How are the time blocks structured?', 'rbfw_faq_content' => 'Each block, 1, 2, 4, or 6 Hour, or Full Day, has a defined start and end time shown on the booking calendar.'],
							['rbfw_faq_title' => 'Can I choose my own start time within a block?', 'rbfw_faq_content' => 'Start times are tied to the specific block selected; contact us if you need a custom time outside the listed options.'],
							['rbfw_faq_title' => 'What if my chosen block is unavailable on my date?', 'rbfw_faq_content' => 'The calendar only shows genuinely available blocks, so you can pick from whatever remains open for that day.'],
							['rbfw_faq_title' => 'Is there a price difference between blocks?', 'rbfw_faq_content' => 'Yes, each block has its own fixed price reflecting its duration.'],
							['rbfw_faq_title' => 'Can I switch my Full Day booking to a shorter block?', 'rbfw_faq_content' => 'Yes, contact us before pickup and we\'ll adjust it if the shorter block is available.'],
							['rbfw_faq_title' => 'Are extras like a tie or shoes available for every block?', 'rbfw_faq_content' => 'Yes, optional add-ons can be selected regardless of which duration block you choose.'],
							['rbfw_faq_title' => 'What happens if I\'m late returning within my block?', 'rbfw_faq_content' => 'A short grace period applies; charges beyond that are billed at the next applicable rate.'],
							['rbfw_faq_title' => 'Is fuel included for car bookings?', 'rbfw_faq_content' => 'Vehicles are provided with a full tank and should be returned at the same level.'],
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
					'content'    => 'Wake up to ocean views and fall asleep to the sound of waves at this relaxing beachfront resort, offering room types suited to every kind of traveler, from a cozy Standard Twin for couples or solo travelers to a spacious Ocean View Suite for families who want a little extra room and a direct view of the water.

The Standard Twin comfortably fits two guests and is priced for travelers who want beach access without paying for extra space they won\'t use, while the Ocean View Suite accommodates up to four guests and includes the kind of view that makes a beach vacation feel complete. Both daylong and overnight rate options are available for each room type, so you can book exactly the stay length your trip calls for.

Private beach access is one of the property\'s standout features, giving guests a quieter, less crowded stretch of sand than nearby public beaches. Back at the resort, an infinity pool overlooks the water for anyone who prefers to swim without the sand, free Wi-Fi keeps you connected throughout your stay, and the on-site restaurant means you don\'t need to leave the property for a proper meal. Two optional add-on services are available: an airport pickup service, and a breakfast buffet if you\'d rather not plan your mornings around finding food nearby.

Check-in starts at 2 PM and check-out is by 12 PM, giving you a full day to enjoy the property before you need to think about departure. Booking is handled through our real-time calendar, so the room type and dates you select are confirmed the moment you complete checkout, no waiting for a callback to confirm your stay.',
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
							['rbfw_faq_title' => 'What time is check-in and check-out?', 'rbfw_faq_content' => 'Check-in starts at 2 PM and check-out is by 12 PM.'],
							['rbfw_faq_title' => 'Is breakfast included in the room price?', 'rbfw_faq_content' => 'Breakfast is available as an add-on service during booking, not included by default.'],
							['rbfw_faq_title' => 'How many guests can each room accommodate?', 'rbfw_faq_content' => 'The Standard Twin fits up to 2 guests, and the Ocean View Suite accommodates up to 4.'],
							['rbfw_faq_title' => 'Is airport pickup available?', 'rbfw_faq_content' => 'Yes, airport pickup can be added as an optional service during your booking.'],
							['rbfw_faq_title' => 'Is the beach access private?', 'rbfw_faq_content' => 'Yes, the resort has its own private beach access separate from public beaches nearby.'],
							['rbfw_faq_title' => 'Is Wi-Fi available throughout the property?', 'rbfw_faq_content' => 'Yes, free Wi-Fi is available across the resort.'],
							['rbfw_faq_title' => 'Can I book a room without selecting an overnight stay?', 'rbfw_faq_content' => 'Yes, a daylong rate is available for guests who only need the room for part of a day.'],
							['rbfw_faq_title' => 'Is the on-site restaurant open to all guests?', 'rbfw_faq_content' => 'Yes, the restaurant is available to every guest regardless of room type.'],
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
					'content'    => 'Treat yourself to a professional styling session with one of our certified hair stylists, booked in a focused 45-minute slot so you get dedicated attention without an open-ended appointment eating into the rest of your day. Whether you\'re preparing for a special event or just want a refresh, this session is designed to get you in and out with results you\'re happy with.

Appointments are available across a mix of morning and early afternoon slots, giving you flexibility to fit a session around work, school runs, or other commitments. Our stylists use premium hair products throughout the session, and the salon itself is designed to be a relaxing space, not rushed, not crowded, so the 45 minutes you book actually feels like a proper styling experience.

Sessions are available up to six days a week, and each booking slot is capped to a small number of guests so your stylist isn\'t juggling multiple clients at once. Whether you need a full restyle, a touch-up before an event, or just want to try a new look with professional guidance, this session format gives you enough time to discuss what you want and have it properly executed.

Booking is handled through our live calendar, so the time slots shown are genuinely open rather than estimated. Simply select your preferred day and time, confirm your booking, and show up ready for your session, no additional preparation is needed since everything required is provided at the salon. If your schedule changes, appointments can be rescheduled up to 24 hours in advance through the same booking confirmation.',
					'categories' => ['Appointment', 'Consultation'],
					'postmeta' => [
						'rdfw_available_time' => ['09:00 AM','09:30 AM','10:00 AM','10:30 AM','11:00 AM','02:00 PM','02:30 PM','03:00 PM'],
						'rbfw_item_type' => 'appointment',
						'rbfw_extra_service_data' => [],
						'rbfw_resort_room_data' => [['room_type' => '', 'rbfw_room_image' => '', 'rbfw_room_daylong_rate' => '', 'rbfw_room_daynight_rate' => '', 'rbfw_room_desc' => '', 'rbfw_room_available_qty' => '']],
						'rbfw_enable_faq_content' => 'yes',
						'mep_event_faq' => [
							['rbfw_faq_title' => 'How long is each styling session?', 'rbfw_faq_content' => 'Each session is 45 minutes of dedicated styling time.'],
							['rbfw_faq_title' => 'Do I need to bring anything?', 'rbfw_faq_content' => 'Just yourself, we provide everything needed for the session.'],
							['rbfw_faq_title' => 'Can I reschedule my appointment?', 'rbfw_faq_content' => 'Yes, appointments can be rescheduled up to 24 hours in advance.'],
							['rbfw_faq_title' => 'Which days are sessions available?', 'rbfw_faq_content' => 'Sessions are available six days a week, Monday through Saturday.'],
							['rbfw_faq_title' => 'What products are used during the session?', 'rbfw_faq_content' => 'Our stylists use premium hair products throughout every session.'],
							['rbfw_faq_title' => 'How many clients are seen per time slot?', 'rbfw_faq_content' => 'Each slot is capped to a small number of guests to keep sessions unhurried.'],
							['rbfw_faq_title' => 'Can I request a specific stylist?', 'rbfw_faq_content' => 'Let us know your preference when booking and we\'ll accommodate it where possible.'],
							['rbfw_faq_title' => 'Is this suitable for a special event like a wedding?', 'rbfw_faq_content' => 'Yes, many guests book this session ahead of weddings, proms, and other formal events.'],
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
					'content'    => 'Make quick work of dirty driveways, patios, decks, and vehicles with this powerful electric pressure washer, rated at 2000 PSI for genuinely effective cleaning without the noise and maintenance hassle of a gas-powered unit. It\'s one of our most reliably booked pieces of equipment for anyone tackling a seasonal outdoor clean-up.

Being corded electric means there\'s no fuel to buy, no engine to maintain, and no exhaust fumes to worry about, just plug in and start cleaning. The unit comes with three interchangeable nozzle tips, letting you switch between a focused jet for stubborn stains and a wider spray pattern for general surface cleaning, so you can match the output to whatever surface you\'re working on.

Rentals are available by the hour or by the day, which makes this a practical option whether you\'re cleaning a single driveway in an afternoon or working through a longer list of outdoor surfaces over a full day. A standard hose and nozzle set is included with every rental, and an extra nozzle set is available as an optional add-on at checkout for an additional cost.

Every unit is tested before it leaves our facility to confirm consistent pressure output and a properly sealed hose connection, so you\'re not left troubleshooting equipment issues partway through your project. Our stock quantity is limited and tracked live, so the availability shown on the booking calendar reflects genuinely available units. If you\'ve never used a pressure washer before, basic guidance is available on request at pickup covering safe operation and the right nozzle for common surfaces like concrete, wood decking, and vehicle paintwork.',
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
							['rbfw_faq_title' => 'What\'s the pressure output of this unit?', 'rbfw_faq_content' => 'It\'s rated at 2000 PSI, suitable for driveways, patios, decks, and vehicles.'],
							['rbfw_faq_title' => 'Can I rent it for just a few hours?', 'rbfw_faq_content' => 'Yes, hourly rentals are available alongside the daily rate.'],
							['rbfw_faq_title' => 'How many nozzle tips are included?', 'rbfw_faq_content' => 'Three interchangeable nozzle tips come standard with every rental.'],
							['rbfw_faq_title' => 'Is it gas-powered or electric?', 'rbfw_faq_content' => 'It\'s corded electric, requiring no fuel or engine maintenance.'],
							['rbfw_faq_title' => 'Can I rent an extra nozzle set?', 'rbfw_faq_content' => 'Yes, an additional nozzle set is available as an optional add-on at checkout.'],
							['rbfw_faq_title' => 'Is this safe to use on a car\'s paintwork?', 'rbfw_faq_content' => 'Yes, with the correct nozzle and distance; guidance is available on request at pickup.'],
							['rbfw_faq_title' => 'What if the unit isn\'t working properly when I receive it?', 'rbfw_faq_content' => 'Contact us immediately and we\'ll arrange a replacement as quickly as possible.'],
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
					'content'    => 'Planning a road trip or need reliable transportation for an extended stay? This economy car is available for multi-day rental, giving you the independence of your own vehicle without the commitment of a long-term lease. It\'s a practical, fuel-efficient choice for anyone who needs to cover real distance over several days.

Pricing is based on a straightforward daily rate, so your total cost is easy to calculate up front based on exactly how many days you need the car. The vehicle itself is equipped with automatic transmission for easy driving, full air conditioning for comfort regardless of the weather, seating for up to five passengers, and Bluetooth audio connectivity so you can stream music or take calls hands-free throughout your trip.

Two optional add-ons are available to make longer trips more comfortable: GPS navigation for anyone unfamiliar with the area they\'re driving through, and a child seat for families traveling with younger passengers. Both can be added during the booking process so everything you need is arranged in a single transaction.

Every vehicle is inspected before handover to confirm it\'s road-ready, with particular attention paid to tire condition, fluid levels, and the overall mechanical state given the longer distances multi-day renters typically cover. Stock availability is limited and tracked live, so once you\'ve selected your dates and confirmed your booking, the car is genuinely reserved for your full rental period. Basic insurance coverage is included with every rental, and additional coverage options can be discussed and added at the pickup location.',
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
							['rbfw_faq_title' => 'How many passengers can this car seat?', 'rbfw_faq_content' => 'It seats up to five passengers comfortably.'],
							['rbfw_faq_title' => 'Can I add GPS navigation or a child seat?', 'rbfw_faq_content' => 'Yes, both are available as optional add-ons during booking.'],
							['rbfw_faq_title' => 'How is the multi-day price calculated?', 'rbfw_faq_content' => 'Pricing is based on a straightforward daily rate multiplied across your selected date range.'],
							['rbfw_faq_title' => 'Is the car automatic or manual transmission?', 'rbfw_faq_content' => 'It has automatic transmission.'],
							['rbfw_faq_title' => 'What condition should I return the car in?', 'rbfw_faq_content' => 'Please return it in the same condition received, with a similar fuel level.'],
							['rbfw_faq_title' => 'Can I extend my rental once it\'s started?', 'rbfw_faq_content' => 'Yes, contact us before your return date and we\'ll extend it if the car remains available.'],
							['rbfw_faq_title' => 'Is Bluetooth audio supported?', 'rbfw_faq_content' => 'Yes, the car includes Bluetooth audio connectivity for music and hands-free calls.'],
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
					'content'    => 'Make an entrance at your next gala, prom, or formal event in this elegant evening gown, crafted from premium satin fabric and cut to a flattering floor length that photographs beautifully under any lighting. Renting rather than buying means you get a genuinely high-end look for a single special occasion without the cost of a dress you may only wear once.

Available in Small, Medium, and Large sizes, this gown is priced by the day, making it easy to book for exactly the event you have planned. The satin fabric holds its shape and shine throughout an evening of wear, and the floor-length cut is designed to suit a range of body types while maintaining an elegant, formal silhouette.

One of the most convenient aspects of renting this gown is that dry cleaning is included in the rental price, simply return it after your event, and professional cleaning is handled on our end. If you\'d like to complete your look, a matching clutch is available as an optional add-on during checkout.

Every gown in our collection is inspected between rentals to confirm it\'s free of visible wear or damage before it reaches the next guest, so what arrives for your event is held to the same standard as a boutique purchase. Booking is simple: choose your event date, select your size from the available options, and add the matching clutch if you\'d like it. Because sizes are limited per date, we recommend booking a week or more ahead of your event to guarantee the size you need is still available.',
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
							['rbfw_faq_title' => 'What sizes are available?', 'rbfw_faq_content' => 'Small, Medium, and Large sizes are available as selectable variations.'],
							['rbfw_faq_title' => 'Is dry cleaning included?', 'rbfw_faq_content' => 'Yes, professional dry cleaning after your event is included in the rental price.'],
							['rbfw_faq_title' => 'Can I add a matching clutch?', 'rbfw_faq_content' => 'Yes, a matching clutch is available as an optional add-on at checkout.'],
							['rbfw_faq_title' => 'How far ahead should I book for an event?', 'rbfw_faq_content' => 'We recommend booking at least a week ahead to guarantee your preferred size.'],
							['rbfw_faq_title' => 'What fabric is the gown made from?', 'rbfw_faq_content' => 'It\'s made from premium satin fabric with a floor-length cut.'],
							['rbfw_faq_title' => 'What if the gown doesn\'t fit when it arrives?', 'rbfw_faq_content' => 'Contact us as soon as possible and we\'ll help arrange an exchange, subject to availability.'],
							['rbfw_faq_title' => 'What happens if there\'s minor damage during my event?', 'rbfw_faq_content' => 'Normal wear is expected and accounted for; please report any significant damage promptly.'],
							['rbfw_faq_title' => 'Can I rent this gown for multiple days?', 'rbfw_faq_content' => 'It\'s priced per day, so multi-day rentals are available by selecting the needed date range.'],
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
					'content'    => 'Make the most of your beach day with our water sports package, letting you rent kayaks, paddleboards, and life jackets individually or in any combination that suits your group. Rather than booking a single fixed package, you choose exactly how many of each item you need, making this a flexible option whether you\'re heading out solo or bringing a group of friends or family.

Kayaks are great for anyone who wants a stable, straightforward way to get out on the water, while paddleboards suit those looking for a more active, balance-focused experience closer to shore. Life jackets are available in generous quantities so every member of your group can be properly equipped regardless of swimming ability. All three item types are available by the hour or by the day, so a short afternoon session and a full day on the water are both priced fairly for the time you actually use.

This package is designed with beginners in mind: no prior experience is assumed, and all the gear needed to get started safely is included once you\'ve selected your items and quantities. Beach delivery is available depending on your location, meaning you may not even need to transport the gear yourself, ask about this option when booking.

Our booking system automatically calculates your total based on the specific items and quantities you select and how long you need them, so there\'s no need to manually work out a combined price. Stock levels for each item type are tracked live, which means the quantities available when you book are what will genuinely be ready for you at pickup.',
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
							['rbfw_faq_title' => 'Can I mix and match items?', 'rbfw_faq_content' => 'Yes, choose any quantity of each item, kayaks, paddleboards, and life jackets, for your booking.'],
							['rbfw_faq_title' => 'Is beach delivery available?', 'rbfw_faq_content' => 'Delivery availability depends on your location; ask about this option when booking.'],
							['rbfw_faq_title' => 'Are life jackets included for every renter?', 'rbfw_faq_content' => 'Life jackets can be rented in whatever quantity your group needs, separate from the kayaks and paddleboards.'],
							['rbfw_faq_title' => 'Do I need prior experience to use this equipment?', 'rbfw_faq_content' => 'No, this package is designed to be accessible for beginners.'],
							['rbfw_faq_title' => 'Can I rent for just a few hours?', 'rbfw_faq_content' => 'Yes, hourly rentals are available alongside the daily rate for each item.'],
							['rbfw_faq_title' => 'How is the total price calculated?', 'rbfw_faq_content' => 'It\'s calculated automatically based on the items, quantities, and duration you select.'],
							['rbfw_faq_title' => 'What if an item I want is out of stock for my date?', 'rbfw_faq_content' => 'The calendar shows live stock levels, so only genuinely available quantities can be booked.'],
							['rbfw_faq_title' => 'Is any safety equipment beyond life jackets provided?', 'rbfw_faq_content' => 'Life jackets are the primary safety equipment included; ask our team about any additional gear for your specific plans.'],
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
					'content'    => 'Head into the outdoors without needing to own a garage full of camping equipment. This bundle lets you rent a 4-person tent, sleeping bags, and camp stoves individually or together, so your setup matches your group size and trip length exactly rather than forcing you into a fixed package that doesn\'t fit your plans.

The 4-person tent is weatherproof and designed for straightforward setup, even for campers who haven\'t pitched a tent in a while, while the sleeping bags are suited to a range of overnight temperatures for a comfortable night\'s rest. Camp stoves round out the bundle, giving you a reliable way to cook meals at your site. All three item types are available on daily and weekly rates, making this bundle practical whether you\'re heading out for a single weekend or a longer stretch in the backcountry.

Every piece of gear is cleaned and inspected after each rental, so what you receive is in genuinely good condition rather than worn down from repeated use. If you\'re new to camping or just haven\'t set up this particular tent model before, setup instructions are included, and on-site setup help can be arranged on request.

Booking works the same way as our other multiple-item rentals: select the quantity of each item you need, choose your date range, and the total is calculated automatically based on your selections. Stock levels are tracked live for each item, so what\'s shown as available is genuinely ready for your trip. Whether you\'re outfitting a single tent\'s worth of gear or equipping a larger group, this bundle is built to flex around your actual camping plans.',
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
							['rbfw_faq_title' => 'Can I rent just the sleeping bags without a tent?', 'rbfw_faq_content' => 'Yes, each item can be rented individually in whatever quantity you need.'],
							['rbfw_faq_title' => 'Are weekly rates available for longer trips?', 'rbfw_faq_content' => 'Yes, daily and weekly rates are both available for every item in this bundle.'],
							['rbfw_faq_title' => 'How many people does the tent sleep?', 'rbfw_faq_content' => 'The tent is a 4-person model.'],
							['rbfw_faq_title' => 'Is the gear cleaned between rentals?', 'rbfw_faq_content' => 'Yes, every piece is cleaned and inspected after each rental.'],
							['rbfw_faq_title' => 'Do I need my own cooking fuel for the camp stove?', 'rbfw_faq_content' => 'Please check at pickup; fuel arrangements may vary and can usually be arranged on request.'],
							['rbfw_faq_title' => 'Can I rent gear for a group larger than 4 people?', 'rbfw_faq_content' => 'Yes, simply increase the quantity of each item to match your group size.'],
							['rbfw_faq_title' => 'What if a tent pole or zipper is damaged during my trip?', 'rbfw_faq_content' => 'Please report any damage when you return the gear so we can assess and address it fairly.'],
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
					'content'    => 'Keep the power running wherever you need it with this reliable portable generator, well suited for outdoor events, job sites without a fixed power source, or simply as backup power during an outage at home. With a 3000-watt output, it\'s capable of running multiple devices or tools simultaneously.

Electric start means you won\'t be stuck pulling a cord repeatedly to get it running, and the unit is designed to operate quietly compared to many generators in its class. A full tank provides approximately eight hours of runtime under a moderate load, giving you a solid window of power before needing to refuel, and an extra fuel can is available as an optional add-on if you\'re planning a longer event or outage.

Rentals are priced on a daily basis and support a defined start and end date, so longer bookings around an extended job or multi-day event are straightforward to arrange. Every generator is tested before it leaves our facility to confirm reliable startup and steady output. Stock is limited and tracked live, so the availability shown reflects genuinely available units for your selected dates.

Whether you\'re running power tools at a job site, lighting and sound equipment at an outdoor event, or keeping essential appliances going during a planned or unplanned outage, this generator is built to be a dependable option rather than something you have to baby along. If you\'ve not operated a generator like this before, basic guidance on safe operation and fuel handling is available on request at pickup.',
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
							['rbfw_faq_title' => 'Is this generator electric start?', 'rbfw_faq_content' => 'Yes, it features electric start rather than a pull cord.'],
							['rbfw_faq_title' => 'What\'s the power output?', 'rbfw_faq_content' => 'It has a 3000-watt output, suitable for running multiple devices or tools at once.'],
							['rbfw_faq_title' => 'Can I rent extra fuel?', 'rbfw_faq_content' => 'Yes, an extra fuel can is available as an optional add-on.'],
							['rbfw_faq_title' => 'Is this generator quiet enough for residential use?', 'rbfw_faq_content' => 'Yes, it\'s designed to operate more quietly than many generators in its class.'],
							['rbfw_faq_title' => 'Can I book this for a multi-day event?', 'rbfw_faq_content' => 'Yes, rentals support a defined start and end date for multi-day bookings.'],
							['rbfw_faq_title' => 'Is the generator tested before each rental?', 'rbfw_faq_content' => 'Yes, every unit is tested for reliable startup and steady output before it leaves our facility.'],
							['rbfw_faq_title' => 'Do you provide operating guidance for first-time users?', 'rbfw_faq_content' => 'Yes, basic guidance on safe operation and fuel handling is available on request at pickup.'],
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
					'content'    => 'Turn any space into a proper viewing venue with this portable projector and screen set, ideal for outdoor movie nights, business presentations, community events, or any gathering that calls for a big, clear picture rather than squinting at a laptop screen. The set is designed to be easy to transport and quick to set up.

The projector delivers a genuine HD 1080p picture, giving you sharp, clear visuals whether you\'re screening a film after dark or presenting slides in a well-lit room. It pairs with a 100-inch portable screen and stand included in the rental, so you don\'t need to source or improvise your own projection surface. Both HDMI and Bluetooth input options are supported, meaning you can connect a laptop directly or stream audio and video from a mobile device without needing extra cables or adapters.

Rentals are available by the hour or by the day, which makes this a practical choice whether you\'re running a single evening event or need the set for a longer multi-day conference or series of screenings. If you\'d like better sound to match the picture quality, a Bluetooth speaker is available as an optional add-on.

Every set is tested before it leaves our facility to confirm the projector\'s bulb life, screen condition, and all connection ports are working properly, so you\'re not troubleshooting technical issues right before your event starts. Stock is limited, so we\'d recommend booking ahead of time for any date tied to a specific event you can\'t easily reschedule. Whether it\'s a backyard movie night or a client presentation, this set is built to make a professional-quality screening simple to arrange.',
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
							['rbfw_faq_title' => 'Is the projector HD?', 'rbfw_faq_content' => 'Yes, it delivers a genuine HD 1080p picture.'],
							['rbfw_faq_title' => 'Can I connect my phone for audio?', 'rbfw_faq_content' => 'Yes, Bluetooth input is supported alongside HDMI for connecting a laptop or other devices.'],
							['rbfw_faq_title' => 'Can I rent a speaker along with the projector?', 'rbfw_faq_content' => 'Yes, a Bluetooth speaker is available as an optional add-on.'],
							['rbfw_faq_title' => 'Is this suitable for outdoor use?', 'rbfw_faq_content' => 'Yes, it\'s commonly used for outdoor movie nights as well as indoor presentations.'],
							['rbfw_faq_title' => 'How long can I rent the set for?', 'rbfw_faq_content' => 'Both hourly and daily rentals are available, including multi-day bookings for longer events.'],
							['rbfw_faq_title' => 'Is setup difficult?', 'rbfw_faq_content' => 'No, the set is designed for quick, straightforward setup without special tools.'],
							['rbfw_faq_title' => 'Is the equipment tested before each rental?', 'rbfw_faq_content' => 'Yes, every set is tested for bulb life, screen condition, and connection ports before it\'s rented out.'],
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
