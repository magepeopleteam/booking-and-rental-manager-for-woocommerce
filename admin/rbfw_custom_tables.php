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
    add_action( "manage_{$post_type}_posts_custom_column", function ( $column_name, $post_id ) {
        if ( $column_name == 'Categories' ) {
            $categories = implode(', ', get_post_meta($post_id,'rbfw_categories',true));
            if($categories){
                echo $categories;
            }else{
                echo '-';
            }
        }
        if ( $column_name == 'Type' ) {
            echo get_post_meta($post_id,'rbfw_item_type',true);
        }
    }, 10, 2 );

}


//add_action("init","rbfw_custom_tables",0);

