<?php
/*
* Author 	:	MagePeople Team
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( ! class_exists( 'RBFW_WC_Meta' ) ) {
    class RBFW_WC_Meta {

        public function __construct(){
            add_action('rbfw_tax_meta_boxs', array($this, 'rbfw_wc_meta_box'));
        }

        public function rbfw_wc_meta_box(){
            $MageRBFWClass = new MageRBFWClass();
            $rbfw_rent_label = get_option("rbfw_basic_gen_settings");
            $cpt_label = !empty($rbfw_rent_label['rbfw_rent_label']) ? $rbfw_rent_label['rbfw_rent_label'] : 'Rent';

            if(get_option( 'woocommerce_calc_taxes' ) == 'yes'){
                $the_array = array(
                            
                    array(
                        'id'      => '_tax_status',
                        'title'   => esc_html__( ' Tax Status', 'booking-and-rental-manager-for-woocommerce' ),
                        'details' => esc_html__( 'Please Select Tax Status', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'select',
                        'class'   => 'omg',
                        'default' => 'taxable',
                        'args'    => array(
                            'taxable'  => esc_html__( 'Taxable', 'booking-and-rental-manager-for-woocommerce' ),
                            'shipping' => esc_html__( 'Shipping only', 'booking-and-rental-manager-for-woocommerce' ),
                            'none'     => esc_html__( 'None', 'booking-and-rental-manager-for-woocommerce' )
                        )
                    ),
                    array(
                        'id'      => '_tax_class',
                        'title'   => esc_html__( ' Tax Class', 'booking-and-rental-manager-for-woocommerce' ),
                        'details' => esc_html__( 'Please Select Tax Class', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'select',
                        'class'   => 'omg',
                        'default' => 'none',
                        'args'    => $MageRBFWClass->all_tax_list()
                    ),
                );
            } else {
                $the_array = array(
                    array(
                        'id'      => 'tax_notice',
                        'title'   => esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
                        'details' => esc_html__( 'To enable automated tax calculation, first ensure that “enable taxes and tax calculations” is checked on WooCommerce > Settings > General. <a href="https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/">View Documentation</a>', 'booking-and-rental-manager-for-woocommerce' ),
                        'type'    => 'notice',
                    ),
                );
            }
        }
    }
    new RBFW_WC_Meta();
}