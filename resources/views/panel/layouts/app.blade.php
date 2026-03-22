@extends('layouts.app')

@prepend('styles')
    <link href="{{ asset('vendor/filepond/filepond.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-image-preview.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-size.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-type.css') }}?v=3" rel="stylesheet">

    <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">

    <style>
        .note-editor.note-frame {
            border-radius: 1.25rem !important;
            border-color: #e2e8f0 !important;
            overflow: hidden;
        }

        .note-toolbar {
            background-color: #f8fafc !important;
            border-bottom-color: #e2e8f0 !important;
            padding: 0.75rem !important;
        }

        .dark .note-editor.note-frame {
            border-color: #1e293b !important;
            background-color: #020617 !important;
        }

        .dark .note-toolbar {
            background-color: #0f172a !important;
            border-bottom-color: #1e293b !important;
        }

        .dark .note-btn {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark .note-editable {
            background-color: #020617 !important;
            color: #f8fafc !important;
        }

        .dark .note-statusbar {
            background-color: #0f172a !important;
            border-top-color: #1e293b !important;
        }

        .dark .note-dropdown-menu {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
        }

        .dark .note-dropdown-item {
            color: #f8fafc !important;
        }

        .dark .note-dropdown-item:hover {
            background-color: #1e293b !important;
        }

        .dark .panel-theme-shell input[class*='dark:bg-slate-']:not([type='checkbox']):not([type='radio']):not([type='range']):not([type='color']):not([type='file']):not([class*='dark:text-']),
        .dark .panel-theme-shell textarea[class*='dark:bg-slate-']:not([class*='dark:text-']),
        .dark .panel-theme-shell select[class*='dark:bg-slate-']:not([class*='dark:text-']) {
            color: #f8fafc;
        }

        .dark .panel-theme-shell input[class*='dark:bg-slate-']:not([type='checkbox']):not([type='radio']):not([type='range']):not([type='color']):not([type='file'])::placeholder,
        .dark .panel-theme-shell textarea[class*='dark:bg-slate-']::placeholder {
            color: #64748b;
            opacity: 1;
        }

        .dark .panel-theme-shell button[class*='dark:bg-slate-']:not([class*='dark:text-']) {
            color: #e2e8f0;
        }

        .filepond--credits {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .panel-theme-shell .filepond--root {
            margin-bottom: 0;
        }

        .panel-theme-shell .filepond--panel-root {
            border-radius: 1.5rem !important;
            border: 1px dashed #cbd5e1 !important;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.95), rgba(241, 245, 249, 0.95)) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .panel-theme-shell .filepond--drop-label {
            color: #475569;
        }

        .panel-theme-shell .filepond--label-action {
            color: #2563eb;
            text-decoration-color: rgba(37, 99, 235, 0.35);
        }

        .panel-theme-shell .filepond--item-panel {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .panel-theme-shell .filepond--file-action-button {
            cursor: pointer;
        }

        .dark .panel-theme-shell .filepond--panel-root {
            border-color: #334155 !important;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.96)) !important;
            box-shadow: inset 0 1px 0 rgba(51, 65, 85, 0.55);
        }

        .dark .panel-theme-shell .filepond--drop-label {
            color: #cbd5e1;
        }

        .dark .panel-theme-shell .filepond--label-action {
            color: #60a5fa;
            text-decoration-color: rgba(96, 165, 250, 0.45);
        }

        .dark .panel-theme-shell .filepond--drip-blob {
            background-color: rgba(96, 165, 250, 0.85);
        }

        .panel-upload-progress {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 70;
            width: min(26rem, calc(100vw - 2rem));
            display: none;
        }

        .panel-upload-progress.is-visible {
            display: block;
        }

        .panel-upload-progress__card {
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(15, 23, 42, 0.94);
            color: #e2e8f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(20px);
            padding: 1rem 1.1rem;
        }

        .panel-upload-progress__bar {
            width: 100%;
            height: 0.6rem;
            border-radius: 9999px;
            background: rgba(148, 163, 184, 0.18);
            overflow: hidden;
        }

        .panel-upload-progress__bar>span {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, #38bdf8, #2563eb);
            transition: width 0.18s ease;
        }

        /* Estilos Premium para Upload Widgets (Consistência com Admin) */
        .premium-upload-box {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .drop-zone-area {
            border: 2px dashed #e2e8f0;
            background: #f8fafc;
            border-radius: 2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
            position: relative;
        }

        .dark .drop-zone-area {
            border-color: #334155;
            background: #0f172a;
        }

        .drop-zone-area:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }

        .premium-upload-box.dragover .drop-zone-area {
            border-color: #22c55e;
            background: rgba(34, 197, 94, 0.05);
            transform: scale(1.01);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
@endprepend

@section('content')
    <div
        class="panel-theme-shell bg-slate-50 dark:bg-slate-950 min-h-screen pt-24 md:pt-0 pb-10 transition-colors duration-300">
        <div class="w-full px-4 md:px-6 xl:px-8 2xl:px-10">
            <nav class="h-24 flex items-center justify-between gap-4" aria-label="breadcrumb">
                <ol
                    class="list-none p-0 inline-flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium">
                    <li class="flex items-center">
                        <a href="{{ route('panel.dashboard') }}"
                            class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 shadow-sm">
                                <i class="fas fa-home text-xs"></i>
                            </div>
                            <span>Painel</span>
                        </a>
                    </li>
                    @hasSection('panel_breadcrumb')
                        <li class="flex items-center text-slate-300 dark:text-slate-700">
                            <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                        </li>
                        <li class="flex items-center">
                            @yield('panel_breadcrumb')
                        </li>
                    @endif
                    <li class="flex items-center text-slate-300 dark:text-slate-700">
                        <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                    </li>
                    <li class="flex items-center text-blue-600 dark:text-blue-400">
                        <span
                            class="font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-full text-xs">
                            @yield('title', ucfirst(Str::after(Route::currentRouteName(), 'panel.')))
                        </span>
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col md:flex-row gap-6 xl:gap-8 items-start">
                <aside class="hidden md:block w-full md:w-80 shrink-0">
                    @include('panel.partials.sidebar')
                </aside>
                <div class="flex-1 min-w-0 w-full">
                    @yield('panel_content')
                </div>
            </div>
        </div>
    </div>

    @include('panel.partials.quick-upload-modal')

    @prepend('scripts')
        <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-image-preview.js') }}?v=3"></script>
        <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-size.js') }}?v=3"></script>
        <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-type.js') }}?v=3"></script>
        <script src="{{ asset('vendor/filepond/filepond.js') }}?v=3"></script>

        <script src="https://unpkg.com/@yaireo/tagify"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

        <script>
            (function () {
                let filePondPluginsRegistered = false;

                function formatBytes(bytes) {
                    if (!bytes || bytes <= 0) {
                        return '0 B';
                    }

                    const units = ['B', 'KB', 'MB', 'GB'];
                    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                    const value = bytes / Math.pow(1024, exponent);

                    return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
                }

                function formatRemainingTime(seconds) {
                    if (!Number.isFinite(seconds) || seconds <= 0) {
                        return 'calculando tempo restante...';
                    }

                    const rounded = Math.round(seconds);

                    if (rounded < 60) {
                        return `${rounded}s restantes`;
                    }

                    const minutes = Math.floor(rounded / 60);
                    const remainingSeconds = rounded % 60;

                    if (minutes < 60) {
                        return `${minutes}min ${remainingSeconds}s restantes`;
                    }

                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;

                    return `${hours}h ${remainingMinutes}min restantes`;
                }

                function normalizeUrl(url) {
                    try {
                        return new URL(url, window.location.href).href.replace(/\/$/, '');
                    } catch (error) {
                        return url;
                    }
                }

                function ensureUploadProgressCard() {
                    let card = document.getElementById('panel-upload-progress');

                    if (card) {
                        return card;
                    }

                    card = document.createElement('div');
                    card.id = 'panel-upload-progress';
                    card.className = 'panel-upload-progress';
                    card.innerHTML = `
                                                                <div class="panel-upload-progress__card">
                                                                    <div class="flex items-start justify-between gap-4">
                                                                        <div>
                                                                            <p class="text-xs font-black uppercase tracking-[0.25em] text-sky-300">Upload</p>
                                                                            <h3 class="mt-1 text-base font-bold text-white">Enviando arquivos</h3>
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <div class="text-sm font-black text-white" data-upload-percent>0%</div>
                                                                            <div class="text-[11px] text-slate-300" data-upload-size>0 B / 0 B</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-4 panel-upload-progress__bar">
                                                                        <span data-upload-fill></span>
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between gap-3 text-[11px] text-slate-300">
                                                                        <span data-upload-status>Preparando envio...</span>
                                                                        <span data-upload-remaining>calculando tempo restante...</span>
                                                                    </div>
                                                                </div>
                                                            `;

                    document.body.appendChild(card);
                    return card;
                }

                function updateUploadProgressCard(card, state) {
                    const percent = card.querySelector('[data-upload-percent]');
                    const size = card.querySelector('[data-upload-size]');
                    const fill = card.querySelector('[data-upload-fill]');
                    const status = card.querySelector('[data-upload-status]');
                    const remaining = card.querySelector('[data-upload-remaining]');

                    if (typeof state.percent === 'number') {
                        percent.textContent = `${Math.max(0, Math.min(100, Math.round(state.percent)))}%`;
                        fill.style.width = `${Math.max(0, Math.min(100, state.percent))}%`;
                    }

                    if (state.loaded !== undefined && state.total !== undefined) {
                        size.textContent = `${formatBytes(state.loaded)} / ${formatBytes(state.total)}`;
                    }

                    if (state.status) {
                        status.textContent = state.status;
                    }

                    if (state.remaining) {
                        remaining.textContent = state.remaining;
                    }
                }

                function setUploadCardVisible(isVisible) {
                    const card = ensureUploadProgressCard();
                    card.classList.toggle('is-visible', !!isVisible);
                }

                function registerFilePondPlugins() {
                    if (filePondPluginsRegistered || !window.FilePond) {
                        return;
                    }

                    FilePond.registerPlugin(
                        FilePondPluginImagePreview,
                        FilePondPluginFileValidateSize,
                        FilePondPluginFileValidateType
                    );

                    filePondPluginsRegistered = true;
                }

                function isFileInputVisible(input) {
                    if (!input || input.disabled || input.dataset.filepondIgnore === 'true') {
                        return false;
                    }

                    if (input.classList.contains('hidden') || input.hidden) {
                        return false;
                    }

                    const computed = window.getComputedStyle(input);
                    if (computed.display === 'none' || computed.visibility === 'hidden') {
                        return false;
                    }

                    return input.offsetParent !== null || input.classList.contains('filepond');
                }

                function createFilePondForInput(input) {
                    if (!window.FilePond || input.dataset.filepondEnhanced === 'true' || !isFileInputVisible(input)) {
                        return;
                    }

                    const accept = (input.getAttribute('accept') || '')
                        .split(',')
                        .map(value => value.trim())
                        .filter(Boolean);

                    const maxFiles = Number.parseInt(input.dataset.maxFiles || (input.multiple ? '5' : '1'), 10);
                    const isImageUpload = accept.some(type => type.startsWith('image/'));

                    FilePond.create(input, {
                        credits: false,
                        storeAsFile: true,
                        allowDrop: true,
                        dropValidation: true,
                        allowMultiple: input.multiple || maxFiles > 1,
                        maxFiles: Number.isFinite(maxFiles) ? maxFiles : 1,
                        acceptedFileTypes: accept.length ? accept : null,
                        allowImagePreview: isImageUpload,
                        allowBrowse: true,
                        labelIdle: '<span class="text-sm font-semibold">Arraste e solte seus arquivos ou <span class="filepond--label-action">clique para selecionar</span></span>',
                        labelFileProcessing: 'Enviando...',
                        labelFileProcessingComplete: 'Arquivo pronto para envio',
                        labelTapToCancel: 'cancelar',
                        labelTapToRetry: 'tentar novamente',
                        labelTapToUndo: 'desfazer',
                        labelInvalidField: 'Arquivo inválido',
                        labelFileWaitingForSize: 'Calculando tamanho',
                        labelFileSizeNotAvailable: 'Tamanho indisponível',
                        labelFileLoading: 'Carregando',
                        labelFileLoadError: 'Erro ao carregar',
                        imagePreviewHeight: isImageUpload ? 180 : null,
                        stylePanelLayout: isImageUpload ? 'integrated' : 'compact',
                        styleLoadIndicatorPosition: 'center bottom',
                        styleProgressIndicatorPosition: 'right bottom',
                        styleButtonRemoveItemPosition: 'left bottom',
                        styleButtonProcessItemPosition: 'right bottom',
                    });

                    input.dataset.filepondEnhanced = 'true';
                }

                function formHasSelectedFiles(form) {
                    const fileInputs = Array.from(form.querySelectorAll('input[type="file"]'));

                    return fileInputs.some(input => (input.files && input.files.length > 0))
                        || !!form.querySelector('.filepond--item');
                }

                function attachUploadProgressToForms(root = document) {
                    root.querySelectorAll('form').forEach(form => {
                        if (form.dataset.uploadProgressBound === 'true' || form.dataset.panelUploadProgress === 'false') {
                            return;
                        }

                        if (!form.querySelector('input[type="file"]')) {
                            return;
                        }

                        form.dataset.uploadProgressBound = 'true';

                        form.addEventListener('submit', function (event) {
                            if (form.dataset.uploadSubmitting === 'true' || !formHasSelectedFiles(form)) {
                                return;
                            }

                            event.preventDefault();

                            form.dataset.uploadSubmitting = 'true';

                            const progressCard = ensureUploadProgressCard();
                            const startedAt = Date.now();
                            updateUploadProgressCard(progressCard, {
                                percent: 0,
                                loaded: 0,
                                total: 0,
                                status: 'Preparando envio...',
                                remaining: 'calculando tempo restante...'
                            });
                            setUploadCardVisible(true);

                            const formData = new FormData(form);
                            const submitter = event.submitter;

                            if (submitter && submitter.name && !formData.has(submitter.name)) {
                                formData.append(submitter.name, submitter.value || '1');
                            }

                            const xhr = new XMLHttpRequest();
                            xhr.open((form.getAttribute('method') || 'POST').toUpperCase(), form.getAttribute('action') || window.location.href, true);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                            xhr.upload.addEventListener('progress', function (uploadEvent) {
                                if (!uploadEvent.lengthComputable) {
                                    return;
                                }

                                const percent = (uploadEvent.loaded / uploadEvent.total) * 100;
                                const elapsedSeconds = Math.max((Date.now() - startedAt) / 1000, 0.2);
                                const speed = uploadEvent.loaded / elapsedSeconds;
                                const remainingSeconds = speed > 0
                                    ? (uploadEvent.total - uploadEvent.loaded) / speed
                                    : 0;

                                updateUploadProgressCard(progressCard, {
                                    percent: percent,
                                    loaded: uploadEvent.loaded,
                                    total: uploadEvent.total,
                                    status: 'Enviando arquivos do formulário...',
                                    remaining: formatRemainingTime(remainingSeconds)
                                });
                            });

                            xhr.addEventListener('load', function () {
                                form.dataset.uploadSubmitting = 'false';

                                const contentType = xhr.getResponseHeader('Content-Type') || '';
                                const currentUrl = normalizeUrl(window.location.href);
                                const responseUrl = normalizeUrl(xhr.responseURL || form.action || window.location.href);

                                if (xhr.status >= 200 && xhr.status < 400) {
                                    updateUploadProgressCard(progressCard, {
                                        percent: 100,
                                        status: 'Finalizando resposta do servidor...',
                                        remaining: 'quase pronto'
                                    });

                                    if (contentType.includes('application/json') && xhr.responseText && window.UNNAjaxGlobal) {
                                        try {
                                            const json = JSON.parse(xhr.responseText);
                                            setUploadCardVisible(false);
                                            if (window.UNNAjaxGlobal.handleJsonResponse) {
                                                window.UNNAjaxGlobal.handleJsonResponse(form, json, { preferPjax: false });
                                                return;
                                            }
                                        } catch (e) { }
                                    }

                                    if (responseUrl && responseUrl !== currentUrl) {
                                        if (window.UNNAjaxGlobal) {
                                            window.UNNAjaxGlobal.navigate(responseUrl, { preferPjax: false, replaceHistory: true });
                                        } else {
                                            window.location.assign(responseUrl);
                                        }
                                        return;
                                    }

                                    if (contentType.includes('text/html') && xhr.responseText) {
                                        if (window.UNNAjaxGlobal) {
                                            window.UNNAjaxGlobal.replaceDocument(xhr.responseText, responseUrl || currentUrl, true);
                                        } else {
                                            document.open();
                                            document.write(xhr.responseText);
                                            document.close();
                                        }
                                        return;
                                    }

                                    if (window.UNNAjaxGlobal) {
                                        window.UNNAjaxGlobal.navigate(window.location.href, { preferPjax: false, replaceHistory: true });
                                    } else {
                                        window.location.reload();
                                    }
                                    return;
                                }

                                // Handle Validation or Server errors (422, 500, etc)
                                setUploadCardVisible(false); // Hide the progress immediately

                                let errorMessage = 'Falha ao enviar arquivo. Verifique o tamanho ou o formato e tente novamente.';
                                if (contentType.includes('application/json')) {
                                    try {
                                        const json = JSON.parse(xhr.responseText);
                                        errorMessage = json.message || errorMessage;
                                        // If there are specific field errors, format them
                                        if (json.errors) {
                                            const details = Object.values(json.errors).flat().join('<br>');
                                            errorMessage += '<br><br><span class="text-sm rounded">' + details + '</span>';
                                        }
                                    } catch (e) { }
                                }

                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Upload Recusado',
                                        html: errorMessage,
                                        confirmButtonText: 'Entendi'
                                    });
                                } else {
                                    alert('Erro no Upload: ' + errorMessage.replace(/<br>/g, '\n').replace(/<[^>]+>/g, ''));
                                }
                            });

                            xhr.addEventListener('error', function () {
                                form.dataset.uploadSubmitting = 'false';
                                setUploadCardVisible(false);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erro de Conexão',
                                        text: 'O upload falhou devido a um problema de rede.',
                                        confirmButtonText: 'Tentar novamente'
                                    });
                                }
                            });

                            xhr.addEventListener('abort', function () {
                                form.dataset.uploadSubmitting = 'false';
                                setUploadCardVisible(false);
                            });

                            xhr.send(formData);
                        });
                    });
                }

                window.initializePanelFileUploads = function (root = document) {
                    registerFilePondPlugins();

                    root.querySelectorAll('input[type="file"]').forEach(createFilePondForInput);
                    attachUploadProgressToForms(root);
                };

                // Funcao global injetada pós-Navegação SPA
                window.initPanelScripts = function () {
                    window.initializePanelFileUploads(document);

                    // Notificações Globais SweetAlert2 (Laravel Flash Messages)
                    @if(session('success'))
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: "{{ session('success') }}",
                            confirmButtonColor: '#3b82f6',
                            timer: 4000,
                            timerProgressBar: true,
                            popup: 'rounded-[32px]'
                        });
                    @endif

                    @if(session('error'))
                        Swal.fire({
                            icon: 'error',
                            title: 'Ops!',
                            text: "{{ session('error') }}",
                            confirmButtonColor: '#ef4444',
                            popup: 'rounded-[32px]'
                        });
                    @endif

                    @if(session('info'))
                        Swal.fire({
                            icon: 'info',
                            title: 'Informação',
                            text: "{{ session('info') }}",
                            confirmButtonColor: '#3b82f6',
                            popup: 'rounded-[32px]'
                        });
                    @endif
                                });
                };

                // Executar inicialização normal
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', window.initPanelScripts);
                } else {
                    window.initPanelScripts();
                }

                // --------- INICIO DO SPA ROUTER (VANILLA JS) ---------
                if (!window.UNNPanelSPA) {
                    window.UNNPanelSPA = true;
                    
                    document.addEventListener('click', function(e) {
                        const link = e.target.closest('a');
                        if (!link) return;

                        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                        const url = new URL(link.href);
                        if (url.origin !== window.location.origin) return;

                        // Ignorar links marcados ou rotas de auth/externas
                        if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noAjax === 'true') return;
                        if (url.pathname.match(/\/(logout|login|register|adminlte|export|download)/i)) return;
                        if (url.pathname.startsWith('/admin') && !url.pathname.startsWith('/painel')) return; // ignorar painel legado
                        
                        // Ignorar links vazios ou apenas hash (ancoras) na mesma pagina
                        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
                        if (link.getAttribute('href') === '#') return;

                        e.preventDefault();

                        // Efeito visual de loading
                        const shell = document.querySelector('.panel-theme-shell');
                        if(shell) shell.style.opacity = '0.4';

                        fetch(url.href, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(r => {
                            if(r.redirected) url.href = r.url;
                            if(!r.ok) throw new Error('Erro na rede');
                            return r.text();
                        }).then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            if(doc.title) document.title = doc.title;

                            // Fallback Graceful: Se a nova página exige scripts/CSS inéditos (ex: Summernote, Leaflet), faremos um hard-reload 
                            // para garantir que dependencias async carreguem na ordem correta, evitando quebras de UI.
                            let requiresHardReload = false;
                            
                            // Check Scripts (src)
                            Array.from(doc.querySelectorAll('script[src]')).forEach(script => {
                                if(!document.querySelector(`script[src="${script.getAttribute('src')}"]`)) {
                                    requiresHardReload = true;
                                }
                            });

                            // Check CSS Links
                            Array.from(doc.querySelectorAll('link[rel="stylesheet"]')).forEach(link => {
                                if(!document.querySelector(`link[href="${link.getAttribute('href')}"]`)) {
                                    requiresHardReload = true;
                                }
                            });

                            if(requiresHardReload || !(newShell && oldShell)) {
                                window.location.href = url.href;
                                return;
                            }

                            // Apenas HTML Atualização Leve
                            oldShell.innerHTML = newShell.innerHTML;
                            oldShell.className = newShell.className; 
                            oldShell.style.opacity = '1';

                            // Capturar e executar apenas scripts inlines (já que scripts src foram isolados acima)
                            const newScripts = Array.from(doc.body.querySelectorAll('script:not([src])'));
                            newScripts.forEach(oldScript => {
                                if(oldScript.innerHTML.trim() === '') return;
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                document.body.appendChild(newScript);
                                newScript.parentNode.removeChild(newScript);
                            });

                            window.initPanelScripts();
                            document.dispatchEvent(new CustomEvent('alpine:init'));

                            window.history.pushState({}, '', url.href);
                            window.scrollTo({ top: 0, behavior: 'instant' });
                        }).catch(err => {
                            console.error('SPA Nav erro:', err);
                            window.location.href = url.href; 
                        });
                    });

                    window.addEventListener('popstate', function() {
                        window.location.reload();
                    });
                }
                // --------- FIM DO SPA ROUTER ---------

            })();
        </script>
        @include('partials.form-draft-autosave')
    @endprepend
@endsection
