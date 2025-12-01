<?php
if (! defined('ABSPATH')) {
	die;
} // Cannot access pages directly.
if (! class_exists('RBFW_Dependencies')) {
	class RBFW_Dependencies
	{
		protected $version;

		public function __construct()
		{
			add_action('admin_enqueue_scripts', array($this, 'rbfw_add_admin_scripts'), 10, 1);
			add_action('wp_enqueue_scripts', array($this, 'rbfw_enqueue_scripts'), 90);
			add_action('admin_head', array($this, 'included_header_script'), 5);
			add_action('wp_head', array($this, 'included_header_script'), 5);
		}

		public function rbfw_add_admin_scripts($hook)
		{
			//font awesome
			wp_enqueue_style('fontawesome.v6', RBFW_PLUGIN_URL . '/assets/font-awesome/all.min.css');
			// wp_enqueue_style( 'fontawesome.v6', RBFW_PLUGIN_URL . '/css/all.min.css' );
			// wp_enqueue_script( 'fontawesome.v6', RBFW_PLUGIN_URL . '/css/all.min.js', array(), time(), true );
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('jquery-ui-accordion');
			//wp_enqueue_style( 'mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style.css', array(), time(), 'all' );
			wp_enqueue_style('mp_admin_settings', RBFW_PLUGIN_URL . '/assets/mp_admin_settings.css', array(), time(), 'all');
			// wp_enqueue_script( 'mp_plugin_global_rbfw', RBFW_PLUGIN_URL . '/assets/mp_script.js', array(), time(), true );
			//mp style
			wp_enqueue_style('mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_style.css', array(), time(), 'all');
			wp_enqueue_script('mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_script.js', array(), time(), true);
			wp_enqueue_script('mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_script.js', array(), time(), true);
			wp_enqueue_script('rental_lists', RBFW_PLUGIN_URL . '/assets/admin/js/rental_lists.js', array(), time(), true);
			//loading owl carousel css
			wp_enqueue_style('owl.carousel.min', RBFW_PLUGIN_URL . '/css/owl.carousel.min.css');
			wp_enqueue_style('owl.theme.default', RBFW_PLUGIN_URL . '/css/owl.theme.default.min.css');
			//loading owl carousel js
			wp_enqueue_script('owl.carousel.min', RBFW_PLUGIN_URL . '/js/owl.carousel.min.js', array('jquery'), '2.3.4', true);
			//loading tooltip js
			wp_enqueue_script('popper.min', RBFW_PLUGIN_URL . '/assets/popper.min.js', array('jquery'), '2.9.2', true);
			wp_enqueue_script('tippy-bundle.umd.min', RBFW_PLUGIN_URL . '/assets/tippy-bundle.umd.min.js', array('jquery'), '6.3.7', true);
			// loading popup css
			wp_enqueue_style('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/css/jquery.modal.min.css');
			// loading popup js
			wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());
			wp_enqueue_style('rbfw-rent-items', plugin_dir_url(__DIR__) . 'css/rbfw_rent_items.css', array());
			wp_enqueue_script('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/js/jquery.modal.min.js', array('jquery'), '0.9.1', false);
			wp_enqueue_script('rbfw_script', RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_script.js', array(), time(), true);
			wp_enqueue_script('md_script', RBFW_PLUGIN_URL . '/assets/mp_script/md_script.js', array(), time(), true);

			wp_enqueue_script('sd_script', RBFW_PLUGIN_URL . '/assets/mp_script/sd_script.js', array(), time(), true);

			wp_enqueue_style('select2css', RBFW_PLUGIN_URL . '/admin/css/select2.min.css', false, '1.0', 'all');
			wp_enqueue_script('select2', RBFW_PLUGIN_URL . '/admin/js/select2.min.js', array('jquery'), null, true);

			if (rbfw_woo_install_check() == 'Yes') {
				wp_localize_script('rbfw_script', 'rbfw_translation', array(
					'return_time' => __('Return Time', 'booking-and-rental-manager-for-woocommerce'),
					'pickup_time' => __('Pickup Time', 'booking-and-rental-manager-for-woocommerce'),
					'available_quantity_is' => __('Available Quantity is', 'booking-and-rental-manager-for-woocommerce'),
					'no_items_available' => __('No Items Available!', 'booking-and-rental-manager-for-woocommerce'),
					'currency' => get_woocommerce_currency_symbol()
				));
			}

			wp_enqueue_script('resort_script', RBFW_PLUGIN_URL . '/assets/mp_script/resort_script.js', array(), time(), true);
			do_action('rbfw_frontend_enqueue_scripts');
			/**************************
			 * Enqueue Admin Styles
			 **************************/
			wp_enqueue_style('rbfw-options-framework', plugin_dir_url(__DIR__) . 'admin/css/mage-options-framework.css', array(), time());
			wp_enqueue_style('jquery-ui', plugin_dir_url(__DIR__) . 'admin/css/jquery-ui.css');
			wp_enqueue_style('select2.min', plugin_dir_url(__DIR__) . 'admin/css/select2.min.css');
			wp_enqueue_style('rbfw-admin-style', plugin_dir_url(__DIR__) . 'admin/css/admin_style.css', array(), time());
			wp_enqueue_style('rbfw-admin', plugin_dir_url(__DIR__) . 'assets/admin/css/admin.css', array(), time());
			wp_enqueue_style('rbfw-placeholder-loading', plugin_dir_url(__DIR__) . 'css/placeholder-loading.css');
			wp_enqueue_style('smart_wizard_all', plugin_dir_url(__DIR__) . 'admin/css/smart_wizard_all.min.css');
			// wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());
			wp_enqueue_style('mage-icons', RBFW_PLUGIN_URL . '/assets/mage-icon/css/mage-icon.css', array(), time());
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
			wp_enqueue_script('rbfw-script', plugins_url('admin/js/mkb-admin.js', __DIR__), array('jquery', 'jquery-ui-datepicker', 'wp-tinymce'), time(), false);

			wp_localize_script('jquery', 'rbfw_ajax_front', array(
				'rbfw_ajaxurl' => admin_url('admin-ajax.php'),
				'nonce_check_resort_availibility'        => wp_create_nonce('rbfw_check_resort_availibility_action'),
				'nonce_bikecarmd_ajax_price_calculation'        => wp_create_nonce('rbfw_bikecarmd_ajax_price_calculation_action'),
				'nonce_multi_items_ajax_price_calculation'        => wp_create_nonce('rbfw_multi_items_ajax_price_calculation_action'),
				'nonce_particular_time_date_dependent'        => wp_create_nonce('particular_time_date_dependent_action'),
				'nonce_get_rent_item_category_info'        => wp_create_nonce('rbfw_get_rent_item_category_info_action'),
				'nonce_service_type_timely_stock'        => wp_create_nonce('rbfw_service_type_timely_stock_action'),
				'nonce_get_left_side_filter_data'        => wp_create_nonce('rbfw_get_left_side_filter_data_action'),
				'nonce_get_resort_sessional_day_wise_price'        => wp_create_nonce('rbfw_get_resort_sessional_day_wise_price_action'),
				'nonce_get_rent_item_left_filter_more_data_popup'        => wp_create_nonce('rbfw_get_rent_item_left_filter_more_data_popup_action'),
				'nonce_bikecarsd_type_list'        => wp_create_nonce('rbfw_bikecarsd_type_list_action'),
				'nonce_bikecarsd_time_table'        => wp_create_nonce('rbfw_bikecarsd_time_table_action'),
				'nonce_bikecarmd_ajax_min_max_and_offdays_info'        => wp_create_nonce('rbfw_bikecarmd_ajax_min_max_and_offdays_info_action'),

			));

			wp_localize_script('jquery', 'rbfw_ajax_admin', array(
				'rbfw_ajaxurl' => admin_url('admin-ajax.php'),
				'nonce_time_slot'        => wp_create_nonce('rbfw_time_slot_action'),
				'nonce_duration_form'        => wp_create_nonce('rbfw_duration_form_action'),
				'nonce_room_types_with_sd_price'        => wp_create_nonce('rbfw_room_types_with_sd_price_action'),
				'nonce_room_types_with_sessional_price'        => wp_create_nonce('rbfw_room_types_with_sessional_price_action'),
				'nonce_room_types_with_resort_price_mds'        => wp_create_nonce('rbfw_room_types_with_resort_price_mds_action'),
				'nonce_fetch_order_details'        => wp_create_nonce('rbfw_fetch_order_details_action'),
				'nonce_load_more_icons'        => wp_create_nonce('rbfw_load_more_icons_action'),
				'nonce_update_time_slot'        => wp_create_nonce('rbfw_update_time_slot_action'),
				'nonce_delete_time_slot'        => wp_create_nonce('rbfw_delete_time_slot_action'),
				'nonce_get_stock_by_filter'        => wp_create_nonce('rbfw_get_stock_by_filter_action'),
				'nonce_get_stock_details'        => wp_create_nonce('rbfw_get_stock_details_action'),
				'nonce_faq_data_save'        => wp_create_nonce('rbfw_faq_data_save_action'),
				'nonce_faq_delete_item'        => wp_create_nonce('rbfw_faq_delete_item_action'),
				'nonce_faq_data_update'        => wp_create_nonce('rbfw_faq_data_update_action'),
			));

			if (function_exists('rbfw_get_option')) {
				$today_booking_enable = rbfw_get_option('today_booking_enable', 'rbfw_basic_gen_settings');
			} else {
				$today_booking_enable = 'no';
			}

            $timezone = wp_timezone(); // WP 5.3+
            $datetime = new DateTime('now', $timezone);

			wp_localize_script(
				'jquery',
				'rbfw_js_variables',
				array(
					'rbfw_today_booking_enable' => $today_booking_enable,
                    'timeFormat' => get_option('time_format'),
                    'currentDateTime' => $datetime->format('Y-m-d H:i:s'),
                    'currentDate' => $datetime->format('Y-m-d'),

				)
			);

			wp_enqueue_script('smartWizard', plugins_url('admin/js/jquery.smartWizard.min.js', __DIR__), array('jquery'), '6.0.6', false);
			wp_enqueue_script('rbfw-admin-input', plugins_url('admin/js/rbfw-admin-input.js', __DIR__), array('jquery'), time(), false);
			do_action('rbfw_admin_enqueue_scripts');
		}

		public function rbfw_enqueue_scripts()
		{
			global $rbfw;
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('jquery-ui-accordion');
			//mp style
			wp_enqueue_style('mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_style.css', array(), time(), 'all');
			wp_enqueue_script('mp_plugin_global', RBFW_PLUGIN_URL . '/assets/mp_style/mp_script.js', array(), time(), true);
			//loading owl carousel css
			wp_enqueue_style('owl.carousel.min', RBFW_PLUGIN_URL . '/css/owl.carousel.min.css');
			wp_enqueue_style('owl.theme.default', RBFW_PLUGIN_URL . '/css/owl.theme.default.min.css');
			//loading owl carousel js
			wp_enqueue_script('owl.carousel.min', RBFW_PLUGIN_URL . '/js/owl.carousel.min.js', array('jquery'), '2.3.4', true);
			//loading tooltip js
			wp_enqueue_script('popper.min', RBFW_PLUGIN_URL . '/assets/popper.min.js', array('jquery'), '2.9.2', true);
			wp_enqueue_script('tippy-bundle.umd.min', RBFW_PLUGIN_URL . '/assets/tippy-bundle.umd.min.js', array('jquery'), '6.3.7', true);
			// loading popup css
			wp_enqueue_style('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/css/jquery.modal.min.css');
			// loading popup js
			wp_enqueue_style('rbfw-style', plugin_dir_url(__DIR__) . 'css/rbfw_style.css', array());
			wp_enqueue_style('rbfw-rent-items', plugin_dir_url(__DIR__) . 'css/rbfw_rent_items.css', array());
			wp_enqueue_script('jquery.modal.min', plugin_dir_url(__DIR__) . 'admin/js/jquery.modal.min.js', array('jquery'), '0.9.1', false);
			// mage icon
			wp_enqueue_style('mage-icons', RBFW_PLUGIN_URL . '/assets/mage-icon/css/mage-icon.css', array(), time());
			wp_enqueue_script('rbfw_script', RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_script.js', array(), time(), true);


			wp_localize_script('rbfw_script', 'rbfw_translation', array(
				'return_time' => __('Return Time', 'booking-and-rental-manager-for-woocommerce'),
				'available_quantity_is' => __('Available Quantity is', 'booking-and-rental-manager-for-woocommerce'),
				'pickup_time' => __('Pickup Time', 'booking-and-rental-manager-for-woocommerce'),
				'sold_out' => __('Sold Out', 'booking-and-rental-manager-for-woocommerce'),
				'off_label' => __('Off', 'booking-and-rental-manager-for-woocommerce'),
				'no_items_available' => __('No Items Available!', 'booking-and-rental-manager-for-woocommerce'),
				'select_pickup_date' => __('Please select the pickup date!', 'booking-and-rental-manager-for-woocommerce'),
				'filter' => __('Filter', 'booking-and-rental-manager-for-woocommerce'),
				'currency' => get_woocommerce_currency_symbol(),

			));







			wp_enqueue_script('md_script', RBFW_PLUGIN_URL . '/assets/mp_script/md_script.js', array(), time(), true);
			wp_enqueue_script('resort_script', RBFW_PLUGIN_URL . '/assets/mp_script/resort_script.js', array(), time(), true);
			wp_enqueue_script('sd_script', RBFW_PLUGIN_URL . '/assets/mp_script/sd_script.js', array(), time(), true);
			wp_enqueue_script('rbfw_custom_script', plugin_dir_url(__DIR__) . 'js/rbfw_script.js', array('jquery'), time(), true);

			wp_enqueue_script('coockie-js', 'https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js', array('jquery'), null, true);


			do_action('rbfw_frontend_enqueue_scripts');
			global $post;
			$post_id = ! empty($post->ID) ? $post->ID : '';
			if (! empty($post_id)) {
				$appointment_days = wp_json_encode(get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true));
				$rent_type        = get_post_meta($post_id, 'rbfw_item_type', true);
			} else {
				$appointment_days = [];
				$rent_type        = '';
			}
			$default_timezone = wp_timezone_string();
			$default_language = get_locale();
			if (strlen($default_language) > 0) {
				$default_language = explode('_', $default_language)[0];
			}
			//wp_enqueue_script('rbfw_calendar', RBFW_PLUGIN_URL . '/js/calendar.min.js', array('jquery'), '1.0.2', false);
			wp_localize_script(
				'rbfw_calendar',
				'rbfw_calendar_object',
				array(
					'default_timezone' => $default_timezone,
					'default_language' => $default_language,
					'appointment_days' => $appointment_days,
					'rent_type'        => $rent_type,
				)
			);



			if (rbfw_woo_install_check() == 'Yes') {
				$view_more_feature_btn_text = (
					($rbfw->get_option_trans('rbfw_text_view_more_features', 'rbfw_basic_translation_settings') && want_loco_translate() == 'no')
					? esc_html($rbfw->get_option_trans('rbfw_text_view_more_features', 'rbfw_basic_translation_settings'))
					: esc_html__('Hide More', 'booking-and-rental-manager-for-woocommerce')
				);
				$hide_more_feature_btn_text = (
					($rbfw->get_option_trans('rbfw_text_hide_more_features', 'rbfw_basic_translation_settings') && want_loco_translate() == 'no')
					? esc_html($rbfw->get_option_trans('rbfw_text_hide_more_features', 'rbfw_basic_translation_settings'))
					: esc_html__('Load More', 'booking-and-rental-manager-for-woocommerce')
				);
				$view_more_offers_btn_text  = (
					($rbfw->get_option_trans('rbfw_text_view_more_offers', 'rbfw_basic_translation_settings') && want_loco_translate() == 'no')
					? esc_html($rbfw->get_option_trans('rbfw_text_view_more_offers', 'rbfw_basic_translation_settings'))
					: esc_html__('View More Offers', 'booking-and-rental-manager-for-woocommerce')
				);
				$hide_more_offers_btn_text  = (
					($rbfw->get_option_trans('rbfw_text_hide_more_offers', 'rbfw_basic_translation_settings') && want_loco_translate() == 'no')
					? esc_html($rbfw->get_option_trans('rbfw_text_hide_more_offers', 'rbfw_basic_translation_settings'))
					: esc_html__('Hide More Offers', 'booking-and-rental-manager-for-woocommerce')
				);
				$version                    = time(); // Time() function will prevent cache
				wp_enqueue_script('jquery');
				wp_enqueue_style('dashicons');
				wp_enqueue_style('rbfw-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array());

				wp_localize_script(
					'rbfw_custom_script',
					'rbfw_ajaxurl',
					array(
						'rbfw_ajaxurl' => admin_url('admin-ajax.php'),
						'view_more_feature_btn_text' => $view_more_feature_btn_text,
						'hide_more_feature_btn_text' => $hide_more_feature_btn_text,
						'view_more_offers_btn_text' => $view_more_offers_btn_text,
						'hide_more_offers_btn_text' => $hide_more_offers_btn_text,

					)
				);



				if (function_exists('rbfw_get_option')) {
					$today_booking_enable = rbfw_get_option('today_booking_enable', 'rbfw_basic_gen_settings');
				} else {
					$today_booking_enable = 'no';
				}

				$timezone = wp_timezone(); // WP 5.3+
				$datetime = new DateTime('now', $timezone);

				wp_localize_script(
					'jquery',
					'rbfw_js_variables',
					array(
						'rbfw_today_booking_enable' => $today_booking_enable,
						'timeFormat' => get_option('time_format'),
						'currentDateTime' => $datetime->format('Y-m-d H:i:s'),
						'currentDate' => $datetime->format('Y-m-d'),
                        'currency' => get_woocommerce_currency_symbol(),
                        'currency_format'                 => get_option( 'woocommerce_currency_pos' ),
                        'price_decimals' => wc_get_price_decimals()
					)
				);

				wp_localize_script('jquery', 'rbfw_ajax_front', array(
					'rbfw_ajaxurl' => admin_url('admin-ajax.php'),
					'nonce_check_resort_availibility'        => wp_create_nonce('rbfw_check_resort_availibility_action'),
					'nonce_bikecarmd_ajax_price_calculation'        => wp_create_nonce('rbfw_bikecarmd_ajax_price_calculation_action'),
					'nonce_multi_items_ajax_price_calculation'        => wp_create_nonce('rbfw_multi_items_ajax_price_calculation_action'),
					'nonce_particular_time_date_dependent'        => wp_create_nonce('particular_time_date_dependent_action'),
					'nonce_get_rent_item_category_info'        => wp_create_nonce('rbfw_get_rent_item_category_info_action'),
					'nonce_service_type_timely_stock'        => wp_create_nonce('rbfw_service_type_timely_stock_action'),
					'nonce_get_left_side_filter_data'        => wp_create_nonce('rbfw_get_left_side_filter_data_action'),
					'nonce_get_resort_sessional_day_wise_price'        => wp_create_nonce('rbfw_get_resort_sessional_day_wise_price_action'),
					'nonce_get_rent_item_left_filter_more_data_popup'        => wp_create_nonce('rbfw_get_rent_item_left_filter_more_data_popup_action'),
					'nonce_bikecarsd_type_list'        => wp_create_nonce('rbfw_bikecarsd_type_list_action'),
					'nonce_bikecarsd_time_table'        => wp_create_nonce('rbfw_bikecarsd_time_table_action'),
					'nonce_bikecarmd_ajax_min_max_and_offdays_info'        => wp_create_nonce('rbfw_bikecarmd_ajax_min_max_and_offdays_info_action'),

				));
				//font awesome
				// wp_enqueue_style( 'fontawesome.v6', RBFW_PLUGIN_URL . '/css/all.min.css' );
				// wp_enqueue_style('fontawesome.v6',  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css');
				wp_enqueue_style('fontawesome.v6', RBFW_PLUGIN_URL . '/assets/font-awesome/all.min.css');
				wp_enqueue_style('flatpickr-css', RBFW_PLUGIN_URL . '/css/flatpickr.min.css', array(), null);
				//wp_enqueue_style( 'fontawesome.v6', RBFW_PLUGIN_URL . '/assets/all.min.css', array(), null );
				wp_enqueue_script('flatpickr-js', RBFW_PLUGIN_URL . '/assets/flatpickr.js', array('jquery'), null, true);
				wp_enqueue_script(
					'jquery-ui-dialog', // WordPress default jQuery UI component (can change based on need, e.g., 'jquery-ui-dialog', 'jquery-ui-datepicker', etc.)
					false, // No need to specify the source URL since it's included by WordPress
					array('jquery', 'jquery-ui-core'), // Ensures jQuery and jQuery UI core are loaded as dependencies
					false, // Version is handled by WordPress
					true // Load in the footer
				);
			}
		}

		public function included_header_script()
		{
?>
			<script>
				// Safely pass WordPress options to JavaScript
				let start_of_week = <?php echo json_encode(esc_js(get_option('start_of_week'))); ?>;
				let wp_date_format = <?php echo json_encode(esc_js(get_option('date_format'))); ?>;
				let wp_time_format = <?php echo json_encode(esc_js(get_option('time_format'))); ?>;
				let js_date_format = 'yy-mm-dd'; // Default date format
                let mp_empty_image_url= "<?php echo esc_attr( RBFW_PLUGIN_URL . '/assets/images/no_image.png' ); ?>";
				// Modify JavaScript date format based on WordPress date format
				if (wp_date_format === 'F j, Y') {
					js_date_format = 'dd M yy';
				} else if (wp_date_format === 'm/d/Y') {
					js_date_format = 'mm/dd/yy';
				} else if (wp_date_format === 'd/m/Y') {
					js_date_format = 'dd/mm/yy';
				}
			</script>
			<script type="text/javascript">
				let rbfw_ajax_url = <?php echo json_encode(esc_url(admin_url('admin-ajax.php'))); ?>;
				let rbfw_vars = {
					rbfw_nonce: <?php echo json_encode(esc_js(wp_create_nonce('rbfw_nonce'))); ?>
				};
			</script>
			<?php
			if (rbfw_woo_install_check() == 'Yes') {
				global $rbfw;
				$custom_cost = $rbfw->get_option_trans('rbfw_custom_css', 'rbfw_custom_style_settings');
			?>
				<style>
					<?php echo esc_html($custom_cost); ?>
				</style>
<?php
			}
		}
	}
	new RBFW_Dependencies();
}
