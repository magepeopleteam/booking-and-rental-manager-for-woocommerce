<?php
function rbfw_woo_install_check() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        return 'Yes';
    } elseif ( is_dir( $plugin_dir ) ) {
        return 'Installed But Not Active';
    } else {
        return 'No';
    }
}