/**
 * RBFW Inventory page – client-side pagination + detail-modal UX.
 *
 * This file is intentionally decoupled from mkb-admin.js: it only reads the
 * DOM that mkb-admin.js produces, so the existing filter / view-details AJAX
 * keeps working untouched. Pagination re-initialises automatically whenever the
 * table is replaced (filter / reset) via a MutationObserver.
 */
(function ($) {
    'use strict';

    var PER_PAGE = 10;

    var SVG_CHEV_L = '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
    var SVG_CHEV_R = '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';

    function i18n() {
        return (typeof window.rbfwInvI18n === 'object' && window.rbfwInvI18n) ? window.rbfwInvI18n : {
            showing: 'Showing %1$s–%2$s of %3$s entries',
            showing_all: 'Showing %s entries'
        };
    }

    function fmt(str, vals) {
        var i = 0;
        return String(str)
            .replace(/%(\d+)\$s/g, function (m, n) { return vals[parseInt(n, 10) - 1]; })
            .replace(/%s/g, function () { return vals[i++]; });
    }

    function paginate() {
        var $root = $('.rbfw_inv');
        if (!$root.length) { return; }

        var $table = $root.find('.rbfw_inventory_page_table_wrap table.rbfw_inv_table').first();
        var $info  = $root.find('.rbfw_inv_row_info');
        var $pager = $root.find('.rbfw_inv_pager');

        if (!$table.length) {
            $info.text('');
            $pager.empty();
            return;
        }

        var $rows = $table.find('tbody > tr').not('.rbfw_inv_empty_tr');
        var total = $rows.length;
        var t     = i18n();

        if (total === 0) {
            $info.text(fmt(t.showing_all, [0]));
            $pager.empty();
            return;
        }

        var pages   = Math.ceil(total / PER_PAGE);
        var current = 1;

        function buildPager() {
            $pager.empty();

            if (pages <= 1) {
                $('<button>', { type: 'button', 'class': 'rbfw_inv_pager_btn active', text: '1' }).appendTo($pager);
                return;
            }

            var $prev = $('<button>', { type: 'button', 'class': 'rbfw_inv_pager_btn' })
                .html(SVG_CHEV_L)
                .prop('disabled', current === 1)
                .on('click', function () { show(current - 1); });
            $pager.append($prev);

            for (var p = 1; p <= pages; p++) {
                (function (page) {
                    $('<button>', {
                        type: 'button',
                        'class': 'rbfw_inv_pager_btn' + (page === current ? ' active' : ''),
                        text: page
                    }).on('click', function () { show(page); }).appendTo($pager);
                })(p);
            }

            var $next = $('<button>', { type: 'button', 'class': 'rbfw_inv_pager_btn' })
                .html(SVG_CHEV_R)
                .prop('disabled', current === pages)
                .on('click', function () { show(current + 1); });
            $pager.append($next);
        }

        function show(page) {
            current = Math.min(Math.max(1, page), pages);
            var start = (current - 1) * PER_PAGE;
            var end   = start + PER_PAGE;

            $rows.each(function (i) {
                this.style.display = (i >= start && i < end) ? '' : 'none';
            });

            $info.text(fmt(t.showing, [start + 1, Math.min(end, total), total]));
            buildPager();
        }

        show(1);
    }

    function observeTable() {
        var wrap = document.querySelector('.rbfw_inv .rbfw_inventory_page_table_wrap');
        if (!wrap || typeof window.MutationObserver === 'undefined') { return; }

        var timer = null;
        var observer = new MutationObserver(function () {
            clearTimeout(timer);
            timer = setTimeout(paginate, 30);
        });
        observer.observe(wrap, { childList: true });
    }

    function modalIsActive() {
        return $.mage_modal && typeof $.mage_modal.isActive === 'function' && $.mage_modal.isActive();
    }

    function closeModal() {
        if (modalIsActive()) { $.mage_modal.close(); }
    }

    $(function () {
        if (!$('.rbfw_inv').length) { return; }

        paginate();
        observeTable();

        /* Custom close button inside the redesigned modal. */
        $(document).on('click', '.rbfw_inv_modal_close', function (e) {
            e.preventDefault();
            closeModal();
        });

        /* Click on the dark overlay (outside the modal box) closes it. */
        $(document).on('click', '.mage_blocker', function (e) {
            if (e.target === this) { closeModal(); }
        });

        /* Escape closes it. */
        $(document).on('keydown.rbfwInv', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) { closeModal(); }
        });
    });

})(jQuery);
