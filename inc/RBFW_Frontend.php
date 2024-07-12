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
				add_action( 'rbfw_booking_form', array( $this, 'booking_form' ) );				
			}

			public function single_template($single_template) {
				global $post;
				if ( $post->post_type && $post->post_type == RBFW_Function::get_cpt_name() ){ 
					$template_path = RBFW_Function::check_template_path('single/');
					$single_template = $template_path.'single-rbfw.php';
				}
				return $single_template;
			}

			public static function load_template($post_id) {
				$rent_type_template = RBFW_Frontend::get_rent_type_template($post_id);
				$template_name = RBFW_Frontend::get_template_name($post_id);
				
				$template_path = 'single/'. $template_name .'/'.$rent_type_template.'.php';
				$template_path = RBFW_Function::check_template_path($template_path);

				include( $template_path );
			}

			public static function get_slider_images($post_id) {
				$gallery_images = get_post_meta($post_id,'rbfw_gallery_images');
				$gallery_images = $gallery_images[0];
				return $gallery_images;
			}

			public static function get_feature_list($post_id) {
				$feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
				return $feature_category;
			}

			public static function get_template_name($post_id) {
				$template_name = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$template_name = strtolower($template_name);
				return $template_name;
			}

			public static function get_rent_type($post_id) {
				$rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
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

			public static function booking_form($post_id) {
				$post_id = get_the_ID();
				$rent_type_template = RBFW_Frontend::get_rent_type_template($post_id);
				$template_name = RBFW_Frontend::get_template_name($post_id);
				$template_path = 'single/'. $template_name .'/views/booking/'.$rent_type_template.'-booking.php';
				$template_path = RBFW_Function::check_template_path($template_path);
				include( $template_path );
			}

			// public static function related_products($post_id){
			// 	$post_id = get_the_ID();
			// 	$template_name = RBFW_Frontend::get_template_name($post_id);
			// 	$path = 'single/'. $template_name .'/views/related.php';
			// 	$path = RBFW_Function::check_template_path($path);
			// 	include( $path );				
			// }
		}
		new RBFW_Frontend();
	}