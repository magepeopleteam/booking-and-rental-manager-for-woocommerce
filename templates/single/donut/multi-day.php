<?php
	// Template Name: Donut Bike Theme
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	global $rbfw;
	$post_id                   = $post_id ?? 0;
	$rbfw_id                   = $post_id;
	$post_title                = get_the_title();
	$post_content              = get_the_content();
	$rbfw_feature_category     = get_post_meta( $post_id, 'rbfw_feature_category', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_feature_category', true ) ) : [];
	$rbfw_enable_faq_content   = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
	$slide_style               = $rbfw->get_option_trans( 'super_slider_style', 'super_slider_settings', '' );
	$post_review_rating        = function_exists( 'rbfw_review_display_average_rating' ) ? rbfw_review_display_average_rating() : '';
	$currency_symbol           = get_woocommerce_currency_symbol();
	$get_hourly_price          = rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'hourly' );
	$get_daily_price           = rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'daily' );
	$enable_daily_rate         = get_post_meta( $rbfw_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_daily_rate', true ) : 'yes';
	$enable_hourly_rate        = get_post_meta( $rbfw_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_hourly_rate', true ) : 'no';
	$rbfw_enable_daywise_price = get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_daywise_price', true ) : 'no';
	$rbfw_related_post_arr     = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ) : [];
	$rbfw_dt_sidebar_switch    = get_post_meta( $post_id, 'rbfw_dt_sidebar_switch', true ) ? get_post_meta( $post_id, 'rbfw_dt_sidebar_switch', true ) : 'off';
	$rbfw_dt_sidebar_content   = get_post_meta( $post_id, 'rbfw_dt_sidebar_content', true );
?>
<div class="rbfw_donut_template">
    <div class="rbfw_dt_row_header">
        <div class="rbfw_dt_header_col1">
            <div class="rbfw_dt_rating">
				<?php if ( ! empty( $post_review_rating ) ): ?>
                    <div class="rbfw_rent_list_average_rating">
                        <?php echo wp_kses( $post_review_rating , rbfw_allowed_html() ); ?>
                    </div>
				<?php endif; ?>
            </div>
            <div class="rbfw_dt_title">
                <h1><?php echo esc_html( $post_title ); ?></h1>
            </div>
        </div>
        <div class="rbfw_dt_header_col2">
            <div class="rbfw_dt_pricing">
                <div class="rbfw_dt_pricing_card">
                    <div class="rbfw_dt_pricing_card_col1"><?php echo esc_html( $currency_symbol ); ?></div>
                    <div class="rbfw_dt_pricing_card_col2">
						<?php if ( ( $enable_daily_rate == 'yes' || $rbfw_enable_daywise_price == 'yes' ) && ! empty( $get_daily_price ) ) : ?>
                            <div class="rbfw_dt_pricing_card_price"><?php echo esc_html( $get_daily_price ); ?><span> / <?php echo esc_html( __( 'PER DAY', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span></div>
						<?php endif; ?>

						<?php if ( ( $enable_hourly_rate == 'yes' || $rbfw_enable_daywise_price == 'yes' ) && ! empty( $get_hourly_price ) ) : ?>
                            <div class="rbfw_dt_pricing_card_price"><?php echo esc_html( $get_hourly_price ); ?><span> / <?php echo esc_html( __( 'PER HOUR', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span></div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rbfw_dt_row_content">
        <div class="rbfw_dt_content_col1">
            <div class="rbfw_dt_slider mpStyle <?php echo esc_attr( $slide_style ); ?>">
				<?php do_action( 'rbfw_slider', $post_id, 'rbfw_gallery_images' ); ?>
            </div>
        </div>
        <div class="rbfw_dt_content_col2">
            <div class="rbfw_dt_post_content">
				<?php echo wp_kses( $post_content , rbfw_allowed_html() ); ?>
            </div>
            <div class="rbfw_dt_highlighted_features">
				<?php if ( $rbfw_feature_category ) :
					foreach ( $rbfw_feature_category as $value ) :
						$cat_title = $value['cat_title'];
						$cat_features = $value['cat_features'] ? $value['cat_features'] : [];
						?>
                        <h3 class="rbfw-sub-heading"><?php echo esc_html( $cat_title ); ?></h3>
                        <ul>
							<?php
								if ( ! empty( $cat_features ) ):
									$i = 1;
									foreach ( $cat_features as $features ):
										$icon = ! empty( $features['icon'] ) ? $features['icon'] : 'fas fa-check-circle';
										$title = $features['title'];
										if ( $title ):?>
                                            <li style="<?php echo ( $i > 4 ) ? 'display:none' : '' ?>" data-status="<?php echo ( $i > 4 ) ? 'extra' : '' ?>">
                                                <i class="<?php echo esc_attr( $icon ); ?>"></i><span><?php echo esc_html( $title ); ?></span>
                                            </li>
										<?php
										endif;
										$i ++;
									endforeach;
								endif;
							?>
                            <li style="width:100%">
                                <a class="rbfw_muff_lmf_btn">
									<?php echo esc_html( __( 'Load More', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </a>
                            </li>
                        </ul>
					<?php
					endforeach;
				endif;
				?>
            </div>
        </div>
    </div>
    <div class="rbfw_dt_row_registration <?php if ( $rbfw_dt_sidebar_switch == 'on' ) {
		echo 'rbfw_dt_sidebar_enabled';
	} ?>">
		<?php if ( $rbfw_dt_sidebar_switch == 'on' ): ?>
            <div class="rbfw_dt_registration_col1">
				<?php do_action( 'rbfw_dt_testimonial', $post_id ); ?>
				<?php echo wp_kses( html_entity_decode( $rbfw_dt_sidebar_content ) , rbfw_allowed_html()); ?>
            </div>
		<?php endif; ?>
        <div class="rbfw_dt_registration_col2">
            <div class="rbfw_dt_heading"><?php echo esc_html( __( 'Booking Detail', 'booking-and-rental-manager-for-woocommerce' ) ); ?></div>
			<?php include( RBFW_Function::get_template_path( 'forms/multi-day-registration.php' ) ); ?>
        </div>
    </div>
	<?php if ( $rbfw_enable_faq_content == 'yes' ): ?>
        <div class="rbfw_dt_row_faq">
            <div class="rbfw_dt_heading">
                <div class="rbfw_dt_heading_tab active" data-tab="tab1">
					<?php echo esc_html( __( 'Freequently Asked Questions', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
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

	<?php if ( ! empty( $rbfw_related_post_arr ) ): ?>
        <div class="rbfw_dt_row_related_item">
            <h3 class="rbfw_dt_heading"><?php echo esc_html( __( 'You May Also Like', 'booking-and-rental-manager-for-woocommerce' ) ); ?></h3>
			<?php do_action( 'rbfw_related_products_style_two', $post_id ); ?>
        </div>
	<?php endif; ?>
</div>