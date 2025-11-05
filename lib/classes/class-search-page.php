<?php
	/*
	* Author 	:	MagePeople Team
	* Copyright	: 	mage-people.com
	* Developer :   Ariful
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( ! class_exists( 'Rbfw_Search_Page' ) ) {
		class Rbfw_Search_Page {
			public function __construct() {
				add_action( 'wp_loaded', array( $this, 'rbfw_search_page' ) );
				add_shortcode( 'rbfw_search_old', array( $this, 'rbfw_search_shortcode_func' ) );
				add_filter( 'display_post_states', array( $this, 'rbfw_add_post_state' ), 10, 2 );

				add_action( 'wp_ajax_rbfw_get_rent_item_category_info', array( $this, 'rbfw_get_rent_item_category_info' ) );
				add_action( 'wp_ajax_nopriv_rbfw_get_rent_item_category_info', array( $this, 'rbfw_get_rent_item_category_info' ) );

                //left Filter popup
				add_action( 'wp_ajax_rbfw_get_rent_item_left_filter_more_data_popup', array( $this, 'rbfw_get_rent_item_left_filter_more_data_popup' ) );
				add_action( 'wp_ajax_nopriv_rbfw_get_rent_item_left_filter_more_data_popup', array( $this, 'rbfw_get_rent_item_left_filter_more_data_popup' ) );
				//Left side filter
				add_action( 'wp_ajax_rbfw_get_left_side_filter_data', array( $this, 'rbfw_get_left_side_filter_data' ) );
				add_action( 'wp_ajax_nopriv_rbfw_get_left_side_filter_data', array( $this, 'rbfw_get_left_side_filter_data' ) );
			}

			public function rbfw_search_page() {
				$search_page_id = rbfw_get_option( 'rbfw_search_page', 'rbfw_basic_gen_settings' );
				if ( $search_page_id ) {
					if ( empty( get_post_meta( $search_page_id, 'rbfw_search_page', true ) ) ) {
						$args = array(
							'ID'           => $search_page_id,
							'post_content' => '[rbfw_search]'
						);
						wp_update_post( $args );
						update_post_meta( $search_page_id, 'rbfw_search_page', 'generated' );
					} else {
						return; //do nothing
					}
				} else {
					$page_obj = rbfw_exist_page_by_title( 'Rental Search' );
					if ( $page_obj === false ) {
						$args    = array(
							'post_title'   => 'Rental Search',
							'post_content' => '[rbfw_search]',
							'post_status'  => 'publish',
							'post_type'    => 'page'
						);
						$post_id = wp_insert_post( $args );
						if ( $post_id ) {
							$gen_settings     = ! empty( get_option( 'rbfw_basic_gen_settings' ) ) ? get_option( 'rbfw_basic_gen_settings' ) : [];
							$new_gen_settings = array_merge( $gen_settings, [ 'rbfw_search_page' => $post_id ] );
							update_option( 'rbfw_basic_gen_settings', $new_gen_settings );
							update_post_meta( $post_id, 'rbfw_search_page', 'generated' );
						}
					}
				}
			}

			function rbfw_add_post_state( $post_states, $post ) {
				$search_page_id = rbfw_get_option( 'rbfw_search_page', 'rbfw_basic_gen_settings' );
				if ( ! empty( $search_page_id ) ) {
					if ( $post->ID == $search_page_id ) {
						$post_states[] = 'Search Page';
					}
				}

				return $post_states;
			}

			public function rbfw_search_shortcode_func() {

                if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }

				ob_start();
				$search_page_id  = rbfw_get_option( 'rbfw_search_page', 'rbfw_basic_gen_settings' );
				$current_page_id = get_queried_object_id();
				if ( ! isset( $search_page_id ) ) {
					return;
				}
				if ( $current_page_id != $search_page_id ) {
					return;
				}
				if ( isset( $_GET['rbfw_search_submit'] ) && ! empty( $_GET['rbfw_search_location'] ) ) {
					$location = sanitize_text_field( wp_unslash($_GET['rbfw_search_location']));
					$atts     = array(
						'location' => $location
					);
					rbfw_rent_search_shortcode_func();
					echo wp_kses_post( rbfw_rent_list_shortcode_func( $atts ) );
				} else {
					rbfw_rent_search_shortcode_func();
				}
				$content = ob_get_clean();

				return $content;
			}

			public function rbfw_get_rent_item_category_info() {
                /*if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
                    return;
                }*/




                check_ajax_referer( 'rbfw_get_rent_item_category_info_action', 'nonce' );

				$all_cat_features = '';
				if ( isset( $_POST['post_id'] ) ) {
					$post_id               = sanitize_text_field( wp_unslash($_POST['post_id']));
					$rbfw_feature_category = get_post_meta( $post_id, 'rbfw_feature_category', true ) ? maybe_unserialize( get_post_meta( $post_id,
						'rbfw_feature_category', true ) ) : [];
					$all_cat_features      = '';
					$all_cat_features      .= '<div class="rbfw_show_all_cat_features rbfw_show_all_cat_title" id="rbfw_show_all_cat_features-' . $post_id . '"> ';
					foreach ( $rbfw_feature_category as $value ) {
						$cat_features     = $value['cat_features'] ? $value['cat_features'] : [];
						$cat_title        = $value['cat_title'];
						$all_cat_features .= '<h2 class="rbfw_popup_fearure_title rbfw_popup_fearure_title_color">' . $cat_title . '</h2>';
						if ( ! empty( $cat_features ) ) {
							$all_cat_features .= '<ul class="rbfw_popup_fearure_lists">';
							foreach ( $cat_features as $features ) {
								$icon        = ! empty( $features['icon'] ) ? $features['icon'] : 'fas fa-check-circle';
								$title       = $features['title'];
								$rand_number = wp_rand();
								if ( $title ) {
									$icom             = esc_html( $icon );
									$all_cat_features .= "<li class='bfw_rent_list_items title  $rand_number '><span class='bfw_rent_list_items_icon'><i class='$icom'></i></span>  $title </li>";
								}
							}
							$all_cat_features .= '</ul>';
						}
					}
					$all_cat_features .= '</div>';
				}
				wp_send_json_success( $all_cat_features );
			}

			public function rbfw_get_left_side_filter_data() {

                check_ajax_referer( 'rbfw_get_left_side_filter_data_action', 'nonce' );


                $response    = '';
				$show_result = '0 result of total 0';

					if ( isset( $_POST['filter_date'] ) ) {
						$filter_date_str     = sanitize_text_field( stripslashes( $_POST['filter_date' ] ) );
                        $filter_date = json_decode( $filter_date_str, true);
						$item_style      = isset( $_POST['rbfw_item_style'] ) ? sanitize_text_field(wp_unslash( $_POST['rbfw_item_style']) ) : '';
						$text_search     = isset( $filter_date['title_text'] ) ? sanitize_text_field( $filter_date['title_text'] ) : '';
						$search_by_title = '';
						if ( ! empty( $text_search ) ) {
							$search_by_title = $text_search;
						}
						$filter_by_price = isset( $filter_date['price'] ) ? $filter_date['price'] : [];

						if ( is_array( $filter_by_price ) && count( $filter_by_price ) > 0 ) {
                            $start_price        = sanitize_text_field( $filter_by_price['start'] );
                            $end_price          = sanitize_text_field( $filter_by_price['end'] );
                            $price_filter_query = array(
                                'key'     => 'rbfw_hourly_rate',
                                'value'   => array( $start_price, $end_price ),
                                'type'    => 'NUMERIC',
                                'compare' => 'BETWEEN',
                            );

						} else {
							$price_filter_query = '';
						}

						$features_to_search   = isset( $filter_date['feature'] ) ? $filter_date['feature'] : [];
						$feature_meta_queries = '';
						if ( is_array( $features_to_search ) && count( $features_to_search ) > 0 ) {
							$feature_meta_queries = array( 'relation' => 'OR' ); // Relation set to 'OR' so it matches any of the feature titles
							foreach ( $features_to_search as $feature ) {
								$feature_meta_queries[] = array(
									'key'     => 'rbfw_feature_category',
									'value'   => sanitize_text_field( $feature ),
									'compare' => 'LIKE', // Use LIKE because the value is part of a serialized array
								);
							}
						}
						$rent_types = isset( $filter_date['type'] ) ? $filter_date['type'] : [];
						$rent_type  = '';
						if ( is_array( $rent_types ) && count( $rent_types ) > 0 ) {
							$rent_type = array( 'relation' => 'OR' );
							foreach ( $rent_types as $type ) {
								$rent_type[] = ! empty( $type ) ? array(
									'key'     => 'rbfw_item_type',
									'value'   => sanitize_text_field( $type ),
									'compare' => '==',
								) : '';
							}
						}
						$rent_locations = isset( $filter_date['location'] ) ? $filter_date['location'] : [];
						if ( is_array( $rent_locations ) && count( $rent_locations ) > 0 ) {
							$location_query = array( 'relation' => 'OR' );
							foreach ( $rent_locations as $location ) {
								$location_query[] = ! empty( $location ) ? array(
									'key'     => 'rbfw_pickup_data',
									'value'   => sanitize_text_field( $location ),
									'compare' => 'LIKE'
								) : '';
							}
						} else {
							$location_query = '';
						}
						$rent_categories = isset( $filter_date['category'] ) ? $filter_date['category'] : [];
						if ( is_array( $rent_categories ) && count( $rent_categories ) > 0 ) {
							$category_query = array( 'relation' => 'OR' );
							foreach ( $rent_categories as $category_name ) {
								$category_query[] = ! empty( $category_name ) ? array(
									'key'     => 'rbfw_categories',
									'value'   => sanitize_text_field( $category_name ),
									'compare' => 'LIKE'
								) : '';
							}
						} else {
							$category_query = '';
						}
						$posts_per_page = 100;
						$number_of_page = 1;
						$args           = array(
							'post_type'      => 'rbfw_item',
							's'              => $search_by_title,
							'meta_query'     => array(
								'relation' => 'OR',
								$price_filter_query,
								$feature_meta_queries,
								$rent_type,
								$location_query,
								$category_query,
							),
							'orderby'        => 'ID',
							'order'          => 'DESC',
							'paged'          => $number_of_page,
							'posts_per_page' => $posts_per_page,
						);
						$query          = new WP_Query( $args );
						$total_posts    = $query->found_posts;
						$post_count     = $query->post_count;
						$d              = 1;
						$post_ids       = [];
						if ( $query->have_posts() ) {
							while ( $query->have_posts() ) {
								$query->the_post();
								$post_ids[] = get_the_ID();
								$response   .= $this->display_filter_rent_items( get_the_ID(), get_the_title(), get_the_content(), $item_style, $d );
								$d ++;
							}
							wp_reset_postdata();
						}

                        global $rbfw;

                        $show_result = $total_posts;
                        $show_result .= (
                        ( $rbfw->get_option_trans( 'rbfw_text_results', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_results', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'results', 'booking-and-rental-manager-for-woocommerce' )
                        );
                        $show_result .= (
                        ( $rbfw->get_option_trans( 'rbfw_text_showings', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_showings', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'Showing', 'booking-and-rental-manager-for-woocommerce' )
                        );
                        $show_result .= $post_count;
                        $show_result .= (
                        ( $rbfw->get_option_trans( 'rbfw_text_of', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_of', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'of', 'booking-and-rental-manager-for-woocommerce' )
                        );
                        $show_result .=  $total_posts ;
                        $show_result .= (
                        ( $rbfw->get_option_trans( 'rbfw_text_of', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_of', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'of', 'booking-and-rental-manager-for-woocommerce' )
                        );
                        $show_result .= (
                        ( $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                            ? esc_html( $rbfw->get_option_trans( 'rbfw_text_total', 'rbfw_basic_translation_settings' ) )
                            : esc_html__( 'total', 'booking-and-rental-manager-for-woocommerce' )
                        );
					}

				$result = array(
					'display_date' => $response,
					'show_text'    => $show_result,
				);
				wp_send_json_success( $result );
			}

			public function display_filter_rent_items( $post_id, $post_title, $the_content, $style, $d ) {
				global $rbfw;
				$post_featured_img = ! empty( get_the_post_thumbnail_url( $post_id, 'full' ) ) ? get_the_post_thumbnail_url( $post_id,
					'full' ) : RBFW_PLUGIN_URL . '/assets/images/no_image.png';
				$post_link         = get_the_permalink();
				$book_now_label    = (
                ( $rbfw->get_option_trans( 'rbfw_text_book_now', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                    ? esc_html( $rbfw->get_option_trans( 'rbfw_text_book_now', 'rbfw_basic_translation_settings' ) )
                    : esc_html__( 'Book Now', 'booking-and-rental-manager-for-woocommerce' )
                );
				$rbfw_offday_range = get_post_meta( $post_id, 'rbfw_offday_range', true ) ? get_post_meta( $post_id, 'rbfw_offday_range', true ) : 'no';
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
							$continue = true;
						}
					}
				}
				$hourly_rate_label       = ($rbfw->get_option_trans( 'rbfw_text_hourly_rate', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                    ? esc_html( $rbfw->get_option_trans( 'rbfw_text_hourly_rate', 'rbfw_basic_translation_settings' ) )
                    : esc_html__( 'Hourly rate', 'booking-and-rental-manager-for-woocommerce' );
				$daily_rate_label        = ($rbfw->get_option_trans( 'rbfw_text_daily_rate', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                    ? esc_html( $rbfw->get_option_trans( 'rbfw_text_daily_rate', 'rbfw_basic_translation_settings' ) )
                    : esc_html__( 'Daily rate', 'booking-and-rental-manager-for-woocommerce' );
				$rbfw_enable_hourly_rate = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate',
					true ) : 'no';
				$rbfw_enable_daily_rate  = get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_daily_rate',
					true ) : 'no';
				$post_content            = $the_content;
				if ( $rbfw_enable_hourly_rate == 'no' ) {
					$the_price_label = $daily_rate_label;
				} else {
					$the_price_label = $hourly_rate_label;
				}
				$prices_start_at = ($rbfw->get_option_trans( 'rbfw_text_prices_start_at', 'rbfw_basic_translation_settings' ) && want_loco_translate() == 'no' )
                    ? esc_html( $rbfw->get_option_trans( 'rbfw_text_prices_start_at', 'rbfw_basic_translation_settings' ) )
                    : esc_html__( 'Prices start at', 'booking-and-rental-manager-for-woocommerce' );
				$rbfw_rent_type  = get_post_meta( $post_id, 'rbfw_item_type', true );
				if ( $rbfw_enable_hourly_rate == 'yes' ) {
					$price     = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
					$price_sun = get_post_meta( $post_id, 'rbfw_sun_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_sun_hourly_rate', true ) : 0;
					$price_mon = get_post_meta( $post_id, 'rbfw_mon_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_mon_hourly_rate', true ) : 0;
					$price_tue = get_post_meta( $post_id, 'rbfw_tue_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_tue_hourly_rate', true ) : 0;
					$price_wed = get_post_meta( $post_id, 'rbfw_wed_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_wed_hourly_rate', true ) : 0;
					$price_thu = get_post_meta( $post_id, 'rbfw_thu_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_thu_hourly_rate', true ) : 0;
					$price_fri = get_post_meta( $post_id, 'rbfw_fri_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_fri_hourly_rate', true ) : 0;
					$price_sat = get_post_meta( $post_id, 'rbfw_sat_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_sat_hourly_rate', true ) : 0;
				} else {
					$price     = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
					$price_sun = get_post_meta( $post_id, 'rbfw_sun_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_sun_daily_rate', true ) : 0;
					$price_mon = get_post_meta( $post_id, 'rbfw_mon_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_mon_daily_rate', true ) : 0;
					$price_tue = get_post_meta( $post_id, 'rbfw_tue_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_tue_daily_rate', true ) : 0;
					$price_wed = get_post_meta( $post_id, 'rbfw_wed_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_wed_daily_rate', true ) : 0;
					$price_thu = get_post_meta( $post_id, 'rbfw_thu_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_thu_daily_rate', true ) : 0;
					$price_fri = get_post_meta( $post_id, 'rbfw_fri_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_fri_daily_rate', true ) : 0;
					$price_sat = get_post_meta( $post_id, 'rbfw_sat_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_sat_daily_rate', true ) : 0;
				}
				$price       = (float) $price;
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
				/* Resort Type */
				$rbfw_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
				if ( ! empty( $rbfw_room_data ) && $rbfw_rent_type == 'resort' ):
					$rbfw_daylong_rate  = [];
					$rbfw_daynight_rate = [];
					foreach ( $rbfw_room_data as $key => $value ) {
						if ( ! empty( $value['rbfw_room_daylong_rate'] ) ) {
							$rbfw_daylong_rate[] = $value['rbfw_room_daylong_rate'];
						}
						if ( ! empty( $value['rbfw_room_daynight_rate'] ) ) {
							$rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
						}
					}
					$merged_arr = array_merge( $rbfw_daylong_rate, $rbfw_daynight_rate );
					if ( ! empty( $merged_arr ) ) {
						$smallest_price = min( $merged_arr );
						$smallest_price = (float) $smallest_price;
					} else {
						$smallest_price = 0;
					}
					$price = $smallest_price;
				endif;
				/* Single Day/Appointment Type */
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
				$rbfw_feature_category = get_post_meta( $post_id, 'rbfw_feature_category', true ) ? maybe_unserialize( get_post_meta( $post_id,
					'rbfw_feature_category', true ) ) : [];
				if ( $rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment' ) {
					$price_level = $the_price_label;
				} elseif ( $rbfw_rent_type == 'resort' && ! empty( $rbfw_room_data ) ) {
					$price_level = $prices_start_at;
				} else {
					$price_level = $prices_start_at;
				}
				if ( isset( $_COOKIE['rbfw_rent_item_list_grid'] ) ) {
					$rbfw_rent_item_list_grid = sanitize_text_field( wp_unslash($_COOKIE['rbfw_rent_item_list_grid']));
				} else {
					$rbfw_rent_item_list_grid = '';
				}
				if ( $rbfw_rent_item_list_grid === '' ) {
					if ( $style == 'grid' ) {
						$image_holder         = 'rbfw_rent_list_grid_view_top';
						$rent_item_info       = 'rbfw_inner_details';
						$rent_item_list_info  = 'rbfw_rent_list_info';
						$is_display           = 'none';
						$display_cat_features = 3;
					} else {
						$image_holder         = 'rbfw_rent_list_lists_images';
						$rent_item_info       = 'rbfw_rent_list_lists_info';
						$rent_item_list_info  = 'rbfw_rent_item_content_list_bottom';
						$is_display           = 'grid';
						$display_cat_features = 5;
					}
				} else {
					if ( $rbfw_rent_item_list_grid == 'rbfw_rent_item_grid' ) {
						$image_holder         = 'rbfw_rent_list_grid_view_top';
						$rent_item_info       = 'rbfw_inner_details';
						$rent_item_list_info  = 'rbfw_rent_list_info';
						$is_display           = 'none';
						$display_cat_features = 3;
					} else {
						$image_holder         = 'rbfw_rent_list_lists_images';
						$rent_item_info       = 'rbfw_rent_list_lists_info';
						$rent_item_list_info  = 'rbfw_rent_item_content_list_bottom';
						$is_display           = 'grid';
						$display_cat_features = 5;
					}
				}
				ob_start()
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
                                    <div class="rbfw_rent_list_grid_row rbfw_pricing-box">
                                        <p class="rbfw_rent_list_row_price"><span class="prc currency_left"><?php echo wp_kses( wc_price( $price ) , rbfw_allowed_html()); ?></span></p>
                                        <span class="rbfw_rent_list_row_price_level">/ <?php echo esc_html( $price_level ); ?></span>
                                    </div>
                                </div>
                                <div class="rbfw_rent_item_description" id="rbfw_rent_item_description">
                                    <p class="rbfw_rent_item_description_text" style="display: <?php echo esc_attr( $is_display ) ?>">
										<?php
											// Trim the content to 14 words
											$post_content = wp_trim_words( $post_content, 14, '...' );
											echo wp_kses_post( $post_content )
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
                                                                    <li class="bfw_rent_list_items title <?php echo esc_attr( $rand_number ); ?>"><span class="bfw_rent_list_items_icon"><i class="<?php echo esc_html( $icon ); ?>"></i></span> <?php echo esc_html( $title ); ?></li>
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
                                <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g
                                        id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                                                       stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path
                                            d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="#000000" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path> </g>
                                </svg>
                            </span>
                                    </a>
                                </div>
                                <!-- /.rbfw_content_wrapper -->
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			}

			public function rbfw_get_rent_item_left_filter_more_data_popup() {

                check_ajax_referer( 'rbfw_get_rent_item_left_filter_more_data_popup_action', 'nonce' );

				$content = '';

					if ( isset( $_POST['filter_type'] ) ) {
						$filter_type = trim( sanitize_text_field( wp_unslash($_POST['filter_type'] )) );
						if ( $filter_type === 'rbfw_left_filter_location' ) {
							$rbfw_features   = get_rbfw_pickup_data_wp_query();
							$type_text       = 'Pickup Location';
							$check_box_class = 'rbfw_location';
						} else if ( $filter_type === 'rbfw_left_filter_category' ) {
							$rbfw_features   = get_rbfw_post_categories_from_meta();
							$type_text       = 'Item Category';
							$check_box_class = 'rbfw_category';
						} else if ( $filter_type === 'rbfw_left_filter_feature' ) {
							$rbfw_features   = get_rbfw_post_features_from_meta();
							$type_text       = 'Item Features';
							$check_box_class = 'rbfw_rent_feature';
						} else {
							$rbfw_features   = [];
							$type_text       = '';
							$check_box_class = '';
						}
						ob_start();
						?>
                        <div class="rbfw_rent_item_fearture_holder">
                            <h5 class="rbfw_toggle-header rbfw_white_color"><?php echo esc_html( $type_text ); ?></h5>
                            <div class="rbfw_toggle-content rbfw_toggle_container">
								<?php
									if ( $filter_type === 'rbfw_left_filter_feature' ) {
										foreach ( $rbfw_features as $features ) {
											if ( ! empty( $features['title'] ) ) {
												?>
                                                <div class="rbfw_types">
                                                    <input type="checkbox" class="<?php echo esc_attr( $check_box_class ) ?>" value="<?php echo esc_attr( $features['title'] ) ?>"><span class="rbfw_rent_item_feature_des_text"> <?php echo esc_attr( $features['title'] ) ?></span></div>
											<?php }
										}
									} else if ( $filter_type === 'rbfw_left_filter_category' ) {
										foreach ( $rbfw_features as $category ) {
											if ( ! empty( $category ) ) {
												?>
                                                <div class="rbfw_types">
                                                    <input type="checkbox" class="rbfw_category" value="<?php echo esc_attr( $category ) ?>"> <?php echo esc_attr( $category ) ?>
                                                </div>
											<?php }
										}
									} else if ( $filter_type === 'rbfw_left_filter_location' ) {
										foreach ( $rbfw_features as $key => $location ) {
											if ( ! empty( $location ) ) {
												?>
                                                <div class="rbfw_types"><input type="checkbox" class="rbfw_location" value="<?php echo esc_attr( $key ) ?>"> <?php echo esc_attr( $location ) ?></div>
											<?php }
										}
									}
								?>
                            </div>
                        </div>
						<?php
						$content = ob_get_clean();
					}

				wp_send_json_success( $content );
			}
		}
		new Rbfw_Search_Page();
	}