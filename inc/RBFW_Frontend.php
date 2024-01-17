<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RBFW_Frontend' ) ) {
		class RBFW_Frontend {
			public function __construct() {
				add_filter( 'single_template', array( $this, 'load_single_template' ) );
			}

			public function load_single_template( $template ): string {
				global $post;

				if ( $post->post_type && $post->post_type == RBFW_Function::get_cpt_name() ) {
					$template = RBFW_Function::template_path( 'single_page/single-rbfw.php' );
				}

				return $template;
			}

		}
		new RBFW_Frontend();
	}