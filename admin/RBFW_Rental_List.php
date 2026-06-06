<?php
/*
* @Author 		rubelcuet10@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('RBFW_Rental_List')) {
    /**
     * Modern responsive card/table design for the Rental items list screen.
     *
     * Controlled by the global setting "New Rental List Design" (General Settings).
     * When enabled the default edit.php?post_type=rbfw_item list is replaced by a
     * fully responsive grid/table view; when disabled the classic WordPress list
     * is shown, so the toggle works in both directions without side effects.
     */
    class RBFW_Rental_List
    {
        const PAGE_SLUG = 'rbfw_item_list';

        public function __construct()
        {
            add_action('admin_menu', array($this, 'register_page'));
            add_action('admin_action_rbfw_duplicate_post', [$this, 'rbfw_duplicate_post_function']);
            // New design wiring.
            add_filter('rbfw_settings_field', [$this, 'register_setting']);
            add_action('load-edit.php', [$this, 'maybe_redirect_to_new_design']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
            add_filter('parent_file', [$this, 'highlight_menu']);
            add_filter('submenu_file', [$this, 'highlight_submenu']);
            add_filter('admin_title', [$this, 'admin_title'], 10, 2);
        }

        /**
         * Whether the new design is currently turned on.
         */
        public static function is_enabled(): bool
        {
            return RBFW_Function::get_general_settings('rbfw_new_list_design', 'enable') === 'enable';
        }

        public static function cpt_label(): string
        {
            return RBFW_Function::get_general_settings('rbfw_rent_label', 'Rent');
        }

        /**
         * Append the enable/disable toggle to the General settings tab.
         */
        public function register_setting($settings_fields)
        {
            $settings_fields['rbfw_basic_gen_settings'][] = array(
                'name'    => 'rbfw_new_list_design',
                'label'   => esc_html__('New Rental List Design', 'booking-and-rental-manager-for-woocommerce'),
                'desc'    => esc_html__('Enable the new responsive card/table design for the rental items list. Disable to use the classic WordPress list.', 'booking-and-rental-manager-for-woocommerce'),
                'type'    => 'select',
                'default' => 'enable',
                'options' => array(
                    'enable'  => esc_html__('Enable', 'booking-and-rental-manager-for-woocommerce'),
                    'disable' => esc_html__('Disable', 'booking-and-rental-manager-for-woocommerce'),
                ),
            );

            return $settings_fields;
        }

        /**
         * Register the hidden admin page that renders the new design.
         */
        public function register_page()
        {
            add_submenu_page(
                '',
                esc_html__('Rental Items', 'booking-and-rental-manager-for-woocommerce'),
                esc_html__('Rental Items', 'booking-and-rental-manager-for-woocommerce'),
                'edit_posts',
                self::PAGE_SLUG,
                array($this, 'render_page')
            );
        }

        /**
         * Send the default CPT list to the new design when the setting is on.
         */
        public function maybe_redirect_to_new_design()
        {
            if (!self::is_enabled()) {
                return;
            }
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                return;
            }
            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
            if ($post_type !== 'rbfw_item') {
                return;
            }
            $status = isset($_GET['post_status']) ? sanitize_text_field(wp_unslash($_GET['post_status'])) : '';
            $view   = isset($_GET['rbfw_view']) ? sanitize_text_field(wp_unslash($_GET['rbfw_view'])) : '';
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            if ($status === 'trash' || $view === 'classic') {
                return;
            }
            if (!current_user_can('edit_posts')) {
                return;
            }
            wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG));
            exit;
        }

        public function enqueue_assets($hook)
        {
            if ($hook !== 'admin_page_' . self::PAGE_SLUG) {
                return;
            }
            wp_enqueue_style('rbfw-rental-list-font', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null);
            wp_enqueue_style('rbfw_rental_list', RBFW_PLUGIN_URL . '/assets/admin/rbfw_rental_list.css', array(), time());
            wp_enqueue_script('rbfw_rental_list', RBFW_PLUGIN_URL . '/assets/admin/rbfw_rental_list.js', array('jquery'), time(), true);
        }

        /**
         * Keep the Rental CPT top menu open while viewing the orphan page.
         */
        public function highlight_menu($parent_file)
        {
            global $pagenow;
            if ($pagenow === 'admin.php' && ($_GET['page'] ?? '') === self::PAGE_SLUG) {
                return 'edit.php?post_type=rbfw_item';
            }

            return $parent_file;
        }

        public function highlight_submenu($submenu_file)
        {
            if (($_GET['page'] ?? '') === self::PAGE_SLUG) {
                return 'edit.php?post_type=rbfw_item';
            }

            return $submenu_file;
        }

        public function admin_title($admin_title, $title)
        {
            if (($_GET['page'] ?? '') !== self::PAGE_SLUG) {
                return $admin_title;
            }
            $is_trash = isset($_GET['rbfw_status']) && sanitize_text_field(wp_unslash($_GET['rbfw_status'])) === 'trash';
            $label    = self::cpt_label() . ' ' . esc_html__('List', 'booking-and-rental-manager-for-woocommerce');
            if ($is_trash) {
                $label = esc_html__('Trash', 'booking-and-rental-manager-for-woocommerce') . ' - ' . $label;
            }

            return $label . ' &lsaquo; ' . get_bloginfo('name') . ' &#8212; WordPress';
        }

        /**
         * Collect rental items for the current view.
         */
        private function get_items($statuses = array('publish', 'draft', 'pending', 'private')): array
        {
            $query = new WP_Query(array(
                'post_type'      => 'rbfw_item',
                'post_status'    => $statuses,
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ));
            $rent_types = RBFW_Function::rbfw_rent_types();
            $items = array();
            foreach ($query->posts as $post) {
                $pid       = $post->ID;
                $type_key  = RBFW_Function::get_post_info($pid, 'rbfw_item_type');
                $type_lbl  = isset($rent_types[$type_key]) ? $rent_types[$type_key] : ($type_key ?: '');
                $cats      = get_post_meta($pid, 'rbfw_categories', true);
                $cats      = is_array($cats) ? array_filter($cats) : array();
                $items[]   = array(
                    'id'         => $pid,
                    'title'      => get_the_title($pid) ?: esc_html__('(no title)', 'booking-and-rental-manager-for-woocommerce'),
                    'type_key'   => $type_key,
                    'type_label' => $type_lbl,
                    'cats'       => $cats,
                    'price'      => $this->get_price_label($pid),
                    'rates'      => $this->get_rates($pid),
                    'status'     => $post->post_status,
                    'thumb'      => get_the_post_thumbnail_url($pid, 'medium_large'),
                    'author'     => get_the_author_meta('display_name', $post->post_author),
                    'date'       => get_the_date('M j, Y', $pid),
                    'edit_link'  => get_edit_post_link($pid, 'raw'),
                    'view_link'  => get_permalink($pid),
                    'trash_link' => get_delete_post_link($pid),
                    'restore_link' => wp_nonce_url(admin_url(sprintf('post.php?post=%d&action=untrash', $pid)), 'untrash-post_' . $pid),
                    'delete_link'  => get_delete_post_link($pid, '', true),
                );
            }

            return $items;
        }

        private function get_price_label($pid): string
        {
            $daily  = RBFW_Function::get_post_info($pid, 'rbfw_daily_rate');
            $hourly = RBFW_Function::get_post_info($pid, 'rbfw_hourly_rate');
            $amount = '';
            $suffix = '';
            if ($daily !== '' && (float) $daily > 0) {
                $amount = $daily;
                $suffix = esc_html__('/day', 'booking-and-rental-manager-for-woocommerce');
            } elseif ($hourly !== '' && (float) $hourly > 0) {
                $amount = $hourly;
                $suffix = esc_html__('/hour', 'booking-and-rental-manager-for-woocommerce');
            }
            if ($amount === '') {
                return '';
            }
            $formatted = function_exists('wc_price') ? wp_strip_all_tags(wc_price($amount)) : number_format_i18n((float) $amount, 2);

            return $formatted . ' ' . $suffix;
        }

        /**
         * Full rate breakdown (only the rates that are actually configured) for
         * compact display as chips.
         */
        private function get_rates($pid): array
        {
            $defs = array(
                'rbfw_hourly_rate'   => esc_html__('hr', 'booking-and-rental-manager-for-woocommerce'),
                'rbfw_half_day_rate' => esc_html__('½ day', 'booking-and-rental-manager-for-woocommerce'),
                'rbfw_daily_rate'    => esc_html__('day', 'booking-and-rental-manager-for-woocommerce'),
                'rbfw_weekly_rate'   => esc_html__('wk', 'booking-and-rental-manager-for-woocommerce'),
                'rbfw_monthly_rate'  => esc_html__('mo', 'booking-and-rental-manager-for-woocommerce'),
            );
            $rates = array();
            foreach ($defs as $key => $unit) {
                $val = RBFW_Function::get_post_info($pid, $key);
                if ($val !== '' && (float) $val > 0) {
                    $amount  = function_exists('wc_price') ? wp_strip_all_tags(wc_price($val)) : number_format_i18n((float) $val, 2);
                    $rates[] = array('unit' => $unit, 'amount' => $amount);
                }
            }

            return $rates;
        }

        private function initials($name): string
        {
            $name = trim(wp_strip_all_tags((string) $name));
            if ($name === '') {
                return '?';
            }
            $parts = preg_split('/\s+/', $name);
            $first = mb_substr($parts[0], 0, 1);
            $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

            return mb_strtoupper($first . $last);
        }

        private function status_label($status): string
        {
            switch ($status) {
                case 'publish':
                    return esc_html__('Published', 'booking-and-rental-manager-for-woocommerce');
                case 'draft':
                    return esc_html__('Draft', 'booking-and-rental-manager-for-woocommerce');
                case 'pending':
                    return esc_html__('Pending', 'booking-and-rental-manager-for-woocommerce');
                case 'private':
                    return esc_html__('Private', 'booking-and-rental-manager-for-woocommerce');
                default:
                    return esc_html(ucfirst($status));
            }
        }

        public function render_page()
        {
            $name = self::cpt_label();
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $is_trash = isset($_GET['rbfw_status']) && sanitize_text_field(wp_unslash($_GET['rbfw_status'])) === 'trash';

            $active    = $this->get_items();
            $total     = count($active);
            $published = 0;
            $draft     = 0;
            $cats_all  = array();
            foreach ($active as $it) {
                if ($it['status'] === 'publish') {
                    $published++;
                } elseif ($it['status'] === 'draft') {
                    $draft++;
                }
                foreach ($it['cats'] as $c) {
                    $cats_all[$c] = true;
                }
            }
            $cat_count = count($cats_all);

            $status_counts = wp_count_posts('rbfw_item');
            $trash         = isset($status_counts->trash) ? (int) $status_counts->trash : 0;

            $items     = $is_trash ? $this->get_items(array('trash')) : $active;
            $base_url  = admin_url('admin.php?page=' . self::PAGE_SLUG);
            $trash_url = add_query_arg('rbfw_status', 'trash', $base_url);
            $add_url   = admin_url('post-new.php?post_type=rbfw_item');
            $classic   = admin_url('edit.php?post_type=rbfw_item&rbfw_view=classic');
            $rent_types = RBFW_Function::rbfw_rent_types();
            ?>
            <div class="wrap rbfw-fleet-wrap">
                <div class="rbfw-fleet">

                    <div class="rbfw-page-header">
                        <div class="rbfw-page-title"><?php echo esc_html($name); ?> <?php esc_html_e('Lists', 'booking-and-rental-manager-for-woocommerce'); ?>
                            <span><?php echo esc_html(sprintf(_n('%d item', '%d items', $total, 'booking-and-rental-manager-for-woocommerce'), $total)); ?></span>
                        </div>
                        <div class="rbfw-header-actions">
                            <a class="rbfw-classic-link" href="<?php echo esc_url($classic); ?>">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                <?php esc_html_e('Classic view', 'booking-and-rental-manager-for-woocommerce'); ?>
                            </a>
                            <a class="rbfw-add-btn" href="<?php echo esc_url($add_url); ?>">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <?php printf(esc_html__('Add New %s', 'booking-and-rental-manager-for-woocommerce'), esc_html($name)); ?>
                            </a>
                        </div>
                    </div>

                    <div class="rbfw-stats">
                        <div class="rbfw-stat-card">
                            <div class="rbfw-stat-icon red">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7L12 3 4 7v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 21V11"/></svg>
                            </div>
                            <div><div class="rbfw-stat-num"><?php echo esc_html($total); ?></div><div class="rbfw-stat-label"><?php printf(esc_html__('Total %s', 'booking-and-rental-manager-for-woocommerce'), esc_html($name)); ?></div></div>
                        </div>
                        <div class="rbfw-stat-card">
                            <div class="rbfw-stat-icon green">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                            </div>
                            <div><div class="rbfw-stat-num"><?php echo esc_html($published); ?></div><div class="rbfw-stat-label"><?php esc_html_e('Published', 'booking-and-rental-manager-for-woocommerce'); ?></div></div>
                        </div>
                        <div class="rbfw-stat-card">
                            <div class="rbfw-stat-icon orange">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <div><div class="rbfw-stat-num"><?php echo esc_html($draft); ?></div><div class="rbfw-stat-label"><?php esc_html_e('Draft', 'booking-and-rental-manager-for-woocommerce'); ?></div></div>
                        </div>
                        <div class="rbfw-stat-card">
                            <div class="rbfw-stat-icon blue">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </div>
                            <div><div class="rbfw-stat-num"><?php echo esc_html($cat_count); ?></div><div class="rbfw-stat-label"><?php esc_html_e('Categories', 'booking-and-rental-manager-for-woocommerce'); ?></div></div>
                        </div>
                    </div>

                    <div class="rbfw-filters">
                        <div class="rbfw-tab-pills">
                            <?php if ($is_trash) : ?>
                                <a class="rbfw-tab-pill" href="<?php echo esc_url($base_url); ?>"><?php printf(esc_html__('All (%d)', 'booking-and-rental-manager-for-woocommerce'), $total); ?></a>
                                <a class="rbfw-tab-pill" href="<?php echo esc_url($base_url); ?>"><?php printf(esc_html__('Published (%d)', 'booking-and-rental-manager-for-woocommerce'), $published); ?></a>
                                <a class="rbfw-tab-pill" href="<?php echo esc_url($base_url); ?>"><?php printf(esc_html__('Draft (%d)', 'booking-and-rental-manager-for-woocommerce'), $draft); ?></a>
                                <span class="rbfw-tab-pill rbfw-tab-trash active">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                    <?php printf(esc_html__('Trash (%d)', 'booking-and-rental-manager-for-woocommerce'), $trash); ?>
                                </span>
                            <?php else : ?>
                                <button class="rbfw-tab-pill rbfw-filter-pill active" data-status=""><?php printf(esc_html__('All (%d)', 'booking-and-rental-manager-for-woocommerce'), $total); ?></button>
                                <button class="rbfw-tab-pill rbfw-filter-pill" data-status="publish"><?php printf(esc_html__('Published (%d)', 'booking-and-rental-manager-for-woocommerce'), $published); ?></button>
                                <button class="rbfw-tab-pill rbfw-filter-pill" data-status="draft"><?php printf(esc_html__('Draft (%d)', 'booking-and-rental-manager-for-woocommerce'), $draft); ?></button>
                                <a class="rbfw-tab-pill rbfw-tab-trash" href="<?php echo esc_url($trash_url); ?>" title="<?php esc_attr_e('View trashed items', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                    <?php printf(esc_html__('Trash (%d)', 'booking-and-rental-manager-for-woocommerce'), $trash); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="rbfw-search-box">
                            <svg class="rbfw-search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" placeholder="<?php esc_attr_e('Search items...', 'booking-and-rental-manager-for-woocommerce'); ?>" id="rbfwSearchInput" autocomplete="off">
                            <button type="button" class="rbfw-search-clear" id="rbfwSearchClear" aria-label="<?php esc_attr_e('Clear search', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <select class="rbfw-filter-select" id="rbfwTypeFilter">
                            <option value=""><?php esc_html_e('All Types', 'booking-and-rental-manager-for-woocommerce'); ?></option>
                            <?php foreach ($rent_types as $tk => $tl) : ?>
                                <option value="<?php echo esc_attr($tk); ?>"><?php echo esc_html($tl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="rbfw-view-toggle">
                            <button class="rbfw-vtog" id="rbfwGridBtn" title="<?php esc_attr_e('Grid view', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            </button>
                            <button class="rbfw-vtog active" id="rbfwListBtn" title="<?php esc_attr_e('List view', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($items)) : ?>
                        <div class="rbfw-no-data">
                            <?php if ($is_trash) : ?>
                                <p><?php esc_html_e('Trash is empty.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                                <a class="rbfw-classic-link" href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Back to list', 'booking-and-rental-manager-for-woocommerce'); ?></a>
                            <?php else : ?>
                                <p><?php printf(esc_html__('No %s items found yet.', 'booking-and-rental-manager-for-woocommerce'), esc_html(strtolower($name))); ?></p>
                                <a class="rbfw-add-btn" href="<?php echo esc_url($add_url); ?>"><?php printf(esc_html__('Add your first %s', 'booking-and-rental-manager-for-woocommerce'), esc_html($name)); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>

                    <div class="rbfw-grid" id="rbfwGrid">
                        <?php foreach ($items as $it) :
                            $first_cat = !empty($it['cats']) ? reset($it['cats']) : '';
                            ?>
                            <div class="rbfw-card" data-name="<?php echo esc_attr(strtolower($it['title'] . ' ' . implode(' ', $it['cats']))); ?>" data-type="<?php echo esc_attr($it['type_key']); ?>" data-status="<?php echo esc_attr($it['status']); ?>">
                                <div class="rbfw-thumb">
                                    <?php if ($it['thumb']) : ?>
                                        <img src="<?php echo esc_url($it['thumb']); ?>" alt="<?php echo esc_attr($it['title']); ?>">
                                    <?php else : ?>
                                        <div class="rbfw-thumb-placeholder">
                                            <svg width="46" height="46" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 7L12 3 4 7v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 21V11"/></svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="rbfw-thumb-overlay"></div>
                                    <div class="rbfw-thumb-badges">
                                        <?php if ($it['type_label']) : ?><span class="rbfw-thumb-badge type"><?php echo esc_html($it['type_label']); ?></span><?php endif; ?>
                                        <?php if ($it['price']) : ?><span class="rbfw-thumb-badge price"><?php echo esc_html($it['price']); ?></span><?php endif; ?>
                                    </div>
                                    <div class="rbfw-actions-top">
                                        <?php if ($is_trash) : ?>
                                            <a class="rbfw-act-btn restore" href="<?php echo esc_url($it['restore_link']); ?>" title="<?php esc_attr_e('Restore', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.3"/><polyline points="3 4 3 8 7 8"/></svg>
                                            </a>
                                            <a class="rbfw-act-btn del" href="<?php echo esc_url($it['delete_link']); ?>" title="<?php esc_attr_e('Delete Permanently', 'booking-and-rental-manager-for-woocommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Permanently delete this item? This cannot be undone.', 'booking-and-rental-manager-for-woocommerce')); ?>');">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </a>
                                        <?php else : ?>
                                            <?php if ($it['view_link']) : ?>
                                            <a class="rbfw-act-btn view" href="<?php echo esc_url($it['view_link']); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e('View on frontend', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <?php endif; ?>
                                            <a class="rbfw-act-btn edit" href="<?php echo esc_url($it['edit_link']); ?>" title="<?php esc_attr_e('Edit', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <a class="rbfw-act-btn del" href="<?php echo esc_url($it['trash_link']); ?>" title="<?php esc_attr_e('Move to Trash', 'booking-and-rental-manager-for-woocommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Move this item to Trash?', 'booking-and-rental-manager-for-woocommerce')); ?>');">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="rbfw-body">
                                    <?php if ($is_trash) : ?>
                                        <span class="rbfw-name"><?php echo esc_html($it['title']); ?></span>
                                    <?php else : ?>
                                        <a class="rbfw-name" href="<?php echo esc_url($it['edit_link']); ?>"><?php echo esc_html($it['title']); ?></a>
                                    <?php endif; ?>
                                    <div class="rbfw-meta">
                                        <?php if ($it['type_label']) : ?><span class="rbfw-meta-pill type"><?php echo esc_html($it['type_label']); ?></span><?php endif; ?>
                                        <?php if ($first_cat) : ?><span class="rbfw-meta-pill cat"><?php echo esc_html($first_cat); ?></span><?php endif; ?>
                                    </div>
                                    <?php if (!empty($it['rates'])) : ?>
                                    <div class="rbfw-rates"><?php foreach ($it['rates'] as $r) : ?><span class="rbfw-rate-chip"><?php echo esc_html($r['amount']); ?><small><?php echo esc_html($r['unit']); ?></small></span><?php endforeach; ?></div>
                                    <?php endif; ?>
                                    <div class="rbfw-footer">
                                        <div class="rbfw-author"><span class="rbfw-author-avatar"><?php echo esc_html($this->initials($it['author'])); ?></span> <?php echo esc_html($it['author']); ?></div>
                                        <span class="rbfw-status-dot status-<?php echo esc_attr($it['status']); ?>"><?php echo esc_html($this->status_label($it['status'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <table class="rbfw-table" id="rbfwTable">
                        <thead>
                            <tr>
                                <th class="rbfw-th-img"><?php esc_html_e('Image', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Name', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Price Type', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Price', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Status', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Actions', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it) : ?>
                                <tr class="rbfw-row" data-name="<?php echo esc_attr(strtolower($it['title'] . ' ' . implode(' ', $it['cats']))); ?>" data-type="<?php echo esc_attr($it['type_key']); ?>" data-status="<?php echo esc_attr($it['status']); ?>">
                                    <td class="rbfw-td-img" data-label="<?php esc_attr_e('Image', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                        <?php $img_href = $is_trash ? '' : ($it['view_link'] ?: $it['edit_link']); ?>
                                        <?php if ($img_href) : ?><a class="rbfw-table-thumb" href="<?php echo esc_url($img_href); ?>"<?php echo $it['view_link'] && !$is_trash ? ' target="_blank" rel="noopener"' : ''; ?>><?php else : ?><span class="rbfw-table-thumb"><?php endif; ?>
                                            <?php if ($it['thumb']) : ?>
                                                <img src="<?php echo esc_url($it['thumb']); ?>" alt="<?php echo esc_attr($it['title']); ?>">
                                            <?php else : ?>
                                                <span class="rbfw-table-thumb-ph"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 7L12 3 4 7v10l8 4 8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 21V11"/></svg></span>
                                            <?php endif; ?>
                                        <?php echo $img_href ? '</a>' : '</span>'; ?>
                                    </td>
                                    <td class="rbfw-td-name" data-label="<?php esc_attr_e('Name', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                        <?php if ($is_trash) : ?><span class="rbfw-cell-name"><?php echo esc_html($it['title']); ?></span><?php else : ?><a class="rbfw-cell-name" href="<?php echo esc_url($it['edit_link']); ?>"><?php echo esc_html($it['title']); ?></a><?php endif; ?>
                                        <span class="rbfw-cell-meta">
                                            <span class="rbfw-cm id">#<?php echo esc_html($it['id']); ?></span>
                                            <?php if ($it['cats']) : ?><span class="rbfw-cm cat"><?php echo esc_html(implode(', ', $it['cats'])); ?></span><?php endif; ?>
                                            <span class="rbfw-cm date"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><?php echo esc_html($it['date']); ?></span>
                                        </span>
                                    </td>
                                    <td data-label="<?php esc_attr_e('Price Type', 'booking-and-rental-manager-for-woocommerce'); ?>"><?php echo $it['type_label'] ? '<span class="rbfw-t-badge type">' . esc_html($it['type_label']) . '</span>' : '-'; ?></td>
                                    <td data-label="<?php esc_attr_e('Price', 'booking-and-rental-manager-for-woocommerce'); ?>"><?php if (!empty($it['rates'])) : ?><span class="rbfw-rates"><?php foreach ($it['rates'] as $r) : ?><span class="rbfw-rate-chip"><?php echo esc_html($r['amount']); ?><small><?php echo esc_html($r['unit']); ?></small></span><?php endforeach; ?></span><?php else : ?>-<?php endif; ?></td>
                                    <td data-label="<?php esc_attr_e('Status', 'booking-and-rental-manager-for-woocommerce'); ?>"><span class="rbfw-status-dot status-<?php echo esc_attr($it['status']); ?>"><?php echo esc_html($this->status_label($it['status'])); ?></span></td>
                                    <td data-label="<?php esc_attr_e('Actions', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                        <div class="rbfw-table-acts">
                                        <?php if ($is_trash) : ?>
                                            <a class="rbfw-table-act restore" href="<?php echo esc_url($it['restore_link']); ?>" title="<?php esc_attr_e('Restore', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Restore', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.3"/><polyline points="3 4 3 8 7 8"/></svg>
                                            </a>
                                            <a class="rbfw-table-act del" href="<?php echo esc_url($it['delete_link']); ?>" title="<?php esc_attr_e('Delete Permanently', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Delete Permanently', 'booking-and-rental-manager-for-woocommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Permanently delete this item? This cannot be undone.', 'booking-and-rental-manager-for-woocommerce')); ?>');">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </a>
                                        <?php else : ?>
                                            <?php if ($it['view_link']) : ?><a class="rbfw-table-act view" href="<?php echo esc_url($it['view_link']); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e('View on frontend', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('View on frontend', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a><?php endif; ?>
                                            <a class="rbfw-table-act edit" href="<?php echo esc_url($it['edit_link']); ?>" title="<?php esc_attr_e('Edit', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Edit', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <a class="rbfw-table-act del" href="<?php echo esc_url($it['trash_link']); ?>" title="<?php esc_attr_e('Move to Trash', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Move to Trash', 'booking-and-rental-manager-for-woocommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Move this item to Trash?', 'booking-and-rental-manager-for-woocommerce')); ?>');">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="rbfw-empty" id="rbfwEmptyMsg"><?php esc_html_e('No items found matching your search.', 'booking-and-rental-manager-for-woocommerce'); ?></div>

                    <div class="rbfw-pagination" id="rbfwPagination">
                        <div class="rbfw-page-info" id="rbfwPageInfo"></div>
                        <div class="rbfw-page-btns" id="rbfwPageBtns"></div>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
            <?php
        }

        function rbfw_duplicate_post_function()
        {
            if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce($_GET['_wpnonce'], 'rbfw_duplicate_post_' . sanitize_text_field($_GET['post_id']))
            ) {
                wp_die('Invalid request (missing or invalid nonce).');
            }

            $post_id = (int)sanitize_text_field(wp_unslash($_GET['post_id']));
            $post = get_post($post_id);

            $new_post = array(
                'post_title' => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status' => 'draft',
                'post_type' => $post->post_type,
                'post_author' => get_current_user_id(),
            );

            $new_post_id = wp_insert_post($new_post);

            if (is_wp_error($new_post_id) || !$new_post_id) {
                wp_die('Failed to duplicate post.');
            }
            $meta = get_post_meta($post_id);
            foreach ($meta as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        }

    }

    new RBFW_Rental_List();

}
