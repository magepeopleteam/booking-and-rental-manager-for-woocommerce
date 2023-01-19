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

            add_submenu_page('edit.php?post_type=rbfw_item', __('Status', 'booking-and-rental-manager-for-woocommerce'), '<span style="color:#13df13">'.__('Status', 'booking-and-rental-manager-for-woocommerce').'</span>', 'manage_options', 'rbfw-status', array($this, 'rbfw_status_page')); 
        }

        public function rbfw_wc_btn(){
            $button_wc = '';

            /* WooCommerce */
            if($this->rbfw_chk_plugin_folder_exist('woocommerce') == false) {
                $button_wc = '<a href="'.esc_url($this->rbfw_wp_plugin_installation_url('woocommerce')).'" class="rbfw_plugin_btn">'.esc_html__('Install','booking-and-rental-manager-for-woocommerce').'</a>';
            }
            elseif($this->rbfw_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active( 'woocommerce/woocommerce.php')){
                $button_wc = '<a href="'.esc_url($this->rbfw_wp_plugin_activation_url('woocommerce/woocommerce.php')).'" class="rbfw_plugin_btn">'.esc_html__('Activate','booking-and-rental-manager-for-woocommerce').'</a>';
            }
            else{
                $button_wc = '<span class="rbfw_plugin_status">'.esc_html__('Activated','booking-and-rental-manager-for-woocommerce').'</span>';
            }
            
            return $button_wc;
        }

        public function rbfw_status_page(){
            $button_wc = '';

            /* WooCommerce */
            if($this->rbfw_chk_plugin_folder_exist('woocommerce') == false) {
                $button_wc = '<a href="'.esc_url($this->rbfw_wp_plugin_installation_url('woocommerce')).'" class="rbfw_plugin_btn">'.esc_html__('Install','booking-and-rental-manager-for-woocommerce').'</a>';
            }
            elseif($this->rbfw_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active( 'woocommerce/woocommerce.php')){
                $button_wc = '<a href="'.esc_url($this->rbfw_wp_plugin_activation_url('woocommerce/woocommerce.php')).'" class="rbfw_plugin_btn">'.esc_html__('Activate','booking-and-rental-manager-for-woocommerce').'</a>';
            }
            else{
                $button_wc = '<span class="rbfw_plugin_status">'.esc_html__('Activated','booking-and-rental-manager-for-woocommerce').'</span>';
            }
            ?>
            <div class="rbfw-status-page-wrapper wrap">
                <h3><?php esc_html_e( 'Booking and Rental Manager Required Plugin', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Plugin Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                            <td><?php echo $button_wc; ?></td>
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
                    padding: 8px;
                    transition: 0.2s;
                    border-radius: 5px;
                    display: inline-block;
                }
                .rbfw-status-page-wrapper .rbfw_plugin_btn:hover{
                    background-color: #ff982d;
                    border-color: #ff982d;
                    color: #fff;
                    transition: 0.2s;
                }
                .rbfw_plugin_status{
                    color: #13df13;
                }
            </style>
            <?php
            
        }

        public function rbfw_plugin_page_location(){

            $location = 'admin.php';
    
            return $location;	
        }

    
        public function rbfw_chk_plugin_folder_exist($slug){
            $plugin_dir = ABSPATH . 'wp-content/plugins/'.$slug;
            if(is_dir($plugin_dir)){
                return true;
            }
            else{
                return false;
            }		
        }
    
        public function rbfw_plugin_activate(){
            if(isset($_GET['rbfw_plugin_activate']) && !is_plugin_active( $_GET['rbfw_plugin_activate'] )){
                $slug = $_GET['rbfw_plugin_activate'];
                $activate = activate_plugin( $slug );
                $url = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_import' );
                echo '<script>
                var url = "'.$url.'";
                window.location.replace(url);
                </script>';
            }
            else{
                return false;
            }
        }

        public function rbfw_plugin_install(){
    
            if(isset($_GET['rbfw_plugin_install']) && $this->rbfw_chk_plugin_folder_exist($_GET['rbfw_plugin_install']) == false){
                $slug = $_GET['rbfw_plugin_install'];
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
                        echo '<script>
                            str = "'.$url.'";
                            var url = str.replace(/&amp;/g, "&");
                            window.location.replace(url);
                            </script>';
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