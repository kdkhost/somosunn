@php
    $galleryUploadPerFileLimitBytes = $galleryUploadPerFileLimitBytes ?? (\App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024));
@endphp

<script>
    $(function () {
        const $modal = $('#uploadModal');
        const $form = $('#adminUploadForm');
        const $eventField = $('#admin-upload-event');
        const $dropzone = $('#adminDropzone');
        const $submitBtn = $('#adminSubmitBtn');
        const $progressBar = $('#adminProgressBar');
        const $progressLabel = $('#adminProgressLabel');
        const $progressValue = $('#adminProgressValue');
        const $selectedFiles = $('#adminSelectedFiles');
        const $selectedSummary = $('#adminSelectedSummary');
        const $selectedSize = $('#adminSelectedSize');
        const $selectedList = $('#adminSelectedList');
        const $inlinePreviewGrid = $('#adminInlinePreviewGrid');
        const fileInputElement = document.getElementById('adminFileInput');
        const dropzoneEmpty = document.getElementById('adminDropzoneEmpty');
        const dropzonePreview = document.getElementById('adminDropzonePreview');
        const addMoreButton = document.getElementById('adminAddMoreFiles');
        const selectedFilter = document.getElementById('gallery-event-filter');
        const galleryContainer = document.getElementById('gallery-container');
        const visibleTotalValue = document.getElementById('adminGalleryVisibleTotal');
        const scopedTotalValue = document.getElementById('adminGalleryScopeCount');
        const resultCountValue = document.getElementById('adminGalleryResultCount');
        const perFileLimitBytes = Math.max(1, parseInt('{{ $galleryUploadPerFileLimitBytes }}', 10) || (20 * 1024 * 1024));
        const mediaStoreUrlTemplate = '{{ url('/admin/events/__EVENT__/media') }}';
        let uploadQueue = [];
        let isUploading = false;
        let queueSeed = 0;
        let renderTick = 0;

        if ($.fn && typeof $.fn.select2 === 'function') {
            $('.select2-modal').select2({
                dropdownParent: $modal
            });
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function stripHtml(value) {
            return String(value || '')
                .replace(/<[^>]+>/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function formatBytes(bytes) {
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const value = bytes / Math.pow(1024, exponent);

            return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
        }

        function formatRemaining(seconds) {
            const safeSeconds = Number(seconds || 0);
            if (!Number.isFinite(safeSeconds) || safeSeconds <= 0) {
                return 'calculando tempo restante...';
            }

            const rounded = Math.round(safeSeconds);
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

        function fileKindOf(file) {
            const type = String(file.type || '').toLowerCase();
            const name = String(file.name || '').toLowerCase();

            if (type.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg)$/i.test(name)) {
                return 'image';
            }

            if (type.startsWith('video/') || /\.(mp4|mov|m4v|webm|mkv)$/i.test(name)) {
                return 'video';
            }

            return 'file';
        }

        function signatureOf(file) {
            return [file.name, file.size, file.lastModified].join('::');
        }

        function previewMarkup(item) {
            if (item.kind === 'image' && item.previewUrl) {
                return `<img src="${item.previewUrl}" alt="${escapeHtml(item.file.name)}">`;
            }

            if (item.kind === 'video' && item.previewUrl) {
                return `<video src="${item.previewUrl}" muted playsinline preload="metadata"></video>`;
            }

            return `<div class="gallery-admin-preview-fallback"><i class="fas fa-file-alt"></i></div>`;
        }

        function itemMeta(item) {
            if (item.state === 'uploading') {
                return { badge: 'badge-primary', label: 'enviando', progress: 'bg-primary progress-bar-striped progress-bar-animated', status: 'text-primary' };
            }

            if (item.state === 'done') {
                return { badge: 'badge-success', label: 'concluido', progress: 'bg-success', status: 'text-success' };
            }

            if (item.state === 'error') {
                return { badge: 'badge-danger', label: 'falhou', progress: 'bg-danger', status: 'text-danger' };
            }

            return {
                badge: item.kind === 'video' ? 'badge-info' : 'badge-secondary',
                label: item.kind === 'video' ? 'video' : 'imagem',
                progress: 'bg-secondary',
                status: 'text-muted'
            };
        }

        function buildQueueItem(file) {
            const kind = fileKindOf(file);

            return {
                id: `admin-gallery-item-${++queueSeed}`,
                signature: signatureOf(file),
                file,
                kind,
                previewUrl: (kind === 'image' || kind === 'video') ? URL.createObjectURL(file) : '',
                progress: 0,
                state: 'ready',
                remaining: 'pronto para iniciar',
                error: '',
                uploadedBytes: 0,
                uploadedMedia: null
            };
        }

        function revokeItem(item) {
            if (item && item.previewUrl) {
                URL.revokeObjectURL(item.previewUrl);
                item.previewUrl = '';
            }
        }

        function revokeQueue() {
            uploadQueue.forEach(revokeItem);
        }

        function toggleDropzoneState() {
            const hasFiles = uploadQueue.length > 0;
            if (dropzoneEmpty) {
                dropzoneEmpty.classList.toggle('d-none', hasFiles);
            }
            if (dropzonePreview) {
                dropzonePreview.classList.toggle('d-none', !hasFiles);
            }
        }

        function resetProgress() {
            $progressBar.css('width', '0%');
            $progressValue.text('0%');
            $progressLabel.text('Aguardando selecao dos arquivos.');
            $('#adminProgressDetails').remove();
        }

        function updateActionState() {
            const hasEvent = String($eventField.val() || '').trim() !== '';
            const hasFiles = uploadQueue.length > 0;

            $submitBtn.prop('disabled', !hasEvent || !hasFiles || isUploading);
            $submitBtn.html(
                isUploading
                    ? '<i class="fas fa-spinner fa-spin mr-2"></i> ENVIANDO...'
                    : '<i class="fas fa-upload mr-1"></i>Publicar na galeria'
            );

            if (addMoreButton) {
                addMoreButton.disabled = !hasEvent || isUploading;
                addMoreButton.classList.toggle('d-none', !hasFiles);
            }

            $dropzone.toggleClass('is-disabled', isUploading);
        }

        function updateGlobalProgress(status, remainingOverride) {
            if (uploadQueue.length === 0) {
                resetProgress();
                return;
            }

            const total = uploadQueue.length;
            const done = uploadQueue.filter((item) => item.state === 'done').length;
            const active = uploadQueue.find((item) => item.state === 'uploading');
            const processed = uploadQueue.reduce((sum, item) => {
                if (item.state === 'done' || item.state === 'error') {
                    return sum + 1;
                }

                if (item.state === 'uploading') {
                    return sum + (Number(item.progress || 0) / 100);
                }

                return sum;
            }, 0);

            const percent = Math.max(0, Math.min(100, Math.round((processed / total) * 100)));
            $progressBar.css('width', `${percent}%`);
            $progressValue.text(`${percent}%`);
            $progressLabel.text(status || (active ? `Enviando ${active.file.name}` : 'Preparando lote...'));

            $('#adminProgressDetails').remove();
            $('<div>', {
                id: 'adminProgressDetails',
                class: 'small text-muted mt-2',
                text: `${done} / ${total} arquivo(s) enviado(s)${remainingOverride ? ` - ${remainingOverride}` : ''}`
            }).insertAfter($progressBar.closest('.progress'));
        }

        function renderSelectedFiles() {
            if (uploadQueue.length === 0) {
                $selectedFiles.addClass('d-none');
                $selectedSummary.text('0 arquivo(s)');
                $selectedSize.text('0 B');
                $selectedList.html('');
                $inlinePreviewGrid.html('');
                toggleDropzoneState();
                updateActionState();
                return;
            }

            const totalBytes = uploadQueue.reduce((sum, item) => sum + Number(item.file.size || 0), 0);
            $selectedFiles.removeClass('d-none');
            $selectedSummary.text(`${uploadQueue.length} arquivo(s) selecionado(s)`);
            $selectedSize.text(formatBytes(totalBytes));
            $selectedList.html(uploadQueue.map((item) => {
                const meta = itemMeta(item);
                const progress = Math.max(0, Math.min(100, Math.round(Number(item.progress || 0))));

                return `
                    <div class="gallery-admin-selected-item card card-body mb-2">
                        <div class="d-flex align-items-start">
                            <div class="gallery-admin-selected-preview ${item.kind === 'video' ? 'is-video' : ''}">
                                ${previewMarkup(item)}
                            </div>
                            <div class="flex-grow-1 min-width-0 ml-3">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between mb-2">
                                    <div class="pr-lg-3 min-width-0">
                                        <div class="d-flex flex-wrap align-items-center mb-1" style="gap:.5rem;">
                                            <span class="badge ${meta.badge} text-uppercase">${meta.label}</span>
                                            <span class="font-weight-bold text-dark text-break">${escapeHtml(item.file.name)}</span>
                                        </div>
                                        <p class="small text-muted mb-0">${formatBytes(item.file.size || 0)}</p>
                                    </div>

                                    <button type="button" class="btn btn-link text-danger p-0 gallery-admin-remove-file" data-id="${item.id}" ${isUploading ? 'disabled' : ''}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <div class="progress gallery-admin-file-progress">
                                    <div class="progress-bar ${meta.progress}" role="progressbar" style="width:${progress}%"></div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="${meta.status} font-weight-bold">${escapeHtml(item.remaining || 'pronto para iniciar')}</small>
                                    <small class="text-muted">${progress}%</small>
                                </div>

                                ${item.error ? `<small class="gallery-admin-item-error">${escapeHtml(item.error)}</small>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join(''));
            $inlinePreviewGrid.html(uploadQueue.map((item) => {
                const meta = itemMeta(item);
                return `
                    <div class="gallery-admin-inline-preview-item ${item.kind === 'video' ? 'is-video' : ''}" title="${escapeHtml(item.file.name)}">
                        ${previewMarkup(item)}
                        <span class="badge ${meta.badge} gallery-admin-inline-badge text-uppercase">${meta.label}</span>
                    </div>
                `;
            }).join(''));

            toggleDropzoneState();
            updateActionState();
        }

        function scheduleRender(force) {
            const now = Date.now();
            if (force || (now - renderTick) > 120) {
                renderTick = now;
                renderSelectedFiles();
            }
        }

        function showMessage(type, title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    icon: type,
                    title,
                    text,
                    confirmButtonText: 'OK'
                });
            }

            alert(`${title}\n\n${text}`);
            return Promise.resolve();
        }

        function extractUploadError(xhr, payload) {
            let message = payload?.message || '';

            if (payload?.errors) {
                message = Object.values(payload.errors).flat().join(' ');
            }

            if (!message && xhr.responseText) {
                message = stripHtml(xhr.responseText);
            }

            if (!message) {
                message = 'Falha ao realizar upload.';
            }

            if (xhr.status === 403 && !payload?.message) {
                message = 'O servidor bloqueou o upload. Verifique permissoes ou regras de seguranca.';
            } else if (xhr.status === 413) {
                message = `O servidor recusou o upload porque o arquivo excede o limite permitido (${formatBytes(perFileLimitBytes)} por arquivo).`;
            } else if (xhr.status === 419) {
                message = 'Sua sessao expirou. Recarregue a pagina e tente novamente.';
            } else if (xhr.status >= 500 && !payload?.message) {
                message = 'O servidor encontrou um erro interno ao processar o upload.';
            }

            return message;
        }

        function mergeFiles(fileList) {
            const incomingFiles = Array.from(fileList || []);
            const invalidMessages = [];

            incomingFiles.forEach((file) => {
                const kind = fileKindOf(file);
                if (kind === 'file') {
                    invalidMessages.push(`${file.name} possui um formato nao suportado.`);
                    return;
                }

                if ((file.size || 0) > perFileLimitBytes) {
                    invalidMessages.push(`${file.name} excede o limite de ${formatBytes(perFileLimitBytes)} por arquivo.`);
                    return;
                }

                if (!uploadQueue.some((item) => item.signature === signatureOf(file))) {
                    uploadQueue.push(buildQueueItem(file));
                }
            });

            if (fileInputElement) {
                fileInputElement.value = '';
            }

            scheduleRender(true);

            if (invalidMessages.length > 0) {
                showMessage('warning', 'Arquivo recusado', invalidMessages[0]);
            }
        }

        function avatarMarkup(item) {
            const ownerName = String(item.owner_name || 'Sistema');
            const initial = escapeHtml(ownerName.trim().charAt(0) || 'S').toUpperCase();
            if (item.owner_avatar) {
                return `
                    <img src="${escapeHtml(item.owner_avatar)}"
                        alt="${escapeHtml(ownerName)}"
                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                `;
            }

            return `<span>${initial}</span>`;
        }

        function createMediaCard(item) {
            const eventTitle = escapeHtml(item.event_title || 'Evento sem titulo');
            const ownerName = escapeHtml(item.owner_name || 'Sistema');
            const uploadedAt = escapeHtml(item.uploaded_at || '--');
            const eventDate = escapeHtml(item.event_date || '--/--/----');
            const assetUrl = escapeHtml(item.url || '{{ asset('img/logo.svg') }}');
            const isCover = Boolean(item.is_cover);
            const isVideo = String(item.type || '') === 'video';

            return `
                <div class="col-sm-6 col-xl-4 mb-4" data-gallery-card-id="${item.id}">
                    <div class="card h-100 shadow-sm">
                        <div class="gallery-admin-thumb">
                            ${isVideo
                                ? `
                                    <a href="${assetUrl}" target="_blank" rel="noopener" class="gallery-admin-video-link">
                                        <video src="${assetUrl}" muted playsinline preload="metadata"></video>
                                        <span class="gallery-admin-video-overlay">
                                            <i class="fas fa-play-circle fa-2x mb-2"></i>
                                            <span class="font-weight-bold">Abrir video</span>
                                        </span>
                                    </a>
                                `
                                : `
                                    <a href="${assetUrl}" target="_blank" rel="noopener">
                                        <img src="${assetUrl}" alt="${eventTitle}" loading="lazy" decoding="async">
                                    </a>
                                `
                            }
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="gallery-admin-avatar mr-2">
                                    ${avatarMarkup(item)}
                                </div>

                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-truncate">${ownerName}</div>
                                    <small class="text-muted">Enviado em ${uploadedAt}</small>
                                </div>
                            </div>

                            <h3 class="h6 font-weight-bold mb-2">${eventTitle}</h3>

                            <ul class="list-unstyled text-muted small mb-3">
                                <li class="mb-1">
                                    <i class="far fa-calendar-alt mr-1"></i>${eventDate}
                                </li>
                                <li class="mb-1">
                                    <i class="fas ${isVideo ? 'fa-film' : 'fa-image'} mr-1"></i>${isVideo ? 'Video' : 'Imagem'}
                                </li>
                                <li>
                                    <i class="fas fa-certificate mr-1"></i>${item.watermarked ? 'Com marca d\\'agua' : 'Sem marca d\\'agua'}
                                </li>
                            </ul>

                            <div class="mb-3">
                                ${isCover ? '<span class="badge badge-warning mr-1"><i class="fas fa-star mr-1"></i>Capa do album</span>' : ''}
                                ${item.watermarked ? '<span class="badge badge-primary">Watermark</span>' : ''}
                            </div>

                            <div class="mt-auto">
                                <div class="row">
                                    ${!isVideo
                                        ? `
                                            <div class="col-8 pr-1">
                                                <form action="${escapeHtml(item.set_cover_url || '')}" method="POST" class="mb-0 gallery-cover-form">
                                                    @csrf
                                                    <button type="submit" class="btn ${isCover ? 'btn-warning' : 'btn-outline-warning'} btn-sm btn-block">
                                                        <i class="fas fa-star mr-1"></i>${isCover ? 'Capa ativa' : 'Definir capa'}
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="col-4 pl-1">
                                                <form action="${escapeHtml(item.delete_url || '')}" method="POST" class="delete-form mb-0" data-confirm-title="Remover da galeria?" data-confirm-text="Esta midia sera excluida permanentemente.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        `
                                        : `
                                            <div class="col-12">
                                                <form action="${escapeHtml(item.delete_url || '')}" method="POST" class="delete-form mb-0" data-confirm-title="Remover da galeria?" data-confirm-text="Esta midia sera excluida permanentemente.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block">
                                                        <i class="fas fa-trash mr-1"></i>Remover video
                                                    </button>
                                                </form>
                                            </div>
                                        `
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function bindDeleteForm(form) {
            if (!form) {
                return;
            }

            $(form).off('submit.galleryDelete').on('submit.galleryDelete', function (event) {
                event.preventDefault();
                const currentForm = this;
                const title = $(currentForm).data('confirm-title') || 'Tem certeza?';
                const text = $(currentForm).data('confirm-text') || 'Esta acao nao podera ser desfeita.';

                if (typeof Swal === 'undefined') {
                    if (window.confirm(text)) {
                        currentForm.submit();
                    }
                    return;
                }

                Swal.fire({
                    title,
                    text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1e293b',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentForm.submit();
                    }
                });
            });
        }

        function updateCountValue(element, nextValue) {
            if (!element || !Number.isFinite(nextValue) || nextValue < 0) {
                return;
            }

            element.textContent = Number(nextValue).toLocaleString('pt-BR');
        }

        function currentCountValue(element) {
            if (!element) {
                return 0;
            }

            const parsed = parseInt(String(element.textContent || '').replace(/\./g, ''), 10);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function shouldAppendMedia(item) {
            if (!selectedFilter || !selectedFilter.value) {
                return true;
            }

            return String(selectedFilter.value) === String(item.event_id || '');
        }

        function prependUploadedMedia(items) {
            if (!galleryContainer || !Array.isArray(items) || items.length === 0) {
                return;
            }

            let appended = 0;

            items.slice().reverse().forEach((item) => {
                if (!shouldAppendMedia(item)) {
                    return;
                }

                galleryContainer.insertAdjacentHTML('afterbegin', createMediaCard(item));
                const card = galleryContainer.firstElementChild;
                if (card) {
                    card.querySelectorAll('.delete-form').forEach(bindDeleteForm);
                }

                appended += 1;
            });

            if (appended === 0) {
                return;
            }

            const emptyState = document.getElementById('adminGalleryEmptyState');
            if (emptyState) {
                emptyState.remove();
            }

            updateCountValue(visibleTotalValue, currentCountValue(visibleTotalValue) + appended);
            if (selectedFilter && selectedFilter.value) {
                updateCountValue(scopedTotalValue, currentCountValue(scopedTotalValue) + appended);
            }
            updateCountValue(resultCountValue, currentCountValue(resultCountValue) + appended);
        }

        function resetQueue() {
            revokeQueue();
            uploadQueue = [];
            isUploading = false;
            renderTick = 0;
            if (fileInputElement) {
                fileInputElement.value = '';
            }
            resetProgress();
            scheduleRender(true);
        }

        async function sendSingleFile(item, index, total) {
            const selectedEventId = String($eventField.val() || '').trim();
            const formData = new FormData();
            const startedAt = Date.now();

            formData.append('files[]', item.file);
            item.state = 'uploading';
            item.progress = 0;
            item.uploadedBytes = 0;
            item.remaining = 'calculando tempo restante...';
            item.error = '';

            scheduleRender(true);
            updateGlobalProgress(`Enviando ${index + 1} de ${total}: ${item.file.name}`, item.remaining);

            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', mediaStoreUrlTemplate.replace('__EVENT__', selectedEventId), true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', function (progressEvent) {
                    const totalBytes = Number(progressEvent.total || item.file.size || 0);
                    const loaded = Number(progressEvent.loaded || 0);
                    const elapsed = Math.max((Date.now() - startedAt) / 1000, 0.2);
                    const speed = loaded / elapsed;

                    item.uploadedBytes = loaded;
                    item.progress = Math.max(0, Math.min(100, Math.round((loaded / Math.max(totalBytes, 1)) * 100)));
                    item.remaining = formatRemaining(speed > 0 ? ((totalBytes - loaded) / speed) : 0);

                    scheduleRender(false);
                    updateGlobalProgress(`Enviando ${index + 1} de ${total}: ${item.file.name}`, item.remaining);
                });

                xhr.addEventListener('load', function () {
                    let payload = null;

                    try {
                        payload = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                    } catch (error) {
                        payload = null;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && payload && payload.success) {
                        resolve(payload);
                        return;
                    }

                    reject(new Error(extractUploadError(xhr, payload)));
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

        async function handleQueueUpload() {
            const selectedEventId = String($eventField.val() || '').trim();

            if (!selectedEventId) {
                showMessage('warning', 'Evento obrigatorio', 'Selecione o evento antes de enviar os arquivos.');
                return;
            }

            if (uploadQueue.length === 0) {
                showMessage('warning', 'Arquivos obrigatorios', 'Selecione pelo menos um arquivo para publicar.');
                return;
            }

            isUploading = true;
            updateActionState();
            scheduleRender(true);
            updateGlobalProgress('Preparando lote...', 'iniciando');

            const failures = [];
            const successfulMedia = [];
            const total = uploadQueue.length;

            for (let index = 0; index < total; index += 1) {
                const item = uploadQueue[index];

                try {
                    const payload = await sendSingleFile(item, index, total);
                    const mediaItem = Array.isArray(payload.media) && payload.media.length > 0 ? payload.media[0] : null;

                    item.state = 'done';
                    item.progress = 100;
                    item.remaining = 'concluido';
                    item.error = '';
                    item.uploadedMedia = mediaItem;

                    if (mediaItem) {
                        successfulMedia.push(mediaItem);
                    }

                    scheduleRender(true);
                    updateGlobalProgress(`Arquivo publicado: ${item.file.name}`, 'concluido');
                } catch (error) {
                    item.state = 'error';
                    item.progress = 100;
                    item.remaining = 'falhou';
                    item.error = error.message || 'Falha no upload.';
                    failures.push(`${item.file.name}: ${item.error}`);

                    scheduleRender(true);
                    updateGlobalProgress(`Falha ao enviar ${item.file.name}`, 'falhou');
                }
            }

            isUploading = false;
            scheduleRender(true);
            updateActionState();
            updateGlobalProgress(
                failures.length > 0 ? 'Concluido com falhas' : 'Upload concluido',
                failures.length > 0 ? 'lote finalizado com ressalvas' : 'concluido'
            );

            if (successfulMedia.length > 0) {
                prependUploadedMedia(successfulMedia);
            }

            if (successfulMedia.length === 0) {
                showMessage('error', 'Upload recusado', failures[0] || 'Nenhum arquivo conseguiu ser enviado.');
                return;
            }

            const title = failures.length > 0 ? 'Upload concluido com ressalvas' : 'Upload concluido';
            const text = `${successfulMedia.length} arquivo(s) enviado(s) com sucesso.${failures.length > 0 ? ` ${failures.length} falharam.` : ''}`;

            showMessage(failures.length > 0 ? 'warning' : 'success', title, text).then(function () {
                if (failures.length === 0) {
                    $modal.modal('hide');
                }
            });
        }

        $dropzone.on('click', function (event) {
            if (isUploading || !fileInputElement) {
                return;
            }

            if ($(event.target).closest('input, label, button, a').length) {
                return;
            }

            fileInputElement.click();
        });

        $dropzone.on('keydown', function (event) {
            if (isUploading || !fileInputElement) {
                return;
            }

            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            fileInputElement.click();
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            $dropzone.on(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (!isUploading) {
                    $dropzone.addClass('dragover');
                }
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
            $dropzone.on(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                $dropzone.removeClass('dragover');
            });
        });

        $dropzone.on('drop', function (event) {
            if (isUploading) {
                return;
            }

            const droppedFiles = Array.from(event.originalEvent?.dataTransfer?.files || []);
            if (droppedFiles.length > 0) {
                mergeFiles(droppedFiles);
            }
        });

        if (fileInputElement) {
            fileInputElement.addEventListener('change', function () {
                mergeFiles(this.files);
            });
        }

        if (addMoreButton) {
            addMoreButton.addEventListener('click', function () {
                if (!isUploading && fileInputElement) {
                    fileInputElement.click();
                }
            });
        }

        $eventField.on('change', updateActionState);
        $form.on('submit', function (event) {
            event.preventDefault();
            if (!isUploading) {
                handleQueueUpload();
            }
        });
        $submitBtn.on('click', function (event) {
            event.preventDefault();
            if (!isUploading) {
                handleQueueUpload();
            }
        });

        $(document).on('click', '.gallery-admin-remove-file', function () {
            if (isUploading) {
                return;
            }

            const fileId = String($(this).data('id') || '');
            const nextQueue = [];

            uploadQueue.forEach((item) => {
                if (item.id === fileId) {
                    revokeItem(item);
                    return;
                }

                nextQueue.push(item);
            });

            uploadQueue = nextQueue;
            scheduleRender(true);
            updateGlobalProgress();
        });

        $('.delete-form').each(function () {
            bindDeleteForm(this);
        });

        $modal.on('hidden.bs.modal', function () {
            resetQueue();
        });

        updateActionState();
        resetProgress();
        scheduleRender(true);
    });
</script>
