<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('Rbfw_Search_Page')) {
    class Rbfw_Search_Page{
        public function __construct(){
            add_action('wp_loaded', array($this,'rbfw_search_page'));
            add_shortcode('rbfw_search_old', array($this,'rbfw_search_shortcode_func'));
            add_filter('display_post_states', array($this, 'rbfw_add_post_state'), 10, 2);


            add_action('wp_ajax_rbfw_get_rent_item_category_info', array($this,'rbfw_get_rent_item_category_info'));
            add_action('wp_ajax_nopriv_rbfw_get_rent_item_category_info', array($this,'rbfw_get_rent_item_category_info'));

            //Left side filter
            add_action('wp_ajax_rbfw_get_left_side_filter_data', array($this,'rbfw_get_left_side_filter_data'));
            add_action('wp_ajax_nopriv_rbfw_get_left_side_filter_data', array($this,'rbfw_get_left_side_filter_data'));
        }

        public function rbfw_search_page(){
            
            $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');

            if($search_page_id){

                if(empty(get_post_meta( $search_page_id, 'rbfw_search_page', true))){

                    $args = array(
                        'ID'           => $search_page_id,
                        'post_content' => '[rbfw_search]'
                    );

                    wp_update_post($args);

                    update_post_meta( $search_page_id, 'rbfw_search_page', 'generated' );

                } else {

                    return; //do nothing
                }

            }else{

                $page_obj = rbfw_exist_page_by_title('Rental Search');

                if($page_obj === false){

                    $args = array(
                        'post_title'    => 'Rental Search',
                        'post_content'  => '[rbfw_search]',
                        'post_status'   => 'publish',
                        'post_type'     => 'page'
                    );
                    $post_id = wp_insert_post( $args );
    
                    if($post_id){
                        $gen_settings = !empty(get_option('rbfw_basic_gen_settings')) ? get_option('rbfw_basic_gen_settings') : [];
                        $new_gen_settings = array_merge($gen_settings, ['rbfw_search_page' => $post_id]);
                        update_option('rbfw_basic_gen_settings', $new_gen_settings);
                        update_post_meta( $post_id, 'rbfw_search_page', 'generated' );
                    }
                }
            }
        }

        function rbfw_add_post_state( $post_states, $post ) {
            $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');

            if(!empty($search_page_id)){
                if( $post->ID == $search_page_id ) {
                    $post_states[] = 'Search Page';
                }
            }

            return $post_states;
        }

        public function rbfw_search_shortcode_func(){
            ob_start();

            $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');
            $current_page_id = get_queried_object_id();

            if(!isset($search_page_id)){
                return;
            }

            if($current_page_id != $search_page_id){
                return;
            }

            if(isset($_GET['rbfw_search_submit']) && !empty($_GET['rbfw_search_location'])){

                $location = strip_tags($_GET['rbfw_search_location']);

                $atts = array(
                    'location' => $location
                );

                rbfw_rent_search_shortcode_func();

                echo rbfw_rent_list_shortcode_func($atts);

            }else{

                rbfw_rent_search_shortcode_func();
            }

            $content = ob_get_clean();
            return $content;
        }

        public function rbfw_get_rent_item_category_info(){

            $all_cat_features = '';

            if(isset($_POST['post_id'])){

                $post_id = sanitize_text_field( $_POST['post_id'] );
                $rbfw_feature_category = get_post_meta($post_id, 'rbfw_feature_category', true) ? maybe_unserialize(get_post_meta($post_id,
                    'rbfw_feature_category', true)) : [];
                $all_cat_features = '';
                $all_cat_features .= '<ul class="rbfw_show_all_cat_features" id="rbfw_show_all_cat_features-'.$post_id.'"> ';
                foreach ($rbfw_feature_category as $value) {
                    $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
                    $cat_title = $value['cat_title'];

                    $all_cat_features .= '<p class="rbfw_popup_fearure_title_text">'.$cat_title.'</p>';
                    if (!empty($cat_features)) {
                        foreach ($cat_features as $features) {
                            $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                            $title = $features['title'];
                            $rand_number = rand();
                            if ($title) {
                                $icom = mep_esc_html($icon);
                                $all_cat_features .= "<li class='bfw_rent_list_items title  $rand_number '><span class='bfw_rent_list_items_icon'><i class='$icom'></i></span>  $title </li>";
                            }
                        }
                    }

                }
                $all_cat_features .= '</ul>';

            }

            wp_send_json_success($all_cat_features);
        }

        public function rbfw_get_left_side_filter_data(){
            $response = '';
            if( isset( $_POST['filter_date'] ) ){
                $filter_date = $_POST['filter_date'];

//                error_log( print_r( [ '$filter_date' => $filter_date ], true ) );
                $features_to_search = isset( $filter_date['feature'] ) ? $filter_date['feature'] : [];
                $feature_meta_queries = '';
                if( is_array( $features_to_search ) && count( $features_to_search ) > 0 ){
                    $feature_meta_queries = array('relation' => 'OR'); // Relation set to 'OR' so it matches any of the feature titles
                    foreach ($features_to_search as $feature) {
                        $feature_meta_queries[] = array(
                            'key'     => 'rbfw_feature_category',
                            'value'   => sanitize_text_field( $feature ),
                            'compare' => 'LIKE', // Use LIKE because the value is part of a serialized array
                        );
                    }
                }

                $rent_types = isset( $filter_date['type'] ) ? $filter_date['type'] : [];
                $rent_type = '';
                if( is_array( $rent_types ) && count( $rent_types ) > 0 ){
                    $rent_type = array('relation' => 'OR');
                    foreach ($rent_types as $type) {
                        $rent_type[] = !empty($type) ? array(
                            'key' => 'rbfw_item_type',
                            'value' => sanitize_text_field( $type ),
                            'compare' => '==',
                        ) : '';
                    }
                }

//                error_log( print_r( [ '$rent_type' => $rent_type ], true ) );

                $rent_locations = isset( $filter_date['location'] ) ? $filter_date['location'] : [];
                $location_query = '';
                if( is_array( $rent_locations ) && count( $rent_locations ) > 0 ) {
                    $location_query = array('relation' => 'OR');
                    foreach ( $rent_locations as $location ) {
                        $location_query = !empty($location) ? array(
                            'key' => 'rbfw_pickup_data',
                            'value' => sanitize_text_field( $location ),
                            'compare' => 'LIKE'
                        ) : '';
                    }
                }

                $rent_categories = isset( $filter_date['category'] ) ? $filter_date['category'] : [];

                if( is_array( $rent_locations ) && count( $rent_locations ) > 0 ) {
                    $category_query = array('relation' => 'OR');
                    foreach ($rent_categories as $category_name) {
                        $category_query['meta_query'][] = array(
                            'key' => 'rbfw_categories',
                            'value' => $category_name,
                            'compare' => 'LIKE'
                        );
                    }
                }else{
                    $category_query = '';
                }



                $args = array(
                    'post_type'  => 'any', // Change 'any' to your specific post type if needed
                    'meta_query' => array(
                        'relation' => 'OR',
                        $feature_meta_queries,
                        $rent_type,
                        $location_query,
                        $category_query,
                    ),
//                    'meta_query' => $rent_type,
                    'posts_per_page' => -1,
                );
                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        error_log( print_r( [ 'the_title' => get_the_title() ], true ) );
                    }
                    wp_reset_postdata();
                }

            }

            wp_send_json_success( $response );
        }

    }
    new Rbfw_Search_Page();
}