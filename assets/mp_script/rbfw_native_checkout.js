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

	function readTotal($form) {
		var $fig = $form.find('.total .price-figure').first();
		if (!$fig.length) {
			$fig = $form.find('.total').first();
		}
		var raw = $fig.text() || '0';
		var num = parseFloat(raw.replace(/[^0-9.]/g, ''));
		return isNaN(num) ? 0 : num;
	}

	function openModal($form) {
		$activeForm = $form;
		var total = readTotal($form);
		var $modal = $('#rbfw-native-checkout-modal');
		if (!$modal.length) {
			return;
		}
		$modal.find('[data-rbfw-native-total]').text(
			(rbfw_ajax_front.currency_symbol || '') + total.toFixed(2)
		);
		$modal.find('[data-rbfw-native-message]').removeClass('error success').text('');
		$modal.attr('aria-hidden', 'false').fadeIn(120);
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
		data.push({ name: 'rbfw_total', value: readTotal($activeForm) });

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
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') {
				closeModal();
			}
		});
	});
})(jQuery);
