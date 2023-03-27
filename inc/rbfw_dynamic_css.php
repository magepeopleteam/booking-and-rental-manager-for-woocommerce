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
	$rent_list_base_color = $rbfw->get_option('rbfw_rent_list_base_color', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_1 = $rbfw->get_option('rbfw_single_page_base_color_1', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_2 = $rbfw->get_option('rbfw_single_page_base_color_2', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_3 = $rbfw->get_option('rbfw_single_page_base_color_3', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_4 = $rbfw->get_option('rbfw_single_page_base_color_4', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_5 = $rbfw->get_option('rbfw_single_page_base_color_5', 'rbfw_basic_style_settings');
	$rbfw_single_page_base_color_6 = $rbfw->get_option('rbfw_single_page_base_color_6', 'rbfw_basic_style_settings');
	?>
	<style>
	<?php if(!empty($rent_list_base_color)): ?>
		:root {
			--rbfw_rent_list_color1: <?php echo $rent_list_base_color; ?>;
		}
	<?php endif; ?>
	<?php if(!empty($rbfw_single_page_base_color_1)): ?>
		:root {
			--rbfw_dt_color1: <?php echo $rbfw_single_page_base_color_1; ?>;
			--rbfw_muff_color2: <?php echo $rbfw_single_page_base_color_1; ?>;
		}
	<?php endif; ?>	
	<?php if(!empty($rbfw_single_page_base_color_2)): ?>
		:root {
			--rbfw_dt_color7: <?php echo $rbfw_single_page_base_color_2; ?>;
		}
	<?php endif; ?>	
	<?php if(!empty($rbfw_single_page_base_color_3)): ?>
		:root {
			--rbfw_dt_color9: <?php echo $rbfw_single_page_base_color_3; ?>;
		}
	<?php endif; ?>
	<?php if(!empty($rbfw_single_page_base_color_4)): ?>
		:root {
			--rbfw_single_page_base_color4: <?php echo $rbfw_single_page_base_color_4; ?>;
		}
	<?php endif; ?>
	<?php if(!empty($rbfw_single_page_base_color_5)): ?>
		:root {
			--rbfw_muff_color3: <?php echo $rbfw_single_page_base_color_5; ?>;
		}
	<?php endif; ?>
	<?php if(!empty($rbfw_single_page_base_color_6)): ?>
		:root {
			--rbfw_muff_color7: <?php echo $rbfw_single_page_base_color_6; ?>;
		}
		.ui-datepicker table thead{
			background-color: <?php echo rbfw_hex2rgba($rbfw_single_page_base_color_6, 0.7); ?>;
		}
	<?php endif; ?>
	</style>
	<?php
}