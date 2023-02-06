<?php
/*
* Author 	:	MagePeople Team
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( ! class_exists( 'RbfwImportDemo' ) ) {
    class RbfwImportDemo {
        public function __construct(){
            
            add_action('rbfw_after_register_activation', array($this, 'rbfw_import_demo_function'));
        }

        public function rbfw_import_demo_function(){
            // Disable a time limit
	        set_time_limit(0);

            $xml_url = RBFW_PLUGIN_URL . '/assets/sample-rent-items.xml';
	        $xml = simplexml_load_file($xml_url);
            $json_string = json_encode($xml);    
            $xml_array = json_decode($json_string, TRUE);

            $sample_rent_items = get_option('rbfw_sample_rent_items');
           
            if($sample_rent_items == 'imported'){
                return;
            }

            $xml_array = !empty($xml_array['item']) ? $xml_array['item'] : [];

	        if($xml !== FALSE && !empty($xml_array)){

                $counter = count($xml_array);

                $i = 1;

                foreach($xml_array as $item){

                    if($i <= $counter){
                        
                        $title = !empty($item['title']) ? $item['title'] : '';
                        $content = !empty($item['content']) ? $item['content'] : '';

                        $rent_args = array(
                            'post_title' 	=> $title,
                            'post_content' 	=> $content,
                            'post_status' 	=> 'publish',
                            'post_type' 	=> 'rbfw_item',
                        );

                        $rent_post_id = wp_insert_post( $rent_args );

                        $rent_post_metas = !empty($item['postmeta']) ? $item['postmeta'] : '';

                        if(!empty($rent_post_id)){

                            foreach($rent_post_metas as $value){

                                $meta_key = $value['meta_key'];

                                if(!empty($value['meta_value'])){
                                    $meta_value = maybe_unserialize($value['meta_value']);
                                }else{
                                    $meta_value = '';
                                }
                                
                                update_post_meta( $rent_post_id, $meta_key, $meta_value );
                            }

                            $wc_product_args = array(
                                'post_type' 	=> 'product',
                                'post_title' 	=> $title,
                                'post_name' => uniqid(),
                                'post_status' 	=> 'publish',
                            );
    
                            $wc_product_id = wp_insert_post( $wc_product_args );

                            if(!empty($wc_product_id)){
                                $product_type = 'yes';
                                update_post_meta($rent_post_id, 'link_wc_product', $wc_product_id);
                                update_post_meta($wc_product_id, 'link_rbfw_id', $rent_post_id);
                                update_post_meta($wc_product_id, '_price', 0.01);
                                update_post_meta($wc_product_id, '_sold_individually', 'yes');
                                update_post_meta($wc_product_id, '_virtual', $product_type);
                                $terms = array('exclude-from-catalog', 'exclude-from-search');
                                wp_set_object_terms($wc_product_id, $terms, 'product_visibility');
                                update_post_meta($rent_post_id, 'check_if_run_once', true);
                            }
                        }   
                    }
                    $i++;
                }

                update_option('rbfw_sample_rent_items', 'imported');
            }
        }

    }
    new RbfwImportDemo();
}