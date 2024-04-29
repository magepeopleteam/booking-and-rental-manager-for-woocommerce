<?php

$rbfw_gallery_meta_boxs = array(
			'page_nav' => __( 'Gallery', 'booking-and-rental-manager-for-woocommerce' ),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title'       => __( 'Image Gallery', 'booking-and-rental-manager-for-woocommerce' ),
					'description' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
					'options'     => array(

						array(
							'id'          => 'rbfw_gallery_images',
							'title'       => __( 'Gallery Images:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please upload images for gallery', 'booking-and-rental-manager-for-woocommerce' ),
							'placeholder' => 'https://via.placeholder.com/1000x500',
							'type'        => 'media_multi',
						),
						array(
							'id'          => 'rbfw_gallery_images_additional',
							'title'       => __( 'Additional Gallery Images:', 'booking-and-rental-manager-for-woocommerce' ),
							'details'     => __( 'Please upload images for gallery', 'booking-and-rental-manager-for-woocommerce' ),
							'placeholder' => 'https://via.placeholder.com/1000x500',
							'type'        => 'media_multi',
						),

					)
				),

			),
		);
		
		$rbfw_gallery_meta_boxs_args = array(
			'meta_box_id'    => 'rbfw_gallery_images_meta_boxes',
			'meta_box_title' => '<i class="fa-solid fa-images"></i>' .__( 'Gallery', 'booking-and-rental-manager-for-woocommerce' ),
			'screen'         => array( 'rbfw_item' ),
			'context'        => 'normal',
			'priority'       => 'low',
			'callback_args'  => array(),
			'nav_position'   => 'none', // right, top, left, none
			'item_name'      => "MagePeople",
			'item_version'   => "2.0",
			'panels'         => array(
				'rbfw_gallery_meta_boxs' => $rbfw_gallery_meta_boxs
			),
		);
		new RMFWAddMetaBox( $rbfw_gallery_meta_boxs_args );