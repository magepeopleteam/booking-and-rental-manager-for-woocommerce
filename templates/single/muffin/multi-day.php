<?php
	// Template Name: Muffin Bike-car-sd Theme
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	global $rbfw;
	$post_id                            = get_the_id();
	$rbfw_id                            = $post_id;
	$post_title                         = get_the_title();
	$post_content                       = get_the_content();
	$rbfw_feature_category              = get_post_meta( $post_id, 'rbfw_feature_category', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_feature_category', true ) ) : [];
	$rbfw_enable_faq_content            = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
	$slide_style                        = $rbfw->get_option_trans( 'super_slider_style', 'super_slider_settings', '' );
	$post_review_rating                 = function_exists( 'rbfw_review_display_average_rating' ) ? rbfw_review_display_average_rating( $post_id, 'muffin', 'style1' ) : '';
	$currency_symbol                    = rbfw_mps_currency_symbol();
	$get_hourly_price                   = rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'hourly' );
	$get_daily_price                    = rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'daily' );
	$enable_daily_rate                  = get_post_meta( $rbfw_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_daily_rate', true ) : 'yes';
	$enable_hourly_rate                 = get_post_meta( $rbfw_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_hourly_rate', true ) : 'no';
	$rbfw_enable_daywise_price          = get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) : 'no';
	$rbfw_related_post_arr              = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ) : [];
	$post_review_rating_style2          = function_exists( 'rbfw_review_display_average_rating' ) ? rbfw_review_display_average_rating( $post_id, 'muffin', 'style2' ) : '';
	$post_review_average                = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id ) : '';
	$post_review_average_hygenic        = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'hygenic' ) : '';
	$post_review_average_quality        = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'quality' ) : '';
	$post_review_average_cost_value     = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'cost_value' ) : '';
	$post_review_average_staff          = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'staff' ) : '';
	$post_review_average_facilities     = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'facilities' ) : '';
	$post_review_average_comfort        = function_exists( 'rbfw_review_get_average_by_id' ) ? rbfw_review_get_average_by_id( $post_id, 'comfort' ) : '';
	$post_review_value_round_hygenic    = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_hygenic ) : '';
	$post_review_value_round_quality    = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_quality ) : '';
	$post_review_value_round_cost_value = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_cost_value ) : '';
	$post_review_value_round_staff      = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_staff ) : '';
	$post_review_value_round_facilities = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_facilities ) : '';
	$post_review_value_round_comfort    = function_exists( 'rbfw_review_value_round' ) ? rbfw_review_value_round( $post_review_average_comfort ) : '';
	$post_hygenic_progress_width        = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_hygenic ) : '';
	$post_quality_progress_width        = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_quality ) : '';
	$post_cost_value_progress_width     = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_cost_value ) : '';
	$post_staff_progress_width          = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_staff ) : '';
	$post_facilities_progress_width     = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_facilities ) : '';
	$post_comfort_progress_width        = function_exists( 'rbfw_review_get_progress_bar_width' ) ? rbfw_review_get_progress_bar_width( $post_review_average_comfort ) : '';
	$additional_gallary_status          = get_post_meta( get_the_ID(), 'rbfw_enable_additional_gallary', true );
	$additional_gallary_status          = $additional_gallary_status ? $additional_gallary_status : 'off';
	$gallery_images_additional          = rbfw_get_additional_gallary_images( $post_id, 2 );
	$prices_start_at                    = $rbfw->get_option_trans( 'rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __( 'Prices start at', 'booking-and-rental-manager-for-woocommerce' ) );
	/* Single Day/Appointment Type */
	$rbfw_rent_type        = get_post_meta( $post_id, 'rbfw_item_type', true );
	$rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
	if ( ! empty( $rbfw_bike_car_sd_data ) && ( $rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment' ) ):
		$rbfw_price_arr = [];
		foreach ( $rbfw_bike_car_sd_data as $key => $value ) {
			if ( ! empty( $value['price'] ) ) {
				$rbfw_price_arr[] = $value['price'];
			}
		}
		if ( ! empty( $rbfw_price_arr ) ) {
			$smallest_price = min( $rbfw_price_arr );
			$smallest_price = (float) $smallest_price;
		} else {
			$smallest_price = 0;
		}
		$price = $smallest_price;
	endif;
	$review_system = rbfw_get_option( 'rbfw_review_system', 'rbfw_basic_review_settings', 'on' );
?>
<div class="rbfw_muffin_template">
    <div class="rbfw_muff_row_header">
        <div class="rbfw_muff_header_col1">
            <div class="rbfw_muff_title">
                <h1><?php echo esc_html( $post_title ); ?></h1>
            </div>
            <div class="rbfw_muff_rating">
				<?php if ( ! empty( $post_review_rating ) ): ?>
                    <div class="rbfw_rent_list_average_rating">
                        <?php echo wp_kses( $post_review_rating , rbfw_allowed_html() ); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
        <div class="rbfw_muff_header_col2">
            <div class="rbfw_muff_pricing">
                <div class="rbfw_muff_pricing_card">
                    <div class="rbfw_muff_pricing_card_col2">
						<?php if ( ! empty( $price ) ) : ?>
                            <div class="rbfw_muff_pricing_card_price">
                                    <span class="rbfw_muff_pricing_card_price_badge">
                                        <?php echo wp_kses( wc_price( $price ) , rbfw_allowed_html()); ?>
                                    </span>
                                <span> / <?php echo esc_html( $prices_start_at ); ?></span>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rbfw_muff_row_slider rbfw_muff_row_slider_content">
        <div class="rbfw_muff_content_col1">
            <div class="rbfw_muff_slider mpStyle <?php echo esc_attr( $slide_style ); ?>">
				<?php do_action( 'rbfw_slider', $post_id, 'rbfw_gallery_images' ); ?>
            </div>
            <div class="rbfw_muff_registration_wrapper">
                <h2 class="rbfw_muff_heading">
					<?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_start_booking', 'rbfw_basic_translation_settings', __( 'Start Booking', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>
                </h2>
				<?php include( RBFW_Function::get_template_path( 'forms/multi-day-registration.php' ) ); ?>
            </div>
        </div>
        <div class="rbfw_muff_content_col2">
            <div class="rbfw_muff_content_wrapper">
                <h2 class="rbfw_muff_post_content_headline"><?php _e('Feature Highlights','booking-and-rental-manager-for-woocommerce'); ?></h2>
            </div>
            <div class="rbfw_muff_highlighted_features">
				<?php if ( $rbfw_feature_category ) : ?>
                    <ul class="muff_features_item">
						<?php
							$total_features    = 0;
							foreach ( $rbfw_feature_category as $value ) :
								$cat_title = $value['cat_title'];
								$cat_features  = ! empty( $value['cat_features'] ) ? $value['cat_features'] : [];
								if ( ! empty( $cat_features ) ):
									foreach ( $cat_features as $index => $features ):
										$icon = ! empty( $features['icon'] ) ? $features['icon'] : 'fas fa-check-circle';
										$title = $features['title'];
										if ( $total_features < 10 ): ?>
                                            <li title="<?php echo esc_attr( $title ); ?>">
                                                <i class="<?php echo esc_attr( $icon ); ?>"></i>
                                                <span><?php echo esc_html( $title ); ?></span>
                                            </li>
											<?php
											$total_features ++;
										endif;
									endforeach;
								endif;
							endforeach;
						?>
                    </ul>
					<?php if ( $total_features >= 10 ) : ?>
                        <div class="rbfw_see_more_category" id="rbfw_see_more_category-<?php echo esc_attr( $post_id ); ?>">See more</div>
					<?php endif; ?>
				<?php endif; ?>
            </div>
            <!-- popup content will show here -->
            <div class="rbfw_popup_wrapper" id="rbfw_popup_wrapper">
                <div class="rbfw_rent_cat_info_popup">
                    <span class="rbfw_popup_close_btn" id="rbfw_popup_close_btn">&times;</span>
                    <div id="rbfw_popup_content">
                    </div>
                </div>
            </div>
            <div class="rbfw_muff_content_wrapper">
                <div class="rbfw_muff_post_content">
                    <h2 class="rbfw_muff_post_content_headline">
						<?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_description', 'rbfw_basic_translation_settings', __( 'Description', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>
                    </h2>
					<?php
						$readmore        = __( 'See More', 'booking-and-rental-manager-for-woocommerce' );
						$readLess        = __( 'Less', 'booking-and-rental-manager-for-woocommerce' );
						$post_content    = get_the_content();
						$trimmed_content = wp_trim_words( $post_content, 100, '... <a href="#" class="rbfw-read-more">' . $readmore . '</a>' );
						$full_content    = apply_filters( 'the_content', $post_content );
						$full_content    .= '<a href="#" class="rbfw-read-more">' . $readLess . '</a>';
					?>
                    <div class="trimmed-content">
						<?php echo wp_kses( $trimmed_content, rbfw_allowed_html()); ?>
                    </div>
                    <div class="full-content" style="display: none;">
						<?php echo wp_kses( $full_content , rbfw_allowed_html()); ?>
                    </div>
                </div>
				<?php if ( $additional_gallary_status == 'on' ): ?>
					<?php if ( ! empty( $gallery_images_additional ) ) { ?>
                        <div class="rbfw_muff_row_slider">
                            <h3 class="rbfw_muff_heading">
								<?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_photos', 'rbfw_basic_translation_settings', __( 'Photos', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>
                            </h3>
							<?php echo wp_kses( $gallery_images_additional , rbfw_allowed_html()); ?>
                        </div>
					<?php } ?>
				<?php endif; ?>
				<?php if ( $rbfw_enable_faq_content == 'yes' ) { ?>
                    <div class="faq" data-id="faq">
                        <h3 class="rbfw-sub-heading"><?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_faq', 'rbfw_basic_translation_settings', __( 'Frequently Asked Questions', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?></h3>
						<?php do_action( 'rbfw_the_faq_only', $post_id ); ?>
                    </div><!--end of tab three-->
				<?php } ?>
            </div>
        </div>
    </div>

	<?php if ( rbfw_check_pro_active() === true && $review_system == 'on' ) { ?>
        <div class="rbfw_muff_row_review_summary">
            <h3 class="rbfw_muff_heading">
				<?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_ratings', 'rbfw_basic_translation_settings', __( 'Ratings', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>
            </h3>
            <div class="rbfw_muff_row_review_inner">
                <div class="rbfw_muff_review_summ_col">
                    <div class="rbfw_muff_review_rating_wrap">
                        <div class="rbfw_muff_review_rating_number">
                                <span class="rbfw_muff_review_average_rating_number">
                                    <?php echo esc_html( $post_review_average ); ?>
                                </span>/5
                        </div>
                        <div class="rbfw_muff_review_rating_stars">
							<?php echo esc_html( $post_review_rating_style2 ); ?>
                        </div>
                    </div>
                </div>
                <div class="rbfw_muff_review_summ_col">
                    <div class="rbfw_muff_review_progress_item_wrapper">
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_hygenic', __( 'Hygenic', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_hygenic_progress_width ); ?>>
                                    </div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg">
									<?php echo esc_html( $post_review_value_round_hygenic ); ?>/5
                                </div>
                            </div>
                        </div>
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_quality', __( 'Quality', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_quality_progress_width ); ?>></div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg"><?php echo esc_html( $post_review_value_round_quality ); ?>/5</div>
                            </div>
                        </div>
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_cost_value', __( 'Cost Value', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_cost_value_progress_width ); ?>></div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg"><?php echo esc_attr( $post_review_value_round_cost_value ); ?>/5</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rbfw_muff_review_summ_col">
                    <div class="rbfw_muff_review_progress_item_wrapper">
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_staff', __( 'Staff', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_staff_progress_width ); ?>></div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg"><?php echo esc_html( $post_review_value_round_staff ); ?>/5</div>
                            </div>
                        </div>
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_facilities', __( 'Facilities', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_facilities_progress_width ); ?>></div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg"><?php echo esc_html( $post_review_value_round_facilities ); ?>/5</div>
                            </div>
                        </div>
                        <div class="rbfw_muff_review_progress_item">
                            <label><?php rbfw_string( 'rbfw_text_comfort', __( 'Comfort', 'booking-and-rental-manager-for-woocommerce' ) ); ?></label>
                            <div class="rbfw_muff_review_progress_inner_wrap">
                                <div class="rbfw_muff_review_progress_bar">
                                    <div class="rbfw_muff_review_progress_bar-green" <?php echo esc_attr( $post_comfort_progress_width ); ?>></div>
                                </div>
                                <div class="rbfw_muff_review_progress_bar_avg"><?php echo esc_html( $post_review_value_round_comfort ); ?>/5</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php } ?>

	<?php if ( rbfw_check_pro_active() === true && $review_system == 'on' ): ?>
        <div class="rbfw_muff_row_reviews">
            <div class="rbfw_muff_heading">
                <div class="rbfw_muff_heading_tab active" data-tab="tab1">
					<?php do_action( 'rbfw_muff_review_tab', $post_id ); ?>
                </div>
                <div class="rbfw_muff_review_write_btn_wrapper">
                    <button class="rbfw_muff_review_write_btn"><?php echo esc_html( $rbfw->get_option( 'rbfw_text_write_review', 'rbfw_basic_translation_settings', __( 'Write Review', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?></button>
                </div>
            </div>
            <div class="rbfw_muff_faq_tab_contents">
                <div class="rbfw_muff_faq_tab_content active" data-content="tab1">
					<?php do_action( 'rbfw_muff_review_content', $post_id ); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>


	<?php if ( ! empty( $rbfw_related_post_arr ) ): ?>
        <div class="rbfw_muff_row_related_item">
            <h3 class="rbfw_muff_heading">
				<?php echo esc_html( $rbfw->get_option_trans( 'rbfw_text_you_may_also_like', 'rbfw_basic_translation_settings', __( 'You May Also Like', 'booking-and-rental-manager-for-woocommerce' ) ) ); ?>
            </h3>
			<?php do_action( 'rbfw_related_products_style_three', $post_id ); ?>
        </div>
	<?php endif; ?>
</div>

