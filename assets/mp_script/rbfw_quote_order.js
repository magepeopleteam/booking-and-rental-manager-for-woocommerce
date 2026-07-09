(function($) {
    'use strict';

    function getItemQuoteMode() {
        return (window.rbfw_ajax_front && window.rbfw_ajax_front.rbfw_quote_mode) ? window.rbfw_ajax_front.rbfw_quote_mode : 'off';
    }

    function getQuoteRows(type) {
        return (window.rbfw_ajax_front && window.rbfw_ajax_front.rbfw_quote_rows && window.rbfw_ajax_front.rbfw_quote_rows[type]) ? window.rbfw_ajax_front.rbfw_quote_rows[type] : {};
    }

    function effectiveModeForRow(type, rowName) {
        var itemMode = getItemQuoteMode();
        var rows = getQuoteRows(type);
        var rowMode = rows[rowName] || 'default';
        if (rowMode === 'yes') {
            return 'quote_only';
        }
        if (rowMode === 'no') {
            return 'off';
        }
        return itemMode;
    }

    function updateQuoteUI($form) {
        var itemMode = getItemQuoteMode();
        var $bookBtn = $form.find('.mp_rbfw_book_now_submit[type="submit"]');
        var $reserveBtn = $form.find('.rbfw_reserve_trip_btn');
        var $priceSummary = $form.find('.rbfw_bikecarsd_price_summary, .rbfw-bikecarmd-price-summary, .rbfw_resort_price_summary, .rbfw-mi-price-summary').first();

        if (itemMode === 'off') {
            $reserveBtn.hide();
            $bookBtn.show();
            $priceSummary.removeClass('rbfw-quote-only-hidden');
            return;
        }

        var rentType = (window.rbfw_ajax_front && window.rbfw_ajax_front.rbfw_item_type) ? window.rbfw_ajax_front.rbfw_item_type : '';
        var effective = itemMode;

        if (rentType === 'bike_car_sd' || rentType === 'appointment') {
            var selectedType = $form.find('#rbfw_service_type_for_st').val() || '';
            if (!selectedType) {
                selectedType = $form.find('input[name="service_type"]').val() || '';
            }
            if (selectedType) {
                effective = effectiveModeForRow('bike_car_sd', selectedType);
            }
        } else if (rentType === 'resort') {
            var hasQuote = false, hasInstant = false;
            $form.find('.rbfw_room_qty').each(function() {
                var qty = parseInt($(this).val(), 10) || 0;
                if (qty > 0) {
                    var roomType = $(this).data('room_type') || $(this).closest('tr').find('.rbfw_room_name').text() || '';
                    var mode = effectiveModeForRow('resort', roomType);
                    if (mode === 'quote_only') {
                        hasQuote = true;
                    }
                    if (mode === 'off') {
                        hasInstant = true;
                    }
                }
            });
            if (hasQuote && !hasInstant) {
                effective = 'quote_only';
            } else if (hasInstant && !hasQuote) {
                effective = 'off';
            } else {
                effective = itemMode;
            }
        } else if (rentType === 'multiple_items') {
            var hasQuote = false, hasInstant = false;
            $form.find('.rbfw_mi_qty_input').each(function() {
                var qty = parseInt($(this).val(), 10) || 0;
                if (qty > 0) {
                    var itemName = $(this).data('item_name') || $(this).closest('.rbfw-mi-item-row').find('.rbfw-mi-item-name').text() || '';
                    var mode = effectiveModeForRow('multiple_items', itemName);
                    if (mode === 'quote_only') {
                        hasQuote = true;
                    }
                    if (mode === 'off') {
                        hasInstant = true;
                    }
                }
            });
            if (hasQuote && !hasInstant) {
                effective = 'quote_only';
            } else if (hasInstant && !hasQuote) {
                effective = 'off';
            } else {
                effective = itemMode;
            }
        }

        if (effective === 'quote_only') {
            $bookBtn.hide();
            $reserveBtn.show();
            $priceSummary.addClass('rbfw-quote-only-hidden');
        } else if (effective === 'price_and_quote') {
            $bookBtn.show();
            $reserveBtn.show();
            $priceSummary.removeClass('rbfw-quote-only-hidden');
        } else {
            $bookBtn.show();
            $reserveBtn.hide();
            $priceSummary.removeClass('rbfw-quote-only-hidden');
        }
    }

    function getTotalText($form) {
        var $total = $form.find('.rbfw-costing .total .price-figure, .rbfw_bikecarsd_price_summary .total .price-figure, .rbfw-bikecarmd-price-summary .total .price-figure, .rbfw_resort_price_summary .total .price-figure, .rbfw-mi-price-summary .total .price-figure').first();
        return $total.length ? $total.text() : '-';
    }

    function openQuoteModal($form) {
        var $modal = $('#rbfw-quote-checkout-modal');
        if (!$modal.length) {
            return;
        }
        $modal.find('[data-rbfw-quote-total]').text(getTotalText($form));
        $('body').addClass('rbfw-modal-open');
        $modal.show().attr('aria-hidden', 'false');
        $modal.data('rbfw-form', $form);
    }

    function closeQuoteModal() {
        $('#rbfw-quote-checkout-modal').hide().attr('aria-hidden', 'true').removeData('rbfw-form');
        $('body').removeClass('rbfw-modal-open');
    }

    function submitQuoteOrder($form) {
        var $modal = $('#rbfw-quote-checkout-modal');
        var $message = $modal.find('[data-rbfw-quote-message]');
        var $submit = $modal.find('[data-rbfw-quote-submit]');
        var name = $.trim($modal.find('#rbfw_quote_billing_name').val());
        var email = $.trim($modal.find('#rbfw_quote_billing_email').val());
        var phone = $.trim($modal.find('#rbfw_quote_billing_phone').val());

        if (!name || !email) {
            $message.addClass('error').text(window.rbfw_quote_i18n.required_fields);
            return;
        }

        $submit.addClass('is-loading').prop('disabled', true);
        $message.removeClass('error success').text('');

        var data = $form.serialize();
        data += '&action=rbfw_create_quote_order';
        data += '&quote_nonce=' + encodeURIComponent(window.rbfw_ajax_front.nonce_create_quote_order);
        data += '&rbfw_quote_billing_name=' + encodeURIComponent(name);
        data += '&rbfw_quote_billing_email=' + encodeURIComponent(email);
        data += '&rbfw_quote_billing_phone=' + encodeURIComponent(phone);

        $.post(window.rbfw_ajax_front.rbfw_ajaxurl, data, function(resp) {
            $submit.removeClass('is-loading').prop('disabled', false);
            if (resp && resp.success) {
                $message.addClass('success').text(resp.data.message || window.rbfw_quote_i18n.success);
                if (resp.data.redirect_url) {
                    window.location.href = resp.data.redirect_url;
                } else {
                    setTimeout(closeQuoteModal, 1500);
                }
            } else {
                $message.addClass('error').text((resp && resp.data && resp.data.message) ? resp.data.message : window.rbfw_quote_i18n.error);
            }
        }).fail(function() {
            $submit.removeClass('is-loading').prop('disabled', false);
            $message.addClass('error').text(window.rbfw_quote_i18n.error);
        });
    }

    $(document).ready(function() {
        if (getItemQuoteMode() === 'off') {
            return;
        }

        var $forms = $('.mp_rbfw_ticket_form');
        if (!$forms.length) {
            return;
        }

        $forms.each(function() {
            var $form = $(this);
            updateQuoteUI($form);
            $form.on('change input', function() {
                updateQuoteUI($form);
            });
        });

        $(document).on('click', '.rbfw_reserve_trip_btn', function(e) {
            e.preventDefault();
            var $form = $(this).closest('.mp_rbfw_ticket_form');
            openQuoteModal($form);
        });

        $(document).on('click', '[data-rbfw-quote-close]', closeQuoteModal);

        $(document).on('click', '[data-rbfw-quote-submit]', function() {
            var $form = $('#rbfw-quote-checkout-modal').data('rbfw-form');
            if ($form && $form.length) {
                submitQuoteOrder($form);
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#rbfw-quote-checkout-modal').is(':visible')) {
                closeQuoteModal();
            }
        });
    });
})(jQuery);
