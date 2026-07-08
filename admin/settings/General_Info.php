<?php
	/*
   * @Author 		raselsha@gmail.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_General_Info' ) ) {
		class RBFW_General_Info {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', array( $this, 'settings_save' ), 99, 1 );
				add_action( 'wp_ajax_rbfw_rent_type_add', [ $this, 'ajax_rent_type_add' ] );
				add_action( 'wp_ajax_rbfw_rent_type_rename', [ $this, 'ajax_rent_type_rename' ] );
				add_action( 'wp_ajax_rbfw_rent_type_delete', [ $this, 'ajax_rent_type_delete' ] );
			}

			/**
			 * @return array[] Hierarchically ordered list of [ term_id, name, parent, depth ].
			 *                 Children immediately follow their parent; depth is the nesting level.
			 */
			public function rbfw_get_rent_type_list() {
				$terms = get_terms( array(
					'taxonomy'   => 'rbfw_item_caregory',
					'hide_empty' => false,
				) );
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					return array();
				}
				return $this->rbfw_order_terms_hierarchically( $terms );
			}

			/**
			 * Flatten a set of terms into a parent-first ordered list carrying a depth level,
			 * so the UI can render sub-categories indented beneath their parent.
			 *
			 * @param WP_Term[] $terms
			 * @param int       $parent Parent term_id to collect children for.
			 * @param int       $depth  Current nesting depth.
			 * @return array[]
			 */
			private function rbfw_order_terms_hierarchically( $terms, $parent = 0, $depth = 0 ) {
				$out = array();
				foreach ( $terms as $term ) {
					if ( (int) $term->parent !== (int) $parent ) {
						continue;
					}
					$out[] = array(
						'term_id' => (int) $term->term_id,
						'name'    => $term->name,
						'parent'  => (int) $term->parent,
						'depth'   => (int) $depth,
					);
					$out = array_merge(
						$out,
						$this->rbfw_order_terms_hierarchically( $terms, $term->term_id, $depth + 1 )
					);
				}
				return $out;
			}

			private function rbfw_rent_type_crud_guard() {
				check_ajax_referer( 'rbfw_rent_type_crud', 'nonce' );
				if ( ! current_user_can( 'manage_categories' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
				}
			}

			public function ajax_rent_type_add() {
				$this->rbfw_rent_type_crud_guard();
				$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				$name = trim( $name );
				if ( '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Please enter a rent type name.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				if ( mb_strlen( $name ) > 200 ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Rent type name must be 200 characters or fewer.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				// Optional parent term — lets an admin create a sub-category.
				$parent = isset( $_POST['parent'] ) ? absint( wp_unslash( $_POST['parent'] ) ) : 0;
				if ( $parent > 0 ) {
					$parent_term = get_term( $parent, 'rbfw_item_caregory' );
					if ( ! $parent_term || is_wp_error( $parent_term ) ) {
						wp_send_json_error( array( 'message' => esc_html__( 'The selected parent category no longer exists.', 'booking-and-rental-manager-for-woocommerce' ) ) );
					}
				}
				$res = wp_insert_term( $name, 'rbfw_item_caregory', array( 'parent' => $parent ) );
				if ( is_wp_error( $res ) ) {
					wp_send_json_error( array( 'message' => sanitize_text_field( $res->get_error_message() ) ) );
				}
				wp_send_json_success( array(
					'rent_types' => $this->rbfw_get_rent_type_list(),
					'added_name' => $name,
				) );
			}

			/**
			 * AJAX: rename a rent type and migrate the name-based selection meta on every
			 * item that used it ( rbfw_categories stores names, not ids ).
			 */
			public function ajax_rent_type_rename() {
				$this->rbfw_rent_type_crud_guard();
				$term_id = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
				$name    = isset( $_POST['name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['name'] ) ) ) : '';
				if ( ! $term_id ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid rent type.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				if ( '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Please enter a rent type name.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				if ( mb_strlen( $name ) > 200 ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Rent type name must be 200 characters or fewer.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$term = get_term( $term_id, 'rbfw_item_caregory' );
				if ( ! $term || is_wp_error( $term ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Rent type not found.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$old_name = $term->name;
				if ( $old_name !== $name ) {
					$res = wp_update_term( $term_id, 'rbfw_item_caregory', array( 'name' => $name ) );
					if ( is_wp_error( $res ) ) {
						wp_send_json_error( array( 'message' => sanitize_text_field( $res->get_error_message() ) ) );
					}
					$this->rbfw_sync_rent_type_meta( $this->rbfw_get_items_with_term( $term_id ), $old_name, $name );
				}
				wp_send_json_success( array(
					'rent_types' => $this->rbfw_get_rent_type_list(),
					'term_id'    => $term_id,
					'old_name'   => $old_name,
					'new_name'   => $name,
				) );
			}

			/**
			 * AJAX: delete a rent type and drop its name from every item's selection meta.
			 */
			public function ajax_rent_type_delete() {
				$this->rbfw_rent_type_crud_guard();
				$term_id = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
				if ( ! $term_id ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid rent type.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$term = get_term( $term_id, 'rbfw_item_caregory' );
				if ( ! $term || is_wp_error( $term ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Rent type not found.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$old_name = $term->name;
				// Capture affected items before the term relationship is removed.
				$affected = $this->rbfw_get_items_with_term( $term_id );
				$res      = wp_delete_term( $term_id, 'rbfw_item_caregory' );
				if ( is_wp_error( $res ) ) {
					wp_send_json_error( array( 'message' => sanitize_text_field( $res->get_error_message() ) ) );
				}
				if ( false === $res || 0 === $res ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Could not delete the rent type.', 'booking-and-rental-manager-for-woocommerce' ) ) );
				}
				$this->rbfw_sync_rent_type_meta( $affected, $old_name, null );
				wp_send_json_success( array(
					'rent_types'      => $this->rbfw_get_rent_type_list(),
					'deleted_term_id' => $term_id,
					'deleted_name'    => $old_name,
				) );
			}

			/**
			 * IDs of rbfw_item posts that currently have the given term assigned.
			 *
			 * @param int $term_id
			 * @return int[]
			 */
			private function rbfw_get_items_with_term( $term_id ) {
				$query = new WP_Query( array(
					'post_type'              => 'rbfw_item',
					'post_status'            => 'any',
					'fields'                 => 'ids',
					'posts_per_page'         => -1,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'tax_query'              => array(
						array(
							'taxonomy' => 'rbfw_item_caregory',
							'field'    => 'term_id',
							'terms'    => (int) $term_id,
						),
					),
				) );
				return array_map( 'intval', (array) $query->posts );
			}

			/**
			 * Update the name-based rbfw_categories post meta for affected items.
			 *
			 * @param int[]       $post_ids
			 * @param string      $old_name
			 * @param string|null $new_name Pass null to remove the name (delete).
			 */
			private function rbfw_sync_rent_type_meta( $post_ids, $old_name, $new_name ) {
				$old_key = strtolower( trim( $old_name ) );
				foreach ( (array) $post_ids as $pid ) {
					$cats = get_post_meta( $pid, 'rbfw_categories', true );
					$cats = is_array( $cats ) ? $cats : ( $cats ? maybe_unserialize( $cats ) : array() );
					if ( ! is_array( $cats ) ) {
						continue;
					}
					$changed = false;
					$out     = array();
					foreach ( $cats as $cat ) {
						if ( strtolower( trim( (string) $cat ) ) === $old_key ) {
							$changed = true;
							if ( null !== $new_name && '' !== $new_name ) {
								$out[] = $new_name; // rename
							}
							// delete -> drop it
						} else {
							$out[] = $cat;
						}
					}
					if ( $changed ) {
						$out = array_values( array_unique( $out ) );
						update_post_meta( $pid, 'rbfw_categories', $out );
					}
				}
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_gen_info"><i class="fas fa-tools"></i><?php esc_html_e( 'General Info', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'General Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure basic information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
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

			public function select_category( $post_id ) {
				$rbfw_categories = get_post_meta( $post_id, 'rbfw_categories', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_categories', true ) ) : [];
				// Hierarchically ordered rent types ( parent first, children indented ).
				$terms = $this->rbfw_get_rent_type_list();
                global $rbfw;
				$label = $rbfw->get_name();
                $rbfw_categories_items = implode(',', $rbfw_categories);
                $rbfw_categories_array = $rbfw_categories_items ? explode(',', $rbfw_categories_items) : [];
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php echo esc_html__( "Category Settings",'booking-and-rental-manager-for-woocommerce'); ?>
                        </label>
                        <p>
                            <?php echo esc_html__( "Here you can manage rent type.",'booking-and-rental-manager-for-woocommerce'); ?>
                            <?php if ( current_user_can( 'manage_categories' ) ) : ?>
                            <a href="#" class="rbfw-rent-type-add-trigger"><?php echo esc_html__( "Add new ",'booking-and-rental-manager-for-woocommerce'); ?></a><?php echo esc_html__( "rent type",'booking-and-rental-manager-for-woocommerce'); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </section>
                <?php $rbfw_rt_can_manage = current_user_can( 'manage_categories' ); ?>
                <section class="rbfw_off_days justify-content-center rbfw-rent-type-checkboxes" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_rent_type_crud' ) ); ?>" data-can-manage="<?php echo $rbfw_rt_can_manage ? '1' : '0'; ?>">
                    <div class="groupCheckBox">
                        <input type="hidden" name="rbfw_categories[]" value="<?php echo esc_attr( $rbfw_categories_items ); ?>">
                        <?php foreach ( $terms as $key => $value ) {
                            $rt_depth = isset( $value['depth'] ) ? (int) $value['depth'] : 0;
                            ?>
                            <label class="customCheckboxLabel rbfw-rt-chip<?php echo $rt_depth > 0 ? ' rbfw-rt-child' : ''; ?>" data-term-id="<?php echo esc_attr( $value['term_id'] ); ?>" data-name="<?php echo esc_attr( $value['name'] ); ?>" data-parent="<?php echo esc_attr( isset( $value['parent'] ) ? $value['parent'] : 0 ); ?>" data-depth="<?php echo esc_attr( $rt_depth ); ?>"<?php echo $rt_depth > 0 ? ' style="margin-left:' . esc_attr( $rt_depth * 22 ) . 'px;"' : ''; ?>>
                                <input type="checkbox" <?php echo esc_attr( in_array( $value['name'], $rbfw_categories_array ) ) ? 'checked' : ''; ?> data-checked="<?php echo esc_attr( $value['name'] ) ?>">
                                <span class="customCheckbox"><?php echo $rt_depth > 0 ? '<span class="rbfw-rt-sub-indicator">↳</span> ' : ''; ?><?php echo esc_html( ucfirst( $value['name'] ) ); ?></span>
                                <?php if ( $rbfw_rt_can_manage ) { ?>
                                    <span class="rbfw-rt-actions">
                                        <span class="rbfw-rt-edit dashicons dashicons-edit" title="<?php esc_attr_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?>"></span>
                                        <span class="rbfw-rt-del dashicons dashicons-trash" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"></span>
                                    </span>
                                <?php } ?>
                            </label>
                        <?php } ?>
                    </div>
                </section>
                <div class="rbfw-rent-type-modal" id="rbfw-rent-type-modal">
                    <div class="rbfw-rent-type-modal__backdrop"></div>
                    <div class="rbfw-rent-type-modal__box">
                        <div class="rbfw-rent-type-modal__head">
                            <h3><?php esc_html_e( 'Add New Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                            <button type="button" class="rbfw-rent-type-modal__close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
                        </div>
                        <div class="rbfw-rent-type-modal__body">
                            <label for="rbfw-rent-type-modal-input"><strong><?php esc_html_e( 'Rent type name', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></label>
                            <input type="text" id="rbfw-rent-type-modal-input" class="widefat" style="margin-top:6px;" maxlength="200" placeholder="<?php esc_attr_e( 'e.g. Bike, Car, Equipment…', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                            <div class="rbfw-rent-type-parent-wrap" style="margin-top:14px;">
                                <label for="rbfw-rent-type-modal-parent"><strong><?php esc_html_e( 'Parent category', 'booking-and-rental-manager-for-woocommerce' ); ?></strong> <span style="font-weight:400;color:#6b7280;">(<?php esc_html_e( 'optional', 'booking-and-rental-manager-for-woocommerce' ); ?>)</span></label>
                                <select id="rbfw-rent-type-modal-parent" class="widefat" style="margin-top:6px;">
                                    <option value="0"><?php esc_html_e( '— None (top level) —', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                </select>
                                <p class="description" style="margin-top:6px;"><?php esc_html_e( 'Pick a parent to create a sub-category.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                            </div>
                        </div>
                        <div class="rbfw-rent-type-modal__foot">
                            <button type="button" class="button button-primary" id="rbfw-rent-type-modal-save"><?php esc_html_e( 'Add Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                            <button type="button" class="button rbfw-rent-type-modal__close"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                        </div>
                    </div>
                </div>
                <script>
                    (function () {
                        var editTermId = 0; // 0 = add mode, >0 = rename mode

                        function rtEsc(s) { return jQuery('<div>').text(s == null ? '' : String(s)).html(); }
                        function rtNonce() { return jQuery('.rbfw-rent-type-checkboxes').data('nonce'); }
                        function rtCanManage() { return String(jQuery('.rbfw-rent-type-checkboxes').data('can-manage')) === '1'; }

                        function rtActionsHtml() {
                            if (!rtCanManage()) { return ''; }
                            return '<span class="rbfw-rt-actions">' +
                                '<span class="rbfw-rt-edit dashicons dashicons-edit" title="Edit"></span>' +
                                '<span class="rbfw-rt-del dashicons dashicons-trash" title="Delete"></span>' +
                            '</span>';
                        }

                        function rtHidden() { return jQuery('.rbfw-rent-type-checkboxes input[type="hidden"]'); }

                        function rtRebuild(rentTypes, selectName) {
                            rentTypes = rentTypes || [];
                            var $section = jQuery('.rbfw-rent-type-checkboxes');
                            var $group   = $section.find('.groupCheckBox');
                            var current  = ($group.find('input[type="hidden"]').val() || '').split(',').filter(Boolean);
                            if (selectName && current.indexOf(selectName) === -1) {
                                current.push(selectName);
                            }
                            $group.find('label.customCheckboxLabel').remove();
                            rentTypes.forEach(function (rt) {
                                var checked = current.indexOf(rt.name) !== -1 ? ' checked' : '';
                                var depth   = parseInt(rt.depth, 10) || 0;
                                var indent  = depth > 0 ? ' style="margin-left:' + (depth * 18) + 'px;"' : '';
                                var prefix  = depth > 0 ? '<span class="rbfw-rt-sub-indicator" aria-hidden="true">↳ </span>' : '';
                                $group.append(
                                    '<label class="customCheckboxLabel rbfw-rt-chip" data-term-id="' + rtEsc(rt.term_id) + '" data-name="' + rtEsc(rt.name) + '" data-parent="' + rtEsc(rt.parent || 0) + '" data-depth="' + depth + '"' + indent + '>' +
                                        '<input type="checkbox"' + checked + ' data-checked="' + rtEsc(rt.name) + '">' +
                                        '<span class="customCheckbox">' + prefix + rtEsc(rt.name.charAt(0).toUpperCase() + rt.name.slice(1)) + '</span>' +
                                        rtActionsHtml() +
                                    '</label>'
                                );
                            });
                            $group.find('input[type="hidden"]').val(current.join(','));
                        }

                        // Build the parent <select> options from the currently rendered chips.
                        // Excludes the term being edited (and its descendants) to prevent cycles.
                        function rtPopulateParents(excludeTermId) {
                            var $select = jQuery('#rbfw-rent-type-modal-parent');
                            if (!$select.length) { return; }
                            excludeTermId = parseInt(excludeTermId, 10) || 0;
                            var prev = String($select.val() || '0');

                            // Collect descendants of the excluded term so they can't become its parent.
                            var excluded = {};
                            if (excludeTermId) {
                                excluded[excludeTermId] = true;
                                var changed = true;
                                while (changed) {
                                    changed = false;
                                    jQuery('.rbfw-rent-type-checkboxes .rbfw-rt-chip').each(function () {
                                        var tid = parseInt(jQuery(this).data('term-id'), 10) || 0;
                                        var pid = parseInt(jQuery(this).data('parent'), 10) || 0;
                                        if (pid && excluded[pid] && !excluded[tid]) { excluded[tid] = true; changed = true; }
                                    });
                                }
                            }

                            $select.find('option:not(:first)').remove();
                            jQuery('.rbfw-rent-type-checkboxes .rbfw-rt-chip').each(function () {
                                var $chip = jQuery(this);
                                var tid   = parseInt($chip.data('term-id'), 10) || 0;
                                if (!tid || excluded[tid]) { return; }
                                var depth = parseInt($chip.data('depth'), 10) || 0;
                                var label = (depth > 0 ? new Array(depth + 1).join('— ') : '') + String($chip.data('name'));
                                $select.append('<option value="' + rtEsc(tid) + '">' + rtEsc(label) + '</option>');
                            });
                            if ($select.find('option[value="' + prev + '"]').length) { $select.val(prev); } else { $select.val('0'); }
                        }

                        function rtOpenModal(mode, termId, name, parentId) {
                            editTermId = mode === 'edit' ? (parseInt(termId, 10) || 0) : 0;
                            var isEdit = editTermId > 0;
                            jQuery('#rbfw-rent-type-modal .rbfw-rent-type-modal__head h3').text(isEdit ? 'Rename Rent Type' : 'Add New Rent Type');
                            jQuery('#rbfw-rent-type-modal-save').text(isEdit ? 'Save Changes' : 'Add Rent Type');
                            jQuery('#rbfw-rent-type-modal-input').val(name || '');
                            rtPopulateParents(editTermId);
                            jQuery('#rbfw-rent-type-modal-parent').val(String(parseInt(parentId, 10) || 0));
                            jQuery('#rbfw-rent-type-modal').addClass('is-open');
                            setTimeout(function () { jQuery('#rbfw-rent-type-modal-input').trigger('focus'); }, 50);
                        }

                        function rtCloseModal() {
                            jQuery('#rbfw-rent-type-modal').removeClass('is-open');
                            editTermId = 0;
                        }

                        jQuery(document).on('click', '.rbfw-rent-type-add-trigger', function (e) {
                            e.preventDefault();
                            rtOpenModal('add');
                        });

                        // Edit (rename) a rent type.
                        jQuery(document).on('click', '.rbfw-rent-type-checkboxes .rbfw-rt-edit', function (e) {
                            e.preventDefault(); e.stopPropagation();
                            var $chip = jQuery(this).closest('.rbfw-rt-chip');
                            rtOpenModal('edit', $chip.data('term-id'), $chip.data('name'), $chip.data('parent'));
                        });

                        // Delete a rent type.
                        jQuery(document).on('click', '.rbfw-rent-type-checkboxes .rbfw-rt-del', function (e) {
                            e.preventDefault(); e.stopPropagation();
                            var $chip  = jQuery(this).closest('.rbfw-rt-chip');
                            var termId = parseInt($chip.data('term-id'), 10) || 0;
                            var name   = $chip.data('name');
                            if (!termId) { return; }
                            if (!window.confirm('Delete rent type "' + name + '"? Items using it will have this type removed.')) { return; }
                            jQuery.post(ajaxurl, {
                                action: 'rbfw_rent_type_delete',
                                nonce: rtNonce(),
                                term_id: termId
                            }, function (resp) {
                                if (resp && resp.success) {
                                    var cur = (rtHidden().val() || '').split(',').filter(Boolean).filter(function (n) {
                                        return n.toLowerCase() !== String(resp.data.deleted_name).toLowerCase();
                                    });
                                    rtHidden().val(cur.join(','));
                                    rtRebuild(resp.data.rent_types);
                                } else {
                                    window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                                }
                            }).fail(function () { window.alert('Request failed.'); });
                        });

                        jQuery(document).on('click', '.rbfw-rent-type-modal__close, .rbfw-rent-type-modal__backdrop', function () {
                            rtCloseModal();
                        });
                        jQuery(document).on('click', '#rbfw-rent-type-modal-save', function () {
                            var name = jQuery.trim(jQuery('#rbfw-rent-type-modal-input').val());
                            if (!name) { jQuery('#rbfw-rent-type-modal-input').trigger('focus'); return; }
                            if (name.length > 200) { name = name.substring(0, 200); }

                            if (editTermId > 0) {
                                jQuery.post(ajaxurl, {
                                    action: 'rbfw_rent_type_rename',
                                    nonce: rtNonce(),
                                    term_id: editTermId,
                                    name: name
                                }, function (resp) {
                                    if (resp && resp.success) {
                                        var cur = (rtHidden().val() || '').split(',').filter(Boolean).map(function (n) {
                                            return n.toLowerCase() === String(resp.data.old_name).toLowerCase() ? resp.data.new_name : n;
                                        });
                                        rtHidden().val(cur.join(','));
                                        rtRebuild(resp.data.rent_types);
                                        rtCloseModal();
                                    } else {
                                        window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                                    }
                                }).fail(function () { window.alert('Request failed.'); });
                            } else {
                                jQuery.post(ajaxurl, {
                                    action: 'rbfw_rent_type_add',
                                    nonce: rtNonce(),
                                    name: name,
                                    parent: parseInt(jQuery('#rbfw-rent-type-modal-parent').val(), 10) || 0
                                }, function (resp) {
                                    if (resp && resp.success) {
                                        rtRebuild(resp.data.rent_types, resp.data.added_name);
                                        rtCloseModal();
                                    } else {
                                        window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                                    }
                                }).fail(function () { window.alert('Request failed.'); });
                            }
                        });
                        jQuery(document).on('keypress', '#rbfw-rent-type-modal-input', function (e) {
                            if (e.which === 13) { e.preventDefault(); jQuery('#rbfw-rent-type-modal-save').trigger('click'); }
                        });
                    })();
                </script>
                <style>
                    .rbfw-rent-type-modal { position: fixed; inset: 0; z-index: 100000; display: none; }
                    .rbfw-rent-type-modal.is-open { display: block; }
                    .rbfw-rent-type-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.5); }
                    .rbfw-rent-type-modal__box { position: relative; max-width: 440px; margin: 12vh auto 0; background: #fff; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,.25); overflow: hidden; }
                    .rbfw-rent-type-modal__head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #e2e6ee; }
                    .rbfw-rent-type-modal__head h3 { margin: 0; font-size: 15px; }
                    .rbfw-rent-type-modal__close { background: none; border: none; font-size: 22px; line-height: 1; cursor: pointer; color: #6b7280; }
                    .rbfw-rent-type-modal__body { padding: 18px; }
                    .rbfw-rent-type-modal__foot { padding: 14px 18px; border-top: 1px solid #e2e6ee; display: flex; gap: 8px; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-chip { position: relative; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-actions { position: absolute; top: -9px; right: -7px; display: none; align-items: center; gap: 1px; background: #fff; border: 1px solid #e2e6ee; border-radius: 4px; padding: 1px 3px; box-shadow: 0 2px 6px rgba(0,0,0,.14); z-index: 3; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-chip:hover .rbfw-rt-actions { display: inline-flex; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-actions .dashicons { font-size: 15px; width: 15px; height: 15px; line-height: 15px; cursor: pointer; color: #6b7280; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-actions .rbfw-rt-edit:hover { color: #2271b1; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-actions .rbfw-rt-del:hover { color: #d63638; }
                    /* Sub-category (child) chips: softer dashed pill + connector elbow */
                    .rbfw-rent-type-checkboxes .rbfw-rt-child { position: relative; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-child .customCheckbox { background: #f8fafc; border-style: dashed; border-color: #cbd5e1; color: #475569; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-child:hover .customCheckbox { background: #eef2f9; border-color: #94a3b8; color: #1e293b; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-child input:checked + .customCheckbox { border-style: solid; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-child::before { content: ""; position: absolute; left: -12px; top: 50%; width: 10px; height: 1px; background: #cbd5e1; pointer-events: none; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-sub-indicator { color: #94a3b8; font-weight: 700; margin-right: 3px; }
                    .rbfw-rent-type-checkboxes .rbfw-rt-child input:checked + .customCheckbox .rbfw-rt-sub-indicator { color: inherit; }
                </style>
				<?php
			}

			public function field_feature_category( $option ) {
				$id = isset( $option['id'] ) ? $option['id'] : "";
				if ( empty( $id ) ) {
					return;
				}
				$field_name  = isset( $option['field_name'] ) ? $option['field_name'] : $id;
				$conditions  = isset( $option['conditions'] ) ? $option['conditions'] : array();
				$placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
				$remove_text = isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-trash-can"></i>';
				$sortable    = isset( $option['sortable'] ) ? $option['sortable'] : true;
				$default     = isset( $option['default'] ) ? $option['default'] : array();
				$values = isset( $option['value'] ) ? $option['value'] : array();
				$values = ! empty( $values ) ? $values : $default;
				$limit = ! empty( $option['limit'] ) ? $option['limit'] : '';
				$field_id   = $id;
				$field_name = ! empty( $field_name ) ? $field_name : $id;
				ob_start();
				?>
                <div id="field-wrapper-<?php echo esc_attr( $id ); ?>" class="field-wrapper field-text-multi-wrapper field-text-multi-wrapper-<?php echo esc_attr( $field_id ); ?>">
                    <section>
                        <div class="w-100">
                            <table class="form-table rbfw_feature_category_table">
                                <tbody class="sortable_tr">
								<?php
									if ( ! empty( $values ) ):
										$i = 0;
										foreach ( $values as $value ):?>
                                            <tr data-cat="<?php echo esc_attr( $i ); ?>">
                                                <td>
                                                    <div class="features_category_wrapper text-center">
                                                        <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                                            <div class="feature_category_inner_wrap">
                                                                <div class="feature_category_title">
                                                                    <label class=" mb-1">
																		<?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                                    </label>
                                                                    <input type="text" value="<?php echo esc_attr( $value['cat_title'] ); ?>" name="rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_title]" data-key="<?php echo esc_attr( $i ); ?>" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/>
                                                                </div>
                                                                <div class="feature_category_inner_item_wrap sortable">
																	<?php
																		if ( ! empty( $value['cat_features'] ) ) {
																			$c = 0;
																			foreach ( $value['cat_features'] as $feature ) {
																				$icon  = $feature['icon'] ?? '';
																				$title = $feature['title'] ?? '';
																				?>
                                                                                <div class="item">
                                                                                    <a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="<?php echo esc_attr( $c ); ?>"><i class="fas fa-circle-plus"></i> <?php _e('Icon','booking-and-rental-manager-for-woocommerce'); ?></a>
                                                                                    <div class="rbfw_feature_icon_preview" data-key="<?php echo esc_attr( $c ); ?>"><?php if ( $icon ) {
																							echo '<i class="' . wp_kses_post( $icon ) . '"></i>';
																						} ?></div>
                                                                                    <input type='hidden' name='rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' value='<?php echo esc_attr( $icon ); ?>' data-key="<?php echo esc_attr( $c ); ?>" class="rbfw_feature_icon"/>
                                                                                    <input type='text' name='rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value="<?php echo esc_attr( $title ); ?>" data-key="<?php echo esc_attr( $c ); ?>"/>
                                                                                    <div>
																						<?php if ( $sortable ): ?>
                                                                                            <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																						<?php endif; ?>
                                                                                        <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                                                                    </div>
                                                                                </div>
																				<?php
																				$c ++;
																			}
																		}
																	?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                                    </div>
                                                </td>
                                                <td>
													<?php if ( $sortable ): ?>
                                                        <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
													<?php endif; ?>
                                                    <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                                </td>
                                            </tr>
											<?php
											$i ++;
										endforeach;
									else:
										?>
                                        <tr data-cat="0">
                                            <td>
                                                <div class="features_category_wrapper text-center">
                                                    <div class="field-list <?php echo esc_attr( $field_id ); ?>">
                                                        <div class="feature_category_inner_wrap">
                                                            <div class="feature_category_title"><label><?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label><input type="text" name="rbfw_feature_category[0][cat_title]" data-key="0" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"/></div>
                                                            <div class="feature_category_inner_item_wrap sortable">
                                                                <div class="item">
                                                                    <a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                                                                    <div class="rbfw_feature_icon_preview p-1" data-key="0"></div>
                                                                    <input type='hidden' name='rbfw_feature_category[0][cat_features][0][icon]' placeholder='<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>' data-key="0" class="rbfw_feature_icon"/>
                                                                    <input type='text' name='rbfw_feature_category[0][cat_features][0][title]' placeholder='<?php echo esc_attr( $placeholder ); ?>' value='' data-key="0"/>
                                                                    <div>
																		<?php if ( $sortable ): ?>
                                                                            <span class="button sort"><i class="fas fa-arrows-alt"></i></span><?php endif; ?><span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                            </td>
                                            <td>
												<?php if ( $sortable ): ?>
                                                    <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
												<?php endif; ?>
                                                <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo wp_kses_post( $remove_text ); ?></span>
                                            </td>
                                        </tr>
									<?php
									endif;
								?>
                                </tbody>
                            </table>
                            <span class="ppof-button add-feature-category mt-1"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature Category', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                    </section>
                </div>
				<?php
				return ob_get_clean();
			}

			public function features_category( $post_id ) {
				?>
				<?php $this->panel_header( 'Item Features Settings', 'Here you can add all features as category if needed.' ); ?>
				<?php
				$options          = array(
					'id'          => 'rbfw_feature_category',
					'type'        => 'feature_category',
					'placeholder' => 'Features Name',
				);
				$option_value     = get_post_meta( $post_id, $options['id'], true );
				$options['value'] = is_serialized( $option_value ) ? unserialize( $option_value ) : $option_value;
				$id = isset( $option['id'] ) ? $option['id'] : "";
				$field_name  = isset( $options['field_name'] ) ? $options['field_name'] : $id;
				$conditions  = isset( $options['conditions'] ) ? $options['conditions'] : array();
				$placeholder = isset( $options['placeholder'] ) ? $options['placeholder'] : "";
				$remove_text = isset( $options['remove_text'] ) ? $options['remove_text'] : '<i class="fas fa-trash-can"></i>';
				$sortable    = isset( $options['sortable'] ) ? $options['sortable'] : true;
				$default     = isset( $options['default'] ) ? $options['default'] : array();
				$values = isset( $option['value'] ) ? $option['value'] : array();
				$values = ! empty( $values ) ? $values : $default;
				$limit = ! empty( $option['limit'] ) ? $option['limit'] : '';
				$field_id   = $id;
				$field_name = ! empty( $field_name ) ? $field_name : $id;
				?>
                <script type="text/javascript">
                    jQuery(".sortable_tr").sortable({handle: '.tr_sort_handler'});
                    jQuery('.tr_remove').click(function (e) {
                        jQuery(this).closest("tr").remove();
                    });
                    jQuery(document).on('click', '.add-feature-category', function (e) {
                        e.stopImmediatePropagation();
                        let dataCat = jQuery('.rbfw_feature_category_table tbody tr:last-child').attr('data-cat');
                        let nextCat = parseInt(dataCat) + 1;
                        let html = '<tr data-cat="' + nextCat + '"><td><div class="features_category_wrapper text-center"><div class="field-list rbfw_feature_category"><div class="feature_category_inner_wrap"><div class="feature_category_title"><label><?php echo esc_html__( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label><input type="text" class="rbfw_feature_category_title" name="rbfw_feature_category[' + nextCat + '][cat_title]" data-cat="' + nextCat + '" placeholder="<?php echo esc_attr__( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"></div><div class="feature_category_inner_item_wrap sortable"><div class="item"><a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i><?php _e('Icon','booking-and-rental-manager-for-woocommerce'); ?></a><div class="rbfw_feature_icon_preview" data-key="0"></div><input type="hidden" name="rbfw_feature_category[' + nextCat + '][cat_features][0][icon]" placeholder="<?php echo esc_attr__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="0" class="rbfw_feature_icon"> <input type="text" name="rbfw_feature_category[' + nextCat + '][cat_features][0][title]" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="" data-key="0"><div><?php if($sortable):?> <span class="button sort"><i class="fas fa-arrows-alt"></i></span><?php endif; ?><span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post( $remove_text ); ?></span></div></div></div></div></div><button type="button" class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php echo esc_html__( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></button></div></td><td> <?php if($sortable):?> <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button tr_remove"><?php echo wp_kses_post( $remove_text ); ?></span></td></tr>';
                        jQuery('.rbfw_feature_category_table tbody').append(html);
                        jQuery(".sortable_tr").sortable({handle: '.tr_sort_handler'});
                        jQuery('.tr_remove').click(function (e) {
                            jQuery(this).closest("tr").remove();
                        });
                    });
                    jQuery(document).on('click', '.add-new-feature', function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        let data_key = jQuery(this).siblings(".rbfw_feature_category").find("div.item:last-child input").attr('data-key');
                        let i = parseInt(data_key) || 0; // Ensuring it is a number
                        let c = i + 1;
                        let theTarget = jQuery(this).siblings('.rbfw_feature_category').find('.feature_category_inner_wrap .feature_category_inner_item_wrap');
                        jQuery(".sortable").sortable({handle: '.sort'});
                        let dataCat = jQuery(this).closest('tr').attr('data-cat');
                        let html = '<div class="item">';
                        html += '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="' + c + '">';
                        html += '<i class="fas fa-circle-plus"></i> <?php _e('Icon','booking-and-rental-manager-for-woocommerce'); ?>';
                        html += '</a>';
                        html += '<div class="rbfw_feature_icon_preview" data-key="' + c + '"></div>';
                        html += '<input type="hidden" name="rbfw_feature_category[' + dataCat + '][cat_features][' + c + '][icon]" placeholder="';
                        html += <?php echo json_encode( esc_html__( 'Icon', 'booking-and-rental-manager-for-woocommerce' ) ); ?> +'" data-key="' + c + '" class="rbfw_feature_icon"/>';
                        html += '<input type="text" name="rbfw_feature_category[' + dataCat + '][cat_features][' + c + '][title]" placeholder="';
                        html += <?php echo json_encode( esc_attr( $placeholder ) ); ?> +'" data-key="' + c + '"/>';
                        html += '<div>';

						<?php if ($sortable): ?>
                        html += ' <span class="button sort"><i class="fas fa-arrows-alt"></i></span>';
						<?php endif; ?>

                        html += '<span class="button remove" onclick="jQuery(this).parent().parent().remove()">';
                        html += <?php echo json_encode( wp_kses_post( $remove_text ) ); ?>;
                        html += '</span>';
                        html += '</div>';
                        html += '</div>';
                        theTarget.append(html);
                    });
                    // Features Icon Popup
                    jQuery(document).on('click', '.rbfw_feature_icon_btn', function (e) {
                        e.stopImmediatePropagation();
                        let remove_exist_data_key = jQuery("#rbfw_features_icon_list_wrapper").removeAttr('data-key');
                        let remove_active_label = jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                        let data_key = jQuery(this).attr('data-key');
                        let data_cat = jQuery(this).parents('tr').attr('data-cat');
                        jQuery('#rbfw_features_search_icon').val('');
                        jQuery('.rbfw_features_icon_list_body label').show();
                        jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', data_key);
                        jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', data_cat);
                        jQuery("#rbfw_features_icon_list_wrapper").mage_modal({
                            escapeClose: false,
                            clickClose: false,
                            showClose: false
                        });
                        // Selected Feature Icon Action
                        jQuery(document).on('click', '.rbfw_features_icon_list_wrapper_modal label', function (e) {

                            e.stopImmediatePropagation();
                            let selected_label = jQuery(this);
                            let selected_val = jQuery('input', this).val();
                            let selected_data_key = jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
                            let selected_data_cat = jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');

                            jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon_preview[data-key="' + selected_data_key + '"]').empty();
                            jQuery(selected_label).addClass('selected');
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon[data-key="' + selected_data_key + '"]').val(selected_val);
                            jQuery('.rbfw_feature_category_table tr[data-cat="' + selected_data_cat + '"]').find('.rbfw_feature_icon_preview[data-key="' + selected_data_key + '"]').append('<i class="' + selected_val + '"></i>');
                        });
                        // Icon Filter
                        jQuery('#rbfw_features_search_icon').keyup(function (e) {
                            let value = jQuery(this).val().toLowerCase();
                            jQuery(".rbfw_features_icon_list_body label[data-id]").show().filter(function () {
                                jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
                            }).hide();
                        });
                        // End Icon Filter
                    });
                    // End Features Icon Popup
                </script>
				<?php
				echo wp_kses( $this->field_feature_category( $options ), rbfw_allowed_html() );
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_gen_info">
					<?php $this->section_header(); ?>
                    <?php $this->sub_title( $post_id ); ?>
					<?php $this->select_category( $post_id ); ?>
					<?php $this->features_category( $post_id ); ?>
                </div>
			<?php }

			public function sub_title( $post_id ) {
                $sub_title = get_post_meta($post_id , 'rbfw_item_sub_title', true);
                $sub_title = $sub_title ? $sub_title : "Premium equipment rental with flexible timing";
                ?>
                <section class="bg-light mt-5">
                    <div>
                        <label>
							<?php esc_html_e( "Sub Title",'booking-and-rental-manager-for-woocommerce'); ?>
                        </label>
                        <p><?php esc_html_e( "Add sub title",'booking-and-rental-manager-for-woocommerce'); ?></p>
                    </div>
                </section>
                <section class="rbfw-sub-title">
                    
                    
                    <input type="text" name="rbfw_item_sub_title" value="<?php echo esc_attr($sub_title); ?>" placeholder="<?php echo esc_attr($sub_title); ?>" style="width:100%;">
                    
                </section>
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
					$rbfw_categories = isset( $_POST['rbfw_categories'] )
						? rbfw_sanitize_rent_type_categories( wp_unslash( $_POST['rbfw_categories'] ) )
						: array();
					wp_set_object_terms( $post_id, $rbfw_categories, 'rbfw_item_caregory' );
					$feature_category_input = isset( $_POST['rbfw_feature_category'] ) ? wp_unslash( $_POST['rbfw_feature_category'] ) : array();
					$feature_category = rbfw_prepare_feature_category_meta_value( $feature_category_input );
					$sub_title = isset( $_POST['rbfw_item_sub_title'] ) ? RBFW_Function::data_sanitize( wp_unslash( $_POST['rbfw_item_sub_title'] ) ) : '';
					update_post_meta( $post_id, 'rbfw_item_sub_title', $sub_title );
					update_post_meta( $post_id, 'rbfw_categories', $rbfw_categories );
					update_post_meta( $post_id, 'rbfw_feature_category', $feature_category );
				}
			}
		}
		new RBFW_General_Info();
	}

       


