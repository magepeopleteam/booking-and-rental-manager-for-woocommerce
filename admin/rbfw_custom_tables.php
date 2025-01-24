<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }  



function rbfw_custom_tables(){

    $post_type = 'rbfw_item';
    // Register the columns.
    add_filter( "manage_{$post_type}_posts_columns", function ( $defaults ) {
        unset( $defaults['title']  );
        $defaults['Categories'] = 'Categories';
        $defaults['Type'] = 'Type';
        return $defaults;
    } );
    // Handle the value for each of the new columns.
    add_action("manage_{$post_type}_posts_custom_column", function ($column_name, $post_id) {
        if ($column_name == 'Categories') {
            $categories = get_post_meta($post_id, 'rbfw_categories', true);

            // Ensure $categories is an array before processing
            if (is_array($categories)) {
                $categories = implode(', ', $categories);
            } else {
                $categories = ''; // Fallback if not an array
            }

            // Output the escaped value or fallback
            echo !empty($categories) ? esc_html($categories) : '-';
        }

        if ($column_name == 'Type') {
            $item_type = get_post_meta($post_id, 'rbfw_item_type', true);

            // Ensure $item_type is a string and output it
            echo !empty($item_type) ? esc_html($item_type) : '-';
        }
    }, 10, 2);

}


//add_action("init","rbfw_custom_tables",0);

