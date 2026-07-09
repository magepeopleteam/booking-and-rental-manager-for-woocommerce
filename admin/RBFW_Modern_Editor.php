<?php
/**
 * Modern rental-item editor page.
 * Mirrors the MEP-Events pattern: intercepts classic edit screens and
 * redirects to a full-page tab UI.  Classic editor is preserved via the
 * "Switch to Classic" link and the _rbfw_editor_mode post-meta flag.
 */
if ( ! defined( 'ABSPATH' ) ) die;

if ( ! class_exists( 'RBFW_Modern_Editor' ) ) {

	class RBFW_Modern_Editor {

		const POST_TYPE      = 'rbfw_item';
		const PAGE_SLUG      = 'rbfw_modern_editor';
		const NONCE_SAVE     = 'rbfw_modern_editor_save';
		const NONCE_SWITCH   = 'rbfw_switch_rental_editor';
		const META_MODE      = '_rbfw_editor_mode';
		const CLASSIC_PARAM  = 'rbfw_classic';

		public function __construct() {
			add_action( 'admin_menu',             [ $this, 'register_menu' ], 80 );
			add_action( 'admin_menu',             [ $this, 'fix_add_new_submenu_link' ], 999 );
			add_action( 'load-post.php',          [ $this, 'maybe_redirect_edit_screen' ] );
			add_action( 'load-post-new.php',      [ $this, 'maybe_redirect_new_screen' ] );
			add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
			add_action( 'admin_head',             [ $this, 'hide_menu_styles' ] );
			add_filter( 'admin_body_class',       [ $this, 'admin_body_class' ] );
			add_action( 'wp_ajax_rbfw_modern_editor_save',   [ $this, 'ajax_save' ] );
			add_action( 'wp_ajax_rbfw_modern_editor_create', [ $this, 'ajax_create_draft' ] );
			add_action( 'admin_post_rbfw_switch_rental_editor', [ $this, 'handle_editor_switch' ] );
			add_filter( 'get_edit_post_link',     [ $this, 'filter_edit_post_link' ], 20, 3 );
			add_filter( 'post_row_actions',       [ $this, 'filter_row_actions' ], 20, 2 );
			add_filter( 'parent_file',            [ $this, 'filter_parent_file' ] );
			add_filter( 'submenu_file',           [ $this, 'filter_submenu_file' ], 10, 2 );
			add_action( 'admin_footer',           [ $this, 'render_classic_switch_button' ] );
		}

		/* ── Menu ──────────────────────────────────────────────────────────── */

		public function register_menu(): void {
			add_submenu_page(
				'edit.php?post_type=' . self::POST_TYPE,
				__( 'Rental Item Editor', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Rental Item Editor', 'booking-and-rental-manager-for-woocommerce' ),
				'edit_posts',
				self::PAGE_SLUG,
				[ $this, 'render_page' ]
			);
		}

		/**
		 * Point the native "Add New" submenu item at the modern editor.
		 */
		public function fix_add_new_submenu_link(): void {
			if ( ! $this->is_modern_mode_enabled() ) {
				return;
			}

			global $submenu;
			$parent = 'edit.php?post_type=' . self::POST_TYPE;

			if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
				return;
			}

			foreach ( $submenu[ $parent ] as $index => $item ) {
				if ( empty( $item[2] ) || ! is_string( $item[2] ) ) {
					continue;
				}

				if ( str_contains( $item[2], 'post-new.php' ) && str_contains( $item[2], self::POST_TYPE ) ) {
					$submenu[ $parent ][ $index ][2] = 'edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG;
				}
			}
		}

		public function hide_menu_styles(): void { ?>
			<style>
				#adminmenu a[href="edit.php?post_type=<?php echo esc_attr( self::POST_TYPE ); ?>&page=<?php echo esc_attr( self::PAGE_SLUG ); ?>"] {
					display: none !important;
				}
			</style>
		<?php }

		/* ── Helpers ────────────────────────────────────────────────────────── */

		private function is_edit_screen(): bool {
			return is_admin()
				&& isset( $_GET['page'] )
				&& sanitize_key( wp_unslash( $_GET['page'] ) ) === self::PAGE_SLUG;
		}

		private function is_classic_bypass(): bool {
			return isset( $_GET[ self::CLASSIC_PARAM ] ) && '1' === (string) wp_unslash( $_GET[ self::CLASSIC_PARAM ] );
		}

		private function get_default_edit_mode(): string {
			$mode = apply_filters( 'rbfw_default_editor_mode', 'modern' );
			return in_array( $mode, [ 'modern', 'classic' ], true ) ? $mode : 'modern';
		}

		private function get_edit_mode( int $post_id = 0 ): string {
			if ( $post_id > 0 ) {
				$mode = get_post_meta( $post_id, self::META_MODE, true );
				if ( in_array( $mode, [ 'modern', 'classic' ], true ) ) {
					return $mode;
				}
			}

			return $this->get_default_edit_mode();
		}

		private function is_modern_mode_enabled( int $post_id = 0 ): bool {
			return $this->get_edit_mode( $post_id ) === 'modern';
		}

		private function set_edit_mode( int $post_id, string $mode ): void {
			if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			if ( ! in_array( $mode, [ 'modern', 'classic' ], true ) ) {
				return;
			}

			update_post_meta( $post_id, self::META_MODE, $mode );
		}

		public static function add_new_url( string $tab = 'general' ): string {
			$tab = sanitize_key( $tab ) ?: 'general';

			return admin_url(
				'edit.php?post_type=' . self::POST_TYPE
				. '&page=' . self::PAGE_SLUG
				. '#/rental/new/' . $tab
			);
		}

		private function create_draft_item(): int {
			// "auto-draft" (not "draft") so an item the user opens but never saves
			// stays out of the list and is auto-purged by WordPress, exactly like
			// the classic "Add New" screen. The first real save promotes it to
			// draft/publish via ajax_save().
			$post_id = wp_insert_post(
				[
					'post_type'   => self::POST_TYPE,
					'post_status' => 'auto-draft',
					'post_title'  => __( 'New Rental Item', 'booking-and-rental-manager-for-woocommerce' ),
				],
				true
			);

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				return 0;
			}

			$this->set_edit_mode( (int) $post_id, 'modern' );

			return (int) $post_id;
		}

		private function edit_url( int $post_id = 0, string $tab = 'general' ): string {
			$base = admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG );
			if ( $post_id > 0 ) {
				return $base . '&item_id=' . $post_id . '#/rental/edit/' . $post_id . '/' . sanitize_key( $tab );
			}
			return $base . '#/rental/new/' . sanitize_key( $tab );
		}

		private function classic_url( int $post_id ): string {
			return admin_url( sprintf( 'post.php?post=%d&action=edit&%s=1', $post_id, self::CLASSIC_PARAM ) );
		}

		private function switch_url( string $mode, int $post_id = 0 ): string {
			$args = [
				'action'   => self::NONCE_SWITCH,
				'mode'     => $mode,
				'_wpnonce' => wp_create_nonce( self::NONCE_SWITCH ),
			];
			if ( $post_id > 0 ) {
				$args['post_id'] = $post_id;
			}
			return add_query_arg( $args, admin_url( 'admin-post.php' ) );
		}

		private function modern_editor_switch_url( int $post_id = 0, string $tab = 'general' ): string {
			$tab = sanitize_key( $tab ) ?: 'general';
			$base = admin_url(
				'edit.php?post_type=' . self::POST_TYPE
				. '&page=' . self::PAGE_SLUG
			);

			if ( $post_id > 0 ) {
				return add_query_arg(
					[
						'item_id'          => $post_id,
						'rbfw_editor_mode' => 'modern',
					],
					$base
				) . '#/rental/edit/' . $post_id . '/' . $tab;
			}

			return $base . '#/rental/new/' . $tab;
		}

		/* ── Redirects ──────────────────────────────────────────────────────── */

		public function maybe_redirect_edit_screen(): void {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( ! is_admin() || $this->is_edit_screen() || $this->is_classic_bypass() ) {
				return;
			}

			// Only intercept the actual edit screen. post.php also handles trash,
			// untrash, delete and preview; load-post.php fires before those run, so
			// redirecting here for every action would bounce "Trash"/"Delete" to the
			// editor and the item would never be deleted.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( $action !== '' && $action !== 'edit' ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen || $screen->base !== 'post' || $screen->post_type !== self::POST_TYPE ) {
				return;
			}

			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			if ( ! $post_id || get_post_type( $post_id ) !== self::POST_TYPE ) {
				return;
			}

			if ( ! $this->is_modern_mode_enabled( $post_id ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			wp_safe_redirect( $this->edit_url( $post_id, 'general' ) );
			exit;
		}

		public function maybe_redirect_new_screen(): void {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( ! is_admin() || $this->is_edit_screen() || $this->is_classic_bypass() ) {
				return;
			}

			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			if ( $post_type !== self::POST_TYPE ) {
				return;
			}

			if ( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			if ( ! $this->is_modern_mode_enabled() ) {
				return;
			}

			$post_id = $this->create_draft_item();
			if ( ! $post_id ) {
				wp_safe_redirect( $this->edit_url( 0, 'general' ) );
				exit;
			}

			wp_safe_redirect( $this->edit_url( $post_id, 'general' ) );
			exit;
		}

		/* ── Body class / menu highlight ────────────────────────────────────── */

		public function admin_body_class( string $classes ): string {
			if ( ! $this->is_edit_screen() ) return $classes;
			return $classes . ' rbfw-modern-editor-screen';
		}

		public function filter_parent_file( $parent_file ) {
			if ( ! $this->is_edit_screen() ) return $parent_file;
			return 'edit.php?post_type=' . self::POST_TYPE;
		}

		public function filter_submenu_file( $submenu_file ) {
			if ( ! $this->is_edit_screen() ) return $submenu_file;
			return 'edit.php?post_type=' . self::POST_TYPE;
		}

		/* ── Edit-link filters (list table + row actions) ───────────────────── */

		public function filter_edit_post_link( string $link, $post_id, string $context ): string {
			unset( $context );

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type !== self::POST_TYPE ) {
				return $link;
			}

			if ( $this->is_classic_bypass() ) {
				return $link;
			}

			if ( ! $this->is_modern_mode_enabled( (int) $post_id ) ) {
				return $link;
			}

			return $this->edit_url( (int) $post_id, 'general' );
		}

		public function filter_row_actions( array $actions, \WP_Post $post ): array {
			if ( $post->post_type !== self::POST_TYPE ) {
				return $actions;
			}

			if ( ! $this->is_modern_mode_enabled( $post->ID ) ) {
				return $actions;
			}

			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $this->edit_url( $post->ID, 'general' ) ),
					esc_html__( 'Edit', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			return $actions;
		}

		/* ── Editor switch handler ──────────────────────────────────────────── */

		public function handle_editor_switch(): void {
			if ( ! is_user_logged_in() ) {
				auth_redirect();
			}

			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, self::NONCE_SWITCH ) ) {
				wp_die(
					esc_html__( 'The link you followed has expired.', 'booking-and-rental-manager-for-woocommerce' ),
					esc_html__( 'Link expired', 'booking-and-rental-manager-for-woocommerce' ),
					[ 'response' => 403, 'back_link' => true ]
				);
			}

			$mode    = isset( $_GET['mode'] ) && 'classic' === $_GET['mode'] ? 'classic' : 'modern';
			$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
			if ( $post_id && current_user_can( 'edit_post', $post_id ) ) {
				update_post_meta( $post_id, self::META_MODE, $mode );
			}
			if ( $mode === 'classic' && $post_id ) {
				wp_safe_redirect( $this->classic_url( $post_id ) );
			} else {
				wp_safe_redirect( $this->edit_url( $post_id, 'general' ) );
			}
			exit;
		}

		/* ── Assets ─────────────────────────────────────────────────────────── */

		public function enqueue_assets(): void {
			if ( ! $this->is_edit_screen() ) return;
			$ver_css = filemtime( RBFW_PLUGIN_DIR . '/admin/css/rbfw-modern-editor.css' ) ?: '1.0.0';
			$ver_js  = filemtime( RBFW_PLUGIN_DIR . '/admin/js/rbfw-modern-editor.js' )  ?: '1.0.0';
			wp_enqueue_media();
			wp_enqueue_style( 'rbfw-fee-management', RBFW_PLUGIN_URL . '/css/fee-management.css', [], '1.0.0' );
			wp_enqueue_style(
				'rbfw-modern-editor',
				RBFW_PLUGIN_URL . '/admin/css/rbfw-modern-editor.css',
				[],
				$ver_css
			);
			wp_enqueue_script(
				'rbfw-modern-editor',
				RBFW_PLUGIN_URL . '/admin/js/rbfw-modern-editor.js',
				[ 'jquery', 'rbfw-script' ],
				$ver_js,
				true
			);
			wp_localize_script( 'rbfw-modern-editor', 'rbfwModernEditor', [
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce_save' => wp_create_nonce( self::NONCE_SAVE ),
				'list_url'   => admin_url( 'edit.php?post_type=' . self::POST_TYPE ),
				'i18n'       => [
					'loading'      => __( 'Loading editor…', 'booking-and-rental-manager-for-woocommerce' ),
					'saving'       => __( 'Saving your changes…', 'booking-and-rental-manager-for-woocommerce' ),
					'saved'        => __( 'All changes saved', 'booking-and-rental-manager-for-woocommerce' ),
					'save_error'   => __( 'Save failed — please try again', 'booking-and-rental-manager-for-woocommerce' ),
					'publish'      => __( 'Publish', 'booking-and-rental-manager-for-woocommerce' ),
					'update'       => __( 'Update', 'booking-and-rental-manager-for-woocommerce' ),
				],
			] );
		}

		/* ── AJAX: create draft ─────────────────────────────────────────────── */

		public function ajax_create_draft(): void {
			check_ajax_referer( self::NONCE_SAVE, 'nonce' );
			if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Forbidden', 403 );
			$post_id = wp_insert_post( [
				'post_type'   => self::POST_TYPE,
				'post_status' => 'auto-draft',
				'post_title'  => __( 'New Rental Item', 'booking-and-rental-manager-for-woocommerce' ),
			], true );
			if ( is_wp_error( $post_id ) ) wp_send_json_error( $post_id->get_error_message() );
			$this->set_edit_mode( (int) $post_id, 'modern' );
			wp_send_json_success( [
				'post_id'  => $post_id,
				'edit_url' => $this->edit_url( $post_id, 'general' ),
			] );
		}

		/* ── AJAX: save ─────────────────────────────────────────────────────── */

		public function ajax_save(): void {
			check_ajax_referer( self::NONCE_SAVE, 'nonce' );
			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
			if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( 'Forbidden', 403 );
			}

			$item_type = isset( $_POST['rbfw_item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_item_type'] ) ) : '';
			if ( class_exists( 'RBFW_Pricing' ) ) {
				$pricing_validator = new RBFW_Pricing();
				$pricing_errors    = $pricing_validator->get_pricing_validation_errors( $item_type, wp_unslash( $_POST ) );
				if ( ! empty( $pricing_errors ) ) {
					wp_send_json_error( [
						'message' => implode( ' ', $pricing_errors ),
						'errors'  => $pricing_errors,
					] );
				}
			}

			/* ── Post fields ── */
			$title   = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
			$content = isset( $_POST['post_content'] ) ? wp_kses_post( wp_unslash( $_POST['post_content'] ) ) : '';
			$status  = isset( $_POST['post_status'] ) && in_array( $_POST['post_status'], [ 'publish', 'draft', 'private' ], true )
				? sanitize_key( wp_unslash( $_POST['post_status'] ) )
				: get_post_status( $post_id );
			// First save of a freshly-opened item promotes the auto-draft to a real draft.
			if ( $status === 'auto-draft' ) {
				$status = 'draft';
			}

			wp_update_post( [
				'ID'           => $post_id,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => $status,
			] );

			/* ── Meta fields ── */
			$meta_keys = [
				'rbfw_item_sub_title', 'rbfw_item_type',
				// Pricing
				'rbfw_daily_rate',   'rbfw_enable_daily_rate',
				'rbfw_hourly_rate',  'rbfw_enable_hourly_rate',
				'rbfw_weekly_rate',  'rbfw_enable_weekly_rate',
				'rbfw_monthly_rate', 'rbfw_enable_monthly_rate',
				'rbfw_enable_daywise_price',
				// Template
				'rbfw_single_template',
				// Date
				'rbfw_enable_start_end_date', 'rbfw_enable_time_picker',
				'rbfw_minimum_booking_day',   'rbfw_maximum_booking_day',
				// Inventory
				'rbfw_item_quantity', 'rbfw_enable_md_type_item_qty', 'rbfw_enable_extra_service_qty',
				// Security deposit
				'rbfw_enable_security_deposit', 'rbfw_security_deposit_type', 'rbfw_security_deposit_amount',
				'rbfw_security_deposit_label',
				// Display
				'rbfw_enable_faq_content', 'rbfw_item_terms_conditions',
				// Location
				'rbfw_enable_pick_point',
			];

			foreach ( $meta_keys as $key ) {
				if ( isset( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
				}
			}

			/* Thumbnail */
			if ( isset( $_POST['_thumbnail_id'] ) ) {
				$thumb_id = absint( $_POST['_thumbnail_id'] );
				if ( $thumb_id > 0 ) {
					set_post_thumbnail( $post_id, $thumb_id );
				} else {
					delete_post_thumbnail( $post_id );
				}
			}

			/* Gallery images */
			$gallery_ids = [];
			if ( isset( $_POST['rbfw_gallery_images'] ) && is_array( $_POST['rbfw_gallery_images'] ) ) {
				$gallery_ids = array_values( array_filter( array_map( 'absint', $_POST['rbfw_gallery_images'] ) ) );
			}
			update_post_meta( $post_id, 'rbfw_gallery_images', $gallery_ids );

			/* Additional gallery (Muffin template) */
			$enable_add_gallery = ( isset( $_POST['rbfw_enable_additional_gallary'] ) && $_POST['rbfw_enable_additional_gallary'] === 'on' ) ? 'on' : 'off';
			update_post_meta( $post_id, 'rbfw_enable_additional_gallary', $enable_add_gallery );
			$add_gallery_ids = [];
			if ( isset( $_POST['rbfw_gallery_images_additional'] ) && is_array( $_POST['rbfw_gallery_images_additional'] ) ) {
				$add_gallery_ids = array_values( array_filter( array_map( 'absint', $_POST['rbfw_gallery_images_additional'] ) ) );
			}
			update_post_meta( $post_id, 'rbfw_gallery_images_additional', $add_gallery_ids );

			/* ── Fee Management ── */
			if ( isset( $_POST['rbfw_fee_data'] ) && is_array( $_POST['rbfw_fee_data'] ) ) {
				$fee_data = [];
				foreach ( $_POST['rbfw_fee_data'] as $fee ) {
					$clean = [
						'label'            => sanitize_text_field( wp_unslash( $fee['label']            ?? '' ) ),
						'description'      => sanitize_text_field( wp_unslash( $fee['description']      ?? '' ) ),
						'calculation_type' => sanitize_text_field( wp_unslash( $fee['calculation_type'] ?? 'fixed' ) ),
						'amount'           => floatval( $fee['amount'] ?? 0 ),
						'frequency'        => sanitize_text_field( wp_unslash( $fee['frequency']        ?? 'one-time' ) ),
						'priority'         => sanitize_text_field( wp_unslash( $fee['priority']         ?? 'optional' ) ),
						'refundable'       => sanitize_text_field( wp_unslash( $fee['refundable']       ?? 'no' ) ),
						'color'            => sanitize_text_field( wp_unslash( $fee['color']            ?? 'security' ) ),
					];
					if ( ! empty( $clean['label'] ) ) {
						$fee_data[] = $clean;
					}
				}
				update_post_meta( $post_id, 'rbfw_fee_data', $fee_data );
			}
			if ( isset( $_POST['rbfw_enable_fee_management'] ) ) {
				update_post_meta( $post_id, 'rbfw_enable_fee_management', sanitize_text_field( wp_unslash( $_POST['rbfw_enable_fee_management'] ) ) );
			}

		/* ── Extra service table (service_name[], service_price[], etc.) ── */
			$input = RBFW_Function::data_sanitize( $_POST );
			$names       = $input['service_name']  ?? [];
			$prices      = $input['service_price'] ?? [];
			$descs       = $input['service_desc']  ?? [];
			$qtys        = $input['service_qty']   ?? [];
			$imgs        = $input['service_img']   ?? [];
			$new_extra   = [];
			$count = count( $names );
			for ( $i = 0; $i < $count; $i++ ) {
				if ( ! empty( $names[ $i ] ) ) {
					$row = [ 'service_name' => sanitize_text_field( $names[ $i ] ) ];
					if ( isset( $prices[ $i ] ) )  $row['service_price'] = sanitize_text_field( $prices[ $i ] );
					if ( isset( $descs[ $i ] ) )   $row['service_desc']  = sanitize_text_field( $descs[ $i ] );
					if ( isset( $qtys[ $i ] ) )    $row['service_qty']   = sanitize_text_field( $qtys[ $i ] );
					if ( isset( $imgs[ $i ] ) )    $row['service_img']   = sanitize_text_field( $imgs[ $i ] );
					$new_extra[] = $row;
				}
			}
			if ( ! empty( $new_extra ) ) {
				update_post_meta( $post_id, 'rbfw_extra_service_data', $new_extra );
			} elseif ( isset( $_POST['service_name'] ) ) {
				delete_post_meta( $post_id, 'rbfw_extra_service_data' );
			}

			/* Extra service category price */
			if ( isset( $_POST['rbfw_service_category_price'] ) && is_array( $_POST['rbfw_service_category_price'] ) ) {
				$scp_raw = wp_unslash( $_POST['rbfw_service_category_price'] );
				array_walk_recursive( $scp_raw, function ( &$v ) { $v = is_string( $v ) ? sanitize_text_field( $v ) : ''; } );
				update_post_meta( $post_id, 'rbfw_service_category_price', wp_json_encode( $scp_raw ) );
			}
			if ( isset( $_POST['rbfw_enable_category_service_price'] ) ) {
				update_post_meta( $post_id, 'rbfw_enable_category_service_price', sanitize_text_field( wp_unslash( $_POST['rbfw_enable_category_service_price'] ) ) );
			}

		/* ── Pricing array fields ── */
			$array_meta = [
				'rbfw_bike_car_sd_data',
				'rbfw_resort_room_data',
				'multiple_items_info',
				'pricing_types',
				'rbfw_particulars_data',
				'rdfw_available_time',
			];
			foreach ( $array_meta as $key ) {
				if ( isset( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, RBFW_Function::data_sanitize( wp_unslash( $_POST[ $key ] ) ) );
				}
			}

			/* Pricing scalar fields */
			$pricing_scalars = [
				'rbfw_item_type',
				'rbfw_enable_daily_rate',  'rbfw_daily_rate',
				'rbfw_enable_hourly_rate', 'rbfw_hourly_rate',
				'rbfw_enable_weekly_rate', 'rbfw_weekly_rate',
				'rbfw_enable_monthly_rate','rbfw_monthly_rate',
				'rbfw_enable_daywise_price',
				'rbfw_enable_time_picker',
				'rbfw_mi_hourly_to_half_day_pivot', 'rbfw_mi_half_day_to_daily_pivot',
				'rbfw_mi_daily_to_weekly_pivot',    'rbfw_mi_weekly_to_monthly_pivot',
				'rbfw_enable_resort_daylong_price',
				'manage_inventory_as_timely', 'rbfw_item_stock_quantity_timely',
				'enable_specific_duration',
				'rbfw_enable_half_day_rate',     'rbfw_half_day_rate',
				'half_day_hour_threshold_start', 'half_day_hour_threshold_end',
				'rbfw_enable_hourly_threshold',  'rbfw_hourly_threshold',
				'rbfw_enable_day_threshold_for_weekly',  'rbfw_day_threshold_for_weekly',
				'rbfw_enable_day_threshold_for_monthly', 'rbfw_day_threshold_for_monthly',
				'rbfw_sd_appointment_max_qty_per_session',
				'rbfw_enable_extra_service_qty', 'rbfw_enable_md_type_item_qty',
			];
			foreach ( $pricing_scalars as $key ) {
				if ( isset( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
				}
			}

			$_ps_item_type = sanitize_text_field( wp_unslash( $_POST['rbfw_item_type'] ?? '' ) );
			if ( $_ps_item_type === 'appointment' ) {
				update_post_meta( $post_id, 'manage_inventory_as_timely', 'off' );
			}

			// rbfw_particular_switch is posted under a type-specific name
			if ( in_array( $_ps_item_type, [ 'bike_car_md', 'equipment', 'dress', 'others' ], true ) ) {
				$_ps_key = 'rbfw_particular_switch_md';
			} elseif ( $_ps_item_type === 'multiple_items' ) {
				$_ps_key = 'rbfw_particular_switch_mi';
			} else {
				$_ps_key = 'rbfw_particular_switch_sd';
			}
			$rbfw_particular_switch = ( isset( $_POST[ $_ps_key ] ) && $_POST[ $_ps_key ] === 'on' ) ? 'on' : 'off';
			update_post_meta( $post_id, 'rbfw_particular_switch', $rbfw_particular_switch );

			/* ── Inventory ── */
			// Store the entered stock as-is (blank stays blank so it is consistently
			// treated as a single unit, like the classic editor). Previously a blank
			// value was silently saved as 1000, which made "one vehicle" items
			// effectively unlimited and allowed double-booking.
			$rbfw_item_stock_raw      = isset( $_POST['rbfw_item_stock_quantity'] ) ? trim( wp_unslash( $_POST['rbfw_item_stock_quantity'] ) ) : '';
			$rbfw_item_stock_quantity = ( '' === $rbfw_item_stock_raw ) ? '' : absint( $rbfw_item_stock_raw );
			update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );

			$stock_manage_on_return_date = ( isset( $_POST['stock_manage_on_return_date'] ) && $_POST['stock_manage_on_return_date'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'stock_manage_on_return_date', $stock_manage_on_return_date );

			$rbfw_enable_variations = ( isset( $_POST['rbfw_enable_variations'] ) && $_POST['rbfw_enable_variations'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );

			$rbfw_variations_data = [];
			if ( isset( $_POST['rbfw_variations_data'] ) && is_array( $_POST['rbfw_variations_data'] ) ) {
				$rbfw_variations_data = rbfw_clean_variations_data( RBFW_Function::data_sanitize( wp_unslash( $_POST['rbfw_variations_data'] ) ) );
			}
			update_post_meta( $post_id, 'rbfw_variations_data', $rbfw_variations_data );

			/* Day-wise rates (sun-sat) */
			foreach ( [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ] as $day ) {
				foreach ( [ "rbfw_{$day}_hourly_rate", "rbfw_{$day}_half_day_rate", "rbfw_{$day}_daily_rate", "rbfw_enable_{$day}_day" ] as $dk ) {
					if ( isset( $_POST[ $dk ] ) ) {
						update_post_meta( $post_id, $dk, sanitize_text_field( wp_unslash( $_POST[ $dk ] ) ) );
					}
				}
			}

			/* FAQ enable toggle — canonical yes/no */
			$faq_enable = ( isset( $_POST['rbfw_enable_faq_content'] ) && $_POST['rbfw_enable_faq_content'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_faq_content', $faq_enable );

			/* Term Settings enable toggle */
			$term_enable = ( isset( $_POST['rbfw_enable_term_content'] ) && $_POST['rbfw_enable_term_content'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_term_content', $term_enable );

			/* Tax Settings enable toggle */
			$tax_settings_enable = ( isset( $_POST['rbfw_enable_tax_settings'] ) && $_POST['rbfw_enable_tax_settings'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_tax_settings', $tax_settings_enable );

			/* Related Items enable toggle */
			$related_items_enable = ( isset( $_POST['rbfw_enable_related_items'] ) && $_POST['rbfw_enable_related_items'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_related_items', $related_items_enable );

			/* Security Deposit */
			$deposit_enable = ( isset( $_POST['rbfw_enable_security_deposit'] ) && $_POST['rbfw_enable_security_deposit'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_security_deposit', $deposit_enable );
			$deposit_label  = isset( $_POST['rbfw_security_deposit_label'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_security_deposit_label'] ) ) : 'Security Deposit';
			update_post_meta( $post_id, 'rbfw_security_deposit_label', $deposit_label );
			$deposit_type   = ( isset( $_POST['rbfw_security_deposit_type'] ) && $_POST['rbfw_security_deposit_type'] === 'fixed_amount' ) ? 'fixed_amount' : 'percentage';
			update_post_meta( $post_id, 'rbfw_security_deposit_type', $deposit_type );
			$deposit_amount = isset( $_POST['rbfw_security_deposit_amount'] ) ? absint( $_POST['rbfw_security_deposit_amount'] ) : 0;
			update_post_meta( $post_id, 'rbfw_security_deposit_amount', $deposit_amount );

			/* Front-end Display Settings enable toggle */
			$frontend_display_enable = ( isset( $_POST['rbfw_enable_frontend_display'] ) && $_POST['rbfw_enable_frontend_display'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_frontend_display', $frontend_display_enable );

			/* Front-end Display Settings */
			if ( isset( $_POST['rbfw_has_frontend_settings'] ) ) {
				$qty_info = ( isset( $_POST['rbfw_available_qty_info_switch'] ) && $_POST['rbfw_available_qty_info_switch'] === 'yes' ) ? 'yes' : 'no';
				update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $qty_info );

				$shipping = ( isset( $_POST['shipping_enable'] ) && $_POST['shipping_enable'] === 'yes' ) ? 'yes' : 'no';
				update_post_meta( $post_id, 'shipping_enable', $shipping );
				$product_id = get_post_meta( $post_id, 'link_wc_product', true ) ?: $post_id;
				update_post_meta( $product_id, '_virtual', $shipping === 'yes' ? 'no' : 'yes' );

				$ship_class = isset( $_POST['rent_shipping_class'] ) ? absint( $_POST['rent_shipping_class'] ) : 0;
				update_post_meta( $post_id, 'rent_shipping_class', $ship_class );
				if ( $ship_class ) {
					wp_set_object_terms( $product_id, [ $ship_class ], 'product_shipping_class' );
				} else {
					wp_set_object_terms( $product_id, [], 'product_shipping_class' );
				}

				$svc_qty = ( isset( $_POST['rbfw_enable_extra_service_qty'] ) && $_POST['rbfw_enable_extra_service_qty'] === 'yes' ) ? 'yes' : 'no';
				update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $svc_qty );
			}

			/* Related items */
			if ( isset( $_POST['rbfw_has_related_picker'] ) ) {
				$related = isset( $_POST['rbfw_releted_rbfw'] ) && is_array( $_POST['rbfw_releted_rbfw'] )
					? array_values( array_filter( array_map( 'absint', $_POST['rbfw_releted_rbfw'] ) ) )
					: [];
				update_post_meta( $post_id, 'rbfw_releted_rbfw', $related );
			}

			/* Tax */
			if ( isset( $_POST['_tax_status'] ) ) {
				update_post_meta( $post_id, '_tax_status', sanitize_text_field( wp_unslash( $_POST['_tax_status'] ) ) );
			}
			if ( isset( $_POST['_tax_class'] ) ) {
				update_post_meta( $post_id, '_tax_class', sanitize_text_field( wp_unslash( $_POST['_tax_class'] ) ) );
			}

			/* Off Day Settings */
			$off_days = isset( $_POST['rbfw_off_days'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_off_days'] ) ) : '';
			update_post_meta( $post_id, 'rbfw_off_days', $off_days );

			$buffer_before = isset( $_POST['rbfw_buffer_time'] ) ? absint( $_POST['rbfw_buffer_time'] ) : 0;
			update_post_meta( $post_id, 'rbfw_buffer_time', $buffer_before );

			$buffer_after = isset( $_POST['rbfw_buffer_time_after'] ) ? absint( $_POST['rbfw_buffer_time_after'] ) : 0;
			update_post_meta( $post_id, 'rbfw_buffer_time_after', $buffer_after );

			/* collectFormData() posts checkboxes as their value when checked, '' when not. */
			$block_offday = ( isset( $_POST['rbfw_block_offday_range_booking'] ) && $_POST['rbfw_block_offday_range_booking'] === 'on' ) ? 'on' : 'off';
			update_post_meta( $post_id, 'rbfw_block_offday_range_booking', $block_offday );

			$from_dates = ( isset( $_POST['off_days_start'] ) && is_array( $_POST['off_days_start'] ) )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['off_days_start'] ) ) : [];
			$to_dates   = ( isset( $_POST['off_days_end'] ) && is_array( $_POST['off_days_end'] ) )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['off_days_end'] ) ) : [];
			$off_schedules = [];
			foreach ( $from_dates as $key => $from_date ) {
				if ( $from_date && ! empty( $to_dates[ $key ] ) ) {
					$off_schedules[] = [ 'from_date' => $from_date, 'to_date' => $to_dates[ $key ] ];
				}
			}
			update_post_meta( $post_id, 'rbfw_offday_range', $off_schedules );

			/* ── Location (pick-up / drop-off) ──
			 * Enable flags plus the selected location slugs (posted as a hidden,
			 * comma-separated value). Stored in the same shape the classic editor
			 * and the front end expect: array( array( 'loc_pickup_name' => slug ) ). */
			$enable_pick = ( isset( $_POST['rbfw_enable_pick_point'] ) && $_POST['rbfw_enable_pick_point'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_pick_point', $enable_pick );

			$enable_dropoff = ( isset( $_POST['rbfw_enable_dropoff_point'] ) && $_POST['rbfw_enable_dropoff_point'] === 'yes' ) ? 'yes' : 'no';
			update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $enable_dropoff );

			$pickup_csv   = isset( $_POST['rbfw_pickup_locations'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_pickup_locations'] ) ) : '';
			$pickup_slugs = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $pickup_csv ) ) ) );
			$pickup_data  = [];
			foreach ( array_values( $pickup_slugs ) as $slug ) {
				$pickup_data[] = [ 'loc_pickup_name' => $slug ];
			}
			update_post_meta( $post_id, 'rbfw_pickup_data', $pickup_data );

			$dropoff_csv   = isset( $_POST['rbfw_dropoff_locations'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_dropoff_locations'] ) ) : '';
			$dropoff_slugs = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $dropoff_csv ) ) ) );
			$dropoff_data  = [];
			foreach ( array_values( $dropoff_slugs ) as $slug ) {
				$dropoff_data[] = [ 'loc_dropoff_name' => $slug ];
			}
			update_post_meta( $post_id, 'rbfw_dropoff_data', $dropoff_data );

			/* Location inventory & price (shared field names with the classic panel) */
			if ( class_exists( 'RBFW_Location' ) && method_exists( 'RBFW_Location', 'save_location_inventory_from_post' ) ) {
				RBFW_Location::save_location_inventory_from_post( $post_id );
			}

			/* Categories (taxonomy) */
			if ( isset( $_POST['rbfw_categories'] ) ) {
				$cats = rbfw_sanitize_rent_type_categories( wp_unslash( $_POST['rbfw_categories'] ) );
				wp_set_object_terms( $post_id, $cats, 'rbfw_item_caregory' );
				update_post_meta( $post_id, 'rbfw_categories', $cats );
			}

			/* Feature categories (repeater) */
			if ( isset( $_POST['rbfw_feature_category'] ) ) {
				$feature_raw      = wp_unslash( $_POST['rbfw_feature_category'] );
				$feature_category = rbfw_prepare_feature_category_meta_value( $feature_raw );
				update_post_meta( $post_id, 'rbfw_feature_category', $feature_category );
			}

			do_action( 'rbfw_modern_editor_save', $post_id );

			$this->set_edit_mode( $post_id, 'modern' );

			wp_send_json_success( [
				'post_id'      => $post_id,
				'post_status'  => get_post_status( $post_id ),
				'permalink'    => get_permalink( $post_id ),
				'edit_url'     => $this->edit_url( $post_id, 'general' ),
			] );
		}

		/* ── Page render ────────────────────────────────────────────────────── */

		public function render_page(): void {
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( esc_html__( 'You are not allowed to access this page.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$post_id = isset( $_GET['item_id'] ) ? absint( $_GET['item_id'] ) : 0;

			if (
				$post_id
				&& isset( $_GET['rbfw_editor_mode'] )
				&& sanitize_key( wp_unslash( $_GET['rbfw_editor_mode'] ) ) === 'modern'
				&& current_user_can( 'edit_post', $post_id )
			) {
				$this->set_edit_mode( $post_id, 'modern' );
			}

			$post    = $post_id ? get_post( $post_id ) : null;

			/* Create draft if no ID yet */
			if ( ! $post_id ) {
				$new_id = $this->create_draft_item();
				if ( $new_id > 0 ) {
					wp_safe_redirect( $this->edit_url( $new_id, 'general' ) );
					exit;
				}
			}

			if ( $post_id && ( ! $post || $post->post_type !== self::POST_TYPE ) ) {
				wp_die( esc_html__( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
			if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) {
				wp_die( esc_html__( 'You are not allowed to edit this item.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			/* Collect meta */
			$m = $this->get_meta( $post_id );

			$screen_title  = $post && trim( $post->post_title ) ? $post->post_title : __( 'New Rental Item', 'booking-and-rental-manager-for-woocommerce' );
			$is_published  = $post && $post->post_status === 'publish';
			// A brand-new item is an auto-draft under the hood; show it as "Draft" in the UI.
			$editor_status = ( $post && $post->post_status !== 'auto-draft' ) ? $post->post_status : 'draft';
			$permalink     = $post_id ? get_permalink( $post_id ) : '';
			$classic_url   = $post_id ? $this->switch_url( 'classic', $post_id ) : '';
			$thumb_id      = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;
			$thumb_url     = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';

			/* ── Taxonomy: categories ── */
			$all_cat_terms = get_terms( [
				'taxonomy'   => 'rbfw_item_caregory',
				'hide_empty' => false,
			] );
			$all_cat_terms   = is_wp_error( $all_cat_terms ) ? [] : $all_cat_terms;
			// Order parent-first and stamp a `depth` on each term so the view can
			// indent sub-categories beneath their parent.
			$all_cat_terms   = $this->order_cat_terms_hierarchically( $all_cat_terms );
			$saved_cat_meta  = $post_id ? get_post_meta( $post_id, 'rbfw_categories', true ) : [];
			$saved_cat_meta  = is_array( $saved_cat_meta ) ? $saved_cat_meta : ( $saved_cat_meta ? maybe_unserialize( $saved_cat_meta ) : [] );
			// Flatten any comma-separated values stored as a single element
			$_flat = [];
			foreach ( (array) $saved_cat_meta as $_val ) {
				foreach ( explode( ',', (string) $_val ) as $_n ) {
					$_n = strtolower( trim( $_n ) );
					if ( $_n !== '' ) $_flat[] = $_n;
				}
			}
			$saved_cat_names = array_unique( $_flat );

			/* ── Feature categories repeater ── */
			$raw_features    = $post_id ? get_post_meta( $post_id, 'rbfw_feature_category', true ) : [];
			if ( is_serialized( $raw_features ) ) $raw_features = unserialize( $raw_features );
			$feature_categories = is_array( $raw_features ) ? $raw_features : [];

			$tabs = [
				[ 'key' => 'general',  'label' => __( 'General',  'booking-and-rental-manager-for-woocommerce' ), 'icon' => 'dashicons-admin-home' ],
				[ 'key' => 'pricing',  'label' => __( 'Pricing',  'booking-and-rental-manager-for-woocommerce' ), 'icon' => 'dashicons-money-alt' ],
				[ 'key' => 'offday',   'label' => __( 'Off Days', 'booking-and-rental-manager-for-woocommerce' ), 'icon' => 'dashicons-calendar-alt' ],
				[ 'key' => 'advanced', 'label' => __( 'Advanced', 'booking-and-rental-manager-for-woocommerce' ), 'icon' => 'dashicons-admin-settings' ],
			];

			$rent_types = [
				'bike_car_sd'    => __( 'Single Day (Bike/Car)',      'booking-and-rental-manager-for-woocommerce' ),
				'bike_car_md'    => __( 'Multiple Day (Bike/Car)',     'booking-and-rental-manager-for-woocommerce' ),
				'appointment'    => __( 'Appointment',                 'booking-and-rental-manager-for-woocommerce' ),
				'resort'         => __( 'Resort',                      'booking-and-rental-manager-for-woocommerce' ),
				'multiple_items' => __( 'Multiple Items',              'booking-and-rental-manager-for-woocommerce' ),
				'dress'          => __( 'Dress',                       'booking-and-rental-manager-for-woocommerce' ),
				'equipment'      => __( 'Equipment',                   'booking-and-rental-manager-for-woocommerce' ),
				'others'         => __( 'Others',                      'booking-and-rental-manager-for-woocommerce' ),
			];

			// Expose $post globally so addon hooks that use `global $post`
			// (e.g. the discount-over-x-days plugin) can resolve the post ID.
			$GLOBALS['post'] = $post;

			include RBFW_PLUGIN_DIR . '/admin/views/rbfw-modern-editor.php';
		}

		/**
		 * Flatten a set of rent-type terms into a parent-first ordered list, stamping a
		 * `depth` property on each term so the view can indent sub-categories beneath
		 * their parent.
		 *
		 * @param WP_Term[] $terms  Flat list of terms.
		 * @param int       $parent Parent term_id to collect children for.
		 * @param int       $depth  Current nesting depth.
		 * @return WP_Term[]
		 */
		private function order_cat_terms_hierarchically( $terms, $parent = 0, $depth = 0 ) {
			$out = [];
			foreach ( $terms as $term ) {
				if ( (int) $term->parent !== (int) $parent ) {
					continue;
				}
				$term->depth = (int) $depth;
				$out[]       = $term;
				$out         = array_merge(
					$out,
					$this->order_cat_terms_hierarchically( $terms, $term->term_id, $depth + 1 )
				);
			}
			return $out;
		}

		private function get_meta( int $post_id ): array {
			if ( ! $post_id ) return [];
			$keys = [
				'rbfw_item_sub_title', 'rbfw_item_type',
				'rbfw_daily_rate',   'rbfw_enable_daily_rate',
				'rbfw_hourly_rate',  'rbfw_enable_hourly_rate',
				'rbfw_weekly_rate',  'rbfw_enable_weekly_rate',
				'rbfw_monthly_rate', 'rbfw_enable_monthly_rate',
				'rbfw_enable_daywise_price',
				'rbfw_single_template',
				'rbfw_enable_start_end_date', 'rbfw_enable_time_picker',
				'rbfw_minimum_booking_day',   'rbfw_maximum_booking_day',
				'rbfw_item_quantity', 'rbfw_enable_md_type_item_qty', 'rbfw_enable_extra_service_qty',
				'rbfw_item_stock_quantity', 'stock_manage_on_return_date', 'rbfw_enable_variations',
				'rbfw_enable_security_deposit', 'rbfw_security_deposit_type',
				'rbfw_security_deposit_amount', 'rbfw_security_deposit_label',
				'rbfw_enable_faq_content', 'rbfw_enable_term_content', 'rbfw_item_terms_conditions',
				'rbfw_enable_pick_point',
				'rbfw_enable_additional_gallary',
				'_tax_status', '_tax_class', 'rbfw_enable_tax_settings',
				'rbfw_available_qty_info_switch', 'shipping_enable', 'rent_shipping_class',
				'rbfw_enable_frontend_display', 'rbfw_enable_related_items',
			];
			$out = [];
			foreach ( $keys as $k ) {
				$out[ $k ] = get_post_meta( $post_id, $k, true );
			}
			return $out;
		}

		/* ── Classic editor: "Switch to Modern Editor" button ──────────────── */

		private function is_classic_admin_screen(): bool {
			if ( ! is_admin() || $this->is_edit_screen() ) {
				return false;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen ) {
				return false;
			}

			if ( $screen->id === 'edit-' . self::POST_TYPE ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$view = isset( $_GET['rbfw_view'] ) ? sanitize_key( wp_unslash( $_GET['rbfw_view'] ) ) : '';
				return $view === 'classic';
			}

			if ( $screen->post_type !== self::POST_TYPE || $screen->base !== 'post' ) {
				return false;
			}

			if ( $screen->action === 'add' ) {
				return $this->is_classic_bypass() || ! $this->is_modern_mode_enabled();
			}

			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}

			return $this->is_classic_bypass() || ! $this->is_modern_mode_enabled( $post_id );
		}

		private function get_modern_switch_url(): string {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen ) {
				return '';
			}

			if ( $screen->id === 'edit-' . self::POST_TYPE ) {
				// On the list screen this button is a view toggle (mirrors the modern
				// list's "Classic view" link), so it must go to the modern LIST — not
				// the modern editor, which would open/create a single item.
				if ( class_exists( 'RBFW_Rental_List' ) ) {
					return admin_url( 'admin.php?page=' . RBFW_Rental_List::PAGE_SLUG );
				}
				return admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG );
			}

			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			if ( ! $post_id && isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
				$post_id = (int) $GLOBALS['post']->ID;
			}

			if ( $post_id > 0 ) {
				return $this->modern_editor_switch_url( $post_id, 'general' );
			}

			return self::add_new_url();
		}

		public function render_classic_switch_button(): void {
			if ( ! $this->is_classic_admin_screen() ) {
				return;
			}

			$modern_url = $this->get_modern_switch_url();
			if ( ! $modern_url ) {
				return;
			}

			// On the list screen the button switches the list view ("Modern view");
			// on a single item it opens the modern editor ("Modern Editor").
			$screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$is_list = $screen && $screen->id === 'edit-' . self::POST_TYPE;
			$label   = $is_list
				? __( 'Modern view', 'booking-and-rental-manager-for-woocommerce' )
				: __( 'Modern Editor', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<script>
			(function ($) {
				$(function () {
					var $wrap = $('.wrap').first();
					if (!$wrap.length || $wrap.find('.rbfw-switch-to-modern').length) {
						return;
					}

					var $anchor = $wrap.children('.page-title-action').last();
					if (!$anchor.length) {
						$anchor = $wrap.find('.page-title-action').last();
					}
					if (!$anchor.length) {
						$anchor = $wrap.find('h1.wp-heading-inline, h1').first();
					}

					var $titleActions = $wrap.children('.page-title-action');
					var $group;

					if ($titleActions.length) {
						if (!$titleActions.first().parent().hasClass('rbfw-classic-title-actions')) {
							$titleActions.wrapAll('<div class="rbfw-classic-title-actions"></div>');
						}
						$group = $wrap.children('.rbfw-classic-title-actions').first();
					}

					var $btn = $('<a>', {
						href: <?php echo wp_json_encode( $modern_url ); ?>,
						class: 'rbfw-switch-to-modern page-title-action',
						title: <?php echo wp_json_encode( $label ); ?>
					}).append(
						$('<span>', { class: 'rbfw-switch-to-modern__icon dashicons dashicons-welcome-view-site', 'aria-hidden': 'true' }),
						$('<span>', { class: 'rbfw-switch-to-modern__label', text: <?php echo wp_json_encode( $label ); ?> })
					);

					if ($group && $group.length) {
						$group.append($btn);
					} else {
						$anchor.after($btn);
					}
				});
			})(jQuery);
			</script>
			<?php
		}
	}

	new RBFW_Modern_Editor();
}
