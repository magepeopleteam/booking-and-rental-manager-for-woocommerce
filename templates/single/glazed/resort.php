<?php
// Template Name: Resort Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
?>
<?php
global $rbfw;
$post_id = get_the_id();
$rbfw_id = $post_id;
$post_title = get_the_title();
$post_content  = get_the_content();
$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option_trans('super_slider_style', 'super_slider_settings','');
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating() : '';
$currency_symbol = rbfw_mps_currency_symbol();

$rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : [];

/* Resort Type */
$rbfw_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
$rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );

if(!empty($rbfw_room_data) && $rbfw_rent_type == 'resort'):
	$rbfw_daylong_rate = [];
	$rbfw_daynight_rate = [];
	foreach ($rbfw_room_data as $key => $value) {

		if(!empty($value['rbfw_room_daylong_rate'])){
			$rbfw_daylong_rate[] =  $value['rbfw_room_daylong_rate'];
		}
		
		if(!empty($value['rbfw_room_daynight_rate'])){
			$rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
		}
		
	}
	//$merged_arr = array_merge($rbfw_daylong_rate,$rbfw_daynight_rate);

	if(!empty($rbfw_daylong_rate)){
		$rbfw_daylong_rate_smallest_price = min($rbfw_daylong_rate);
		$rbfw_daylong_rate_smallest_price = (float)$rbfw_daylong_rate_smallest_price;
	} else {
		$rbfw_daylong_rate_smallest_price = 0;
	}

	if(!empty($rbfw_daynight_rate)){
		$rbfw_daynight_rate_smallest_price = min($rbfw_daynight_rate);
		$rbfw_daynight_rate_smallest_price = (float)$rbfw_daynight_rate_smallest_price;
	} else {
		$rbfw_daynight_rate_smallest_price = 0;
	}	
	
endif;

$rbfw_dt_sidebar_switch  = get_post_meta( $post_id, 'rbfw_dt_sidebar_switch', true ) ? get_post_meta( $post_id, 'rbfw_dt_sidebar_switch', true ) : 'off';
$rbfw_dt_sidebar_content = get_post_meta( $post_id, 'rbfw_dt_sidebar_content', true );
?>
<div class="rbfw_donut_template">
	<div class="rbfw_dt_row_header">
        <div class="rbfw_dt_rating">
            <?php if(!empty($post_review_rating)): ?>
            <div class="rbfw_rent_list_average_rating">
                <?php echo $post_review_rating; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="rbfw_dt_title">
            <h1><?php echo esc_html($post_title); ?></h1>
        </div>
	</div>

    <div class="rbfw_dt_row_header">
        <div class="rbfw_dt_pricing">
            <div class="rbfw_dt_pricing_card">
                <div class="rbfw_dt_pricing_card_col1"><?php echo $currency_symbol; ?></div>
                <div class="rbfw_dt_pricing_card_col2">

                    <?php if (!empty($rbfw_daylong_rate_smallest_price)) : ?>
                        <div class="rbfw_dt_pricing_card_price"><?php echo $rbfw_daylong_rate_smallest_price; ?><span> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('DAYLONG','booking-and-rental-manager-for-woocommerce'))); ?></span></div>
                    <?php endif; ?>

                    <?php if (!empty($rbfw_daynight_rate_smallest_price)) : ?>
                        <div class="rbfw_dt_pricing_card_price"><?php echo $rbfw_daynight_rate_smallest_price; ?><span> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('DAYNIGHT','booking-and-rental-manager-for-woocommerce'))); ?></span></div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>


	<div class="rbfw_dt_row_content">
		<div class="rbfw_dt_content_col1">
			<div class="rbfw_dt_slider mpStyle <?php echo $slide_style; ?>">
                <?php do_action( 'add_super_slider', $post_id ,'rbfw_gallery_images'); ?>
            </div>
		</div>
		<div class="rbfw_dt_content_col2">
			<div class="rbfw_dt_post_content">
				<?php echo $post_content; ?>
			</div>
		</div>
	</div>


    <div class="rbfw_muffin_template">



        <div class="rbfw_muff_row_hf">

            <div class="rbfw_muff_highlighted_features" data-type="resort">
                <?php if ( $rbfw_feature_category ) :

                    foreach ( $rbfw_feature_category as $value ) :

                        $cat_title = $value['cat_title'];
                        $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
                        ?>
                        <div class="rbfw_muff_hf_inner_wrap">
                            <h2 class="rbfw_muff_post_content_headline"><?php echo esc_html($cat_title); ?></h2>
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
										<?php echo $rbfw->get_option_trans('rbfw_text_view_more_features', 'rbfw_basic_translation_settings', __('Load More','booking-and-rental-manager-for-woocommerce')); ?>
									</a>
								</li>
							</ul>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </div>

	<div class="rbfw_dt_row_registration <?php if($rbfw_dt_sidebar_switch == 'on'){ echo 'rbfw_dt_sidebar_enabled'; } ?>">
		<?php if($rbfw_dt_sidebar_switch == 'on'): ?>
		<div class="rbfw_dt_registration_col1">
			<?php echo do_action('rbfw_dt_testimonial', $post_id); ?>
			<?php echo html_entity_decode($rbfw_dt_sidebar_content); ?>
		</div>
		<?php endif; ?>
		<div class="rbfw_dt_registration_col2">
			<div class="rbfw_dt_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_booking_detail', 'rbfw_basic_translation_settings', __('Booking Detail','booking-and-rental-manager-for-woocommerce'))); ?></div>
			<?php include(  RBFW_TEMPLATE_PATH .'forms/resort-registration.php' ); ?>
		</div>
	</div>

	<?php if($rbfw_enable_faq_content == 'yes'): ?>
	<div class="rbfw_dt_row_faq">
		<div class="rbfw_dt_heading">
			<div class="rbfw_dt_heading_tab active" data-tab="tab1">
				<?php echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Freequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?>
			</div>
			<div class="rbfw_dt_heading_tab" data-tab="tab2">
				<?php do_action( 'rbfw_dt_review_tab', $post_id ); ?>
			</div>
		</div>
		<div class="rbfw_dt_faq_tab_contents">
			<div class="rbfw_dt_faq_tab_content active" data-content="tab1">
				<?php do_action( 'rbfw_the_faq_style_two', $post_id ); ?>
			</div>
			<div class="rbfw_dt_faq_tab_content" data-content="tab2">
				<?php do_action( 'rbfw_dt_review_content', $post_id ); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if(!empty($rbfw_related_post_arr)): ?>
	<div class="rbfw_dt_row_related_item">
		<div class="rbfw_dt_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_you_may_also_like', 'rbfw_basic_translation_settings', __('You May Also Like','booking-and-rental-manager-for-woocommerce'))); ?></div>
		<?php do_action( 'rbfw_related_products_style_two', $post_id ); ?>
	</div>
	<?php endif; ?>
</div>