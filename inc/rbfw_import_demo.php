<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('RbfwImportDemo')) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		class RbfwImportDemo {
			public function __construct() {
				$this->dummy_import();
				// add_action('admin_init', [$this, 'dummy_import'], 99);
			}

			public function dummy_import() {
				$dummy_post_inserted = get_option('rbfw_sample_rent_items');
				if ($dummy_post_inserted) {
					return;
				}
				$count_existing_event = 0;
				// $plugin_active = self::check_plugin('booking-and-rental-manager-for-woocommerce', 'rent-manager.php');
				if ($count_existing_event == 0 && $dummy_post_inserted != 'yes') {
					// $this->create_dummy_page();
					$retnal_data = $this->retnal_data();
					$retnal_ids = $this->insert_posts($retnal_data, 'rbfw_item');
					$this->insert_thumbnails($retnal_ids,'');
					$this->insert_gallery_images($retnal_ids);
					$this->rbfw_update_related_products();
					update_option('rbfw_sample_rent_items', 'yes');
				}
			}

			public function rbfw_update_related_products() {
				$args = array( 'fields' => 'ids', 'post_type' => 'rbfw_item', 'numberposts' => - 1, 'post_status' => 'publish' );
				$ids  = get_posts( $args );
				foreach ( $ids as $id ) {
					update_post_meta( $id, 'rbfw_releted_rbfw', $ids );
				}
			}

			public function insert_posts($posts, $post_type) {
				$post_ids = [];

				// Ensure $posts is an array to avoid foreach errors
				if (!is_array($posts)) {
					return $post_ids;
				}

				foreach ($posts as $data) {
					// Use ?? to provide a default empty string if the key is missing
					$post = [
						'post_type' => $post_type,
						'post_title' => isset($data['title']) ? $data['title'] : '',
						'post_content' => isset($data['content']) ? $data['content'] : '',
						'post_status' => 'publish',
					];

					$post_id = wp_insert_post($post);

					// Add meta data only if the ID is valid and the key exists
					if (!is_wp_error($post_id)) {
						$meta_data = $data['postmeta'] ?? []; // Default to empty array if missing
						
						if (is_array($meta_data)) {
							foreach ($meta_data as $meta_key => $meta_value) {
								update_post_meta($post_id, $meta_key, $meta_value);
							}
						}
					}
					$post_ids[] = $post_id;
				}
				return $post_ids;
			}

			public function insert_gallery_images($retnal_ids){
				$attachment_ids = self::dummy_images();
				
				$gallary_arr = [];
				foreach ( $retnal_ids as $post_id ) {

					// Example: assign all images to each post
					$gallery_arr = array_map( 'intval', $attachment_ids );

					update_post_meta( $post_id, 'rbfw_gallery_images', $gallery_arr );
					update_post_meta( $post_id, 'rbfw_gallery_images_additional', $gallery_arr );
				}
			}
			public function insert_thumbnails($postsids,$meta_key=''){
				$attachment_ids = self::dummy_images();
				foreach ( $postsids as $index => $post_id ) {
					$attachment_id = $attachment_ids[ $index ];
					set_post_thumbnail( $post_id, $attachment_id );
					if($meta_key!=''){
						update_post_meta( $post_id, $meta_key, $attachment_id );
					}
					
				}
			}

			public function retnal_data(){
				return[
						[
							'title'   => 'Bike/Car For Single Day Multiple Slot - Classic Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'00:00','00:30','01:00','06:00','08:00','08:30','09:00','09:30',
									'10:00','10:30','11:30','12:00','12:30','18:00','20:00','20:30',
									'21:00','21:30','22:00','22:30','23:30'
								],

								'rbfw_item_type' => 'bike_car_sd',

								'rbfw_extra_service_data' => [
									[
										'service_name'  => 'Tie',
										'service_price'  => '10',
										'service_qty'    => '100',
									],
									[
										'service_name'  => 'Shoes',
										'service_price'  => '10',
										'service_qty'    => '100',
									],
								],

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => 'Morning Session',
										'short_desc' => '9 am to 12pm',
										'price'      => '10',
										'qty'        => '100',
										'start_time' => '09:00',
										'end_time'   => '12:00',
										'duration'   => '6',
										'd_type'     => 'Hours',
									],
									[
										'rent_type'  => 'Afternoon Session',
										'short_desc' => '3 pm to 6pm',
										'price'      => '10',
										'qty'        => '',
										'start_time' => '15:00',
										'end_time'   => '18:00',
										'duration'   => '6',
										'd_type'     => 'Hours',
									],
									[
										'rent_type'  => 'Full Day',
										'short_desc' => '6 am to 12 pm',
										'price'      => '18',
										'qty'        => '',
										'start_time' => '09:00',
										'end_time'   => '18:00',
										'duration'   => '24',
										'd_type'     => 'Hours',
									],
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => [],

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',

								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_item_stock_quantity' => '10',

								'rbfw_time_slot_switch' => 'on',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Bike Features',
										'cat_features' => [
											['title' => 'Disc Brakes'],
											['title' => 'Shock Absorbers'],
											['title' => 'Headlight and Taillight'],
											['title' => 'Bottle Holder'],
											['title' => 'Electric Horn'],
										],
									]
								],

								'rbfw_inventory' => [
									53 => [
										'rbfw_start_date_ymd' => '2025-03-20',
										'rbfw_end_date_ymd'   => '2025-03-20',
										'rbfw_start_time_24'  => '09:00',
										'rbfw_end_time_24'    => '12:00',
										'booked_dates'        => ['20-03-2025'],
										'rbfw_item_quantity'  => 1,
										'rbfw_order_status'   => 'processing',
									],
									61 => [
										'rbfw_start_date_ymd' => '2025-03-21',
										'rbfw_end_date_ymd'   => '2025-03-21',
										'rbfw_start_time_24'  => '09:00',
										'rbfw_end_time_24'    => '18:00',
										'booked_dates'        => ['21-03-2025'],
										'rbfw_item_quantity'  => 1,
										'rbfw_order_status'   => 'processing',
									],
								],

								'rbfw_gallery_images' => [],

								'rbfw_single_template' => 'Default',
							],
						],
						[
							'title'   => 'Resort - Muffin Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
							'postmeta' => [
								

								'rdfw_available_time' => [
									'10:00','11:00','12:00','13:00','14:00','15:00','14:00','17:00','21:00'
								],

								'rbfw_item_type' => 'resort',

								'rbfw_extra_service_data' => [
									[
										'service_img'   => '1340',
										'service_name'  => 'BBQ-party',
										'service_price' => '10.99',
										'service_qty'   => '10',
									],
									[
										'service_img'   => '1339',
										'service_name'  => 'Casino Royal',
										'service_price' => '10.99',
										'service_qty'   => '10',
									],
									[
										'service_img'   => '1341',
										'service_name'  => 'Spa and Cure',
										'service_price' => '10.99',
										'service_qty'   => '10',
									],
								],

								'rbfw_resort_room_data' => [
									[
										'room_type'                 => 'Single',
										'rbfw_room_image'           => '1335',
										'rbfw_room_daylong_rate'    => '10.99',
										'rbfw_room_daynight_rate'   => '40.99',
										'rbfw_room_desc'            => 'Max. person: 2',
										'rbfw_room_available_qty'   => '10',
									],
									[
										'room_type'                 => 'Delux',
										'rbfw_room_image'           => '1336',
										'rbfw_room_daylong_rate'    => '20.99',
										'rbfw_room_daynight_rate'   => '50.99',
										'rbfw_room_desc'            => 'Max. person: 2',
										'rbfw_room_available_qty'   => '10',
									],
									[
										'room_type'                 => 'King',
										'rbfw_room_image'           => '1334',
										'rbfw_room_daylong_rate'    => '30.99',
										'rbfw_room_daynight_rate'   => '60.99',
										'rbfw_room_desc'            => 'Max. person: 2',
										'rbfw_room_available_qty'   => '10',
									],
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price'  => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => '',
										'short_desc' => '',
										'price'      => '',
										'qty'        => '',
									]
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => '',

								'rbfw_enable_hourly_rate' => 'no',
								'rbfw_enable_daily_rate'  => 'no',
								'rbfw_enable_pick_point'  => 'no',

								'rbfw_hourly_rate' => '',
								'rbfw_daily_rate'  => '',

								'rbfw_sun_hourly_rate' => '',
								'rbfw_sun_daily_rate'  => '',
								'rbfw_enable_sun_day'   => 'no',

								'rbfw_mon_hourly_rate' => '',
								'rbfw_mon_daily_rate'  => '',
								'rbfw_enable_mon_day'  => 'no',

								'rbfw_tue_hourly_rate' => '',
								'rbfw_tue_daily_rate'  => '',
								'rbfw_enable_tue_day'  => 'no',

								'rbfw_wed_hourly_rate' => '',
								'rbfw_wed_daily_rate'  => '',
								'rbfw_enable_wed_day'  => 'no',

								'rbfw_thu_hourly_rate' => '',
								'rbfw_thu_daily_rate'  => '',
								'rbfw_enable_thu_day'  => 'no',

								'rbfw_fri_hourly_rate' => '',
								'rbfw_fri_daily_rate'  => '',
								'rbfw_enable_fri_day'  => 'no',

								'rbfw_sat_hourly_rate' => '',
								'rbfw_sat_daily_rate'  => '',
								'rbfw_enable_sat_day'  => 'no',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Room Services',
										'cat_features' => [
											['title' => 'Air Cooling'],
											['title' => 'Wi-Fi'],
											['title' => 'Smart Ironing'],
											['title' => '24/7 Room Serivice'],
											['title' => 'Garden Balcony'],
											['title' => 'Swimming Pool'],
											['title' => 'Bath Tab'],
										],
									],
									[
										'cat_title' => 'Hotel Services',
										'cat_features' => [
											['title' => 'Breakfast Included'],
											['title' => 'Spa Center'],
											['title' => 'Hill View'],
											['title' => 'BBQ zone'],
											['title' => 'Large Swimming Pool'],
											['title' => 'Easy to Travel'],
											['title' => 'Parking'],
										],
									],
								],

								'rbfw_single_template' => 'Muffin',

								'rbfw_time_slot_switch' => 'on',
								'rbfw_available_qty_info_switch' => 'no',
								'rbfw_enable_extra_service_qty' => 'yes',

								'rbfw_enable_variations' => 'no',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '0',
								'rbfw_enable_resort_daylong_price' => 'yes',
							],
						],
						[
							'title'   => 'Doctor Appointment - Muffin Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua....',
							'postmeta' => [
								
								'rdfw_available_time' => [
									'10:00 AM','10:00 PM','10:30 AM','10:30 PM',
									'11:00 AM','11:30 AM','11:30 PM','12:00 PM','12:30 PM'
								],

								'rbfw_item_type' => 'appointment',

								'rbfw_extra_service_data' => [],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
									[
										'rbfw_faq_title'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price'  => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => '30 Minute',
										'short_desc' => 'Consult for 30 minutes',
										'price'      => '100',
										'qty'        => '90',
									]
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => '',

								'rbfw_enable_hourly_rate' => 'no',
								'rbfw_enable_daily_rate'  => 'no',
								'rbfw_enable_pick_point'  => 'no',

								'rbfw_hourly_rate' => '',
								'rbfw_daily_rate'  => '',

								'rbfw_sun_hourly_rate' => '',
								'rbfw_sun_daily_rate'  => '',
								'rbfw_enable_sun_day'  => 'no',

								'rbfw_mon_hourly_rate' => '',
								'rbfw_mon_daily_rate'  => '',
								'rbfw_enable_mon_day'  => 'no',

								'rbfw_tue_hourly_rate' => '',
								'rbfw_tue_daily_rate'  => '',
								'rbfw_enable_tue_day'  => 'no',

								'rbfw_wed_hourly_rate' => '',
								'rbfw_wed_daily_rate'  => '',
								'rbfw_enable_wed_day'  => 'no',

								'rbfw_thu_hourly_rate' => '',
								'rbfw_thu_daily_rate'  => '',
								'rbfw_enable_thu_day'  => 'no',

								'rbfw_fri_hourly_rate' => '',
								'rbfw_fri_daily_rate'  => '',
								'rbfw_enable_fri_day'  => 'no',

								'rbfw_sat_hourly_rate' => '',
								'rbfw_sat_daily_rate'  => '',
								'rbfw_enable_sat_day'  => 'no',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Bike Features',
										'cat_features' => [
											['title' => 'Disc Brakes'],
											['title' => 'Shock Absorbers'],
											['title' => 'Headlight and Taillight'],
											['title' => 'Bottle Holder'],
											['title' => 'Electric Horn'],
										],
									]
								],

								'rbfw_single_template' => 'Muffin',

								'rbfw_time_slot_switch' => 'on',
								'rbfw_enable_extra_service_qty' => 'yes',

								'rbfw_enable_variations' => 'no',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '0',
								'rbfw_enable_resort_daylong_price' => 'no',

								'rbfw_sd_appointment_ondays' => [
									'Monday','Tuesday','Wednesday','Thursday','Friday'
								],

								'rbfw_sd_appointment_max_qty_per_session' => '10',

								'rbfw_variations_data' => [
									[
										'field_label' => '',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => '',
												'quantity' => '',
											]
										],
										'selected_value' => '',
									]
								],



							],
						],
						[
							'title'   => 'Equipment - Muffin Template',

							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','21:00'
								],

								'rbfw_item_type' => 'equipment',

								'rbfw_extra_service_data' => [],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...'
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price' => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => '30 Minute',
										'short_desc' => 'Consult for 30 minutes',
										'price'      => '100',
										'qty'        => '90',
									]
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => '',

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',
								'rbfw_enable_pick_point'  => 'no',

								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Highlighted Features',
										'cat_features' => [
											['title' => 'Brand: Bosch'],
											['title' => 'Power Source: Corded Electric'],
											['title' => 'Item Dimensions: 39.5 x 12 x 33 Centimeters'],
											['title' => 'Weight: 4.4 Kilograms'],
										],
									]
								],

								'rbfw_single_template' => 'Muffin',

								'rbfw_time_slot_switch' => 'on',
								'rbfw_enable_extra_service_qty' => 'yes',

								'rbfw_enable_variations' => 'no',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '10',
								'rbfw_enable_resort_daylong_price' => 'no',

								'rbfw_variations_data' => [
									[
										'field_label' => '',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => '',
												'quantity' => '',
											]
										],
										'selected_value' => '',
									]
								],
								'rbfw_sd_appointment_ondays' => [],
								'rbfw_sd_appointment_max_qty_per_session' => '',
							],
						],
						[
							'title'   => 'Bike/Car For Multiple Day - Muffin Template',
							'content' => 'A bike rental or bike hire business rents out bicycles for short periods of time, usually for a few hours. Most rentals are provided by bike shops as a sideline to their main businesses of sales and service, but shops specialize in rentals.',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','21:00'
								],

								'rbfw_item_type' => 'bike_car_md',

								'rbfw_enable_start_end_date' => 'yes',

								'rbfw_extra_service_data' => [
									[
										'service_name'  => 'Extra Tire',
										'service_price' => '5',
										'service_qty'   => '10',
									],
									[
										'service_name'  => 'Helmet',
										'service_price' => '5',
										'service_qty'   => '10',
									],
									[
										'service_name'  => 'Extra engine Oil',
										'service_price' => '2',
										'service_qty'   => '10',
									],
									[
										'service_name'  => 'Tool Box',
										'service_price' => '2',
										'service_qty'   => '10',
									],
								],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price' => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => '',
										'short_desc' => '',
										'price'      => '',
										'qty'        => '',
									]
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => [],

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',

								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_sun_hourly_rate' => '',
								'rbfw_sun_daily_rate'  => '',
								'rbfw_enable_sun_day'  => 'no',

								'rbfw_mon_hourly_rate' => '',
								'rbfw_mon_daily_rate'  => '',
								'rbfw_enable_mon_day'  => 'no',

								'rbfw_tue_hourly_rate' => '',
								'rbfw_tue_daily_rate'  => '',
								'rbfw_enable_tue_day'  => 'no',

								'rbfw_wed_hourly_rate' => '',
								'rbfw_wed_daily_rate'  => '',
								'rbfw_enable_wed_day'  => 'no',

								'rbfw_thu_hourly_rate' => '',
								'rbfw_thu_daily_rate'  => '',
								'rbfw_enable_thu_day'  => 'no',

								'rbfw_fri_hourly_rate' => '',
								'rbfw_fri_daily_rate'  => '',
								'rbfw_enable_fri_day'  => 'no',

								'rbfw_sat_hourly_rate' => '',
								'rbfw_sat_daily_rate'  => '',
								'rbfw_enable_sat_day'  => 'no',

								'rbfw_enable_pick_point' => 'no',

								'rbfw_item_stock_quantity' => '10',

								'rbfw_time_slot_switch' => 'on',

								'rbfw_enable_extra_service_qty' => 'yes',

								'rbfw_enable_variations' => 'no',

								'rbfw_enable_md_type_item_qty' => 'yes',

								'rbfw_variations_data' => [
									[
										'field_label' => '',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => '',
												'quantity' => '',
											]
										],
										'selected_value' => '',
									]
								],

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Bike Features',
										'cat_features' => [
											['title' => 'Disc Brakes'],
											['title' => 'Shock Absorbers'],
											['title' => 'Headlight and Taillight'],
											['title' => 'Bottle Holder'],
											['title' => 'Electric Horn'],
										],
									]
								],

								'rbfw_dt_sidebar_switch' => 'off',
								'rbfw_dt_sidebar_testimonials' => '',
								'rbfw_dt_sidebar_content' => '',

								'_thumbnail_id' => '',




								'rbfw_single_template' => 'Muffin',
							],
						],
						[
							'title'   => 'Dress - Muffin Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'10:00','11:00','12:00','13:00','14:00','3:00 PM','16:00','5:00 PM','21:00'
								],

								'rbfw_item_type' => 'dress',

								'rbfw_extra_service_data' => [
									[
										'service_name'  => 'Tie',
										'service_price' => '10',
										'service_qty'   => '100',
									],
									[
										'service_name'  => 'Shoes',
										'service_price' => '10',
										'service_qty'   => '100',
									],
								],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price' => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => '',
										'short_desc' => '',
										'price'      => '',
										'qty'        => '',
									]
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => [],

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',

								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_sun_hourly_rate' => '',
								'rbfw_sun_daily_rate'  => '',
								'rbfw_enable_sun_day'  => 'no',

								'rbfw_mon_hourly_rate' => '',
								'rbfw_mon_daily_rate'  => '',
								'rbfw_enable_mon_day'  => 'no',

								'rbfw_tue_hourly_rate' => '',
								'rbfw_tue_daily_rate'  => '',
								'rbfw_enable_tue_day'  => 'no',

								'rbfw_wed_hourly_rate' => '',
								'rbfw_wed_daily_rate'  => '',
								'rbfw_enable_wed_day'  => 'no',

								'rbfw_thu_hourly_rate' => '',
								'rbfw_thu_daily_rate'  => '',
								'rbfw_enable_thu_day'  => 'no',

								'rbfw_fri_hourly_rate' => '',
								'rbfw_fri_daily_rate'  => '',
								'rbfw_enable_fri_day'  => 'no',

								'rbfw_sat_hourly_rate' => '',
								'rbfw_sat_daily_rate'  => '',
								'rbfw_enable_sat_day'  => 'no',

								'rbfw_list_thumbnail' => '',
								'rbfw_theme_file' => '',

								'rbfw_available_qty_info_switch' => 'no',

								'rbfw_single_template' => 'Muffin',

								'rbfw_time_slot_switch' => 'on',

								'rbfw_enable_extra_service_qty' => 'yes',
								'rbfw_enable_variations' => 'yes',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '10',

								'rbfw_enable_resort_daylong_price' => 'no',

								'rbfw_variations_data' => [
									[
										'field_label' => 'Color',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => 'Red',
												'quantity' => '5',
											],
											[
												'name'     => 'Blue',
												'quantity' => '5',
											],
										],
									],
									[
										'field_label' => 'Size',
										'field_id'    => 'rbfw_variation_id_1',
										'value'       => [
											[
												'name'     => 'Small',
												'quantity' => '5',
											],
											[
												'name'     => 'Medium',
												'quantity' => '5',
											],
										],
									],
								],

								'rbfw_sd_appointment_ondays' => [],
								'rbfw_sd_appointment_max_qty_per_session' => '',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Dress Features',
										'cat_features' => [
											['title' => 'Very High Quality Product'],
											['title' => 'Various Sizeable'],
											['title' => 'High Quality Fabric'],
											['title' => 'Attractive To See'],
											['title' => 'Well Fitting'],
										],
									]
								],

								'rbfw_dt_sidebar_switch' => 'off',
								'rbfw_dt_sidebar_testimonials' => '',
								'rbfw_dt_sidebar_content' => '',

							],
						],
						[
							'title'   => 'Bike/Car For Single Day - Classic Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'10:00 AM','10:00 PM','10:30 AM','10:30 PM','11:30 AM','11:30 PM',
									'12:00 AM','12:00 PM','12:30 AM','12:30 PM','1:00 AM','6:00 AM',
									'6:00 PM','8:00 AM','8:00 PM','8:30 AM','8:30 PM','9:00 AM',
									'9:00 PM','9:30 AM','9:30 PM'
								],

								'rbfw_item_type' => 'bike_car_sd',

								'rbfw_extra_service_data' => [
									[
										'service_name'  => 'Tie',
										'service_price' => '10',
										'service_qty'   => '100',
									],
									[
										'service_name'  => 'Shoes',
										'service_price' => '10',
										'service_qty'   => '100',
									],
								],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
									[
										'rbfw_faq_title'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price' => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'  => 'One Hour Rentals',
										'short_desc' => 'Up to 1 hours',
										'price'      => '7.99',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'Two Hour Rentals',
										'short_desc' => 'Up to 2 hours',
										'price'      => '15.98',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'Three Hour Rentals',
										'short_desc' => 'Up to 3 hours',
										'price'      => '19.97',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'Four Hour Rentals',
										'short_desc' => 'Up to 4 hours',
										'price'      => '24.76',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'Five Hour Rentals',
										'short_desc' => 'Up to 5 hours',
										'price'      => '28.75',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'Half Day Rental',
										'short_desc' => 'Up to 6 hours',
										'price'      => '29.99',
										'qty'        => '100',
									],
									[
										'rent_type'  => 'All Day Rentals',
										'short_desc' => 'Up to 10 Hours',
										'price'      => '38.99',
										'qty'        => '100',
									],
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => [],

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',

								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_enable_pick_point' => 'no',

								'rbfw_sun_hourly_rate' => '',
								'rbfw_sun_daily_rate'  => '',
								'rbfw_enable_sun_day'  => 'no',

								'rbfw_mon_hourly_rate' => '',
								'rbfw_mon_daily_rate'  => '',
								'rbfw_enable_mon_day'  => 'no',

								'rbfw_tue_hourly_rate' => '',
								'rbfw_tue_daily_rate'  => '',
								'rbfw_enable_tue_day'  => 'no',

								'rbfw_wed_hourly_rate' => '',
								'rbfw_wed_daily_rate'  => '',
								'rbfw_enable_wed_day'  => 'no',

								'rbfw_thu_hourly_rate' => '',
								'rbfw_thu_daily_rate'  => '',
								'rbfw_enable_thu_day'  => 'no',

								'rbfw_fri_hourly_rate' => '',
								'rbfw_fri_daily_rate'  => '',
								'rbfw_enable_fri_day'  => 'no',

								'rbfw_sat_hourly_rate' => '',
								'rbfw_sat_daily_rate'  => '',
								'rbfw_enable_sat_day'  => 'no',

								'rbfw_list_thumbnail' => '',
								'rbfw_theme_file' => '',

								'rbfw_available_qty_info_switch' => 'no',
								'rbfw_single_template' => 'Default',

								'rbfw_time_slot_switch' => 'on',
								'rbfw_enable_extra_service_qty' => 'yes',
								'rbfw_enable_variations' => 'no',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '10',

								'rbfw_enable_resort_daylong_price' => 'no',

								'rbfw_variations_data' => [
									[
										'field_label' => '',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => '',
												'quantity' => '',
											]
										],
										'selected_value' => '',
									]
								],

								'rbfw_sd_appointment_ondays' => [],
								'rbfw_sd_appointment_max_qty_per_session' => '',

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Bike Features',
										'cat_features' => [
											['title' => 'Disc Brakes'],
											['title' => 'Shock Absorbers'],
											['title' => 'Headlight and Taillight'],
											['title' => 'Bottle Holder'],
											['title' => 'Electric Horn'],
										],
									]
								],

								'rbfw_dt_sidebar_switch' => 'off',
								'rbfw_dt_sidebar_testimonials' => '',
								'rbfw_dt_sidebar_content' => '',

							],
						],
						[
							'title'   => 'Bike/Car For Single Day multi hour - Classic Template',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',

							'postmeta' => [
								

								'rdfw_available_time' => [
									'00:00','00:30','01:00','06:00','08:00','08:30','09:00','09:30',
									'10:00','10:30','11:30','12:00','12:30','18:00','20:00','20:30',
									'21:00','21:30','22:00','22:30','23:30'
								],

								'rbfw_item_type' => 'bike_car_sd',

								'rbfw_extra_service_data' => [
									[
										'service_name'  => 'Tie',
										'service_price' => '10',
										'service_qty'   => '100',
									],
									[
										'service_name'  => 'Shoes',
										'service_price' => '10',
										'service_qty'   => '100',
									],
								],

								'rbfw_resort_room_data' => [
									[
										'room_type' => '',
										'rbfw_room_image' => '',
										'rbfw_room_daylong_rate' => '',
										'rbfw_room_daynight_rate' => '',
										'rbfw_room_desc' => '',
										'rbfw_room_available_qty' => '',
									]
								],

								'rbfw_enable_faq_content' => 'yes',

								'mep_event_faq' => [
									[
										'rbfw_faq_title' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
									[
										'rbfw_faq_title' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
										'rbfw_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
									],
								],

								'rbfw_enable_dropoff_point' => 'no',
								'rbfw_enable_daywise_price' => 'no',

								'rbfw_bike_car_sd_data' => [
									[
										'rent_type'   => '1 Hour Rent',
										'short_desc'  => 'Rent for 1 hour',
										'price'       => '10',
										'qty'         => '100',
										'start_time'  => '09:00',
										'end_time'    => '12:00',
										'duration'    => '1',
										'd_type'      => 'Hours',
									],
									[
										'rent_type'   => '2 Hour Rent',
										'short_desc'  => 'Rent for 2 hour',
										'price'       => '15',
										'qty'         => '',
										'start_time'  => '15:00',
										'end_time'    => '18:00',
										'duration'    => '2',
										'd_type'      => 'Hours',
									],
									[
										'rent_type'   => '4 Hour Rent',
										'short_desc'  => 'Rent for 4 hour',
										'price'       => '25',
										'qty'         => '',
										'start_time'  => '09:00',
										'end_time'    => '18:00',
										'duration'    => '4',
										'd_type'      => 'Hours',
									],
									[
										'rent_type'   => '6 Hour Rent',
										'short_desc'  => 'Rent for 6 hour',
										'price'       => '30',
										'qty'         => '',
										'start_time'  => '',
										'end_time'    => '',
										'duration'    => '6',
										'd_type'      => 'Hours',
									],
									[
										'rent_type'   => 'Full Day Rent',
										'short_desc'  => 'Rent for full day',
										'price'       => '40',
										'qty'         => '',
										'start_time'  => '',
										'end_time'    => '',
										'duration'    => '24',
										'd_type'      => 'Hours',
									],
								],

								'rbfw_time_format' => '12',
								'rbfw_off_dates'   => [],

								'rbfw_enable_hourly_rate' => 'yes',
								'rbfw_enable_daily_rate'  => 'yes',

								'rbfw_enable_pick_point' => 'no',
								'rbfw_hourly_rate' => '10',
								'rbfw_daily_rate'  => '100',

								'rbfw_enable_sun_day' => 'no',
								'rbfw_enable_mon_day' => 'no',
								'rbfw_enable_tue_day' => 'no',
								'rbfw_enable_wed_day' => 'no',
								'rbfw_enable_thu_day' => 'no',
								'rbfw_enable_fri_day' => 'no',
								'rbfw_enable_sat_day' => 'no',

								'rbfw_available_qty_info_switch' => 'no',
								'rbfw_single_template' => 'Default',

								'rbfw_time_slot_switch' => 'on',
								'rbfw_enable_extra_service_qty' => 'yes',
								'rbfw_enable_variations' => 'no',
								'rbfw_enable_md_type_item_qty' => 'no',

								'rbfw_item_stock_quantity' => '10',

								'rbfw_variations_data' => [
									[
										'field_label' => '',
										'field_id'    => 'rbfw_variation_id_0',
										'value'       => [
											[
												'name'     => '',
												'quantity' => '',
											]
										],
										'selected_value' => '',
									]
								],

								'rbfw_feature_category' => [
									[
										'cat_title' => 'Bike Features',
										'cat_features' => [
											['title' => 'Disc Brakes'],
											['title' => 'Shock Absorbers'],
											['title' => 'Headlight and Taillight'],
											['title' => 'Bottle Holder'],
											['title' => 'Electric Horn'],
										],
									]
								],

								'rbfw_dt_sidebar_switch' => 'off',

								'rbfw_gallery_images' => [],
								'rbfw_gallery_images_additional' => [],

								'rbfw_categories' => [],

								'rbfw_inventory' => [],

								'rbfw_single_template' => 'Default',
							],
						]
					];
			}

			private function dummy_images() {
				$urls = array(
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image1.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image2.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image3.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image4.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image5.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image6.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image7.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image8.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image9.jpeg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/rental/image10.jpeg',
					);

				unset($image_ids);
				$image_ids = array();
				foreach ($urls as $url) {
					$image_ids[] = media_sideload_image($url, '0', 'Dummy Images', 'id');
				}
				return $image_ids;
			}

			public function create_dummy_page() {
				$pages_to_create = [
					'find' => [
						'slug' => 'booking',
						'title' => 'Booking Ticket',
						'content' => '[wtbm_ticket_booking]',
						'option_key' => 'mptrs_booking_page_created',
					]
				];
				foreach ($pages_to_create as $page_data) {
					$existing_page = get_page_by_path( $page_data['slug'], OBJECT, 'page' );
					if ( $existing_page ) {
						return;
					}
					$page = [
						'post_type' => 'page',
						'post_name' => $page_data['slug'],
						'post_title' => $page_data['title'],
						'post_content' => $page_data['content'],
						'post_status' => 'publish',
					];
					$page_id = wp_insert_post($page);
					if (is_wp_error($page_id)) {
						printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($page_id->get_error_message()));
					} else {
						update_option($page_data['option_key'], true);
					}
					
				}
			}

			public static function check_plugin($plugin_dir_name, $plugin_file): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
					return 1;
				}
				elseif (is_dir($plugin_dir)) {
					return 2;
				}
				else {
					return 0;
				}
			}
		}
		$dummy_import = new RbfwImportDemo();		
	}