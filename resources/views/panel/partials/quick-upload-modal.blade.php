@php
    $panelQuickUploadPerFileLimitBytes = \App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024)
        ?? (20 * 1024 * 1024);
    $panelQuickUploadPerFileLimitMb = number_format($panelQuickUploadPerFileLimitBytes / 1024 / 1024, 2, '.', '');
    $panelQuickUploadEvents = \App\Models\Event::query()
        ->when(
            auth()->check() && !auth()->user()->isAdmin(),
            fn ($query) => $query->where('user_id', auth()->id())
        )
        ->select(['id', 'title', 'start_at'])
        ->orderBy('start_at', 'desc')
        ->limit(250)
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') : '',
            ];
        })
        ->values();
@endphp

<div id="panelQuickUploadModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
    hidden
    aria-hidden="true"
    role="dialog" aria-modal="true" aria-labelledby="panelQuickUploadTitle">

    <div id="panelQuickUploadOverlay"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onclick="window.closePanelQuickUpload()"></div>

    <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <div>
                    <h3 id="panelQuickUploadTitle" class="text-lg font-extrabold leading-none text-slate-900 dark:text-white">Registro rapido de midias</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Selecione um evento e publique fotos ou videos em lote.</p>
                </div>
            </div>

            <button type="button" onclick="window.closePanelQuickUpload()"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="space-y-5 p-6">
            <div id="panelQuickStep1">
                <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">
                    1. Selecione o evento
                </label>

                <div id="panelQuickSearchWrap" class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="panelQuickSearch"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        placeholder="Digite o nome do evento para buscar...">
                </div>

                <div id="panelQuickResults"
                    hidden
                    class="mt-2 max-h-[220px] overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900"></div>

                <div id="panelQuickSelected"
                    hidden
                    class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center gap-2 text-sm font-semibold text-blue-700 dark:text-blue-300">
                        <i class="fas fa-calendar-check"></i>
                        <span id="panelQuickSelectedName">-</span>
                    </div>
                    <button type="button" onclick="window.clearPanelQuickSelection()"
                        class="text-xs font-bold text-blue-500 underline underline-offset-2 transition hover:text-blue-700">
                        Trocar evento
                    </button>
                </div>
            </div>

            <div id="panelQuickStep2" hidden>
                <div class="mb-2 flex flex-col justify-between gap-2 md:flex-row md:items-center">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                        2. Selecione e envie arquivos
                    </label>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        O envio rapido publica o lote um arquivo por vez para reduzir recusas do servidor.
                    </p>
                </div>

                <input type="file" id="panelQuickFileInput" multiple accept="image/*,video/*" hidden>

                <div id="panelQuickDropZone"
                    class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-8 text-center transition dark:border-slate-700 dark:bg-slate-950">
                    <i class="fas fa-cloud-upload-alt mb-3 block text-3xl text-blue-500"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                        Arraste fotos e videos ou <span class="text-blue-600 underline underline-offset-2">clique para selecionar</span>
                    </p>
                    <p class="mt-2 text-xs text-slate-400">
                        Imagens e videos com ate {{ $panelQuickUploadPerFileLimitMb }} MB por arquivo no fluxo rapido.
                    </p>
                </div>

                <div id="panelQuickSelectedFiles"
                    class="mt-4 hidden rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Arquivos prontos</p>
                            <p id="panelQuickSelectedSummary" class="mt-2 text-sm font-bold text-slate-800 dark:text-slate-100">0 arquivo(s)</p>
                        </div>
                        <span id="panelQuickSelectedSize"
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:bg-slate-800 dark:text-slate-300">0 B</span>
                    </div>
                    <div id="panelQuickSelectedList" class="mt-4 grid gap-2"></div>
                </div>

                <div id="panelQuickProgress" class="mt-4" hidden>
                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" id="panelQuickStatus">Aguardando envio...</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200" id="panelQuickPercent">0%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div id="panelQuickProgressBar" class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-300" style="width:0%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                        <span id="panelQuickDetails">0 / 0 arquivos enviados</span>
                        <span id="panelQuickRemaining">pronto para iniciar</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 pb-6 pt-4 dark:border-slate-800">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" onclick="window.closePanelQuickUpload()"
                    class="w-full rounded-xl bg-slate-100 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 sm:w-auto sm:px-6">
                    Fechar
                </button>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="button" id="panelQuickAddFiles"
                        class="hidden rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-blue-900/40 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-blue-900/20">
                        <i class="fas fa-plus mr-2"></i> Adicionar arquivos
                    </button>
                    <button type="button" id="panelQuickSubmit" disabled
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black uppercase tracking-[0.16em] text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-white dark:disabled:bg-slate-700">
                        <i class="fas fa-paper-plane mr-2"></i> Publicar na galeria
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let selectedEventId = null;
    let searchTimeout = null;
    let uploadQueue = [];
    let isUploading = false;

    const modal = document.getElementById('panelQuickUploadModal');
    const searchWrap = document.getElementById('panelQuickSearchWrap');
    const searchInput = document.getElementById('panelQuickSearch');
    const results = document.getElementById('panelQuickResults');
    const selected = document.getElementById('panelQuickSelected');
    const selectedName = document.getElementById('panelQuickSelectedName');
    const step2 = document.getElementById('panelQuickStep2');
    const fileInput = document.getElementById('panelQuickFileInput');
    const dropZone = document.getElementById('panelQuickDropZone');
    const addFilesButton = document.getElementById('panelQuickAddFiles');
    const submitButton = document.getElementById('panelQuickSubmit');
    const progress = document.getElementById('panelQuickProgress');
    const progressBar = document.getElementById('panelQuickProgressBar');
    const progressPercent = document.getElementById('panelQuickPercent');
    const progressStatus = document.getElementById('panelQuickStatus');
    const progressDetails = document.getElementById('panelQuickDetails');
    const progressRemaining = document.getElementById('panelQuickRemaining');
    const selectedWrap = document.getElementById('panelQuickSelectedFiles');
    const selectedSummary = document.getElementById('panelQuickSelectedSummary');
    const selectedSize = document.getElementById('panelQuickSelectedSize');
    const selectedList = document.getElementById('panelQuickSelectedList');
    const perFileLimitBytes = Math.max(1, parseInt(@json($panelQuickUploadPerFileLimitBytes), 10) || @json($panelQuickUploadPerFileLimitBytes));
    const availableEvents = @json($panelQuickUploadEvents);

    if (!modal || !searchWrap || !searchInput || !results || !selected || !selectedName || !step2 || !fileInput || !dropZone || !addFilesButton || !submitButton || !progress || !progressBar || !progressPercent || !progressStatus || !progressDetails || !progressRemaining || !selectedWrap || !selectedSummary || !selectedSize || !selectedList) {
        return;
    }

    function setModalVisibility(isOpen) {
        modal.hidden = !isOpen;
        modal.classList.toggle('hidden', !isOpen);
        modal.classList.toggle('flex', isOpen);
        modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    function formatBytes(bytes) {
        const value = Number(bytes || 0);
        if (!Number.isFinite(value) || value <= 0) {
            return '0 B';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        const exponent = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
        const amount = value / Math.pow(1024, exponent);

        return `${amount.toFixed(amount >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fileKind(file) {
        const type = String(file.type || '').toLowerCase();
        const name = String(file.name || '').toLowerCase();

        if (type.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg)$/.test(name)) {
            return 'image';
        }

        if (type.startsWith('video/') || /\.(mp4|mov|m4v|webm|mkv)$/.test(name)) {
            return 'video';
        }

        return 'file';
    }

    function updateActionState() {
        const hasEvent = Boolean(selectedEventId);
        const hasFiles = uploadQueue.length > 0;

        submitButton.disabled = !hasEvent || !hasFiles || isUploading;
        submitButton.innerHTML = isUploading
            ? '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...'
            : '<i class="fas fa-paper-plane mr-2"></i> Publicar na galeria';

        addFilesButton.classList.toggle('hidden', !hasEvent);
        addFilesButton.disabled = !hasEvent || isUploading;

        dropZone.classList.toggle('pointer-events-none', isUploading);
        dropZone.classList.toggle('opacity-50', isUploading);
    }

    function resetProgress() {
        progress.hidden = true;
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressStatus.textContent = 'Aguardando envio...';
        progressDetails.textContent = '0 / 0 arquivos enviados';
        progressRemaining.textContent = 'pronto para iniciar';
    }

    function renderSelectedFiles() {
        if (uploadQueue.length === 0) {
            selectedWrap.classList.add('hidden');
            selectedSummary.textContent = '0 arquivo(s)';
            selectedSize.textContent = '0 B';
            selectedList.innerHTML = '';
            updateActionState();
            return;
        }

        const totalBytes = uploadQueue.reduce((sum, file) => sum + Number(file.size || 0), 0);
        selectedWrap.classList.remove('hidden');
        selectedSummary.textContent = `${uploadQueue.length} arquivo(s) selecionado(s)`;
        selectedSize.textContent = formatBytes(totalBytes);
        selectedList.innerHTML = uploadQueue.map((file, index) => {
            const kind = fileKind(file);
            const badgeLabel = kind === 'video' ? 'video' : (kind === 'image' ? 'imagem' : 'arquivo');
            const badgeClass = kind === 'video'
                ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300'
                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300';

            return `
                <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-950">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] ${badgeClass}">${badgeLabel}</span>
                            <span class="truncate font-bold text-slate-700 dark:text-slate-200">${escapeHtml(file.name)}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">${formatBytes(file.size || 0)}</p>
                    </div>
                    <button type="button" class="panel-quick-remove-file rounded-full p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20" data-index="${index}" ${isUploading ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }).join('');

        updateActionState();
    }

    function showUploadMessage(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon,
                title,
                text,
                confirmButtonColor: '#1F5EDB'
            });
        }

        alert(`${title}\n\n${text}`);
        return Promise.resolve();
    }

    function resetQuickUploadState() {
        selectedEventId = null;
        uploadQueue = [];
        isUploading = false;
        searchInput.value = '';
        searchInput.disabled = false;
        searchWrap.hidden = false;
        results.hidden = true;
        results.innerHTML = '';
        selected.hidden = true;
        step2.hidden = true;
        fileInput.value = '';
        resetProgress();
        renderSelectedFiles();
    }

    window.openQuickUploadModal = function () {
        resetQuickUploadState();
        setModalVisibility(true);
        searchInput.focus();
    };

    window.closePanelQuickUpload = function () {
        resetQuickUploadState();
        setModalVisibility(false);
    };

    window.clearPanelQuickSelection = function () {
        resetQuickUploadState();
        searchInput.focus();
    };

    window.addEventListener('pageshow', function () {
        window.closePanelQuickUpload();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            window.closePanelQuickUpload();
        }
    });

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        if (query.length < 2) {
            results.hidden = true;
            results.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(function () {
            const loweredQuery = query.toLowerCase();
            const events = availableEvents.filter(function (eventItem) {
                return String(eventItem.title || '').toLowerCase().indexOf(loweredQuery) !== -1;
            }).slice(0, 30);

            results.innerHTML = '';

            if (events.length === 0) {
                results.innerHTML = '<p class="px-4 py-3 text-sm text-slate-400">Nenhum evento encontrado</p>';
                results.hidden = false;
                return;
            }

            events.forEach(function (eventItem) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'w-full border-b border-slate-100 px-4 py-3 text-left text-sm text-slate-800 transition hover:bg-blue-50 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-blue-900/20';
                button.innerHTML = '<span class="font-bold">' + escapeHtml(eventItem.title) + '</span> <span class="ml-2 text-xs text-slate-400">' + escapeHtml(eventItem.start || '') + '</span>';
                button.addEventListener('click', function () {
                    selectedEventId = eventItem.id;
                    selectedName.textContent = eventItem.title;
                    searchWrap.hidden = true;
                    results.hidden = true;
                    selected.hidden = false;
                    step2.hidden = false;
                    renderSelectedFiles();
                });
                results.appendChild(button);
            });

            results.hidden = false;
        }, 180);
    });

    function mergeFiles(fileList) {
        const files = Array.from(fileList || []);
        const invalidFiles = [];

        files.forEach(function (file) {
            const kind = fileKind(file);

            if (kind === 'file') {
                invalidFiles.push(file.name + ' possui formato nao suportado.');
                return;
            }

            if ((file.size || 0) > perFileLimitBytes) {
                invalidFiles.push(file.name + ' excede o limite de ' + formatBytes(perFileLimitBytes) + ' por arquivo.');
                return;
            }

            const alreadySelected = uploadQueue.some(function (queuedFile) {
                return queuedFile.name === file.name
                    && queuedFile.size === file.size
                    && queuedFile.lastModified === file.lastModified;
            });

            if (!alreadySelected) {
                uploadQueue.push(file);
            }
        });

        renderSelectedFiles();

        if (invalidFiles.length > 0) {
            showUploadMessage('error', 'Arquivo recusado', invalidFiles[0]);
        }
    }

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            mergeFiles(this.files);
            this.value = '';
        }
    });

    ['dragover', 'dragenter'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault();
            if (!isUploading) {
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            }
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('click', function () {
        if (!isUploading && selectedEventId) {
            fileInput.click();
        }
    });

    dropZone.addEventListener('drop', function (event) {
        if (event.dataTransfer.files.length > 0 && !isUploading) {
            mergeFiles(event.dataTransfer.files);
        }
    });

    addFilesButton.addEventListener('click', function () {
        if (!isUploading && selectedEventId) {
            fileInput.click();
        }
    });

    async function sendSingleFile(file, fileIndex, totalFiles) {
        const formData = new FormData();
        formData.append('files[]', file);

        return new Promise(function (resolve, reject) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `{{ url('/painel/admin/events') }}/${selectedEventId}/media`, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.addEventListener('progress', function (event) {
                const total = Number(event.total || 0);
                const loaded = Number(event.loaded || 0);
                const fraction = total > 0 ? loaded / total : 0;
                const percent = Math.min(100, Math.round(((fileIndex + fraction) / totalFiles) * 100));

                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
                progressStatus.textContent = `Enviando ${fileIndex + 1} de ${totalFiles}: ${file.name}`;
                progressDetails.textContent = `${fileIndex + 1} / ${totalFiles} arquivos em processamento`;
                progressRemaining.textContent = fileKind(file) === 'video' ? 'processando video...' : 'processando imagem...';
            });

            xhr.addEventListener('load', function () {
                let payload = {};

                try {
                    payload = xhr.responseText ? JSON.parse(xhr.responseText) : {};
                } catch (error) {
                    payload = {};
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve({ data: payload });
                    return;
                }

                reject({
                    response: { data: payload },
                    message: payload.message || ('Falha no upload (HTTP ' + xhr.status + ')')
                });
            });

            xhr.addEventListener('error', function () {
                reject(new Error('Falha de conexao durante o upload.'));
            });

            xhr.addEventListener('abort', function () {
                reject(new Error('Upload cancelado.'));
            });

            xhr.send(formData);
        });
    }

    async function handlePanelUpload() {
        if (!selectedEventId) {
            showUploadMessage('warning', 'Evento obrigatorio', 'Selecione o evento antes de enviar os arquivos.');
            return;
        }

        if (uploadQueue.length === 0) {
            showUploadMessage('warning', 'Arquivos obrigatorios', 'Selecione pelo menos um arquivo para publicar.');
            return;
        }

        isUploading = true;
        progress.hidden = false;
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressStatus.textContent = 'Preparando lote...';
        progressDetails.textContent = `0 / ${uploadQueue.length} arquivos enviados`;
        progressRemaining.textContent = 'iniciando';
        updateActionState();
        renderSelectedFiles();

        const failures = [];
        let successCount = 0;
        const totalFiles = uploadQueue.length;

        for (let index = 0; index < totalFiles; index++) {
            const file = uploadQueue[index];

            try {
                const response = await sendSingleFile(file, index, totalFiles);
                if (response.data && response.data.success) {
                    successCount += Number(response.data.uploaded_count || 1);
                    progressDetails.textContent = `${successCount} / ${totalFiles} arquivos enviados`;
                    progressRemaining.textContent = 'lote em andamento';
                    continue;
                }

                throw new Error(response.data?.message || 'Falha no upload');
            } catch (error) {
                const message = error.response?.data?.message || error.message || 'Falha ao enviar o arquivo.';
                failures.push(file.name + ': ' + message);
            }
        }

        isUploading = false;
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        progressDetails.textContent = `${successCount} / ${totalFiles} arquivos enviados`;
        progressRemaining.textContent = failures.length > 0 ? 'lote finalizado com ressalvas' : 'concluido';
        progressStatus.textContent = failures.length > 0 ? 'Concluido com falhas' : 'Upload concluido';
        updateActionState();

        if (successCount === 0) {
            showUploadMessage('error', 'Erro no upload', failures[0] || 'Nenhum arquivo conseguiu ser enviado.');
            resetProgress();
            return;
        }

        const summary = `${successCount} arquivo(s) enviado(s) com sucesso.` + (failures.length > 0 ? ` ${failures.length} falharam.` : '');

        showUploadMessage(failures.length > 0 ? 'warning' : 'success', failures.length > 0 ? 'Upload concluido com ressalvas' : 'Upload concluido', summary)
            .then(function () {
                window.closePanelQuickUpload();
            });
    }

    submitButton.addEventListener('click', handlePanelUpload);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.panel-quick-remove-file');
        if (!button || isUploading) {
            return;
        }

        const index = parseInt(button.getAttribute('data-index') || '-1', 10);
        if (index < 0) {
            return;
        }

        uploadQueue.splice(index, 1);
        renderSelectedFiles();
    });

    window.closePanelQuickUpload();
})();
</script>
