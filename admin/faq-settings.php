<?php


	add_action( 'rbfw_meta_box_tab_name', 'rbfw_add_meta_box_tab_faq', 100 );
	function rbfw_add_meta_box_tab_faq( $rbfw_id ) {
		?>
		<li data-target-tabs="#rbfw_faq"><i class="fa-solid fa-circle-question"></i><?php esc_html_e( ' FAQ', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
		<?php

	}

	add_action( 'rbfw_meta_box_tab_content', 'rbfw_add_meta_box_tab_faq_content', 100 );
	function rbfw_add_meta_box_tab_faq_content( $rbfw_id ) {
		$rbfw_enable_faq_content  = get_post_meta( $rbfw_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_faq_content', true ) : 'no';
		?>
		<div class="mpStyle mp_tab_item " data-tab-item="#rbfw_faq">
			
			<h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php echo ''.esc_html__( 'FAQ Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
			
			<section class="component d-flex justify-content-between align-items-center mb-2">
				<div class="w-30 d-flex justify-content-between align-items-center">
					<p class=""><?php esc_html_e( 'FAQ Content', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span>It displays available quantity information in item details page.</span></i></p>
				</div>
				<div class="w-70 d-flex justify-content-between align-items-center">
					<div class="rbfw_switch_wrapper rbfw_switch_faq">
						<div class="rbfw_switch">
							<label for="rbfw_enable_faq_content_on" class="<?php if ( $rbfw_enable_faq_content == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_faq_content" class="rbfw_enable_faq_content" value="yes" id="rbfw_enable_faq_content_on" <?php if ( $rbfw_enable_faq_content == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_faq_content_off" class="<?php if ( $rbfw_enable_faq_content != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_faq_content" class="rbfw_enable_faq_content" value="no" id="rbfw_enable_faq_content_off" <?php if ( $rbfw_enable_faq_content != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
						</div>
					</div>
				</div>
			</section>

			<div class="rbfw_faq_content_wrapper" style="display: <?php if ($rbfw_enable_faq_content == 'yes' ) {
					echo esc_attr( 'block' );
				} else {
					echo esc_attr( 'none' );
				} ?>;">
				<?php
					$faqs = RBFW_Function::get_post_info( $rbfw_id, 'mep_event_faq', array() );
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

			</div>
		</div>
		<script>
			jQuery(document).ready(function(){
				jQuery('.rbfw_enable_faq_switch_label').click(function (e) {
					let checked_attr = jQuery('.rbfw_enable_faq_switch_label input').attr('checked');
					if(typeof checked_attr !== 'undefined' && checked_attr !== false){
						jQuery('.rbfw_enable_faq_switch_label input').removeAttr('checked');
						jQuery('.rbfw_faq_content_wrapper').hide();
					}
					else{
						jQuery('.rbfw_enable_faq_switch_label input').attr('checked',true);
						jQuery('.rbfw_faq_content_wrapper').show();
					}
				});
			});
        </script>
		<?php
	}
