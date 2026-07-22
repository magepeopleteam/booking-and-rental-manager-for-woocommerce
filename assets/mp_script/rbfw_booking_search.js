/**
 * [rbfw_booking_search] front-end: date-range search, result cards,
 * multi-item quick add to the WooCommerce cart, floating booking bar.
 */
(function ($) {
    'use strict';

    $(function () {

        var $wraps = $('.rbfw_bsearch_wrap');
        if (!$wraps.length || typeof rbfw_bsearch_vars === 'undefined') {
            return;
        }

        $wraps.each(function () {
            var $wrap = $(this);
            var $start = $wrap.find('.rbfw_bsearch_start');
            var $end = $wrap.find('.rbfw_bsearch_end');

            if ($.fn.datepicker) {
                $start.datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0,
                    onSelect: function (dateText) {
                        var min = $.datepicker.parseDate('yy-mm-dd', dateText);
                        $end.datepicker('option', 'minDate', min);
                        if (!$end.val()) {
                            $end.datepicker('show');
                        }
                    }
                });
                $end.datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0
                });
            }
        });

        function showMsg($card, text) {
            $card.find('.rbfw_bsearch_msg').text(text).addClass('rbfw_bsearch_msg_show');
            window.setTimeout(function () {
                $card.find('.rbfw_bsearch_msg').removeClass('rbfw_bsearch_msg_show');
            }, 6000);
        }

        function currentView($wrap) {
            var stored = null;
            try { stored = window.localStorage.getItem('rbfw_bsearch_view'); } catch (e) { /* private mode */ }
            return (stored === 'list' || stored === 'grid') ? stored : ($wrap.data('style') === 'list' ? 'list' : 'grid');
        }

        function applyView($wrap, view) {
            var $grid = $wrap.find('.rbfw_bsearch_grid');
            $grid.toggleClass('rbfw_bsearch_view_list', view === 'list');
            $wrap.find('.rbfw_bsearch_view_list_btn').toggleClass('rbfw_bsearch_viewtoggle_active', view === 'list');
            $wrap.find('.rbfw_bsearch_view_grid_btn').toggleClass('rbfw_bsearch_viewtoggle_active', view !== 'list');
            try { window.localStorage.setItem('rbfw_bsearch_view', view); } catch (e) { /* private mode */ }
        }

        $(document).on('click', '.rbfw_bsearch_view_list_btn', function () {
            applyView($(this).closest('.rbfw_bsearch_wrap'), 'list');
        });
        $(document).on('click', '.rbfw_bsearch_view_grid_btn', function () {
            applyView($(this).closest('.rbfw_bsearch_wrap'), 'grid');
        });

        function updateBar($wrap, count, totalHtml) {
            var $bar = $wrap.find('.rbfw_bsearch_bar_float');
            $bar.find('.rbfw_bsearch_bar_count').text(count);
            if (totalHtml) {
                $bar.find('.rbfw_bsearch_bar_total').html(totalHtml);
            }
            if (count > 0) {
                $bar.slideDown(150);
            }
        }

        /* ---------- search ---------- */
        $(document).on('click', '.rbfw_bsearch_go', function () {
            var $wrap = $(this).closest('.rbfw_bsearch_wrap');
            var start = $wrap.find('.rbfw_bsearch_start').val();
            var end = $wrap.find('.rbfw_bsearch_end').val();
            var $results = $wrap.find('.rbfw_bsearch_results');

            if (!start || !end) {
                $results.html('<div class="rbfw_bsearch_empty">' + rbfw_bsearch_vars.txt_choose_dates + '</div>');
                return;
            }

            var location = $wrap.data('location') || $wrap.find('.rbfw_bsearch_location').val() || '';
            var type = $wrap.find('.rbfw_bsearch_type').val() || '';

            $results.html('<div class="rbfw_bsearch_loading"><span class="rbfw_bsearch_spinner"></span>' + rbfw_bsearch_vars.txt_searching + '</div>');

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_search_results',
                nonce: rbfw_bsearch_vars.nonce,
                start: start,
                end: end,
                location: location,
                type: type,
                types: $wrap.data('types') || '',
                show: $wrap.data('show') || 24,
                show_stock: $wrap.data('show-stock') || 'yes',
                show_qty: $wrap.data('show-qty') || 'yes',
                button_text: $wrap.data('button-text') || '',
                style: currentView($wrap)
            }).done(function (res) {
                if (res && res.success) {
                    $results.html(res.data.html);
                    $results.find('.rbfw_bsearch_grid').attr('data-columns', $wrap.data('columns') || 3);
                    applyView($wrap, currentView($wrap));
                } else {
                    $results.html('<div class="rbfw_bsearch_empty">' + ((res && res.data && res.data.message) ? res.data.message : rbfw_bsearch_vars.txt_error) + '</div>');
                }
            }).fail(function () {
                $results.html('<div class="rbfw_bsearch_empty">' + rbfw_bsearch_vars.txt_error + '</div>');
            });
        });

        /* ---------- qty stepper ---------- */
        $(document).on('click', '.rbfw_bsearch_qty_inc, .rbfw_bsearch_qty_dec', function () {
            var $input = $(this).siblings('.rbfw_bsearch_qty_input');
            var max = parseInt($input.attr('max'), 10) || 99;
            var val = parseInt($input.val(), 10) || 1;
            val += $(this).hasClass('rbfw_bsearch_qty_inc') ? 1 : -1;
            $input.val(Math.min(max, Math.max(1, val)));
        });

        /* ---------- "Select options" modal ---------- */
        function closeModal($wrap) {
            $wrap.find('.rbfw_bsearch_modal').attr('hidden', true).removeClass('rbfw_bsearch_modal_wide');
            $wrap.find('.rbfw_bsearch_modal_body').empty();
            $wrap.find('.rbfw_bsearch_modal_title').text('');
            $wrap.find('.rbfw_bsearch_modal_msg').text('').removeClass('rbfw_bsearch_modal_msg_show');
            $('body').removeClass('rbfw_bsearch_noscroll');
        }

        /* Tidy the rental booking details in the order-review table: drop the
           stray empty ":" label and any detail row with no value, so the
           summary reads as clean key/value pairs. */
        function tidyOrderReview($modal) {
            var $review = $modal.find('#order_review');
            if (!$review.length) {
                return;
            }
            $review.find('dl.variation dt').each(function () {
                var t = $(this).text().replace(/[\s:]/g, '');
                if (!t) {
                    $(this).remove();
                }
            });
            $review.find('.rbfw_room_cart_table tr').each(function () {
                if (!$(this).find('td').text().replace(/\s/g, '')) {
                    $(this).remove();
                }
            });
        }
        /* WooCommerce replaces the review table on every update_checkout — re-tidy. */
        $(document.body).on('updated_checkout', function () {
            $('.rbfw_bsearch_modal:not([hidden])').each(function () {
                tidyOrderReview($(this));
            });
        });

        /* Refresh the order review (totals, coupon rows, payment) after a coupon
           is applied/removed. Mirrors WooCommerce's own update_order_review AJAX,
           since checkout.js is not loaded on this page. */
        function refreshOrderReview($modal) {
            var $form = $modal.find('form.checkout');
            if (!$form.length || !rbfw_bsearch_vars.update_review_url) {
                return;
            }
            $modal.find('#order_review').addClass('rbfw_bsearch_checkout_processing');
            $.ajax({
                type: 'POST',
                url: rbfw_bsearch_vars.update_review_url,
                data: {
                    security: rbfw_bsearch_vars.update_review_nonce,
                    payment_method: $form.find('input[name="payment_method"]:checked').val(),
                    country: $form.find('#billing_country').val(),
                    state: $form.find('#billing_state').val(),
                    postcode: $form.find('input#billing_postcode').val(),
                    city: $form.find('#billing_city').val(),
                    address: $form.find('#billing_address_1').val(),
                    address_2: $form.find('#billing_address_2').val(),
                    post_data: $form.serialize()
                },
                dataType: 'json'
            }).done(function (data) {
                if (data && data.fragments) {
                    $.each(data.fragments, function (sel, html) {
                        var $target = $modal.find(sel);
                        if ($target.length) {
                            $target.replaceWith(html);
                        }
                    });
                }
                tidyOrderReview($modal);
            }).always(function () {
                $modal.find('#order_review').removeClass('rbfw_bsearch_checkout_processing');
            });
        }

        /* Coupon: toggle the entry form. */
        $(document).on('click', '.rbfw_bsearch_modal_body .showcoupon', function (e) {
            e.preventDefault();
            $(this).closest('.rbfw_bsearch_modal_body').find('.checkout_coupon').stop(true, true).slideToggle(200);
        });

        /* Coupon: apply. */
        $(document).on('submit', '.rbfw_bsearch_modal_body form.checkout_coupon', function (e) {
            e.preventDefault();
            var $cform = $(this);
            var $modal = $cform.closest('.rbfw_bsearch_modal');
            var code = $.trim($cform.find('input[name="coupon_code"]').val());
            if (!code || !rbfw_bsearch_vars.apply_coupon_url) {
                return;
            }
            var $btn = $cform.find('button');
            $btn.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: rbfw_bsearch_vars.apply_coupon_url,
                data: { security: rbfw_bsearch_vars.apply_coupon_nonce, coupon_code: code }
            }).done(function (resp) {
                $modal.find('.rbfw_bs_coupon_notice').remove();
                if (resp) {
                    $cform.before('<div class="rbfw_bs_coupon_notice">' + resp + '</div>');
                }
                var isError = /woocommerce-error|is-error/.test(String(resp));
                if (isError) {
                    return; // keep the form open so the customer can correct the code
                }
                $cform.find('input[name="coupon_code"]').val('');
                $cform.slideUp(150);
                refreshOrderReview($modal);
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });

        /* Coupon: remove (link WooCommerce renders in the review table). */
        $(document).on('click', '.rbfw_bsearch_modal_body .woocommerce-remove-coupon', function (e) {
            e.preventDefault();
            var $modal = $(this).closest('.rbfw_bsearch_modal');
            var coupon = $(this).data('coupon');
            if (!coupon || !rbfw_bsearch_vars.remove_coupon_url) {
                return;
            }
            $.ajax({
                type: 'POST',
                url: rbfw_bsearch_vars.remove_coupon_url,
                data: { security: rbfw_bsearch_vars.remove_coupon_nonce, coupon: coupon }
            }).done(function () {
                $modal.find('.rbfw_bs_coupon_notice').remove();
                refreshOrderReview($modal);
            });
        });

        /* ---------- Booking details popover (the "eye" icon) ---------- */
        function closeBarDetails($bar) {
            $bar.find('.rbfw_bsearch_bar_details').slideUp(140, function () { $(this).attr('hidden', true); });
            $bar.find('.rbfw_bsearch_bar_view').attr('aria-expanded', false);
        }

        $(document).on('click', '.rbfw_bsearch_bar_view', function () {
            var $btn = $(this);
            var $bar = $btn.closest('.rbfw_bsearch_bar_float');
            var $panel = $bar.find('.rbfw_bsearch_bar_details');

            if (!$panel.attr('hidden')) {
                closeBarDetails($bar);
                return;
            }

            $panel.removeAttr('hidden').hide().slideDown(140);
            $btn.attr('aria-expanded', true);

            var $body = $panel.find('.rbfw_bsearch_bar_details_body');
            $body.html('<div class="rbfw_bsearch_loading"><span class="rbfw_bsearch_spinner"></span>' + rbfw_bsearch_vars.txt_searching + '</div>');

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_bar_details',
                nonce: rbfw_bsearch_vars.nonce
            }).done(function (res) {
                if (res && res.success) {
                    $body.html(res.data.html);
                } else {
                    $body.html('<div class="rbfw_bsearch_bar_details_empty">' + ((res && res.data && res.data.message) ? res.data.message : rbfw_bsearch_vars.txt_error) + '</div>');
                }
            }).fail(function () {
                $body.html('<div class="rbfw_bsearch_bar_details_empty">' + rbfw_bsearch_vars.txt_error + '</div>');
            });
        });

        $(document).on('click', '.rbfw_bsearch_bar_details_close', function () {
            closeBarDetails($(this).closest('.rbfw_bsearch_bar_float'));
        });

        $(document).on('click', function (e) {
            var $panel = $('.rbfw_bsearch_bar_details:not([hidden])');
            if (!$panel.length) {
                return;
            }
            if ($(e.target).closest('.rbfw_bsearch_bar_details, .rbfw_bsearch_bar_view').length) {
                return;
            }
            closeBarDetails($panel.closest('.rbfw_bsearch_bar_float'));
        });

        /* ---------- Empty the booking (clear cart) ---------- */
        $(document).on('click', '.rbfw_bsearch_bar_empty', function () {
            var $btn = $(this);
            var $wrap = $btn.closest('.rbfw_bsearch_wrap');
            if ($btn.data('rbfw-bs-busy')) {
                return;
            }
            if (rbfw_bsearch_vars.txt_empty_confirm && !window.confirm(rbfw_bsearch_vars.txt_empty_confirm)) {
                return;
            }
            $btn.data('rbfw-bs-busy', true).addClass('rbfw_bsearch_bar_empty_busy');

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_empty_cart',
                nonce: rbfw_bsearch_vars.nonce
            }).done(function (res) {
                if (res && res.success) {
                    var $bar = $wrap.find('.rbfw_bsearch_bar_float');
                    $bar.find('.rbfw_bsearch_bar_count').text(res.data.count);
                    $bar.find('.rbfw_bsearch_bar_total').html(res.data.total);
                    if (!res.data.count) {
                        $bar.slideUp(150);
                        closeBarDetails($bar);
                    }
                    /* Reflect the emptied booking on any visible result cards. */
                    $wrap.find('.rbfw_bsearch_add.rbfw_bsearch_added').removeClass('rbfw_bsearch_added').each(function () {
                        var t = $(this).data('orig-text');
                        if (t) { $(this).text(t); }
                    });
                }
            }).always(function () {
                $btn.data('rbfw-bs-busy', false).removeClass('rbfw_bsearch_bar_empty_busy');
            });
        });

        /* ---------- Checkout in a modal (no iframe) ---------- */
        $(document).on('click', '.rbfw_bsearch_bar_checkout', function (e) {
            if (!rbfw_bsearch_vars.checkout_url) {
                return; // WooCommerce unavailable: fall back to the href
            }
            e.preventDefault();
            var $wrap = $(this).closest('.rbfw_bsearch_wrap');
            var $modal = $wrap.find('.rbfw_bsearch_modal');

            $modal.removeAttr('hidden').addClass('rbfw_bsearch_modal_wide');
            $('body').addClass('rbfw_bsearch_noscroll');
            $modal.find('.rbfw_bsearch_modal_title').text(rbfw_bsearch_vars.txt_placing ? 'Checkout' : 'Checkout');
            $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_loading"><span class="rbfw_bsearch_spinner"></span>' + rbfw_bsearch_vars.txt_loading + '</div>');

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_checkout_form',
                nonce: rbfw_bsearch_vars.nonce
            }).done(function (res) {
                if (res && res.success) {
                    $modal.find('.rbfw_bsearch_modal_body').html(res.data.html);
                    tidyOrderReview($modal);
                    /* Let WooCommerce wire up its own checkout (payment methods,
                       country/state, fragment refresh). checkout.js binds to
                       form.checkout on init_checkout. */
                    $(document.body).trigger('init_checkout');
                    $(document.body).trigger('update_checkout');
                } else {
                    $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_empty">' + ((res && res.data && res.data.message) ? res.data.message : rbfw_bsearch_vars.txt_error) + '</div>');
                }
            }).fail(function () {
                $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_empty">' + rbfw_bsearch_vars.txt_error + '</div>');
            });
        });

        /* Place order from the modal: post the checkout form to WooCommerce's
           native wc-ajax=checkout endpoint and follow its redirect. This runs
           the real order pipeline (rental meta, gateways, taxes) unchanged. */
        $(document).on('submit', '.rbfw_bsearch_modal_body form.checkout, .rbfw_bsearch_modal_body form.woocommerce-checkout', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $modal = $form.closest('.rbfw_bsearch_modal');

            if ($form.data('rbfw-bs-busy')) {
                return;
            }
            $form.data('rbfw-bs-busy', true);
            $form.addClass('rbfw_bsearch_checkout_processing');
            $modal.find('.rbfw_bsearch_modal_msg').text('').removeClass('rbfw_bsearch_modal_msg_show');

            $.ajax({
                type: 'POST',
                url: rbfw_bsearch_vars.checkout_url,
                data: $form.serialize(),
                dataType: 'json'
            }).done(function (res) {
                if (res && res.result === 'success') {
                    window.location.href = (res.redirect || '').replace('&#038;', '&');
                    return;
                }
                $form.data('rbfw-bs-busy', false);
                $form.removeClass('rbfw_bsearch_checkout_processing');
                if (res && res.messages) {
                    /* WooCommerce returns notice HTML; show it inside the modal. */
                    var $err = $modal.find('.rbfw_bsearch_checkout_notices');
                    if (!$err.length) {
                        $err = $('<div class="rbfw_bsearch_checkout_notices"></div>').prependTo($form);
                    }
                    $err.html(res.messages);
                    $modal.find('.rbfw_bsearch_modal_dialog').scrollTop(0);
                    $(document.body).trigger('checkout_error', [res.messages]);
                } else if (res && res.reload) {
                    window.location.reload();
                }
            }).fail(function (xhr) {
                $form.data('rbfw-bs-busy', false);
                $form.removeClass('rbfw_bsearch_checkout_processing');
                var text = xhr && xhr.responseText ? $('<div>').html(xhr.responseText).text().trim() : '';
                $modal.find('.rbfw_bsearch_modal_msg').text(text || rbfw_bsearch_vars.txt_error).addClass('rbfw_bsearch_modal_msg_show');
                $modal.find('.rbfw_bsearch_modal_dialog').scrollTop(0);
            });
        });

        $(document).on('click', '.rbfw_bsearch_openmodal', function (e) {
            e.preventDefault();
            var $link = $(this);
            var $wrap = $link.closest('.rbfw_bsearch_wrap');
            var $modal = $wrap.find('.rbfw_bsearch_modal');

            $modal.removeAttr('hidden');
            $('body').addClass('rbfw_bsearch_noscroll');
            $modal.find('.rbfw_bsearch_modal_title').text($link.closest('.rbfw_bsearch_card').find('.rbfw_bsearch_title').text());
            $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_loading"><span class="rbfw_bsearch_spinner"></span>' + rbfw_bsearch_vars.txt_searching + '</div>');

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_search_item_form',
                nonce: rbfw_bsearch_vars.nonce,
                item_id: $link.data('item'),
                start: $link.data('start') || '',
                end: $link.data('end') || ''
            }).done(function (res) {
                if (res && res.success) {
                    /* The plugin's booking scripts are delegated (document/body
                       handlers), so the injected form is fully interactive. */
                    var $body = $modal.find('.rbfw_bsearch_modal_body');
                    $body.html(res.data.html);

                    /* Inline single-day calendar is built by this global from
                       sd_script.js — re-run it for the injected markup. */
                    if (typeof window.datepicker_inline === 'function') {
                        window.datepicker_inline();
                    }
                    /* Location cards bind per-form — init the injected one. */
                    if (typeof window.rbfw_loc_cards_init === 'function') {
                        window.rbfw_loc_cards_init($body.find('#rbfw_loc_cards_wrap'));
                    }

                    /* The customer already chose dates in the search — carry
                       the pickup date into the form instead of asking again. */
                    var start = String($link.data('start') || '');
                    if (start) {
                        var $sdDate = $body.find('input[name="rbfw_bikecarsd_selected_date"]');
                        if ($sdDate.length) {
                            $body.find('.rbfw-bikecarsd-calendar').each(function () {
                                try { $(this).datepicker('setDate', new Date(start + 'T00:00:00')); } catch (err) { /* visual only */ }
                            });
                            $sdDate.val(start).trigger('change');
                        }
                    }

                    /* And the searched location: pick its card once the
                       date-wise stock arrives and the card unlocks. */
                    var loc = String($link.data('location') || '');
                    if (loc) {
                        var tries = 0;
                        (function pickLoc() {
                            var $card = $body.find('.rbfw_loc_card[data-loc="' + loc + '"]');
                            var $lwrap = $card.closest('.rbfw_loc_cards_wrap');
                            if ($card.length && !$lwrap.hasClass('rbfw_loc_waitdates') && !$card.prop('disabled')) {
                                $card.trigger('click');
                            } else if ($card.length && ++tries < 15) {
                                setTimeout(pickLoc, 350);
                            }
                        })();
                    }

                    /* Single-day (bike/car) items: once the date-wise duration
                       table loads, a single available duration is the obvious
                       choice — set its quantity to 1 so "Book Now" is ready
                       immediately instead of sitting disabled until the
                       customer notices they must click "+" first. Left alone
                       (still 0) whenever more than one duration is offered,
                       since then the choice is genuinely the customer's. */
                    var qtyTries = 0;
                    (function autoSelectSoleQty() {
                        var $qty = $body.find('.rbfw_bikecarsd_qty');
                        if ($qty.length === 1) {
                            if (parseInt($qty.val(), 10) === 0) {
                                $qty.val(1).trigger('input');
                            }
                        } else if (!$qty.length && ++qtyTries < 15) {
                            setTimeout(autoSelectSoleQty, 350);
                        }
                    })();
                } else {
                    $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_empty">' + ((res && res.data && res.data.message) ? res.data.message : rbfw_bsearch_vars.txt_error) + '</div>');
                }
            }).fail(function () {
                $modal.find('.rbfw_bsearch_modal_body').html('<div class="rbfw_bsearch_empty">' + rbfw_bsearch_vars.txt_error + '</div>');
            });
        });

        /* Add to cart from the modal form: post the whole form to our AJAX
           endpoint so the customer stays on the search page and can keep
           adding rentals. */
        $(document).on('submit', '.rbfw_bsearch_modal_body form', function (e) {
            var $form = $(this);
            var $submit = $form.find('[name="add-to-cart"]');
            var productId = $submit.val();
            if (!productId) {
                return; // not a booking form we understand
            }
            e.preventDefault();

            var $wrap = $form.closest('.rbfw_bsearch_wrap');
            var $modal = $wrap.find('.rbfw_bsearch_modal');
            var $msg = $modal.find('.rbfw_bsearch_modal_msg');

            if ($form.data('rbfw-bs-busy')) {
                return;
            }
            $form.data('rbfw-bs-busy', true);
            $submit.prop('disabled', true);
            $msg.text('').removeClass('rbfw_bsearch_modal_msg_show');

            $.post(
                rbfw_bsearch_vars.ajax_url,
                $form.serialize()
                    + '&action=rbfw_booking_modal_add'
                    + '&bsnonce=' + encodeURIComponent(rbfw_bsearch_vars.nonce)
                    + '&rbfw_modal_product=' + encodeURIComponent(productId)
            ).done(function (res, status, xhr) {
                $form.data('rbfw-bs-busy', false);
                $submit.prop('disabled', false);
                if (res && res.success) {
                    updateBar($wrap, res.data.count, res.data.total);
                    closeModal($wrap);
                } else {
                    var text = (res && res.data && res.data.message)
                        ? res.data.message
                        : $('<div>').html((typeof res === 'string') ? res : (xhr && xhr.responseText ? xhr.responseText : '')).text().trim();
                    $msg.text(text || rbfw_bsearch_vars.txt_error).addClass('rbfw_bsearch_modal_msg_show');
                    $modal.find('.rbfw_bsearch_modal_dialog').scrollTop(0);
                }
            }).fail(function (xhr) {
                $form.data('rbfw-bs-busy', false);
                $submit.prop('disabled', false);
                var text = xhr && xhr.responseText ? $('<div>').html(xhr.responseText).text().trim() : '';
                $msg.text(text || rbfw_bsearch_vars.txt_error).addClass('rbfw_bsearch_modal_msg_show');
                $modal.find('.rbfw_bsearch_modal_dialog').scrollTop(0);
            });
        });

        $(document).on('click', '.rbfw_bsearch_modal_close, .rbfw_bsearch_modal_overlay', function () {
            closeModal($(this).closest('.rbfw_bsearch_wrap'));
        });
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('.rbfw_bsearch_modal:not([hidden])').each(function () {
                    closeModal($(this).closest('.rbfw_bsearch_wrap'));
                });
            }
        });

        /* ---------- quick add ---------- */
        $(document).on('click', '.rbfw_bsearch_add', function () {
            var $btn = $(this);
            var $card = $btn.closest('.rbfw_bsearch_card');
            var $wrap = $btn.closest('.rbfw_bsearch_wrap');
            var qty = parseInt($card.find('.rbfw_bsearch_qty_input').val(), 10) || 1;

            if ($btn.hasClass('rbfw_bsearch_busy')) {
                return;
            }
            $btn.addClass('rbfw_bsearch_busy').data('orig-text', $btn.text()).text(rbfw_bsearch_vars.txt_adding);

            $.post(rbfw_bsearch_vars.ajax_url, {
                action: 'rbfw_booking_quick_add',
                nonce: rbfw_bsearch_vars.nonce,
                item_id: $card.data('item'),
                qty: qty,
                start: $btn.data('start'),
                end: $btn.data('end'),
                location: $btn.data('location') || ''
            }).done(function (res, status, xhr) {
                if (res && res.success) {
                    $btn.removeClass('rbfw_bsearch_busy').addClass('rbfw_bsearch_added').text(rbfw_bsearch_vars.txt_added);
                    window.setTimeout(function () {
                        $btn.removeClass('rbfw_bsearch_added').text($btn.data('orig-text'));
                    }, 2500);
                    updateBar($wrap, res.data.count, res.data.total);
                } else if (res && res.data && res.data.message) {
                    $btn.removeClass('rbfw_bsearch_busy').text($btn.data('orig-text'));
                    showMsg($card, res.data.message);
                } else {
                    /* Validators may wp_die() printing notice HTML instead of JSON. */
                    $btn.removeClass('rbfw_bsearch_busy').text($btn.data('orig-text'));
                    var raw = (typeof res === 'string') ? res : (xhr && xhr.responseText ? xhr.responseText : '');
                    var text = $('<div>').html(raw).text().trim();
                    showMsg($card, text || rbfw_bsearch_vars.txt_error);
                }
            }).fail(function (xhr) {
                $btn.removeClass('rbfw_bsearch_busy').text($btn.data('orig-text'));
                var text = '';
                if (xhr && xhr.responseText) {
                    text = $('<div>').html(xhr.responseText).text().trim();
                }
                showMsg($card, text || rbfw_bsearch_vars.txt_error);
            });
        });
    });
})(jQuery);
