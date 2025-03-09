<?php
/*
* Author     : MagePeople Team
* Copyright  : mage-people.com
* Description: Admin functions for Customer Loyalty System
*/
if (!defined('ABSPATH')) {
    exit;
}

// Add AJAX handler for updating loyalty points from admin
add_action('wp_ajax_rbfw_update_loyalty_points', 'rbfw_update_loyalty_points_callback');

function rbfw_update_loyalty_points_callback() {
    // Check nonce
    check_admin_referer('rbfw_update_loyalty_points', 'nonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'booking-and-rental-manager-for-woocommerce')));
        return;
    }
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $points_adjustment = isset($_POST['points']) ? intval($_POST['points']) : 0;
    
    if (!$user_id) {
        wp_send_json_error(array('message' => __('Invalid user ID.', 'booking-and-rental-manager-for-woocommerce')));
        return;
    }
    
    // Get current points
    $current_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
    $current_points = !empty($current_points) ? intval($current_points) : 0;
    
    // Calculate new points
    $new_points = $current_points + $points_adjustment;
    
    // Ensure points don't go negative
    if ($new_points < 0) {
        $new_points = 0;
    }
    
    // Update user meta
    update_user_meta($user_id, 'rbfw_loyalty_points', $new_points);
    
    // Get user info for the response
    $user = get_userdata($user_id);
    
    wp_send_json_success(array(
        'message' => sprintf(__('Loyalty points for %s updated from %d to %d.', 'booking-and-rental-manager-for-woocommerce'), 
            $user->display_name, 
            $current_points, 
            $new_points
        ),
        'new_points' => $new_points
    ));
}

// Add customer loyalty information to user profile
add_action('show_user_profile', 'rbfw_show_loyalty_points_in_profile');
add_action('edit_user_profile', 'rbfw_show_loyalty_points_in_profile');

function rbfw_show_loyalty_points_in_profile($user) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $loyalty_points = get_user_meta($user->ID, 'rbfw_loyalty_points', true);
    $loyalty_points = !empty($loyalty_points) ? intval($loyalty_points) : 0;
    
    ?>
    <h3><?php _e('Booking and Rental Manager Loyalty Points', 'booking-and-rental-manager-for-woocommerce'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="rbfw_loyalty_points"><?php _e('Loyalty Points', 'booking-and-rental-manager-for-woocommerce'); ?></label></th>
            <td>
                <input type="number" name="rbfw_loyalty_points" id="rbfw_loyalty_points" value="<?php echo esc_attr($loyalty_points); ?>" class="regular-text" />
                <p class="description"><?php _e('Current loyalty points for this customer.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

// Save customer loyalty points when profile is updated
add_action('personal_options_update', 'rbfw_save_loyalty_points_in_profile');
add_action('edit_user_profile_update', 'rbfw_save_loyalty_points_in_profile');

function rbfw_save_loyalty_points_in_profile($user_id) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_POST['rbfw_loyalty_points'])) {
        $loyalty_points = intval($_POST['rbfw_loyalty_points']);
        if ($loyalty_points < 0) {
            $loyalty_points = 0;
        }
        update_user_meta($user_id, 'rbfw_loyalty_points', $loyalty_points);
    }
}

// Add loyalty points column to users list
add_filter('manage_users_columns', 'rbfw_add_loyalty_points_column');
function rbfw_add_loyalty_points_column($columns) {
    $columns['rbfw_loyalty_points'] = __('Loyalty Points', 'booking-and-rental-manager-for-woocommerce');
    return $columns;
}

// Display loyalty points in the users list
add_filter('manage_users_custom_column', 'rbfw_show_loyalty_points_column_content', 10, 3);
function rbfw_show_loyalty_points_column_content($value, $column_name, $user_id) {
    if ('rbfw_loyalty_points' === $column_name) {
        $loyalty_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
        $loyalty_points = !empty($loyalty_points) ? intval($loyalty_points) : 0;
        return $loyalty_points;
    }
    return $value;
}

// Make the loyalty points column sortable
add_filter('manage_users_sortable_columns', 'rbfw_make_loyalty_points_column_sortable');
function rbfw_make_loyalty_points_column_sortable($columns) {
    $columns['rbfw_loyalty_points'] = 'rbfw_loyalty_points';
    return $columns;
}

// Add sorting functionality to the loyalty points column
add_action('pre_get_users', 'rbfw_sort_users_by_loyalty_points');
function rbfw_sort_users_by_loyalty_points($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('rbfw_loyalty_points' === $orderby) {
        $query->set('meta_key', 'rbfw_loyalty_points');
        $query->set('orderby', 'meta_value_num');
    }
}