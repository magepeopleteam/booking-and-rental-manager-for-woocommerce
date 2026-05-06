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
        }

        public function rbfw_status_submenu(){

            add_submenu_page('edit.php?post_type=rbfw_item', esc_html__('Status', 'booking-and-rental-manager-for-woocommerce'), '<span style="color:#13df13">'.esc_html__('Status', 'booking-and-rental-manager-for-woocommerce').'</span>', 'manage_options', 'rbfw-status', array($this, 'rbfw_status_page'));
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

            return '<a href="#" class="rbfw_plugin_btn rbfw-trigger-pdf-popup" onclick="return false;">' . $label . '</a>';
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
            ?>
            <div class="rbfw-status-page-wrapper wrap">
                <h3><?php esc_html_e( 'Booking and Rental Manager Required Plugin', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Plugin Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php esc_html_e( 'Required for booking, payments and order management.', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                            <td><?php echo wp_kses($button_wc, rbfw_allowed_html()); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'MagePeople PDF Support', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php esc_html_e( 'Required for PDF booking receipts and email attachments.', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                            <td><?php echo wp_kses($button_pdf, array(
                                'a' => array( 'href' => array(), 'class' => array(), 'onclick' => array() ),
                                'span' => array( 'class' => array() ),
                            )); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <style>
                .rbfw-status-page-wrapper table{
                    width: 100%;
                    border-collapse:collapse;
                }
                .rbfw-status-page-wrapper table thead th {
                    background: #c18d5f;
                    color: #fff;
                    text-align: left;
                    padding: 10px;
                    font-weight: 500;
                }
                .rbfw-status-page-wrapper table tbody td{
                    padding:10px;
                }
                .rbfw-status-page-wrapper table tbody tr:nth-child(odd){
                    background: #fff;
                }
                .rbfw-status-page-wrapper table tbody tr:nth-child(even){
                    background: #cdd1e3;
                }
                .rbfw-status-page-wrapper .rbfw_plugin_btn{
                    background-color: #ff982d;
                    border-color: #ff982d;
                    color: #fff;
                    text-decoration: none;
                    padding: 8px 16px;
                    transition: 0.2s;
                    border-radius: 5px;
                    display: inline-block;
                    cursor: pointer;
                    font-weight: 500;
                }
                .rbfw-status-page-wrapper .rbfw_plugin_btn:hover{
                    background-color: #e68a25;
                    color: #fff;
                    transition: 0.2s;
                }
                .rbfw_plugin_status{
                    color: #13df13;
                    font-weight: 600;
                }
                .rbfw_plugin_na{
                    color: #999;
                    font-style: italic;
                }
            </style>
            <script>
            jQuery(document).ready(function($) {
                // When user clicks the Activate / Install & Activate button on the status page
                $(document).on('click', '.rbfw-trigger-pdf-popup', function(e) {
                    e.preventDefault();

                    // Clear the dismissal so the popup shows again
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'rbfw_pro_clear_pdf_dismiss',
                            nonce: '<?php echo esc_js( wp_create_nonce( "rbfw_pro_clear_pdf_dismiss" ) ); ?>'
                        },
                        complete: function() {
                            // Reload the page — the popup will appear after reload
                            window.location.reload();
                        }
                    });
                });
            });
            </script>
            <?php

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
