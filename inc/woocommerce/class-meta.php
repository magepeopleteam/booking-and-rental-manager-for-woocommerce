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
                        'title'   => __( ' Tax Status', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'details' => __( 'Please Select Tax Status', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'type'    => 'select',
                        'class'   => 'omg',
                        'default' => 'taxable',
                        'args'    => array(
                            'taxable'  => __( 'Taxable', 'booking-and-rental-manager-for-woocommerce-pro' ),
                            'shipping' => __( 'Shipping only', 'booking-and-rental-manager-for-woocommerce-pro' ),
                            'none'     => __( 'None', 'booking-and-rental-manager-for-woocommerce-pro' )
                        )
                    ),
                    array(
                        'id'      => '_tax_class',
                        'title'   => __( ' Tax Class', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'details' => __( 'Please Select Tax Class', 'booking-and-rental-manager-for-woocommerce-pro' ),
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
                        'title'   => __( 'Tax', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'details' => __( 'To enable automated tax calculation, first ensure that “enable taxes and tax calculations” is checked on WooCommerce > Settings > General. <a href="https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/">View Documentation</a>', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'type'    => 'notice',
                    ),
                );
            }


            $rbfw_tax_meta_boxs = array(
                'page_nav' => __( 'Tax', 'booking-and-rental-manager-for-woocommerce-pro' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title'       => __( 'Tax Configuration', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'description' => __( '', 'booking-and-rental-manager-for-woocommerce-pro' ),
                        'options'     => $the_array
                    ),
                
                ),
            );
            
            $rbfw_tax_meta_boxs_args = array(
                'meta_box_id'    => 'rbfw_tax_meta_boxes',
                'meta_box_title' => '<i class="fa-solid fa-file-lines"></i>'. __( 'Tax', 'booking-and-rental-manager-for-woocommerce-pro' ),
                'screen'         => array( 'rbfw_item' ),
                'context'        => 'normal',
                'priority'       => 'low',
                'callback_args'  => array(),
                'nav_position'   => 'none', // right, top, left, none
                'item_name'      => "MagePeople",
                'item_version'   => "2.0",
                'panels'         => array(
                    'rbfw_tax_meta_boxs' => $rbfw_tax_meta_boxs
                ),
            );
            
            new RMFWAddMetaBox( $rbfw_tax_meta_boxs_args );
        }
    }
    new RBFW_WC_Meta();
}