<?php
// Template Name: Bike Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
global $rbfw;
global $frontend;
$post_id = $post_id??0;
$frontend = $frontend??0;

$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
$tab_style = $rbfw->get_option_trans('rbfw_single_rent_tab_style', 'rbfw_basic_single_rent_page_settings','horizontal');
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option_trans('super_slider_style', 'super_slider_settings','');
?>
	<div class="mp_default_theme">
		<div class="mpContainer">
			<div class="mp_details_page">
                <?php  if($frontend){ ?>
				<div class="mp_left_section">
					<div class="mpStyle <?php echo esc_attr($slide_style); ?>">
						<?php do_action( 'rbfw_slider', $post_id ,'rbfw_gallery_images'); ?>
					</div>

					<div class="rbfw-single-left-container">
						<div class="rbfw-single-left-information">
						<div class="rbfw-header-container">
							<div class="rbfw-post-meta">
								<?php do_action( 'rbfw_product_meta', $post_id ); ?>
							</div>
						</div>
							<div class="rbfw-tab-container <?php echo esc_attr($tab_style); ?>">
								<div class="rbfw-tab-menu">
									<!-- <ul class="rbfw-ul">
										<li><a href="#" class="rbfw-features rbfw-tab-a active-a"
											data-id="features"><i class="fas fa-list-check"></i></a></li>
										<li><a href="#" class="rbfw-description rbfw-tab-a"
											data-id="description"><i class="fas fa-circle-info"></i></a>
										</li>
										<?php if(!empty($rbfw_enable_faq_content) && $rbfw_enable_faq_content == 'yes'): ?>
										<li><a href="#" class="rbfw-faq rbfw-tab-a"
											data-id="faq"><i class="fas fa-circle-question"></i></a></li>
										<?php endif; ?>
										<?php do_action( 'rbfw_tab_menu_list', $post_id ); ?>
									</ul> -->
								</div><!--end of tab-menu-->

                                <!-- Fiture lists with icon will be shown here -->
								<?php do_action( 'rbfw_product_feature_lists', $post_id ); ?>

								<div class="description" data-id="description">
									<h3 class="rbfw-sub-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_description', 'rbfw_basic_translation_settings', __('Description','booking-and-rental-manager-for-woocommerce'))); ?></h3>
									<?php the_content(); ?>
								</div><!--end of tab two-->

								<?php if(!empty($rbfw_enable_faq_content) && $rbfw_enable_faq_content == 'yes'): ?>
								<div class="faq" data-id="faq">
									<h3 class="rbfw-sub-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Frequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?></h3>
									<?php do_action( 'rbfw_the_faq_only', $post_id ); ?>
								</div><!--end of tab three-->
								<?php endif; ?>

								<?php do_action( 'rbfw_tab_content', $post_id ); ?>
							</div><!--end of container-->
						</div>
					</div>
					<div class="rbfw-related-products-wrapper"><?php do_action( 'rbfw_related_products', $post_id ); ?></div>
				</div>
                <?php } ?>
				<div class="mp_right_section rbfw_multi_items_right">
					<?php do_action('booking_form_header',$post_id); ?>
					<div class="rbfw-booking-form">
						<?php include( RBFW_Function::get_template_path( 'forms/multi-items-registration.php' ) ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
