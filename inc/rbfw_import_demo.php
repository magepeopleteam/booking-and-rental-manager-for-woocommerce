<?php
	/*
	* Author 	:	MagePeople Team
	* Version	:	1.0.0
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'RbfwImportDemo' ) ) {
		class RbfwImportDemo {
			public function __construct() {
				$sample_rent_items = get_option( 'rbfw_sample_rent_items' );
				if ( $sample_rent_items != 'imported' ) {
					$this->rbfw_import_demo_function();
				}

			}

			public function rbfw_import_demo_function() {
				// Disable a time limit
				set_time_limit( 0 );
				$xml_url     = RBFW_PLUGIN_URL . '/assets/sample-rent-items.xml';
				$xml         = simplexml_load_file( $xml_url );
				$json_string = json_encode( $xml );
				$xml_array   = json_decode( $json_string, true );
				$xml_array = ! empty( $xml_array['item'] ) ? $xml_array['item'] : [];
				if ( $xml !== false && ! empty( $xml_array ) ) {
					$counter = count( $xml_array );
					$i = 1;
					foreach ( $xml_array as $item ) {
						if ( $i <= $counter ) {
							$title   = ! empty( $item['title'] ) ? $item['title'] : '';
							$content = ! empty( $item['content'] ) ? $item['content'] : '';
							unset($rent_args);
							$rent_args = array(
								'post_title'   => $title,
								'post_content' => $content,
								'post_status'  => 'publish',
								'post_type'    => 'rbfw_item',
							);
							$rent_post_id = wp_insert_post( $rent_args );
							$rent_post_metas = ! empty( $item['postmeta'] ) ? $item['postmeta'] : '';
							if ( ! empty( $rent_post_id ) ) {
								foreach ( $rent_post_metas as $value ) {
									$meta_key = $value['meta_key'];
									if ( ! empty( $value['meta_value'] ) ) {
										$meta_value = maybe_unserialize( $value['meta_value'] );
									} else {
										$meta_value = '';
									}
									update_post_meta( $rent_post_id, $meta_key, $meta_value );
								}
								$rbfw_bkp_gallary_imgs = get_post_meta( $rent_post_id, 'rbfw_bkp_gallary_imgs', true );
								$rbfw_bkp_thumb_img    = get_post_meta( $rent_post_id, 'rbfw_bkp_thumb_img', true );
								$gallary_arr = [];
								foreach ( $rbfw_bkp_gallary_imgs as $url ) {
									$attach_id     = $this->rbfw_media_upload_from_url( $url );
									$gallary_arr[] = $attach_id;
								}
								update_post_meta( $rent_post_id, 'rbfw_gallery_images', $gallary_arr );
								update_post_meta( $rent_post_id, 'rbfw_gallery_images_additional', $gallary_arr );
								$thumb_id = '';
								foreach ( $rbfw_bkp_thumb_img as $url ) {
									$attach_id = $this->rbfw_media_upload_from_url( $url );
									$thumb_id  = $attach_id;
								}
								update_post_meta( $rent_post_id, '_thumbnail_id', $thumb_id );
							}
						}
						$i ++;
					}
					$this->rbfw_update_related_products();
					update_option( 'rbfw_sample_rent_items', 'imported' );
				}
			}

			public function rbfw_update_related_products() {
				$args = array( 'fields' => 'ids', 'post_type' => 'rbfw_item', 'numberposts' => - 1, 'post_status' => 'publish' );
				$ids  = get_posts( $args );
				foreach ( $ids as $id ) {
					update_post_meta( $id, 'rbfw_releted_rbfw', $ids );
				}
			}

			public function rbfw_media_upload_from_url( $url, $title = null ) {
				require_once( ABSPATH . "/wp-load.php" );
				require_once( ABSPATH . "/wp-admin/includes/image.php" );
				require_once( ABSPATH . "/wp-admin/includes/file.php" );
				require_once( ABSPATH . "/wp-admin/includes/media.php" );
				// Download url to a temp file
				$tmp = download_url( $url );
				if ( is_wp_error( $tmp ) ) {
					return false;
				}
				// Get the filename and extension ("photo.png" => "photo", "png")
				$filename  = pathinfo( $url, PATHINFO_FILENAME );
				$extension = pathinfo( $url, PATHINFO_EXTENSION );
				// An extension is required or else WordPress will reject the upload
				if ( ! $extension ) {
					// Look up mime type, example: "/photo.png" -> "image/png"
					$mime = mime_content_type( $tmp );
					$mime = is_string( $mime ) ? sanitize_mime_type( $mime ) : false;
					// Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
					$mime_extensions = array(
						// mime_type         => extension (no period)
						'text/plain'         => 'txt',
						'text/csv'           => 'csv',
						'application/msword' => 'doc',
						'image/jpg'          => 'jpg',
						'image/jpeg'         => 'jpeg',
						'image/gif'          => 'gif',
						'image/png'          => 'png',
						'video/mp4'          => 'mp4',
					);
					if ( isset( $mime_extensions[ $mime ] ) ) {
						// Use the mapped extension
						$extension = $mime_extensions[ $mime ];
					} else {
						// Could not identify extension
						@unlink( $tmp );

						return false;
					}
				}
				// Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
				$args = array(
					'name'     => "$filename.$extension",
					'tmp_name' => $tmp,
				);
				// Do the upload
				$attachment_id = media_handle_sideload( $args, 0, $title );
				// Cleanup temp file
				@unlink( $tmp );
				// Error uploading
				if ( is_wp_error( $attachment_id ) ) {
					return false;
				}

				// Success, return attachment ID (int)
				return (int) $attachment_id;
			}

		}
		new RbfwImportDemo();
	}