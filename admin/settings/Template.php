<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Template')) {
        class RBFW_Template{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_template_settings_meta_boxes"><i class="fa-solid fa-pager"></i><?php esc_html_e('Template', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure template Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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


			public function add_tabs_content( $post_id ) {
            ?>
				<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_template_settings_meta_boxes">
					<?php $this->section_header(); ?>
                    <?php $this->panel_header('Template Settings','Template Settings'); ?>

					<section>
						<label for=""><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<?php  $template =  get_post_meta($post_id, 'rbfw_single_template', true) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default'; ?>
						 <?php 
						 	$the_template = array(
								'Muffin' => 'Muffin template',
								'Donut' => 'Donut template',
								'Default' => 'Classic template',
							);
						 ?>
						<select name="rbfw_single_template" id="rbfw_single_template">
							<option selected="" value="Muffin">Muffin template</option>
							<option value="Donut">Donut template</option>
							<option value="Default">Classic template</option>
							<?php foreach($template as $kay => $value): ?>
								<option <?php echo (in_array($value,$the_template))?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
							<?php endforeach; ?>
						</select>
					</section>
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
                    $rbfw_single_template = isset( $_POST['rbfw_single_template'] ) ? rbfw_array_strip( $_POST['rbfw_single_template'] ) : '';
                    update_post_meta( $post_id, 'rbfw_single_template', $rbfw_single_template );
                }
            }
		}
		new RBFW_Template();
	}