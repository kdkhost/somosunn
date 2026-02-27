<style>
    :root {
        --unn-placeholder-light: #64748b;
        --unn-placeholder-dark: #94a3b8;
    }

    input::placeholder,
    textarea::placeholder {
        color: var(--unn-placeholder-light) !important;
        opacity: 1 !important;
    }

    html.dark input::placeholder,
    html.dark textarea::placeholder,
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder {
        color: var(--unn-placeholder-dark) !important;
        opacity: 1 !important;
    }

    input::-webkit-input-placeholder,
    textarea::-webkit-input-placeholder {
        color: var(--unn-placeholder-light) !important;
        opacity: 1 !important;
    }

    html.dark input::-webkit-input-placeholder,
    html.dark textarea::-webkit-input-placeholder,
    body.dark-mode input::-webkit-input-placeholder,
    body.dark-mode textarea::-webkit-input-placeholder {
        color: var(--unn-placeholder-dark) !important;
        opacity: 1 !important;
    }

    input:-ms-input-placeholder,
    textarea:-ms-input-placeholder {
        color: var(--unn-placeholder-light) !important;
        opacity: 1 !important;
    }

    html.dark input:-ms-input-placeholder,
    html.dark textarea:-ms-input-placeholder,
    body.dark-mode input:-ms-input-placeholder,
    body.dark-mode textarea:-ms-input-placeholder {
        color: var(--unn-placeholder-dark) !important;
        opacity: 1 !important;
    }
</style>

<script>
    (function () {
        const SKIP_INPUT_TYPES = new Set([
            'hidden',
            'file',
            'checkbox',
            'radio',
            'range',
            'color',
            'button',
            'submit',
            'reset',
            'image'
        ]);

        function normalizeText(text) {
            if (!text) return '';

            return text
                .replace(/\s+/g, ' ')
                .replace(/\s*\*+\s*$/g, '')
                .replace(/\(\s*opcional\s*\)/ig, '')
                .trim();
        }

        function fromInputName(element) {
            const name = (element.getAttribute('name') || '').trim();
            if (!name) return '';

            const normalized = name
                .replace(/\[\]$/g, '')
                .replace(/\[[^\]]*\]/g, ' ')
                .replace(/[_\-]+/g, ' ');

            return normalizeText(normalized);
        }

        function findLabelByFor(element) {
            const id = element.id;
            if (!id || !window.CSS || typeof window.CSS.escape !== 'function') return '';

            const label = document.querySelector('label[for="' + window.CSS.escape(id) + '"]');
            return label ? normalizeText(label.textContent) : '';
        }

        function findNearbyLabel(element) {
            const parentLabel = element.closest('label');
            if (parentLabel) {
                const text = normalizeText(parentLabel.textContent);
                if (text) return text;
            }

            const wrappers = ['.form-group', '.form-field', '.field', '.input-group', '.mb-2', '.mb-3', '.mb-4'];
            for (const selector of wrappers) {
                const wrapper = element.closest(selector);
                if (!wrapper) continue;

                const localLabel = wrapper.querySelector('label');
                if (localLabel) {
                    const text = normalizeText(localLabel.textContent);
                    if (text) return text;
                }
            }

            return '';
        }

        function guessPlaceholder(element) {
            const aria = normalizeText(element.getAttribute('aria-label') || '');
            if (aria) return aria;

            const labelFor = findLabelByFor(element);
            if (labelFor) return labelFor;

            const nearby = findNearbyLabel(element);
            if (nearby) return nearby;

            return fromInputName(element);
        }

        function shouldHandle(element) {
            if (!(element instanceof HTMLElement)) return false;
            if (element.matches('[data-no-auto-placeholder], [data-no-auto-placeholder="1"]')) return false;

            const tag = element.tagName.toLowerCase();
            if (tag === 'textarea') return true;
            if (tag !== 'input') return false;

            const type = (element.getAttribute('type') || 'text').toLowerCase();
            return !SKIP_INPUT_TYPES.has(type);
        }

        function hasPlaceholder(element) {
            return normalizeText(element.getAttribute('placeholder') || '') !== '';
        }

        function applyPlaceholder(element) {
            if (!shouldHandle(element)) return;
            if (hasPlaceholder(element)) return;

            const placeholder = guessPlaceholder(element);
            if (!placeholder) return;

            element.setAttribute('placeholder', placeholder);
            element.setAttribute('data-auto-placeholder', '1');
        }

        function applyToContainer(container) {
            if (!container || !(container instanceof HTMLElement || container instanceof Document)) return;

            if (container instanceof HTMLElement) {
                applyPlaceholder(container);
            }

            const fields = container.querySelectorAll('input, textarea');
            fields.forEach(applyPlaceholder);
        }

        function initAutoPlaceholders() {
            applyToContainer(document);

            if (window.jQuery) {
                window.jQuery(document).on('shown.bs.modal pjax:end ajaxComplete', function () {
                    applyToContainer(document);
                });
            }

            if (!window.MutationObserver || !document.body) return;

            const observer = new MutationObserver(function (mutations) {
                for (const mutation of mutations) {
                    mutation.addedNodes.forEach(function (node) {
                        if (!(node instanceof HTMLElement)) return;
                        applyToContainer(node);
                    });
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAutoPlaceholders);
        } else {
            initAutoPlaceholders();
        }
    })();
</script>
