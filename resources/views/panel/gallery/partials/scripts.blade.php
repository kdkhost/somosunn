@php
    $galleryUploadPerFileLimitBytes = $galleryUploadPerFileLimitBytes
        ?? (\App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024));
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const uploadModal = document.getElementById('gallery-upload-modal');
    const lightbox = document.getElementById('gallery-lightbox');
    const uploadForm = document.getElementById('gallery-upload-form');
    const filesInput = document.getElementById('gallery-files-input');
    const dropzone = document.getElementById('gallery-dropzone');
    const pickerButton = document.getElementById('gallery-file-picker');
    const selectedWrap = document.getElementById('gallery-selected-files');
    const selectedSummary = document.getElementById('gallery-selected-summary');
    const selectedSize = document.getElementById('gallery-selected-size');
    const selectedList = document.getElementById('gallery-selected-list');
    const progressBar = document.getElementById('gallery-upload-progress-bar');
    const progressLabel = document.getElementById('gallery-upload-progress-label');
    const progressValue = document.getElementById('gallery-upload-progress-value');
    const submitButton = document.getElementById('gallery-upload-submit');
    const lightboxImage = document.getElementById('gallery-lightbox-image');
    const lightboxTitle = document.getElementById('gallery-lightbox-title');
    const eventField = uploadForm?.querySelector('[name="event_id"]');
    const csrfToken = uploadForm?.querySelector('input[name="_token"]')?.value || '';
    const perFileLimitBytes = Math.max(1, parseInt(@json($galleryUploadPerFileLimitBytes), 10) || @json($galleryUploadPerFileLimitBytes));
    let queue = [];
    let isUploading = false;
    let seed = 0;

    function lockBody(locked) {
        body.classList.toggle('overflow-hidden', locked);
    }

    function fmtBytes(bytes) {
        const value = Number(bytes || 0);
        if (!Number.isFinite(value) || value <= 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const exp = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
        const amount = value / Math.pow(1024, exp);
        return `${amount.toFixed(amount >= 100 || exp === 0 ? 0 : 1)} ${units[exp]}`;
    }

    function fmtRemaining(seconds) {
        const value = Number(seconds || 0);
        if (!Number.isFinite(value) || value <= 0) return 'calculando tempo restante...';
        const rounded = Math.round(value);
        if (rounded < 60) return `${rounded}s restantes`;
        const minutes = Math.floor(rounded / 60);
        const rest = rounded % 60;
        return minutes < 60 ? `${minutes}min ${rest}s restantes` : `${Math.floor(minutes / 60)}h ${minutes % 60}min restantes`;
    }

    function esc(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function stripHtml(value) {
        return String(value || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function notify(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({ icon, title, text, confirmButtonText: 'OK' });
        }
        alert(`${title}\n\n${text}`);
        return Promise.resolve();
    }

    function setProgress(percent, label) {
        const safe = Math.max(0, Math.min(100, Math.round(percent)));
        if (progressBar) progressBar.style.width = `${safe}%`;
        if (progressLabel) progressLabel.textContent = label;
        if (progressValue) progressValue.textContent = `${safe}%`;
    }

    function updateActionState() {
        const hasEvent = Boolean(String(eventField?.value || '').trim());
        const hasFiles = queue.length > 0;
        if (submitButton) {
            submitButton.disabled = !hasEvent || !hasFiles || isUploading;
            submitButton.innerHTML = isUploading
                ? '<i class="fas fa-spinner fa-spin"></i> Enviando...'
                : '<i class="fas fa-paper-plane"></i> Publicar na galeria';
        }
        if (pickerButton) pickerButton.disabled = isUploading;
        if (dropzone) {
            dropzone.classList.toggle('opacity-60', isUploading);
            dropzone.classList.toggle('pointer-events-none', isUploading);
        }
    }

    function resetQueue() {
        queue = [];
        if (filesInput) filesInput.value = '';
        renderQueue();
        setProgress(0, 'Aguardando arquivos');
    }

    function openUploadModal() {
        if (!uploadModal) return;
        uploadModal.classList.remove('hidden');
        lockBody(true);
    }

    function closeUploadModal() {
        if (!uploadModal || isUploading) return;
        uploadModal.classList.add('hidden');
        lockBody(false);
    }

    function openLightbox(src, title) {
        if (!lightbox || !lightboxImage) return;
        lightboxImage.src = src;
        lightboxImage.alt = title || 'Foto da galeria';
        if (lightboxTitle) lightboxTitle.textContent = title || '';
        lightbox.classList.remove('hidden');
        lockBody(true);
    }

    function closeLightbox() {
        if (!lightbox || !lightboxImage) return;
        lightbox.classList.add('hidden');
        lightboxImage.src = '';
        lightboxImage.alt = '';
        if (lightboxTitle) lightboxTitle.textContent = '';
        if (!uploadModal || uploadModal.classList.contains('hidden')) lockBody(false);
    }

    function itemBadge(item) {
        if (item.state === 'done') return ['concluido', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'];
        if (item.state === 'error') return ['falhou', 'bg-rose-100 text-rose-700 dark:bg-rose-900/20 dark:text-rose-300'];
        if (item.state === 'uploading') return ['enviando', 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'];
        return ['imagem', 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'];
    }

    function renderQueue() {
        if (!selectedWrap || !selectedSummary || !selectedSize || !selectedList) return;
        if (queue.length === 0) {
            selectedWrap.classList.add('hidden');
            selectedSummary.textContent = '0 arquivo(s)';
            selectedSize.textContent = '0 B';
            selectedList.innerHTML = '';
            updateActionState();
            return;
        }
        const totalBytes = queue.reduce((sum, item) => sum + Number(item.file.size || 0), 0);
        selectedWrap.classList.remove('hidden');
        selectedSummary.textContent = `${queue.length} arquivo(s) selecionado(s)`;
        selectedSize.textContent = fmtBytes(totalBytes);
        selectedList.innerHTML = queue.map((item, index) => {
            const badge = itemBadge(item);
            const progress = Math.max(0, Math.min(100, Math.round(Number(item.progress || 0))));
            const barClass = item.state === 'error' ? 'bg-rose-500' : (item.state === 'done' ? 'bg-emerald-500' : 'bg-blue-600');
            return `
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm dark:bg-slate-950">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] ${badge[1]}">${badge[0]}</span>
                                <span class="truncate font-bold text-slate-700 dark:text-slate-200">${index + 1}. ${esc(item.file.name)}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                <span>${fmtBytes(item.file.size || 0)}</span>
                                <span>&bull;</span>
                                <span>${esc(item.remaining || 'pronto para iniciar')}</span>
                            </div>
                        </div>
                        <button type="button" class="gallery-remove-file inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-900/20" data-id="${item.id}" ${isUploading ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div class="h-full rounded-full ${barClass}" style="width:${progress}%"></div>
                    </div>
                    ${item.error ? `<p class="mt-2 text-xs font-semibold text-rose-500">${esc(item.error)}</p>` : ''}
                </div>
            `;
        }).join('');
        updateActionState();
    }

    function addFiles(fileList) {
        const incoming = Array.from(fileList || []);
        const rejected = [];
        incoming.forEach((file) => {
            const mime = String(file.type || '').toLowerCase();
            const name = String(file.name || '').toLowerCase();
            const isImage = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(mime) || /\.(jpe?g|png|webp)$/i.test(name);
            if (!isImage) {
                rejected.push(`${file.name} possui formato nao suportado. Use JPG, PNG ou WEBP.`);
                return;
            }
            if ((file.size || 0) > perFileLimitBytes) {
                rejected.push(`${file.name} excede o limite de ${fmtBytes(perFileLimitBytes)} por arquivo.`);
                return;
            }
            const signature = [file.name, file.size, file.lastModified].join('::');
            if (!queue.some((item) => item.signature === signature)) {
                queue.push({ id: `gallery-upload-${++seed}`, signature, file, progress: 0, state: 'ready', remaining: 'pronto para iniciar', error: '' });
            }
        });
        if (filesInput) filesInput.value = '';
        renderQueue();
        if (rejected.length > 0) {
            notify('warning', 'Arquivo recusado', rejected.length > 1 ? `${rejected[0]} Outros ${rejected.length - 1} arquivo(s) tambem foram recusados.` : rejected[0]);
        }
    }

    function extractError(xhr, payload) {
        let message = payload?.message || '';
        if (payload?.errors) message = Object.values(payload.errors).flat().join(' ');
        if (!message && xhr.responseText) message = stripHtml(xhr.responseText);
        if (!message) message = 'Falha ao enviar as fotos para a galeria.';
        if (xhr.status === 413) message = `O servidor recusou o arquivo por exceder o limite permitido de ${fmtBytes(perFileLimitBytes)}.`;
        if (xhr.status === 419) message = 'Sua sessao expirou. Recarregue a pagina e tente novamente.';
        if (xhr.status >= 500 && !payload?.message) message = 'O servidor encontrou um erro interno ao processar o upload.';
        return message;
    }

    function sendSingleFile(item, index, total) {
        const formData = new FormData();
        const startedAt = Date.now();
        if (csrfToken) formData.append('_token', csrfToken);
        formData.append('event_id', String(eventField?.value || '').trim());
        formData.append('files[]', item.file);
        item.state = 'uploading';
        item.progress = 0;
        item.error = '';
        item.remaining = 'calculando tempo restante...';
        renderQueue();
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadForm.action, true);
            if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', function (event) {
                const totalBytes = Number(event.total || item.file.size || 0);
                const loaded = Number(event.loaded || 0);
                const elapsed = Math.max((Date.now() - startedAt) / 1000, 0.2);
                const speed = loaded / elapsed;
                item.progress = Math.max(0, Math.min(100, Math.round((loaded / Math.max(totalBytes, 1)) * 100)));
                item.remaining = fmtRemaining(speed > 0 ? ((totalBytes - loaded) / speed) : 0);
                const processed = queue.reduce((sum, entry) => sum + (entry.state === 'done' || entry.state === 'error' ? 1 : (entry === item ? (item.progress / 100) : 0)), 0);
                setProgress((processed / total) * 100, `Enviando ${index + 1} de ${total}: ${item.file.name}`);
                renderQueue();
            });
            xhr.addEventListener('load', function () {
                let payload = null;
                try { payload = xhr.responseText ? JSON.parse(xhr.responseText) : null; } catch (error) { payload = null; }
                if (xhr.status >= 200 && xhr.status < 300 && payload && payload.success) {
                    resolve(payload);
                    return;
                }
                reject(new Error(extractError(xhr, payload)));
            });
            xhr.addEventListener('error', function () { reject(new Error('Falha de conexao durante o upload.')); });
            xhr.send(formData);
        });
    }

    document.querySelectorAll('[data-gallery-open-upload]').forEach((button) => button.addEventListener('click', openUploadModal));
    document.querySelectorAll('[data-gallery-close-upload]').forEach((button) => button.addEventListener('click', closeUploadModal));
    document.querySelectorAll('[data-gallery-close-lightbox]').forEach((button) => button.addEventListener('click', closeLightbox));
    document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', function () { openLightbox(this.dataset.lightboxSrc || '', this.dataset.lightboxTitle || ''); }));
    document.querySelectorAll('.gallery-delete-form').forEach((form) => {
        form.addEventListener('submit', function (event) {
            const prompt = 'Esta foto sera removida da galeria. Deseja continuar?';
            if (typeof Swal === 'undefined') {
                if (!window.confirm(prompt)) event.preventDefault();
                return;
            }
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Excluir foto?',
                text: prompt,
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (!isUploading) closeUploadModal();
        closeLightbox();
    });

    if (eventField) eventField.addEventListener('change', updateActionState);
    if (pickerButton && filesInput) pickerButton.addEventListener('click', function () { if (!isUploading) filesInput.click(); });
    if (filesInput) filesInput.addEventListener('change', function () { if (this.files.length > 0) addFiles(this.files); });

    if (dropzone && filesInput) {
        dropzone.addEventListener('click', function (event) {
            if (isUploading || event.target === filesInput || event.target.closest('#gallery-file-picker') || event.target.closest('.gallery-remove-file')) return;
            filesInput.click();
        });
        ['dragenter', 'dragover'].forEach((name) => dropzone.addEventListener(name, function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (!isUploading) dropzone.classList.add('is-dragover');
        }));
        ['dragleave', 'drop'].forEach((name) => dropzone.addEventListener(name, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('is-dragover');
        }));
        dropzone.addEventListener('drop', function (event) {
            const dropped = Array.from(event.dataTransfer?.files || []);
            if (!isUploading && dropped.length > 0) addFiles(dropped);
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.gallery-remove-file');
        if (!button || isUploading) return;
        const id = String(button.getAttribute('data-id') || '');
        if (!id) return;
        queue = queue.filter((item) => item.id !== id);
        renderQueue();
    });

    if (uploadForm) {
        uploadForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!String(eventField?.value || '').trim()) {
                notify('warning', 'Evento obrigatorio', 'Selecione o evento antes de enviar as fotos.');
                return;
            }
            if (queue.length === 0) {
                notify('warning', 'Fotos obrigatorias', 'Selecione pelo menos uma imagem para publicar.');
                return;
            }

            isUploading = true;
            updateActionState();
            setProgress(0, 'Preparando envio');

            const failures = [];
            const total = queue.length;
            for (let index = 0; index < total; index += 1) {
                const item = queue[index];
                try {
                    await sendSingleFile(item, index, total);
                    item.state = 'done';
                    item.progress = 100;
                    item.remaining = 'concluido';
                    item.error = '';
                    renderQueue();
                } catch (error) {
                    item.state = 'error';
                    item.progress = 100;
                    item.remaining = 'falhou';
                    item.error = error.message || 'Falha no upload.';
                    failures.push(`${item.file.name}: ${item.error}`);
                    renderQueue();
                }
            }

            isUploading = false;
            updateActionState();

            if (failures.length === 0) {
                setProgress(100, 'Upload concluido');
                await notify('success', 'Galeria atualizada', `${total} foto(s) publicada(s) com sucesso.`);
                resetQueue();
                closeUploadModal();
                window.location.reload();
                return;
            }

            queue = queue.filter((item) => item.state === 'error').map((item) => {
                item.state = 'ready';
                item.progress = 0;
                item.remaining = 'pronto para reenviar';
                return item;
            });
            renderQueue();
            setProgress(0, 'Envio concluido com pendencias');

            const summary = failures.length > 1 ? `${failures[0]} Outros ${failures.length - 1} arquivo(s) tambem falharam.` : failures[0];
            await notify(
                failures.length < total ? 'warning' : 'error',
                failures.length < total ? 'Upload concluido com ressalvas' : 'Upload recusado',
                failures.length < total ? `${total - failures.length} foto(s) foram publicadas. ${summary}` : summary
            );
        });
    }

    updateActionState();
    setProgress(0, 'Aguardando arquivos');
});
</script>
