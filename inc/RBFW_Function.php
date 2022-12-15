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

			public static function data_sanitize( $data ) {
				$data = maybe_unserialize( $data );
				if ( is_string( $data ) ) {
					$data = maybe_unserialize( $data );
					if ( is_array( $data ) ) {
						$data = self::data_sanitize( $data );
					} else {
						$data = sanitize_text_field( $data );
					}
				} elseif ( is_array( $data ) ) {
					foreach ( $data as &$value ) {
						if ( is_array( $value ) ) {
							$value = self::data_sanitize( $value );
						} else {
							$value = sanitize_text_field( $value );
						}
					}
				}

				return $data;
			}

			public static function submit_sanitize( $key, $default = '' ) {
				$data = $_POST[ $key ] ?? $default;
				$data = stripslashes( strip_tags( $data ) );

				return self::data_sanitize( $data );
			}

			public static function get_submit_info( $key, $default = '' ) {
				$data = $_POST[ $key ] ?? $default;

				return self::data_sanitize( $data );
			}

			//***********Template********************//
			public static function all_details_template() {
				$template_path = get_stylesheet_directory() . '/rbfw_templates/themes/';
				$default_path  = RBFW_PLUGIN_DIR . '/templates/themes/';
				$dir           = is_dir( $template_path ) ? glob( $template_path . "*" ) : glob( $default_path . "*" );
				$names         = array();
				foreach ( $dir as $filename ) {
					if ( is_file( $filename ) ) {
						$file           = basename( $filename );
						$name           = str_replace( "?>", "", strip_tags( file_get_contents( $filename, false, null, 24, 16 ) ) );
						$names[ $file ] = $name;
					}
				}
				$name = [];
				foreach ( $names as $key => $value ) {
					$name[ $key ] = $value;
				}

				return apply_filters( 'rbfw_template_list_arr', $name );
			}

			public static function details_template_path(): string {
				$rent_id       	= get_the_id();
				$rent_type 		= get_post_meta( $rent_id, 'rbfw_item_type', true );

				if($rent_type == 'bike_car_sd'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike-car-sd.php' );
				}
				elseif($rent_type == 'bike_car_md'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike.php' );
				}
				elseif($rent_type == 'equipment'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike.php' );
				}
				elseif($rent_type == 'dress'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike.php' );
				}
				elseif($rent_type == 'resort'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'resort.php' );
				}
				elseif($rent_type == 'appointment'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike-car-sd.php' );
				}
				elseif($rent_type == 'others'){
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike.php' );
				}												
				else{
					$template_name = self::get_post_info( $rent_id, 'rbfw_theme_file', 'bike.php' );
				}
				
				$file_name     = 'themes/' . $template_name;
				$dir           = RBFW_PLUGIN_DIR . '/templates/' . $file_name;
				if ( ! file_exists( $dir ) ) {
					$file_name = 'No Template Found!';
				}

				return self::template_path( $file_name );
			}

			public static function template_path( $file_name ): string {
				$template_path = get_stylesheet_directory() . '/rbfw_templates/';
				$default_dir   = RBFW_PLUGIN_DIR . '/templates/';
				$dir           = is_dir( $template_path ) ? $template_path : $default_dir;
				$file_path     = $dir . $file_name;

				return locate_template( array( 'rbfw_templates/' . $file_name ) ) ? $file_path : $default_dir . $file_name;
			}

			//*******************************//
			public static function get_thumbnail( $post_id = '', $image_id = '', $size = 'full' ){
				return self::get_image_url( $post_id, $image_id, $size );
			}

			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
				if ( $post_id ) {
					$image_id = self::get_post_info( $post_id, 'rbfw_list_thumbnail' );
					$image_id = $image_id ?: get_post_thumbnail_id( $post_id );
				}
				$url = wp_get_attachment_image_url( $image_id, $size );
				if ( ! $url ) {
					$url = RBFW_PLUGIN_URL . '/assets/images/no_image.png';
				} else {
					if ( function_exists( 'fopen' ) && ini_get( 'allow_url_fopen' ) && ! @getimagesize( $url ) ) {
						$url = RBFW_PLUGIN_URL . '/assets/images/no_image.png';
					}
				}

				return $url;
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
				echo mep_esc_html( self::settings( $options, $key, $default ) );
			}

			//***************************//
			public static function get_cpt_name(): string {
				return 'rbfw_item';
			}
		}
	}