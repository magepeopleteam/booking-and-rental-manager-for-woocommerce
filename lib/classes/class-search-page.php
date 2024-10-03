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
                $all_cat_features .= '<div class="rbfw_show_all_cat_features" id="rbfw_show_all_cat_features-'.$post_id.'"> ';
                foreach ($rbfw_feature_category as $value) {
                    $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
                    $cat_title = $value['cat_title'];

                    $all_cat_features .= '<h2 class="rbfw_popup_fearure_title">'.$cat_title.'</h2>';
                    if (!empty($cat_features)) {
                        $all_cat_features .= '<ul class="rbfw_popup_fearure_lists">';
                        foreach ($cat_features as $features) {
                            $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                            $title = $features['title'];
                            $rand_number = rand();
                            if ($title) {
                                $icom = mep_esc_html($icon);
                                $all_cat_features .= "<li class='bfw_rent_list_items title  $rand_number '><span class='bfw_rent_list_items_icon'><i class='$icom'></i></span>  $title </li>";
                            }
                        }
                        $all_cat_features .= '</ul>';
                    }
                }
                $all_cat_features .= '</div>';

            }

            wp_send_json_success($all_cat_features);
        }
    }
    new Rbfw_Search_Page();
}