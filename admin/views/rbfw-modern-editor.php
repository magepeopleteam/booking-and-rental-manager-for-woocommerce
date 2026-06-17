<?php if ( ! defined( 'ABSPATH' ) ) die; ?>

<div class="rbfw-me-wrap" data-post-id="<?php echo esc_attr( $post_id ); ?>">

	<!-- ── Header ───────────────────────────────────────────────────────── -->
	<div class="rbfw-me-header">
		<div class="rbfw-me-header__left">
			<a class="rbfw-me-back" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rbfw_item' ) ); ?>">
				<span class="dashicons dashicons-arrow-left-alt"></span>
			</a>
			<input
				class="rbfw-me-title-input"
				type="text"
				name="post_title"
				value="<?php echo esc_attr( $screen_title ); ?>"
				placeholder="<?php esc_attr_e( 'Rental item name…', 'booking-and-rental-manager-for-woocommerce' ); ?>"
				autocomplete="off"
			/>
		</div>
		<div class="rbfw-me-header__right">
			<span class="rbfw-me-save-indicator" aria-live="polite"></span>
			<?php if ( $permalink ) : ?>
				<a class="rbfw-me-btn rbfw-me-btn--ghost" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Preview', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			<?php endif; ?>
			<button type="button" class="rbfw-me-btn rbfw-me-btn--secondary rbfw-me-save-draft">
				<?php esc_html_e( 'Save Draft', 'booking-and-rental-manager-for-woocommerce' ); ?>
			</button>
			<button type="button" class="rbfw-me-btn rbfw-me-btn--primary rbfw-me-publish" data-published="<?php echo esc_attr( $is_published ? '1' : '0' ); ?>">
				<?php echo esc_html( $is_published
					? __( 'Update', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'Publish', 'booking-and-rental-manager-for-woocommerce' )
				); ?>
			</button>
			<?php if ( $classic_url ) : ?>
				<a class="rbfw-me-classic-switch" href="<?php echo esc_url( $classic_url ); ?>" title="<?php esc_attr_e( 'Switch to Classic Editor', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<span class="dashicons dashicons-editor-code"></span>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- ── Tabs ─────────────────────────────────────────────────────────── -->
	<div class="rbfw-me-tabs" role="tablist">
		<?php foreach ( $tabs as $i => $tab ) : ?>
			<button
				class="rbfw-me-tab <?php echo $i === 0 ? 'is-active' : ''; ?>"
				role="tab"
				data-tab="<?php echo esc_attr( $tab['key'] ); ?>"
				aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
			>
				<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
				<?php echo esc_html( $tab['label'] ); ?>
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
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Additional Extra Services', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Configure optional add-on services customers can select during booking.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
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

			<!-- Services ────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="services">
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Service Options', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Configure add-on service quantity options for this item.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Item Quantity Selection', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
								<p><?php esc_html_e( 'Let customers choose how many of this item to rent.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_md_type_item_qty" value="yes" <?php checked( ( $m['rbfw_enable_md_type_item_qty'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Extra Service Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
								<p><?php esc_html_e( 'Allow customers to specify quantities for optional add-ons.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_extra_service_qty" value="yes" <?php checked( ( $m['rbfw_enable_extra_service_qty'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-field">
							<label class="rbfw-me-field__label"><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input class="rbfw-me-input" type="number" min="0" name="rbfw_item_quantity" value="<?php echo esc_attr( $m['rbfw_item_quantity'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g. 10', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
							<p class="rbfw-me-field__help"><?php esc_html_e( 'Maximum number of this item available for simultaneous rental.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Location ────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="location">
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Pickup & Drop-off Points', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Enable location selection for customers on the booking form.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable Location Selection', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
								<p><?php esc_html_e( 'Show pickup and drop-off location dropdowns on the booking form.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_pick_point" value="yes" <?php checked( ( $m['rbfw_enable_pick_point'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<p class="rbfw-me-notice">
							<span class="dashicons dashicons-info-outline"></span>
							<?php esc_html_e( 'Location points (pickup/drop-off addresses) are managed in the Classic Editor under the Location tab.', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<?php if ( $post_id ) : ?>
								<a href="<?php echo esc_url( $this->classic_url( $post_id ) ); ?>"><?php esc_html_e( 'Open Classic Editor', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Template ────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="template">
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Display Template', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Choose how this rental item page looks to customers.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<div class="rbfw-me-card__body">
						<?php
						$screenshot_url = RBFW_PLUGIN_URL . '/templates/screenshot/';
						$templates      = RBFW_Function::get_all_template();
						$current_tpl    = $m['rbfw_single_template'] ?? 'Default';
						?>
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
			</div>

			<!-- Advanced ─────────────────────────────────────────────────── -->
			<div class="rbfw-me-panel" data-panel="advanced">
				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Booking Date Options', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable Start / End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_start_end_date" value="yes" <?php checked( ( $m['rbfw_enable_start_end_date'] ?? 'yes' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable Time Picker', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_time_picker" value="yes" <?php checked( ( $m['rbfw_enable_time_picker'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-row">
							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label"><?php esc_html_e( 'Min Booking Days', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input class="rbfw-me-input" type="number" min="0" name="rbfw_minimum_booking_day" value="<?php echo esc_attr( $m['rbfw_minimum_booking_day'] ?? '' ); ?>" placeholder="0" />
							</div>
							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label"><?php esc_html_e( 'Max Booking Days', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input class="rbfw-me-input" type="number" min="0" name="rbfw_maximum_booking_day" value="<?php echo esc_attr( $m['rbfw_maximum_booking_day'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
							</div>
						</div>
					</div>
				</div>

				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_security_deposit" value="yes" <?php checked( ( $m['rbfw_enable_security_deposit'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input rbfw-me-toggle--reveal" data-reveals=".rbfw-me-deposit-fields" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="rbfw-me-deposit-fields <?php echo ( $m['rbfw_enable_security_deposit'] ?? '' ) === 'yes' ? '' : 'rbfw-me-hidden'; ?>">
							<div class="rbfw-me-field">
								<label class="rbfw-me-field__label"><?php esc_html_e( 'Label', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input class="rbfw-me-input" type="text" name="rbfw_security_deposit_label" value="<?php echo esc_attr( $m['rbfw_security_deposit_label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
							</div>
							<div class="rbfw-me-row">
								<div class="rbfw-me-field">
									<label class="rbfw-me-field__label"><?php esc_html_e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<select class="rbfw-me-select" name="rbfw_security_deposit_type">
										<option value="fixed"      <?php selected( ( $m['rbfw_security_deposit_type'] ?? 'fixed' ), 'fixed' ); ?>><?php esc_html_e( 'Fixed Amount', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
										<option value="percentage" <?php selected( ( $m['rbfw_security_deposit_type'] ?? '' ), 'percentage' ); ?>><?php esc_html_e( 'Percentage', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									</select>
								</div>
								<div class="rbfw-me-field">
									<label class="rbfw-me-field__label"><?php esc_html_e( 'Amount / %', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<input class="rbfw-me-input" type="number" min="0" step="0.01" name="rbfw_security_deposit_amount" value="<?php echo esc_attr( $m['rbfw_security_deposit_amount'] ?? '' ); ?>" placeholder="0" />
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="rbfw-me-card">
					<div class="rbfw-me-card__head">
						<h2><?php esc_html_e( 'Content', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					</div>
					<div class="rbfw-me-card__body">
						<div class="rbfw-me-field rbfw-me-field--toggle-row">
							<div class="rbfw-me-field__info">
								<strong><?php esc_html_e( 'Enable FAQ Section', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							</div>
							<label class="rbfw-me-toggle">
								<input type="checkbox" name="rbfw_enable_faq_content" value="yes" <?php checked( ( $m['rbfw_enable_faq_content'] ?? '' ), 'yes' ); ?> class="rbfw-me-toggle__input" />
								<span class="rbfw-me-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
					</div>
				</div>
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

			<div class="rbfw-me-card rbfw-me-card--sidebar">
				<div class="rbfw-me-card__head">
					<h3><?php esc_html_e( 'Quick Help', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
				</div>
				<div class="rbfw-me-card__body">
					<p class="rbfw-me-help-text"><?php esc_html_e( 'Advanced options (off-days, gallery, fee management, extra services, terms) are available in the Classic Editor.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					<?php if ( $classic_url ) : ?>
						<a href="<?php echo esc_url( $classic_url ); ?>" class="rbfw-me-btn rbfw-me-btn--ghost rbfw-me-btn--full">
							<span class="dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'Open Classic Editor', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</aside>
	</div><!-- /.rbfw-me-body -->

</div><!-- /.rbfw-me-wrap -->
