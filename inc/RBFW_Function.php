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

			/**
			 * Whether the WooCommerce cart/checkout should own bookings.
			 *
			 * This is the "Enable WooCommerce Payment" toggle on the Payments settings
			 * tab (option rbfw_payment_settings). It defaults to 'on' so existing
			 * installs keep using WooCommerce, and is the authoritative signal that
			 * WooCommerce — rather than the standalone / custom (Pro) payment flow —
			 * handles checkout. When it is off, WooCommerce never owns the booking even
			 * though WooCommerce itself may still be active.
			 */
			public static function wc_payment_enabled(): bool {
				return self::get_settings( 'rbfw_enable_wc_payment', 'rbfw_payment_settings', 'on' ) !== 'off';
			}

			/**
			 * Active booking mode: 'woocommerce' | 'standalone'.
			 *
			 * WooCommerce mode requires all of: WooCommerce active, the "Enable
			 * WooCommerce Payment" toggle on, and the legacy Booking Mode radio not set
			 * to standalone. Either switch flips the whole plugin to the standalone /
			 * custom (Pro) payment flow — so the two payment systems can never both
			 * think they own the same booking. Falls back to 'standalone' whenever
			 * WooCommerce is not active so the plugin never assumes WooCommerce exists.
			 */
			public static function booking_mode(): string {
				if ( ! self::has_woocommerce() || ! self::wc_payment_enabled() ) {
					return 'standalone';
				}
				$mode = self::get_settings( 'rbfw_booking_mode', 'rbfw_basic_payment_settings', 'woocommerce' );
				return ( $mode === 'standalone' ) ? 'standalone' : 'woocommerce';
			}

			/**
			 * Whether the WooCommerce cart/checkout/order flow should be used for bookings.
			 *
			 * True only when WooCommerce is active AND the admin has selected WooCommerce
			 * mode. Otherwise bookings use the native (standalone) flow.
			 */
			public static function use_wc(): bool {
				return self::has_woocommerce() && self::booking_mode() === 'woocommerce';
			}

			/**
			 * Whether the free plugin can actually complete a booking in this request.
			 *
			 * A working checkout path must exist for the *current* mode, not merely a
			 * plugin being active:
			 *  - WooCommerce mode: WooCommerce owns checkout, so it is always available.
			 *  - Standalone mode: at least one Pro custom payment method (PayPal / Stripe
			 *    / Offline) must be enabled — WooCommerce being active but with its
			 *    payment disabled, or Pro active with no gateway enabled, is NOT enough.
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
			 * Whether the Pro plugin has at least one custom payment method enabled.
			 *
			 * The free plugin never references Pro classes directly: when Pro is active
			 * it exposes its enabled gateways/offline method via the
			 * `rbfw_pro_enabled_payment_methods` filter. Without Pro the filter is never
			 * added, so this is false — there is no standalone checkout to take payment.
			 */
			public static function has_enabled_custom_payment(): bool {
				if ( ! ( function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active() ) ) {
					return false;
				}
				$methods = apply_filters( 'rbfw_pro_enabled_payment_methods', array() );
				return ! empty( $methods );
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