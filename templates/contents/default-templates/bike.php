<?php
// Template Name: Bike Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
?>
<?php
global $rbfw;
$post_id = get_the_id();
$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
$tab_style = $rbfw->get_option('rbfw_single_rent_tab_style', 'rbfw_basic_single_rent_page_settings','vertical');
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option('super_slider_style', 'super_slider_settings','');
?>
	<div class="mp_default_theme">
		<div class="mpContainer">
			<div class="mp_details_page">
				<div class="mp_left_section">
					<div class="mpStyle <?php echo $slide_style; ?>">
						<?php do_action( 'add_super_slider', $post_id ,'rbfw_gallery_images'); ?>
					</div>
					
					<div class="rbfw-single-left-container">
						<div class="rbfw-single-left-information">
						<div class="rbfw-header-container">
							<div class="rbfw-post-title"><?php echo esc_html(get_the_title()); ?></div>
							<div class="rbfw-post-meta">
								<?php do_action( 'rbfw_product_meta', $post_id ); ?>
							</div>
						</div>
							<div class="rbfw-tab-container <?php echo mep_esc_html($tab_style); ?>">
								<div class="rbfw-tab-menu">
									<ul class="rbfw-ul">
										<li><a href="#" class="rbfw-features rbfw-tab-a active-a"
											 data-id="features"><i class="fa-solid fa-list-check"></i></a></li>
										<li><a href="#" class="rbfw-description rbfw-tab-a"
											 data-id="description"><i class="fa-solid fa-circle-info"></i></a>
										</li>
										<?php if(!empty($rbfw_enable_faq_content) && $rbfw_enable_faq_content == 'yes'): ?>
										<li><a href="#" class="rbfw-faq rbfw-tab-a"
											 data-id="faq"><i class="fa-solid fa-circle-question"></i></a></li>
										<?php endif; ?>	 
										<?php do_action( 'rbfw_tab_menu_list', $post_id ); ?>
									</ul>
								</div><!--end of tab-menu-->
								<div class="rbfw-tab rbfw-tab-active" data-id="features">
									<div class="rbfw-single-left-information-item">
									<?php if ( $rbfw_feature_category ) :
											foreach ( $rbfw_feature_category as $value ) :
												$cat_title = $value['cat_title'];
												$cat_features = $value['cat_features'] ? $value['cat_features'] : [];
										?>
										<div class="rbfw-sub-heading"><?php echo esc_html($cat_title); ?></div>
										<ul>
											<?php
											if(!empty($cat_features)):
												$i = 1;
												foreach ($cat_features as $features):
													$icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
													$title = $features['title'];
													if($title):?>
														<li style="<?php echo ($i > 4)?'display:none':''?>" data-status="<?php echo ($i > 4)?'extra':''?>">
															<i class="<?php echo esc_attr(mep_esc_html($icon)); ?>"></i><span><?php echo mep_esc_html($title); ?></span>
														</li>
													<?php
													endif;
													$i++;
												endforeach;
											endif;
											?>
											<li style="width:100%">
												<a class="rbfw_muff_lmf_btn">
													<?php echo $rbfw->get_option('rbfw_text_view_more_features', 'rbfw_basic_translation_settings', __('Load More','booking-and-rental-manager-for-woocommerce')); ?>
												</a>
											</li>
										</ul>
										<?php
											endforeach;
										endif;
										?>
									</div>
								</div><!--end of tab one-->
								<div class="rbfw-tab " data-id="description">
								<div class="rbfw-sub-heading"><?php echo esc_html($rbfw->get_option('rbfw_text_description', 'rbfw_basic_translation_settings', __('Description','booking-and-rental-manager-for-woocommerce'))); ?></div>	
									<?php the_content(); ?>
								</div><!--end of tab two-->

								<?php if(!empty($rbfw_enable_faq_content) && $rbfw_enable_faq_content == 'yes'): ?>
								<div class="rbfw-tab " data-id="faq">
								<div class="rbfw-sub-heading"><?php echo esc_html($rbfw->get_option('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Frequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?></div>
									<?php do_action( 'rbfw_the_faq_only', $post_id ); ?>
								</div><!--end of tab three-->
								<?php endif; ?>
								
								<?php do_action( 'rbfw_tab_content', $post_id ); ?>
							</div><!--end of container-->
						</div>
					</div>
					<div class="rbfw-related-products-wrapper"><?php do_action( 'rbfw_related_products', $post_id ); ?></div>
				</div>
				<div class="mp_right_section">
					<?php include( RBFW_Function::template_path( 'forms/bike-registration.php' ) ); ?>
				</div>
			</div>
		</div>
	</div>
