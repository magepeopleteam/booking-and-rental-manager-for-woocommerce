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


            $rbfw_tax_meta_boxs = array(
                'page_nav' => esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title'       => esc_html__( 'Tax Configuration', 'booking-and-rental-manager-for-woocommerce' ),
                        'description' => esc_html__( 'desc', 'booking-and-rental-manager-for-woocommerce' ),
                        'options'     => $the_array
                    ),
                
                ),
            );
            
            /*$rbfw_tax_meta_boxs_args = array(
                'meta_box_id'    => 'rbfw_tax_meta_boxes',
                'meta_box_title' => '<i class="fas fa-file-lines"></i>'. esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ),
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
            
            new RMFWAddMetaBox( $rbfw_tax_meta_boxs_args );*/
        }
    }
    new RBFW_WC_Meta();
}