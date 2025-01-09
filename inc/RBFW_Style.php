<?php
	/*
	* @Author 		magePeople
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RBFW_Style' ) ) {
		class RBFW_Style {
			public function __construct() {
				add_action( 'wp_head', array( $this, 'rbfw_style' ), 100 );
				add_action( 'admin_head', array( $this, 'rbfw_style' ), 100 );
			}
			
			public function rbfw_style() {
				$default_color   = RBFW_Function::get_style_settings( 'rbfw_default_text_color', '#000' );
				$theme_color     = RBFW_Function::get_style_settings( 'rbfw_theme_color', '#F12971' );
				$alternate_color = RBFW_Function::get_style_settings( 'rbfw_theme_alternate_color', '#fff' );
				$warning_color   = RBFW_Function::get_style_settings( 'rbfw_warning_color', '#E67C30' );
				
				$default_fs   = RBFW_Function::get_style_settings( 'rbfw_default_font_size', '14' ) . 'px';
				$fs_h1        = RBFW_Function::get_style_settings( 'rbfw_font_size_h1', '35' ) . 'px';
				$fs_h2        = RBFW_Function::get_style_settings( 'rbfw_font_size_h2', '30' ) . 'px';
				$fs_h3        = RBFW_Function::get_style_settings( 'rbfw_font_size_h3', '28' ) . 'px';
				$fs_h4        = RBFW_Function::get_style_settings( 'rbfw_font_size_h4', '24' ) . 'px';
				$fs_h5        = RBFW_Function::get_style_settings( 'rbfw_font_size_h5', '20' ) . 'px';
				$fs_h6        = RBFW_Function::get_style_settings( 'rbfw_font_size_h6', '18' ) . 'px';
				$fs_label     = RBFW_Function::get_style_settings( 'rbfw_font_size_label', '14' ) . 'px';
				$button_fs    = RBFW_Function::get_style_settings( 'rbfw_font_size_button', '14' ) . 'px';
				$button_color = RBFW_Function::get_style_settings( 'rbfw_button_color', $alternate_color );
				$button_bg    = RBFW_Function::get_style_settings( 'rbfw_button_bg', '#ea8125' );
				$section_bg    = RBFW_Function::get_style_settings( 'rbfw_section_bg', '#FAFCFE' );

				?>
				<style>
					:root {
						/*--font-family: "Poppins", sans-serif;*/
						--default-color: <?php echo esc_attr($default_color); ?>;
						--container-max-width: 1320px;
						--default-mp: 20px;
						--default-mp_negetive: -20px;
						--default-mp-xs: 10px;
						--default-mp-xs_negative: -10px;
						--default-border-radious: 5px;
						--medium-border-radious: 10px;
						--border_color: #DDD;
						--active_color: #0E6BB7;
						--default-bg: #FFF;
						--theme-color: <?php echo esc_attr($theme_color); ?>;
						--theme-color_ee: <?php echo esc_attr($theme_color).'ee'; ?>;
						--theme-color_cc: <?php echo esc_attr($theme_color).'cc'; ?>;
						--theme-color_aa: <?php echo esc_attr($theme_color).'aa'; ?>;
						--theme-color_88: <?php echo esc_attr($theme_color).'88'; ?>;
						--theme-color_77: <?php echo esc_attr($theme_color).'77'; ?>;
						--theme-alternate-color: <?php echo esc_attr($alternate_color); ?>;
						--default-content-shadow: 0 0 2px #665F5F7A;
						--content_bg: <?php echo esc_attr($section_bg); ?>;
					}
					/*****Header********/
					:root {
						--header-bg: #FFF;
						--header-color: #333;
						--header-padding: 20px 0;
						--header-fs: 16px;
						--header-shadows: none;
					}
					/******Navbar*******/
					:root {
						--nav-font-size: 18px;
						--nav-font-weight: 400;
						--nav-color: #FFF;
						--nav-padding: 0;
						--nav-margin: 0;
						--nav-border: 1px solid #DDD;
						--nav-active-color: #BCB;
						--nav-bg: #6148BA;
						--nav-content-shadow: none;
					}
					/*****Button********/
					:root {
						--button-bg: <?php echo esc_attr($button_bg); ?>;
						--button-color: <?php echo esc_attr($button_color); ?>;
						--button-fs: <?php echo esc_attr($button_fs); ?>;
						--button-height: 40px;
						--button-height-xs: 30px;
						--button-width: 120px;
						--button-shadows: 0 8px 12px rgb(51 65 80 / 6%), 0 14px 44px rgb(51 65 80 / 11%);
					}
					/*****Font size********/
					:root {
						--fs: <?php echo esc_attr($default_fs); ?>;
						--fw: normal;
						--fs_small: 12px;
						--font-size-label: <?php echo esc_attr($fs_label); ?>;
						--font-size-h6: <?php echo esc_attr($fs_h6); ?>;
						--font-size-h5: <?php echo esc_attr($fs_h5); ?>;
						--font-size-h4: <?php echo esc_attr($fs_h4); ?>;
						--font-size-h3: <?php echo esc_attr($fs_h3); ?>;
						--font-size-h2: <?php echo esc_attr($fs_h2); ?>;
						--font-size-h1: <?php echo esc_attr($fs_h1); ?>;
						--fw-thin: 400; /*font weight medium*/
						--fw-medium: 600; /*font weight medium*/
						--fw-bold: bold; /*font weight bold*/
					}
					/*****Section********/
					:root {
						--section-bg: #F2F2F2;
						--section-padding: 30px 0;
						--section-margin: 0;
						--left-sidebar-width: 280px;
						--main-content-width: calc(100% - 300px);
						--right-sidebar-width: 300px;
						--sidebar-bg: #FAFCFE;
					}
					/******Footer*******/
					:root {
						--footer-bg: #FFF;
						--footer-padding: 0;
						--footer-fs: 16px;
						--footer-color: #FFF;
						--footer-top-bg: tranparent;
						--footer-top-color: #555;
						--footer-top-padding: 50px 0;
						--footer-bottom-bg: #2C3E50;
						--footer-bottom-color: #FFF;
						--footer-bottom-padding: 15px 0;
					}
					/*******Color***********/
					:root {
						--warning_color: <?php echo esc_attr($warning_color); ?>;
						--info-bg: #F2F2F2;
						--success-color: #006607;
						--danger-color: #C00;
						--required-color: #C00;
						--light-color: #F2F2F2;
						--yellow-color: #FEBB02;
						--blue-color: #815DF2;
						--navy-blue-color: #007CBA;
						--color_1: #0C5460;
					}
					/*****Click Slider******/
					:root {
						--click-slide-bg: #FFF;
					}
					@media only screen and (max-width: 1100px) {
						:root {
							--fs: 14px;
							--fs_small: 12px;
							--font-size-label: 15px;
							--font-size-h4: 20px;
							--font-size-h3: 22px;
							--font-size-h2: 25px;
							--font-size-h1: 30px;
							
							--default-mp: 16px;
							--default-mp_negetive: -16px;
							--default-mp-xs: 8px;
							--default-mp-xs_negative: -8px;
						}
					}
					@media only screen and (max-width: 800px) {
						/*:root {*/
						/*	--default-mp: 10px;*/
						/*	--default-mp-xs: 5px;*/
						/*	--default-mp-xs_negative: -5px;*/
						/*}*/
					}
					@media only screen and (max-width: 500px) {
						:root {
							--fs: 12px;
							--fs_small: 10px;
							--font-size-label: 13px;
							--font-size-h6: 15px;
							--font-size-h5: 16px;
							--font-size-h4: 18px;
							--font-size-h3: 20px;
							--font-size-h2: 22px;
							--font-size-h1: 24px;
						}
					}
				</style>
				<?php
			}
			
		}
		
		new RBFW_Style();
	}