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
			'id'    => 'rbfw_basic_translation_settings',
			'title' => '<i class="fas fa-language"></i>'.esc_html__( 'Translation Settings', 'booking-and-rental-manager-for-woocommerce' )
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
            /*array(
                'name' => 'display_default_time_enable_time_slot',
                'label' => esc_html__( 'Dispaly default time while time slot disable', 'booking-and-rental-manager-for-woocommerce' ),
                'desc' => esc_html__( "If you want to Dispaly default time while time slot disable, then yes it.", 'booking-and-rental-manager-for-woocommerce' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Yes',
                    'no'  => 'No'
                )
            ),*/
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
		'rbfw_basic_translation_settings' => array(
			array(
				'name'    => 'rbfw_text_hightlighted_features',
				'label'   => esc_html__( 'Highlighted Features', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Highlighted Features.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Highlighted Features'
			),
			array(
				'name'    => 'rbfw_text_description',
				'label'   => esc_html__( 'Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Description'
			),
			array(
				'name'    => 'rbfw_text_faq',
				'label'   => esc_html__( 'Frequently Asked Questions', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Frequently Asked Questions.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Frequently Asked Questions'
			),

			array(
				'name'    => 'rbfw_text_related_items',
				'label'   => esc_html__( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Related Items.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Related Items'
			),

			array(
				'name'    => 'rbfw_text_you_may_also_like',
				'label'   => esc_html__( 'You May Also Like', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>You May Also Like.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'You May Also Like'
			),
			array(
				'name'    => 'rbfw_text_read_more',
				'label'   => esc_html__( 'Read More', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Read More.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Read More'
			),
			array(
				'name'    => 'rbfw_text_book_it',
				'label'   => esc_html__( 'Book It', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Book It.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book It'
			),
			array(
				'name'    => 'rbfw_text_pricing_info',
				'label'   => esc_html__( 'Pricing Info', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pricing Info.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pricing Info'
			),
			array(
				'name'    => 'rbfw_text_daily_rate',
				'label'   => esc_html__( 'Daily Rate', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Daily Rate.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daily Rate'
			),
			array(
				'name'    => 'rbfw_text_day',
				'label'   => esc_html__( 'Day', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Day.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day'
			),
			array(
				'name'    => 'rbfw_text_hourly_rate',
				'label'   => esc_html__( 'Hourly rate', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hourly rate.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hourly rate'
			),
			array(
				'name'    => 'rbfw_text_hour',
				'label'   => esc_html__( 'Hour', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hour.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hour'
			),
			array(
				'name'    => 'rbfw_text_hours',
				'label'   => esc_html__( 'Hours', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hours.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hours'
			),
			array(
				'name'    => 'rbfw_text_pickup_location',
				'label'   => esc_html__( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pickup Location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup Location'
			),
			array(
				'name'    => 'rbfw_text_choose_pickup_location',
				'label'   => esc_html__( 'Choose pickup location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Choose pickup location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose pickup location'
			),
			array(
				'name'    => 'rbfw_text_dropoff_location',
				'label'   => esc_html__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Drop-off Location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off Location'
			),
			array(
				'name'    => 'rbfw_text_choose_dropoff_location',
				'label'   => esc_html__( 'Choose drop-off location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Choose drop-off location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose drop-off location'
			),

			array(
				'name'    => 'rbfw_text_pickup_date_time',
				'label'   => esc_html__( 'Pickup Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pickup Date & Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup Date & Time'
			),
			array(
				'name'    => 'rbfw_text_pickup_date',
				'label'   => esc_html__( 'Pickup date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pickup date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup date'
			),
			array(
				'name'    => 'rbfw_text_pickup_time',
				'label'   => esc_html__( 'Pickup Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pickup Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup Time'
			),
			array(
				'name'    => 'rbfw_text_pickup_point',
				'label'   => esc_html__( 'Pickup point', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pickup point.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup point'
			),
			array(
				'name'    => 'rbfw_text_dropoff_date_time',
				'label'   => esc_html__( 'Drop-off Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Drop-off Date & Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off Date & Time'
			),
			array(
				'name'    => 'rbfw_text_dropoff_date',
				'label'   => esc_html__( 'Drop-off date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Drop-off date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off date'
			),
			array(
				'name'    => 'rbfw_text_dropoff_time',
				'label'   => esc_html__( 'Drop-off time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Drop-off time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off time'
			),
			array(
				'name'    => 'rbfw_text_dropoff_point',
				'label'   => esc_html__( 'Drop-off point', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Drop-off point.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off point'
			),
			array(
				'name'    => 'rbfw_text_duration',
				'label'   => esc_html__( 'Duration', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Duration.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Duration'
			),
			array(
				'name'    => 'rbfw_text_resources',
				'label'   => esc_html__( 'Resources', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Resources.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Resources'
			),
			array(
				'name'    => 'rbfw_text_onetime',
				'label'   => esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>One Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'One Time'
			),
			array(
				'name'    => 'rbfw_text_checkin_checkout_date',
				'label'   => esc_html__( 'Check-In & Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Check-In & Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-In & Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_checkin_date',
				'label'   => esc_html__( 'Check-In Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Check-In Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-In Date'
			),
			array(
				'name'    => 'rbfw_text_checkout_date',
				'label'   => esc_html__( 'Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_choose_checkin_date',
				'label'   => esc_html__( 'Please Choose Check-In Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Please Choose Check-In Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please Choose Check-In Date'
			),
			array(
				'name'    => 'rbfw_text_choose_checkout_date',
				'label'   => esc_html__( 'Please Choose Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Please Choose Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please Choose Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_daylong',
				'label'   => esc_html__( 'Daylong', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Daylong.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daylong'
			),
			array(
				'name'    => 'rbfw_text_daylong_subtitle',
				'label'   => esc_html__( '9 AM  to 6 PM', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>9 AM  to 6 PM.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => '9 AM  to 6 PM'
			),
			array(
				'name'    => 'rbfw_text_daynight',
				'label'   => esc_html__( 'Daynight', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Daynight.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daynight'
			),
			array(
				'name'    => 'rbfw_text_daynight_subtitle',
				'label'   => esc_html__( 'Day & Night Stay', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Day & Night Stay.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day & Night Stay'
			),
			array(
				'name'    => 'rbfw_text_room_type',
				'label'   => esc_html__( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Type.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Type'
			),
			array(
				'name'    => 'rbfw_text_room_desc',
				'label'   => esc_html__( 'Room Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Description'
			),
			array(
				'name'    => 'rbfw_text_room_image',
				'label'   => esc_html__( 'Room Image', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Image.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Image'
			),
			array(
				'name'    => 'rbfw_text_room_price',
				'label'   => esc_html__( 'Room Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Price.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Price'
			),
			array(
				'name'    => 'rbfw_text_room_qty',
				'label'   => esc_html__( 'Room Qty', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Qty.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Qty'
			),
			array(
				'name'    => 'rbfw_text_room_service_name',
				'label'   => esc_html__( 'Room Service Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Service Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Name'
			),
			array(
				'name'    => 'rbfw_text_room_service_price',
				'label'   => esc_html__( 'Room Service Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Service Price.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Price'
			),
			array(
				'name'    => 'rbfw_text_room_service_qty',
				'label'   => esc_html__( 'Room Service Qty', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Service Qty.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Qty'
			),
			array(
				'name'    => 'rbfw_text_duration_cost',
				'label'   => esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Duration Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Duration Cost'
			),
			array(
				'name'    => 'rbfw_text_resource_cost',
				'label'   => esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Resource Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Resource Cost'
			),
			array(
				'name'    => 'rbfw_text_subtotal',
				'label'   => esc_html__( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Subtotal.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Subtotal'
			),
			array(
				'name'    => 'rbfw_text_total',
				'label'   => esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Total.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total'
			),
			array(
				'name'    => 'rbfw_text_total_cost',
				'label'   => esc_html__( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Total Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total Cost'
			),
			array(
				'name'    => 'rbfw_text_book_now',
				'label'   => esc_html__( 'Book Now', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Book Now.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book Now'
			),
			array(
				'name'    => 'rbfw_text_package',
				'label'   => esc_html__( 'Package', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Package.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Package'
			),
			array(
				'name'    => 'rbfw_text_room_information',
				'label'   => esc_html__( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Information'
			),
			array(
				'name'    => 'rbfw_text_room_service_information',
				'label'   => esc_html__( 'Room Service Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Room Service Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Service Information'
			),

			array(
				'name'    => 'rbfw_text_select_booking_type',
				'label'   => esc_html__( 'Choose Booking Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Select Booking Type.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'CHOOSE BOOKING TYPE'
			),
			array(
				'name'    => 'rbfw_text_prices_start_at',
				'label'   => esc_html__( 'Prices start at', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Prices start at.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Prices start at'
			),
			array(
				'name'    => 'rbfw_text_tax',
				'label'   => esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Tax'
			),
			array(
				'name'    => 'rbfw_text_order_tax',
				'label'   => esc_html__( 'Order tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order tax'
			),
			array(
				'name'    => 'rbfw_text_excluding_tax',
				'label'   => esc_html__( 'Excluding tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Excluding tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Excluding tax'
			),
			array(
				'name'    => 'rbfw_text_quantity',
				'label'   => esc_html__( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Quantity'
			),
			array(
				'name'    => 'rbfw_text_return_date',
				'label'   => esc_html__( 'Return Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Return Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Return Date'
			),
			array(
				'name'    => 'rbfw_text_return_time',
				'label'   => esc_html__( 'Return Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Return Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Return Time'
			),
			array(
				'name'    => 'rbfw_text_choose_number_of_qty',
				'label'   => esc_html__( 'Choose number of quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Choose number of quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose number of quantity'
			),
			array(
				'name'    => 'rbfw_text_choose',
				'label'   => esc_html__( 'Choose', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Choose.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose'
			),
			array(
				'name'    => 'rbfw_text_write_a_review',
				'label'   => esc_html__( 'Write a Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Write a Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Write a Review'
			),
			array(
				'name'    => 'rbfw_text_your_email_will_not_be_published',
				'label'   => esc_html__( 'Your email address will not be published', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Your email address will not be published.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Your email address will not be published'
			),
			array(
				'name'    => 'rbfw_text_required_fields_are_marked',
				'label'   => esc_html__( 'Required Fields are Marked', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Required Fields are Marked.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Required Fields are Marked'
			),
			array(
				'name'    => 'rbfw_text_reviews_are_closed',
				'label'   => esc_html__( 'Reviews are closed', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Reviews are closed.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviews are closed'
			),
			array(
				'name'    => 'rbfw_text_no_review_yet',
				'label'   => esc_html__( 'No Review Yet', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>No Review Yet.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'No Review Yet'
			),
			array(
				'name'    => 'rbfw_text_one_review',
				'label'   => esc_html__( '1 Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>1 Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => '1 Review'
			),
			array(
				'name'    => 'rbfw_text_reply',
				'label'   => esc_html__( 'Reply', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Reply.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reply'
			),
			array(
				'name'    => 'rbfw_text_reviews',
				'label'   => esc_html__( 'Reviews', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Reviews.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviews'
			),
			array(
				'name'    => 'rbfw_text_reviewer_name',
				'label'   => esc_html__( 'Reviewer Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Reviewer Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviewer Name'
			),
			array(
				'name'    => 'rbfw_text_reviewer_email',
				'label'   => esc_html__( 'Reviewer Email', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Reviewer Email.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviewer Email'
			),
			array(
				'name'    => 'rbfw_text_submit_review',
				'label'   => esc_html__( 'Submit Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Submit Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Submit Review'
			),
			array(
				'name'    => 'rbfw_text_review_rating',
				'label'   => esc_html__( 'Review Rating', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Review Rating.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Rating'
			),
			array(
				'name'    => 'rbfw_text_ratings',
				'label'   => esc_html__( 'Ratings', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Ratings.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Ratings'
			),
			array(
				'name'    => 'rbfw_text_rating_score',
				'label'   => esc_html__( 'Rating Score', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Rating Score.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rating Score'
			),
			array(
				'name'    => 'rbfw_text_total_reviews',
				'label'   => esc_html__( 'Total Reviews', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Total Reviews.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total Reviews'
			),
			array(
				'name'    => 'rbfw_text_review_title',
				'label'   => esc_html__( 'Review Title', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Review Title.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Title'
			),
			array(
				'name'    => 'rbfw_text_review_description',
				'label'   => esc_html__( 'Review Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Review Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Description'
			),
			array(
				'name'    => 'rbfw_text_order_summary',
				'label'   => esc_html__( 'Order Summary', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order Summary.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order Summary'
			),
			array(
				'name'    => 'rbfw_text_start_date',
				'label'   => esc_html__( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Start Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Date'
			),
			array(
				'name'    => 'rbfw_text_start_time',
				'label'   => esc_html__( 'Start Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Start Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Time'
			),
			array(
				'name'    => 'rbfw_text_end_date',
				'label'   => esc_html__( 'End Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>End Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Date'
			),
			array(
				'name'    => 'rbfw_text_end_time',
				'label'   => esc_html__( 'End Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>End Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Time'
			),
			array(
				'name'    => 'rbfw_text_checkout',
				'label'   => esc_html__( 'Checkout', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Checkout.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Checkout'
			),
			array(
				'name'    => 'rbfw_text_first_name',
				'label'   => esc_html__( 'First Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>First Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'First Name'
			),
			array(
				'name'    => 'rbfw_text_last_name',
				'label'   => esc_html__( 'Last Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Last Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Last Name'
			),
			array(
				'name'    => 'rbfw_text_email_address',
				'label'   => esc_html__( 'Email Address', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Email Address.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Email Address'
			),
			array(
				'name'    => 'rbfw_text_pay_with',
				'label'   => esc_html__( 'Pay With', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Pay With.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pay With'
			),
			array(
				'name'    => 'rbfw_text_offline_payment',
				'label'   => esc_html__( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Offline Payment.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Offline Payment'
			),
			array(
				'name'    => 'rbfw_text_paypal',
				'label'   => esc_html__( 'Paypal', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Paypal.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Paypal'
			),
			array(
				'name'    => 'rbfw_text_stripe',
				'label'   => esc_html__( 'Stripe', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Stripe.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Stripe'
			),
			array(
				'name'    => 'rbfw_text_place_order',
				'label'   => esc_html__( 'Place Order', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Place Order.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Place Order'
			),
			array(
				'name'    => 'rbfw_text_sign_in',
				'label'   => esc_html__( 'Sign In', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Sign In.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sign In'
			),
			array(
				'name'    => 'rbfw_text_sign_up',
				'label'   => esc_html__( 'Sign-Up', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Sign-Up.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sign-Up'
			),
			array(
				'name'    => 'rbfw_text_password',
				'label'   => esc_html__( 'Password', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Password.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Password'
			),
			array(
				'name'    => 'rbfw_text_forget_password',
				'label'   => esc_html__( 'Forgot password?', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Forgot password?</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Forgot password?'
			),
			array(
				'name'    => 'rbfw_text_log_in',
				'label'   => esc_html__( 'Log In', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Log In</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Log In'
			),
			array(
				'name'    => 'rbfw_text_registration_information',
				'label'   => esc_html__( 'Registration Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Registration Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Registration Information'
			),
			array(
				'name'    => 'rbfw_text_already_have_account_with_us',
				'label'   => esc_html__( 'Do you already have an account with us?', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Do you already have an account with us?</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Do you already have an account with us?'
			),
			array(
				'name'    => 'rbfw_text_thankyou_ur_order_received',
				'label'   => esc_html__( 'Thank you. Your order has been received.', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Thank you. Your order has been received.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Thank you. Your order has been received.'
			),
			array(
				'name'    => 'rbfw_text_order_received',
				'label'   => esc_html__( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order Information'
			),
			array(
				'name'    => 'rbfw_text_order_number',
				'label'   => esc_html__( 'Order number', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order number</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order number'
			),
			array(
				'name'    => 'rbfw_text_order_created_date',
				'label'   => esc_html__( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order created date</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order created date'
			),
			array(
				'name'    => 'rbfw_text_name',
				'label'   => esc_html__( 'Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Name</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Name'
			),
			array(
				'name'    => 'rbfw_text_email',
				'label'   => esc_html__( 'Email', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Email</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Email'
			),
			array(
				'name'    => 'rbfw_text_payment_method',
				'label'   => esc_html__( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Payment method</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Payment method'
			),
			array(
				'name'    => 'rbfw_text_item_information',
				'label'   => esc_html__( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Item Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Information'
			),
			array(
				'name'    => 'rbfw_text_item_name',
				'label'   => esc_html__( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Item Name</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Name'
			),
			array(
				'name'    => 'rbfw_text_item_type',
				'label'   => esc_html__( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Item Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Type'
			),
			array(
				'name'    => 'rbfw_text_rent_information',
				'label'   => esc_html__( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Rent Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent Information'
			),
			array(
				'name'    => 'rbfw_text_extra_service_information',
				'label'   => esc_html__( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Extra Service Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Extra Service Information'
			),
			array(
				'name'    => 'rbfw_text_start_date_and_time',
				'label'   => esc_html__( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Start Date and Time</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Date and Time'
			),
			array(
				'name'    => 'rbfw_text_end_date_and_time',
				'label'   => esc_html__( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>End Date and Time</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Date and Time'
			),
			array(
				'name'    => 'rbfw_text_includes',
				'label'   => esc_html__( 'Includes', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Includes</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Includes'
			),
			array(
				'name'    => 'rbfw_text_payment_id',
				'label'   => esc_html__( 'Payment ID', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Payment ID</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Payment ID'
			),
			array(
				'name'    => 'rbfw_text_download_booking_receipt',
				'label'   => esc_html__( 'Download Booking Receipt', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Download Booking Receipt</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Download Booking Receipt'
			),
			array(
				'name'    => 'rbfw_text_ur_order_has_been_received',
				'label'   => esc_html__( 'Your order has been received!', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Your order has been received!</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Your order has been received!'
			),
			array(
				'name'    => 'rbfw_text_status',
				'label'   => esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Status</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Status'
			),
			array(
				'name'    => 'rbfw_text_order_succesful_msg',
				'label'   => esc_html__( 'Order successful, redirecting...', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Order successful, redirecting...</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order successful, redirecting...'
			),
			array(
				'name'    => 'rbfw_text_booking_information',
				'label'   => esc_html__( 'Booking Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Booking Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booking Information'
			),
			array(
				'name'    => 'rbfw_text_pin',
				'label'   => esc_html__( 'PIN', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>PIN</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'PIN'
			),
			array(
				'name'    => 'rbfw_text_billing_information',
				'label'   => esc_html__( 'Billing Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Billing Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Billing Information'
			),
			array(
				'name'    => 'rbfw_text_phone',
				'label'   => esc_html__( 'Phone', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Phone</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Phone'
			),
			array(
				'name'    => 'rbfw_text_address',
				'label'   => esc_html__( 'Address', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Address</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Address'
			),
			array(
				'name'    => 'rbfw_text_book_online',
				'label'   => esc_html__( 'Book online', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Book online</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book online'
			),
			array(
				'name'    => 'rbfw_text_real_time_availability',
				'label'   => esc_html__( 'Real-time availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Real-time availability</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Real-time availability'
			),
			array(
				'name'    => 'rbfw_text_instant_confirmation',
				'label'   => esc_html__( 'Instant confirmation', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Instant confirmation</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Instant confirmation'
			),
			array(
				'name'    => 'rbfw_text_click_date_to_browse_availability',
				'label'   => esc_html__( 'Click a date to browse availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Click a date to browse availability</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Click a date to browse availability'
			),
			array(
				'name'    => 'rbfw_text_daylong_price',
				'label'   => esc_html__( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Day-long price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day-long price'
			),
			array(
				'name'    => 'rbfw_text_daynight_price',
				'label'   => esc_html__( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Day-night price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day-night price'
			),
			array(
				'name'    => 'rbfw_text_rent_type',
				'label'   => esc_html__( 'Rent Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Rent Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent Type'
			),
			array(
				'name'    => 'rbfw_text_price',
				'label'   => esc_html__( 'Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Price'
			),
			array(
				'name'    => 'rbfw_text_back_to_previous_step',
				'label'   => esc_html__( 'Back to Previous Step', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Back to Previous Step</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Back to Previous Step'
			),
			array(
				'name'    => 'rbfw_text_you_selected',
				'label'   => esc_html__( 'You selected', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>You selected</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'You selected'
			),
			array(
				'name'    => 'rbfw_text_service_name',
				'label'   => esc_html__( 'Service Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Service Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Name'
			),
			array(
				'name'    => 'rbfw_text_general_information',
				'label'   => esc_html__( 'General Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>General Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'General Information'
			),
			array(
				'name'    => 'rbfw_text_billing_information',
				'label'   => esc_html__( 'Billing Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Billing Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Billing Information'
			),
			array(
				'name'    => 'rbfw_text_variation_information',
				'label'   => esc_html__( 'Variation Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Variation Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Variation Information'
			),
			array(
				'name'    => 'rbfw_text_item_quantity',
				'label'   => esc_html__( 'Item Quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Item Quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Quantity'
			),
            array(
                'name'    => 'rbfw_text_service_info',
                'label'   => esc_html__( 'Service Info', 'booking-and-rental-manager-for-woocommerce' ),
                'desc'    => esc_html__( 'Enter the translated text of <strong>Service Info.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
                'type'    => 'text',
                'default' => 'Service Info'
            ),
			array(
				'name'    => 'rbfw_text_check_availability',
				'label'   => esc_html__( 'Check Availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Check Availability.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check Availability'
			),
			array(
				'name'    => 'rbfw_text_item_not_available',
				'label'   => esc_html__( 'Sorry, this item is out of stock.', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Sorry, this item is out of stock.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sorry, this item is out of stock.'
			),
			array(
				'name'    => 'rbfw_text_out_of_stock',
				'label'   => esc_html__( 'Out of stock', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Out of stock.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Out of stock'
			),
			array(
				'name'    => 'rbfw_text_available_qty_is',
				'label'   => esc_html__( 'Available Quantity is:', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Available Quantity is:</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Available Quantity is: '
			),
			array(
				'name'    => 'rbfw_text_available',
				'label'   => esc_html__( 'Available:', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Available:</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Available:'
			),
			array(
				'name'    => 'rbfw_text_left_qty',
				'label'   => esc_html__( 'Quantity Left', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Quantity Left</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Quantity Left'
			),
			array(
				'name'    => 'rbfw_text_booked',
				'label'   => esc_html__( 'Booked', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Booked</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booked'
			),
			array(
				'name'    => 'rbfw_text_type_information',
				'label'   => esc_html__( 'Type Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Type Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Type Information'
			),
			array(
				'name'    => 'rbfw_text_discount',
				'label'   => esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Discount</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Discount'
			),
			array(
				'name'    => 'rbfw_text_discount_type',
				'label'   => esc_html__( 'Discount Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Discount Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Discount Type'
			),
			array(
				'name'    => 'rbfw_text_book_over',
				'label'   => esc_html__( 'Book over', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Book over</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book over'
			),
			array(
				'name'    => 'rbfw_text_and',
				'label'   => esc_html__( 'and', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>and</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'and'
			),
			array(
				'name'    => 'rbfw_text_save',
				'label'   => esc_html__( 'save', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>save</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'save'
			),
			array(
				'name'    => 'rbfw_text_day',
				'label'   => esc_html__( 'day', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>day</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'day'
			),
			array(
				'name'    => 'rbfw_text_days',
				'label'   => esc_html__( 'days', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>days</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'days'
			),
			array(
				'name'    => 'rbfw_text_min_number_days_have_to_book',
				'label'   => esc_html__( 'Minimum number of days have to book is', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Minimum number of days have to book is</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Minimum number of days have to book is'
			),
			array(
				'name'    => 'rbfw_text_max_number_days_have_to_book',
				'label'   => esc_html__( 'Maximum number of days can book is', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Maximum number of days can book is</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Maximum number of days can book is'
			),
			array(
				'name'    => 'rbfw_text_booking_detail',
				'label'   => esc_html__( 'Booking Detail', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Booking Detail</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booking Detail'
			),
			array(
				'name'    => 'rbfw_text_testimonials',
				'label'   => esc_html__( 'Testimonials', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Testimonials</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Testimonials'
			),
			array(
				'name'    => 'rbfw_text_view_more_features',
				'label'   => esc_html__( 'View More Features', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>View More Features</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'View More Features'
			),
			array(
				'name'    => 'rbfw_text_hide_more_features',
				'label'   => esc_html__( 'Hide More Features', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hide More Features</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hide More Features'
			),
			array(
				'name'    => 'rbfw_text_pls_choose_option',
				'label'   => esc_html__( 'Please choose the option', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Please choose the option</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please choose the option'
			),
			array(
				'name'    => 'rbfw_text_customer_information',
				'label'   => esc_html__( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Customer Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Customer Information'
			),
			array(
				'name'    => 'rbfw_text_male',
				'label'   => esc_html__( 'Male', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Male</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Male'
			),
			array(
				'name'    => 'rbfw_text_female',
				'label'   => esc_html__( 'Female', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Female</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Female'
			),
			array(
				'name'    => 'rbfw_text_other',
				'label'   => esc_html__( 'Other', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Other</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Other'
			),
			array(
				'name'    => 'rbfw_text_rated_by',
				'label'   => esc_html__( 'Rated By', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Rated By</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rated By'
			),
			array(
				'name'    => 'rbfw_text_user',
				'label'   => esc_html__( 'User', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>User</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'User'
			),
			array(
				'name'    => 'rbfw_text_users',
				'label'   => esc_html__( 'Users', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Users</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Users'
			),
			array(
				'name'    => 'rbfw_text_start_booking',
				'label'   => esc_html__( 'Start Booking', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Start Booking</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Booking'
			),
			array(
				'name'    => 'rbfw_text_credit_debit_card',
				'label'   => esc_html__( 'Credit or debit card', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Credit or debit card</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Credit or debit card'
			),
			array(
				'name'    => 'rbfw_text_next',
				'label'   => esc_html__( 'Next', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Next</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Next'
			),
			array(
				'name'    => 'rbfw_text_change',
				'label'   => esc_html__( 'Change', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Change</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Change'
			),
			array(
				'name'    => 'rbfw_text_hygenic',
				'label'   => esc_html__( 'Hygenic', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hygenic</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hygenic'
			),
			array(
				'name'    => 'rbfw_text_quality',
				'label'   => esc_html__( 'Quality', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Quality</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Quality'
			),
			array(
				'name'    => 'rbfw_text_cost_value',
				'label'   => esc_html__( 'Cost Value', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Cost Value</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Cost Value'
			),
			array(
				'name'    => 'rbfw_text_staff',
				'label'   => esc_html__( 'Staff', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Staff</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Staff'
			),
			array(
				'name'    => 'rbfw_text_facilities',
				'label'   => esc_html__( 'Facilities', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Facilities</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Facilities'
			),
			array(
				'name'    => 'rbfw_text_comfort',
				'label'   => esc_html__( 'Comfort', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Comfort</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Comfort'
			),
			array(
				'name'    => 'rbfw_text_review_success_msg',
				'label'   => esc_html__( 'Review has been submitted. It\'s pending for approval', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Review has been submitted. It\'s pending for approval</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review has been submitted. It\'s pending for approval'
			),
			array(
				'name'    => 'rbfw_text_write_a_review',
				'label'   => esc_html__( 'Write a Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Write a Review</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Write a Review'
			),
			array(
				'name'    => 'rbfw_text_photos',
				'label'   => esc_html__( 'Photos', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Photos</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Photos'
			),
			array(
				'name'    => 'rbfw_text_date',
				'label'   => esc_html__( 'Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Date</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Date'
			),
			array(
				'name'    => 'rbfw_text_time',
				'label'   => esc_html__( 'Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Time</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Time'
			),
			array(
				'name'    => 'rbfw_text_offers',
				'label'   => esc_html__( 'Offers', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Offers</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Offers'
			),
			array(
				'name'    => 'rbfw_text_on_booking_over',
				'label'   => esc_html__( 'On booking over', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>On booking over</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'On booking over'
			),
			array(
				'name'    => 'rbfw_text_off',
				'label'   => esc_html__( 'Off', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Off</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Off'
			),
			array(
				'name'    => 'rbfw_text_view_more_offers',
				'label'   => esc_html__( 'View More Offers', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>View More Offers</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'View More Offers'
			),
			array(
				'name'    => 'rbfw_text_hide_more_offers',
				'label'   => esc_html__( 'Hide More Offers', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Hide More Offers</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hide More Offers'
			),
			array(
				'name'    => 'rbfw_text_terrible',
				'label'   => esc_html__( 'Terrible', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Terrible</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Terrible'
			),
			array(
				'name'    => 'rbfw_text_poor',
				'label'   => esc_html__( 'Poor', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Poor</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Poor'
			),
			array(
				'name'    => 'rbfw_text_average',
				'label'   => esc_html__( 'Average', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Average</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Average'
			),
			array(
				'name'    => 'rbfw_text_very_good',
				'label'   => esc_html__( 'Very Good', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Very Good</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Very Good'
			),
			array(
				'name'    => 'rbfw_text_excellent',
				'label'   => esc_html__( 'Excellent', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Excellent</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Excellent'
			),
			array(
				'name'    => 'rbfw_text_or',
				'label'   => esc_html__( 'Or', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Or</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Or'
			),
			array(
				'name'    => 'rbfw_text_no_items_available',
				'label'   => esc_html__( 'No Items Available!', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>No Items Available!</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'No Items Available!'
			),
			array(
				'name'    => 'rbfw_text_booking_expired',
				'label'   => esc_html__( 'Booking Time Expired!', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Booking Time Expired!</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booking Time Expired!'
			),
			array(
				'name'    => 'rbfw_text_search',
				'label'   => esc_html__( 'Search', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Search</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Search'
			),
			array(
				'name'    => 'rbfw_text_nodatafound',
				'label'   => esc_html__( 'Sorry, no data found!', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Sorry, no data found!</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sorry, no data found!'
			),
			array(
				'name'    => 'rbfw_text_pls_select_location',
				'label'   => esc_html__( 'Please Select a Location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Please Select a Location</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please Select a Location'
			),
			array(
				'name'    => 'rbfw_text_summary',
				'label'   => esc_html__( 'Summary', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => esc_html__( 'Enter the translated text of <strong>Summary.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Summary'
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