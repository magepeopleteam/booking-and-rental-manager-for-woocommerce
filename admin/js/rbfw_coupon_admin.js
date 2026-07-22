/**
 * Coupons manager — multi-step wizard.
 *
 * Field names are identical to the flat form the AJAX handler already validates
 * (RBFW_Coupon_Admin::ajax_save), so the wizard is purely a presentation layer: it
 * serializes the whole <form> in one go regardless of which step is visible.
 */
(function ($) {
	'use strict';

	var STEPS = 5;
	var LAST = STEPS - 1;

	var $modal, $form, $rail, $panes, $progress, $msg, $prev, $next, $save, $caption;
	var step = 0;

	function t(key, fallback) {
		return (typeof rbfwCoupon !== 'undefined' && rbfwCoupon[key]) ? rbfwCoupon[key] : fallback;
	}

	/* ------------------------------------------------------------------ */
	/* Step navigation                                                      */
	/* ------------------------------------------------------------------ */

	function captionFor(i) {
		var $item = $rail.find('.rbfw-cpn-railitem').eq(i);
		var title = $item.find('strong').text();
		return 'Step ' + (i + 1) + ' of ' + STEPS + ' · ' + title;
	}

	function paint() {
		$panes.find('.rbfw-cpn-pane').removeClass('is-active')
			.filter('[data-pane="' + step + '"]').addClass('is-active');

		$rail.find('.rbfw-cpn-railitem').each(function (i) {
			$(this).toggleClass('is-active', i === step)
				   .toggleClass('is-done', i < step);
		});

		$progress.find('i').css('width', (((step + 1) / STEPS) * 100) + '%');
		$caption.text(captionFor(step));

		$prev.prop('disabled', step === 0);
		$next.prop('hidden', step === LAST);
		$save.prop('hidden', step !== LAST);

		$panes.scrollTop(0);
	}

	function go(target) {
		if (target > step) {
			// Validate every step between here and the target.
			for (var i = step; i < target; i++) {
				if (!validateStep(i)) { step = i; paint(); return; }
			}
		}
		step = Math.max(0, Math.min(LAST, target));
		if (step === LAST) buildReview();
		paint();
	}

	/* ------------------------------------------------------------------ */
	/* Validation (mirrors the server-side rules in ajax_save)              */
	/* ------------------------------------------------------------------ */

	/** Show a validation error directly UNDER the offending field (not in the footer). */
	function fail($el, message) {
		clearFieldErrors();
		if ($el && $el.length) {
			$el.addClass('has-error');
			var $label = $el.closest('label');
			var $err = $('<span class="rbfw-cpn-fielderr" role="alert"></span>').text(message);
			($label.length ? $label : $el).append($err);
			$el.one('input change', clearFieldErrors);
			try { $el.trigger('focus'); } catch (e) {}
		}
		return false;
	}

	function clearFieldErrors() {
		$form.find('.rbfw-cpn-fielderr').remove();
		$form.find('.has-error').removeClass('has-error');
	}

	function validateStep(i) {
		setMsg('', '');
		clearFieldErrors();
		if (i !== 0) return true;

		var $code = $form.find('[name=code]');
		if (!$.trim($code.val())) return fail($code, t('i18n_need_code', 'Please enter a coupon code.'));

		var $val = $form.find('[name=discount_value]');
		var v = parseFloat($val.val());
		if (isNaN(v) || v <= 0) return fail($val, t('i18n_need_val', 'Enter a discount value greater than zero.'));

		if (typeVal() === 'percentage' && v > 100) return fail($val, t('i18n_pct_max', 'A percentage discount cannot exceed 100%.'));

		return true;
	}

	function setMsg(text, cls) {
		$msg.removeClass('ok err').addClass(cls || '').text(text || '');
	}

	/* ------------------------------------------------------------------ */
	/* Type-dependent UI                                                    */
	/* ------------------------------------------------------------------ */

	function typeVal() {
		return $form.find('[name=discount_type]:checked').val() || 'percentage';
	}

	function syncType() {
		var type = typeVal();
		$('#rbfw-cpn-cap-wrap').toggle(type === 'percentage');

		var label = 'Value';
		if (type === 'percentage') label = 'Percentage (%)';
		else if (type === 'fixed') label = 'Amount (' + t('currency', '') + ')';
		else if (type === 'free_days') label = 'Free days / hours';
		$form.find('[data-value-label]').html(label + ' <b class="req">*</b>');
	}

	/* ------------------------------------------------------------------ */
	/* Review step                                                          */
	/* ------------------------------------------------------------------ */

	function checkedLabels(name) {
		var out = [];
		$form.find('[name="' + name + '[]"]:checked').each(function () {
			out.push($.trim($(this).next('span').text()));
		});
		return out;
	}

	function selectedLabels(name) {
		var out = [];
		$form.find('select[name="' + name + '[]"] option:selected').each(function () {
			out.push($.trim($(this).text()));
		});
		return out;
	}

	function val(name) { return $.trim($form.find('[name="' + name + '"]').val() || ''); }
	function on(name) { return $form.find('[name="' + name + '"]').is(':checked'); }

	function group(title, rows) {
		var visible = rows.filter(function (r) { return r[1] !== ''; });
		if (!visible.length) return '';
		var html = '<div class="rbfw-cpn-rev-group"><h4>' + title + '</h4>';
		visible.forEach(function (r) {
			html += '<div class="rbfw-cpn-rev-row"><span>' + r[0] + '</span><b>' + $('<i>').text(r[1]).html() + '</b></div>';
		});
		return html + '</div>';
	}

	function buildReview() {
		var type = typeVal();
		var v = val('discount_value');
		var amount = type === 'percentage' ? v + '%'
			: type === 'fixed' ? t('currency', '') + v
			: v + ' day(s)';

		var ALL = t('i18n_all', 'All rentals');
		var NONE = t('i18n_none', 'None');
		var UNL = t('i18n_unlimited', 'Unlimited');

		var inc = selectedLabels('target_items').concat(selectedLabels('target_rent_types')).concat(selectedLabels('target_locations'));
		var exc = selectedLabels('exclude_items').concat(selectedLabels('exclude_rent_types')).concat(selectedLabels('exclude_locations'));

		var from = val('valid_from'), to = val('valid_to');
		var validity = (from || to) ? ((from || '…') + ' → ' + (to || '…')) : t('i18n_always', 'Always');

		var days = checkedLabels('weekdays');
		var roles = checkedLabels('allowed_roles');

		var html = '';
		html += group('Basics', [
			['Code', val('code').toUpperCase()],
			['Status', $form.find('[name=status]').val() === 'publish' ? 'Active' : 'Inactive'],
			['Discount', amount],
			['Max discount cap', (type === 'percentage' && val('max_discount') && val('max_discount') !== '0') ? t('currency', '') + val('max_discount') : ''],
			['Applies automatically', on('auto_apply') ? 'Yes (no code needed)' : 'No — code required'],
			['Priority', on('auto_apply') ? (val('priority') || '0') : ''],
			['Stacking', on('allow_combine') ? 'Can combine with other coupons' : 'Cannot be combined']
		]);
		html += group('Targeting', [
			['Includes', inc.length ? inc.join(', ') : ALL],
			['Excludes', exc.length ? exc.join(', ') : NONE]
		]);
		html += group('Spend & date rules', [
			['Minimum amount', (val('min_amount') && val('min_amount') !== '0') ? t('currency', '') + val('min_amount') : ''],
			['Maximum amount', (val('max_amount') && val('max_amount') !== '0') ? t('currency', '') + val('max_amount') : ''],
			['Validity', validity],
			['Allowed weekdays', days.length ? days.join(', ') : 'Every day'],
			['Blackout dates', val('blackout_dates')]
		]);
		html += group('Limits & eligibility', [
			['Total uses', (val('usage_limit') && val('usage_limit') !== '0') ? val('usage_limit') : UNL],
			['Per user', (val('usage_limit_per_user') && val('usage_limit_per_user') !== '0') ? val('usage_limit_per_user') : UNL],
			['Per day', (val('usage_limit_per_day') && val('usage_limit_per_day') !== '0') ? val('usage_limit_per_day') : UNL],
			['Allowed roles', roles.length ? roles.join(', ') : t('i18n_everyone', 'Everyone')],
			['Allowed emails', val('allowed_emails')],
			['First booking only', on('first_booking_only') ? 'Yes' : '']
		]);

		$('#rbfw-cpn-review').html(html);
	}

	/* ------------------------------------------------------------------ */
	/* Open / populate                                                      */
	/* ------------------------------------------------------------------ */

	function setChecks(name, values) {
		values = (values || []).map(String);
		$form.find('[name="' + name + '[]"]').each(function () {
			$(this).prop('checked', values.indexOf(String($(this).val())) !== -1);
		});
	}

	function setMulti(name, values) {
		values = (values || []).map(String);
		$form.find('select[name="' + name + '[]"] option').each(function () {
			$(this).prop('selected', values.indexOf(String($(this).val())) !== -1);
		});
	}

	function open(data) {
		data = data || {};

		$form[0].reset();
		$form.find('input[type=checkbox]').prop('checked', false);
		$form.find('select[multiple] option').prop('selected', false);
		$form.find('.has-error').removeClass('has-error');
		setMsg('', '');

		$('#rbfw-cpn-modal-title').text(data.id ? t('i18n_edit', 'Edit Coupon') : t('i18n_add', 'Add Coupon'));
		$form.find('[name=coupon_id]').val(data.id || 0);

		['code', 'status', 'discount_value', 'max_discount', 'priority',
		 'min_amount', 'max_amount', 'valid_from', 'valid_to', 'blackout_dates',
		 'usage_limit', 'usage_limit_per_user', 'usage_limit_per_day', 'allowed_emails'
		].forEach(function (k) {
			if (data[k] !== undefined && data[k] !== null) $form.find('[name="' + k + '"]').val(data[k]);
		});

		$form.find('[name=discount_type][value="' + (data.discount_type || 'percentage') + '"]').prop('checked', true);

		['auto_apply', 'allow_combine', 'first_booking_only'].forEach(function (k) {
			$form.find('[name="' + k + '"]').prop('checked', data[k] === 'yes');
		});

		// Targeting fields are all <select multiple> (token pickers).
		setMulti('target_items', data.target_items);
		setMulti('exclude_items', data.exclude_items);
		setMulti('target_rent_types', data.target_rent_types);
		setMulti('exclude_rent_types', data.exclude_rent_types);
		setMulti('target_locations', data.target_locations);
		setMulti('exclude_locations', data.exclude_locations);
		// Weekdays + roles stay checkbox grids.
		setChecks('weekdays', data.weekdays);
		setChecks('allowed_roles', data.allowed_roles);

		clearFieldErrors();
		Tokens.refreshAll();   // repaint chips from the native selects we just set

		syncType();
		step = 0;
		paint();
		$modal.addClass('is-open').attr('aria-hidden', 'false');
		setTimeout(function () { $form.find('[name=code]').trigger('focus'); }, 60);
	}

	function close() {
		$modal.removeClass('is-open').attr('aria-hidden', 'true');
	}

	/* ------------------------------------------------------------------ */
	/* Persistence                                                          */
	/* ------------------------------------------------------------------ */

	function save() {
		for (var i = 0; i < STEPS; i++) {
			if (!validateStep(i)) { step = i; paint(); return; }
		}

		var data = $form.serializeArray();
		data.push({ name: 'action', value: 'rbfw_coupon_save' });
		data.push({ name: 'nonce', value: rbfwCoupon.nonce });

		$save.prop('disabled', true);
		setMsg('', '');

		$.post(rbfwCoupon.ajaxurl, data, function (res) {
			if (res && res.success) {
				setMsg(res.data.message, 'ok');
				setTimeout(function () { window.location.reload(); }, 500);
			} else {
				setMsg((res && res.data && res.data.message) ? res.data.message : 'Error', 'err');
				$save.prop('disabled', false);
			}
		}).fail(function () {
			setMsg(t('i18n_network', 'Network error. Please try again.'), 'err');
			$save.prop('disabled', false);
		});
	}

	function rowAction(action, id) {
		$.post(rbfwCoupon.ajaxurl, { action: action, nonce: rbfwCoupon.nonce, coupon_id: id }, function () {
			window.location.reload();
		});
	}

	/* ------------------------------------------------------------------ */
	/* Token multi-select (click → searchable dropdown → chips)             */
	/*                                                                      */
	/* The native <select multiple> stays in the DOM as the single source   */
	/* of truth (so serializeArray keeps posting name[] unchanged); this    */
	/* only paints chips + a filterable menu over it.                       */
	/* ------------------------------------------------------------------ */

	var Tokens = (function () {

		function build($sel) {
			if ($sel.data('tokBound')) return;
			$sel.data('tokBound', true);

			var placeholder = $sel.data('placeholder') || '';
			var emptyText   = $sel.data('empty') || 'No matches';
			var isEx        = /(^|_)exclude/.test($sel.attr('name') || '');

			var $tok     = $('<div class="rbfw-tok"></div>').toggleClass('is-ex', isEx);
			var $control = $('<div class="rbfw-tok-control"></div>');
			var $search  = $('<input type="text" class="rbfw-tok-search" autocomplete="off">');
			var $caret   = $('<i class="fas fa-chevron-down rbfw-tok-caret"></i>');
			var $menu    = $('<div class="rbfw-tok-menu" hidden></div>');
			var $empty   = $('<div class="rbfw-tok-empty" hidden></div>').text(emptyText);

			$sel.find('option').each(function () {
				$('<div class="rbfw-tok-opt"></div>')
					.attr('data-value', $(this).attr('value'))
					.text($(this).text())
					.appendTo($menu);
			});
			$menu.append($empty);
			$control.append($search).append($caret);
			$tok.append($control).append($menu);
			$sel.after($tok);

			function setSelected(value, on) {
				$sel.find('option').each(function () {
					if ($(this).attr('value') === String(value)) $(this).prop('selected', on);
				});
			}

			function filter(q) {
				q = (q || '').toLowerCase();
				var visible = 0;
				$menu.find('.rbfw-tok-opt').each(function () {
					var $o = $(this);
					var match = !$o.hasClass('is-picked') && $o.text().toLowerCase().indexOf(q) !== -1;
					$o.toggle(match);
					if (match) visible++;
				});
				$empty.prop('hidden', visible > 0);
			}

			function repaint() {
				$control.find('.rbfw-tok-chip').remove();
				var picked = {};
				$sel.find('option:selected').each(function () {
					var $o = $(this);
					picked[$o.attr('value')] = 1;
					var $chip = $('<span class="rbfw-tok-chip"></span>').data('value', $o.attr('value'));
					$('<span></span>').text($o.text()).appendTo($chip);
					$('<b aria-hidden="true">&times;</b>').appendTo($chip);
					$search.before($chip);
				});
				$menu.find('.rbfw-tok-opt').each(function () {
					$(this).toggleClass('is-picked', !!picked[$(this).attr('data-value')]);
				});
				$search.attr('placeholder', $control.find('.rbfw-tok-chip').length ? '' : placeholder);
				filter($search.val());
			}

			function openMenu() { $tok.addClass('is-open'); $menu.prop('hidden', false); filter($search.val()); }
			function closeMenu() { $tok.removeClass('is-open'); $menu.prop('hidden', true); }

			function pick(value) { setSelected(value, true); $search.val(''); repaint(); $sel.trigger('change'); }
			function unpick(value) { setSelected(value, false); repaint(); $sel.trigger('change'); }

			$control.on('click', function (e) {
				if ($(e.target).closest('.rbfw-tok-chip b').length) return;
				$search.trigger('focus');
			});
			$search.on('focus', openMenu);
			$search.on('input', function () { openMenu(); filter($(this).val()); });
			$menu.on('click', '.rbfw-tok-opt', function () {
				if (!$(this).hasClass('is-picked')) { pick($(this).attr('data-value')); $search.trigger('focus'); }
			});
			$control.on('click', '.rbfw-tok-chip b', function (e) {
				e.stopPropagation();
				unpick($(this).closest('.rbfw-tok-chip').data('value'));
			});
			$search.on('keydown', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault(); e.stopPropagation();   // never let the wizard advance
					var $first = $menu.find('.rbfw-tok-opt:visible').first();
					if ($first.length) pick($first.attr('data-value'));
				} else if (e.key === 'Escape') {
					e.stopPropagation(); closeMenu();
				} else if (e.key === 'Backspace' && !$search.val()) {
					var $last = $control.find('.rbfw-tok-chip').last();
					if ($last.length) unpick($last.data('value'));
				}
			});

			$sel.data('tokRefresh', repaint);
			repaint();
		}

		$(document).on('click', function (e) {
			if (!$(e.target).closest('.rbfw-tok').length) {
				$('.rbfw-tok').removeClass('is-open').find('.rbfw-tok-menu').prop('hidden', true);
			}
		});

		return {
			init: function () { $('#rbfw-cpn-form select[data-token]').each(function () { build($(this)); }); },
			refreshAll: function () {
				$('#rbfw-cpn-form select[data-token]').each(function () {
					var fn = $(this).data('tokRefresh');
					if (fn) fn();
				});
			}
		};
	})();

	/* ------------------------------------------------------------------ */
	/* Boot                                                                 */
	/* ------------------------------------------------------------------ */

	$(function () {
		$modal = $('#rbfw-cpn-modal');
		if (!$modal.length) return;

		$form     = $('#rbfw-cpn-form');
		$rail     = $modal.find('.rbfw-cpn-rail');
		$panes    = $modal.find('.rbfw-cpn-panes');
		$progress = $modal.find('.rbfw-cpn-progress');
		$msg      = $modal.find('.rbfw-cpn-msg');
		$prev     = $modal.find('[data-prev]');
		$next     = $modal.find('[data-next]');
		$save     = $modal.find('[data-save]');
		$caption  = $modal.find('.rbfw-cpn-stepcaption');

		Tokens.init();

		$(document).on('click', '.rbfw-cpn-add', function () { open({}); });
		$(document).on('click', '.rbfw-cpn-edit', function () { open($(this).closest('tr').data('coupon')); });
		$(document).on('click', '.rbfw-cpn-close', close);

		$modal.on('click', function (e) { if (e.target === this) close(); });
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape' && $modal.hasClass('is-open')) close();
		});

		$next.on('click', function () { go(step + 1); });
		$prev.on('click', function () { go(step - 1); });
		$save.on('click', save);
		$rail.on('click', '.rbfw-cpn-railitem', function () { go(parseInt($(this).data('step'), 10)); });

		$form.on('change', '[name=discount_type]', syncType);

		// A stray Enter must advance the wizard, never submit the form.
		$form.on('submit', function (e) { e.preventDefault(); });
		$form.on('keydown', 'input', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				if (step === LAST) save(); else go(step + 1);
			}
		});

		$(document).on('click', '.rbfw-cpn-toggle', function () { rowAction('rbfw_coupon_toggle', $(this).data('id')); });
		$(document).on('click', '.rbfw-cpn-delete', function () {
			if (window.confirm(t('i18n_confirm', 'Delete this coupon permanently?'))) {
				rowAction('rbfw_coupon_delete', $(this).data('id'));
			}
		});
	});
})(jQuery);
