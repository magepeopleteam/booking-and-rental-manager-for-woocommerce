<?php

 $rbfw_tax_meta_boxs_args = array(
            'meta_box_id'    => 'rbfw_tax',
            'meta_box_title' => '<i class="fa-solid fa-dollar-sign"></i>' .__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
            'screen'         => array( 'rbfw_item' ),
            'context'        => 'normal',
            'priority'       => 'low',
            'callback_args'  => array(),
            'nav_position'   => 'none',
            'item_name'      => "MagePeople",
            'item_version'   => "2.0",
        );
        new RMFWAddMetaBox( $rbfw_tax_meta_boxs_args );