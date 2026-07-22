<?php
/**
 * Coupons manager — admin list + CRUD for the unified coupon engine.
 *
 * Menu: Rent Item → Coupons (registered on the free plugin's `rbfw_admin_menu_after_settings`
 * extension hook, so it sits right after Settings).
 *
 * All writes go through AJAX guarded by check_ajax_referer('rbfw_coupon_nonce') +
 * current_user_can( rbfw_bookings_capability() ). Every field is whitelisted/sanitized on save;
 * every value is escaped on output.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Admin' ) ) {
	class RBFW_Coupon_Admin {

		const SLUG  = 'rbfw_coupons';
		const NONCE = 'rbfw_coupon_nonce';

		/** @var string Screen hook returned by add_submenu_page(), used to gate asset loading. */
		private $hook = '';

		public function __construct() {
			add_action( 'rbfw_admin_menu_after_settings', array( $this, 'menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'wp_ajax_rbfw_coupon_save', array( $this, 'ajax_save' ) );
			add_action( 'wp_ajax_rbfw_coupon_delete', array( $this, 'ajax_delete' ) );
			add_action( 'wp_ajax_rbfw_coupon_toggle', array( $this, 'ajax_toggle' ) );
		}

		public function menu() {
			$this->hook = add_submenu_page(
				'edit.php?post_type=rbfw_item',
				__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_bookings_capability(),
				self::SLUG,
				array( $this, 'render_page' )
			);
		}

		/** Page-scoped assets only (same $hook gating the rest of the plugin's admin pages use). */
		public function enqueue( $hook ) {
			if ( $hook !== $this->hook && false === strpos( (string) $hook, self::SLUG ) ) {
				return;
			}

			wp_enqueue_style( 'fontawesome.v6', RBFW_PLUGIN_URL . '/assets/font-awesome/all.min.css', array(), '6.0' );
			wp_enqueue_style(
				'rbfw-coupon-admin',
				RBFW_PLUGIN_URL . '/admin/css/rbfw_coupon_admin.css',
				array(),
				filemtime( RBFW_PLUGIN_DIR . '/admin/css/rbfw_coupon_admin.css' )
			);
			wp_enqueue_script(
				'rbfw-coupon-admin',
				RBFW_PLUGIN_URL . '/admin/js/rbfw_coupon_admin.js',
				array( 'jquery' ),
				filemtime( RBFW_PLUGIN_DIR . '/admin/js/rbfw_coupon_admin.js' ),
				true
			);
			wp_localize_script( 'rbfw-coupon-admin', 'rbfwCoupon', array(
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( self::NONCE ),
				'i18n_add'       => __( 'Add Coupon', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_edit'      => __( 'Edit Coupon', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_confirm'   => __( 'Delete this coupon permanently? This cannot be undone.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_need_code' => __( 'Please enter a coupon code.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_need_val'  => __( 'Enter a discount value greater than zero.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_pct_max'   => __( 'A percentage discount cannot exceed 100%.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_network'   => __( 'Network error. Please try again.', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_all'       => __( 'All rentals', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_none'      => __( 'None', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_unlimited' => __( 'Unlimited', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_always'    => __( 'Always', 'booking-and-rental-manager-for-woocommerce' ),
				'i18n_everyone'  => __( 'Everyone', 'booking-and-rental-manager-for-woocommerce' ),
				'currency'       => html_entity_decode( get_woocommerce_currency_symbol() ),
			) );
		}

		/* -------------------------------------------------------------------------
		 * Security
		 * ---------------------------------------------------------------------- */

		private function guard() {
			check_ajax_referer( self::NONCE, 'nonce' );
			if ( ! current_user_can( rbfw_bookings_capability() ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
			}
		}

		/** Any coupon mutation may change whether automatic rules exist. */
		private function flush_caches() {
			if ( class_exists( 'RBFW_Coupon_Frontend' ) ) {
				RBFW_Coupon_Frontend::flush_auto_cache();
			}
		}

		/* -------------------------------------------------------------------------
		 * Option sources
		 * ---------------------------------------------------------------------- */

		private function item_options() {
			$out   = array();
			$items = get_posts( array(
				'post_type'      => 'rbfw_item',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			) );
			foreach ( $items as $p ) {
				$out[ $p->ID ] = $p->post_title;
			}
			return $out;
		}

		/** Rent types are stored NAME-based on items (rbfw_categories), so we key by name. */
		private function rent_type_options() {
			$out   = array();
			$terms = get_terms( array( 'taxonomy' => 'rbfw_item_caregory', 'hide_empty' => false ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$out[ $t->name ] = $t->name;
				}
			}
			return $out;
		}

		/** Locations are matched by term slug. */
		private function location_options() {
			$out   = array();
			$terms = get_terms( array( 'taxonomy' => 'rbfw_item_location', 'hide_empty' => false ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$out[ $t->slug ] = $t->name;
				}
			}
			return $out;
		}

		private function role_options() {
			$out = array();
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}
			foreach ( get_editable_roles() as $key => $role ) {
				$out[ $key ] = translate_user_role( $role['name'] );
			}
			return $out;
		}

		private function weekday_options() {
			return array(
				0 => __( 'Sunday', 'booking-and-rental-manager-for-woocommerce' ),
				1 => __( 'Monday', 'booking-and-rental-manager-for-woocommerce' ),
				2 => __( 'Tuesday', 'booking-and-rental-manager-for-woocommerce' ),
				3 => __( 'Wednesday', 'booking-and-rental-manager-for-woocommerce' ),
				4 => __( 'Thursday', 'booking-and-rental-manager-for-woocommerce' ),
				5 => __( 'Friday', 'booking-and-rental-manager-for-woocommerce' ),
				6 => __( 'Saturday', 'booking-and-rental-manager-for-woocommerce' ),
			);
		}

		private function all_coupons() {
			return get_posts( array(
				'post_type'      => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 500,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );
		}

		/* -------------------------------------------------------------------------
		 * AJAX: save / delete / toggle
		 * ---------------------------------------------------------------------- */

		public function ajax_save() {
			$this->guard();

			$post = wp_unslash( $_POST ); // sanitized field-by-field below.

			$id   = isset( $post['coupon_id'] ) ? absint( $post['coupon_id'] ) : 0;
			$code = RBFW_Coupon::normalize_code( isset( $post['code'] ) ? sanitize_text_field( $post['code'] ) : '' );

			if ( '' === $code ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Coupon code is required.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( $this->code_exists( $code, $id ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'That coupon code already exists.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$type = isset( $post['discount_type'] ) ? sanitize_text_field( $post['discount_type'] ) : 'percentage';
			if ( ! in_array( $type, array( 'percentage', 'fixed', 'free_days' ), true ) ) {
				$type = 'percentage';
			}

			$value = isset( $post['discount_value'] ) ? (float) $post['discount_value'] : 0;
			if ( $value <= 0 ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Discount value must be greater than zero.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			if ( 'percentage' === $type && $value > 100 ) {
				wp_send_json_error( array( 'message' => esc_html__( 'A percentage discount cannot exceed 100%.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			$status = ( isset( $post['status'] ) && 'draft' === $post['status'] ) ? 'draft' : 'publish';

			$postarr = array(
				'post_type'   => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_title'  => $code,
				'post_status' => $status,
			);

			if ( $id ) {
				$postarr['ID'] = $id;
				$result        = wp_update_post( $postarr, true );
			} else {
				$result = wp_insert_post( $postarr, true );
			}
			if ( is_wp_error( $result ) || ! $result ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Could not save the coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			$cid = (int) ( $id ? $id : $result );

			// --- Whitelisted, typed meta ---
			update_post_meta( $cid, 'rbfw_code', $code );
			update_post_meta( $cid, 'rbfw_discount_type', $type );
			update_post_meta( $cid, 'rbfw_discount_value', $value );
			update_post_meta( $cid, 'rbfw_max_discount', isset( $post['max_discount'] ) ? max( 0, (float) $post['max_discount'] ) : 0 );

			update_post_meta( $cid, 'rbfw_auto_apply', $this->yn( $post, 'auto_apply' ) );
			update_post_meta( $cid, 'rbfw_priority', isset( $post['priority'] ) ? (int) $post['priority'] : 0 );
			update_post_meta( $cid, 'rbfw_allow_combine', $this->yn( $post, 'allow_combine' ) );

			update_post_meta( $cid, 'rbfw_target_items', $this->ints( $post, 'target_items' ) );
			update_post_meta( $cid, 'rbfw_exclude_items', $this->ints( $post, 'exclude_items' ) );
			update_post_meta( $cid, 'rbfw_target_rent_types', $this->texts( $post, 'target_rent_types' ) );
			update_post_meta( $cid, 'rbfw_exclude_rent_types', $this->texts( $post, 'exclude_rent_types' ) );
			update_post_meta( $cid, 'rbfw_target_locations', $this->slugs( $post, 'target_locations' ) );
			update_post_meta( $cid, 'rbfw_exclude_locations', $this->slugs( $post, 'exclude_locations' ) );

			update_post_meta( $cid, 'rbfw_min_amount', isset( $post['min_amount'] ) ? max( 0, (float) $post['min_amount'] ) : 0 );
			update_post_meta( $cid, 'rbfw_max_amount', isset( $post['max_amount'] ) ? max( 0, (float) $post['max_amount'] ) : 0 );

			update_post_meta( $cid, 'rbfw_valid_from', $this->date( $post, 'valid_from' ) );
			update_post_meta( $cid, 'rbfw_valid_to', $this->date( $post, 'valid_to' ) );
			update_post_meta( $cid, 'rbfw_weekdays', $this->weekdays( $post ) );
			update_post_meta( $cid, 'rbfw_blackout_dates', $this->date_list( isset( $post['blackout_dates'] ) ? $post['blackout_dates'] : '' ) );

			update_post_meta( $cid, 'rbfw_usage_limit', isset( $post['usage_limit'] ) ? max( 0, (int) $post['usage_limit'] ) : 0 );
			update_post_meta( $cid, 'rbfw_usage_limit_per_user', isset( $post['usage_limit_per_user'] ) ? max( 0, (int) $post['usage_limit_per_user'] ) : 0 );
			update_post_meta( $cid, 'rbfw_usage_limit_per_day', isset( $post['usage_limit_per_day'] ) ? max( 0, (int) $post['usage_limit_per_day'] ) : 0 );

			update_post_meta( $cid, 'rbfw_allowed_roles', $this->keys( $post, 'allowed_roles' ) );
			update_post_meta( $cid, 'rbfw_allowed_emails', $this->email_list( isset( $post['allowed_emails'] ) ? $post['allowed_emails'] : '' ) );
			update_post_meta( $cid, 'rbfw_first_booking_only', $this->yn( $post, 'first_booking_only' ) );

			// Initialise the counter row on create so the atomic increment always finds it.
			if ( ! $id ) {
				add_post_meta( $cid, 'rbfw_usage_count', 0, true );
			}

			$this->flush_caches();

			wp_send_json_success( array(
				'message' => esc_html__( 'Coupon saved.', 'booking-and-rental-manager-for-woocommerce' ),
				'id'      => $cid,
			) );
		}

		public function ajax_delete() {
			$this->guard();
			$id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;
			if ( ! $id || get_post_type( $id ) !== RBFW_Coupon_Post_Type::POST_TYPE ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			wp_delete_post( $id, true );
			$this->flush_caches();
			wp_send_json_success( array( 'message' => esc_html__( 'Coupon deleted.', 'booking-and-rental-manager-for-woocommerce' ) ) );
		}

		public function ajax_toggle() {
			$this->guard();
			$id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;
			if ( ! $id || get_post_type( $id ) !== RBFW_Coupon_Post_Type::POST_TYPE ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}
			$new = ( get_post_status( $id ) === 'publish' ) ? 'draft' : 'publish';
			wp_update_post( array( 'ID' => $id, 'post_status' => $new ) );
			$this->flush_caches();
			wp_send_json_success( array( 'status' => $new ) );
		}

		/* -------------------------------------------------------------------------
		 * Sanitizers
		 * ---------------------------------------------------------------------- */

		private function code_exists( $code, $exclude_id = 0 ) {
			$q = new WP_Query( array(
				'post_type'      => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'post__not_in'   => $exclude_id ? array( $exclude_id ) : array(),
				'meta_query'     => array(
					array( 'key' => 'rbfw_code', 'value' => $code, 'compare' => '=' ),
				),
			) );
			return ! empty( $q->posts );
		}

		private function yn( $post, $key ) {
			return ( isset( $post[ $key ] ) && in_array( (string) $post[ $key ], array( 'yes', 'on', '1', 'true' ), true ) ) ? 'yes' : 'no';
		}

		private function ints( $post, $key ) {
			if ( empty( $post[ $key ] ) || ! is_array( $post[ $key ] ) ) {
				return array();
			}
			return array_values( array_filter( array_map( 'absint', $post[ $key ] ) ) );
		}

		private function texts( $post, $key ) {
			if ( empty( $post[ $key ] ) || ! is_array( $post[ $key ] ) ) {
				return array();
			}
			return array_values( array_filter( array_map( 'sanitize_text_field', $post[ $key ] ) ) );
		}

		private function slugs( $post, $key ) {
			if ( empty( $post[ $key ] ) || ! is_array( $post[ $key ] ) ) {
				return array();
			}
			return array_values( array_filter( array_map( 'sanitize_title', $post[ $key ] ) ) );
		}

		private function keys( $post, $key ) {
			if ( empty( $post[ $key ] ) || ! is_array( $post[ $key ] ) ) {
				return array();
			}
			return array_values( array_filter( array_map( 'sanitize_key', $post[ $key ] ) ) );
		}

		private function weekdays( $post ) {
			if ( empty( $post['weekdays'] ) || ! is_array( $post['weekdays'] ) ) {
				return array();
			}
			$out = array();
			foreach ( $post['weekdays'] as $d ) {
				$d = (int) $d;
				if ( $d >= 0 && $d <= 6 ) {
					$out[] = $d;
				}
			}
			return array_values( array_unique( $out ) );
		}

		/** Validate a single Y-m-d date, returning '' when invalid/empty. */
		private function date( $post, $key ) {
			$raw = isset( $post[ $key ] ) ? sanitize_text_field( $post[ $key ] ) : '';
			return $this->valid_date( $raw );
		}

		private function valid_date( $raw ) {
			if ( '' === $raw ) {
				return '';
			}
			$d = DateTime::createFromFormat( 'Y-m-d', $raw );
			return ( $d && $d->format( 'Y-m-d' ) === $raw ) ? $raw : '';
		}

		/** Comma / newline separated Y-m-d list. */
		private function date_list( $raw ) {
			$parts = preg_split( '/[\s,;]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY );
			$out   = array();
			foreach ( (array) $parts as $p ) {
				$d = $this->valid_date( sanitize_text_field( $p ) );
				if ( $d ) {
					$out[] = $d;
				}
			}
			return array_values( array_unique( $out ) );
		}

		private function email_list( $raw ) {
			$parts = preg_split( '/[\s,;]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY );
			$out   = array();
			foreach ( (array) $parts as $p ) {
				$e = sanitize_email( $p );
				if ( $e && is_email( $e ) ) {
					$out[] = strtolower( $e );
				}
			}
			return array_values( array_unique( $out ) );
		}

		/* -------------------------------------------------------------------------
		 * Render
		 * ---------------------------------------------------------------------- */

		/** The full config of a coupon, as JSON for the edit modal. */
		private function coupon_json( $post ) {
			$c = new RBFW_Coupon( $post->ID );
			return array(
				'id'                   => $post->ID,
				'code'                 => $c->get_code(),
				'status'               => $post->post_status,
				'discount_type'        => $c->get_discount_type(),
				'discount_value'       => $c->get_discount_value(),
				'max_discount'         => $c->get_max_discount(),
				'auto_apply'           => $c->is_auto_apply() ? 'yes' : 'no',
				'priority'             => $c->get_priority(),
				'allow_combine'        => $c->allows_combine() ? 'yes' : 'no',
				'target_items'         => $c->get_target_items(),
				'exclude_items'        => $c->get_exclude_items(),
				'target_rent_types'    => $c->get_target_rent_types(),
				'exclude_rent_types'   => $c->get_exclude_rent_types(),
				'target_locations'     => $c->get_target_locations(),
				'exclude_locations'    => $c->get_exclude_locations(),
				'min_amount'           => $c->get_min_amount(),
				'max_amount'           => $c->get_max_amount(),
				'valid_from'           => $c->get_valid_from(),
				'valid_to'             => $c->get_valid_to(),
				'weekdays'             => $c->get_weekdays(),
				'blackout_dates'       => implode( ', ', $c->get_blackout_dates() ),
				'usage_limit'          => $c->get_usage_limit(),
				'usage_limit_per_user' => $c->get_usage_limit_per_user(),
				'usage_limit_per_day'  => $c->get_usage_limit_per_day(),
				'allowed_roles'        => $c->get_allowed_roles(),
				'allowed_emails'       => implode( ', ', $c->get_allowed_emails() ),
				'first_booking_only'   => $c->is_first_booking_only() ? 'yes' : 'no',
			);
		}

		private function describe_value( RBFW_Coupon $c ) {
			switch ( $c->get_discount_type() ) {
				case 'fixed':
					return wp_strip_all_tags( wc_price( $c->get_discount_value() ) );
				case 'free_days':
					/* translators: %s: number of free units */
					return sprintf( esc_html__( '%s free day(s)', 'booking-and-rental-manager-for-woocommerce' ), (float) $c->get_discount_value() );
				default:
					return (float) $c->get_discount_value() . '%';
			}
		}

		private function describe_targets( RBFW_Coupon $c ) {
			if ( $c->targets_everything() ) {
				return esc_html__( 'All rentals', 'booking-and-rental-manager-for-woocommerce' );
			}
			$bits = array();
			if ( $c->get_target_items() ) {
				/* translators: %d: number of rental items */
				$bits[] = sprintf( esc_html__( '%d item(s)', 'booking-and-rental-manager-for-woocommerce' ), count( $c->get_target_items() ) );
			}
			if ( $c->get_target_rent_types() ) {
				$bits[] = implode( ', ', array_slice( $c->get_target_rent_types(), 0, 3 ) );
			}
			if ( $c->get_target_locations() ) {
				/* translators: %d: number of locations */
				$bits[] = sprintf( esc_html__( '%d location(s)', 'booking-and-rental-manager-for-woocommerce' ), count( $c->get_target_locations() ) );
			}
			return implode( ' · ', $bits );
		}

		public function render_page() {
			if ( ! current_user_can( rbfw_bookings_capability() ) ) {
				wp_die( esc_html__( 'You do not have permission to view this page.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$coupons       = $this->all_coupons();
			$items         = $this->item_options();
			$rent_types    = $this->rent_type_options();
			$locations     = $this->location_options();
			$roles         = $this->role_options();
			$weekdays      = $this->weekday_options();
			$engine_on     = RBFW_Coupon_Engine::is_enabled();
			$default_combi = RBFW_Coupon_Engine::setting( 'rbfw_coupon_default_combine', 'off' ) === 'on';
			$settings_url  = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings_page' );

			$active      = 0;
			$redemptions = 0;
			$saved       = 0.0;
			foreach ( $coupons as $p ) {
				if ( 'publish' === $p->post_status ) {
					$active++;
				}
				$redemptions += (int) get_post_meta( $p->ID, 'rbfw_usage_count', true );
				$saved       += (float) get_post_meta( $p->ID, 'rbfw_usage_amount_total', true );
			}
			$mode_label = RBFW_Function::booking_mode() === 'woocommerce'
				? __( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' )
				: __( 'Standalone', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<div class="wrap rbfw-cpn">
				<div class="rbfw-cpn-panel">
					<div class="rbfw-cpn-panel-head">
						<div class="rbfw-cpn-head-icon"><i class="fas fa-ticket"></i></div>
						<div class="rbfw-cpn-head-text">
							<h2><?php esc_html_e( 'Coupons &amp; Automatic Discounts', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'One engine for both booking modes — per-rental targeting, date &amp; spend conditions, usage limits.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<button type="button" class="rbfw-cpn-btn-add rbfw-cpn-add">
							<i class="fas fa-plus"></i> <?php esc_html_e( 'Add Coupon', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>

					<div class="rbfw-cpn-panel-body">
						<?php if ( ! $engine_on ) : ?>
							<div class="rbfw-cpn-notice">
								<i class="fas fa-triangle-exclamation"></i>
								<span>
									<?php esc_html_e( 'The coupon engine is disabled — coupons will not apply on the frontend.', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Enable it in Settings → Coupons', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
								</span>
							</div>
						<?php endif; ?>

						<div class="rbfw-cpn-stats">
							<div class="rbfw-cpn-stat">
								<div class="rbfw-cpn-stat-ico blue"><i class="fas fa-ticket"></i></div>
								<div><span><?php echo esc_html( count( $coupons ) ); ?></span><label><?php esc_html_e( 'Total Coupons', 'booking-and-rental-manager-for-woocommerce' ); ?></label></div>
							</div>
							<div class="rbfw-cpn-stat">
								<div class="rbfw-cpn-stat-ico green"><i class="fas fa-circle-check"></i></div>
								<div><span><?php echo esc_html( $active ); ?></span><label><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></label></div>
							</div>
							<div class="rbfw-cpn-stat">
								<div class="rbfw-cpn-stat-ico pink"><i class="fas fa-arrow-trend-down"></i></div>
								<div><span><?php echo esc_html( $redemptions ); ?></span><label><?php esc_html_e( 'Redemptions', 'booking-and-rental-manager-for-woocommerce' ); ?></label></div>
							</div>
							<div class="rbfw-cpn-stat">
								<div class="rbfw-cpn-stat-ico purple"><i class="fas fa-sack-dollar"></i></div>
								<div><span><?php echo wp_kses_post( wc_price( $saved ) ); ?></span><label><?php esc_html_e( 'Customer Savings', 'booking-and-rental-manager-for-woocommerce' ); ?></label></div>
							</div>
							<div class="rbfw-cpn-stat">
								<div class="rbfw-cpn-stat-ico slate"><i class="fas fa-cart-shopping"></i></div>
								<div><span class="sm"><?php echo esc_html( $mode_label ); ?></span><label><?php esc_html_e( 'Booking Mode', 'booking-and-rental-manager-for-woocommerce' ); ?></label></div>
							</div>
						</div>

						<div class="rbfw-cpn-tablecard">
							<table class="rbfw-cpn-table">
								<thead><tr>
									<th><?php esc_html_e( 'Code', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Discount', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Applies To', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Validity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Usage', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th class="ta-r"><?php esc_html_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</tr></thead>
								<tbody>
								<?php if ( ! $coupons ) : ?>
									<tr><td colspan="7" class="rbfw-cpn-empty">
										<i class="fas fa-ticket"></i>
										<strong><?php esc_html_e( 'No coupons yet', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
										<span><?php esc_html_e( 'Create your first coupon code or an automatic discount rule.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<button type="button" class="rbfw-cpn-btn primary rbfw-cpn-add"><i class="fas fa-plus"></i> <?php esc_html_e( 'Add Coupon', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
									</td></tr>
								<?php endif; ?>
								<?php
								foreach ( $coupons as $p ) :
									$c     = new RBFW_Coupon( $p->ID );
									$limit = $c->get_usage_limit();
									$used  = $c->get_usage_count();
									$from  = $c->get_valid_from();
									$to    = $c->get_valid_to();
									$pct   = ( $limit > 0 ) ? min( 100, ( $used / $limit ) * 100 ) : 0;
									?>
									<tr data-coupon="<?php echo esc_attr( wp_json_encode( $this->coupon_json( $p ) ) ); ?>">
										<td>
											<div class="rbfw-cpn-code"><?php echo esc_html( $c->get_code() ); ?></div>
											<div class="rbfw-cpn-tags">
												<?php if ( $c->is_auto_apply() ) : ?>
													<span class="rbfw-cpn-badge auto"><i class="fas fa-bolt"></i><?php esc_html_e( 'AUTO', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
												<?php endif; ?>
												<?php if ( $c->allows_combine() ) : ?>
													<span class="rbfw-cpn-badge stack"><i class="fas fa-layer-group"></i><?php esc_html_e( 'STACKS', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
												<?php endif; ?>
											</div>
										</td>
										<td><span class="rbfw-cpn-amount"><?php echo esc_html( $this->describe_value( $c ) ); ?></span></td>
										<td><span class="rbfw-cpn-muted"><?php echo esc_html( $this->describe_targets( $c ) ); ?></span></td>
										<td>
											<span class="rbfw-cpn-muted">
											<?php
											if ( $from || $to ) {
												echo esc_html( ( $from ? $from : '…' ) . ' → ' . ( $to ? $to : '…' ) );
											} else {
												esc_html_e( 'Always', 'booking-and-rental-manager-for-woocommerce' );
											}
											?>
											</span>
										</td>
										<td>
											<div class="rbfw-cpn-usage"><?php echo esc_html( $used . ( $limit ? ' / ' . $limit : '' ) ); ?></div>
											<?php if ( $limit > 0 ) : ?>
												<div class="rbfw-cpn-bar"><i style="width:<?php echo esc_attr( $pct ); ?>%"></i></div>
											<?php endif; ?>
										</td>
										<td>
											<span class="rbfw-cpn-status <?php echo 'publish' === $p->post_status ? 'on' : 'off'; ?>">
												<?php echo 'publish' === $p->post_status ? esc_html__( 'Active', 'booking-and-rental-manager-for-woocommerce' ) : esc_html__( 'Inactive', 'booking-and-rental-manager-for-woocommerce' ); ?>
											</span>
										</td>
										<td class="ta-r rbfw-cpn-actions">
											<button type="button" class="rbfw-cpn-ico rbfw-cpn-edit" data-id="<?php echo esc_attr( $p->ID ); ?>" title="<?php esc_attr_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-pen"></i></button>
											<button type="button" class="rbfw-cpn-ico rbfw-cpn-toggle" data-id="<?php echo esc_attr( $p->ID ); ?>" title="<?php esc_attr_e( 'Activate / Deactivate', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-power-off"></i></button>
											<button type="button" class="rbfw-cpn-ico danger rbfw-cpn-delete" data-id="<?php echo esc_attr( $p->ID ); ?>" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-trash"></i></button>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<?php $this->render_modal( $items, $rent_types, $locations, $roles, $weekdays, $default_combi ); ?>
			</div>
			<?php
		}

		/**
		 * A searchable multi-select "token" field: click → dropdown list → pick → chip, repeat.
		 *
		 * The native <select multiple> stays in the DOM (visually hidden) and remains the single
		 * source of truth, so $form.serializeArray() keeps posting `name[]` exactly as before and
		 * ajax_save() is unchanged. The JS only paints chips + a filterable menu on top of it.
		 */
		private function token_select( $name, $options, $legend, $mode = 'in', $placeholder = '' ) {
			$placeholder = $placeholder ? $placeholder : __( 'Click to choose…', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<label>
				<span class="lb">
					<i class="fas <?php echo 'in' === $mode ? 'fa-circle-plus ok' : 'fa-circle-minus no'; ?>"></i>
					<?php echo esc_html( $legend ); ?>
				</span>
				<select class="rbfw-tok-native"
						name="<?php echo esc_attr( $name ); ?>[]"
						multiple
						data-token
						data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
						data-empty="<?php esc_attr_e( 'No matches found', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<?php foreach ( $options as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if ( ! $options ) : ?>
					<small><?php esc_html_e( 'None available yet.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
				<?php endif; ?>
			</label>
			<?php
		}

		/** A bordered checkbox grid used for roles / weekdays. */
		private function checkbox_grid( $name, $options, $legend, $hint = '' ) {
			?>
			<fieldset class="rbfw-cpn-grid">
				<legend><?php echo esc_html( $legend ); ?></legend>
				<?php if ( $hint ) : ?><p class="rbfw-cpn-hint sm"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
				<div class="rbfw-cpn-grid-items">
					<?php foreach ( $options as $val => $label ) : ?>
						<label class="rbfw-cpn-chk">
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $val ); ?>">
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
					<?php if ( ! $options ) : ?>
						<em class="rbfw-cpn-none"><?php esc_html_e( 'None available', 'booking-and-rental-manager-for-woocommerce' ); ?></em>
					<?php endif; ?>
				</div>
			</fieldset>
			<?php
		}

		/** Multi-step wizard. Field names are unchanged — ajax_save() consumes them as-is. */
		private function render_modal( $items, $rent_types, $locations, $roles, $weekdays, $default_combi ) {
			$steps = array(
				array( 'key' => 'basics',    'icon' => 'fa-sliders',        'title' => __( 'Basics', 'booking-and-rental-manager-for-woocommerce' ),    'sub' => __( 'Code & discount', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'key' => 'targeting', 'icon' => 'fa-crosshairs',     'title' => __( 'Targeting', 'booking-and-rental-manager-for-woocommerce' ), 'sub' => __( 'Which rentals', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'key' => 'rules',     'icon' => 'fa-calendar-check', 'title' => __( 'Rules', 'booking-and-rental-manager-for-woocommerce' ),     'sub' => __( 'Spend & dates', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'key' => 'limits',    'icon' => 'fa-user-shield',    'title' => __( 'Limits', 'booking-and-rental-manager-for-woocommerce' ),    'sub' => __( 'Usage & eligibility', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'key' => 'review',    'icon' => 'fa-clipboard-check','title' => __( 'Review', 'booking-and-rental-manager-for-woocommerce' ),    'sub' => __( 'Confirm & save', 'booking-and-rental-manager-for-woocommerce' ) ),
			);
			?>
			<div id="rbfw-cpn-modal" class="rbfw-cpn-modal" aria-hidden="true">
				<div class="rbfw-cpn-box" role="dialog" aria-modal="true" aria-labelledby="rbfw-cpn-modal-title">

					<div class="rbfw-cpn-modal-head">
						<div class="rbfw-cpn-head-icon"><i class="fas fa-ticket"></i></div>
						<div class="rbfw-cpn-head-text">
							<h2 id="rbfw-cpn-modal-title"><?php esc_html_e( 'Add Coupon', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p class="rbfw-cpn-stepcaption"></p>
						</div>
						<button type="button" class="rbfw-cpn-close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
					</div>

					<div class="rbfw-cpn-progress"><i></i></div>

					<form id="rbfw-cpn-form" class="rbfw-cpn-modal-body" novalidate>
						<input type="hidden" name="coupon_id" value="0">

						<nav class="rbfw-cpn-rail" role="tablist">
							<?php foreach ( $steps as $i => $s ) : ?>
								<button type="button" class="rbfw-cpn-railitem<?php echo 0 === $i ? ' is-active' : ''; ?>" data-step="<?php echo esc_attr( $i ); ?>" role="tab">
									<span class="rbfw-cpn-railnum"><span class="n"><?php echo esc_html( $i + 1 ); ?></span><i class="fas fa-check"></i></span>
									<span class="rbfw-cpn-railtxt">
										<strong><?php echo esc_html( $s['title'] ); ?></strong>
										<small><?php echo esc_html( $s['sub'] ); ?></small>
									</span>
								</button>
							<?php endforeach; ?>
						</nav>

						<div class="rbfw-cpn-panes">

							<!-- STEP 1 — Basics -->
							<section class="rbfw-cpn-pane is-active" data-pane="0">
								<h3 class="rbfw-cpn-sec"><i class="fas fa-sliders"></i><?php esc_html_e( 'Coupon basics', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<div class="rbfw-cpn-row">
									<label class="f2"><span class="lb"><?php esc_html_e( 'Coupon Code', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="req">*</b></span>
										<input type="text" name="code" placeholder="SUMMER20" autocomplete="off">
										<small><?php esc_html_e( 'Also used as the internal name for automatic rules.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
									</label>
									<label><span class="lb"><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<select name="status">
											<option value="publish"><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
											<option value="draft"><?php esc_html_e( 'Inactive', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
										</select>
									</label>
								</div>

								<div class="rbfw-cpn-types">
									<label class="rbfw-cpn-type">
										<input type="radio" name="discount_type" value="percentage" checked>
										<span class="card"><i class="fas fa-percent"></i><strong><?php esc_html_e( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( '% off the rental subtotal', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
									<label class="rbfw-cpn-type">
										<input type="radio" name="discount_type" value="fixed">
										<span class="card"><i class="fas fa-tag"></i><strong><?php esc_html_e( 'Fixed amount', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'A flat sum off', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
									<label class="rbfw-cpn-type">
										<input type="radio" name="discount_type" value="free_days">
										<span class="card"><i class="fas fa-calendar-day"></i><strong><?php esc_html_e( 'Free days', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'Waive N rental days', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
								</div>

								<div class="rbfw-cpn-row">
									<label><span class="lb" data-value-label><?php esc_html_e( 'Value', 'booking-and-rental-manager-for-woocommerce' ); ?> <b class="req">*</b></span>
										<input type="number" name="discount_value" step="0.01" min="0" placeholder="20">
									</label>
									<label id="rbfw-cpn-cap-wrap"><span class="lb"><?php esc_html_e( 'Maximum Discount (cap)', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="max_discount" step="0.01" min="0" placeholder="<?php esc_attr_e( '0 = no cap', 'booking-and-rental-manager-for-woocommerce' ); ?>">
										<small><?php esc_html_e( 'Caps how much a percentage coupon can take off.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
									</label>
									<label><span class="lb"><?php esc_html_e( 'Priority', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="priority" step="1" value="0">
										<small><?php esc_html_e( 'Higher wins when several automatic rules match.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
									</label>
								</div>

								<div class="rbfw-cpn-switches">
									<label class="rbfw-cpn-switch">
										<input type="checkbox" name="auto_apply" value="yes"><span class="track"></span>
										<span class="txt"><strong><?php esc_html_e( 'Apply automatically', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'Customers get it without entering a code.', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
									<label class="rbfw-cpn-switch">
										<input type="checkbox" name="allow_combine" value="yes" <?php checked( $default_combi ); ?>><span class="track"></span>
										<span class="txt"><strong><?php esc_html_e( 'Allow stacking', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'Can combine with other coupons that also allow it.', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
								</div>
							</section>

							<!-- STEP 2 — Targeting -->
							<section class="rbfw-cpn-pane" data-pane="1">
								<h3 class="rbfw-cpn-sec"><i class="fas fa-crosshairs"></i><?php esc_html_e( 'Per-rental targeting', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<p class="rbfw-cpn-hint"><?php esc_html_e( 'Leave everything empty to apply to all rentals. Include rules are combined (any match wins); exclude rules always override.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>

								<div class="rbfw-cpn-row">
									<?php $this->token_select( 'target_items', $items, __( 'Include Rental Items', 'booking-and-rental-manager-for-woocommerce' ), 'in', __( 'Search rentals…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
									<?php $this->token_select( 'exclude_items', $items, __( 'Exclude Rental Items', 'booking-and-rental-manager-for-woocommerce' ), 'ex', __( 'Search rentals…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
								</div>
								<div class="rbfw-cpn-row">
									<?php $this->token_select( 'target_rent_types', $rent_types, __( 'Include Rent Types', 'booking-and-rental-manager-for-woocommerce' ), 'in', __( 'Search rent types…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
									<?php $this->token_select( 'exclude_rent_types', $rent_types, __( 'Exclude Rent Types', 'booking-and-rental-manager-for-woocommerce' ), 'ex', __( 'Search rent types…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
								</div>
								<div class="rbfw-cpn-row">
									<?php $this->token_select( 'target_locations', $locations, __( 'Include Locations', 'booking-and-rental-manager-for-woocommerce' ), 'in', __( 'Search locations…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
									<?php $this->token_select( 'exclude_locations', $locations, __( 'Exclude Locations', 'booking-and-rental-manager-for-woocommerce' ), 'ex', __( 'Search locations…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
								</div>
							</section>

							<!-- STEP 3 — Rules -->
							<section class="rbfw-cpn-pane" data-pane="2">
								<h3 class="rbfw-cpn-sec"><i class="fas fa-calendar-check"></i><?php esc_html_e( 'Spend &amp; date rules', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<div class="rbfw-cpn-row">
									<label><span class="lb"><?php esc_html_e( 'Minimum Booking Amount', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="min_amount" step="0.01" min="0" placeholder="<?php esc_attr_e( '0 = none', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</label>
									<label><span class="lb"><?php esc_html_e( 'Maximum Booking Amount', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="max_amount" step="0.01" min="0" placeholder="<?php esc_attr_e( '0 = none', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</label>
									<label><span class="lb"><?php esc_html_e( 'Valid From', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="date" name="valid_from">
									</label>
									<label><span class="lb"><?php esc_html_e( 'Valid To', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="date" name="valid_to">
									</label>
								</div>
								<div class="rbfw-cpn-row">
									<?php $this->checkbox_grid( 'weekdays', $weekdays, __( 'Allowed Booking Weekdays', 'booking-and-rental-manager-for-woocommerce' ), __( 'Empty = every day allowed.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
									<label><span class="lb"><?php esc_html_e( 'Blackout Dates', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<textarea name="blackout_dates" rows="5" placeholder="2026-12-24, 2026-12-25"></textarea>
										<small><?php esc_html_e( 'Y-m-d, separated by commas or new lines. Invalid dates are ignored.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
									</label>
								</div>
							</section>

							<!-- STEP 4 — Limits -->
							<section class="rbfw-cpn-pane" data-pane="3">
								<h3 class="rbfw-cpn-sec"><i class="fas fa-user-shield"></i><?php esc_html_e( 'Usage limits &amp; eligibility', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<div class="rbfw-cpn-row">
									<label><span class="lb"><?php esc_html_e( 'Total Usage Limit', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="usage_limit" step="1" min="0" placeholder="<?php esc_attr_e( '0 = unlimited', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</label>
									<label><span class="lb"><?php esc_html_e( 'Limit Per User', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="usage_limit_per_user" step="1" min="0" placeholder="<?php esc_attr_e( '0 = unlimited', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</label>
									<label><span class="lb"><?php esc_html_e( 'Limit Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<input type="number" name="usage_limit_per_day" step="1" min="0" placeholder="<?php esc_attr_e( '0 = unlimited', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									</label>
								</div>
								<div class="rbfw-cpn-row">
									<?php $this->checkbox_grid( 'allowed_roles', $roles, __( 'Allowed User Roles', 'booking-and-rental-manager-for-woocommerce' ), __( 'Empty = everyone, including guests.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
									<label><span class="lb"><?php esc_html_e( 'Allowed Emails', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<textarea name="allowed_emails" rows="5" placeholder="a@example.com, b@example.com"></textarea>
										<small><?php esc_html_e( 'Empty = any customer. Invalid addresses are ignored.', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
									</label>
								</div>
								<div class="rbfw-cpn-switches">
									<label class="rbfw-cpn-switch">
										<input type="checkbox" name="first_booking_only" value="yes"><span class="track"></span>
										<span class="txt"><strong><?php esc_html_e( 'First booking only', 'booking-and-rental-manager-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'Only customers with no previous booking or order.', 'booking-and-rental-manager-for-woocommerce' ); ?></small></span>
									</label>
								</div>
							</section>

							<!-- STEP 5 — Review -->
							<section class="rbfw-cpn-pane" data-pane="4">
								<h3 class="rbfw-cpn-sec"><i class="fas fa-clipboard-check"></i><?php esc_html_e( 'Review &amp; save', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<div class="rbfw-cpn-review" id="rbfw-cpn-review"></div>
							</section>
						</div>
					</form>

					<div class="rbfw-cpn-modal-foot">
						<button type="button" class="rbfw-cpn-btn ghost rbfw-cpn-close"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						<span class="rbfw-cpn-msg"></span>
						<div class="rbfw-cpn-nav">
							<button type="button" class="rbfw-cpn-btn ghost" data-prev disabled><i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Back', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<button type="button" class="rbfw-cpn-btn accent" data-next><?php esc_html_e( 'Next', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-arrow-right"></i></button>
							<button type="button" class="rbfw-cpn-btn primary" data-save hidden><i class="fas fa-floppy-disk"></i> <?php esc_html_e( 'Save Coupon', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	new RBFW_Coupon_Admin();
}
