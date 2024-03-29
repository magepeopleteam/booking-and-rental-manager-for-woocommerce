<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }  


function rbfw_tax(){
global $rbfw;

	$labelso = array(
		'name'                       => _x( 'Location','booking-and-rental-manager-for-woocommerce' ),
		'singular_name'              => _x( 'Location','booking-and-rental-manager-for-woocommerce' ),
		'menu_name'                  => __( 'Location', 'booking-and-rental-manager-for-woocommerce' ),
	);

	$argso = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $labelso,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'rbfw_location' ),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
		'rest_base'             => 'rbfw_location',		
	);
	register_taxonomy('rbfw_item_location', 'rbfw_item', $argso);

}
add_action("init","rbfw_tax",10);


function rbfw_cat(){
    global $rbfw;

    $labelso = array(
        'name'                       => _x( 'Category','booking-and-rental-manager-for-woocommerce' ),
        'singular_name'              => _x( 'Category','booking-and-rental-manager-for-woocommerce' ),
        'menu_name'                  => __( 'Category', 'booking-and-rental-manager-for-woocommerce' ),
    );

    $argso = array(
        'hierarchical'          => true,
        "public" 				=> true,
        'labels'                => $labelso,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'rbfw_caregory' ),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
        'rest_base'             => 'rbfw_caregory',
    );
    register_taxonomy('rbfw_item_caregory', 'rbfw_item', $argso);

}
add_action("init","rbfw_cat",10);

