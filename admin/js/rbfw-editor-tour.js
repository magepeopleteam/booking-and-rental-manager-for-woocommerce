/**
 * Section-by-section guided tour for the Modern Editor (Add/Edit Rental Item
 * screen). Auto-starts once per admin user (persisted via the
 * rbfw_editor_tour_seen user meta, set through the rbfw_editor_tour_seen AJAX
 * action — see RBFW_Modern_Editor::ajax_tour_seen()), and can be replayed any
 * time from the "Take a Tour" button in the sidebar's Resources card.
 *
 * Self-contained: no external tour library, just a highlighted "spotlight"
 * box positioned over the real DOM element for each step plus a small
 * tooltip with Back/Next/Skip, matching the rest of this editor's own
 * hand-built jQuery UI rather than pulling in a new dependency for it.
 */
(function ($) {
	'use strict';

	if (typeof rbfwEditorTour === 'undefined') {
		return;
	}

	$(function () {
		var $wrap = $('.rbfw-me-wrap');
		if (!$wrap.length) {
			return;
		}

		/*
		 * Each step targets a real, already-rendered piece of the editor.
		 * `tab`, when set, is the data-tab value to switch to first — some
		 * targets live inside a tab panel that's hidden until its tab is
		 * active. Steps whose target isn't present/visible when reached
		 * (e.g. the Payment Method card, which this editor hides entirely
		 * once a gateway is configured) are skipped automatically rather
		 * than shown against nothing.
		 */
		// i18n shorthand — every step's title/text is looked up here by key so
		// the list below stays readable; see RBFW_Modern_Editor::enqueue_assets()
		// for the actual translatable strings.
		var t = rbfwEditorTour.i18n;

		/*
		 * One step per distinct section rather than one per tab: each targets
		 * either a stable existing class (.rbfw-me-rent-type-card,
		 * .rbfw-me-inventory-card, ...) or a data-rbfw-tour="..." attribute added
		 * specifically for this tour where no unique class already existed
		 * (see the matching data-rbfw-tour values in
		 * admin/views/rbfw-modern-editor.php). `tab`, when set, is the data-tab
		 * to switch to first. A step whose target isn't visible when reached
		 * (an optional/toggled-off section, a Pro-only add-on card that isn't
		 * installed, the Payment Method card once a gateway is configured, ...)
		 * is skipped automatically rather than shown pointing at nothing.
		 */
		var STEPS = [
			// ── Orientation ──────────────────────────────────────────
			// Points at the General tab button itself (not just the tab panel
			// content) so the very first thing the admin sees is literally
			// "start here", the same action-first opening Eventin's own tour
			// uses on its Create Event button.
			{ selector: '.rbfw-me-tab[data-tab="general"]', tab: 'general', title: t.step_title_general_tab, text: t.step_text_general_tab },

			// ── General tab ──────────────────────────────────────────
			// The Name input itself, not the whole Basic Information card —
			// keeps the spotlight tight on the one field this step is about.
			{ selector: '#rbfw_me_post_title', tab: 'general', title: t.step_title_name, text: t.step_text_name },
			// Rent Item Type: the taxonomy checkbox grid (RBFW_Category_Manager),
			// not the whole Category Settings card — these work like WooCommerce
			// product categories, driving front-end filtering/browsing by type.
			{ selector: '.rbfw-me-checkbox-grid', tab: 'general', title: t.step_title_rent_item_type, text: t.step_text_rent_item_type },

			// ── Pricing tab ──────────────────────────────────────────
			// Points at the Pricing tab button itself, mirroring the General
			// tab orientation step above, before diving into its fields.
			{ selector: '.rbfw-me-tab[data-tab="pricing"]', tab: 'pricing', title: t.step_title_pricing_tab, text: t.step_text_pricing_tab },
			// Rent type: shown separately from the rest of Pricing because it's
			// the one choice that changes what every other field on this tab
			// even means — bikes/boats price by time slot, resorts by night,
			// appointments by service duration, and so on.
			{
				selector: '.rbfw-tent-types',
				tab: 'pricing',
				title: t.step_title_rent_type,
				text: function () { return currentRentTypeCopy(t.step_text_rent_type_current, t.step_text_rent_type_fallback); }
			},

			/*
			 * Each rent type's own pricing fields, broken into their real,
			 * uniquely-targetable sub-cards (verified directly against a live
			 * item of every type) rather than one combined step per type —
			 * only the card(s) matching the item's actual rbfw_item_type are
			 * ever visible; the rest render but stay hidden and are skipped
			 * automatically.
			 */
			// Single Day + Appointment share the exact same wrapper markup
			// (.rbfw_bike_car_sd_wrapper) in admin/settings/Pricing.php — split
			// into its own 2 real sub-parts rather than one combined step:
			// the item-wise pricing table (RBFW_Pricing::bike_car_single_day())
			// and the time-picker/slots toggle it shares with Multiple Day
			// (the tail end of RBFW_Pricing::md_price_config()).
			{ selector: '[data-rbfw-tour="sd-price-table"]', tab: 'pricing', title: t.step_title_sd_pricing, text: t.step_text_sd_pricing },
			{ selector: '.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper', tab: 'pricing', title: t.step_title_sd_time_picker, text: t.step_text_sd_time_picker },
			// Appointment adds two of its own cards on top of that shared
			// session/time-slot pricing (RBFW_Pricing::appointment()).
			{ selector: '.rbfw_switch_sd_appointment_row', tab: 'pricing', title: t.step_title_appt_max_qty, text: t.step_text_appt_max_qty },
			{ selector: '.appointment-onday', tab: 'pricing', title: t.step_title_appt_ondays, text: t.step_text_appt_ondays },
			// Multiple Day (RBFW_Pricing::md_price_config()) has 3 real cards.
			{ selector: '[data-rbfw-tour="md-duration-rates"]', tab: 'pricing', title: t.step_title_md_duration, text: t.step_text_md_duration },
			{ selector: '[data-rbfw-tour="md-time-config"]', tab: 'pricing', title: t.step_title_md_time_config, text: t.step_text_md_time_config },
			{ selector: '#rbfw-daywise-config-wrapper', tab: 'pricing', title: t.step_title_md_daywise, text: t.step_text_md_daywise },
			// Resort (RBFW_Pricing::resort_price_config()) has 2 real sections.
			{ selector: '[data-rbfw-tour="resort-daylong"]', tab: 'pricing', title: t.step_title_resort_daylong, text: t.step_text_resort_daylong },
			{ selector: '[data-rbfw-tour="resort-rooms"]', tab: 'pricing', title: t.step_title_resort_rooms, text: t.step_text_resort_rooms },
			// Multiple Items (RBFW_Pricing::multiple_items()) has 3 real cards.
			{ selector: '.rbfw-mi-price-types-card', tab: 'pricing', title: t.step_title_mi_types, text: t.step_text_mi_types },
			{ selector: '.rbfw-mi-items-card', tab: 'pricing', title: t.step_title_mi_items, text: t.step_text_mi_items },
			{ selector: '#rbfw-pricing-thresholds-card', tab: 'pricing', title: t.step_title_mi_thresholds, text: t.step_text_mi_thresholds },

			{ selector: '[data-rbfw-tour="extra-service"]', tab: 'pricing', title: t.step_title_extra_service, text: t.step_text_extra_service },
			{ selector: '.rbfw-me-inventory-card', tab: 'pricing', title: t.step_title_inventory, text: t.step_text_inventory },
			{ selector: '.rbfw-me-buffer-card', tab: 'pricing', title: t.step_title_buffer, text: t.step_text_buffer },
			{ selector: '[data-rbfw-tour="fee-management"]', tab: 'pricing', title: t.step_title_fees, text: t.step_text_fees },

			// ── Off Days tab ─────────────────────────────────────────
			{ selector: '[data-rbfw-tour="offday"]', tab: 'offday', title: t.step_title_offday, text: t.step_text_offday },

			// ── Advanced tab ─────────────────────────────────────────
			{ selector: '.rbfw-me-location-card', tab: 'advanced', title: t.step_title_location, text: t.step_text_location },
			{ selector: '[data-rbfw-tour="template"]', tab: 'advanced', title: t.step_title_template, text: t.step_text_template },
			{ selector: '.rbfw-me-additional-gallery-card', tab: 'advanced', title: t.step_title_additional_gallery, text: t.step_text_additional_gallery },
			{ selector: '[data-rbfw-tour="tax-settings"]', tab: 'advanced', title: t.step_title_tax, text: t.step_text_tax },
			{ selector: '[data-rbfw-tour="security-deposit"]', tab: 'advanced', title: t.step_title_deposit, text: t.step_text_deposit },

			// ── Sidebar (visible regardless of active tab) ──────────
			{ selector: '.rbfw-me-payment-card', title: t.step_title_payment, text: t.step_text_payment },

			// ── Header ───────────────────────────────────────────────
			{ selector: '.rbfw-me-publish-group', title: t.step_title_publish, text: t.step_text_publish }
		];

		var current = -1;
		var lastDir = 1;
		var $overlay, $spot, $tooltip;

		function buildUI() {
			$overlay = $('<div class="rbfw-tour-overlay"></div>').appendTo('body');
			$spot = $('<div class="rbfw-tour-spot"></div>').appendTo('body');
			$tooltip = $(
				'<div class="rbfw-tour-tip" role="dialog" aria-live="polite">' +
					'<div class="rbfw-tour-tip__arrow"></div>' +
					'<div class="rbfw-tour-tip__step"></div>' +
					'<h4 class="rbfw-tour-tip__title"></h4>' +
					'<p class="rbfw-tour-tip__text"></p>' +
					'<div class="rbfw-tour-tip__actions">' +
						'<button type="button" class="rbfw-tour-btn rbfw-tour-btn--ghost rbfw-tour-skip">' + rbfwEditorTour.i18n.skip + '</button>' +
						'<div class="rbfw-tour-tip__nav">' +
							'<button type="button" class="rbfw-tour-btn rbfw-tour-btn--ghost rbfw-tour-back">' + rbfwEditorTour.i18n.back + '</button>' +
							'<button type="button" class="rbfw-tour-btn rbfw-tour-btn--primary rbfw-tour-next"></button>' +
						'</div>' +
					'</div>' +
				'</div>'
			).appendTo('body');

			$tooltip.on('click', '.rbfw-tour-next', function () { advance(1); });
			$tooltip.on('click', '.rbfw-tour-back', function () { advance(-1); });
			$tooltip.on('click', '.rbfw-tour-skip', endTour);
		}

		function activateTabFor(step) {
			if (!step.tab) {
				return;
			}
			var $tab = $wrap.find('.rbfw-me-tab[data-tab="' + step.tab + '"]');
			if ($tab.length && !$tab.hasClass('is-active')) {
				$tab.trigger('click');
			}
		}

		function findTarget(step) {
			var $t = $(step.selector).filter(':visible').first();
			return $t.length ? $t : null;
		}

		// A step's title/text can be a plain string or a function returning one
		// (used where the copy depends on live page state, e.g. which rent type
		// is currently selected).
		function resolveCopy(value) {
			return (typeof value === 'function') ? value() : value;
		}

		// The rent type picker (top of the Pricing card, .rbfw-tent-types) already
		// carries each type's own real name + description as data-rent-type-desc
		// on its .rbfw-rent-type cards (see RBFW_Pricing::rent_type() in
		// admin/settings/Pricing.php) — reused here instead of duplicating that
		// copy, so the tour always matches whichever type is actually selected.
		function currentRentTypeName() {
			var $sel = $('.rbfw-rent-type.selected').first();
			return $sel.length ? $.trim($sel.clone().find('.icon').remove().end().text()) : '';
		}

		function currentRentTypeCopy(prefixTemplate, fallback) {
			var name = currentRentTypeName();
			var $sel = $('.rbfw-rent-type.selected').first();
			if (!name || !$sel.length) {
				return fallback;
			}
			var desc = $.trim(String($sel.attr('data-rent-type-desc') || '')).replace(/<\/?b>/g, '');
			var text = prefixTemplate.replace('%s', name);
			return desc ? (text + ' ' + desc) : text;
		}

		function position($target) {
			var rect = $target[0].getBoundingClientRect();
			var pad = 8;
			$spot.css({
				top: (rect.top - pad) + 'px',
				left: (rect.left - pad) + 'px',
				width: (rect.width + pad * 2) + 'px',
				height: (rect.height + pad * 2) + 'px'
			});

			var tipWidth = $tooltip.outerWidth();
			var tipHeight = $tooltip.outerHeight();
			var spaceBelow = window.innerHeight - rect.bottom;
			var top, tipIsBelowTarget;

			if (spaceBelow > tipHeight + 24 || spaceBelow > rect.top) {
				top = rect.bottom + 16;
				tipIsBelowTarget = true;
			} else {
				top = rect.top - tipHeight - 16;
				tipIsBelowTarget = false;
			}
			top = Math.max(12, Math.min(top, window.innerHeight - tipHeight - 12));

			var left = rect.left + (rect.width / 2) - (tipWidth / 2);
			left = Math.max(12, Math.min(left, window.innerWidth - tipWidth - 12));

			$tooltip.css({ top: top + 'px', left: left + 'px' });

			// Arrow: attaches to whichever edge faces the target (top edge,
			// pointing up, when the tip sits below it; bottom edge, pointing
			// down, when the tip sits above it) and slides along that edge to
			// stay under the target's actual center even when the tip itself
			// has been shifted sideways to stay on-screen.
			var targetCenter = rect.left + (rect.width / 2);
			var arrowLeft = Math.max(16, Math.min(targetCenter - left, tipWidth - 16));
			$tooltip
				.toggleClass('rbfw-tour-tip--arrow-top', tipIsBelowTarget)
				.toggleClass('rbfw-tour-tip--arrow-bottom', !tipIsBelowTarget)
				.find('.rbfw-tour-tip__arrow').css('left', arrowLeft + 'px');
		}

		function renderStep(index) {
			var step = STEPS[index];
			activateTabFor(step);

			setTimeout(function () {
				var $target = findTarget(step);
				if (!$target) {
					// Nothing visible to highlight for this step (e.g. the Payment
					// Method card, hidden once a gateway is already configured) —
					// keep moving in whichever direction we were already going.
					advance(lastDir);
					return;
				}

				$target[0].scrollIntoView({ block: 'center', behavior: 'smooth' });

				setTimeout(function () {
					position($target);
					$tooltip.find('.rbfw-tour-tip__step').text(
						rbfwEditorTour.i18n.step_of
							.replace('%1$d', index + 1)
							.replace('%2$d', STEPS.length)
					);
					$tooltip.find('.rbfw-tour-tip__title').text(resolveCopy(step.title));
					$tooltip.find('.rbfw-tour-tip__text').text(resolveCopy(step.text));
					$tooltip.find('.rbfw-tour-back').prop('disabled', index === 0);
					$tooltip.find('.rbfw-tour-next').text(
						index === STEPS.length - 1 ? rbfwEditorTour.i18n.finish : rbfwEditorTour.i18n.next
					);
				}, 350);
			}, step.tab ? 200 : 0);
		}

		function advance(dir) {
			lastDir = dir;
			var next = current + dir;
			if (next < 0) {
				return;
			}
			if (next >= STEPS.length) {
				endTour();
				return;
			}
			current = next;
			renderStep(current);
		}

		function startTour() {
			current = 0;
			lastDir = 1;
			$('body').addClass('rbfw-tour-active');
			buildUI();
			renderStep(0);
			$(document).on('keydown.rbfwTour', function (e) {
				if (e.key === 'Escape') {
					endTour();
				}
			});
			$(window).on('resize.rbfwTour scroll.rbfwTour', function () {
				if (current > -1 && $tooltip && $tooltip.length) {
					var $target = findTarget(STEPS[current]);
					if ($target) {
						position($target);
					}
				}
			});
		}

		function endTour() {
			current = -1;
			$('body').removeClass('rbfw-tour-active');
			if ($overlay) { $overlay.remove(); }
			if ($spot) { $spot.remove(); }
			if ($tooltip) { $tooltip.remove(); }
			$(document).off('keydown.rbfwTour');
			$(window).off('resize.rbfwTour scroll.rbfwTour');

			if (!rbfwEditorTour.seen) {
				rbfwEditorTour.seen = true;
				$.post(rbfwEditorTour.ajax_url, {
					action: 'rbfw_editor_tour_seen',
					nonce: rbfwEditorTour.nonce
				});
			}
		}

		$(document).on('click', '#rbfw-me-tour-restart', function (e) {
			e.preventDefault();
			startTour();
		});

		// Auto-start once per user, after the editor clears its own loading
		// skeleton (rbfw-modern-editor.js removes .is-loading once ready).
		if (!rbfwEditorTour.seen) {
			var tries = 0;
			var waitReady = setInterval(function () {
				tries++;
				if (!$wrap.hasClass('is-loading') || tries > 40) {
					clearInterval(waitReady);
					startTour();
				}
			}, 150);
		}
	});
})(jQuery);
