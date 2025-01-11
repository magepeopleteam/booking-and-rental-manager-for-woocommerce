<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function rbfw_taxonomy_register(){
    global $rbfw;
    $label = $rbfw->get_name();
    $labelso = array(
        'name'                       => _x( 'Category','booking-and-rental-manager-for-woocommerce' ),
        'singular_name'              => _x( 'Category','booking-and-rental-manager-for-woocommerce' ),
        'menu_name'                  =>  $label.__( ' Type', 'booking-and-rental-manager-for-woocommerce' ),
    );

    $argso = array(
        'hierarchical'          => true,
        "public" 				=> true,
        'labels'                => $labelso,
        'show_ui'               => true,
        'show_admin_column'     => false,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'rbfw_caregory' ),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
        'rest_base'             => 'rbfw_caregory',
    );
    register_taxonomy('rbfw_item_caregory', 'rbfw_item', $argso);


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
        'show_admin_column'     => false,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'rbfw_location' ),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
        'rest_base'             => 'rbfw_location',
    );
    register_taxonomy('rbfw_item_location', 'rbfw_item', $argso);
}


add_action("init","rbfw_taxonomy_register",10);

add_action("init","insert_dummy_taxonomy_terms");

function insert_dummy_taxonomy_terms() {
    $value = get_option('rbfw_taxonomy_imported');

    if( $value!='yes' ){
        $terms = array('Bike', 'Car', 'Equipment', 'Yacht', 'Boat', 'Helicopter', 'Dress', 'Tent', 'Resort');
        foreach ($terms as $term) {
            // Check if the term already exists
            if (!term_exists($term, 'rbfw_item_caregory')) {
                // Insert the term into the taxonomy
                wp_insert_term($term, 'rbfw_item_caregory');
            }
        }
        update_option('rbfw_taxonomy_imported', 'yes');
    }
}

function add_rbfw_item_caregory_columns( $columns ) {
    $columns['term_id'] = 'Category ID';
    return $columns;
}
add_filter( 'manage_edit-rbfw_item_caregory_columns', 'add_rbfw_item_caregory_columns' );

function add_rbfw_item_caregory_column_content( $content, $column_name, $term_id ) {
    return $term_id;
}

add_filter( 'manage_rbfw_item_caregory_custom_column', 'add_rbfw_item_caregory_column_content', 2, 3 );

