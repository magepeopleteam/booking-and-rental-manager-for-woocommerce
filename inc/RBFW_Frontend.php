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

			public static function get_template($post_id) {

				$template = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$template_path = get_template_directory().'/templates/'. $template.'.php';				
				if ( file_exists( $template_path ) ) {
					include($template_path);
				} elseif ( file_exists( RBFW_TEMPLATE_PATH . $template.'.php' )  ) {
					include( RBFW_TEMPLATE_PATH . $template . '.php' );
				} else {
					echo __( 'Sorry, No Template Found!', 'booking-and-rental-manager-for-woocommerce' );
				}
			}
			
		}
		new RBFW_Frontend();
	}