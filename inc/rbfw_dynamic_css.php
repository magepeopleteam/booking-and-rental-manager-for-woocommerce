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

add_action('wp_head','rbfw_dynamic_css');
function rbfw_dynamic_css(){
	global $rbfw;
	$primary_color   = $rbfw->get_option('rbfw_primary_color', 'rbfw_basic_style_settings');
	$secondary_color = $rbfw->get_option('rbfw_secondary_color', 'rbfw_basic_style_settings');
	$single_column_bg = $rbfw->get_option('rbfw_single_rent_column_bg', 'rbfw_basic_style_settings');
	?>
	<style>
	<?php 
	if($primary_color):
	?>
	.mp_right_section button.rbfw-book-now-btn,
	.rbfw-post-sharing a:hover,
	.mpStyle a.rbfw-related-product-btn:hover,
	.rbfw-toggle-btn:hover,
	.icon-arrow i:hover,
	input:checked+.slider,
	div.superSlider .iconIndicator:hover,
	.rbfw-related-products-wrapper .owl-carousel .owl-nav button:hover
	{
		background: <?php echo esc_html($primary_color); ?>;
	}
	.rbfw-tab-menu ul li a.active-a,
	.rbfw-tab-menu ul li a:hover{
		color: <?php echo esc_html($primary_color); ?>;
	}
	.tippy-box[data-theme~='blue'] {
		background-color: <?php echo esc_html($primary_color); ?>;
		color: #fff;
	}
	.tippy-box[data-theme~='blue'][data-placement^='top']>.tippy-arrow::before {
		border-top-color: <?php echo esc_html($primary_color); ?>;
	}

	.tippy-box[data-theme~='blue'][data-placement^='bottom']>.tippy-arrow::before {
		border-bottom-color: <?php echo esc_html($primary_color); ?>;
	}

	.tippy-box[data-theme~='blue'][data-placement^='left']>.tippy-arrow::before {
		border-left-color: <?php echo esc_html($primary_color); ?>;
	}

	.tippy-box[data-theme~='blue'][data-placement^='right']>.tippy-arrow::before {
		border-right-color: <?php echo esc_html($primary_color); ?>;
	}
	<?php
	endif;
	if($secondary_color):
	?>
	.rbfw-tab-menu ul,
	.rbfw-sub-heading,
	.pricing-content,
	.rbfw-datetime input,
	.rbfw-datetime select,
	.rbfw-location select,
	.rbfw-costing,
	.rbfw-duration .item-content,
	#rbfw_faq_accordion .rbfw_faq_header,
	.rbfw-post-sharing a,
	.mpStyle a.rbfw-related-product-btn,
	.rbfw-related-products-wrapper .owl-carousel .owl-nav button
	{
		background: <?php echo esc_html($secondary_color); ?>;
		border-color: <?php echo esc_html($secondary_color); ?>;
	}
	div.superSlider .iconIndicator{
		color: <?php echo esc_html($secondary_color); ?>;
	}
	#rbfw_faq_accordion .rbfw_faq_content_wrapper
	{
		border-color: <?php echo esc_html($secondary_color); ?>;
	}	
	<?php
	endif;
	if($single_column_bg):
	?>
	div.mp_left_section, 
	div.mp_right_section
	{
		background: <?php echo esc_html($single_column_bg); ?>;
	}	
	<?php
	endif;	
	?>	
	</style>
	<?php
}