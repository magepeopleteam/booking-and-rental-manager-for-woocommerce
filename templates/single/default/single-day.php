<?php
// Template Name: Bike Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
global $rbfw;
$post_id = $post_id??0;

$rbfw_feature_category = rbfw_get_feature_category_meta( $post_id );
$tab_style = $rbfw->get_option_trans('rbfw_single_rent_tab_style', 'rbfw_basic_single_rent_page_settings','horizontal');
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option_trans('super_slider_style', 'super_slider_settings','');

$post_title = get_the_title();
$rbfw_default_hero_price = 0;
$_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
if ( ! empty( $_sd_data ) ) {
    $_prices = array_filter( array_column( $_sd_data, 'price' ) );
    if ( ! empty( $_prices ) ) {
        $rbfw_default_hero_price = (float) min( $_prices );
    }
}
if ( ! $rbfw_default_hero_price ) {
    $_daily  = (float) rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'daily' );
    $_hourly = (float) rbfw_get_bike_car_md_hourly_daily_price( $post_id, 'hourly' );
    $rbfw_default_hero_price = $_daily ?: $_hourly;
}
$rbfw_default_hero_features = [];
if ( ! empty( $rbfw_feature_category ) ) {
    foreach ( $rbfw_feature_category as $_cat ) {
        if ( ! empty( $_cat['cat_features'] ) ) {
            foreach ( $_cat['cat_features'] as $_feat ) {
                if ( count( $rbfw_default_hero_features ) >= 3 ) break 2;
                $rbfw_default_hero_features[] = [
                    'icon'  => ! empty( $_feat['icon'] ) ? $_feat['icon'] : 'fas fa-check-circle',
                    'label' => ! empty( $_feat['title'] ) ? $_feat['title'] : '',
                ];
            }
        }
    }
}
$rbfw_default_hero_subtitle = get_post_meta( $post_id, 'rbfw_item_sub_title', true );
?>
	<div class="mp_default_theme">
		<div class="mpContainer">
			<div class="mp_details_page">
                <?php  if(!is_admin()){ ?>
				<div class="mp_left_section">
					<div class="rbfw_default_hero_wrap mpStyle <?php echo esc_attr( $slide_style ); ?>">
						<?php do_action( 'rbfw_slider', $post_id, 'rbfw_gallery_images' ); ?>
						<div class="rbfw_default_hero_overlay">
							<div class="rbfw_default_hero_content">
								<div class="rbfw_default_hero_badge">
									<i class="fas fa-star"></i>
									<?php esc_html_e( 'Best Seller', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</div>
								<h1 class="rbfw_default_hero_title"><?php echo esc_html( $post_title ); ?></h1>
								<?php if ( ! empty( $rbfw_default_hero_subtitle ) ) : ?>
								<p class="rbfw_default_hero_desc"><?php echo esc_html( $rbfw_default_hero_subtitle ); ?></p>
								<?php endif; ?>
								<?php if ( $rbfw_default_hero_price > 0 ) : ?>
								<div class="rbfw_default_hero_price_wrap">
									<div class="rbfw_default_hero_price_label"><?php esc_html_e( 'Prices Start At', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw_default_hero_price_amount"><?php echo wp_kses( wc_price( $rbfw_default_hero_price ), rbfw_allowed_html() ); ?></div>
								</div>
								<?php endif; ?>
								<a href="#rbfw_default_booking_form" class="rbfw_default_hero_book_btn">
									<?php esc_html_e( 'Book Now', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<i class="fas fa-chevron-right"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="rbfw-single-left-container">
						<div class="rbfw-single-left-information">
						<div class="rbfw-header-container">
							
							<?php do_action( 'rbfw_product_meta', $post_id ); ?>
							
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
									<h3 class="rbfw-sub-heading">

                                        <?php
                                        if($rbfw->get_option_trans('rbfw_text_description', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                            echo esc_html($rbfw->get_option_trans('rbfw_text_description', 'rbfw_basic_translation_settings'));
                                        }else{
                                            echo esc_html__('Description','booking-and-rental-manager-for-woocommerce');
                                        }
                                        ?>


                                    </h3>
									<?php the_content(); ?>
								</div><!--end of tab two-->

								<?php if(!empty($rbfw_enable_faq_content) && $rbfw_enable_faq_content == 'yes'): ?>
								<div class="faq" data-id="faq">
									<h3 class="rbfw-sub-heading">

                                        <?php
                                        if($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                            echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings'));
                                        }else{
                                            echo esc_html__('Frequently Asked Questions','booking-and-rental-manager-for-woocommerce');
                                        }
                                        ?>

                                    </h3>
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
				<div class="mp_right_section">
					<?php do_action('booking_form_header',$post_id); ?>
					<div class="rbfw-booking-form single-day-booking" id="rbfw_default_booking_form">
                    	<?php include( RBFW_Function::get_template_path( 'forms/single-day-registration.php' ) ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

