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