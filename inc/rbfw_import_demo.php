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
				add_action( 'admin_init', array( $this, 'rbfw_trigger_import_demo' ) );
			}

			public function rbfw_trigger_import_demo() {
				
				$sample_rent_items = get_option( 'rbfw_sample_rent_items' );
				if ( $sample_rent_items != 'imported' ) {
					$this->rbfw_import_demo_function();
				}

			}

			public function rbfw_import_demo_function() {
				// If you must ensure longer execution time, consider handling this at the server level.
				
				$xml_url     = RBFW_PLUGIN_URL . '/assets/sample-rent-items.xml';
				$xml         = simplexml_load_file( $xml_url );
				$json_string = wp_json_encode( $xml );
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
								// meata key and value udpates
								foreach ( $rent_post_metas as $value ) {
									$meta_key = $value['meta_key'];
									$meta_value = ! empty( $value['meta_value'] ) ? maybe_unserialize( $value['meta_value'] ) : '';
									update_post_meta( $rent_post_id, $meta_key, $meta_value );
								}
								// set featured thumbnail image
								$rbfw_bkp_thumb_img    = get_post_meta( $rent_post_id, 'rbfw_bkp_thumb_img', true );
								$thumb_id = '';
								foreach ( $rbfw_bkp_thumb_img as $url ) {
									$filename = pathinfo($url, PATHINFO_BASENAME);
									$path = RBFW_PLUGIN_DIR.'/assets/importimg/'.$filename;
									$attach_id = $this->rbfw_media_upload_from_path( $path );
									$thumb_id  = $attach_id;
								}
								update_post_meta( $rent_post_id, '_thumbnail_id', $thumb_id );
								// gallery image;
								// $rbfw_bkp_gallary_imgs = get_post_meta( $rent_post_id, 'rbfw_bkp_gallary_imgs', true );
								// $gallary_arr = [];
								// foreach ( $rbfw_bkp_gallary_imgs as $url ) {
								// 	$filename = pathinfo($url, PATHINFO_BASENAME);
								// 	$path = RBFW_PLUGIN_DIR.'/assets/importimg/'.$filename;
								// 	$attach_id = $this->rbfw_media_upload_from_path( $path );
								// 	$gallary_arr[] = $attach_id;
								// }
								// update_post_meta( $rent_post_id, 'rbfw_gallery_images', $gallary_arr );
								// update_post_meta( $rent_post_id, 'rbfw_gallery_images_additional', $gallary_arr );
							}
						}
						$i++;
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
						wp_delete_file( $tmp );

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
				wp_delete_file( $tmp );
				// Error uploading
				if ( is_wp_error( $attachment_id ) ) {
					return false;
				}

				// Success, return attachment ID (int)
				return (int) $attachment_id;
			}

			public function rbfw_media_upload_from_path( $file_path, $title = null ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
			
				if ( ! file_exists( $file_path ) ) {
					return false; // File does not exist
				}
			
				// Get the filename and extension
				$file_name = basename( $file_path );
				$upload_dir = wp_upload_dir();
				$new_file_path = $upload_dir['path'] . '/' . $file_name;
			
				// Copy the file to the uploads directory
				if ( ! copy( $file_path, $new_file_path ) ) {
					return false; // Failed to copy the file
				}
			
				// Get file type
				$filetype = wp_check_filetype( $new_file_path );
			
				// Prepare an array of attachment data
				$attachment = array(
					'guid'           => $upload_dir['url'] . '/' . $file_name,
					'post_mime_type' => $filetype['type'],
					'post_title'     => $title ? $title : sanitize_file_name( $file_name ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
			
				// Insert attachment into the media library
				$attach_id = wp_insert_attachment( $attachment, $new_file_path );
			
				// Generate attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $new_file_path );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			
				return $attach_id; // Return the attachment ID
			}
		}
		$dummy_import = new RbfwImportDemo();
	}