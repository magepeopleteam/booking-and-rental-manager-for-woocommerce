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
        initResortImages();
        initGallery();
        initAdditionalGallery();
        initCategories();
        initFeatures();
        initTitleSync();
        initRelatedPicker();
        initFaq();
        initTerm();
        initOffDays();
        initPublishDropdown();
        initSave();
        initHashNav();
    });

    /* ── Stepper ─────────────────────────────────────────────── */
    function initTabs() {
        var $tabs = $wrap.find('.rbfw-me-tab');
        var total = $tabs.length;

        function goToStep(idx) {
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
            if (postId) {
                history.replaceState(null, '', '#/rental/edit/' + postId + '/' + tabKey);
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

        goToStep($tabs.index($tabs.filter('.is-active')));
    }

    /* ── Restore tab from URL hash ───────────────────────────── */
    function initHashNav() {
        var hash = window.location.hash;
        var match = hash.match(/#\/rental\/(?:edit|new)\/\d+\/(\w+)/);
        if (match && match[1]) {
            var $tab = $wrap.find('.rbfw-me-tab[data-tab="' + match[1] + '"]');
            if ($tab.length) $tab.trigger('click');
        }
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

    function doSave(status) {
        var $indicator = $wrap.find('.rbfw-me-save-indicator');
        $indicator.text(cfg.i18n && cfg.i18n.saving || 'Saving…').removeClass('is-saved is-error').addClass('is-saving');

        var data = collectFormData();
        data.action      = 'rbfw_modern_editor_save';
        data.nonce       = cfg.nonce_save || '';
        data.post_id     = postId;
        data.post_status = status;

        $.post(cfg.ajax_url, data, function (res) {
            $indicator.removeClass('is-saving');
            if (res.success) {
                $indicator.text(cfg.i18n && cfg.i18n.saved || 'Saved').addClass('is-saved');
                // Update publish button label if status changed
                if (status === 'publish') {
                    $wrap.find('.rbfw-me-publish').text(cfg.i18n && cfg.i18n.update || 'Update').data('published', '1');
                }
                // Update status dot
                var $dot   = $wrap.find('.rbfw-me-status-dot');
                var $label = $wrap.find('.rbfw-me-status-label');
                $dot.attr('class', 'rbfw-me-status-dot rbfw-me-status-dot--' + status);
                $label.text(status.charAt(0).toUpperCase() + status.slice(1));

                setTimeout(function () { $indicator.text(''); }, 3000);
            } else {
                $indicator.text(cfg.i18n && cfg.i18n.save_error || 'Save failed').addClass('is-error');
            }
        }).fail(function () {
            $indicator.removeClass('is-saving').text(cfg.i18n && cfg.i18n.save_error || 'Save failed').addClass('is-error');
        });
    }

    /* ── Sync card title → header h1 ────────────────────────────── */
    function initTitleSync() {
        $wrap.on('input', '.rbfw-me-card-title-input', function () {
            $wrap.find('.rbfw-me-title-display').text($(this).val());
        });
    }

    /* ── Category checkboxes → hidden input sync ─────────────── */
    function initCategories() {
        $wrap.on('change', '.rbfw-me-cat-checkbox', function () {
            var selected = [];
            $wrap.find('.rbfw-me-cat-checkbox:checked').each(function () {
                selected.push($(this).data('name'));
            });
            $wrap.find('.rbfw-me-cats-hidden').val(selected.join(','));
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
                + '<div class="feature_category_title"><label>Feature Category Title</label>'
                + '<input type="text" name="rbfw_feature_category[' + nextCat + '][cat_title]" data-key="' + nextCat + '" placeholder="Feature Category Label" /></div>'
                + '<div class="feature_category_inner_item_wrap sortable">'
                + '<div class="item">'
                + '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="0"><i class="fas fa-circle-plus"></i> Icon</a>'
                + '<div class="rbfw_feature_icon_preview" data-key="0"></div>'
                + '<input type="hidden" name="rbfw_feature_category[' + nextCat + '][cat_features][0][icon]" data-key="0" class="rbfw_feature_icon" />'
                + '<input type="text" name="rbfw_feature_category[' + nextCat + '][cat_features][0][title]" placeholder="Features Name" data-key="0" />'
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
            var $items   = $(this).siblings('.rbfw_feature_category').find('.feature_category_inner_item_wrap');
            var lastKey  = parseInt($items.find('div.item:last-child input[data-key]').attr('data-key')) || 0;
            var newKey   = lastKey + 1;
            var dataCat  = $(this).closest('tr').attr('data-cat');
            var html = '<div class="item">'
                + '<a href="#rbfw_features_icon_list_wrapper" class="rbfw_feature_icon_btn btn" data-key="' + newKey + '"><i class="fas fa-circle-plus"></i> Icon</a>'
                + '<div class="rbfw_feature_icon_preview" data-key="' + newKey + '"></div>'
                + '<input type="hidden" name="rbfw_feature_category[' + dataCat + '][cat_features][' + newKey + '][icon]" data-key="' + newKey + '" class="rbfw_feature_icon" />'
                + '<input type="text" name="rbfw_feature_category[' + dataCat + '][cat_features][' + newKey + '][title]" placeholder="Features Name" data-key="' + newKey + '" />'
                + '<div><span class="button sort"><i class="fas fa-arrows-alt"></i></span>'
                + '<span class="button remove" onclick="jQuery(this).parent().parent().remove()"><i class="fas fa-trash-can"></i></span></div>'
                + '</div>';
            $items.append(html);
            if ($.fn.sortable) $items.sortable({ handle: '.sort' });
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
            if (!confirm('Are you sure you want to delete this FAQ?')) return;
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
            if (!confirm('Are you sure you want to delete this term?')) return;
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

        // Add new date range row
        $wrap.on('click', '.rbfw-me-offdate-add', function () {
            var $list = $(this).closest('.rbfw-me-card__body').find('.rbfw-me-offdate-list');
            var $row = $(
                '<div class="rbfw-me-offdate-row">' +
                    '<div class="rbfw-me-field">' +
                        '<label class="rbfw-me-label">Start Date</label>' +
                        '<input type="date" name="off_days_start[]" class="rbfw-me-input">' +
                    '</div>' +
                    '<div class="rbfw-me-field">' +
                        '<label class="rbfw-me-label">End Date</label>' +
                        '<input type="date" name="off_days_end[]" class="rbfw-me-input">' +
                    '</div>' +
                    '<button type="button" class="rbfw-me-offdate-remove" title="Remove">' +
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
