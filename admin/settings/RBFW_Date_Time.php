<?php

$rbfw_date_time_meta_boxs = array(
			'page_nav' => __( 'Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Date Configuration', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(
						array(
							'id'      => 'rbfw_time_slot_switch',
							'title'   => __( 'Time Slot', 'booking-and-rental-manager-for-woocommerce' ),
							'details' => __( 'It enables/disables the time slot for Bike/Car Single Day and Appointment rent type.', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'switch',
							'args' => array(
								'on' => 'On',
								'off' => 'Off',
							),
							'default' => 'on',
						),
						array(
							'id'       => 'rdfw_available_time',
							'title'    => __( 'Available Time Slot', 'booking-and-rental-manager-for-woocommerce' ),
							'details'  => __( 'Please select the availabe time slots', 'booking-and-rental-manager-for-woocommerce' ),
							'type'     => 'time_slot',
							'multiple' => true,
							'default'  => '',
							'args'     => rbfw_get_available_time_slots_local()
						),

					)
				),

			),
		);

		$rbfw_date_time_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_date_settings_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-calendar-days"></i>' .__( 'Date & Time', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none',
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_date_meta_boxs' => $rbfw_date_time_meta_boxs
			),
		);
		new RMFWAddMetaBox( $rbfw_date_time_meta_boxs_args );


function rbfw_get_available_time_slots_local(){

	$rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
	asort($rbfw_time_slots);

	return $rbfw_time_slots;
}