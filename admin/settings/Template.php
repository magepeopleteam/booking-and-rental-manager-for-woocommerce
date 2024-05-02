<?php

$rbfw_template_info_boxs = array(
			'page_nav' => __( 'Template', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Template Settings', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(
						array(
							'id'          => 'rbfw_single_template',
							'title'       => __( 'Template:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( '', 'booking-and-rental-manager-for-woocommerce' ),
							'type'        => 'select',
							'args' => RBFW_Function::all_details_template(),
						),
					)
				),
			),
		);

$rbfw_template_info_boxs_args = array(
        'meta_box_id'    => 'rbfw_template_settings_meta_boxes',
        'meta_box_title' => '<i class="fa-solid fa-pager"></i>' . __( 'Template', 'booking-and-rental-manager-for-woocommerce' ),
        'screen'         => array( 'rbfw_item' ),
        'context'        => 'normal',
        'priority'       => 'high',
        'callback_args'  => array(),
        'nav_position'   => 'none',
        'item_name'      => "MagePeople",
        'item_version'   => "2.0",
        'panels'         => array(
            'rbfw_basic_meta_boxs' => $rbfw_template_info_boxs
        )
    );
    new RMFWAddMetaBox( $rbfw_template_info_boxs_args );