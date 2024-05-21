<?php
/**
 * @author Shahadat hossain <raselsha@gmail.com>
 */

if( ! defined('ABSPATH')){die;}
if( ! class_exists('RBFW_Related')){
    class RBFW_Related{
        public function __construct() {
            add_action('rbfw_meta_box_tab_name',[$this,'add_tab_menu']);
            add_action('rbfw_meta_box_tab_content',[$this,'add_tabs_content']);
            add_action('save_post',[$this,'settings_save'],99, 1);
        }
        public function add_tab_menu(){
            ?>
                <li data-target-tabs="#rbfw_related"><i class="fas fa-plug"></i><?php esc_html_e('Related', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
        }

        public function section_header(){
            ?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__('Related Items', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure related items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    
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


        public function related_items($post_id){
            ?>
                <section>
                    <div id="rbfw_releted_rbfw" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rbfw_releted_rbfw w-100">
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
                </section>
            <?php
        }

        public function add_tabs_content($post_id){
            ?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_related">
                    <?php $this->section_header(); ?>
                    <?php $this->panel_header('Related Items','Related Items'); ?>
                    <?php $this->related_items($post_id); ?>
                </div>
            <?php
        }

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
                $related_categories 	 = isset( $_POST['rbfw_releted_rbfw'] ) ? rbfw_array_strip( $_POST['rbfw_releted_rbfw'] ) : [];
                update_post_meta( $post_id, 'rbfw_releted_rbfw', $related_categories );
            }
        }
    }
    new RBFW_Related();
}