<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Tax')) {
        class RBFW_Tax{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_tax"><i class="fa-solid fa-dollar-sign"></i><?php esc_html_e('Tax', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

             public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Tax Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure tax information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
                <?php
            }

            public function panel_header($title,$description){
                ?>
                    <section class="bg-light mt-5">
                        <div>
                            <label>
                                <?php echo esc_html($title); ?>
                            </label>
                            <span><?php echo esc_html($description); ?></span>
                        </div>
                    </section>
                <?php
            }

            public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_tax">

                    <?php $this->section_header(); ?>
                    <?php $this->panel_header('Tax Settings','Here you can set tax information.'); ?>

                    <div>

                            <?php if(get_option( 'woocommerce_calc_taxes' ) == 'yes'){ ?>

                                <?php
                                $tax_class  = get_post_meta($post_id,'_tax_class',true);
                                $tax_status  = get_post_meta($post_id,'_tax_status',true);
                                $tax_slugs = WC_Tax::get_tax_class_slugs();
                                $tax_classes = WC_Tax::get_tax_classes();
                                ?>

                            <section >
                                <div>
                                    <label><?php esc_html_e('Tax Status', 'booking-and-rental-manager-for-woocommerce'); ?></label>
                                    <span><?php esc_html_e('Please Select Tax Status', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                                </div>
                                <select class="formControl max_300" name="_tax_status">
                                    <option><?php esc_html_e('Select Tax Status', 'booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <option value="taxable" <?php echo esc_attr($tax_status == 'taxable' ? 'selected' : ''); ?>>
                                        <?php esc_html_e('Taxable', 'booking-and-rental-manager-for-woocommerce'); ?>
                                    </option>
                                    <option value="shipping" <?php echo esc_attr($tax_status == 'shipping' ? 'selected' : ''); ?>>
                                        <?php esc_html_e('Shipping only', 'booking-and-rental-manager-for-woocommerce'); ?>
                                    </option>
                                    <option value="none" <?php echo esc_attr($tax_status == 'none' ? 'selected' : ''); ?>>
                                        <?php esc_html_e('None', 'booking-and-rental-manager-for-woocommerce'); ?>
                                    </option>
                                </select>
                            </section>

                            <section>
                                <div>
                                    <label>
                                        <?php esc_html_e('Tax Class', 'booking-and-rental-manager-for-woocommerce'); ?>
                                    </label>
                                    <span><?php esc_html_e('Please Select Tax Class', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                                </div>
                                <select id="_tax_class" name="_tax_class">
                                    <option><?php esc_html_e('Select Tax Class', 'booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <option value="standard" <?php echo esc_attr($tax_class == 'standard' ? 'selected' : ''); ?>>
                                        <?php esc_html_e('Standard', 'booking-and-rental-manager-for-woocommerce'); ?>
                                    </option>
                                    <?php if (sizeof($tax_classes) > 0) { ?>
                                        <?php foreach ($tax_classes as $key => $class) { ?>
                                            <option value="<?php echo esc_attr($tax_slugs[$key]); ?>" <?php echo esc_attr($tax_class == $tax_slugs[$key] ? 'selected' : ''); ?>>
                                                <?php echo esc_html($class); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </section>

                            <?php }else{ ?>

                            <section >
                                <p><?php esc_html_e('To enable automated tax calculation, first ensure that “enable taxes and tax calculations” is checked on WooCommerce &gt; Settings &gt; General. ','booking-and-rental-manager-for-woocommerce')?><a href="<?php esc_attr('https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/') ?>"><?php esc_html_e('View Documentation','booking-and-rental-manager-for-woocommerce'); ?></a></p>
                            </section>
                            <?php } ?>

                        </div>

                </div>
            <?php 
            }

            public function settings_save($post_id) {
                
                if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['rbfw_ticket_type_nonce'])), 'rbfw_ticket_type_nonce' ) ) {
                    return;
                }

                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return;
                }

                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }

                if ( get_post_type( $post_id ) == 'rbfw_item' ) {
                    $_tax_class = isset( $_POST['_tax_class'] ) 
                        ? rbfw_array_strip( sanitize_text_field( wp_unslash( $_POST['_tax_class'] ) ) ) 
                        : '';

                    $_tax_status = isset( $_POST['_tax_status'] ) 
                        ? rbfw_array_strip( sanitize_text_field( wp_unslash( $_POST['_tax_status'] ) ) ) 
                        : '';


                    update_post_meta( $post_id, '_tax_class', $_tax_class );
                    update_post_meta( $post_id, '_tax_status', $_tax_status );
 
                }
            }
        }
        new RBFW_Tax();
    }
