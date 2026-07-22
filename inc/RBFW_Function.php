<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RBFW_Function' ) ) {
		class RBFW_Function {

			public static function get_post_info( $post_id, $key, $default = '' ) {
				$data = get_post_meta( $post_id, $key, true ) ?: $default;
				
				return self::data_sanitize( $data );
			}

			public static function data_sanitize( $array ) {
				if ( ! is_array( $array ) ) {
					return sanitize_text_field( $array );
				}

				foreach ( $array as $key => $value ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = self::data_sanitize( $value );
					} else {
						if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
							$array[ $key ] = sanitize_email( $value );
						} elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
							$array[ $key ] = esc_url_raw( $value );
						} else {
							$array[ $key ] = sanitize_text_field( $value );
						}
					}
				}

				return $array;
			}


			//***********Template********************//

			public static function get_all_template() {
				
				$template_path = RBFW_Function::get_template_path('single/');
				$template_path  = glob( $template_path . "*" );
				
				foreach ( $template_path as $template_dir ) {
					if(is_dir($template_dir)){
						$template_name = preg_replace("/[^a-zA-Z0-9]/", "",(ucfirst(basename($template_dir,''))));
						$template_lists[ $template_name ] = $template_name.' Template';
					}
					
				}
				foreach ( $template_lists as $key => $value ) {
					$templates[ $key ] = $value;
				}
				
				return apply_filters('rbfw_template_list', $templates );
			}

			public static function get_template_path($path='') {
				$theme_path = get_stylesheet_directory().'/templates/'.$path;
				$default_path = RBFW_TEMPLATE_PATH . $path;
				if (is_dir($theme_path)) {
					return $theme_path;
				} elseif (is_dir($default_path)) {
					return $default_path;
				} elseif(file_exists($theme_path)){
					return $theme_path;
				}elseif(file_exists($default_path)){
					return $default_path;
				}else{
					return $default_path;
				}
			}

			public static function get_template_file_url($path=''){
				$theme_path = get_stylesheet_directory_uri().'/templates/'.$path;
				$default_path = RBFW_PLUGIN_URL.'/templates/'. $path;
				if (is_dir($theme_path)) {
					return $theme_path;
				} elseif (is_dir($default_path)) {
					return $default_path;
				} elseif(file_exists($theme_path)){
					return $theme_path;
				}elseif(file_exists($default_path)){
					return $default_path;
				}else{
					return $default_path;
				}
			}

			//*******************************//
			public static function get_thumbnail( $post_id = '', $image_id = '', $size = 'full' ){
				return self::get_image_url( $post_id, $image_id, $size );
			}

			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
				if ($post_id) {
					$image_id = get_post_thumbnail_id($post_id);
					$image_id = $image_id ?: self::get_post_info($post_id, 'rbfw_list_thumbnail');
				}
				return wp_get_attachment_image_url($image_id, $size);
			}

			//*******************************//
			public static function get_faq( $rbfw_id ) {
				return self::get_post_info( $rbfw_id, 'mep_event_faq', array() );
			}

			//************************//
			public static function get_taxonomy( $name ) {
				return get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
			}

			//************************//
			public static function get_settings( $key, $option_name, $default = '' ) {
				$options = get_option( $option_name );

				return self::settings( $options, $key, $default );
			}

			public static function settings( $options, $key, $default = '' ) {
				if ( isset( $options[ $key ] ) && $options[ $key ] ) {
					$default = $options[ $key ];
				}

				return $default;
			}

			public static function get_general_settings( $key, $default = '' ) {
				$options = get_option( 'rbfw_basic_gen_settings' );

				return self::settings( $options, $key, $default );
			}

			public static function get_style_settings( $key, $default = '' ) {
				$options = get_option( 'rbfw_basic_style_settings' );

				return self::settings( $options, $key, $default );
			}

			public static function get_translation_settings( $key, $default = '' ) {
				$options = get_option( 'rbfw_basic_translation_settings' );

				return self::settings( $options, $key, $default );
			}

			public static function translation_settings( $key, $default = '' ) {
				$options = get_option( 'rbfw_basic_translation_settings' );
				echo esc_html( self::settings( $options, $key, $default ) );
			}

			//***************************//
			public static function get_cpt_name(): string {
				return 'rbfw_item';
			}

			//*********** WooCommerce optional / booking mode ***********//
			/**
			 * Whether WooCommerce is active in this request.
			 *
			 * Single source of truth for runtime branching. `rbfw_woo_install_check()`
			 * is kept for the installer UI (it also reports "Installed But Not Active"),
			 * but feature code should branch on this instead.
			 */
			public static function has_woocommerce(): bool {
				return class_exists( 'WooCommerce' );
			}

			/** Option + key the explicit Booking Mode choice is stored under. */
			const MODE_OPTION = 'rbfw_payment_settings';
			const MODE_KEY    = 'rbfw_booking_mode';

			/**
			 * Whether the Pro plugin is active. Pro provides the standalone custom
			 * payment gateways, so it is what makes a real WooCommerce-vs-Custom choice
			 * possible.
			 */
			public static function has_pro(): bool {
				return function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();
			}

			/**
			 * Which booking systems can actually process a booking right now. When only
			 * one side is available there is nothing to choose — the mode is simply
			 * whichever one can run (see booking_mode()).
			 *
			 * @return string 'both' | 'woocommerce_only' | 'custom_only' | 'none'
			 */
			public static function mode_availability(): string {
				$woo = self::has_woocommerce();
				$pro = self::has_pro();
				// The standalone/custom flow can run whenever the Pro gateways are active OR
				// the built-in free Offline method is enabled (Offline needs no online
				// processor, so it works in the free plugin on its own).
				$custom = $pro || self::offline_payment_enabled();
				// A real two-way choice (the mode switcher) still requires the Pro gateways;
				// otherwise the single available flow is auto-resolved by booking_mode().
				if ( $woo && $pro ) {
					return 'both';
				}
				if ( $woo ) {
					return 'woocommerce_only';
				}
				if ( $custom ) {
					return 'custom_only';
				}
				return 'none';
			}

			/** True only when a real choice exists and the admin hasn't made it yet. */
			public static function needs_mode_selection(): bool {
				return 'both' === self::mode_availability() && '' === self::stored_booking_mode();
			}

			/**
			 * The admin's explicit stored choice ('woocommerce'|'standalone'), or '' if
			 * never made. Transparently migrates the value from its two older homes so
			 * upgrading installs keep behaving exactly as before until the admin actively
			 * changes it.
			 *
			 * The legacy effective mode came from two independent signals: bookings were
			 * standalone if the "Enable WooCommerce Payment" checkbox
			 * (rbfw_payment_settings[rbfw_enable_wc_payment]) was off, OR if the "Booking
			 * Mode" radio (rbfw_basic_payment_settings[rbfw_booking_mode]) was set to
			 * standalone; otherwise WooCommerce. Migration reproduces exactly that so no
			 * upgrading site silently flips modes.
			 */
			public static function stored_booking_mode(): string {
				$opts = get_option( self::MODE_OPTION, array() );
				$opts = is_array( $opts ) ? $opts : array();

				if ( ! empty( $opts[ self::MODE_KEY ] ) && in_array( $opts[ self::MODE_KEY ], array( 'woocommerce', 'standalone' ), true ) ) {
					return $opts[ self::MODE_KEY ];
				}

				$legacy      = get_option( 'rbfw_basic_payment_settings', array() );
				$legacy      = is_array( $legacy ) ? $legacy : array();
				$has_toggle  = isset( $opts['rbfw_enable_wc_payment'] );
				$has_radio   = ! empty( $legacy['rbfw_booking_mode'] ) && in_array( $legacy['rbfw_booking_mode'], array( 'woocommerce', 'standalone' ), true );

				// Nothing was ever set — no explicit choice yet.
				if ( ! $has_toggle && ! $has_radio ) {
					return '';
				}

				// Reproduce the old effective mode: standalone if the toggle was off OR the
				// radio said standalone; WooCommerce otherwise.
				$toggle_off      = $has_toggle && 'off' === $opts['rbfw_enable_wc_payment'];
				$radio_standalone = $has_radio && 'standalone' === $legacy['rbfw_booking_mode'];
				$migrated        = ( $toggle_off || $radio_standalone ) ? 'standalone' : 'woocommerce';

				$opts[ self::MODE_KEY ] = $migrated;
				update_option( self::MODE_OPTION, $opts );
				return $migrated;
			}

			/**
			 * Persist an explicit mode choice and keep the legacy "Enable WooCommerce
			 * Payment" mirror in sync, so any older code still reading that flag agrees
			 * with booking_mode(). Only meaningful when mode_availability() === 'both'.
			 */
			public static function set_booking_mode( $mode ) {
				if ( ! in_array( $mode, array( 'woocommerce', 'standalone' ), true ) ) {
					return false;
				}
				$opts                           = get_option( self::MODE_OPTION, array() );
				$opts                           = is_array( $opts ) ? $opts : array();
				$opts[ self::MODE_KEY ]         = $mode;
				$opts['rbfw_enable_wc_payment'] = ( 'woocommerce' === $mode ) ? 'on' : 'off';
				return update_option( self::MODE_OPTION, $opts );
			}

			/**
			 * Active booking mode: 'woocommerce' | 'standalone'.
			 *
			 * Auto-resolves when only one system can run, so the two payment systems can
			 * never both think they own the same booking; falls back to the admin's
			 * stored choice (default WooCommerce) only when both are available.
			 */
			public static function booking_mode(): string {
				switch ( self::mode_availability() ) {
					case 'woocommerce_only':
						return 'woocommerce';
					case 'custom_only':
					case 'none':
						return 'standalone';
					case 'both':
					default:
						return 'standalone' === self::stored_booking_mode() ? 'standalone' : 'woocommerce';
				}
			}

			/**
			 * Retained for back-compat: whether the WooCommerce cart/checkout should own
			 * bookings. Now derived from the resolved booking mode.
			 */
			public static function wc_payment_enabled(): bool {
				return self::has_woocommerce() && 'woocommerce' === self::booking_mode();
			}

			/**
			 * Whether the WooCommerce cart/checkout/order flow should be used for bookings.
			 *
			 * True only when WooCommerce is active AND the resolved mode is WooCommerce.
			 * Otherwise bookings use the native (standalone) flow.
			 */
			public static function use_wc(): bool {
				return self::has_woocommerce() && 'woocommerce' === self::booking_mode();
			}

			/**
			 * Whether the free plugin can actually complete a booking in this request.
			 *
			 * A working checkout path must exist for the *current* mode, not merely a
			 * plugin being active:
			 *  - WooCommerce mode: WooCommerce owns checkout, so it is always available.
			 *  - Standalone mode: at least one custom payment method must be enabled — the
			 *    built-in free Offline method, or a Pro gateway (PayPal / Stripe). WooCommerce
			 *    being active but with its payment disabled, or no gateway enabled, is NOT enough.
			 *
			 * When this returns false, callers disable the "Book Now" button and explain
			 * why instead of letting the submit fail silently ("no payment method").
			 */
			public static function is_booking_available(): bool {
				if ( self::use_wc() ) {
					return true;
				}
				return self::has_enabled_custom_payment();
			}

			/**
			 * Whether the standalone flow has at least one usable custom payment method.
			 *
			 * Two independent sources qualify:
			 *  - The built-in free Offline method (see offline_payment_enabled()), which
			 *    needs no online processor — the native checkout records a pending booking
			 *    to be paid on pickup / by bank transfer.
			 *  - Any Pro custom gateway (PayPal / Stripe / …), exposed via the
			 *    `rbfw_pro_enabled_payment_methods` filter only when Pro is active.
			 */
			public static function has_enabled_custom_payment(): bool {
				if ( self::offline_payment_enabled() ) {
					return true;
				}
				if ( ! ( function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active() ) ) {
					return false;
				}
				$methods = apply_filters( 'rbfw_pro_enabled_payment_methods', array() );
				return ! empty( $methods );
			}

			/**
			 * Whether the built-in Offline payment method is enabled on the Payments tab.
			 *
			 * Offline is the one custom payment method that works in the free plugin: it
			 * requires no online gateway, so the native standalone checkout can complete a
			 * booking (as pending, paid on pickup / by transfer) without Pro. PayPal &
			 * Stripe remain Pro-only. Stored under rbfw_payment_settings[rbfw_offline_enable].
			 */
			public static function offline_payment_enabled(): bool {
				$opts = get_option( 'rbfw_payment_settings', array() );
				return is_array( $opts ) && isset( $opts['rbfw_offline_enable'] ) && 'on' === $opts['rbfw_offline_enable'];
			}

			/**
			 * Whether a customer must be logged in to place / view a booking.
			 *
			 * Scoped to the standalone (custom payment) flow: in WooCommerce mode,
			 * WooCommerce's own account / guest-checkout settings apply instead, so this
			 * returns false there. Controlled by the "Require Account Login" toggle on the
			 * Payments → Custom Payment tab (rbfw_payment_settings[rbfw_require_login]),
			 * default on.
			 */
			public static function login_required(): bool {
				if ( self::use_wc() ) {
					return false;
				}
				return self::get_settings( 'rbfw_require_login', 'rbfw_payment_settings', 'on' ) !== 'off';
			}

            public static function rbfw_rent_types( ) {
                $item_type = [
                    'bike_car_sd'     => __('Rent item for single day', 'booking-and-rental-manager-for-woocommerce'),
                    'bike_car_md'     => __('Rent item for multiple day', 'booking-and-rental-manager-for-woocommerce'),
                    'resort'          => __('Resort', 'booking-and-rental-manager-for-woocommerce'),
                    'equipment'       => __('Equipment', 'booking-and-rental-manager-for-woocommerce'),
                    'dress'           => __('Dress', 'booking-and-rental-manager-for-woocommerce'),
                    'appointment'     => __('Appointment', 'booking-and-rental-manager-for-woocommerce'),
                    'others'          => __('Others', 'booking-and-rental-manager-for-woocommerce'),
                    'multiple_items'  => __('Multiple day for multiple items', 'booking-and-rental-manager-for-woocommerce'),
                ];
                return $item_type;
            }

			//****************feture lists***********//
		}
	}