<?php
/*
* @Author 		rubelcuet10@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('RBFW_Rental_List')) {
    class RBFW_Rental_List
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'rental_list_menu'));

            add_action('admin_action_rbfw_duplicate_post', [$this, 'rbfw_duplicate_post_function']);
        }

        function rbfw_duplicate_post_function()
        {
            if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce($_GET['_wpnonce'], 'rbfw_duplicate_post_' . sanitize_text_field($_GET['post_id']))
            ) {
                wp_die('Invalid request (missing or invalid nonce).');
            }

            $post_id = (int)sanitize_text_field(wp_unslash($_GET['post_id']));
            $post = get_post($post_id);

            $new_post = array(
                'post_title' => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status' => 'draft',
                'post_type' => $post->post_type,
                'post_author' => get_current_user_id(),
            );

            $new_post_id = wp_insert_post($new_post);

            if (is_wp_error($new_post_id) || !$new_post_id) {
                wp_die('Failed to duplicate post.');
            }
            $meta = get_post_meta($post_id);
            foreach ($meta as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        }

        public function rental_list_menu()
        {
            add_submenu_page('edit.php?post_type=rbfw_item', __('Rental Lists', 'booking-and-rental-manager-for-woocommerce'), __('Rental Lists', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_rental_lists', array($this, 'display_rental_lists'));
        }

        public function display_rental_lists()
        {
            include( RBFW_Function::get_template_path( 'rental_lists.php' ) );
        }

    }

    new RBFW_Rental_List();

}
