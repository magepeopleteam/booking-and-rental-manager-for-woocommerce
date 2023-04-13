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

if (!class_exists('Rbfw_Account_Page')) {
    class Rbfw_Account_Page{
        public function __construct(){
            add_action('wp_loaded', array($this,'rbfw_account_page'));
            add_shortcode('rbfw_account', array($this,'rbfw_account_shortcode_func'));
            add_action('wp_footer', array($this,'rbfw_account_frontend_scripts'));
            add_action('wp_ajax_rbfw_ba_user_signin_signup_form_submit', array($this, 'rbfw_ba_user_signin_signup_form_submit'));
            add_action('wp_ajax_nopriv_rbfw_ba_user_signin_signup_form_submit', array($this,'rbfw_ba_user_signin_signup_form_submit'));
            add_action('rbfw_before_account_order_list', array($this,'rbfw_account_page_navigation'));
            add_action('rbfw_before_account_order_details', array($this,'rbfw_account_page_navigation'));
            add_filter('display_post_states', array($this, 'rbfw_add_post_state'), 10, 2);
        }

        public function rbfw_account_page(){
            
            $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');
            if($ac_page_id){
                $args = array(
                    'ID'           => $ac_page_id,
                    'post_content' => '[rbfw_account]',
                    'post_status'   => 'publish'
                );
                wp_update_post($args);

            }else{

                $page_obj = rbfw_exist_page_by_title('Booking Account');

                if($page_obj === false){

                    $args = array(
                        'post_title'    => 'Booking Account',
                        'post_content'  => '[rbfw_account]',
                        'post_status'   => 'publish',
                        'post_type'     => 'page'
                    );
                    $post_id = wp_insert_post( $args );
    
                    if($post_id){
                        $gen_settings = !empty(get_option('rbfw_basic_gen_settings')) ? get_option('rbfw_basic_gen_settings') : [];
                        $new_gen_settings = array_merge($gen_settings, ['rbfw_account_page' => $post_id]);
                        update_option('rbfw_basic_gen_settings', $new_gen_settings);
    
                    }
                }
            }
        }

        function rbfw_add_post_state( $post_states, $post ) {
            $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');

            if(!empty($ac_page_id)){
                if( $post->ID == $ac_page_id ) {
                    $post_states[] = 'Booking Account Page';
                }
            }

            return $post_states;
        }

        public function rbfw_account_shortcode_func(){
            ob_start();
            if(!is_user_logged_in()){
                echo $this->rbfw_account_signin_signout_form();

            }else{

                $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');
                $current_page_id = get_queried_object_id();

                if(!isset($ac_page_id)){
                    return;
                }

                if($current_page_id != $ac_page_id){
                    return;
                }

                if(isset($_GET['view_order'])){
                    $this->rbfw_account_view_order_details();
                }else{
                    $this->rbfw_account_view_order_list();
                }
            }

            $content = ob_get_clean();
            return $content;
        }

        public function rbfw_account_page_navigation(){
            $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');
            $ac_page_url = get_page_link($ac_page_id);

            $content  = '';
            $content .= '<div class="rbfw_ba_page_nav_wrap">';
            $content .= '<a href="'.esc_url($ac_page_url).'">'.__('Dashboard','booking-and-rental-manager-for-woocommerce').'</a>';
            $content .= do_action('rbfw_ba_nav_menu');
            $content .= '<a href="'.esc_url(wp_logout_url($ac_page_url)).'">'.__('Log-out','booking-and-rental-manager-for-woocommerce').'</a>';
            $content .= '</div>';

            echo $content;
        }

        public function rbfw_account_signin_signout_form(){
            $content = '';

            $content .= '<div class="rbfw_ba_user_form_wrap">';

            /* Sign In Form Wrap */
            $content .= '<div class="rbfw_ba_signin_form_wrap rbfw_ba_form_wrap" data-id="login">';
            $content .= '<form class="rbfw_ba_signin_form" method="POST">';
            
            $content .= '<div class="rbfw_ba_form_header">';
            $content .= esc_html__('Sign In','booking-and-rental-manager-for-woocommerce');
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_email">'.esc_html__('Email Address','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="email" name="rbfw_ba_user_email" id="rbfw_ba_user_email" class="rbfw_ba_user_input"/>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_password">'.esc_html__('Password','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="password" name="rbfw_ba_user_password" id="rbfw_ba_user_password" class="rbfw_ba_user_input"/>';
            $content .= '</div>'; 
            
            $content .= '<a class="rbfw_ba_forgot_password_link" href="'.esc_url(wp_lostpassword_url()).'" target="_blank">'.esc_html__('Forgot password?','booking-and-rental-manager-for-woocommerce').'</a>';
            
            $content .= '<div class="rbfw_ba_button_group">';
            $content .= '<button type="submit" id="rbfw_ba_user_signin_button" class="rbfw_ba_user_button">'.esc_html__('Log In','booking-and-rental-manager-for-woocommerce').' <i class="fas fa-spin"></i></button>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_user_form_result"></div>';
            
            $content .= wp_nonce_field( 'rbfw_ba_user_submit_request', 'rbfw_ba_user_submit_request_nonce' );
            $content .= '<input type="hidden" name="action" value="rbfw_ba_user_signin_signup_form_submit"/>';
            $content .= '<input type="hidden" name="rbfw_ba_user_submit_request" value="signin"/>';
            $content .= '</form>';
            $content .= '</div>';
            /* End Sign In Form Wrap */
            
            /* Sign Up Form Wrap */
            $content .= '<div class="rbfw_ba_signup_form_wrap rbfw_ba_form_wrap" data-id="signup">';
            $content .= '<form class="rbfw_ba_signup_form" method="POST">';
            
            $content .= '<div class="rbfw_ba_form_header">';
            $content .= esc_html__('Sign Up','booking-and-rental-manager-for-woocommerce');
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_fname">'.esc_html__('First Name','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="text" name="rbfw_ba_user_fname" id="rbfw_ba_user_fname" class="rbfw_ba_user_input" value=""/>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_lname">'.esc_html__('Last Name','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="text" name="rbfw_ba_user_lname" id="rbfw_ba_user_lname" class="rbfw_ba_user_input" value=""/>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_email">'.esc_html__('Email Address','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="email" name="rbfw_ba_user_email" id="rbfw_ba_user_email" class="rbfw_ba_user_input"/>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_password">'.esc_html__('Password','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="password" name="rbfw_ba_user_password" id="rbfw_ba_user_password" class="rbfw_ba_user_input"/>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_input_group">';
            $content .= '<label for="rbfw_ba_user_cpassword">'.esc_html__('Confirm Password','booking-and-rental-manager-for-woocommerce').'</label>';
            $content .= '<input type="password" name="rbfw_ba_user_cpassword" id="rbfw_ba_user_cpassword" class="rbfw_ba_user_input"/>';
            $content .= '</div>';                    
            
            $content .= '<div class="rbfw_ba_button_group">';
            $content .= '<button type="submit" id="rbfw_ba_user_signup_button" class="rbfw_ba_user_button">'.esc_html__('Sign Up','booking-and-rental-manager-for-woocommerce').' <i class="fas fa-spin"></i></button>';
            $content .= '</div>';
            
            $content .= '<div class="rbfw_ba_user_form_result"></div>';
            
            $content .= wp_nonce_field( 'rbfw_ba_user_submit_request', 'rbfw_ba_user_submit_request_nonce' );
            $content .= '<input type="hidden" name="action" value="rbfw_ba_user_signin_signup_form_submit"/>';
            $content .= '<input type="hidden" name="rbfw_ba_user_submit_request" value="signup"/>';
            $content .= '</form>';
            $content .= '</div>';
            /* End Sign Up Form Wrap */
            
            $content .= '</div>';

            return $content;
            
        }

        public function rbfw_ba_user_signin_signup_form_submit(){
            check_ajax_referer( 'rbfw_ba_user_submit_request', 'rbfw_ba_user_submit_request_nonce' );
        
            $request = isset($_POST['rbfw_ba_user_submit_request']) ? strip_tags($_POST['rbfw_ba_user_submit_request']) : '';
            $username = isset($_POST['rbfw_ba_user_username']) ? strip_tags($_POST['rbfw_ba_user_username']) : '';
            $email = isset($_POST['rbfw_ba_user_email']) ? filter_var($_POST['rbfw_ba_user_email'], FILTER_SANITIZE_EMAIL) : '';
            $password = isset($_POST['rbfw_ba_user_password']) ? strip_tags($_POST['rbfw_ba_user_password']) : '';
            $confirm_password = isset($_POST['rbfw_ba_user_cpassword']) ? strip_tags($_POST['rbfw_ba_user_cpassword']) : '';
            $first_name = isset($_POST['rbfw_ba_user_fname']) ? strip_tags($_POST['rbfw_ba_user_fname']) : '';
            $last_name = isset($_POST['rbfw_ba_user_lname']) ? strip_tags($_POST['rbfw_ba_user_lname']) : '';
        
            if($request == 'signin'){
                
                $errors = '';
        
                if(empty($email)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is not valid!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(empty($password)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Password is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(empty($errors)){
                    $creds = array(
                        'user_login'    => $email,
                        'user_password' => $password,
                        'remember'      => true
                    );
                 
                    $user = wp_signon( $creds, false );
                 
                    if ( is_wp_error( $user ) ) {
                        $msg = '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('The username or password is incorrect!','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;
                    }else{
                        wp_set_current_user($user->ID);
                        wp_set_auth_cookie($user->ID);
                        $msg = '<p class="ba_alert_login_success"><i class="fa-solid fa-circle-check"></i> '.__('Login successful, redirecting...','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;
                    }
                }
                else{
                    echo $errors;
                }
        
            }elseif($request == 'signup'){
                
                $errors = '';
        
                if(empty($first_name)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('First name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(empty($last_name)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Last name is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(empty($email)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Email is not valid!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(empty($password)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Password is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
                
                if(empty($confirm_password)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Confirm Password is required!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
        
                if(!empty($password) && !empty($confirm_password) && ($password != $confirm_password)):
                $errors .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.__('Password doesn\'t match!','booking-and-rental-manager-for-woocommerce').'</p>';
                endif;
                
                if(empty($errors)){
                    $user_id = wp_create_user( $email, $password ,$email );
        
                    if ( is_wp_error( $user_id ) ) {
                        $msg = '';
                        foreach( $user_id->errors as $key => $val ){
                            foreach( $val as $k => $v ){
                                $msg .= '<p class="ba_alert_warning"><i class="fa-solid fa-circle-info"></i> '.$v.'</p>';
                            }
                        }
                        echo $msg;
                    }else{
                        wp_new_user_notification($user_id, 'both');
                        wp_set_current_user($user_id);
                        wp_set_auth_cookie($user_id);
                        update_user_meta( $user_id, 'first_name', $first_name );
                        update_user_meta( $user_id, 'last_name', $last_name );
                        $msg = '<p class="ba_alert_login_success"><i class="fa-solid fa-circle-check"></i> '.__('Registration successful, redirecting...','booking-and-rental-manager-for-woocommerce').'</p>';
                        echo $msg;
                    }
                }
                else{
                    echo $errors;
                }
                
            }else{
                // Display Something is wrong
            }
        
        
            wp_die();
        }

        public function rbfw_account_frontend_scripts(){
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery( ".rbfw_ba_form_wrap form" ).on( "submit", function( e ) {
                            e.preventDefault();
                            let this_form = jQuery(this);
                            let form_data = jQuery(this).serialize();

                            jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax.rbfw_ajaxurl,
                                data: form_data,
                                beforeSend: function() {
                                    jQuery('.rbfw_ba_user_form_result').empty();
                                    this_form.find('.rbfw_ba_user_button i').addClass('fa-spinner');
                                },		
                                success: function (response) {  
                                    jQuery('.rbfw_ba_user_button i').removeClass('fa-spinner');
                                    this_form.find('.rbfw_ba_user_form_result').html(response);
                                    if (response.indexOf('ba_alert_login_success') >= 0){
                                        window.location=document.location.href;
                                    } 
                                }
                            });
                    });
                }); 
            </script>
            <?php
        }

        public function rbfw_account_view_order_list(){
            $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');
            $current_user = wp_get_current_user();
            $current_user_email = $current_user->user_email;

            $args = array(
                'post_type' => 'rbfw_order',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby'=> 'post_date', 
                'order' => 'DESC'
            );
        
            $meta_query = array(
                'meta_query' => array(
                    'meta_value' => array(
                            'key' => 'rbfw_billing_email',
                            'value' => $current_user_email,
                            'compare' => '==', 
                    )
                )
            );

            $args = array_merge($args,$meta_query);
            
            $query = new WP_Query($args);  
            
            ob_start();
            ?>
            <div class="rbfw_account_page_wrap">
            <?php do_action('rbfw_before_account_order_list'); ?>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Order','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th><?php esc_html_e('Status','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th><?php esc_html_e('Total','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th><?php esc_html_e('Action','booking-and-rental-manager-for-woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
            <?php 
            if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post(); 
            global $post;
            $order_id = $post->ID;
            $status = get_post_meta($order_id, 'rbfw_order_status', true);
            $total_cost = rbfw_mps_price(get_post_meta($order_id, 'rbfw_ticket_total_price', true));
            $view_order_url = get_page_link($ac_page_id).'?view_order='.$order_id;
            ?>
                    <tr>
                        <td><?php echo '#'.$order_id; ?></td>
                        <td><?php echo esc_html(get_the_date( 'F j, Y' )).' '.esc_html(get_the_time()); ?></td>
                        <td><?php echo esc_html($status); ?></td>
                        <td><?php echo $total_cost; ?></td>
                        <td>
                            <a href="<?php echo esc_url($view_order_url); ?>" class="rbfw_order_action_btn"><?php esc_html_e('View','booking-and-rental-manager-for-woocommerce'); ?></a>
                            <?php do_action('rbfw_after_order_action_btn',$order_id); ?>
                        </td>
                    </tr>
            <?php
            endwhile; 
        else:
            ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('Sorry, no data found!','booking-and-rental-manager-for-woocommerce'); ?></td>
                    </tr>            
            <?php            
        endif;
            wp_reset_query();
            ?>
                </tbody>
            </table>
            <?php do_action('rbfw_after_account_order_list'); ?>
            </div>
            <?php
            $content = ob_get_clean();
            echo $content;
        }

        public function rbfw_account_view_order_details(){
            global $rbfw;

            if(!is_user_logged_in()){
                return;
            }      
            
            $ac_page_id = rbfw_get_option('rbfw_account_page','rbfw_basic_gen_settings');
            $current_page_id = get_queried_object_id();

            $current_user = wp_get_current_user();
            $current_user_email = $current_user->user_email;

            if(isset($_GET['view_order'])){
                $order_id = $_GET['view_order'];
                $billing_email = get_post_meta($order_id, 'rbfw_billing_email', true);

                if($current_user_email != $billing_email){
                    return;
                }

                $status = get_post_meta($order_id, 'rbfw_order_status', true);
                $billing_name = get_post_meta($order_id, 'rbfw_billing_name', true);
                $billing_email = get_post_meta($order_id, 'rbfw_billing_email', true);
                $payment_method = get_post_meta($order_id, 'rbfw_payment_method', true);
                $payment_id = get_post_meta($order_id, 'rbfw_payment_id', true);
                
                $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            
                if($rbfw_payment_system == 'wps'){
                    $order_no = get_post_meta($order_id, 'rbfw_order_id', true);
                }else{
                    $order_no = $order_id;
                }
                $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
                $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');

                ob_start();
                ?>
                <div class="rbfw_account_page_wrap">
                <?php do_action('rbfw_before_account_order_details',$order_id); ?>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th colspan="2"><?php esc_html_e( 'Order Information', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e( 'Order number:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo esc_html($order_id); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Order created date:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo esc_html(get_the_date( 'F j, Y' )).' '.esc_html(get_the_time()); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Name:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo esc_html($billing_name); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Email:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo esc_html($billing_email); ?></td>
                        </tr>                        
                        <tr>
                            <td><strong><?php esc_html_e( 'Payment method:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo esc_html($payment_method); ?></td>
                        </tr>                    
                    </tbody>
                </table>
                <?php 
                /* Loop Ticket Info */
                $ticket_infos = !empty(get_post_meta($order_id,'rbfw_ticket_info',true)) ? get_post_meta($order_id,'rbfw_ticket_info',true) : [];
                 foreach ($ticket_infos as $ticket_info) {
                
            
                $item_name = $ticket_info['ticket_name'];
                $rbfw_id = $ticket_info['rbfw_id'];
                $item_id = $rbfw_id;
                $rent_type = $ticket_info['rbfw_rent_type'];

                $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-time-text');
                $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-time-text');
                $rbfw_start_time = $ticket_info['rbfw_start_time'];
                $rbfw_end_time = $ticket_info['rbfw_end_time'];

                if($rent_type == 'resort'){

                    $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-text');
                    $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-text');

                }elseif($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){

                    $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-time-text');
                    $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-text');

                }else{

                    $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-time-text');
                    $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-time-text');
                }

                $tax = !empty($ticket_info['rbfw_mps_tax']) ? $ticket_info['rbfw_mps_tax'] : 0;
                $mps_tax_percentage = !empty(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) : '';
                $tax_status = '';
                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && $mps_tax_format == 'including_tax'){
                    $tax_status = '('.rbfw_string_return('rbfw_text_includes',__('Includes','booking-and-rental-manager-for-woocommerce')).' '.rbfw_mps_price($tax).' '.rbfw_string_return('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')).')';
                }

                if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                    $BikeCarSdClass = new RBFW_BikeCarSd_Function();
                    $rent_info = $ticket_info['rbfw_type_info'];
                    $service_info = $ticket_info['rbfw_service_info'];
                    $rent_info = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info($item_id, $rent_info);
                    $service_info = $BikeCarSdClass->rbfw_get_bikecarsd_service_info($item_id, $service_info);

                }elseif($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){
                    $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                    $service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
                    $service_info = $BikeCarMdClass->rbfw_get_bikecarmd_service_info($item_id, $service_info);

                    $item_quantity = !empty($ticket_info['rbfw_item_quantity']) ? $ticket_info['rbfw_item_quantity'] : 1;
                    $pickup_point = !empty($ticket_info['rbfw_pickup_point']) ? $ticket_info['rbfw_pickup_point'] : '';
                    $dropoff_point = !empty($ticket_info['rbfw_dropoff_point']) ? $ticket_info['rbfw_dropoff_point'] : '';

                }elseif($rent_type == 'resort'){
                    $ResortClass = new RBFW_Resort_Function();
                    $package = $ticket_info['rbfw_resort_package'];
                    $rent_info = $ticket_info['rbfw_type_info'];
                    $rent_info  = $ResortClass->rbfw_get_resort_room_info($item_id, $rent_info, $package);
                    $service_info = $ticket_info['rbfw_service_info'];
                    $service_info = $ResortClass->rbfw_get_resort_service_info($item_id, $service_info);

                }else{
                    $rent_info = '';
                    $service_info = '';
                }

                $duration_cost = rbfw_mps_price($ticket_info['duration_cost']);
                $service_cost = rbfw_mps_price($ticket_info['service_cost']);
                $total_cost = rbfw_mps_price($ticket_info['ticket_price']);

                /* End  loop*/
                ?>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th colspan="2"><?php esc_html_e( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php esc_html_e( 'Item Name:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($item_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Item Type:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo rbfw_get_type_label($rent_type); ?></td>
                    </tr>
                    <?php if($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){ ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Pick-up Point:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($pickup_point); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Drop-off Point:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($dropoff_point); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if($rent_type == 'resort'){ ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Package:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($package); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){ ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Rent Information:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                                if(!empty($rent_info)){
                                    foreach ($rent_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value;?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($rent_type == 'resort'){ ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Room Information:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                                if(!empty($rent_info)){
                                    foreach ($rent_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Extra Service Information:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $key; ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            elseif($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            elseif($rent_type == 'resort'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Start Date and Time:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($rbfw_start_datetime); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'End Date and Time:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html($rbfw_end_datetime); ?></td>
                    </tr>

                    <tr>
                        <td><strong><?php esc_html_e( 'Duration Cost:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo $duration_cost; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Resource Cost:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo $service_cost; ?></td>
                    </tr>
                    <?php if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($tax)){ ?>
                    <tr>
                        <td><strong><?php echo $rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                        <td><?php echo rbfw_mps_price($tax); ?></td>
                    </tr>
                    <?php } ?>    
                    <tr>
                        <td><strong><?php esc_html_e( 'Total Cost:', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo $total_cost.' '.$tax_status; ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } ?>
                <?php do_action('rbfw_after_account_order_details',$order_id); ?>
                </div>
                <?php
                $content = ob_get_clean();
                echo $content;
            }
        }


    }
    new Rbfw_Account_Page();
}