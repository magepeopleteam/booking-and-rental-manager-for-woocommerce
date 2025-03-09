<?php
/*
* Author     : MagePeople Team
* Copyright  : mage-people.com
* Description: Customer Profile Shortcode for Booking and Rental Manager
*/
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customer profile shortcode
 */
function rbfw_customer_profile_shortcode($atts) {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return '<p>' . esc_html__('Please log in to view your profile.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
    }
    
    ob_start();
    
    // Display user info
    $user = get_userdata($user_id);
    echo '<div class="rbfw-customer-profile">';
    echo '<h2>' . esc_html__('Your Profile', 'booking-and-rental-manager-for-woocommerce') . '</h2>';
    
    echo '<div class="rbfw-customer-info">';
    echo '<p><strong>' . esc_html__('Name:', 'booking-and-rental-manager-for-woocommerce') . '</strong> ' . esc_html($user->display_name) . '</p>';
    echo '<p><strong>' . esc_html__('Email:', 'booking-and-rental-manager-for-woocommerce') . '</strong> ' . esc_html($user->user_email) . '</p>';
    echo '</div>';
    
    // Display loyalty points
    $loyalty_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
    $loyalty_points = !empty($loyalty_points) ? $loyalty_points : 0;
    
    echo '<div class="rbfw-loyalty-points-container">';
    echo '<h3>' . esc_html__('Your Loyalty Points', 'booking-and-rental-manager-for-woocommerce') . '</h3>';
    echo '<p>' . sprintf(esc_html__('You have %s loyalty points.', 'booking-and-rental-manager-for-woocommerce'), '<strong>' . $loyalty_points . '</strong>') . '</p>';
    
    if ($loyalty_points > 0) {
        echo '<p>' . esc_html__('You can redeem your points during checkout for discounts on future bookings.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
        echo '<p>' . sprintf(esc_html__('Each point is worth %s.', 'booking-and-rental-manager-for-woocommerce'), rbfw_mps_price(1)) . '</p>';
    }
    
    echo '</div>';
    
    // Display booking history
    $args = array(
        'post_type'      => 'rbfw_order',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'rbfw_user_id',
                'value'   => $user_id,
                'compare' => '='
            )
        )
    );
    
    $orders = new WP_Query($args);
    
    if ($orders->have_posts()) {
        echo '<h3>' . esc_html__('Your Booking History', 'booking-and-rental-manager-for-woocommerce') . '</h3>';
        echo '<div class="rbfw-booking-history">';
        echo '<table class="rbfw-booking-history-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__('Booking ID', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '<th>' . esc_html__('Item', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '<th>' . esc_html__('Date', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '<th>' . esc_html__('Status', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '<th>' . esc_html__('Total', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '<th>' . esc_html__('Actions', 'booking-and-rental-manager-for-woocommerce') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while ($orders->have_posts()) {
            $orders->the_post();
            $order_id = get_the_ID();
            $wc_order_id = get_post_meta($order_id, 'rbfw_order_id', true);
            $rbfw_id = get_post_meta($order_id, 'rbfw_id', true);
            $status = get_post_meta($order_id, 'rbfw_order_status', true);
            $total = get_post_meta($order_id, 'rbfw_ticket_total_price', true);
            $ticket_info = get_post_meta($order_id, 'rbfw_ticket_info', true);
            
            $item_name = '';
            if (!empty($ticket_info) && is_array($ticket_info)) {
                foreach ($ticket_info as $ticket) {
                    if (isset($ticket['ticket_name'])) {
                        $item_name = $ticket['ticket_name'];
                        break;
                    }
                }
            }
            
            $start_date = get_post_meta($order_id, 'start_date', true);
            $end_date = get_post_meta($order_id, 'end_date', true);
            $date_info = '';
            
            if ($start_date && $end_date) {
                $date_info = rbfw_get_datetime($start_date, 'date-text') . ' - ' . rbfw_get_datetime($end_date, 'date-text');
            } elseif ($start_date) {
                $date_info = rbfw_get_datetime($start_date, 'date-text');
            }
            
            echo '<tr>';
            echo '<td>#' . esc_html($wc_order_id) . '</td>';
            echo '<td>' . esc_html($item_name) . '</td>';
            echo '<td>' . esc_html($date_info) . '</td>';
            echo '<td>' . esc_html(ucfirst($status)) . '</td>';
            echo '<td>' . esc_html(rbfw_mps_price($total)) . '</td>';
            echo '<td><a href="' . esc_url(get_permalink($rbfw_id)) . '" class="button">' . esc_html__('View Item', 'booking-and-rental-manager-for-woocommerce') . '</a></td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p>' . esc_html__('You have not made any bookings yet.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
    }
    
    wp_reset_postdata();
    
    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('rbfw-customer-profile', 'rbfw_customer_profile_shortcode');