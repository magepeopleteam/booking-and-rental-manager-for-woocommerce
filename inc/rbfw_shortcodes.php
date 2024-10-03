<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

/******************************
 * Rent List Shortcode
 ******************************/
add_shortcode('rent-list', 'rbfw_rent_list_shortcode_func');
/******************************
 * Rent List Shortcode
 ******************************/
add_shortcode('search-result', 'rbfw_rent_list_shortcode_func');
function rbfw_rent_list_shortcode_func($atts = null) {

    $attributes = shortcode_atts( array(
        'style' => 'grid',
        'show'  => -1,
        'order' => 'DESC',
        'orderby' => '',
        'meta_key' => '',
        'type'  => '',
        'location' => '',
        'category' => '',
        'cat_ids' => '',
        'columns' => '',
        'left-filter' => '',
    ), $atts );

    $style  = $attributes['style'];
    $show   = $attributes['show'];
    $order  = $attributes['order'];
    $orderby  = $attributes['orderby'];
    $meta_key  = $attributes['meta_key'];
    $type   = $attributes['type'];
    $location   = $attributes['location'];
    $category   = $attributes['category'];
    $cat_ids   = $attributes['cat_ids'];
    $columns   = $attributes['columns'];
    $left_filter   = $attributes['left-filter'];

    if(!$category){
        $category  = $cat_ids;
    }

    $location = !empty( $_GET['rbfw_search_location'] ) ? strip_tags( $_GET['rbfw_search_location'] ) : $location;
    if( $category ){
        $category = !empty( $_GET['rbfw_search_type'] ) ? strip_tags( trim( $_GET['rbfw_search_type'] ) ) : $category ;
    }else{
        $search_category = !empty( $_GET['rbfw_search_type'] ) ? strip_tags( trim( $_GET['rbfw_search_type'] ) ) : '' ;
    }

    $pickup_date = !empty( $_GET['rbfw-pickup-date'] ) ? strip_tags( trim( $_GET['rbfw-pickup-date'] ) ) : '';
    if( $pickup_date !== 'Pickup date' && !empty( $pickup_date )) {
        $date = DateTime::createFromFormat('F j, Y', $pickup_date );
        $pickup_date = $date->format('d-m-Y');
    }

    if( !empty( $pickup_date ) && $pickup_date !== 'Pickup date' ){
        $date_time = new DateTime( $pickup_date );
        $day_of_week = strtolower( $date_time->format('l' ) );
        $date_range_query = array(
            'relation' => 'OR', // Either condition can be true
            array(
                'key'     => 'rbfw_off_days',
                'compare' => 'NOT EXISTS', // Meta key doesn't exist
            ),
            array(
                'key'     => 'rbfw_off_days',
                'value'   => $day_of_week,
                'compare' => 'NOT LIKE', // Meta key exists, but doesn't contain the day of the week
            ),
        );
    } else {
        $date_range_query = '';
    }

    $rent_type = !empty($type) ? array(
        'key' => 'rbfw_item_type',
        'value' => $type,
        'compare' => '==',
    ) : '';
    $location_query = !empty($location) ? array(
        'key' => 'rbfw_pickup_data',
        'value' => $location,
        'compare' => 'LIKE'
    ) : '';

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => $show,
        'paged' => $paged,
        'meta_key' => $meta_key,
        'orderby' => $orderby,
        'order' => $order,
        'meta_query' => array(
            'relation' => 'AND',
            $rent_type,
            $location_query,
            $date_range_query,
        )
    );



    if( $category ){
        if(!empty($category)):
            $category = explode(',', $category);
            foreach ($category as $cat){
                $category_name=isset(get_term($cat)->name) ? get_term($cat)->name : '';
                $args['meta_query'][] = array(
                    'key' => 'rbfw_categories',
                    'value' => $category_name,
                    'compare' => 'LIKE'
                );
            }
        endif;
    }else{
        if( !empty( $search_category ) ):
            $search_category_name=$search_category;
            $args['meta_query'][] = array(
                'key' => 'rbfw_categories',
                'value' => $search_category_name,
                'compare' => 'LIKE'
            );
        endif;
    }



    $query = new WP_Query($args);
    $total_posts = $query->found_posts;
    $post_count = $query->post_count;

    $j = 1;
    if(isset($_COOKIE['rbfw_rent_item_list_grid'])) {
        $rbfw_rent_item_list_grid = $_COOKIE['rbfw_rent_item_list_grid'];
    }else{
        $rbfw_rent_item_list_grid = '';
    }

    if( $rbfw_rent_item_list_grid === '' ){
        if( $style == 'grid' ){
            $image_holder = 'rbfw_rent_list_grid_view_top';
            $rent_item_info = 'rbfw_inner_details';
            $rent_item_list_info = 'rbfw_rent_list_info';
            $is_display = 'none';
            $style = 'grid';
            $is_grid_selected = 'selected_list_grid';
            $is_list_selected = '';
            $display_cat_features = 3;
        }else{
            $image_holder = 'rbfw_rent_list_lists_images';
            $rent_item_info = 'rbfw_rent_list_lists_info';
            $rent_item_list_info = 'rbfw_rent_item_content_list_bottom';
            $is_display = 'grid';
            $style = 'list';
            $is_grid_selected = '';
            $is_list_selected = 'selected_list_grid';

            $display_cat_features = 5;
        }
    }else{
        if( $rbfw_rent_item_list_grid == 'rbfw_rent_item_grid' ){
            $image_holder = 'rbfw_rent_list_grid_view_top';
            $rent_item_info = 'rbfw_inner_details';
            $rent_item_list_info = 'rbfw_rent_list_info';
            $is_display = 'none';
            $style = 'grid';
            $is_grid_selected = 'selected_list_grid';
            $is_list_selected = '';

            $display_cat_features = 3;
        }else{
            $image_holder = 'rbfw_rent_list_lists_images';
            $rent_item_info = 'rbfw_rent_list_lists_info';
            $rent_item_list_info = 'rbfw_rent_item_content_list_bottom';
            $is_display = 'grid';
            $style = 'list';
            $is_grid_selected = '';
            $is_list_selected = 'selected_list_grid';

            $display_cat_features = 5;
        }
    }

    ob_start();
//echo '<pre>';print_r($query);echo '</pre>';
    $grid_class = 'rbfw-w-33';

    if($columns){
        $grid_class = ($columns==1 || $columns==2)?'rbfw-w-50':(($columns==3)?'rbfw-w-33':(($columns==4)?'rbfw-w-25':(($columns==5)?'rbfw-w-20':'rbfw-w-20')));
    }


    $shoe_result =  $total_posts. ' results. Showing '.$post_count. ' of '. $total_posts. ' of total';
    ?>
    <div class="rbfw_rent_show_result_list_grid_icon_holder">

        <div class="rbfw_popup_wrapper" id="rbfw_popup_wrapper">
            <div class="rbfw_rent_cat_info_popup">
                <span class="rbfw_popup_close_btn" id="rbfw_popup_close_btn">&times;</span>
                <div id="rbfw_popup_content">

                </div>
            </div>
        </div>

        <div class="rbfw_shoe_result_text" id="rbfw_shoe_result_text">
            <span> <?php echo esc_attr( $shoe_result );?></span>
        </div>
        <div class="rbfw_rent_list_grid_icon_holder">
            <div class="rbfw_rent_items_list_grid rbfw_rent_items_grid <?php echo esc_attr( $is_grid_selected )?>" id="rbfw_rent_items_grid">
                <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.3783 2C5.3905 2 5.40273 2 5.415 2L7.62171 2C8.01734 1.99998 8.37336 1.99996 8.66942 2.02454C8.98657 2.05088 9.32336 2.11052 9.65244 2.28147C10.109 2.51866 10.4813 2.89096 10.7185 3.34757C10.8895 3.67665 10.9491 4.01343 10.9755 4.33059C11 4.62664 11 4.98265 11 5.37828V7.62172C11 8.01735 11 8.37337 10.9755 8.66942C10.9491 8.98657 10.8895 9.32336 10.7185 9.65244C10.4813 10.109 10.109 10.4813 9.65244 10.7185C9.32336 10.8895 8.98657 10.9491 8.66942 10.9755C8.37337 11 8.01735 11 7.62172 11H5.37828C4.98265 11 4.62664 11 4.33059 10.9755C4.01343 10.9491 3.67665 10.8895 3.34757 10.7185C2.89096 10.4813 2.51866 10.109 2.28147 9.65244C2.11052 9.32336 2.05088 8.98657 2.02454 8.66942C1.99996 8.37336 1.99998 8.01734 2 7.62171L2 5.415C2 5.40273 2 5.3905 2 5.3783C1.99998 4.98266 1.99996 4.62664 2.02454 4.33059C2.05088 4.01343 2.11052 3.67665 2.28147 3.34757C2.51866 2.89096 2.89096 2.51866 3.34757 2.28147C3.67665 2.11052 4.01343 2.05088 4.33059 2.02454C4.62664 1.99996 4.98266 1.99998 5.3783 2ZM4.27752 4.05297C4.27226 4.05488 4.27001 4.05604 4.26952 4.0563C4.17819 4.10373 4.10373 4.17819 4.0563 4.26952C4.05604 4.27001 4.05488 4.27226 4.05297 4.27752C4.05098 4.28299 4.04767 4.29312 4.04372 4.30961C4.03541 4.34427 4.02554 4.40145 4.01768 4.49611C4.00081 4.69932 4 4.9711 4 5.415V7.585C4 8.02891 4.00081 8.30068 4.01768 8.5039C4.02554 8.59855 4.03541 8.65574 4.04372 8.6904C4.04767 8.70688 4.05098 8.71701 4.05297 8.72249C4.05488 8.72775 4.05604 8.72999 4.0563 8.73049C4.10373 8.82181 4.17819 8.89627 4.26952 8.94371C4.27001 8.94397 4.27226 8.94513 4.27752 8.94704C4.28299 8.94903 4.29312 8.95234 4.30961 8.95629C4.34427 8.96459 4.40145 8.97446 4.49611 8.98232C4.69932 8.9992 4.9711 9 5.415 9H7.585C8.02891 9 8.30068 8.9992 8.5039 8.98232C8.59855 8.97446 8.65574 8.96459 8.6904 8.95629C8.70688 8.95234 8.71701 8.94903 8.72249 8.94704C8.72775 8.94513 8.72999 8.94397 8.73049 8.94371C8.82181 8.89627 8.89627 8.82181 8.94371 8.73049C8.94397 8.72999 8.94513 8.72775 8.94704 8.72249C8.94903 8.71701 8.95234 8.70688 8.95629 8.6904C8.96459 8.65574 8.97446 8.59855 8.98232 8.5039C8.9992 8.30068 9 8.02891 9 7.585V5.415C9 4.9711 8.9992 4.69932 8.98232 4.49611C8.97446 4.40145 8.96459 4.34427 8.95629 4.30961C8.95234 4.29312 8.94903 4.28299 8.94704 4.27752C8.94513 4.27226 8.94397 4.27001 8.94371 4.26952C8.89627 4.17819 8.82181 4.10373 8.73049 4.0563C8.72999 4.05604 8.72775 4.05488 8.72249 4.05297C8.71701 4.05098 8.70688 4.04767 8.6904 4.04372C8.65574 4.03541 8.59855 4.02554 8.5039 4.01768C8.30068 4.00081 8.02891 4 7.585 4H5.415C4.9711 4 4.69932 4.00081 4.49611 4.01768C4.40145 4.02554 4.34427 4.03541 4.30961 4.04372C4.29312 4.04767 4.28299 4.05098 4.27752 4.05297ZM16.3783 2H18.6217C19.0173 1.99998 19.3734 1.99996 19.6694 2.02454C19.9866 2.05088 20.3234 2.11052 20.6524 2.28147C21.109 2.51866 21.4813 2.89096 21.7185 3.34757C21.8895 3.67665 21.9491 4.01343 21.9755 4.33059C22 4.62665 22 4.98267 22 5.37832V7.62168C22 8.01733 22 8.37336 21.9755 8.66942C21.9491 8.98657 21.8895 9.32336 21.7185 9.65244C21.4813 10.109 21.109 10.4813 20.6524 10.7185C20.3234 10.8895 19.9866 10.9491 19.6694 10.9755C19.3734 11 19.0174 11 18.6217 11H16.3783C15.9827 11 15.6266 11 15.3306 10.9755C15.0134 10.9491 14.6766 10.8895 14.3476 10.7185C13.891 10.4813 13.5187 10.109 13.2815 9.65244C13.1105 9.32336 13.0509 8.98657 13.0245 8.66942C13 8.37337 13 8.01735 13 7.62172V5.37828C13 4.98265 13 4.62664 13.0245 4.33059C13.0509 4.01344 13.1105 3.67665 13.2815 3.34757C13.5187 2.89096 13.891 2.51866 14.3476 2.28147C14.6766 2.11052 15.0134 2.05088 15.3306 2.02454C15.6266 1.99996 15.9827 1.99998 16.3783 2ZM15.2775 4.05297C15.2723 4.05488 15.27 4.05604 15.2695 4.0563C15.1782 4.10373 15.1037 4.17819 15.0563 4.26952C15.056 4.27001 15.0549 4.27226 15.053 4.27752C15.051 4.28299 15.0477 4.29312 15.0437 4.30961C15.0354 4.34427 15.0255 4.40145 15.0177 4.49611C15.0008 4.69932 15 4.9711 15 5.415V7.585C15 8.02891 15.0008 8.30068 15.0177 8.5039C15.0255 8.59855 15.0354 8.65574 15.0437 8.6904C15.0477 8.70688 15.051 8.71701 15.053 8.72249C15.0549 8.72775 15.056 8.72999 15.0563 8.73049C15.1037 8.82181 15.1782 8.89627 15.2695 8.94371C15.27 8.94397 15.2723 8.94513 15.2775 8.94704C15.283 8.94903 15.2931 8.95234 15.3096 8.95629C15.3443 8.96459 15.4015 8.97446 15.4961 8.98232C15.6993 8.9992 15.9711 9 16.415 9H18.585C19.0289 9 19.3007 8.9992 19.5039 8.98232C19.5986 8.97446 19.6557 8.96459 19.6904 8.95629C19.7069 8.95234 19.717 8.94903 19.7225 8.94704C19.7277 8.94513 19.73 8.94397 19.7305 8.94371C19.8218 8.89627 19.8963 8.82181 19.9437 8.73049C19.944 8.72999 19.9451 8.72775 19.947 8.72249C19.949 8.71701 19.9523 8.70688 19.9563 8.6904C19.9646 8.65573 19.9745 8.59855 19.9823 8.5039C19.9992 8.30068 20 8.02891 20 7.585V5.415C20 4.9711 19.9992 4.69932 19.9823 4.49611C19.9745 4.40145 19.9646 4.34427 19.9563 4.30961C19.9523 4.29312 19.949 4.28299 19.947 4.27752C19.9451 4.27226 19.944 4.27001 19.9437 4.26952C19.8963 4.17819 19.8218 4.10373 19.7305 4.0563C19.73 4.05604 19.7277 4.05488 19.7225 4.05297C19.717 4.05098 19.7069 4.04767 19.6904 4.04372C19.6557 4.03541 19.5986 4.02554 19.5039 4.01768C19.3007 4.00081 19.0289 4 18.585 4H16.415C15.9711 4 15.6993 4.00081 15.4961 4.01768C15.4015 4.02554 15.3443 4.03541 15.3096 4.04372C15.2931 4.04767 15.283 4.05098 15.2775 4.05297ZM5.37828 13H7.62172C8.01735 13 8.37337 13 8.66942 13.0245C8.98657 13.0509 9.32336 13.1105 9.65244 13.2815C10.109 13.5187 10.4813 13.891 10.7185 14.3476C10.8895 14.6766 10.9491 15.0134 10.9755 15.3306C11 15.6266 11 15.9827 11 16.3783V18.6217C11 19.0174 11 19.3734 10.9755 19.6694C10.9491 19.9866 10.8895 20.3234 10.7185 20.6524C10.4813 21.109 10.109 21.4813 9.65244 21.7185C9.32336 21.8895 8.98657 21.9491 8.66942 21.9755C8.37336 22 8.01733 22 7.62168 22H5.37832C4.98267 22 4.62665 22 4.33059 21.9755C4.01343 21.9491 3.67665 21.8895 3.34757 21.7185C2.89096 21.4813 2.51866 21.109 2.28147 20.6524C2.11052 20.3234 2.05088 19.9866 2.02454 19.6694C1.99996 19.3734 1.99998 19.0173 2 18.6217V16.3783C1.99998 15.9827 1.99996 15.6266 2.02454 15.3306C2.05088 15.0134 2.11052 14.6766 2.28147 14.3476C2.51866 13.891 2.89096 13.5187 3.34757 13.2815C3.67665 13.1105 4.01344 13.0509 4.33059 13.0245C4.62664 13 4.98265 13 5.37828 13ZM4.27752 15.053C4.27226 15.0549 4.27001 15.056 4.26952 15.0563C4.17819 15.1037 4.10373 15.1782 4.0563 15.2695C4.05604 15.27 4.05488 15.2723 4.05297 15.2775C4.05098 15.283 4.04767 15.2931 4.04372 15.3096C4.03541 15.3443 4.02554 15.4015 4.01768 15.4961C4.00081 15.6993 4 15.9711 4 16.415V18.585C4 19.0289 4.00081 19.3007 4.01768 19.5039C4.02554 19.5986 4.03541 19.6557 4.04372 19.6904C4.04767 19.7069 4.05098 19.717 4.05297 19.7225C4.05488 19.7277 4.05604 19.73 4.0563 19.7305C4.10373 19.8218 4.17819 19.8963 4.26952 19.9437C4.27001 19.944 4.27226 19.9451 4.27752 19.947C4.28299 19.949 4.29312 19.9523 4.30961 19.9563C4.34427 19.9646 4.40145 19.9745 4.49611 19.9823C4.69932 19.9992 4.9711 20 5.415 20H7.585C8.02891 20 8.30068 19.9992 8.5039 19.9823C8.59855 19.9745 8.65573 19.9646 8.6904 19.9563C8.70688 19.9523 8.71701 19.949 8.72249 19.947C8.72775 19.9451 8.72999 19.944 8.73049 19.9437C8.82181 19.8963 8.89627 19.8218 8.94371 19.7305C8.94397 19.73 8.94513 19.7277 8.94704 19.7225C8.94903 19.717 8.95234 19.7069 8.95629 19.6904C8.96459 19.6557 8.97446 19.5986 8.98232 19.5039C8.9992 19.3007 9 19.0289 9 18.585V16.415C9 15.9711 8.9992 15.6993 8.98232 15.4961C8.97446 15.4015 8.96459 15.3443 8.95629 15.3096C8.95234 15.2931 8.94903 15.283 8.94704 15.2775C8.94513 15.2723 8.94397 15.27 8.94371 15.2695C8.89627 15.1782 8.82181 15.1037 8.73049 15.0563C8.73026 15.0562 8.72968 15.0559 8.72861 15.0554C8.72733 15.0548 8.72536 15.054 8.72249 15.053C8.71701 15.051 8.70688 15.0477 8.6904 15.0437C8.65574 15.0354 8.59855 15.0255 8.5039 15.0177C8.30068 15.0008 8.02891 15 7.585 15H5.415C4.9711 15 4.69932 15.0008 4.49611 15.0177C4.40145 15.0255 4.34427 15.0354 4.30961 15.0437C4.29312 15.0477 4.28299 15.051 4.27752 15.053ZM16.3783 13H18.6217C19.0174 13 19.3734 13 19.6694 13.0245C19.9866 13.0509 20.3234 13.1105 20.6524 13.2815C21.109 13.5187 21.4813 13.891 21.7185 14.3476C21.8895 14.6766 21.9491 15.0134 21.9755 15.3306C22 15.6266 22 15.9827 22 16.3783V18.6217C22 19.0173 22 19.3734 21.9755 19.6694C21.9491 19.9866 21.8895 20.3234 21.7185 20.6524C21.4813 21.109 21.109 21.4813 20.6524 21.7185C20.3234 21.8895 19.9866 21.9491 19.6694 21.9755C19.3734 22 19.0173 22 18.6217 22H16.3783C15.9827 22 15.6266 22 15.3306 21.9755C15.0134 21.9491 14.6766 21.8895 14.3476 21.7185C13.891 21.4813 13.5187 21.109 13.2815 20.6524C13.1105 20.3234 13.0509 19.9866 13.0245 19.6694C13 19.3734 13 19.0174 13 18.6217V16.3783C13 15.9827 13 15.6266 13.0245 15.3306C13.0509 15.0134 13.1105 14.6766 13.2815 14.3476C13.5187 13.891 13.891 13.5187 14.3476 13.2815C14.6766 13.1105 15.0134 13.0509 15.3306 13.0245C15.6266 13 15.9827 13 16.3783 13ZM15.2775 15.053C15.2723 15.0549 15.27 15.056 15.2695 15.0563C15.1782 15.1037 15.1037 15.1782 15.0563 15.2695C15.056 15.27 15.0549 15.2723 15.053 15.2775C15.051 15.283 15.0477 15.2931 15.0437 15.3096C15.0354 15.3443 15.0255 15.4015 15.0177 15.4961C15.0008 15.6993 15 15.9711 15 16.415V18.585C15 19.0289 15.0008 19.3007 15.0177 19.5039C15.0255 19.5986 15.0354 19.6557 15.0437 19.6904C15.0477 19.7069 15.051 19.717 15.053 19.7225C15.0549 19.7277 15.056 19.73 15.0563 19.7305C15.1037 19.8218 15.1782 19.8963 15.2695 19.9437C15.27 19.944 15.2723 19.9451 15.2775 19.947C15.283 19.949 15.2931 19.9523 15.3096 19.9563C15.3443 19.9646 15.4015 19.9745 15.4961 19.9823C15.6993 19.9992 15.9711 20 16.415 20H18.585C19.0289 20 19.3007 19.9992 19.5039 19.9823C19.5986 19.9745 19.6557 19.9646 19.6904 19.9563C19.7069 19.9523 19.717 19.949 19.7225 19.947C19.7277 19.9451 19.73 19.944 19.7305 19.9437C19.8218 19.8963 19.8963 19.8218 19.9437 19.7305C19.944 19.73 19.9451 19.7277 19.947 19.7225C19.949 19.717 19.9523 19.7069 19.9563 19.6904C19.9646 19.6557 19.9745 19.5986 19.9823 19.5039C19.9992 19.3007 20 19.0289 20 18.585V16.415C20 15.9711 19.9992 15.6993 19.9823 15.4961C19.9745 15.4015 19.9646 15.3443 19.9563 15.3096C19.9523 15.2931 19.949 15.283 19.947 15.2775C19.9463 15.2756 19.9458 15.2741 19.9453 15.2729C19.9444 15.2709 19.9439 15.2698 19.9437 15.2695C19.8963 15.1782 19.8218 15.1037 19.7305 15.0563C19.73 15.056 19.7277 15.0549 19.7225 15.053C19.717 15.051 19.7069 15.0477 19.6904 15.0437C19.6557 15.0354 19.5986 15.0255 19.5039 15.0177C19.3007 15.0008 19.0289 15 18.585 15H16.415C15.9711 15 15.6993 15.0008 15.4961 15.0177C15.4015 15.0255 15.3443 15.0354 15.3096 15.0437C15.2931 15.0477 15.283 15.051 15.2775 15.053Z" fill="#0F1729"/>
                </svg>
            </div>
            <div class="rbfw_rent_items_list_grid rbfw_rent_items_list <?php echo esc_attr( $is_list_selected )?>" id="rbfw_rent_items_list">
                <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3 6C3 5.44772 3.44772 5 4 5H20C20.5523 5 21 5.44772 21 6C21 6.55228 20.5523 7 20 7H4C3.44772 7 3 6.55228 3 6ZM3 12C3 11.4477 3.44772 11 4 11H20C20.5523 11 21 11.4477 21 12C21 12.5523 20.5523 13 20 13H4C3.44772 13 3 12.5523 3 12ZM3 18C3 17.4477 3.44772 17 4 17H20C20.5523 17 21 17.4477 21 18C21 18.5523 20.5523 19 20 19H4C3.44772 19 3 18.5523 3 18Z" fill="#0F1729"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="rbfw_rent_item_with_left_filter">
        <?php
        if( $left_filter === 'yes' ){
            $rent_list_wrapper_cls = 'rbfw_rent_list_wrapper_with_left_filter';
            echo rbfw_rent_left_filter();
        }else{
            $rent_list_wrapper_cls = 'rbfw_rent_list_wrapper';
        }
        ?>
        <div class=" <?php echo $rent_list_wrapper_cls.' '.$grid_class ?> rbfw_rent_list_style_<?php echo esc_attr($style); ?>" id="rbfw_rent_list_wrapper">

            <?php
            $d = 1;
            if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();
                $the_content = get_the_content();

                $rbfw_id = get_the_id();

                $expire = 'no';
                $rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';

                if($rbfw_enable_start_end_date=='no'){
                    $rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
                    $rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
                    $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                    $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                    $rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
                    $rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));
                    if($rbfw_event_last_date<$rbfw_todays_date){
                        $expire = 'yes';
                    }
                }
                // load c
                if($expire == 'no'){
//                $grid=RBFW_Function::get_template_path('archive/grid.php');
                    $grid=RBFW_Function::get_template_path('archive/grid_new.php');
//                $list=RBFW_Function::get_template_path('archive/list.php');
                    $list=RBFW_Function::get_template_path('archive/list_new.php');

                    if($style == 'grid'){
                        include($grid);
                    }
                    elseif($style == 'list'){
                        include($list);
                    }
                    else{
                        include( $list );
                    }
                }
                $d++;
                $j++;
            endwhile;
            else:
                ?>
                <div class="rbfw-lsn-new-message-box">
                    <div class="rbfw-lsn-new-message-box-info">
                        <div class="rbfw-lsn-info-tab rbfw-lsn-tip-icon-info" title="error"><i></i></div>
                        <div class="rbfw-lsn-tip-box-info">
                            <p><?php rbfw_string('rbfw_text_nodatafound',__('Sorry, no data found!','booking-and-rental-manager-for-woocommerce')); ?></p>
                        </div>
                    </div>
                </div>
            <?php
            endif;

            wp_reset_query();
            ?>
        </div>
    </div>

    <?php
    $content = ob_get_clean();

    if( isset( $atts['pagination'] ) && $atts['pagination'] == 'yes') {
        $content .= '<div class="pagination rbfw_pagination" id="rbfw_rent_list_pagination">';
        $content .= paginate_links(array(
            'total' => $query->max_num_pages,
            'prev_text' => __('« '), // Optional: Add previous and next text
            'next_text' => __(' »'),
        ));
        $content .= '</div>';
    }
    wp_reset_postdata();

    return $content;
}

/******************************
 * Single Add to Cart Shortcode
 ******************************/
add_shortcode('rent-add-to-cart', 'rbfw_add_to_cart_shortcode_func');

function rbfw_add_to_cart_shortcode_func($atts){

   // echo print_r($atts);exit;



    $attributes = shortcode_atts( array(
        'id' => '',
        'backend' => ''
    ), $atts );



    $post_id = $attributes['id'];
    $backend = $attributes['backend']??0;



    if (defined('add_to_cart_id')) {
        define("add_to_cart_id", $post_id);
    }



    if(empty($post_id)){
        return;
    }

    $rbfw_item_type = get_post_meta($post_id, 'rbfw_item_type', true);


    if(!$backend){
        ob_start();
        do_action( 'woocommerce_before_single_product' );
    }


    if($rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){

        include( RBFW_TEMPLATE_PATH . 'forms/single-day-registration.php' );

        $BikeCarSdclass = new RBFW_BikeCarSd_Function();
        $BikeCarSdclass->rbfw_bike_car_sd_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'bike_car_md' || $rbfw_item_type == 'equipment' || $rbfw_item_type == 'dress' || $rbfw_item_type == 'others'){


        include(  RBFW_TEMPLATE_PATH . 'forms/multi-day-registration.php' );

        $BikeCarMdclass = new RBFW_BikeCarMd_Function();
        $BikeCarMdclass->rbfw_bike_car_md_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'resort'){

        include(  RBFW_TEMPLATE_PATH . 'forms/resort-registration.php' );

        $Resortclass = new RBFW_Resort_Function();
        $Resortclass->rbfw_resort_frontend_scripts($post_id);
    }

    if(!$backend){
        $content = ob_get_clean();
        return $content;
    }

}

/******************************
 * Rent Filter Form Shortcode
 ******************************/
add_shortcode('rbfw-search1', 'rbfw_rent_search_shortcode_func');
function rbfw_rent_search_shortcode_func() {

    $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');
    $search_page_link = get_page_link($search_page_id);
    $location_arr = rbfw_get_location_arr();
    $location = !empty($_GET['rbfw_search_location']) ? strip_tags($_GET['rbfw_search_location']) : '';
    ?>
    <div class="rbfw_search_form_wrap">
        <form class="rbfw_search_form" action="<?php echo esc_url($search_page_link); ?>" method="GET">
            <div class="rbfw_search_form_col">
                <label><?php rbfw_string('rbfw_text_pickup_location',__('Pickup Location','booking-and-rental-manager-for-woocommerce')); ?></label>
                <select name="rbfw_search_location">
                    <?php foreach ( $location_arr as $key => $value ) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php if($location == $key){ echo 'selected'; }?>><?php echo esc_html($value); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="rbfw_search_form_col">
                <label></label>
                <button type="submit" name="rbfw_search_submit" class="rbfw_search_submit"><?php rbfw_string('rbfw_text_search',__('Search','booking-and-rental-manager-for-woocommerce')); ?></button>
            </div>
        </form>
    </div>
    <?php
}

add_shortcode('rbfw_search', 'rbfw_rent_search_shortcode' );
function rbfw_rent_search_shortcode( $attr = null ){

    $search_page_id = rbfw_get_option('search-item-list','rbfw_basic_gen_settings');
    $search_page_link = get_page_link($search_page_id);
    $location = !empty($_GET['rbfw_search_location']) ? strip_tags($_GET['rbfw_search_location']) : '';
    $type = !empty($_GET['rbfw_search_type']) ? strip_tags($_GET['rbfw_search_type']) : '';
    $pickup_date = !empty($_GET['rbfw-pickup-date']) ? strip_tags($_GET['rbfw-pickup-date']) : 'Pickup date';

    ob_start();
    ?>

    <section class="rbfw_rent_item_search_elementor_section">
        <div class="rbfw_rent_item_search_elementor_container">
<!--            <form class="rbfw_search_form_new" action="--><?php //echo esc_url($search_page_link); ?><!--" method="GET">-->
            <form class="rbfw_search_form_new" action="<?php echo get_home_url() . '/search-item-list/';  ?>" method="GET">
                <div class="rbfw_rent_item_search_container">

                    <div class="rbfw_rent_item_searchContentHolder">
                        <div class="rbfw_rent_item_searchTypeLocationHolder">
                            <div class="rbfw_rent_item_search_item">
                                <?php rbfw_get_dropdown_new( 'rbfw_search_type', $type,  'rbfw_rent_item_search_type_location', 'category' );?>
                            </div>
                            <div class="rbfw_rent_item_search_item">
                                <?php rbfw_get_dropdown_new( 'rbfw_search_location', $location, 'rbfw_rent_item_search_type_location', 'location' );?>
                            </div>
                        </div>
                        <div class="rbfw_rent_item_search_dateButtonHolder">
                            <div class="rbfw_rent_item_search-item_date">
                                <div class="rbfw_rent_item_date_picker">
                                    <div class="rbfw_rent_item_search_date_picker_wrapper">
                                        <input type="text" name="rbfw-pickup-date" id="rbfw_rent_item_search_pickup_date" value="<?php echo esc_attr( $pickup_date )?>" placeholder="dd-mm-yyyy">
                                        <i class="fa fa-calendar" id="rbfw_rent_item_search_calendar_icon"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="rbfw_rent_item_search_button_holder">
                                <div class="rbfw_rent_item_search_button">
                                    <button type="submit" class="rbfw_rent_item_search_submit">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </section>
<?php
    $search_content = ob_get_clean();

    return $search_content;
//    ob_get_clean(); }
}

add_shortcode('rbfw_left_filter', 'rbfw_rent_left_filter' );
function rbfw_rent_left_filter( $attr = null ){

    $rbfw_categorys = get_rbfw_post_categories_from_meta();
    $rbfw_locations = get_rbfw_pickup_data_wp_query();
    $rbfw_rent_types =get_rbfw_item_type_wp_query();
    $rbfw_features_category =  get_rbfw_post_features_from_meta();

    ob_start();
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <div class="rbfw_filter_sidebar">
        <form action="#" id="rbfw_left_filter_form" type="post">
            <h4 data-placeholder=""><span class="rbfw_filter_icon mR_xs fas fa-filter"></span>Filters</h4>
            <div class="rbfw_price-range">
                <h5 class="rbfw_toggle-header">Price <span class="rbfw_toggle-icon">+</span></h5>
                <div class="rbfw_toggle-content" style="display: none">
                    <p>
                        <label for="price">Price range:</label>
                        <input type="text" id="price" readonly style="border:0; color:#f6931f; font-weight:bold;">
                    </p>
                    <div id="slider-range"></div>
                </div>
            </div>
            <div class="rbfw_filter_sidebar_locations">
                <h5 class="rbfw_toggle-header">Pickup Location<span class="rbfw_toggle-icon">+</span></h5>
                <div class="rbfw_toggle-content" style="display: none">
                    <?php foreach ( $rbfw_locations as $key => $location ) { ?>
                        <label><input type="checkbox" class="rbfw_location" value="<?php echo esc_attr( $key )?>"> <?php echo esc_attr( $location )?></label>
                    <?php } ?>
                </div>
            </div>
            <div class="rbfw_filter_sidebar_category">
                <h5 class="rbfw_toggle-header">Item Category <span class="rbfw_toggle-icon">+</span></h5>
                <div class="rbfw_toggle-content" style="display: none">
                    <?php foreach ( $rbfw_categorys as $category ) { ?>
                        <label><input type="checkbox" class="rbfw_category" value="<?php echo esc_attr( $category )?>"> <?php echo esc_attr( $category )?></label>
                    <?php } ?>
                </div>
            </div>
            <div class="rbfw_filter_sidebar_product-type">
                <h5 class="rbfw_toggle-header">Item Type <span class="rbfw_toggle-icon">+</span></h5>
                <div class="rbfw_toggle-content" style="display: none">
                    <?php foreach ( $rbfw_rent_types as $item ) { ?>
                        <label><input type="checkbox" class="rbfw_rent_type" value="<?php echo esc_attr( $item )?>"> <?php echo esc_attr( $item )?> </label>
                    <?php } ?>
                </div>
            </div>
            <div class="rbfw_rent_item_fearture_holder">
                <h5 class="rbfw_toggle-header">Item Features<span class="rbfw_toggle-icon">+</span></h5>
                <div class="rbfw_toggle-content" style="display: none">
                    <?php foreach ( $rbfw_features_category as $features ) { ?>
                        <label><input type="checkbox" class="rbfw_rent_feature" value="<?php echo esc_attr( $features['title'] )?>"> <?php echo esc_attr( $features['title'] )?> </label>
                    <?php } ?>

                </div>
<!--                <button id="rbfw_feature_loadMore">Load More</button>-->
            </div>
        </form>
        <div class="rbfw_left_filter_button">Filter</div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>

    </script>

    <?php
    $left_filter = ob_get_clean();

    return $left_filter;

}

?>