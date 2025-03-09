<?php
/*
* Author     : MagePeople Team
* Copyright  : mage-people.com
* Description: Customer Management System for Booking and Rental Manager
*/
if (!defined('ABSPATH')) {
    exit;
}

class RBFW_Customer_Management {
    
    public function __construct() {
        // Add customer profile tab to My Account page
        add_filter('woocommerce_account_menu_items', array($this, 'add_booking_history_tab'), 10, 1);
        add_action('woocommerce_account_booking-history_endpoint', array($this, 'booking_history_content'));
        
        // Register endpoint for booking history
        add_action('init', array($this, 'add_booking_history_endpoint'));
        
        // Add loyalty points system
        add_action('woocommerce_order_status_completed', array($this, 'add_loyalty_points_on_order_complete'), 10, 1);
        
        // Add shortcode for customer profile
        add_shortcode('rbfw_customer_profile', array($this, 'customer_profile_shortcode'));
        
        // Add meta box to display customer loyalty points in admin
        add_action('add_meta_boxes', array($this, 'add_customer_loyalty_meta_box'));
        
        // Add AJAX handler for redeeming points
        add_action('wp_ajax_rbfw_redeem_loyalty_points', array($this, 'redeem_loyalty_points'));
        add_action('wp_ajax_nopriv_rbfw_redeem_loyalty_points', array($this, 'redeem_loyalty_points'));
        
        // Add scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add filter for cart item price
        add_filter('woocommerce_cart_item_price', array($this, 'apply_loyalty_discount'), 10, 3);
    }
    
    /**
     * Add booking history endpoint
     */
    public function add_booking_history_endpoint() {
        add_rewrite_endpoint('booking-history', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
    
    /**
     * Add booking history tab to My Account menu
     */
    public function add_booking_history_tab($items) {
        $items['booking-history'] = __('Booking History', 'booking-and-rental-manager-for-woocommerce');
        return $items;
    }
    
    /**
     * Display booking history content
     */
    public function booking_history_content() {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            echo '<p>' . esc_html__('Please log in to view your booking history.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
            return;
        }
        
        // Get user's orders
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
            echo '<h2>' . esc_html__('Your Booking History', 'booking-and-rental-manager-for-woocommerce') . '</h2>';
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
            
            // Display loyalty points
            $this->display_loyalty_points($user_id);
            
        } else {
            echo '<p>' . esc_html__('You have not made any bookings yet.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
        }
        
        wp_reset_postdata();
    }
    
    /**
     * Display loyalty points
     */
    public function display_loyalty_points($user_id) {
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
    }
    
    /**
     * Add loyalty points when order is completed
     */
    public function add_loyalty_points_on_order_complete($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) {
            return; // Guest checkout, no points
        }
        
        $order_total = $order->get_total();
        $points_earned = floor($order_total); // 1 point per currency unit
        
        // Check if this is a rental/booking order
        $has_rbfw_item = false;
        foreach ($order->get_items() as $item) {
            $rbfw_id = $item->get_meta('_rbfw_id');
            if ($rbfw_id) {
                $has_rbfw_item = true;
                break;
            }
        }
        
        if (!$has_rbfw_item) {
            return; // Not a booking order
        }
        
        // Add points to user account
        $current_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
        $current_points = !empty($current_points) ? $current_points : 0;
        $new_points = $current_points + $points_earned;
        
        update_user_meta($user_id, 'rbfw_loyalty_points', $new_points);
        
        // Add note to order
        $order->add_order_note(
            sprintf(__('Customer earned %d loyalty points from this booking.', 'booking-and-rental-manager-for-woocommerce'), $points_earned)
        );
    }
    
    /**
     * Customer profile shortcode
     */
    public function customer_profile_shortcode($atts) {
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
        $this->display_loyalty_points($user_id);
        
        // Display booking history
        $this->booking_history_content();
        
        echo '</div>';
        
        return ob_get_clean();
    }
    
    /**
     * Add meta box to display customer loyalty points in admin
     */
    public function add_customer_loyalty_meta_box() {
        add_meta_box(
            'rbfw_customer_loyalty',
            __('Customer Loyalty Points', 'booking-and-rental-manager-for-woocommerce'),
            array($this, 'customer_loyalty_meta_box_content'),
            'rbfw_order',
            'side',
            'default'
        );
    }
    
    /**
     * Customer loyalty meta box content
     */
    public function customer_loyalty_meta_box_content($post) {
        $user_id = get_post_meta($post->ID, 'rbfw_user_id', true);
        
        if (!$user_id) {
            echo '<p>' . esc_html__('No customer associated with this order.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
            return;
        }
        
        $user = get_userdata($user_id);
        $loyalty_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
        $loyalty_points = !empty($loyalty_points) ? $loyalty_points : 0;
        
        echo '<p><strong>' . esc_html__('Customer:', 'booking-and-rental-manager-for-woocommerce') . '</strong> ' . esc_html($user->display_name) . '</p>';
        echo '<p><strong>' . esc_html__('Loyalty Points:', 'booking-and-rental-manager-for-woocommerce') . '</strong> ' . esc_html($loyalty_points) . '</p>';
        
        // Add form to manually adjust points
        echo '<div class="rbfw-adjust-points">';
        echo '<label for="rbfw_adjust_points">' . esc_html__('Adjust Points:', 'booking-and-rental-manager-for-woocommerce') . '</label>';
        echo '<input type="number" id="rbfw_adjust_points" name="rbfw_adjust_points" value="0" />';
        echo '<p class="description">' . esc_html__('Enter a positive or negative number to adjust points.', 'booking-and-rental-manager-for-woocommerce') . '</p>';
        echo '<input type="hidden" name="rbfw_user_id" value="' . esc_attr($user_id) . '" />';
        echo '<button type="button" class="button" id="rbfw_update_points">' . esc_html__('Update Points', 'booking-and-rental-manager-for-woocommerce') . '</button>';
        echo '</div>';
        
        // Add nonce for security
        wp_nonce_field('rbfw_update_loyalty_points', 'rbfw_loyalty_nonce');
    }
    
    /**
     * Redeem loyalty points
     */
    public function redeem_loyalty_points() {
        check_ajax_referer('rbfw_redeem_points', 'nonce');
        
        $user_id = get_current_user_id();
        $points_to_redeem = isset($_POST['points']) ? intval($_POST['points']) : 0;
        
        if (!$user_id || $points_to_redeem <= 0) {
            wp_send_json_error(array('message' => __('Invalid request.', 'booking-and-rental-manager-for-woocommerce')));
            return;
        }
        
        $current_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
        $current_points = !empty($current_points) ? intval($current_points) : 0;
        
        if ($points_to_redeem > $current_points) {
            wp_send_json_error(array('message' => __('You do not have enough points.', 'booking-and-rental-manager-for-woocommerce')));
            return;
        }
        
        // Store points to redeem in session
        WC()->session->set('rbfw_redeem_points', $points_to_redeem);
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d points will be applied to your order.', 'booking-and-rental-manager-for-woocommerce'), $points_to_redeem),
            'discount' => $points_to_redeem
        ));
    }
    
    /**
     * Apply loyalty discount to cart item price
     */
    public function apply_loyalty_discount($price, $cart_item, $cart_item_key) {
        // Check if points are being redeemed
        $points_to_redeem = WC()->session->get('rbfw_redeem_points');
        
        if (!$points_to_redeem) {
            return $price;
        }
        
        // Check if this is a rental/booking item
        if (!isset($cart_item['rbfw_id'])) {
            return $price;
        }
        
        // Calculate discount (1 point = 1 currency unit)
        $discount = min($points_to_redeem, $cart_item['data']->get_price());
        
        if ($discount > 0) {
            $new_price = $cart_item['data']->get_price() - $discount;
            $cart_item['data']->set_price($new_price);
            
            // Update session to reflect used points
            WC()->session->set('rbfw_redeem_points', $points_to_redeem - $discount);
            
            // Deduct points from user account
            $user_id = get_current_user_id();
            if ($user_id) {
                $current_points = get_user_meta($user_id, 'rbfw_loyalty_points', true);
                $current_points = !empty($current_points) ? intval($current_points) : 0;
                update_user_meta($user_id, 'rbfw_loyalty_points', $current_points - $discount);
            }
            
            return wc_price($new_price);
        }
        
        return $price;
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('rbfw-customer-management', RBFW_PLUGIN_URL . '/assets/css/rbfw-customer-management.css', array(), '1.0.0');
        wp_enqueue_script('rbfw-customer-management', RBFW_PLUGIN_URL . '/assets/js/rbfw-customer-management.js', array('jquery'), '1.0.0', true);
        
        wp_localize_script('rbfw-customer-management', 'rbfw_customer', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rbfw_redeem_points'),
            'redeem_confirm' => __('Are you sure you want to redeem these points?', 'booking-and-rental-manager-for-woocommerce')
        ));
    }
}

// Initialize the class
new RBFW_Customer_Management();