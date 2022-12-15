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
			'title' => '<i class="fa-solid fa-screwdriver-wrench"></i>'.__( 'General Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
		array(
			'id'    => 'rbfw_basic_translation_settings',
			'title' => '<i class="fa-solid fa-language"></i>'.__( 'Translation Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
		array(
			'id'    => 'rbfw_basic_style_settings',
			'title' => '<i class="fa-solid fa-palette"></i>'.__( 'Style Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
		array(
			'id'    => 'rbfw_basic_single_rent_page_settings',
			'title' => '<i class="fa-solid fa-gears"></i>'.__( 'Single Rent Page Settings', 'booking-and-rental-manager-for-woocommerce' )
		),
		array(
			'id'    => 'rbfw_basic_payment_settings',
			'title' => '<i class="fa-solid fa-money-check-dollar"></i>'.__( 'Payment Settings', 'booking-and-rental-manager-for-woocommerce' )
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
				'label' => __( 'On/Off Gutenburg', 'booking-and-rental-manager-for-woocommerce' ),
				'desc' => __( 'Enable/Disable gutenburg editor.', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => 'select',
				'default' => 'on',
				'options' => array(
					'on' => 'On',
					'off'  => 'Off'
				)
			),			
			array(
				'name'    => 'rbfw_rent_label',
				'label'   => __( 'CPT Label', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'If you want to change the for rent custom post type label in the dashboard menu you can change here.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent'
			),
			array(
				'name'    => 'rbfw_rent_slug',
				'label'   => __( 'CPT Slug', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Please enter the slug name for rent custom post type. Remember after change this slug you need to flush permalink, Just go to Settings->Permalink hit the Save Settings button', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'rent'
			),
			array(
				'name'    => 'rbfw_rent_icon',
				'label'   => __( 'CPT Icon', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Please enter the icon class name for rent custom post type. Example: dashicons-list-view.', 'booking-and-rental-manager-for-woocommerce' ).' Find Icons: <a href="https://developer.wordpress.org/resource/dashicons/">Dashicons</a>',
				'type'    => 'text',
				'default' => 'dashicons-list-view'
			),

			array(
				'name'    => 'rbfw_thankyou_page',
				'label'   => __( 'Thank You Page', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'It will work when the mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'generatepage',
				'default' => '',
				'options' => rbfw_get_pages_arr()
			),
			array(
				'name'    => 'rbfw_account_page',
				'label'   => __( 'Booking Account Page', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'It will work when the mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'generatepage',
				'default' => '',
				'options' => rbfw_get_pages_arr()
			),					
		),
		'rbfw_basic_translation_settings' => array(
			array(
				'name'    => 'rbfw_text_hightlighted_features',
				'label'   => __( 'Highlighted Features', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Highlighted Features.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Highlighted Features'
			),
			array(
				'name'    => 'rbfw_text_description',
				'label'   => __( 'Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Description'
			),
			array(
				'name'    => 'rbfw_text_faq',
				'label'   => __( 'Frequently Asked Questions', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Frequently Asked Questions.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Frequently Asked Questions'
			),

			array(
				'name'    => 'rbfw_text_related_products',
				'label'   => __( 'Related Products', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Related Products.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Related Products'
			),
			array(
				'name'    => 'rbfw_text_read_more',
				'label'   => __( 'Read More', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Read More.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Read More'
			),
			array(
				'name'    => 'rbfw_text_pricing_info',
				'label'   => __( 'Pricing Info', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pricing Info.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pricing Info'
			),
			array(
				'name'    => 'rbfw_text_daily_rate',
				'label'   => __( 'Daily Rate', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Daily Rate.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daily Rate'
			),
			array(
				'name'    => 'rbfw_text_day',
				'label'   => __( 'Day', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Day.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day'
			),			
			array(
				'name'    => 'rbfw_text_hourly_rate',
				'label'   => __( 'Hourly rate', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Hourly rate.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hourly rate'
			),
			array(
				'name'    => 'rbfw_text_hour',
				'label'   => __( 'Hour', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Hour.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Hour'
			),			
			array(
				'name'    => 'rbfw_text_pickup_location',
				'label'   => __( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pickup Location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup Location'
			),
			array(
				'name'    => 'rbfw_text_choose_pickup_location',
				'label'   => __( 'Choose pickup location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Choose pickup location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose pickup location'
			),
			array(
				'name'    => 'rbfw_text_dropoff_location',
				'label'   => __( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Drop-off Location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off Location'
			),
			array(
				'name'    => 'rbfw_text_choose_dropoff_location',
				'label'   => __( 'Choose drop-off location', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Choose drop-off location.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose drop-off location'
			),
			array(
				'name'    => 'rbfw_text_dress_size',
				'label'   => __( 'Dress Size', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Dress Size.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Dress Size'
			),
			array(
				'name'    => 'rbfw_text_choose_dress_size',
				'label'   => __( 'Choose dress size', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Choose dress size.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose dress size'
			),			
			array(
				'name'    => 'rbfw_text_pickup_date_time',
				'label'   => __( 'Pickup Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pickup Date & Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup Date & Time'
			),
			array(
				'name'    => 'rbfw_text_pickup_date',
				'label'   => __( 'Pickup date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pickup date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup date'
			),
			array(
				'name'    => 'rbfw_text_pickup_time',
				'label'   => __( 'Pickup time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pickup time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup time'
			),
			array(
				'name'    => 'rbfw_text_pickup_point',
				'label'   => __( 'Pickup point', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pickup point.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pickup point'
			),
			array(
				'name'    => 'rbfw_text_dropoff_date_time',
				'label'   => __( 'Drop-off Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Drop-off Date & Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off Date & Time'
			),
			array(
				'name'    => 'rbfw_text_dropoff_date',
				'label'   => __( 'Drop-off date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Drop-off date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off date'
			),
			array(
				'name'    => 'rbfw_text_dropoff_time',
				'label'   => __( 'Drop-off time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Drop-off time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off time'
			),
			array(
				'name'    => 'rbfw_text_dropoff_point',
				'label'   => __( 'Drop-off point', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Drop-off point.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Drop-off point'
			),			
			array(
				'name'    => 'rbfw_text_duration',
				'label'   => __( 'Duration', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Duration.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Duration'
			),
			array(
				'name'    => 'rbfw_text_resources',
				'label'   => __( 'Resources', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Resources.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Resources'
			),
			array(
				'name'    => 'rbfw_text_onetime',
				'label'   => __( 'One Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>One Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'One Time'
			),
			array(
				'name'    => 'rbfw_text_checkin_checkout_date',
				'label'   => __( 'Check-In & Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Check-In & Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-In & Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_checkin_date',
				'label'   => __( 'Check-In Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Check-In Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-In Date'
			),			
			array(
				'name'    => 'rbfw_text_checkout_date',
				'label'   => __( 'Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_choose_checkin_date',
				'label'   => __( 'Please Choose Check-In Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Please Choose Check-In Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please Choose Check-In Date'
			),
			array(
				'name'    => 'rbfw_text_choose_checkout_date',
				'label'   => __( 'Please Choose Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Please Choose Check-Out Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Please Choose Check-Out Date'
			),
			array(
				'name'    => 'rbfw_text_daylong',
				'label'   => __( 'Daylong', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Daylong.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daylong'
			),
			array(
				'name'    => 'rbfw_text_daylong_subtitle',
				'label'   => __( '9 AM  to 6 PM', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>9 AM  to 6 PM.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => '9 AM  to 6 PM'
			),			
			array(
				'name'    => 'rbfw_text_daynight',
				'label'   => __( 'Daynight', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Daynight.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Daynight'
			),
			array(
				'name'    => 'rbfw_text_daynight_subtitle',
				'label'   => __( 'Day & Night Stay', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Day & Night Stay.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day & Night Stay'
			),			
			array(
				'name'    => 'rbfw_text_room_type',
				'label'   => __( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Type.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Type'
			),
			array(
				'name'    => 'rbfw_text_room_desc',
				'label'   => __( 'Room Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Description'
			),			
			array(
				'name'    => 'rbfw_text_room_image',
				'label'   => __( 'Room Image', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Image.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Image'
			),
			array(
				'name'    => 'rbfw_text_room_price',
				'label'   => __( 'Room Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Price.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Price'
			),			
			array(
				'name'    => 'rbfw_text_room_qty',
				'label'   => __( 'Room Qty', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Qty.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Qty'
			),
			array(
				'name'    => 'rbfw_text_room_service_name',
				'label'   => __( 'Room Service Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Service Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Name'
			),
			array(
				'name'    => 'rbfw_text_room_service_price',
				'label'   => __( 'Room Service Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Service Price.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Price'
			),
			array(
				'name'    => 'rbfw_text_room_service_qty',
				'label'   => __( 'Room Service Qty', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Service Qty.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Qty'
			),
			array(
				'name'    => 'rbfw_text_duration_cost',
				'label'   => __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Duration Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Duration Cost'
			),
			array(
				'name'    => 'rbfw_text_resource_cost',
				'label'   => __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Resource Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Resource Cost'
			),
			array(
				'name'    => 'rbfw_text_subtotal',
				'label'   => __( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Subtotal.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Subtotal'
			),
			array(
				'name'    => 'rbfw_text_total',
				'label'   => __( 'Total', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Total.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total'
			),
			array(
				'name'    => 'rbfw_text_total_cost',
				'label'   => __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Total Cost.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total Cost'
			),
			array(
				'name'    => 'rbfw_text_book_now',
				'label'   => __( 'Book Now', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Book Now.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book Now'
			),
			array(
				'name'    => 'rbfw_text_package',
				'label'   => __( 'Package', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Package.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Package'
			),
			array(
				'name'    => 'rbfw_text_room_information',
				'label'   => __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Information'
			),
			array(
				'name'    => 'rbfw_text_room_service_information',
				'label'   => __( 'Room Service Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Room Service Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Room Service Information'
			),

			array(
				'name'    => 'rbfw_text_select_booking_type',
				'label'   => __( 'Select Booking Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Select Booking Type.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Select Booking Type'
			),
			array(
				'name'    => 'rbfw_text_prices_start_at',
				'label'   => __( 'Prices start at', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Prices start at.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Prices start at'
			),
			array(
				'name'    => 'rbfw_text_tax',
				'label'   => __( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Tax'
			),
			array(
				'name'    => 'rbfw_text_order_tax',
				'label'   => __( 'Order tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order tax'
			),
			array(
				'name'    => 'rbfw_text_excluding_tax',
				'label'   => __( 'Excluding tax', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Excluding tax.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Excluding tax'
			),
			array(
				'name'    => 'rbfw_text_quantity',
				'label'   => __( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Quantity'
			),
			array(
				'name'    => 'rbfw_text_return_date',
				'label'   => __( 'Return Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Return Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Return Date'
			),
			array(
				'name'    => 'rbfw_text_return_time',
				'label'   => __( 'Return Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Return Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Return Time'
			),
			array(
				'name'    => 'rbfw_text_choose_number_of_qty',
				'label'   => __( 'Choose number of quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Choose number of quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose number of quantity'
			),
			array(
				'name'    => 'rbfw_text_choose',
				'label'   => __( 'Choose', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Choose.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Choose'
			),
			array(
				'name'    => 'rbfw_text_write_a_review',
				'label'   => __( 'Write a Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Write a Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Write a Review'
			),
			array(
				'name'    => 'rbfw_text_your_email_will_not_be_published',
				'label'   => __( 'Your email address will not be published', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Your email address will not be published.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Your email address will not be published'
			),
			array(
				'name'    => 'rbfw_text_required_fields_are_marked',
				'label'   => __( 'Required Fields are Marked', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Required Fields are Marked.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Required Fields are Marked'
			),
			array(
				'name'    => 'rbfw_text_reviews_are_closed',
				'label'   => __( 'Reviews are closed', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Reviews are closed.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviews are closed'
			),
			array(
				'name'    => 'rbfw_text_no_review_yet',
				'label'   => __( 'No Review Yet', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>No Review Yet.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'No Review Yet'
			),
			array(
				'name'    => 'rbfw_text_one_review',
				'label'   => __( '1 Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>1 Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => '1 Review'
			),
			array(
				'name'    => 'rbfw_text_reply',
				'label'   => __( 'Reply', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Reply.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reply'
			),
			array(
				'name'    => 'rbfw_text_reviews',
				'label'   => __( 'Reviews', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Reviews.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviews'
			),
			array(
				'name'    => 'rbfw_text_reviewer_name',
				'label'   => __( 'Reviewer Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Reviewer Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviewer Name'
			),
			array(
				'name'    => 'rbfw_text_reviewer_email',
				'label'   => __( 'Reviewer Email', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Reviewer Email.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Reviewer Email'
			),
			array(
				'name'    => 'rbfw_text_submit_review',
				'label'   => __( 'Submit Review', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Submit Review.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Submit Review'
			),
			array(
				'name'    => 'rbfw_text_review_rating',
				'label'   => __( 'Review Rating', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Review Rating.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Rating'
			),
			array(
				'name'    => 'rbfw_text_rating_score',
				'label'   => __( 'Rating Score', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Rating Score.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rating Score'
			),
			array(
				'name'    => 'rbfw_text_total_reviews',
				'label'   => __( 'Total Reviews', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Total Reviews.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Total Reviews'
			),
			array(
				'name'    => 'rbfw_text_review_title',
				'label'   => __( 'Review Title', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Review Title.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Title'
			),
			array(
				'name'    => 'rbfw_text_review_description',
				'label'   => __( 'Review Description', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Review Description.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Review Description'
			),
			array(
				'name'    => 'rbfw_text_order_summary',
				'label'   => __( 'Order Summary', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order Summary.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order Summary'
			),	
			array(
				'name'    => 'rbfw_text_start_date',
				'label'   => __( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Start Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Date'
			),
			array(
				'name'    => 'rbfw_text_start_time',
				'label'   => __( 'Start Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Start Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Time'
			),
			array(
				'name'    => 'rbfw_text_end_date',
				'label'   => __( 'End Date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>End Date.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Date'
			),
			array(
				'name'    => 'rbfw_text_end_time',
				'label'   => __( 'End Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>End Time.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Time'
			),
			array(
				'name'    => 'rbfw_text_checkout',
				'label'   => __( 'Checkout', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Checkout.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Checkout'
			),
			array(
				'name'    => 'rbfw_text_first_name',
				'label'   => __( 'First Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>First Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'First Name'
			),
			array(
				'name'    => 'rbfw_text_last_name',
				'label'   => __( 'Last Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Last Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Last Name'
			),
			array(
				'name'    => 'rbfw_text_email_address',
				'label'   => __( 'Email Address', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Email Address.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Email Address'
			),	
			array(
				'name'    => 'rbfw_text_pay_with',
				'label'   => __( 'Pay With', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Pay With.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Pay With'
			),
			array(
				'name'    => 'rbfw_text_offline_payment',
				'label'   => __( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Offline Payment.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Offline Payment'
			),
			array(
				'name'    => 'rbfw_text_paypal',
				'label'   => __( 'Paypal', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Paypal.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Paypal'
			),
			array(
				'name'    => 'rbfw_text_stripe',
				'label'   => __( 'Stripe', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Stripe.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Stripe'
			),
			array(
				'name'    => 'rbfw_text_place_order',
				'label'   => __( 'Place Order', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Place Order.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Place Order'
			),	
			array(
				'name'    => 'rbfw_text_sign_in',
				'label'   => __( 'Sign In', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Sign In.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sign In'
			),
			array(
				'name'    => 'rbfw_text_sign_up',
				'label'   => __( 'Sign-Up', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Sign-Up.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sign-Up'
			),			
			array(
				'name'    => 'rbfw_text_password',
				'label'   => __( 'Password', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Password.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Password'
			),
			array(
				'name'    => 'rbfw_text_forget_password',
				'label'   => __( 'Forgot password?', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Forgot password?</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Forgot password?'
			),
			array(
				'name'    => 'rbfw_text_log_in',
				'label'   => __( 'Log In', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Log In</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Log In'
			),	
			array(
				'name'    => 'rbfw_text_registration_information',
				'label'   => __( 'Registration Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Registration Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Registration Information'
			),	
			array(
				'name'    => 'rbfw_text_already_have_account_with_us',
				'label'   => __( 'Do you already have an account with us?', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Do you already have an account with us?</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Do you already have an account with us?'
			),	
			array(
				'name'    => 'rbfw_text_thankyou_ur_order_received',
				'label'   => __( 'Thank you. Your order has been received.', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Thank you. Your order has been received.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Thank you. Your order has been received.'
			),
			array(
				'name'    => 'rbfw_text_order_received',
				'label'   => __( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order Information'
			),
			array(
				'name'    => 'rbfw_text_order_number',
				'label'   => __( 'Order number', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order number</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order number'
			),
			array(
				'name'    => 'rbfw_text_order_created_date',
				'label'   => __( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order created date</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order created date'
			),
			array(
				'name'    => 'rbfw_text_name',
				'label'   => __( 'Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Name</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Name'
			),
			array(
				'name'    => 'rbfw_text_email',
				'label'   => __( 'Email', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Email</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Email'
			),
			array(
				'name'    => 'rbfw_text_payment_method',
				'label'   => __( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Payment method</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Payment method'
			),
			array(
				'name'    => 'rbfw_text_item_information',
				'label'   => __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Item Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Information'
			),
			array(
				'name'    => 'rbfw_text_item_name',
				'label'   => __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Item Name</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Name'
			),
			array(
				'name'    => 'rbfw_text_item_type',
				'label'   => __( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Item Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Type'
			),
			array(
				'name'    => 'rbfw_text_rent_information',
				'label'   => __( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Rent Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent Information'
			),
			array(
				'name'    => 'rbfw_text_extra_service_information',
				'label'   => __( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Extra Service Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Extra Service Information'
			),
			array(
				'name'    => 'rbfw_text_start_date_and_time',
				'label'   => __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Start Date and Time</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Start Date and Time'
			),
			array(
				'name'    => 'rbfw_text_end_date_and_time',
				'label'   => __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>End Date and Time</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'End Date and Time'
			),
			array(
				'name'    => 'rbfw_text_includes',
				'label'   => __( 'Includes', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Includes</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Includes'
			),
			array(
				'name'    => 'rbfw_text_payment_id',
				'label'   => __( 'Payment ID', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Payment ID</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Payment ID'
			),
			array(
				'name'    => 'rbfw_text_download_booking_receipt',
				'label'   => __( 'Download Booking Receipt', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Download Booking Receipt</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Download Booking Receipt'
			),
			array(
				'name'    => 'rbfw_text_ur_order_has_been_received',
				'label'   => __( 'Your order has been received!', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Your order has been received!</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Your order has been received!'
			),
			array(
				'name'    => 'rbfw_text_status',
				'label'   => __( 'Status', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Status</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Status'
			),
			array(
				'name'    => 'rbfw_text_order_succesful_msg',
				'label'   => __( 'Order successful, redirecting...', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Order successful, redirecting...</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Order successful, redirecting...'
			),	
			array(
				'name'    => 'rbfw_text_booking_information',
				'label'   => __( 'Booking Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Booking Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booking Information'
			),
			array(
				'name'    => 'rbfw_text_pin',
				'label'   => __( 'PIN', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>PIN</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'PIN'
			),	
			array(
				'name'    => 'rbfw_text_customer_information',
				'label'   => __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Customer Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Customer Information'
			),
			array(
				'name'    => 'rbfw_text_phone',
				'label'   => __( 'Phone', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Phone</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Phone'
			),
			array(
				'name'    => 'rbfw_text_address',
				'label'   => __( 'Address', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Address</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Address'
			),
			array(
				'name'    => 'rbfw_text_book_online',
				'label'   => __( 'Book online', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Book online</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book online'
			),
			array(
				'name'    => 'rbfw_text_real_time_availability',
				'label'   => __( 'Real-time availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Real-time availability</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Real-time availability'
			),
			array(
				'name'    => 'rbfw_text_instant_confirmation',
				'label'   => __( 'Instant confirmation', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Instant confirmation</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Instant confirmation'
			),
			array(
				'name'    => 'rbfw_text_click_date_to_browse_availability',
				'label'   => __( 'Click a date to browse availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Click a date to browse availability</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Click a date to browse availability'
			),
			array(
				'name'    => 'rbfw_text_daylong_price',
				'label'   => __( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Day-long price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day-long price'
			),
			array(
				'name'    => 'rbfw_text_daynight_price',
				'label'   => __( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Day-night price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Day-night price'
			),	
			array(
				'name'    => 'rbfw_text_rent_type',
				'label'   => __( 'Rent Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Rent Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Rent Type'
			),
			array(
				'name'    => 'rbfw_text_price',
				'label'   => __( 'Price', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Price</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Price'
			),
			array(
				'name'    => 'rbfw_text_back_to_previous_step',
				'label'   => __( 'Back to Previous Step', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Back to Previous Step</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Back to Previous Step'
			),
			array(
				'name'    => 'rbfw_text_you_selected',
				'label'   => __( 'You selected', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>You selected</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'You selected'
			),
			array(
				'name'    => 'rbfw_text_service_name',
				'label'   => __( 'Service Name', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Service Name.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Service Name'
			),
			array(
				'name'    => 'rbfw_text_general_information',
				'label'   => __( 'General Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>General Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'General Information'
			),
			array(
				'name'    => 'rbfw_text_billing_information',
				'label'   => __( 'Billing Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Billing Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Billing Information'
			),
			array(
				'name'    => 'rbfw_text_variation_information',
				'label'   => __( 'Variation Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Variation Information.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Variation Information'
			),
			array(
				'name'    => 'rbfw_text_item_quantity',
				'label'   => __( 'Item Quantity', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Item Quantity.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Item Quantity'
			),
			array(
				'name'    => 'rbfw_text_check_availability',
				'label'   => __( 'Check Availability', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Check Availability.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Check Availability'
			),
			array(
				'name'    => 'rbfw_text_item_not_available',
				'label'   => __( 'Sorry, this item is out of stock.', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Sorry, this item is out of stock.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Sorry, this item is out of stock.'
			),
			array(
				'name'    => 'rbfw_text_out_of_stock',
				'label'   => __( 'Out of stock', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Out of stock.</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Out of stock'
			),
			array(
				'name'    => 'rbfw_text_available_qty_is',
				'label'   => __( 'Available Quantity is:', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Available Quantity is:</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Available Quantity is: '
			),
			array(
				'name'    => 'rbfw_text_left_qty',
				'label'   => __( 'Quantity Left', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Quantity Left</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Quantity Left'
			),
			array(
				'name'    => 'rbfw_text_booked',
				'label'   => __( 'Booked', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Booked</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Booked'
			),
			array(
				'name'    => 'rbfw_text_type_information',
				'label'   => __( 'Type Information', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Type Information</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Type Information'
			),
			array(
				'name'    => 'rbfw_text_discount',
				'label'   => __( 'Discount', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Discount</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Discount'
			),
			array(
				'name'    => 'rbfw_text_discount_type',
				'label'   => __( 'Discount Type', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Discount Type</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Discount Type'
			),
			array(
				'name'    => 'rbfw_text_book_over',
				'label'   => __( 'Book over', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Book over</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Book over'
			),
			array(
				'name'    => 'rbfw_text_and',
				'label'   => __( 'and', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>and</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'and'
			),
			array(
				'name'    => 'rbfw_text_save',
				'label'   => __( 'save', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>save</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'save'
			),
			array(
				'name'    => 'rbfw_text_day',
				'label'   => __( 'day', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>day</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'day'
			),
			array(
				'name'    => 'rbfw_text_days',
				'label'   => __( 'days', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>days</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'days'
			),
			array(
				'name'    => 'rbfw_text_min_number_days_have_to_book',
				'label'   => __( 'Minimum number of days have to book is', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Minimum number of days have to book is</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Minimum number of days have to book is'
			),
			array(
				'name'    => 'rbfw_text_max_number_days_have_to_book',
				'label'   => __( 'Maximum number of days can book is', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Enter the translated text of <strong>Maximum number of days can book is</strong>', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'text',
				'default' => 'Maximum number of days can book is'
			),																																					
		),
		'rbfw_basic_style_settings' => array(
			array(
				'name'    => 'rbfw_primary_color',
				'label'   => __( 'Primary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Select primary color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => ''
			),
			array(
				'name'    => 'rbfw_secondary_color',
				'label'   => __( 'Secondary Color', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Select secondary color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => ''
			),
			array(
				'name'    => 'rbfw_single_rent_column_bg',
				'label'   => __( 'Single Rent Column Background', 'booking-and-rental-manager-for-woocommerce' ),
				'desc'    => __( 'Select column background color', 'booking-and-rental-manager-for-woocommerce' ),
				'type'    => 'color',
				'default' => ''
			),						
		),
		'rbfw_basic_single_rent_page_settings' => array(
			array(
				'name' => 'rbfw_single_rent_tab_style',
				'label' => __( 'Choose Single Rent Tab Style', 'booking-and-rental-manager-for-woocommerce' ),
				'desc' => __( 'Tab style for single rent page.', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => 'select',
				'default' => 'vertical',
				'options' => array(
					'vertical' => 'Vertical',
					'horizontal'  => 'Horizontal'
				),
			),
		),

	);

	return apply_filters('rbfw_settings_field', $settings_fields );
}  