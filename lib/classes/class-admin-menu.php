<?php
/*
* @Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Mahin and Ariful
* Version	:	1.0.0
*/
if (!defined('ABSPATH'))
    exit;  // if direct access

if (!class_exists('MageRBFWClass')) {
    class MageRBFWClass{

        private $settings_api;

        public function __construct() {
            $this->settings_api = new RBFW_Setting_API;
            add_action('add_meta_boxes', array($this, 'add_meta_box_func'));
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            /* WooCommerce Action and Filter */
            add_filter('woocommerce_order_status_changed', array($this, 'rbfw_wc_status_update'), 10, 4);
            //add_action('wp_insert_post', array($this, 'create_hidden_wc_product_on_publish'), 10, 3);
            add_action('save_post', array($this, 'run_link_product_on_save'), 99, 1);
            add_action('wp', array($this, 'hide_hidden_wc_product_from_frontend'));
            add_action('parse_query', [$this, 'rbfw_hide_hidden_wc_products_in_list_page']);
            /* End WooCommerce Action and Filter */
        }

    function rbfw_hide_hidden_wc_products_in_list_page($query) {
        global $pagenow;
        $taxonomy = 'product_visibility';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
            $tax_query = array(
                [
                    'taxonomy' => 'product_visibility',
                    'field' => 'slug',
                    'terms' => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ],
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'uncategorized	',
                    'operator' => 'NOT IN',
                ]
            );
            $query->set('tax_query', $tax_query);
        }

    }

        function admin_init() {
            $this->settings_api->set_sections($this->get_settings_sections());
            $this->settings_api->set_fields($this->get_settings_fields());
            $this->settings_api->admin_init();
        }

        function admin_menu() {

            add_submenu_page('edit.php?post_type=rbfw_item', __('Time Slots', 'booking-and-rental-manager-for-woocommerce'), __('Time Slots', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_time_slots', array($this, 'rbfw_time_slots'));

            add_submenu_page('edit.php?post_type=rbfw_item', __('Order List', 'booking-and-rental-manager-for-woocommerce'), __('Order List', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_order', array($this, 'rbfw_order_list'));

            add_submenu_page('edit.php?post_type=rbfw_item', __('Inventory', 'booking-and-rental-manager-for-woocommerce'), __('Inventory', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_inventory', array($this, 'rbfw_inventory_list'));

            do_action('rbfw_admin_menu_after_inventory');

            add_submenu_page('edit.php?post_type=rbfw_item', __('Settings', 'booking-and-rental-manager-for-woocommerce'), __('Settings', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_settings_page', array($this, 'plugin_page'));

            do_action('rbfw_admin_menu_after_settings');

            // If PRO plugin is activated
            if ( !is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php' ) ) {
                /* Add Pro Submenu */
                add_submenu_page('edit.php?post_type=rbfw_item', __('Get PRO','booking-and-rental-manager-for-woocommerce'), '<span class="rbfw_plugin_pro_menu">'.__('Get PRO','booking-and-rental-manager-for-woocommerce').'</span>', 'manage_options', 'rbfw_go_pro_page', array($this, 'rbfw_go_pro_page'));
            }
            // End PRO plugin is activated
        }

        public function rbfw_time_slots(){
            $time_slots_page = new RBFW_Timeslots_Page();
            echo $time_slots_page->rbfw_time_slots_page();
        }

        public function rbfw_order_list(){
            $order_page = new RBFWOrderPage();
            echo $order_page->rbfw_order_page();
        }

        public function rbfw_inventory_list(){
            $inventory_page = new RBFWInventoryPage();
            echo $inventory_page->rbfw_inventory_page();
        }

        public function get_cpt_name() {
            return 'rbfw_item';
        }

        public function get_name() {
            return $this->get_option('rbfw_rent_label', 'rbfw_basic_gen_settings', 'Rent Item');
        }

        public function get_slug() {
            return $this->get_option('rbfw_rent_slug', 'rbfw_basic_gen_settings', 'rent');
        }

        public function get_icon() {
            return $this->get_option('rbfw_rent_icon', 'rbfw_basic_gen_settings', 'dashicons-clipboard');;
        }

        public function get_cat_name() {
            return $this->get_option('rbfw_rent_cat_label', 'rbfw_basic_gen_settings', 'Category');
        }

        public function get_cat_slug() {
            return $this->get_option('rbfw_rent_cat_slug', 'rbfw_basic_gen_settings', 'rent-category');
        }

        function rbfw_go_pro_page(){
            $RBFWProPage = new RBFWProPage();
            $RBFWProPage->rbfw_go_pro_page();
        }

        function get_settings_sections() {
            $sections = array();
            return apply_filters('rbfw_settings_sec_reg', $sections);
        }

        function get_settings_fields() {
            $settings_fields = array();
            return apply_filters('rbfw_settings_sec_fields', $settings_fields);
        }

        function plugin_page() {
            echo '<div class="wrap">';
            settings_errors();
            echo '</div>';
            echo '<div class="rbfw_settings_wrapper">';
            echo '<div class="rbfw_settings_inner_wrapper">';
            echo '<div class="rbfw_settings_panel_header">';
            echo rbfw_get_plugin_data('Name');
            echo '<small>'.rbfw_get_plugin_data('Version').'</small>';
            echo '</div>';
            echo '<div class="mage_settings_panel_wrap rbfw_settings_panel">';
            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();
            echo '</div>';
            echo '</div>';
            echo '</div>';

        }

        function array_strip($array_or_string) {
            if (is_string($array_or_string)) {
                $array_or_string = sanitize_text_field($array_or_string);
            } elseif (is_array($array_or_string)) {
                foreach ($array_or_string as $key => &$value) {
                    if (is_array($value)) {
                        $value = rbfw_array_strip($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                }
            }
            return $array_or_string;
        }

        function get_pages() {
            $pages = get_pages();
            $pages_options = array();
            if ($pages) {
                foreach ($pages as $page) {
                    $pages_options[$page->ID] = $page->post_title;
                }
            }
            return $pages_options;
        }

        function get_option($option, $section, $default = '') {
            $options = get_option($section);
            if (!empty($options[$option])) {

                if(is_array($options[$option])){
                    return $options[$option];
                }else {
                    return esc_html($options[$option]);
                }

            }
            return $default;
        }

        public function send_email($sent_email, $rbfw_id = '', $email_sub = '', $content = '', $order_id = '', $attathment_file_url = '') {
            $global_email_text = $this->get_option('mep_confirmation_email_text', 'email_setting_sec', '');
            $global_email_form_email = $this->email_from_email();
            $global_email_form = $this->email_from_name();
            $global_email_sub = $this->get_option('mep_email_subject', 'email_setting_sec', '');
            $admin_email = get_option('admin_email');
            $site_name = get_option('blogname');
            $attachments = array();

            if (!empty($email_sub)) {
                $email_sub = $email_sub;
            } elseif ($global_email_sub) {
                $email_sub = $global_email_sub;
            } else {
                $email_sub = 'Confirmation Email';
            }
            if ($global_email_form) {
                $form_name = $global_email_form;
            } else {
                $form_name = $site_name;
            }
            if ($global_email_form_email) {
                $form_email = $global_email_form_email;
            } else {
                $form_email = $admin_email;
            }
            if (!empty($content)) {
                $email_body = $content;
            } elseif ($event_email_text) {
                $email_body = $event_email_text;
            } else {
                $email_body = $global_email_text;
            }

            $headers[] = "From: $form_name <$form_email>";
            if (!empty($attathment_file_url) && !is_wp_error($attathment_file_url))
                $attachments[] = $attathment_file_url;
            if ($email_body) {
                $confirmation_email_text = apply_filters('rbfw_send_email_content_text', $email_body, $rbfw_id, $order_id);
                wp_mail($sent_email, $email_sub, nl2br($confirmation_email_text), $headers, $attachments);
            }
        }

        private function check_page_by_slug($slug) {
            if ($pages = get_pages()) {
                foreach ($pages as $page) {
                    if ($slug === $page->post_name) {
                        return $page;
                    }
                }
            }
            return false;
        }

        public function page_create($name, $slug) {
            if (!$this->check_page_by_slug($slug)) {
                $create_page = array(
                    'post_type'     => 'page',
                    'post_name'     => $slug,
                    'post_title'    => $name,
                    'post_content'  => '',
                    'post_status'   => 'publish',
                );
                wp_insert_post($create_page);
            }
        }

        public function add_meta_box_func() {
            $cpt_label = $this->get_option('rbfw_rent_label', 'rbfw_basic_gen_settings', 'Rent');
            add_meta_box('rbfw_add_meta_box', __( $cpt_label . ' Settings : ', 'booking-and-rental-manager-for-woocommerce') . get_the_title(get_the_id()), array($this, 'mp_event_all_in_tab'), 'rbfw_item', 'normal', 'high');
        }

        public function mp_event_all_in_tab() {
            $cpt_label = $this->get_option('rbfw_rent_label', 'rbfw_basic_gen_settings', 'Rent');
            $post_id = get_the_id();
            ?>
            <div class="mp_event_tab_area">
                <aside class="mp_tab_menu">
                    <ul>
                        <?php do_action('rbfw_meta_box_tab_name', $post_id); ?>
                    </ul>
                </aside>
                <section class="mp_tab_details">
                    <?php do_action('rbfw_meta_box_tab_content', $post_id); ?>
                </section>
            </div>
            <?php
        }

        public function create_meta($options, $rbfw_id) {
            echo '<ul>';
            foreach ($options as $option) :
                $id = $option['id'];
                echo "<li><label for=".mep_esc_html($id).">";
                echo mep_esc_html($option['title']);
                $option_value = get_post_meta($rbfw_id, $option['id'], true);
                if (is_serialized($option_value)) {
                    $option_value = unserialize($option_value);
                }
                $option['value'] = $option_value;
                $this->field_generator($option, $rbfw_id);
                echo '</label></li>';
            endforeach;
            echo '</ul>';
        }

        public function field_generator($option, $rbfw_id) {



            $id = isset($option['id']) ? $option['id'] : "";
            $type = isset($option['type']) ? $option['type'] : "";
            $details = isset($option['details']) ? $option['details'] : "";

            $post_id = $rbfw_id;

            if (empty($id)) {
                return;
            }

            $prent_option_name = '';
            $FormFieldsGenerator = new RbfwFormFieldsGenerator();

            if (!empty($prent_option_name)) :
                $field_name = $prent_option_name . '[' . $id . ']';
                $option['field_name'] = $field_name;

                $prent_option_value = get_post_meta($post_id, $prent_option_name, true);

                $prent_option_value = is_serialized($prent_option_value) ? unserialize($prent_option_value) : array();
                $option['value'] = isset($prent_option_value[$id]) ? $prent_option_value[$id] : '';
            else :
                $option['field_name'] = $id;
                $option_value = get_post_meta($post_id, $id, true);
                $option['value'] = is_serialized($option_value) ? unserialize($option_value) : $option_value;

            endif;

            if (isset($option['type']) && $option['type'] === 'text') {
                echo mep_esc_html($FormFieldsGenerator->field_text($option));
            } elseif (isset($option['type']) && $option['type'] === 'text_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_text_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'textarea') {
                echo mep_esc_html($FormFieldsGenerator->field_textarea($option));
            } elseif (isset($option['type']) && $option['type'] === 'checkbox') {
                echo mep_esc_html($FormFieldsGenerator->field_checkbox($option));
            } elseif (isset($option['type']) && $option['type'] === 'checkbox_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_checkbox_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'radio') {
                echo mep_esc_html($FormFieldsGenerator->field_radio($option));
            } elseif (isset($option['type']) && $option['type'] === 'select') {
                echo mep_esc_html($FormFieldsGenerator->field_select($option));
            } elseif (isset($option['type']) && $option['type'] === 'range') {
                echo mep_esc_html($FormFieldsGenerator->field_range($option));
            } elseif (isset($option['type']) && $option['type'] === 'range_input') {
                echo mep_esc_html($FormFieldsGenerator->field_range_input($option));
            } elseif (isset($option['type']) && $option['type'] === 'switch') {
                echo mep_esc_html($FormFieldsGenerator->field_switch($option));
            } elseif (isset($option['type']) && $option['type'] === 'switch_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_switch_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'switch_img') {
                echo mep_esc_html($FormFieldsGenerator->field_switch_img($option));
            } elseif (isset($option['type']) && $option['type'] === 'time_format') {
                echo mep_esc_html($FormFieldsGenerator->field_time_format($option));
            } elseif (isset($option['type']) && $option['type'] === 'date_format') {
                echo mep_esc_html($FormFieldsGenerator->field_date_format($option));
            } elseif (isset($option['type']) && $option['type'] === 'datepicker') {
                echo mep_esc_html($FormFieldsGenerator->field_datepicker($option));
            } elseif (isset($option['type']) && $option['type'] === 'color_sets') {
                echo mep_esc_html($FormFieldsGenerator->field_color_sets($option));
            } elseif (isset($option['type']) && $option['type'] === 'colorpicker') {
                echo mep_esc_html($FormFieldsGenerator->field_colorpicker($option));
            } elseif (isset($option['type']) && $option['type'] === 'colorpicker_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_colorpicker_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'link_color') {
                echo mep_esc_html($FormFieldsGenerator->field_link_color($option));
            } elseif (isset($option['type']) && $option['type'] === 'icon') {
                echo mep_esc_html($FormFieldsGenerator->field_icon($option));
            } elseif (isset($option['type']) && $option['type'] === 'icon_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_icon_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'dimensions') {
                echo mep_esc_html($FormFieldsGenerator->field_dimensions($option));
            } elseif (isset($option['type']) && $option['type'] === 'wp_editor') {
                echo mep_esc_html($FormFieldsGenerator->field_wp_editor($option));
            } elseif (isset($option['type']) && $option['type'] === 'select2') {
                echo mep_esc_html($FormFieldsGenerator->field_select2($option));
            } elseif (isset($option['type']) && $option['type'] === 'faq') {
                echo mep_esc_html($FormFieldsGenerator->field_faq($option));
            } elseif (isset($option['type']) && $option['type'] === 'grid') {
                echo mep_esc_html($FormFieldsGenerator->field_grid($option));
            } elseif (isset($option['type']) && $option['type'] === 'color_palette') {
                echo mep_esc_html($FormFieldsGenerator->field_color_palette($option));
            } elseif (isset($option['type']) && $option['type'] === 'color_palette_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_color_palette_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'media') {
                echo mep_esc_html($FormFieldsGenerator->field_media($option));
            } elseif (isset($option['type']) && $option['type'] === 'media_multi') {
                echo mep_esc_html($FormFieldsGenerator->field_media_multi($option));
            } elseif (isset($option['type']) && $option['type'] === 'repeatable') {
                echo mep_esc_html($FormFieldsGenerator->field_repeatable($option));
            } elseif (isset($option['type']) && $option['type'] === 'user') {
                echo mep_esc_html($FormFieldsGenerator->field_user($option));
            } elseif (isset($option['type']) && $option['type'] === 'margin') {
                echo mep_esc_html($FormFieldsGenerator->field_margin($option));
            } elseif (isset($option['type']) && $option['type'] === 'padding') {
                echo mep_esc_html($FormFieldsGenerator->field_padding($option));
            } elseif (isset($option['type']) && $option['type'] === 'border') {
                echo mep_esc_html($FormFieldsGenerator->field_border($option));
            } elseif (isset($option['type']) && $option['type'] === 'switcher') {
                echo mep_esc_html($FormFieldsGenerator->field_switcher($option));
            } elseif (isset($option['type']) && $option['type'] === 'password') {
                echo mep_esc_html($FormFieldsGenerator->field_password($option));
            } elseif (isset($option['type']) && $option['type'] === 'post_objects') {
                echo mep_esc_html($FormFieldsGenerator->field_post_objects($option));
            } elseif (isset($option['type']) && $option['type'] === 'google_map') {
                echo mep_esc_html($FormFieldsGenerator->field_google_map($option));
            } elseif (isset($option['type']) && $option['type'] === $type) {
                do_action("wp_theme_settings_field_$type", $option);
            } elseif (isset($option['type']) && $option['type'] === 'time_slot') {
                echo mep_esc_html($FormFieldsGenerator->field_time_slot($option));
            }elseif (isset($option['type']) && $option['type'] === 'add_to_cart_shortcode') {
                echo mep_esc_html($FormFieldsGenerator->field_add_to_cart_shortcode($option));
            } else{
                echo '';
            }
            if (!empty($details)) {
                echo "<p class='description'>".mep_esc_html($details)."</p>";
            }
        }

        function get_datetime($date, $type) {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $wpdatesettings = $date_format . '  ' . $time_format;
            $timezone = wp_timezone_string();
            $timestamp = strtotime($date . ' ' . $timezone);

            if ($type == 'date') {
                return wp_date($date_format, $timestamp);
            }
            if ($type == 'date-time') {
                return wp_date($wpdatesettings, $timestamp);
            }
            if ($type == 'date-text') {

                return wp_date($date_format, $timestamp);
            }
            if ($type == 'date-time-text') {
                return wp_date($wpdatesettings, $timestamp, wp_timezone());
            }
            if ($type == 'time') {
                return wp_date($time_format, $timestamp, wp_timezone());
            }
            if ($type == 'day') {
                return wp_date('d', $timestamp);
            }
            if ($type == 'month') {
                return wp_date('M', $timestamp);
            }
        }

        function template_file_path($file_name) {
            $template_path = get_stylesheet_directory() . '/rbfw_templates/';
            $default_path = plugin_dir_path(__DIR__) . '../templates/';
            $thedir = is_dir($template_path) ? $template_path : $default_path;
            $themedir = $thedir . $file_name;
            $the_file_path = locate_template(array('rbfw_templates/' . $file_name)) ? $themedir : $default_path . $file_name;
            return $the_file_path;
        }

        function get_template($post_id) {
            $template_name = get_post_meta($post_id, 'rbfw_theme_file', true) ? get_post_meta($post_id, 'rbfw_theme_file', true) : 'default.php';
            return $file_path = $this->template_file_path('themes/' . $template_name);
        }

        function rbfw_add_order_data($meta_data = array(), $ticket_info = array(),$rbfw_service_price_data_actual) {

            global $rbfw;
            $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $title = $meta_data['rbfw_billing_name'];
            $cpt_name = 'rbfw_order';

            if($rbfw_payment_system == 'wps'){

                $rbfw_id = $meta_data['rbfw_id'];
                $wc_order_id = $meta_data['rbfw_order_id'];
                $ticket_info = $meta_data['rbfw_ticket_info'];
                $duration_cost = $meta_data['rbfw_duration_cost'];
                $service_cost = $meta_data['rbfw_service_cost'];
                $order_tax = !empty(get_post_meta($wc_order_id, '_order_tax', true)) ? get_post_meta($wc_order_id, '_order_tax', true) : 0;
                $is_tax_inclusive = get_option('woocommerce_prices_include_tax', true);

                $args = array(
                    'post_title' => $title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => $cpt_name
                );

                $meta_query = array(
                    'meta_query' => array(
                        'meta_value' => array(
                                'key' => 'rbfw_order_id',
                                'value' => $wc_order_id,
                                'compare' => '==',
                        )
                    )
                );

                $args = array_merge($args,$meta_query);

                $query = new WP_Query($args);

                /* If Order already created, update the order */

                if($query->have_posts()){

                    while ($query->have_posts()){
                        $query->the_post();
                        global $post;
                        $post_id = $post->ID;

                        $current_ticket_info = get_post_meta($post_id, 'rbfw_ticket_info', true);
                        $merged_ticket_info = array_merge($ticket_info, $current_ticket_info);

                        $current_duration_cost = get_post_meta($post_id, 'rbfw_duration_cost', true);
                        $merged_duration_cost = (float)$current_duration_cost + (float)$duration_cost;

                        $current_service_cost = get_post_meta($post_id, 'rbfw_service_cost', true);
                        $merged_service_cost = (float)$current_service_cost + (float)$service_cost;

                        //$total_price = $merged_duration_cost + $merged_service_cost + $order_tax;

                        if($is_tax_inclusive == 'yes'){
                            $total_price = $merged_duration_cost + $merged_service_cost;
                        } else{
                            $total_price = $merged_duration_cost + $merged_service_cost + $order_tax;
                        }

                        if (sizeof($meta_data) > 0) {
                            foreach ($meta_data as $key => $value) {
                                update_post_meta($post_id, $key, $value);
                            }
                            wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$wc_order_id.' '.$title));
                        }

                        update_post_meta($post_id, 'rbfw_ticket_info', $merged_ticket_info);
                        update_post_meta($post_id, 'rbfw_duration_cost', $merged_duration_cost);
                        update_post_meta($post_id, 'rbfw_service_cost', $merged_service_cost);
                        update_post_meta($post_id, 'rbfw_ticket_total_price', $total_price);
                        if(!empty($order_tax)){ update_post_meta($post_id, 'rbfw_order_tax', $order_tax); }
                    }

                }else{
                    /* If Order not exist, create the order */
                    $args = array(
                        'post_title' => $title,
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_type' => $cpt_name
                    );

                    $post_id = wp_insert_post($args);





                    if (sizeof($meta_data) > 0) {
                        foreach ($meta_data as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                        wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$wc_order_id.' '.$title));
                    }

                    $rbfw_pin = $meta_data['rbfw_user_id'] . $meta_data['rbfw_order_id'] . $post_id;
                    update_post_meta($post_id, 'rbfw_pin', $rbfw_pin);

                    update_post_meta($wc_order_id, '_rbfw_link_order_id', $post_id);

                    if(!empty($order_tax)){ update_post_meta($post_id, 'rbfw_order_tax', $order_tax); }
                    $total_price = $meta_data['rbfw_ticket_total_price'];
                    //$total_price = $total_price + $order_tax;

                    if($is_tax_inclusive == 'yes'){
                        $total_price = $total_price;
                    } else{
                        $total_price = $total_price + $order_tax;
                    }

                    update_post_meta($post_id, 'rbfw_ticket_total_price', $total_price);
                    update_post_meta($post_id, 'rbfw_link_order_id', $wc_order_id);

                }

                wp_reset_query();

            }


            return $post_id;
        }



        function rbfw_add_order_meta_data($meta_data = array(), $ticket_info = array()) {



            global $rbfw;
            $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');
            $title = $meta_data['rbfw_billing_name'];
            $cpt_name = 'rbfw_order_meta';

            if($rbfw_payment_system == 'wps'){

                $wc_order_id = $meta_data['rbfw_order_id'];
                $ticket_info = $meta_data['rbfw_ticket_info'];
                $order_tax = !empty(get_post_meta($wc_order_id, '_order_tax', true)) ? get_post_meta($wc_order_id, '_order_tax', true) : 0;
                $total_cost = get_post_meta($wc_order_id, '_order_total', true);
                $rbfw_link_order_id = get_post_meta($wc_order_id, '_rbfw_link_order_id', true);
                $rbfw_pin = get_post_meta($rbfw_link_order_id, 'rbfw_pin', true);

                /* If Order not exist, create the order */
                $args = array(
                    'post_title' => $title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => $cpt_name
                );

                $post_id = wp_insert_post($args);

                if (sizeof($meta_data) > 0) {
                    foreach ($meta_data as $key => $value) {
                        if($key != 'rbfw_ticket_info'){
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                    if(!empty($ticket_info)){
                        foreach ($ticket_info as $key =>$item) {
                            $rbfw_id = $item['rbfw_id'];
                            foreach ($item as $key => $value) {
                                if ($key == 'rbfw_start_date' || $key == 'rbfw_end_date') {
                                    $value = date('Y-m-d', strtotime($value));
                                }
                                if ($key == 'rbfw_start_datetime' || $key == 'rbfw_end_datetime') {
                                    $value = date('Y-m-d h:i A', strtotime($value));
                                }
                                update_post_meta($post_id, $key, $value);
                            }
                            rbfw_create_inventory_meta($item, $rbfw_id, $wc_order_id);
                        }
                    }
                    wp_update_post(array('ID' => $post_id, 'post_title' => '#'.$wc_order_id.' '.$title));
                }

                update_post_meta($post_id, 'rbfw_pin', $rbfw_pin);

                if(!empty($order_tax)){ update_post_meta($post_id, 'rbfw_order_tax', $order_tax); }

                update_post_meta($post_id, 'rbfw_ticket_total_price', $total_cost);
                update_post_meta($post_id, 'rbfw_link_order_id', $wc_order_id);
                /* End */

                rbfw_update_inventory( $wc_order_id, 'processing');
            }

            return $post_id;
        }

        function get_qyery_loop($post_type) {
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
            );
            $loop = new WP_Query($args);
            return $loop;
        }

        function merge_saved_array($arr1, $arr2) {
            $output = [];
            for ($i = 0; $i < count($arr1); $i++) {
                $output[$i] = array_merge($arr1[$i], $arr2[$i]);
            }
            return $output;
        }

        /**************************************
        * WooCommerce Functions Start from here
        ***************************************/

        function get_from_email_address() {
            return get_option('woocommerce_email_from_address');
        }

        function get_from_email_name() {
            return get_option('woocommerce_email_from_name');
        }

        function email_from_name() {
            return get_option('woocommerce_email_from_name');
        }

        function email_from_email() {
            return get_option('woocommerce_email_from_address');
        }

        public function get_order_meta($item_id, $key) {
            global $wpdb;
            $table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
            $results = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key));
            foreach ($results as $result) {
                $value = $result->meta_value;
            }
            $val = isset($value) ? $value : '';
            return $val;
        }

        public function rbfw_wc_status_update($order_id, $from_status, $to_status, $order) {
            $order = wc_get_order($order_id);
            $order_meta = get_post_meta($order_id);
            $order_status = $order->get_status();

            foreach ($order->get_items() as $item_id => $item_values) {
                $item_id = $item_id;
                $rbfw_id = $this->get_order_meta($item_id, '_rbfw_id');

                if (get_post_type($rbfw_id) == $this->get_cpt_name()) {
                    if ($order->has_status('processing')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('pending')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('on-hold')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('completed')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('cancelled')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('refunded')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                    if ($order->has_status('failed')) {
                        do_action('rbfw_wc_order_status_change', $order_status, $rbfw_id, $order_id);
                    }
                }
            }
        }

        function create_hidden_wc_product($post_id, $title) {
            $new_post = array(
                'post_title' => $title,
                'post_content' => '',
                'post_name' => uniqid(),
                'post_category' => array(),
                'tags_input' => array(),
                'post_status' => 'publish',
                'post_type' => 'product'
            );
            $pid = wp_insert_post($new_post);
            update_post_meta($post_id, 'link_wc_product', $pid);
            update_post_meta($pid, 'link_rbfw_id', $post_id);
            update_post_meta($pid, '_price', 0.01);
            update_post_meta($pid, '_sold_individually', 'yes');


            update_post_meta($pid, '_virtual', 'yes');
            $terms = array('exclude-from-catalog', 'exclude-from-search');
            wp_set_object_terms($pid, $terms, 'product_visibility');
            update_post_meta($post_id, 'check_if_run_once', true);
        }

        function create_hidden_wc_product_on_publish($post_id, $post, $update) {
            if ($post->post_type == $this->get_cpt_name() && $post->post_status == 'publish' && empty(get_post_meta($post_id, 'check_if_run_once'))) {
                $new_post = array(
                    'post_title' => $post->post_title,
                    'post_content' => '',
                    'post_name' => uniqid(),
                    'post_category' => array(),  // Usable for custom taxonomies too
                    'tags_input' => array(),
                    'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
                    'post_type' => 'product'  //'post',page' or use a custom post type if you want to
                );

                $pid = wp_insert_post($new_post);


                update_post_meta($post_id, 'link_wc_product', $pid);
                update_post_meta($pid, 'link_rbfw_id', $post_id);
                update_post_meta($pid, '_price', 0.01);
                update_post_meta($pid, '_sold_individually', 'yes');
                update_post_meta($pid, '_virtual', 'yes');

                $terms = array('exclude-from-catalog', 'exclude-from-search');
                wp_set_object_terms($pid, $terms, 'product_visibility');
                update_post_meta($post_id, 'check_if_run_once', true);
            }
        }

        function count_hidden_wc_product($post_id) {
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'link_rbfw_id',
                        'value' => $post_id,
                        'compare' => '='
                    )
                )
            );
            $loop = new WP_Query($args);
            return $loop->post_count;
        }

        function run_link_product_on_save($post_id) {

            if (get_post_type($post_id) == $this->get_cpt_name()) {

                if (!isset($_POST['rbfw_ticket_type_nonce']) || !wp_verify_nonce($_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce')) {
                    return;
                }

                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }

                if (!current_user_can('edit_post', $post_id)) {
                    return;
                }
                $event_name = get_the_title($post_id);

                $hidden_product_id = get_post_meta($post_id, 'link_wc_product', true);

                if($hidden_product_id){
                    if($this->count_hidden_wc_product($post_id) == 0){
                        $this->create_hidden_wc_product($post_id, $event_name);
                    }
                }else{
                    $this->create_hidden_wc_product($post_id, $event_name);
                }

                /*if ($this->count_hidden_wc_product($post_id) == 0 || empty(get_post_meta($post_id, 'link_wc_product', true))) {
                    $this->create_hidden_wc_product($post_id, $event_name);
                }*/

                $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;

                $shipping_enable = get_post_meta($post_id, 'shipping_enable', true) ? get_post_meta($post_id, 'shipping_enable', true) : 'off';

                set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
                wp_publish_post($product_id);

                if($shipping_enable=='on'){
                    $product_type = 'no';
                }else{
                    $product_type = 'yes';
                }



                $_tax_status = isset($_POST['_tax_status']) ? rbfw_array_strip($_POST['_tax_status']) : 'none';
                $_tax_class = isset($_POST['_tax_class']) ? rbfw_array_strip($_POST['_tax_class']) : '';

                $update__tax_status = update_post_meta($product_id, '_tax_status', $_tax_status);
                $update__tax_class = update_post_meta($product_id, '_tax_class', $_tax_class);
                $update__tax_class = update_post_meta($product_id, '_stock_status', 'instock');
                $update__tax_class = update_post_meta($product_id, '_manage_stock', 'no');
                $update__tax_class = update_post_meta($product_id, '_virtual', $product_type);
                $update__tax_class = update_post_meta($product_id, '_sold_individually', 'yes');
                $my_post = array(
                    'ID' => $product_id,
                    'post_title' => $event_name,
                    'post_name' => uniqid()
                );
                remove_action('save_post', 'run_link_product_on_save');
                wp_update_post($my_post);
                add_action('save_post', 'run_link_product_on_save');
            }
        }

        function hide_wc_hidden_product_from_product_list($query) {
            global $pagenow;
            $taxonomy = 'product_visibility';
            $q_vars = &$query->query_vars;
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
                $tax_query = array([
                    'taxonomy' => 'product_visibility',
                    'field' => 'slug',
                    'terms' => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ]);
                $query->set('tax_query', $tax_query);
            }
        }

        function hide_hidden_wc_product_from_frontend() {
            global $post, $wp_query;
            if (class_exists( 'WooCommerce' ) && is_product()) {

                $post_id = $post->ID;
                $visibility = get_the_terms($post_id, 'product_visibility');                
                if (is_object($visibility) || is_array($visibility)) {                    
                    if ($visibility[0]->name == 'exclude-from-catalog') {
                        $check_event_hidden = get_post_meta($post_id, 'link_rbfw_id', true) ? get_post_meta($post_id, 'link_rbfw_id', true) : 0;
                        if ($check_event_hidden > 0) {
                            $wp_query->set_404();
                            status_header(404);
                            get_template_part(404);
                            exit();
                        }
                    }
                }
            }
        }

        function get_wc_raw_price($post_id, $price, $args = array()) {

            $args = wp_parse_args(
                $args,
                array(
                    'qty' => '',
                    'price' => '',
                )
            );

            $_product = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
            // $price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
            $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

            $product = wc_get_product($_product);


            $tax_with_price = get_option('woocommerce_tax_display_shop');


            if ('' === $price) {
                return '';
            } elseif (empty($qty)) {
                return 0.0;
            }

            $line_price = $price * $qty;
            $return_price = $line_price;

            if ($product->is_taxable()) {
                if (!wc_prices_include_tax()) {
                    $tax_rates = WC_Tax::get_rates($product->get_tax_class());
                    $taxes = WC_Tax::calc_tax($line_price, $tax_rates, false);
                    if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                        $taxes_total = array_sum($taxes);

                    } else {

                        $taxes_total = array_sum(array_map('wc_round_tax_total', $taxes));
                    }

                    $return_price = $tax_with_price == 'excl' ? round($line_price, wc_get_price_decimals()) : round($line_price + $taxes_total, wc_get_price_decimals());


                } else {


                    $tax_rates = WC_Tax::get_rates($product->get_tax_class());
                    $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

                    /**
                     * If the customer is excempt from VAT, remove the taxes here.
                     * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
                     */
                    if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
                        $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);

                        if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                            $remove_taxes_total = array_sum($remove_taxes);
                        } else {
                            $remove_taxes_total = array_sum(array_map('wc_round_tax_total', $remove_taxes));
                        }

                        // $return_price = round( $line_price, wc_get_price_decimals() );
                        $return_price = round($line_price - $remove_taxes_total, wc_get_price_decimals());
                        /**
                         * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                         * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                         * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                         */
                    } else {
                        $base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
                        $modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates, false);

                        if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                            $base_taxes_total = array_sum($base_taxes);
                            $modded_taxes_total = array_sum($modded_taxes);
                        } else {
                            $base_taxes_total = array_sum(array_map('wc_round_tax_total', $base_taxes));
                            $modded_taxes_total = array_sum(array_map('wc_round_tax_total', $modded_taxes));
                        }

                        $return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, wc_get_price_decimals()) : round($line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals());
                    }
                }
            }
            return apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
        }

        function all_tax_list() {
            global $wpdb;

            if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                return;
            }

            $table_name = $wpdb->prefix . 'wc_tax_rate_classes';
            $result = $wpdb->get_results("SELECT * FROM $table_name");
            $tax_list = [];

            if(!empty($result)){
                foreach ($result as $tax) {
                    $tax_list[$tax->slug] = $tax->name;
                }
            }

            return $tax_list;
        }

        /**************************************
        * End WooCommerce Functions here
        ***************************************/
    }
}

$rbfw = new MageRBFWClass();
