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
				add_action('rbfw_template_pricing', array($this,'pricing'));
				add_action('rbfw_template_slider', array($this,'slider'));
			}

			public function pricing(){
				include( RBFW_Frontend::load_template_parts('pricing'));
			}

			public function slider(){
				include( RBFW_Frontend::load_template_parts('slider'));
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

				$template_path = get_stylesheet_directory().'/templates/single/'. $template_name.'/'.$file_name;
				$default_path = RBFW_TEMPLATE_PATH .'single/'. $template_name.'/'.$file_name;
				$load_template = file_exists( $template_path )? $template_path : $default_path;
				
				include( RBFW_Frontend::load_template_parts('header') );
				include( $load_template );
			}


			public static function load_template_parts($file_name) {

				$file_name = $file_name.'.php';

				$template_name = !empty(get_post_meta(get_the_ID(), 'rbfw_single_template', true)) ? get_post_meta(get_the_ID(), 'rbfw_single_template', true) : 'Default';
				$template_name = strtolower($template_name);

				$template_path = get_stylesheet_directory().'/templates/single/'. $template_name.'/parts/'.$file_name;
				$default_path = RBFW_TEMPLATE_PATH .'single/'. $template_name.'/parts/'.$file_name;
			
				$path = file_exists( $template_path )? $template_path : $default_path;
				return $path;
			}


		}
		new RBFW_Frontend();
	}