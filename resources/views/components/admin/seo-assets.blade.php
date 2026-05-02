{{-- Global SEO assets: counters, listing preview, SEO Health logic + styles.
     Wrapped in @once so it can be safely emitted from any view that uses the
     SEO components. --}}
@once
    @push('scripts')
        <style>
            /* =============== SEO field visual states =============== */
            [data-seo-field] .seo-input.is-seo-success {
                border-color: var(--bs-success) !important;
                color: var(--bs-success);
            }

            [data-seo-field] .seo-input.is-seo-warning {
                border-color: var(--bs-warning) !important;
                color: #b27800;
            }

            [data-seo-field] .seo-input.is-seo-danger {
                border-color: var(--bs-danger) !important;
                color: var(--bs-danger);
            }

            [data-seo-field] .seo-counter-badge {
                transition: all 150ms ease-in-out;
            }

            [data-seo-field] .seo-counter-badge.is-seo-success {
                background-color: rgba(80, 205, 137, 0.15);
                color: var(--bs-success);
            }

            [data-seo-field] .seo-counter-badge.is-seo-warning {
                background-color: rgba(255, 199, 0, 0.18);
                color: #b27800;
            }

            [data-seo-field] .seo-counter-badge.is-seo-danger {
                background-color: rgba(241, 65, 108, 0.15);
                color: var(--bs-danger);
            }

            /* =============== Search-listing preview =============== */
            .cms-search-preview {
                background: var(--bs-light);
                border-radius: 0.625rem;
                padding: 1rem 1.25rem;
            }

            .cms-search-preview .preview-title {
                color: #1a0dab;
                font-size: 1.05rem;
                font-weight: 600;
                line-height: 1.3;
                word-break: break-word;
            }

            .cms-search-preview .preview-url {
                color: #006621;
                font-size: 0.85rem;
                margin-top: 2px;
            }

            .cms-search-preview .preview-description {
                color: #545454;
                font-size: 0.9rem;
                margin-top: 4px;
                line-height: 1.45;
            }

            /* =============== SEO Health aside =============== */
            .seo-score-donut [data-seo-score-arc] {
                transition: stroke-dashoffset 350ms ease-in-out, stroke 200ms ease-in-out;
            }

            .seo-checklist .seo-check {
                transition: opacity 150ms ease-in-out;
            }

            .seo-check-icon {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: var(--bs-gray-200);
                color: var(--bs-gray-600);
                font-size: 0.85rem;
            }

            .seo-check.is-success .seo-check-icon {
                background: rgba(80, 205, 137, 0.18);
                color: var(--bs-success);
            }

            .seo-check.is-warning .seo-check-icon {
                background: rgba(255, 199, 0, 0.2);
                color: #b27800;
            }

            .seo-check.is-danger .seo-check-icon {
                background: rgba(241, 65, 108, 0.18);
                color: var(--bs-danger);
            }

            .seo-check.is-success .seo-check-status {
                background: rgba(80, 205, 137, 0.18);
                color: var(--bs-success);
            }

            .seo-check.is-warning .seo-check-status {
                background: rgba(255, 199, 0, 0.2);
                color: #b27800;
            }

            .seo-check.is-danger .seo-check-status {
                background: rgba(241, 65, 108, 0.18);
                color: var(--bs-danger);
            }

            .seo-check.is-empty {
                opacity: 0.7;
            }

            .letter-spacing-1 {
                letter-spacing: 0.05em;
            }
        </style>

        <script>
            (function () {
                'use strict';

                var I18N = {
                    characters: @json(__('characters')),
                    keywords: @json(__('keywords')),
                    empty: @json(__('Empty')),
                    ok: @json(__('OK')),
                    tooShort: @json(__('Too short')),
                    tooLong: @json(__('Too long')),
                    missing: @json(__('Missing')),
                    added: @json(__('Added')),
                    excellent: @json(__('Excellent SEO')),
                    good: @json(__('Good — keep improving')),
                    needsWork: @json(__('Needs work')),
                    poor: @json(__('Poor — fix the issues')),
                };

                var SEO_BUS = (function () {
                    var subscribers = [];
                    return {
                        subscribe: function (fn) { subscribers.push(fn); },
                        publish: function (source) {
                            subscribers.forEach(function (fn) {
                                try { fn(source); } catch (e) {}
                            });
                        },
                    };
                })();

                // ====================================================
                //  Field counters
                // ====================================================
                function evalLength(value, optimalMin, optimalMax, hardMax) {
                    var length = (value || '').length;
                    if (hardMax > 0 && length > hardMax) {
                        return { state: 'danger', length: length };
                    }
                    if (optimalMin > 0 && optimalMax > 0) {
                        if (length === 0) return { state: 'neutral', length: length };
                        if (length >= optimalMin && length <= optimalMax) {
                            return { state: 'success', length: length };
                        }
                        return { state: 'warning', length: length };
                    }
                    return { state: 'neutral', length: length };
                }

                function evalKeywords(value, optimalMin, optimalMax) {
                    if (!value) return { state: 'neutral', length: 0 };
                    var parts = String(value).split(',').map(function (p) {
                        return p.trim();
                    }).filter(Boolean);
                    if (parts.length === 0) return { state: 'neutral', length: 0 };
                    if (parts.length >= optimalMin && parts.length <= optimalMax) {
                        return { state: 'success', length: parts.length };
                    }
                    return { state: 'warning', length: parts.length };
                }

                function applyFieldState(input, badge, valueSpan, result) {
                    ['is-seo-success', 'is-seo-warning', 'is-seo-danger'].forEach(function (cls) {
                        input.classList.remove(cls);
                        if (badge) badge.classList.remove(cls);
                    });
                    if (result.state !== 'neutral') {
                        var cls = 'is-seo-' + result.state;
                        input.classList.add(cls);
                        if (badge) badge.classList.add(cls);
                    }
                    if (valueSpan) valueSpan.textContent = result.length;
                }

                function bindCounter(input) {
                    var optimalMin = parseInt(input.dataset.seoOptimalMin || '0', 10);
                    var optimalMax = parseInt(input.dataset.seoOptimalMax || '0', 10);
                    var hardMax = parseInt(input.dataset.seoHardMax || '0', 10);
                    var unit = input.dataset.seoUnit || 'characters';

                    var wrapper = input.closest('[data-seo-field]');
                    if (!wrapper) return;

                    var badge = wrapper.querySelector('.seo-counter-badge');
                    var valueSpan = wrapper.querySelector('.seo-counter-value');

                    function update() {
                        var result = unit === 'keywords'
                            ? evalKeywords(input.value, optimalMin || 3, optimalMax || 8)
                            : evalLength(input.value, optimalMin, optimalMax, hardMax);

                        applyFieldState(input, badge, valueSpan, result);
                        SEO_BUS.publish(input);
                    }

                    input.addEventListener('input', update);
                    input.addEventListener('change', update);
                    update();
                }

                // ====================================================
                //  Search-listing preview
                // ====================================================
                function safeQuery(selector, root) {
                    if (!selector) return null;
                    var scope = root && root.querySelector ? root : document;
                    try {
                        return scope.querySelector(selector);
                    } catch (e) {
                        return null;
                    }
                }

                function safeQueryAll(selector, root) {
                    if (!selector) return [];
                    var scope = root && root.querySelector ? root : document;
                    try {
                        return Array.prototype.slice.call(scope.querySelectorAll(selector));
                    } catch (e) {
                        return [];
                    }
                }

                function initSearchPreview() {
                    document.querySelectorAll('[data-seo-preview]').forEach(function (preview) {
                        var titleNode = preview.querySelector('.preview-title');
                        var descNode = preview.querySelector('.preview-description');
                        var urlNode = preview.querySelector('.preview-url');

                        var titleSource = safeQuery(preview.dataset.titleSource);
                        var descSource = safeQuery(preview.dataset.descSource);
                        var slugSource = safeQuery(preview.dataset.slugSource);

                        var defaultTitle = preview.dataset.defaultTitle || '';
                        var defaultDesc = preview.dataset.defaultDesc || '';
                        var baseUrl = preview.dataset.baseUrl || '/';

                        function update() {
                            if (titleNode) {
                                var t = (titleSource && titleSource.value) || defaultTitle;
                                titleNode.textContent = t || @json(__('Your title here'));
                            }
                            if (descNode) {
                                var d = (descSource && descSource.value) || defaultDesc;
                                descNode.textContent = d || @json(__('Your meta description preview will appear here.'));
                            }
                            if (urlNode) {
                                var s = (slugSource && slugSource.value) || '';
                                urlNode.textContent = baseUrl + s;
                            }
                        }

                        [titleSource, descSource, slugSource].forEach(function (src) {
                            if (src) {
                                src.addEventListener('input', update);
                                src.addEventListener('change', update);
                            }
                        });

                        update();
                    });
                }

                // ====================================================
                //  SEO Health aside
                // ====================================================
                function getTinymceContent(targetEl) {
                    if (typeof tinymce === 'undefined' || !targetEl || !targetEl.id) {
                        return targetEl ? (targetEl.value || '') : '';
                    }
                    var editor = tinymce.get(targetEl.id);
                    if (editor) return editor.getContent({ format: 'text' }) || '';
                    return targetEl.value || '';
                }

                function evaluateCheck(check, root) {
                    var scope = root && root.querySelector ? root : document;
                    var rule = check.dataset.seoRule;
                    var min = parseInt(check.dataset.seoMin || '0', 10);
                    var max = parseInt(check.dataset.seoMax || '0', 10);
                    var hardMax = parseInt(check.dataset.seoHardMax || '0', 10);
                    var target = safeQuery(check.dataset.seoTarget, scope);

                    if (rule === 'image') {
                        var hasInitial = check.dataset.seoInitial === '1';
                        var targets = safeQueryAll(check.dataset.seoTarget, scope);
                        var hasFile = targets.some(function (item) {
                            return item && item.files && item.files.length > 0;
                        });
                        var hasValue = targets.some(function (item) {
                            return item && item.value && String(item.value).trim().length > 0;
                        });
                        if (hasFile || hasValue || hasInitial) {
                            return { state: 'success', label: I18N.added };
                        }
                        return { state: 'danger', label: I18N.missing };
                    }

                    if (!target) {
                        return { state: 'empty', label: I18N.empty, length: 0 };
                    }

                    var value = (target.id === 'tinymce' || (target.tagName === 'TEXTAREA' && target.classList.contains('tinymce-editor')))
                        ? getTinymceContent(target)
                        : (target.value || '');

                    if (rule === 'count') {
                        var parts = String(value).split(',').map(function (p) { return p.trim(); }).filter(Boolean);
                        if (parts.length === 0) return { state: 'empty', label: I18N.empty, length: 0, unit: I18N.keywords };
                        if (parts.length < min) return { state: 'warning', label: I18N.tooShort, length: parts.length, unit: I18N.keywords };
                        if (parts.length > max) return { state: 'warning', label: I18N.tooLong, length: parts.length, unit: I18N.keywords };
                        return { state: 'success', label: I18N.ok, length: parts.length, unit: I18N.keywords };
                    }

                    if (rule === 'presence') {
                        if (!value || String(value).trim().length === 0) {
                            return { state: 'danger', label: I18N.missing };
                        }
                        return { state: 'success', label: I18N.added };
                    }

                    var length = (value || '').length;
                    if (length === 0) return { state: 'empty', label: I18N.empty, length: 0, unit: I18N.characters };
                    if (hardMax > 0 && length > hardMax) return { state: 'danger', label: I18N.tooLong, length: length, unit: I18N.characters };
                    if (min > 0 && length < min) return { state: 'warning', label: I18N.tooShort, length: length, unit: I18N.characters };
                    if (max > 0 && length > max) return { state: 'warning', label: I18N.tooLong, length: length, unit: I18N.characters };
                    return { state: 'success', label: I18N.ok, length: length, unit: I18N.characters };
                }

                /** 0–1 fraction for the donut score (warnings count partial credit so RTL / Arabic is not stuck at 0). */
                function scoreFractionForCheck(result, rule) {
                    if (result.state === 'success') return 1;
                    if (result.state === 'warning') return 0.65;
                    if (result.state === 'empty') return 0;
                    if (result.state === 'danger') {
                        if (rule === 'image' || rule === 'presence') return 0;
                        return 0.25;
                    }
                    return 0;
                }

                var STATE_CLASSES = ['is-success', 'is-warning', 'is-danger', 'is-empty'];
                var STATE_ICONS = {
                    success: 'bi bi-check-circle-fill',
                    warning: 'bi bi-exclamation-triangle-fill',
                    danger: 'bi bi-x-circle-fill',
                    empty: 'bi bi-circle',
                };

                function paintCheck(check, result) {
                    STATE_CLASSES.forEach(function (cls) { check.classList.remove(cls); });
                    check.classList.add('is-' + result.state);

                    var iconWrap = check.querySelector('.seo-check-icon i');
                    if (iconWrap) iconWrap.className = STATE_ICONS[result.state] || STATE_ICONS.empty;

                    var statusBadge = check.querySelector('.seo-check-status');
                    if (statusBadge) {
                        if (result.state === 'empty') {
                            statusBadge.textContent = result.label;
                        } else if (typeof result.length !== 'undefined') {
                            statusBadge.textContent = result.length + (result.unit ? ' ' + result.unit : '');
                        } else {
                            statusBadge.textContent = result.label;
                        }
                    }
                }

                function paintScore(donut, score) {
                    var arc = donut.querySelector('[data-seo-score-arc]');
                    var value = donut.querySelector('[data-seo-score-value]');
                    var label = donut.querySelector('[data-seo-score-label]');

                    if (value) value.textContent = score;

                    if (arc) {
                        var circumference = parseFloat(arc.dataset.seoArcCircumference || '0');
                        var offset = circumference - (circumference * (score / 100));
                        arc.style.strokeDashoffset = offset;

                        var color;
                        if (score >= 80) color = 'var(--bs-success)';
                        else if (score >= 60) color = 'var(--bs-primary)';
                        else if (score >= 40) color = 'var(--bs-warning)';
                        else color = 'var(--bs-danger)';
                        arc.style.stroke = color;
                    }

                    if (label) {
                        var msg, tone;
                        if (score >= 80) { msg = I18N.excellent; tone = 'text-success'; }
                        else if (score >= 60) { msg = I18N.good; tone = 'text-primary'; }
                        else if (score >= 40) { msg = I18N.needsWork; tone = 'text-warning'; }
                        else { msg = I18N.poor; tone = 'text-danger'; }

                        label.classList.remove('text-success', 'text-primary', 'text-warning', 'text-danger', 'text-muted');
                        label.classList.add(tone);
                        label.textContent = msg;
                    }
                }

                function initSeoHealth() {
                    document.querySelectorAll('[data-seo-health]').forEach(function (card) {
                        var checks = Array.prototype.slice.call(card.querySelectorAll('[data-seo-check]'));
                        if (checks.length === 0) return;
                        var donut = card.querySelector('[data-seo-score-summary]');
                        var formRoot = card.closest('form');

                        function recompute() {
                            var totalFrac = 0;
                            checks.forEach(function (check) {
                                var result = evaluateCheck(check, formRoot);
                                paintCheck(check, result);
                                totalFrac += scoreFractionForCheck(result, check.dataset.seoRule);
                            });
                            var score = checks.length === 0 ? 0 : Math.round((totalFrac / checks.length) * 100);
                            if (donut) paintScore(donut, score);
                        }

                        var form = formRoot || document;
                        ['input', 'change'].forEach(function (evt) {
                            form.addEventListener(evt, recompute, true);
                        });

                        SEO_BUS.subscribe(recompute);

                        function hookTinymce() {
                            if (typeof tinymce === 'undefined') return;
                            tinymce.on('AddEditor', function (e) {
                                e.editor.on('input change keyup blur SetContent', recompute);
                            });
                            (tinymce.editors || []).forEach(function (ed) {
                                ed.on('input change keyup blur SetContent', recompute);
                            });
                        }
                        if (typeof tinymce === 'undefined') {
                            var attempts = 0;
                            var interval = setInterval(function () {
                                attempts++;
                                if (typeof tinymce !== 'undefined' || attempts > 40) {
                                    clearInterval(interval);
                                    hookTinymce();
                                    recompute();
                                }
                            }, 250);
                        } else {
                            hookTinymce();
                        }

                        recompute();
                    });
                }

                function safeRun(fn, label) {
                    try { fn(); } catch (e) {
                        if (window.console && console.error) {
                            console.error('[SEO] ' + label + ' failed:', e);
                        }
                    }
                }

                function init() {
                    safeRun(function () {
                        document.querySelectorAll('[data-seo-counter="true"]').forEach(function (input) {
                            try { bindCounter(input); } catch (e) {}
                        });
                    }, 'bindCounters');
                    safeRun(initSearchPreview, 'initSearchPreview');
                    safeRun(initSeoHealth, 'initSeoHealth');
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>
    @endpush
@endonce
