/**
 * Unified coupon field.
 *
 * Works in both booking modes, choosing its endpoint from rbfw_ajax_front.booking_mode:
 *   - standalone -> rbfw_apply_coupon_native (validate + preview only; the authoritative
 *                   recompute happens server-side in RBFW_Native_Checkout::process()).
 *   - woocommerce -> rbfw_apply_coupon / rbfw_remove_coupon (stores the code in the WC session,
 *                   then reloads so WooCommerce re-renders totals from the reduced line prices).
 *
 * IMPORTANT: this script never rewrites the `.total` element. rbfw_native_checkout.js reads it
 * and posts it as `rbfw_total`; discounting it here would double-apply the coupon. The saving is
 * shown in the coupon's own summary row, and exposed on window.rbfwCouponState so the native
 * checkout modal can display the discounted figure.
 */
(function ($) {
	'use strict';

	window.rbfwCouponState = { code: '', discount: 0 };

	function vars() {
		return typeof rbfw_coupon_vars !== 'undefined' ? rbfw_coupon_vars : null;
	}

	function isStandalone() {
		return typeof rbfw_ajax_front !== 'undefined' && rbfw_ajax_front.booking_mode === 'standalone';
	}

	/** Prefer the shared nonce bag so the cache-safe nonce-refresh guard can renew it. */
	function nonce() {
		if (typeof rbfw_ajax_front !== 'undefined' && rbfw_ajax_front.nonce_apply_coupon) {
			return rbfw_ajax_front.nonce_apply_coupon;
		}
		var v = vars();
		return v ? v.nonce : '';
	}

	function ajaxUrl() {
		if (typeof rbfw_ajax_front !== 'undefined' && rbfw_ajax_front.rbfw_ajaxurl) {
			return rbfw_ajax_front.rbfw_ajaxurl;
		}
		var v = vars();
		return v ? v.ajaxurl : '';
	}

	function readNumber($el) {
		if (!$el || !$el.length) return 0;
		var n = parseFloat(($el.text() || '0').replace(/[^0-9.]/g, ''));
		return isNaN(n) ? 0 : n;
	}

	function formOf($wrap) {
		return $wrap.closest('.mp_rbfw_ticket_form');
	}

	/** The booking subtotal (rental + services), falling back to the grand total. */
	function readSubtotal($form) {
		var $s = $form.find('.subtotal .price-figure').first();
		if (!$s.length) $s = $form.find('.subtotal span').first();
		if (!$s.length) $s = $form.find('.total .price-figure').first();
		return readNumber($s);
	}

	function readTotal($form) {
		var $t = $form.find('.total .price-figure').first();
		if (!$t.length) $t = $form.find('.total span').first();
		return readNumber($t);
	}

	function setMsg($wrap, text, type) {
		$wrap.find('.rbfw-coupon__msg')
			.removeClass('is-error is-success')
			.addClass(type ? 'is-' + type : '')
			.text(text || '');
	}

	function showSummary($wrap, code, discountHtml, totalHtml) {
		var v = vars() || {};
		var tpl = v.i18n_saved || 'Coupon %1$s applied — you save %2$s';
		var txt = tpl.replace('%1$s', code).replace('%2$s', discountHtml);
		if (totalHtml) {
			txt += ' · ' + (v.i18n_new_total || 'New total:') + ' ' + totalHtml;
		}
		$wrap.find('.rbfw-coupon__applied').text(txt);
		$wrap.find('.rbfw-coupon__summary').prop('hidden', false);
	}

	function clearSummary($wrap) {
		$wrap.find('.rbfw-coupon__applied').text('');
		$wrap.find('.rbfw-coupon__summary').prop('hidden', true);
	}

	function busy($wrap, on) {
		$wrap.find('.rbfw-coupon__apply, .rbfw-coupon__remove').prop('disabled', !!on);
		$wrap.toggleClass('is-busy', !!on);
	}

	/* ---------------------------------------------------------------------
	 * Standalone
	 * ------------------------------------------------------------------ */

	function nativeRequest($wrap, code, isPreview) {
		var $form = formOf($wrap);
		if (!$form.length) return null;

		var data = $form.serializeArray();
		data.push({ name: 'action', value: 'rbfw_apply_coupon_native' });
		data.push({ name: 'nonce', value: nonce() });
		data.push({ name: 'code', value: code });
		data.push({ name: 'preview', value: isPreview ? '1' : '0' });
		data.push({ name: 'rbfw_subtotal', value: readSubtotal($form) });
		data.push({ name: 'rbfw_total', value: readTotal($form) });
		return $.post(ajaxUrl(), data);
	}

	function nativeApply($wrap, code, isPreview) {
		var req = nativeRequest($wrap, code, isPreview);
		if (!req) return;
		busy($wrap, true);

		req.done(function (res) {
			if (res && res.success) {
				var d = res.data;
				window.rbfwCouponState = { code: d.code || '', discount: parseFloat(d.discount) || 0 };

				if (window.rbfwCouponState.discount > 0) {
					$wrap.find('.rbfw-coupon__code').val(code || d.code || '');
					showSummary($wrap, d.code, d.discount_html, d.total_html);
					setMsg($wrap, isPreview ? '' : d.message, isPreview ? '' : 'success');
				} else {
					clearSummary($wrap);
					if (!isPreview) setMsg($wrap, d.message, 'error');
				}
			} else if (!isPreview) {
				var m = res && res.data && res.data.message ? res.data.message : 'Could not apply the coupon.';
				window.rbfwCouponState = { code: '', discount: 0 };
				$wrap.find('.rbfw-coupon__code').val('');
				clearSummary($wrap);
				setMsg($wrap, m, 'error');
			}
		}).fail(function () {
			if (!isPreview) setMsg($wrap, 'Network error. Please try again.', 'error');
		}).always(function () {
			busy($wrap, false);
		});
	}

	/* ---------------------------------------------------------------------
	 * WooCommerce
	 * ------------------------------------------------------------------ */

	function wcPost($wrap, action, code) {
		busy($wrap, true);
		$.post(ajaxUrl(), { action: action, nonce: nonce(), code: code || '' })
			.done(function (res) {
				if (res && res.success) {
					setMsg($wrap, res.data.message, 'success');
					window.location.reload();
				} else {
					var m = res && res.data && res.data.message ? res.data.message : 'Could not apply the coupon.';
					setMsg($wrap, m, 'error');
					busy($wrap, false);
				}
			})
			.fail(function () {
				setMsg($wrap, 'Network error. Please try again.', 'error');
				busy($wrap, false);
			});
	}

	/* ---------------------------------------------------------------------
	 * Wiring
	 * ------------------------------------------------------------------ */

	$(function () {
		var v = vars();
		if (!v) return;

		$(document).on('click', '.rbfw-coupon__apply', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('[data-rbfw-coupon]');
			var code = $.trim($wrap.find('.rbfw-coupon__input').val() || '');
			if (!code) {
				setMsg($wrap, v.i18n_enter_code || 'Please enter a coupon code.', 'error');
				return;
			}
			if (isStandalone()) {
				nativeApply($wrap, code, false);
			} else {
				wcPost($wrap, 'rbfw_apply_coupon', code);
			}
		});

		$(document).on('click', '.rbfw-coupon__remove', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('[data-rbfw-coupon]');
			if (isStandalone()) {
				window.rbfwCouponState = { code: '', discount: 0 };
				$wrap.find('.rbfw-coupon__input').val('');
				$wrap.find('.rbfw-coupon__code').val('');
				clearSummary($wrap);
				setMsg($wrap, '', '');
				// An automatic rule may still apply once the manual code is gone.
				if (v.has_auto === '1') nativeApply($wrap, '', true);
			} else {
				wcPost($wrap, 'rbfw_remove_coupon', '');
			}
		});

		// Enter key inside the coupon input must not submit the booking form.
		$(document).on('keydown', '.rbfw-coupon__input', function (e) {
			if (e.key === 'Enter' || e.keyCode === 13) {
				e.preventDefault();
				$(this).closest('[data-rbfw-coupon]').find('.rbfw-coupon__apply').trigger('click');
			}
		});

		if (!isStandalone()) return;

		// Standalone only: the discount depends on the live booking price, so re-resolve
		// (debounced) whenever the totals change, and surface automatic rules on load.
		var timer = null;
		function rerun() {
			clearTimeout(timer);
			timer = setTimeout(function () {
				$('[data-rbfw-coupon][data-rbfw-coupon-mode="native"]').each(function () {
					var $wrap = $(this);
					var code = $.trim($wrap.find('.rbfw-coupon__code').val() || '');
					if (code || v.has_auto === '1') {
						nativeApply($wrap, code, true);
					}
				});
			}, 450);
		}

		$('[data-rbfw-coupon][data-rbfw-coupon-mode="native"]').each(function () {
			var $form = formOf($(this));
			if (!$form.length) return;

			var totalEl = $form.find('.total').get(0);
			if (totalEl && typeof MutationObserver !== 'undefined') {
				new MutationObserver(rerun).observe(totalEl, { childList: true, subtree: true, characterData: true });
			}
			$form.on('change', 'input, select', rerun);
		});

		if (v.has_auto === '1') rerun();
	});
})(jQuery);
