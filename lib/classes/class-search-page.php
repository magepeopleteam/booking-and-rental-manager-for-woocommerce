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
            add_shortcode('rbfw_search', array($this,'rbfw_search_shortcode_func'));
            add_filter('display_post_states', array($this, 'rbfw_add_post_state'), 10, 2);
        }

        public function rbfw_search_page(){
            
            $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');

            if($search_page_id){
                $args = array(
                    'ID'           => $search_page_id,
                    'post_content' => '[rbfw_search]',
                    'post_status'   => 'publish'
                );
                wp_update_post($args);

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
    }
    new Rbfw_Search_Page();
}