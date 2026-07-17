/* RBFW Modern Editor JS */
(function ($) {
    'use strict';

    var cfg = window.rbfwModernEditor || {};
    var $wrap, postId;

    /* ── Init ────────────────────────────────────────────────── */
    $(function () {
        $wrap  = $('.rbfw-me-wrap');
        if (! $wrap.length) return;
        postId = parseInt($wrap.data('post-id'), 10) || 0;

        initTabs();
        initRateRows();
        initToggles();
        initTemplate();
        initThumbnail();
        initServiceImages();
        initServiceCategoryActions();
        initServiceCategoryHeader();
        initResortImages();
        initGallery();
        initAdditionalGallery();
        initCategories();
        initFeatures();
        initFeatureAccordion();
        initTitleSync();
        initEditorTabsInToolbar();
        initEditorMediaBtn();
        initPricingTypeSwitch();
        initParticularSwitch();
        initMdPricing();
        initRelatedPicker();
        initFaq();
        initTerm();
        initOffDays();
        initPublishDropdown();
        initSave();
        initPageLoader();
    });

    function initPageLoader() {
        var $loader = $wrap.find('.rbfw-me-page-loader');
        if (!$loader.length) {
            $wrap.removeClass('is-loading');
            return;
        }

        var hidden = false;

        function hideLoader() {
            if (hidden || !$wrap.hasClass('is-loading')) {
                return;
            }
            hidden = true;
            $wrap.removeClass('is-loading');
            $loader.fadeOut(280, function () {
                $(this).remove();
            });
        }

        $(window).on('load.rbfwMeLoader', hideLoader);
        setTimeout(hideLoader, 6000);

        if (document.readyState === 'complete') {
            setTimeout(hideLoader, 150);
        }
    }

    /* ── Stepper ─────────────────────────────────────────────── */
    function initTabs() {
        var $tabs = $wrap.find('.rbfw-me-tab');
        var total = $tabs.length;
        var storageKey = 'rbfw_me_active_tab_' + (postId || 'new');

        function parseTabFromHash() {
            var hash = window.location.hash || '';
            var match = hash.match(/#\/rental\/(?:edit\/(\d+)|new)\/(\w+)/);
            if (!match || !match[2]) {
                return null;
            }
            var hashPostId = match[1] ? parseInt(match[1], 10) : 0;
            if (postId && hashPostId && hashPostId !== postId) {
                return null;
            }
            return match[2];
        }

        function tabIndexFromKey(tabKey) {
            if (!tabKey) {
                return -1;
            }
            return $tabs.index($tabs.filter('[data-tab="' + tabKey + '"]'));
        }

        function updateHash(tabKey) {
            var hash = postId
                ? '#/rental/edit/' + postId + '/' + tabKey
                : '#/rental/new/' + tabKey;
            if (window.location.hash !== hash) {
                history.replaceState(null, '', hash);
            }
        }

        function getInitialTabIndex() {
            var idx = tabIndexFromKey(parseTabFromHash());
            if (idx >= 0) {
                return idx;
            }

            try {
                idx = tabIndexFromKey(localStorage.getItem(storageKey));
                if (idx >= 0) {
                    return idx;
                }
            } catch (e) {}

            var activeIdx = $tabs.index($tabs.filter('.is-active'));
            return activeIdx >= 0 ? activeIdx : 0;
        }

        function goToStep(idx, options) {
            options = options || {};
            if (idx < 0 || idx >= total) return;
            $tabs.each(function (i) {
                $(this)
                    .toggleClass('is-active', i === idx)
                    .toggleClass('is-done',   i < idx)
                    .attr('aria-selected', i === idx ? 'true' : 'false');
            });
            var tabKey = $tabs.eq(idx).data('tab');
            $wrap.find('.rbfw-me-panel').removeClass('is-active');
            $wrap.find('.rbfw-me-panel[data-panel="' + tabKey + '"]').addClass('is-active');
            $wrap.find('.rbfw-me-step-counter').text('Step ' + (idx + 1) + ' of ' + total);
            $wrap.find('.rbfw-me-step-prev').prop('disabled', idx === 0);
            var $next = $wrap.find('.rbfw-me-step-next');
            if (idx === total - 1) {
                $next.html('<span class="dashicons dashicons-yes"></span> Finish');
            } else {
                $next.html('Next <span class="dashicons dashicons-arrow-right-alt2"></span>');
            }
            if (options.updateHash !== false) {
                updateHash(tabKey);
                try {
                    localStorage.setItem(storageKey, tabKey);
                } catch (e) {}
            }
        }

        $wrap.on('click', '.rbfw-me-tab', function () {
            goToStep($tabs.index(this));
        });
        $wrap.on('click', '.rbfw-me-step-next', function () {
            goToStep($tabs.index($tabs.filter('.is-active')) + 1);
        });
        $wrap.on('click', '.rbfw-me-step-prev', function () {
            goToStep($tabs.index($tabs.filter('.is-active')) - 1);
        });

        $(window).on('hashchange.rbfwMeTabs', function () {
            var idx = tabIndexFromKey(parseTabFromHash());
            if (idx >= 0) {
                goToStep(idx, { updateHash: false });
            }
        });

        goToStep(getInitialTabIndex());
    }

    /* ── Rate rows (enable/disable with toggle) ──────────────── */
    function initRateRows() {
        $wrap.on('change', '.rbfw-me-rate-row .rbfw-me-toggle__input', function () {
            $(this).closest('.rbfw-me-rate-row').toggleClass('is-enabled', this.checked);
        });
    }

    /* ── Generic reveal toggles ──────────────────────────────── */
    function initToggles() {
        $wrap.on('change', '.rbfw-me-toggle--reveal', function () {
            var target = $(this).data('reveals');
            if (target) {
                $(target).toggleClass('rbfw-me-hidden', ! this.checked);
            }
        });

        // Collapsible "How Location Inventory & Price works" rules block (starts collapsed).
        $wrap.on('click', '.rbfw-me-loc-inv-collapse__head', function () {
            var $box  = $(this).closest('.rbfw-me-loc-inv-collapse');
            var $body = $box.children('.rbfw-me-loc-inv-collapse__body');
            if ($box.hasClass('is-collapsed')) {
                $box.removeClass('is-collapsed');
                $body.hide().stop(true, true).slideDown(200, function () {
                    $body.css('display', ''); // restore stylesheet display
                });
            } else {
                $body.stop(true, true).slideUp(200, function () {
                    $box.addClass('is-collapsed');
                    $body.css('display', ''); // let the .is-collapsed CSS rule hide it
                });
            }
        });

        // Location pick-up / drop-off: mirror the checkbox group into a hidden
        // comma-separated value (one hidden input per .rbfw-me-loc-group) that
        // the modern AJAX save reads.
        $wrap.on('change', '.rbfw-me-loc-checkbox', function () {
            var $group = $(this).closest('.rbfw-me-loc-group');
            var selected = [];
            $group.find('.rbfw-me-loc-checkbox:checked').each(function () {
                selected.push($(this).data('loc'));
            });
            $group.find('.rbfw-me-loc-hidden').val(selected.join(','));
        });

        /* ── Inline location CRUD (add / rename / delete taxonomy terms) ── */
        function locEsc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }

        function locAjax(action, data, $manage, cb) {
            data.action = action;
            data.nonce  = $manage.data('nonce');
            $.post(window.ajaxurl, data, function (resp) {
                if (resp && resp.success) {
                    rebuildLocations(resp.data.locations);
                    if (cb) cb();
                } else {
                    window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                }
            }).fail(function () { window.alert((rbfwModernEditor_i18n('Request failed.') || 'Request failed.')); });
        }

        // Rebuild the manage list + every pick-up/drop-off checkbox group from the
        // authoritative location list returned by the server, preserving the
        // current per-item selection (and dropping any deleted values).
        function rebuildLocations(locations) {
            locations = locations || [];
            var $list = $wrap.find('.rbfw-me-loc-list').empty();
            locations.forEach(function (loc) {
                $list.append(
                    '<li class="rbfw-me-loc-row" data-term-id="' + loc.term_id + '" data-value="' + locEsc(loc.value) + '">' +
                        '<span class="rbfw-me-loc-row__name">' + locEsc(loc.name) + '</span>' +
                        '<span class="rbfw-me-loc-row__actions">' +
                            '<button type="button" class="rbfw-me-loc-edit" title="' + (rbfwModernEditor_i18n('Rename') || 'Rename') + '"><i class="fas fa-pen" aria-hidden="true"></i></button>' +
                            '<button type="button" class="rbfw-me-loc-delete" title="' + (rbfwModernEditor_i18n('Delete') || 'Delete') + '"><i class="fas fa-trash-can" aria-hidden="true"></i></button>' +
                        '</span>' +
                    '</li>'
                );
            });

            $wrap.find('.rbfw-me-loc-group').each(function () {
                var $group  = $(this);
                var current = ($group.find('.rbfw-me-loc-hidden').val() || '').split(',').filter(Boolean);
                var values  = [];
                var $cb     = $group.find('.rbfw-me-loc-checkboxes').empty();
                locations.forEach(function (loc) {
                    values.push(loc.value);
                    var checked = current.indexOf(loc.value) !== -1 ? ' checked' : '';
                    $cb.append(
                        '<label class="rbfw-me-loc-label">' +
                            '<input type="checkbox" class="rbfw-me-loc-checkbox" data-loc="' + locEsc(loc.value) + '"' + checked + ' />' +
                            '<span>' + locEsc(loc.name) + '</span>' +
                        '</label>'
                    );
                });
                // Keep the hidden CSV in sync (remove values whose location is gone).
                $group.find('.rbfw-me-loc-hidden').val(current.filter(function (v) { return values.indexOf(v) !== -1; }).join(','));
                $group.find('.rbfw-me-loc-empty').toggleClass('rbfw-me-hidden', locations.length > 0);
            });
        }

        $wrap.on('click', '.rbfw-me-loc-add-btn', function () {
            var $manage = $(this).closest('.rbfw-me-loc-manage');
            var $input  = $manage.find('.rbfw-me-loc-new');
            var name    = $.trim($input.val());
            if (! name) { $input.trigger('focus'); return; }
            locAjax('rbfw_location_add', { name: name }, $manage, function () { $input.val(''); });
        });

        $wrap.on('keypress', '.rbfw-me-loc-new', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).closest('.rbfw-me-loc-manage').find('.rbfw-me-loc-add-btn').trigger('click');
            }
        });

        // Edit → open the rename modal (no browser prompt).
        $wrap.on('click', '.rbfw-me-loc-edit', function () {
            var $row   = $(this).closest('.rbfw-me-loc-row');
            var $modal = $wrap.find('#rbfw-me-loc-modal');
            $modal.find('#rbfw-me-loc-modal-term-id').val($row.data('term-id'));
            $modal.find('#rbfw-me-loc-modal-input').val($row.find('.rbfw-me-loc-row__name').text());
            $modal.addClass('is-open');
            setTimeout(function () { $modal.find('#rbfw-me-loc-modal-input').trigger('focus').select(); }, 50);
        });

        $wrap.on('click', '#rbfw-me-loc-modal .rbfw-me-faq-modal__close, #rbfw-me-loc-modal .rbfw-me-faq-modal__backdrop', function () {
            $wrap.find('#rbfw-me-loc-modal').removeClass('is-open');
        });

        $wrap.on('click', '#rbfw-me-loc-modal-save', function () {
            var $modal  = $wrap.find('#rbfw-me-loc-modal');
            var $manage = $wrap.find('.rbfw-me-loc-manage');
            var termId  = $modal.find('#rbfw-me-loc-modal-term-id').val();
            var name    = $.trim($modal.find('#rbfw-me-loc-modal-input').val());
            if (! name) { $modal.find('#rbfw-me-loc-modal-input').trigger('focus'); return; }
            locAjax('rbfw_location_update', { term_id: termId, name: name }, $manage, function () {
                $modal.removeClass('is-open');
            });
        });

        $wrap.on('keypress', '#rbfw-me-loc-modal-input', function (e) {
            if (e.which === 13) { e.preventDefault(); $wrap.find('#rbfw-me-loc-modal-save').trigger('click'); }
        });

        $wrap.on('click', '.rbfw-me-loc-delete', function () {
            var $row    = $(this).closest('.rbfw-me-loc-row');
            var $manage = $(this).closest('.rbfw-me-loc-manage');
            if (! window.confirm((rbfwModernEditor_i18n('Delete this location? Items using it will no longer reference it.') || 'Delete this location? Items using it will no longer reference it.'))) return;
            locAjax('rbfw_location_delete', { term_id: $row.data('term-id') }, $manage);
        });

        // Old-style toggle: value attribute stores DB state ('on'/'off'); sync it then
        // delegate all show/hide to syncTimelyUI scoped to the containing panel.
        // stopPropagation prevents rbfw-admin-input.js (loaded globally) from reading
        // the already-updated value and inverting the toggle a second time.
        $wrap.on('click', '[name="manage_inventory_as_timely"]', function (e) {
            e.stopPropagation();
            $(this).val(this.checked ? 'on' : 'off');
            syncTimelyUI($(this).closest('.rbfw-me-panel'));
        });

        $wrap.on('click', '[name="enable_specific_duration"]', function (e) {
            e.stopPropagation();
            $(this).val(this.checked ? 'on' : 'off');
            syncTimelyUI($(this).closest('.rbfw-me-panel'));
        });
    }

    // Sync all timely-dependent UI elements based on current toggle states.
    // .rbfw_item_stock_quantity   — stock qty + specific-duration section (show when timely=on)
    // .duration_enable columns    — start/end time cols (show when timely=on AND specific=on)
    // .duration_disable columns   — duration/d_type cols (show when timely=on AND specific=off)
    // .rbfw_without_time_inventory — stock/day col (show when timely=off)
    function syncTimelyUI($pricing) {
        var isTimely   = $pricing.find('[name="manage_inventory_as_timely"]').is(':checked');
        var isSpecific = $pricing.find('[name="enable_specific_duration"]').is(':checked');

        var $stockSection = $pricing.find('.rbfw_time_inventory.rbfw_item_stock_quantity');
        if (isTimely) {
            $stockSection.removeClass('rbfw_hide').css('display', 'block');
            $pricing.find('.rbfw_item_quantiry_duration').css('display', '');
        } else {
            $stockSection.addClass('rbfw_hide').css('display', 'none');
            $pricing.find('.rbfw_item_quantiry_duration').css('display', 'none');
        }

        if (isTimely) { $pricing.find('.rbfw_without_time_inventory').hide(); }
        else          { $pricing.find('.rbfw_without_time_inventory').show(); }

        if (isTimely && isSpecific)  { $pricing.find('.rbfw_time_inventory.duration_enable').show(); }
        else                          { $pricing.find('.rbfw_time_inventory.duration_enable').hide(); }

        if (isTimely && !isSpecific) { $pricing.find('.rbfw_time_inventory.duration_disable').show(); }
        else                          { $pricing.find('.rbfw_time_inventory.duration_disable').hide(); }

        // Only toggle the single-day Enable Time Picker for bike_car_sd / appointment.
        // For multiple_items the .rbfw_bike_car_sd_wrapper is hidden by applyType and must stay hidden.
        var _meType = $pricing.find('#rbfw_item_type').val();
        if ( _meType === 'bike_car_sd' || _meType === 'appointment' ) {
            if (isTimely && isSpecific)  { $pricing.find('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').hide(); }
            else                          { $pricing.find('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper').show(); }
        }
    }

    /* ── Update service category enable toggle label ────────────── */
    function initServiceCategoryHeader() {
        var $section = $wrap.find('.additional-service-item-price > section:not(.bg-light)');
        if ( ! $section.length ) return;
        // Update label text
        $section.find('> div > label').text('Enable category-wise extra services');
        // Update description
        $section.find('> div > p').text('Enable or disable category-wise additional services for this item.');
    }

    /* ── Move service category sort/remove into title section ─── */
    function initServiceCategoryActions() {
        function moveActions($table) {
            $table.find('tr').each(function () {
                var $tr      = $(this);
                var $titleSec = $tr.find('.service_category_title');
                var $actionTd = $tr.find('td:last-child');
                if ( ! $titleSec.length || ! $actionTd.length ) return;
                // Already moved
                if ( $titleSec.find('.rbfw-svc-cat-actions').length ) return;

                var $actions = $(
                    '<div class="rbfw-svc-cat-actions">' +
                      '<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>' +
                      '<span class="button tr_remove"><i class="fas fa-trash-can"></i></span>' +
                    '</div>'
                );

                // Wire tr_remove to the original onclick
                $actions.find('.tr_remove').on('click', function () {
                    $tr.remove();
                });

                $titleSec.append($actions);
                $actionTd.hide();
            });
        }

        // Run on existing tables
        $wrap.find('.rbfw_service_category_table').each(function () {
            moveActions($(this));
        });

        // Re-run when new category rows are added (add-service-category button)
        $wrap.on('click', '.add-service-category', function () {
            setTimeout(function () {
                $wrap.find('.rbfw_service_category_table').each(function () {
                    moveActions($(this));
                });
            }, 50);
        });
    }

    /* ── Extra-service image upload ──────────────────────────── */
    function initServiceImages() {
        // Click on preview square → open media picker
        $wrap.on('click', '.rbfw_pricing_table .rbfw_service_image_preview', function () {
            var $preview = $(this);
            var $row     = $preview.closest('tr');
            var bkp      = wp.media.editor.send.attachment;
            wp.media.editor.send.attachment = function (props, attachment) {
                $row.find('.rbfw_service_image_preview img').remove();
                $row.find('.rbfw_service_image_preview').append('<img src="' + attachment.url + '" />');
                $row.find('.rbfw_service_image').val(attachment.id);
                wp.media.editor.send.attachment = bkp;
            };
            wp.media.editor.open($preview[0]);
            return false;
        });

        // Click on remove button (hidden but kept for JS hook) → clear image
        $wrap.on('click', '.rbfw_pricing_table .rbfw_remove_service_image_btn', function () {
            var $row = $(this).closest('tr');
            $row.find('.rbfw_service_image_preview img').remove();
            $row.find('.rbfw_service_image').val('');
        });
    }

    /* ── Resort room type image upload ──────────────────────────── */
    function initResortImages() {
        // Move each row's remove button inside its preview so :has() + absolute positioning works
        function setupResortRow($row) {
            var $preview   = $row.find('.rbfw_room_type_image_preview');
            var $removeBtn = $row.find('.rbfw_remove_room_type_image_btn');
            if ($preview.length && $removeBtn.length && !$removeBtn.parent().is($preview)) {
                $preview.append($removeBtn);
            }
        }
        $wrap.find('.rbfw_resort_price_table_row').each(function () { setupResortRow($(this)); });

        // When a new resort row is added, set it up
        $wrap.on('click', '#add-resort-type-row', function () {
            setTimeout(function () {
                setupResortRow($wrap.find('.rbfw_resort_price_table_row:last'));
            }, 50);
        });

        // Click preview → open media picker (ignore clicks on the remove button)
        $wrap.on('click', '.rbfw_resort_price_table .rbfw_room_type_image_preview', function (e) {
            if ($(e.target).closest('.rbfw_remove_room_type_image_btn').length) return;
            var $preview = $(this);
            var $row     = $preview.closest('tr');
            var bkp      = wp.media.editor.send.attachment;
            wp.media.editor.send.attachment = function (props, attachment) {
                $preview.find('img').remove();
                $preview.prepend('<img src="' + attachment.url + '" />');
                $row.find('.rbfw_room_image').val(attachment.id);
                wp.media.editor.send.attachment = bkp;
            };
            wp.media.editor.open($preview[0]);
            return false;
        });

        // Click remove → clear image
        $wrap.on('click', '.rbfw_resort_price_table .rbfw_remove_room_type_image_btn', function (e) {
            e.stopPropagation();
            var $row = $(this).closest('tr');
            $row.find('.rbfw_room_type_image_preview img').remove();
            $row.find('.rbfw_room_image').val('');
        });
    }

    /* ── Gallery ─────────────────────────────────────────────── */
    function initGallery() {
        var galleryFrame;

        $wrap.on('click', '.rbfw-me-gallery-upload', function () {
            if (galleryFrame) { galleryFrame.open(); return; }
            galleryFrame = wp.media({
                title:    'Select Gallery Images',
                button:   { text: 'Add to Gallery' },
                multiple: true,
                library:  { type: 'image' },
            });
            galleryFrame.on('select', function () {
                var selection = galleryFrame.state().get('selection');
                selection.each(function (attachment) {
                    var a = attachment.toJSON();
                    var html = '<div class="rbfw-me-gallery-image">'
                        + '<button type="button" class="rbfw-me-gallery-remove" onclick="jQuery(this).closest(\'.rbfw-me-gallery-image\').remove()">'
                        + '<i class="fas fa-trash-can"></i></button>'
                        + '<img src="' + a.url + '" alt="" />'
                        + '<input type="hidden" name="rbfw_gallery_images[]" value="' + parseInt( a.id, 10 ) + '" />'
                        + '</div>';
                    $wrap.find('.rbfw-me-gallery-list').append(html);
                });
            });
            galleryFrame.open();
        });

        $wrap.on('click', '.rbfw-me-gallery-clear', function () {
            $wrap.find('.rbfw-me-gallery-list .rbfw-me-gallery-image').remove();
        });
    }

    /* ── Additional Gallery (Muffin template) ───────────────────── */
    function initAdditionalGallery() {
        var addGalleryFrame;

        // Show/hide the card based on the currently selected template
        function syncVisibility() {
            var tpl  = $wrap.find('.rbfw-me-tpl-value').val();
            var $card = $wrap.find('.rbfw-me-additional-gallery-card');
            if ( tpl === 'Muffin' ) {
                $card.removeClass('rbfw-me-hidden');
            } else {
                $card.addClass('rbfw-me-hidden');
            }
        }

        // Run immediately on page load, then again whenever a template card is clicked
        syncVisibility();
        $wrap.on('click', '.rbfw-me-tpl-card', function () {
            setTimeout( syncVisibility, 0 );
        });

        // Upload button
        $wrap.on('click', '.rbfw-me-add-gallery-upload', function () {
            if ( addGalleryFrame ) { addGalleryFrame.open(); return; }
            addGalleryFrame = wp.media({
                title:    'Select Additional Gallery Images',
                button:   { text: 'Add to Gallery' },
                multiple: true,
                library:  { type: 'image' },
            });
            addGalleryFrame.on('select', function () {
                var selection = addGalleryFrame.state().get('selection');
                selection.each(function ( attachment ) {
                    var a    = attachment.toJSON();
                    var html = '<div class="rbfw-me-gallery-image">'
                        + '<button type="button" class="rbfw-me-gallery-remove" onclick="jQuery(this).closest(\'.rbfw-me-gallery-image\').remove()">'
                        + '<i class="fas fa-trash-can"></i></button>'
                        + '<img src="' + a.url + '" alt="" />'
                        + '<input type="hidden" name="rbfw_gallery_images_additional[]" value="' + parseInt( a.id, 10 ) + '" />'
                        + '</div>';
                    $wrap.find('.rbfw-me-add-gallery-list').append(html);
                });
            });
            addGalleryFrame.open();
        });

        // Clear all
        $wrap.on('click', '.rbfw-me-add-gallery-clear', function () {
            $wrap.find('.rbfw-me-add-gallery-list .rbfw-me-gallery-image').remove();
        });
    }

    /* ── Template picker ─────────────────────────────────────── */
    function initTemplate() {
        $wrap.on('click', '.rbfw-me-tpl-card', function () {
            $wrap.find('.rbfw-me-tpl-card').removeClass('is-selected');
            $(this).addClass('is-selected');
            $wrap.find('.rbfw-me-tpl-value').val($(this).data('tpl'));
        });

        $wrap.find('.rbfw-me-tpl-card__img').each(function () {
            var $img = $(this).find('img');
            if (!$img.length || $(this).find('.rbfw-me-tpl-preview-btn').length) return;
            $(this).append(
                '<button type="button" class="rbfw-me-tpl-preview-btn" title="' + (rbfwModernEditor_i18n('Preview') || 'Preview') + '">' +
                    '<span class="dashicons dashicons-visibility"></span>' +
                '</button>'
            );
        });

        var $overlay;
        function getOverlay() {
            if (!$overlay || !$overlay.length) {
                $overlay = $(
                    '<div class="rbfw-me-tpl-overlay">' +
                        '<div class="rbfw-me-tpl-overlay__inner">' +
                            '<button type="button" class="rbfw-me-tpl-overlay__close" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '<img src="" alt="" />' +
                        '</div>' +
                    '</div>'
                );
                $wrap.append($overlay);
                $overlay.on('click', function (e) {
                    if (e.target === this) closePreview();
                });
                $overlay.find('.rbfw-me-tpl-overlay__close').on('click', closePreview);
                $(document).on('keydown.rbfwTplPreview', function (e) {
                    if (e.key === 'Escape') closePreview();
                });
            }
            return $overlay;
        }
        function openPreview(src, alt) {
            var $o = getOverlay();
            $o.find('img').attr('src', src).attr('alt', alt || '');
            $o.addClass('is-open');
        }
        function closePreview() {
            if ($overlay && $overlay.length) $overlay.removeClass('is-open');
        }

        $wrap.on('click', '.rbfw-me-tpl-preview-btn', function (e) {
            e.stopPropagation();
            var $card = $(this).closest('.rbfw-me-tpl-card');
            var $img  = $card.find('.rbfw-me-tpl-card__img img');
            var src   = $img.attr('src');
            var alt   = $img.attr('alt');
            if (src) openPreview(src, alt);
        });
    }

    /* ── Featured image ──────────────────────────────────────── */
    function initThumbnail() {
        var mediaFrame;

        $wrap.on('click', '.rbfw-me-thumb-set', function (e) {
            e.preventDefault();
            if (mediaFrame) { mediaFrame.open(); return; }
            mediaFrame = wp.media({
                title:    rbfwModernEditor_i18n('Set Featured Image') || 'Set Featured Image',
                button:   { text: rbfwModernEditor_i18n('Use this image') || 'Use this image' },
                multiple: false,
            });
            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                $wrap.find('.rbfw-me-thumb-id').val(attachment.id);
                var $preview = $wrap.find('.rbfw-me-thumb-preview');
                $preview.addClass('has-image').html('<img src="' + attachment.url + '" alt="" />');
                $wrap.find('.rbfw-me-thumb-set').text('Change Image');
                if (! $wrap.find('.rbfw-me-thumb-remove').length) {
                    $wrap.find('.rbfw-me-thumb-actions').append(
                        '<button type="button" class="rbfw-me-btn rbfw-me-btn--danger rbfw-me-thumb-remove">Remove</button>'
                    );
                }
            });
            mediaFrame.open();
        });

        $wrap.on('click', '.rbfw-me-thumb-remove', function () {
            $wrap.find('.rbfw-me-thumb-id').val('');
            $wrap.find('.rbfw-me-thumb-preview').removeClass('has-image').empty();
            $wrap.find('.rbfw-me-thumb-set').text('Set Featured Image');
            $(this).remove();
        });
    }

    /* ── Publish dropdown chevron ────────────────────────────── */
    function initPublishDropdown() {
        $wrap.on('click', '.rbfw-me-publish-chevron', function (e) {
            e.stopPropagation();
            var $dd = $(this).siblings('.rbfw-me-publish-dropdown');
            var isHidden = $dd.prop('hidden');
            $dd.prop('hidden', !isHidden);
        });
        $(document).on('click', function () {
            $wrap.find('.rbfw-me-publish-dropdown').prop('hidden', true);
        });
        $wrap.on('click', '.rbfw-me-publish-dropdown', function (e) {
            e.stopPropagation();
        });
    }

    /* ── Save ────────────────────────────────────────────────── */
    function initSave() {
        $wrap.on('click', '.rbfw-me-save-draft', function () {
            doSave('draft');
        });

        $wrap.on('click', '.rbfw-me-publish', function () {
            var isPublished = $(this).data('published') === 1 || $(this).data('published') === '1';
            doSave(isPublished ? 'publish' : 'publish');
        });
    }

    /* ── Navigate to field: switch tab + scroll ─────────────────── */
    function navigateToField($field) {
        // Find nearest panel — check direct parent first, then walk up
        var $panel = $field.closest('.rbfw-me-panel[data-panel]');

        // If not found directly (e.g. classic-editor table rows), check
        // whether any panel contains the element
        if ( ! $panel.length ) {
            $wrap.find('.rbfw-me-panel[data-panel]').each(function () {
                if ( $.contains(this, $field[0]) ) {
                    $panel = $(this);
                    return false;
                }
            });
        }

        if ( $panel.length && ! $panel.hasClass('is-active') ) {
            var $tab = $wrap.find('.rbfw-me-tab[data-tab="' + $panel.data('panel') + '"]');
            if ( $tab.length ) $tab.trigger('click');
        }

        setTimeout(function () {
            var el = $field[0];
            if ( el && el.scrollIntoView ) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            if ( $field.is('input, textarea, select') ) $field.focus();
        }, 250);
    }

    /* ── Inline field validation ─────────────────────────────── */
    function showFieldError($field, msg) {
        clearFieldError($field);
        var $err = $('<span class="rbfw-me-field-error">' + msg + '</span>');
        $field.addClass('rbfw-me-field-invalid').after($err);
        $field.one('input change keyup', function () { clearFieldError($field); });
    }
    function clearFieldError($field) {
        $field.removeClass('rbfw-me-field-invalid');
        $field.next('.rbfw-me-field-error').remove();
    }

    function getMainSdPriceRows() {
        var $sdBodies = $wrap.find('.rbfw_bike_car_sd_price_table_body').filter(function () {
            var $body = $(this);
            if ( $body.closest('.mp_hidden_content').length ) {
                return false;
            }
            if ( $body.closest('.sessional_price_single_day, .sessional_price_resort, .rbfw_seasonal_price_config_wrapper').length ) {
                return false;
            }
            if ( $body.closest('.rbfw_bike_car_sd_price_table_sp').length ) {
                return false;
            }
            return true;
        });

        return $sdBodies.find('tr.rbfw_bike_car_sd_price_table_row');
    }

    function getMainResortPriceRows() {
        return $wrap.find('.rbfw_resort_price_config_wrapper .rbfw_resort_price_table:not(.rbfw_resort_price_table_sp) .rbfw_resort_price_table_body .rbfw_resort_price_table_row');
    }

    function showPricingTableWarning(message, $anchor, removeOnClick) {
        if ( ! $anchor || ! $anchor.length ) {
            return;
        }

        if ( ! $anchor.prev('.rbfw-me-table-warning').length ) {
            $anchor.before(
                '<div class="rbfw-me-table-warning">' +
                  '<span class="dashicons dashicons-warning"></span>' +
                  message +
                '</div>'
            );
        }

        if ( removeOnClick ) {
            $wrap.one(removeOnClick, function () {
                $wrap.find('.rbfw-me-table-warning').remove();
            });
        }

        var $pricingPanel = $wrap.find('.rbfw-me-panel[data-panel="pricing"]');
        if ( $pricingPanel.length && ! $pricingPanel.hasClass('is-active') ) {
            $wrap.find('.rbfw-me-tab[data-tab="pricing"]').trigger('click');
        }

        setTimeout(function () {
            var el = $anchor[0] || $pricingPanel[0];
            if ( el ) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 250);
    }

    function validateSdPricingRows(rentType, errors) {
        var $sdRows = getMainSdPriceRows();
        // Read the live checkbox state (matches syncTimelyUI's show/hide logic) rather
        // than the value attribute, so validation never disagrees with the visible columns.
        var isTimely = $wrap.find('input[type="checkbox"][name="manage_inventory_as_timely"]').is(':checked');
        var isSpecific = $wrap.find('input[type="checkbox"][name="enable_specific_duration"]').is(':checked');
        var requireQty = rentType === 'appointment' || ! isTimely;
        // Duration columns are required only when their column is actually visible:
        //  - Start/End Time  → hourly inventory ON + duration-based ON
        //  - Duration        → hourly inventory ON + duration-based OFF
        var needTimeCols = isTimely && isSpecific;
        var needDuration = isTimely && ! isSpecific;
        var typeLabel = rentType === 'appointment' ? 'Appointment' : 'Single Day';
        var hasValidRow = false;

        if ( ! $sdRows.length ) {
            var $addBtn = $wrap.find('#add-bike-car-sd-type-row').closest('.sd-add-type-and-sessional');
            if ( ! $addBtn.length ) {
                $addBtn = $wrap.find('.sd-add-type-and-sessional').first();
            }
            if ( ! $addBtn.length ) {
                $addBtn = $wrap.find('.rbfw_bike_car_sd_wrapper .rbfw_bike_car_sd_price_table').first();
            }
            showPricingTableWarning(
                'At least one rental option row is required for ' + typeLabel + ' type.',
                $addBtn,
                '#add-bike-car-sd-type-row'
            );
            return false;
        }

        $sdRows.each(function (idx) {
            var $row = $(this);
            var $title = $row.find('[name^="rbfw_bike_car_sd_data["][name*="[rent_type]"]');
            var $price = $row.find('[name^="rbfw_bike_car_sd_data["][name*="[price]"]');
            var $stock = $row.find('[name^="rbfw_bike_car_sd_data["][name*="[qty]"]');

            if ( ! $title.length ) {
                $title = $row.find('[name*="[rent_type]"]').filter(function () {
                    return ( $(this).attr('name') || '' ).indexOf('rbfw_bike_car_sd_data_sp') === -1;
                }).first();
            }
            if ( ! $price.length ) {
                $price = $row.find('[name*="[price]"]').filter(function () {
                    return ( $(this).attr('name') || '' ).indexOf('rbfw_bike_car_sd_data_sp') === -1;
                }).first();
            }
            if ( ! $stock.length ) {
                $stock = $row.find('[name*="[qty]"]').filter(function () {
                    return ( $(this).attr('name') || '' ).indexOf('rbfw_bike_car_sd_data_sp') === -1;
                }).first();
            }

            // Duration / Start Time / End Time live in the same row; validate them only
            // when their column is visible for the current mode. The seasonal (_sp) copies
            // are excluded so we check the main table row only.
            var notSp = function () {
                return ( $( this ).attr( 'name' ) || '' ).indexOf( 'rbfw_bike_car_sd_data_sp' ) === -1;
            };
            var $duration = $row.find('[name*="[duration]"]').filter( notSp ).first();
            var $start    = $row.find('[name*="[start_time]"]').filter( notSp ).first();
            var $end      = $row.find('[name*="[end_time]"]').filter( notSp ).first();

            var rentTypeVal = $.trim($title.val());
            var priceVal = $.trim($price.val());
            var stockVal = $.trim($stock.val());
            var durationVal = $.trim($duration.val());
            var startVal = $.trim($start.val());
            var endVal = $.trim($end.val());

            // Skip a completely untouched row (nothing relevant to the current mode filled).
            var rowTouched = rentTypeVal || priceVal || stockVal ||
                             ( needDuration && durationVal ) ||
                             ( needTimeCols && ( startVal || endVal ) );
            if ( ! rowTouched ) {
                return;
            }

            if ( ! rentTypeVal ) {
                errors.push({ $field: $title, msg: 'Row ' + (idx + 1) + ': Rental option name is required.' });
            }
            if ( priceVal === '' ) {
                errors.push({ $field: $price, msg: 'Row ' + (idx + 1) + ': Price is required.' });
            }
            if ( requireQty && stockVal === '' ) {
                errors.push({ $field: $stock, msg: 'Row ' + (idx + 1) + ': Stock/Day is required.' });
            }
            if ( needDuration && durationVal === '' ) {
                errors.push({ $field: $duration, msg: 'Row ' + (idx + 1) + ': Duration is required.' });
            }
            if ( needTimeCols && startVal === '' ) {
                errors.push({ $field: $start, msg: 'Row ' + (idx + 1) + ': Start Time is required.' });
            }
            if ( needTimeCols && endVal === '' ) {
                errors.push({ $field: $end, msg: 'Row ' + (idx + 1) + ': End Time is required.' });
            }

            if ( rentTypeVal && priceVal !== '' &&
                 ( ! requireQty || stockVal !== '' ) &&
                 ( ! needDuration || durationVal !== '' ) &&
                 ( ! needTimeCols || ( startVal !== '' && endVal !== '' ) ) ) {
                hasValidRow = true;
            }
        });

        if ( ! hasValidRow ) {
            var $firstRow = $sdRows.first();
            var $firstTitle = $firstRow.find('[name^="rbfw_bike_car_sd_data["][name*="[rent_type]"]').first();
            if ( ! $firstTitle.length ) {
                $firstTitle = $firstRow.find('[name*="[rent_type]"]').first();
            }
            errors.push({
                $field: $firstTitle.length ? $firstTitle : $sdRows.first(),
                msg: 'At least one complete rental option row is required (name, price' + (requireQty ? ', stock/day' : '') + ').'
            });
        }

        return true;
    }

    function validateResortPricingRows(errors) {
        var $rows = getMainResortPriceRows();
        var hasValidRow = false;

        if ( ! $rows.length ) {
            showPricingTableWarning(
                'At least one resort room type row is required.',
                $wrap.find('#add-resort-type-row').first(),
                '#add-resort-type-row'
            );
            return false;
        }

        $rows.each(function (idx) {
            var $row = $(this);
            var $roomType = $row.find('[name*="[room_type]"]').first();
            var $dayNight = $row.find('[name*="[rbfw_room_daynight_rate]"]').first();
            var $qty = $row.find('[name*="[rbfw_room_available_qty]"]').first();
            var roomTypeVal = $.trim($roomType.val());
            var dayNightVal = $.trim($dayNight.val());
            var qtyVal = $.trim($qty.val());

            if ( ! roomTypeVal && dayNightVal === '' && qtyVal === '' ) {
                return;
            }

            if ( ! roomTypeVal ) {
                errors.push({ $field: $roomType, msg: 'Row ' + (idx + 1) + ': Room type is required.' });
            }
            if ( dayNightVal === '' ) {
                errors.push({ $field: $dayNight, msg: 'Row ' + (idx + 1) + ': Day-night price is required.' });
            }
            if ( qtyVal === '' ) {
                errors.push({ $field: $qty, msg: 'Row ' + (idx + 1) + ': Stock quantity is required.' });
            }

            if ( roomTypeVal && dayNightVal !== '' && qtyVal !== '' ) {
                hasValidRow = true;
            }
        });

        if ( ! hasValidRow ) {
            var $firstRoom = $rows.first().find('[name*="[room_type]"]').first();
            errors.push({
                $field: $firstRoom.length ? $firstRoom : $rows.first(),
                msg: 'At least one complete resort room row is required (room type, day-night price, stock quantity).'
            });
        }

        return true;
    }

    function validateMultipleItemsPricingRows(errors) {
        var $rows = $wrap.find('.rbfw_multiple_items #itemRows .item-row');
        var enabledTypes = {
            hourly: $wrap.find('#enableHourly').prop('checked'),
            daily: $wrap.find('#enableDaily').prop('checked'),
            weekly: $wrap.find('#enableWeekly').prop('checked'),
            monthly: $wrap.find('#enableMonthly').prop('checked')
        };
        var hasValidRow = false;

        if ( ! $rows.length ) {
            showPricingTableWarning(
                'At least one item row is required for Multiple Items type.',
                $wrap.find('.rbfw_multiple_items .add-more-btn').first(),
                '.rbfw_multiple_items .add-more-btn'
            );
            return false;
        }

        $rows.each(function (idx) {
            var $row = $(this);
            var $name = $row.find('[name*="[item_name]"]').first();
            var $qty = $row.find('[name*="[available_qty]"]').first();
            var nameVal = $.trim($name.val());
            var qtyVal = $.trim($qty.val());
            var hasPrice = false;
            var $firstEnabledPrice = null;

            $.each(enabledTypes, function (type, enabled) {
                if ( ! enabled ) {
                    return;
                }
                var $price = $row.find('[name*="[' + type + '_price]"]').first();
                if ( $price.length && ! $firstEnabledPrice ) {
                    $firstEnabledPrice = $price;
                }
                if ( $price.length && $.trim($price.val()) !== '' ) {
                    hasPrice = true;
                }
            });

            if ( ! nameVal && qtyVal === '' && ! hasPrice ) {
                return;
            }

            if ( ! nameVal ) {
                errors.push({ $field: $name, msg: 'Row ' + (idx + 1) + ': Item name is required.' });
            }
            if ( qtyVal === '' ) {
                errors.push({ $field: $qty, msg: 'Row ' + (idx + 1) + ': Quantity is required.' });
            }
            if ( ! hasPrice ) {
                errors.push({
                    $field: $firstEnabledPrice || $name,
                    msg: 'Row ' + (idx + 1) + ': At least one enabled price is required.'
                });
            }

            if ( nameVal && qtyVal !== '' && hasPrice ) {
                hasValidRow = true;
            }
        });

        if ( ! hasValidRow ) {
            var $firstName = $rows.first().find('[name*="[item_name]"]').first();
            errors.push({
                $field: $firstName.length ? $firstName : $rows.first(),
                msg: 'At least one complete item row is required (item name, quantity, and price).'
            });
        }

        return true;
    }

    function validateBeforeSave() {
        var errors = [];

        // ── Title ──
        var titleVal = $.trim($wrap.find('.rbfw-me-card-title-input').val()
                       || $wrap.find('.rbfw-me-title-input').val());
        if ( ! titleVal ) {
            errors.push({ $field: $wrap.find('.rbfw-me-card-title-input'), msg: 'Title is required.' });
            errors.push({ $field: $wrap.find('.rbfw-me-title-input'),      msg: 'Title is required.' });
        }

        // ── Description ──
        var contentVal = '';
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get('rbfw_me_post_content');
            contentVal = (ed && !ed.isHidden())
                ? $.trim(ed.getContent({ format: 'text' }))
                : $.trim($wrap.find('[name="post_content"]').val());
        } else {
            contentVal = $.trim($wrap.find('[name="post_content"]').val());
        }
        if ( ! contentVal ) {
            errors.push({ $field: $wrap.find('.rbfw-me-editor-wrap'), msg: 'Description is required.' });
        }

        // ── Rent-type pricing rows ──
        var rentType = $.trim($wrap.find('#rbfw_item_type').val());
        if ( rentType === 'bike_car_sd' || rentType === 'appointment' ) {
            if ( ! validateSdPricingRows(rentType, errors) ) {
                return false;
            }
        } else if ( rentType === 'resort' ) {
            if ( ! validateResortPricingRows(errors) ) {
                return false;
            }
        } else if ( rentType === 'multiple_items' ) {
            if ( ! validateMultipleItemsPricingRows(errors) ) {
                return false;
            }
        }

        if ( rentType === 'bike_car_sd' ) {
            // ── Single Day seasonal pricing: validate only when both dates are set ──
            $wrap.find('.sessional_price_single_day .rbfw-sp-item-row').each(function () {
                var $block = $(this);
                if ( $block.closest('.mp_hidden_content').length ) {
                    return;
                }

                var startDate = $.trim($block.find('[name*="[start_date]"]').first().val());
                var endDate   = $.trim($block.find('[name*="[end_date]"]').first().val());

                if ( ! startDate || ! endDate ) {
                    return;
                }

                $block.find('tr.rbfw_bike_car_sd_price_table_row').each(function (idx) {
                    var $price = $(this).find('[name*="rbfw_bike_car_sd_data_sp"][name*="[price]"]').first();
                    if ( $price.length && $.trim($price.val()) === '' ) {
                        errors.push({
                            $field: $price,
                            msg: 'Seasonal pricing row ' + (idx + 1) + ': Price is required when start and end dates are set.'
                        });
                    }
                });
            });
        }

        // ── Time Picker: at least one time slot required when enabled ──
        var timePickerValid = true;
        $wrap.find('.time-slots-section').each(function () {
            var $section = $(this);
            if ( ! $section.is(':visible') ) return; // time picker off or section hidden

            var slotCount = $section.find('.time-slots .time-slot').length;
            if ( slotCount === 0 ) {
                timePickerValid = false;
                var $addSlotContainer = $section.find('.add-slot-container');
                if ( $addSlotContainer.length && ! $addSlotContainer.prev('.rbfw-me-table-warning').length ) {
                    $addSlotContainer.before(
                        '<div class="rbfw-me-table-warning">' +
                          '<span class="dashicons dashicons-warning"></span>' +
                          ' At least one time slot is required when Time Picker is enabled.' +
                        '</div>'
                    );
                    $wrap.one('click', '.add-slot-btn', function () {
                        $wrap.find('.rbfw-me-table-warning').remove();
                    });
                }
                var $panel = $section.closest('.rbfw-me-panel[data-panel]');
                if ( $panel.length && ! $panel.hasClass('is-active') ) {
                    $wrap.find('.rbfw-me-tab[data-tab="' + $panel.data('panel') + '"]').trigger('click');
                }
                setTimeout(function () {
                    var el = $addSlotContainer[0] || $section[0];
                    if ( el ) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 250);
                return false; // break .each
            }
        });
        if ( ! timePickerValid ) return false;

        // ── Generic: all [required] fields across every panel ──
        $wrap.find('.rbfw-me-panel').each(function () {
            $(this).find('input[required], select[required], textarea[required]').each(function () {
                var $f   = $(this);
                var type = ($f.attr('type') || '').toLowerCase();
                var val  = $.trim($f.val());
                var empty = false;

                if ( type === 'checkbox' || type === 'radio' ) {
                    // group — at least one must be checked
                    var name = $f.attr('name');
                    if ( name && ! $wrap.find('[name="' + name + '"]:checked').length ) {
                        empty = true;
                    }
                } else {
                    empty = val === '';
                }

                if ( empty ) {
                    var label = $f.attr('placeholder')
                              || $f.closest('.rbfw-me-field').find('.rbfw-me-field__label').text()
                              || $f.attr('name')
                              || 'This field';
                    errors.push({ $field: $f, msg: label + ' is required.' });
                }
            });
        });

        if ( ! errors.length ) return true;

        // Show all errors
        $.each(errors, function (i, e) {
            showFieldError(e.$field, e.msg);
        });

        // Navigate to the FIRST errored field inside a panel
        var navigated = false;
        $.each(errors, function (i, e) {
            if ( navigated ) return false;
            var $panel = e.$field.closest('.rbfw-me-panel[data-panel]');
            if ( $panel.length ) {
                navigateToField(e.$field);
                navigated = true;
            }
        });
        if ( ! navigated ) {
            $wrap.find('.rbfw-me-title-input').focus();
        }

        return false;
    }

    function setSaveIndicator(state, text) {
        var $indicator = $wrap.find('.rbfw-me-save-indicator');
        var iconClass  = '';

        if (state === 'saving') {
            iconClass = 'dashicons-update rbfw-me-save-indicator__icon--spin';
        } else if (state === 'saved') {
            iconClass = 'dashicons-yes-alt';
        } else if (state === 'error') {
            iconClass = 'dashicons-warning';
        }

        if (!state || !text) {
            $indicator.removeClass('is-saving is-saved is-error is-visible').empty();
            return;
        }

        var html = '<span class="dashicons ' + iconClass + ' rbfw-me-save-indicator__icon"></span>' +
            '<span class="rbfw-me-save-indicator__text">' + text + '</span>';

        $indicator
            .removeClass('is-saving is-saved is-error is-visible')
            .addClass('is-' + state + ' is-visible')
            .html(html);
    }

    function doSave(status) {
        if ( ! validateBeforeSave() ) return;

        setSaveIndicator('saving', cfg.i18n && cfg.i18n.saving || 'Saving your changes…');

        var data = collectFormData();
        data.action      = 'rbfw_modern_editor_save';
        data.nonce       = cfg.nonce_save || '';
        data.post_id     = postId;
        data.post_status = status;

        $.post(cfg.ajax_url, data, function (res) {
            if (res.success) {
                setSaveIndicator('saved', cfg.i18n && cfg.i18n.saved || 'All changes saved');
                // Update publish button label if status changed
                if (status === 'publish') {
                    $wrap.find('.rbfw-me-publish').text(cfg.i18n && cfg.i18n.update || 'Update').data('published', '1');
                }
                // Update status dot
                var $dot   = $wrap.find('.rbfw-me-status-dot');
                var $label = $wrap.find('.rbfw-me-status-label');
                $dot.attr('class', 'rbfw-me-status-dot rbfw-me-status-dot--' + status);
                $label.text(status.charAt(0).toUpperCase() + status.slice(1));

                setTimeout(function () { setSaveIndicator('', ''); }, 4500);
            } else {
                var errorMsg = cfg.i18n && cfg.i18n.save_error || 'Save failed — please try again';
                if (res.data && res.data.message) {
                    errorMsg = res.data.message;
                } else if (res.data && res.data.errors && res.data.errors.length) {
                    errorMsg = res.data.errors.join(' ');
                }
                setSaveIndicator('error', errorMsg);
            }
        }).fail(function () {
            setSaveIndicator('error', cfg.i18n && cfg.i18n.save_error || 'Save failed — please try again');
        });
    }

    /* ── VISUAL / CODE label-row switch ─────────────────────────── */
    function initEditorTabsInToolbar() {
        var $edWrap  = $('#wp-rbfw_me_post_content-wrap');
        var $visual  = $wrap.find('.rbfw-me-sw-visual');
        var $code    = $wrap.find('.rbfw-me-sw-code');

        function wireBtns() {
            var $tmceBtn = $edWrap.find('#rbfw_me_post_content-tmce');
            var $htmlBtn = $edWrap.find('#rbfw_me_post_content-html');
            if ( ! $tmceBtn.length ) return false;

            $visual.off('click.edswitch').on('click.edswitch', function () {
                $tmceBtn.trigger('click');
                $visual.addClass('is-active');
                $code.removeClass('is-active');
            });
            $code.off('click.edswitch').on('click.edswitch', function () {
                $htmlBtn.trigger('click');
                $code.addClass('is-active');
                $visual.removeClass('is-active');
            });
            return true;
        }

        // Try immediately, then via TinyMCE init event, then fallback
        if ( ! wireBtns() ) {
            $(document).on('tinymce-editor-init', function (e, editor) {
                if ( editor && editor.id === 'rbfw_me_post_content' ) {
                    setTimeout(wireBtns, 50);
                }
            });
            setTimeout(wireBtns, 1000);
        }
    }

    /* ── Add Media button — inject into both Visual and Code toolbars ── */
    function initEditorMediaBtn() {
        var $edWrap = $('#wp-rbfw_me_post_content-wrap');

        function getOrig() {
            return $edWrap.find('#insert-media-button');
        }

        function makeClone(id, cls) {
            var $orig  = getOrig();
            if ( ! $orig.length ) return null;
            return $orig.clone()
                .removeClass()
                .addClass(cls)
                .attr('id', id)
                .on('click', function (e) {
                    e.preventDefault();
                    $orig.trigger('click');
                });
        }

        /* Visual mode — inject into mce flow-layout toolbar */
        function injectVisual() {
            if ( $edWrap.find('.rbfw-add-media-mce').length ) return;
            var $flowLayout = $edWrap.find('.mce-toolbar-grp .mce-flow-layout').first();
            if ( ! $flowLayout.length ) return;
            var $clone = makeClone('rbfw-insert-media-mce', 'rbfw-add-media-mce rbfw-add-media-qt');
            if ( $clone ) $flowLayout.append($clone);
        }

        /* Code mode — inject into quicktags toolbar */
        function injectCode() {
            var $qt = $edWrap.find('#qt_rbfw_me_post_content_toolbar');
            if ( ! $qt.length ) return;
            if ( $qt.find('.rbfw-add-media-qt-code').length ) return;
            var $clone = makeClone('rbfw-insert-media-qt', 'rbfw-add-media-qt rbfw-add-media-qt-code');
            if ( $clone ) $qt.append($clone);
        }

        function inject() {
            injectVisual();
            injectCode();
        }

        $(document).on('tinymce-editor-init', function (e, editor) {
            if ( editor && editor.id === 'rbfw_me_post_content' ) {
                setTimeout(inject, 100);
            }
        });
        setTimeout(inject, 800);
        setTimeout(inject, 2000);
    }

    /* ── Sync card title → header h1 ────────────────────────────── */
    function initTitleSync() {
        $wrap.on('input', '.rbfw-me-card-title-input', function () {
            $wrap.find('.rbfw-me-title-display').text($(this).val());
        });
    }

    /* ── Category checkboxes → hidden input sync + pill state ─── */
    function initCategories() {
        function syncPill($cb) {
            $cb.closest('.rbfw-me-checkbox-label').toggleClass('is-checked', $cb.is(':checked'));
        }

        function rtEsc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }

        var meEditTermId = 0; // 0 = add mode, >0 = rename mode

        function meCard()      { return $wrap.find('.rbfw-me-rent-type-card'); }
        function meNonce()     { return meCard().data('nonce'); }
        function meCanManage() { return String(meCard().data('can-manage')) === '1'; }
        function meHidden()    { return $wrap.find('.rbfw-me-cats-hidden'); }

        function meActionsHtml() {
            if (!meCanManage()) { return ''; }
            return '<span class="rbfw-rt-actions">' +
                '<span class="rbfw-rt-edit dashicons dashicons-edit" title="' + (rbfwModernEditor_i18n('Edit') || 'Edit') + '"></span>' +
                '<span class="rbfw-rt-del dashicons dashicons-trash" title="' + (rbfwModernEditor_i18n('Delete') || 'Delete') + '"></span>' +
            '</span>';
        }

        function rebuildRentTypes(rentTypes, selectName) {
            rentTypes = rentTypes || [];
            var current = (meHidden().val() || '').split(',').filter(Boolean);
            if (selectName) {
                var selectLower = String(selectName).toLowerCase().trim();
                var has = current.some(function (n) { return String(n).toLowerCase().trim() === selectLower; });
                if (!has) {
                    current.push(selectName);
                }
            }
            var $grid = $wrap.find('.rbfw-me-checkbox-grid').empty();
            rentTypes.forEach(function (rt) {
                var nameLower = String(rt.name).toLowerCase().trim();
                var checked = current.some(function (n) { return String(n).toLowerCase().trim() === nameLower; });
                var checkedAttr = checked ? ' checked' : '';
                var depth  = parseInt(rt.depth, 10) || 0;
                var indent = depth > 0 ? ' style="margin-left:' + (depth * 18) + 'px;"' : '';
                var prefix = depth > 0 ? '<span class="rbfw-rt-sub-indicator" aria-hidden="true">↳ </span>' : '';
                $grid.append(
                    '<label class="rbfw-me-checkbox-label rbfw-rt-chip' + (checked ? ' is-checked' : '') + (depth > 0 ? ' rbfw-rt-child' : '') + '" data-term-id="' + rtEsc(rt.term_id) + '" data-name="' + rtEsc(rt.name) + '" data-parent="' + rtEsc(rt.parent || 0) + '" data-depth="' + depth + '"' + indent + '>' +
                        '<input type="checkbox" class="rbfw-me-cat-checkbox" data-name="' + rtEsc(rt.name) + '"' + checkedAttr + ' />' +
                        '<span>' + prefix + rtEsc(rt.name.charAt(0).toUpperCase() + rt.name.slice(1)) + '</span>' +
                        meActionsHtml() +
                    '</label>'
                );
            });
            meHidden().val(current.join(','));
            $wrap.find('.rbfw-me-rent-type-empty').toggleClass('rbfw-me-hidden', rentTypes.length > 0);
        }

        // Build the parent <select> options from the currently rendered chips.
        // Excludes the term being edited (and its descendants) to prevent cycles.
        function mePopulateParents(excludeTermId) {
            var $select = $wrap.find('#rbfw-me-rent-type-modal-parent');
            if (!$select.length) { return; }
            excludeTermId = parseInt(excludeTermId, 10) || 0;
            var prev = String($select.val() || '0');

            var excluded = {};
            if (excludeTermId) {
                excluded[excludeTermId] = true;
                var changed = true;
                while (changed) {
                    changed = false;
                    $wrap.find('.rbfw-me-rent-type-card .rbfw-rt-chip').each(function () {
                        var tid = parseInt($(this).data('term-id'), 10) || 0;
                        var pid = parseInt($(this).data('parent'), 10) || 0;
                        if (pid && excluded[pid] && !excluded[tid]) { excluded[tid] = true; changed = true; }
                    });
                }
            }

            $select.find('option:not(:first)').remove();
            $wrap.find('.rbfw-me-rent-type-card .rbfw-rt-chip').each(function () {
                var $chip = $(this);
                var tid   = parseInt($chip.data('term-id'), 10) || 0;
                if (!tid || excluded[tid]) { return; }
                var depth = parseInt($chip.data('depth'), 10) || 0;
                var label = (depth > 0 ? new Array(depth + 1).join('— ') : '') + String($chip.data('name'));
                $select.append('<option value="' + rtEsc(tid) + '">' + rtEsc(label) + '</option>');
            });
            if ($select.find('option[value="' + prev + '"]').length) { $select.val(prev); } else { $select.val('0'); }
        }

        function openRentTypeModal(mode, termId, name, parentId) {
            meEditTermId = mode === 'edit' ? (parseInt(termId, 10) || 0) : 0;
            var isEdit = meEditTermId > 0;
            var $modal = $wrap.find('#rbfw-me-rent-type-modal');
            $modal.find('.rbfw-me-faq-modal__head h3').text(isEdit ? 'Rename Rent Type' : 'Add New Rent Type');
            $modal.find('#rbfw-me-rent-type-modal-save').text(isEdit ? 'Save Changes' : 'Add Rent Type');
            $modal.find('#rbfw-me-rent-type-modal-input').val(name || '');
            mePopulateParents(meEditTermId);
            $modal.find('#rbfw-me-rent-type-modal-parent').val(String(parseInt(parentId, 10) || 0));
            $modal.addClass('is-open');
            setTimeout(function () { $modal.find('#rbfw-me-rent-type-modal-input').trigger('focus'); }, 50);
        }

        function closeRentTypeModal() {
            $wrap.find('#rbfw-me-rent-type-modal').removeClass('is-open');
            meEditTermId = 0;
        }

        // Set initial pill state for pre-checked boxes
        $wrap.find('.rbfw-me-cat-checkbox').each(function () {
            syncPill($(this));
        });

        $wrap.on('change', '.rbfw-me-cat-checkbox', function () {
            syncPill($(this));
            var selected = [];
            $wrap.find('.rbfw-me-cat-checkbox:checked').each(function () {
                selected.push($(this).data('name'));
            });
            meHidden().val(selected.join(','));
        });

        $wrap.on('click', '.rbfw-rent-type-add-trigger', function (e) {
            e.preventDefault();
            openRentTypeModal('add');
        });

        // Edit (rename) a rent type.
        $wrap.on('click', '.rbfw-me-rent-type-card .rbfw-rt-edit', function (e) {
            e.preventDefault(); e.stopPropagation();
            var $chip = $(this).closest('.rbfw-rt-chip');
            openRentTypeModal('edit', $chip.data('term-id'), $chip.data('name'), $chip.data('parent'));
        });

        // Delete a rent type.
        $wrap.on('click', '.rbfw-me-rent-type-card .rbfw-rt-del', function (e) {
            e.preventDefault(); e.stopPropagation();
            var $chip  = $(this).closest('.rbfw-rt-chip');
            var termId = parseInt($chip.data('term-id'), 10) || 0;
            var name   = $chip.data('name');
            if (!termId) { return; }
            if (!window.confirm((rbfwModernEditor_i18n('Delete rent type "%s"? Items using it will have this type removed.') || 'Delete rent type "%s"? Items using it will have this type removed.').replace('%s', name))) { return; }
            $.post(window.ajaxurl, {
                action: 'rbfw_rent_type_delete',
                nonce:  meNonce(),
                term_id: termId
            }, function (resp) {
                if (resp && resp.success) {
                    var cur = (meHidden().val() || '').split(',').filter(Boolean).filter(function (n) {
                        return n.toLowerCase() !== String(resp.data.deleted_name).toLowerCase();
                    });
                    meHidden().val(cur.join(','));
                    rebuildRentTypes(resp.data.rent_types);
                } else {
                    window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                }
            }).fail(function () { window.alert((rbfwModernEditor_i18n('Request failed.') || 'Request failed.')); });
        });

        $wrap.on('click', '#rbfw-me-rent-type-modal .rbfw-me-faq-modal__close, #rbfw-me-rent-type-modal .rbfw-me-faq-modal__backdrop, .rbfw-me-rent-type-modal-cancel', function () {
            closeRentTypeModal();
        });

        $wrap.on('click', '#rbfw-me-rent-type-modal-save', function () {
            var $input = $wrap.find('#rbfw-me-rent-type-modal-input');
            var name   = $.trim($input.val());
            if (!name) { $input.trigger('focus'); return; }
            if (name.length > 200) { name = name.substring(0, 200); }

            if (meEditTermId > 0) {
                $.post(window.ajaxurl, {
                    action: 'rbfw_rent_type_rename',
                    nonce:  meNonce(),
                    term_id: meEditTermId,
                    name:   name,
                    parent: parseInt($wrap.find('#rbfw-me-rent-type-modal-parent').val(), 10) || 0
                }, function (resp) {
                    if (resp && resp.success) {
                        var cur = (meHidden().val() || '').split(',').filter(Boolean).map(function (n) {
                            return n.toLowerCase() === String(resp.data.old_name).toLowerCase() ? resp.data.new_name : n;
                        });
                        meHidden().val(cur.join(','));
                        rebuildRentTypes(resp.data.rent_types);
                        closeRentTypeModal();
                    } else {
                        window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                    }
                }).fail(function () { window.alert((rbfwModernEditor_i18n('Request failed.') || 'Request failed.')); });
            } else {
                $.post(window.ajaxurl, {
                    action: 'rbfw_rent_type_add',
                    nonce:  meNonce(),
                    name:   name,
                    parent: parseInt($wrap.find('#rbfw-me-rent-type-modal-parent').val(), 10) || 0
                }, function (resp) {
                    if (resp && resp.success) {
                        rebuildRentTypes(resp.data.rent_types, resp.data.added_name);
                        closeRentTypeModal();
                    } else {
                        window.alert((resp && resp.data && resp.data.message) || 'Action failed.');
                    }
                }).fail(function () { window.alert((rbfwModernEditor_i18n('Request failed.') || 'Request failed.')); });
            }
        });

        $wrap.on('keypress', '#rbfw-me-rent-type-modal-input', function (e) {
            if (e.which === 13) { e.preventDefault(); $wrap.find('#rbfw-me-rent-type-modal-save').trigger('click'); }
        });
    }

    /* ── Feature category accordion ─────────────────────────── */
    function initFeatureAccordion() {
        function setupRow($row) {
            var $title   = $row.find('.feature_category_title');
            var $content = $row.find('.feature_category_inner_item_wrap');
            var $addBtn  = $row.find('.add-new-feature');
            if ( ! $title.length || $title.find('.rbfw-feat-chevron').length ) return;

            // Wrap content + button in a single body container
            if ( ! $row.find('.rbfw-feat-body').length ) {
                $content.add($addBtn).wrapAll('<div class="rbfw-feat-body"></div>');
            }
            var $body = $row.find('.rbfw-feat-body');

            // Inject chevron
            var $chevron = $('<span class="rbfw-feat-chevron"><i class="fas fa-chevron-down"></i></span>');
            $title.prepend($chevron);

            // Start expanded
            $row.addClass('rbfw-feat-open');
            $body.show();

            // Toggle on title row click (not on input / action buttons)
            $title.on('click.accordion', function (e) {
                if ( $(e.target).closest('input, .rbfw-me-features-actions').length ) return;
                var isOpen = $row.hasClass('rbfw-feat-open');
                if ( isOpen ) {
                    $row.removeClass('rbfw-feat-open');
                    $body.stop(true).slideUp(200);
                } else {
                    $row.addClass('rbfw-feat-open');
                    $body.stop(true).slideDown(200);
                }
            });
        }

        // Init existing rows
        $wrap.find('.rbfw_feature_category_table tbody tr').each(function () {
            setupRow($(this));
        });

        // Init dynamically added rows
        $wrap.on('click.accordion', '.add-feature-category', function () {
            setTimeout(function () {
                $wrap.find('.rbfw_feature_category_table tbody tr').each(function () {
                    setupRow($(this));
                });
            }, 60);
        });
    }

    /* ── Feature category repeater ───────────────────────────── */
    function initFeatures() {
        // Init sortable
        function initSortable() {
            if ($.fn.sortable) {
                $wrap.find('.sortable_tr').sortable({ handle: '.tr_sort_handler' });
                $wrap.find('.sortable').sortable({ handle: '.sort' });
            }
        }
        initSortable();

        // Add new feature category row
        $wrap.on('click', '.add-feature-category', function (e) {
            e.stopImmediatePropagation();
            var $tbody   = $wrap.find('.rbfw_feature_category_table tbody');
            var lastCat  = parseInt($tbody.find('tr:last-child').attr('data-cat')) || 0;
            var nextCat  = lastCat + 1;
            var html = '<tr data-cat="' + nextCat + '">'
                + '<td><div class="features_category_wrapper">'
                + '<div class="field-list rbfw_feature_category">'
                + '<div class="feature_category_inner_wrap">'
                + '<div class="feature_category_title"><label>' + (rbfwModernEditor_i18n('Feature Category Title') || 'Feature Category Title') + '</label>'
                + '<input type="text" name="rbfw_feature_category[' + nextCat + '][cat_title]" data-key="' + nextCat + '" placeholder="' + (rbfwModernEditor_i18n('Feature Category Label') || 'Feature Category Label') + '" />'
                + '<div class="rbfw-me-features-actions"><span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span><span class="button tr_remove"><i class="fas fa-trash-can"></i></span></div>'
                + '</div>'
                + '<div class="feature_category_inner_item_wrap sortable">'
                + '<div class="item">'
                + '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> Icon</a>'
                + '<div class="rbfw_feature_icon_preview" data-key="0"></div>'
                + '<input type="hidden" name="rbfw_feature_category[' + nextCat + '][cat_features][0][icon]" data-key="0" class="rbfw_feature_icon" />'
                + '<input type="text" name="rbfw_feature_category[' + nextCat + '][cat_features][0][title]" placeholder="' + (rbfwModernEditor_i18n('Features Name') || 'Features Name') + '" data-key="0" />'
                + '<div><span class="button sort"><i class="fas fa-arrows-alt"></i></span>'
                + '<span class="button remove" onclick="jQuery(this).parent().parent().remove()"><i class="fas fa-trash-can"></i></span></div>'
                + '</div></div></div></div>'
                + '<button type="button" class="ppof-button add-new-feature"><i class="fas fa-circle-plus"></i> Add New Feature</button>'
                + '</div></td>'
                + '<td class="rbfw-me-features-actions">'
                + '<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>'
                + '<span class="button tr_remove"><i class="fas fa-trash-can"></i></span>'
                + '</td></tr>';
            $tbody.append(html);
            initSortable();
        });

        // Remove category row
        $wrap.on('click', '.tr_remove', function () {
            $(this).closest('tr').remove();
        });

        // Add new feature item inside a category
        $wrap.on('click', '.add-new-feature', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $row     = $(this).closest('tr');
            var $items   = $row.find('.feature_category_inner_item_wrap').first();
            var lastKey  = parseInt($items.find('div.item:last-child input[data-key]').attr('data-key')) || 0;
            var newKey   = lastKey + 1;
            var dataCat  = $row.attr('data-cat');
            var html = '<div class="item">'
                + '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="' + newKey + '"><i class="fas fa-circle-plus"></i> Icon</a>'
                + '<div class="rbfw_feature_icon_preview" data-key="' + newKey + '"></div>'
                + '<input type="hidden" name="rbfw_feature_category[' + dataCat + '][cat_features][' + newKey + '][icon]" data-key="' + newKey + '" class="rbfw_feature_icon" />'
                + '<input type="text" name="rbfw_feature_category[' + dataCat + '][cat_features][' + newKey + '][title]" placeholder="' + (rbfwModernEditor_i18n('Features Name') || 'Features Name') + '" data-key="' + newKey + '" />'
                + '<div><span class="button sort"><i class="fas fa-arrows-alt"></i></span>'
                + '<span class="button remove" onclick="jQuery(this).parent().parent().remove()"><i class="fas fa-trash-can"></i></span></div>'
                + '</div>';
            $items.append(html);
            if ($.fn.sortable) $items.sortable({ handle: '.sort' });
        });

        // Feature icon picker (FontAwesome icon modal)
        $wrap.on('click', '.rbfw_feature_icon_btn', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $btn     = $(this);
            var dataKey  = $btn.attr('data-key');
            var dataCat  = $btn.closest('tr').attr('data-cat');
            var $modal   = $('#rbfw_features_icon_list_wrapper');

            $modal.removeAttr('data-key').attr('data-key', dataKey);
            $modal.attr('data-cat', dataCat);
            $modal.find('label').removeClass('selected');
            $('#rbfw_features_search_icon').val('');
            $modal.find('.rbfw_features_icon_list_body label[data-id]').show();

            if ($.fn.mage_modal) {
                $modal.mage_modal({
                    escapeClose: false,
                    clickClose: false,
                    showClose: false
                });
            }
        });

        // Icon selection inside the modal
        $(document).on('click', '#rbfw_features_icon_list_wrapper label', function (e) {
            e.stopImmediatePropagation();
            var $label   = $(this);
            var selected = $label.find('input').val() || '';
            var $modal   = $('#rbfw_features_icon_list_wrapper');
            var dataKey  = $modal.attr('data-key');
            var dataCat  = $modal.attr('data-cat');

            $modal.find('label').removeClass('selected');
            $label.addClass('selected');

            var $targetRow = $('.rbfw_feature_category_table tr[data-cat="' + dataCat + '"]');
            $targetRow.find('.rbfw_feature_icon[data-key="' + dataKey + '"]').val(selected);
            $targetRow.find('.rbfw_feature_icon_preview[data-key="' + dataKey + '"]').html('<i class="' + selected + '"></i>');
        });

        // Icon search filter
        $(document).on('keyup', '#rbfw_features_search_icon', function () {
            var value = $.trim($(this).val()).toLowerCase();
            $('#rbfw_features_icon_list_wrapper .rbfw_features_icon_list_body label[data-id]').each(function () {
                var id = $(this).attr('data-id') || '';
                $(this).toggle(id.toLowerCase().indexOf(value) > -1);
            });
        });
    }

    /* ── FAQ Settings ───────────────────────────────────────── */
    function initFaq() {
        var ajaxUrl = cfg.ajax_url || '';
        var nonces  = window.rbfw_ajax_admin || {};

        /* Open modal – Add mode */
        $wrap.on('click', '.rbfw-me-faq-add-btn', function () {
            openFaqModal('add');
        });

        /* View toggle */
        $wrap.on('click', '.rbfw-me-faq-view', function () {
            $(this).closest('.rbfw-me-faq-item').find('.rbfw-me-faq-item__content').toggleClass('rbfw-me-hidden');
        });

        /* Open modal – Edit mode */
        $wrap.on('click', '.rbfw-me-faq-edit', function () {
            var $item   = $(this).closest('.rbfw-me-faq-item');
            var id      = $item.data('id');
            var title   = $item.find('.rbfw-me-faq-item__title').text().trim();
            var content = $item.find('.rbfw-me-faq-item__content').html() || '';
            openFaqModal('edit', id, title, content);
        });

        /* Delete */
        $wrap.on('click', '.rbfw-me-faq-delete', function () {
            if (!confirm((rbfwModernEditor_i18n('Are you sure you want to delete this FAQ?') || 'Are you sure you want to delete this FAQ?'))) return;
            var id     = $(this).closest('.rbfw-me-faq-item').data('id');
            var postId = $wrap.find('.rbfw-me-faq-post-id').val();
            $.post(ajaxUrl, {
                action:           'rbfw_me_faq_delete',
                rbfw_faq_postID:  postId,
                itemId:           id,
                nonce:            nonces.nonce_faq_delete_item
            }, function (res) {
                if (res.success) $wrap.find('.rbfw-me-faq-items').html(res.data.html);
            });
        });

        /* Save */
        $wrap.on('click', '#rbfw-me-faq-save', function () {
            var postId  = $wrap.find('.rbfw-me-faq-post-id').val();
            var title   = $('#rbfw-me-faq-title').val().trim();
            var content = getFaqEditorContent();
            if (!title) { showFaqMsg('error', 'Please enter a title.'); return; }
            $.post(ajaxUrl, {
                action:           'rbfw_me_faq_save',
                rbfw_faq_title:   title,
                rbfw_faq_content: content,
                rbfw_faq_postID:  postId,
                nonce:            nonces.nonce_faq_data_save
            }, function (res) {
                if (res.success) {
                    $wrap.find('.rbfw-me-faq-items').html(res.data.html);
                    closeFaqModal();
                } else {
                    showFaqMsg('error', res.data && res.data.message ? res.data.message : 'Error saving FAQ.');
                }
            });
        });

        /* Update */
        $wrap.on('click', '#rbfw-me-faq-update', function () {
            var postId  = $wrap.find('.rbfw-me-faq-post-id').val();
            var itemId  = $('#rbfw-me-faq-item-id').val();
            var title   = $('#rbfw-me-faq-title').val().trim();
            var content = getFaqEditorContent();
            if (!title) { showFaqMsg('error', 'Please enter a title.'); return; }
            $.post(ajaxUrl, {
                action:           'rbfw_me_faq_update',
                rbfw_faq_title:   title,
                rbfw_faq_content: content,
                rbfw_faq_postID:  postId,
                rbfw_faq_itemID:  itemId,
                nonce:            nonces.nonce_faq_data_update
            }, function (res) {
                if (res.success) {
                    $wrap.find('.rbfw-me-faq-items').html(res.data.html);
                    closeFaqModal();
                } else {
                    showFaqMsg('error', res.data && res.data.message ? res.data.message : 'Error updating FAQ.');
                }
            });
        });

        /* Close modal */
        $wrap.on('click', '.rbfw-me-faq-modal__close, .rbfw-me-faq-modal__backdrop', function () {
            closeFaqModal();
        });

        function openFaqModal(mode, itemId, title, content) {
            $('#rbfw-me-faq-title').val(title || '');
            $('#rbfw-me-faq-item-id').val(itemId || '');
            $('#rbfw-me-faq-msg').html('');
            setFaqEditorContent(content || '');

            if (mode === 'edit') {
                $('#rbfw-me-faq-modal-title').text('Edit F.A.Q.');
                $('#rbfw-me-faq-save').addClass('rbfw-me-hidden');
                $('#rbfw-me-faq-update').removeClass('rbfw-me-hidden');
            } else {
                $('#rbfw-me-faq-modal-title').text('Add F.A.Q.');
                $('#rbfw-me-faq-save').removeClass('rbfw-me-hidden');
                $('#rbfw-me-faq-update').addClass('rbfw-me-hidden');
            }
            $('#rbfw-me-faq-modal').addClass('is-open');
        }

        function closeFaqModal() {
            $('#rbfw-me-faq-modal').removeClass('is-open');
        }

        function getFaqEditorContent() {
            if (typeof tinymce !== 'undefined' && tinymce.get('rbfw_me_faq_content')) {
                return tinymce.get('rbfw_me_faq_content').getContent();
            }
            return $('#rbfw_me_faq_content').val();
        }

        function setFaqEditorContent(content) {
            if (typeof tinymce !== 'undefined' && tinymce.get('rbfw_me_faq_content')) {
                tinymce.get('rbfw_me_faq_content').setContent(content);
            } else {
                $('#rbfw_me_faq_content').val(content);
            }
        }

        function showFaqMsg(type, msg) {
            $('#rbfw-me-faq-msg').html('<span class="rbfw-me-' + type + '">' + msg + '</span>');
        }
    }

    /* ── Term Settings ──────────────────────────────────────── */
    function initTerm() {
        var ajaxUrl = cfg.ajax_url || '';
        var nonces  = window.rbfw_ajax_admin || {};

        /* Open modal – Add mode */
        $wrap.on('click', '.rbfw-me-term-add-btn', function () {
            openTermModal('add');
        });

        /* Open modal – Edit mode */
        $wrap.on('click', '.rbfw-me-term-edit', function () {
            var $item = $(this).closest('.rbfw-me-faq-item');
            var id    = $item.data('id');
            var title = $item.find('.rbfw-me-term-item__title').text().trim();
            var url   = $item.find('.rbfw-me-term-url-val').val();
            var req   = $item.find('.rbfw-me-term-req-val').val();
            openTermModal('edit', id, title, url, req);
        });

        /* Delete */
        $wrap.on('click', '.rbfw-me-term-delete', function () {
            if (!confirm((rbfwModernEditor_i18n('Are you sure you want to delete this term?') || 'Are you sure you want to delete this term?'))) return;
            var id     = $(this).closest('.rbfw-me-faq-item').data('id');
            var postId = $wrap.find('.rbfw-me-term-post-id').val();
            $.post(ajaxUrl, {
                action:           'rbfw_me_term_delete',
                rbfw_term_postID: postId,
                itemId:           id,
                nonce:            nonces.nonce_term_delete_item
            }, function (res) {
                if (res.success) $wrap.find('.rbfw-me-term-items').html(res.data.html);
            });
        });

        /* Save */
        $wrap.on('click', '#rbfw-me-term-save-btn', function () {
            var postId = $wrap.find('.rbfw-me-term-post-id').val();
            var title  = $('#rbfw-me-term-title-input').val().trim();
            var url    = $('#rbfw-me-term-url-input').val().trim();
            var req    = $('#rbfw-me-term-required-chk').prop('checked') ? 'yes' : 'no';
            if (!title) { $('#rbfw-me-term-msg').html('<span style="color:red">Please enter a title.</span>'); return; }
            $.post(ajaxUrl, {
                action:              'rbfw_me_term_save',
                rbfw_term_title:     title,
                rbfw_term_url:       url,
                rbfw_term_required:  req,
                rbfw_term_postID:    postId,
                nonce:               nonces.nonce_term_data_save
            }, function (res) {
                if (res.success) {
                    $wrap.find('.rbfw-me-term-items').html(res.data.html);
                    closeTermModal();
                }
            });
        });

        /* Update */
        $wrap.on('click', '#rbfw-me-term-update-btn', function () {
            var postId = $wrap.find('.rbfw-me-term-post-id').val();
            var itemId = $('#rbfw-me-term-item-id').val();
            var title  = $('#rbfw-me-term-title-input').val().trim();
            var url    = $('#rbfw-me-term-url-input').val().trim();
            var req    = $('#rbfw-me-term-required-chk').prop('checked') ? 'yes' : 'no';
            if (!title) { $('#rbfw-me-term-msg').html('<span style="color:red">Please enter a title.</span>'); return; }
            $.post(ajaxUrl, {
                action:              'rbfw_me_term_update',
                rbfw_term_title:     title,
                rbfw_term_url:       url,
                rbfw_term_required:  req,
                rbfw_term_postID:    postId,
                rbfw_term_itemID:    itemId,
                nonce:               nonces.nonce_term_data_update
            }, function (res) {
                if (res.success) {
                    $wrap.find('.rbfw-me-term-items').html(res.data.html);
                    closeTermModal();
                }
            });
        });

        /* Close modal */
        $wrap.on('click', '.rbfw-me-faq-modal__close, .rbfw-me-faq-modal__backdrop', function () {
            if ($(this).closest('#rbfw-me-term-modal').length) closeTermModal();
        });

        function openTermModal(mode, itemId, title, url, req) {
            $('#rbfw-me-term-title-input').val(title || '');
            $('#rbfw-me-term-url-input').val(url || '');
            $('#rbfw-me-term-required-chk').prop('checked', req === 'yes');
            $('#rbfw-me-term-item-id').val(itemId || '');
            $('#rbfw-me-term-msg').html('');
            if (mode === 'edit') {
                $('#rbfw-me-term-modal-title').text('Edit Term');
                $('#rbfw-me-term-save-btn').addClass('rbfw-me-hidden');
                $('#rbfw-me-term-update-btn').removeClass('rbfw-me-hidden');
            } else {
                $('#rbfw-me-term-modal-title').text('Add Term');
                $('#rbfw-me-term-save-btn').removeClass('rbfw-me-hidden');
                $('#rbfw-me-term-update-btn').addClass('rbfw-me-hidden');
            }
            $('#rbfw-me-term-modal').addClass('is-open');
        }

        function closeTermModal() {
            $('#rbfw-me-term-modal').removeClass('is-open');
        }
    }

    /* ── Related Items Tag Picker ───────────────────────────── */
    function initRelatedPicker() {
        // Open dropdown on search focus
        $wrap.on('focus', '.rbfw-me-tag-picker__search', function () {
            var $picker = $(this).closest('.rbfw-me-tag-picker');
            filterOptions($picker, $(this).val());
            $picker.find('.rbfw-me-tag-picker__dropdown').removeClass('rbfw-me-hidden');
        });

        // Filter options as user types
        $wrap.on('input', '.rbfw-me-tag-picker__search', function () {
            filterOptions($(this).closest('.rbfw-me-tag-picker'), $(this).val());
        });

        // Click anywhere in field → focus search input
        $wrap.on('click', '.rbfw-me-tag-picker__field', function (e) {
            if (!$(e.target).closest('.rbfw-me-tag').length) {
                $(this).find('.rbfw-me-tag-picker__search').trigger('focus');
            }
        });

        // Select an option — use mousedown so it fires before blur
        $wrap.on('mousedown', '.rbfw-me-tag-picker__option', function (e) {
            e.preventDefault();
            var $picker = $(this).closest('.rbfw-me-tag-picker');
            var id      = $(this).data('id');
            var title   = String($(this).data('title'));
            $(this).addClass('is-selected');
            var chip = '<div class="rbfw-me-tag" data-id="' + id + '">'
                + '<span>' + escHtml(title) + '</span>'
                + '<button type="button" class="rbfw-me-tag__remove" aria-label="Remove">'
                + '<span class="dashicons dashicons-no-alt"></span>'
                + '</button>'
                + '<input type="hidden" name="rbfw_releted_rbfw[]" value="' + parseInt(id, 10) + '">'
                + '</div>';
            $picker.find('.rbfw-me-tag-picker__search').before(chip).val('');
            filterOptions($picker, '');
        });

        // Remove a tag chip
        $wrap.on('click', '.rbfw-me-tag__remove', function () {
            var $tag    = $(this).closest('.rbfw-me-tag');
            var id      = $tag.data('id');
            var $picker = $tag.closest('.rbfw-me-tag-picker');
            $picker.find('.rbfw-me-tag-picker__option[data-id="' + id + '"]').removeClass('is-selected');
            $tag.remove();
            filterOptions($picker, $picker.find('.rbfw-me-tag-picker__search').val());
        });

        // Close dropdown when clicking outside
        $(document).on('mousedown.rbfw-picker', function (e) {
            if (!$(e.target).closest('.rbfw-me-tag-picker').length) {
                $wrap.find('.rbfw-me-tag-picker__dropdown').addClass('rbfw-me-hidden');
            }
        });

        function filterOptions($picker, query) {
            var q = (query || '').toLowerCase().trim();
            var visible = 0;
            $picker.find('.rbfw-me-tag-picker__option').each(function () {
                if ($(this).hasClass('is-selected')) return;
                var match = !q || String($(this).data('title')).toLowerCase().indexOf(q) !== -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $picker.find('.rbfw-me-tag-picker__no-results').toggleClass('rbfw-me-hidden', visible > 0);
        }

        function escHtml(str) {
            return $('<div>').text(str).html();
        }
    }

    /* ── Off Day Settings ────────────────────────────────────── */
    function initOffDays() {
        // Sync day checkboxes → hidden field
        $wrap.on('change', '.rbfw-me-offday-checkbox', function () {
            var $group = $(this).closest('.rbfw-me-offday-days');
            var selected = [];
            $group.find('.rbfw-me-offday-checkbox:checked').each(function () {
                selected.push($(this).data('day'));
            });
            $group.find('.rbfw-me-offday-hidden').val(selected.join(','));
        });

        // Collapsible card: click the head to expand/collapse the body. Used by the
        // "Block Booking" off-day card, which renders collapsed by default. Clicks on
        // the on/off switch (or any control) inside the head must not toggle collapse.
        $wrap.on('click', '.rbfw-me-card--collapsible .rbfw-me-card__head', function (e) {
            if ($(e.target).closest('.switch, input, button, a, select').length) return;
            var $card = $(this).closest('.rbfw-me-card--collapsible');
            var $body = $card.children('.rbfw-me-card__body');
            if ($card.hasClass('is-collapsed')) {
                $card.removeClass('is-collapsed');
                $body.hide().stop(true, true).slideDown(200, function () {
                    $body.css('display', ''); // restore stylesheet display (flex)
                });
            } else {
                $body.stop(true, true).slideUp(200, function () {
                    $card.addClass('is-collapsed');
                    $body.css('display', ''); // let the .is-collapsed CSS rule hide it
                });
            }
        });

        // Add new date range row
        $wrap.on('click', '.rbfw-me-offdate-add', function () {
            var $list = $(this).closest('.rbfw-me-card__body').find('.rbfw-me-offdate-list');
            var $row = $(
                '<div class="rbfw-me-offdate-row">' +
                    '<div class="rbfw-me-field">' +
                        '<label class="rbfw-me-label">' + (rbfwModernEditor_i18n('Start Date') || 'Start Date') + '</label>' +
                        '<input type="date" name="off_days_start[]" class="rbfw-me-input">' +
                    '</div>' +
                    '<div class="rbfw-me-field">' +
                        '<label class="rbfw-me-label">' + (rbfwModernEditor_i18n('End Date') || 'End Date') + '</label>' +
                        '<input type="date" name="off_days_end[]" class="rbfw-me-input">' +
                    '</div>' +
                    '<button type="button" class="rbfw-me-offdate-remove" title="' + (rbfwModernEditor_i18n('Remove') || 'Remove') + '">' +
                        '<span class="dashicons dashicons-trash"></span>' +
                    '</button>' +
                '</div>'
            );
            $list.append($row);
        });

        // Remove date range row
        $wrap.on('click', '.rbfw-me-offdate-remove', function () {
            var $list = $(this).closest('.rbfw-me-offdate-list');
            if ($list.find('.rbfw-me-offdate-row').length > 1) {
                $(this).closest('.rbfw-me-offdate-row').remove();
            } else {
                $(this).closest('.rbfw-me-offdate-row').find('input[type="date"]').val('');
            }
        });
    }

    /* ── Pricing rent-type switching ────────────────────────── */
    function initPricingTypeSwitch() {
        var $pricing = $wrap.find('.rbfw-me-panel[data-panel="pricing"]');
        if (!$pricing.length) return;

        function applyType(type) {
            $pricing.attr('data-item-type', type);
            // Reset — hide all switchable sections
            $pricing.find('.rbfw_bike_car_sd_wrapper').hide();
            $pricing.find('.rbfw_resort_price_config_wrapper').hide();
            $pricing.find('.rbfw_general_price_config_wrapper').hide();
            $pricing.find('.rbfw_multiple_items').hide();
            $pricing.find('.rbfw_switch_sd_appointment_row').addClass('hide').removeClass('show').hide();
            $pricing.find('section.appointment-onday').addClass('hide').hide();
            $pricing.find('.rbfw_discount_price_config_wrapper').hide();
            $pricing.find('.rbfw_seasonal_price_config_wrapper:not(.rbfw-sp-modern-panel):not(.mds_price_resort):not(.mds_price_md)').hide();
            $pricing.find('.mds_price_resort, .mds_price_md').hide();

            if (type === 'bike_car_sd') {
                $pricing.find('.rbfw_bike_car_sd_wrapper').show();
                if (typeof window.rbfwSetTimelyInventorySection === 'function') {
                    window.rbfwSetTimelyInventorySection($pricing, true);
                }
                $pricing.find('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').show();
                syncTimelyUI($pricing);

            } else if (type === 'appointment') {
                $pricing.find('.rbfw_bike_car_sd_wrapper').show();
                if (typeof window.rbfwSetTimelyInventorySection === 'function') {
                    window.rbfwSetTimelyInventorySection($pricing, false);
                }
                $pricing.find('.rbfw_time_inventory').hide();
                $pricing.find('.rbfw_item_stock_quantity').hide();
                $pricing.find('.rbfw_switch_sd_appointment_row').removeClass('hide').addClass('show').show();
                $pricing.find('section.appointment-onday').removeClass('hide').show();
                $pricing.find('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').hide();
                $pricing.find('.rbfw_without_time_inventory').show();

            } else if (type === 'resort') {
                $pricing.find('.rbfw_resort_price_config_wrapper').show();
                $pricing.find('.rbfw_discount_price_config_wrapper').show();
                $pricing.find('.mds_price_resort').show();

            } else if (type === 'multiple_items') {
                $pricing.find('.rbfw_multiple_items').show();
                $pricing.find('.rbfw_bike_car_sd_price_table_action_column,.rbfw_bike_car_sd_price_table_add_new_type_btn_wrap').show();
                syncTimelyUI($pricing);

            } else {
                // bike_car_md and legacy aliases
                $pricing.find('.rbfw_general_price_config_wrapper').show();
                $pricing.find('.rbfw_discount_price_config_wrapper').show();
                $pricing.find('.mds_price_md').show();
            }

            // Inventory card (stock + variations): mirror the classic editor, which
            // hides inventory for resort / appointment. Single Day (bike_car_sd) now
            // supports item variations, so its inventory card stays visible.
            var _invShow = (type !== 'resort' && type !== 'appointment');
            $pricing.find('.rbfw-me-inventory-card').toggleClass('rbfw-me-hidden', !_invShow);

            // Inventory sub-sections that only apply to specific rent types:
            //  - Return-date release: date-range rentals only (hide for Single Day & Appointment).
            //  - Multiple-item selection: multi-day Bike/Car, Dress, Equipment & Others only.
            $pricing.find('.rbfw_stock_return_date_section').toggle(type !== 'bike_car_sd' && type !== 'appointment');
            $pricing.find('.rbfw_switch_md_type_item_qty').toggle(
                type === 'bike_car_md' || type === 'dress' || type === 'equipment' || type === 'others'
            );

            // Location card (Advanced step): available for every rent type
            // ( multi-location feature ).
            $wrap.find('.rbfw-me-location-card').removeClass('rbfw-me-hidden');

            if (typeof window.rbfwMdsSyncPanelForRentType === 'function') {
                window.rbfwMdsSyncPanelForRentType(type, $pricing);
            }

            if (typeof window.rbfwSpSyncSeasonalPanelForRentType === 'function') {
                window.rbfwSpSyncSeasonalPanelForRentType(type, $pricing);
            }

            // Extra service sections: one category per rental type (initial load + type change).
            if (typeof window.rbfwUpdateExtraServiceSectionVisibility === 'function') {
                window.rbfwUpdateExtraServiceSectionVisibility(type, $pricing);
            }

            // Update description box
            var $card = $pricing.find('.rbfw-rent-type[data-rent-type="' + type + '"]');
            if ($card.length) {
                var desc = $card.data('rent-type-desc') || '';
                var name = $card.clone().find('.icon').remove().end().text().trim();
                $pricing.find('.rbfw-rent-type-desc').html('<strong class="rbfw-rent-type-desc-name">' + name + '</strong>' + desc);
            }
        }

        var savedType = $pricing.find('#rbfw_item_type').val() || 'bike_car_sd';
        applyType(savedType);

        // applyType show/hide sequences can disturb the PHP-rendered display:none on
        // the SD time-slots-section. Re-enforce it from the hidden input value so the
        // initial state always matches the saved DB value regardless of execution order.
        (function syncSdTimeSlotsOnLoad() {
            var $sdWrap = $pricing.find('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper');
            if (!$sdWrap.length) return;
            var enabled = $sdWrap.find('[name="rbfw_enable_time_picker"]').val() === 'yes';
            $sdWrap.find('.time-slots-section').css('display', enabled ? 'block' : 'none');
        }());

        $pricing.on('click', '.rbfw-rent-type', function () {
            var type = $(this).data('rent-type');
            $pricing.find('#rbfw_item_type').val(type);
            $pricing.find('.rbfw-rent-type').removeClass('selected');
            $(this).addClass('selected');
            applyType(type);
        });
    }

    /* ── Particular date time slots toggle (all rent types) ─── */
    function initParticularSwitch() {
        $wrap.on('change', '.rbfw_particular_switch', function () {
            var $input  = $(this);
            var enabled = this.checked;

            $input.val(enabled ? 'on' : 'off');

            var $panel = $input.closest('.mpStyle').children('.available-particular').first();
            if (!$panel.length) {
                $panel = $input.closest('.mpStyle').find('.available-particular').first();
            }

            if (enabled) {
                $panel.stop(true, true).slideDown().removeClass('hide').addClass('show');
            } else {
                $panel.stop(true, true).slideUp().removeClass('show').addClass('hide');
            }
        });

        // Align value attribute with saved checked state on load
        $wrap.find('.rbfw_particular_switch').each(function () {
            $(this).val(this.checked ? 'on' : 'off');
        });
    }

    /* ── Multiple Day Pricing Interactivity ─────────────────── */
    function initMdPricing() {
        var $pricing = $wrap.find('.rbfw-me-panel[data-panel="pricing"]');
        if (!$pricing.length) return;

        var $md = $pricing.find('.rbfw_general_price_config_wrapper');
        if (!$md.length) return;

        var monthlyPriceEnabled   = $md.find('#rbfw_enable_monthly_rate').val() === 'yes';
        var weeklyPriceEnabled    = $md.find('#rbfw_enable_weekly_rate').val() === 'yes';
        var dailyPriceEnabled     = $md.find('#rbfw_enable_daily_rate').val() === 'yes';
        var monthThresholdEnabled = $md.find('#rbfw_enable_day_threshold_for_monthly').val() === 'yes';
        var weekThresholdEnabled  = $md.find('#rbfw_enable_day_threshold_for_weekly').val() === 'yes';
        var timePickerEnabled     = $md.find('#rbfw_enable_time_picker').val() === 'yes';
        var hourlyPriceEnabled    = $md.find('#rbfw_enable_hourly_rate').val() === 'yes';
        var halfDayPriceEnabled   = $md.find('#rbfw_enable_half_day_rate').val() === 'yes';
        var hourThresholdEnabled  = $md.find('#rbfw_enable_hourly_threshold').val() === 'yes';

        // rbfw-md-hidden beats .md-price-card .item { display:flex !important }
        // via higher selector specificity with its own !important
        function mdHide($el) { $el.addClass('rbfw-md-hidden'); }
        function mdShow($el) { $el.removeClass('rbfw-md-hidden'); }

        function updateDaywiseVisibility() {
            var atLeastOne = dailyPriceEnabled || (timePickerEnabled && (hourlyPriceEnabled || halfDayPriceEnabled));
            $md.find('#rbfw-daywise-config-wrapper').css('display', atLeastOne ? '' : 'none');
        }

        function applyInitialState() {
            // Monthly price
            $md.find('.monthly-price-toggle').toggleClass('active', monthlyPriceEnabled);
            $md.find('#monthly-price-input').prop('disabled', !monthlyPriceEnabled);
            $md.find('.day-threshold-item-for-month').toggleClass('rbfw-md-hidden', !monthlyPriceEnabled);

            // Monthly threshold
            $md.find('.day-threshold-toggle-for-month').toggleClass('active', monthThresholdEnabled);
            $md.find('#day-threshold-input-for-monthly').prop('disabled', !monthThresholdEnabled);

            // Weekly price
            $md.find('.weekly-price-toggle').toggleClass('active', weeklyPriceEnabled);
            $md.find('#weekly-price-input').prop('disabled', !weeklyPriceEnabled);
            $md.find('.day-threshold-item-for-week').toggleClass('rbfw-md-hidden', !weeklyPriceEnabled);

            // Weekly threshold
            $md.find('.day-threshold-toggle-for-week').toggleClass('active', weekThresholdEnabled);
            $md.find('#day-threshold-input-for-weekly').prop('disabled', !weekThresholdEnabled);

            // Daily price
            $md.find('.daily-price-toggle').toggleClass('active', dailyPriceEnabled);
            $md.find('#daily-price-input').prop('disabled', !dailyPriceEnabled);
            $md.find('.rbfw-daywise-dailyprice-col').css('display', dailyPriceEnabled ? '' : 'none');

            // Time picker — half-day/hourly rows and time slots
            $md.find('.time-picker-toggle').toggleClass('active', timePickerEnabled);
            $md.find('.hourly-price-item').toggleClass('rbfw-md-hidden', !timePickerEnabled);
            $md.find('.time-slots-section').css('display', timePickerEnabled ? 'block' : 'none');

            // Half-day / hourly / hour-threshold all require the time picker. When it is
            // off, force those dependent toggles off so the saved data stays consistent
            // (otherwise a previously-enabled hourly/half-day stays "yes" while hidden).
            if (!timePickerEnabled) {
                hourlyPriceEnabled   = false;
                halfDayPriceEnabled  = false;
                hourThresholdEnabled = false;
                $md.find('#rbfw_enable_hourly_rate, #rbfw_enable_half_day_rate, #rbfw_enable_hourly_threshold').val('no');
            }

            // Hourly price
            $md.find('.hourly-price-toggle').toggleClass('active', hourlyPriceEnabled);
            $md.find('#hourly-price-input').prop('disabled', !hourlyPriceEnabled);
            $md.find('.hour-threshold-item').toggleClass('rbfw-md-hidden', !(hourlyPriceEnabled && timePickerEnabled));
            $md.find('.rbfw-daywise-hourly-col').css('display', (timePickerEnabled && hourlyPriceEnabled) ? '' : 'none');

            // Half-day price
            $md.find('.half-day-price-toggle').toggleClass('active', halfDayPriceEnabled);
            $md.find('#half-day-price-input').prop('disabled', !halfDayPriceEnabled);
            $md.find('.half-day-price-item').toggleClass('rbfw-md-hidden', !(halfDayPriceEnabled && timePickerEnabled));
            $md.find('.rbfw-daywise-halfday-col').css('display', (timePickerEnabled && halfDayPriceEnabled) ? '' : 'none');

            // Hour threshold
            $md.find('.hour-threshold-toggle').toggleClass('active', hourThresholdEnabled);
            $md.find('#hour-threshold-input').prop('disabled', !hourThresholdEnabled);

            updateDaywiseVisibility();
        }

        applyInitialState();

        // ── Duration Rate Toggles ────────────────────────────────

        $md.on('click', '.monthly-price-toggle', function () {
            monthlyPriceEnabled = !monthlyPriceEnabled;
            $(this).toggleClass('active', monthlyPriceEnabled);
            $md.find('#monthly-price-input').prop('disabled', !monthlyPriceEnabled);
            $md.find('#rbfw_enable_monthly_rate').val(monthlyPriceEnabled ? 'yes' : 'no');
            $md.find('.day-threshold-item-for-month').toggleClass('rbfw-md-hidden', !monthlyPriceEnabled);
        });

        $md.on('click', '.weekly-price-toggle', function () {
            weeklyPriceEnabled = !weeklyPriceEnabled;
            $(this).toggleClass('active', weeklyPriceEnabled);
            $md.find('#weekly-price-input').prop('disabled', !weeklyPriceEnabled);
            $md.find('#rbfw_enable_weekly_rate').val(weeklyPriceEnabled ? 'yes' : 'no');
            $md.find('.day-threshold-item-for-week').toggleClass('rbfw-md-hidden', !weeklyPriceEnabled);
        });

        $md.on('click', '.daily-price-toggle', function () {
            dailyPriceEnabled = !dailyPriceEnabled;
            $(this).toggleClass('active', dailyPriceEnabled);
            $md.find('#daily-price-input').prop('disabled', !dailyPriceEnabled);
            $md.find('#rbfw_enable_daily_rate').val(dailyPriceEnabled ? 'yes' : 'no');
            $md.find('.rbfw-daywise-dailyprice-col').css('display', dailyPriceEnabled ? '' : 'none');
            updateDaywiseVisibility();
        });

        // ── Threshold Toggles ───────────────────────────────────

        $md.on('click', '.day-threshold-toggle-for-month', function () {
            monthThresholdEnabled = !monthThresholdEnabled;
            $(this).toggleClass('active', monthThresholdEnabled);
            $md.find('#day-threshold-input-for-monthly').prop('disabled', !monthThresholdEnabled);
            $md.find('#rbfw_enable_day_threshold_for_monthly').val(monthThresholdEnabled ? 'yes' : 'no');
        });

        $md.on('click', '.day-threshold-toggle-for-week', function () {
            weekThresholdEnabled = !weekThresholdEnabled;
            $(this).toggleClass('active', weekThresholdEnabled);
            $md.find('#day-threshold-input-for-weekly').prop('disabled', !weekThresholdEnabled);
            $md.find('#rbfw_enable_day_threshold_for_weekly').val(weekThresholdEnabled ? 'yes' : 'no');
        });

        // ── Time Configuration ──────────────────────────────────

        $md.on('click', '.time-picker-toggle', function () {
            timePickerEnabled = !timePickerEnabled;
            $(this).toggleClass('active', timePickerEnabled);
            // Time Picker off → force every dependent toggle off & disabled.
            if (!timePickerEnabled) {
                hourlyPriceEnabled   = false;
                halfDayPriceEnabled  = false;
                hourThresholdEnabled = false;
                $md.find('.hourly-price-toggle, .half-day-price-toggle, .hour-threshold-toggle').removeClass('active');
                $md.find('#rbfw_enable_hourly_rate, #rbfw_enable_half_day_rate, #rbfw_enable_hourly_threshold').val('no');
                $md.find('#hourly-price-input, #half-day-price-input, #hour-threshold-input').prop('disabled', true);
            }
            $md.find('.hourly-price-item').toggleClass('rbfw-md-hidden', !timePickerEnabled);
            $md.find('.time-slots-section').css('display', timePickerEnabled ? 'block' : 'none');
            // Sub-rows also depend on time picker being active
            $md.find('.half-day-price-item').toggleClass('rbfw-md-hidden', !(timePickerEnabled && halfDayPriceEnabled));
            $md.find('.hour-threshold-item').toggleClass('rbfw-md-hidden', !(timePickerEnabled && hourlyPriceEnabled));
            $md.find('.rbfw-daywise-hourly-col').css('display', (timePickerEnabled && hourlyPriceEnabled) ? '' : 'none');
            $md.find('.rbfw-daywise-halfday-col').css('display', (timePickerEnabled && halfDayPriceEnabled) ? '' : 'none');
            $md.find('#rbfw_enable_time_picker').val(timePickerEnabled ? 'yes' : 'no');
            $md.find('.rbfw_enable_time_picker').val(timePickerEnabled ? 'yes' : 'no');
            updateDaywiseVisibility();
        });

        $md.on('click', '.hourly-price-toggle', function () {
            if (!timePickerEnabled) { return; } // requires Time Picker
            hourlyPriceEnabled = !hourlyPriceEnabled;
            $(this).toggleClass('active', hourlyPriceEnabled);
            $md.find('#hourly-price-input').prop('disabled', !hourlyPriceEnabled);
            $md.find('#rbfw_enable_hourly_rate').val(hourlyPriceEnabled ? 'yes' : 'no');
            $md.find('.rbfw-daywise-hourly-col').css('display', (hourlyPriceEnabled && timePickerEnabled) ? '' : 'none');
            $md.find('.hour-threshold-item').toggleClass('rbfw-md-hidden', !(hourlyPriceEnabled && timePickerEnabled));
            updateDaywiseVisibility();
        });

        $md.on('click', '.half-day-price-toggle', function () {
            if (!timePickerEnabled) { return; } // requires Time Picker
            halfDayPriceEnabled = !halfDayPriceEnabled;
            $(this).toggleClass('active', halfDayPriceEnabled);
            $md.find('#half-day-price-input').prop('disabled', !halfDayPriceEnabled);
            $md.find('#rbfw_enable_half_day_rate').val(halfDayPriceEnabled ? 'yes' : 'no');
            $md.find('.half-day-price-item').toggleClass('rbfw-md-hidden', !(halfDayPriceEnabled && timePickerEnabled));
            $md.find('.rbfw-daywise-halfday-col').css('display', (halfDayPriceEnabled && timePickerEnabled) ? '' : 'none');
            updateDaywiseVisibility();
        });

        $md.on('click', '.hour-threshold-toggle', function () {
            if (!timePickerEnabled) { return; } // requires Time Picker
            hourThresholdEnabled = !hourThresholdEnabled;
            $(this).toggleClass('active', hourThresholdEnabled);
            $md.find('#hour-threshold-input').prop('disabled', !hourThresholdEnabled);
            $md.find('#rbfw_enable_hourly_threshold').val(hourThresholdEnabled ? 'yes' : 'no');
        });

        $md.on('change', '#hour-threshold-input', function () {
            $md.find('#hour-threshold-display').text($(this).val());
        });

        // ── Day-wise Pricing Toggle ──────────────────────────────

        $md.on('click', '.daywise-price-toggle', function () {
            var $toggle  = $(this);
            var $wrapper = $toggle.closest('#rbfw-daywise-config-wrapper');
            var $input   = $toggle.closest('.item-right').find('input[name="rbfw_enable_daywise_price"]');
            var enabled  = !$toggle.hasClass('active');

            $toggle.toggleClass('active', enabled);
            $input.val(enabled ? 'yes' : 'no');

            var $panel = $wrapper.children('.day-wise-price-configuration');
            if (enabled) {
                $panel.stop(true, true).slideDown().removeClass('hide').addClass('show');
            } else {
                $panel.stop(true, true).slideUp().removeClass('show').addClass('hide');
            }
        });

        // ── Particular Date Time Slots Toggle — see initParticularSwitch() ──

        // ── Time Slot Management ─────────────────────────────────

        $md.on('click', '.time-slot-remove', function (e) {
            e.stopPropagation();
            $(this).closest('.time-slot').remove();
        });

        $md.on('click', '.time-slot-indicator', function () {
            var $indicator   = $(this);
            var $timeSlot    = $indicator.closest('.time-slot');
            var $statusInput = $timeSlot.find('input[name*="[status]"]');
            $indicator.toggleClass('active');
            if ($indicator.hasClass('active')) {
                $statusInput.val('enabled');
                $timeSlot.removeClass('disabled').addClass('enabled');
            } else {
                $statusInput.val('');
                $timeSlot.removeClass('enabled').addClass('disabled');
            }
        });

        $md.on('change', '.new-slot-time', function () {
            $(this).closest('.add-slot-form').find('.add-slot-btn').prop('disabled', !$(this).val());
        });

        $md.on('click', '.add-slot-btn', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var time     = $btn.closest('.add-slot-form').find('.new-slot-time').val();
            if (!time) return;

            var nameAttr = $btn.data('name_attr');
            var rentType = $btn.data('rent_type');
            var $slotsContainer = $btn.closest('.add-slot-container').prevAll('.time-slots-container').first().find('.time-slots');

            var isDuplicate = $slotsContainer.find('.time-slot-time').filter(function () {
                return $(this).text() === time;
            }).length > 0;
            if ( isDuplicate ) {
                $btn.closest('.add-slot-form').find('.rbfw-slot-duplicate-warning').remove();
                var $warning = $('<span class="rbfw-slot-duplicate-warning" style="display:block;color:#c0392b;font-size:12px;margin-top:4px;">' +
                    '<span class="dashicons dashicons-warning"></span> This time slot already exists.</span>');
                $btn.after($warning);
                setTimeout(function () { $warning.remove(); }, 3000);
                return;
            }

            var index = $slotsContainer.children('.time-slot').length;

            var newSlot = '';
            if (nameAttr === 'rdfw_available_time' && rentType === 'md') {
                newSlot =
                    '<div class="time-slot enabled" data-id="' + index + '">' +
                    '<span class="time-slot-time">' + time + '</span>' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][id]" value="' + index + '">' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][time]" value="' + time + '">' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][status]" value="enabled">' +
                    '<div class="time-slot-remove" title="' + (rbfwModernEditor_i18n('Remove time slot') || 'Remove time slot') + '">×</div>' +
                    '</div>';
            }

            if (!newSlot) return;

            $slotsContainer.append(newSlot);

            var $slots = $slotsContainer.children('.time-slot');
            $slots.sort(function (a, b) {
                return $(a).find('.time-slot-time').text().localeCompare($(b).find('.time-slot-time').text());
            });
            $slotsContainer.html($slots);

            $btn.closest('.add-slot-form').find('.new-slot-time').val('');
            $btn.prop('disabled', true);
        });
    }

    /* ── Collect all form values ─────────────────────────────── */
    function collectFormData() {
        var data = {};

        // Text / number / select inputs (skip checkboxes — handled below)
        $wrap.find('input[name], select[name], textarea[name]').each(function () {
            var name = $(this).attr('name');
            if (! name) return;
            var type = $(this).attr('type');
            // Skip category checkboxes (handled separately) and toggle checkboxes
            if (type === 'checkbox' && $(this).hasClass('rbfw-me-cat-checkbox')) return;
            if (type === 'checkbox') {
                data[name] = this.checked ? $(this).val() : '';
            } else if (name.slice(-2) === '[]') {
                var baseName = name.slice(0, -2);
                if (!Array.isArray(data[baseName])) data[baseName] = [];
                data[baseName].push($(this).val());
            } else {
                data[name] = $(this).val();
            }
        });

        // Categories: send the hidden comma-separated value as array items
        var catsVal = $wrap.find('.rbfw-me-cats-hidden').val();
        if (catsVal) {
            data['rbfw_categories'] = catsVal.split(',').filter(Boolean);
        } else {
            data['rbfw_categories'] = [];
        }

        // TinyMCE content — get active editor instance if available
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get('rbfw_me_post_content');
            if (ed && !ed.isHidden()) {
                data.post_content = ed.getContent();
            }
        }

        return data;
    }

    function rbfwModernEditor_i18n(key) {
        return cfg.i18n && cfg.i18n[key] ? cfg.i18n[key] : null;
    }

}(jQuery));
