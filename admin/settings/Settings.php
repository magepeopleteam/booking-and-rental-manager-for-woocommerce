<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Settings' ) ) {
		class RBFW_Settings {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_frontend_display"><i class="fas fa-gear"></i><?php esc_html_e( ' Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php esc_html_e( 'Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label><?php echo esc_html( $title ); ?></label>
                        <p><?php echo esc_html( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function shipping_enable( $post_id ) {
            ?>
            <section>
                <div>
                    <label>
                        <?php echo esc_html__( 'Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </label>
                    <p><?php echo esc_html__( 'Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                </div>
                <?php
                $shipping_enable_switch = get_post_meta( $post_id, 'shipping_enable', true ) ? get_post_meta( $post_id, 'shipping_enable', true ) : 'no';
                if($shipping_enable_switch=='off'){
                    $shipping_enable_switch = 'no';
                }
                ?>

                <label class="switch">
                    <input type="checkbox" name="shipping_enable" value="<?php echo esc_attr($shipping_enable_switch); ?>" <?php echo esc_attr( ( $shipping_enable_switch == 'yes' ) ? 'checked' : '' ); ?>>
                    <span class="slider round"></span>
                </label>
            </section>
            <?php
        }


            public function shipping_method( $post_id ) {

				$shipping_classes = get_terms( 'product_shipping_class', array( 'hide_empty' => false ) );

				if(!empty($shipping_classes)){



                ?>
                <section>
                    
				    <div>
                        <label>
                            <?php echo esc_html__( 'Select shopping class', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                    </div>

                    <?php

                    // Get the saved shipping class for this post
                    $selected_class = get_post_meta( $post_id, 'rent_shipping_class', true );

                    echo '<select name="rent_shipping_class" id="rent_shipping_class">';
                    echo '<option value="">'.esc_html__( 'Select shopping class', 'booking-and-rental-manager-for-woocommerce' ).'</option>';

                    foreach ( $shipping_classes as $class ) {
                        $selected = ( $selected_class == $class->term_id ) ? 'selected' : '';
                        echo '<option value="' . $class->term_id . '" ' . $selected . '>' . esc_html( $class->name ) . '</option>';
                    }
                    echo '</select>';
                    ?>
                </section>
                <?php
				}
            }

			public function quantity_display( $post_id ) {
				$rbfw_available_qty_info_switch = get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $post_id, 'rbfw_available_qty_info_switch', true ) : 'no';
                ?>
                <section>
                    <div>
                        <label><?php esc_html_e( 'Display item available quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                        <p><?php esc_html_e( 'It displays available quantity information in item details page.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="rbfw_available_qty_info_switch" value="<?php echo esc_attr( $rbfw_available_qty_info_switch ); ?>" <?php echo esc_attr( ( $rbfw_available_qty_info_switch == 'yes' ) ? 'checked' : '' ); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
				<?php
			}

			public function shortcode( $post_id ) {
				?>
                <section>
                    <div>
                        <label>
							<?php echo esc_html__( 'Add To Cart Form Shortcode', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <p><?php echo esc_html__( 'This short code you can put anywhere in your content.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <code class="rbfw_add_to_cart_shortcode_code">[rent-add-to-cart id='<?php echo esc_attr( $post_id ); ?>']</code>
                </section>
				<?php
			}

			public function  service_quantity_box($post_id){
				$rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'yes';
				?>
				<div class="wervice_quantity_input_box">
					<section>
						<div>
							<label><?php esc_html_e( 'Enable Service Quantity Box', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<p><?php esc_html_e( 'If you Enable this customer can select number of quantity in front-end.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="switch">
							<input type="checkbox" name="rbfw_enable_extra_service_qty" value="<?php echo esc_attr( $rbfw_enable_extra_service_qty ); ?>" <?php echo esc_attr( ( $rbfw_enable_extra_service_qty == 'yes' ) ? 'checked' : '' ); ?>>
							<span class="slider round"></span>
						</label>
					</section>
				</div>
				<?php
			}

			public function reset_orders_section( $post_id ) {
				?>
				<section>
					<div>
						<label><?php esc_html_e( 'Reset All Rental Orders', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<p><?php esc_html_e( 'This will cancel all rental-related orders for this item. This action cannot be undone.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<button type="button" id="rbfw-reset-orders-btn" class="button button-secondary" data-item-id="<?php echo esc_attr( $post_id ); ?>">
						<?php esc_html_e( 'Reset', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
					<div id="rbfw-reset-orders-result"></div>
				</section>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_frontend_display">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Front-end Display Settings ', 'Front-end Display Settings' ); ?>
					<?php $this->shortcode( $post_id ); ?>
					<?php $this->quantity_display( $post_id ); ?>
					<?php $this->shipping_enable( $post_id ); ?>
					<?php $this->shipping_method( $post_id ); ?>

					<?php $this->service_quantity_box( $post_id ); ?>
					<?php $this->reset_orders_section( $post_id ); ?>
                </div>
				<?php
			}

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$rbfw_available_qty_info_switch = isset( $_POST['rbfw_available_qty_info_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_available_qty_info_switch'] ) ) : 'no';
					$shipping_enable                = isset( $_POST['shipping_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_enable'] ) ) : 'no';
                    update_post_meta( $post_id, 'shipping_enable', $shipping_enable );
                    $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
                    update_post_meta($product_id, '_virtual', ($shipping_enable=='yes')?'no':'yes');
					update_post_meta( $post_id, 'rbfw_available_qty_info_switch', $rbfw_available_qty_info_switch );

             
					
					$rbfw_enable_extra_service_qty = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_extra_service_qty'] ) ) : 'no';
					update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$faq_description    = isset( $_POST['rbfw_faq_description'] ) ? sanitize_text_field($_POST['rbfw_faq_description']) : '';
					update_post_meta( $post_id, 'rbfw_faq_description', $faq_description );
				}

                if ( isset( $_POST['rent_shipping_class'] ) ) {
                    update_post_meta( $post_id, 'rent_shipping_class', sanitize_text_field( wp_unslash($_POST['rent_shipping_class'] )) );
                }


				$product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
                
                $product_type = get_post_meta($post_id, 'shipping_enable', true) ? get_post_meta($post_id, 'shipping_enable', true) : 'no';
                $term_id = get_post_meta($post_id, 'rent_shipping_class', true) ? get_post_meta($post_id, 'rent_shipping_class', true) : '';
               
    
                global $wpdb;
                wp_set_object_terms($product_id, array(), 'product_shipping_class');
                $taxonomy   = 'product_shipping_class';
                $term_taxonomy_id = $wpdb->get_var( $wpdb->prepare("
                SELECT term_taxonomy_id 
                    FROM {$wpdb->term_taxonomy}
                    WHERE term_id = %d AND taxonomy = %s
                    ", $term_id, $taxonomy));
  
                if ($term_taxonomy_id) {
                        // Check if already assigned
                        $exists = $wpdb->get_var( $wpdb->prepare("
                            SELECT object_id 
                            FROM {$wpdb->term_relationships}
                            WHERE object_id = %d AND term_taxonomy_id = %d
                        ", $product_id, $term_taxonomy_id));
                    
                        if (!$exists) {
                            // Insert the relationship
                            $wpdb->insert(
                                $wpdb->term_relationships,
                                array(
                                    'object_id' => $product_id,
                                    'term_taxonomy_id' => $term_taxonomy_id
                                ),
                                array('%d', '%d')
                            );
            
                        } 
                    }

					update_post_meta($product_id, '_virtual', ($product_type=='yes')?'no':'yes');
			}

		}
		new RBFW_Settings();
	}
