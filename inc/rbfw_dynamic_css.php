<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('rbfw_frontend_enqueue_scripts','rbfw_dynamic_css');
function rbfw_dynamic_css(){
	global $rbfw;
	$rent_list_base_color = $rbfw->get_option('rbfw_rent_list_base_color', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_4 = $rbfw->get_option('rbfw_single_page_base_color_4', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_5 = $rbfw->get_option('rbfw_single_page_base_color_5', 'rbfw_basic_style_settings');
	$rbfw_single_page_secondary_color = $rbfw->get_option('rbfw_single_page_secondary_color', 'rbfw_basic_style_settings');
	$rbfw_booking_form_bg_color = $rbfw->get_option('rbfw_booking_form_bg_color', 'rbfw_basic_style_settings');

	$rbfw_single_page_base_color_1 = $rbfw->get_option('rbfw_single_page_base_color_1', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_2 = $rbfw->get_option('rbfw_single_page_base_color_2', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_3 = $rbfw->get_option('rbfw_single_page_base_color_3', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_6 = $rbfw->get_option('rbfw_single_page_base_color_6', 'rbfw_basic_style_settings');
	$uidatepicker = rbfw_hex2rgba($rbfw_single_page_base_color_6, 0.7);
	
	$inline_css =  
	":root{
		--rbfw_rent_list_color1:{$rent_list_base_color};  
		--rbfw_single_page_base_color4:{$rbfw_single_page_base_color_4}; 
		--rbfw_muff_color3:{$rbfw_single_page_base_color_5};     
		--rbfw_single_page_secondary_color:{$rbfw_single_page_secondary_color};     
		--rbfw_booking_form_bg_color:{$rbfw_booking_form_bg_color};     
		--rbfw_dt_color1:{$rbfw_single_page_base_color_1};
		--rbfw_muff_color2:{$rbfw_single_page_base_color_1}; 
		--rbfw_dt_color7:{$rbfw_single_page_base_color_2};     
		--rbfw_dt_color9:{$rbfw_single_page_base_color_3};     
		     
		     
		--rbfw_muff_color7:{$rbfw_single_page_base_color_6};     
	}
	.ui-datepicker table thead{
		background-color:{$uidatepicker};
	}
	";
	// 
	wp_add_inline_style('rbfw-style', $inline_css);

}