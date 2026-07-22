<?php
/**
 * Coupon model — a thin, typed wrapper around a single `rbfw_coupon` post + its meta.
 *
 * Loading:
 *   RBFW_Coupon::load_by_code( 'SUMMER10' )   → ?RBFW_Coupon  (active coupons only)
 *   RBFW_Coupon::get_active_auto_coupons()     → RBFW_Coupon[] (auto-apply rules, priority DESC)
 *
 * All getters return already-typed values (int/float/bool/array) so the engine never has to
 * re-cast meta. Config keys are documented in the plan's data-model table.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon' ) ) {
	class RBFW_Coupon {

		/** @var int */
		protected $id = 0;

		/** @var array Cached meta bag (key => raw value). */
		protected $meta = array();

		public function __construct( $post_id ) {
			$this->id = absint( $post_id );
		}

		/**
		 * Normalize a user-entered code to its canonical form (trim + uppercase).
		 * The same normalization is applied on save, so lookups are exact.
		 */
		public static function normalize_code( $code ) {
			$code = is_string( $code ) ? $code : '';
			// Multibyte-safe uppercasing; collapse internal whitespace is intentionally NOT done
			// (codes may legitimately contain spaces if an owner chooses).
			$code = trim( wp_strip_all_tags( $code ) );
			return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $code, 'UTF-8' ) : strtoupper( $code );
		}

		/**
		 * Load an ACTIVE coupon by its code. Returns null when the code is empty, unknown,
		 * or the coupon post is not published (inactive).
		 *
		 * @return RBFW_Coupon|null
		 */
		public static function load_by_code( $code ) {
			$code = self::normalize_code( $code );
			if ( '' === $code ) {
				return null;
			}

			$query = new WP_Query( array(
				'post_type'              => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'meta_query'             => array(
					array(
						'key'     => 'rbfw_code',
						'value'   => $code,
						'compare' => '=',
					),
				),
			) );

			if ( empty( $query->posts ) ) {
				return null;
			}

			return new self( (int) $query->posts[0] );
		}

		const AUTO_CACHE = 'rbfw_has_auto_coupons';

		/**
		 * Whether ANY active automatic rule exists. Cached, and used as a cheap short-circuit so
		 * stores without coupons never pay for building a coupon context on every cart
		 * recalculation / booking submit.
		 */
		public static function has_auto_coupons() {
			$cached = get_transient( self::AUTO_CACHE );
			if ( false !== $cached ) {
				return '1' === $cached;
			}

			$q = new WP_Query( array(
				'post_type'      => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => 'rbfw_auto_apply', 'value' => 'yes', 'compare' => '=' ),
				),
			) );

			$has = ! empty( $q->posts );
			set_transient( self::AUTO_CACHE, $has ? '1' : '0', HOUR_IN_SECONDS );
			return $has;
		}

		/** Safe as a direct call and as a `deleted_post` / `save_post_*` hook callback. */
		public static function flush_auto_cache( $post_id = 0, $post = null ) {
			if ( $post instanceof WP_Post && RBFW_Coupon_Post_Type::POST_TYPE !== $post->post_type ) {
				return;
			}
			delete_transient( self::AUTO_CACHE );
		}

		/**
		 * Every active automatic (no-code) coupon, ordered by priority (highest first).
		 *
		 * @return RBFW_Coupon[]
		 */
		public static function get_active_auto_coupons() {
			$query = new WP_Query( array(
				'post_type'              => RBFW_Coupon_Post_Type::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => 100,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'meta_query'             => array(
					array(
						'key'     => 'rbfw_auto_apply',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			) );

			$coupons = array();
			foreach ( $query->posts as $pid ) {
				$coupons[] = new self( (int) $pid );
			}

			// Priority DESC (higher wins); stable on ties.
			usort( $coupons, static function ( $a, $b ) {
				return $b->get_priority() <=> $a->get_priority();
			} );

			return $coupons;
		}

		/* -------------------------------------------------------------------------
		 * Identity
		 * ---------------------------------------------------------------------- */

		public function get_id() {
			return $this->id;
		}

		public function exists() {
			return $this->id > 0 && get_post_type( $this->id ) === RBFW_Coupon_Post_Type::POST_TYPE;
		}

		public function is_active() {
			return get_post_status( $this->id ) === 'publish';
		}

		public function get_code() {
			$code = $this->meta( 'rbfw_code' );
			if ( '' === $code ) {
				$code = self::normalize_code( get_the_title( $this->id ) );
			}
			return $code;
		}

		/* -------------------------------------------------------------------------
		 * Raw meta access (cached per instance)
		 * ---------------------------------------------------------------------- */

		public function meta( $key, $default = '' ) {
			if ( ! array_key_exists( $key, $this->meta ) ) {
				$val                = get_post_meta( $this->id, $key, true );
				$this->meta[ $key ] = ( '' === $val || null === $val ) ? $default : $val;
			}
			return $this->meta[ $key ];
		}

		protected function meta_array( $key ) {
			$val = $this->meta( $key, array() );
			return is_array( $val ) ? $val : array();
		}

		protected function meta_bool( $key ) {
			return 'yes' === $this->meta( $key, 'no' );
		}

		protected function meta_float( $key ) {
			return (float) $this->meta( $key, 0 );
		}

		protected function meta_int( $key ) {
			return (int) $this->meta( $key, 0 );
		}

		/* -------------------------------------------------------------------------
		 * Discount definition
		 * ---------------------------------------------------------------------- */

		/** @return string percentage|fixed|free_days */
		public function get_discount_type() {
			$type = $this->meta( 'rbfw_discount_type', 'percentage' );
			return in_array( $type, array( 'percentage', 'fixed', 'free_days' ), true ) ? $type : 'percentage';
		}

		public function get_discount_value() {
			return max( 0, $this->meta_float( 'rbfw_discount_value' ) );
		}

		public function get_max_discount() {
			return max( 0, $this->meta_float( 'rbfw_max_discount' ) );
		}

		public function is_auto_apply() {
			return $this->meta_bool( 'rbfw_auto_apply' );
		}

		public function get_priority() {
			return $this->meta_int( 'rbfw_priority' );
		}

		public function allows_combine() {
			return $this->meta_bool( 'rbfw_allow_combine' );
		}

		/* -------------------------------------------------------------------------
		 * Targeting (name-based rent types + item ids + location slugs)
		 * ---------------------------------------------------------------------- */

		public function get_target_items() {
			return array_map( 'absint', $this->meta_array( 'rbfw_target_items' ) );
		}

		public function get_exclude_items() {
			return array_map( 'absint', $this->meta_array( 'rbfw_exclude_items' ) );
		}

		public function get_target_rent_types() {
			return array_map( 'strval', $this->meta_array( 'rbfw_target_rent_types' ) );
		}

		public function get_exclude_rent_types() {
			return array_map( 'strval', $this->meta_array( 'rbfw_exclude_rent_types' ) );
		}

		public function get_target_locations() {
			return array_map( 'strval', $this->meta_array( 'rbfw_target_locations' ) );
		}

		public function get_exclude_locations() {
			return array_map( 'strval', $this->meta_array( 'rbfw_exclude_locations' ) );
		}

		/** True when the coupon has no include filters → applies to every rental line. */
		public function targets_everything() {
			return ! $this->get_target_items()
				&& ! $this->get_target_rent_types()
				&& ! $this->get_target_locations();
		}

		/* -------------------------------------------------------------------------
		 * Spend + date rules
		 * ---------------------------------------------------------------------- */

		public function get_min_amount() {
			return max( 0, $this->meta_float( 'rbfw_min_amount' ) );
		}

		public function get_max_amount() {
			return max( 0, $this->meta_float( 'rbfw_max_amount' ) );
		}

		public function get_valid_from() {
			return $this->meta( 'rbfw_valid_from', '' );
		}

		public function get_valid_to() {
			return $this->meta( 'rbfw_valid_to', '' );
		}

		/** @return int[] 0-6 (Sun-Sat). Empty = every weekday allowed. */
		public function get_weekdays() {
			return array_map( 'absint', $this->meta_array( 'rbfw_weekdays' ) );
		}

		/** @return string[] Y-m-d blackout dates. */
		public function get_blackout_dates() {
			return array_map( 'strval', $this->meta_array( 'rbfw_blackout_dates' ) );
		}

		/* -------------------------------------------------------------------------
		 * Usage limits + eligibility
		 * ---------------------------------------------------------------------- */

		public function get_usage_limit() {
			return max( 0, $this->meta_int( 'rbfw_usage_limit' ) );
		}

		public function get_usage_limit_per_user() {
			return max( 0, $this->meta_int( 'rbfw_usage_limit_per_user' ) );
		}

		public function get_usage_limit_per_day() {
			return max( 0, $this->meta_int( 'rbfw_usage_limit_per_day' ) );
		}

		public function get_allowed_roles() {
			return array_map( 'strval', $this->meta_array( 'rbfw_allowed_roles' ) );
		}

		public function get_allowed_emails() {
			return array_map( 'strtolower', array_map( 'strval', $this->meta_array( 'rbfw_allowed_emails' ) ) );
		}

		public function is_first_booking_only() {
			return $this->meta_bool( 'rbfw_first_booking_only' );
		}

		public function get_usage_count() {
			return $this->meta_int( 'rbfw_usage_count' );
		}
	}
}
