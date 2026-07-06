/**
 * AI Assistant JavaScript
 *
 * @package Booking and Rental Manager for WooCommerce
 */

(function($) {
    'use strict';

    const RBFW_AI = {
        debounceTimer: null,

        // Returns the title input element used by the current editor.
        // Modern editor: #rbfw_me_post_title. Classic editor: #title.
        $titleInput() {
            return $('#rbfw_me_post_title').length ? $('#rbfw_me_post_title') : $('#title');
        },

        // Returns the content (description) input element used by the current editor.
        // Modern editor: #rbfw_me_post_content (TinyMCE if available, else textarea).
        // Classic editor: textarea#content (TinyMCE if available, else textarea).
        $contentInput() {
            if (typeof tinyMCE !== 'undefined') {
                const mceModern = tinyMCE.get('rbfw_me_post_content');
                if (mceModern) return $(mceModern.getBody()).closest('.rbfw-me-editor-wrap').find('textarea#rbfw_me_post_content');
                const mceClassic = tinyMCE.get('content');
                if (mceClassic) return $('textarea#content');
            }
            return $('#rbfw_me_post_content').length ? $('#rbfw_me_post_content') : $('textarea#content');
        },

        // Set the description content regardless of editor (TinyMCE first, else textarea).
        setDescriptionContent(html) {
            if (typeof tinyMCE !== 'undefined') {
                if (tinyMCE.get('rbfw_me_post_content')) {
                    tinyMCE.get('rbfw_me_post_content').setContent(html);
                    return true;
                }
                if (tinyMCE.get('content')) {
                    tinyMCE.get('content').setContent(html);
                    return true;
                }
            }
            const $ta = $('#rbfw_me_post_content').length ? $('#rbfw_me_post_content') : $('textarea#content');
            if ($ta.length) { $ta.val(html); return true; }
            return false;
        },

        // Returns the post slug input (WP post_name), creating a hidden mirror next to
        // the title input if it doesn't exist (modern editor doesn't render the slug field).
        $slugInput() {
            let $slug = $('input[name="post_name"]');
            if ($slug.length) return $slug;
            // Reuse the existing mirror if it was already injected.
            $slug = $('#rbfw_ai_post_name_mirror');
            if ($slug.length) return $slug;
            // Mirror the slug inside a hidden input so the AI button can read/write it.
            // The modern editor saves post_title/post_content via its own AJAX and does
            // not currently persist post_name, so the mirror is best-effort and harmless.
            $slug = $('<input>', { type: 'hidden', name: 'post_name', id: 'rbfw_ai_post_name_mirror', value: '' });
            this.$titleInput().after($slug);
            return $slug;
        },

        // Returns the SEO meta-description textarea (modern editor). Empty jQuery set
        // in the classic editor, where the body content is used for scoring instead.
        $metaDescInput() {
            return $('#rbfw_me_meta_description');
        },

        init() {
            this.bindEvents();
            this.updateSEO();
        },

        bindEvents() {
            // AI generation buttons
            $(document).on('click', '.rbfw-ai-generate-btn', this.handleGenerate.bind(this));
            $(document).on('click', '.rbfw-ai-generate-all-btn', this.handleGenerateAll.bind(this));

            // Real-time SEO scoring (debounced)
            $(document).on('input', '#rbfw_me_post_title, #title', this.debounceUpdateSEO.bind(this));
            $(document).on('input', 'input[name="post_name"]', this.debounceUpdateSEO.bind(this));
            $(document).on('input', '#rbfw_me_meta_description', this.debounceUpdateSEO.bind(this));
            // Track classic editor TinyMCE changes too
            if (typeof tinyMCE !== 'undefined') {
                const self = this;
                const wire = function(ed) {
                    ed.on('input keyup change', function() { self.debounceUpdateSEO(); });
                };
                ['rbfw_me_post_content', 'content'].forEach(function(id){
                    if (tinyMCE.get(id)) wire(tinyMCE.get(id));
                    else tinyMCE.on('addEditor', function(e){ if (e.editor.id === id) wire(e.editor); });
                });
            }

            // Modal actions
            $(document).on('click', '.rbfw-ai-modal__close, .rbfw-ai-modal-cancel', this.closeModal.bind(this));
            $(document).on('click', '.rbfw-ai-apply', this.applyGeneratedContent.bind(this));
        },

        debounceUpdateSEO() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.updateSEO(), 500);
        },

        async handleGenerate(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const type = $btn.data('type');

            this.showLoading($btn);

            try {
                const input = this.getInputForType(type);
                const result = await this.generateContent(type, input);

                if (type === 'all') {
                    this.showPreviewModal(result);
                } else {
                    this.applySingleField(type, result.content);
                }
            } catch (error) {
                this.showError(error.message);
            } finally {
                this.hideLoading($btn);
            }
        },

        async handleGenerateAll(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);

            this.showLoading($btn);

            try {
                const title = this.$titleInput().val() || 'New rental item';
                const result = await this.generateContent('all', title);

                // Parse JSON response
                let content;
                try {
                    content = JSON.parse(result.content);
                } catch (e) {
                    throw new Error('Invalid AI response format');
                }

                this.showPreviewModal(content);
            } catch (error) {
                this.showError(error.message);
            } finally {
                this.hideLoading($btn);
            }
        },

        async generateContent(type, input) {
            const context = {
                item_type: this.getItemType(),
                category: this.getCategory()
            };

            const data = {
                action: 'rbfw_ai_generate',
                nonce: rbfwAIAssistant.nonce,
                type: type,
                input: input,
                context: JSON.stringify(context)
            };

            const response = await $.ajax({
                url: rbfwAIAssistant.ajax_url,
                method: 'POST',
                data: data
            });

            if (!response.success) {
                throw new Error(response.data || 'Generation failed');
            }

            return response.data;
        },

        async updateSEO() {
            const title = this.$titleInput().val() || '';
            const slug = this.$slugInput().val() || this.generateSlug(title);
            // Score the dedicated SEO meta description when present (modern editor);
            // fall back to the body content in the classic editor.
            const description = this.$metaDescInput().length
                ? (this.$metaDescInput().val() || '')
                : this.getPlainDescription();

            try {
                const response = await $.ajax({
                    url: rbfwAIAssistant.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'rbfw_ai_seo_score',
                        nonce: rbfwAIAssistant.nonce,
                        title: title,
                        slug: slug,
                        description: description
                    }
                });

                if (response.success) {
                    this.updateSEODisplay(response.data);
                }
            } catch (error) {
                console.error('SEO score update failed:', error);
            }
        },

        updateSEODisplay(data) {
            // Update score circle
            $('#rbfw_seo_score_value').text(data.score);
            $('#rbfw_seo_score_grade').text(data.grade);

            // Update color
            const $circle = $('#rbfw_seo_score_circle');
            $circle.removeClass('good ok bad');
            if (data.score >= 80) {
                $circle.addClass('good');
            } else if (data.score >= 60) {
                $circle.addClass('ok');
            } else {
                $circle.addClass('bad');
            }

            // Update feedback items
            ['title', 'slug', 'description'].forEach(field => {
                if (data.feedback[field]) {
                    const $status = $(`#rbfw_seo_${field}_status`);
                    $status.text(data.feedback[field].message);
                    $status.removeClass('good ok bad').addClass(data.feedback[field].status);
                }
            });
        },

        showPreviewModal(content) {
            $('#rbfw_ai_preview_title').text(content.title || '');
            $('#rbfw_ai_preview_slug').text(content.slug || '');
            $('#rbfw_ai_preview_description').text(content.description || '');
            $('#rbfw_ai_modal').removeAttr('hidden').addClass('is-visible');
        },

        closeModal() {
            $('#rbfw_ai_modal').attr('hidden', true).removeClass('is-visible');
        },

        // Strip code fences, surrounding quotes, and leading labels from an
        // AI response so it can be written directly into a form field.
        cleanOutput(type, raw) {
            if (typeof raw !== 'string') return '';
            let s = raw;

            // Markdown code fences.
            s = s.replace(/^```[a-zA-Z0-9_+\-]*\s*/, '').replace(/```\s*$/, '').replace(/`/g, '');

            // "type": "value" JSON-ish prefix.
            const m = s.match(/^\{?\s*"?type"?\s*:\s*"(.*)"\s*\}?$/is);
            if (m) s = m[1];

            // Leading labels: "Here is the title:", "Slug:", etc.
            s = s.replace(/^\s*(?:here\s+is|here's|answer|output|result|response)\s*(?:your|the)?\s*(?:title|slug|description|subtitle|answer)?\s*[:\-]\s*/i, '');
            s = s.replace(/^\s*(?:title|slug|description|subtitle)\s*[:\-]\s*/i, '');

            // Surrounding quotes.
            s = s.trim().replace(/^["'“”‘’]+|["'“”‘’]+$/g, '');

            // Collapse whitespace.
            s = s.replace(/\s+/g, ' ').trim();

            if (type === 'slug') {
                s = s.toLowerCase()
                     .replace(/[^a-z0-9]+/g, '-')
                     .replace(/^-+|-+$/g, '')
                     .replace(/-+/g, '-');
            } else if (type === 'subtitle') {
                if (s.length > 120) s = s.substring(0, 120).trim();
            }

            return s;
        },

        applyGeneratedContent() {
            const title       = this.cleanOutput('title',       $('#rbfw_ai_preview_title').text());
            const slug        = this.cleanOutput('slug',        $('#rbfw_ai_preview_slug').text());
            const description = this.cleanOutput('description', $('#rbfw_ai_preview_description').text());

            if (title) {
                this.$titleInput().val(title).trigger('input');
            }
            if (slug) {
                this.$slugInput().val(slug).trigger('input');
            }
            if (description) {
                if (this.$metaDescInput().length) {
                    this.$metaDescInput().val(description).trigger('input');
                } else {
                    this.setDescriptionContent(description);
                    this.$contentInput().trigger('input');
                }
            }

            this.closeModal();
            this.updateSEO();

            this.showSuccess('AI content applied successfully');
        },

        applySingleField(type, content) {
            const clean = this.cleanOutput(type, content);
            if (!clean) {
                this.showError('AI returned an empty response. Please try again.');
                return;
            }
            switch (type) {
                case 'title':
                    this.$titleInput().val(clean).trigger('input');
                    break;
                case 'subtitle':
                    if ($('#rbfw_me_subtitle').length) {
                        $('#rbfw_me_subtitle').val(clean).trigger('input');
                    } else {
                        var $sub = $('input[name="rbfw_item_sub_title"]');
                        if ($sub.length) $sub.val(clean).trigger('input');
                        else this.$titleInput().val(clean).trigger('input');
                    }
                    break;
                case 'slug':
                    this.$slugInput().val(clean).trigger('input');
                    break;
                case 'description':
                    if (this.$metaDescInput().length) {
                        this.$metaDescInput().val(clean).trigger('input');
                    } else {
                        this.setDescriptionContent(clean);
                        this.$contentInput().trigger('input');
                    }
                    break;
            }
            this.updateSEO();
            this.showSuccess(`${type.charAt(0).toUpperCase() + type.slice(1)} generated successfully`);
        },

        getInputForType(type) {
            switch (type) {
                case 'title':
                case 'slug':
                case 'description':
                case 'subtitle':
                    return this.$titleInput().val() || '';
                default:
                    return this.$titleInput().val() || '';
            }
        },

        getItemType() {
            return $('select[name="rbfw_item_type"]').val() || 'rental item';
        },

        getCategory() {
            const cats = $('input[name="rbfw_categories[]"]').val();
            return cats ? cats.split(',')[0] : '';
        },

        getPlainDescription() {
            let content = '';
            if (typeof tinyMCE !== 'undefined') {
                if (tinyMCE.get('rbfw_me_post_content')) {
                    content = tinyMCE.get('rbfw_me_post_content').getContent({ format: 'text' });
                } else if (tinyMCE.get('content')) {
                    content = tinyMCE.get('content').getContent({ format: 'text' });
                } else {
                    content = this.$contentInput().val() || '';
                }
            } else {
                content = this.$contentInput().val() || '';
            }
            return content.replace(/<[^>]*>/g, '').substring(0, 200);
        },

        generateSlug(text) {
            return text.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .substring(0, 60);
        },

        showLoading($btn) {
            $btn.prop('disabled', true).addClass('is-loading');
        },

        hideLoading($btn) {
            $btn.prop('disabled', false).removeClass('is-loading');
        },

        showError(message) {
            this.showNotice(message, 'error');
        },

        showSuccess(message) {
            this.showNotice(message, 'success');
        },

        showNotice(message, type) {
            const $notice = $(`<div class="rbfw-ai-notice rbfw-ai-notice--${type}">${message}</div>`);
            const $target = $('.rbfw-me-save-indicator').length
                ? $('.rbfw-me-save-indicator')
                : ($('#rbfw-ai-classic-wrap').length ? $('#rbfw-ai-classic-wrap') : $('body'));
            $target.html($notice);
            if ($target.hasClass('rbfw-me-save-indicator')) {
                $target.addClass('is-visible');
                setTimeout(() => $target.removeClass('is-visible'), 3000);
            } else {
                setTimeout(() => $notice.fadeOut(300, function() { $(this).remove(); }), 3000);
            }
        }
    };

    $(document).ready(() => RBFW_AI.init());
})(jQuery);
