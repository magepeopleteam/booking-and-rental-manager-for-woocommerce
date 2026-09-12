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
		var STEPS = [
			{
				selector: '#rbfw_me_post_title',
				tab: 'general',
				title: rbfwEditorTour.i18n.step_title_name,
				text: rbfwEditorTour.i18n.step_text_name
			},
			{
				selector: '.rbfw-me-tabs',
				title: rbfwEditorTour.i18n.step_title_tabs,
				text: rbfwEditorTour.i18n.step_text_tabs
			},
			{
				selector: '.rbfw-me-rent-type-card',
				tab: 'general',
				title: rbfwEditorTour.i18n.step_title_categories,
				text: rbfwEditorTour.i18n.step_text_categories
			},
			{
				selector: '.rbfw-me-panel[data-panel="pricing"]',
				tab: 'pricing',
				title: rbfwEditorTour.i18n.step_title_pricing,
				text: rbfwEditorTour.i18n.step_text_pricing
			},
			{
				selector: '.rbfw-me-panel[data-panel="offday"]',
				tab: 'offday',
				title: rbfwEditorTour.i18n.step_title_offday,
				text: rbfwEditorTour.i18n.step_text_offday
			},
			{
				selector: '.rbfw-me-panel[data-panel="advanced"]',
				tab: 'advanced',
				title: rbfwEditorTour.i18n.step_title_advanced,
				text: rbfwEditorTour.i18n.step_text_advanced
			},
			{
				selector: '.rbfw-me-card--sidebar:has(.rbfw-me-thumb-preview)',
				tab: 'general',
				title: rbfwEditorTour.i18n.step_title_image,
				text: rbfwEditorTour.i18n.step_text_image
			},
			{
				selector: '.rbfw-me-payment-card',
				title: rbfwEditorTour.i18n.step_title_payment,
				text: rbfwEditorTour.i18n.step_text_payment
			},
			{
				selector: '.rbfw-me-publish-group',
				title: rbfwEditorTour.i18n.step_title_publish,
				text: rbfwEditorTour.i18n.step_text_publish
			}
		];

		var current = -1;
		var lastDir = 1;
		var $overlay, $spot, $tooltip;

		function buildUI() {
			$overlay = $('<div class="rbfw-tour-overlay"></div>').appendTo('body');
			$spot = $('<div class="rbfw-tour-spot"></div>').appendTo('body');
			$tooltip = $(
				'<div class="rbfw-tour-tip" role="dialog" aria-live="polite">' +
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
			var top;

			if (spaceBelow > tipHeight + 24 || spaceBelow > rect.top) {
				top = rect.bottom + 16;
			} else {
				top = rect.top - tipHeight - 16;
			}
			top = Math.max(12, Math.min(top, window.innerHeight - tipHeight - 12));

			var left = rect.left + (rect.width / 2) - (tipWidth / 2);
			left = Math.max(12, Math.min(left, window.innerWidth - tipWidth - 12));

			$tooltip.css({ top: top + 'px', left: left + 'px' });
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
					$tooltip.find('.rbfw-tour-tip__title').text(step.title);
					$tooltip.find('.rbfw-tour-tip__text').text(step.text);
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
