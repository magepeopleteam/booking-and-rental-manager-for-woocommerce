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

            public function multiple_category_select($post_id){
                
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
                <?php
            }

            public function related_post( $post_id ) {
                ?>
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
                <?php
            }


            public function add_tabs_content( $post_id ) {
                ?>
                    <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_gen_info">
                        
                        <?php $this->section_header(); ?>
                        
                        <?php $this->panel_header('Basic Settings','Here you can settings basic info.'); ?>
                        
                        
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
                                <?php $this->multiple_category_select($post_id); ?>
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
                                <?php $this->related_post($post_id); ?>					
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
                        </section>
                        
                        <?php $this->panel_header('Sidebar Testimonial settigns','Sidebar Testimonial settigns'); ?>
                        <section>
                            <div class="w-100 text-center">
                                <div class="testimonials">
                                    <?php 
                                        $sidebar_testimonials = get_post_meta($post_id,'rbfw_dt_sidebar_testimonials',true);
                                        foreach($sidebar_testimonials as $key => $data): ?>
                                        <div class="testimonial">
                                            <button onclick="jQuery(this).parent().remove()"> <i class="fas fa-trash"></i></button>
                                            <textarea class="testimonial-field" name="rbfw_dt_sidebar_testimonials[<?php echo  $key; ?>]['rbfw_dt_sidebar_testimonial_text']" cols="30" rows="10"><?php echo esc_html(current($data)); ?></textarea>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="testimonial-clone">
                                        <button onclick="jQuery(this).parent().remove()"> <i class="fas fa-trash"></i> </button>
                                        <textarea class="testimonial-field" name=""  cols="30" rows="10"></textarea>
                                    </div>
                                </div>
                            
                                <div class="ppof-button add-item" onclick="createTestimonial()"><i class="fas fa-plus-square"></i><?php _e('Add New Testimonial','booking-and-rental-manager-for-woocommerce'); ?></div>
                            </div>
                        </section>
                        
                        <?php $this->panel_header('Donut Template Sidebar Content','Donut Template Sidebar Content'); ?>
                        <section>
                            <div class="w-100">
                                <?php 
                                    $sidebar_content = get_post_meta($post_id,'rbfw_dt_sidebar_content',true);
                                    $settings = array(
                                            'textarea_rows' => '10',
                                            'media_buttons' => true,
                                            'textarea_name' => 'rbfw_dt_sidebar_content',
                                    );
                                    wp_editor( $sidebar_content, 'rbfw_dt_sidebar_content', $settings );
                                ?>
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
                        <section>
                            <div class="feature-categories">
                                <div class="feature-category"></div>
                                <?php $feature_categories = get_post_meta($post_id,'rbfw_feature_category',true); ?>
                                <?php foreach($feature_categories as $key => $data): ?>
                                    <div class="feature-category">
                                        <section class="bg-light">
                                            <label for="">Feature category title</label>
                                            <input class="feature-category-title" type="text" name="rbfw_feature_category[<?php echo $key; ?>][cat_title]" value="<?php echo $data['cat_title']; ?>">
                                        </section>
                                        <section class="feature-list">
                                            <section>
                                                <button>icon</button>
                                                <input type="text"  >
                                                <button>remove</button>
                                            </section>
                                        </section>
                                        <button onclick="jQuery(this).parent().remove()">x</button>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mt-5 text-center">
                                    <div class="ppof-button add-item" onclick="createFeatureCategory()"><i class="fas fa-circle-plus"></i>Add New Feature Category</div>
                                </div>
                                <!-- clone item -->
                                <div class="feature-category-clone">
                                    <section class="bg-light">
                                        <label for="">Feature category title</label>
                                        <input class="feature-category-title" type="text">
                                    </section>
                                    <section class="feature-list">
                                        <section class="feature-item">
                                            <button>icon</button>
                                            <input type="text" >
                                            <button>remove</button>
                                        </section>
                                        <div class="mt-5 text-center">
                                            <div class="ppof-button add-item" onclick="createFeatureItem(jQuery(this))"><i class="fas fa-circle-plus"></i>Add New Feature</div>
                                        </div>
                                    </section>
                                    <button onclick="jQuery(this).parent().remove()">x</button>
                                </div>
                            </div>
                            
                        </section>

                    </div>

                    <script>
                        jQuery('input[name=rbfw_dt_sidebar_switch]').click(function(){
                            var status = jQuery(this).val();
                            if(status == 'on') {
                                jQuery(this).val('off') 
                            }  
                            if(status == 'off') {
                                jQuery(this).val('on');  
                            }
                        });

                        jQuery('input[name=shipping_enable]').click(function(){  
                            var status = jQuery(this).val();
                            if(status == 'on') {
                                jQuery(this).val('off') 
                            }  
                            if(status == 'off') {
                                jQuery(this).val('on');  
                            }
                        });

                        function createTestimonial(){
                            now = jQuery.now();

                            jQuery(".testimonial-clone").clone().appendTo(".testimonials")
                            .removeClass('testimonial-clone').addClass('testimonial')
                            .children('.testimonial-field').attr('name','rbfw_dt_sidebar_testimonials['+now+'][rbfw_dt_sidebar_testimonial_text]');
                        };

                        function removeItem(event){
                            event.preventDefault();
                            jQuery(this).parent().remove();
                        }

                        function createFeatureCategory(){
                            var items=jQuery(".feature-category").find('.feature-category-title').length;
                            items=items++;
                            jQuery(".feature-category-clone").clone().insertAfter(".feature-category:last").removeClass('feature-category-clone').addClass('feature-category').find('.feature-category-title').attr('name','rbfw_feature_category['+ items +'][cat_title]');
                        }

                        function createFeatureItem($this){
                            $this.closest('.feature-list').find(".feature-item").clone().insertBefore($this.closest('section'));
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
                    $related_categories 	 = isset( $_POST['rbfw_releted_rbfw'] ) ? rbfw_array_strip( $_POST['rbfw_releted_rbfw'] ) : [];
                    $dt_sidebar_switch 	 = isset( $_POST['rbfw_dt_sidebar_switch'] ) ? rbfw_array_strip($_POST['rbfw_dt_sidebar_switch']) : '';
                    $shipping_enable 	 = isset( $_POST['shipping_enable'] ) ? rbfw_array_strip( $_POST['shipping_enable'] ) : '';
                    $testimonials 	 = isset( $_POST['rbfw_dt_sidebar_testimonials'] ) ? rbfw_array_strip( $_POST['rbfw_dt_sidebar_testimonials'] ) : [];
                    $sidebar_content 	 = isset( $_POST['rbfw_dt_sidebar_content'] ) ? rbfw_array_strip( $_POST['rbfw_dt_sidebar_content'] ) : [];
                    $feature_category 	 = isset( $_POST['rbfw_feature_category'] ) ? rbfw_array_strip( $_POST['rbfw_feature_category'] ) : [];
                       
                    update_post_meta( $post_id, 'rbfw_categories', $rbfw_categories );
                    update_post_meta( $post_id, 'rbfw_releted_rbfw', $related_categories );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_switch', $dt_sidebar_switch );
                    update_post_meta( $post_id, 'shipping_enable', $shipping_enable );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_testimonials', $testimonials );
                    update_post_meta( $post_id, 'rbfw_dt_sidebar_content', $sidebar_content );
                    update_post_meta( $post_id, 'rbfw_feature_category', $feature_category );
 
                }
            }
        }

        new RBFW_General_Info();
    }

       


