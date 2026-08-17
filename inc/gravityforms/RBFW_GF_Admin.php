<?php
	/**
	 * Admin side: choose a Gravity Form per rental item.
	 *
	 * The dropdown is rebuilt from GFAPI::get_forms() on every render, so however
	 * many forms the site has — one or a hundred — all of them are selectable on
	 * every rental item. No form id is known in advance anywhere in this module.
	 *
	 * A rental can be edited in either of the two editors the plugin ships, so the
	 * panel is registered twice: as a card in the modern editor's extension
	 * section, and as a metabox on the classic edit screen. Both write the same
	 * three meta keys, and each uses its own host's markup conventions so neither
	 * looks bolted on.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Admin' ) ) {

		class RBFW_GF_Admin {

			const NONCE = 'rbfw_gf_save_item';

			public function __construct() {
				/* Modern editor (single-page AJAX-saved rental editor). */
				add_action( 'rbfw_modern_editor_advanced_sections', array( $this, 'render_modern_panel' ) );
				add_action( 'rbfw_modern_editor_save', array( $this, 'save_item' ) );

				/* Classic editor (WP post edit screen with metaboxes). */
				add_action( 'add_meta_boxes', array( $this, 'add_classic_metabox' ) );
				add_action( 'save_post', array( $this, 'save_classic' ), 20, 2 );

				/* Site-wide default form, added to the plugin's own General
				 * settings tab rather than a separate screen. */
				add_filter( 'rbfw_settings_field', array( $this, 'add_default_form_setting' ) );
				add_action( 'update_option_rbfw_basic_gen_settings', 'rbfw_gf_flush_forms_in_use' );

				/* Entry detail box linking a Gravity entry back to its order. */
				add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'entry_detail_box' ), 10, 3 );

				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			}

			/**
			 * Only the classic metabox needs styling of its own; the modern editor
			 * card reuses the editor's existing card and field classes.
			 */
			public function enqueue(): void {
				$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
				if ( ! $screen || rbfw_gf_cpt() !== $screen->post_type ) {
					return;
				}

				wp_enqueue_style(
					'rbfw-gf-admin',
					RBFW_PLUGIN_URL . '/assets/gravityforms/rbfw_gf_admin.css',
					array(),
					RBFW_GF_ASSET_VER
				);
			}

			/* ── Modern editor ────────────────────────────────────────────────── */

			/**
			 * Rendered as a .rbfw-me-card among the editor's own cards, using the
			 * same head/body/field markup so it is indistinguishable from the
			 * sections shipped with the editor.
			 *
			 * @param int $post_id
			 */
			public function render_modern_panel( $post_id ): void {
				$post_id = (int) $post_id;
				if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				$forms = rbfw_gf_get_forms();
				?>
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<div>
							<h2><?php esc_html_e( 'Booking Questions (Gravity Forms)', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Ask a Gravity Form before this rental is booked. Answers are saved with the booking and shown on the order, the booking record and the confirmation email.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
					</div>
					<div class="rbfw-me-card__body">
						<?php if ( ! $forms ) : ?>
							<p class="rbfw-me-field__help">
								<?php
									printf(
										/* translators: %s: link to the Gravity Forms new-form screen. */
										esc_html__( 'No Gravity Forms exist yet. %s and it will appear here.', 'booking-and-rental-manager-for-woocommerce' ),
										'<a href="' . esc_url( admin_url( 'admin.php?page=gf_new_form' ) ) . '">' . esc_html__( 'Create one', 'booking-and-rental-manager-for-woocommerce' ) . '</a>'
									);
								?>
							</p>
						<?php else : ?>
							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label" for="rbfw_gf_form_id">
									<?php esc_html_e( 'Form to ask before booking', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</label>
								<select class="rbfw-me-input" id="rbfw_gf_form_id" name="<?php echo esc_attr( RBFW_GF_META_FORM ); ?>">
									<?php $this->form_options( $post_id, $forms ); ?>
								</select>
								<p class="rbfw-me-field__hint">
									<?php esc_html_e( 'Every active Gravity Form on this site is listed here.', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</p>
							</div>

							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label" for="rbfw_gf_mode">
									<?php esc_html_e( 'When is it asked?', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</label>
								<select class="rbfw-me-input" id="rbfw_gf_mode" name="<?php echo esc_attr( RBFW_GF_META_MODE ); ?>">
									<?php $this->mode_options( $post_id ); ?>
								</select>
								<p class="rbfw-me-field__hint">
									<?php esc_html_e( 'Choosing "This form IS the order form" hides the rental booking form. Dates come from the Gravity form, so availability, inventory and off-days no longer restrict the booking.', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</p>
							</div>

							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label" for="rbfw_gf_section_title">
									<?php esc_html_e( 'Heading above the form', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<span class="rbfw-me-field__optional">(<?php esc_html_e( 'optional', 'booking-and-rental-manager-for-woocommerce' ); ?>)</span>
								</label>
								<input class="rbfw-me-input" type="text" id="rbfw_gf_section_title"
									name="<?php echo esc_attr( RBFW_GF_META_TITLE ); ?>"
									value="<?php echo esc_attr( (string) get_post_meta( $post_id, RBFW_GF_META_TITLE, true ) ); ?>"
									placeholder="<?php esc_attr_e( 'Uses the form title when left empty', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			/* ── Classic editor ───────────────────────────────────────────────── */

			public function add_classic_metabox(): void {
				add_meta_box(
					'rbfw_gf_form_box',
					__( 'Booking Questions (Gravity Forms)', 'booking-and-rental-manager-for-woocommerce' ),
					array( $this, 'render_classic_metabox' ),
					rbfw_gf_cpt(),
					'side',
					'default'
				);
			}

			public function render_classic_metabox( $post ): void {
				$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
				if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				$forms = rbfw_gf_get_forms();

				wp_nonce_field( self::NONCE, 'rbfw_gf_nonce' );

				echo '<div class="rbfw-gf-panel">';

				if ( ! $forms ) {
					echo '<p class="rbfw-gf-help">';
					printf(
						/* translators: %s: link to the Gravity Forms new-form screen. */
						esc_html__( 'No Gravity Forms exist yet. %s and it will appear here.', 'booking-and-rental-manager-for-woocommerce' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=gf_new_form' ) ) . '">' . esc_html__( 'Create one', 'booking-and-rental-manager-for-woocommerce' ) . '</a>'
					);
					echo '</p></div>';

					return;
				}
				?>
				<p class="rbfw-gf-field">
					<label for="rbfw_gf_form_id_classic"><strong><?php esc_html_e( 'Form to ask before booking', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
					<select class="widefat" id="rbfw_gf_form_id_classic" name="<?php echo esc_attr( RBFW_GF_META_FORM ); ?>">
						<?php $this->form_options( $post_id, $forms ); ?>
					</select>
					<span class="rbfw-gf-help"><?php esc_html_e( 'Answers are saved with the booking and shown on the order, the booking record and the confirmation email.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</p>

				<p class="rbfw-gf-field">
					<label for="rbfw_gf_mode_classic"><strong><?php esc_html_e( 'When is it asked?', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
					<select class="widefat" id="rbfw_gf_mode_classic" name="<?php echo esc_attr( RBFW_GF_META_MODE ); ?>">
						<?php $this->mode_options( $post_id ); ?>
					</select>
				</p>

				<p class="rbfw-gf-field">
					<label for="rbfw_gf_title_classic"><strong><?php esc_html_e( 'Heading above the form', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
					<input type="text" class="widefat" id="rbfw_gf_title_classic"
						name="<?php echo esc_attr( RBFW_GF_META_TITLE ); ?>"
						value="<?php echo esc_attr( (string) get_post_meta( $post_id, RBFW_GF_META_TITLE, true ) ); ?>"
						placeholder="<?php esc_attr_e( 'Uses the form title when left empty', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
				</p>
				<?php
				echo '</div>';
			}

			/* ── Shared option lists ──────────────────────────────────────────── */

			/**
			 * @param int               $post_id
			 * @param array<int,string> $forms
			 */
			private function form_options( int $post_id, array $forms ): void {
				$current    = (string) get_post_meta( $post_id, RBFW_GF_META_FORM, true );
				$default_id = rbfw_gf_default_form_id();

				$default_label = ( $default_id && isset( $forms[ $default_id ] ) )
					/* translators: %s: title of the form set as the site-wide default. */
					? sprintf( __( 'Site default (%s)', 'booking-and-rental-manager-for-woocommerce' ), $forms[ $default_id ] )
					: __( 'Site default (none set)', 'booking-and-rental-manager-for-woocommerce' );

				printf(
					'<option value="" %s>%s</option>',
					selected( '', $current, false ),
					esc_html( $default_label )
				);
				printf(
					'<option value="-1" %s>%s</option>',
					selected( '-1', $current, false ),
					esc_html__( '— No form for this rental —', 'booking-and-rental-manager-for-woocommerce' )
				);

				foreach ( $forms as $form_id => $form_title ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( (string) $form_id ),
						selected( (string) $form_id, $current, false ),
						esc_html( $form_title . ' (#' . $form_id . ')' )
					);
				}
			}

			private function mode_options( int $post_id ): void {
				$mode = rbfw_gf_mode_for_item( $post_id );

				printf(
					'<option value="required" %s>%s</option>',
					selected( 'required', $mode, false ),
					esc_html__( 'Before booking — dates appear after it is sent', 'booking-and-rental-manager-for-woocommerce' )
				);
				printf(
					'<option value="optional" %s>%s</option>',
					selected( 'optional', $mode, false ),
					esc_html__( 'Optional — customer can book without answering', 'booking-and-rental-manager-for-woocommerce' )
				);
				printf(
					'<option value="booking" %s>%s</option>',
					selected( 'booking', $mode, false ),
					esc_html__( 'This form IS the order form — submitting it creates the booking', 'booking-and-rental-manager-for-woocommerce' )
				);
			}

			/* ── Saving ───────────────────────────────────────────────────────── */

			/**
			 * Modern editor path. Reached from the editor's ajax_save(), which has
			 * already verified its nonce; the capability is re-checked here because
			 * this method hangs off a public action and must not trust its caller.
			 */
			public function save_item( $post_id ): void {
				$post_id = (int) $post_id;
				if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				$this->persist( $post_id );
			}

			public function save_classic( $post_id, $post ): void {
				$post_id = (int) $post_id;

				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! $post || rbfw_gf_cpt() !== $post->post_type ) {
					return;
				}
				if ( wp_is_post_revision( $post_id ) ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				$nonce = isset( $_POST['rbfw_gf_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_gf_nonce'] ) ) : '';
				if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE ) ) {
					return;
				}

				$this->persist( $post_id );
			}

			/**
			 * Absent keys are left alone rather than cleared. Both editors post
			 * partial payloads depending on which panels rendered, and treating a
			 * missing field as "set to empty" would silently detach forms from
			 * rentals whenever an unrelated section was saved.
			 */
			private function persist( int $post_id ): void {
				if ( isset( $_POST[ RBFW_GF_META_FORM ] ) ) {
					$raw = sanitize_text_field( wp_unslash( $_POST[ RBFW_GF_META_FORM ] ) );

					if ( '' === $raw ) {
						delete_post_meta( $post_id, RBFW_GF_META_FORM );
					} elseif ( '-1' === $raw ) {
						update_post_meta( $post_id, RBFW_GF_META_FORM, '-1' );
					} else {
						$form_id = absint( $raw );
						// Store only a form that exists, so the frontend never has
						// to render a phantom id.
						if ( $form_id > 0 && rbfw_gf_form_exists( $form_id ) ) {
							update_post_meta( $post_id, RBFW_GF_META_FORM, (string) $form_id );
						}
					}

					rbfw_gf_flush_forms_in_use();
				}

				if ( isset( $_POST[ RBFW_GF_META_MODE ] ) ) {
					$mode = sanitize_text_field( wp_unslash( $_POST[ RBFW_GF_META_MODE ] ) );
					if ( ! in_array( $mode, array( 'required', 'optional' ), true ) ) {
						$mode = 'required';
					}
					update_post_meta( $post_id, RBFW_GF_META_MODE, $mode );
				}

				if ( isset( $_POST[ RBFW_GF_META_TITLE ] ) ) {
					$title = sanitize_text_field( wp_unslash( $_POST[ RBFW_GF_META_TITLE ] ) );
					if ( '' === $title ) {
						delete_post_meta( $post_id, RBFW_GF_META_TITLE );
					} else {
						update_post_meta( $post_id, RBFW_GF_META_TITLE, $title );
					}
				}
			}

			/* ── Site-wide default, in the plugin's own settings ──────────────── */

			/**
			 * Adds the default-form select to the plugin's existing General
			 * settings tab, so all plugin settings stay in one place.
			 *
			 * @param array $settings_fields
			 *
			 * @return array
			 */
			public function add_default_form_setting( $settings_fields ) {
				if ( ! is_array( $settings_fields ) || ! isset( $settings_fields['rbfw_basic_gen_settings'] ) ) {
					return $settings_fields;
				}

				$options = array( '0' => esc_html__( '— None —', 'booking-and-rental-manager-for-woocommerce' ) );
				foreach ( rbfw_gf_get_forms() as $form_id => $form_title ) {
					$options[ (string) $form_id ] = $form_title . ' (#' . $form_id . ')';
				}

				$settings_fields['rbfw_basic_gen_settings'][] = array(
					'name'    => 'rbfw_gf_default_form',
					'label'   => esc_html__( 'Default Gravity Form', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Asked before booking on rentals that have no form of their own. Each rental can override this, or opt out, in its own editor.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'select',
					'default' => '0',
					'options' => $options,
				);

				$settings_fields['rbfw_basic_gen_settings'][] = array(
					'name'    => 'rbfw_gf_force_order_form',
					'label'   => esc_html__( 'Gravity form is the order form', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Applies to every rental that has a form attached: the rental booking form is hidden and submitting the Gravity form creates the booking. Overrides each rental\'s own "When is it asked?" setting. Availability, inventory and off-days cannot restrict these bookings, because a Gravity form carries no rental calendar.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'no'  => esc_html__( 'No — each rental decides', 'booking-and-rental-manager-for-woocommerce' ),
						'yes' => esc_html__( 'Yes — for all rentals with a form', 'booking-and-rental-manager-for-woocommerce' ),
					),
				);

				return $settings_fields;
			}

			/* ── Entry detail: link back to the order ─────────────────────────── */

			public function entry_detail_box( $meta_boxes, $entry, $form ) {
				if ( empty( $entry['id'] ) || empty( $form['id'] ) ) {
					return $meta_boxes;
				}
				if ( ! in_array( (int) $form['id'], rbfw_gf_forms_in_use(), true ) ) {
					return $meta_boxes;
				}

				$meta_boxes['rbfw_gf_booking'] = array(
					'title'    => __( 'Rental booking', 'booking-and-rental-manager-for-woocommerce' ),
					'callback' => array( $this, 'render_entry_detail_box' ),
					'context'  => 'side',
				);

				return $meta_boxes;
			}

			public function render_entry_detail_box( $args ): void {
				$entry    = isset( $args['entry'] ) ? $args['entry'] : array();
				$entry_id = isset( $entry['id'] ) ? (int) $entry['id'] : 0;
				if ( $entry_id <= 0 ) {
					return;
				}

				$order_id   = (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_ORDER );
				$booking_id = (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_BOOKING );
				$item_id    = (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_ITEM );

				echo '<div class="rbfw-gf-entry-box">';

				if ( $item_id > 0 ) {
					echo '<p><strong>' . esc_html__( 'Rental:', 'booking-and-rental-manager-for-woocommerce' ) . '</strong><br />';
					echo esc_html( get_the_title( $item_id ) );
					echo '</p>';
				}

				// Custom (standalone) mode has a booking post but no order.
				if ( $booking_id > 0 ) {
					echo '<p><strong>' . esc_html__( 'Booking:', 'booking-and-rental-manager-for-woocommerce' ) . '</strong><br />';
					$reference = (string) get_post_meta( $booking_id, 'rbfw_reference', true );
					echo '<a href="' . esc_url( get_edit_post_link( $booking_id ) ) . '">'
						. esc_html( '' !== $reference ? $reference : '#' . $booking_id )
						. '</a></p>';
				}

				if ( $order_id > 0 ) {
					echo '<p><strong>' . esc_html__( 'Order:', 'booking-and-rental-manager-for-woocommerce' ) . '</strong><br />';
					$edit_link = function_exists( 'wc_get_order' ) ? admin_url( 'post.php?post=' . $order_id . '&action=edit' ) : '';
					if ( $edit_link ) {
						echo '<a href="' . esc_url( $edit_link ) . '">#' . esc_html( (string) $order_id ) . '</a>';
					} else {
						echo '#' . esc_html( (string) $order_id );
					}
					echo '</p>';
				} elseif ( $booking_id <= 0 ) {
					echo '<p class="rbfw-gf-muted">' . esc_html__( 'Not yet attached to a booking.', 'booking-and-rental-manager-for-woocommerce' ) . '</p>';
				}

				echo '</div>';
			}
		}
	}
