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
        initParticularSlotPicker();
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
            var $panel = $(this).closest('.rbfw-me-panel');
            syncTimelyUI($panel);

            // Time-slot bookings need Enable Time Picker on to actually collect
            // a time -- Pricing.php's save-time validation already requires this
            // combination (unless "Enable duration-based rental items" is on,
            // which supplies its own fixed Start/End Time instead and doesn't
            // need the picker). Auto-enable it here instead of only telling the
            // admin about the missing toggle after a failed save.
            var isSpecificDurationOn = $panel.find('[name="enable_specific_duration"]').is(':checked');
            if (this.checked && ! isSpecificDurationOn) {
                var $sdWrap = $panel.find('.rbfw_bike_car_sd_wrapper');
                var $tpToggle = $sdWrap.find('.time-picker-toggle');
                if ($tpToggle.length && ! $tpToggle.hasClass('active')) {
                    $tpToggle.addClass('active');
                    $sdWrap.find('.rbfw_enable_time_picker').val('yes');
                    $sdWrap.find('.time-slots-section').show();
                }
            }
        });

        $wrap.on('click', '[name="enable_specific_duration"]', function (e) {
            e.stopPropagation();
            $(this).val(this.checked ? 'on' : 'off');
            syncTimelyUI($(this).closest('.rbfw-me-panel'));
        });

        // Category-wise extra services toggle. Old-style toggle: the value attribute
        // ('on'/'off') is what collectFormData() persists, and the classic
        // mkb-admin.js handler self-disables inside .rbfw-me-wrap — so sync the value
        // here and reveal/hide the service-category fields, mirroring the classic UI.
        $wrap.on('click', '[name="rbfw_enable_category_service_price"]', function (e) {
            e.stopPropagation();
            $(this).val(this.checked ? 'on' : 'off');
            var $fields = $wrap.find('#field-wrapper-rbfw_service_category_price');
            if (this.checked) {
                $fields.stop(true, true).slideDown(200).removeClass('hide').addClass('show');
            } else {
                $fields.stop(true, true).slideUp(200).removeClass('show').addClass('hide');
            }
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

        // "Rent Item Stock Quantity" lives in the Inventory card, a separate
        // panel from this toggle (see RBFW_Inventory::stock_settings()) — mirror
        // the toggle's live state there too instead of waiting on a save+reload.
        // Single Day only (matches $show_timely_stock in Inventory.php): this
        // toggle checkbox only has real meaning for bike_car_sd, but its
        // saved value persists even after switching an item to another type
        // (e.g. Multiple Day), so without the type check here an item saved
        // with manage_inventory_as_timely=on would show this section
        // *alongside* Multiple Day's own always-visible Stock Quantity field
        // -- two stock-qty fields at once.
        $wrap.find('.rbfw-me-inventory-card .rbfw_timely_stock_quantity_section').toggle(isTimely && _meType === 'bike_car_sd');
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
        // Never abort the request on a validation failure — doing so used to
        // silently discard every Advanced-tab change (Template, FAQ toggle, Tax,
        // Security Deposit, Terms, Related, Front-end Display, …) whenever the
        // pricing/description/time-slots were still incomplete.
        //
        //   • A draft may legitimately be incomplete, so it always saves as-is.
        //   • Publishing still runs the full client-side checks so the exact
        //     problems are highlighted inline; but instead of blocking, an
        //     incomplete item is saved as a *draft* so nothing the user entered
        //     is lost. This mirrors the server, which keeps an incomplete item
        //     out of "publish" status and returns the pricing errors.
        if ( status === 'publish' && ! validateBeforeSave() ) {
            status = 'draft';
        }

        setSaveIndicator('saving', cfg.i18n && cfg.i18n.saving || 'Saving your changes…');

        var data = collectFormData();
        data.action      = 'rbfw_modern_editor_save';
        data.nonce       = cfg.nonce_save || '';
        data.post_id     = postId;
        data.post_status = status;

        $.post(cfg.ajax_url, data, function (res) {
            if (res.success) {
                var savedStatus = (res.data && res.data.post_status) || status;
                var pricingErrors = (res.data && res.data.pricing_errors) || [];

                if (status === 'publish' && pricingErrors.length && savedStatus !== 'publish') {
                    // Everything was saved, but the item couldn't go live because the
                    // pricing setup is incomplete — surface that without discarding input.
                    setSaveIndicator('error', pricingErrors.join(' '));
                } else {
                    setSaveIndicator('saved', cfg.i18n && cfg.i18n.saved || 'All changes saved');
                    setTimeout(function () { setSaveIndicator('', ''); }, 4500);
                }

                // Update publish button label if status changed
                if (savedStatus === 'publish') {
                    $wrap.find('.rbfw-me-publish').text(cfg.i18n && cfg.i18n.update || 'Update').data('published', '1');
                }
                // Update status dot
                var $dot   = $wrap.find('.rbfw-me-status-dot');
                var $label = $wrap.find('.rbfw-me-status-label');
                $dot.attr('class', 'rbfw-me-status-dot rbfw-me-status-dot--' + savedStatus);
                $label.text(savedStatus.charAt(0).toUpperCase() + savedStatus.slice(1));
                $wrap.find('select.rbfw-me-select[name="post_status"]').val(savedStatus);
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
                    '<label class="rbfw-me-checkbox-label rbfw-rt-chip' + (checked ? ' is-checked' : '') + (depth > 0 ? ' rbfw-rt-child' : '') + '" data-term-id="' + rtEsc(rt.term_id) + '" data-name="' + rtEsc(rt.name) + '" data-parent="' + rtEsc(rt.parent || 0) + '" data-depth="' + depth + '" data-image-id="' + rtEsc(rt.image_id || 0) + '" data-image-url="' + rtEsc(rt.image_url || '') + '"' + indent + '>' +
                        '<input type="checkbox" class="rbfw-me-cat-checkbox" data-name="' + rtEsc(rt.name) + '"' + checkedAttr + ' />' +
                        '<span>' + prefix + rtEsc(rt.name.charAt(0).toUpperCase() + rt.name.slice(1)) + '</span>' +
                        meActionsHtml() +
                    '</label>'
                );
            });
            meHidden().val(current.join(','));
            $wrap.find('.rbfw-me-rent-type-empty').toggleClass('rbfw-me-hidden', rentTypes.length > 0);
        }

        var rtImagePlaceholderHtml = '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>';

        function openRentTypeModal(mode, termId, name, imageId, imageUrl) {
            meEditTermId = mode === 'edit' ? (parseInt(termId, 10) || 0) : 0;
            var isEdit = meEditTermId > 0;
            var $modal = $wrap.find('#rbfw-me-rent-type-modal');
            $modal.find('.rbfw-me-faq-modal__head h3').text(isEdit ? 'Rename Rent Type' : 'Add New Rent Type');
            $modal.find('#rbfw-me-rent-type-modal-save').text(isEdit ? 'Save Changes' : 'Add Rent Type');
            $modal.find('#rbfw-me-rent-type-modal-input').val(name || '');

            var $preview = $modal.find('#rbfw-me-rent-type-modal-image-preview');
            var $removeBtn = $modal.find('#rbfw-me-rent-type-modal-image-remove');
            imageId = parseInt(imageId, 10) || 0;
            $modal.find('#rbfw-me-rent-type-modal-image-id').val(imageId || '');
            if (imageId && imageUrl) {
                $preview.html('<img src="' + imageUrl + '" alt="" />');
                $removeBtn.show();
            } else {
                $preview.html(rtImagePlaceholderHtml);
                $removeBtn.hide();
            }

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
            openRentTypeModal('edit', $chip.data('term-id'), $chip.data('name'), $chip.data('image-id'), $chip.data('image-url'));
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

        // Rent type image — same wp.media() pattern used for the item's featured image.
        var rtMediaFrame;
        $wrap.on('click', '#rbfw-me-rent-type-modal-image-select', function (e) {
            e.preventDefault();
            if (rtMediaFrame) { rtMediaFrame.open(); return; }
            rtMediaFrame = wp.media({
                title:    rbfwModernEditor_i18n('Select Image') || 'Select Image',
                button:   { text: rbfwModernEditor_i18n('Use this image') || 'Use this image' },
                multiple: false,
                library:  { type: 'image' }
            });
            rtMediaFrame.on('select', function () {
                var attachment = rtMediaFrame.state().get('selection').first().toJSON();
                $wrap.find('#rbfw-me-rent-type-modal-image-id').val(attachment.id);
                $wrap.find('#rbfw-me-rent-type-modal-image-preview').html('<img src="' + attachment.url + '" alt="" />');
                $wrap.find('#rbfw-me-rent-type-modal-image-remove').show();
            });
            rtMediaFrame.open();
        });

        $wrap.on('click', '#rbfw-me-rent-type-modal-image-remove', function (e) {
            e.preventDefault();
            $wrap.find('#rbfw-me-rent-type-modal-image-id').val('');
            $wrap.find('#rbfw-me-rent-type-modal-image-preview').html(rtImagePlaceholderHtml);
            $(this).hide();
        });

        $wrap.on('click', '#rbfw-me-rent-type-modal-save', function () {
            var $input = $wrap.find('#rbfw-me-rent-type-modal-input');
            var name   = $.trim($input.val());
            if (!name) { $input.trigger('focus'); return; }
            if (name.length > 200) { name = name.substring(0, 200); }
            var imageId = parseInt($wrap.find('#rbfw-me-rent-type-modal-image-id').val(), 10) || 0;

            if (meEditTermId > 0) {
                $.post(window.ajaxurl, {
                    action: 'rbfw_rent_type_rename',
                    nonce:  meNonce(),
                    term_id: meEditTermId,
                    name:   name,
                    image_id: imageId
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
                    image_id: imageId
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
            // Multiple Items carries per-item stock in its own pricing table, so the
            // card-level inventory does not apply to it.
            var _invShow = (type !== 'resort' && type !== 'appointment' && type !== 'multiple_items');
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

    /**
     * Particular-date-range time slot picker: tap any globally-configured
     * slot to add/remove it from that specific date range, replacing the
     * previous "type a time + Add Slot" flow for these rows (which saved
     * every added slot under row index 0 regardless of which row's button
     * was actually clicked -- a pre-existing bug in both this file and
     * mkb-admin.js, not something introduced here).
     *
     * Shared by Single Day, Multiple Day and Multiple Items (all three call
     * RBFW_Pricing::multiple_time_slot_with_particular() with the same
     * markup), so this is intentionally NOT scoped to any one rent type's
     * wrapper -- it scopes itself per `.time-slots-section` instead, since
     * each rent type's section has its own self-contained global slot list
     * and its own particular-date rows.
     */
    function initParticularSlotPicker() {
        function $sectionOf($el) {
            return $el.closest('.time-slots-section');
        }
        function globalSlotTimes($section) {
            var times = [];
            $section.find('#time-slots-container .time-slot').each(function () {
                var $slot = $(this);
                if ($slot.find('input[name*="[status]"]').val() !== 'enabled') { return; }
                var t = $.trim($slot.find('.time-slot-time').text());
                if (t && times.indexOf(t) === -1) { times.push(t); }
            });
            return times;
        }
        function syncRow($pickerWrap) {
            var $hidden = $pickerWrap.siblings('.rbfw-particular-hidden-slots').empty();
            var baseName = $pickerWrap.data('base-name');
            var particularId = $pickerWrap.data('particular-id');
            if (baseName === undefined || particularId === undefined || particularId === '') { return; }
            var j = 0;
            $pickerWrap.find('.slotchip-pick.active').each(function () {
                var t = $(this).text();
                $hidden.append(
                    '<input type="hidden" class="rbfw-particular-slot-time" name="' + baseName + '[' + particularId + '][available_time][' + j + '][id]" value="' + j + '">' +
                    '<input type="hidden" name="' + baseName + '[' + particularId + '][available_time][' + j + '][time]" value="' + t + '">' +
                    '<input type="hidden" name="' + baseName + '[' + particularId + '][available_time][' + j + '][status]" value="enabled">'
                );
                j++;
            });
            $pickerWrap.find('.rbfw-slot-picker-empty').toggle(j === 0);
        }
        function buildPicker($pickerWrap) {
            var $section = $sectionOf($pickerWrap);
            var $hidden = $pickerWrap.siblings('.rbfw-particular-hidden-slots');
            var selected = {};
            $hidden.find('input[name*="[time]"]').each(function () {
                selected[$.trim($(this).val())] = true;
            });
            var $picker = $pickerWrap.find('.rbfw-slot-picker').empty();
            globalSlotTimes($section).forEach(function (t) {
                var $chip = $('<button type="button" class="slotchip-pick"></button>').text(t);
                if (selected[t]) { $chip.addClass('active'); }
                $picker.append($chip);
            });
            syncRow($pickerWrap);
        }
        function refreshSection($section) {
            $section.find('.rbfw_pdwt_insert > .rbfw_pdwt_row .rbfw-slot-picker-wrap').each(function () {
                buildPicker($(this));
            });
        }

        // Initial build for every server-rendered particular row, on every
        // rent type's section present on this screen.
        $wrap.find('.time-slots-section').each(function () {
            refreshSection($(this));
        });

        // Toggle a slot chip in/out of this row's selection.
        $wrap.on('click', '.slotchip-pick', function () {
            $(this).toggleClass('active');
            syncRow($(this).closest('.rbfw-slot-picker-wrap'));
        });

        // Keep every row's picker in sync with the global slot list within
        // its own section (covers slots added via the existing global
        // .add-slot-btn, and removed via .time-slot-remove/.time-slot-indicator).
        $wrap.find('.time-slots-section').each(function () {
            var $section = $(this);
            var $globalSlots = $section.find('#time-slots-container')[0];
            if ($globalSlots && window.MutationObserver) {
                new MutationObserver(function () { refreshSection($section); }).observe($globalSlots, { childList: true });
            }
        });
        $wrap.on('click', '.time-slot-indicator', function () {
            var $section = $sectionOf($(this));
            setTimeout(function () { refreshSection($section); }, 0);
        });

        // Custom "+ Add new slot" within a particular row: adds the time to
        // this section's own global list too (so it becomes a normal,
        // reusable slot for every row in this section), then marks it
        // active for the row it was added from.
        $wrap.on('click', '.rbfw-slot-custom-add-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $pickerWrap = $btn.closest('.rbfw_pdwt_row, .mp_hidden_item').find('.rbfw-slot-picker-wrap');
            var $section = $sectionOf($btn);
            var rawTime = $btn.closest('.rbfw-slot-custom-add').find('.rbfw-slot-custom-time').val();
            if (! rawTime) { return; }

            var parts = rawTime.split(':');
            var h = parseInt(parts[0], 10);
            var period = h >= 12 ? 'PM' : 'AM';
            var h12 = h % 12 || 12;
            var time = h12 + ':' + parts[1] + ' ' + period;

            var $globalSlots = $section.find('#time-slots-container');
            var exists = $globalSlots.find('.time-slot-time').filter(function () { return $(this).text() === time; }).length > 0;
            if (! exists) {
                var index = $globalSlots.children('.time-slot').length;
                $globalSlots.append(
                    '<div class="time-slot enabled" data-id="' + index + '">' +
                    '<span class="time-slot-time">' + time + '</span>' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][id]" value="' + index + '">' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][time]" value="' + time + '">' +
                    '<input type="hidden" name="rdfw_available_time[' + index + '][status]" value="enabled">' +
                    '<div class="time-slot-remove" title="Remove time slot">×</div>' +
                    '</div>'
                );
            }
            refreshSection($section);
            $pickerWrap.find('.rbfw-slot-picker .slotchip-pick').filter(function () { return $(this).text() === time; }).addClass('active');
            syncRow($pickerWrap);
            $btn.closest('.rbfw-slot-custom-add').find('.rbfw-slot-custom-time').val('');
        });

        // Newly-cloned particular rows (via #add-particular-row, cloned in
        // mkb-admin.js from .mp_hidden_item): give the new row's picker the
        // correct particular_id and populate it. Deferred so this runs after
        // the clone has actually been appended to the DOM.
        $wrap.on('click', '#add-particular-row', function () {
            var $btn = $(this);
            setTimeout(function () {
                var $insert = $btn.closest('.available-particular').find('.rbfw_pdwt_insert');
                var $rows = $insert.children('.rbfw_pdwt_row');
                var $last = $rows.last();
                var $pickerWrap = $last.find('.rbfw-slot-picker-wrap');
                if (! $pickerWrap.length || $pickerWrap.attr('data-particular-id') !== undefined) { return; }
                var newId = $rows.length - 1;
                $pickerWrap.attr('data-particular-id', newId).data('particular-id', newId);
                buildPicker($pickerWrap);
            }, 0);
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

            // Half-day price requires the time picker; when it's off, force it off too so
            // the saved data stays consistent (otherwise a previously-enabled half-day
            // stays "yes" while hidden). Hourly Price now lives in the always-visible
            // Pricing table and the Day Threshold has no toggle at all in the redesigned
            // Thresholds card -- both are independent of the time picker.
            if (!timePickerEnabled) {
                halfDayPriceEnabled  = false;
                $md.find('#rbfw_enable_half_day_rate').val('no');
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
            // Time Picker off → force Half-Day (the only remaining dependent
            // toggle) off & disabled. Hourly Price and the Day Threshold are
            // independent of the time picker in the redesigned layout.
            if (!timePickerEnabled) {
                halfDayPriceEnabled = false;
                $md.find('.half-day-price-toggle').removeClass('active');
                $md.find('#rbfw_enable_half_day_rate').val('no');
                $md.find('#half-day-price-input').prop('disabled', true);
            }
            $md.find('.time-slots-section').css('display', timePickerEnabled ? 'block' : 'none');
            // Sub-rows also depend on time picker being active
            $md.find('.half-day-price-item').toggleClass('rbfw-md-hidden', !(timePickerEnabled && halfDayPriceEnabled));
            $md.find('.rbfw-daywise-hourly-col').css('display', (timePickerEnabled && hourlyPriceEnabled) ? '' : 'none');
            $md.find('.rbfw-daywise-halfday-col').css('display', (timePickerEnabled && halfDayPriceEnabled) ? '' : 'none');
            $md.find('#rbfw_enable_time_picker').val(timePickerEnabled ? 'yes' : 'no');
            $md.find('.rbfw_enable_time_picker').val(timePickerEnabled ? 'yes' : 'no');
            updateDaywiseVisibility();
        });

        $md.on('click', '.hourly-price-toggle', function () {
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
            } else if (type === 'radio') {
                // A radio group shares one name; only the *selected* option may
                // contribute its value. Without this guard the loop overwrites
                // data[name] with every radio's value in DOM order, so the last
                // option always won — e.g. the service_price_type radios always
                // saved "day_wise" no matter which the user actually picked.
                if (this.checked) {
                    data[name] = $(this).val();
                }
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

/**
 * Frontend Preview sidebar card (Pricing step).
 *
 * Visual language ported 1:1 from the approved design prototype's live
 * preview panel ("lp-*" classes), renamed "rbfw-me-fp-*" -- same colors,
 * type scale and spacing, rebuilt as plain markup/CSS/jQuery here.
 *
 * Functionally mirrors the real PHP calculation for each rent type so an
 * admin can sanity-check pricing before saving:
 *  - Single Day/Appointment: rbfw_bikecarsd_price_calculation() (inc/class-bike-car-sd-function.php) -- option price * qty.
 *  - Multiple Day/Equipment/Dress/Others: rbfw_md_duration_price_calculation() (inc/rbfw_functions.php) -- Monthly→Weekly→Daily tiers + Time Picker half-day/hourly leftover.
 *  - Resort: rbfw_resort_price_calculation()'s default branch (Frontend/RBFW_Woocommerse.php) -- rate * qty * total_days.
 * Day-wise overrides, seasonal pricing, fees, extra services/variations and
 * the tiered-pricing/multi-day-saver add-ons are intentionally not
 * replicated -- see the note printed in the card itself. A separate,
 * self-contained IIFE so a mistake here can't break the rest of the
 * editor's save/validate logic above.
 */
(function ($) {
    'use strict';

    $(function () {
        var $wrap = $('.rbfw-me-wrap');
        var $preview = $wrap.find('.rbfw-me-frontend-preview');
        var $pricingPanel = $wrap.find('.rbfw-me-panel[data-panel="pricing"]');
        if (! $wrap.length || ! $preview.length || ! $pricingPanel.length) {
            return;
        }

        var MD_TYPES = ['bike_car_md', 'equipment', 'dress', 'others'];
        var SD_TYPES = ['bike_car_sd', 'appointment'];
        var RESORT_TYPES = ['resort'];
        var MI_TYPES = ['multiple_items'];
        var currency = $preview.data('currency') || '$';
        var countExtraDay = $preview.data('count-extra-day') !== 'off';
        var $sdControls = $preview.find('.rbfw-me-fp-sd-controls');
        var $resortControls = $preview.find('.rbfw-me-fp-resort-controls');
        var $durationBanner = $preview.find('.rbfw-me-fp-duration-banner');
        var $warnSlot = $preview.find('.rbfw-me-fp-warn-slot');
        var $summary = $preview.find('.rbfw-me-fp-summary');

        $sdControls.data('qty', 1);
        $resortControls.data('qty', 1);

        function num(sel) {
            var v = parseFloat($pricingPanel.find(sel).val());
            return isNaN(v) ? 0 : v;
        }
        function isYes(sel) {
            return $pricingPanel.find(sel).first().val() === 'yes';
        }
        function money(n) {
            return currency + (Math.round((n + 1e-9) * 100) / 100).toFixed(2);
        }
        function pad2(n) {
            return (n < 10 ? '0' : '') + n;
        }
        function toIso(d) {
            return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        }
        function currentRentType() {
            return $wrap.find('#rbfw_item_type').val() || $pricingPanel.data('item-type') || 'bike_car_sd';
        }
        function activePanelKey() {
            return $wrap.find('.rbfw-me-panel.is-active').data('panel');
        }

        function summaryLine($rows, label, value) {
            $rows.append(
                $('<div class="rbfw-me-fp-summary-row"></div>')
                    .append($('<span></span>').text(label))
                    .append($('<span></span>').text(value))
            );
        }
        function showWarning(text) {
            $warnSlot.empty().append(
                $('<div class="rbfw-me-fp-warnbanner"></div>')
                    .append('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>')
                    .append($('<span></span>').text(text))
            );
        }
        function clearWarning() {
            $warnSlot.empty();
        }
        function renderOptrows($container, rows, selectedIdx, onSelect) {
            $container.empty();
            rows.forEach(function (row, i) {
                var $btn = $('<button type="button" class="rbfw-me-fp-optrow"></button>');
                if (i === selectedIdx) { $btn.addClass('sel'); }
                $btn.append($('<span></span>').text(row.label + (row.sub ? ' — ' + row.sub : '')));
                $btn.append($('<strong></strong>').text(money(row.price)));
                $btn.on('click', function () { onSelect(i); });
                $container.append($btn);
            });
        }

        // Default preview window: today → +3 days, long enough to show a
        // weekly tier if one is configured, without the admin having to set
        // dates themselves first.
        function ensureDefaultDates() {
            var $s = $preview.find('.rbfw-me-fp-start');
            var $e = $preview.find('.rbfw-me-fp-end');
            if (! $s.val()) {
                var today = new Date();
                var end = new Date();
                end.setDate(end.getDate() + 3);
                $s.val(toIso(today));
                $e.val(toIso(end));
            }
        }

        var $extras = $preview.find('.rbfw-me-fp-extras');
        var $variations = $preview.find('.rbfw-me-fp-variations');
        var $sdTimeField = $preview.find('.rbfw-me-fp-sd-time-field');
        var $mdTimeFields = $preview.find('.rbfw-me-fp-md-start-time-field, .rbfw-me-fp-md-end-time-field');
        $preview.data('extrasQty', {});
        $preview.data('variationsQty', {});

        /* ───────────── Time slots ─────────────
           Reads the GLOBAL slot list only (rdfw_available_time[_sd]) -- the
           optional per-date-range "Particular date time slots" override
           (rbfw_particulars[...][available_time]) is not reflected here. */
        function timeSlotValues(namePrefix) {
            var values = [];
            $wrap.find('input[name^="' + namePrefix + '["][name$="[time]"]').each(function () {
                var $timeInput = $(this);
                var m = $timeInput.attr('name').match(/\[(\d+)\]\[time\]$/);
                if (! m) { return; }
                var $status = $wrap.find('input[name="' + namePrefix + '[' + m[1] + '][status]"]');
                if (($status.length ? $status.val() : '') !== 'enabled') { return; }
                var time = $.trim($timeInput.val());
                if (time) { values.push(time); }
            });
            return values;
        }
        function parseTimeToMinutes(t) {
            if (! t) { return null; }
            var m = $.trim(t).match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
            if (! m) { return null; }
            var h = parseInt(m[1], 10);
            var min = parseInt(m[2], 10);
            var ap = m[3] ? m[3].toUpperCase() : null;
            if (ap === 'PM' && h !== 12) { h += 12; }
            if (ap === 'AM' && h === 12) { h = 0; }
            return (h * 60) + min;
        }
        // No value is pre-selected -- the customer must tap a chip, which is
        // what reveals the next step in the progressive booking flow.
        function renderTimeChips($container, values, selectedVal, onSelect) {
            $container.empty();
            if (! values.length) {
                $container.append($('<span class="rbfw-me-fp-svcrow-price"></span>').text('No time slots configured'));
                return null;
            }
            var active = (selectedVal && values.indexOf(selectedVal) !== -1) ? selectedVal : null;
            values.forEach(function (v) {
                var $chip = $('<button type="button" class="rbfw-me-fp-chip"></button>').text(v);
                if (v === active) { $chip.addClass('active'); }
                $chip.on('click', function () { onSelect(v); });
                $container.append($chip);
            });
            return active;
        }

        // Multiple Day's Pickup/Return Time render as a dropdown styled to
        // match the date fields (see the approved design), not chips.
        function populateTimeSelect($select, values) {
            var prev = $select.val();
            $select.empty();
            if (! values.length) {
                $select.append($('<option></option>').val('').text('No time slots configured'));
                $select.prop('disabled', true);
                return null;
            }
            $select.prop('disabled', false);
            values.forEach(function (v) {
                $select.append($('<option></option>').val(v).text(v));
            });
            var active = (prev && values.indexOf(prev) !== -1) ? prev : values[0];
            $select.val(active);
            return active;
        }

        /* ───────────── Mini calendar (Single Day / Appointment) ───────────── */

        var MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Resort's per-field "×" clear buttons -- bound once via delegation
        // since the buttons always exist in the DOM (just hidden outside
        // Resort). Clearing re-triggers whichever recalc the active type uses.
        // Pickup clears both fields (ensureDefaultDates() re-seeds them from
        // today the moment pickup is empty, so a pickup-only clear would
        // otherwise just silently snap back); return can be cleared alone,
        // which already has a supported "pick a valid date" warning state.
        $preview.on('click', '.rbfw-me-fp-start-clear', function () {
            $preview.find('.rbfw-me-fp-start, .rbfw-me-fp-end').val('');
            updateVisibility();
        });
        $preview.on('click', '.rbfw-me-fp-end-clear', function () {
            $preview.find('.rbfw-me-fp-end').val('');
            updateVisibility();
        });

        // Resort: Continue past the dates card into the Room Type section
        // (see resortRoomsRevealed, read/reset in updateVisibility() and
        // recalcResort()). Disabled (no-op) until the dates are valid.
        $preview.on('click', '.rbfw-me-fp-resort-continue-btn', function () {
            if ($(this).hasClass('is-disabled')) { return; }
            $preview.data('resortRoomsRevealed', true);
            updateVisibility();
        });

        // Some browsers (Safari) only open a native <input type="date">'s
        // picker when the small built-in calendar icon itself is clicked,
        // not the rest of the field. Opening it explicitly on every click
        // makes the whole box clickable everywhere, same as Chrome's default
        // behavior -- calling showPicker() again after a click that already
        // opened it natively is a harmless no-op.
        $preview.on('click', '.rbfw-me-fp-resort-dates-card input[type="date"]', function () {
            if (typeof this.showPicker === 'function') {
                try { this.showPicker(); } catch (err) { /* ignore -- e.g. not a user gesture */ }
            }
        });

        var $calField = $preview.find('.rbfw-me-fp-calendar-field');
        var $calMonth = $preview.find('.rbfw-me-fp-cal-month');
        var $calGrid = $preview.find('.rbfw-me-fp-cal-grid');

        // Off weekdays: Off Day Settings card (Off Days tab) stores a
        // comma-separated list of lowercase weekday names.
        function offWeekdays() {
            var val = $wrap.find('.rbfw-me-offday-hidden, input[name="rbfw_off_days"]').first().val() || '';
            var map = { sunday: 0, monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5, saturday: 6 };
            return val.split(',')
                .map(function (s) { return map[$.trim(s).toLowerCase()]; })
                .filter(function (n) { return n !== undefined; });
        }
        // Off date ranges: Off Date Settings card, repeatable start/end rows.
        function offDateRanges() {
            var ranges = [];
            $wrap.find('.rbfw-me-offdate-row').each(function () {
                var s = $(this).find('input[name="off_days_start[]"]').val();
                var e = $(this).find('input[name="off_days_end[]"]').val();
                if (s && e) { ranges.push({ start: s, end: e }); }
            });
            return ranges;
        }
        function isDateOff(d, offWeekdaySet, ranges) {
            if (offWeekdaySet.indexOf(d.getDay()) !== -1) { return true; }
            var iso = toIso(d);
            return ranges.some(function (r) { return iso >= r.start && iso <= r.end; });
        }

        function renderCalendar(onSelect) {
            var today = new Date();
            var year = today.getFullYear();
            var month = today.getMonth(); // current month only -- matches the approved design, no navigation
            $calMonth.text(MONTH_NAMES[month] + ' ' + year);

            // No default selection -- the customer must explicitly pick a
            // date, which is what reveals the next step (Time Slot/Rental
            // Option/...), mirroring the real frontend booking flow.
            var selectedIso = $calField.data('selected') || null;

            var firstDow = new Date(year, month, 1).getDay();
            var daysInMonth = new Date(year, month + 1, 0).getDate();
            var offWeekdaySet = offWeekdays();
            var ranges = offDateRanges();

            $calGrid.empty();
            for (var i = 0; i < firstDow; i++) {
                $calGrid.append('<span class="rbfw-me-fp-cal-day rbfw-me-fp-cal-day--blank"></span>');
            }
            for (var day = 1; day <= daysInMonth; day++) {
                var d = new Date(year, month, day);
                var iso = toIso(d);
                var isWeekend = (d.getDay() === 0 || d.getDay() === 6);
                if (isDateOff(d, offWeekdaySet, ranges)) {
                    $calGrid.append(
                        $('<span class="rbfw-me-fp-cal-day rbfw-me-fp-cal-day--off" title="Unavailable"></span>').text(day)
                    );
                    continue;
                }
                var $btn = $('<button type="button" class="rbfw-me-fp-cal-day"></button>').text(day);
                if (isWeekend) { $btn.addClass('weekend'); }
                if (iso === selectedIso) { $btn.addClass('sel'); }
                $btn.on('click', (function (dayIso) {
                    return function () {
                        $calField.data('selected', dayIso);
                        onSelect(dayIso);
                    };
                }(iso)));
                $calGrid.append($btn);
            }
            return selectedIso;
        }

        /* ───────────── Fee Configuration Settings ───────────── */
        // Mirrors the real calculation added to Frontend/RBFW_Woocommerse.php:
        // Fixed/Percentage of subtotal, Per booking/Per day (days = date-range
        // day count), All days/Weekends only. Every configured fee is shown as
        // applied -- the preview has no customer-facing "optional fee" opt-in
        // checkbox, so Required vs Optional isn't distinguished here.
        function feeRows() {
            var rows = [];
            $wrap.find('#wprently_fee_body tr').each(function () {
                var $row = $(this);
                var label = $.trim($row.find('input[name*="[label]"]').val());
                if (! label) { return; }
                var amount = parseFloat($row.find('input[name*="[amount]"]').val());
                rows.push({
                    label: label,
                    calcType: $row.find('select[name*="[calculation_type]"]').val() || 'fixed',
                    amount: isNaN(amount) ? 0 : amount,
                    frequency: $row.find('select[name*="[frequency]"]').val() || 'one-time',
                    applyOn: $row.find('select[name*="[apply_on]"]').val() || 'all-days'
                });
            });
            return rows;
        }
        function countWeekendDays(startDate, totalDays) {
            if (! startDate || totalDays <= 0) { return 0; }
            var count = 0;
            for (var i = 0; i < totalDays; i++) {
                var d = new Date(startDate.getTime());
                d.setDate(d.getDate() + i);
                if (d.getDay() === 0 || d.getDay() === 6) { count++; }
            }
            return count;
        }
        function computeFees($summaryRows, subtotal, totalDays, startDate) {
            if (! $wrap.find('input[name="rbfw_enable_fee_management"]').is(':checked')) {
                return 0;
            }
            var total = 0;
            feeRows().forEach(function (fee) {
                var amt;
                if (fee.calcType === 'percentage') {
                    amt = (fee.amount / 100) * subtotal;
                } else {
                    var days = 1;
                    if (fee.frequency === 'per-day' && totalDays > 0) {
                        days = (fee.applyOn === 'weekends') ? countWeekendDays(startDate, totalDays) : totalDays;
                    }
                    amt = fee.amount * days;
                }
                if (amt > 0) {
                    summaryLine($summaryRows, fee.label, money(amt));
                    total += amt;
                }
            });
            return total;
        }

        /* ───────────── Extra services ───────────── */

        // Basic table (Single Day / Appointment / Multiple Items): a flat
        // list, price * qty, never multiplied by day count.
        function basicExtraServiceRows() {
            var rows = [];
            $wrap.find('.rbfw_es_price_config_wrapper tbody.mp_event_type_sortable tr').not('.empty-row').each(function () {
                var $row = $(this);
                var name = $.trim($row.find('input[name="service_name[]"]').val());
                var price = parseFloat($row.find('input[name="service_price[]"]').val());
                if (! name || isNaN(price)) { return; }
                rows.push({ key: name, label: name, price: price, dayWise: false });
            });
            return rows;
        }

        // Category-wise table (Multiple Day family / Multiple Items):
        // each service can be marked One Time or Day Wise (price * qty * days).
        function categoryExtraServiceRows() {
            var rows = [];
            // Real checkout only reads this data when "Enable Additional
            // service" is on (multi-day-registration.php / multi-items-
            // registration.php both check $enable_service_price === 'on')
            // -- a real <input type="checkbox">, so .prop('checked') is
            // required (its .val() is a static "on"/"off" attribute that
            // doesn't reflect whether it's actually ticked).
            if (! $wrap.find('input[name="rbfw_enable_category_service_price"]').prop('checked')) {
                return rows;
            }
            $wrap.find('.rbfw_service_category_table tbody.sortable_tr tr[data-cat]').each(function () {
                var $cat = $(this);
                var catTitle = $.trim($cat.find('input[name*="[cat_title]"]').first().val());
                $cat.find('.service_category_inner_item_wrap .item').each(function () {
                    var $item = $(this);
                    var title = $.trim($item.find('input[name*="[title]"]').val());
                    var price = parseFloat($item.find('input[name*="[price]"]').val());
                    if (! title || isNaN(price)) { return; }
                    var dayWise = $item.find('input[type="radio"][name*="[service_price_type]"]:checked').val() === 'day_wise';
                    rows.push({ key: catTitle + '::' + title, label: (catTitle ? catTitle + ' — ' : '') + title, price: price, dayWise: dayWise });
                });
            });
            return rows;
        }

        // Extra Services render as checkboxes (included or not) -- the real
        // frontend form supports a quantity per service, but the approved
        // design shows a simple included/not-included checkbox, so qtyMap
        // here only ever holds 0 or 1 per key.
        function renderExtraChecks($container, items, qtyMap, onToggle, priceFormatter) {
            $container.empty();
            items.forEach(function (item) {
                var checked = (qtyMap[item.key] || 0) > 0;
                var id = 'rbfw-me-fp-extra-' + Math.random().toString(36).slice(2, 9);
                var $cb = $('<input type="checkbox">').attr('id', id).prop('checked', checked);
                $cb.on('change', function () { onToggle(item.key, this.checked ? 1 : 0); });
                var $label = $('<label></label>').attr('for', id).text(item.label);
                var $row = $('<div class="rbfw-me-fp-svc-check"></div>')
                    .append($cb)
                    .append($label)
                    .append($('<strong></strong>').text(priceFormatter(item)));
                $container.append($row);
            });
        }

        function extrasTotal(rows, qtyMap, totalDays) {
            var total = 0;
            rows.forEach(function (row) {
                var qty = qtyMap[row.key] || 0;
                if (qty <= 0) { return; }
                total += row.price * qty * ((row.dayWise && totalDays > 0) ? totalDays : 1);
            });
            return total;
        }

        /* ───────────── Variations ───────────── */

        function variationGroups() {
            var groups = [];
            // Respect the "Item variation" toggle -- its chip values stay in
            // the DOM (just visually hidden) when switched off, so without
            // this check the preview kept showing Options regardless of the
            // toggle's actual state.
            var enableVariations = $wrap.find('input[name="rbfw_enable_variations"]').val();
            if (enableVariations !== 'yes') { return groups; }
            // Item Variations no longer shows a "Field Label" box (just the
            // chip list under a fixed "Item Variations" title), so a blank
            // label here means "untitled group", not "skip it".
            $wrap.find('.rbfw_variations_table_body .rbfw_variations_table_row').each(function () {
                var $field = $(this);
                var fieldLabel = $.trim($field.find('input[name*="[field_label]"]').val()) || 'Options';
                var values = [];
                $field.find('.rbfw_variations_value_table_tbody .rbfw_variations_value_table_row').each(function () {
                    var $vrow = $(this);
                    var name = $.trim($vrow.find('input.rbfw_variation_value').val());
                    var price = parseFloat($vrow.find('input[name*="[price]"]').val());
                    if (! name) { return; }
                    values.push({ key: fieldLabel + '::' + name, label: name, price: isNaN(price) ? 0 : price });
                });
                if (values.length) { groups.push({ label: fieldLabel, values: values }); }
            });
            return groups;
        }

        function flattenVariationValues(groups) {
            var values = [];
            groups.forEach(function (g) { values = values.concat(g.values); });
            return values;
        }

        // Variations render as single-select pills per field (e.g. one Size,
        // one Color) -- tap again to deselect. The real data model allows a
        // quantity per selected value; this preview keeps it to one unit per
        // selected value per group, matching the approved design. There's only
        // ever one group now (no Field Label input left to name it), and
        // variationGroups() defaults its label to "Options" -- the same text
        // as the section title right above, so rendering it here just
        // duplicated that heading. Skipped entirely rather than picking a
        // different default that would just as easily collide.
        function renderVariationGroups($container, groups, selectedMap, onSelect) {
            $container.empty();
            groups.forEach(function (group) {
                var $row = $('<div class="rbfw-me-fp-chips"></div>');
                group.values.forEach(function (v) {
                    var isSel = (selectedMap[v.key] || 0) > 0;
                    var $pill = $('<button type="button" class="rbfw-me-fp-variation-pill"></button>');
                    if (isSel) { $pill.addClass('sel'); }
                    // Price is no longer an editable field on a variation chip (always
                    // 0 for any new value), so showing "+$0.00" next to every pill was
                    // just noise -- plain name only, matching the approved design.
                    $pill.append($('<span></span>').text(v.label));
                    $pill.on('click', function () { onSelect(group, v.key); });
                    $row.append($pill);
                });
                $container.append($row);
            });
        }

        function variationsTotal(groups, qtyMap) {
            return extrasTotal(flattenVariationValues(groups).map(function (v) {
                return { key: v.key, price: v.price, dayWise: false };
            }), qtyMap, 0);
        }

        function updateVisibility() {
            var type = currentRentType();
            var isMd = MD_TYPES.indexOf(type) !== -1;
            var isSd = SD_TYPES.indexOf(type) !== -1;
            var isResort = RESORT_TYPES.indexOf(type) !== -1;
            var isMi = MI_TYPES.indexOf(type) !== -1;
            var show = activePanelKey() === 'pricing' && (isMd || isSd || isResort || isMi);
            $preview.toggle(show);
            $preview.find('.rbfw-me-fp-pickup-row').toggle(isMd || isResort || isMi);
            $preview.find('.rbfw-me-fp-return-row').toggle(isMd || isResort);
            $sdControls.toggle(isSd);
            // Resort's Room Type section stays hidden behind the Continue
            // button (see resortRoomsRevealed / recalcResort()) until the
            // dates are confirmed; leaving Resort resets that step so coming
            // back to it later always starts at the dates card again.
            if (! isResort) { $preview.data('resortRoomsRevealed', false); }
            $resortControls.toggle(isResort && !! $preview.data('resortRoomsRevealed'));
            $preview.find('.rbfw-me-fp-mi-controls').toggle(isMi);
            if (! isMi) { $preview.find('.rbfw-me-fp-mi-items').hide(); }
            $calField.toggle(isSd);
            if (! isSd) { $sdTimeField.hide(); }
            // Resort's labels are short ("Pickup"/"Return") since they sit as
            // small uppercase captions in the compact two-column dates card.
            $preview.find('.rbfw-me-fp-start-label').text(isResort ? 'Pickup' : 'Pickup Date');
            $preview.find('.rbfw-me-fp-end-label').text(isResort ? 'Return' : 'Return Date');
            // Every type except Multiple Items: "Instant Booking Summary" card
            // head + trust badges, in place of the feature image/name lead-in.
            // Multiple Items and Multiple Day both get the same header too,
            // just reached via their own branch below since their "Starting
            // from" unit is dynamic (whichever duration type is actually
            // cheapest, set in recalcMi() / mdFromPrice()).
            var isSummaryHead = isMd || isSd || isResort || isMi;
            var isBoxedPrice = isMd || isResort || isMi;
            $preview.find('.rbfw-me-fp-feature').toggle(! isSummaryHead);
            $preview.find('.rbfw-me-fp-md-summary-head').toggle(isSummaryHead);
            $preview.find('.rbfw-me-fp-trustrow').toggle(isSummaryHead);
            $preview.find('.rbfw-me-fp-pricerow').toggleClass('rbfw-me-fp-pricerow--boxed', isBoxedPrice);
            if (! isMi && ! isMd) { $preview.find('.rbfw-me-fp-pricerow-unit').text(isResort ? '/ Night' : '/ Day'); }
            $preview.find('.rbfw-me-fp-summary-title').toggle(! isMd);
            $preview.find('.rbfw-me-fp-summary-total span:first-child').text(isMd ? 'Price' : 'Total');
            // Resort's "Check-In & Check-Out Date" card / Multiple Items' Rental
            // Duration + Pickup Date + Pickup Time card; Multiple Day keeps the
            // plain stacked rows the wrapper also holds.
            $preview.find('.rbfw-me-fp-resort-dates-card').toggle(isMd || isResort || isMi)
                .toggleClass('is-resort', isResort)
                .toggleClass('is-mi', isMi);
            $preview.find('.rbfw-me-fp-mi-duration-field').toggle(isMi);
            var resortRevealed = !! $preview.data('resortRoomsRevealed');
            // Only one CTA on screen at a time: Continue in step 1, Check
            // Availability once the Room Type section is revealed in step 2
            // (the book button was previously always rendered regardless of
            // this step, so both showed together -- that's the "duplicate
            // button" bug).
            $preview.find('.rbfw-me-fp-resort-continue-btn').toggle(isResort && ! resortRevealed);
            $preview.find('.rbfw-me-fp-book-btn').toggle(! (isResort && ! resortRevealed));
            $preview.find('.rbfw-me-fp-book-btn').toggleClass('is-resort-style', isResort);
            $preview.find('.rbfw-me-fp-book-btn').text(isResort ? 'Check Availability' : 'Book Now');
            $wrap.find('.rbfw-me-sidebar').toggleClass('is-previewing-pricing', show);
            if (! show) { return; }
            if (isSd) { recalcSd(); } else if (isResort) { recalcResort(); } else if (isMi) { recalcMi(); } else { recalc(); }
        }

        /* ───────────── Single Day / Appointment ───────────── */

        function sdRows() {
            var rows = [];
            $wrap.find('.rbfw_bike_car_sd_price_table_body .rbfw_bike_car_sd_price_table_row').each(function () {
                var $row = $(this);
                var label = $.trim($row.find('.rbfw_type_title').val());
                var price = parseFloat($row.find('input[name*="[price]"]').val());
                var duration = $.trim($row.find('input[name*="[duration]"]').val());
                var dType = $row.find('select[name*="[d_type]"]').val();
                if (! label || isNaN(price)) { return; }
                rows.push({ label: label, price: price, sub: duration ? (duration + ' ' + (dType || '')) : '' });
            });
            return rows;
        }

        function recalcSd() {
            var type = currentRentType();
            var rows = sdRows();
            var $totalAmt = $preview.find('.rbfw-me-fp-total-amt');
            var $from = $preview.find('.rbfw-me-fp-from-amt');
            $from.text(rows.length ? money(Math.min.apply(null, rows.map(function (r) { return r.price; }))) : '—');

            // Progressive reveal, mirroring the real frontend booking flow:
            // Calendar is shown first; picking a date reveals Time Slot (if
            // this item has one); picking a time (or having none to pick)
            // reveals Rental Option; picking an option reveals Quantity,
            // Options & Add-ons and the Booking Summary. Nothing is
            // pre-selected by default -- each step requires an explicit tap.
            var selectedDateIso = renderCalendar(function () { recalcSd(); });
            var dateSelected = !!selectedDateIso;

            // Time Slot: display-only for Single Day/Appointment -- the real
            // calculation (rbfw_bikecarsd_price_calculation) doesn't price by
            // time of day, it only affects which slot the booking occupies.
            var $sdWrap = $wrap.find('.rbfw_multi_day_price_conf.rbfw_bike_car_sd_wrapper');
            var sdTimePickerOn = $sdWrap.find('[name="rbfw_enable_time_picker"]').val() === 'yes';
            $sdTimeField.toggle(dateSelected && sdTimePickerOn);
            var selectedTime = null;
            if (dateSelected && sdTimePickerOn) {
                var slotValues = timeSlotValues('rdfw_available_time_sd');
                var prevTime = $sdTimeField.data('selected');
                selectedTime = renderTimeChips($preview.find('.rbfw-me-fp-sd-time-chips'), slotValues, prevTime, function (v) {
                    $sdTimeField.data('selected', v);
                    recalcSd();
                });
                $sdTimeField.data('selected', selectedTime);
            }
            var timeReady = ! sdTimePickerOn || !! selectedTime;

            // Info banner: shows whatever has been picked so far.
            if (dateSelected) {
                var selDObj = new Date(selectedDateIso + 'T00:00:00');
                var selDateLabel = MONTH_NAMES[selDObj.getMonth()].slice(0, 3) + ' ' + selDObj.getDate() + ', ' + selDObj.getFullYear();
                $durationBanner.show().find('.rbfw-me-fp-duration-text').text(
                    selDateLabel + (sdTimePickerOn && selectedTime ? ' at ' + selectedTime : '')
                );
            } else {
                $durationBanner.hide();
            }

            var readyForOptions = dateSelected && timeReady;
            $sdControls.toggle(readyForOptions);

            if (! readyForOptions) {
                clearWarning();
                showWarning(! dateSelected ? 'Select a date above to continue.' : 'Select a time slot above to continue.');
                $summary.hide();
                $extras.hide();
                $variations.hide();
                $totalAmt.text(money(0));
                return;
            }

            if (! rows.length) {
                clearWarning();
                showWarning('Add a rental option in the table below to preview its price.');
                renderOptrows($preview.find('.rbfw-me-fp-sd-optrows'), rows, -1, function () {});
                $preview.find('.rbfw-me-fp-sd-qty-field').hide();
                $summary.hide();
                $extras.hide();
                $variations.hide();
                $totalAmt.text(money(0));
                return;
            }

            var selLabel = $sdControls.data('selectedLabel');
            var idx = selLabel ? rows.findIndex(function (r) { return r.label === selLabel; }) : -1;
            renderOptrows($preview.find('.rbfw-me-fp-sd-optrows'), rows, idx, function (i) {
                $sdControls.data('selectedLabel', rows[i].label);
                recalcSd();
            });

            var optionSelected = idx !== -1;
            $preview.find('.rbfw-me-fp-sd-qty-field').toggle(optionSelected);
            $summary.toggle(optionSelected);

            if (! optionSelected) {
                clearWarning();
                showWarning('Select a rental option above to see pricing.');
                $extras.hide();
                $variations.hide();
                $totalAmt.text(money(0));
                return;
            }
            clearWarning();

            var qty = Math.max(1, parseInt($sdControls.data('qty'), 10) || 1);
            $preview.find('.rbfw-me-fp-sd-qty-val').text(qty);

            var $summaryRows = $preview.find('.rbfw-me-fp-summary-rows').empty();
            var row = rows[idx];
            summaryLine($summaryRows, row.label + ' × ' + qty, money(row.price * qty));
            if (selectedDateIso) {
                var dObj = new Date(selectedDateIso + 'T00:00:00');
                summaryLine($summaryRows, 'Date', MONTH_NAMES[dObj.getMonth()].slice(0, 3) + ' ' + dObj.getDate() + ', ' + dObj.getFullYear());
            }
            if (sdTimePickerOn && selectedTime) {
                summaryLine($summaryRows, 'Time', selectedTime);
            }
            summaryLine($summaryRows, 'Quantity', String(qty));
            var total = row.price * qty;

            var extraRows = basicExtraServiceRows();
            var groups = (type === 'bike_car_sd') ? variationGroups() : [];

            // Variations: only plain Single Day (not Appointment) exposes this card.
            $variations.toggle(groups.length > 0);
            if (groups.length) {
                var variationsQty = $preview.data('variationsQty');
                renderVariationGroups($variations.find('.rbfw-me-fp-variations-groups'), groups, variationsQty, function (group, key) {
                    var wasSelected = (variationsQty[key] || 0) > 0;
                    group.values.forEach(function (v) { variationsQty[v.key] = 0; });
                    variationsQty[key] = wasSelected ? 0 : 1;
                    recalcSd();
                });
                groups.forEach(function (group) {
                    var selectedVal = group.values.filter(function (v) { return (variationsQty[v.key] || 0) > 0; })[0];
                    if (selectedVal) { summaryLine($summaryRows, group.label, selectedVal.label); }
                });
                var variationsAmt = variationsTotal(groups, variationsQty);
                total += variationsAmt;
            }

            // Extra Services: basic table, shown for Single Day/Appointment.
            $extras.toggle(extraRows.length > 0);
            if (extraRows.length) {
                var extrasQty = $preview.data('extrasQty');
                renderExtraChecks($extras.find('.rbfw-me-fp-extras-rows'), extraRows, extrasQty, function (key, newQty) {
                    extrasQty[key] = newQty;
                    recalcSd();
                }, function (item) { return money(item.price); });
                extraRows.forEach(function (item) {
                    if ((extrasQty[item.key] || 0) > 0) { summaryLine($summaryRows, item.label, money(item.price)); }
                });
                total += extrasTotal(extraRows, extrasQty, 0);
            }

            var feesAmt = computeFees($summaryRows, total, 0, null);
            total += feesAmt;

            $totalAmt.text(money(total));
        }

        /* ───────────── Resort ───────────── */

        function resortRows() {
            var rows = [];
            $wrap.find('.rbfw_resort_price_table_body .rbfw_resort_price_table_row').each(function () {
                var $row = $(this);
                var label = $.trim($row.find('.rbfw_room_title').val());
                var daynight = parseFloat($row.find('input[name*="[rbfw_room_daynight_rate]"]').val());
                var daylong = parseFloat($row.find('input[name*="[rbfw_room_daylong_rate]"]').val());
                if (! label) { return; }
                rows.push({
                    label: label,
                    daynight: isNaN(daynight) ? 0 : daynight,
                    daylong: isNaN(daylong) ? 0 : daylong
                });
            });
            return rows;
        }

        function recalcResort() {
            ensureDefaultDates();
            // Resort has no Time Picker, extra-service or variation card in
            // the modern editor, so this preview mode never shows them.
            $sdTimeField.hide();
            $mdTimeFields.hide();
            $calField.hide();
            $extras.hide();
            $variations.hide();

            var $totalAmt = $preview.find('.rbfw-me-fp-total-amt');
            var $from = $preview.find('.rbfw-me-fp-from-amt');
            var $continueBtn = $preview.find('.rbfw-me-fp-resort-continue-btn');
            var $bookBtn = $preview.find('.rbfw-me-fp-book-btn');
            var revealed = !! $preview.data('resortRoomsRevealed');
            // Only one CTA at a time, regardless of entry point (this function
            // can run directly from the optrow-select / Continue click
            // handlers, not just through updateVisibility()).
            $continueBtn.toggle(! revealed);
            $bookBtn.toggle(revealed);

            // Dates are read and validated first -- the Continue step only
            // needs a valid Check-In/Check-Out pair, not a room selection yet.
            var startVal = $preview.find('.rbfw-me-fp-start').val();
            var endVal = $preview.find('.rbfw-me-fp-end').val();
            var start = startVal ? new Date(startVal + 'T00:00:00') : null;
            var end = endVal ? new Date(endVal + 'T00:00:00') : null;
            var datesValid = !! (start && end && end >= start);
            $continueBtn.toggleClass('is-disabled', ! datesValid);

            // Rooms + "Starting from" price are independent of the Continue
            // step -- the price box above the dates card stays live in step 1.
            var rows = resortRows();
            var daylongOn = $wrap.find('input[name="rbfw_enable_resort_daylong_price"]').is(':checked');
            $preview.find('.rbfw-me-fp-resort-package').toggle(daylongOn);
            var pkg = daylongOn ? ($preview.find('.rbfw-me-fp-resort-pkg').val() || 'daynight') : 'daynight';
            var rateOf = function (r) { return pkg === 'daylong' ? r.daylong : r.daynight; };
            $from.text(rows.length ? money(Math.min.apply(null, rows.map(rateOf))) : '—');

            if (! datesValid) {
                showWarning('Pick a valid check-in/check-out date.');
                $resortControls.hide();
                $summary.hide();
                $durationBanner.hide();
                $totalAmt.text(money(0));
                $preview.find('.rbfw-me-fp-book-btn').addClass('is-disabled');
                return;
            }

            if (! revealed) {
                // Step 1: dates only -- the Room Type section, summary and
                // Book button stay hidden until Continue is clicked.
                clearWarning();
                $resortControls.hide();
                $summary.hide();
                $durationBanner.hide();
                return;
            }

            var selLabel = $resortControls.data('selectedLabel');
            var idx = selLabel ? rows.findIndex(function (r) { return r.label === selLabel; }) : -1;
            if (idx === -1) { idx = rows.length ? 0 : -1; }

            renderOptrows($preview.find('.rbfw-me-fp-resort-optrows'), rows.map(function (r) {
                return { label: r.label, price: (pkg === 'daylong') ? r.daylong : r.daynight };
            }), idx, function (i) {
                $resortControls.data('selectedLabel', rows[i].label);
                recalcResort();
            });

            if (! rows.length) {
                showWarning('Add a room type in the table below to preview its price.');
                $summary.hide();
                $totalAmt.text(money(0));
                $preview.find('.rbfw-me-fp-book-btn').addClass('is-disabled');
                return;
            }
            clearWarning();
            // Resort has no separate price-calc box either -- its one rate
            // line goes straight into the Booking Summary rows.
            $summary.show();

            var totalDays = Math.round((end - start) / 86400000);
            if (countExtraDay) { totalDays += 1; }
            totalDays = Math.max(0, totalDays);

            var qty = Math.max(1, parseInt($resortControls.data('qty'), 10) || 1);
            $preview.find('.rbfw-me-fp-resort-qty-val').text(qty);

            var $summaryRows = $preview.find('.rbfw-me-fp-summary-rows').empty();
            var row = rows[idx];
            var rate = rateOf(row);
            var total = rate * qty * totalDays;

            $durationBanner.show().find('.rbfw-me-fp-duration-text').text(totalDays + ' night(s)');
            summaryLine($summaryRows, 'Room', row.label);
            summaryLine($summaryRows, 'Package', pkg === 'daylong' ? 'Day Long' : 'Day & Night');
            summaryLine($summaryRows, 'Duration', totalDays + ' night(s)');
            summaryLine($summaryRows, 'Quantity', String(qty));
            summaryLine($summaryRows, row.label + ' @ ' + money(rate) + '/night × ' + totalDays, money(rate * totalDays));

            var feesAmt = computeFees($summaryRows, total, totalDays, start);
            total += feesAmt;
            $totalAmt.text(money(total));
            $preview.find('.rbfw-me-fp-book-btn').toggleClass('is-disabled', total <= 0);
        }

        /* ───────────── Multiple Day / Equipment / Dress / Others ───────────── */

        // addLine(label, amount) -- lets the caller decide where each tier
        // line is rendered (Booking Summary rows, not a separate price-calc
        // box).
        var DAY_SLUGS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        // Per-weekday "Day-wise Pricing" override for a given date, falling
        // back to the flat daily rate when that weekday has no override --
        // mirrors rbfw_get_day_rate()'s PHP logic (checks only that one
        // weekday's own enable flag + rate field, both saved per weekday
        // regardless of the day-wise section's own show/hide toggle, since
        // that's exactly what the real checkout price reads).
        function dayRateFor(date, fallbackRate) {
            var slug = DAY_SLUGS[date.getDay()];
            // rbfw_enable_{day}_day is a real <input type="checkbox">, not
            // the hidden-input-plus-styled-toggle pattern isYes() is built
            // for -- .val() on a checkbox always returns its static "yes"
            // value attribute regardless of checked state, so isYes() here
            // would treat every day as enabled even when unticked. Checking
            // .prop('checked') is required, matching PHP's own read of the
            // saved checkbox state (unticked = field omitted/empty = 'no').
            if (! $pricingPanel.find('[name="rbfw_enable_' + slug + '_day"]').prop('checked')) { return fallbackRate; }
            var raw = $pricingPanel.find('[name="rbfw_' + slug + '_daily_rate"]').val();
            if (raw === '' || raw === undefined || raw === null) { return fallbackRate; }
            var custom = parseFloat(raw);
            return isNaN(custom) ? fallbackRate : custom;
        }

        function computeWholeDayBreakdown(addLine, wholeDays, fromDate) {
            var remaining = wholeDays;
            var offset = 0;
            var total = 0;

            var monthlyOn = isYes('#rbfw_enable_monthly_rate');
            var monthThreshold = isYes('#rbfw_enable_day_threshold_for_monthly') ? num('#day-threshold-input-for-monthly') : 30;
            var monthlyRate = num('#monthly-price-input');

            var weeklyOn = isYes('#rbfw_enable_weekly_rate');
            var weekThreshold = isYes('#rbfw_enable_day_threshold_for_weekly') ? num('#day-threshold-input-for-weekly') : 7;
            var weeklyRate = num('#weekly-price-input');

            var dailyOn = isYes('#rbfw_enable_daily_rate');
            var dailyRate = num('#daily-price-input');
            var missingDaily = false;

            if (monthlyOn && monthlyRate > 0 && monthThreshold > 0 && remaining >= monthThreshold) {
                var months = Math.floor(remaining / monthThreshold);
                addLine(months + ' month(s) × ' + money(monthlyRate), months * monthlyRate);
                total += months * monthlyRate;
                remaining -= months * monthThreshold;
                offset += months * monthThreshold;
            }
            if (weeklyOn && weeklyRate > 0 && weekThreshold > 0 && remaining >= weekThreshold) {
                var weeks = Math.floor(remaining / weekThreshold);
                addLine(weeks + ' week(s) × ' + money(weeklyRate), weeks * weeklyRate);
                total += weeks * weeklyRate;
                remaining -= weeks * weekThreshold;
                offset += weeks * weekThreshold;
            }
            if (remaining > 0) {
                if (dailyOn) {
                    // Sum the remaining days one at a time so a Day-wise
                    // Pricing override on any specific weekday is actually
                    // reflected here, instead of always multiplying by the
                    // flat daily rate -- mirrors rbfw_daywise_days_sum().
                    if (fromDate) {
                        var dayAmt = 0;
                        var hasOverride = false;
                        for (var j = 0; j < remaining; j++) {
                            var d = new Date(fromDate.getTime() + (offset + j) * 86400000);
                            var rate = dayRateFor(d, dailyRate);
                            if (rate !== dailyRate) { hasOverride = true; }
                            dayAmt += rate;
                        }
                        addLine(
                            hasOverride ? (remaining + ' day(s) (day-wise pricing applied)') : (remaining + ' day(s) × ' + money(dailyRate)),
                            dayAmt
                        );
                        total += dayAmt;
                    } else {
                        addLine(remaining + ' day(s) × ' + money(dailyRate), remaining * dailyRate);
                        total += remaining * dailyRate;
                    }
                } else {
                    missingDaily = true;
                }
            }
            return { total: total, missingDaily: missingDaily, remaining: remaining };
        }

        function computeLeftoverHours(addLine, leftoverHours, wholeDaysBilled) {
            leftoverHours = Math.max(0, Math.round(leftoverHours * 100) / 100);
            if (leftoverHours <= 0) {
                return 0;
            }

            var dailyOn = isYes('#rbfw_enable_daily_rate');
            var dailyRate = num('#daily-price-input');
            var hourThresholdOn = isYes('#rbfw_enable_hourly_threshold');
            var hourThreshold = num('#hour-threshold-input');

            if (hourThresholdOn && hourThreshold > 0 && leftoverHours >= hourThreshold && dailyOn) {
                addLine('Final ' + leftoverHours + 'h rounds up to a full day', dailyRate);
                return dailyRate;
            }

            var halfDayOn = isYes('#rbfw_enable_half_day_rate') && num('#half-day-price-input') > 0;
            var halfStart = parseFloat($pricingPanel.find('[name="half_day_hour_threshold_start"]').val());
            var halfEnd = parseFloat($pricingPanel.find('[name="half_day_hour_threshold_end"]').val());
            if (halfDayOn && halfStart > 0 && halfEnd >= halfStart && leftoverHours >= halfStart && leftoverHours <= halfEnd) {
                var halfRate = num('#half-day-price-input');
                addLine('Half-day (' + leftoverHours + 'h)', halfRate);
                return halfRate;
            }

            var hourlyOn = isYes('#rbfw_enable_hourly_rate') && num('#hourly-price-input') > 0;
            if (hourlyOn) {
                var hourlyRate = num('#hourly-price-input');
                var hAmt = leftoverHours * hourlyRate;
                addLine(leftoverHours + 'h × ' + money(hourlyRate) + '/hr', hAmt);
                return hAmt;
            }

            if (wholeDaysBilled > 0 || dailyOn) {
                showWarning('No rate covers the final ' + leftoverHours + 'h of this booking.');
            }
            return 0;
        }

        // Returns {price, unit} for whichever enabled rate (Hour/Day/Week/
        // Month/Half Day) is cheapest, so "Starting from" can show its real
        // unit instead of always assuming Day.
        function mdFromPrice() {
            var candidates = [];
            if (isYes('#rbfw_enable_daily_rate') && num('#daily-price-input') > 0) { candidates.push({ price: num('#daily-price-input'), unit: 'Day' }); }
            if (isYes('#rbfw_enable_weekly_rate') && num('#weekly-price-input') > 0) { candidates.push({ price: num('#weekly-price-input'), unit: 'Week' }); }
            if (isYes('#rbfw_enable_monthly_rate') && num('#monthly-price-input') > 0) { candidates.push({ price: num('#monthly-price-input'), unit: 'Month' }); }
            if (isYes('#rbfw_enable_hourly_rate') && num('#hourly-price-input') > 0) { candidates.push({ price: num('#hourly-price-input'), unit: 'Hour' }); }
            if (isYes('#rbfw_enable_half_day_rate') && num('#half-day-price-input') > 0) { candidates.push({ price: num('#half-day-price-input'), unit: 'Half Day' }); }
            if (! candidates.length) { return null; }
            return candidates.reduce(function (min, c) { return c.price < min.price ? c : min; });
        }

        // "3 days 14h 30m" / "3 days" / "14h 30m" -- shows the real day and
        // hour breakdown instead of a vague "(incl. partial day)" note, and
        // omits whichever part is zero (e.g. a same-day booking has no
        // "0 days" prefix).
        function formatDaysHours(days, hours) {
            var parts = [];
            if (days > 0) { parts.push(days + ' day' + (days === 1 ? '' : 's')); }
            var h = Math.floor(hours + 1e-9);
            var m = Math.round((hours - h) * 60);
            if (m === 60) { h += 1; m = 0; }
            if (h > 0 && m > 0) { parts.push(h + 'h ' + m + 'm'); }
            else if (h > 0) { parts.push(h + 'h'); }
            else if (m > 0) { parts.push(m + 'm'); }
            return parts.length ? parts.join(' ') : '0 days';
        }

        function recalc() {
            ensureDefaultDates();
            clearWarning();
            // Multiple Day has no separate price-calc box -- its tier
            // breakdown (e.g. "3 day(s) × $100.00") is written straight into
            // the Booking Summary rows below instead.
            $summary.show();
            $preview.find('.rbfw-me-fp-sd-optrows, .rbfw-me-fp-resort-optrows').empty();

            var $totalAmt = $preview.find('.rbfw-me-fp-total-amt');
            var $from = $preview.find('.rbfw-me-fp-from-amt');
            var fromInfo = mdFromPrice();
            $from.text(fromInfo === null ? '—' : money(fromInfo.price));
            $preview.find('.rbfw-me-fp-pricerow-unit').text(fromInfo ? '/ ' + fromInfo.unit : '');

            var startVal = $preview.find('.rbfw-me-fp-start').val();
            var endVal = $preview.find('.rbfw-me-fp-end').val();
            var start = startVal ? new Date(startVal + 'T00:00:00') : null;
            var end = endVal ? new Date(endVal + 'T00:00:00') : null;

            // Single Day-only elements: must be explicitly hidden here too,
            // not just left over from whatever recalcSd() last set, since
            // switching rent types doesn't otherwise touch them.
            $calField.hide();
            $sdTimeField.hide();

            if (! start || ! end || end < start) {
                showWarning('Pick a valid pickup/return date.');
                $durationBanner.hide();
                $mdTimeFields.hide();
                $extras.hide();
                $variations.hide();
                $preview.find('.rbfw-me-fp-summary-rows').empty();
                $totalAmt.text(money(0));
                $preview.find('.rbfw-me-fp-book-btn').addClass('is-disabled');
                return;
            }

            var totalDays = Math.round((end - start) / 86400000) + 1;
            var timePickerOn = isYes('#rbfw_enable_time_picker');

            $mdTimeFields.toggle(timePickerOn);
            var pickupTime = null, returnTime = null, stMin = null, etMin = null;
            if (timePickerOn) {
                var slotValues = timeSlotValues('rdfw_available_time');
                pickupTime = populateTimeSelect($preview.find('.rbfw-me-fp-md-start-time'), slotValues);
                returnTime = populateTimeSelect($preview.find('.rbfw-me-fp-md-end-time'), slotValues);
                stMin = parseTimeToMinutes(pickupTime);
                etMin = parseTimeToMinutes(returnTime);
            }

            // Whole days + a leftover-hours remainder for the last day --
            // mirrors rbfw_md_duration_price_calculation()'s PHP
            // date_diff(pickup_datetime, dropoff_datetime): the real elapsed
            // time between the two full datetimes (date AND time), not just
            // the calendar day count. Using only the raw return time here
            // (ignoring how late pickup was) overstated the leftover on any
            // booking that didn't start at midnight -- e.g. pickup 08:00 /
            // return 18:00 three days later is "3 days 10h" elapsed, not
            // "3 days 18h".
            var wholeDays = totalDays;
            var leftoverHours = 0;
            if (timePickerOn) {
                if (totalDays === 1) {
                    wholeDays = 0;
                    leftoverHours = (stMin !== null && etMin !== null) ? Math.max(0, (etMin - stMin) / 60) : 24;
                } else {
                    var pickupMin = (stMin !== null) ? stMin : 0;
                    var dropoffMin = (etMin !== null) ? etMin : 0;
                    var totalMinutes = Math.round((end - start) / 60000) + (dropoffMin - pickupMin);
                    wholeDays = Math.max(0, Math.floor(totalMinutes / 1440));
                    leftoverHours = Math.max(0, (totalMinutes - wholeDays * 1440) / 60);
                }
            }

            var durationLabel = timePickerOn ? formatDaysHours(wholeDays, leftoverHours) : (totalDays + ' day' + (totalDays === 1 ? '' : 's'));
            $durationBanner.show().find('.rbfw-me-fp-duration-text').text(durationLabel);

            var $summaryRows = $preview.find('.rbfw-me-fp-summary-rows').empty();
            summaryLine($summaryRows, 'Duration', durationLabel);

            var tierAddLine = function (label, amount) { summaryLine($summaryRows, label, money(amount)); };
            var breakdown = computeWholeDayBreakdown(tierAddLine, wholeDays, start);
            var total = breakdown.total;

            if (timePickerOn) {
                total += computeLeftoverHours(tierAddLine, leftoverHours, wholeDays);
            } else if (breakdown.missingDaily) {
                showWarning('No Daily Price enabled for the remaining ' + breakdown.remaining + ' day(s) of this booking.');
            }

            if (timePickerOn && pickupTime) { summaryLine($summaryRows, 'Pickup Time', pickupTime); }
            if (timePickerOn && returnTime) { summaryLine($summaryRows, 'Return Time', returnTime); }

            var extraRows = categoryExtraServiceRows();
            var groups = variationGroups();

            // Variations (same card as Single Day, also shown for the MD family).
            $variations.toggle(groups.length > 0);
            if (groups.length) {
                var variationsQty = $preview.data('variationsQty');
                renderVariationGroups($variations.find('.rbfw-me-fp-variations-groups'), groups, variationsQty, function (group, key) {
                    var wasSelected = (variationsQty[key] || 0) > 0;
                    group.values.forEach(function (v) { variationsQty[v.key] = 0; });
                    variationsQty[key] = wasSelected ? 0 : 1;
                    recalc();
                });
                groups.forEach(function (group) {
                    var selectedVal = group.values.filter(function (v) { return (variationsQty[v.key] || 0) > 0; })[0];
                    if (selectedVal) { summaryLine($summaryRows, group.label, selectedVal.label); }
                });
                total += variationsTotal(groups, variationsQty);
            }

            // Extra Services: category-wise table (Multiple Day/Equipment/Dress/Others).
            $extras.toggle(extraRows.length > 0);
            if (extraRows.length) {
                var extrasQty = $preview.data('extrasQty');
                renderExtraChecks($extras.find('.rbfw-me-fp-extras-rows'), extraRows, extrasQty, function (key, newQty) {
                    extrasQty[key] = newQty;
                    recalc();
                }, function (item) { return item.dayWise ? money(item.price) + '/day' : money(item.price); });
                extraRows.forEach(function (item) {
                    if ((extrasQty[item.key] || 0) <= 0) { return; }
                    // Day-wise services charge per day, not once -- showing just
                    // the flat per-day rate here (with the multiplied total
                    // happening silently elsewhere) read like a flat fee and
                    // confused admins. Spell out the multiplication, same as
                    // Resort's "$rate/night × N" room line.
                    if (item.dayWise && totalDays > 0) {
                        summaryLine($summaryRows, item.label + ' (' + money(item.price) + '/day × ' + totalDays + ')', money(item.price * totalDays));
                    } else {
                        summaryLine($summaryRows, item.label, money(item.price));
                    }
                });
                total += extrasTotal(extraRows, extrasQty, totalDays);
            }

            // "Subtotal" line = everything above, before fees -- so the Price
            // box below always reads as Subtotal + fees = Price, even when
            // there are no fees configured.
            summaryLine($summaryRows, 'Subtotal', money(total));

            var feesAmt = computeFees($summaryRows, total, totalDays, start);
            total += feesAmt;

            $totalAmt.text(money(total));
            $preview.find('.rbfw-me-fp-book-btn').toggleClass('is-disabled', total <= 0);
        }

        /* ───────────── Multiple Items ─────────────
           A package of linked item rows, each with its own Hourly/Daily/
           Weekly/Monthly price; the customer picks ONE duration type + qty
           for the whole booking (not per item), then how many of each item. */

        var MI_DURATION_LABELS = { hourly: 'Hourly', daily: 'Daily', weekly: 'Weekly', monthly: 'Monthly' };
        var MI_DURATION_UNIT = { hourly: ['Hour', 'Hours'], daily: ['Day', 'Days'], weekly: ['Week', 'Weeks'], monthly: ['Month', 'Months'] };
        var MI_DURATION_MAX = { hourly: 23, daily: 30, weekly: 4, monthly: 30 };

        var $miControls = $preview.find('.rbfw-me-fp-mi-controls');
        var $miItems = $preview.find('.rbfw-me-fp-mi-items');
        $miControls.data('qty', 1);
        $preview.data('miItemQty', {});

        function miEnabledDurationTypes() {
            var types = [];
            if ($wrap.find('#enableHourly').is(':checked')) { types.push('hourly'); }
            if ($wrap.find('#enableDaily').is(':checked')) { types.push('daily'); }
            if ($wrap.find('#enableWeekly').is(':checked')) { types.push('weekly'); }
            if ($wrap.find('#enableMonthly').is(':checked')) { types.push('monthly'); }
            return types;
        }

        function miRows() {
            var rows = [];
            $wrap.find('#itemRows .item-row').each(function () {
                var $row = $(this);
                var name = $.trim($row.find('.item-name-input').val());
                if (! name) { return; }
                rows.push({
                    name: name,
                    hourly: parseFloat($row.find('.hourly-price-input').val()) || 0,
                    daily: parseFloat($row.find('.daily-price-input').val()) || 0,
                    weekly: parseFloat($row.find('.weekly-price-input').val()) || 0,
                    monthly: parseFloat($row.find('.monthly-price-input').val()) || 0
                });
            });
            return rows;
        }

        // Returns {price, type} for the cheapest entry-point price across
        // every item/enabled duration type -- type is used to label the
        // "Starting from" price box's unit (e.g. "/ Hour"), independent of
        // whatever duration type the admin currently has selected below.
        function miFromPrice(rows, types) {
            var min = null, minType = null;
            rows.forEach(function (row) {
                types.forEach(function (t) {
                    var p = row[t];
                    if (p > 0 && (min === null || p < min)) { min = p; minType = t; }
                });
            });
            return { price: min, type: minType };
        }

        // Mirrors rbfwGetMultipleItemsPivotBilling() in md_script.js: once a
        // duration crosses the configured pivot threshold, billing auto-
        // upgrades to the next tier up (e.g. 30 hours -> billed as whole
        // days), falling back to the original tier when an item has no
        // price set at the upgraded one.
        function miPivotBilling(durationType, durationQty) {
            var hourlyToDay = parseFloat($wrap.find('#rbfw_mi_hourly_to_half_day_pivot').val()) || 0;
            var dailyToWeekly = parseFloat($wrap.find('#rbfw_mi_daily_to_weekly_pivot').val()) || 0;
            var weeklyToMonthly = parseFloat($wrap.find('#rbfw_mi_weekly_to_monthly_pivot').val()) || 0;

            var billing = { priceType: durationType, units: durationQty, multiplier: durationQty };

            if (durationType === 'weekly' && weeklyToMonthly > 0 && durationQty >= weeklyToMonthly) {
                billing.priceType = 'monthly';
                billing.units = Math.max(1, Math.ceil(durationQty / 4));
                billing.multiplier = billing.units;
            } else if (durationType === 'daily' && dailyToWeekly > 0 && durationQty >= dailyToWeekly) {
                var weeklyUnits = Math.max(1, Math.ceil(durationQty / 7));
                if (weeklyToMonthly > 0 && weeklyUnits >= weeklyToMonthly) {
                    billing.priceType = 'monthly';
                    billing.units = Math.max(1, Math.ceil(weeklyUnits / 4));
                } else {
                    billing.priceType = 'weekly';
                    billing.units = weeklyUnits;
                }
                billing.multiplier = billing.units;
            } else if (durationType === 'hourly') {
                if (hourlyToDay > 0 && durationQty >= hourlyToDay) {
                    var dailyUnits = Math.max(1, Math.ceil(durationQty / 24));
                    if (dailyToWeekly > 0 && dailyUnits >= dailyToWeekly) {
                        var hourlyWeeklyUnits = Math.max(1, Math.ceil(dailyUnits / 7));
                        if (weeklyToMonthly > 0 && hourlyWeeklyUnits >= weeklyToMonthly) {
                            billing.priceType = 'monthly';
                            billing.units = Math.max(1, Math.ceil(hourlyWeeklyUnits / 4));
                        } else {
                            billing.priceType = 'weekly';
                            billing.units = hourlyWeeklyUnits;
                        }
                    } else {
                        billing.priceType = 'daily';
                        billing.units = dailyUnits;
                    }
                    billing.multiplier = billing.units;
                }
            }
            return billing;
        }

        function recalcMi() {
            clearWarning();
            $calField.hide();
            $sdTimeField.hide();

            var rows = miRows();
            var types = miEnabledDurationTypes();
            var $totalAmt = $preview.find('.rbfw-me-fp-total-amt');
            var $from = $preview.find('.rbfw-me-fp-from-amt');
            var fromInfo = miFromPrice(rows, types);
            $from.text(fromInfo.price === null ? '—' : money(fromInfo.price));
            $preview.find('.rbfw-me-fp-pricerow-unit').text(fromInfo.type ? '/ ' + MI_DURATION_UNIT[fromInfo.type][0] : '');

            // Duration Type: no default -- with every tier optional and none
            // inherently "first", the admin taps one just like the real
            // booking form's tabs.
            var selectedType = $miControls.data('durationType');
            if (selectedType && types.indexOf(selectedType) === -1) { selectedType = null; }
            var $durationChips = $preview.find('.rbfw-me-fp-mi-duration-chips');
            if (types.length) {
                var typeChips = types.map(function (t) { return MI_DURATION_LABELS[t]; });
                var activeLabel = renderTimeChips(
                    $durationChips,
                    typeChips,
                    selectedType ? MI_DURATION_LABELS[selectedType] : null,
                    function (label) {
                        $miControls.data('durationType', types.filter(function (t) { return MI_DURATION_LABELS[t] === label; })[0]);
                        recalcMi();
                    }
                );
                if (activeLabel) {
                    selectedType = types.filter(function (t) { return MI_DURATION_LABELS[t] === activeLabel; })[0];
                }
            } else {
                $durationChips.empty();
                selectedType = null;
            }

            $preview.find('.rbfw-me-fp-mi-duration-field').toggle(!!selectedType);
            $miItems.hide();
            $durationBanner.hide();
            $preview.find('.rbfw-me-fp-md-start-time-field').hide();
            $preview.find('.rbfw-me-fp-md-end-time-field').hide();

            if (! rows.length) {
                showWarning('Add at least one item in the table below to preview its price.');
                $summary.hide();
                $extras.hide();
                $totalAmt.text(money(0));
                return;
            }
            if (! types.length) {
                showWarning('Enable at least one price type above (Hourly/Daily/Weekly/Monthly) to preview pricing.');
                $summary.hide();
                $extras.hide();
                $totalAmt.text(money(0));
                return;
            }
            if (! selectedType) {
                showWarning('Select a duration type above to continue.');
                $summary.hide();
                $extras.hide();
                $totalAmt.text(money(0));
                return;
            }

            var maxQty = MI_DURATION_MAX[selectedType] || 30;
            var qty = Math.max(1, Math.min(maxQty, parseInt($miControls.data('qty'), 10) || 1));
            $miControls.data('qty', qty);
            // "Rental Duration" dropdown -- options formatted "1 Day", "2 Days",
            // etc. per the approved design, in place of the old +/- stepper.
            var unit = MI_DURATION_UNIT[selectedType];
            var $qtySelect = $preview.find('.rbfw-me-fp-mi-qty-select');
            $qtySelect.empty();
            for (var n = 1; n <= maxQty; n++) {
                $qtySelect.append($('<option></option>').val(n).text(n + ' ' + (n === 1 ? unit[0] : unit[1])));
            }
            $qtySelect.val(qty);

            ensureDefaultDates();
            var startVal = $preview.find('.rbfw-me-fp-start').val();
            var start = startVal ? new Date(startVal + 'T00:00:00') : null;

            var miTimePickerOn = $wrap.find('.rbfw-mi-time-settings-wrap .rbfw_enable_time_picker').val() === 'yes';
            var pickupTime = null;
            if (miTimePickerOn) {
                var slotValues = timeSlotValues('rdfw_available_time_mi');
                pickupTime = populateTimeSelect($preview.find('.rbfw-me-fp-md-start-time'), slotValues);
                $preview.find('.rbfw-me-fp-md-start-time-field').show();
            }

            if (! start) {
                showWarning('Pick a pickup date above to continue.');
                $summary.hide();
                $extras.hide();
                $totalAmt.text(money(0));
                return;
            }

            // End date, mirroring rbfwGetMultipleItemsSummaryEndDate().
            var end = new Date(start);
            if (selectedType === 'weekly') { end.setDate(end.getDate() + (qty * 7)); }
            else if (selectedType === 'monthly') { end.setDate(end.getDate() + (qty * 30)); }
            else if (selectedType === 'daily') { end.setDate(end.getDate() + qty); }
            // Hourly bookings stay within the same day for this date-only preview.

            var durationLabel = qty + ' ' + (qty === 1 ? unit[0] : unit[1]);
            var startLabel = MONTH_NAMES[start.getMonth()].slice(0, 3) + ' ' + start.getDate() + ', ' + start.getFullYear();
            var endLabel = MONTH_NAMES[end.getMonth()].slice(0, 3) + ' ' + end.getDate() + ', ' + end.getFullYear();
            $durationBanner.show().find('.rbfw-me-fp-duration-text').text(
                durationLabel + (selectedType === 'hourly' ? ' · ' + startLabel : ' · ' + startLabel + ' – ' + endLabel)
            );

            $miItems.show();
            var $itemRows = $miItems.find('.rbfw-me-fp-mi-item-rows').empty();
            var miQtyMap = $preview.data('miItemQty');
            var pivotBilling = miPivotBilling(selectedType, qty);
            var $summaryRows = $preview.find('.rbfw-me-fp-summary-rows').empty();
            summaryLine($summaryRows, 'Duration', durationLabel);
            summaryLine($summaryRows, 'Start Date', startLabel);
            if (selectedType !== 'hourly') { summaryLine($summaryRows, 'End Date', endLabel); }
            if (pickupTime) { summaryLine($summaryRows, 'Pickup Time', pickupTime); }

            var itemTotal = 0;
            var anySelected = false;
            rows.forEach(function (row, idx) {
                var key = row.name + '::' + idx;
                var perUnit = row[pivotBilling.priceType] || 0;
                var multiplier = pivotBilling.multiplier;
                // The upgraded tier may not have a price configured for this
                // item -- fall back to its original tier/qty rather than
                // silently pricing it at 0 (mirrors calculateTotalMultipleItems()).
                if (! perUnit && pivotBilling.priceType !== selectedType) {
                    perUnit = row[selectedType] || 0;
                    multiplier = qty;
                }
                var itemQty = miQtyMap[key] || 0;
                if (itemQty > 0) { anySelected = true; }

                var $row = $('<div class="rbfw-me-fp-svcrow"></div>');
                var $nameCol = $('<div class="rbfw-me-fp-svcrow-name"></div>').text(row.name);
                $nameCol.append($('<span class="rbfw-me-fp-svcrow-price"></span>').text(money(perUnit) + ' / ' + MI_DURATION_LABELS[pivotBilling.priceType]));
                $row.append($nameCol);
                var $stepperPill = $('<div class="rbfw-me-fp-stepper-pill rbfw-me-fp-stepper-pill--sm"></div>');
                var $minus = $('<button type="button" class="rbfw-me-fp-step-minus">&minus;</button>');
                var $val = $('<span></span>').text(itemQty);
                var $plus = $('<button type="button" class="rbfw-me-fp-step-plus">+</button>');
                $minus.on('click', function () {
                    miQtyMap[key] = Math.max(0, (miQtyMap[key] || 0) - 1);
                    recalcMi();
                });
                $plus.on('click', function () {
                    miQtyMap[key] = (miQtyMap[key] || 0) + 1;
                    recalcMi();
                });
                $stepperPill.append($minus).append($val).append($plus);
                $row.append($stepperPill);
                $itemRows.append($row);

                if (itemQty > 0) {
                    var lineTotal = perUnit * itemQty * multiplier;
                    summaryLine($summaryRows, row.name + ' × ' + itemQty, money(lineTotal));
                    itemTotal += lineTotal;
                }
            });

            if (! anySelected) {
                showWarning('Select at least one item\'s quantity above to continue.');
                $summary.hide();
                $extras.hide();
                $totalAmt.text(money(0));
                return;
            }

            var total = itemTotal;

            // Extra Services: Multiple Items' real frontend form reads the
            // category-wise rbfw_service_category_price data (same as
            // Multiple Day), not the simple table -- templates/forms/
            // multi-items-registration.php:561-582 confirms this. The basic
            // table's rbfw_extra_service_data is saved here but never read
            // by the actual booking form, so using it here showed nothing.
            var extraRows = categoryExtraServiceRows();
            $extras.toggle(extraRows.length > 0);
            if (extraRows.length) {
                var extrasQty = $preview.data('extrasQty');
                renderExtraChecks($extras.find('.rbfw-me-fp-extras-rows'), extraRows, extrasQty, function (k, newQty) {
                    extrasQty[k] = newQty;
                    recalcMi();
                }, function (item) { return money(item.price); });
                extraRows.forEach(function (item) {
                    if ((extrasQty[item.key] || 0) > 0) { summaryLine($summaryRows, item.label, money(item.price)); }
                });
                total += extrasTotal(extraRows, extrasQty, 0);
            }

            var feesAmt = computeFees($summaryRows, total, 0, null);
            total += feesAmt;

            $summary.show();
            $totalAmt.text(money(total));
        }

        /* ───────────── Visibility + event wiring ───────────── */

        // Day-wise Pricing's 7 per-weekday enable checkboxes + rate fields --
        // without these the preview never re-renders when one is ticked,
        // unticked, or edited, so dayRateFor()'s result would only show up
        // after some unrelated field happened to trigger a recalc.
        var DAY_WISE_SELECTORS = DAY_SLUGS.map(function (d) {
            return '[name="rbfw_enable_' + d + '_day"], [name="rbfw_' + d + '_daily_rate"]';
        }).join(', ');

        var WATCHED_SELECTORS = [
            '#monthly-price-input', '#rbfw_enable_monthly_rate',
            '#day-threshold-input-for-monthly', '#rbfw_enable_day_threshold_for_monthly',
            '#weekly-price-input', '#rbfw_enable_weekly_rate',
            '#day-threshold-input-for-weekly', '#rbfw_enable_day_threshold_for_weekly',
            '#daily-price-input', '#rbfw_enable_daily_rate',
            '#rbfw_enable_time_picker',
            '#half-day-price-input', '#rbfw_enable_half_day_rate',
            '[name="half_day_hour_threshold_start"]', '[name="half_day_hour_threshold_end"]',
            '#hourly-price-input', '#rbfw_enable_hourly_rate',
            '#hour-threshold-input', '#rbfw_enable_hourly_threshold',
            DAY_WISE_SELECTORS
        ].join(', ');

        function isPreviewingMd() {
            return $preview.is(':visible') && MD_TYPES.indexOf(currentRentType()) !== -1;
        }
        function isPreviewingSd() {
            return $preview.is(':visible') && SD_TYPES.indexOf(currentRentType()) !== -1;
        }
        function isPreviewingResort() {
            return $preview.is(':visible') && RESORT_TYPES.indexOf(currentRentType()) !== -1;
        }
        function isPreviewingMi() {
            return $preview.is(':visible') && MI_TYPES.indexOf(currentRentType()) !== -1;
        }

        $wrap.on('change input', WATCHED_SELECTORS, function () {
            if (isPreviewingMd()) { recalc(); }
        });
        $wrap.on('change', '.rbfw-me-fp-start, .rbfw-me-fp-end', function () {
            if (isPreviewingMd()) { recalc(); }
            if (isPreviewingResort()) { recalcResort(); }
            if (isPreviewingMi()) { recalcMi(); }
        });
        $wrap.on('change', '.rbfw-me-fp-md-start-time, .rbfw-me-fp-md-end-time', function () {
            if (isPreviewingMd()) { recalc(); }
            if (isPreviewingMi()) { recalcMi(); }
        });
        $wrap.on('change', '#rbfw_item_type', function () {
            setTimeout(updateVisibility, 0);
        });
        // The Rent Type cards (Single Day/Multiple Day/Resort/...) set
        // #rbfw_item_type's value directly via .val() when clicked (see
        // initRentTypeSelector()'s click handler on '.rbfw-rent-type'
        // earlier in this file) without firing a native 'change' event, so
        // a plain change listener on the hidden input never sees a card
        // click -- listen on the click itself instead, after applyType()'s
        // own show/hide work has run.
        $wrap.on('click', '.rbfw-rent-type', function () {
            setTimeout(updateVisibility, 0);
        });
        $wrap.on('click', '.rbfw-me-tab, .rbfw-me-step-next, .rbfw-me-step-prev', function () {
            setTimeout(updateVisibility, 0);
        });
        // The rate toggles are clickable <div>s that flip a sibling hidden
        // input's value via their own handlers elsewhere in this file; those
        // may not fire a native 'change' event, so also recalc shortly after
        // any click inside the pricing panel's rate cards.
        $pricingPanel.on('click', '.toggle', function () {
            setTimeout(function () {
                if (isPreviewingMd()) { recalc(); }
                if (isPreviewingMi()) { recalcMi(); }
            }, 50);
        });

        // Single Day / Appointment: recalc on qty stepper +/-, on any edit
        // inside the price table (label/price/duration), and -- via a
        // MutationObserver, since rows are added/removed/reordered by other
        // code in this file -- whenever a row is inserted or removed. Option
        // selection itself is wired per-click inside renderOptrows().
        $wrap.on('click', '.rbfw-me-fp-sd-stepper .rbfw-me-fp-step-minus, .rbfw-me-fp-sd-stepper .rbfw-me-fp-step-plus', function () {
            var qty = Math.max(1, (parseInt($sdControls.data('qty'), 10) || 1) + parseInt($(this).data('step'), 10));
            $sdControls.data('qty', qty);
            if (isPreviewingSd()) { recalcSd(); }
        });
        $wrap.on('input change', '.rbfw_bike_car_sd_price_table_body input, .rbfw_bike_car_sd_price_table_body select', function () {
            if (isPreviewingSd()) { recalcSd(); }
        });
        var $sdTbody = $wrap.find('.rbfw_bike_car_sd_price_table_body');
        if ($sdTbody.length && window.MutationObserver) {
            new MutationObserver(function () {
                if (isPreviewingSd()) { recalcSd(); }
            }).observe($sdTbody[0], { childList: true });
        }

        // Resort: recalc on qty stepper +/-, package change, on any edit
        // inside the room price table, on the Day-long pricing toggle, and
        // whenever a room row is added/removed.
        $wrap.on('click', '.rbfw-me-fp-resort-stepper .rbfw-me-fp-step-minus, .rbfw-me-fp-resort-stepper .rbfw-me-fp-step-plus', function () {
            var qty = Math.max(1, (parseInt($resortControls.data('qty'), 10) || 1) + parseInt($(this).data('step'), 10));
            $resortControls.data('qty', qty);
            if (isPreviewingResort()) { recalcResort(); }
        });
        $wrap.on('change', '.rbfw-me-fp-resort-pkg', function () {
            if (isPreviewingResort()) { recalcResort(); }
        });
        $wrap.on('input change', '.rbfw_resort_price_table_body input', function () {
            if (isPreviewingResort()) { recalcResort(); }
        });
        $wrap.on('change', 'input[name="rbfw_enable_resort_daylong_price"]', function () {
            if (isPreviewingResort()) { recalcResort(); }
        });
        var $resortTbody = $wrap.find('.rbfw_resort_price_table_body');
        if ($resortTbody.length && window.MutationObserver) {
            new MutationObserver(function () {
                if (isPreviewingResort()) { recalcResort(); }
            }).observe($resortTbody[0], { childList: true });
        }

        // Multiple Items: recalc on the Rental Duration dropdown, on enabling/
        // disabling a price type, on any pivot threshold edit, and on any
        // item-row edit (name/qty-cap/price) including rows added/removed
        // by addItemRow()/removeItemRow() (global functions elsewhere in
        // this file) via a MutationObserver on #itemRows.
        $wrap.on('change', '.rbfw-me-fp-mi-qty-select', function () {
            $miControls.data('qty', parseInt($(this).val(), 10) || 1);
            if (isPreviewingMi()) { recalcMi(); }
        });
        $wrap.on('change', '#enableHourly, #enableDaily, #enableWeekly, #enableMonthly', function () {
            if (isPreviewingMi()) { recalcMi(); }
        });
        $wrap.on('input change', '#rbfw_mi_hourly_to_half_day_pivot, #rbfw_mi_daily_to_weekly_pivot, #rbfw_mi_weekly_to_monthly_pivot', function () {
            if (isPreviewingMi()) { recalcMi(); }
        });
        $wrap.on('input change', '#itemRows .item-name-input, #itemRows .hourly-price-input, #itemRows .daily-price-input, #itemRows .weekly-price-input, #itemRows .monthly-price-input', function () {
            if (isPreviewingMi()) { recalcMi(); }
        });
        var $itemRowsBody = $wrap.find('#itemRows')[0];
        if ($itemRowsBody && window.MutationObserver) {
            new MutationObserver(function () {
                if (isPreviewingMi()) { recalcMi(); }
            }).observe($itemRowsBody, { childList: true });
        }

        function recalcCurrent() {
            if (isPreviewingSd()) { recalcSd(); }
            else if (isPreviewingResort()) { recalcResort(); }
            else if (isPreviewingMi()) { recalcMi(); }
            else if (isPreviewingMd()) { recalc(); }
        }

        // The global Time Slots Configuration chips (admin side) that the
        // preview's own Pickup/Return Time chips are populated from.
        $wrap.on('click', '.time-slot', function () {
            setTimeout(recalcCurrent, 0);
        });

        // Extra Services: basic table (Single Day/Appointment) and the
        // category-wise table (Multiple Day family + Multiple Items), plus
        // add/remove and the category-wise table's own master enable toggle
        // (outside the table itself, so not covered by the input below).
        $wrap.on('input change', '.rbfw_es_price_config_wrapper input, .rbfw_service_category_table input', function () {
            recalcCurrent();
        });
        $wrap.on('change', 'input[name="rbfw_enable_category_service_price"]', function () {
            recalcCurrent();
        });
        var $esBody = $wrap.find('.rbfw_es_price_config_wrapper tbody.mp_event_type_sortable');
        if ($esBody.length && window.MutationObserver) {
            new MutationObserver(recalcCurrent).observe($esBody[0], { childList: true });
        }
        var $catBody = $wrap.find('.rbfw_service_category_table tbody.sortable_tr');
        if ($catBody.length && window.MutationObserver) {
            new MutationObserver(recalcCurrent).observe($catBody[0], { childList: true, subtree: true });
        }

        // Variations: field/value rows, including add/remove of either, plus
        // the "Item variation" master toggle itself (mkb-admin.js updates its
        // value to 'yes'/'no' on click before this change handler runs).
        $wrap.on('input change', '.rbfw_variations_table_body input', function () {
            recalcCurrent();
        });
        $wrap.on('change', 'input[name="rbfw_enable_variations"]', function () {
            recalcCurrent();
        });
        var $variationsBody = $wrap.find('.rbfw_variations_table_body');
        if ($variationsBody.length && window.MutationObserver) {
            new MutationObserver(recalcCurrent).observe($variationsBody[0], { childList: true, subtree: true });
        }

        // Fee Configuration Settings: master toggle, any field edit, and row
        // add/remove (rbfwAddFeeRow()/rbfwDuplicateFeeRow() insert <tr>s into
        // #wprently_fee_body).
        $wrap.on('change', 'input[name="rbfw_enable_fee_management"]', recalcCurrent);
        $wrap.on('input change', '#wprently_fee_body input, #wprently_fee_body select', recalcCurrent);
        var $feeBody = $wrap.find('#wprently_fee_body')[0];
        if ($feeBody && window.MutationObserver) {
            new MutationObserver(recalcCurrent).observe($feeBody, { childList: true });
        }

        // Off Day Settings (weekday checkboxes) and Off Date Settings (date
        // ranges) feed the Single Day calendar's weekend/unavailable marking.
        // Deferred: the checkbox's own handler (elsewhere in this file)
        // writes the comma-separated value into .rbfw-me-offday-hidden on
        // this same 'change' event, and may run after this listener.
        $wrap.on('change', '.rbfw-me-offday-checkbox, .rbfw-me-offdate-row input', function () {
            setTimeout(function () { if (isPreviewingSd()) { recalcSd(); } }, 0);
        });
        var $offdateList = $wrap.find('.rbfw-me-offdate-list')[0];
        if ($offdateList && window.MutationObserver) {
            new MutationObserver(function () {
                if (isPreviewingSd()) { recalcSd(); }
            }).observe($offdateList, { childList: true });
        }

        /* ───────────── Featured image + item name ─────────────
           Independent of pricing recalc — mirrors the Featured Image card
           and Title field live, so the preview shows what a customer sees
           before they even reach the booking fields below. */
        var $featureImg = $preview.find('.rbfw-me-fp-feature-img');
        var $featureName = $preview.find('.rbfw-me-fp-feature-name');

        function updateFeaturedPreview() {
            var $thumb = $wrap.find('.rbfw-me-thumb-preview');
            var $thumbImg = $thumb.find('img').first();
            if ($thumb.hasClass('has-image') && $thumbImg.length) {
                var src = $thumbImg.attr('src');
                var $existing = $featureImg.find('img');
                if (! $existing.length || $existing.attr('src') !== src) {
                    $featureImg.find('img').remove();
                    $featureImg.append($('<img>').attr('src', src).attr('alt', ''));
                }
                $featureImg.addClass('has-image');
            } else {
                $featureImg.removeClass('has-image').find('img').remove();
            }

            var title = $.trim($wrap.find('#rbfw_me_post_title').val());
            $featureName.text(title || 'Untitled rental item');
        }

        $wrap.on('input change', '#rbfw_me_post_title', updateFeaturedPreview);
        // The Featured Image card replaces .rbfw-me-thumb-preview's innerHTML
        // asynchronously once an image is actually picked in the media frame
        // (see initThumbnail() above) or removed via its own button -- a
        // MutationObserver catches both regardless of that timing.
        var $thumbWrap = $wrap.find('.rbfw-me-thumb-preview')[0];
        if ($thumbWrap && window.MutationObserver) {
            new MutationObserver(updateFeaturedPreview).observe($thumbWrap, { childList: true, attributes: true });
        }
        updateFeaturedPreview();

        updateVisibility();
    });

}(jQuery));
