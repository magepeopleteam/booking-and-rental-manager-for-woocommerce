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
        initSave();
        initHashNav();
    });

    /* ── Tabs ────────────────────────────────────────────────── */
    function initTabs() {
        $wrap.on('click', '.rbfw-me-tab', function () {
            var tab = $(this).data('tab');
            $wrap.find('.rbfw-me-tab').removeClass('is-active').attr('aria-selected', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true');
            $wrap.find('.rbfw-me-panel').removeClass('is-active');
            $wrap.find('.rbfw-me-panel[data-panel="' + tab + '"]').addClass('is-active');
            // Update hash
            if (postId) {
                history.replaceState(null, '', '#/rental/edit/' + postId + '/' + tab);
            }
        });
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

    /* ── Sync card title ↔ header title ─────────────────────────── */
    function initTitleSync() {
        // Card title → header title
        $wrap.on('input', '.rbfw-me-card-title-input', function () {
            $wrap.find('.rbfw-me-title-input').val($(this).val());
        });
        // Header title → card title
        $wrap.on('input', '.rbfw-me-title-input', function () {
            $wrap.find('.rbfw-me-card-title-input').val($(this).val());
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

        // Title from header input
        data.post_title = $wrap.find('.rbfw-me-title-input').val();

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
