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
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu']);
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content']);
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
                                            $post_id = get_post_meta($post_id,'rbfw_releted_rbfw',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_releted_rbfw', true)) : [];
                                            $the_query = new WP_Query( array(
                                                'post_type' => 'rbfw_item',
                                            ) );
                                        ?>
                                        <?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
                                            <option <?php echo (in_array(get_the_ID(),$post_id))?'selected':'' ?> value="<?php the_ID(); ?>"> <?php the_title(); ?> </option>
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
                            
                            <label class="switch">
                                <?php $switch = get_post_meta($post_id,'rbfw_dt_sidebar_switch',true);?>

                                <input type="checkbox" name="rbfw_dt_sidebar_switch" value="<?php echo esc_attr(($switch=='on')?$switch:'off'); ?>" <?php echo esc_attr(($switch=='on')?'checked':''); ?>>
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
                                <span><?php echo esc_html__('Donut Template Sidebar', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>
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
        }

        new RBFW_General_Info();
    }

       


