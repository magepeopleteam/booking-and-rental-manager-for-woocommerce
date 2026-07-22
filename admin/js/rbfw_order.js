/**
 * RBFW Order List – client-side pagination, search, and animated detail expand.
 * Vanilla JS, no icon-font dependency (inline SVG). Reuses the existing
 * fetch_order_details AJAX endpoint so all server logic stays untouched.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
    }

    var CHEV_L = '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>';
    var CHEV_R = '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>';
    var ICON_X = '<svg class="rbfw_inv_ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>';

    ready(function () {
        var root = document.querySelector('.rbfw_ol');
        if (!root) { return; }
        var tbody = document.getElementById('order-list');
        if (!tbody) { return; }

        var searchInput = document.getElementById('search');
        var fbFieldSel  = document.getElementById('rbfw_ol_fb_field');
        var fbItemSel   = document.getElementById('rbfw_ol_fb_item');
        var fbTextWrap  = document.getElementById('rbfw_ol_fb_textwrap');
        var fbItemWrap  = document.getElementById('rbfw_ol_fb_itemwrap');
        var statusSel   = document.getElementById('rbfw_ol_filter_status');
        var fromInput   = document.getElementById('rbfw_ol_filter_from');
        var toInput     = document.getElementById('rbfw_ol_filter_to');
        var resetBtn    = document.getElementById('rbfw_ol_filter_reset');
        var info  = document.getElementById('row-info');
        var pager = document.getElementById('rbfw_ol_pager');
        var loader = document.getElementById('loader');

        var FB_PLACEHOLDERS = {
            name:  'Search by name...',
            order: 'Search by order ID...',
            phone: 'Search by phone...',
            email: 'Search by email...'
        };

        var PER_PAGE = 10;
        var current = 1;
        var search = '';
        var fbField = 'name';
        var fbItem = '';
        var statusFilter = '';
        var fromDate = '';
        var toDate = '';
        var fpFrom = null, fpTo = null;

        function orderRows() { return Array.prototype.slice.call(tbody.querySelectorAll('tr.order-row')); }
        function detailFor(row) { var n = row.nextElementSibling; return (n && n.classList.contains('order-details')) ? n : null; }

        function collapseRow(row) {
            var d = detailFor(row);
            if (d) {
                var a = d.querySelector('.rbfw_ol_detail_anim');
                if (a) { a.classList.remove('open'); }
                d.style.display = 'none';
            }
            row.classList.remove('rbfw_ol_open');
            var vb = row.querySelector('.rbfw_order_view_btn');
            if (vb) { vb.classList.remove('rbfw_ol_act_active'); }
        }

        function filtered() {
            return orderRows().filter(function (r) {
                if (fbField === 'item') {
                    if (fbItem && (r.getAttribute('data-item') || '').split(' ').indexOf(fbItem) === -1) { return false; }
                } else if (search) {
                    if ((r.getAttribute('data-' + fbField) || '').indexOf(search) === -1) { return false; }
                }
                if (statusFilter && (r.getAttribute('data-status') || '') !== statusFilter) { return false; }
                var start = r.getAttribute('data-start') || '';
                if (fromDate && (!start || start < fromDate)) { return false; }
                if (toDate && (!start || start > toDate)) { return false; }
                return true;
            });
        }

        function mkBtn(html, active) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'rbfw_ol_pager_btn' + (active ? ' active' : '');
            b.innerHTML = html;
            return b;
        }

        function buildPager(pages) {
            if (!pager) { return; }
            pager.innerHTML = '';
            if (pages <= 1) { pager.appendChild(mkBtn('1', true)); return; }

            var prev = mkBtn(CHEV_L, false);
            prev.disabled = current === 1;
            prev.addEventListener('click', function () { go(current - 1); });
            pager.appendChild(prev);

            for (var p = 1; p <= pages; p++) {
                (function (pg) {
                    var btn = mkBtn(String(pg), pg === current);
                    btn.addEventListener('click', function () { go(pg); });
                    pager.appendChild(btn);
                })(p);
            }

            var next = mkBtn(CHEV_R, false);
            next.disabled = current === pages;
            next.addEventListener('click', function () { go(current + 1); });
            pager.appendChild(next);
        }

        function go(p) {
            var pages = Math.max(1, Math.ceil(filtered().length / PER_PAGE));
            current = Math.min(Math.max(1, p), pages);
            render();
        }

        function render() {
            orderRows().forEach(function (r) { r.style.display = 'none'; collapseRow(r); });
            var list = filtered();
            var total = list.length;
            var pages = Math.max(1, Math.ceil(total / PER_PAGE));
            if (current > pages) { current = pages; }
            var start = (current - 1) * PER_PAGE;
            var end = start + PER_PAGE;
            list.slice(start, end).forEach(function (r) { r.style.display = ''; });
            if (info) {
                info.textContent = total
                    ? ('Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total + ' orders')
                    : 'No orders found';
            }
            buildPager(pages);
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                search = this.value.toLowerCase().trim();
                current = 1;
                render();
            });
        }
        if (fbFieldSel) {
            fbFieldSel.addEventListener('change', function () {
                fbField = this.value;
                if (fbField === 'item') {
                    if (fbTextWrap) { fbTextWrap.style.display = 'none'; }
                    if (fbItemWrap) { fbItemWrap.style.display = ''; }
                    search = '';
                    if (searchInput) { searchInput.value = ''; }
                    fbItem = fbItemSel ? fbItemSel.value : '';
                } else {
                    if (fbItemWrap) { fbItemWrap.style.display = 'none'; }
                    if (fbTextWrap) { fbTextWrap.style.display = ''; }
                    fbItem = '';
                    if (searchInput) {
                        searchInput.placeholder = FB_PLACEHOLDERS[fbField] || 'Search...';
                        search = searchInput.value.toLowerCase().trim();
                        searchInput.focus();
                    }
                }
                current = 1;
                render();
            });
        }
        if (fbItemSel) {
            fbItemSel.addEventListener('change', function () { fbItem = this.value; current = 1; render(); });
        }
        if (statusSel) {
            statusSel.addEventListener('change', function () { statusFilter = this.value; current = 1; render(); });
        }
        if (fromInput) {
            fromInput.addEventListener('change', function () { fromDate = this.value; current = 1; render(); });
        }
        if (toInput) {
            toInput.addEventListener('change', function () { toDate = this.value; current = 1; render(); });
        }
        if (typeof flatpickr !== 'undefined') {
            var fpBase = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'M j, Y', altInputClass: 'rbfw_ol_filter_date rbfw_ol_fp_alt', disableMobile: true };
            if (fromInput) {
                fpFrom = flatpickr(fromInput, Object.assign({}, fpBase, {
                    onChange: function (sel, dateStr) { fromDate = dateStr; current = 1; render(); }
                }));
            }
            if (toInput) {
                fpTo = flatpickr(toInput, Object.assign({}, fpBase, {
                    onChange: function (sel, dateStr) { toDate = dateStr; current = 1; render(); }
                }));
            }
        }
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                search = ''; statusFilter = ''; fromDate = ''; toDate = '';
                fbField = 'name'; fbItem = '';
                if (searchInput) { searchInput.value = ''; searchInput.placeholder = FB_PLACEHOLDERS.name; }
                if (fbFieldSel) { fbFieldSel.value = 'name'; }
                if (fbItemSel) { fbItemSel.value = ''; }
                if (fbItemWrap) { fbItemWrap.style.display = 'none'; }
                if (fbTextWrap) { fbTextWrap.style.display = ''; }
                if (statusSel) { statusSel.value = ''; }
                if (fpFrom) { fpFrom.clear(); } else if (fromInput) { fromInput.value = ''; }
                if (fpTo) { fpTo.clear(); } else if (toInput) { toInput.value = ''; }
                rbfwSyncAllDropdowns();
                current = 1;
                render();
            });
        }

        /* ── modern themed dropdowns (custom option list for the filter selects) ── */
        var DD_CHEV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>';
        var rbfwDropdowns = [];
        function rbfwCloseAllDropdowns(except) {
            rbfwDropdowns.forEach(function (dd) { if (dd !== except) { dd.close(); } });
        }
        function rbfwSyncAllDropdowns() { rbfwDropdowns.forEach(function (dd) { dd.sync(); }); }

        function rbfwEnhanceSelect(select) {
            if (!select || select.getAttribute('data-rbfw-dd') === '1') { return; }
            select.setAttribute('data-rbfw-dd', '1');
            var field = select.closest('.rbfw_ol_filter_field') || select.parentNode;

            var wrap = document.createElement('span');
            wrap.className = 'rbfw_ol_dd';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            select.classList.add('rbfw_ol_dd_native');

            var trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'rbfw_ol_dd_trigger';
            var lab = document.createElement('span');
            lab.className = 'rbfw_ol_dd_label';
            var chev = document.createElement('span');
            chev.className = 'rbfw_ol_dd_chev';
            chev.innerHTML = DD_CHEV;
            trigger.appendChild(lab);
            trigger.appendChild(chev);
            wrap.appendChild(trigger);

            var panel = document.createElement('div');
            panel.className = 'rbfw_ol_dd_panel';
            field.appendChild(panel);

            var ctx = { open: false };
            function syncLabel() {
                var o = select.options[select.selectedIndex];
                lab.textContent = o ? o.textContent : '';
            }
            function build() {
                panel.innerHTML = '';
                Array.prototype.forEach.call(select.options, function (opt) {
                    var it = document.createElement('div');
                    it.className = 'rbfw_ol_dd_opt' + (opt.value === select.value ? ' selected' : '');
                    it.textContent = opt.textContent;
                    it.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (select.value !== opt.value) {
                            select.value = opt.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        syncLabel();
                        ctx.close();
                    });
                    panel.appendChild(it);
                });
            }
            ctx.close = function () {
                if (!ctx.open) { return; }
                panel.classList.remove('open');
                trigger.classList.remove('open');
                ctx.open = false;
            };
            ctx.sync = syncLabel;
            trigger.addEventListener('click', function (e) {
                e.preventDefault(); e.stopPropagation();
                if (ctx.open) { ctx.close(); return; }
                rbfwCloseAllDropdowns(ctx);
                build();
                panel.classList.add('open');
                trigger.classList.add('open');
                ctx.open = true;
            });
            syncLabel();
            rbfwDropdowns.push(ctx);
        }

        Array.prototype.forEach.call(root.querySelectorAll('.rbfw_ol_filter_select'), rbfwEnhanceSelect);
        document.addEventListener('click', function () { rbfwCloseAllDropdowns(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { rbfwCloseAllDropdowns(); } });

        /* ── status edit popup ── */
        var statusModal = document.getElementById('rbfw_ol_status_modal');
        var statusCtx   = { row: null, btn: null, postId: null };

        function statusEls() {
            return {
                orderNo: document.getElementById('rbfw_ol_status_order_no'),
                select:  document.getElementById('rbfw_ol_status_select'),
                msg:     document.getElementById('rbfw_ol_status_msg'),
                save:    statusModal ? statusModal.querySelector('.rbfw_ol_status_save') : null
            };
        }

        function showStatusMsg(text, type) {
            var els = statusEls();
            if (!els.msg) { return; }
            els.msg.textContent = text;
            els.msg.className = 'rbfw_ol_status_msg ' + (type === 'error' ? 'is_error' : 'is_success');
            els.msg.style.display = 'block';
        }

        function openStatusModal(btn) {
            if (!statusModal) { return; }
            var els = statusEls();
            statusCtx.btn    = btn;
            statusCtx.row    = btn.closest('tr.order-row');
            statusCtx.postId = btn.getAttribute('data-post-id');
            if (els.orderNo) { els.orderNo.textContent = btn.getAttribute('data-order-no') || ''; }
            if (els.select)  { els.select.value = btn.getAttribute('data-status') || ''; }
            if (els.msg)     { els.msg.style.display = 'none'; els.msg.textContent = ''; }
            if (els.save)    { els.save.disabled = false; els.save.classList.remove('is_loading'); }
            statusModal.style.display = 'flex';
            statusModal.setAttribute('aria-hidden', 'false');
            if (els.select) { els.select.focus(); }
        }

        function closeStatusModal() {
            if (!statusModal) { return; }
            statusModal.style.display = 'none';
            statusModal.setAttribute('aria-hidden', 'true');
            statusCtx.row = statusCtx.btn = statusCtx.postId = null;
        }

        function saveStatus() {
            if (!statusModal || !statusCtx.postId) { return; }
            var els = statusEls();
            var newStatus = els.select ? els.select.value : '';
            if (!newStatus) { return; }
            if (els.save) { els.save.disabled = true; els.save.classList.add('is_loading'); }
            if (els.msg)  { els.msg.style.display = 'none'; }

            var body = 'action=rbfw_update_order_status' +
                       '&post_id=' + encodeURIComponent(statusCtx.postId) +
                       '&status='  + encodeURIComponent(newStatus) +
                       '&nonce='   + encodeURIComponent(rbfw_ajax_admin.nonce_update_order_status);

            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (els.save) { els.save.disabled = false; els.save.classList.remove('is_loading'); }
                    if (!res || !res.success) {
                        var emsg = (res && res.data && res.data.message) ? res.data.message : 'Update failed.';
                        showStatusMsg(emsg, 'error');
                        return;
                    }
                    var status = res.data.status;
                    var label  = res.data.status_label || status;
                    /* update the row badge + button so the list reflects the change live */
                    if (statusCtx.row) {
                        var badge = statusCtx.row.querySelector('.rbfw_ol_badge');
                        if (badge) {
                            badge.className = 'rbfw_ol_badge rbfw_ol_badge_' + status;
                            badge.textContent = label;
                        }
                    }
                    if (statusCtx.btn) { statusCtx.btn.setAttribute('data-status', status); }
                    showStatusMsg(res.data.message || 'Order status updated.', 'success');
                    setTimeout(closeStatusModal, 700);
                })
                .catch(function () {
                    if (els.save) { els.save.disabled = false; els.save.classList.remove('is_loading'); }
                    showStatusMsg('Network error. Please try again.', 'error');
                });
        }

        if (statusModal) {
            statusModal.addEventListener('click', function (e) {
                if (e.target.closest('.rbfw_ol_status_save'))   { e.preventDefault(); saveStatus(); return; }
                if (e.target.closest('.rbfw_ol_status_cancel') ||
                    e.target.closest('.rbfw_ol_status_modal_close') ||
                    e.target.classList.contains('rbfw_ol_status_modal_overlay')) {
                    e.preventDefault(); closeStatusModal();
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && statusModal.style.display === 'flex') { closeStatusModal(); }
            });
        }

        /* ── delete ( move to trash ) popup ── */
        var deleteModal = document.getElementById('rbfw_ol_delete_modal');
        var deleteCtx   = { row: null, postId: null };

        function deleteEls() {
            return {
                orderNo: document.getElementById('rbfw_ol_delete_order_no'),
                msg:     document.getElementById('rbfw_ol_delete_msg'),
                confirm: deleteModal ? deleteModal.querySelector('.rbfw_ol_delete_confirm') : null
            };
        }

        function showDeleteMsg(text, type) {
            var els = deleteEls();
            if (!els.msg) { return; }
            els.msg.textContent = text;
            els.msg.className = 'rbfw_ol_status_msg ' + (type === 'error' ? 'is_error' : 'is_success');
            els.msg.style.display = 'block';
        }

        function openDeleteModal(btn) {
            if (!deleteModal) { return; }
            var els = deleteEls();
            deleteCtx.row    = btn.closest('tr.order-row');
            deleteCtx.postId = btn.getAttribute('data-post-id');
            if (els.orderNo) { els.orderNo.textContent = btn.getAttribute('data-order-no') || ''; }
            if (els.msg)     { els.msg.style.display = 'none'; els.msg.textContent = ''; }
            if (els.confirm) { els.confirm.disabled = false; els.confirm.classList.remove('is_loading'); }
            deleteModal.style.display = 'flex';
            deleteModal.setAttribute('aria-hidden', 'false');
        }

        function closeDeleteModal() {
            if (!deleteModal) { return; }
            deleteModal.style.display = 'none';
            deleteModal.setAttribute('aria-hidden', 'true');
            deleteCtx.row = deleteCtx.postId = null;
        }

        function applyStats(stats, headerText, revenue) {
            if (stats) {
                Object.keys(stats).forEach(function (key) {
                    var card = root.querySelector('[data-stat="' + key + '"]');
                    if (!card) { return; }
                    var num = card.querySelector('.rbfw_ol_stat_num');
                    var amt = card.querySelector('.rbfw_ol_stat_amt');
                    if (num) { num.textContent = stats[key].count; }
                    if (amt) {
                        amt.innerHTML = stats[key].amount;
                        amt.className = 'rbfw_ol_stat_amt ' + (stats[key].pos ? 'pos' : 'zero');
                    }
                });
            }
            if (revenue) {
                Object.keys(revenue).forEach(function (rk) {
                    var el = root.querySelector('[data-rev="' + rk + '"]');
                    if (el) { el.innerHTML = revenue[rk]; }
                });
            }
            var headBadge = root.querySelector('.rbfw_ol_badge_count');
            if (headBadge && headerText) { headBadge.textContent = headerText; }
        }

        function confirmDelete() {
            if (!deleteModal || !deleteCtx.postId) { return; }
            var els = deleteEls();
            if (els.confirm) { els.confirm.disabled = true; els.confirm.classList.add('is_loading'); }
            if (els.msg)     { els.msg.style.display = 'none'; }

            var body = 'action=rbfw_delete_order' +
                       '&post_id=' + encodeURIComponent(deleteCtx.postId) +
                       '&nonce='   + encodeURIComponent(rbfw_ajax_admin.nonce_delete_order);

            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (els.confirm) { els.confirm.disabled = false; els.confirm.classList.remove('is_loading'); }
                    if (!res || !res.success) {
                        var emsg = (res && res.data && res.data.message) ? res.data.message : 'Delete failed.';
                        showDeleteMsg(emsg, 'error');
                        return;
                    }
                    /* remove the order row + its detail row, then refresh stats & pager */
                    var pid = deleteCtx.postId;
                    if (deleteCtx.row) { deleteCtx.row.parentNode.removeChild(deleteCtx.row); }
                    var detail = document.getElementById('order-details-' + pid);
                    if (detail) { detail.parentNode.removeChild(detail); }
                    applyStats(res.data.stats, res.data.header, res.data.revenue);
                    closeDeleteModal();
                    render();
                })
                .catch(function () {
                    if (els.confirm) { els.confirm.disabled = false; els.confirm.classList.remove('is_loading'); }
                    showDeleteMsg('Network error. Please try again.', 'error');
                });
        }

        if (deleteModal) {
            deleteModal.addEventListener('click', function (e) {
                if (e.target.closest('.rbfw_ol_delete_confirm')) { e.preventDefault(); confirmDelete(); return; }
                if (e.target.closest('.rbfw_ol_delete_cancel') ||
                    e.target.closest('.rbfw_ol_status_modal_close') ||
                    e.target.classList.contains('rbfw_ol_status_modal_overlay')) {
                    e.preventDefault(); closeDeleteModal();
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && deleteModal.style.display === 'flex') { closeDeleteModal(); }
            });
        }

        /* ── full order edit popup ── */
        var editModal = document.getElementById('rbfw_ol_edit_modal');
        var editBody  = document.getElementById('rbfw_ol_edit_body');
        var editCtx   = { postId: null };
        var editPickers = [];

        function eoEls() {
            return {
                msg:  document.getElementById('rbfw_ol_edit_msg'),
                save: editModal ? editModal.querySelector('.rbfw_ol_edit_save') : null
            };
        }
        function eoVal(sel) { var el = editBody ? editBody.querySelector(sel) : null; return el ? el.value : ''; }
        function eoMoney(n) {
            var sym = (typeof rbfw_ajax_admin !== 'undefined' && rbfw_ajax_admin.currency_symbol) ? rbfw_ajax_admin.currency_symbol : '';
            n = Math.round((n + Number.EPSILON) * 100) / 100;
            return n.toFixed(2) + (sym ? (' ' + sym) : '');
        }
        function eoCalcDays() {
            var sd = eoVal('#rbfw_eo_start_date'), ed = eoVal('#rbfw_eo_end_date');
            var fb = parseInt(eoVal('#rbfw_eo_total_days'), 10) || 1;
            if (!sd || !ed) { return fb; }
            var d = Math.round((new Date(ed) - new Date(sd)) / 86400000);
            return d > 0 ? d : 1;
        }
        function eoRecalc() {
            if (!editBody) { return; }
            var days = eoCalcDays();
            var resource = 0;
            editBody.querySelectorAll('.rbfw_eo_svc_qty').forEach(function (inp) {
                var qty = parseInt(inp.value, 10) || 0;
                if (qty <= 0) { return; }
                var price = parseFloat(inp.getAttribute('data-price')) || 0;
                resource += (inp.getAttribute('data-type') === 'day_wise') ? price * qty * days : price * qty;
            });
            var duration = parseFloat(eoVal('#rbfw_eo_duration')) || 0;
            var mgmt     = parseFloat(eoVal('#rbfw_eo_management')) || 0;
            var disc     = parseFloat(eoVal('#rbfw_eo_discount')) || 0;
            var dep      = parseFloat(eoVal('#rbfw_eo_deposit')) || 0;
            var lineTotal = Math.max(0, duration + resource + mgmt - disc);
            var setT = function (sel, v) { var el = editBody.querySelector(sel); if (el) { el.textContent = v; } };
            setT('#rbfw_eo_resource', eoMoney(resource));
            setT('#rbfw_eo_subtotal', eoMoney(lineTotal));
            setT('#rbfw_eo_total', eoMoney(lineTotal + dep));
        }
        function showEoMsg(text, type) {
            var els = eoEls();
            if (!els.msg) { return; }
            els.msg.textContent = text;
            els.msg.className = 'rbfw_ol_status_msg rbfw_ol_edit_msg ' + (type === 'error' ? 'is_error' : 'is_success');
            els.msg.style.display = 'block';
        }
        function destroyEditPickers() {
            editPickers.forEach(function (fp) { try { fp.destroy(); } catch (e) {} });
            editPickers = [];
        }
        function initEditPickers() {
            if (typeof flatpickr === 'undefined' || !editBody) { return; }
            // static:true keeps the calendar anchored to the input inside the fixed modal
            // (default body-append + scroll math positions it off-screen on a scrolled page).
            editBody.querySelectorAll('.rbfw_eo_fp_date').forEach(function (el) {
                editPickers.push(flatpickr(el, {
                    dateFormat: 'Y-m-d', altInput: true, altFormat: 'M j, Y',
                    altInputClass: 'rbfw_eo_fp_alt', disableMobile: true, static: true, onChange: eoRecalc
                }));
            });
            editBody.querySelectorAll('.rbfw_eo_fp_time').forEach(function (el) {
                editPickers.push(flatpickr(el, {
                    enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true,
                    disableMobile: true, static: true, onChange: eoRecalc
                }));
            });
        }
        function openEditModal(btn) {
            if (!editModal || !editBody) { return; }
            editCtx.postId = btn.getAttribute('data-post-id');
            var els = eoEls();
            if (els.msg)  { els.msg.style.display = 'none'; els.msg.textContent = ''; }
            if (els.save) { els.save.disabled = false; els.save.classList.remove('is_loading'); }
            destroyEditPickers();
            editBody.innerHTML = '<div class="rbfw_ol_edit_loading"><span class="rbfw_ol_spinner"></span></div>';
            editModal.style.display = 'flex';
            editModal.setAttribute('aria-hidden', 'false');

            var body = 'action=rbfw_get_order_edit_form&post_id=' + encodeURIComponent(editCtx.postId) +
                       '&nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_get_order_edit_form);
            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res || !res.success) {
                        editBody.innerHTML = '<p class="rbfw_ol_edit_err">' + ((res && res.data && res.data.message) ? res.data.message : 'Failed to load.') + '</p>';
                        return;
                    }
                    editBody.innerHTML = res.data.html;
                    initEditPickers();
                    eoRecalc();
                })
                .catch(function () { editBody.innerHTML = '<p class="rbfw_ol_edit_err">Network error.</p>'; });
        }
        function closeEditModal() {
            if (!editModal) { return; }
            destroyEditPickers();
            editModal.style.display = 'none';
            editModal.setAttribute('aria-hidden', 'true');
            editCtx.postId = null;
        }
        function refreshDetail(postId) {
            var content = document.querySelector('#order-details-' + postId + ' .order-details-content');
            if (!content) { return; }
            var body = 'action=fetch_order_details&post_id=' + encodeURIComponent(postId) +
                       '&nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_fetch_order_details);
            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    content.innerHTML = html;
                    content.setAttribute('data-loaded', '1');
                    injectClose(document.getElementById('order-details-' + postId), postId);
                });
        }
        function saveEdit() {
            if (!editModal || !editCtx.postId) { return; }
            var els = eoEls();
            if (els.save) { els.save.disabled = true; els.save.classList.add('is_loading'); }
            if (els.msg)  { els.msg.style.display = 'none'; }

            var parts = [
                'action=rbfw_save_order_edit',
                'post_id=' + encodeURIComponent(editCtx.postId),
                'nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_save_order_edit),
                'start_date=' + encodeURIComponent(eoVal('#rbfw_eo_start_date')),
                'start_time=' + encodeURIComponent(eoVal('#rbfw_eo_start_time')),
                'end_date=' + encodeURIComponent(eoVal('#rbfw_eo_end_date')),
                'end_time=' + encodeURIComponent(eoVal('#rbfw_eo_end_time')),
                'duration_cost=' + encodeURIComponent(eoVal('#rbfw_eo_duration')),
                'management_price=' + encodeURIComponent(eoVal('#rbfw_eo_management')),
                'discount_amount=' + encodeURIComponent(eoVal('#rbfw_eo_discount')),
                'security_deposit=' + encodeURIComponent(eoVal('#rbfw_eo_deposit')),
                'billing_first_name=' + encodeURIComponent(eoVal('#rbfw_eo_b_first')),
                'billing_last_name=' + encodeURIComponent(eoVal('#rbfw_eo_b_last')),
                'billing_email=' + encodeURIComponent(eoVal('#rbfw_eo_b_email')),
                'billing_phone=' + encodeURIComponent(eoVal('#rbfw_eo_b_phone')),
                'billing_company=' + encodeURIComponent(eoVal('#rbfw_eo_b_company')),
                'billing_address_1=' + encodeURIComponent(eoVal('#rbfw_eo_b_addr1')),
                'billing_address_2=' + encodeURIComponent(eoVal('#rbfw_eo_b_addr2')),
                'billing_city=' + encodeURIComponent(eoVal('#rbfw_eo_b_city')),
                'billing_state=' + encodeURIComponent(eoVal('#rbfw_eo_b_state')),
                'billing_postcode=' + encodeURIComponent(eoVal('#rbfw_eo_b_postcode')),
                'billing_country=' + encodeURIComponent(eoVal('#rbfw_eo_b_country'))
            ];
            editBody.querySelectorAll('.rbfw_eo_svc_qty').forEach(function (inp) {
                var c = inp.getAttribute('data-cat'), s = inp.getAttribute('data-svc');
                var q = parseInt(inp.value, 10) || 0;
                parts.push('service_qty[' + encodeURIComponent(c) + '][' + encodeURIComponent(s) + ']=' + encodeURIComponent(q));
            });
            editBody.querySelectorAll('.rbfw_eo_regf').forEach(function (inp) {
                var k = inp.getAttribute('data-key');
                parts.push('regf[' + encodeURIComponent(k) + ']=' + encodeURIComponent(inp.value));
            });

            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: parts.join('&')
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (els.save) { els.save.disabled = false; els.save.classList.remove('is_loading'); }
                    if (!res || !res.success) {
                        showEoMsg((res && res.data && res.data.message) ? res.data.message : 'Update failed.', 'error');
                        return;
                    }
                    var pid = editCtx.postId;
                    var vbtn = document.querySelector('.rbfw_order_view_btn[data-post-id="' + pid + '"]');
                    var row  = vbtn ? vbtn.closest('tr.order-row') : null;
                    if (row) {
                        var totalCell = row.querySelector('.rbfw_ol_td_total');
                        if (totalCell && res.data.total_html) { totalCell.innerHTML = res.data.total_html; }
                        var nameCell = row.querySelector('.rbfw_ol_td_name');
                        if (nameCell && res.data.billing) { nameCell.textContent = res.data.billing; }
                        var dates = row.querySelectorAll('.rbfw_ol_td_date');
                        if (dates[1] && res.data.start_display) { dates[1].textContent = res.data.start_display; }
                        if (dates[2] && res.data.end_display) { dates[2].textContent = res.data.end_display; }
                    }
                    refreshDetail(pid);
                    showEoMsg(res.data.message || 'Order updated.', 'success');
                    setTimeout(closeEditModal, 800);
                })
                .catch(function () {
                    if (els.save) { els.save.disabled = false; els.save.classList.remove('is_loading'); }
                    showEoMsg('Network error. Please try again.', 'error');
                });
        }
        if (editModal) {
            if (editBody) { editBody.addEventListener('input', eoRecalc); }
            editModal.addEventListener('click', function (e) {
                if (e.target.closest('.rbfw_ol_edit_save')) { e.preventDefault(); saveEdit(); return; }
                // Only the close (×) and Cancel buttons close this modal — an outside
                // (overlay) click or Esc must NOT close it, to avoid losing unsaved edits.
                if (e.target.closest('.rbfw_ol_edit_cancel') ||
                    e.target.closest('.rbfw_ol_edit_close')) {
                    e.preventDefault(); closeEditModal();
                }
            });
        }

        /* ── detail expand / collapse with slide animation ── */
        function injectClose(detailRow, postId) {
            var head = detailRow.querySelector('.rbfw_order_meta_box_head');
            if (head && !head.querySelector('.rbfw_ol_detail_close')) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'rbfw_ol_detail_close';
                b.setAttribute('data-post-id', postId);
                b.innerHTML = ICON_X;
                head.appendChild(b);
            }
        }

        function openDetail(row, postId) {
            var detailRow = document.getElementById('order-details-' + postId);
            if (!detailRow) { return; }
            var anim = detailRow.querySelector('.rbfw_ol_detail_anim');
            var content = detailRow.querySelector('.order-details-content');
            detailRow.style.display = 'table-row';
            row.classList.add('rbfw_ol_open');
            var vb = row.querySelector('.rbfw_order_view_btn');
            if (vb) { vb.classList.add('rbfw_ol_act_active'); }

            if (content.getAttribute('data-loaded') === '1') {
                requestAnimationFrame(function () { anim.classList.add('open'); });
                return;
            }

            if (loader) { loader.style.display = 'flex'; }
            var body = 'action=fetch_order_details&post_id=' + encodeURIComponent(postId) +
                       '&nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_fetch_order_details);
            fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    content.innerHTML = html;
                    content.setAttribute('data-loaded', '1');
                    injectClose(detailRow, postId);
                    if (loader) { loader.style.display = 'none'; }
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () { anim.classList.add('open'); });
                    });
                })
                .catch(function () { if (loader) { loader.style.display = 'none'; } });
        }

        function closeDetail(row, postId) {
            var detailRow = document.getElementById('order-details-' + postId);
            if (!detailRow) { return; }
            var anim = detailRow.querySelector('.rbfw_ol_detail_anim');
            if (anim) { anim.classList.remove('open'); }
            row.classList.remove('rbfw_ol_open');
            var vb = row.querySelector('.rbfw_order_view_btn');
            if (vb) { vb.classList.remove('rbfw_ol_act_active'); }
            setTimeout(function () { detailRow.style.display = 'none'; }, 380);
        }

        root.addEventListener('click', function (e) {
            var statusBtn = e.target.closest ? e.target.closest('.rbfw_order_status_edit_btn') : null;
            if (statusBtn) {
                e.preventDefault();
                openStatusModal(statusBtn);
                return;
            }
            var delBtn = e.target.closest ? e.target.closest('.rbfw_order_delete_btn') : null;
            if (delBtn) {
                e.preventDefault();
                openDeleteModal(delBtn);
                return;
            }
            var editBtn = e.target.closest ? e.target.closest('.rbfw_ol_edit_order_btn') : null;
            if (editBtn) {
                e.preventDefault();
                openEditModal(editBtn);
                return;
            }
            var view = e.target.closest ? e.target.closest('.rbfw_order_view_btn') : null;
            if (view) {
                e.preventDefault();
                var postId = view.getAttribute('data-post-id');
                var row = view.closest('tr.order-row');
                var detailRow = document.getElementById('order-details-' + postId);
                var isOpen = detailRow && detailRow.style.display !== 'none' &&
                    detailRow.querySelector('.rbfw_ol_detail_anim').classList.contains('open');
                if (isOpen) { closeDetail(row, postId); } else { openDetail(row, postId); }
                return;
            }
            var close = e.target.closest ? e.target.closest('.rbfw_ol_detail_close') : null;
            if (close) {
                e.preventDefault();
                var pid = close.getAttribute('data-post-id');
                var vbtn = document.querySelector('.rbfw_order_view_btn[data-post-id="' + pid + '"]');
                var prow = vbtn ? vbtn.closest('tr.order-row') : null;
                if (prow) { closeDetail(prow, pid); }
            }
        });

        /* ----------------------------------------------------------------- *
         *  Export (CSV / PDF) — item-wise + month-range download.
         * ----------------------------------------------------------------- */
        (function initExport() {
            var exportBtn   = document.getElementById('rbfw_ol_export_btn');
            var exportModal = document.getElementById('rbfw_ol_export_modal');
            if (!exportBtn || !exportModal) { return; }

            var itemSel   = document.getElementById('rbfw_ol_export_item');
            var statusSelE = document.getElementById('rbfw_ol_export_status');
            var fromMonth = document.getElementById('rbfw_ol_export_from');
            var toMonth   = document.getElementById('rbfw_ol_export_to');

            function openExport() {
                // Pre-fill from the currently applied on-page filters as a convenience.
                if (itemSel && fbItem) { itemSel.value = fbItem; }
                if (statusSelE && statusFilter) { statusSelE.value = statusFilter; }
                // Use flex (not block) so the shared .rbfw_ol_status_modal
                // centering (align/justify center) applies — same as the other modals.
                exportModal.style.display = 'flex';
                exportModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('rbfw_ol_modal_open');
            }

            function closeExport() {
                exportModal.style.display = 'none';
                exportModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('rbfw_ol_modal_open');
            }

            exportBtn.addEventListener('click', function (e) { e.preventDefault(); openExport(); });

            exportModal.addEventListener('click', function (e) {
                if (!e.target) { return; }
                if (e.target.closest('.rbfw_ol_export_close') ||
                    e.target.closest('.rbfw_ol_export_cancel') ||
                    e.target.classList.contains('rbfw_ol_status_modal_overlay')) {
                    e.preventDefault();
                    closeExport();
                    return;
                }
                // Active-state styling for the format cards.
                var fmt = e.target.closest('.rbfw_ol_export_fmt');
                if (fmt) {
                    var radio = fmt.querySelector('input[type="radio"]');
                    if (radio) { radio.checked = true; }
                    exportModal.querySelectorAll('.rbfw_ol_export_fmt').forEach(function (c) {
                        c.classList.toggle('is-active', c === fmt);
                    });
                    return;
                }
                // Trigger the download.
                if (e.target.closest('.rbfw_ol_export_do')) {
                    e.preventDefault();
                    doExport();
                }
            });

            // Keep card highlight in sync if the radio itself receives focus/change.
            exportModal.querySelectorAll('input[name="rbfw_ol_export_format"]').forEach(function (r) {
                r.addEventListener('change', function () {
                    exportModal.querySelectorAll('.rbfw_ol_export_fmt').forEach(function (c) {
                        c.classList.toggle('is-active', c.contains(r) && r.checked);
                    });
                });
            });

            function doExport() {
                if (typeof rbfw_ajax_admin === 'undefined' || !rbfw_ajax_admin.admin_post_url) { return; }
                var fmtEl = exportModal.querySelector('input[name="rbfw_ol_export_format"]:checked');
                var params = {
                    action: 'rbfw_export_orders',
                    format: fmtEl ? fmtEl.value : 'csv',
                    item_id: itemSel ? itemSel.value : '0',
                    status: statusSelE ? statusSelE.value : '',
                    from_month: fromMonth ? fromMonth.value : '',
                    to_month: toMonth ? toMonth.value : '',
                    _wpnonce: rbfw_ajax_admin.nonce_export_orders || ''
                };
                var qs = Object.keys(params).map(function (k) {
                    return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
                }).join('&');

                // Navigate to the download endpoint. The server streams a file
                // (CSV/PDF) with a Content-Disposition: attachment header, so the
                // current admin page is not replaced.
                window.location.href = rbfw_ajax_admin.admin_post_url + '?' + qs;
                closeExport();
            }
        })();

        /* ----------------------------------------------------------------- *
         *  Export Settings accordion — toggle export columns (CSV / PDF).
         * ----------------------------------------------------------------- */
        (function initExportSettings() {
            var exs = document.getElementById('rbfw_ol_exs');
            if (!exs) { return; }
            var head    = exs.querySelector('.rbfw_ol_exs_head');
            var body    = document.getElementById('rbfw_ol_exs_body');
            var allBtn  = exs.querySelector('.rbfw_ol_exs_all');
            var noneBtn = exs.querySelector('.rbfw_ol_exs_none');
            var msg     = document.getElementById('rbfw_ol_exs_msg');

            function checks() { return Array.prototype.slice.call(exs.querySelectorAll('.rbfw_ol_exs_cb')); }

            function setOpen(open) {
                body.hidden = !open;
                exs.classList.toggle('is-open', open);
                if (head) { head.setAttribute('aria-expanded', open ? 'true' : 'false'); }
            }
            if (head) { head.addEventListener('click', function () { setOpen(body.hidden); }); }

            function showMsg(text, ok) {
                if (!msg) { return; }
                msg.textContent = text;
                msg.className = 'rbfw_ol_exs_msg ' + (ok ? 'is_success' : 'is_error');
                msg.style.display = 'inline-block';
            }

            function doSave() {
                if (typeof rbfw_ajax_admin === 'undefined' || !rbfw_ajax_admin.nonce_save_export_settings) {
                    // Stale page / missing localisation — make the failure visible
                    // instead of silently doing nothing.
                    showMsg('Could not save — please reload the page and try again.', false);
                    return;
                }
                var parts = [
                    'action=rbfw_save_export_settings',
                    'nonce=' + encodeURIComponent(rbfw_ajax_admin.nonce_save_export_settings)
                ];
                checks().forEach(function (c) {
                    parts.push('columns[' + encodeURIComponent(c.getAttribute('data-col')) + ']=' + (c.checked ? '1' : '0'));
                });
                showMsg('Saving…', true);
                fetch(rbfw_ajax_admin.rbfw_ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: parts.join('&')
                }).then(function (r) { return r.json(); }).then(function (res) {
                    if (res && res.success) { showMsg((res.data && res.data.message) || 'Export settings saved.', true); }
                    else { showMsg((res && res.data && res.data.message) || 'Could not save.', false); }
                }).catch(function () {
                    showMsg('Network error. Please try again.', false);
                });
            }

            // Debounced auto-save: toggling a column persists automatically so the
            // next CSV / PDF export always reflects the current selection.
            var saveTimer = null;
            function scheduleSave() {
                if (saveTimer) { clearTimeout(saveTimer); }
                saveTimer = setTimeout(doSave, 500);
            }

            checks().forEach(function (c) { c.addEventListener('change', scheduleSave); });

            if (allBtn) { allBtn.addEventListener('click', function () { checks().forEach(function (c) { c.checked = true; }); scheduleSave(); }); }
            if (noneBtn) { noneBtn.addEventListener('click', function () { checks().forEach(function (c) { c.checked = false; }); scheduleSave(); }); }
        })();

        render();
    });
})();
