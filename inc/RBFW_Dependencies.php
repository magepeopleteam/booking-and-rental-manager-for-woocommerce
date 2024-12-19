<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	
	if ( ! class_exists( 'RBFW_Dependencies' ) ) {
		class RBFW_Dependencies {

			protected $version;

			public function __construct() {

				add_action('admin_enqueue_scripts', array( $this, 'rbfw_add_admin_scripts' ), 10, 1);
				add_action('wp_enqueue_scripts', array( $this, 'rbfw_enqueue_scripts' ), 90);

                add_action('admin_head', array( $this, 'included_header_script' ), 5 );
                add_action('wp_head', array( $this, 'included_header_script' ), 5 );
			}


			public function rbfw_add_admin_scripts($hook)
			{

                //font awesome
                wp_enqueue_style('fontawesome.v6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css');


                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script('jquery-ui-accordion');

                wp_enqueue_style( 'mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style.css', array(), time(), 'all' );
                wp_enqueue_style( 'mp_admin_settings', RBFW_PLUGIN_URL . '/assets/mp_admin_settings.css', array(), time(), 'all' );
                wp_enqueue_script( 'mp_plugin_global_rbfw', RBFW_PLUGIN_URL . '/assets/mp_script.js', array(), time(), true );

                //mp style
                wp_enqueue_style( 'mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_style.css', array(), time(), 'all' );
                wp_enqueue_script( 'mp_plugin_global_rbfw', RBFW_PLUGIN_URL . '/assets/mp_style/mp_script.js', array(), time(), true );

                //loading owl carousel css
                wp_enqueue_style('owl.carousel.min', RBFW_PLUGIN_URL . '/css/owl.carousel.min.css');
                wp_enqueue_style('owl.theme.default', RBFW_PLUGIN_URL . '/css/owl.theme.default.min.css');

                //loading owl carousel js
                wp_enqueue_script( 'owl.carousel.min', RBFW_PLUGIN_URL . '/js/owl.carousel.min.js', array('jquery'), '2.3.4', true );

                //loading tooltip js
                wp_enqueue_script( 'popper.min', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js', array('jquery'), '2.9.2', true );
                wp_enqueue_script( 'tippy-bundle.umd.min', 'https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.7/tippy-bundle.umd.min.js', array('jquery'), '6.3.7', true );
                // loading popup css
                wp_enqueue_style('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/css/jquery.modal.min.css');
                // loading popup js

                wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());
                wp_enqueue_style('rbfw-rent-items', plugin_dir_url(__DIR__) . 'css/rbfw_rent_items.css', array());

                wp_enqueue_script('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/js/jquery.modal.min.js', array('jquery'), '0.9.1', false);


                wp_enqueue_script( 'rbfw_script', RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_script.js', array(), time(), true );

                wp_enqueue_script( 'md_script', RBFW_PLUGIN_URL . '/assets/mp_script/md_script.js', array(), time(), true );
                wp_enqueue_script( 'sd_script', RBFW_PLUGIN_URL . '/assets/mp_script/sd_script.js', array(), time(), true );
                wp_enqueue_script( 'resort_script', RBFW_PLUGIN_URL . '/assets/mp_script/resort_script.js', array(), time(), true );


                do_action('rbfw_frontend_enqueue_scripts');

			   /**************************
			   * Enqueue Admin Styles
			   **************************/
			  
				wp_enqueue_style('rbfw-options-framework', plugin_dir_url(__DIR__) . 'admin/css/mage-options-framework.css', array(),time());
				wp_enqueue_style('jquery-ui', plugin_dir_url(__DIR__) . 'admin/css/jquery-ui.css');
				wp_enqueue_style('select2.min', plugin_dir_url(__DIR__) . 'admin/css/select2.min.css');
				wp_enqueue_style('rbfw-admin-style', plugin_dir_url(__DIR__) . 'admin/css/admin_style.css', array(),time());
				wp_enqueue_style('rbfw-admin', plugin_dir_url(__DIR__) . 'assets/admin/css/admin.css', array(),time());
				wp_enqueue_style('rbfw-placeholder-loading', plugin_dir_url(__DIR__) . 'css/placeholder-loading.css');
				wp_enqueue_style('smart_wizard_all', plugin_dir_url(__DIR__) . 'admin/css/smart_wizard_all.min.css');
               // wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());


				/**************************
				* Enqueue Admin Scripts
				*************************/
				//wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-sortable');
				//wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('jquery-effects-slide');
				wp_enqueue_script('jquery-effects-fade');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_script('rbfw-options-framework', plugins_url('admin/js/mage-options-framework.js', __DIR__), array('jquery', 'wp-color-picker', 'jquery-ui-sortable'));
				wp_localize_script('PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array('PickpluginsOptionsFramework_ajaxurl' => admin_url('admin-ajax.php')));
				wp_enqueue_script('select2.min', plugins_url('admin/js/select2.min.js', __DIR__), array('jquery'));
				wp_enqueue_script('form-field-dependency', plugins_url('admin/js/form-field-dependency.js', __DIR__), array('jquery'), null, false);
				wp_enqueue_script('rbfw-script', plugins_url('admin/js/mkb-admin.js', __DIR__), array('jquery', 'jquery-ui-datepicker','wp-tinymce'), time(), false);
				wp_localize_script('jquery', 'rbfw_ajax', array( 'rbfw_ajaxurl' => admin_url( 'admin-ajax.php')));
				wp_enqueue_script('smartWizard', plugins_url('admin/js/jquery.smartWizard.min.js', __DIR__), array('jquery'), '6.0.6', false);
				wp_enqueue_script('rbfw-admin-input', plugins_url('admin/js/rbfw-admin-input.js', __DIR__), array('jquery'), time(), false);
                do_action('rbfw_admin_enqueue_scripts');
			}
			
			
			public function rbfw_enqueue_scripts() {

                global $rbfw;


                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script('jquery-ui-accordion');

                //mp style
                wp_enqueue_style( 'mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_style.css', array(), time(), 'all' );
                wp_enqueue_script( 'mp_plugin_global_rbfw', RBFW_PLUGIN_URL . '/assets/mp_style/mp_script.js', array(), time(), true );

                //loading owl carousel css
                wp_enqueue_style('owl.carousel.min', RBFW_PLUGIN_URL . '/css/owl.carousel.min.css');
                wp_enqueue_style('owl.theme.default', RBFW_PLUGIN_URL . '/css/owl.theme.default.min.css');

                //loading owl carousel js
                wp_enqueue_script( 'owl.carousel.min', RBFW_PLUGIN_URL . '/js/owl.carousel.min.js', array('jquery'), '2.3.4', true );

                //loading tooltip js
                wp_enqueue_script( 'popper.min', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js', array('jquery'), '2.9.2', true );
                wp_enqueue_script( 'tippy-bundle.umd.min', 'https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.7/tippy-bundle.umd.min.js', array('jquery'), '6.3.7', true );
                // loading popup css
                wp_enqueue_style('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/css/jquery.modal.min.css');
                // loading popup js

                wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());
                wp_enqueue_style('rbfw-rent-items', plugin_dir_url(__DIR__) . 'css/rbfw_rent_items.css', array());

                wp_enqueue_script('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/js/jquery.modal.min.js', array('jquery'), '0.9.1', false);


                wp_enqueue_script( 'rbfw_script', RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_script.js', array(), time(), true );

                wp_enqueue_script( 'md_script', RBFW_PLUGIN_URL . '/assets/mp_script/md_script.js', array(), time(), true );

                wp_enqueue_script( 'resort_script', RBFW_PLUGIN_URL . '/assets/mp_script/resort_script.js', array(), time(), true );
                wp_enqueue_script( 'sd_script', RBFW_PLUGIN_URL . '/assets/mp_script/sd_script.js', array(), time(), true );
                wp_enqueue_script('rbfw_custom_script', plugin_dir_url(__DIR__) . 'js/rbfw_script.js', array('jquery'), time(), true);

                do_action('rbfw_frontend_enqueue_scripts');

                global $post;
                $post_id = !empty($post->ID) ? $post->ID : '';

                if(!empty($post_id)){
                    $appointment_days = json_encode(get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true));
                    $rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
                } else {
                    $appointment_days = [];
                    $rent_type = '';
                }

                $default_timezone = wp_timezone_string();
                $default_language = get_locale();
                if ( strlen( $default_language ) > 0 ) {
                    $default_language = explode( '_', $default_language )[0];
                }

                //wp_enqueue_script('rbfw_calendar', RBFW_PLUGIN_URL . '/js/calendar.min.js', array('jquery'), '1.0.2', false);

                wp_localize_script( 'rbfw_calendar', 'rbfw_calendar_object',
                    array(
                        'default_timezone' => $default_timezone,
                        'default_language' => $default_language,
                        'appointment_days' => $appointment_days,
                        'rent_type' => $rent_type,
                    )
                );
                if (rbfw_woo_install_check() == 'Yes') {

                    $view_more_feature_btn_text = $rbfw->get_option_trans('rbfw_text_view_more_features', 'rbfw_basic_translation_settings', __('Hide More', 'booking-and-rental-manager-for-woocommerce'));
                    $hide_more_feature_btn_text = $rbfw->get_option_trans('rbfw_text_hide_more_features', 'rbfw_basic_translation_settings', __('Load More', 'booking-and-rental-manager-for-woocommerce'));
                    $view_more_offers_btn_text = $rbfw->get_option_trans('rbfw_text_view_more_offers', 'rbfw_basic_translation_settings', __('View More Offers', 'booking-and-rental-manager-for-woocommerce'));
                    $hide_more_offers_btn_text = $rbfw->get_option_trans('rbfw_text_hide_more_offers', 'rbfw_basic_translation_settings', __('Hide More Offers', 'booking-and-rental-manager-for-woocommerce'));
                    $version = time(); // Time() function will prevent cache
                    wp_enqueue_script('jquery');

                    wp_enqueue_style('dashicons');
                    wp_enqueue_style('rbfw-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array());


                    wp_localize_script('rbfw_custom_script', 'rbfw_ajaxurl', array('rbfw_ajaxurl' => admin_url('admin-ajax.php'), 'view_more_feature_btn_text' => $view_more_feature_btn_text, 'hide_more_feature_btn_text' => $hide_more_feature_btn_text, 'view_more_offers_btn_text' => $view_more_offers_btn_text, 'hide_more_offers_btn_text' => $hide_more_offers_btn_text));
                    wp_localize_script('jquery', 'rbfw_ajax', array('rbfw_ajaxurl' => admin_url('admin-ajax.php')));
                    //font awesome
                    wp_enqueue_style('fontawesome.v6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css');

                    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), null);
                    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true);
                    wp_enqueue_script(
                        'jquery-ui-cdn',
                        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
                        array('jquery'), // Ensures jQuery is loaded as a dependency
                        '1.12.1',
                        true // Load in the footer
                    );
                }

			}

            public function included_header_script() {

                ?>
                <script>
                    let start_of_week = "<?php echo  get_option('start_of_week') ?>";
                    let wp_date_format = "<?php echo  get_option('date_format') ?>";
                    let wp_time_format = "<?php echo  get_option('time_format') ?>";
                    let js_date_format = 'yy-mm-dd';
                    if(wp_date_format=='F j, Y'){
                        js_date_format = 'dd M yy';
                    }else if(wp_date_format=='m/d/Y'){
                        js_date_format = 'mm/dd/yy';
                    }else if(wp_date_format=='d/m/Y'){
                        js_date_format = 'dd/mm/yy';
                    }else{
                        js_date_format = 'yy-mm-dd';
                    }

                </script>

                <script type="text/javascript">
                    let rbfw_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                    let rbfw_vars  = {
                        rbfw_nonce :  "<?php echo wp_create_nonce( 'rbfw_nonce' ) ?>"
                    };
                </script>

                <?php
                if (rbfw_woo_install_check() == 'Yes') {
                global $rbfw;
                $custom_cost = $rbfw->get_option_trans('rbfw_custom_css', 'rbfw_custom_style_settings');
                ?>
                <style>
                    <?php echo $custom_cost; ?>
                </style>
                <?php
                }
            }



		}

		new RBFW_Dependencies();
	}