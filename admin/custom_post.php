<?php

if (!defined('ABSPATH')) { die; } // Cannot access pages directly.

if( ! class_exists('RBFW_Custom_Post')){
    class RBFW_Custom_Post{
        public function __construct()
        {
            add_action('init',[$this,'rbfw_cpt']); 
            add_filter( 'manage_rbfw_item_posts_columns', array($this,'rbfw_cpt_columns') ) ;
            add_action( 'manage_rbfw_item_posts_custom_column', array($this,'rbfw_cpt_custom_column'),10,2 ) ;
            add_filter( 'manage_edit-rbfw_item_sortable_columns', array($this,'rbfw_cpt_sortable_columns') ) ;
        }

        public function rbfw_cpt_columns($columns){
            unset($columns['date']);
            $columns['rbfw_item_type']= ($rbfw->get_option_trans('rbfw_text_price_type', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_price_type', 'rbfw_basic_translation_settings')) : esc_html__('Price Type','booking-and-rental-manager-for-woocommerce');
            $columns['rbfw_categories']      =  ($rbfw->get_option_trans('rbfw_text_rent_type', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_rent_type', 'rbfw_basic_translation_settings')) : esc_html__('Rent Type','booking-and-rental-manager-for-woocommerce');
            $columns['author']      =  ($rbfw->get_option_trans('rbfw_text_author', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_author', 'rbfw_basic_translation_settings')) : esc_html__('Author','booking-and-rental-manager-for-woocommerce');
            $columns['date']        = ($rbfw->get_option_trans('rbfw_text_date', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_date', 'rbfw_basic_translation_settings')) : esc_html__('Date','booking-and-rental-manager-for-woocommerce');
            return $columns;
        }

        public function rbfw_cpt_custom_column($columns,$post_id){
            switch($columns){
                case 'rbfw_item_type':
                    $rbfw_item_type = get_post_meta($post_id,'rbfw_item_type',true);
                    $item_type = RBFW_Function::rbfw_rent_types();
                    foreach($item_type as $kay => $value):
                        echo $kay==$rbfw_item_type ? esc_html($value) : '';
                    endforeach;
                break;
                case 'rbfw_categories':
                    $cats = get_post_meta($post_id,'rbfw_categories',true);
                    if ( ! empty($cats) ) {
                        foreach ($cats as $key => $cat) {
                            echo "<a href='edit.php?post_type=rbfw_item&rbfw_categories=".esc_attr($cat)."'>".esc_html($cat)."</a>";
                            if ($key !== count($cats) - 1) {
                                echo ', ';
                            }
                        }
                    }
                break;
            }
        }

        public function rbfw_cpt_sortable_columns($columns){
            $columns['rbfw_item_type']='rbfw_item_type';
            $columns['rbfw_categories']='rbfw_categories';
            $columns['author']='author';
            return $columns;
        }

        public function rbfw_cpt(){
            global $rbfw;
            $cpt_label        = $rbfw->get_name();
            $cpt_slug         = $rbfw->get_slug();    
            $cpt_icon         = $rbfw->get_icon();
            $gutenburg_switch  = $rbfw->get_option_trans('rbfw_gutenburg_switch', 'rbfw_basic_gen_settings', 'on');
            if(isset($gutenburg_switch) && $gutenburg_switch == 'on'){
                $editor = true;
            } else {
                $editor = false;
            }
            $labels = array(
                        'name'                  => $cpt_label,
                        'singular_name'         => $cpt_label,
                        'menu_name'             => $cpt_label,
                        'name_admin_bar'        => $cpt_label,
                        'archives'              => $cpt_label . esc_html__(' List', 'booking-and-rental-manager-for-woocommerce'),
                        'attributes'            => $cpt_label . esc_html__(' List', 'booking-and-rental-manager-for-woocommerce'),
                        'parent_item_colon'     => $cpt_label . esc_html__(' Item:', 'booking-and-rental-manager-for-woocommerce'),
                        'all_items'             => ($rbfw->get_option_trans('rbfw_text_all_items', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_all_items', 'rbfw_basic_translation_settings')) : esc_html__('All Items', 'booking-and-rental-manager-for-woocommerce'),
                        'add_new_item'          => ($rbfw->get_option_trans('rbfw_text_add_new_item', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_add_new_item', 'rbfw_basic_translation_settings')) : esc_html__('Add New Item', 'booking-and-rental-manager-for-woocommerce'),
                        'add_new'               => ($rbfw->get_option_trans('rbfw_text_add_new_item', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_add_new_item', 'rbfw_basic_translation_settings')) : esc_html__('Add New Item', 'booking-and-rental-manager-for-woocommerce'),
                        'new_item'              => esc_html__('New Item ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'edit_item'             => esc_html__('Edit ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'update_item'           => esc_html__('Update ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'view_item'             => esc_html__('View ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'view_items'            => esc_html__('View ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'search_items'          => esc_html__('Search ', 'booking-and-rental-manager-for-woocommerce').$cpt_label,
                        'not_found'             => $cpt_label . esc_html__(' Not found', 'booking-and-rental-manager-for-woocommerce'),
                        'not_found_in_trash'    => $cpt_label . esc_html__(' Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                        'featured_image'        => $cpt_label . esc_html__(' Featured Image', 'booking-and-rental-manager-for-woocommerce'),
                        'set_featured_image'    => esc_html__('Set ','booking-and-rental-manager-for-woocommerce'). $cpt_label . esc_html__(' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'remove_featured_image' => esc_html__('Remove ','booking-and-rental-manager-for-woocommerce') . $cpt_label . esc_html__(' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'use_featured_image'    => esc_html__('Use as ','booking-and-rental-manager-for-woocommerce'). $cpt_label . esc_html__(' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'insert_into_item'      => esc_html__('Insert into ','booking-and-rental-manager-for-woocommerce') . $cpt_label,
                        'uploaded_to_this_item' => esc_html__('Uploaded to this ','booking-and-rental-manager-for-woocommerce') . $cpt_label,
                        'items_list'            => $cpt_label . esc_html__(' list', 'booking-and-rental-manager-for-woocommerce'),
                        'items_list_navigation' => $cpt_label . esc_html__(' list navigation', 'booking-and-rental-manager-for-woocommerce'),
                        'filter_items_list'     => esc_html__('Filter ','booking-and-rental-manager-for-woocommerce') . $cpt_label . esc_html__(' list', 'booking-and-rental-manager-for-woocommerce'),
                    );

                $args = array(
                    'public'                => true,
                    'show_in_nav_menus'     => false,
                    'labels'                => $labels,
                    'menu_icon'             => $cpt_icon,
                    'show_in_rest'          => $editor,
                    'supports'              => array('title', 'thumbnail', 'editor', 'excerpt', 'comments'),
                    'rewrite'               => array('slug' => $cpt_slug)
                );

                register_post_type('rbfw_item', $args);


                $labels = array(
                    'name'                  => ($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings')) : esc_html__('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'singular_name'         => ($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings')) : esc_html__('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'menu_name'             => ($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings')) : esc_html__('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'name_admin_bar'        => ($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_order', 'rbfw_basic_translation_settings')) : esc_html__('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'archives'              => esc_html__('Order List', 'booking-and-rental-manager-for-woocommerce'),
                    'attributes'            => esc_html__('Order List', 'booking-and-rental-manager-for-woocommerce'),
                    'parent_item_colon'     => esc_html__('Order Item:', 'booking-and-rental-manager-for-woocommerce'),
                    'all_items'             => ($rbfw->get_option_trans('rbfw_text_all_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_all_order', 'rbfw_basic_translation_settings')) : esc_html__('All Order', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new_item'          => ($rbfw->get_option_trans('rbfw_text_add_new_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_add_new_order', 'rbfw_basic_translation_settings')) : esc_html__('Add New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new'               => ($rbfw->get_option_trans('rbfw_text_add_new_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_add_new_order', 'rbfw_basic_translation_settings')) : esc_html__('Add New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'new_item'              => ($rbfw->get_option_trans('rbfw_text_new_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_new_order', 'rbfw_basic_translation_settings')) : esc_html__('New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'edit_item'             => ($rbfw->get_option_trans('rbfw_text_edit_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_edit_order', 'rbfw_basic_translation_settings')) : esc_html__('Edit Order', 'booking-and-rental-manager-for-woocommerce'),
                    'update_item'           => ($rbfw->get_option_trans('rbfw_text_update_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_update_order', 'rbfw_basic_translation_settings')) : esc_html__('Update Order', 'booking-and-rental-manager-for-woocommerce'),
                    'view_item'             => ($rbfw->get_option_trans('rbfw_text_view_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_view_order', 'rbfw_basic_translation_settings')) : esc_html__('View Order', 'booking-and-rental-manager-for-woocommerce'),
                    'view_items'            => ($rbfw->get_option_trans('rbfw_text_view_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_view_order', 'rbfw_basic_translation_settings')) : esc_html__('View Order', 'booking-and-rental-manager-for-woocommerce'),
                    'search_items'          => ($rbfw->get_option_trans('rbfw_text_search_order', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_search_order', 'rbfw_basic_translation_settings')) : esc_html__('Search Order', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found'             => ($rbfw->get_option_trans('rbfw_text_order_not_found', 'rbfw_basic_translation_settings') && want_loco_translate()=='no') ? esc_html($rbfw->get_option_trans('rbfw_text_order_not_found', 'rbfw_basic_translation_settings')) : esc_html__('Order Not found', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found_in_trash'    => esc_html__('Order Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                    'featured_image'        => esc_html__('Order Feature Image', 'booking-and-rental-manager-for-woocommerce'),
                    'set_featured_image'    => esc_html__('Set Order featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'remove_featured_image' => esc_html__('Remove Order eatured image', 'booking-and-rental-manager-for-woocommerce'),
                    'use_featured_image'    => esc_html__('Use as Order featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'insert_into_item'      => esc_html__('Insert into Order', 'booking-and-rental-manager-for-woocommerce'),
                    'uploaded_to_this_item' => esc_html__('Uploaded to this Order', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list'            => esc_html__('Order list', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list_navigation' => esc_html__('Order list navigation', 'booking-and-rental-manager-for-woocommerce'),
                    'filter_items_list'     => esc_html__('Filter Order list', 'booking-and-rental-manager-for-woocommerce'),
                );
                
                $args = array(
                'public'                => true,
                'show_in_nav_menus'     => false,
                'show_in_menu'          => false,
                'publicly_queryable'    => false,
                'labels'                => $labels,
                'show_in_rest'          => false,
                'supports'              => array('', '', '', '', ''),
                'rewrite'               => array('slug' => 'rbfw_order'),
                'capabilities' => array(
                    'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
                ),
                'map_meta_cap' => true,
                );

                register_post_type('rbfw_order', $args);    

                $labels = array(
                    'name'                  => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'singular_name'         => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'menu_name'             => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'name_admin_bar'        => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'archives'              => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'attributes'            => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'parent_item_colon'     => esc_html__('Order Meta Item:', 'booking-and-rental-manager-for-woocommerce'),
                    'all_items'             => esc_html__('All Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new_item'          => esc_html__('Add New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new'               => esc_html__('Add New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'new_item'              => esc_html__('New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'edit_item'             => esc_html__('Edit Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'update_item'           => esc_html__('Update Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'view_item'             => esc_html__('View Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'view_items'            => esc_html__('View Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'search_items'          => esc_html__('Search Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found'             => esc_html__('Order Meta Not found', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found_in_trash'    => esc_html__('Order Meta Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                    'featured_image'        => esc_html__('Order Meta Featured Image', 'booking-and-rental-manager-for-woocommerce'),
                    'set_featured_image'    => esc_html__('Set Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'remove_featured_image' => esc_html__('Remove Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'use_featured_image'    => esc_html__('Use as Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'insert_into_item'      => esc_html__('Insert into Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'uploaded_to_this_item' => esc_html__('Uploaded to this Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list'            => esc_html__('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list_navigation' => esc_html__('Order Meta navigation', 'booking-and-rental-manager-for-woocommerce'),
                    'filter_items_list'     => esc_html__('Filter Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                );
                
                $args = array(
                'public'                => true,
                'show_in_nav_menus'     => false,
                'show_in_menu'          => false,
                'publicly_queryable'    => false,
                'labels'                => $labels,
                'show_in_rest'          => false,
                'supports'              => array('', '', '', '', ''),
                'rewrite'               => array('slug' => 'rbfw_order_meta'),
                );

                register_post_type('rbfw_order_meta', $args);  
        }

    }
    $RBFW_Custom_Post = new RBFW_Custom_Post();
}