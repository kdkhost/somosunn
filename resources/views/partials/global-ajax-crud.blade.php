<script>
    (function () {
        if (window.UNNAjaxGlobal) {
            return;
        }

        const config = {
            autoBind: @json($unnAjaxAutoBind ?? true),
            preferPjax: @json($unnAjaxPreferPjax ?? false),
            pjaxContainer: @json($unnAjaxPjaxContainer ?? '#pjax-container'),
        };

        const excludedPatterns = [
            /\/login(?:\/|$)/i,
            /\/register(?:\/|$)/i,
            /\/logout(?:\/|$)/i,
            /\/password(?:\/|$)/i,
            /\/checkout(?:\/|$)/i,
            /\/payment/i,
            /mercadopago/i,
            /pagseguro/i,
            /\/subscription/i,
            /\/export/i,
            /\/download/i,
            /\/oauth/i,
            /\/social\/redirect/i,
            /\/webhook/i,
            /\/install(?:\/|$)/i,
        ];

        function getCsrfToken(form) {
            return form.querySelector('input[name="_token"]')?.value
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || '';
        }

        function sameOrigin(url) {
            try {
                return new URL(url, window.location.href).origin === window.location.origin;
            } catch (error) {
                return false;
            }
        }

        function normalizeUrl(url) {
            try {
                return new URL(url, window.location.href).href;
            } catch (error) {
                return '';
            }
        }

        function getActionUrl(form) {
            return normalizeUrl(form.getAttribute('action') || window.location.href);
        }

        function getFormMethod(form) {
            const override = form.querySelector('input[name="_method"]')?.value;
            return String(override || form.getAttribute('method') || 'GET').toUpperCase();
        }

        function getTransportMethod(form) {
            return String(form.getAttribute('method') || 'POST').toUpperCase();
        }

        function formHasSelectedFiles(form) {
            const fileInputs = Array.from(form.querySelectorAll('input[type="file"]'));

            return fileInputs.some((input) => (input.files && input.files.length > 0))
                || !!form.querySelector('.filepond--item');
        }

        function shouldHandleForm(form) {
            if (!(form instanceof HTMLFormElement)) {
                return false;
            }

            if (form.dataset.noAjax === 'true'
                || form.dataset.nativeSubmit === 'true'
                || form.dataset.uploadSubmitting === 'true'
                || form.classList.contains('no-ajax')
                || form.closest('[data-no-ajax-scope="true"]')) {
                return false;
            }

            if (String(form.getAttribute('method') || 'GET').toUpperCase() === 'GET') {
                return false;
            }

            const target = (form.getAttribute('target') || '').trim();
            if (target !== '' && target.toLowerCase() !== '_self') {
                return false;
            }

            const actionUrl = getActionUrl(form);
            if (!sameOrigin(actionUrl)) {
                return false;
            }

            if (excludedPatterns.some((pattern) => pattern.test(actionUrl))) {
                return false;
            }

            if (form.dataset.uploadProgressBound === 'true' && formHasSelectedFiles(form)) {
                return false;
            }

            return true;
        }

        function showSuccess(message) {
            if (typeof window.showSuccess === 'function') {
                window.showSuccess(message);
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: message || 'Operacao concluida com sucesso.',
                    timer: 2200,
                    showConfirmButton: false,
                });
                return;
            }

            window.alert(message || 'Operacao concluida com sucesso.');
        }

        function showError(message) {
            if (typeof window.showError === 'function') {
                window.showError(message);
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: message || 'Nao foi possivel concluir a operacao.',
                });
                return;
            }

            window.alert(message || 'Nao foi possivel concluir a operacao.');
        }

        function setSubmittingState(form, state) {
            form.dataset.ajaxSubmitting = state ? 'true' : 'false';

            const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            buttons.forEach((button) => {
                button.disabled = !!state;
            });
        }

        function replaceDocument(html, url, replaceHistory = true) {
            if (url && sameOrigin(url)) {
                const historyMethod = replaceHistory ? 'replaceState' : 'pushState';
                window.history[historyMethod]({}, '', url);
            }

            document.open();
            document.write(html);
            document.close();
        }

        async function navigate(url, options = {}) {
            const targetUrl = normalizeUrl(url);
            if (!targetUrl) {
                return;
            }

            if (options.preferPjax
                && typeof window.jQuery !== 'undefined'
                && typeof window.jQuery.pjax === 'function'
                && document.querySelector(config.pjaxContainer)) {
                window.jQuery.pjax({
                    url: targetUrl,
                    container: config.pjaxContainer,
                    timeout: 8000,
                });
                return;
            }

            const response = await fetch(targetUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Cache-Control': 'no-cache',
                },
            });

            const html = await response.text();
            replaceDocument(html, response.url || targetUrl, !!options.replaceHistory);
        }

        function getErrorMessageFromJson(payload, fallback) {
            let message = payload?.message || fallback;

            if (payload && payload.errors && typeof payload.errors === 'object') {
                const firstKey = Object.keys(payload.errors)[0];
                const firstError = firstKey ? payload.errors[firstKey] : null;
                if (Array.isArray(firstError) && firstError[0]) {
                    message = firstError[0];
                }
            }

            return message;
        }

        function removeAffectedNode(form) {
            const selector = form.dataset.ajaxRemoveTarget || form.__unnAjaxSubmitter?.dataset?.ajaxRemoveTarget;
            if (selector) {
                document.querySelectorAll(selector).forEach((node) => node.remove());
                return true;
            }

            const row = form.closest('tr');
            if (row && typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable === 'function') {
                const table = row.closest('table');
                if (table && window.jQuery.fn.DataTable.isDataTable(table)) {
                    window.jQuery(table).DataTable().row(row).remove().draw(false);
                    return true;
                }
            }

            const candidate = form.closest('[data-ajax-item], tr, .list-group-item, .card');
            if (candidate) {
                candidate.remove();
                return true;
            }

            return false;
        }

        async function handleJsonResponse(form, payload, options = {}) {
            const effectiveMethod = getFormMethod(form);

            if (payload?.redirect) {
                if (payload?.message) {
                    showSuccess(payload.message);
                }
                await navigate(payload.redirect, { preferPjax: !!options.preferPjax, replaceHistory: true });
                return;
            }

            if (effectiveMethod === 'DELETE') {
                const removed = removeAffectedNode(form);
                showSuccess(payload?.message || 'Item removido com sucesso.');
                if (!removed && payload?.reload !== false) {
                    await navigate(window.location.href, { preferPjax: !!options.preferPjax, replaceHistory: true });
                }
                return;
            }

            showSuccess(payload?.message || options.successMessage || 'Operacao concluida com sucesso.');

            if (payload?.reload === true || form.dataset.ajaxReload === 'true') {
                await navigate(window.location.href, { preferPjax: !!options.preferPjax, replaceHistory: true });
            }
        }

        async function submitForm(form, options = {}) {
            const actionUrl = getActionUrl(form);
            const transportMethod = getTransportMethod(form);
            const headers = {
                'Accept': options.forceAjaxHeaders
                    ? 'application/json, text/html;q=0.9, */*;q=0.8'
                    : 'text/html, application/xhtml+xml, application/json;q=0.9, */*;q=0.8',
            };
            const token = getCsrfToken(form);

            if (token) {
                headers['X-CSRF-TOKEN'] = token;
            }

            if (options.forceAjaxHeaders) {
                headers['X-Requested-With'] = 'XMLHttpRequest';
            }

            const formData = new FormData(form);
            const submitter = options.submitter || form.__unnAjaxSubmitter || null;
            if (submitter && submitter.name && !formData.has(submitter.name)) {
                formData.append(submitter.name, submitter.value || '1');
            }

            setSubmittingState(form, true);

            try {
                const response = await fetch(actionUrl, {
                    method: transportMethod,
                    body: transportMethod === 'GET' ? null : formData,
                    credentials: 'same-origin',
                    headers,
                    redirect: 'follow',
                });

                const contentType = response.headers.get('Content-Type') || '';

                if (contentType.includes('application/json')) {
                    const payload = await response.json();

                    if (!response.ok) {
                        showError(getErrorMessageFromJson(payload, 'Nao foi possivel concluir a operacao.'));
                        return;
                    }

                    await handleJsonResponse(form, payload, options);
                    return;
                }

                const html = await response.text();

                if (!response.ok && !html) {
                    showError('Nao foi possivel concluir a operacao.');
                    return;
                }

                replaceDocument(html, response.url || actionUrl, true);
            } catch (error) {
                showError('Falha de rede ao enviar a operacao.');
            } finally {
                setSubmittingState(form, false);
                delete form.__unnAjaxSubmitter;
            }
        }

        document.addEventListener('click', function (event) {
            const submitter = event.target.closest('button[type="submit"], input[type="submit"]');
            if (submitter && submitter.form) {
                submitter.form.__unnAjaxSubmitter = submitter;
            }
        }, true);

        if (config.autoBind) {
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (event.defaultPrevented || !shouldHandleForm(form) || form.dataset.ajaxSubmitting === 'true') {
                    return;
                }

                event.preventDefault();
                submitForm(form, {
                    submitter: event.submitter || form.__unnAjaxSubmitter || null,
                    preferPjax: !!config.preferPjax,
                });
            });
        }

        window.UNNAjaxGlobal = {
            config,
            handleJsonResponse,
            navigate,
            replaceDocument,
            shouldHandleForm,
            submitForm,
            showSuccess,
            showError,
            formHasSelectedFiles,
        };
    })();
</script>
