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
					$single_template = get_template_directory().'/templates/single-rbfw.php';
					if( ! file_exists($single_template)){
						$single_template =  RBFW_PLUGIN_DIR . '/templates/single-rbfw.php';
					}
				}
				return $single_template;
			}

			public static function load_template($post_id) {

				$template = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
				$template = strtolower($template);
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

				$theme_dir_path = get_template_directory().'/templates/single/'. $template.'/'.$file_name;
				$plugin_dir_path = RBFW_TEMPLATE_PATH .'single/'. $template.'/'.$file_name;

				if ( file_exists( $theme_dir_path ) ) {
					include($theme_dir_path);
				} elseif ( file_exists( $plugin_dir_path )  ) {
					include( $plugin_dir_path );
				} else {
					echo __( 'Sorry, No Template Found!', 'booking-and-rental-manager-for-woocommerce' );
				}
			}
			
		}
		new RBFW_Frontend();
	}