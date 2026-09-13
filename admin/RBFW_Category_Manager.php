<?php
/**
 * Categories admin page — replaces the native edit-tags.php taxonomy screen
 * for `rbfw_item_caregory` with a full dashboard: header stats, search +
 * filter + sort toolbar, a grid/list card view, and a single Add/Edit modal
 * that saves over AJAX (no page reload). The underlying operations are
 * exactly what the native screen already did
 * (wp_insert_term()/wp_update_term()/wp_delete_term(), the same
 * `manage_categories` capability) — just reached through this screen and
 * two small AJAX actions instead of a full-page form post.
 *
 * Entirely self-contained in this plugin, including the category image
 * field (term meta `rentiva_category_image_id`) — this page works whether
 * or not any particular theme is even active. The meta key keeps its
 * historical `rentiva_` prefix only because the Rentiva theme's own
 * front-end templates (template-parts/home/categories.php and friends)
 * already read it by that exact name; renaming it would require touching
 * theme code for no functional benefit. `rbfw_category_manager_*` hooks
 * still fire around the image field so a theme/plugin can extend the modal
 * further, but nothing here requires anyone to hook in.
 */
if ( ! defined( 'ABSPATH' ) ) die;

if ( ! class_exists( 'RBFW_Category_Manager' ) ) {

	class RBFW_Category_Manager {

		const TAXONOMY       = 'rbfw_item_caregory';
		const PAGE_SLUG       = 'rbfw_category_manager';
		const NONCE           = 'rbfw_category_manager';
		const IMAGE_META_KEY  = 'rentiva_category_image_id';
		const PER_PAGE        = 15;

		/** @var string Set once add_submenu_page() returns it, so enqueue_assets() can target only this page. */
		private static $hook = '';

		public function __construct() {
			add_action( 'admin_menu', [ $this, 'register_menu' ], 20 );
			add_action( 'admin_init', [ $this, 'redirect_legacy_screens' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
			add_action( 'wp_ajax_rbfw_category_manager_save', [ $this, 'ajax_save' ] );
			add_action( 'wp_ajax_rbfw_category_manager_delete', [ $this, 'ajax_delete' ] );
		}

		/**
		 * The native "Rent Item Type" taxonomy submenu (auto-added by
		 * register_taxonomy()'s show_in_menu) is the one visible entry now —
		 * this page is registered as before (so its capability check, hook
		 * suffix, and the edit-tags.php/term.php redirects in
		 * redirect_legacy_screens() all keep working exactly the same), but
		 * immediately hidden from the sidebar so it doesn't show up as a
		 * second, redundant "Categories" row next to it.
		 *
		 * (An earlier version tried to do the reverse — hide the native
		 * submenu and keep this one — via remove_submenu_page() with a plain
		 * '&' in the slug. That never actually worked: WP core's own taxonomy
		 * menu registration in wp-admin/menu.php builds that slug with a
		 * literal '&amp;' — e.g. "edit-tags.php?taxonomy=%s&amp;post_type=$post_type"
		 * — not '&', so the removal silently failed to match and both menu
		 * items showed up. Not worth reproducing that string just to remove
		 * it again now that the desired outcome has flipped.)
		 */
		public function register_menu(): void {
			self::$hook = add_submenu_page(
				'edit.php?post_type=rbfw_item',
				__( 'Categories', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Categories', 'booking-and-rental-manager-for-woocommerce' ),
				'manage_categories',
				self::PAGE_SLUG,
				[ $this, 'render_page' ]
			);

			// Hide the row, not the page: remove_submenu_page() only strips the
			// nav link, so this screen stays fully reachable at its own URL —
			// which is exactly what "Rent Item Type" (and any legacy
			// edit-tags.php/term.php link, via redirect_legacy_screens() below)
			// still redirects to.
			remove_submenu_page( 'edit.php?post_type=rbfw_item', self::PAGE_SLUG );
		}

		/**
		 * Old edit-tags.php/term.php links for this taxonomy (bookmarks,
		 * anything elsewhere that still points at them) land on this page
		 * instead. A term.php edit link carries its term through so the page
		 * can open straight into that term's edit modal.
		 */
		public function redirect_legacy_screens(): void {
			global $pagenow;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect, no state change.
			if ( ! isset( $_GET['taxonomy'] ) || self::TAXONOMY !== $_GET['taxonomy'] ) {
				return;
			}

			$target = admin_url( 'edit.php?post_type=rbfw_item&page=' . self::PAGE_SLUG );

			if ( 'edit-tags.php' === $pagenow ) {
				wp_safe_redirect( esc_url_raw( $target ) );
				exit;
			}

			if ( 'term.php' === $pagenow && isset( $_GET['tag_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$term_id = absint( wp_unslash( $_GET['tag_ID'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_safe_redirect( esc_url_raw( add_query_arg( 'edit', $term_id, $target ) ) );
				exit;
			}
		}

		public function enqueue_assets( $hook ): void {
			if ( $hook !== self::$hook ) {
				return;
			}

			wp_enqueue_media();

			$css_ver = filemtime( RBFW_PLUGIN_DIR . '/admin/css/rbfw-category-manager.css' );
			$js_ver  = filemtime( RBFW_PLUGIN_DIR . '/admin/js/rbfw-category-manager.js' );

			wp_enqueue_style( 'rbfw-category-manager', RBFW_PLUGIN_URL . '/admin/css/rbfw-category-manager.css', [], $css_ver ?: '1.0.0' );
			wp_enqueue_script( 'rbfw-category-manager', RBFW_PLUGIN_URL . '/admin/js/rbfw-category-manager.js', [ 'jquery' ], $js_ver ?: '1.0.0', true );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, just pre-opens a modal.
			$open_edit_id = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0;

			wp_localize_script( 'rbfw-category-manager', 'rbfwCategoryManager', [
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( self::NONCE ),
				'openEditId'       => $open_edit_id,
				'perPage'          => self::PER_PAGE,
				'selectImageTitle' => __( 'Select an image', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nAddTitle'     => __( 'Add New Category', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nEditTitle'    => __( 'Edit Category', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nConfirm'      => __( 'Delete this category? Items already in it are not deleted.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nSaving'       => __( 'Saving…', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nSave'         => __( 'Save Category', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nError'        => __( 'Something went wrong. Please try again.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nNameRequired' => __( 'Name is required.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nEmpty'        => __( 'No categories yet. Add your first one above.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18nNoMatch'      => __( 'No categories match your search.', 'booking-and-rental-manager-for-woocommerce' ),
				/* translators: 1: first item number, 2: last item number, 3: total number of categories. */
				'i18nShowing'      => __( 'Showing %1$s to %2$s of %3$s categories', 'booking-and-rental-manager-for-woocommerce' ),
			] );
		}

		/**
		 * @return WP_Term[]
		 */
		public static function get_terms(): array {
			$terms = get_terms( [
				'taxonomy'   => self::TAXONOMY,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			] );
			return is_wp_error( $terms ) ? [] : $terms;
		}

		/**
		 * Header-strip stats — every value is derived straight from the same
		 * get_terms() call the grid itself uses, no extra queries.
		 *
		 * @param WP_Term[] $terms
		 * @return array{total:int,liveRentals:int,empty:int,topPerformer:string}
		 */
		private static function compute_stats( array $terms ): array {
			$live_rentals = 0;
			$empty_count  = 0;
			$top_term     = null;

			foreach ( $terms as $term ) {
				$count = (int) $term->count;
				$live_rentals += $count;
				if ( 0 === $count ) {
					++$empty_count;
				}
				if ( ! $top_term || $count > (int) $top_term->count ) {
					$top_term = $term;
				}
			}

			return [
				'total'        => count( $terms ),
				'liveRentals'  => $live_rentals,
				'empty'        => $empty_count,
				'topPerformer' => $top_term ? $top_term->name : __( '—', 'booking-and-rental-manager-for-woocommerce' ),
			];
		}

		public function render_page(): void {
			if ( ! current_user_can( 'manage_categories' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to manage categories for this site.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$terms      = self::get_terms();
			$stats      = self::compute_stats( $terms );
			$with_items = count( array_filter( $terms, static fn( $t ) => (int) $t->count > 0 ) );
			?>
			<div class="wrap rbfw-cat-manager">
				<div class="rbfw-cat-manager__header">
					<div>
						<h1>
							<?php esc_html_e( 'Categories', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<span class="rbfw-cat-manager__total-pill">
								<?php
								printf(
									/* translators: %d: total number of categories. */
									esc_html__( '%d Total', 'booking-and-rental-manager-for-woocommerce' ),
									(int) $stats['total']
								);
								?>
							</span>
						</h1>
						<p class="rbfw-cat-manager__subtitle"><?php esc_html_e( 'Organize the categories rental items are grouped into on the front end. Control visibility, hero photography, and catalogue mappings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<button type="button" class="button button-primary rbfw-cat-manager__add-btn" id="rbfw-cat-add-btn">
						<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
						<?php esc_html_e( 'Add New Category', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
				</div>

				<div class="rbfw-cat-stats">
					<div class="rbfw-cat-stat">
						<span class="rbfw-cat-stat__icon rbfw-cat-stat__icon--green"><span class="dashicons dashicons-grid-view"></span></span>
						<span class="rbfw-cat-stat__body">
							<span class="rbfw-cat-stat__label"><?php esc_html_e( 'Categories', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-cat-stat__value">
								<?php
								printf(
									/* translators: %d: number of active categories. */
									esc_html__( '%d Active', 'booking-and-rental-manager-for-woocommerce' ),
									(int) $stats['total']
								);
								?>
							</span>
						</span>
					</div>
					<div class="rbfw-cat-stat">
						<span class="rbfw-cat-stat__icon rbfw-cat-stat__icon--blue"><span class="dashicons dashicons-archive"></span></span>
						<span class="rbfw-cat-stat__body">
							<span class="rbfw-cat-stat__label"><?php esc_html_e( 'Live Rentals', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-cat-stat__value">
								<?php
								printf(
									/* translators: %d: total items across all categories. */
									esc_html__( '%d Units', 'booking-and-rental-manager-for-woocommerce' ),
									(int) $stats['liveRentals']
								);
								?>
							</span>
						</span>
					</div>
					<div class="rbfw-cat-stat">
						<span class="rbfw-cat-stat__icon rbfw-cat-stat__icon--amber"><span class="dashicons dashicons-warning"></span></span>
						<span class="rbfw-cat-stat__body">
							<span class="rbfw-cat-stat__label"><?php esc_html_e( 'Draft / Empty', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-cat-stat__value">
								<?php
								printf(
									/* translators: %d: number of categories with no items. */
									esc_html__( '%d Unassigned', 'booking-and-rental-manager-for-woocommerce' ),
									(int) $stats['empty']
								);
								?>
							</span>
						</span>
					</div>
					<div class="rbfw-cat-stat">
						<span class="rbfw-cat-stat__icon rbfw-cat-stat__icon--purple"><span class="dashicons dashicons-chart-line"></span></span>
						<span class="rbfw-cat-stat__body">
							<span class="rbfw-cat-stat__label"><?php esc_html_e( 'Top Performer', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-cat-stat__value"><?php echo esc_html( $stats['topPerformer'] ); ?></span>
						</span>
					</div>
				</div>

				<div class="rbfw-cat-manager__toolbar">
					<div class="rbfw-cat-manager__search">
						<span class="dashicons dashicons-search" aria-hidden="true"></span>
						<input type="search" id="rbfw-cat-search" placeholder="<?php esc_attr_e( 'Search categories…', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					</div>

					<div class="rbfw-cat-manager__toolbar-right">
						<div class="rbfw-cat-filter-pills" role="group" aria-label="<?php esc_attr_e( 'Filter categories', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<button type="button" class="rbfw-cat-filter-pill is-active" data-filter="all">
								<?php
								printf(
									/* translators: %d: total number of categories. */
									esc_html__( 'All (%d)', 'booking-and-rental-manager-for-woocommerce' ),
									count( $terms )
								);
								?>
							</button>
							<button type="button" class="rbfw-cat-filter-pill" data-filter="with-items">
								<?php
								printf(
									/* translators: %d: number of categories with at least one item. */
									esc_html__( 'With Items (%d)', 'booking-and-rental-manager-for-woocommerce' ),
									$with_items
								);
								?>
							</button>
							<button type="button" class="rbfw-cat-filter-pill" data-filter="empty">
								<?php
								printf(
									/* translators: %d: number of categories with no items. */
									esc_html__( 'Empty (%d)', 'booking-and-rental-manager-for-woocommerce' ),
									(int) $stats['empty']
								);
								?>
							</button>
						</div>

						<label class="rbfw-cat-sort">
							<span class="screen-reader-text"><?php esc_html_e( 'Sort by', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-cat-sort__prefix"><?php esc_html_e( 'Sort:', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<select id="rbfw-cat-sort">
								<option value="name-asc"><?php esc_html_e( 'Alphabetical (A-Z)', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="name-desc"><?php esc_html_e( 'Alphabetical (Z-A)', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="count-desc"><?php esc_html_e( 'Most Items', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="count-asc"><?php esc_html_e( 'Fewest Items', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
							</select>
						</label>

						<div class="rbfw-cat-view-toggle" role="group" aria-label="<?php esc_attr_e( 'Layout', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<button type="button" class="rbfw-cat-view-toggle__btn is-active" data-view="grid" aria-pressed="true">
								<span class="dashicons dashicons-grid-view"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Grid view', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</button>
							<button type="button" class="rbfw-cat-view-toggle__btn" data-view="list" aria-pressed="false">
								<span class="dashicons dashicons-list-view"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'List view', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div class="rbfw-cat-grid" id="rbfw-cat-grid">
					<?php if ( empty( $terms ) ) : ?>
						<p class="rbfw-cat-grid__empty"><?php esc_html_e( 'No categories yet. Add your first one above.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					<?php else : ?>
						<?php foreach ( $terms as $term ) : ?>
							<?php echo self::render_card_html( $term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped inside the method. ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="rbfw-cat-manager__footer">
					<span class="rbfw-cat-manager__showing" id="rbfw-cat-showing"></span>
					<div class="rbfw-cat-pagination" id="rbfw-cat-pagination"></div>
				</div>

				<?php self::render_modal( $terms ); ?>
			</div>
			<?php
		}

		/**
		 * One category card — used both for the initial page render and (via
		 * output buffering) the AJAX save response, so the two never drift.
		 */
		public static function render_card_html( WP_Term $term ): string {
			$count       = (int) $term->count;
			$image_id    = (int) get_term_meta( $term->term_id, self::IMAGE_META_KEY, true );
			$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
			$archive_url = get_term_link( $term );
			$archive_url = is_wp_error( $archive_url ) ? '' : $archive_url;

			$payload = [
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'parent'      => (int) $term->parent,
				'description' => $term->description,
				'imageId'     => $image_id,
				'imageUrl'    => $image_url,
			];

			/**
			 * The JSON payload embedded in each card, read by JS to pre-fill the
			 * Edit modal. Add your own keys here to have them available client-side.
			 *
			 * @param array   $payload
			 * @param WP_Term $term
			 */
			$payload = apply_filters( 'rbfw_category_manager_term_payload', $payload, $term );

			ob_start();
			?>
			<div
				class="rbfw-cat-card"
				id="rbfw-cat-card-<?php echo (int) $term->term_id; ?>"
				data-term-id="<?php echo (int) $term->term_id; ?>"
				data-term-name="<?php echo esc_attr( strtolower( $term->name ) ); ?>"
				data-count="<?php echo $count; ?>"
				data-has-items="<?php echo $count > 0 ? '1' : '0'; ?>"
				data-term='<?php echo esc_attr( wp_json_encode( $payload ) ); ?>'
			>
				<div class="rbfw-cat-card__media">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="">
					<?php else : ?>
						<span class="rbfw-cat-card__media-placeholder" aria-hidden="true">
							<span class="dashicons dashicons-format-image"></span>
						</span>
					<?php endif; ?>

					<?php if ( $count > 0 ) : ?>
						<span class="rbfw-cat-card__count-pill">
							<?php
							printf(
								/* translators: %d: number of items in this category. */
								esc_html( _n( '%d item', '%d items', $count, 'booking-and-rental-manager-for-woocommerce' ) ),
								$count
							);
							?>
						</span>
					<?php endif; ?>

					<?php
					/*
					 * Edit/Delete used to live in their own full-width footer strip
					 * below the title/description — a whole extra row per card just
					 * for two icon buttons. Moved into the media corner instead,
					 * grouped with the existing "More actions" (⋮) button so all of
					 * a card's actions sit in one compact top-right cluster.
					 */
					?>
					<div class="rbfw-cat-card__overlay-actions">
						<button type="button" class="rbfw-cat-card__edit" data-action="edit" title="<?php esc_attr_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Edit category', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="M12 20h9"></path>
								<path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
							</svg>
						</button>
						<button type="button" class="rbfw-cat-card__delete" data-action="delete" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Delete category', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="M3 6h18"></path>
								<path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
								<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
								<path d="M10 11v6"></path>
								<path d="M14 11v6"></path>
							</svg>
						</button>
						<?php if ( $archive_url ) : ?>
							<div class="rbfw-cat-card__menu">
								<button type="button" class="rbfw-cat-card__menu-btn" data-action="menu" aria-label="<?php esc_attr_e( 'More actions', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-expanded="false">
									<span class="dashicons dashicons-ellipsis"></span>
								</button>
								<div class="rbfw-cat-card__menu-panel" hidden>
									<a href="<?php echo esc_url( $archive_url ); ?>" target="_blank" rel="noopener noreferrer">
										<span class="dashicons dashicons-external"></span> <?php esc_html_e( 'View on Front End', 'booking-and-rental-manager-for-woocommerce' ); ?>
									</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="rbfw-cat-card__body">
					<h3 class="rbfw-cat-card__title"><?php echo esc_html( $term->name ); ?></h3>
					<?php if ( $term->description ) : ?>
						<p class="rbfw-cat-card__description"><?php echo esc_html( $term->description ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * The single Add/Edit modal, reused for both by JS (title + hidden
		 * term_id swap, fields pre-filled from a card's data-term payload).
		 *
		 * @param WP_Term[] $terms For the Parent <select>.
		 */
		private static function render_modal( array $terms ): void {
			?>
			<div class="rbfw-cat-modal" id="rbfw-cat-modal" hidden>
				<div class="rbfw-cat-modal__backdrop" id="rbfw-cat-modal-backdrop"></div>
				<div class="rbfw-cat-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rbfw-cat-modal-title">
					<div class="rbfw-cat-modal__header">
						<h2 id="rbfw-cat-modal-title"><?php esc_html_e( 'Add New Category', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<button type="button" class="rbfw-cat-modal__close" id="rbfw-cat-modal-close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
						</button>
					</div>

					<form id="rbfw-cat-form">
						<div class="rbfw-cat-modal__body">
							<div class="rbfw-cat-modal__error" id="rbfw-cat-modal-error" hidden></div>

							<input type="hidden" name="term_id" id="rbfw-cat-term-id" value="0">

							<div class="rbfw-cat-field">
								<label for="rbfw-cat-name"><?php esc_html_e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="rbfw-cat-field__required">*</span></label>
								<input type="text" id="rbfw-cat-name" name="name" required maxlength="200">
							</div>

							<div class="rbfw-cat-field">
								<label for="rbfw-cat-slug"><?php esc_html_e( 'Slug', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" id="rbfw-cat-slug" name="slug" placeholder="<?php esc_attr_e( 'Auto-generated from name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							</div>

							<div class="rbfw-cat-field">
								<label for="rbfw-cat-description"><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<textarea id="rbfw-cat-description" name="description" rows="3"></textarea>
							</div>

							<div class="rbfw-cat-field">
								<label><?php esc_html_e( 'Category Image', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<div class="rbfw-cat-image-field">
									<div class="rbfw-cat-image-field__preview" id="rbfw-cat-image-preview">
										<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
									</div>
									<div class="rbfw-cat-image-field__actions">
										<input type="hidden" id="rbfw-cat-image-id" name="image_id" value="">
										<button type="button" class="rbfw-cat-image-field__btn rbfw-cat-image-field__btn--select" id="rbfw-cat-image-select">
											<span class="dashicons dashicons-upload" aria-hidden="true"></span>
											<?php esc_html_e( 'Select image', 'booking-and-rental-manager-for-woocommerce' ); ?>
										</button>
										<button type="button" class="rbfw-cat-image-field__btn rbfw-cat-image-field__btn--remove" id="rbfw-cat-image-remove" style="display:none">
											<span class="dashicons dashicons-trash" aria-hidden="true"></span>
											<?php esc_html_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>
										</button>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Shown on the front-end category grid and its hero tile.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>

							<?php
							/**
							 * Extra modal fields — an extension point for a theme/plugin
							 * that wants to add its own field to this form. The core
							 * "Category image" field above no longer depends on this.
							 */
							do_action( 'rbfw_category_manager_modal_fields' );
							?>
						</div>

						<div class="rbfw-cat-modal__footer">
							<button type="button" class="button rbfw-cat-modal__cancel" id="rbfw-cat-modal-cancel"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<button type="submit" class="button button-primary rbfw-cat-modal__submit" id="rbfw-cat-modal-submit"><?php esc_html_e( 'Save Category', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</form>
				</div>
			</div>
			<?php
		}

		/** AJAX: create or update a category (term_id present + non-zero = update). */
		public function ajax_save(): void {
			check_ajax_referer( self::NONCE, 'nonce' );

			if ( ! current_user_can( 'manage_categories' ) ) {
				wp_send_json_error( [ 'message' => __( 'You are not allowed to manage categories.', 'booking-and-rental-manager-for-woocommerce' ) ], 403 );
			}

			$term_id  = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
			$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$slug     = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
			// The modal no longer exposes a Parent field. Kept accepted (not required)
			// so an existing sub-category isn't silently flattened to top-level just by
			// being edited here — omitted from $_POST, its current parent is left alone.
			$parent   = isset( $_POST['parent'] ) ? absint( wp_unslash( $_POST['parent'] ) ) : null;
			$desc     = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
			$image_id = isset( $_POST['image_id'] ) ? absint( wp_unslash( $_POST['image_id'] ) ) : 0;

			if ( '' === $name ) {
				wp_send_json_error( [ 'message' => __( 'Name is required.', 'booking-and-rental-manager-for-woocommerce' ) ] );
			}

			// A category can't be its own parent (wp_update_term() already rejects
			// making it a descendant of itself, but not this direct self-parent case).
			if ( $term_id && null !== $parent && $parent === $term_id ) {
				wp_send_json_error( [ 'message' => __( 'A category cannot be its own parent.', 'booking-and-rental-manager-for-woocommerce' ) ] );
			}

			$args = [
				'description' => $desc,
			];
			if ( null !== $parent ) {
				$args['parent'] = $parent;
			} elseif ( ! $term_id ) {
				$args['parent'] = 0; // New category, no parent field sent: top-level.
			}
			if ( '' !== $slug ) {
				$args['slug'] = $slug;
			}

			if ( $term_id ) {
				$existing = get_term( $term_id, self::TAXONOMY );
				if ( ! $existing || is_wp_error( $existing ) ) {
					wp_send_json_error( [ 'message' => __( 'That category no longer exists.', 'booking-and-rental-manager-for-woocommerce' ) ] );
				}
				$result = wp_update_term( $term_id, self::TAXONOMY, array_merge( [ 'name' => $name ], $args ) );
			} else {
				$result = wp_insert_term( $name, self::TAXONOMY, $args );
			}

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			}

			$saved_term_id = (int) $result['term_id'];
			$is_new        = 0 === $term_id;

			if ( $image_id ) {
				update_term_meta( $saved_term_id, self::IMAGE_META_KEY, $image_id );
			} else {
				delete_term_meta( $saved_term_id, self::IMAGE_META_KEY );
			}

			/**
			 * Fires right after a category is created or updated — for a
			 * theme/plugin that hooked `rbfw_category_manager_modal_fields` to
			 * add its own field(s), the place to save them from $_POST.
			 *
			 * @param int  $saved_term_id
			 * @param bool $is_new
			 */
			do_action( 'rbfw_category_manager_term_saved', $saved_term_id, $is_new );

			$term = get_term( $saved_term_id, self::TAXONOMY );
			if ( ! $term || is_wp_error( $term ) ) {
				wp_send_json_error( [ 'message' => __( 'Category was saved, but could not be reloaded.', 'booking-and-rental-manager-for-woocommerce' ) ] );
			}

			wp_send_json_success( [
				'termId'   => $saved_term_id,
				'isNew'    => $is_new,
				'cardHtml' => self::render_card_html( $term ),
			] );
		}

		/** AJAX: delete a category. */
		public function ajax_delete(): void {
			check_ajax_referer( self::NONCE, 'nonce' );

			if ( ! current_user_can( 'manage_categories' ) ) {
				wp_send_json_error( [ 'message' => __( 'You are not allowed to manage categories.', 'booking-and-rental-manager-for-woocommerce' ) ], 403 );
			}

			$term_id = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
			if ( ! $term_id ) {
				wp_send_json_error( [ 'message' => __( 'Missing category.', 'booking-and-rental-manager-for-woocommerce' ) ] );
			}

			$result = wp_delete_term( $term_id, self::TAXONOMY );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			}
			if ( false === $result ) {
				wp_send_json_error( [ 'message' => __( 'That category no longer exists.', 'booking-and-rental-manager-for-woocommerce' ) ] );
			}

			wp_send_json_success( [ 'termId' => $term_id ] );
		}
	}

	new RBFW_Category_Manager();
}
