<?php
	/**
	 * Shared helpers. Everything that answers "which form, if any, belongs to
	 * this rental item?" lives here so the admin, frontend and mapper can never
	 * disagree about it.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! function_exists( 'rbfw_gf_cpt' ) ) {
		/**
		 * The rental item post type, read from the host plugin rather than
		 * hardcoded, so a future rename does not silently detach the bridge.
		 */
		function rbfw_gf_cpt(): string {
			if ( class_exists( 'RBFW_Function' ) && method_exists( 'RBFW_Function', 'get_cpt_name' ) ) {
				return RBFW_Function::get_cpt_name();
			}

			return 'rbfw_item';
		}
	}

	if ( ! function_exists( 'rbfw_gf_get_forms' ) ) {
		/**
		 * Every active Gravity Form on the site, as id => title, for the admin
		 * dropdown. There is no fixed form id anywhere in this plugin: whatever
		 * forms exist when the editor is opened are what the admin can choose.
		 *
		 * @return array<int,string>
		 */
		function rbfw_gf_get_forms(): array {
			$out = array();

			if ( ! class_exists( 'GFAPI' ) ) {
				return $out;
			}

			// true = active forms only; inactive forms cannot be rendered anyway.
			$forms = GFAPI::get_forms( true );
			if ( is_wp_error( $forms ) || ! is_array( $forms ) ) {
				return $out;
			}

			foreach ( $forms as $form ) {
				if ( empty( $form['id'] ) ) {
					continue;
				}
				$title = isset( $form['title'] ) && '' !== trim( (string) $form['title'] )
					? (string) $form['title']
					/* translators: %d: Gravity Forms form ID used when a form has no title. */
					: sprintf( __( 'Form #%d', 'booking-and-rental-manager-for-woocommerce' ), (int) $form['id'] );

				$out[ (int) $form['id'] ] = $title;
			}

			asort( $out, SORT_NATURAL | SORT_FLAG_CASE );

			return $out;
		}
	}

	if ( ! function_exists( 'rbfw_gf_default_form_id' ) ) {
		/**
		 * Site-wide fallback, so a newly created rental can inherit a form
		 * instead of starting with none. Lives on the plugin's General settings
		 * tab and is read through the plugin's own settings API.
		 *
		 * Read straight from the settings option rather than through
		 * rbfw_get_option(), which depends on the global $rbfw settings object.
		 * That global is null outside a normal web request — under WP-CLI and in
		 * some early-hook contexts — where rbfw_get_option() bails and returns
		 * null. Going to the option directly is the same lookup
		 * RBFW_Settings_API::get_option_trans() performs, minus that dependency,
		 * so the default resolves identically in CLI, cron and web requests.
		 */
		function rbfw_gf_default_form_id(): int {
			$settings = get_option( 'rbfw_basic_gen_settings' );

			if ( ! is_array( $settings ) || ! isset( $settings['rbfw_gf_default_form'] ) ) {
				return 0;
			}

			$value = $settings['rbfw_gf_default_form'];

			return is_scalar( $value ) ? max( 0, (int) $value ) : 0;
		}
	}

	if ( ! function_exists( 'rbfw_gf_force_order_form' ) ) {
		/**
		 * Whether every rental with an attached Gravity form should treat that form
		 * as its order form.
		 *
		 * Lives on the plugin's General settings tab. Read straight from the option
		 * for the same reason rbfw_gf_default_form_id() does — the settings-API
		 * global is absent outside a normal web request.
		 */
		function rbfw_gf_force_order_form(): bool {
			$settings = get_option( 'rbfw_basic_gen_settings' );

			return is_array( $settings )
				&& isset( $settings['rbfw_gf_force_order_form'] )
				&& 'yes' === (string) $settings['rbfw_gf_force_order_form'];
		}
	}

	if ( ! function_exists( 'rbfw_gf_form_id_for_item' ) ) {
		/**
		 * The form attached to one rental item.
		 *
		 * Resolution order: explicit per-item choice, then the site default, then
		 * none. A per-item value of -1 means "explicitly none" so an admin can
		 * opt a single rental out of a site-wide default.
		 *
		 * Returns 0 when no form applies, or when the chosen form no longer
		 * exists — a deleted form must not produce a broken render.
		 */
		function rbfw_gf_form_id_for_item( $item_id ): int {
			$item_id = (int) $item_id;
			if ( $item_id <= 0 ) {
				return 0;
			}

			$raw = get_post_meta( $item_id, RBFW_GF_META_FORM, true );

			if ( '-1' === (string) $raw ) {
				$form_id = 0;
			} elseif ( '' === (string) $raw ) {
				$form_id = rbfw_gf_default_form_id();
			} else {
				$form_id = (int) $raw;
			}

			$form_id = (int) apply_filters( 'rbfw_gf_form_id', $form_id, $item_id );

			if ( $form_id > 0 && ! rbfw_gf_form_exists( $form_id ) ) {
				return 0;
			}

			return max( 0, $form_id );
		}
	}

	if ( ! function_exists( 'rbfw_gf_form_exists' ) ) {
		/**
		 * Cheap existence check, memoised per request because the frontend asks
		 * for the same form id several times while rendering one page.
		 */
		function rbfw_gf_form_exists( $form_id ): bool {
			static $cache = array();

			$form_id = (int) $form_id;
			if ( $form_id <= 0 ) {
				return false;
			}
			if ( isset( $cache[ $form_id ] ) ) {
				return $cache[ $form_id ];
			}

			$form                = class_exists( 'GFAPI' ) ? GFAPI::get_form( $form_id ) : false;
			$cache[ $form_id ]   = ( is_array( $form ) && ! empty( $form['id'] ) && ! empty( $form['is_active'] ) );

			return $cache[ $form_id ];
		}
	}

	if ( ! function_exists( 'rbfw_gf_mode_for_item' ) ) {
		/**
		 * 'required' — the booking form stays hidden until the Gravity form is
		 *              completed. Use when the answers are needed to fulfil.
		 * 'optional' — both are shown; answers attach only if the form was sent.
		 * 'booking'  — the Gravity form IS the order form: the rental booking form
		 *              is not rendered, and submitting the Gravity form opens the
		 *              booking directly. Availability and inventory cannot gate it,
		 *              because a Gravity form carries no rental calendar.
		 */
		function rbfw_gf_mode_for_item( $item_id ): string {
			$item_id = (int) $item_id;

			$mode = (string) get_post_meta( $item_id, RBFW_GF_META_MODE, true );
			if ( ! in_array( $mode, array( 'required', 'optional', 'booking' ), true ) ) {
				$mode = 'required';
			}

			/**
			 * Site-wide override: treat the attached Gravity form as the order form
			 * on every rental, whatever each rental's own setting says.
			 *
			 * The per-item value is written on every save of a rental — the select
			 * always posts something — so an item reading 'required' has not
			 * necessarily been given that mode deliberately. This switch lets a shop
			 * that runs one ordering flow set it once instead of revisiting every
			 * rental.
			 */
			if ( rbfw_gf_force_order_form() && rbfw_gf_form_id_for_item( $item_id ) > 0 ) {
				$mode = 'booking';
			}

			return (string) apply_filters( 'rbfw_gf_mode', $mode, $item_id );
		}
	}

	if ( ! function_exists( 'rbfw_gf_section_title_for_item' ) ) {
		/**
		 * Heading shown above the embedded form. Falls back to the form's own
		 * title so the section is never unlabelled.
		 */
		function rbfw_gf_section_title_for_item( $item_id, $form_id ): string {
			$title = trim( (string) get_post_meta( (int) $item_id, RBFW_GF_META_TITLE, true ) );

			if ( '' === $title ) {
				$forms = rbfw_gf_get_forms();
				$title = isset( $forms[ (int) $form_id ] ) ? $forms[ (int) $form_id ] : '';
			}

			return $title;
		}
	}

	if ( ! function_exists( 'rbfw_gf_forms_in_use' ) ) {
		/**
		 * Every form id attached to at least one rental item, plus the site
		 * default. Used to decide whether a given Gravity Forms submission is
		 * any of this bridge's business — which is what keeps the submission
		 * hooks form-agnostic instead of bound to one hardcoded id.
		 *
		 * Cached for the request; invalidated on save via the admin class.
		 *
		 * @return int[]
		 */
		function rbfw_gf_forms_in_use(): array {
			$cached = wp_cache_get( 'rbfw_gf_forms_in_use', 'rbfw_gf' );
			if ( is_array( $cached ) ) {
				return $cached;
			}

			global $wpdb;

			$ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
					RBFW_GF_META_FORM
				)
			);

			$ids = array_map( 'intval', (array) $ids );

			$default = rbfw_gf_default_form_id();
			if ( $default > 0 ) {
				$ids[] = $default;
			}

			$ids = array_values( array_unique( array_filter( $ids, static fn( $id ) => $id > 0 ) ) );

			wp_cache_set( 'rbfw_gf_forms_in_use', $ids, 'rbfw_gf', 300 );

			return $ids;
		}
	}

	if ( ! function_exists( 'rbfw_gf_flush_forms_in_use' ) ) {
		function rbfw_gf_flush_forms_in_use(): void {
			wp_cache_delete( 'rbfw_gf_forms_in_use', 'rbfw_gf' );
		}
	}

	if ( ! function_exists( 'rbfw_gf_item_id_from_post' ) ) {
		/**
		 * The rental item behind the current submission.
		 *
		 * Shared by both checkout modes. The booking form posts rbfw_post_id (see
		 * multi-day-registration.php:876), which is the normal path in either
		 * mode. The product fallback only matters under WooCommerce, where an
		 * add-to-cart may arrive knowing just the product id.
		 *
		 * The link is stored the other way round — the rental item holds
		 * `link_wc_product` pointing at its product — so product → item needs a
		 * reverse lookup. A duplicated rental can inherit that link, so one
		 * product may back several items (see RBFW_Hidden_Product.php:131); an
		 * ambiguous match is treated as no match rather than guessing at the
		 * wrong rental's form.
		 */
		function rbfw_gf_item_id_from_post( $product_id = 0 ): int {
			if ( isset( $_POST['rbfw_post_id'] ) ) {
				$item_id = absint( wp_unslash( $_POST['rbfw_post_id'] ) );
				if ( $item_id > 0 && rbfw_gf_cpt() === get_post_type( $item_id ) ) {
					return $item_id;
				}
			}

			$product_id = (int) $product_id;
			if ( $product_id <= 0 ) {
				return 0;
			}

			global $wpdb;

			$item_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'link_wc_product' AND meta_value = %s LIMIT 2",
					(string) $product_id
				)
			);

			return ( is_array( $item_ids ) && 1 === count( $item_ids ) ) ? (int) $item_ids[0] : 0;
		}
	}

	if ( ! function_exists( 'rbfw_gf_verified_entry_from_post' ) ) {
		/**
		 * The posted Gravity entry for this rental, or null when absent or not
		 * provably the visitor's own.
		 *
		 * Memoised per item because both the validation step and the persist step
		 * need it within one request, and the second must not be defeated by the
		 * single-use claim recorded by the first.
		 *
		 * @return array|null
		 */
		function rbfw_gf_verified_entry_from_post( int $item_id ): ?array {
			static $cache = array();

			if ( array_key_exists( $item_id, $cache ) ) {
				return $cache[ $item_id ];
			}

			$entry_id = isset( $_POST['rbfw_gf_entry_id'] ) ? absint( wp_unslash( $_POST['rbfw_gf_entry_id'] ) ) : 0;
			$token    = isset( $_POST['rbfw_gf_token'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_gf_token'] ) ) : '';

			$cache[ $item_id ] = RBFW_GF_Entry_Store::verify( $entry_id, $token, $item_id );

			return $cache[ $item_id ];
		}
	}

	if ( ! function_exists( 'rbfw_gf_answers_required' ) ) {
		/**
		 * Whether an add-to-cart / native checkout must be refused when the form has
		 * not been answered.
		 *
		 * True only for 'required'. In 'booking' mode there is no second form to
		 * gate — the Gravity submission is itself the booking — so gating the
		 * checkout endpoints there would block nothing useful.
		 */
		function rbfw_gf_answers_required( int $item_id ): bool {
			return rbfw_gf_form_id_for_item( $item_id ) > 0
				&& 'required' === rbfw_gf_mode_for_item( $item_id );
		}
	}
