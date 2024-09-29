<?php
// Template Name: Muffin Resort Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
?>
<?php
global $rbfw;
$post_id = $post_id??0;
$rbfw_id = $post_id;
$frontend = $frontend??0;
global $rbfw;

$post_title = get_the_title();
$post_content  = get_the_content();
$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option_trans('super_slider_style', 'super_slider_settings','');
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($post_id,'muffin','style1') : '';
$currency_symbol = rbfw_mps_currency_symbol();
$get_hourly_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'hourly');
$get_daily_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'daily');
$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'no';
$rbfw_enable_daywise_price = get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) : 'no';
$rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : [];
$post_review_rating_style2 = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($post_id,'muffin', 'style2') : '';
$post_review_average = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id) : '';

$post_review_average_hygenic = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'hygenic') : '';
$post_review_average_quality = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'quality') : '';
$post_review_average_cost_value = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'cost_value') : '';
$post_review_average_staff = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'staff') : '';
$post_review_average_facilities = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'facilities') : '';
$post_review_average_comfort = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'comfort') : '';

$post_review_value_round_hygenic = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_hygenic) : '';
$post_review_value_round_quality = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_quality) : '';
$post_review_value_round_cost_value = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_cost_value) : '';
$post_review_value_round_staff = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_staff) : '';
$post_review_value_round_facilities = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_facilities) : '';
$post_review_value_round_comfort = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_comfort) : '';

$post_hygenic_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_hygenic) : '';
$post_quality_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_quality) : '';
$post_cost_value_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_cost_value) : '';
$post_staff_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_staff) : '';
$post_facilities_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_facilities) : '';
$post_comfort_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_comfort) : '';

$gallery_images_additional = rbfw_get_additional_gallary_images($post_id, 5, 'style2');
$daylong_price_label = $rbfw->get_option_trans('rbfw_text_daylong', 'rbfw_basic_translation_settings', __('Daylong','booking-and-rental-manager-for-woocommerce'));
$daynight_price_label = $rbfw->get_option_trans('rbfw_text_daynight', 'rbfw_basic_translation_settings', __('Daynight','booking-and-rental-manager-for-woocommerce'));

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

    if(!empty($rbfw_daylong_rate)){
        $daylong_smallest_price = min($rbfw_daylong_rate);
        $daylong_smallest_price = (float)$daylong_smallest_price;
    } else {
        $daylong_smallest_price = 0;
    }

    if(!empty($rbfw_daynight_rate)){
        $daynight_smallest_price = min($rbfw_daynight_rate);
        $daynight_smallest_price = (float)$daynight_smallest_price;
    } else {
        $daynight_smallest_price = 0;
    }

endif;

$review_system = rbfw_get_option('rbfw_review_system', 'rbfw_basic_review_settings', 'on');
?>
<div class="rbfw_muffin_template">
	<div class="rbfw_muff_row_header">
		<div class="rbfw_muff_header_col1">
            <div class="rbfw_muff_title">
				<h1><?php echo esc_html($post_title); ?></h1>
			</div>
			<div class="rbfw_muff_rating">
				<?php if(!empty($post_review_rating)): ?>
				<div class="rbfw_rent_list_average_rating">
					<?php echo $post_review_rating; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="rbfw_muff_header_col2">
			<div class="rbfw_muff_pricing">
				<div class="rbfw_muff_pricing_card">

					<div class="rbfw_muff_pricing_card_col2">

						<?php if (!empty($daylong_smallest_price)) : ?>
						<div class="rbfw_muff_pricing_card_price" data-type="resort"><span class="rbfw_muff_pricing_card_price_badge"><?php echo rbfw_mps_price($daylong_smallest_price); ?></span><span> / <?php echo esc_html($daylong_price_label); ?></span></div>
						<?php endif; ?>

						<?php if (!empty($daynight_smallest_price)) : ?>
						<div class="rbfw_muff_pricing_card_price" data-type="resort"><span class="rbfw_muff_pricing_card_price_badge"><?php echo rbfw_mps_price($daynight_smallest_price); ?></span><span> / <?php echo esc_html($daynight_price_label); ?></span></div>
						<?php endif; ?>

					</div>
				</div>
			</div>
		</div>
	</div>

    <?php if($frontend){ ?>

	<div class="rbfw_muff_row_slider">
        <div class="rbfw_muff_slider mpStyle <?php echo $slide_style; ?>">
            <?php do_action( 'add_super_slider', $post_id ,'rbfw_gallery_images'); ?>
        </div>
    </div>
    <div class="rbfw_muff_row_content">
        <div class="rbfw_muff_post_content">
            <h2 class="rbfw_muff_post_content_headline"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_description', 'rbfw_basic_translation_settings', __('Description','booking-and-rental-manager-for-woocommerce'))); ?></h2>
            <?php echo $post_content; ?>
        </div>
    </div>

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

    <?php } ?>

    <div class="rbfw_muff_row_registration">
        <div class="rbfw_muff_registration_wrapper" data-type="resort">
            <h3 class="rbfw_muff_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_start_booking', 'rbfw_basic_translation_settings', __('Start Booking','booking-and-rental-manager-for-woocommerce'))); ?></h3>
            <?php include( RBFW_Function::get_template_path( 'forms/resort-registration.php' ) ); ?>
        </div>
    </div>

    <?php if($frontend){ ?>


    <?php if(!empty($gallery_images_additional)) { ?>
    <div class="rbfw_muff_row_slider">
        <div class="rbfw_muff_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_photos', 'rbfw_basic_translation_settings', __('Photos','booking-and-rental-manager-for-woocommerce'))); ?></div>
        <?php echo $gallery_images_additional; ?>
    </div>
    <?php } ?>

	<?php if(rbfw_check_pro_active() === true && $review_system == 'on'){ ?>
	<div class="rbfw_muff_row_review_summary">
		<div class="rbfw_muff_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_ratings', 'rbfw_basic_translation_settings', __('Ratings','booking-and-rental-manager-for-woocommerce'))); ?></div>
		<div class="rbfw_muff_row_review_inner">
			<div class="rbfw_muff_review_summ_col">
				<div class="rbfw_muff_review_rating_wrap">
					<div class="rbfw_muff_review_rating_number"><span class="rbfw_muff_review_average_rating_number"><?php echo $post_review_average; ?></span>/5</div>
					<div class="rbfw_muff_review_rating_stars"><?php echo $post_review_rating_style2; ?></div>
				</div>
			</div>
			<div class="rbfw_muff_review_summ_col">
				<div class="rbfw_muff_review_progress_item_wrapper">
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_hygenic',__('Hygenic','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_hygenic_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_hygenic; ?>/5</div>
						</div>
					</div>
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_quality',__('Quality','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_quality_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_quality; ?>/5</div>
						</div>
					</div>
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_cost_value',__('Cost Value','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_cost_value_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_cost_value; ?>/5</div>
						</div>
					</div>
				</div>
			</div>
			<div class="rbfw_muff_review_summ_col">
			<div class="rbfw_muff_review_progress_item_wrapper">
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_staff',__('Staff','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_staff_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_staff; ?>/5</div>
						</div>
					</div>
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_facilities',__('Facilities','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_facilities_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_facilities; ?>/5</div>
						</div>
					</div>
					<div class="rbfw_muff_review_progress_item">
						<label><?php rbfw_string('rbfw_text_comfort',__('Comfort','rbfw-pro')); ?></label>
						<div class="rbfw_muff_review_progress_inner_wrap">
							<div class="rbfw_muff_review_progress_bar">
								<div class="rbfw_muff_review_progress_bar-green" <?php echo $post_comfort_progress_width; ?>></div>
							</div>
							<div class="rbfw_muff_review_progress_bar_avg"><?php echo $post_review_value_round_comfort; ?>/5</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php if(rbfw_check_pro_active() === true && $review_system == 'on'): ?>
	<div class="rbfw_muff_row_reviews">
		<div class="rbfw_muff_heading">
			<div class="rbfw_muff_heading_tab active" data-tab="tab1">
				<?php do_action( 'rbfw_muff_review_tab', $post_id ); ?>
			</div>
		</div>
		<div class="rbfw_muff_faq_tab_contents">
			<div class="rbfw_muff_faq_tab_content active" data-content="tab1">
				<?php do_action( 'rbfw_muff_review_content', $post_id ); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if(!empty($rbfw_related_post_arr)): ?>
	<div class="rbfw_muff_row_related_item">
		<h3 class="rbfw_muff_heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_you_may_also_like', 'rbfw_basic_translation_settings', __('You May Also Like','booking-and-rental-manager-for-woocommerce'))); ?></h3>
		<?php do_action( 'rbfw_related_products_style_three', $post_id ); ?>
	</div>
	<?php endif; ?>
	<?php if($rbfw_enable_faq_content == 'yes') { ?>
		<div class="rbfw_muff_row_faq">
			<div class="rbfw_muff_heading rbfw_muff_faq_heading">
				<?php echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Freequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?>
			</div>
			<?php do_action( 'rbfw_the_faq_style_two', $post_id ); ?>
		</div>
	<?php } ?>
    <?php } ?>
</div>
