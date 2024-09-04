<?php
/**
 * @author shahadat <raselsha@gmail.com>
 * @version 2.0.5
 * @since 1.0.0
 */
	if ( ! defined( 'ABSPATH' ) ) die;
		
	if ( ! class_exists( 'RBFW_Frontend' ) ) {
		class RBFW_Frontend {
			
			public function __construct() {
				add_filter( 'single_template', array( $this, 'single_template' ) );						
			}

			public function single_template($single_template) {
				global $post;
				if ( $post->post_type && $post->post_type == RBFW_Function::get_cpt_name() ){ 
					$single_template = RBFW_Function::get_template_path('single/single-rbfw.php');
				}
				return $single_template;
			}

			public static function load_template($post_id) {
				$rent_type_template = RBFW_Frontend::get_rent_type_template($post_id);
				$template_name = RBFW_Frontend::get_template_name($post_id);

				$template_path = 'single/'. $template_name .'/'.$rent_type_template.'.php';
				$template_path = RBFW_Function::get_template_path($template_path);
				include( $template_path );
			}

			public static function get_template_name($post_id) {
				$template_name = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$template_name = strtolower($template_name);
				return $template_name;
			}

			public static function get_rent_type($post_id) {
				$rent_type = !empty(get_post_meta( $post_id, 'rbfw_item_type', true )) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				return $rent_type;
			}


			public static function get_rent_type_template($post_id) {

				$rent_type = RBFW_Frontend::get_rent_type($post_id);
				
				switch($rent_type){
					case 'bike_car_sd':
					case 'appointment':
						$file_name = 'single-day';
					break;
					case 'bike_car_md':
					case 'equipment':
					case 'dress':
					case 'others':
						$file_name = 'multi-day';
					break;
					case 'resort':
						$file_name = 'resort';
					break;
					default:
						$file_name = 'multi-day';
				}
				return $file_name;
			}
		}
		new RBFW_Frontend();
	}