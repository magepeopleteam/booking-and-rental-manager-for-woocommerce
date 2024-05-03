<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Location')) {
        class RBFW_Location{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_location_config"><i class="fa-solid fa-location-dot"></i><?php esc_html_e('Location', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
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
                                <?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $title ); ?>
                            </label>
                            <span><?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $description ); ?></span>
                        </div>
                    </section>
                <?php
            }

            public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_location_config">
					rbfw_location_config
				</div>
			<?php
			}
		}
		new RBFW_Location();
	}