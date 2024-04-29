<?php

    $rbfw_off_days_meta_boxs_args = array(
        'meta_box_id'    => 'travel_off_days',
        'meta_box_title' => '<i class="fa-regular fa-calendar-xmark"></i>' .__( 'Off Days', 'booking-and-rental-manager-for-woocommerce' ),
        'screen'         => array( 'rbfw_item' ),
        'context'        => 'normal',
        'priority'       => 'low',
        'callback_args'  => array(),
        'nav_position'   => 'none',
        'item_name'      => "MagePeople",
        'item_version'   => "2.0",

    );
    new RMFWAddMetaBox( $rbfw_off_days_meta_boxs_args );