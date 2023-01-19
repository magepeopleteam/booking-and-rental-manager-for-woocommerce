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

if (!class_exists('Rbfw_Wc_Notice')) {
    class Rbfw_Wc_Notice{
        public function __construct(){
            if(rbfw_chk_plugin_folder_exist('woocommerce') == false || !is_plugin_active( 'woocommerce/woocommerce.php')){
                add_action('admin_notices', array($this, 'rbfw_admin_notices'));
            }
        }

        public function rbfw_admin_notices(){
            $status = new RBFW_Status();
            $wc_btn = $status->rbfw_wc_btn();
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php _e('Please install or activate the WooCommerce plugin. ', 'booking-and-rental-manager-for-woocommerce'); ?></strong><?php echo $wc_btn; ?></p>
            </div>
            <?php
        }
    }
    new Rbfw_Wc_Notice();
}