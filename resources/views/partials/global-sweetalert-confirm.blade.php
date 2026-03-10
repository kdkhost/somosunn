<script>
    (function () {
        if (window.__unnGlobalSweetAlertConfirmInitialized) {
            return;
        }

        window.__unnGlobalSweetAlertConfirmInitialized = true;

        function decodeConfirmText(value) {
            return String(value || '')
                .replace(/\\\\/g, '\\')
                .replace(/\\'/g, "'")
                .replace(/\\"/g, '"')
                .replace(/\\n/g, '\n')
                .replace(/\\r/g, '\r')
                .replace(/\\t/g, '\t');
        }

        function extractInlineConfirm(attributeValue) {
            if (!attributeValue) {
                return null;
            }

            const normalized = String(attributeValue).trim();
            const match = normalized.match(/^return\s+confirm\((['"])([\s\S]*?)\1\)\s*;?$/i);

            if (!match) {
                return null;
            }

            return decodeConfirmText(match[2]);
        }

        function setConfirmDataset(element, message) {
            if (!element || !message) {
                return;
            }

            if (!element.getAttribute('data-confirm-text')) {
                element.setAttribute('data-confirm-text', message);
            }

            if (!element.getAttribute('data-confirm-title')) {
                const destructive = /excluir|apagar|remover|revogar/i.test(message);
                element.setAttribute('data-confirm-title', destructive ? 'Deseja continuar?' : 'Confirmar ação');
            }
        }

        function upgradeInlineConfirm(root) {
            if (!root || typeof root.querySelectorAll !== 'function') {
                return;
            }

            root.querySelectorAll('form[onsubmit], a[onclick], button[onclick], input[onclick]').forEach(function (element) {
                const attributeName = element.tagName === 'FORM' ? 'onsubmit' : 'onclick';
                const message = extractInlineConfirm(element.getAttribute(attributeName));

                if (!message) {
                    return;
                }

                setConfirmDataset(element, message);
                element.removeAttribute(attributeName);
            });
        }

        function buildConfirmOptions(element, fallbackText) {
            const text = (element && element.getAttribute('data-confirm-text')) || fallbackText || 'Confirme para continuar.';
            const destructive = /excluir|apagar|remover|revogar/i.test(text);

            return {
                title: (element && element.getAttribute('data-confirm-title')) || (destructive ? 'Deseja continuar?' : 'Confirmar ação'),
                text: text,
                icon: (element && element.getAttribute('data-confirm-icon')) || (destructive ? 'warning' : 'question'),
                confirmButtonText: (element && element.getAttribute('data-confirm-confirm')) || 'Sim, continuar',
                cancelButtonText: (element && element.getAttribute('data-confirm-cancel')) || 'Cancelar',
                confirmButtonColor: (element && element.getAttribute('data-confirm-confirm-color')) || '#2563eb',
                cancelButtonColor: (element && element.getAttribute('data-confirm-cancel-color')) || '#64748b',
                reverseButtons: true,
                focusCancel: true
            };
        }

        window.showConfirmDialog = async function (options) {
            const merged = Object.assign({
                title: 'Confirmar ação',
                text: 'Confirme para continuar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true
            }, options || {});

            if (!window.Swal || typeof window.Swal.fire !== 'function') {
                return window.confirm(merged.text || 'Confirme para continuar.');
            }

            const result = await window.Swal.fire(merged);
            return !!result.isConfirmed;
        };

        function submitConfirmedForm(form, submitter) {
            if (!form) {
                return;
            }

            form.dataset.confirmBypass = '1';

            if (submitter && submitter.dataset) {
                submitter.dataset.confirmBypass = '1';
            }

            if (form.classList.contains('ajax-form')) {
                if (window.jQuery) {
                    window.jQuery(form).trigger('submit');
                    return;
                }

                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                return;
            }

            if (typeof HTMLFormElement !== 'undefined' && HTMLFormElement.prototype.submit) {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            form.submit();
        }

        window.confirmAction = function (event, title, text, onConfirm) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            return window.showConfirmDialog({
                title: title || 'Confirmar ação',
                text: text || 'Confirme para continuar.',
                icon: 'warning'
            }).then(function (confirmed) {
                if (!confirmed) {
                    return false;
                }

                if (typeof onConfirm === 'function') {
                    onConfirm();
                    return true;
                }

                const target = event && (event.currentTarget || event.target);
                const form = target && target.closest ? target.closest('form') : null;

                if (form) {
                    submitConfirmedForm(form, target);
                    return true;
                }

                const href = target && target.getAttribute ? target.getAttribute('href') : null;
                if (href && href !== '#') {
                    window.location.href = href;
                    return true;
                }

                return true;
            });
        };

        document.addEventListener('submit', function (event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.dataset.confirmBypass === '1') {
                form.dataset.confirmBypass = '0';
                return;
            }

            const inlineText = extractInlineConfirm(form.getAttribute('onsubmit'));
            if (inlineText) {
                setConfirmDataset(form, inlineText);
                form.removeAttribute('onsubmit');
            }

            if (!form.hasAttribute('data-confirm-text') && !form.hasAttribute('data-confirm-title')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            window.showConfirmDialog(buildConfirmOptions(form, inlineText)).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                submitConfirmedForm(form, event.submitter || null);
            });
        }, true);

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('a[onclick], button[onclick], input[onclick], a[data-confirm-text], button[data-confirm-text], input[data-confirm-text], a[data-confirm-title], button[data-confirm-title], input[data-confirm-title]');

            if (!trigger) {
                return;
            }

            if (trigger.dataset.confirmBypass === '1') {
                trigger.dataset.confirmBypass = '0';
                return;
            }

            if (trigger.matches('.btn-delete, [data-confirm-delete]')) {
                return;
            }

            const inlineText = extractInlineConfirm(trigger.getAttribute('onclick'));
            if (inlineText) {
                setConfirmDataset(trigger, inlineText);
                trigger.removeAttribute('onclick');
            }

            if (!trigger.hasAttribute('data-confirm-text') && !trigger.hasAttribute('data-confirm-title')) {
                return;
            }

            const form = trigger.closest('form');
            if (form && (trigger.tagName === 'BUTTON' || trigger.tagName === 'INPUT')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            window.showConfirmDialog(buildConfirmOptions(trigger, inlineText)).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                if (form) {
                    submitConfirmedForm(form, trigger);
                    return;
                }

                const href = trigger.getAttribute('href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            });
        }, true);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                upgradeInlineConfirm(document);
            });
        } else {
            upgradeInlineConfirm(document);
        }
    })();
</script>
