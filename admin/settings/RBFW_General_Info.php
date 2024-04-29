<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_General_Info')) {
        class RBFW_General_Info{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_gen_info"><i class="fas fa-tools"></i><?php esc_html_e('General Info', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

            public function add_tabs_content( $post_id ) {
                ?>
                    <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_gen_info">
                        <h2 class="mp_tab_item_title"><?php echo esc_html__('General Info', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                        <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure basic information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>

                        <section class="bg-light">
                            <div>
                                <label>
                                    <?php echo esc_html__('Basic Settings', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Here you can settings basic info.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Add To Cart Form Shortcode', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('This short code you can put anywhere in your content.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <code class="rbfw_add_to_cart_shortcode_code">[rent-add-to-cart  id='<?php echo $post_id; ?>']</code>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php _e( 'Select Categories', 'booking-and-rental-manager-for-woocommerce' ) ?>
                                </label>
                                <span><?php _e( 'Add multiple categories', 'booking-and-rental-manager-for-woocommerce' ) ?></span>
                            </div>
                            <div class="w-50">
                                <?php
                                $rbfw_categories = get_post_meta($post_id,'rbfw_categories',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_categories', true)) : [];
                                ?>
                                <select name="rbfw_categories[]" multiple class="category2">
                                    <?php
                                    $terms = get_terms( array(
                                        'taxonomy'   => 'rbfw_item_caregory',
                                        'hide_empty' => false,
                                    ) );
                                    foreach ( $terms as $key => $value ) {
                                        ?>
                                        <option <?php echo (in_array($value->name,$rbfw_categories))?'selected':'' ?> value="<?php echo $value->name ?>"> <?php echo $value->name ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Add related items here', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div class="w-50">
                                <div id="rbfw_releted_rbfw" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rbfw_releted_rbfw">
                                    <select name="rbfw_releted_rbfw[]" id="rbfw_releted_rbfw" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                                        <?php 
                                            $releted_post_id = get_post_meta($post_id,'rbfw_releted_rbfw',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_releted_rbfw', true)) : [];
                                            $the_query = new WP_Query( array(
                                                'post_type' => 'rbfw_item',
                                            ) );
                                        ?>
                                        <?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
                                            <option <?php echo (in_array(get_the_ID(),$releted_post_id))?'selected':'' ?> value="<?php the_ID(); ?>"> <?php the_title(); ?> </option>
                                        <?php endwhile;  ?>
                                        
                                    </select>
                                </div>					
                            </div>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>

                            <?php $dt_sidebar_switch = get_post_meta($post_id,'rbfw_dt_sidebar_switch',true);?>
                            <label class="switch">
                                <input type="checkbox" name="rbfw_dt_sidebar_switch" value="<?php echo esc_attr(($dt_sidebar_switch=='on')?$dt_sidebar_switch:'off'); ?>" <?php echo esc_attr(($dt_sidebar_switch=='on')?'checked':''); ?>>
                                <span class="slider round"></span>
                            </label>
                            <script>
                                jQuery('input[name=rbfw_dt_sidebar_switch]').click(function(){
                                    
                                    var status = jQuery(this).val();
                                    if(status == 'on') {
                                        jQuery(this).val('off') 
                                    }  
                                    if(status == 'off') {
                                        jQuery(this).val('on');  
                                    }
                                })
                            </script>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Is shipping enable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <?php $shipping_enable_switch = get_post_meta($post_id,'shipping_enable',true);?>
                            <label class="switch">
                                <input type="checkbox" name="shipping_enable" value="<?php echo esc_attr(($shipping_enable_switch=='on')?$shipping_enable_switch:'off'); ?>" <?php echo esc_attr(($shipping_enable_switch=='on')?'checked':''); ?>>
                                <span class="slider round"></span>
                            </label>
                            <script>
                                jQuery('input[name=shipping_enable]').click(function(){
                                    
                                    var status = jQuery(this).val();
                                    if(status == 'on') {
                                        jQuery(this).val('off') 
                                    }  
                                    if(status == 'off') {
                                        jQuery(this).val('on');  
                                    }
                                })
                            </script>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Donut Template Sidebar Testimonials', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div class="ppof-button add-item"><i class="fas fa-plus-square"></i> Add New Testimonial</div>
                        </section>
                        <section>
                            <div>
                                <label>
                                    <?php echo esc_html__( 'Donut Template Sidebar Content', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($post_id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-wp_editor-wrapper
                                field-wp_editor-wrapper-<?php echo esc_attr($post_id); ?>">
                                    
                                    <div class="error-mgs"></div>
                            </div>
                        </section>

                        <section class="bg-light mt-5">
                            <div>
                                <label>
                                    <?php echo esc_html__('Features Category Settings', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Here you can configure features category.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                        </section>
                        <section >
                            <div>
                                <label>
                                    <?php echo esc_html__('Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </label>
                                <span><?php echo esc_html__('Set title for feature category.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <div>
                                field_feature_category() will use here. 
                            </div>
                        </section>
                    </div>
            <?php } 

            public function settings_save($post_id) {
                
                if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( $_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce' ) ) {
                    return;
                }

                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return;
                }

                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }

                if ( get_post_type( $post_id ) == 'rbfw_item' ) {
                    $rbfw_categories 	 = isset( $_POST['rbfw_categories'] ) ? rbfw_array_strip( $_POST['rbfw_categories'] ) : [];
                    $related_categories 	 = isset( $_POST['rbfw_releted_rbfw'] ) ? rbfw_array_strip( $_POST['rbfw_releted_rbfw'] ) : [];
                    $dt_sidebar_switch 	 = isset( $_POST['rbfw_dt_sidebar_switch'] ) ? rbfw_array_strip($_POST['rbfw_dt_sidebar_switch']) : '';
                    $shipping_enable 	 = isset( $_POST['shipping_enable'] ) ? rbfw_array_strip( $_POST['shipping_enable'] ) : '';
                       
        
                    update_post_meta( $post_id, 'rbfw_categories', $rbfw_categories );
                    update_post_meta( $post_id, 'rbfw_releted_rbfw', $related_categories );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_switch', $dt_sidebar_switch );
                    update_post_meta( $post_id, 'shipping_enable', $shipping_enable );

                   



                }
            }
        }

        new RBFW_General_Info();
    }

       


