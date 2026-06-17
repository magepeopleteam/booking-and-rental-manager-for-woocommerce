<?php if ( ! defined( 'ABSPATH' ) ) die; ?>

<div class="rbfw-me-wrap" data-post-id="<?php echo esc_attr( $post_id ); ?>">

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
			<span class="rbfw-me-save-indicator" aria-live="polite"></span>
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

	<!-- ── Body ──────────────────────────────────────────────────────────── -->
	<div class="rbfw-me-body">
		<div class="rbfw-me-main">

			<!-- General ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel is-active" data-panel="general">
				<div class="rbfw-me-card">
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
						<div class="rbfw-me-field">
							<label class="rbfw-me-field__label"><?php esc_html_e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
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
									]
								);
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Category Settings ───────────────────────────────── -->
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Category Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p>
							<?php esc_html_e( 'Here you can manage rent type.', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=rbfw_item_caregory&post_type=rbfw_item' ) ); ?>" target="_blank">
								<?php esc_html_e( 'Add new rent type', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</a>
						</p>
					</div>
					<div class="rbfw-me-card__body">
						<?php
						$saved_cats_str = implode( ',', $saved_cat_names );
						?>
						<input type="hidden" name="rbfw_categories[]" class="rbfw-me-cats-hidden" value="<?php echo esc_attr( $saved_cats_str ); ?>">
						<div class="rbfw-me-checkbox-grid">
							<?php foreach ( $all_cat_terms as $term ) :
								$checked = in_array( strtolower( trim( $term->name ) ), $saved_cat_names, true );
							?>
								<label class="rbfw-me-checkbox-label">
									<input
										type="checkbox"
										class="rbfw-me-cat-checkbox"
										data-name="<?php echo esc_attr( $term->name ); ?>"
										<?php checked( $checked ); ?>
									/>
									<span><?php echo esc_html( ucfirst( $term->name ) ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php if ( empty( $all_cat_terms ) ) : ?>
							<p class="rbfw-me-field__help">
								<?php esc_html_e( 'No categories found.', 'booking-and-rental-manager-for-woocommerce' ); ?>
								<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=rbfw_item_caregory&post_type=rbfw_item' ) ); ?>" target="_blank"><?php esc_html_e( 'Create one', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Item Features ─────────────────────────────────── -->
				<div class="rbfw-me-card">
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
									<td class="rbfw-me-features-actions">
										<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
										<span class="button tr_remove" onclick="jQuery(this).closest('tr').remove()"><i class="fas fa-trash-can"></i></span>
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
									<td class="rbfw-me-features-actions">
										<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
										<span class="button tr_remove" onclick="jQuery(this).closest('tr').remove()"><i class="fas fa-trash-can"></i></span>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<button type="button" class="ppof-button add-feature-category mt-1">
							<i class="fas fa-circle-plus"></i> <?php esc_html_e( 'Add New Feature Category', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Pricing ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="pricing">
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Pricing::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<div class="rbfw-me-card">
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Extra_Service::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Fee Management', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Configure multiple fees with different calculation types and frequencies.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body rbfw-me-pricing-classic-wrap">
						<?php RBFW_Fee_Management::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>
			</div>

			<!-- Off Days ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="offday">
				<?php RBFW_Off_Day::render_for_modern_editor( $post_id ); ?>
			</div>

			<!-- Advanced ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="advanced">

				<!-- Template picker -->
				<?php
				$screenshot_url = RBFW_PLUGIN_URL . '/templates/screenshot/';
				$templates      = RBFW_Function::get_all_template();
				$current_tpl    = $m['rbfw_single_template'] ?? 'Default';
				?>
				<div class="rbfw-me-card">
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
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__body">
						<?php RBFW_Faq_Settings::render_for_modern_editor( $post_id ); ?>
					</div>
				</div>

				<!-- Tax Settings ─────────────────────────────────────────── -->
				<?php $tax_enabled = ( $m['rbfw_enable_tax_settings'] ?? '' ) === 'yes'; ?>
				<div class="rbfw-me-card">
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
				<div class="rbfw-me-card">
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
				<div class="rbfw-me-card">
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
				<div class="rbfw-me-card">
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

			<div class="rbfw-me-card rbfw-me-card--sidebar">
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
						<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-thumb-set">
							<?php echo $thumb_id
								? esc_html__( 'Change Image', 'booking-and-rental-manager-for-woocommerce' )
								: esc_html__( 'Set Featured Image', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</button>
						<?php if ( $thumb_id ) : ?>
							<button type="button" class="rbfw-me-btn rbfw-me-btn--danger rbfw-me-thumb-remove"><?php esc_html_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Gallery ─────────────────────────────────────────── -->
			<div class="rbfw-me-card rbfw-me-card--sidebar">
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

			<div class="rbfw-me-card rbfw-me-card--sidebar">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body">
					<div class="rbfw-me-status-row">
						<span class="rbfw-me-status-dot rbfw-me-status-dot--<?php echo esc_attr( $post ? $post->post_status : 'draft' ); ?>"></span>
						<span class="rbfw-me-status-label">
							<?php echo esc_html( ucfirst( $post ? $post->post_status : 'draft' ) ); ?>
						</span>
					</div>
					<select class="rbfw-me-select" name="post_status">
						<option value="draft"   <?php selected( $post ? $post->post_status : 'draft', 'draft' ); ?>><?php esc_html_e( 'Draft', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
						<option value="publish" <?php selected( $post ? $post->post_status : '', 'publish' ); ?>><?php esc_html_e( 'Published', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
						<option value="private" <?php selected( $post ? $post->post_status : '', 'private' ); ?>><?php esc_html_e( 'Private', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
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

					<!-- Documentation -->
					<a href="https://booking-and-rental-manager.com/documentation/" target="_blank" rel="noopener" class="rbfw-me-help-link rbfw-me-help-link--docs">
						<span class="rbfw-me-help-link__icon dashicons dashicons-media-document"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Guides, tutorials & how-tos', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

					<div class="rbfw-me-help-divider">
						<span><?php esc_html_e( 'Upgrade & Addons', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<!-- Pro Version -->
					<a href="https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/" target="_blank" rel="noopener" class="rbfw-me-help-link rbfw-me-help-link--pro">
						<span class="rbfw-me-help-link__icon dashicons dashicons-star-filled"></span>
						<div class="rbfw-me-help-link__text">
							<strong><?php esc_html_e( 'Buy Pro Version', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Unlock all premium features', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
					</a>

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

				</div>
			</div>
		</aside>
	</div><!-- /.rbfw-me-body -->

</div><!-- /.rbfw-me-wrap -->
