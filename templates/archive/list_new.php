<?php
	/*********************************
	 * Rent List Shortcode Grid Style
	 *********************************/
	global $rbfw;
	$post_id        = get_the_id();
	$post_title     = get_the_title();
	$gallery_images = get_post_meta( get_the_ID(), 'rbfw_gallery_images', true );
	if ( ! empty( $gallery_images ) ) {
		$gallery_image = wp_get_attachment_url( $gallery_images[0] );
	} else {
		$gallery_image = RBFW_PLUGIN_URL . '/assets/images/no_image.png';
	}
	$post_featured_img = ! empty( get_the_post_thumbnail_url( $post_id, 'full' ) ) ? get_the_post_thumbnail_url( $post_id,
		'full' ) : $gallery_image;
	$post_link         = get_the_permalink();
	$book_now_label    = __( 'Book Now', 'booking-and-rental-manager-for-woocommerce' );
	$rbfw_offday_range = get_post_meta( get_the_id(), 'rbfw_offday_range', true ) ? get_post_meta( get_the_id(), 'rbfw_offday_range', true ) : 'no';
	$continue          = false;
	if ( $rbfw_offday_range !== 'no' && ! empty( $pickup_date ) ) {
		foreach ( $rbfw_offday_range as $date_rang ) {
			$start_date    = $date_rang['from_date'];
			$end_date      = $date_rang['to_date'];
			$check_date    = $pickup_date;
			$startDateTime = DateTime::createFromFormat( 'd-m-Y', $start_date );
			$endDateTime   = DateTime::createFromFormat( 'd-m-Y', $end_date );
			$checkDateTime = DateTime::createFromFormat( 'd-m-Y', $check_date );
			if ( $checkDateTime >= $startDateTime && $checkDateTime <= $endDateTime ) {
				//error_log(print_r(['$continue' => $continue], true));
				$continue = true;
			}
		}
	}
	if ( ! $continue ) {

        $post_content = get_the_content();

        $prices_start_at = __('Prices start at', 'booking-and-rental-manager-for-woocommerce');
        $rbfw_rent_type = get_post_meta($post_id, 'rbfw_item_type', true);

        $pricing_display_for_listing = rbfw_get_option( 'pricing_display_for_listing', 'rbfw_basic_gen_settings' );

        $price = 0;

        if ($rbfw_rent_type == 'bike_car_md') {

            $hourly_rate_label = __('Hourly rate', 'booking-and-rental-manager-for-woocommerce');
            $daily_rate_label = __('Daily rate', 'booking-and-rental-manager-for-woocommerce');
            $weekly_rate_label = __('Weekly rate', 'booking-and-rental-manager-for-woocommerce');
            $monthly_rate_label = __('Monthly rate', 'booking-and-rental-manager-for-woocommerce');
            $rbfw_enable_hourly_rate = get_post_meta($post_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_enable_hourly_rate', true) : 'no';
            $rbfw_enable_daily_rate = get_post_meta(get_the_id(), 'rbfw_enable_daily_rate', true) ? get_post_meta(get_the_id(), 'rbfw_enable_daily_rate', true) : 'no';
            $rbfw_enable_weekly_rate = get_post_meta(get_the_id(), 'rbfw_enable_weekly_rate', true) ? get_post_meta(get_the_id(), 'rbfw_enable_weekly_rate', true) : 'no';
            $rbfw_enable_monthly_rate = get_post_meta(get_the_id(), 'rbfw_enable_monthly_rate', true) ? get_post_meta(get_the_id(), 'rbfw_enable_monthly_rate', true) : 'no';

            $rbfw_enable_time_picker = get_post_meta(get_the_id(), 'rbfw_enable_time_picker', true) ? get_post_meta(get_the_id(), 'rbfw_enable_time_picker', true) : 'no';


            if ($rbfw_enable_monthly_rate == 'yes' && $pricing_display_for_listing=='monthly') {
                $price_label = $monthly_rate_label;
                $price = get_post_meta($post_id, 'rbfw_monthly_rate', true) ? get_post_meta($post_id, 'rbfw_monthly_rate', true) : 0;
            } elseif($rbfw_enable_weekly_rate == 'yes' && $pricing_display_for_listing=='weekly') {
                $price_label = $weekly_rate_label;
                $price = get_post_meta($post_id, 'rbfw_weekly_rate', true) ? get_post_meta($post_id, 'rbfw_weekly_rate', true) : 0;
            }else{
                if($rbfw_enable_daily_rate == 'yes' && $pricing_display_for_listing=='daily') {
                    $price_label = $daily_rate_label;
                    $price = get_post_meta($post_id, 'rbfw_daily_rate', true) ? get_post_meta($post_id, 'rbfw_daily_rate', true) : 0;
                    $price_sun = get_post_meta($post_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sun_daily_rate', true) : 0;
                    $price_mon = get_post_meta($post_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($post_id, 'rbfw_mon_daily_rate', true) : 0;
                    $price_tue = get_post_meta($post_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($post_id, 'rbfw_tue_daily_rate', true) : 0;
                    $price_wed = get_post_meta($post_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($post_id, 'rbfw_wed_daily_rate', true) : 0;
                    $price_thu = get_post_meta($post_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($post_id, 'rbfw_thu_daily_rate', true) : 0;
                    $price_fri = get_post_meta($post_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($post_id, 'rbfw_fri_daily_rate', true) : 0;
                    $price_sat = get_post_meta($post_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sat_daily_rate', true) : 0;
                }elseif($rbfw_enable_time_picker == 'yes' && $rbfw_enable_hourly_rate == 'yes'){
                    $price_label = $hourly_rate_label;
                    $price = get_post_meta($post_id, 'rbfw_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_hourly_rate', true) : 0;
                    $price_sun = get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) : 0;
                    $price_mon = get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) : 0;
                    $price_tue = get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) : 0;
                    $price_wed = get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) : 0;
                    $price_thu = get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) : 0;
                    $price_fri = get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) : 0;
                    $price_sat = get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) : 0;
                }

                $price = (float)$price;
                $enabled_sun = get_post_meta($post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($post_id, 'rbfw_enable_sun_day', true) : 'yes';
                $enabled_mon = get_post_meta($post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($post_id, 'rbfw_enable_mon_day', true) : 'yes';
                $enabled_tue = get_post_meta($post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($post_id, 'rbfw_enable_tue_day', true) : 'yes';
                $enabled_wed = get_post_meta($post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($post_id, 'rbfw_enable_wed_day', true) : 'yes';
                $enabled_thu = get_post_meta($post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($post_id, 'rbfw_enable_thu_day', true) : 'yes';
                $enabled_fri = get_post_meta($post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($post_id, 'rbfw_enable_fri_day', true) : 'yes';
                $enabled_sat = get_post_meta($post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($post_id, 'rbfw_enable_sat_day', true) : 'yes';
                $current_day = gmdate('D');
                if ($current_day == 'Sun' && $enabled_sun == 'yes') {
                    $price = (float)$price_sun;
                } elseif ($current_day == 'Mon' && $enabled_mon == 'yes') {
                    $price = (float)$price_mon;
                } elseif ($current_day == 'Tue' && $enabled_tue == 'yes') {
                    $price = (float)$price_tue;
                } elseif ($current_day == 'Wed' && $enabled_wed == 'yes') {
                    $price = (float)$price_wed;
                } elseif ($current_day == 'Thu' && $enabled_thu == 'yes') {
                    $price = (float)$price_thu;
                } elseif ($current_day == 'Fri' && $enabled_fri == 'yes') {
                    $price = (float)$price_fri;
                } elseif ($current_day == 'Sat' && $enabled_sat == 'yes') {
                    $price = (float)$price_sat;
                } else {
                    $price = (float)$price;
                }
                $current_date = gmdate('Y-m-d');
                $rbfw_sp_prices = get_post_meta($post_id, 'rbfw_seasonal_prices', true);
                if (!empty($rbfw_sp_prices)) {
                    $sp_array = [];
                    $i = 0;
                    foreach ($rbfw_sp_prices as $value) {
                        $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
                        $rbfw_sp_end_date = $value['rbfw_sp_end_date'];
                        $rbfw_sp_price_h = $value['rbfw_sp_price_h'];
                        $rbfw_sp_price_d = $value['rbfw_sp_price_d'];
                        $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
                        $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
                        $sp_array[$i]['sp_daily_rate'] = $rbfw_sp_price_d;
                        $i++;
                    }
                    foreach ($sp_array as $sp_arr) {
                        if (in_array($current_date, $sp_arr['sp_dates'])) {
                            if ($rbfw_enable_hourly_rate == 'yes') {
                                $price = (float)$sp_arr['sp_hourly_rate'];
                            } else {
                                $price = (float)$sp_arr['sp_daily_rate'];
                            }
                        }
                    }
                }

                if($rbfw_enable_daily_rate=='yes' || $rbfw_enable_hourly_rate=='yes'){


                    if ($rbfw_enable_time_picker == 'yes' && $rbfw_enable_hourly_rate == 'yes' ) {
                        $price     = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
                        $price_sun = get_post_meta( $post_id, 'rbfw_sun_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_sun_hourly_rate', true ) : 0;
                        $price_mon = get_post_meta( $post_id, 'rbfw_mon_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_mon_hourly_rate', true ) : 0;
                        $price_tue = get_post_meta( $post_id, 'rbfw_tue_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_tue_hourly_rate', true ) : 0;
                        $price_wed = get_post_meta( $post_id, 'rbfw_wed_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_wed_hourly_rate', true ) : 0;
                        $price_thu = get_post_meta( $post_id, 'rbfw_thu_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_thu_hourly_rate', true ) : 0;
                        $price_fri = get_post_meta( $post_id, 'rbfw_fri_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_fri_hourly_rate', true ) : 0;
                        $price_sat = get_post_meta( $post_id, 'rbfw_sat_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_sat_hourly_rate', true ) : 0;
                    } else {
                        $price_label = $daily_rate_label;
                        $price     = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
                        $price_sun = get_post_meta( $post_id, 'rbfw_sun_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_sun_daily_rate', true ) : 0;
                        $price_mon = get_post_meta( $post_id, 'rbfw_mon_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_mon_daily_rate', true ) : 0;
                        $price_tue = get_post_meta( $post_id, 'rbfw_tue_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_tue_daily_rate', true ) : 0;
                        $price_wed = get_post_meta( $post_id, 'rbfw_wed_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_wed_daily_rate', true ) : 0;
                        $price_thu = get_post_meta( $post_id, 'rbfw_thu_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_thu_daily_rate', true ) : 0;
                        $price_fri = get_post_meta( $post_id, 'rbfw_fri_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_fri_daily_rate', true ) : 0;
                        $price_sat = get_post_meta( $post_id, 'rbfw_sat_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_sat_daily_rate', true ) : 0;
                    }
                    $price = (float) $price;
                    $enabled_sun = get_post_meta( $post_id, 'rbfw_enable_sun_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_sun_day', true ) : 'yes';
                    $enabled_mon = get_post_meta( $post_id, 'rbfw_enable_mon_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_mon_day', true ) : 'yes';
                    $enabled_tue = get_post_meta( $post_id, 'rbfw_enable_tue_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_tue_day', true ) : 'yes';
                    $enabled_wed = get_post_meta( $post_id, 'rbfw_enable_wed_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_wed_day', true ) : 'yes';
                    $enabled_thu = get_post_meta( $post_id, 'rbfw_enable_thu_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_thu_day', true ) : 'yes';
                    $enabled_fri = get_post_meta( $post_id, 'rbfw_enable_fri_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_fri_day', true ) : 'yes';
                    $enabled_sat = get_post_meta( $post_id, 'rbfw_enable_sat_day', true ) ? get_post_meta( $post_id, 'rbfw_enable_sat_day', true ) : 'yes';
                    $current_day = gmdate( 'D' );
                    if ( $current_day == 'Sun' && $enabled_sun == 'yes' ) {
                        $price = (float) $price_sun;
                    } elseif ( $current_day == 'Mon' && $enabled_mon == 'yes' ) {
                        $price = (float) $price_mon;
                    } elseif ( $current_day == 'Tue' && $enabled_tue == 'yes' ) {
                        $price = (float) $price_tue;
                    } elseif ( $current_day == 'Wed' && $enabled_wed == 'yes' ) {
                        $price = (float) $price_wed;
                    } elseif ( $current_day == 'Thu' && $enabled_thu == 'yes' ) {
                        $price = (float) $price_thu;
                    } elseif ( $current_day == 'Fri' && $enabled_fri == 'yes' ) {
                        $price = (float) $price_fri;
                    } elseif ( $current_day == 'Sat' && $enabled_sat == 'yes' ) {
                        $price = (float) $price_sat;
                    } else {
                        $price = (float) $price;
                    }
                    $current_date   = gmdate( 'Y-m-d' );
                    $rbfw_sp_prices = get_post_meta( $post_id, 'rbfw_seasonal_prices', true );
                    if ( ! empty( $rbfw_sp_prices ) ) {
                        $sp_array = [];
                        $i        = 0;
                        foreach ( $rbfw_sp_prices as $value ) {
                            $rbfw_sp_start_date               = $value['rbfw_sp_start_date'];
                            $rbfw_sp_end_date                 = $value['rbfw_sp_end_date'];
                            $rbfw_sp_price_h                  = $value['rbfw_sp_price_h'];
                            $rbfw_sp_price_d                  = $value['rbfw_sp_price_d'];
                            $sp_array[ $i ]['sp_dates']       = rbfw_getBetweenDates( $rbfw_sp_start_date, $rbfw_sp_end_date );
                            $sp_array[ $i ]['sp_hourly_rate'] = $rbfw_sp_price_h;
                            $sp_array[ $i ]['sp_daily_rate']  = $rbfw_sp_price_d;
                            $i ++;
                        }
                        foreach ( $sp_array as $sp_arr ) {
                            if ( in_array( $current_date, $sp_arr['sp_dates'] ) ) {
                                if ( $rbfw_enable_hourly_rate == 'yes' ) {
                                    $price = (float) $sp_arr['sp_hourly_rate'];
                                } else {
                                    $price = (float) $sp_arr['sp_daily_rate'];
                                }
                            }
                        }
                    }


                }else{

                    if($rbfw_enable_weekly_rate=='yes'){
                        $price_label = $weekly_rate_label;
                        $price = get_post_meta($post_id, 'rbfw_weekly_rate', true) ? get_post_meta($post_id, 'rbfw_weekly_rate', true) : 0;
                    }elseif ($rbfw_enable_monthly_rate=='yes'){
                        $price_label = $monthly_rate_label;
                        $price = get_post_meta($post_id, 'rbfw_monthly_rate', true) ? get_post_meta($post_id, 'rbfw_monthly_rate', true) : 0;
                    }

                }
            }
        } elseif ($rbfw_rent_type == 'resort') {
            $rbfw_room_data = get_post_meta($post_id, 'rbfw_resort_room_data', true);

            if (!empty($rbfw_room_data)) {
                $rbfw_daylong_rate = [];
                $rbfw_daynight_rate = [];
                foreach ($rbfw_room_data as $key => $value) {
                    if (!empty($value['rbfw_room_daylong_rate'])) {
                        $rbfw_daylong_rate[] = $value['rbfw_room_daylong_rate'];
                    }
                    if (!empty($value['rbfw_room_daynight_rate'])) {
                        $rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
                    }
                }
                $merged_arr = array_merge($rbfw_daylong_rate, $rbfw_daynight_rate);
                if (!empty($merged_arr)) {
                    $smallest_price = min($merged_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            }
            $price_label = $prices_start_at;
        } elseif ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'){
            $rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
            if (!empty($rbfw_bike_car_sd_data)){
                $rbfw_price_arr = [];
                foreach ($rbfw_bike_car_sd_data as $key => $value) {
                    if (!empty($value['price'])) {
                        $rbfw_price_arr[] = $value['price'];
                    }
                }
                if (!empty($rbfw_price_arr)) {
                    $smallest_price = min($rbfw_price_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            }
            $price_label = $prices_start_at;
        } else{

            $multiple_items_info           = get_post_meta( $post_id, 'multiple_items_info', true ) ? get_post_meta( $post_id, 'multiple_items_info', true ) : [];

            $result = findMinimumPrice($multiple_items_info,$pricing_display_for_listing);
            $price = $result['price'];
            $price_label = ($result['price_type']=='hourly_price')?'Hourly':(($result['price_type']=='daily_price')?'Daily':(($result['price_type']=='weekly_price')?'Weekly':'Monthly'));

        }

        $rbfw_feature_category = rbfw_get_feature_category_meta( $post_id );

        ?>
        <div class="rbfw_rent_list_col rbfw_grid_list_col_<?php echo esc_attr( $d ); ?>">
            <div class="rbfw_rent_list_inner_wrapper">
                <div class="<?php echo esc_attr( $image_holder ) ?>">
                    <a class="rbfw_rent_list_grid_view_top_img" href="<?php echo esc_url( $post_link ); ?>">
                        <img src="<?php echo esc_url( $post_featured_img ); ?>" alt="Catalog Image">
                    </a>
                </div>
                <div class="<?php echo esc_attr( $rent_item_info ) ?>">
                    <div class="rbfw_rent_list_content">
                        <div class="rbfw_rent_list_grid_title_wrapper">
                            <h2 class="rbfw_rent_list_grid_title">
                                <a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post_title ); ?></a>
                            </h2>
                            <?php if( !isset($rbfw_hide_price) || $rbfw_hide_price !== 'yes' ): ?>
                            <div class="rbfw_rent_list_grid_row rbfw_pricing-box">
                                <p class="rbfw_rent_list_row_price"><span class="prc currency_left"><?php echo wp_kses( wc_price( $price ) , rbfw_allowed_html()); ?></span></p>
                                <span class="rbfw_rent_list_row_price_level">/ <?php echo esc_html( $price_label ); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="rbfw_rent_item_description" id="rbfw_rent_item_description">
                            <p class="rbfw_rent_item_description_text" style="display: <?php echo esc_attr( $is_display ) ?>">
								<?php
									// Trim the content to 14 words
									$post_content = wp_trim_words( $post_content, 14, '...' );
									echo esc_html( $post_content )
								?>
                            </p>
                        </div>
                    </div>
                    <div class="rbfw_rent_item_bottom_info">
						<?php if ( $rbfw_feature_category ) :
							$n = 1;
							foreach ( $rbfw_feature_category as $value ) :
								$cat_title = $value['cat_title'];
								$cat_features = $value['cat_features'] ? $value['cat_features'] : [];
								if ( $n == 1 ) {
									?>
                                    <ul class="<?php echo esc_attr( $rent_item_list_info ) ?>">
										<?php
											if ( ! empty( $cat_features ) ) {
												$i = 1;
												foreach ( $cat_features as $features ) {
													if ( $i <= $display_cat_features ) {
														$icon        = ! empty( $features['icon'] ) ? $features['icon'] : 'fas fa-check-circle';
														$title       = $features['title'];
														$rand_number = wp_rand();
														if ( $title ) {
															?>
                                                            <li class="bfw_rent_list_items title <?php echo esc_attr( $rand_number ); ?>">
																<span class="bfw_rent_list_items_icon">
																	<i class="<?php echo esc_attr( $icon ); ?>"></i>
																</span>
																<?php echo esc_html( $title ); ?>
															</li>

															<?php
														}
													}
													$i ++;
												}
											}
										?>
										<?php if ( count( $cat_features ) > $display_cat_features ) { ?>
                                            <div class="rbfw_see_more_category" id="rbfw_see_more_category-<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html__( 'See more','booking-and-rental-manager-for-woocommerce' ) ?></div>
										<?php } ?>
                                    </ul>
									<?php
								}
								$n ++;
							endforeach;
						endif;
						?>
                        <div class="rbfw_rent_list_btn_holder">
                            <a class="rbfw_rent_list_link rbfw_rent_list_btn btn" href="<?php echo esc_url( $post_link ); ?>">
								<?php echo esc_html( $book_now_label ); ?>
                                <span class="button-icon">
                            <i class="fas fa-angle-double-right"></i>
                        </span>
                            </a>
                        </div>
                        <!-- /.rbfw_content_wrapper -->
                    </div>
                </div>
            </div>
        </div>
	<?php } ?>