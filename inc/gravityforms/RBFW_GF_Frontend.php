<?php
	/**
	 * Frontend: render the chosen form above the booking form.
	 *
	 * Placement is the whole design problem here. The rental booking template
	 * opens its own <form method="post"> (multi-day-registration.php:130) and the
	 * existing "registration fields" block renders inside it. Gravity Forms emits
	 * its own <form>, and nested forms are invalid HTML — browsers silently drop
	 * the inner one, so the Gravity fields would post nothing at all.
	 *
	 * Stripping Gravity's form wrapper to inline the fields is the tempting fix
	 * and the wrong one: it breaks Gravity's validation, multi-page state,
	 * conditional logic and anti-spam, all of which a long multi-step form
	 * depends on.
	 *
	 * So the form is rendered as a sibling *before* the booking form, using the
	 * host plugin's own `booking_form_header` action — which fires at
	 * single/default/multi-day.php:148, one line before the form template is
	 * included, and exists identically in all eight single templates across both
	 * bundled themes. No template overrides are needed.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Frontend' ) ) {

		class RBFW_GF_Frontend {

			public function __construct() {
				add_action( 'booking_form_header', array( $this, 'render' ), 10, 1 );
				// Priority 95 so this lands after the plugin's own frontend
				// enqueue at 90, and still well before the templates render.
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 95 );

				/* Let the rental context prefill matching form fields. */
				add_filter( 'gform_field_value_rbfw_item', array( $this, 'prefill_item_title' ) );
				add_filter( 'gform_field_value_rbfw_item_id', array( $this, 'prefill_item_id' ) );
				add_filter( 'gform_field_value_rbfw_item_url', array( $this, 'prefill_item_url' ) );

				/* {rbfw_item} / {rbfw_order_id} merge tags for GF notifications. */
				add_filter( 'gform_replace_merge_tags', array( $this, 'merge_tags' ), 10, 3 );
			}

			/**
			 * Resolved here rather than at render time: assets enqueued from
			 * inside the template would be registered after wp_head has run, and
			 * wp_localize_script has to happen before the handle is printed.
			 * Deciding it during wp_enqueue_scripts keeps both correct.
			 */
			public function enqueue(): void {
				$item_id = $this->singular_item_id();
				if ( $item_id <= 0 ) {
					return;
				}

				$form_id = rbfw_gf_form_id_for_item( $item_id );
				if ( $form_id <= 0 ) {
					// No form attached: nothing is loaded, and the booking form
					// behaves exactly as it did before this module existed.
					return;
				}

				wp_enqueue_style(
					'rbfw-gf',
					RBFW_PLUGIN_URL . '/assets/gravityforms/rbfw_gf.css',
					array(),
					RBFW_GF_ASSET_VER
				);
				wp_enqueue_script(
					'rbfw-gf',
					RBFW_PLUGIN_URL . '/assets/gravityforms/rbfw_gf.js',
					array( 'jquery' ),
					RBFW_GF_ASSET_VER,
					true
				);
				wp_localize_script(
					'rbfw-gf',
					'rbfwGf',
					array(
						'i18n' => array(
							'answersSaved' => __( 'Your answers are saved and will be sent with this booking.', 'booking-and-rental-manager-for-woocommerce' ),
						),
					)
				);
			}

			/**
			 * The rental item being viewed, or 0 when this is not a single rental
			 * page. The module only ever renders on a single rental, so archive and
			 * shortcode listings load none of its assets.
			 */
			private function singular_item_id(): int {
				if ( ! is_singular( rbfw_gf_cpt() ) ) {
					return 0;
				}

				$post = get_queried_object();

				return ( $post instanceof WP_Post ) ? (int) $post->ID : 0;
			}

			/**
			 * @param int $post_id Rental item id, passed by the host plugin's action.
			 */
			public function render( $post_id ): void {
				$item_id = (int) $post_id;
				if ( $item_id <= 0 ) {
					return;
				}

				$form_id = rbfw_gf_form_id_for_item( $item_id );
				if ( $form_id <= 0 ) {
					// No form attached: the booking form renders exactly as it did
					// before this plugin existed.
					return;
				}

				// If the assets were not resolved during wp_enqueue_scripts — a
				// template rendering a rental outside a singular query — skip
				// rather than emit a form the gate script cannot drive.
				if ( ! wp_script_is( 'rbfw-gf', 'enqueued' ) ) {
					return;
				}

				$mode  = rbfw_gf_mode_for_item( $item_id );
				$title = rbfw_gf_section_title_for_item( $item_id, $form_id );

				$classes = 'rbfw-gf-wrap rbfw-gf-wrap--' . $mode;
				?>
				<div class="<?php echo esc_attr( $classes ); ?>"
					data-rbfw-gf-item="<?php echo esc_attr( (string) $item_id ); ?>"
					data-rbfw-gf-form="<?php echo esc_attr( (string) $form_id ); ?>"
					data-rbfw-gf-mode="<?php echo esc_attr( $mode ); ?>">

					<?php if ( '' !== $title ) : ?>
						<h3 class="rbfw-gf-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>

					<div class="rbfw-gf-form">
						<?php
							/**
							 * ajax = true keeps the whole multi-page walk inside this
							 * container, so paging between steps never reloads the
							 * rental page and never loses the booking form's state.
							 */
							gravity_form(
								$form_id,
								false,
								false,
								false,
								$this->prefill_values( $item_id ),
								true,
								0,
								true
							);
						?>
					</div>

					<?php if ( 'required' === $mode ) : ?>
						<p class="rbfw-gf-gate-note">
							<?php esc_html_e( 'Complete the questions above to continue to dates and availability.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</p>
					<?php endif; ?>
					<?php /* In 'booking' mode this form is the order form, so there is no
					         second step to explain — the booking form is never shown. */ ?>
				</div>
				<?php
			}

			/**
			 * Values offered to any field with "Allow field to be populated
			 * dynamically" enabled. Harmless when the form uses none of them.
			 *
			 * @return array<string,mixed>
			 */
			private function prefill_values( int $item_id ): array {
				return array(
					'rbfw_item'    => get_the_title( $item_id ),
					'rbfw_item_id' => $item_id,
					'rbfw_item_url'=> get_permalink( $item_id ),
				);
			}

			/* ── Prefill filters ──────────────────────────────────────────────── */

			public function prefill_item_title( $value ) {
				$id = $this->current_item_id();

				return $id ? get_the_title( $id ) : $value;
			}

			public function prefill_item_id( $value ) {
				$id = $this->current_item_id();

				return $id ? $id : $value;
			}

			public function prefill_item_url( $value ) {
				$id = $this->current_item_id();

				return $id ? get_permalink( $id ) : $value;
			}

			private function current_item_id(): int {
				if ( isset( $_POST['rbfw_gf_item_id'] ) ) {
					return absint( wp_unslash( $_POST['rbfw_gf_item_id'] ) );
				}

				$post = get_post();

				return ( $post && rbfw_gf_cpt() === $post->post_type ) ? (int) $post->ID : 0;
			}

			/* ── Merge tags for GF notifications ──────────────────────────────── */

			public function merge_tags( $text, $form, $entry ) {
				if ( ! is_string( $text ) || false === strpos( $text, '{rbfw_' ) ) {
					return $text;
				}

				$entry_id = ( is_array( $entry ) && ! empty( $entry['id'] ) ) ? (int) $entry['id'] : 0;
				$item_id  = $entry_id ? (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_ITEM ) : 0;
				$order_id = $entry_id ? (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_ORDER ) : 0;

				return str_replace(
					array( '{rbfw_item}', '{rbfw_item_id}', '{rbfw_order_id}' ),
					array(
						$item_id ? get_the_title( $item_id ) : '',
						$item_id ? (string) $item_id : '',
						$order_id ? (string) $order_id : '',
					),
					$text
				);
			}
		}
	}
