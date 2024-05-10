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

            public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('General Info', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure basic information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
                <?php
            }

            public function panel_header($title,$description){
                ?>
                    <section class="bg-light mt-5">
                        <div>
                            <label>
                                <?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $title ); ?>
                            </label>
                            <span><?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $description ); ?></span>
                        </div>
                    </section>
                <?php
            }


            public function shortcode($post_id){
                ?>
                    <section>
                        <div>
                            <label>
                                <?php echo esc_html__( 'Add To Cart Form Shortcode', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <span><?php echo esc_html__('This short code you can put anywhere in your content.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <code class="rbfw_add_to_cart_shortcode_code">[rent-add-to-cart  id='<?php echo $post_id; ?>']</code>
                    </section>
                <?php
            }

            public function select_category($post_id){
                $rbfw_categories = get_post_meta($post_id,'rbfw_categories',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_categories', true)) : [];
                ?>
                    <section>
                        <div>
                            <label>
                                <?php _e( 'Select Categories', 'booking-and-rental-manager-for-woocommerce' ) ?>
                            </label>
                            <span><?php _e( 'Add multiple categories', 'booking-and-rental-manager-for-woocommerce' ) ?></span>
                        </div>
                        <div class="w-50">
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
                <?php
            }

            public function shipping_enable($post_id){
                ?>
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
                </section>
                <?php
            }

            public function features_category( $post_id ) {
                ?>
                    <?php $this->panel_header('Features Category Settings','Here you can configure features category.'); ?>
                        <section>
                            <div class="w-100">
                                <div class="feature-categories">
                                    <?php $feature_categories = get_post_meta($post_id,'rbfw_feature_category',true); ?>
                                    <?php foreach($feature_categories as $key => $data): ?>
                                        <div class="feature-category">
                                            <header>
                                                <label for="">Feature category title</label>
                                                <input class="feature-category-title" type="text" name="rbfw_feature_category[<?php echo $key; ?>][cat_title]" value="<?php echo $data['cat_title']; ?>">
                                            </header>
                                            <div class="feature-lists">
                                                <div class="feature-items">
                                                    <div class="feature-item-clone">
                                                        test
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="ppof-button add-item" onclick="createFeatureItem()"><i class="fas fa-circle-plus"></i>Add New Feature</div>
                                                </div>
                                            </div>
                                            <button onclick="jQuery(this).parent().remove()">x</button>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- clone item -->
                                    <div class="feature-category-clone">
                                        <header>
                                            <label for="">Feature category title</label>
                                            <!-- name value will add by jquery on duplication-->
                                            <input class="feature-category-title" type="text" name="">

                                        </header>
                                        <div class="feature-lists">
                                            <div class="feature-items">
                                                    <div class="feature-item-clone">
                                                        test
                                                    </div>
                                                </div>
                                            <div class="text-center">
                                                <div class="ppof-button add-item" onclick="createFeatureItem()"><i class="fas fa-circle-plus"></i>Add New Feature</div>
                                            </div>
                                        </div>
                                        <button onclick="jQuery(this).parent().remove()"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="ppof-button add-item" onclick="createFeatureCategory()"><i class="fas fa-circle-plus"></i>Add New Feature Category</div>
                                </div>
                            </div>
                        </section>
                <?php
            }
            public function add_tabs_content( $post_id ) {
                ?>
                    <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_gen_info">
                        
                        <?php $this->section_header(); ?>
                        
                        <?php $this->panel_header('Basic Settings','Here you can settings basic info.'); ?>
                        
                        
                        <?php $this->shortcode($post_id); ?>

                        <?php $this->select_category($post_id); ?>

                        <?php $this->shipping_enable($post_id); ?>

                        <?php $this->features_category($post_id); ?>


                    </div>

                    <script>
                        

                        jQuery('input[name=shipping_enable]').click(function(){  
                            var status = jQuery(this).val();
                            if(status == 'on') {
                                jQuery(this).val('off') 
                            }  
                            if(status == 'off') {
                                jQuery(this).val('on');  
                            }
                        });

                        

                        function createFeatureCategory(){
                            var items=jQuery(".feature-category").find('.feature-category-title').length;
                            items=items++;
                            jQuery(".feature-category-clone").clone().appendTo(".feature-categories").removeClass('feature-category-clone').addClass('feature-category')
                            .find('.feature-category-title').attr('name','rbfw_feature_category['+ items +'][cat_title]');
                        }

                        function createFeatureItem(){
                            jQuery(".feature-item-clone").clone().appendTo('.feature-items').removeClass('feature-item-clone').addClass('feature-item');
                        }
                    </script>
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
                    $shipping_enable 	 = isset( $_POST['shipping_enable'] ) ? rbfw_array_strip( $_POST['shipping_enable'] ) : '';
                    $feature_category 	 = isset( $_POST['rbfw_feature_category'] ) ? rbfw_array_strip( $_POST['rbfw_feature_category'] ) : [];
                    
                    update_post_meta( $post_id, 'rbfw_categories', $rbfw_categories );
                    update_post_meta( $post_id, 'shipping_enable', $shipping_enable );
                    update_post_meta( $post_id, 'rbfw_feature_category', $feature_category );
                }
            }
        }

        new RBFW_General_Info();
    }

       


