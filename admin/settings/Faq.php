<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_FAQ')) {
        class RBFW_FAQ{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_faq"><i class="fa-solid fa-circle-question"></i><?php esc_html_e( ' FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

            public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php _e('Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php _e('Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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
				$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
			?>
			<div class="mpStyle mp_tab_item" data-tab-item="#rbfw_faq">
			
				<?php $this->section_header(); ?>
                
				<?php $this->panel_header('FAQ Settings','FAQ Settings'); ?>
				<section >
					<div>
						<label><?php _e( 'FAQ Content', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'FAQ Content turn on/off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_enable_faq_content" value="<?php echo esc_attr($rbfw_enable_faq_content); ?>" <?php echo esc_attr(($rbfw_enable_faq_content=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>
				<section class="rbfw-faq-content <?php echo esc_attr(($rbfw_enable_faq_content=='yes')?'show':'hide'); ?>" >
					<?php
							$faqs = RBFW_Function::get_post_info( $post_id, 'mep_event_faq', array() );
							if ( sizeof( $faqs ) > 0 ) {
								foreach ( $faqs as $faq ) {
									$id = 'rbfw_faq_content_' . uniqid();
									echo rbfw_repeated_item( $id, 'mep_event_faq', $faq );
								}
							}
						?>
						<button type="button" class=" rbfw_add_faq_content ppof-button">
							<i class="fa-solid fa-circle-plus"></i>
							<?php esc_html_e( 'Add New FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
				</section>
			</div>
			<script>
					jQuery('input[name=rbfw_enable_faq_content]').click(function(){
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw-faq-content').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw-faq-content').slideDown().removeClass('hide').addClass('show');
						}
					});
			</script>
			<?php 
			}
		}
		new RBFW_FAQ();
	}

