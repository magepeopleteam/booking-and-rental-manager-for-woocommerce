<?php
if (!defined('ABSPATH')) {
    exit;
}  // if direct access
if (!class_exists('RBFW_Hidden_Product')) {
    class RBFW_Hidden_Product {
        public function __construct() {

            add_action('wp_insert_post', array($this, 'create_hidden_wc_product_on_publish'), 10, 3);
            add_action('save_post', array($this, 'run_link_product_on_save'), 99);
            // Self-healing: reconcile missing backing products (e.g. items imported in
            // Standalone mode that later switched to WooCommerce mode).
            add_action('admin_init', array($this, 'maybe_backfill_hidden_products'));
            add_action('admin_init', array($this, 'maybe_backfill_product_tax'));
            // Drop dangling links the moment a backing product is permanently deleted, so
            // the item is detected as needing repair instead of pointing at a gone id.
            add_action('before_delete_post', array($this, 'unlink_deleted_hidden_product'));
            add_action('update_option_rbfw_basic_payment_settings', array($this, 'flush_backfill_flag'));
            add_action('add_option_rbfw_basic_payment_settings', array($this, 'flush_backfill_flag'));
            add_action('parse_query', array($this, 'hide_wc_hidden_product_from_product_list'));
            add_action('wp', array($this, 'hide_hidden_wc_product_from_frontend'));
            //******************//
            add_action('wp_head', [$this, 'url_exclude_search_engine']);
            // add_action('init', [$this, 'get_all_hidden_product_id']);
            add_filter('wpseo_exclude_from_sitemap_by_post_ids', [$this, 'get_all_hidden_product_id']);
        }

        /**
         * Meta that binds an item to ITS OWN backing product and must never be copied
         * onto a duplicate. Cloning these made the copy share the source's product and
         * marked it as already-provisioned, so it could never mint one of its own.
         *
         * @var string[]
         */
        public static $own_product_meta = array( 'link_wc_product', 'check_if_run_once' );

        /**
         * Resolve an item's backing product id, or 0 when one must be (re)built.
         *
         * Validity is judged on reality, not on history: the post must still exist, be a
         * product, not be trashed, and not belong to a DIFFERENT rental item. That last
         * condition matters because duplicating an item used to copy `link_wc_product`
         * verbatim, leaving many items pointing at one product — so deleting that single
         * product broke every one of them at once, and until then a booking made on the
         * copy was recorded against the original item.
         *
         * @param int $item_id rbfw_item id.
         * @return int Product id, or 0 when the link is missing/dangling/shared.
         */
        public static function valid_hidden_product_id( $item_id ) {
            $pid = (int) get_post_meta( $item_id, 'link_wc_product', true );
            if ( $pid < 1 || 'product' !== get_post_type( $pid ) ) {
                return 0; // never linked, or the product has been deleted
            }
            if ( 'trash' === get_post_status( $pid ) ) {
                return 0;
            }
            $owner = (int) get_post_meta( $pid, 'link_rbfw_id', true );
            if ( $owner && $owner !== (int) $item_id ) {
                return 0; // shared with another item (duplicate fallout)
            }

            return $pid;
        }

        /**
         * Guarantee the item owns a publishable backing product and return its id.
         *
         * Safe to call on every save: it is a couple of meta reads when the link is
         * already healthy, and only writes when something is actually broken.
         *
         * @param int $item_id rbfw_item id.
         * @return int Product id, or 0 when not applicable (Standalone mode / not an item).
         */
        public function ensure_hidden_product( $item_id ) {
            if ( ! rbfw_has_woocommerce() || 'rbfw_item' !== get_post_type( $item_id ) ) {
                return 0;
            }

            $pid = self::valid_hidden_product_id( $item_id );
            if ( $pid ) {
                // Adopt products created before the back-reference meta existed, so the
                // ownership test above keeps recognising them on later runs.
                if ( ! get_post_meta( $pid, 'link_rbfw_id', true ) ) {
                    update_post_meta( $pid, 'link_rbfw_id', $item_id );
                }
                if ( 'publish' !== get_post_status( $pid ) ) {
                    wp_publish_post( $pid );
                }

                return $pid;
            }

            $this->create_hidden_wc_product( $item_id, get_the_title( $item_id ) );

            $pid = (int) get_post_meta( $item_id, 'link_wc_product', true );
            if ( ! $pid ) {
                return 0;
            }

            // Mirror the purchasable defaults the classic save path applies, so an item
            // healed outside that path is immediately bookable.
            update_post_meta( $pid, '_stock_status', 'instock' );
            update_post_meta( $pid, '_manage_stock', 'no' );
            $this->sync_tax_to_product( $item_id, $pid );
            if ( '' === get_post_meta( $pid, '_regular_price', true ) ) {
                update_post_meta( $pid, '_regular_price', 0.01 );
            }
            set_post_thumbnail( $pid, get_post_thumbnail_id( $item_id ) );

            return $pid;
        }

        /**
         * Drop dangling links when a backing product is permanently deleted.
         *
         * Without this the item keeps pointing at a gone product id, its Book Now button
         * submits add-to-cart for a post that no longer exists, and nothing marks the item
         * as needing repair. Trashed products need no handling here — valid_hidden_product_id()
         * already treats them as invalid, so an untrashed product simply becomes usable again.
         *
         * @param int $post_id Post being deleted.
         * @return void
         */
        public function unlink_deleted_hidden_product( $post_id ) {
            if ( 'product' !== get_post_type( $post_id ) ) {
                return;
            }
            global $wpdb;

            // Every item pointing here, not just the recorded owner: duplicates used to
            // inherit the link, so one product can back several items.
            $item_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'link_wc_product' AND meta_value = %s",
                    (string) $post_id
                )
            );
            foreach ( (array) $item_ids as $item_id ) {
                delete_post_meta( $item_id, 'link_wc_product' );
                delete_post_meta( $item_id, 'check_if_run_once' );
            }
        }

        /**
         * Clear the backfill throttle whenever the payment settings are saved.
         *
         * Switching Booking Mode from Standalone to WooCommerce is the case that strands
         * items without a backing product; re-running the reconcile on the next admin
         * request heals them automatically.
         */
        public function flush_backfill_flag() {
            delete_option('rbfw_hidden_product_backfill_done');
            delete_transient('rbfw_hidden_product_scan_lock');
        }

        /**
         * One-time (per signal) reconcile that ensures every published rental item has a
         * valid backing hidden WooCommerce product.
         *
         * Items created while the site was in Standalone mode (or imported as demo data)
         * never received a hidden product, so after switching to WooCommerce mode their
         * "Book Now" button submits add-to-cart with an id that has no purchasable product
         * behind it — the click silently does nothing. This repairs them.
         *
         * Guarded by an option so the full scan runs only once until the next settings save.
         */
        public function maybe_backfill_hidden_products() {
            // Only relevant in WooCommerce mode (Standalone has no backing products).
            if (!function_exists('rbfw_booking_mode') || rbfw_booking_mode() !== 'woocommerce') {
                return;
            }
            if (!current_user_can('manage_options')) {
                return;
            }
            /*
             * This used to be a once-ever run guarded by the rbfw_hidden_product_backfill_done
             * option, which meant any product deleted AFTER that run left its items broken
             * forever. Reconcile on a throttle instead: the detection query below is a single
             * indexed join that returns nothing on a healthy site, so it is cheap to repeat.
             */
            if (get_transient('rbfw_hidden_product_scan_lock')) {
                return;
            }
            set_transient('rbfw_hidden_product_scan_lock', 1, 10 * MINUTE_IN_SECONDS);

            $broken = $this->find_items_missing_hidden_product(self::REPAIR_BATCH);
            if (empty($broken)) {
                return;
            }
            foreach ($broken as $item_id) {
                $this->ensure_hidden_product($item_id);
            }
            /*
             * A full batch means there is probably more to do — release the throttle so the
             * next admin request continues instead of waiting out the window.
             */
            if (count($broken) >= self::REPAIR_BATCH) {
                delete_transient('rbfw_hidden_product_scan_lock');
            }
        }

        /**
         * One-time repair for items whose tax was configured before the mirror existed.
         *
         * Until this release the product's tax meta was only written from the classic
         * metabox's POST, so every item saved in the modern editor left its product at
         * "none" and WooCommerce charged no tax. Those items are already correct in their
         * own meta and would each need a manual re-save, so reconcile them once here.
         *
         * @return int Products corrected.
         */
        public function maybe_backfill_product_tax() {
            if (get_option('rbfw_product_tax_mirror_done')) {
                return 0;
            }
            if (!function_exists('rbfw_booking_mode') || rbfw_booking_mode() !== 'woocommerce') {
                return 0; // Standalone has no backing products; retry after a mode switch.
            }

            global $wpdb;
            // Only items that actually carry a tax status — an untouched item has nothing to mirror.
            $items = $wpdb->get_col(
                "SELECT p.ID
                   FROM {$wpdb->posts} p
                   INNER JOIN {$wpdb->postmeta} tax  ON tax.post_id = p.ID AND tax.meta_key = '_tax_status'
                   INNER JOIN {$wpdb->postmeta} link ON link.post_id = p.ID AND link.meta_key = 'link_wc_product'
                  WHERE p.post_type = 'rbfw_item'
                    AND tax.meta_value <> ''
                    AND link.meta_value <> ''"
            );

            $fixed = 0;
            foreach ((array) $items as $item_id) {
                $product_id = (int) get_post_meta((int) $item_id, 'link_wc_product', true);
                if (!$product_id || get_post_type($product_id) !== 'product') {
                    continue;
                }
                $this->sync_tax_to_product((int) $item_id, $product_id);
                $fixed++;
            }

            update_option('rbfw_product_tax_mirror_done', 1);

            return $fixed;
        }

        /** Items repaired per admin request, so a large backlog never stalls a page load. */
        const REPAIR_BATCH = 25;

        /**
         * Published rental items whose backing product is missing, deleted, trashed, or
         * owned by a different item.
         *
         * @param int $limit Maximum ids to return.
         * @return int[]
         */
        public function find_items_missing_hidden_product( $limit = 25 ) {
            global $wpdb;

            $sql = "
                SELECT p.ID
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} link
                       ON link.post_id = p.ID AND link.meta_key = 'link_wc_product'
                LEFT JOIN {$wpdb->posts} prod
                       ON prod.ID = link.meta_value
                      AND prod.post_type = 'product'
                      AND prod.post_status <> 'trash'
                LEFT JOIN {$wpdb->postmeta} owner
                       ON owner.post_id = prod.ID AND owner.meta_key = 'link_rbfw_id'
                WHERE p.post_type = 'rbfw_item'
                  AND p.post_status = 'publish'
                  AND (
                        link.meta_value IS NULL
                     OR link.meta_value = ''
                     OR prod.ID IS NULL
                     OR (owner.meta_value IS NOT NULL AND owner.meta_value <> '' AND owner.meta_value <> p.ID)
                  )
                LIMIT %d
            ";

            return array_map('intval', (array) $wpdb->get_col($wpdb->prepare($sql, (int) $limit)));
        }

        /**
         * Create backing hidden products for any published rbfw_item missing a valid one.
         *
         * @return int Number of products created/repaired.
         */
        public function backfill_missing_hidden_products() {
            $items = get_posts(array(
                'post_type'      => 'rbfw_item',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ));

            $repaired = 0;
            foreach ($items as $item_id) {
                if (self::valid_hidden_product_id($item_id)) {
                    continue;
                }
                // ensure_hidden_product() creates the product AND applies the purchasable
                // defaults, so the full-scan and the per-save paths cannot drift apart.
                if ($this->ensure_hidden_product($item_id)) {
                    $repaired++;
                }
            }

            return $repaired;
        }

        public function create_hidden_wc_product($post_id, $title) {
	        // The hidden product is a WooCommerce 'product' post; skip in Standalone mode.
	        if ( ! rbfw_has_woocommerce() ) {
		        return;
	        }
	        $new_post = array(
		        'post_title'    => $title,
		        'post_content'  => '',
		        'post_name'     => uniqid(),
		        'post_category' => array(),
		        'tags_input'    => array(),
		        'post_status'   => 'publish',
		        'post_type'     => 'product'
	        );
	        $pid      = wp_insert_post( $new_post );
	        update_post_meta( $post_id, 'link_wc_product', $pid );
	        update_post_meta( $pid, 'link_rbfw_id', $post_id );
	        update_post_meta( $pid, '_price', 0.01 );
	        update_post_meta( $pid, '_sold_individually', $this->sold_individually_meta_value() );
	        update_post_meta( $pid, '_virtual', 'no' );
	        $terms = array('exclude-from-catalog', 'exclude-from-search');
	        wp_set_object_terms($pid, $terms, 'product_visibility');
	        update_post_meta($post_id, 'check_if_run_once', true);
        }
        public function create_hidden_wc_product_on_publish($post_id, $post) {
	        // No backing WooCommerce product is needed in Standalone mode.
	        if ( ! rbfw_has_woocommerce() ) {
		        return;
	        }
	        if ( ! is_a( $post, 'WP_Post' ) || 'rbfw_item' !== $post->post_type || 'publish' !== $post->post_status ) {
		        return;
	        }
	        /*
	         * Previously gated on the one-shot `check_if_run_once` meta, i.e. "have I ever
	         * run for this item?" rather than "does this item still have a product?". Once
	         * that flag was set the item could never rebuild a backing product, so when the
	         * product was later deleted, re-saving the item silently did nothing — forever.
	         * Duplicates inherited the flag too, which is why duplicating a broken item was
	         * not a workaround either. ensure_hidden_product() asks the real question.
	         */
	        $this->ensure_hidden_product( $post_id );
        }
        public function count_hidden_wc_product($post_id): int {
	        $args = array(
		        'post_type'      => 'product',
		        'posts_per_page' => - 1,
		        'meta_query'     => array(
			        array(
				        'key'     => 'link_rbfw_id',
				        'value'   => $post_id,
				        'compare' => '='
			        )
		        )
	        );
	        $loop = new WP_Query( $args );

	        return $loop->post_count;
        }
        public function run_link_product_on_save($post_id) {
            // Linking a backing WooCommerce product only applies in WooCommerce mode.
            if ( ! rbfw_has_woocommerce() ) {
                return;
            }
            if (get_post_type($post_id) == 'rbfw_item') {

                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }
                if (!current_user_can('edit_post', $post_id)) {
                    return;
                }
                
                /*
                 * The backing-product link is repaired on EVERY save, not only on saves
                 * carrying the classic metabox nonce. Elementor, Quick Edit, the modern
                 * editor and programmatic saves all post without `rbfw_ticket_type_nonce`,
                 * so the old early-return here is why re-saving a broken item through
                 * Elementor appeared to do nothing at all.
                 */
                $product_id = $this->ensure_hidden_product($post_id);
                if (!$product_id) {
                    return; // Standalone mode, or the product could not be created
                }
                
                $event_name = get_the_title($post_id);
                set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
                update_post_meta($product_id, '_stock_status', 'instock');
                update_post_meta($product_id, '_manage_stock', 'no');
                update_post_meta($product_id, '_sold_individually', $this->sold_individually_meta_value());

                // Keep the hidden product's title in step with the item it backs. The slug
                // is left alone: it is set once at creation, and this path now runs on every
                // save, so re-rolling uniqid() here would churn it for no benefit.
                if ($event_name !== get_the_title($product_id)) {
                    wp_update_post(array(
                    'ID'         => $product_id,
                    'post_title' => $event_name,
                    ));
                }

                /*
                 * Tax comes from the ITEM's own meta, not from $_POST.
                 *
                 * This used to read the posted fields behind the classic ticket-type metabox
                 * nonce. The modern editor saves the very same _tax_status / _tax_class on the
                 * item but posts its own nonce, so this block never ran: the item said
                 * "taxable" while its backing product stayed at the creation default "none",
                 * and WooCommerce — which only ever looks at the product — charged no tax.
                 */
                $this->sync_tax_to_product($post_id, $product_id);
            }
}

        /**
         * Copy a rental item's tax configuration onto its backing WooCommerce product.
         *
         * The item is the single source of truth (both editors store _tax_status /
         * _tax_class on the rbfw_item post); the product is only the mirror WooCommerce
         * reads when it calculates cart and order tax.
         *
         * @param int $item_id    Rental item.
         * @param int $product_id Backing hidden product.
         * @return void
         */
        private function sync_tax_to_product($item_id, $product_id) {
            $item_id    = (int) $item_id;
            $product_id = (int) $product_id;
            if ( ! $item_id || ! $product_id ) {
                return;
            }

            $tax = $this->resolve_item_tax($item_id);

            update_post_meta($product_id, '_tax_status', $tax['status']);
            update_post_meta($product_id, '_tax_class', $tax['class']);

            // WooCommerce caches product objects; without this the cart can keep pricing
            // against the previous tax status for the rest of the request and beyond.
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients($product_id);
            }
        }

        /**
         * The tax status/class WooCommerce should apply to an item, normalized.
         *
         * @param int $item_id Rental item.
         * @return array{status:string,class:string}
         */
        private function resolve_item_tax($item_id) {
            $status = (string) get_post_meta($item_id, '_tax_status', true);
            $class  = (string) get_post_meta($item_id, '_tax_class', true);

            /*
             * The modern editor keeps the tax fields behind an "Enable tax settings" toggle.
             * A collapsed section still posts its selects, so an item switched back to off
             * would otherwise stay taxable on the strength of leftover values.
             */
            if ('no' === get_post_meta($item_id, 'rbfw_enable_tax_settings', true)) {
                return array('status' => 'none', 'class' => '');
            }

            // Unset, or the "Select Tax Status" placeholder option.
            if ( ! in_array($status, array('taxable', 'shipping', 'none'), true)) {
                $status = 'none';
            }

            if ('none' === $status) {
                return array('status' => 'none', 'class' => '');
            }

            /*
             * WooCommerce's Standard class IS the empty string — its own product screen posts
             * value="" for it. The rental tax tab offers value="standard" instead, and storing
             * that verbatim made WC_Tax look up a class slug that no rate row carries, so a
             * "taxable / Standard" rental still came out with zero tax.
             */
            if ('standard' === $class) {
                $class = '';
            }
            if ('' !== $class && class_exists('WC_Tax') && ! in_array($class, WC_Tax::get_tax_class_slugs(), true)) {
                $class = '';
            }

            return array('status' => $status, 'class' => $class);
        }

        /**
         * Return the WooCommerce meta value matching the duplicate-cart setting.
         *
         * @return string "no" when duplicate rental bookings are enabled, otherwise "yes".
         */
        private function sold_individually_meta_value() {
            return rbfw_allow_duplicate_rental_cart_items() ? 'no' : 'yes';
        }

        public function hide_wc_hidden_product_from_product_list($query) {
	        global $pagenow;
	        $q_vars = &$query->query_vars;
	        if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == 'product' ) {
		        $hidden_clause = array(
			        'taxonomy' => 'product_visibility',
			        'field'    => 'slug',
			        'terms'    => 'exclude-from-catalog',
			        'operator' => 'NOT IN',
		        );

		        /*
		         * Merge, never replace. This used to overwrite `tax_query` wholesale,
		         * dropping any clause already on the products list table — WooCommerce's
		         * "Filter by shipping class" among them. Nested so an existing
		         * 'relation' => 'OR' group keeps its own meaning.
		         */
		        $existing = $query->get( 'tax_query' );

		        if ( is_array( $existing ) && ! empty( $existing ) ) {
			        $tax_query = array(
				        'relation' => 'AND',
				        $existing,
				        array( $hidden_clause ),
			        );
		        } else {
			        $tax_query = array( $hidden_clause );
		        }

		        $query->set( 'tax_query', $tax_query );
	        }
        }
        public function hide_hidden_wc_product_from_frontend() {
            global $post, $wp_query;

            if(rbfw_woo_install_check() == 'Yes' ){
                if (is_product()) {
                    $post_id = $post->ID;
                    $visibility = get_the_terms($post_id, 'product_visibility');
                    if (is_object($visibility)) {
                        if ($visibility[0]->name == 'exclude-from-catalog') {
                            $check_event_hidden = get_post_meta( $post_id, 'link_rbfw_id', true ) ? get_post_meta( $post_id, 'link_rbfw_id', true ) : 0;
                            if ($check_event_hidden > 0) {
                                $wp_query->set_404();
                                status_header(404);
                                get_template_part(404);
                                exit();
                            }
                        }
                    }
                }
            }
        }

        //**************Google search url hidden*********************//
        public function url_exclude_search_engine() {
            global $post;
            if (is_single() && is_product()) {
                $post_id = $post->ID;
                $visibility = get_the_terms($post_id, 'product_visibility') ? get_the_terms($post_id, 'product_visibility') : [0];
                if (is_object($visibility[0]) && $visibility[0]->name == 'exclude-from-catalog') {
                    $check_hidden = get_post_meta( $post_id, 'link_rbfw_id', true ) ? get_post_meta( $post_id, 'link_rbfw_id', true ) : 0;
                    if ($check_hidden > 0) {
                        ?>
                        <meta name="robots" content="noindex, nofollow">
                        <?php
                    }
                }
            }
        }
        public function get_all_hidden_product_id() {
            $product_id = [];
            $query = self::query_post_type('rbfw_item');
            foreach ($query->posts as $result) {
                $post_id = $result->ID;
                $product_id[] = get_post_meta( $post_id, 'link_wc_product', true );
            }
            return array_filter($product_id);
        }
	    public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
		    $args = array(
			    'post_type' => $post_type,
			    'posts_per_page' => $show,
			    'paged' => $page,
			    'post_status' => 'publish'
		    );
		    return new WP_Query($args);
	    }
    }
}
new RBFW_Hidden_Product();
