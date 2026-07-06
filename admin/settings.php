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

add_filter( 'rbfw_settings_sec_reg', 'rbfw_admin_settings_sec_reg_basic', 9 );
function rbfw_admin_settings_sec_reg_basic( $default_sec ) {
	$sections = array(
		array(
			'id'    => 'rbfw_basic_gen_settings',
			'title' => '<i class="fas fa-screwdriver-wrench"></i>'.__( 'General Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
		array(
			'id'    => 'rbfw_basic_style_settings',
			'title' => '<i class="fas fa-palette"></i>'.esc_html__( 'Style Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
        array(
            'id'    => 'rbfw_custom_style_settings',
            'title' => '<i class="fas fa-palette"></i>'.esc_html__( 'Custom CSS', 'booking-and-rental-manager-for-woocommerce' )
        ),




		array(
			'id'    => 'rbfw_basic_payment_settings',
			'title' => '<i class="fas fa-money-check-dollar"></i>'.esc_html__( 'Checkout Page', 'booking-and-rental-manager-for-woocommerce' )
		),
	);

	return array_merge( $default_sec, $sections );
}


add_filter( 'rbfw_settings_sec_fields', 'rbfw_settings_sec_fields_basic', 9 );
function rbfw_settings_sec_fields_basic( $default_fields ) {
	$settings_fields = array(

		'rbfw_basic_gen_settings' => array(
			array(
				'name' => 'rbfw_gutenburg_switch',
				'label' => esc_html__( 'On/Off Gutenburg', 'booking-and-rental-manager-for-woocommerce' ),
				'desc' => esc_html__( 'Enable/Disable gutenburg editor.', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => 'select',
				'default' => 'off',
				'options' => array(
					'on' => 'On',
					'off'  => 'Off'
				)
			),
			array(
				'name'    => 'rbfw_rent_label',
				'label'   => esc_html__( 'CPT Label', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'If you want to change the for rent custom post type label in the dashboard menu you can change here.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent'
			),
			array(
				'name'    => 'rbfw_rent_slug',
				'label'   => esc_html__( 'CPT Slug', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Please enter the slug name for rent custom post type. Remember after change this slug you need to flush permalink, Just go to Settings->Permalink hit the Save Settings button', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'rent'
			),
			array(
				'name'    => 'rbfw_rent_icon',
				'label'   => esc_html__( 'CPT Icon', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Please enter the icon class name for rent custom post type. Example: dashicons-list-view.', 'booking-and-rental-manager-for-woocommerce' ).' Find Icons: <a href="https://developer.wordpress.org/resource/dashicons/">Dashicons</a>',
				'type'    => 'text',
				'default' => 'dashicons-clipboard'
			),

			array(
				'name'    => 'rbfw_thankyou_page',
				'label'   => esc_html__( 'Thank You Page', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'It will work when the mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'generatepage',
				'default' => '',
				'options' => rbfw_get_pages_arr()
			),
			array(
				'name'    => 'rbfw_search_page',
				'label'   => esc_html__( 'Search Page', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'The filter form result will display on search result page.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'generatepage',
				'default' => '',
				'options' => rbfw_get_pages_arr()
			),
            array(
                'name' => 'rbfw_count_extra_day_enable',
                'label' => esc_html__( 'Count Extra Day Enable', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you don't want the extra day to count as a return day, then off it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'on',
                'options' => array(
                    'on' => 'On',
                    'off'  => 'Off'
                )
            ),
            array(
                'name' => 'rbfw_pricing_info_display',
                'label' => esc_html__( 'Pricing Info Display', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to display pricing info, then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name' => 'rbfw_real_time_availability_display',
                'label' => esc_html__( 'Real-time availability and Instant confirmation Display', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to display Real-time availability and Instant confirmation, then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name' => 'want_loco_translate',
                'label' => esc_html__( 'Want to use loco translate', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to change translation by using loco translate then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name' => 'today_booking_enable',
                'label' => esc_html__( 'Same day booking enable', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to enable same day booking, then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name'    => 'inventory_managed_order_status',
                'label'   => __( 'Inventory Managed Order Status', 'booking-and-rental-manager-for-woocommerce' ),
                'desc'    => __( 'Please Select which order status Will be Managed Inventory', 'booking-and-rental-manager-for-woocommerce' ),
                'type'    => 'multicheck',
                'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
                'options' => array(
                    'on-hold'    => 'On Hold',
                    'pending'    => 'Pending',
                    'processing' => 'Processing',
                    'completed'  => 'Completed'
                    // 'cancelled'     => 'Cancelled'
                )
            ),

            array(
                'name' => 'rbfw_allow_duplicate_rental_cart_item',
                'label' => esc_html__( 'Allow duplicate rental item in cart', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( 'If Yes, the same rental product can be added multiple times (for different variations/configurations). If No, it will work as sold individually.', 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),




            array(
                'name' => 'inventory_based_on_return',
                'label' => esc_html__( 'Inventory manage based on return', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to inventory manage based on return, then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name' => 'rbfw_share_section_enable',
                'label' => esc_html__( 'Enable Share Section', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to display the social media share section on rental item pages, then enable it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),

            array(
                'name' => 'pricing_display_for_listing',
                'label' => esc_html__( 'Pricing display for listing', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "Pricing display for listing.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'hourly',
                'options' => array(
                    'hourly' => 'Hourly',
                    'daily'  => 'Daily',
                    'weekly'  => 'Weekly',
                    'monthly'  => 'Monthly'
                )
            ),

        ),



		'rbfw_basic_style_settings' => array(
			array(
				'name'    => 'rbfw_rent_list_base_color',
				'label'   => esc_html__( 'Rent List Primary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Rent List Primary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#ff3726'
			),
			array(
				'name'    => 'rbfw_single_page_base_color_4',
				'label'   => esc_html__( 'Rent List Secondary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Rent List Page Secondary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#000000'
			),
			array(
				'name'    => 'rbfw_single_page_base_color_5',
				'label'   => esc_html__( 'Rent Booking Page Primary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Booking Page Primary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#ff3726'
			),
			array(
				'name'    => 'rbfw_single_page_secondary_color',
				'label'   => esc_html__( 'Rent Booking Page Secondary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Booking Page Secondary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#333'
			),
			array(
				'name'    => 'rbfw_booking_form_bg_color',
				'label'   => esc_html__( 'Rent Booking Page Form Background Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Booking Page Form Background Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#ddd'
			),
			array(
				'name'    => 'rbfw_single_page_base_color_1',
				'label'   => esc_html__( 'Single Page Base Color-1', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Single Page Base Color-1', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#ffcd00'
			),
			array(
				'name'    => 'rbfw_single_page_base_color_2',
				'label'   => esc_html__( 'Single Page Base Color-2', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Single Page Base Color-2', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#074836'
			),
			array(
				'name'    => 'rbfw_single_page_base_color_3',
				'label'   => esc_html__( 'Single Page Base Color-3', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Single Page Base Color-3', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#6F1E51'
			),


			array(
				'name'    => 'rbfw_single_page_base_color_6',
				'label'   => esc_html__( 'Single Page Base Color-6', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Select Single Page Base Color-6', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => '#1ABC9C'
			),

		),
        'rbfw_custom_style_settings' => array(
            array(
                'name'    => 'rbfw_custom_css',
                'label'   => esc_html__( 'Write Your Custom CSS Code Here', 'booking-and-rental-manager-for-woocommerce' ),
                'type'    => 'textarea',
            ),
        )

	);

	return apply_filters('rbfw_settings_field', $settings_fields );
}


// For license Page
add_filter( 'rbfw_settings_sec_reg', 'rbfw_license_settings_sec', 100 );
if (!function_exists('rbfw_license_settings_sec')) {
	function rbfw_license_settings_sec( $default_sec ) {
		$sections = array(
			array(
				'id'    => 'rbfw_license_settings',
				'title' => '<i class="fa-solid fa-address-card"></i>' . __( 'License', 'booking-and-rental-manager-for-woocommerce' )
			),
		);
		return array_merge( $default_sec, $sections );
	}
}

add_action('wsa_form_bottom_rbfw_license_settings', 'rbfw_licensing_landing_page', 5);
if (!function_exists('rbfw_licensing_landing_page')) {
function rbfw_licensing_landing_page($form) {
    ?>
    <div class='mep-licensing-page'>
        <h3><?php esc_html_e( 'Booking and Rental Manager Licensing', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
        <p><?php esc_html_e( 'Thanks you for using our Booking and Rental Manager plugin. This plugin is free and no license is required. We have some additional addons to enhance features of this plugin functionality. If you have any addon you need to enter a valid license for that plugin below.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>

        <div class="mep_licensae_info"></div>
        <table class='wp-list-table widefat striped posts mep-licensing-table'>
            <thead>
            <tr>
                <th><?php esc_html_e( 'Plugin Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th width=10%><?php esc_html_e( 'Order No', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th width=15%><?php esc_html_e( 'Expire on', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th width=30%><?php esc_html_e( 'License Key', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th width=10%><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th width=10%><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php do_action('rbfw_license_page_addon_list'); ?>
            </tbody>
        </table>
    </div>
    <?php
}
}

if (!function_exists('mep_license_expire_date')) {
    function mep_license_expire_date($date) {
        if (empty($date) || $date == 'lifetime') {
            echo esc_html($date);
        } else {
            if (strtotime(current_time('Y-m-d H:i')) < strtotime(date('Y-m-d H:i', strtotime($date)))) {
                echo rbfw_get_datetime($date, 'date-time-text');
            } else {
                esc_html_e('Expired', 'booking-and-rental-manager-for-woocommerce');
            }
        }
    }
}


// Removing functions from license hook
add_action('plugins_loaded', 'rbfw_remove_rbfw_license_action', 110);
function rbfw_remove_rbfw_license_action() {
    remove_action('wsa_form_bottom_rbfw_license_settings', 'rbfw_licensing_page', 5);
	remove_action( 'rbfw_settings_sec_reg', 'rbfw_free_settings_sec', 100 );
	remove_action( 'rbfw_settings_sec_reg', 'rbfw_freeb_settings_sec', 100 );
}

/**
 * AI & SEO Settings Section
 */
add_filter( 'rbfw_settings_sec_reg', 'rbfw_ai_settings_section', 10 );
function rbfw_ai_settings_section( $sections ) {
	$sections[] = array(
		'id'    => 'rbfw_ai_settings',
		'title' => '<i class="fas fa-robot"></i>' . esc_html__( 'AI & SEO Settings', 'booking-and-rental-manager-for-woocommerce' ),
	);
	return $sections;
}

add_filter( 'rbfw_settings_sec_fields', 'rbfw_ai_settings_fields', 10 );
function rbfw_ai_settings_fields( $fields ) {
	// Build the provider <select> options from the manager, not a hardcoded list.
	$provider_options = class_exists( 'RBFW_AI_Manager' )
		? RBFW_AI_Manager::get_provider_options()
		: array();

	// Build the initial Model <select> options from the active provider's
	// own static list. The JS will repopulate this dynamically as soon as
	// the API responds (or the user clicks "Fetch Models").
	$ai_settings   = get_option( 'rbfw_ai_settings', array() );
	$active_id     = isset( $ai_settings['rbfw_ai_provider'] ) ? (string) $ai_settings['rbfw_ai_provider'] : 'openai';
	$saved_model   = isset( $ai_settings['rbfw_ai_model'] ) ? (string) $ai_settings['rbfw_ai_model'] : '';
	$model_options = array();
	if ( class_exists( 'RBFW_AI_Manager' ) ) {
		$active_provider = RBFW_AI_Manager::get_provider( $active_id );
		if ( $active_provider ) {
			$model_options = $active_provider->get_static_models();
		}
	}
	// Preserve the saved value across reloads, even if it isn't in the
	// provider's current list (e.g. it was a deprecated/renamed model).
	if ( '' !== $saved_model && ! isset( $model_options[ $saved_model ] ) ) {
		$model_options[ $saved_model ] = $saved_model;
	}

	$fields['rbfw_ai_settings'] = array(
		array(
			'name'    => 'rbfw_ai_provider',
			'label'   => esc_html__( 'AI Provider', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Select which AI service to use for content generation. Only the API key field for the selected provider will be shown.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'select',
			'class'   => 'rbfw-ai-provider-select',
			'default' => 'openai',
			'options' => $provider_options,
		),
		array(
			'name'    => 'rbfw_ai_openai_key',
			'label'   => esc_html__( 'OpenAI API Key', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Get your key from platform.openai.com/api-keys', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'password',
			'class'   => 'rbfw-ai-key-openai',
			'default' => '',
		),
		array(
			'name'    => 'rbfw_ai_anthropic_key',
			'label'   => esc_html__( 'Anthropic API Key', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Get your key from console.anthropic.com', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'password',
			'class'   => 'rbfw-ai-key-anthropic',
			'default' => '',
		),
		array(
			'name'    => 'rbfw_ai_groq_key',
			'label'   => esc_html__( 'Groq API Key', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Get free key from console.groq.com (no credit card required)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'password',
			'class'   => 'rbfw-ai-key-groq',
			'default' => '',
		),
		array(
			'name'    => 'rbfw_ai_xai_key',
			'label'   => esc_html__( 'xAI API Key', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Get key from x.ai/api ($25 free + $150/month with data sharing)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'password',
			'class'   => 'rbfw-ai-key-xai',
			'default' => '',
		),
		array(
			'name'    => 'rbfw_ai_commandcode_key',
			'label'   => esc_html__( 'CommandCode API Key', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'CommandCode API key', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'password',
			'class'   => 'rbfw-ai-key-commandcode',
			'default' => '',
		),
		array(
			'name'    => 'rbfw_ai_model',
			'label'   => esc_html__( 'Model', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'The list of models is loaded automatically from the selected provider. Click "Fetch Models" to refresh it.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'select',
			'class'   => 'rbfw-ai-model-select',
			'default' => $saved_model,
			'options' => $model_options,
		),
		array(
			'name'    => 'rbfw_ai_max_tokens',
			'label'   => esc_html__( 'Max Tokens', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Maximum tokens for AI response (100-2000)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'number',
			'default' => '500',
		),
		array(
			'name'    => 'rbfw_ai_temperature',
			'label'   => esc_html__( 'Temperature', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Creativity level (0.0 = focused, 1.0 = creative)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'number',
			'default' => '0.7',
		),
		array(
			'name'    => 'rbfw_seo_title_length',
			'label'   => esc_html__( 'SEO Title Length', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Optimal character range (e.g., 50-60)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => '50-60',
		),
		array(
			'name'    => 'rbfw_seo_description_length',
			'label'   => esc_html__( 'SEO Description Length', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Optimal character range (e.g., 150-160)', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => '150-160',
		),
		array(
			'name'    => 'rbfw_seo_auto_score',
			'label'   => esc_html__( 'Auto Score on Type', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Automatically calculate SEO score as you type', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'on',
		),
	);

	return $fields;
}

/**
 * Output inline JS for AI settings provider-based conditional field visibility
 */
add_action( 'admin_footer', 'rbfw_ai_settings_conditional_fields_js' );
function rbfw_ai_settings_conditional_fields_js() {
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'rbfw_settings_page' ) === false ) {
		return;
	}
	$ai_admin = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'rbfw_ai_action' ),
		// Registered provider ids come from the manager, not from JS.
		'providers' => class_exists( 'RBFW_AI_Manager' ) ? RBFW_AI_Manager::get_provider_ids() : array(),
		'i18n'     => array(
			'fetching' => __( 'Fetching models…', 'booking-and-rental-manager-for-woocommerce' ),
			'fetched'  => __( 'Models loaded from provider.', 'booking-and-rental-manager-for-woocommerce' ),
			'fallback' => __( 'Could not reach the provider API. Showing the built-in list.', 'booking-and-rental-manager-for-woocommerce' ),
			'no_key'   => __( 'Enter an API key first.', 'booking-and-rental-manager-for-woocommerce' ),
			'no_ep'    => __( 'This provider has no remote models endpoint; showing the built-in list.', 'booking-and-rental-manager-for-woocommerce' ),
			'error'    => __( 'Failed to fetch models.', 'booking-and-rental-manager-for-woocommerce' ),
		),
	);
	?>
	<script>
	window.rbfwAIAdmin = <?php echo wp_json_encode( $ai_admin ); ?>;
	jQuery(document).ready(function($) {
		var $section = $('#rbfw_ai_settings');
		if ( !$section.length ) return;

		var providers = (window.rbfwAIAdmin && rbfwAIAdmin.providers) ? rbfwAIAdmin.providers : [];

		var $modelSelect = $section.find('select[id$="[rbfw_ai_model]"]');
		var $provider    = $section.find('select[id$="[rbfw_ai_provider]"]');
		var $status      = $('<span class="rbfw-ai-fetch-status description" style="margin-left:8px;"></span>');
		var initialModel = $modelSelect.val();
		var hasUserTouched = false;

		// Inject the "Fetch Models" button next to the Model field description.
		var $modelRow = $modelSelect.closest('tr');
		if ($modelRow.length) {
			var $btn = $('<button type="button" class="button button-secondary rbfw-ai-fetch-models" style="margin-left:8px;"><span class="dashicons dashicons-update" style="line-height:1.3;"></span> Fetch Models</button>');
			$modelRow.find('.description').first().append($btn).append($status);
		}

		function applyModelFilter() {
			var selected = $provider.val();
			var $row;

			providers.forEach(function(p) {
				$row = $section.find('tr.rbfw-ai-key-' + p);
				if (p === selected) $row.show(); else $row.hide();
			});
		}

		function currentApiKey() {
			var sel = $provider.val();
			var $keyInput = $section.find('input[type="password"][id$="[rbfw_ai_' + sel + '_key]"]');
			return $keyInput.length ? $keyInput.val() : '';
		}

		function repopulateModels(models, source) {
			var $select = $modelSelect;
			if (!$select.length) return;

			var previous = $select.val() || initialModel;
			$select.empty();

			Object.keys(models).forEach(function(id) {
				$select.append($('<option>', { value: id, text: models[id] }));
			});

			// If the previous value still exists in the new list, keep it.
			// Otherwise leave the select with no explicit selection — the
			// user (or the next save) will pick one.
			var exists = $select.find('option[value="' + previous + '"]').length > 0;
			if (exists) {
				$select.val(previous);
			} else if (source === 'remote') {
				$select.val(Object.keys(models)[0] || '');
			}
		}

		function fetchModels(force) {
			if (typeof rbfwAIAdmin === 'undefined' || !rbfwAIAdmin.ajax_url) {
				$status.text('Configuration error: rbfwAIAdmin not loaded.');
				return;
			}

			var provider = $provider.val();
			var apiKey   = currentApiKey();

			$status.text(rbfwAIAdmin.i18n.fetching);

			$.ajax({
				url:  rbfwAIAdmin.ajax_url,
				method: 'POST',
				data: {
					action:   'rbfw_ai_get_models',
					nonce:    rbfwAIAdmin.nonce,
					provider: provider,
					api_key:  apiKey,
					force:    force ? 1 : 0
				}
			}).done(function(response) {
				if (response && response.success && response.data && response.data.models) {
					var source  = response.data.source || 'error';
					var message = response.data.message || '';
					repopulateModels(response.data.models, source);
					if (source === 'remote') {
						$status.text(rbfwAIAdmin.i18n.fetched);
					} else if (source === 'no_endpoint') {
						$status.text(message || rbfwAIAdmin.i18n.no_ep);
					} else if (source === 'no_key') {
						$status.text(rbfwAIAdmin.i18n.no_key);
					} else {
						$status.text(rbfwAIAdmin.i18n.fallback + (message ? ' — ' + message : ''));
					}
				} else {
					$status.text((response && response.data) ? response.data : rbfwAIAdmin.i18n.error);
				}
			}).fail(function(xhr) {
				var body = '';
				try { body = (xhr.responseJSON && xhr.responseJSON.data) ? xhr.responseJSON.data : ''; } catch (e) {}
				$status.text(rbfwAIAdmin.i18n.fallback + (body ? ' — ' + body : ' (HTTP ' + xhr.status + ')'));
			});
		}

		$provider.on('change', function() {
			hasUserTouched = true;
			initialModel = '';
			applyModelFilter();
			fetchModels(true);
		});
		$section.on('click', '.rbfw-ai-fetch-models', function(e) {
			e.preventDefault();
			fetchModels(true);
		});

		applyModelFilter();
		// Auto-fetch on initial load for the currently selected provider.
		fetchModels(false);
	});
	</script>
	<?php
}