<style>
    .form-draft-status {
        position: fixed;
        left: 1rem;
        bottom: 1rem;
        z-index: 1065;
        display: none;
        align-items: center;
        gap: 0.55rem;
        padding: 0.72rem 0.95rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.3);
        background: rgba(15, 23, 42, 0.94);
        color: #e2e8f0;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.24);
        backdrop-filter: blur(14px);
        font-size: 0.82rem;
        line-height: 1;
    }

    .form-draft-status.is-visible {
        display: inline-flex;
    }

    .form-draft-status__dot {
        width: 0.6rem;
        height: 0.6rem;
        border-radius: 999px;
        background: #38bdf8;
        box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.35);
        transition: background-color 0.2s ease;
    }

    .form-draft-status.is-pending .form-draft-status__dot {
        background: #f59e0b;
        animation: form-draft-pulse 1.25s infinite;
    }

    .form-draft-status.is-saved .form-draft-status__dot {
        background: #22c55e;
    }

    .form-draft-status.is-restored .form-draft-status__dot {
        background: #38bdf8;
    }

    @keyframes form-draft-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.38);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
        }
    }

    @media (max-width: 768px) {
        .form-draft-status {
            left: 0.85rem;
            right: 0.85rem;
            bottom: 0.85rem;
            justify-content: center;
            width: auto;
        }
    }
</style>

<script>
    (function () {
        const IGNORED_INPUT_TYPES = new Set(['password', 'file', 'hidden', 'submit', 'button', 'reset', 'image']);
        const AUTOSAVE_DELAY_MS = 850;
        const saveTimers = new WeakMap();
        let draftStatusTimeout = null;

        function ensureDraftStatusNode() {
            let node = document.getElementById('formDraftStatus');

            if (node) {
                return node;
            }

            node = document.createElement('div');
            node.id = 'formDraftStatus';
            node.className = 'form-draft-status';
            node.innerHTML = '<span class="form-draft-status__dot"></span><span data-draft-message>Rascunho automatico ativo</span>';
            document.body.appendChild(node);

            return node;
        }

        function showDraftStatus(message, state) {
            const node = ensureDraftStatusNode();
            const messageNode = node.querySelector('[data-draft-message]');

            node.classList.remove('is-pending', 'is-saved', 'is-restored');
            node.classList.add('is-visible');

            if (state) {
                node.classList.add(state);
            }

            if (messageNode) {
                messageNode.textContent = message;
            }

            if (draftStatusTimeout) {
                clearTimeout(draftStatusTimeout);
            }

            draftStatusTimeout = window.setTimeout(function () {
                node.classList.remove('is-visible', 'is-pending', 'is-saved', 'is-restored');
            }, state === 'is-pending' ? 2200 : 3000);
        }

        function getFormStorageKey(form, index) {
            const formId = form.id || form.getAttribute('name') || form.getAttribute('action') || ('form-' + index);
            return ['unn-form-draft', window.location.pathname, formId].join('::');
        }

        function getEligibleFields(form) {
            return Array.from(form.querySelectorAll('input[name], textarea[name], select[name]')).filter(function (field) {
                const tag = field.tagName.toLowerCase();
                const type = (field.getAttribute('type') || '').toLowerCase();

                if (!field.name || field.disabled) {
                    return false;
                }

                if (tag === 'input' && IGNORED_INPUT_TYPES.has(type)) {
                    return false;
                }

                return true;
            });
        }

        function syncSummernoteEditors(form) {
            if (!(window.jQuery && $.fn && $.fn.summernote)) {
                return;
            }

            $(form).find('textarea.summernote, textarea.summernote-sm').each(function () {
                const $field = $(this);

                if ($field.next('.note-editor').length) {
                    $field.val($field.summernote('code'));
                }
            });
        }

        function serializeFormState(form) {
            syncSummernoteEditors(form);

            const data = {};
            const fields = getEligibleFields(form);

            fields.forEach(function (field) {
                const tag = field.tagName.toLowerCase();
                const type = (field.getAttribute('type') || '').toLowerCase();
                const name = field.name;

                if (type === 'radio') {
                    if (field.checked) {
                        data[name] = field.value;
                    }
                    return;
                }

                if (type === 'checkbox') {
                    const group = fields.filter(function (candidate) {
                        return candidate.name === name && (candidate.getAttribute('type') || '').toLowerCase() === 'checkbox';
                    });

                    if (group.length > 1 || name.endsWith('[]')) {
                        data[name] = group.filter(function (candidate) {
                            return candidate.checked;
                        }).map(function (candidate) {
                            return candidate.value;
                        });
                    } else {
                        data[name] = field.checked;
                    }

                    return;
                }

                if (tag === 'select' && field.multiple) {
                    data[name] = Array.from(field.selectedOptions).map(function (option) {
                        return option.value;
                    });
                    return;
                }

                data[name] = field.value;
            });

            return data;
        }

        function applyFieldValue(field, value) {
            const tag = field.tagName.toLowerCase();
            const type = (field.getAttribute('type') || '').toLowerCase();

            if (type === 'radio') {
                field.checked = String(field.value) === String(value);
                return;
            }

            if (type === 'checkbox') {
                if (Array.isArray(value)) {
                    field.checked = value.map(String).includes(String(field.value));
                } else {
                    field.checked = !!value;
                }
                return;
            }

            if (tag === 'select' && field.multiple && Array.isArray(value)) {
                Array.from(field.options).forEach(function (option) {
                    option.selected = value.map(String).includes(String(option.value));
                });
                return;
            }

            if ((field.classList.contains('summernote') || field.classList.contains('summernote-sm'))
                && window.jQuery && $.fn && $.fn.summernote && $(field).next('.note-editor').length) {
                $(field).summernote('code', value || '');
                return;
            }

            field.value = value == null ? '' : value;
        }

        function restoreFormState(form, index) {
            const key = getFormStorageKey(form, index);
            const raw = window.localStorage.getItem(key);

            if (!raw) {
                return;
            }

            let stored;

            try {
                stored = JSON.parse(raw);
            } catch (error) {
                window.localStorage.removeItem(key);
                return;
            }

            if (!stored || typeof stored !== 'object') {
                return;
            }

            const fields = getEligibleFields(form);
            let restoredAnyField = false;

            fields.forEach(function (field) {
                if (!Object.prototype.hasOwnProperty.call(stored, field.name)) {
                    return;
                }

                applyFieldValue(field, stored[field.name]);
                restoredAnyField = true;
            });

            if (restoredAnyField) {
                showDraftStatus('Rascunho restaurado automaticamente.', 'is-restored');
            }
        }

        function persistFormState(form, index) {
            const key = getFormStorageKey(form, index);

            try {
                window.localStorage.setItem(key, JSON.stringify(serializeFormState(form)));
                showDraftStatus('Rascunho salvo automaticamente as ' + new Date().toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) + '.', 'is-saved');
            } catch (error) {
                // Ignore quota and private mode errors.
            }
        }

        function scheduleFormSave(form, index) {
            if (saveTimers.has(form)) {
                clearTimeout(saveTimers.get(form));
            }

            showDraftStatus('Alteracoes pendentes. Salvando rascunho...', 'is-pending');

            saveTimers.set(form, window.setTimeout(function () {
                persistFormState(form, index);
            }, AUTOSAVE_DELAY_MS));
        }

        function clearFormDraft(form, index) {
            if (saveTimers.has(form)) {
                clearTimeout(saveTimers.get(form));
                saveTimers.delete(form);
            }

            window.localStorage.removeItem(getFormStorageKey(form, index));
        }

        function bindFormAutosave(form, index) {
            if (!form || form.dataset.autosaveBound === 'true' || form.dataset.autosave === 'false') {
                return;
            }

            const method = String(form.getAttribute('method') || 'GET').toUpperCase();
            const fields = getEligibleFields(form);

            if (method === 'GET' || !fields.length) {
                return;
            }

            form.dataset.autosaveBound = 'true';

            restoreFormState(form, index);

            form.addEventListener('input', function () {
                scheduleFormSave(form, index);
            });

            form.addEventListener('change', function () {
                scheduleFormSave(form, index);
            });

            form.addEventListener('submit', function () {
                clearFormDraft(form, index);
            });
        }

        window.initializeFormDraftAutosave = function (root) {
            const scope = root || document;
            const forms = Array.from(scope.querySelectorAll('form'));

            forms.forEach(function (form, index) {
                bindFormAutosave(form, index);
            });
        };

        if (window.jQuery) {
            $(document).on('summernote.change', 'textarea.summernote, textarea.summernote-sm', function () {
                const form = this.form;

                if (!form) {
                    return;
                }

                $(this).val($(this).summernote('code'));

                const forms = Array.from(document.querySelectorAll('form'));
                const index = forms.indexOf(form);
                scheduleFormSave(form, index >= 0 ? index : 0);
            });
        }

        function bootAutosave() {
            if (typeof window.initializeFormDraftAutosave === 'function') {
                window.initializeFormDraftAutosave(document);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootAutosave);
        } else {
            bootAutosave();
        }
    })();
</script>
