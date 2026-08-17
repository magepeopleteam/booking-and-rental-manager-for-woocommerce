<?php
	/**
	 * Gravity Forms integration — module bootstrap.
	 *
	 * Lets an admin attach any Gravity Form to any rental item. The customer
	 * answers it before booking, and their answers travel with the booking into
	 * the cart, the order, the booking record, the confirmation emails and the
	 * customer's booking view.
	 *
	 * The whole module is inert unless Gravity Forms is active, so this loads
	 * unconditionally alongside the rest of the plugin and costs one class_exists
	 * check on sites that do not use it.
	 *
	 * Nothing here is bound to a particular form: the admin dropdown is built from
	 * GFAPI::get_forms() on every render, and the Gravity Forms hooks are the
	 * un-suffixed, all-forms variants.
	 *
	 * @since 2.7.6
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	/* Per-item meta keys. Underscore-prefixed so they stay out of the generic
	 * custom-fields box on the classic edit screen. */
	if ( ! defined( 'RBFW_GF_META_FORM' ) ) {
		define( 'RBFW_GF_META_FORM', '_rbfw_gf_form_id' );
		define( 'RBFW_GF_META_MODE', '_rbfw_gf_mode' );
		define( 'RBFW_GF_META_TITLE', '_rbfw_gf_section_title' );
	}

	if ( ! defined( 'RBFW_GF_ASSET_VER' ) ) {
		// Tied to the plugin version so a plugin update busts the asset cache.
		define( 'RBFW_GF_ASSET_VER', defined( 'RBFW_VERSION' ) ? RBFW_VERSION : '2.7.5' );
	}

	if ( ! class_exists( 'RBFW_GF_Bridge' ) ) {

		final class RBFW_GF_Bridge {

			/** @var RBFW_GF_Bridge|null */
			private static $instance = null;

			public static function instance(): RBFW_GF_Bridge {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}

				return self::$instance;
			}

			private function __construct() {
				// plugins_loaded at 20 so Gravity Forms has certainly declared its
				// classes, however the two plugins happen to be ordered on disk.
				add_action( 'plugins_loaded', array( $this, 'boot' ), 20 );
			}

			/**
			 * Gravity Forms is checked at runtime rather than once on activation:
			 * a site can deactivate it at any time, and rental booking has to keep
			 * working exactly as it did before when that happens.
			 */
			public function boot(): void {
				if ( ! self::gravity_forms_active() ) {
					return;
				}

				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/rbfw_gf_functions.php';
				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Entry_Store.php';
				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Frontend.php';
				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Mapper.php';
				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Standalone.php';
				require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Booking_Creator.php';

				new RBFW_GF_Entry_Store();
				new RBFW_GF_Frontend();

				// Both checkout paths are wired unconditionally. The mapper binds
				// only to woocommerce_* hooks, which simply never fire when
				// WooCommerce is inactive; the standalone class binds only to the
				// native checkout AJAX action and its booking-created hook. So the
				// answers survive in either mode, including a site that switches
				// between them, without this module having to detect the mode.
				new RBFW_GF_Mapper();
				new RBFW_GF_Standalone();

				// "This form IS the order form" mode. Runs in both admin and
				// frontend contexts because a Gravity submission can be processed
				// from either (front-end submit, or admin-side entry creation).
				new RBFW_GF_Booking_Creator();

				if ( is_admin() ) {
					require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Admin.php';
					require_once RBFW_PLUGIN_DIR . '/inc/gravityforms/RBFW_GF_Booking_Details.php';
					new RBFW_GF_Admin();
					new RBFW_GF_Booking_Details();
				}
			}

			public static function gravity_forms_active(): bool {
				return class_exists( 'GFAPI' ) && class_exists( 'GFCommon' ) && function_exists( 'gform_update_meta' );
			}
		}

		RBFW_GF_Bridge::instance();
	}
