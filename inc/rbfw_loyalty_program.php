<?php
/**
 * Loyalty/Rewards Program for Booking and Rental Manager
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'RBFW_Loyalty_Program' ) ) {
    class RBFW_Loyalty_Program {
        
        public function __construct() {
            // Add settings section
            add_filter( 'rbfw_settings_sec_reg', array( $this, 'add_loyalty_settings_section' ), 10 );
            add_filter( 'rbfw_settings_sec_fields', array( $this, 'add_loyalty_settings_fields' ), 10 );

            // Add endpoint to My Account page
            add_action( 'init', array( $this, 'add_loyalty_endpoint' ) );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'add_loyalty_menu_item' ) );
            add_action( 'woocommerce_account_loyalty-rewards_endpoint', array( $this, 'loyalty_rewards_content' ) );

            // Award points on order completion
            add_action( 'woocommerce_order_status_completed', array( $this, 'award_loyalty_points' ) );

            // Process coupon generation
            add_action( 'wp_ajax_rbfw_generate_loyalty_coupon', array( $this, 'generate_loyalty_coupon' ) );
            add_action( 'wp_ajax_nopriv_rbfw_generate_loyalty_coupon', array( $this, 'generate_loyalty_coupon' ) );

            // Enqueue scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            // Display loyalty points in order details
            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_loyalty_points_in_order' ) );
            add_action( 'woocommerce_email_after_order_table', array( $this, 'display_loyalty_points_in_email' ) );

            // Display loyalty points notice in cart
            add_action( 'woocommerce_before_cart_totals', array( $this, 'display_loyalty_points_in_cart' ) );
            add_action( 'woocommerce_before_checkout_form', array( $this, 'display_loyalty_points_in_cart' ) );

            // Fix existing loyalty points data
            add_action( 'init', array( $this, 'fix_loyalty_points_data' ), 20 );
        }

        /**
         * Fix existing loyalty points data
         * This function ensures all loyalty points are stored as integers
         */
        public function fix_loyalty_points_data() {
            // Only run this once
            $fix_run = get_option('rbfw_loyalty_points_fix_run', 'no');
            if ($fix_run === 'yes') {
                return;
            }

            // Get all users with loyalty points
            $users_with_points = get_users(array(
                'meta_key' => 'rbfw_loyalty_points',
                'fields' => array('ID')
            ));

            foreach ($users_with_points as $user) {
                $user_id = $user->ID;
                $points = get_user_meta($user_id, 'rbfw_loyalty_points', true);

                // Convert to integer and update
                $points = intval($points);
                update_user_meta($user_id, 'rbfw_loyalty_points', $points);

                // Log the fix
                error_log('RBFW Loyalty: Fixed points for user ' . $user_id . ' - now ' . $points);
            }

            // Mark as run
            update_option('rbfw_loyalty_points_fix_run', 'yes');
        }

        /**
         * Display loyalty points in order details
         */
        public function display_loyalty_points_in_order( $order ) {
            include RBFW_TEMPLATE_PATH . 'loyalty/loyalty-points-display.php';
        }

        /**
         * Display loyalty points in order email
         */
        public function display_loyalty_points_in_email( $order ) {
            include RBFW_TEMPLATE_PATH . 'loyalty/loyalty-points-display.php';
        }

        /**
         * Display loyalty points in cart
         */
        public function display_loyalty_points_in_cart() {
            include RBFW_TEMPLATE_PATH . 'loyalty/loyalty-cart-notice.php';
        }
        
        /**
         * Add loyalty settings section
         */
        public function add_loyalty_settings_section( $sections ) {
            $loyalty_section = array(
                array(
                    'id'    => 'rbfw_loyalty_settings',
                    'title' => '<i class="fas fa-award"></i>' . __( 'Loyalty Program', 'booking-and-rental-manager-for-woocommerce' )
                )
            );
            
            return array_merge( $sections, $loyalty_section );
        }
        
        /**
         * Add loyalty settings fields
         */
        public function add_loyalty_settings_fields( $fields ) {
            $loyalty_fields = array(
                'rbfw_loyalty_settings' => array(
                    array(
                        'name'    => 'rbfw_loyalty_enable',
                        'label'   => __( 'Enable Loyalty Program', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Enable or disable the loyalty/rewards program.', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'select',
                        'default' => 'no',
                        'options' => array(
                            'yes' => 'Yes',
                            'no'  => 'No'
                        )
                    ),
                    array(
                        'name'    => 'rbfw_loyalty_points_per_currency',
                        'label'   => __( 'Points per Currency', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Number of points awarded per currency unit spent (e.g., 10 points per $1).', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'number',
                        'default' => '10'
                    ),
                    array(
                        'name'    => 'rbfw_loyalty_points_for_coupon',
                        'label'   => __( 'Points Required for Coupon', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Number of points required to generate a coupon.', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'number',
                        'default' => '1000'
                    ),
                    array(
                        'name'    => 'rbfw_loyalty_coupon_amount',
                        'label'   => __( 'Coupon Amount', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Amount of the coupon generated from points.', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'number',
                        'default' => '10'
                    ),
                    array(
                        'name'    => 'rbfw_loyalty_coupon_type',
                        'label'   => __( 'Coupon Type', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Type of coupon to generate.', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'select',
                        'default' => 'fixed_cart',
                        'options' => array(
                            'fixed_cart'  => 'Fixed Cart Discount',
                            'percent'     => 'Percentage Discount'
                        )
                    ),
                    array(
                        'name'    => 'rbfw_loyalty_coupon_expiry',
                        'label'   => __( 'Coupon Expiry (days)', 'booking-and-rental-manager-for-woocommerce' ),
                        'desc'    => __( 'Number of days until the coupon expires. Leave empty for no expiry.', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'number',
                        'default' => '30'
                    )
                )
            );
            
            return array_merge( $fields, $loyalty_fields );
        }
        
        /**
         * Add loyalty endpoint to My Account page
         */
        public function add_loyalty_endpoint() {
            add_rewrite_endpoint( 'loyalty-rewards', EP_ROOT | EP_PAGES );
            
            // Check if we need to flush rewrite rules
            if ( get_option( 'rbfw_loyalty_flush_rewrite_rules', 'yes' ) === 'yes' ) {
                flush_rewrite_rules();
                update_option( 'rbfw_loyalty_flush_rewrite_rules', 'no' );
            }
        }
        
        /**
         * Add loyalty menu item to My Account menu
         */
        public function add_loyalty_menu_item( $items ) {
            // Get loyalty program status
            $loyalty_enabled = get_option( 'rbfw_loyalty_settings' );
            $is_enabled = isset( $loyalty_enabled['rbfw_loyalty_enable'] ) ? $loyalty_enabled['rbfw_loyalty_enable'] : 'no';
            
            if ( $is_enabled === 'yes' ) {
                // Add the loyalty rewards item after the dashboard
                $new_items = array();
                
                foreach ( $items as $key => $value ) {
                    $new_items[ $key ] = $value;
                    
                    if ( $key === 'dashboard' ) {
                        $new_items['loyalty-rewards'] = __( 'Loyalty Rewards', 'booking-and-rental-manager-for-woocommerce' );
                    }
                }
                
                return $new_items;
            }
            
            return $items;
        }
        
        /**
         * Display loyalty rewards content in My Account page
         */
        public function loyalty_rewards_content() {
            // Get current user
            $user_id = get_current_user_id();

            // Get user's loyalty points
            $loyalty_points = get_user_meta( $user_id, 'rbfw_loyalty_points', true );
            if ( empty( $loyalty_points ) ) {
                $loyalty_points = 0;
            }

            // Ensure points are stored as integers
            $loyalty_points = intval($loyalty_points);

            // Get settings
            $loyalty_settings = get_option( 'rbfw_loyalty_settings' );
            $points_for_coupon = isset( $loyalty_settings['rbfw_loyalty_points_for_coupon'] ) ? intval($loyalty_settings['rbfw_loyalty_points_for_coupon']) : 1000;
            $coupon_amount = isset( $loyalty_settings['rbfw_loyalty_coupon_amount'] ) ? $loyalty_settings['rbfw_loyalty_coupon_amount'] : 10;
            $coupon_type = isset( $loyalty_settings['rbfw_loyalty_coupon_type'] ) ? $loyalty_settings['rbfw_loyalty_coupon_type'] : 'fixed_cart';

            // Get coupon type text
            $coupon_type_text = $coupon_type === 'fixed_cart' ? get_woocommerce_currency_symbol() . $coupon_amount : $coupon_amount . '%';

            // Get user's coupons
            $args = array(
                'posts_per_page' => -1,
                'post_type'      => 'shop_coupon',
                'meta_query'     => array(
                    array(
                        'key'     => 'rbfw_loyalty_coupon_user',
                        'value'   => $user_id,
                        'compare' => '='
                    )
                )
            );

            $coupons = get_posts( $args );

            // Display the content
            ?>
            <h2><?php _e( 'Loyalty Rewards', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>

            <div class="rbfw-loyalty-points-container">
                <div class="rbfw-loyalty-points-balance">
                    <h3><?php _e( 'Your Points Balance', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                    <p class="rbfw-loyalty-points-count"><?php echo esc_html( $loyalty_points ); ?></p>
                </div>

                <div class="rbfw-loyalty-points-info">
                    <p><?php printf( __( 'You need %s points to generate a %s coupon.', 'booking-and-rental-manager-for-woocommerce' ), '<strong>' . esc_html( $points_for_coupon ) . '</strong>', '<strong>' . esc_html( $coupon_type_text ) . '</strong>' ); ?></p>

                    <?php if ( $loyalty_points >= $points_for_coupon ) : ?>
                        <button class="button rbfw-generate-coupon-btn" data-nonce="<?php echo wp_create_nonce( 'rbfw_loyalty_coupon_nonce' ); ?>"><?php _e( 'Generate Coupon', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        <div class="rbfw-coupon-message"></div>
                    <?php else : ?>
                        <p><?php printf( __( 'You need %s more points to generate a coupon.', 'booking-and-rental-manager-for-woocommerce' ), '<strong>' . esc_html( $points_for_coupon - $loyalty_points ) . '</strong>' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ( ! empty( $coupons ) ) : ?>
                <div class="rbfw-loyalty-coupons">
                    <h3><?php _e( 'Your Coupons', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                    <table class="shop_table shop_table_responsive">
                        <thead>
                            <tr>
                                <th><?php _e( 'Coupon Code', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th><?php _e( 'Amount', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th><?php _e( 'Expiry Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th><?php _e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $coupons as $coupon ) : 
                                $wc_coupon = new WC_Coupon( $coupon->ID );
                                $expiry_date = $wc_coupon->get_date_expires();
                                $is_expired = $expiry_date && current_time( 'timestamp' ) > $expiry_date->getTimestamp();
                                $usage_count = $wc_coupon->get_usage_count();
                                $usage_limit = $wc_coupon->get_usage_limit();
                                $is_used = $usage_limit && $usage_count >= $usage_limit;
                                
                                if ( $is_expired ) {
                                    $status = __( 'Expired', 'booking-and-rental-manager-for-woocommerce' );
                                } elseif ( $is_used ) {
                                    $status = __( 'Used', 'booking-and-rental-manager-for-woocommerce' );
                                } else {
                                    $status = __( 'Active', 'booking-and-rental-manager-for-woocommerce' );
                                }
                            ?>
                                <tr>
                                    <td><?php echo esc_html( $wc_coupon->get_code() ); ?></td>
                                    <td>
                                        <?php 
                                        if ( $wc_coupon->get_discount_type() === 'percent' ) {
                                            echo esc_html( $wc_coupon->get_amount() ) . '%';
                                        } else {
                                            echo get_woocommerce_currency_symbol() . esc_html( $wc_coupon->get_amount() );
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ( $expiry_date ) {
                                            echo esc_html( $expiry_date->date_i18n( get_option( 'date_format' ) ) );
                                        } else {
                                            _e( 'No expiry', 'booking-and-rental-manager-for-woocommerce' );
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html( $status ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="rbfw-loyalty-history">
                <h3><?php _e( 'Points History', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                <?php
                $points_history = get_user_meta( $user_id, 'rbfw_loyalty_points_history', true );
                
                if ( empty( $points_history ) ) {
                    echo '<p>' . __( 'No points history available.', 'booking-and-rental-manager-for-woocommerce' ) . '</p>';
                } else {
                    echo '<table class="shop_table shop_table_responsive">';
                    echo '<thead><tr>';
                    echo '<th>' . __( 'Date', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
                    echo '<th>' . __( 'Points', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
                    echo '<th>' . __( 'Description', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                    
                    foreach ( array_reverse( $points_history ) as $history ) {
                        echo '<tr>';
                        echo '<td>' . date_i18n( get_option( 'date_format' ), $history['date'] ) . '</td>';
                        echo '<td>' . ( $history['points'] > 0 ? '+' : '' ) . esc_html( $history['points'] ) . '</td>';
                        echo '<td>' . esc_html( $history['description'] ) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                }
                ?>
            </div>
            <?php
        }
        
        /**
         * Award loyalty points when an order is completed
         */
        public function award_loyalty_points( $order_id ) {
            // Get order
            $order = wc_get_order( $order_id );

            // Check if order exists and has a customer
            if ( ! $order || ! $order->get_user_id() ) {
                return;
            }

            // Get loyalty program status
            $loyalty_settings = get_option( 'rbfw_loyalty_settings' );
            $is_enabled = isset( $loyalty_settings['rbfw_loyalty_enable'] ) ? $loyalty_settings['rbfw_loyalty_enable'] : 'no';

            if ( $is_enabled !== 'yes' ) {
                return;
            }

            // Check if points were already awarded for this order
            $points_awarded = get_post_meta( $order_id, 'rbfw_loyalty_points_awarded', true );
            if ( $points_awarded === 'yes' ) {
                return;
            }

            // Get user ID
            $user_id = $order->get_user_id();

            // Get points per currency setting
            $points_per_currency = isset( $loyalty_settings['rbfw_loyalty_points_per_currency'] ) ? $loyalty_settings['rbfw_loyalty_points_per_currency'] : 10;

            // Calculate points to award
            $order_total = $order->get_total();
            $points_to_award = round( $order_total * $points_per_currency );

            // Check if order contains RBFW items
            $has_rbfw_items = false;
            foreach ( $order->get_items() as $item ) {
                $product_id = $item->get_product_id();
                $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true );

                if ( $linked_rbfw_id || get_post_type( $product_id ) === 'rbfw_item' ) {
                    $has_rbfw_items = true;
                    break;
                }
            }

            // Only award points if order contains RBFW items
            if ( $has_rbfw_items ) {
                // Get current points
                $current_points = get_user_meta( $user_id, 'rbfw_loyalty_points', true );
                if ( empty( $current_points ) ) {
                    $current_points = 0;
                }

                // Add points
                $new_points = $current_points + $points_to_award;

                // Make sure we're storing as a numeric value, not a string
                $new_points = intval($new_points);

                // Update user meta with the new points value
                update_user_meta( $user_id, 'rbfw_loyalty_points', $new_points );

                // Add to points history
                $this->add_points_history( $user_id, $points_to_award, sprintf( __( 'Points earned from order #%s', 'booking-and-rental-manager-for-woocommerce' ), $order_id ) );

                // Mark order as points awarded
                update_post_meta( $order_id, 'rbfw_loyalty_points_awarded', 'yes' );
                update_post_meta( $order_id, 'rbfw_loyalty_points_amount', $points_to_award );

                // Add note to order
                $order->add_order_note( sprintf( __( 'Loyalty points awarded: %s', 'booking-and-rental-manager-for-woocommerce' ), $points_to_award ) );
            }
        }
        
        /**
         * Add entry to points history
         */
        public function add_points_history( $user_id, $points, $description ) {
            $history = get_user_meta( $user_id, 'rbfw_loyalty_points_history', true );

            if ( empty( $history ) ) {
                $history = array();
            }

            // Ensure points are stored as integers
            $points = intval($points);

            $history[] = array(
                'date'        => current_time( 'timestamp' ),
                'points'      => $points,
                'description' => $description
            );

            update_user_meta( $user_id, 'rbfw_loyalty_points_history', $history );

            // Debug: Log the points history update
            error_log('RBFW Loyalty: Added ' . $points . ' points to user ' . $user_id . ' - ' . $description);
            error_log('RBFW Loyalty: Current history: ' . print_r($history, true));
        }
        
        /**
         * Generate a coupon from loyalty points
         */
        public function generate_loyalty_coupon() {
            // Verify nonce
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'rbfw_loyalty_coupon_nonce' ) ) {
                wp_send_json_error( array( 'message' => __( 'Security check failed.', 'booking-and-rental-manager-for-woocommerce' ) ) );
            }

            // Get current user
            $user_id = get_current_user_id();

            if ( ! $user_id ) {
                wp_send_json_error( array( 'message' => __( 'You must be logged in to generate a coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
            }

            // Get loyalty settings
            $loyalty_settings = get_option( 'rbfw_loyalty_settings' );
            $points_for_coupon = isset( $loyalty_settings['rbfw_loyalty_points_for_coupon'] ) ? intval($loyalty_settings['rbfw_loyalty_points_for_coupon']) : 1000;
            $coupon_amount = isset( $loyalty_settings['rbfw_loyalty_coupon_amount'] ) ? $loyalty_settings['rbfw_loyalty_coupon_amount'] : 10;
            $coupon_type = isset( $loyalty_settings['rbfw_loyalty_coupon_type'] ) ? $loyalty_settings['rbfw_loyalty_coupon_type'] : 'fixed_cart';
            $coupon_expiry = isset( $loyalty_settings['rbfw_loyalty_coupon_expiry'] ) ? $loyalty_settings['rbfw_loyalty_coupon_expiry'] : 30;

            // Get user's loyalty points
            $loyalty_points = get_user_meta( $user_id, 'rbfw_loyalty_points', true );
            if ( empty( $loyalty_points ) ) {
                $loyalty_points = 0;
            }

            // Ensure points are stored as integers
            $loyalty_points = intval($loyalty_points);

            // Check if user has enough points
            if ( $loyalty_points < $points_for_coupon ) {
                wp_send_json_error( array( 'message' => __( 'You do not have enough points to generate a coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
            }

            // Generate coupon code
            $user_info = get_userdata( $user_id );
            $username = $user_info->user_login;
            $coupon_code = 'LOYALTY-' . strtoupper( substr( $username, 0, 5 ) ) . '-' . strtoupper( wp_generate_password( 5, false ) );

            // Create coupon
            $coupon = array(
                'post_title'   => $coupon_code,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'shop_coupon'
            );

            $coupon_id = wp_insert_post( $coupon );

            if ( is_wp_error( $coupon_id ) ) {
                wp_send_json_error( array( 'message' => __( 'Error creating coupon.', 'booking-and-rental-manager-for-woocommerce' ) ) );
            }

            // Set coupon data
            update_post_meta( $coupon_id, 'discount_type', $coupon_type );
            update_post_meta( $coupon_id, 'coupon_amount', $coupon_amount );
            update_post_meta( $coupon_id, 'individual_use', 'yes' );
            update_post_meta( $coupon_id, 'usage_limit', '1' );
            update_post_meta( $coupon_id, 'usage_limit_per_user', '1' );
            update_post_meta( $coupon_id, 'rbfw_loyalty_coupon', 'yes' );
            update_post_meta( $coupon_id, 'rbfw_loyalty_coupon_user', $user_id );

            // Set expiry date if specified
            if ( ! empty( $coupon_expiry ) ) {
                $expiry_date = date( 'Y-m-d', strtotime( "+{$coupon_expiry} days" ) );
                update_post_meta( $coupon_id, 'date_expires', strtotime( $expiry_date ) );
            }

            // Deduct points from user
            $new_points = $loyalty_points - $points_for_coupon;

            // Ensure new points are stored as integers
            $new_points = intval($new_points);

            update_user_meta( $user_id, 'rbfw_loyalty_points', $new_points );

            // Add to points history
            $this->add_points_history( $user_id, -$points_for_coupon, sprintf( __( 'Points redeemed for coupon %s', 'booking-and-rental-manager-for-woocommerce' ), $coupon_code ) );

            // Return success
            wp_send_json_success( array(
                'message'     => __( 'Coupon generated successfully!', 'booking-and-rental-manager-for-woocommerce' ),
                'coupon_code' => $coupon_code,
                'new_points'  => $new_points
            ) );
        }
        
        /**
         * Enqueue scripts
         */
        public function enqueue_scripts() {
            if ( is_account_page() ) {
                wp_enqueue_style( 'rbfw-loyalty-style', RBFW_PLUGIN_URL . '/assets/css/rbfw-loyalty.css', array(), '1.0.0' );
                wp_enqueue_script( 'rbfw-loyalty-script', RBFW_PLUGIN_URL . '/assets/js/rbfw-loyalty.js', array( 'jquery' ), '1.0.0', true );
                
                wp_localize_script( 'rbfw-loyalty-script', 'rbfw_loyalty', array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'i18n'     => array(
                        'generating_coupon' => __( 'Generating coupon...', 'booking-and-rental-manager-for-woocommerce' ),
                        'error'             => __( 'Error:', 'booking-and-rental-manager-for-woocommerce' ),
                        'success'           => __( 'Success:', 'booking-and-rental-manager-for-woocommerce' ),
                        'coupon_code'       => __( 'Your coupon code is:', 'booking-and-rental-manager-for-woocommerce' )
                    )
                ) );
            }
        }
    }
    
    new RBFW_Loyalty_Program();
}