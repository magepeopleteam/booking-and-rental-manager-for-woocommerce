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


			public static function get_submit_info( $key, $default = '' ) {

                if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }

                $data = isset($_POST[ $key ])?sanitize_text_field(wp_unslash($_POST[ $key ])): $default;

				return self::data_sanitize( $data );
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

			//****************feture lists***********//
		}
	}