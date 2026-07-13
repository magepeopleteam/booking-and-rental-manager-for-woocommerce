<?php
/**
 * "Coupons" tab on the Rental global settings page.
 *
 * Registers the tab via rbfw_settings_sec_reg and its fields via rbfw_settings_field (the same
 * pattern RBFW_Payment_Settings uses). The section id IS the wp_option row name, so everything
 * lands in `rbfw_coupon_settings` — which is exactly what RBFW_Coupon_Engine::is_enabled() and
 * ::setting() read.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Coupon_Settings' ) ) :
	class RBFW_Coupon_Settings {

		const OPTION = 'rbfw_coupon_settings';

		public function __construct() {
			add_filter( 'rbfw_settings_sec_reg', array( $this, 'register_section' ), 16 );
			add_filter( 'rbfw_settings_field', array( $this, 'register_fields' ), 16 );
		}

		public function register_section( $sections ) {
			$sections[] = array(
				'id'    => self::OPTION,
				'title' => '<i class="fas fa-ticket"></i>' . esc_html__( 'Coupons', 'booking-and-rental-manager-for-woocommerce' ),
			);
			return $sections;
		}

		public function register_fields( $settings_fields ) {
			$manage_url = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_coupons' );

			$settings_fields[ self::OPTION ] = array(
				array(
					'name'    => 'rbfw_coupon_enable',
					'label'   => __( 'Enable Coupon Engine', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => __( 'Turn the coupon &amp; automatic-discount engine on. Works in both WooCommerce and Standalone booking modes.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'on',
				),
				array(
					'name'     => 'rbfw_coupon_manage_link',
					'label'    => __( 'Manage Coupons', 'booking-and-rental-manager-for-woocommerce' ),
					'type'     => 'html',
					'desc'     => '<a class="button button-primary" href="' . esc_url( $manage_url ) . '">'
						. esc_html__( 'Open Coupons Manager', 'booking-and-rental-manager-for-woocommerce' ) . '</a>'
						. '<p style="margin:8px 0 0;color:#6b7280;font-size:12px;">'
						. esc_html__( 'Create coupon codes and automatic discount rules, with per-rental targeting, date/spend conditions and usage limits.', 'booking-and-rental-manager-for-woocommerce' )
						. '</p>',
				),
				array(
					'name'    => 'rbfw_coupon_label',
					'label'   => __( 'Coupon Field Label', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => __( 'Shown above the coupon input on the booking form / cart.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'text',
					'default' => __( 'Have a coupon?', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_coupon_placeholder',
					'label'   => __( 'Coupon Field Placeholder', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'text',
					'default' => __( 'Enter coupon code', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_coupon_show_field',
					'label'   => __( 'Show Coupon Field', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => __( 'Where customers can enter a code. Automatic (no-code) discounts always apply regardless of this setting.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => __( 'Yes — show the coupon field', 'booking-and-rental-manager-for-woocommerce' ),
						'no'  => __( 'No — automatic discounts only', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'name'    => 'rbfw_coupon_default_combine',
					'label'   => __( 'Allow Stacking by Default', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => __( 'Pre-tick "Allow combining with other coupons" when creating a new coupon. Each coupon still controls its own stacking.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'off',
				),
			);

			return $settings_fields;
		}
	}

	new RBFW_Coupon_Settings();
endif;
