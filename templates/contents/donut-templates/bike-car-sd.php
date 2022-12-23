<?php
// Template Name: Donut Bike/Car Sd Theme
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
$highlited_features = get_post_meta($post_id, 'rbfw_highlights_texts', true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_highlights_texts', true)) : [];
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option('super_slider_style', 'super_slider_settings','');
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating() : '';
$currency_symbol = rbfw_mps_currency_symbol();
$rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : [];

/* Single Day/Appointment Type */
$rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
$rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );

$price = 0;
if(!empty($rbfw_bike_car_sd_data) && ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment')):
	$rbfw_price_arr = [];

	foreach ($rbfw_bike_car_sd_data as $key => $value) {

		if(!empty($value['price'])){
			$rbfw_price_arr[] =  $value['price'];
		}
		
	}

	if(!empty($rbfw_price_arr)){
		$smallest_price = min($rbfw_price_arr);
		$smallest_price = (float)$smallest_price;
	} else {
		$smallest_price = 0;
	}
	$price = $smallest_price;
endif;
?>
<div class="rbfw_donut_template">
	<div class="rbfw_dt_row_header">
		<div class="rbfw_dt_header_col1">
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
		<div class="rbfw_dt_header_col2">
			<div class="rbfw_dt_pricing">
				<div class="rbfw_dt_pricing_card">
					<div class="rbfw_dt_pricing_card_col1"><?php echo $currency_symbol; ?></div>
					<div class="rbfw_dt_pricing_card_col2">

						<?php if (!empty($price)) : ?>
						<div class="rbfw_dt_pricing_card_price rbfw_text_center"><?php echo $price; ?></div>
						<?php endif; ?>
						
					</div>
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
			<div class="rbfw_dt_highlighted_features">
			<?php if ( $highlited_features ) : ?>
			<ul>
			<?php foreach ( $highlited_features as $feature ) :

			if($feature['icon']):
				$icon = $feature['icon'];
			else:
				$icon = 'fas fa-arrow-right';
			endif;

			if($feature['title']):
				echo '<li><i class="'.mep_esc_html($icon).'"></i><span>' . $feature['title'] . '</span></li>';
			endif;

			endforeach; 
			?>
			</ul>
			<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="rbfw_dt_row_registration">
		<div class="rbfw_dt_heading"><?php echo esc_html($rbfw->get_option('rbfw_text_booking_detail', 'rbfw_basic_translation_settings', __('Booking Detail','booking-and-rental-manager-for-woocommerce'))); ?></div>
		<?php include( RBFW_Function::template_path( 'forms/bike-car-sd-registration.php' ) ); ?>
	</div>

	<?php if($rbfw_enable_faq_content == 'yes'): ?>
	<div class="rbfw_dt_row_faq">
		<div class="rbfw_dt_heading"><?php echo esc_html($rbfw->get_option('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Freequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?></div>
		<?php do_action( 'rbfw_the_faq_style_two', $post_id ); ?>
	</div>
	<?php endif; ?>
	
	<?php do_action( 'rbfw_content_before_related_items', $post_id ); ?>


	<?php if(!empty($rbfw_related_post_arr)): ?>
	<div class="rbfw_dt_row_related_item">
		<div class="rbfw_dt_heading"><?php echo esc_html($rbfw->get_option('rbfw_text_you_may_also_like', 'rbfw_basic_translation_settings', __('You May Also Like','booking-and-rental-manager-for-woocommerce'))); ?></div>
		<?php do_action( 'rbfw_related_products_style_two', $post_id ); ?>
	</div>
	<?php endif; ?>
</div>