<?php


/**
 * @author Shahadat <raselsha@gmail.com>
 * @version 1.3.4
 */

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
            $columns['rbfw_item_type']= esc_html__('Rent Type','booking-and-rental-manager-for-woocommerce');
            $columns['rbfw_categories']      =  esc_html__('Categories','booking-and-rental-manager-for-woocommerce');
            $columns['author']      =  esc_html__('Author','booking-and-rental-manager-for-woocommerce');
            $columns['date']        = esc_html__('Date','booking-and-rental-manager-for-woocommerce');
            return $columns;
        }

        public function rbfw_cpt_custom_column($columns,$post_id){
            switch($columns){
                case 'rbfw_item_type':
                    $rbfw_item_type = esc_html__(get_post_meta($post_id,'rbfw_item_type',true));
                    $item_type = [
						'bike_car_sd' => 'Bike/Car for single day',
						'bike_car_md' => 'Bike/Car for multiple day',
						'resort' => 'Resort',
						'equipment' => 'Equipment',
						'dress' => 'Dress',
						'appointment' => 'Appointment',
						'others' => 'Others',
					];
                    foreach($item_type as $kay => $value):
                        echo __(($kay==$rbfw_item_type)?$value:'');
                    endforeach;
                break;
                case 'rbfw_categories':
                    $cats = get_post_meta($post_id,'rbfw_categories',true);
                    if ( ! empty($cats) ) {
                        foreach ($cats as $key => $cat) {
                            echo __("<a href='edit.php?post_type=rbfw_item&rbfw_categories=".$cat."'>".$cat."</a>");
                            if ($key !== count($cats) - 1) {
                                echo __(', ');
                            }
                        }
                    }
                break;
            }
        }

        public function rbfw_cpt_sortable_columns($columns){
            $columns['rbfw_item_type']='rbfw_item_type';
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
                        'name'                  => __($cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'singular_name'         => __($cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'menu_name'             => __($cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'name_admin_bar'        => __($cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'archives'              => __($cpt_label . ' List', 'booking-and-rental-manager-for-woocommerce'),
                        'attributes'            => __($cpt_label . ' List', 'booking-and-rental-manager-for-woocommerce'),
                        'parent_item_colon'     => __($cpt_label . ' Item:', 'booking-and-rental-manager-for-woocommerce'),
                        'all_items'             => __('All Items', 'booking-and-rental-manager-for-woocommerce'),
                        'add_new_item'          => __('Add New Item', 'booking-and-rental-manager-for-woocommerce'),
                        'add_new'               => __('Add New Item', 'booking-and-rental-manager-for-woocommerce'),
                        'new_item'              => __('New Item' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'edit_item'             => __('Edit ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'update_item'           => __('Update ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'view_item'             => __('View ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'view_items'            => __('View ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'search_items'          => __('Search ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'not_found'             => __($cpt_label . ' Not found', 'booking-and-rental-manager-for-woocommerce'),
                        'not_found_in_trash'    => __($cpt_label . ' Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                        'featured_image'        => __($cpt_label . ' Featured Image', 'booking-and-rental-manager-for-woocommerce'),
                        'set_featured_image'    => __('Set ' . $cpt_label . ' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'remove_featured_image' => __('Remove ' . $cpt_label . ' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'use_featured_image'    => __('Use as ' . $cpt_label . ' featured image', 'booking-and-rental-manager-for-woocommerce'),
                        'insert_into_item'      => __('Insert into ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'uploaded_to_this_item' => __('Uploaded to this ' . $cpt_label, 'booking-and-rental-manager-for-woocommerce'),
                        'items_list'            => __($cpt_label . ' list', 'booking-and-rental-manager-for-woocommerce'),
                        'items_list_navigation' => __($cpt_label . ' list navigation', 'booking-and-rental-manager-for-woocommerce'),
                        'filter_items_list'     => __('Filter ' . $cpt_label . ' list', 'booking-and-rental-manager-for-woocommerce'),
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
                    'name'                  => __('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'singular_name'         => __('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'menu_name'             => __('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'name_admin_bar'        => __('Order', 'booking-and-rental-manager-for-woocommerce'),
                    'archives'              => __('Order List', 'booking-and-rental-manager-for-woocommerce'),
                    'attributes'            => __('Order List', 'booking-and-rental-manager-for-woocommerce'),
                    'parent_item_colon'     => __('Order Item:', 'booking-and-rental-manager-for-woocommerce'),
                    'all_items'             => __('All Order', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new_item'          => __('Add New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new'               => __('Add New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'new_item'              => __('New Order', 'booking-and-rental-manager-for-woocommerce'),
                    'edit_item'             => __('Edit Order', 'booking-and-rental-manager-for-woocommerce'),
                    'update_item'           => __('Update Order', 'booking-and-rental-manager-for-woocommerce'),
                    'view_item'             => __('View Order', 'booking-and-rental-manager-for-woocommerce'),
                    'view_items'            => __('View Order', 'booking-and-rental-manager-for-woocommerce'),
                    'search_items'          => __('Search Order', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found'             => __('Order Not found', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found_in_trash'    => __('Order Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                    'featured_image'        => __('Order Feature Image', 'booking-and-rental-manager-for-woocommerce'),
                    'set_featured_image'    => __('Set Order featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'remove_featured_image' => __('Remove Order eatured image', 'booking-and-rental-manager-for-woocommerce'),
                    'use_featured_image'    => __('Use as Order featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'insert_into_item'      => __('Insert into Order', 'booking-and-rental-manager-for-woocommerce'),
                    'uploaded_to_this_item' => __('Uploaded to this Order', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list'            => __('Order list', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list_navigation' => __('Order list navigation', 'booking-and-rental-manager-for-woocommerce'),
                    'filter_items_list'     => __('Filter Order list', 'booking-and-rental-manager-for-woocommerce'),
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
                    'name'                  => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'singular_name'         => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'menu_name'             => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'name_admin_bar'        => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'archives'              => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'attributes'            => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'parent_item_colon'     => __('Order Meta Item:', 'booking-and-rental-manager-for-woocommerce'),
                    'all_items'             => __('All Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new_item'          => __('Add New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'add_new'               => __('Add New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'new_item'              => __('New Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'edit_item'             => __('Edit Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'update_item'           => __('Update Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'view_item'             => __('View Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'view_items'            => __('View Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'search_items'          => __('Search Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found'             => __('Order Meta Not found', 'booking-and-rental-manager-for-woocommerce'),
                    'not_found_in_trash'    => __('Order Meta Not found in Trash', 'booking-and-rental-manager-for-woocommerce'),
                    'featured_image'        => __('Order Meta Featured Image', 'booking-and-rental-manager-for-woocommerce'),
                    'set_featured_image'    => __('Set Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'remove_featured_image' => __('Remove Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'use_featured_image'    => __('Use as Order Meta featured image', 'booking-and-rental-manager-for-woocommerce'),
                    'insert_into_item'      => __('Insert into Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'uploaded_to_this_item' => __('Uploaded to this Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list'            => __('Order Meta', 'booking-and-rental-manager-for-woocommerce'),
                    'items_list_navigation' => __('Order Meta navigation', 'booking-and-rental-manager-for-woocommerce'),
                    'filter_items_list'     => __('Filter Order Meta', 'booking-and-rental-manager-for-woocommerce'),
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