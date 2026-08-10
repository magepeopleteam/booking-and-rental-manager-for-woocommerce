/**
 * Standalone (non-WooCommerce) checkout.
 *
 * When Booking Mode = Standalone, the rental booking form must NOT submit to the
 * WooCommerce cart. This script intercepts the "Book Now" submit, opens the native
 * checkout modal to collect contact details, and posts the booking to
 * wp_ajax_rbfw_native_checkout. In WooCommerce mode it does nothing — the standard
 * add-to-cart form submission proceeds unchanged.
 */
(function ($) {
	'use strict';

	function isStandalone() {
		return typeof rbfw_ajax_front !== 'undefined' && rbfw_ajax_front.booking_mode === 'standalone';
	}

	var $activeForm = null;

	// Coupon state for the current modal session (owned here, entered in the modal).
	// `manualCouponCode` is deliberately separate: only a code the CUSTOMER typed may be posted
	// with the booking. Posting back an automatic resolution would hand the server a code it
	// treats as manual — and several stacked automatic codes arrive as "A, B", which no lookup
	// can resolve, so the booking would be refused as an invalid coupon.
	var appliedCouponCode = '';
	var manualCouponCode = '';
	var appliedCouponDiscount = 0;

	function readTotal($form) {
		var $fig = $form.find('.total .price-figure').first();
		if (!$fig.length) {
			$fig = $form.find('.total').first();
		}
		var raw = $fig.text() || '0';
		var num = parseFloat(raw.replace(/[^0-9.]/g, ''));
		return isNaN(num) ? 0 : num;
	}

	/**
	 * The booking total to POST, which deliberately EXCLUDES delivery.
	 *
	 * The displayed total includes delivery so the customer sees what they will pay, but the
	 * server prices delivery itself from the band table and adds it on top — that is what
	 * makes the charge tamper-proof. Posting the displayed figure therefore counted delivery
	 * twice: a $20 booking with $100 of delivery arrived as $120 and was confirmed at $220.
	 *
	 * Subtracting it here keeps one rule: the client reports the RENTAL, the server decides
	 * the DELIVERY.
	 *
	 * @param {jQuery} $form
	 * @return {number}
	 */
	function readPostableTotal($form) {
		var delivery = parseFloat(window.rbfwDeliveryTotal) || 0;
		return Math.max(0, readTotal($form) - delivery);
	}

	function currency() {
		return (typeof rbfw_ajax_front !== 'undefined' && rbfw_ajax_front.currency_symbol) ? rbfw_ajax_front.currency_symbol : '';
	}

	/** Refresh the modal's Total = gross booking total − applied coupon discount. */
	function updateModalTotal() {
		if (!$activeForm) { return; }
		var payable = Math.max(0, readTotal($activeForm) - appliedCouponDiscount);
		$('#rbfw-native-checkout-modal').find('[data-rbfw-native-total]').text(currency() + payable.toFixed(2));
	}

	function resetCoupon($modal) {
		appliedCouponCode = '';
		manualCouponCode = '';
		appliedCouponDiscount = 0;
		$modal.find('[data-rbfw-native-coupon-input]').val('');
		$modal.find('[data-rbfw-native-coupon-msg]').removeClass('error success').text('');
		$modal.find('[data-rbfw-native-coupon-applied]').prop('hidden', true);
	}

	/**
	 * The booking payload the coupon endpoint is judged against.
	 *
	 * It is the booking form itself, so every rule the coupon can carry — targeting, dates,
	 * spend window — sees the same values the booking will be created with. The customer
	 * identity is added from the modal: without it, "Allowed emails" and "First booking only"
	 * were evaluated against an empty address and rejected every guest at Apply time, even
	 * though the very same rules then passed on submit.
	 */
	function couponPayload($modal) {
		// Coupons apply to the rental, not to the delivery legs, so they are priced against
		// the same base the booking itself will be created with.
		var gross = readPostableTotal($activeForm);
		var data = $activeForm.serializeArray();
		data.push({ name: 'action', value: 'rbfw_apply_coupon_native' });
		data.push({ name: 'nonce', value: rbfw_ajax_front.nonce_apply_coupon });
		data.push({ name: 'rbfw_subtotal', value: gross });
		data.push({ name: 'rbfw_total', value: gross });

		var email = $.trim($modal.find('#rbfw_billing_email').val() || '');
		if (email) {
			data.push({ name: 'rbfw_billing_email', value: email });
		}
		return data;
	}

	/** Paint the outcome of a coupon request (shared by manual apply and automatic preview). */
	function showCouponResult(res, $modal, $msg, typedCode, silent) {
		var discount = (res && res.success) ? parseFloat(res.data.discount) : 0;

		if (res && res.success && discount > 0) {
			appliedCouponCode = res.data.code || typedCode;
			manualCouponCode = silent ? '' : typedCode;
			appliedCouponDiscount = discount;
			$modal.find('[data-rbfw-native-coupon-summary]').text(
				(res.data.code || typedCode) + '  −' + (res.data.discount_html || '')
			);
			$modal.find('[data-rbfw-native-coupon-applied]').prop('hidden', false);
			if (!silent) {
				$msg.removeClass('error').addClass('success').text(res.data.message || 'Coupon applied.');
			}
		} else {
			appliedCouponCode = '';
			manualCouponCode = '';
			appliedCouponDiscount = 0;
			$modal.find('[data-rbfw-native-coupon-applied]').prop('hidden', true);
			// A silent preview stays silent: nothing was typed, so there is nothing to report.
			if (!silent) {
				var m = (res && res.data && res.data.message) ? res.data.message : 'Coupon could not be applied.';
				$msg.removeClass('success').addClass('error').text(m);
			}
		}
		updateModalTotal();
	}

	function applyCoupon() {
		var $modal = $('#rbfw-native-checkout-modal');
		var $wrap = $modal.find('[data-rbfw-native-coupon]');
		var $msg = $modal.find('[data-rbfw-native-coupon-msg]');
		var code = $.trim($modal.find('[data-rbfw-native-coupon-input]').val() || '');
		if (!$activeForm) { return; }
		if (!code) {
			$msg.removeClass('success').addClass('error').text('Please enter a coupon code.');
			return;
		}

		// Validate + price the coupon against the live booking form, server-side.
		var data = couponPayload($modal);
		data.push({ name: 'code', value: code });
		data.push({ name: 'preview', value: '0' });

		$wrap.addClass('is-busy');
		$msg.removeClass('error success').text('');

		$.post(rbfw_ajax_front.rbfw_ajaxurl, data)
			.done(function (res) {
				showCouponResult(res, $modal, $msg, code, false);
			})
			.fail(function () {
				$msg.removeClass('success').addClass('error').text('Network error. Please try again.');
			})
			.always(function () {
				$wrap.removeClass('is-busy');
			});
	}

	/**
	 * Automatic (no-code) discounts.
	 *
	 * The server applies these on submit whether or not anything was typed, so the modal has to
	 * show them too — otherwise the customer confirms a Total that is higher than what they are
	 * actually charged. Only runs when the page was rendered with active automatic rules.
	 */
	function previewAutoCoupons($modal) {
		if (!$activeForm || $modal.attr('data-rbfw-native-auto') !== '1') { return; }

		var data = couponPayload($modal);
		data.push({ name: 'code', value: '' });
		data.push({ name: 'preview', value: '1' });

		$.post(rbfw_ajax_front.rbfw_ajaxurl, data).done(function (res) {
			// Never clobber a code the customer typed while this was in flight.
			if (appliedCouponCode) { return; }
			showCouponResult(res, $modal, $modal.find('[data-rbfw-native-coupon-msg]'), '', true);
		});
	}

	function removeCoupon() {
		var $modal = $('#rbfw-native-checkout-modal');
		resetCoupon($modal);
		updateModalTotal();
		// Removing a typed code does not remove the shop's automatic discounts — the server
		// still applies them — so the displayed Total has to fall back to them, not to gross.
		previewAutoCoupons($modal);
	}

	function openModal($form) {
		$activeForm = $form;
		var $modal = $('#rbfw-native-checkout-modal');
		if (!$modal.length) {
			return;
		}
		// Fresh coupon state each time the modal opens.
		resetCoupon($modal);
		updateModalTotal();
		$modal.find('[data-rbfw-native-message]').removeClass('error success').text('');
		$modal.attr('aria-hidden', 'false').fadeIn(120);
		previewAutoCoupons($modal);
	}

	function closeModal() {
		$('#rbfw-native-checkout-modal').attr('aria-hidden', 'true').fadeOut(120);
	}

	function submitBooking() {
		if (!$activeForm || !$activeForm.length) {
			return;
		}
		var $modal = $('#rbfw-native-checkout-modal');
		var $msg = $modal.find('[data-rbfw-native-message]');
		var name = $.trim($modal.find('#rbfw_billing_name').val() || '');
		var email = $.trim($modal.find('#rbfw_billing_email').val() || '');
		var phone = $.trim($modal.find('#rbfw_billing_phone').val() || '');

		if (!name) {
			$msg.addClass('error').text('Please enter your name.');
			return;
		}
		if (!email || email.indexOf('@') === -1) {
			$msg.addClass('error').text('Please enter a valid email address.');
			return;
		}

		// Serialize the whole booking form, then add billing + meta fields.
		var data = $activeForm.serializeArray();
		data.push({ name: 'action', value: 'rbfw_native_checkout' });
		data.push({ name: 'nonce', value: rbfw_ajax_front.nonce_native_checkout });
		data.push({ name: 'rbfw_billing_name', value: name });
		data.push({ name: 'rbfw_billing_email', value: email });
		data.push({ name: 'rbfw_billing_phone', value: phone });
		data.push({ name: 'rbfw_total', value: readPostableTotal($activeForm) });
		// Only the code the customer typed (server re-validates + recomputes the discount
		// authoritatively, and resolves automatic rules on its own).
		if (manualCouponCode) {
			data.push({ name: 'rbfw_coupon_code', value: manualCouponCode });
		}

		// Payment method (Pro): the selector lives in the modal, not the booking form,
		// so push the chosen gateway explicitly when present.
		var pm = $modal.find('input[name="rbfw_payment_method"]:checked').val();
		if (pm) {
			data.push({ name: 'rbfw_payment_method', value: pm });
		}

		var $btn = $modal.find('[data-rbfw-native-submit]');
		$btn.prop('disabled', true).addClass('is-loading');
		$msg.removeClass('error success').text('');

		$.post(rbfw_ajax_front.rbfw_ajaxurl, data)
			.done(function (res) {
				if (res && res.success) {
					$msg.addClass('success').text((res.data && res.data.message) || 'Booking received.');
					// Only navigate when the server returned a real URL to send the
					// customer to (e.g. a gateway hosted checkout or confirmation page).
					// Otherwise stop the loader so the button never hangs on a spinner.
					var redirect = res.data && res.data.redirect ? String(res.data.redirect) : '';
					if (/^(https?:)?\/\//i.test(redirect) || redirect.charAt(0) === '/') {
						window.location.href = redirect;
					} else {
						$btn.prop('disabled', false).removeClass('is-loading');
					}
				} else {
					var m = res && res.data && res.data.message ? res.data.message : 'Something went wrong. Please try again.';
					$msg.addClass('error').text(m);
					$btn.prop('disabled', false).removeClass('is-loading');
				}
			})
			.fail(function () {
				$msg.addClass('error').text('Network error. Please try again.');
				$btn.prop('disabled', false).removeClass('is-loading');
			});
	}

	$(function () {
		if (!isStandalone()) {
			return;
		}

		// Intercept the booking form submit (covers the Book Now button).
		$(document).on('submit', '.mp_rbfw_ticket_form', function (e) {
			e.preventDefault();
			openModal($(this));
		});

		// Some buttons may trigger via click without a native submit; guard those too.
		$(document).on('click', '.mp_rbfw_book_now_submit', function (e) {
			var $form = $(this).closest('.mp_rbfw_ticket_form');
			if ($form.length) {
				e.preventDefault();
				openModal($form);
			}
		});

		$(document).on('click', '[data-rbfw-native-close]', closeModal);
		$(document).on('click', '[data-rbfw-native-submit]', submitBooking);
		$(document).on('click', '[data-rbfw-native-coupon-apply]', applyCoupon);
		$(document).on('click', '[data-rbfw-native-coupon-remove]', removeCoupon);
		// Enter inside the coupon input applies the coupon (never submits the booking).
		$(document).on('keydown', '[data-rbfw-native-coupon-input]', function (e) {
			if (e.key === 'Enter' || e.keyCode === 13) {
				e.preventDefault();
				applyCoupon();
			}
		});
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') {
				closeModal();
			}
		});
	});
})(jQuery);
