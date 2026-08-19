<?php
	/**
	 * Shared access-control helpers for the plugin's public AJAX surface.
	 *
	 * Every `wp_ajax_nopriv_*` endpoint in this plugin takes an item ID from the
	 * request and reads that item's meta (availability, stock, prices, off days).
	 * A nonce alone does not authorise anything here: the front-end nonces are
	 * printed on public pages, so anybody can obtain one and then swap the ID for
	 * an item that is still a draft, pending review, private or in the trash.
	 *
	 * These helpers add the missing object-level authorisation: an item is
	 * readable over a public endpoint only when it is a real rental item AND it is
	 * publicly viewable, or the current user is actually allowed to read it
	 * (so admins previewing an unpublished item keep working).
	 *
	 * @package Booking_And_Rental_Manager
	 * @since   2.7.6
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.

	if ( ! function_exists( 'rbfw_public_item_post_types' ) ) {
		/**
		 * Post types a public endpoint is allowed to expose.
		 *
		 * Add-ons that expose their own CPT through the same endpoints can extend
		 * this list instead of bypassing the guard.
		 *
		 * @return string[]
		 */
		function rbfw_public_item_post_types() {
			$types = array( RBFW_Function::get_cpt_name() );

			return array_filter( array_map( 'strval', (array) apply_filters( 'rbfw_public_ajax_post_types', $types ) ) );
		}
	}

	if ( ! function_exists( 'rbfw_post_is_publicly_viewable' ) ) {
		/**
		 * Whether a post can be seen by a visitor who is not logged in.
		 *
		 * Wraps core's is_post_publicly_viewable() (WP 5.7+) with a fallback for
		 * the WP 5.3 floor this plugin still supports.
		 *
		 * @param int|WP_Post $post Post ID or object.
		 *
		 * @return bool
		 */
		function rbfw_post_is_publicly_viewable( $post ) {
			$post = get_post( $post );
			if ( ! $post instanceof WP_Post ) {
				return false;
			}
			if ( function_exists( 'is_post_publicly_viewable' ) ) {
				return (bool) is_post_publicly_viewable( $post );
			}
			$type_object = get_post_type_object( $post->post_type );
			if ( ! $type_object || empty( $type_object->public ) ) {
				return false;
			}
			$public_statuses = get_post_stati( array( 'public' => true ) );

			return in_array( $post->post_status, (array) $public_statuses, true );
		}
	}

	if ( ! function_exists( 'rbfw_can_view_item' ) ) {
		/**
		 * Object-level authorisation check for a rental item ID coming from a request.
		 *
		 * @param int                 $post_id       Item ID from the request.
		 * @param string[]|null       $allowed_types Post types to accept. Defaults to rbfw_public_item_post_types().
		 *
		 * @return bool True when the requester is allowed to read this item.
		 */
		function rbfw_can_view_item( $post_id, $allowed_types = null ) {
			$post_id = absint( $post_id );
			if ( ! $post_id ) {
				return false;
			}
			$post = get_post( $post_id );
			if ( ! $post instanceof WP_Post ) {
				return false;
			}

			$allowed_types = ( null === $allowed_types ) ? rbfw_public_item_post_types() : (array) $allowed_types;
			if ( ! empty( $allowed_types ) && ! in_array( $post->post_type, $allowed_types, true ) ) {
				return false;
			}

			/*
			 * Published (and any other publicly viewable status) is readable by
			 * everyone. Sites that deliberately keep items unpublished and render
			 * the booking form through a shortcode can widen this with the filter
			 * below — it is opt-in on purpose, because the default has to be safe.
			 */
			$allowed = rbfw_post_is_publicly_viewable( $post );

			if ( ! $allowed ) {
				$extra_statuses = (array) apply_filters( 'rbfw_public_ajax_extra_statuses', array(), $post );
				$allowed        = in_array( $post->post_status, $extra_statuses, true );
			}

			if ( ! $allowed ) {
				// Editors/admins previewing an unpublished item must keep working.
				$allowed = current_user_can( 'read_post', $post->ID );
			}

			/**
			 * Final say on whether this item may be exposed over a public endpoint.
			 *
			 * @param bool    $allowed Result of the checks above.
			 * @param WP_Post $post    The requested item.
			 */
			return (bool) apply_filters( 'rbfw_can_view_item', $allowed, $post );
		}
	}

	if ( ! function_exists( 'rbfw_ajax_access_denied' ) ) {
		/**
		 * End the request for an unauthorised item lookup.
		 *
		 * Deliberately generic: it must not tell the caller whether the ID exists,
		 * is the wrong post type, or is merely unpublished.
		 *
		 * @param string $message Optional override.
		 *
		 * @return void Never returns.
		 */
		function rbfw_ajax_access_denied( $message = '' ) {
			$message = $message ? $message : esc_html__( 'Sorry, this rental item is not available.', 'booking-and-rental-manager-for-woocommerce' );
			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				wp_send_json_error( array( 'message' => $message ), 403 );
			}
			wp_die( esc_html( $message ), '', array( 'response' => 403 ) );
		}
	}

	if ( ! function_exists( 'rbfw_ajax_item_id' ) ) {
		/**
		 * Read, sanitise and authorise an item ID posted to a public endpoint.
		 *
		 * Ends the request with a 403 when the ID is missing (and required) or the
		 * requester is not allowed to read it, so callers can use the return value
		 * without any further checks.
		 *
		 * @param string        $key           Request key holding the ID.
		 * @param bool          $required      Deny when the key is absent/zero.
		 * @param string[]|null $allowed_types Post types to accept.
		 *
		 * @return int Validated item ID, or 0 when not required and absent.
		 */
		function rbfw_ajax_item_id( $key = 'post_id', $required = true, $allowed_types = null ) {
			/*
			 * Read POST first and only then GET — never $_REQUEST. Its precedence
			 * follows php.ini's request_order, so on some hosts a query-string
			 * post_id would win over the body the handler itself reads, and the
			 * guard could end up authorising a different item than the one used.
			 */
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- callers verify the nonce first.
			if ( isset( $_POST[ $key ] ) ) {
				$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			} elseif ( isset( $_GET[ $key ] ) ) {
				$raw = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} else {
				$raw = '';
			}
			$post_id = is_scalar( $raw ) ? absint( $raw ) : 0;

			if ( ! $post_id ) {
				if ( ! $required ) {
					return 0;
				}
				rbfw_ajax_access_denied();
			}

			if ( ! rbfw_can_view_item( $post_id, $allowed_types ) ) {
				rbfw_ajax_access_denied();
			}

			return $post_id;
		}
	}

	if ( ! function_exists( 'rbfw_ajax_require_cap' ) ) {
		/**
		 * Capability gate for endpoints that are only ever driven from wp-admin.
		 *
		 * @param string $capability Capability to require.
		 *
		 * @return void Dies when the current user lacks it.
		 */
		function rbfw_ajax_require_cap( $capability = 'edit_posts' ) {
			if ( ! current_user_can( $capability ) ) {
				rbfw_ajax_access_denied( esc_html__( 'You are not allowed to do this.', 'booking-and-rental-manager-for-woocommerce' ) );
			}
		}
	}

	/* -----------------------------------------------------------------------
	 * PHP Object Injection hardening
	 *
	 * Two things have to be true, and only one of them is about unserialize().
	 *
	 * 1. WRITE SIDE (the actual fix). WordPress stores a meta value with
	 *    maybe_serialize() and reads it back with maybe_unserialize() *inside
	 *    core*. So when an attacker-controlled STRING that happens to be
	 *    serialized data is written to post meta, get_post_meta() instantiates
	 *    the object all by itself — long before any plugin code runs. No
	 *    amount of allowed_classes in this plugin can stop that. The only
	 *    place to stop it is on the way in, which is what the meta guard below
	 *    does. Arrays are unaffected: core serialises those itself and they
	 *    come back as arrays of scalars.
	 *
	 * 2. READ SIDE (defence in depth). Every explicit unserialize() in the
	 *    plugin now goes through rbfw_safe_unserialize(), which forbids object
	 *    instantiation outright. This covers double-serialized values, values
	 *    read straight from $wpdb, and order-item meta.
	 * -------------------------------------------------------------------- */

	if ( ! function_exists( 'rbfw_scrub_php_objects' ) ) {
		/**
		 * Recursively drop anything that came back as an object.
		 *
		 * With allowed_classes => false PHP hands back __PHP_Incomplete_Class
		 * placeholders rather than real objects. They are inert, but callers here
		 * all expect arrays/scalars, so returning one would only turn an injection
		 * into a type-confusion bug. Drop them instead.
		 *
		 * @param mixed $value Decoded value.
		 *
		 * @return mixed
		 */
		function rbfw_scrub_php_objects( $value ) {
			if ( is_object( $value ) ) {
				return '';
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $item ) {
					$value[ $key ] = rbfw_scrub_php_objects( $item );
				}
			}

			return $value;
		}
	}

	if ( ! function_exists( 'rbfw_safe_unserialize' ) ) {
		/**
		 * Drop-in replacement for maybe_unserialize() / unserialize() that can
		 * never instantiate a class.
		 *
		 * Same contract as maybe_unserialize(): a value that is not serialized is
		 * returned untouched, so this substitutes cleanly at every call site.
		 *
		 * Note that maybe_unserialize() takes ONE argument — passing
		 * array( 'allowed_classes' => false ) to it does nothing at all.
		 *
		 * @param mixed $value Possibly-serialized value.
		 *
		 * @return mixed Decoded value with every object removed.
		 */
		function rbfw_safe_unserialize( $value ) {
			if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
				return rbfw_scrub_php_objects( $value );
			}

			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- malformed input must not warn.
			$decoded = @unserialize( $value, array( 'allowed_classes' => false ) );

			if ( false === $decoded ) {
				return ( 'b:0;' === $value ) ? false : '';
			}

			return rbfw_scrub_php_objects( $decoded );
		}
	}

	if ( ! function_exists( 'rbfw_is_guarded_meta_key' ) ) {
		/**
		 * Whether a post meta key belongs to this plugin and must be guarded.
		 *
		 * Scoped to our own keys on purpose — this filter runs on every post meta
		 * write on the site and has no business policing other plugins' data.
		 *
		 * @param string $meta_key Meta key being written.
		 *
		 * @return bool
		 */
		function rbfw_is_guarded_meta_key( $meta_key ) {
			if ( ! is_string( $meta_key ) || '' === $meta_key ) {
				return false;
			}
			if ( preg_match( '/^_?(rbfw|rdfw)_/i', $meta_key ) ) {
				return true;
			}
			$extra = (array) apply_filters( 'rbfw_guarded_meta_keys', array( 'link_wc_product', 'check_if_run_once' ) );

			return in_array( $meta_key, $extra, true );
		}
	}

	if ( ! function_exists( 'rbfw_neutralize_serialized_strings' ) ) {
		/**
		 * Strip serialized payloads out of a value that is about to be stored.
		 *
		 * Only STRINGS are touched. An array is fine — core serialises it on the
		 * way out and rebuilds the same array on the way in. A string that is
		 * already serialized data is the dangerous shape, and this plugin never
		 * legitimately stores one, so it is dropped.
		 *
		 * @param mixed $value Value heading for the meta table.
		 *
		 * @return mixed Same value with serialized strings blanked.
		 */
		function rbfw_neutralize_serialized_strings( $value ) {
			if ( is_string( $value ) ) {
				return is_serialized( $value ) ? '' : $value;
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $item ) {
					$value[ $key ] = rbfw_neutralize_serialized_strings( $item );
				}
			}

			return $value;
		}
	}

	if ( ! function_exists( 'rbfw_guard_meta_write' ) ) {
		/**
		 * Refuse to store a serialized string under one of our meta keys.
		 *
		 * Returns null (core carries on untouched) for every value that is already
		 * clean, which is all of them in normal use — the rewrite path only runs
		 * for an actual payload.
		 *
		 * @param null|bool $check      Short-circuit value.
		 * @param int       $object_id  Post ID.
		 * @param string    $meta_key   Meta key.
		 * @param mixed     $meta_value Meta value (already unslashed by core).
		 * @param mixed     $extra      $prev_value on update, $unique on add.
		 * @param string    $mode       'update' or 'add'.
		 *
		 * @return null|bool|int
		 */
		function rbfw_guard_meta_write( $check, $object_id, $meta_key, $meta_value, $extra, $mode ) {
			static $busy = false;

			if ( null !== $check || $busy ) {
				return $check;
			}
			if ( ! rbfw_is_guarded_meta_key( $meta_key ) ) {
				return $check;
			}

			$clean = rbfw_neutralize_serialized_strings( $meta_value );
			if ( $clean === $meta_value ) {
				return $check; // Nothing to strip — let core handle it normally.
			}

			/*
			 * Core unslashed $meta_value before calling us and will unslash again
			 * inside the nested call, so re-slash to keep the round trip lossless.
			 */
			$busy = true;
			if ( 'add' === $mode ) {
				$result = add_metadata( 'post', $object_id, $meta_key, wp_slash( $clean ), $extra );
			} else {
				$result = update_metadata( 'post', $object_id, $meta_key, wp_slash( $clean ), $extra );
			}
			$busy = false;

			return $result;
		}

		add_filter(
			'update_post_metadata',
			static function ( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
				return rbfw_guard_meta_write( $check, $object_id, $meta_key, $meta_value, $prev_value, 'update' );
			},
			10,
			5
		);

		add_filter(
			'add_post_metadata',
			static function ( $check, $object_id, $meta_key, $meta_value, $unique ) {
				return rbfw_guard_meta_write( $check, $object_id, $meta_key, $meta_value, $unique, 'add' );
			},
			10,
			5
		);
	}

	if ( ! function_exists( 'rbfw_heal_injected_meta' ) ) {
		/**
		 * One-off cleanup of object payloads already stored in post meta.
		 *
		 * The write guard stops new payloads, but a site attacked before the
		 * update still has one sitting in wp_postmeta, and core re-instantiates it
		 * on every read. Rows are repaired rather than deleted: the value is
		 * decoded with object instantiation disabled and written back, so a
		 * legitimate array that merely carried an injected element keeps the rest
		 * of its data.
		 *
		 * Runs in the admin only, in bounded batches, and stops for good once a
		 * pass finds nothing left.
		 *
		 * @return void
		 */
		function rbfw_heal_injected_meta() {
			if ( 'done' === get_option( 'rbfw_poi_meta_healed' ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			global $wpdb;

			/*
			 * Page forward on meta_id instead of re-running the same LIMIT. A row can
			 * match the coarse LIKE but fail the strict token test below; without a
			 * cursor those rows are re-selected on every pass and the cleanup never
			 * reaches the end of the table.
			 */
			$cursor = (int) get_option( 'rbfw_poi_meta_heal_cursor', 0 );

			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_id, post_id, meta_value
					   FROM {$wpdb->postmeta}
					  WHERE meta_id > %d
					    AND ( meta_key LIKE 'rbfw\\_%%'
					       OR meta_key LIKE '\\_rbfw\\_%%'
					       OR meta_key LIKE 'rdfw\\_%%'
					       OR meta_key LIKE '\\_rdfw\\_%%' )
					    AND meta_value LIKE %s
					  ORDER BY meta_id ASC
					  LIMIT 200",
					$cursor,
					'%O:%:"%'
				)
			);

			if ( empty( $rows ) ) {
				update_option( 'rbfw_poi_meta_healed', 'done', false );
				delete_option( 'rbfw_poi_meta_heal_cursor' );

				return;
			}

			foreach ( $rows as $row ) {
				$cursor = max( $cursor, (int) $row->meta_id );
				// Only rows that really carry an object token, not ones whose text
				// merely contains "O:". Covers objects and enums at any nesting.
				if ( ! preg_match( '/(^|[;{:])[OCE]:\d+:"/', (string) $row->meta_value ) ) {
					continue;
				}

				$clean = rbfw_safe_unserialize( (string) $row->meta_value );

				$wpdb->update(
					$wpdb->postmeta,
					array( 'meta_value' => maybe_serialize( $clean ) ),
					array( 'meta_id' => (int) $row->meta_id )
				);
				wp_cache_delete( (int) $row->post_id, 'post_meta' );
			}

			update_option( 'rbfw_poi_meta_heal_cursor', $cursor, false );
		}

		add_action( 'admin_init', 'rbfw_heal_injected_meta', 5 );
	}
