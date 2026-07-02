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

if (!class_exists('RBFW_Status')) {

	class RBFW_Status{
        public function __construct(){
            add_action( 'admin_init', array( $this, 'rbfw_plugin_install' ) );
            add_action( 'admin_init', array( $this, 'rbfw_plugin_activate' ) );
            add_action( 'rbfw_admin_menu_after_settings', array( $this, 'rbfw_status_submenu' ) );

            /* Status now also lives as a tab inside Global Settings, so hide the
             * separate CPT submenu entry ( priority 999, after it's registered
             * above, matching the same pattern used to hide the Pro "Reports"
             * submenu in class-admin-menu.php ). The page itself stays
             * registered — the install/activate/PDF-popup action URLs below
             * redirect back to it and must keep working either way. */
            add_action( 'admin_menu', array( $this, 'rbfw_hide_status_submenu' ), 999 );
            add_filter( 'rbfw_settings_sec_reg', array( $this, 'rbfw_register_status_settings_section' ), 110 );
            add_action( 'wsa_form_top_rbfw_status_page_settings', array( $this, 'rbfw_status_page' ) );
        }

        public function rbfw_status_submenu(){

            add_submenu_page('edit.php?post_type=rbfw_item', esc_html__('Status', 'booking-and-rental-manager-for-woocommerce'), '<span style="color:#13df13">'.esc_html__('Status', 'booking-and-rental-manager-for-woocommerce').'</span>', 'manage_options', 'rbfw-status', array($this, 'rbfw_status_page'));
        }

        public function rbfw_hide_status_submenu() {
            remove_submenu_page( 'edit.php?post_type=rbfw_item', 'rbfw-status' );
        }

        /**
         * Register the "Status" tab on the Global Settings page.
         *
         * No fields are added for this section id ( rbfw_settings_sec_fields
         * is intentionally left untouched ), so the Settings API renders no
         * Save button for it — the tab is a live action page ( install /
         * activate / PDF-popup links ), not a saved options form.
         *
         * @param array $sections Registered settings sections.
         * @return array
         */
        public function rbfw_register_status_settings_section( $sections ) {
            if ( ! is_array( $sections ) ) {
                return $sections;
            }
            $sections[] = array(
                'id'    => 'rbfw_status_page_settings',
                'title' => '<i class="fas fa-heart-pulse"></i>' . esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ),
            );
            return $sections;
        }

        public function rbfw_wc_btn() {
            $button_wc = '';
        
            /* WooCommerce */
            if ($this->rbfw_free_chk_plugin_folder_exist('woocommerce') == false) {
                $button_wc = '<a href="' . esc_url($this->rbfw_wp_plugin_installation_url('woocommerce')) . '" class="' . esc_attr('rbfw_plugin_btn') . '">' . esc_html__('Install', 'booking-and-rental-manager-for-woocommerce') . '</a>';
            }
            elseif ($this->rbfw_free_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active('woocommerce/woocommerce.php')) {
                $button_wc = '<a href="' . esc_url($this->rbfw_wp_plugin_activation_url('woocommerce/woocommerce.php')) . '" class="' . esc_attr('rbfw_plugin_btn') . '">' . esc_html__('Activate', 'booking-and-rental-manager-for-woocommerce') . '</a>';
            }
            else {
                $button_wc = '<span class="rbfw_plugin_status">' . esc_html__('Activated', 'booking-and-rental-manager-for-woocommerce') . '</span>';
            }
        
            return $button_wc;
        }

        /**
         * PDF Support status row — shows on status page.
         * Only visible when the Pro plugin is active and PDF feature is enabled.
         */
        public function rbfw_pdf_btn() {
            // Only show if Pro plugin is active and PDF feature is enabled
            if ( ! function_exists( 'rbfw_check_pro_active' ) || ! rbfw_check_pro_active() ) {
                return '<span class="rbfw_plugin_na">' . esc_html__( 'Pro plugin not active', 'booking-and-rental-manager-for-woocommerce' ) . '</span>';
            }

            // Check if PDF feature is enabled in Pro settings
            global $rbfw;
            $send_pdf = 'no';
            if ( $rbfw && method_exists( $rbfw, 'get_option' ) ) {
                $send_pdf = $rbfw->get_option( 'rbfw_send_pdf', 'rbfw_basic_pdf_settings', 'no' );
            }

            if ( $send_pdf !== 'yes' ) {
                return '<span class="rbfw_plugin_na">' . esc_html__( 'PDF feature is disabled in settings', 'booking-and-rental-manager-for-woocommerce' ) . '</span>';
            }

            $pdf_active    = is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php' );
            $pdf_installed = $this->rbfw_free_chk_plugin_folder_exist( 'magepeople-pdf-support-master' );

            if ( $pdf_active ) {
                return '<span class="rbfw_plugin_status">' . esc_html__( 'Activated', 'booking-and-rental-manager-for-woocommerce' ) . '</span>';
            }

            // Not active — show button that triggers the popup
            $label = $pdf_installed
                ? esc_html__( 'Activate', 'booking-and-rental-manager-for-woocommerce' )
                : esc_html__( 'Install & Activate', 'booking-and-rental-manager-for-woocommerce' );

            $show_popup_url = wp_nonce_url(
                admin_url( 'edit.php?post_type=rbfw_item&page=rbfw-status&rbfw_show_pdf_popup=1' ),
                'rbfw_show_pdf_popup'
            );

            return '<a href="' . esc_url( $show_popup_url ) . '" class="rbfw_plugin_btn">' . $label . '</a>';
        }

        public function rbfw_status_page(){
            $button_wc = '';

            /* WooCommerce */
            if ($this->rbfw_free_chk_plugin_folder_exist('woocommerce') == false) {
                $button_wc = '<a href="' . esc_url($this->rbfw_wp_plugin_installation_url('woocommerce')) . '" class="' . esc_attr('rbfw_plugin_btn') . '">' . esc_html__('Install', 'booking-and-rental-manager-for-woocommerce') . '</a>';
            }
            elseif ($this->rbfw_free_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active('woocommerce/woocommerce.php')) {
                $button_wc = '<a href="' . esc_url($this->rbfw_wp_plugin_activation_url('woocommerce/woocommerce.php')) . '" class="' . esc_attr('rbfw_plugin_btn') . '">' . esc_html__('Activate', 'booking-and-rental-manager-for-woocommerce') . '</a>';
            }
            else {
                $button_wc = '<span class="rbfw_plugin_status">' . esc_html__('Activated', 'booking-and-rental-manager-for-woocommerce') . '</span>';
            }

            // PDF Support
            $button_pdf = $this->rbfw_pdf_btn();

            $server_rows = $this->rbfw_server_environment_rows();
            ?>
            <div class="rbfw-status-page-wrapper rbfw_status_modern">

                <div class="rbfw_status_section">
                    <div class="rbfw_status_section_head">
                        <span class="rbfw_status_section_icon"><?php echo rbfw_inv_icon('box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                        <div>
                            <h3><?php esc_html_e( 'Required Plugins', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                            <p><?php esc_html_e( 'Third-party plugins this plugin depends on for full functionality.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                    </div>
                    <div class="rbfw_status_card_grid">
                        <div class="rbfw_status_card">
                            <div class="rbfw_status_card_icon"><?php echo rbfw_inv_icon('box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_status_card_body">
                                <div class="rbfw_status_card_title"><?php esc_html_e( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_status_card_desc"><?php esc_html_e( 'Required for booking, payments and order management.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                            </div>
                            <div class="rbfw_status_card_action"><?php echo wp_kses($button_wc, rbfw_allowed_html()); ?></div>
                        </div>
                        <div class="rbfw_status_card">
                            <div class="rbfw_status_card_icon"><?php echo rbfw_inv_icon('file_pdf'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></div>
                            <div class="rbfw_status_card_body">
                                <div class="rbfw_status_card_title"><?php esc_html_e( 'MagePeople PDF Support', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                <div class="rbfw_status_card_desc"><?php esc_html_e( 'Required for PDF booking receipts and email attachments.', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                            </div>
                            <div class="rbfw_status_card_action"><?php echo wp_kses($button_pdf, array(
                                'a' => array( 'href' => array(), 'class' => array(), 'onclick' => array() ),
                                'span' => array( 'class' => array() ),
                            )); ?></div>
                        </div>
                    </div>
                </div>

                <div class="rbfw_status_section">
                    <div class="rbfw_status_section_head">
                        <span class="rbfw_status_section_icon"><?php echo rbfw_inv_icon('clipboard'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
                        <div>
                            <h3><?php esc_html_e( 'Server Environment', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                            <p><?php esc_html_e( 'Useful when reporting an issue to support — copy these details along with your report.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                    </div>
                    <div class="rbfw_status_info_grid">
                        <?php foreach ( $server_rows as $row ) : ?>
                            <div class="rbfw_status_info_item">
                                <span class="rbfw_status_info_label"><?php echo esc_html( $row['label'] ); ?></span>
                                <span class="rbfw_status_info_value <?php echo esc_attr( $row['state'] ); ?>"><?php echo esc_html( $row['value'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <style>
                /* The Settings API auto-prints an <h2>{section title}</h2> for every
                   registered section, even when it has no fields — that's the plugin
                   icon + "Status" text that showed up duplicated under this tab. */
                #rbfw_status_page_settings > form > h2 { display: none; }

                .rbfw_status_modern,
                .rbfw_status_modern * {
                    box-sizing: border-box;
                }
                .rbfw_status_modern {
                    --rbfw-st-bg:        #F4F6FA;
                    --rbfw-st-surface:   #FFFFFF;
                    --rbfw-st-border:    #E4E9F2;
                    --rbfw-st-text-1:    #1A1F36;
                    --rbfw-st-text-2:    #4A5568;
                    --rbfw-st-text-3:    #8896B0;
                    --rbfw-st-accent:    #4F6EF7;
                    --rbfw-st-accent-lt: #EEF1FE;
                    --rbfw-st-green:     #10B981;
                    --rbfw-st-green-lt:  #D1FAE5;
                    --rbfw-st-amber:     #B45309;
                    --rbfw-st-amber-lt:  #FEF3C7;
                    --rbfw-st-radius:    12px;
                    --rbfw-st-shadow:    0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
                    color: var(--rbfw-st-text-1);
                    font-size: 14px;
                }
                .rbfw_status_modern svg { width: 1em; height: 1em; stroke: currentColor; fill: none; }

                .rbfw_status_section { margin-bottom: 26px; }
                .rbfw_status_section_head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
                .rbfw_status_section_icon {
                    width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
                    background: var(--rbfw-st-accent-lt); color: var(--rbfw-st-accent);
                    display: grid; place-items: center; font-size: 17px;
                }
                .rbfw_status_section_head h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: var(--rbfw-st-text-1); }
                .rbfw_status_section_head p { margin: 0; font-size: 12px; color: var(--rbfw-st-text-3); }

                .rbfw_status_card_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; }
                .rbfw_status_card {
                    display: flex; align-items: center; gap: 14px;
                    background: var(--rbfw-st-surface); border: 1px solid var(--rbfw-st-border);
                    border-radius: var(--rbfw-st-radius); box-shadow: var(--rbfw-st-shadow);
                    padding: 16px;
                }
                .rbfw_status_card_icon {
                    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
                    background: var(--rbfw-st-bg); color: var(--rbfw-st-text-2);
                    display: grid; place-items: center; font-size: 18px;
                }
                .rbfw_status_card_body { flex: 1; min-width: 0; }
                .rbfw_status_card_title { font-weight: 700; font-size: 13px; color: var(--rbfw-st-text-1); margin-bottom: 2px; }
                .rbfw_status_card_desc { font-size: 12px; color: var(--rbfw-st-text-3); line-height: 1.4; }
                .rbfw_status_card_action { flex-shrink: 0; }

                .rbfw_status_modern .rbfw_plugin_btn {
                    display: inline-flex; align-items: center; height: 32px; padding: 0 14px;
                    background: var(--rbfw-st-accent); color: #fff; border-radius: 7px;
                    font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer;
                    transition: opacity .15s;
                }
                .rbfw_status_modern .rbfw_plugin_btn:hover { opacity: .88; color: #fff; }
                .rbfw_status_modern .rbfw_plugin_status {
                    display: inline-flex; align-items: center; height: 26px; padding: 0 12px;
                    background: var(--rbfw-st-green-lt); color: #065F46;
                    border-radius: 20px; font-size: 11px; font-weight: 700;
                }
                .rbfw_status_modern .rbfw_plugin_na { font-size: 12px; color: var(--rbfw-st-text-3); font-style: italic; }

                .rbfw_status_info_grid {
                    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                    background: var(--rbfw-st-surface); border: 1px solid var(--rbfw-st-border);
                    border-radius: var(--rbfw-st-radius); box-shadow: var(--rbfw-st-shadow);
                    overflow: hidden;
                }
                .rbfw_status_info_item {
                    display: flex; align-items: center; justify-content: space-between; gap: 10px;
                    padding: 12px 16px; border-bottom: 1px solid var(--rbfw-st-border); border-right: 1px solid var(--rbfw-st-border);
                }
                .rbfw_status_info_label { font-size: 12px; color: var(--rbfw-st-text-3); font-weight: 600; }
                .rbfw_status_info_value { font-size: 12px; font-weight: 700; color: var(--rbfw-st-text-1); text-align: right; word-break: break-word; }
                .rbfw_status_info_value.warn { color: var(--rbfw-st-amber); }
                .rbfw_status_info_value.good { color: var(--rbfw-st-green); }

                @media (max-width: 782px) {
                    .rbfw_status_card { flex-wrap: wrap; }
                }
            </style>
            <?php

        }

        /**
         * Server / environment info rows for the Status tab.
         *
         * Read-only diagnostics ( PHP, WordPress, database, server, theme,
         * WooCommerce and this plugin's own version ) useful when reporting an
         * issue to support. A couple of well-known thresholds are flagged so a
         * misconfigured host stands out at a glance.
         *
         * @return array[] Each row: [ 'label' => string, 'value' => string, 'state' => ''|'good'|'warn' ].
         */
        public function rbfw_server_environment_rows() {
            global $wpdb;

            $php_version = phpversion();
            $memory_limit_raw = ini_get( 'memory_limit' );
            $memory_limit_bytes = wp_convert_hr_to_bytes( $memory_limit_raw );

            $rows = array();

            $rows[] = array(
                'label' => esc_html__( 'Plugin Version', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => class_exists( 'RBFW_Rent_Manager' ) ? RBFW_Rent_Manager::get_plugin_data( 'Version' ) : '',
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'PHP Version', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => $php_version,
                'state' => version_compare( $php_version, '7.4', '<' ) ? 'warn' : 'good',
            );
            $rows[] = array(
                'label' => esc_html__( 'PHP Memory Limit', 'booking-and-rental-manager-for-woocommerce' ),
                /* -1 ( or 0 ) means "unlimited" — show that instead of the raw ini value. */
                'value' => $memory_limit_bytes > 0 ? $memory_limit_raw : esc_html__( 'Unlimited', 'booking-and-rental-manager-for-woocommerce' ),
                'state' => ( $memory_limit_bytes > 0 && $memory_limit_bytes < 128 * MB_IN_BYTES ) ? 'warn' : 'good',
            );
            $rows[] = array(
                'label' => esc_html__( 'Max Upload Size', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => size_format( wp_max_upload_size() ),
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'Max Execution Time', 'booking-and-rental-manager-for-woocommerce' ),
                /* translators: %s: seconds. */
                'value' => sprintf( esc_html__( '%s seconds', 'booking-and-rental-manager-for-woocommerce' ), ini_get( 'max_execution_time' ) ),
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'WordPress Version', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => get_bloginfo( 'version' ),
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'Database Version', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => $wpdb->db_version(),
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'Server Software', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'Active Theme', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ),
                'state' => '',
            );
            $rows[] = array(
                'label' => esc_html__( 'WooCommerce Version', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => defined( 'WC_VERSION' ) ? WC_VERSION : esc_html__( 'Not active', 'booking-and-rental-manager-for-woocommerce' ),
                'state' => defined( 'WC_VERSION' ) ? 'good' : 'warn',
            );
            $rows[] = array(
                'label' => esc_html__( 'Multisite', 'booking-and-rental-manager-for-woocommerce' ),
                'value' => is_multisite() ? esc_html__( 'Yes', 'booking-and-rental-manager-for-woocommerce' ) : esc_html__( 'No', 'booking-and-rental-manager-for-woocommerce' ),
                'state' => '',
            );

            return $rows;
        }

        public function rbfw_plugin_page_location(){

            $location = 'admin.php';

            return $location;
        }


        public function rbfw_free_chk_plugin_folder_exist($slug){
            $plugin_dir = ABSPATH . 'wp-content/plugins/'.$slug;
            if(is_dir($plugin_dir)){
                return true;
            }
            else{
                return false;
            }
        }

        public function rbfw_plugin_activate(){
            if (!current_user_can('activate_plugins')) {
                return;
            }
            if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                return;
            }
	        
	        if ( isset( $_GET['rbfw_plugin_activate'] ) && !is_plugin_active( sanitize_text_field( wp_unslash( $_GET['rbfw_plugin_activate'] ) ) ) ) {
		        $slug = sanitize_text_field( wp_unslash( $_GET['rbfw_plugin_activate'] ) );
		        $activate = activate_plugin( $slug );
                $url = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw-status' );
                echo wp_kses_post('<script>
                    var url = "' . esc_url( $url ) . '";
                    window.location.replace(url);
                </script>');

            }
            else{
                return false;
            }
        }

        public function rbfw_plugin_install(){

            if (!current_user_can('install_plugins')) {
                return;
            }
            if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                return;
            }
	        
	        if (isset($_GET['rbfw_plugin_install']) && $this->rbfw_free_chk_plugin_folder_exist(sanitize_text_field(wp_unslash($_GET['rbfw_plugin_install']))) == false) {
		        $slug = sanitize_text_field(wp_unslash($_GET['rbfw_plugin_install']));
		        if($slug == 'woocommerce'){
                    $action = 'install-plugin';
                    $url = wp_nonce_url(
                        add_query_arg(
                            array(
                                'action' => $action,
                                'plugin' => $slug
                            ),
                            admin_url( 'update.php' )
                        ),
                        $action.'_'.$slug
                    );
                    if(isset($url)){
                        echo wp_kses_post('<script>
                            var str = "' . esc_js( esc_url( $url ) ) . '";
                            var url = str.replace(/&amp;/g, "&");
                            window.location.replace(url);
                        </script>');

                    }


                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        }

        public function rbfw_wp_plugin_activation_url($slug){

            $url = admin_url($this->rbfw_plugin_page_location()).'?page=rbfw-status&rbfw_plugin_activate='.$slug;


            return $url;
        }

        public function rbfw_wp_plugin_installation_url($slug){

            if($slug){
    
                $url = admin_url($this->rbfw_plugin_page_location()).'?page=rbfw-status&rbfw_plugin_install='.$slug;
            }
            else{

                $url = '';
            }

            return $url;
        }

    }
    new RBFW_Status();
}
