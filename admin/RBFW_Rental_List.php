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
                'post_type'              => 'rbfw_item',
                'post_status'            => $statuses,
                'posts_per_page'         => -1,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'no_found_rows'          => true,
                'update_post_meta_cache' => true,
                'update_term_meta_cache' => true,
            ));
            $rent_types = RBFW_Function::rbfw_rent_types();
            // Resolved in one pass so AIOSEO needs a single query rather than one per row.
            $seo_data = $this->seo_data(wp_list_pluck($query->posts, 'ID'));
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
                    'seo'        => isset($seo_data[$pid]) ? $seo_data[$pid] : null,
                    'status'     => $post->post_status,
                    'thumb'      => get_the_post_thumbnail_url($pid, 'medium_large'),
                    'author'     => get_the_author_meta('display_name', $post->post_author),
                    'edit_link'  => get_edit_post_link($pid, 'raw'),
                    'view_link'  => get_permalink($pid),
                    'trash_link' => get_delete_post_link($pid),
                    'restore_link' => wp_nonce_url(admin_url(sprintf('post.php?post=%d&action=untrash', $pid)), 'untrash-post_' . $pid),
                    'delete_link'  => get_delete_post_link($pid, '', true),
                    // Reuses the existing secure duplicator (RBFW_Rent_Manager::duplicate_post_link /
                    // rbfw_duplicate_post on admin_init): capability-checked and resets inventory on the copy.
                    'duplicate_link' => wp_nonce_url(admin_url('edit.php?post_type=rbfw_item&rbfw_duplicate=' . $pid), 'duplicate_post_action', 'nonce'),
                );
            }

            return $items;
        }

        /**
         * Which SEO plugin is currently ACTIVE, if any.
         *
         * Detection is by runtime constant, not by the plugin being present on
         * disk: an installed-but-deactivated plugin stores no scores, so the
         * column must stay hidden for it.
         *
         * @return string '' | 'yoast' | 'rankmath' | 'aioseo' | 'seopress'
         */
        private function seo_plugin(): string
        {
            static $plugin = null;
            if (null !== $plugin) {
                return $plugin;
            }

            if (defined('WPSEO_VERSION')) {
                $plugin = 'yoast';
            } elseif (defined('RANK_MATH_VERSION')) {
                $plugin = 'rankmath';
            } elseif (defined('AIOSEO_VERSION')) {
                $plugin = 'aioseo';
            } elseif (defined('SEOPRESS_VERSION')) {
                $plugin = 'seopress';
            } else {
                $plugin = '';
            }

            /**
             * Let an unsupported SEO plugin declare itself; pair with
             * `rbfw_item_seo_data` to supply the signals.
             */
            $plugin = (string) apply_filters('rbfw_item_seo_plugin', $plugin);

            return $plugin;
        }

        /**
         * Human name of the active SEO plugin, for the column tooltip.
         */
        private function seo_plugin_label(): string
        {
            switch ($this->seo_plugin()) {
                case 'yoast':
                    return __('Yoast SEO', 'booking-and-rental-manager-for-woocommerce');
                case 'rankmath':
                    return __('Rank Math', 'booking-and-rental-manager-for-woocommerce');
                case 'aioseo':
                    return __('All in One SEO', 'booking-and-rental-manager-for-woocommerce');
                case 'seopress':
                    return __('SEOPress', 'booking-and-rental-manager-for-woocommerce');
            }

            return __('SEO', 'booking-and-rental-manager-for-woocommerce');
        }

        /**
         * SEO signals for a set of items, keyed by post ID.
         *
         * Read in bulk rather than per row: the meta-backed plugins ride the
         * WP_Query meta cache, and AIOSEO (which stores its data in its own
         * table) gets a single query instead of one per item.
         *
         * Every entry has the same shape:
         *   score       int|null  0-100, null when not analysed
         *   readability int|null  0-100, null when the plugin has no such score
         *   keyword     string    primary focus keyword ('' when unset)
         *   has_desc    bool      a meta description is set
         *   noindex     bool      excluded from search engines
         *
         * Both Yoast and Rank Math store 0 for an unanalysed post, so 0 is
         * treated as "not analysed", the way their own UIs treat it.
         *
         * Meta keys are taken from each plugin's source, not guessed:
         * Yoast builds keys from $meta_prefix '_yoast_wpseo_' + field name, and
         * its meta-robots-noindex is '1' = no-index ('0' post-type default,
         * '2' = index).
         *
         * @param int[] $post_ids
         * @return array<int, array<string, mixed>>
         */
        private function seo_data(array $post_ids): array
        {
            $plugin = $this->seo_plugin();
            $out    = array();
            $blank  = array(
                'score'       => null,
                'readability' => null,
                'keyword'     => '',
                'has_desc'    => false,
                'noindex'     => false,
            );

            if ('' === $plugin || empty($post_ids)) {
                return $out;
            }

            switch ($plugin) {
                case 'yoast':
                    foreach ($post_ids as $pid) {
                        $score = get_post_meta($pid, '_yoast_wpseo_linkdex', true);
                        $read  = get_post_meta($pid, '_yoast_wpseo_content_score', true);
                        $out[$pid] = array(
                            'score'       => ('' === $score || null === $score) ? null : (int) $score,
                            'readability' => ('' === $read || null === $read) ? null : (int) $read,
                            'keyword'     => (string) get_post_meta($pid, '_yoast_wpseo_focuskw', true),
                            'has_desc'    => '' !== trim((string) get_post_meta($pid, '_yoast_wpseo_metadesc', true)),
                            'noindex'     => '1' === (string) get_post_meta($pid, '_yoast_wpseo_meta-robots-noindex', true),
                        );
                    }
                    break;

                case 'rankmath':
                    foreach ($post_ids as $pid) {
                        $score  = get_post_meta($pid, 'rank_math_seo_score', true);
                        $kw     = (string) get_post_meta($pid, 'rank_math_focus_keyword', true);
                        $robots = get_post_meta($pid, 'rank_math_robots', true);
                        $kw     = explode(',', $kw); // comma-separated; first entry is the primary keyword
                        $out[$pid] = array(
                            'score'       => ('' === $score || null === $score) ? null : (int) $score,
                            'readability' => null, // Rank Math reports one combined score, no separate readability
                            'keyword'     => trim((string) reset($kw)),
                            'has_desc'    => '' !== trim((string) get_post_meta($pid, 'rank_math_description', true)),
                            'noindex'     => is_array($robots) && in_array('noindex', $robots, true),
                        );
                    }
                    break;

                case 'aioseo':
                    global $wpdb;
                    $table = $wpdb->prefix . 'aioseo_posts';
                    // The constant can exist before the table does (fresh install).
                    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table) {
                        $ids = implode(',', array_map('absint', $post_ids));
                        // $ids is a sanitised integer list; the table name is prefix-derived.
                        $rows = $wpdb->get_results("SELECT post_id, seo_score, description, keyphrases, robots_noindex FROM {$table} WHERE post_id IN ({$ids})"); // phpcs:ignore WordPress.DB.PreparedSQL
                        foreach ((array) $rows as $row) {
                            $kw         = '';
                            $keyphrases = json_decode((string) $row->keyphrases, true);
                            if (isset($keyphrases['focus']['keyphrase'])) {
                                $kw = (string) $keyphrases['focus']['keyphrase'];
                            }
                            $out[(int) $row->post_id] = array(
                                'score'       => (null === $row->seo_score) ? null : (int) $row->seo_score,
                                'readability' => null,
                                'keyword'     => $kw,
                                'has_desc'    => '' !== trim((string) $row->description),
                                'noindex'     => ! empty($row->robots_noindex),
                            );
                        }
                    }
                    break;

                case 'seopress':
                    foreach ($post_ids as $pid) {
                        $data = get_post_meta($pid, '_seopress_analysis_data', true);
                        $out[$pid] = array(
                            'score'       => (is_array($data) && isset($data['score']['value'])) ? (int) $data['score']['value'] : null,
                            'readability' => null,
                            'keyword'     => (string) get_post_meta($pid, '_seopress_analysis_target_kw', true),
                            'has_desc'    => '' !== trim((string) get_post_meta($pid, '_seopress_titles_desc', true)),
                            'noindex'     => 'yes' === (string) get_post_meta($pid, '_seopress_robots_index', true),
                        );
                    }
                    break;
            }

            // Normalise: every requested id gets the full shape, so the view never
            // has to guard for a missing key.
            foreach ($post_ids as $pid) {
                $out[$pid] = isset($out[$pid]) ? array_merge($blank, $out[$pid]) : $blank;
            }

            /**
             * Filter the resolved SEO signals, keyed by post ID. Use with
             * `rbfw_item_seo_plugin` to support an SEO plugin this list does not
             * know about; keep the array shape documented above.
             */
            return (array) apply_filters('rbfw_item_seo_data', $out, $post_ids, $plugin);
        }

        /**
         * Bucket a score using the active plugin's own thresholds, so the badge
         * agrees with what that plugin shows on the edit screen.
         *
         * @param int  $score          0-100
         * @param bool $is_readability Readability is graded on its own 71/41
         *                             bands, independent of the plugin's SEO bands.
         * @return string 'good' | 'ok' | 'bad'
         */
        private function seo_rank(int $score, bool $is_readability = false): string
        {
            if ($is_readability) {
                if ($score >= 71) {
                    return 'good';
                }
                return ($score >= 41) ? 'ok' : 'bad';
            }

            switch ($this->seo_plugin()) {
                case 'rankmath':
                    if ($score >= 81) {
                        return 'good';
                    }
                    return ($score >= 51) ? 'ok' : 'bad';

                case 'aioseo':
                    if ($score >= 80) {
                        return 'good';
                    }
                    return ($score >= 50) ? 'ok' : 'bad';
            }

            // Yoast / SEOPress / default — mirrors WPSEO_Rank: 71+ good, 41-70 ok.
            if ($score >= 71) {
                return 'good';
            }

            return ($score >= 41) ? 'ok' : 'bad';
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

            // SEO column is opt-in on the active SEO plugin: no plugin, no column.
            $seo_active = '' !== $this->seo_plugin();
            $seo_label  = $this->seo_plugin_label();

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
            $add_url   = class_exists( 'RBFW_Modern_Editor' ) ? RBFW_Modern_Editor::add_new_url() : admin_url( 'post-new.php?post_type=rbfw_item' );
            $rent_types = RBFW_Function::rbfw_rent_types();
            ?>
            <div class="wrap rbfw-fleet-wrap">
                <div class="rbfw-fleet">

                    <div class="rbfw-page-header">
                        <div class="rbfw-page-title"><?php echo esc_html($name); ?> <?php esc_html_e('Lists', 'booking-and-rental-manager-for-woocommerce'); ?>
                            <span><?php echo esc_html(sprintf(_n('%d item', '%d items', $total, 'booking-and-rental-manager-for-woocommerce'), $total)); ?></span>
                        </div>
                        <div class="rbfw-header-actions">
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
                                            <a class="rbfw-act-btn dup" href="<?php echo esc_url($it['duplicate_link']); ?>" title="<?php esc_attr_e('Duplicate', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
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
                                <?php // SEO column appears only while an SEO plugin is active — header and cell are gated together. ?>
                                <?php if ($seo_active) : ?>
                                <th class="rbfw-th-seo" title="<?php echo esc_attr(sprintf(/* translators: %s: SEO plugin name */ __('SEO score from %s', 'booking-and-rental-manager-for-woocommerce'), $seo_label)); ?>"><?php esc_html_e('SEO', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                                <?php endif; ?>
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
                                        <span class="rbfw-cell-sub">#<?php echo esc_html($it['id']); ?><?php echo $it['cats'] ? ' · ' . esc_html(implode(', ', $it['cats'])) : ''; ?></span>
                                    </td>
                                    <td data-label="<?php esc_attr_e('Price Type', 'booking-and-rental-manager-for-woocommerce'); ?>"><?php echo $it['type_label'] ? '<span class="rbfw-t-badge type">' . esc_html($it['type_label']) . '</span>' : '-'; ?></td>
                                    <td data-label="<?php esc_attr_e('Price', 'booking-and-rental-manager-for-woocommerce'); ?>"><?php echo esc_html($it['price'] ?: '-'); ?></td>
                                    <?php if ($seo_active) : $seo = $it['seo']; ?>
                                    <td class="rbfw-td-seo" data-label="<?php esc_attr_e('SEO', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                        <span class="rbfw-seo-line">
                                            <?php if (null === $seo['score'] || 0 === $seo['score']) : ?>
                                                <span class="rbfw-seo-badge is-none" title="<?php esc_attr_e('Not analysed yet — open the item and set a focus keyword.', 'booking-and-rental-manager-for-woocommerce'); ?>">&mdash;</span>
                                            <?php else : ?>
                                                <span class="rbfw-seo-badge is-<?php echo esc_attr($this->seo_rank((int) $seo['score'])); ?>" title="<?php echo esc_attr(sprintf(/* translators: 1: score 0-100, 2: SEO plugin name */ __('SEO score %1$s/100 (%2$s)', 'booking-and-rental-manager-for-woocommerce'), number_format_i18n((int) $seo['score']), $seo_label)); ?>">
                                                    <?php echo esc_html(number_format_i18n((int) $seo['score'])); ?><i>/100</i>
                                                </span>
                                            <?php endif; ?>
                                            <?php // Readability is a separate score; only Yoast reports one. ?>
                                            <?php if (null !== $seo['readability'] && 0 !== $seo['readability']) : ?>
                                                <span class="rbfw-seo-badge is-<?php echo esc_attr($this->seo_rank((int) $seo['readability'], true)); ?>" title="<?php echo esc_attr(sprintf(/* translators: %s: score 0-100 */ __('Readability %s/100', 'booking-and-rental-manager-for-woocommerce'), number_format_i18n((int) $seo['readability']))); ?>">
                                                    <b>Aa</b> <?php echo esc_html(number_format_i18n((int) $seo['readability'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <?php
                                        // Detail line: keyword text, then icon-only warnings. Icons rather
                                        // than words because this column is narrow — the tooltip carries
                                        // the explanation, so the cell stays one line at any table width.
                                        ?>
                                        <span class="rbfw-seo-sub">
                                            <?php if ('' !== $seo['keyword']) : ?>
                                                <span class="rbfw-seo-kw" title="<?php echo esc_attr(sprintf(/* translators: %s: focus keyword */ __('Focus keyword: %s', 'booking-and-rental-manager-for-woocommerce'), $seo['keyword'])); ?>"><?php echo esc_html($seo['keyword']); ?></span>
                                            <?php else : ?>
                                                <span class="rbfw-seo-ico" title="<?php esc_attr_e('No focus keyword set — the plugin cannot score this item.', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('No focus keyword', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7.5" cy="15.5" r="4.5"/><path d="M10.7 12.3 21 2"/><path d="m17 6 3 3"/></svg>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (! $seo['has_desc']) : ?>
                                                <span class="rbfw-seo-ico" title="<?php esc_attr_e('No meta description — search engines will invent one.', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('No meta description', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h5"/><path d="M8 17h8"/></svg>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($seo['noindex']) : ?>
                                                <span class="rbfw-seo-ico is-noindex" title="<?php esc_attr_e('Excluded from search engines (noindex).', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Excluded from search engines', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.9 4.2A10.9 10.9 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.2 3.2"/><path d="M6.6 6.6A18.4 18.4 0 0 0 2 12s3 8 10 8a10.9 10.9 0 0 0 5.4-1.4"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="m2 2 20 20"/></svg>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <?php endif; ?>
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
                                            <a class="rbfw-table-act dup" href="<?php echo esc_url($it['duplicate_link']); ?>" title="<?php esc_attr_e('Duplicate', 'booking-and-rental-manager-for-woocommerce'); ?>" aria-label="<?php esc_attr_e('Duplicate', 'booking-and-rental-manager-for-woocommerce'); ?>">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
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

    }

    new RBFW_Rental_List();

}
