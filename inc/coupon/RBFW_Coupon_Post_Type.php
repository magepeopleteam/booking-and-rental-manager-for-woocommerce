<?php
/**
 * Registers the `rbfw_coupon` custom post type that stores each coupon / automatic
 * discount rule for the unified coupon engine.
 *
 * The engine works identically in WooCommerce and Standalone modes, so this CPT (and
 * the whole engine) loads in EVERY execution context — do not gate registration on
 * is_admin(). It is hidden from the default admin menu (show_in_menu => false); coupons
 * are managed through the custom "Coupons" page under the Rental menu (RBFW_Coupon_Admin).
 *
 * Storage philosophy mirrors rbfw_booking: CPT + flat rbfw_* post meta, no custom tables.
 * The coupon code lives both in post_title (human-readable, admin list) and in the
 * normalized meta `rbfw_code` (uppercased, trimmed) which is what lookups query against.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Post_Type' ) ) {
	class RBFW_Coupon_Post_Type {

		const POST_TYPE = 'rbfw_coupon';

		public function __construct() {
			add_action( 'init', array( $this, 'register' ) );
		}

		public function register() {
			$labels = array(
				'name'          => esc_html__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				'singular_name' => esc_html__( 'Coupon', 'booking-and-rental-manager-for-woocommerce' ),
				'menu_name'     => esc_html__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				'all_items'     => esc_html__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				'edit_item'     => esc_html__( 'Edit Coupon', 'booking-and-rental-manager-for-woocommerce' ),
				'search_items'  => esc_html__( 'Search Coupons', 'booking-and-rental-manager-for-woocommerce' ),
				'not_found'     => esc_html__( 'No coupons found', 'booking-and-rental-manager-for-woocommerce' ),
			);

			$args = array(
				'labels'              => $labels,
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => true,
				'capability_type'     => 'post',
			);

			register_post_type( self::POST_TYPE, $args );
		}
	}

	new RBFW_Coupon_Post_Type();
}
