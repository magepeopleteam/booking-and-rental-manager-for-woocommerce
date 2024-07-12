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
					$template_path = RBFW_Function::check_template_path('single/');
					$single_template = $template_path.'single-rbfw.php';
				}
				return $single_template;
			}

			public static function get_slider_images($post_id) {
				$rbfw_gallery_images = get_post_meta(get_the_ID(),'rbfw_gallery_images');
				$gallery_images = $rbfw_gallery_images[0];
				return $gallery_images;
			}

			public static function get_feature_categories($post_id) {
				$rbfw_gallery_images = get_post_meta(get_the_ID(),'rbfw_gallery_images');
				$gallery_images = $rbfw_gallery_images[0];
				return $gallery_images;
			}

			public static function load_template($post_id) {

				$template_name = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
				$template_name = strtolower($template_name);
				switch($rent_type){
					case 'bike_car_sd':
					case 'appointment':
						$file_name = 'single-day.php';
					break;
					case 'bike_car_md':
					case 'equipment':
					case 'dress':
					case 'others':
						$file_name = 'multi-day.php';
					break;
					case 'resort':
						$file_name = 'resort.php';
					break;
					default:
						$file_name = 'multi-day.php';
				}

				$template_path = 'single/'. $template_name.'/'.$file_name;
				$template_path = RBFW_Function::check_template_path($template_path);
				
				include( $template_path );
			}

			public function load_template_parts($file){	
				$file_name 	   = $file.'.php';
				$template_name = !empty(get_post_meta(get_the_ID(), 'rbfw_single_template', true)) ? get_post_meta(get_the_ID(), 'rbfw_single_template', true) : 'Default';
				$template_name = strtolower($template_name);
				$template_path =  'single/'. $template_name.'/views/'.$file_name;
				$template_path =  RBFW_Function::check_template_path($template_path);
				return $template_path;
			}
		}
		new RBFW_Frontend();
	}