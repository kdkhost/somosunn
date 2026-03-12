@php
    $quickUploadPerFileLimitBytes = \App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024)
        ?? (20 * 1024 * 1024);
    $quickUploadPerFileLimitMb = number_format($quickUploadPerFileLimitBytes / 1024 / 1024, 2, '.', '');
    $quickUploadEvents = \App\Models\Event::query()
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

<div class="modal fade" id="modalQuickUpload" tabindex="-1" role="dialog" aria-labelledby="modalQuickUploadLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-xl quick-upload-modal-shell">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" id="modalQuickUploadLabel">
                    <i class="fas fa-camera-retro mr-2"></i> Registro Rapido de Midias
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <div id="quickUploadStep1">
                    <label class="font-weight-bold mb-2 d-block">1. Selecione o evento</label>

                    <div id="quickUploadSearchWrap" class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="quickUploadSearch" class="form-control border-left-0"
                            placeholder="Digite o nome do evento para buscar...">
                    </div>

                    <div id="quickUploadResults" class="list-group mb-3 overflow-auto shadow-sm"
                        style="max-height: 260px; display: none;"></div>

                    <div id="quickUploadSelected"
                        class="alert alert-info d-none d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <i class="fas fa-calendar-check mr-2"></i>
                            <span id="quickUploadSelectedName" class="font-weight-bold">Nenhum evento selecionado</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-info font-weight-bold p-0"
                            onclick="window.clearQuickUploadSelection()">
                            Trocar evento
                        </button>
                    </div>
                </div>

                <div id="quickUploadStep2" class="mt-4 d-none">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-2">
                        <label class="font-weight-bold mb-1 mb-lg-0">2. Selecione e envie arquivos</label>
                        <small class="text-muted">
                            Arraste varias imagens e videos ou adicione novos arquivos antes de publicar.
                        </small>
                    </div>

                    <input type="file" id="quickUploadInput" multiple accept="image/*,video/*" class="d-none">

                    <div class="premium-upload-box mb-3" id="quickUploadMediaBox">
                        <div class="drop-zone-area p-5 text-center border-2 border-dashed rounded-lg bg-light position-relative quick-upload-dropzone"
                            id="quickUploadDropZone" role="button" tabindex="0">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 d-block"></i>
                                <h5 class="font-weight-bold mb-2">Arraste fotos e videos aqui</h5>
                                <p class="text-muted mb-3">
                                    ou <span class="text-primary" style="text-decoration: underline;">clique para selecionar</span>
                                </p>
                                <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                                    <span class="badge badge-pill badge-primary px-3">Imagens</span>
                                    <span class="badge badge-pill badge-info px-3">Videos</span>
                                    <span class="badge badge-pill badge-secondary px-3">Ate {{ $quickUploadPerFileLimitMb }} MB por arquivo</span>
                                </div>
                                <small class="text-secondary d-block">
                                    O envio rapido publica os arquivos em lote, um por vez, para evitar bloqueios do servidor.
                                </small>
                            </div>
                        </div>

                        <div id="quickUploadSelectedFiles" class="card border-0 shadow-sm mt-3 d-none">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                                    <div>
                                        <p class="text-uppercase text-muted small font-weight-bold mb-1">Arquivos prontos</p>
                                        <p id="quickUploadSelectedSummary" class="font-weight-bold mb-0">0 arquivo(s)</p>
                                    </div>
                                    <span id="quickUploadSelectedSize" class="badge badge-pill badge-light px-3 py-2 mt-2 mt-md-0">0 B</span>
                                </div>
                                <div id="quickUploadSelectedList" class="quick-upload-selected-list"></div>
                            </div>
                        </div>

                        <div id="quickUploadProgress" class="mt-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-primary" id="quickUploadStatus">Aguardando envio...</span>
                                <span class="small font-weight-bold" id="quickUploadPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 999px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    id="quickUploadProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted" id="quickUploadDetails">0 / 0 arquivos enviados</small>
                                <small class="text-muted" id="quickUploadRemaining">pronto para iniciar</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light rounded-bottom-xl d-flex flex-column flex-md-row justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4 mb-2 mb-md-0" data-dismiss="modal">
                    Fechar
                </button>

                <div class="d-flex flex-column flex-md-row">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 mr-md-2 mb-2 mb-md-0 d-none"
                        id="quickUploadAddFiles">
                        <i class="fas fa-plus mr-1"></i> Adicionar arquivos
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="quickUploadSubmit" disabled>
                        <i class="fas fa-paper-plane mr-1"></i> Publicar na galeria
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            let selectedEventId = null;
            let searchTimeout = null;
            let uploadQueue = [];
            let isUploading = false;

            const modal = $('#modalQuickUpload');
            const searchWrap = $('#quickUploadSearchWrap');
            const searchInput = $('#quickUploadSearch');
            const results = $('#quickUploadResults');
            const selectedBox = $('#quickUploadSelected');
            const selectedName = $('#quickUploadSelectedName');
            const step2 = $('#quickUploadStep2');
            const fileInput = document.getElementById('quickUploadInput');
            const dropZone = document.getElementById('quickUploadDropZone');
            const addFilesButton = document.getElementById('quickUploadAddFiles');
            const submitButton = document.getElementById('quickUploadSubmit');
            const selectedFilesWrap = document.getElementById('quickUploadSelectedFiles');
            const selectedSummary = document.getElementById('quickUploadSelectedSummary');
            const selectedSize = document.getElementById('quickUploadSelectedSize');
            const selectedList = document.getElementById('quickUploadSelectedList');
            const progressBox = $('#quickUploadProgress');
            const progressBar = $('#quickUploadProgressBar');
            const percentText = $('#quickUploadPercent');
            const statusText = $('#quickUploadStatus');
            const detailsText = $('#quickUploadDetails');
            const remainingText = $('#quickUploadRemaining');
            const perFileLimitBytes = Math.max(1, parseInt(window.UNN_ADMIN_UPLOAD_MAX_BYTES || {{ $quickUploadPerFileLimitBytes }}, 10) || {{ $quickUploadPerFileLimitBytes }});
            const availableEvents = @json($quickUploadEvents);

            function formatBytes(bytes) {
                const value = Number(bytes || 0);
                if (!Number.isFinite(value) || value <= 0) {
                    return '0 B';
                }

                const units = ['B', 'KB', 'MB', 'GB'];
                const exponent = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
                const amount = value / Math.pow(1024, exponent);

                return amount.toFixed(amount >= 100 || exponent === 0 ? 0 : 1) + ' ' + units[exponent];
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

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function resetProgress() {
                progressBox.addClass('d-none');
                progressBar.css('width', '0%');
                percentText.text('0%');
                statusText.text('Aguardando envio...');
                detailsText.text('0 / 0 arquivos enviados');
                remainingText.text('pronto para iniciar');
            }

            function updateActionState() {
                const hasEvent = Boolean(selectedEventId);
                const hasFiles = uploadQueue.length > 0;

                if (submitButton) {
                    submitButton.disabled = !hasEvent || !hasFiles || isUploading;
                    submitButton.innerHTML = isUploading
                        ? '<i class="fas fa-spinner fa-spin mr-1"></i> Enviando...'
                        : '<i class="fas fa-paper-plane mr-1"></i> Publicar na galeria';
                }

                if (addFilesButton) {
                    addFilesButton.classList.toggle('d-none', !hasEvent);
                    addFilesButton.disabled = !hasEvent || isUploading;
                }

                if (dropZone) {
                    dropZone.classList.toggle('is-disabled', isUploading);
                }
            }

            function renderSelectedFiles() {
                if (!selectedFilesWrap || !selectedSummary || !selectedSize || !selectedList) {
                    return;
                }

                if (uploadQueue.length === 0) {
                    selectedFilesWrap.classList.add('d-none');
                    selectedSummary.textContent = '0 arquivo(s)';
                    selectedSize.textContent = '0 B';
                    selectedList.innerHTML = '';
                    updateActionState();
                    return;
                }

                const totalBytes = uploadQueue.reduce((sum, file) => sum + Number(file.size || 0), 0);
                selectedFilesWrap.classList.remove('d-none');
                selectedSummary.textContent = uploadQueue.length + ' arquivo(s) selecionado(s)';
                selectedSize.textContent = formatBytes(totalBytes);
                selectedList.innerHTML = uploadQueue.map(function (file, index) {
                    const kind = fileKind(file);
                    const badgeClass = kind === 'video' ? 'badge-info' : (kind === 'image' ? 'badge-primary' : 'badge-secondary');
                    const badgeLabel = kind === 'video' ? 'video' : (kind === 'image' ? 'imagem' : 'arquivo');

                    return `
                        <div class="quick-upload-selected-item">
                            <div class="d-flex align-items-center flex-grow-1 mr-3">
                                <span class="badge ${badgeClass} mr-2 text-uppercase">${badgeLabel}</span>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold text-truncate">${escapeHtml(file.name)}</span>
                                    <small class="text-muted">${formatBytes(file.size || 0)}</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-link text-danger p-0 quick-upload-remove-file" data-index="${index}" ${isUploading ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }).join('');

                updateActionState();
            }

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no upload',
                    text: message || 'Falha ao enviar os arquivos.'
                });
            }

            function clearQuickUploadSelection() {
                selectedEventId = null;
                uploadQueue = [];
                isUploading = false;
                step2.addClass('d-none');
                selectedBox.addClass('d-none');
                searchWrap.removeClass('d-none');
                searchInput.val('').prop('disabled', false);
                results.hide().empty();
                fileInput.value = '';
                resetProgress();
                renderSelectedFiles();
            }

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
                    showError(invalidFiles[0]);
                }
            }

            async function sendSingleFile(file, fileIndex, totalFiles) {
                const formData = new FormData();
                formData.append('files[]', file);

                const baseUrl = '{{ request()->routeIs("panel.*") ? url("/painel/admin/events") : url("/admin/events") }}';
                const url = baseUrl + '/' + selectedEventId + '/media';

                return new Promise(function (resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.addEventListener('progress', function (progressEvent) {
                        const fraction = progressEvent.total > 0
                            ? (progressEvent.loaded / progressEvent.total)
                            : 0;
                        const percent = Math.min(100, Math.round(((fileIndex + fraction) / totalFiles) * 100));

                        progressBar.css('width', percent + '%');
                        percentText.text(percent + '%');
                        statusText.text('Enviando ' + (fileIndex + 1) + ' de ' + totalFiles + ': ' + file.name);
                        detailsText.text((fileIndex + 1) + ' / ' + totalFiles + ' arquivos em processamento');
                        remainingText.text(fileKind(file) === 'video' ? 'processando video...' : 'processando imagem...');
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

            async function handleQuickUpload() {
                if (!selectedEventId) {
                    showError('Selecione um evento antes de enviar os arquivos.');
                    return;
                }

                if (uploadQueue.length === 0) {
                    showError('Selecione pelo menos um arquivo para publicar.');
                    return;
                }

                isUploading = true;
                progressBox.removeClass('d-none');
                progressBar.css('width', '0%');
                percentText.text('0%');
                statusText.text('Preparando lote...');
                detailsText.text('0 / ' + uploadQueue.length + ' arquivos enviados');
                remainingText.text('iniciando');
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
                            detailsText.text(successCount + ' / ' + totalFiles + ' arquivos enviados');
                            remainingText.text('lote em andamento');
                            continue;
                        }

                        throw new Error(response.data?.message || 'Falha no upload');
                    } catch (error) {
                        const message = error.response?.data?.message
                            || error.message
                            || 'Falha no upload';

                        failures.push(file.name + ': ' + message);
                    }
                }

                isUploading = false;
                progressBar.css('width', '100%');
                percentText.text('100%');
                detailsText.text(successCount + ' / ' + totalFiles + ' arquivos enviados');
                remainingText.text(failures.length > 0 ? 'lote finalizado com ressalvas' : 'concluido');
                statusText.text(failures.length > 0 ? 'Concluido com falhas' : 'Upload concluido');
                updateActionState();

                if (successCount === 0) {
                    showError(failures[0] || 'Nenhum arquivo conseguiu ser enviado.');
                    resetProgress();
                    return;
                }

                const successMessage = successCount + ' arquivo(s) enviado(s) com sucesso.'
                    + (failures.length > 0 ? ' ' + failures.length + ' falharam.' : '');

                Swal.fire({
                    icon: failures.length > 0 ? 'warning' : 'success',
                    title: failures.length > 0 ? 'Upload concluido com ressalvas' : 'Upload concluido',
                    text: successMessage,
                    confirmButtonText: 'OK'
                }).then(function () {
                    $('#modalQuickUpload').modal('hide');
                    if (selectedEventId && window.location.href.indexOf('/admin/events/' + selectedEventId) !== -1) {
                        window.location.reload();
                    }
                });
            }

            function selectEvent(eventItem) {
                selectedEventId = eventItem.id;
                selectedName.text(eventItem.title);
                selectedBox.removeClass('d-none');
                searchWrap.addClass('d-none');
                results.hide();
                step2.removeClass('d-none');
                renderSelectedFiles();
            }

            window.openQuickUploadModal = function () {
                modal.modal('show');
                clearQuickUploadSelection();
            };

            window.clearQuickUploadSelection = clearQuickUploadSelection;

            modal.on('hidden.bs.modal', function () {
                clearQuickUploadSelection();
            });

            searchInput.on('input', function () {
                const query = String($(this).val() || '').trim();
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                if (query.length < 2) {
                    results.hide().empty();
                    return;
                }

                searchTimeout = setTimeout(function () {
                    const loweredQuery = query.toLowerCase();
                    const events = availableEvents.filter(function (eventItem) {
                        return String(eventItem.title || '').toLowerCase().indexOf(loweredQuery) !== -1;
                    }).slice(0, 30);

                    results.empty().show();

                    if (events.length === 0) {
                        results.append('<div class="list-group-item text-muted">Nenhum evento encontrado</div>');
                        return;
                    }

                    events.forEach(function (eventItem) {
                        const date = eventItem.start || '';
                        $('<button type="button" class="list-group-item list-group-item-action py-3">')
                            .html('<strong>' + escapeHtml(eventItem.title) + '</strong> <small class="text-muted ml-2">' + escapeHtml(date) + '</small>')
                            .on('click', function () { selectEvent(eventItem); })
                            .appendTo(results);
                    });
                }, 180);
            });

            if (dropZone) {
                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropZone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        if (!isUploading) {
                            dropZone.classList.add('is-dragover');
                        }
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropZone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        dropZone.classList.remove('is-dragover');
                    });
                });

                dropZone.addEventListener('click', function () {
                    if (!isUploading && selectedEventId) {
                        fileInput.click();
                    }
                });

                dropZone.addEventListener('keydown', function (event) {
                    if ((event.key === 'Enter' || event.key === ' ') && !isUploading && selectedEventId) {
                        event.preventDefault();
                        fileInput.click();
                    }
                });

                dropZone.addEventListener('drop', function (event) {
                    if (isUploading) {
                        return;
                    }

                    mergeFiles(event.dataTransfer.files);
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    mergeFiles(this.files);
                    this.value = '';
                });
            }

            if (addFilesButton) {
                addFilesButton.addEventListener('click', function () {
                    if (!isUploading && selectedEventId) {
                        fileInput.click();
                    }
                });
            }

            if (submitButton) {
                submitButton.addEventListener('click', handleQuickUpload);
            }

            document.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.quick-upload-remove-file');
                if (!removeButton || isUploading) {
                    return;
                }

                const index = parseInt(removeButton.getAttribute('data-index') || '-1', 10);
                if (index < 0) {
                    return;
                }

                uploadQueue.splice(index, 1);
                renderSelectedFiles();
            });

            clearQuickUploadSelection();
        })();
    </script>
@endpush

<style>
    .rounded-xl {
        border-radius: 1rem !important;
    }

    .rounded-bottom-xl {
        border-bottom-left-radius: 1rem !important;
        border-bottom-right-radius: 1rem !important;
    }

    .quick-upload-modal-shell .quick-upload-dropzone {
        border-color: #d0dae8;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quick-upload-modal-shell .quick-upload-dropzone:hover,
    .quick-upload-modal-shell .quick-upload-dropzone.is-dragover {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
    }

    .quick-upload-modal-shell .quick-upload-dropzone.is-disabled {
        opacity: 0.55;
        pointer-events: none;
    }

    .quick-upload-selected-list {
        display: grid;
        gap: 0.75rem;
        max-height: 220px;
        overflow-y: auto;
    }

    .quick-upload-selected-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 0.9rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
</style>
