<?php if ( ! defined( 'ABSPATH' ) ) die; ?>

<div class="rbfw-me-wrap is-loading" data-post-id="<?php echo esc_attr( $post_id ); ?>">

	<!-- ── Header ───────────────────────────────────────────────────────── -->
	<div class="rbfw-me-header">
		<div class="mep-top-nav-info">
		<div class="rbfw-me-header__left">
			<a class="rbfw-me-back" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rbfw_item' ) ); ?>">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
				<span class="rbfw-me-back__text"><?php esc_html_e( 'Back to Items', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
			</a>
		</div>
		<div class="rbfw-me-header__center">
			<h1 class="rbfw-me-title-display"><?php echo esc_html( $screen_title ); ?></h1>
		</div>
		<div class="rbfw-me-header__right">
			<?php if ( $classic_url ) : ?>
				<a class="rbfw-me-btn rbfw-me-btn--ghost rbfw-me-classic-switch" href="<?php echo esc_url( $classic_url ); ?>" title="<?php esc_attr_e( 'Switch to Classic Editor', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<span class="dashicons dashicons-editor-code"></span>
					<?php esc_html_e( 'Classic editor', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</a>
			<?php endif; ?>
			<div class="rbfw-me-publish-group">
				<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-publish" data-published="<?php echo esc_attr( $is_published ? '1' : '0' ); ?>">
					<?php echo esc_html( $is_published
						? __( 'Update', 'booking-and-rental-manager-for-woocommerce' )
						: __( 'Publish', 'booking-and-rental-manager-for-woocommerce' )
					); ?>
				</button>
				<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-publish-chevron" aria-label="<?php esc_attr_e( 'More options', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="rbfw-me-publish-dropdown" hidden>
					<?php if ( $permalink ) : ?>
						<a class="rbfw-me-publish-dropdown__item" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'Preview', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
					<?php endif; ?>
					<button type="button" class="rbfw-me-publish-dropdown__item rbfw-me-save-draft">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Draft', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
				</div>
			</div>
		</div>
		</div><!-- /.mep-top-nav-info -->
	</div>

	<div class="rbfw-me-save-indicator" role="status" aria-live="polite" aria-atomic="true"></div>

	<div class="rbfw-me-page-loader" aria-hidden="true">
		<div class="rbfw-me-page-loader__inner">
			<div class="rbfw-me-sk-tabs">
				<span class="rbfw-me-sk rbfw-me-sk-tab"></span>
				<span class="rbfw-me-sk rbfw-me-sk-tab"></span>
				<span class="rbfw-me-sk rbfw-me-sk-tab"></span>
				<span class="rbfw-me-sk rbfw-me-sk-tab"></span>
			</div>
			<div class="rbfw-me-page-loader__grid">
				<div class="rbfw-me-sk-card">
					<div class="rbfw-me-sk rbfw-me-sk-card-head"></div>
					<div class="rbfw-me-sk rbfw-me-sk-card-sub"></div>
					<div class="rbfw-me-sk rbfw-me-sk-field"></div>
					<div class="rbfw-me-sk rbfw-me-sk-field rbfw-me-sk-field--short"></div>
					<div class="rbfw-me-sk rbfw-me-sk-editor"></div>
					<div class="rbfw-me-sk rbfw-me-sk-field"></div>
					<div class="rbfw-me-sk rbfw-me-sk-field rbfw-me-sk-field--short"></div>
				</div>
				<div class="rbfw-me-sk-sidebar">
					<div class="rbfw-me-sk rbfw-me-sk-side-card"></div>
					<div class="rbfw-me-sk rbfw-me-sk-side-card rbfw-me-sk-side-card--sm"></div>
				</div>
			</div>
			<p class="rbfw-me-page-loader__text"><?php esc_html_e( 'Loading editor…', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
		</div>
	</div>

	<!-- ── Steps progress bar ───────────────────────────────────────────── -->
	<div class="rbfw-me-tabs" role="tablist">
		<?php foreach ( $tabs as $i => $tab ) : ?>
			<button
				class="rbfw-me-tab <?php echo $i === 0 ? 'is-active' : ''; ?>"
				role="tab"
				data-tab="<?php echo esc_attr( $tab['key'] ); ?>"
				data-step="<?php echo $i; ?>"
				aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
			>
				<span class="rbfw-me-step-circle">
					<span class="rbfw-me-step-num"><?php echo $i + 1; ?></span>
					<span class="dashicons dashicons-yes rbfw-me-step-done-icon" aria-hidden="true"></span>
				</span>
				<span class="rbfw-me-step-label"><?php echo esc_html( $tab['label'] ); ?></span>
			</button>
		<?php endforeach; ?>
	</div>

	<?php
		/**
		 * Slim, editor-scoped notices shown directly under the step bar.
		 *
		 * Used by RBFW_Payment_Settings for the "No payment method is currently
		 * configured" banner, mirroring the taxi plugin's edit-screen notice.
		 * Nothing hooked here may output a named form control — the editor's
		 * collectFormData() serializes every [name] input inside .rbfw-me-wrap
		 * and would persist it as item meta.
		 */
		do_action( 'rbfw_modern_editor_notices', $post_id );
	?>

	<!-- ── Body ──────────────────────────────────────────────────────────── -->
	<div class="rbfw-me-body">
		<div class="rbfw-me-main">

			<!-- General ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel is-active" data-panel="general">
				<div class="rbfw-me-card" data-rbfw-tour="basic-info">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Basic Information', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Set the rental item name, type, and subtitle.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field">
							<label class="rbfw-me-field__label" for="rbfw_me_post_title"><?php esc_html_e( 'Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input class="rbfw-me-input rbfw-me-card-title-input" type="text" id="rbfw_me_post_title" name="post_title" value="<?php echo esc_attr( $post ? $post->post_title : '' ); ?>" placeholder="<?php esc_attr_e( 'Rental item name…', 'booking-and-rental-manager-for-woocommerce' ); ?>" autocomplete="off" />
						</div>
						<div class="rbfw-me-field">
							<label class="rbfw-me-field__label" for="rbfw_me_subtitle"><?php esc_html_e( 'Subtitle', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input class="rbfw-me-input" type="text" id="rbfw_me_subtitle" name="rbfw_item_sub_title" value="<?php echo esc_attr( $m['rbfw_item_sub_title'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Short description shown in hero…', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
						</div>
						<div class="rbfw-me-field" data-rbfw-tour="description">
							<div class="rbfw-me-editor-label-row">
								<label class="rbfw-me-field__label"><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<div class="rbfw-me-editor-switch">
									<button type="button" class="rbfw-me-sw-btn rbfw-me-sw-visual is-active">VISUAL</button>
									<button type="button" class="rbfw-me-sw-btn rbfw-me-sw-code">CODE</button>
								</div>
							</div>
							<div class="rbfw-me-editor-wrap">
								<?php
								wp_editor(
									$post ? apply_filters( 'the_content', $post->post_content ) : '',
									'rbfw_me_post_content',
									[
										'textarea_name' => 'post_content',
										'textarea_rows' => 10,
										'media_buttons' => true,
										'teeny'         => false,
										'quicktags'     => true,
										'tinymce'       => [
											'content_style' => 'body{padding:14px 16px !important;background:#fff !important;}',
										],
									]
								);
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Category Settings ───────────────────────────────── -->
				<div class="rbfw-me-card rbfw-me-rent-type-card" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_rent_type_crud' ) ); ?>" data-can-manage="<?php echo current_user_can( 'manage_categories' ) ? '1' : '0'; ?>">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Category Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p>
							<?php esc_html_e( 'Here you can manage rent type.', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<?php if ( current_user_can( 'manage_categories' ) ) : ?>
							<a href="#" class="rbfw-rent-type-add-trigger"><?php esc_html_e( 'Add new rent type', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
							<?php endif; ?>
						</p>
					</div>
					<div class="rbfw-me-card__body">
						<?php
						$saved_cats_str = implode( ',', $saved_cat_names );
						?>
						<input type="hidden" name="rbfw_categories[]" class="rbfw-me-cats-hidden" value="<?php echo esc_attr( $saved_cats_str ); ?>">
						<div class="rbfw-me-checkbox-grid">
							<?php foreach ( $all_cat_terms as $term ) :
								$checked   = in_array( strtolower( trim( $term->name ) ), $saved_cat_names, true );
								$cat_depth = isset( $term->depth ) ? (int) $term->depth : 0;
								$image_id  = (int) get_term_meta( $term->term_id, 'rentiva_category_image_id', true );
								$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
							?>
								<label class="rbfw-me-checkbox-label rbfw-rt-chip<?php echo $cat_depth > 0 ? ' rbfw-rt-chip--child' : ''; ?>" data-term-id="<?php echo esc_attr( $term->term_id ); ?>" data-name="<?php echo esc_attr( $term->name ); ?>" data-parent="<?php echo esc_attr( (int) $term->parent ); ?>" data-depth="<?php echo esc_attr( $cat_depth ); ?>" data-image-id="<?php echo esc_attr( $image_id ); ?>" data-image-url="<?php echo esc_url( $image_url ); ?>" style="--rbfw-rt-depth: <?php echo esc_attr( $cat_depth ); ?>;">
									<input
										type="checkbox"
										class="rbfw-me-cat-checkbox"
										data-name="<?php echo esc_attr( $term->name ); ?>"
										<?php checked( $checked ); ?>
									/>
									<?php if ( $cat_depth > 0 ) : ?><span class="rbfw-rt-subarrow" aria-hidden="true">&rsaquo;</span><?php endif; ?><span><?php echo esc_html( ucfirst( $term->name ) ); ?></span><?php if ( current_user_can( 'manage_categories' ) ) : ?><span class="rbfw-rt-actions"><span class="rbfw-rt-edit dashicons dashicons-edit" title="<?php esc_attr_e( 'Edit', 'booking-and-rental-manager-for-woocommerce' ); ?>"></span><span class="rbfw-rt-del dashicons dashicons-trash" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>"></span></span><?php endif; ?>
								</label>
							<?php endforeach; ?>
						</div>
						<?php if ( empty( $all_cat_terms ) ) : ?>
							<p class="rbfw-me-field__help rbfw-me-rent-type-empty">
								<?php esc_html_e( 'No rent types found.', 'booking-and-rental-manager-for-woocommerce' ); ?>
								<?php if ( current_user_can( 'manage_categories' ) ) : ?>
								<a href="#" class="rbfw-rent-type-add-trigger"><?php esc_html_e( 'Create one', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>

					<div class="rbfw-me-faq-modal" id="rbfw-me-rent-type-modal">
						<div class="rbfw-me-faq-modal__backdrop"></div>
						<div class="rbfw-me-faq-modal__box">
							<div class="rbfw-me-faq-modal__head">
								<h3><?php esc_html_e( 'Add New Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
								<button type="button" class="rbfw-me-faq-modal__close"><span class="dashicons dashicons-no-alt"></span></button>
							</div>
							<div class="rbfw-me-faq-modal__body">
								<div class="rbfw-me-field">
									<label class="rbfw-me-field__label" for="rbfw-me-rent-type-modal-input"><?php esc_html_e( 'Rent type name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<input class="rbfw-me-input" type="text" id="rbfw-me-rent-type-modal-input" maxlength="200" placeholder="<?php esc_attr_e( 'e.g. Bike, Car, Equipment…', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
								</div>
								<div class="rbfw-me-field">
									<label class="rbfw-me-field__label"><?php esc_html_e( 'Rent type image', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="rbfw-me-field__optional">(<?php esc_html_e( 'optional', 'booking-and-rental-manager-for-woocommerce' ); ?>)</span></label>
									<div class="rbfw-rt-image-field">
										<div class="rbfw-rt-image-field__preview" id="rbfw-me-rent-type-modal-image-preview">
											<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
										</div>
										<div class="rbfw-rt-image-field__actions">
											<input type="hidden" id="rbfw-me-rent-type-modal-image-id" value="">
											<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary" id="rbfw-me-rent-type-modal-image-select">
												<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Select Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
											</button>
											<button type="button" class="rbfw-me-btn rbfw-me-btn--danger" id="rbfw-me-rent-type-modal-image-remove" style="display:none">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</div>
									</div>
									<p class="rbfw-me-field__hint"><?php esc_html_e( 'Shown on the front-end category grid.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
								</div>
							</div>
							<div class="rbfw-me-faq-modal__foot">
								<button type="button" id="rbfw-me-rent-type-modal-save" class="rbfw-me-btn rbfw-me-btn--primary"><?php esc_html_e( 'Add Rent Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
								<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-rent-type-modal-cancel"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</div>
						</div>
					</div>
				</div>

				<!-- Item Features ─────────────────────────────────── -->
				<div class="rbfw-me-card" data-rbfw-tour="features">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Item Features Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Add all features as category if needed.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body rbfw-me-features-body">
						<table class="rbfw_feature_category_table rbfw-me-features-table">
							<tbody class="sortable_tr">
								<?php if ( ! empty( $feature_categories ) ) :
									$i = 0;
									foreach ( $feature_categories as $cat ) :
										$cat_title    = $cat['cat_title'] ?? '';
										$cat_features = $cat['cat_features'] ?? [];
								?>
								<tr data-cat="<?php echo esc_attr( $i ); ?>">
									<td>
										<div class="features_category_wrapper">
											<div class="field-list rbfw_feature_category">
												<div class="feature_category_inner_wrap">
													<div class="feature_category_title">
														<label><?php esc_html_e( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
														<input type="text" name="rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_title]" value="<?php echo esc_attr( $cat_title ); ?>" data-key="<?php echo esc_attr( $i ); ?>" placeholder="<?php esc_attr_e( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
														<div class="rbfw-me-features-actions">
															<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
															<span class="button tr_remove" onclick="jQuery(this).closest('tr').remove()"><i class="fas fa-trash-can"></i></span>
														</div>
													</div>
													<div class="feature_category_inner_item_wrap sortable">
														<?php $c = 0; foreach ( $cat_features as $feature ) :
															$icon  = $feature['icon']  ?? '';
															$title = $feature['title'] ?? '';
														?>
														<div class="item">
															<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="<?php echo esc_attr( $c ); ?>"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
															<div class="rbfw_feature_icon_preview" data-key="<?php echo esc_attr( $c ); ?>"><?php if ( $icon ) echo '<i class="' . esc_attr( $icon ) . '"></i>'; ?></div>
															<input type="hidden" name="rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][icon]" value="<?php echo esc_attr( $icon ); ?>" data-key="<?php echo esc_attr( $c ); ?>" class="rbfw_feature_icon" />
															<input type="text" name="rbfw_feature_category[<?php echo esc_attr( $i ); ?>][cat_features][<?php echo esc_attr( $c ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Features Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="<?php echo esc_attr( $c ); ?>" />
															<div>
																<span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																<span class="button remove" onclick="jQuery(this).parent().parent().remove()"><i class="fas fa-trash-can"></i></span>
															</div>
														</div>
														<?php $c++; endforeach; ?>
													</div>
												</div>
											</div>
											<button type="button" class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
										</div>
									</td>
								</tr>
								<?php $i++; endforeach;
								else : ?>
								<tr data-cat="0">
									<td>
										<div class="features_category_wrapper">
											<div class="field-list rbfw_feature_category">
												<div class="feature_category_inner_wrap">
													<div class="feature_category_title">
														<label><?php esc_html_e( 'Feature Category Title', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
														<input type="text" name="rbfw_feature_category[0][cat_title]" data-key="0" placeholder="<?php esc_attr_e( 'Feature Category Label', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
														<div class="rbfw-me-features-actions">
															<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
															<span class="button tr_remove" onclick="jQuery(this).closest('tr').remove()"><i class="fas fa-trash-can"></i></span>
														</div>
													</div>
													<div class="feature_category_inner_item_wrap sortable">
														<div class="item">
															<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Icon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
															<div class="rbfw_feature_icon_preview" data-key="0"></div>
															<input type="hidden" name="rbfw_feature_category[0][cat_features][0][icon]" data-key="0" class="rbfw_feature_icon" />
															<input type="text" name="rbfw_feature_category[0][cat_features][0][title]" placeholder="<?php esc_attr_e( 'Features Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" data-key="0" />
															<div>
																<span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																<span class="button remove" onclick="jQuery(this).parent().parent().remove()"><i class="fas fa-trash-can"></i></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<button type="button" class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Feature', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
										</div>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<button type="button" class="ppof-button add-feature-category mt-1">
							<i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add Feature Category', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Pricing ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="pricing" data-item-type="<?php echo esc_attr( get_post_meta( $post_id, 'rbfw_item_type', true ) ?: 'bike_car_sd' ); ?>">
				<?php $GLOBALS['rbfw_modern_editor_rendering'] = true; ?>
				<div class="rbfw-me-card" data-rbfw-tour="pricing">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Pricing::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<div class="rbfw-me-card" data-rbfw-tour="extra-service">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Extra_Service::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<?php if ( has_action( 'rbfw_after_extra_service_table' ) ) : ?>
				<div class="rbfw-me-card rbfw-me-addon-discount-card">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php do_action( 'rbfw_after_extra_service_table' ); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( has_action( 'rbfw_after_week_price_table' ) ) : ?>
				<div class="rbfw-me-card rbfw-me-addon-seasonal-card">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php do_action( 'rbfw_after_week_price_table', $post_id ); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( has_action( 'rbfw_after_general_price_table' ) || has_action( 'rbfw_after_room_type_price_saver_price_table' ) ) : ?>
				<div class="rbfw-me-card rbfw-me-addon-mds-card">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php do_action( 'rbfw_after_general_price_table', $post_id ); ?>
						<?php do_action( 'rbfw_after_room_type_price_saver_price_table', $post_id ); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php
				// Inventory (stock quantity + variations). The classic editor hides this
				// for resort / appointment; mirror that here, with applyType()
				// in the JS keeping it in sync when the rental type is changed live.
				// Single Day (bike_car_sd) now supports item variations, so it is no
				// longer hidden. Multiple Items carries its own per-item stock in the
				// pricing table, so the card-level inventory does not apply to it.
				if ( class_exists( 'RBFW_Inventory' ) ) :
					$rbfw_me_inv_type   = get_post_meta( $post_id, 'rbfw_item_type', true ) ?: 'bike_car_sd';
					$rbfw_me_inv_hidden = in_array( $rbfw_me_inv_type, [ 'resort', 'appointment', 'multiple_items' ], true );
				?>
				<div class="rbfw-me-card rbfw-me-inventory-card<?php echo $rbfw_me_inv_hidden ? ' rbfw-me-hidden' : ''; ?>">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Inventory', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Manage stock quantity, return-date availability and item variations.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Inventory::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php
				// Buffer Time — moved here from the Off Days step. The fields keep their
				// original names, and collectFormData() scans the whole editor wrap, so
				// saving is unaffected by which panel renders them.
				if ( class_exists( 'RBFW_Off_Day' ) ) : ?>
				<div class="rbfw-me-card rbfw-me-buffer-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Buffer Time', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Reserve extra hours before and after each booking for cleaning, preparation or turnaround.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<?php RBFW_Off_Day::render_buffer_for_modern_editor( $post_id ); ?>
					</div>
				</div>
				<?php endif; ?>

				<div class="rbfw-me-card" data-rbfw-tour="fee-management">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Configure multiple fees with different calculation types and frequencies.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Fee_Management::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>
				<?php unset( $GLOBALS['rbfw_modern_editor_rendering'] ); ?>
			</div>

			<!-- Off Days ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="offday">
				<?php RBFW_Off_Day::render_for_modern_editor( $post_id ); ?>
			</div>

			<!-- Advanced ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="advanced">

				<?php
				// Location Configuration (pick-up / drop-off + Location Inventory &
				// Price) doesn't apply to Resort (guests come to the resort, there's
				// no pickup point) or Appointment (a fixed business location) -- hide
				// the whole card for those two types. applyType() in
				// rbfw-modern-editor.js mirrors this for a live type switch.
				$rbfw_me_loc_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ?: 'bike_car_sd';
				if ( class_exists( 'RBFW_Location' ) ) :
					$rbfw_me_loc_hidden = in_array( $rbfw_me_loc_type, [ 'resort', 'appointment' ], true );
				?>
				<div class="rbfw-me-card rbfw-me-location-card<?php echo $rbfw_me_loc_hidden ? ' rbfw-me-hidden' : ''; ?>">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Location Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Configure pick-up and drop-off locations for this rental item.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<?php RBFW_Location::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Template picker -->
				<?php
				$screenshot_url = RBFW_PLUGIN_URL . '/templates/screenshot/';
				$templates      = RBFW_Function::get_all_template();
				$current_tpl    = $m['rbfw_single_template'] ?? 'Default';
				?>
				<div class="rbfw-me-card" data-rbfw-tour="template">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Template', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Choose how this rental item page looks to customers.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<input type="hidden" name="rbfw_single_template" class="rbfw-me-tpl-value" value="<?php echo esc_attr( $current_tpl ); ?>" />
						<div class="rbfw-me-template-grid">
							<?php foreach ( $templates as $key => $label ) : ?>
								<div class="rbfw-me-tpl-card <?php echo $current_tpl === $key ? 'is-selected' : ''; ?>" data-tpl="<?php echo esc_attr( $key ); ?>">
									<div class="rbfw-me-tpl-card__img">
										<img src="<?php echo esc_url( $screenshot_url . strtolower( $key ) . '.webp' ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy" />
									</div>
									<div class="rbfw-me-tpl-card__label">
										<?php if ( $current_tpl === $key ) : ?>
											<span class="rbfw-me-tpl-badge"><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<?php endif; ?>
										<strong><?php echo esc_html( $label ); ?></strong>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Additional Gallery (visible only for Muffin template) -->
				<?php
				$current_tpl_adv    = $m['rbfw_single_template'] ?? 'Default';
				$add_gallery_on     = ( $m['rbfw_enable_additional_gallary'] ?? 'off' ) === 'on';
				$add_gallery_images = get_post_meta( $post_id, 'rbfw_gallery_images_additional', true );
				$add_gallery_images = is_array( $add_gallery_images ) ? array_filter( $add_gallery_images ) : [];
				?>
				<div class="rbfw-me-card rbfw-me-additional-gallery-card <?php echo $current_tpl_adv === 'Muffin' ? '' : 'rbfw-me-hidden'; ?>">
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable Additional Gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
								<span class="rbfw-me-field__desc"><?php esc_html_e( 'Enable / Disable the additional gallery section on the item page.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_additional_gallary" value="on" <?php checked( $add_gallery_on ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-add-gallery-images" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-add-gallery-images <?php echo $add_gallery_on ? '' : 'rbfw-me-hidden'; ?>">
							<div class="rbfw-me-gallery-list rbfw-me-add-gallery-list">
								<?php foreach ( $add_gallery_images as $image_id ) :
									$img_url = wp_get_attachment_url( $image_id );
									if ( ! $img_url ) continue;
								?>
									<div class="rbfw-me-gallery-image">
										<button type="button" class="rbfw-me-gallery-remove" onclick="jQuery(this).closest('.rbfw-me-gallery-image').remove()">
											<i class="fas fa-trash-can"></i>
										</button>
										<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
										<input type="hidden" name="rbfw_gallery_images_additional[]" value="<?php echo esc_attr( $image_id ); ?>" />
									</div>
								<?php endforeach; ?>
							</div>
							<div class="rbfw-me-gallery-actions">
								<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-add-gallery-upload">
									<span class="dashicons dashicons-plus-alt2"></span>
									<?php esc_html_e( 'Upload Images', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</button>
								<button type="button" class="rbfw-me-add-gallery-clear">
									<?php esc_html_e( 'Clear All', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- FAQ Settings ────────────────────────────────────────── -->
				<div class="rbfw-me-card" data-rbfw-tour="faq">
					<div class="rbfw-me-card__body">
						<?php RBFW_Faq_Settings::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Tax Settings ─────────────────────────────────────────── -->
				<?php $tax_enabled = ( $m['rbfw_enable_tax_settings'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card" data-rbfw-tour="tax-settings">
					<div class="rbfw-me-card__head rbfw-me-card__head--with-toggle">
						<div>
							<h2><?php esc_html_e( 'Tax Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Here you can set tax information.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="rbfw-me-toggle">
							<input type="checkbox" name="rbfw_enable_tax_settings" value="yes" <?php checked( $tax_enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-tax-settings-body" />
							<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
						</label>
					</div>
					<div class="rbfw-me-card__body rbfw-me-tax-settings-body<?php echo $tax_enabled ? '' : ' rbfw-me-hidden'; ?>">
						<?php RBFW_Tax::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Security Deposit ─────────────────────────────────────── -->
				<?php $deposit_enabled = ( $m['rbfw_enable_security_deposit'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card" data-rbfw-tour="security-deposit">
					<div class="rbfw-me-card__head rbfw-me-card__head--with-toggle">
						<div>
							<h2><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Turn on/off security deposit by switching this button.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="rbfw-me-toggle">
							<input type="checkbox" name="rbfw_enable_security_deposit" value="yes" <?php checked( $deposit_enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-security-deposit-body" />
							<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
						</label>
					</div>
					<div class="rbfw-me-card__body rbfw-me-security-deposit-body<?php echo $deposit_enabled ? '' : ' rbfw-me-hidden'; ?>">
						<?php RBFW_Security_Deposit::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Related Items ────────────────────────────────────────── -->
				<?php $related_enabled = ( $m['rbfw_enable_related_items'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card" data-rbfw-tour="related-items">
					<div class="rbfw-me-card__head rbfw-me-card__head--with-toggle">
						<div>
							<h2><?php esc_html_e( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Select related rental items to display on this item\'s page.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="rbfw-me-toggle">
							<input type="checkbox" name="rbfw_enable_related_items" value="yes" <?php checked( $related_enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-related-items-body" />
							<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
						</label>
					</div>
					<div class="rbfw-me-card__body rbfw-me-related-items-body<?php echo $related_enabled ? '' : ' rbfw-me-hidden'; ?>">
						<?php RBFW_Related::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Front-end Display Settings ───────────────────────────── -->
				<?php $frontend_enabled = ( $m['rbfw_enable_frontend_display'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head rbfw-me-card__head--with-toggle">
						<div>
							<h2><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="rbfw-me-toggle">
							<input type="checkbox" name="rbfw_enable_frontend_display" value="yes" <?php checked( $frontend_enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-frontend-settings-body" />
							<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
						</label>
					</div>
					<div class="rbfw-me-card__body rbfw-me-frontend-settings-body<?php echo $frontend_enabled ? '' : ' rbfw-me-hidden'; ?>">
						<?php RBFW_Settings::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Term Settings ────────────────────────────────────────── -->
				<?php $term_enabled = ( $m['rbfw_enable_term_content'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card" data-rbfw-tour="terms">
					<div class="rbfw-me-card__head rbfw-me-card__head--with-toggle">
						<div>
							<h2><?php esc_html_e( 'Term Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<p><?php esc_html_e( 'Configure rental terms and conditions.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<label class="rbfw-me-toggle">
							<input type="checkbox" name="rbfw_enable_term_content" value="yes" <?php checked( $term_enabled ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-term-settings-body" />
							<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
						</label>
					</div>
					<div class="rbfw-me-card__body rbfw-me-term-settings-body<?php echo $term_enabled ? '' : ' rbfw-me-hidden'; ?>">
						<?php RBFW_Terms_Settings::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<?php do_action( 'rbfw_modern_editor_advanced_sections', $post_id ); ?>

			</div>

			<!-- ── Step Navigation ──────────────────────────────────────── -->
			<div class="rbfw-me-step-nav">
				<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-step-prev" disabled>
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<?php esc_html_e( 'Previous', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</button>
				<span class="rbfw-me-step-counter"><?php printf( esc_html__( 'Step %d of %d', 'booking-and-rental-manager-for-woocommerce' ), 1, count( $tabs ) ); ?></span>
				<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-step-next">
					<?php esc_html_e( 'Next', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>

		</div><!-- /.rbfw-me-main -->

		<!-- ── Sidebar ───────────────────────────────────────────────────── -->
		<aside class="rbfw-me-sidebar">

			<?php
				/**
				 * Top of the editor sidebar, above the Featured Image.
				 *
				 * RBFW_Payment_Settings renders its "Payment Method" status card here
				 * (active booking flow + enabled gateway), matching the taxi plugin's
				 * transportation edit screen. Same rule as rbfw_modern_editor_notices:
				 * no named form controls — collectFormData() would save them as item meta.
				 */
				do_action( 'rbfw_modern_editor_sidebar_top', $post_id );
			?>

			<!-- Frontend Preview (Pricing step only) ─────────────────────────
			     Shown instead of the Featured Image/Gallery/Status cards while
			     the Pricing tab is active -- mirrors the live price breakdown a
			     customer would see on the frontend booking form, computed from
			     this screen's own (not-yet-saved) rate fields via JS so admins
			     can sanity-check pricing before saving. Covers Single Day/
			     Appointment (option + qty), Multiple Day/Equipment/Dress/Others
			     (date range), Resort (room + date range) and Multiple Items
			     (duration type/qty + pickup date + per-item qty). -->
			<?php
				$rbfw_gen_settings       = get_option( 'rbfw_basic_gen_settings' );
				$rbfw_count_extra_day_fp = ( is_array( $rbfw_gen_settings ) && isset( $rbfw_gen_settings['rbfw_count_extra_day_enable'] ) ) ? $rbfw_gen_settings['rbfw_count_extra_day_enable'] : 'on';
			?>
			<div class="rbfw-me-card rbfw-me-card--sidebar rbfw-me-frontend-preview" style="display:none" data-currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-count-extra-day="<?php echo esc_attr( $rbfw_count_extra_day_fp ); ?>">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Frontend Preview', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'What a customer would see and pay right now.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
				<div class="rbfw-me-card__body rbfw-me-fp-body">
					<!-- Featured image + item name, mirrored live from the Title field and
					     the Featured Image card -- what a customer sees before they even
					     start booking. -->
					<div class="rbfw-me-fp-feature">
						<div class="rbfw-me-fp-feature-img">
							<span class="rbfw-me-fp-feature-placeholder">
								<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"></rect><circle cx="9" cy="9" r="1.5" fill="currentColor" stroke="none"></circle><path d="M21 15l-5-5L5 21"></path></svg>
							</span>
						</div>
						<div class="rbfw-me-fp-feature-name">&nbsp;</div>
					</div>

					<!-- Multiple Day only: badges + summary heading, shown above the
					     price box instead of the plain "From $X" line the other types
					     use (toggled by updateVisibility() via the --boxed modifier
					     below and this block's own display). -->
					<div class="rbfw-me-fp-md-summary-head" style="display:none">
						<div class="rbfw-me-fp-badges">
							<span class="rbfw-me-fp-badge rbfw-me-fp-badge--available"><span class="rbfw-me-fp-badge-dot"></span><?php esc_html_e( 'Available Today', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="rbfw-me-fp-badge rbfw-me-fp-badge--bestseller"><?php esc_html_e( 'Best Seller', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<h4 class="rbfw-me-fp-md-title"><?php esc_html_e( 'Instant Booking Summary', 'booking-and-rental-manager-for-woocommerce' ); ?></h4>
						<p class="rbfw-me-fp-md-subtitle"><?php esc_html_e( 'Select dates to see final price and availability in real time.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>

					<div class="rbfw-me-fp-pricerow">
						<span class="rbfw-me-fp-pricerow-label"><?php esc_html_e( 'Starting from', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<span class="rbfw-me-fp-pricerow-plain"><?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<strong class="rbfw-me-fp-from-amt">&nbsp;</strong>
						<span class="rbfw-me-fp-pricerow-unit">/ <?php esc_html_e( 'Day', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<!-- Multiple Day only: trust badges under the price box. -->
					<div class="rbfw-me-fp-trustrow" style="display:none">
						<span class="rbfw-me-fp-trust">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="8 12 11 15 16 9"></polyline></svg>
							<?php esc_html_e( 'Instant confirmation', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</span>
						<span class="rbfw-me-fp-trust">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="9" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
							<?php esc_html_e( 'Secure payment', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</span>
					</div>

					<!-- Pickup / Drop-off Location (Location Configuration, Advanced tab) --
					     shown for every rent type once enabled with at least one location,
					     mirroring the real booking form's own select. Sits before any
					     date/time selection, same position as on the real booking forms. -->
					<div class="rbfw-me-fp-field rbfw-me-fp-pickup-loc-field" style="display:none">
						<label class="rbfw-me-fp-label"><?php esc_html_e( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<select class="rbfw-me-fp-select rbfw-me-fp-pickup-loc"></select>
					</div>
					<div class="rbfw-me-fp-field rbfw-me-fp-dropoff-loc-field" style="display:none">
						<label class="rbfw-me-fp-label"><?php esc_html_e( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<select class="rbfw-me-fp-select rbfw-me-fp-dropoff-loc"></select>
					</div>

					<!-- Calendar: Single Day only -- a plain display/booking-detail date, it
					     doesn't affect price (rbfw_bikecarsd_price_calculation is date-independent). -->
					<div class="rbfw-me-fp-field rbfw-me-fp-calendar-field" style="display:none">
						<label class="rbfw-me-fp-label"><?php esc_html_e( 'Select Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<div class="rbfw-me-fp-cal">
							<div class="rbfw-me-fp-cal-month"></div>
							<div class="rbfw-me-fp-cal-dow"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
							<div class="rbfw-me-fp-cal-grid"></div>
							<div class="rbfw-me-fp-cal-legend">
								<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--weekend"></i><?php esc_html_e( 'Weekend', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--off"></i><?php esc_html_e( 'Unavailable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
						</div>
					</div>

					<!-- Time Slot chips: Single Day / Appointment only -->
					<div class="rbfw-me-fp-field rbfw-me-fp-sd-time-field" style="display:none">
						<label class="rbfw-me-fp-label"><?php esc_html_e( 'Pickup Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<div class="rbfw-me-fp-chips rbfw-me-fp-sd-time-chips"></div>
					</div>

					<!-- Single Day / Appointment: rental option rows + qty stepper -->
					<div class="rbfw-me-fp-sd-controls">
						<div class="rbfw-me-fp-section-divider"></div>
						<p class="rbfw-me-fp-section-label"><?php esc_html_e( 'Rental Option', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-optrows rbfw-me-fp-sd-optrows"></div>
						<div class="rbfw-me-fp-field rbfw-me-fp-field--inline rbfw-me-fp-sd-qty-field" style="display:none">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<div class="rbfw-me-fp-stepper">
								<div class="rbfw-me-fp-stepper-pill rbfw-me-fp-sd-stepper">
									<button type="button" class="rbfw-me-fp-step-minus" data-step="-1">&minus;</button>
									<span class="rbfw-me-fp-sd-qty-val">1</span>
									<button type="button" class="rbfw-me-fp-step-plus" data-step="1">+</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Multiple Items: duration-type pills, top half of a card that
					     visually continues into the Pickup Date/Time card below it
					     (see .is-mi-card / .is-mi in the CSS -- matching borders, no
					     gap, so the two physical elements read as one card). The
					     Rental Duration qty picker itself lives inside the pickup row
					     below (rbfw-me-fp-mi-duration-field), paired with Pickup Date. -->
					<div class="rbfw-me-fp-mi-controls">
						<div class="rbfw-me-fp-section-divider"></div>
						<p class="rbfw-me-fp-section-label rbfw-me-fp-mi-duration-title"><?php esc_html_e( 'Rental Duration Type', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-chips rbfw-me-fp-mi-duration-chips"></div>
					</div>

					<!-- Multiple Day / Resort: Pickup Date+Time, Return Date+Time paired per row.
					     The Time fields are selects (not chips) styled to match the date
					     fields -- only Multiple Day shows them (Time Picker on), hidden for
					     Resort. Resort additionally wraps both rows in a bordered card with
					     its own "Check-In & Check-Out Date" header (rbfw-me-fp-resort-dates-*,
					     toggled in updateVisibility()) -- the rows/fields/inputs themselves
					     are unchanged so recalcResort()/recalc() keep working unmodified. -->
					<div class="rbfw-me-fp-resort-dates-card" style="display:none">
						<div class="rbfw-me-fp-resort-dates-head">
							<span><?php esc_html_e( 'Check-In & Check-Out Date', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
						</div>
						<div class="rbfw-me-fp-resort-dates-body">
					<div class="rbfw-me-fp-row rbfw-me-fp-pickup-row">
						<!-- Multiple Items only: pairs with Pickup Date below in the same
						     row (see .is-mi flex-wrap rules in the CSS); the real data is
						     still the same qty $miControls stores, just presented as a
						     "1 Day"-style dropdown instead of a +/- stepper. -->
						<div class="rbfw-me-fp-field rbfw-me-fp-mi-duration-field" style="display:none">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Rental Duration', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<div class="rbfw-me-fp-dtfield">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
								<select class="rbfw-me-fp-mi-qty-select"></select>
								<svg class="rbfw-me-fp-dtfield-chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
							</div>
						</div>
						<div class="rbfw-me-fp-field">
							<div class="rbfw-me-fp-resort-date-top">
								<label class="rbfw-me-fp-label rbfw-me-fp-start-label"><?php esc_html_e( 'Pickup Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<button type="button" class="rbfw-me-fp-dtfield-clear rbfw-me-fp-start-clear" aria-label="<?php esc_attr_e( 'Clear pickup date', 'booking-and-rental-manager-for-woocommerce' ); ?>" tabindex="-1">&times;</button>
							</div>
							<div class="rbfw-me-fp-dtfield">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
								<span class="rbfw-me-fp-dtfield-display"></span>
								<input type="date" class="rbfw-me-fp-start">
							</div>
							<!-- Multiple Day only: custom off-day-aware calendar popover,
							     same grid/legend markup and CSS as the Single Day calendar
							     above (.rbfw-me-fp-cal), reused here so Pickup/Return can
							     grey out configured Off Days the way a native date input
							     never can. Opened/closed and rendered in JS
							     (initMdCalendarPopovers() in rbfw-modern-editor.js); the
							     underlying input.rbfw-me-fp-start value is still what
							     recalc() reads, so picking a day here just sets that value
							     and fires 'change' same as typing into the native picker
							     would -- Resort/Multiple Items keep using the native picker
							     untouched. -->
							<div class="rbfw-me-fp-md-cal-popover rbfw-me-fp-md-cal-popover--start" style="display:none">
								<div class="rbfw-me-fp-cal">
									<div class="rbfw-me-fp-cal-month"></div>
									<div class="rbfw-me-fp-cal-dow"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
									<div class="rbfw-me-fp-cal-grid"></div>
									<div class="rbfw-me-fp-cal-legend">
										<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--weekend"></i><?php esc_html_e( 'Weekend', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--off"></i><?php esc_html_e( 'Unavailable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
									</div>
								</div>
							</div>
						</div>
						<div class="rbfw-me-fp-field rbfw-me-fp-md-start-time-field" style="display:none">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Pickup Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<div class="rbfw-me-fp-dtfield">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
								<select class="rbfw-me-fp-md-start-time"></select>
								<svg class="rbfw-me-fp-dtfield-chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
							</div>
						</div>
					</div>
					<div class="rbfw-me-fp-row rbfw-me-fp-return-row">
						<div class="rbfw-me-fp-field">
							<div class="rbfw-me-fp-resort-date-top">
								<label class="rbfw-me-fp-label rbfw-me-fp-end-label"><?php esc_html_e( 'Return Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<button type="button" class="rbfw-me-fp-dtfield-clear rbfw-me-fp-end-clear" aria-label="<?php esc_attr_e( 'Clear return date', 'booking-and-rental-manager-for-woocommerce' ); ?>" tabindex="-1">&times;</button>
							</div>
							<div class="rbfw-me-fp-dtfield">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
								<span class="rbfw-me-fp-dtfield-display"></span>
								<input type="date" class="rbfw-me-fp-end">
							</div>
							<!-- Multiple Day only: see the matching popover on Pickup Date
							     above for how this is wired up. Dates before the selected
							     Pickup Date are also disabled here, on top of Off Days. -->
							<div class="rbfw-me-fp-md-cal-popover rbfw-me-fp-md-cal-popover--end" style="display:none">
								<div class="rbfw-me-fp-cal">
									<div class="rbfw-me-fp-cal-month"></div>
									<div class="rbfw-me-fp-cal-dow"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
									<div class="rbfw-me-fp-cal-grid"></div>
									<div class="rbfw-me-fp-cal-legend">
										<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--weekend"></i><?php esc_html_e( 'Weekend', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
										<span><i class="rbfw-me-fp-cal-dot rbfw-me-fp-cal-dot--off"></i><?php esc_html_e( 'Unavailable', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
									</div>
								</div>
							</div>
						</div>
						<div class="rbfw-me-fp-field rbfw-me-fp-md-end-time-field" style="display:none">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Return Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<div class="rbfw-me-fp-dtfield">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
								<select class="rbfw-me-fp-md-end-time"></select>
								<svg class="rbfw-me-fp-dtfield-chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
							</div>
						</div>
					</div>
						</div>
					</div>

					<!-- Resort only: gates the Room Type section behind an explicit
					     Continue tap -- updateVisibility()/recalcResort() keep the room
					     rows, qty, summary and Book button hidden until this is clicked
					     (see the resortRoomsRevealed flag in the JS). -->
					<button type="button" class="rbfw-me-fp-resort-continue-btn" style="display:none">
						<?php esc_html_e( 'Continue', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>

					<div class="rbfw-me-fp-duration-banner" style="display:none">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
						<span class="rbfw-me-fp-duration-text"></span>
					</div>

					<!-- Resort: room rows + package + qty stepper -- sits after the
					     "X night(s)" duration banner above, per the approved layout. -->
					<div class="rbfw-me-fp-resort-controls">
						<div class="rbfw-me-fp-section-divider"></div>
						<p class="rbfw-me-fp-section-label"><?php esc_html_e( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-optrows rbfw-me-fp-resort-optrows"></div>
						<div class="rbfw-me-fp-field rbfw-me-fp-resort-package">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Package', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<select class="rbfw-me-fp-select rbfw-me-fp-resort-pkg">
								<option value="daynight"><?php esc_html_e( 'Day & Night', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
								<option value="daylong"><?php esc_html_e( 'Day Long', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
							</select>
						</div>
						<div class="rbfw-me-fp-field rbfw-me-fp-field--inline">
							<label class="rbfw-me-fp-label"><?php esc_html_e( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<div class="rbfw-me-fp-stepper">
								<div class="rbfw-me-fp-stepper-pill rbfw-me-fp-resort-stepper">
									<button type="button" class="rbfw-me-fp-step-minus" data-step="-1">&minus;</button>
									<span class="rbfw-me-fp-resort-qty-val">1</span>
									<button type="button" class="rbfw-me-fp-step-plus" data-step="1">+</button>
								</div>
							</div>
						</div>
					</div>

					<div class="rbfw-me-fp-warn-slot"></div>

					<!-- Multiple Items: one qty stepper per linked item row, revealed once
					     duration + pickup date are set (see recalcMi()). -->
					<div class="rbfw-me-fp-mi-items" style="display:none">
						<div class="rbfw-me-fp-section-divider"></div>
						<p class="rbfw-me-fp-section-label"><?php esc_html_e( 'Rental Item', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-mi-item-rows"></div>
					</div>

					<!-- Variations (Size/Color/etc.): single-select pills per field, price surcharge -->
					<div class="rbfw-me-fp-variations" style="display:none">
						<p class="rbfw-me-fp-section-label"><?php esc_html_e( 'Options', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-variations-groups"></div>
					</div>

					<!-- Extra Services (basic table for SD/Appointment/Multiple Items, category-wise for MD family) -->
					<div class="rbfw-me-fp-extras" style="display:none">
						<div class="rbfw-me-fp-section-divider"></div>
						<p class="rbfw-me-fp-section-label"><?php esc_html_e( 'Extra Services', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-extras-rows"></div>
					</div>

					<div class="rbfw-me-fp-summary">
						<p class="rbfw-me-fp-summary-title"><?php esc_html_e( 'Booking Summary', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<div class="rbfw-me-fp-summary-rows"></div>
						<div class="rbfw-me-fp-summary-total">
							<span><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<strong class="rbfw-me-fp-total-amt">$0.00</strong>
						</div>
					</div>

					<div class="rbfw-me-fp-book-btn-wrap">
						<button type="button" class="rbfw-me-fp-book-btn" tabindex="-1"><?php esc_html_e( 'Book Now', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						<span class="rbfw-me-fp-book-btn-tip"><?php esc_html_e( 'Preview only — this button doesn\'t place a real booking.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<p class="rbfw-me-fp-note"><?php esc_html_e( 'Based on the rate fields on this screen, including unsaved changes. Extra services, variations, fees, day-wise and seasonal pricing add-ons are not reflected here.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
			</div>

			<div class="rbfw-me-card rbfw-me-card--sidebar" data-rbfw-tour="featured-image">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Featured Image', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body rbfw-me-thumb-wrap">
					<input type="hidden" name="_thumbnail_id" class="rbfw-me-thumb-id" value="<?php echo esc_attr( $thumb_id ?: '' ); ?>" />
					<div class="rbfw-me-thumb-preview <?php echo $thumb_url ? 'has-image' : ''; ?>">
						<?php if ( $thumb_url ) : ?>
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<div class="rbfw-me-thumb-actions">
						<button type="button" class="rbfw-me-btn rbfw-me-thumb-set">
							<?php echo $thumb_id
								? esc_html__( 'Change Image', 'booking-and-rental-manager-for-woocommerce' )
								: esc_html__( 'Set Featured Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
						<?php if ( $thumb_id ) : ?>
							<button type="button" class="rbfw-me-btn rbfw-me-thumb-remove" title="<?php esc_attr_e( 'Remove Image', 'booking-and-rental-manager-for-woocommerce' ); ?>"><i class="fas fa-trash"></i></button>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Gallery ─────────────────────────────────────────── -->
			<div class="rbfw-me-card rbfw-me-card--sidebar" data-rbfw-tour="gallery">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Gallery', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body rbfw-me-gallery-wrap">
					<div class="rbfw-me-gallery-list">
						<?php
						$gallery_images = get_post_meta( $post_id, 'rbfw_gallery_images', true );
						$gallery_images = is_array( $gallery_images ) ? array_filter( $gallery_images ) : [];
						foreach ( $gallery_images as $image_id ) :
							$img_url = wp_get_attachment_url( $image_id );
							if ( ! $img_url ) continue;
						?>
							<div class="rbfw-me-gallery-image">
								<button type="button" class="rbfw-me-gallery-remove" onclick="jQuery(this).closest('.rbfw-me-gallery-image').remove()">
									<i class="fas fa-trash-can"></i>
								</button>
								<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
								<input type="hidden" name="rbfw_gallery_images[]" value="<?php echo esc_attr( $image_id ); ?>" />
							</div>
						<?php endforeach; ?>
					</div>
					<div class="rbfw-me-gallery-actions">
						<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-gallery-upload">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Upload Images', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
						<button type="button" class="rbfw-me-gallery-clear">
							<?php esc_html_e( 'Clear All', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div class="rbfw-me-card rbfw-me-card--sidebar" data-rbfw-tour="status">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body">
					<div class="rbfw-me-status-row">
						<span class="rbfw-me-status-dot rbfw-me-status-dot--<?php echo esc_attr( $editor_status ); ?>"></span>
						<span class="rbfw-me-status-label">
							<?php echo esc_html( ucfirst( $editor_status ) ); ?>
						</span>
					</div>
					<select class="rbfw-me-select" name="post_status">
						<option value="draft"   <?php selected( $editor_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
						<option value="publish" <?php selected( $editor_status, 'publish' ); ?>><?php esc_html_e( 'Published', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
						<option value="private" <?php selected( $editor_status, 'private' ); ?>><?php esc_html_e( 'Private', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
					</select>
					<?php if ( $permalink ) : ?>
						<a class="rbfw-me-permalink" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener">
							<span class="dashicons dashicons-admin-links"></span>
							<?php esc_html_e( 'View item', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="rbfw-me-card rbfw-me-card--sidebar rbfw-me-help-card">
				<div class="rbfw-me-help-card__header">
					<span class="dashicons dashicons-book-alt rbfw-me-help-card__icon"></span>
					<h3><?php esc_html_e( 'Resources & Addons', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body">

					<div class="rbfw-me-help-divider">
						<span><?php esc_html_e( 'Getting Started', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<button type="button" class="rbfw-me-help-link rbfw-me-help-link--button" id="rbfw-me-tour-restart">
						<span class="rbfw-me-help-link__icon dashicons dashicons-lightbulb"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Take a Tour', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'A quick walkthrough of this editor', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</button>

					<div class="rbfw-me-help-divider">
						<span><?php esc_html_e( 'Upgrade & Addons', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<?php
						/**
						 * Pro Version row. Rendered by RBFW_Pro_Features_Notice so the
						 * feature list has ONE home: it prints the same "Buy Pro Version"
						 * row that used to be hard-coded here, plus the Pro feature list
						 * it expands into, and prints nothing at all once Pro is active.
						 */
						do_action( 'rbfw_modern_editor_pro_links', $post_id );
					?>

					<!-- Min and Max Booking Limit -->
					<a href="https://mage-people.com/product/min-and-max-booking-day-for-booking-and-rental-plugin/" target="_blank" rel="noopener" class="rbfw-me-help-link">
						<span class="rbfw-me-help-link__icon dashicons dashicons-controls-repeat"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Min & Max Booking Limit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Control booking duration limits', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<!-- Seasonal Pricing -->
					<a href="https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-addon-seasonal-pricing/" target="_blank" rel="noopener" class="rbfw-me-help-link">
						<span class="rbfw-me-help-link__icon dashicons dashicons-calendar-alt"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Seasonal Pricing Management', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Set prices by season or date range', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<!-- Multi-Day Discount -->
					<a href="https://mage-people.com/product/multi-day-price-saver-addon-for-wprently/" target="_blank" rel="noopener" class="rbfw-me-help-link">
						<span class="rbfw-me-help-link__icon dashicons dashicons-tag"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Multi-Day Discount Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Reward longer bookings with discounts', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<!-- Backend Order -->
					<a href="https://mage-people.com/product/backend-order-addon-wprently/" target="_blank" rel="noopener" class="rbfw-me-help-link">
						<span class="rbfw-me-help-link__icon dashicons dashicons-clipboard"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Backend Order', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Create orders directly from admin', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<!-- Pricing Discount Over x Days -->
					<a href="https://mage-people.com/product/pricing-discount-over-x-day-addon-for-rental-and-booking-plugin/" target="_blank" rel="noopener" class="rbfw-me-help-link">
						<span class="rbfw-me-help-link__icon dashicons dashicons-chart-line"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Pricing Discount Over x Days', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Apply tiered discounts by duration', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<div class="rbfw-me-help-divider">
						<span><?php esc_html_e( 'Compatible Integrations', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<!-- SecureHold WP -->
					<a href="https://secureholdwp.com/docs/" target="_blank" rel="noopener noreferrer" class="rbfw-me-help-link">
						<img class="rbfw-me-help-link__logo" src="<?php echo esc_url( RBFW_PLUGIN_URL . '/assets/images/securehold-icon.png' ); ?>" alt="">
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'SecureHold WP', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Stripe authorization holds for fixed security deposits (3.4.11+)', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

				</div>
			</div>
		</aside>
	</div><!-- /.rbfw-me-body -->

</div><!-- /.rbfw-me-wrap -->
