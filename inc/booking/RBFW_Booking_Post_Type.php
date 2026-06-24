<?php
/**
 * Registers the `rbfw_booking` custom post type used to persist native (standalone)
 * bookings created without WooCommerce.
 *
 * Like rbfw_item, this MUST register in every execution context (web, cron, WP-CLI) so
 * rewrite rules and the admin list stay consistent — do not gate registration on is_admin().
 * It is hidden from the main admin menu (show_in_menu => false) and surfaced under the
 * Rental menu / settings.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Post_Type' ) ) {
	class RBFW_Booking_Post_Type {

		const POST_TYPE = 'rbfw_booking';

		public function __construct() {
			add_action( 'init', array( $this, 'register' ) );
		}

		public function register() {
			$labels = array(
				'name'          => esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				'singular_name' => esc_html__( 'Booking', 'booking-and-rental-manager-for-woocommerce' ),
				'menu_name'     => esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				'all_items'     => esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				'edit_item'     => esc_html__( 'View Booking', 'booking-and-rental-manager-for-woocommerce' ),
				'search_items'  => esc_html__( 'Search Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				'not_found'     => esc_html__( 'No bookings found', 'booking-and-rental-manager-for-woocommerce' ),
			);

			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_admin_bar'  => false,
				'show_in_nav_menus'  => false,
				'exclude_from_search'=> true,
				'has_archive'        => false,
				'rewrite'            => false,
				'query_var'          => false,
				'hierarchical'       => false,
				'supports'           => array( 'title' ),
				'map_meta_cap'       => true,
				'capability_type'    => 'post',
			);

			register_post_type( self::POST_TYPE, $args );
		}
	}
	new RBFW_Booking_Post_Type();
}
