<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Location' ) ) {
		class RBFW_Location {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
				// Inline location (taxonomy term) CRUD — shared by the classic and
				// modern editors so locations can be managed without leaving the page.
				add_action( 'wp_ajax_rbfw_location_add',    [ $this, 'ajax_location_add' ] );
				add_action( 'wp_ajax_rbfw_location_update', [ $this, 'ajax_location_update' ] );
				add_action( 'wp_ajax_rbfw_location_delete', [ $this, 'ajax_location_delete' ] );
			}

			/**
			 * Return every location as a flat list. `value` is sanitize_title( name ),
			 * the identity the render + save use throughout (NOT the raw term slug),
			 * so the JS that rebuilds checkboxes/options keys on the same value.
			 *
			 * @return array[] List of [ term_id, name, value ].
			 */
			public function rbfw_get_location_list() {
				$terms = get_terms( array( 'taxonomy' => 'rbfw_item_location', 'hide_empty' => false ) );
				$out   = array();
				if ( ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$out[] = array(
							'term_id' => (int) $term->term_id,
							'name'    => $term->name,
							'value'   => sanitize_title( $term->name ),
						);
					}
				}
				return $out;
			}

			/** Shared guard for the location CRUD endpoints. */
			private function rbfw_location_crud_guard() {
				check_ajax_referer( 'rbfw_location_crud', 'nonce' );
				if ( ! current_user_can( 'manage_categories' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
				}
			}

			public function ajax_location_add() {
				$this->rbfw_location_crud_guard();
				$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				if ( '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Please enter a location name.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$res = wp_insert_term( $name, 'rbfw_item_location' );
				if ( is_wp_error( $res ) ) {
					wp_send_json_error( array( 'message' => $res->get_error_message() ) );
				}
				wp_send_json_success( array( 'locations' => $this->rbfw_get_location_list() ) );
			}

			public function ajax_location_update() {
				$this->rbfw_location_crud_guard();
				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				if ( ! $term_id || '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid location.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$res = wp_update_term( $term_id, 'rbfw_item_location', array( 'name' => $name ) );
				if ( is_wp_error( $res ) ) {
					wp_send_json_error( array( 'message' => $res->get_error_message() ) );
				}
				wp_send_json_success( array( 'locations' => $this->rbfw_get_location_list() ) );
			}

			public function ajax_location_delete() {
				$this->rbfw_location_crud_guard();
				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				if ( ! $term_id ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid location.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$res = wp_delete_term( $term_id, 'rbfw_item_location' );
				if ( is_wp_error( $res ) ) {
					wp_send_json_error( array( 'message' => $res->get_error_message() ) );
				}
				wp_send_json_success( array( 'locations' => $this->rbfw_get_location_list() ) );
			}

			public function add_tab_menu($rbfw_id) {
				// Location is available for every rent type ( multi-location feature ).
				?>
                <li data-target-tabs="#rbfw_location_config">
                    <i class="fas fa-location-dot"></i><?php esc_html_e( 'Location', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Location Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure locatoin', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php echo esc_html( $title ); ?>
                        </label>
                        <p><?php echo esc_html( $description ); ?></p>
                    </div>
                </section>
				<?php
			}

			public function rbfw_get_location_arr() {
				$terms = get_terms( array(
					'taxonomy'   => 'rbfw_item_location',
					'hide_empty' => false,
				) );
				$arr   = array();
				foreach ( $terms as $_terms ) {
					$arr[ $_terms->name ] = $_terms->name;
				}

				return $arr;
			}

			public function searchForId( $id, $array ) {
				foreach ( $array as $key => $val ) {
					if ( $val['loc_pickup_name'] == $id ) {
						echo true;
					} else {
						echo false;
					}
				}
			}

			public function rbfw_get_location_dropdown( $name, $saved_value = '', $class = '' ) {
				$location_arr = $this->rbfw_get_location_arr();
				echo '<select name="' . esc_attr( $name ) . '" class="' . esc_attr( $class ) . '">';
				foreach ( $location_arr as $key => $value ) {
					$selected_text = ! empty( $saved_value ) && $saved_value == $key ? ' selected' : '';
					echo '<option value="' . esc_attr( $key ) . '"' . $selected_text . '>' . esc_html( $value ) . '</option>';
				}
				echo "</select>";
			}

			public function pickup_location_config( $post_id ) {
				$rbfw_enable_pick_point = get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) ? get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) : 'no';
				$rbfw_pickup_data       = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
				$saved_pickup_slugs     = wp_list_pluck( $rbfw_pickup_data, 'loc_pickup_name' );
				?>
                <section>
                    <div>
                        <label><?php esc_html_e( 'Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                        <p><?php esc_html_e( 'Turn Pick-up Location On/Off', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="rbfw_enable_pick_point" value="<?php echo esc_attr( $rbfw_enable_pick_point ); ?>" <?php echo esc_attr( ( $rbfw_enable_pick_point == 'yes' ) ? 'checked' : '' ); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
				<?php $location_arr = $this->rbfw_get_location_arr(); ?>
                <section class="rbfw-pickup-location <?php echo esc_attr( ( $rbfw_enable_pick_point == 'yes' ) ? 'show' : 'hide' ); ?>">
                    <div class="rbfw-pickup-locations">
                        <div id="field-wrapper-rdfw_available_time" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rdfw_available_time">
                            <select name="loc_pickup_name[]" id="rdfw_pickup_location" multiple tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
								<?php
                                foreach ( $location_arr as $key => $value ) {
                                    $slug     = sanitize_title( $value );
                                    $selected = in_array( $slug, $saved_pickup_slugs, true ) ? 'selected' : '';
                                    echo '<option ' . $selected . ' value="' . esc_attr( $slug ) . '">';
                                    echo esc_html( $key );
                                    echo '</option>';
                                }
								?>
                            </select>
                        </div>
                    </div>
                </section>
				<?php
			}

			public function drop_off_location_config( $post_id ) {
				$rbfw_enable_dropoff_point = get_post_meta( $post_id, 'rbfw_enable_dropoff_point', true ) ? get_post_meta( $post_id, 'rbfw_enable_dropoff_point', true ) : 'no';
				$rbfw_dropoff_data         = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
				$location_arr              = $this->rbfw_get_location_arr();
				$saved_dropoff_slugs       = wp_list_pluck( $rbfw_dropoff_data, 'loc_dropoff_name' );
				?>
                <section>
                    <div>
                        <label><?php esc_html_e( 'Drop-Off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                        <p><?php esc_html_e( 'Turn drop off Location On/Off', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="rbfw_enable_dropoff_point" value="<?php echo esc_attr( $rbfw_enable_dropoff_point ); ?>" <?php echo esc_attr( ( $rbfw_enable_dropoff_point == 'yes' ) ? 'checked' : '' ); ?>>
                        <span class="slider round"></span>
                    </label>
                </section>
                <section class="rbfw-drop-off-location <?php echo esc_attr( ( $rbfw_enable_dropoff_point == 'yes' ) ? 'show' : 'hide' ); ?>">
                    <div class="rbfw-drop-off-locations">
                        <div id="field-wrapper-rdfw_available_time" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rdfw_available_time">
                            <select name="loc_dropoff_name[]" id="rdfw_dropoff_location" multiple tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                                <?php
                                foreach ( $location_arr as $key => $value ) {
                                    $slug     = sanitize_title( $value );
                                    $selected = in_array( $slug, $saved_dropoff_slugs, true ) ? 'selected' : '';
                                    echo '<option ' . $selected . ' value="' . esc_attr( $slug ) . '">';
                                    echo esc_html( $key );
                                    echo '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </section>
				<?php
			}

			/**
			 * Render the Location Configuration for the modern editor.
			 *
			 * Mirrors the RBFW_Off_Day modern pattern: the modern editor had no
			 * location UI, and its AJAX save only stored the pick-up toggle (never
			 * the drop-off toggle or the pick-up/drop-off location data). Rather
			 * than reuse the classic markup (which hard-codes select2 + an inline
			 * toggle script), this renders clean modern markup — the existing
			 * reveal-toggle component plus a checkbox group backed by a hidden
			 * comma-separated value (the same approach as Off Days / Categories,
			 * which collectFormData() serialises reliably).
			 *
			 * @param int $post_id Current rental item ID.
			 * @return void
			 */
			public static function render_for_modern_editor( int $post_id ): void {
				$renderer      = ( new \ReflectionClass( static::class ) )->newInstanceWithoutConstructor();
				$locations     = $renderer->rbfw_get_location_list();
				$pickup_data   = get_post_meta( $post_id, 'rbfw_pickup_data', true );
				$dropoff_data  = get_post_meta( $post_id, 'rbfw_dropoff_data', true );
				$pickup_slugs  = is_array( $pickup_data ) ? array_filter( (array) wp_list_pluck( $pickup_data, 'loc_pickup_name' ) ) : [];
				$dropoff_slugs = is_array( $dropoff_data ) ? array_filter( (array) wp_list_pluck( $dropoff_data, 'loc_dropoff_name' ) ) : [];
				?>
				<div class="rbfw-me-loc-manage" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_location_crud' ) ); ?>">
					<div class="rbfw-me-loc-manage__head">
						<strong><?php esc_html_e( 'Manage Locations', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
						<span class="rbfw-me-field__desc"><?php esc_html_e( 'Add, rename or remove the locations available for pick-up and drop-off.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<div class="rbfw-me-loc-manage__add">
						<input type="text" class="rbfw-me-input rbfw-me-loc-new" placeholder="<?php esc_attr_e( 'New location name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-loc-add-btn"><i class="fas fa-circle-plus" aria-hidden="true"></i> <?php esc_html_e( 'Add', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
					</div>
					<ul class="rbfw-me-loc-list">
						<?php foreach ( $locations as $loc ) : ?>
							<li class="rbfw-me-loc-row" data-term-id="<?php echo esc_attr( $loc['term_id'] ); ?>" data-value="<?php echo esc_attr( $loc['value'] ); ?>">
								<span class="rbfw-me-loc-row__name"><?php echo esc_html( $loc['name'] ); ?></span>
								<span class="rbfw-me-loc-row__actions">
									<button type="button" class="rbfw-me-loc-edit" title="<?php esc_attr_e( 'Rename', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-pen" aria-hidden="true"></i></button>
									<button type="button" class="rbfw-me-loc-delete" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-trash-can" aria-hidden="true"></i></button>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>

					<!-- Rename modal (reuses the modern editor's modal styling) -->
					<div class="rbfw-me-faq-modal" id="rbfw-me-loc-modal">
						<div class="rbfw-me-faq-modal__backdrop"></div>
						<div class="rbfw-me-faq-modal__box">
							<div class="rbfw-me-faq-modal__head">
								<h3><?php esc_html_e( 'Rename Location', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<button type="button" class="rbfw-me-faq-modal__close"><span class="dashicons dashicons-no-alt"></span></button>
							</div>
							<div class="rbfw-me-faq-modal__body">
								<div class="rbfw-me-field">
									<label class="rbfw-me-field__label"><?php esc_html_e( 'Location name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<input class="rbfw-me-input" type="text" id="rbfw-me-loc-modal-input" />
								</div>
								<input type="hidden" id="rbfw-me-loc-modal-term-id" value="">
							</div>
							<div class="rbfw-me-faq-modal__foot">
								<button type="button" id="rbfw-me-loc-modal-save" class="rbfw-me-btn rbfw-me-btn--primary"><?php esc_html_e( 'Update', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php

				self::render_modern_location_group(
					__( 'Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ),
					__( 'Turn Pick-up Location On/Off', 'booking-and-rental-manager-for-woocommerce' ),
					'rbfw_enable_pick_point',
					get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) === 'yes',
					'rbfw-me-pickup-locations',
					'rbfw_pickup_locations',
					$locations,
					$pickup_slugs
				);

				self::render_modern_location_group(
					__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ),
					__( 'Turn Drop-off Location On/Off', 'booking-and-rental-manager-for-woocommerce' ),
					'rbfw_enable_dropoff_point',
					get_post_meta( $post_id, 'rbfw_enable_dropoff_point', true ) === 'yes',
					'rbfw-me-dropoff-locations',
					'rbfw_dropoff_locations',
					$locations,
					$dropoff_slugs
				);
			}

			/**
			 * Render one location group (toggle + reveal + checkbox list) for the
			 * modern editor. Checkbox state is mirrored into a hidden, comma
			 * separated value (of sanitize_title() values) that the modern AJAX
			 * save reads.
			 */
			private static function render_modern_location_group( $title, $desc, $enable_name, $enabled, $reveal_class, $hidden_name, $locations, $selected_slugs ) {
				$csv = implode( ',', array_map( 'sanitize_title', (array) $selected_slugs ) );
				?>
				<div class="rbfw-me-field rbfw-me-field--toggle-row">
					<div class="rbfw-me-field__info">
						<strong><?php echo esc_html( $title ); ?></strong>
						<span class="rbfw-me-field__desc"><?php echo esc_html( $desc ); ?></span>
					</div>
					<label class="rbfw-me-toggle">
						<input type="checkbox" name="<?php echo esc_attr( $enable_name ); ?>" value="yes" <?php checked( $enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".<?php echo esc_attr( $reveal_class ); ?>" />
						<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
					</label>
				</div>
				<div class="<?php echo esc_attr( $reveal_class ); ?> rbfw-me-loc-group<?php echo $enabled ? '' : ' rbfw-me-hidden'; ?>">
					<input type="hidden" name="<?php echo esc_attr( $hidden_name ); ?>" class="rbfw-me-loc-hidden" value="<?php echo esc_attr( $csv ); ?>">
					<div class="rbfw-me-loc-checkboxes">
						<?php foreach ( $locations as $loc ) : ?>
							<label class="rbfw-me-loc-label">
								<input type="checkbox" class="rbfw-me-loc-checkbox" data-loc="<?php echo esc_attr( $loc['value'] ); ?>" <?php checked( in_array( $loc['value'], (array) $selected_slugs, true ) ); ?> />
								<span><?php echo esc_html( $loc['name'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<p class="rbfw-me-loc-empty<?php echo empty( $locations ) ? '' : ' rbfw-me-hidden'; ?>"><?php esc_html_e( 'No locations yet — add one above.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_location_config">
					<?php $this->section_header(); ?>
                    <div class="rbfw-location-manage" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_location_crud' ) ); ?>">
						<?php $this->panel_header( 'Manage Locations', 'Add, rename or remove the locations available for pick-up and drop-off.' ); ?>
                        <section>
                            <div>
                                <label><?php esc_html_e( 'Add Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                                <p><?php esc_html_e( 'Create a new location.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                            <div class="rbfw-location-add">
                                <input type="text" class="rbfw-location-new" placeholder="<?php esc_attr_e( 'New location name', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                <button type="button" class="button button-primary rbfw-location-add-btn"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            </div>
                        </section>
                        <section>
                            <ul class="rbfw-location-list">
								<?php foreach ( $this->rbfw_get_location_list() as $loc ) : ?>
                                    <li class="rbfw-location-row" data-term-id="<?php echo esc_attr( $loc['term_id'] ); ?>" data-value="<?php echo esc_attr( $loc['value'] ); ?>">
                                        <span class="rbfw-location-row-name"><?php echo esc_html( $loc['name'] ); ?></span>
                                        <span class="rbfw-location-row-actions">
                                            <button type="button" class="button rbfw-location-edit" title="<?php esc_attr_e( 'Rename', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-pen"></i></button>
                                            <button type="button" class="button rbfw-location-delete" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-trash-can"></i></button>
                                        </span>
                                    </li>
								<?php endforeach; ?>
                            </ul>
                        </section>
                        <div class="rbfw-location-modal" id="rbfw-location-modal">
                            <div class="rbfw-location-modal__backdrop"></div>
                            <div class="rbfw-location-modal__box">
                                <div class="rbfw-location-modal__head">
                                    <h3><?php esc_html_e( 'Rename Location', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                    <button type="button" class="rbfw-location-modal__close" aria-label="Close">&times;</button>
                                </div>
                                <div class="rbfw-location-modal__body">
                                    <label for="rbfw-location-modal-input"><strong><?php esc_html_e( 'Location name', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
                                    <input type="text" id="rbfw-location-modal-input" class="widefat" style="margin-top:6px;">
                                    <input type="hidden" id="rbfw-location-modal-term-id" value="">
                                </div>
                                <div class="rbfw-location-modal__foot">
                                    <button type="button" class="button button-primary" id="rbfw-location-modal-save"><?php esc_html_e( 'Update', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                    <button type="button" class="button rbfw-location-modal__close"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php do_action( 'rbfw_location_config_before', $post_id ); ?>
					<?php $this->panel_header( 'Pick-up Location Configuration', 'Here you can set location.' ); ?>
					<?php $this->pickup_location_config( $post_id ); ?>
					<?php $this->panel_header( 'Drop-off Location Configuration', 'Here you can set drop off location.' ); ?>
					<?php $this->drop_off_location_config( $post_id ); ?>
					<?php do_action( 'rbfw_location_config_after', $post_id ); ?>
                </div>
                <script>
                    jQuery('input[name=rbfw_enable_pick_point]').click(function () {
                        var status = jQuery(this).val();
                        if (status == 'yes') {
                            jQuery(this).val('no');
                            jQuery('.rbfw-pickup-location').slideUp().removeClass('show').addClass('hide');
                        }
                        if (status == 'no') {
                            jQuery(this).val('yes');
                            jQuery('.rbfw-pickup-location').slideDown().removeClass('hide').addClass('show');
                        }
                    });
                    jQuery('input[name=rbfw_enable_dropoff_point]').click(function () {
                        var status = jQuery(this).val();
                        if (status == 'yes') {
                            jQuery(this).val('no');
                            jQuery('.rbfw-drop-off-location').slideUp().removeClass('show').addClass('hide');
                        }
                        if (status == 'no') {
                            jQuery(this).val('yes');
                            jQuery('.rbfw-drop-off-location').slideDown().removeClass('hide').addClass('show');
                        }
                    });
                    function createPickupLocation() {
                        jQuery(".rbfw-pickup-clone").clone().appendTo(".rbfw-pickup-locations")
                            .removeClass('rbfw-pickup-clone').addClass('rbfw-pickup');
                    };
                    function createDropOffLocation() {
                        jQuery(".rbfw-drop-off-clone").clone().appendTo(".rbfw-drop-off-locations")
                            .removeClass('rbfw-drop-off-clone').addClass('rbfw-drop-off');
                    };

                    /* ── Inline location CRUD (classic editor) ── */
                    (function () {
                        function locEsc(s) { return jQuery('<div>').text(s == null ? '' : String(s)).html(); }
                        function locNonce() { return jQuery('.rbfw-location-manage').data('nonce'); }

                        function locRebuild(locations) {
                            locations = locations || [];
                            var $list = jQuery('.rbfw-location-list').empty();
                            locations.forEach(function (loc) {
                                $list.append(
                                    '<li class="rbfw-location-row" data-term-id="' + loc.term_id + '" data-value="' + locEsc(loc.value) + '">' +
                                        '<span class="rbfw-location-row-name">' + locEsc(loc.name) + '</span>' +
                                        '<span class="rbfw-location-row-actions">' +
                                            '<button type="button" class="button rbfw-location-edit"><i class="fas fa-pen"></i></button> ' +
                                            '<button type="button" class="button rbfw-location-delete"><i class="fas fa-trash-can"></i></button>' +
                                        '</span>' +
                                    '</li>'
                                );
                            });
                            // Rebuild the pick-up / drop-off selects, preserving the current selection.
                            jQuery('#rdfw_pickup_location, #rdfw_dropoff_location').each(function () {
                                var $sel = jQuery(this);
                                var current = $sel.val() || [];
                                $sel.empty();
                                locations.forEach(function (loc) {
                                    var sel = (current.indexOf(loc.value) !== -1) ? ' selected' : '';
                                    $sel.append('<option value="' + locEsc(loc.value) + '"' + sel + '>' + locEsc(loc.name) + '</option>');
                                });
                                $sel.trigger('change');
                            });
                        }

                        function locAjax(action, data, cb) {
                            data.action = action;
                            data.nonce = locNonce();
                            jQuery.post(ajaxurl, data, function (resp) {
                                if (resp && resp.success) { locRebuild(resp.data.locations); if (cb) cb(); }
                                else { window.alert((resp && resp.data && resp.data.message) || 'Action failed.'); }
                            }).fail(function () { window.alert('Request failed.'); });
                        }

                        jQuery(document).on('click', '.rbfw-location-add-btn', function () {
                            var $input = jQuery('.rbfw-location-new');
                            var name = jQuery.trim($input.val());
                            if (!name) { $input.focus(); return; }
                            locAjax('rbfw_location_add', { name: name }, function () { $input.val(''); });
                        });
                        jQuery(document).on('keypress', '.rbfw-location-new', function (e) {
                            if (e.which === 13) { e.preventDefault(); jQuery('.rbfw-location-add-btn').click(); }
                        });
                        // Edit → open the rename modal (no browser prompt).
                        jQuery(document).on('click', '.rbfw-location-edit', function () {
                            var $row = jQuery(this).closest('.rbfw-location-row');
                            jQuery('#rbfw-location-modal-term-id').val($row.data('term-id'));
                            jQuery('#rbfw-location-modal-input').val(jQuery.trim($row.find('.rbfw-location-row-name').text()));
                            jQuery('#rbfw-location-modal').addClass('is-open');
                            setTimeout(function () { jQuery('#rbfw-location-modal-input').focus().select(); }, 50);
                        });
                        jQuery(document).on('click', '.rbfw-location-modal__close, .rbfw-location-modal__backdrop', function () {
                            jQuery('#rbfw-location-modal').removeClass('is-open');
                        });
                        jQuery(document).on('click', '#rbfw-location-modal-save', function () {
                            var term_id = jQuery('#rbfw-location-modal-term-id').val();
                            var name = jQuery.trim(jQuery('#rbfw-location-modal-input').val());
                            if (!name) { jQuery('#rbfw-location-modal-input').focus(); return; }
                            locAjax('rbfw_location_update', { term_id: term_id, name: name }, function () {
                                jQuery('#rbfw-location-modal').removeClass('is-open');
                            });
                        });
                        jQuery(document).on('keypress', '#rbfw-location-modal-input', function (e) {
                            if (e.which === 13) { e.preventDefault(); jQuery('#rbfw-location-modal-save').click(); }
                        });
                        jQuery(document).on('click', '.rbfw-location-delete', function () {
                            var $row = jQuery(this).closest('.rbfw-location-row');
                            if (!window.confirm('Delete this location? Items using it will no longer reference it.')) { return; }
                            locAjax('rbfw_location_delete', { term_id: $row.data('term-id') });
                        });
                    })();
                </script>
                <style>
                    .rbfw-location-add { display: flex; gap: 8px; align-items: center; }
                    .rbfw-location-new { min-width: 240px; }
                    .rbfw-location-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 6px; width: 100%; }
                    .rbfw-location-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; border: 1px solid #e2e6ee; border-radius: 6px; background: #fff; }
                    .rbfw-location-row-name { font-weight: 500; }
                    .rbfw-location-row-actions { display: inline-flex; gap: 6px; }
                    .rbfw-location-modal { position: fixed; inset: 0; z-index: 100000; display: none; }
                    .rbfw-location-modal.is-open { display: block; }
                    .rbfw-location-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.5); }
                    .rbfw-location-modal__box { position: relative; max-width: 440px; margin: 12vh auto 0; background: #fff; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,.25); overflow: hidden; }
                    .rbfw-location-modal__head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #e2e6ee; }
                    .rbfw-location-modal__head h3 { margin: 0; font-size: 15px; }
                    .rbfw-location-modal__close { background: none; border: none; font-size: 22px; line-height: 1; cursor: pointer; color: #6b7280; }
                    .rbfw-location-modal__body { padding: 18px; }
                    .rbfw-location-modal__foot { padding: 14px 18px; border-top: 1px solid #e2e6ee; display: flex; gap: 8px; }
                </style>
				<?php
			}

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$rbfw_enable_pick_point = isset( $_POST['rbfw_enable_pick_point'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_pick_point'] ) ) : 'no';
					$rbfw_enable_dropoff_point = isset( $_POST['rbfw_enable_dropoff_point'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_enable_dropoff_point'] ) ) : 'no';
					update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );
					update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $rbfw_enable_dropoff_point );
					// Saving Pickup Location Data
					$old_rbfw_pickup_data = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
					$new_rbfw_pickup_data = array();
					$names                = isset( $_POST['loc_pickup_name'] ) ? RBFW_Function::data_sanitize( $_POST['loc_pickup_name'] ) : [];
					$count = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] != '' ) :
							$new_rbfw_pickup_data[ $i ]['loc_pickup_name'] = stripslashes( wp_strip_all_tags( $names[ $i ] ) );
						endif;
					}
					$pickup_data_arr = apply_filters( 'rbfw_pickup_arr_save', $new_rbfw_pickup_data );
					if ( ! empty( $pickup_data_arr ) && $pickup_data_arr != $old_rbfw_pickup_data ) {
						update_post_meta( $post_id, 'rbfw_pickup_data', $pickup_data_arr );
					} elseif ( empty( $pickup_data_arr ) && $old_rbfw_pickup_data ) {
						delete_post_meta( $post_id, 'rbfw_pickup_data', $old_rbfw_pickup_data );
					}
					// Saving Dropoff Data
					$old_rbfw_dropoff_data = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
					$new_rbfw_dropoff_data = array();
					$names = isset( $_POST['loc_dropoff_name'] ) ? RBFW_Function::data_sanitize( $_POST['loc_dropoff_name'] ) : [];
					$count = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] != '' ) :
							$new_rbfw_dropoff_data[ $i ]['loc_dropoff_name'] = stripslashes( wp_strip_all_tags( $names[ $i ] ) );
						endif;
					}
					$dropoff_data_arr = apply_filters( 'rbfw_dropoff_arr_save', $new_rbfw_dropoff_data );
					if ( ! empty( $dropoff_data_arr ) && $dropoff_data_arr != $old_rbfw_dropoff_data ) {
						update_post_meta( $post_id, 'rbfw_dropoff_data', $dropoff_data_arr );
					} elseif ( empty( $dropoff_data_arr ) && $old_rbfw_dropoff_data ) {
						delete_post_meta( $post_id, 'rbfw_dropoff_data', $old_rbfw_dropoff_data );
					}
				}
			}
		}
		new RBFW_Location();
	}