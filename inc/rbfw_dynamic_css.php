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
	$rent_list_base_color = $rbfw->get_option_trans('rbfw_rent_list_base_color', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_4 = $rbfw->get_option_trans('rbfw_single_page_base_color_4', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_5 = $rbfw->get_option_trans('rbfw_single_page_base_color_5', 'rbfw_basic_style_settings');
	$rbfw_single_page_secondary_color = $rbfw->get_option_trans('rbfw_single_page_secondary_color', 'rbfw_basic_style_settings');
	$rbfw_booking_form_bg_color = $rbfw->get_option_trans('rbfw_booking_form_bg_color', 'rbfw_basic_style_settings');

	$rbfw_single_page_base_color_1 = $rbfw->get_option_trans('rbfw_single_page_base_color_1', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_2 = $rbfw->get_option_trans('rbfw_single_page_base_color_2', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_3 = $rbfw->get_option_trans('rbfw_single_page_base_color_3', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_6 = $rbfw->get_option_trans('rbfw_single_page_base_color_6', 'rbfw_basic_style_settings');
	
	$rent_list_base_color = $rent_list_base_color? $rent_list_base_color: '#ff3726';
	$rbfw_single_page_base_color_4 = $rbfw_single_page_base_color_4? $rbfw_single_page_base_color_4: '#000000';
	$rbfw_single_page_base_color_5 = $rbfw_single_page_base_color_5? $rbfw_single_page_base_color_5: '#ff3726';
	$rbfw_single_page_secondary_color = $rbfw_single_page_secondary_color? $rbfw_single_page_secondary_color: '#333';
	$rbfw_booking_form_bg_color = $rbfw_booking_form_bg_color? $rbfw_booking_form_bg_color: '#ecf0f4';
	$rbfw_single_page_base_color_1 = $rbfw_single_page_base_color_1? $rbfw_single_page_base_color_1: '#ffcd00';
	$rbfw_single_page_base_color_2 = $rbfw_single_page_base_color_2? $rbfw_single_page_base_color_2: '#074836';
	$rbfw_single_page_base_color_3 = $rbfw_single_page_base_color_3? $rbfw_single_page_base_color_3: '#6F1E51';
	$rbfw_single_page_base_color_6 = $rbfw_single_page_base_color_6? $rbfw_single_page_base_color_6: '#1ABC9C';

	$uidatepicker = rbfw_hex2rgba($rbfw_single_page_base_color_5, 0.1);

	$inline_css =  
	":root{
		--rbfw_rent_list_color1:{$rent_list_base_color};  
		--rbfw_single_page_base_color4:{$rbfw_single_page_base_color_4}; 
		--rbfw_color_primary:{$rbfw_single_page_base_color_5};     
		--rbfw_single_page_secondary_color:{$rbfw_single_page_secondary_color};     
		--rbfw_booking_form_bg_color:{$rbfw_booking_form_bg_color};     
		--rbfw_dt_color1:{$rbfw_single_page_base_color_1};
		--rbfw_muff_color2:{$rbfw_single_page_base_color_1}; 
		--rbfw_dt_color7:{$rbfw_single_page_base_color_2};     
		--rbfw_dt_color9:{$rbfw_single_page_base_color_3};     
		--rbfw_muff_color7:{$rbfw_single_page_base_color_6};     
		--rbfw_primary_opacity:{$uidatepicker};     
	}
	.ui-datepicker table thead{
		background-color:var(--rbfw_primary_opacity);
	}
	";
	// 
	wp_add_inline_style('rbfw-style', $inline_css);

}