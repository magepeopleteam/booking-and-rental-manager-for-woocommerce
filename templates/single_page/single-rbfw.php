<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	get_header();
	the_post();
	$post_id = get_the_id();
	do_action('rbfw_single_page_before_wrapper');
	if ( post_password_required() ) {
		echo get_the_password_form(); // WPCS: XSS ok.
	} else {
		do_action( 'woocommerce_before_single_product' );
		include_once( RBFW_Function::details_template_path() );
	}
	do_action('rbfw_single_page_after_wrapper');
	do_action('rbfw_single_page_footer',$post_id);
	get_footer();