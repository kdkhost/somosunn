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
        let galleryGrid = document.getElementById('panel-gallery-grid');
        let galleryEmptyState = document.getElementById('panel-gallery-empty-state');
        const totalValue = document.getElementById('panel-gallery-total-value');
        const selectedFilter = document.getElementById('gallery-event-filter');

        function setBodyLocked(locked) {
            body.classList.toggle('overflow-hidden', locked);
        }

        function openUploadModal() {
            if (!uploadModal) {
                return;
            }

            uploadModal.classList.remove('hidden');
            setBodyLocked(true);
        }

        function closeUploadModal() {
            if (!uploadModal) {
                return;
            }

            uploadModal.classList.add('hidden');
            setBodyLocked(false);
        }

        function openLightbox(src, title) {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightboxImage.src = src;
            lightboxImage.alt = title || 'Foto da galeria';
            if (lightboxTitle) {
                lightboxTitle.textContent = title || '';
            }

            lightbox.classList.remove('hidden');
            setBodyLocked(true);
        }

        function closeLightbox() {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightbox.classList.add('hidden');
            lightboxImage.src = '';
            lightboxImage.alt = '';
            if (lightboxTitle) {
                lightboxTitle.textContent = '';
            }
            setBodyLocked(false);
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

        function updateSelectedFiles() {
            if (!filesInput || !selectedWrap || !selectedSummary || !selectedList || !selectedSize) {
                return;
            }

            const files = Array.from(filesInput.files || []);
            if (files.length === 0) {
                selectedWrap.classList.add('hidden');
                selectedSummary.textContent = '';
                selectedSize.textContent = '';
                selectedList.innerHTML = '';
                return;
            }

            const totalBytes = files.reduce((sum, file) => sum + (file.size || 0), 0);
            selectedWrap.classList.remove('hidden');
            selectedSummary.textContent = `${files.length} arquivo(s) selecionado(s)`;
            selectedSize.textContent = formatBytes(totalBytes);
            selectedList.innerHTML = files.map((file, index) => `
                <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-950">
                    <span class="truncate font-bold text-slate-700 dark:text-slate-200">${index + 1}. ${file.name}</span>
                    <span class="shrink-0 text-xs font-black uppercase tracking-[0.14em] text-slate-400">${formatBytes(file.size || 0)}</span>
                </div>
            `).join('');
        }

        function setUploadProgress(percent, label) {
            if (!progressBar || !progressLabel || !progressValue) {
                return;
            }

            const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
            progressBar.style.width = `${safePercent}%`;
            progressLabel.textContent = label;
            progressValue.textContent = `${safePercent}%`;
        }

        function notify(type, title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    icon: type,
                    title: title,
                    text: text,
                    confirmButtonText: 'OK'
                });
            }

            alert(`${title}\n\n${text}`);
            return Promise.resolve();
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function bindLightboxTrigger(button) {
            if (!button) {
                return;
            }

            button.addEventListener('click', function () {
                openLightbox(this.dataset.lightboxSrc || '', this.dataset.lightboxTitle || '');
            });
        }

        function bindDeleteForm(form) {
            if (!form) {
                return;
            }

            form.addEventListener('submit', function (event) {
                const prompt = 'Esta foto sera removida da galeria. Deseja continuar?';

                if (typeof Swal === 'undefined') {
                    if (!window.confirm(prompt)) {
                        event.preventDefault();
                    }
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }

        function ensureGalleryGrid() {
            if (galleryGrid) {
                return galleryGrid;
            }

            if (!galleryEmptyState) {
                return null;
            }

            const section = document.createElement('section');
            section.className = 'space-y-5';
            section.innerHTML = `
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">Colecao</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Painel de fotos publicadas</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Novas midias publicadas aparecem aqui imediatamente, sem recarregar a tela.</p>
                    </div>
                </div>
                <div id="panel-gallery-grid" class="grid gap-6 lg:grid-cols-2 2xl:grid-cols-3"></div>
            `;

            galleryEmptyState.replaceWith(section);
            galleryGrid = section.querySelector('#panel-gallery-grid');
            galleryEmptyState = null;

            return galleryGrid;
        }

        function shouldAppendUploadedMedia(eventId) {
            if (!selectedFilter || !selectedFilter.value) {
                return true;
            }

            return String(selectedFilter.value) === String(eventId || '');
        }

        function renderPanelGalleryCard(item) {
            const avatarMarkup = item.owner_avatar
                ? `<img src="${escapeHtml(item.owner_avatar)}" alt="${escapeHtml(item.owner_name)}" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">`
                : `<span class="flex h-full w-full items-center justify-center text-sm font-black uppercase">${escapeHtml((item.owner_name || 'S').trim().charAt(0) || 'S')}</span>`;

            const deleteMarkup = item.can_delete
                ? `
                    <form method="POST" action="${escapeHtml(item.delete_url)}" class="gallery-delete-form shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-300">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                `
                : '';

            return `
                <article class="group overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white shadow-sm shadow-slate-200/60 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-950">
                        <button type="button"
                            data-lightbox-src="${escapeHtml(item.url)}"
                            data-lightbox-title="${escapeHtml(item.event_title)}"
                            class="h-full w-full text-left">
                            <img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.event_title)}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent opacity-90"></div>
                            <div class="absolute left-5 right-5 top-5 flex items-start justify-between gap-4">
                                <span class="rounded-full border border-white/10 bg-slate-950/55 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                    ${escapeHtml(item.event_title)}
                                </span>
                                ${item.watermarked ? '<span class="rounded-full border border-cyan-400/25 bg-cyan-400/12 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-cyan-100 backdrop-blur">Watermark</span>' : ''}
                            </div>
                            <div class="absolute inset-x-5 bottom-5 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-200">Abrir foto</p>
                                    <p class="mt-1 text-lg font-black text-white">${escapeHtml(item.owner_name)}</p>
                                </div>
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition group-hover:bg-white/20">
                                    <i class="fas fa-up-right-and-down-left-from-center"></i>
                                </span>
                            </div>
                        </button>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-400 text-white shadow-lg shadow-blue-500/20">
                                    ${avatarMarkup}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900 dark:text-white">${escapeHtml(item.owner_name)}</p>
                                    <p class="mt-1 truncate text-xs font-medium text-slate-500 dark:text-slate-400">Enviado em ${escapeHtml(item.uploaded_at || '--')}</p>
                                </div>
                            </div>
                            ${deleteMarkup}
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-[1.4rem] bg-slate-50 px-4 py-3 dark:bg-slate-950">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Evento</p>
                                <p class="mt-1 line-clamp-2 font-bold text-slate-800 dark:text-slate-100">${escapeHtml(item.event_title)}</p>
                            </div>
                            <div class="rounded-[1.4rem] bg-slate-50 px-4 py-3 dark:bg-slate-950">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Data base</p>
                                <p class="mt-1 font-bold text-slate-800 dark:text-slate-100">${escapeHtml(item.event_date || '--/--/----')}</p>
                            </div>
                        </div>
                    </div>
                </article>
            `;
        }

        document.querySelectorAll('[data-gallery-open-upload]').forEach((button) => {
            button.addEventListener('click', openUploadModal);
        });

        document.querySelectorAll('[data-gallery-close-upload]').forEach((button) => {
            button.addEventListener('click', closeUploadModal);
        });

        document.querySelectorAll('[data-gallery-close-lightbox]').forEach((button) => {
            button.addEventListener('click', closeLightbox);
        });

        document.querySelectorAll('[data-lightbox-src]').forEach(bindLightboxTrigger);
        document.querySelectorAll('.gallery-delete-form').forEach(bindDeleteForm);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeUploadModal();
                closeLightbox();
            }
        });

        if (pickerButton && filesInput) {
            pickerButton.addEventListener('click', function () {
                filesInput.click();
            });
        }

        if (filesInput) {
            filesInput.addEventListener('change', updateSelectedFiles);
        }

        if (dropzone && filesInput) {
            dropzone.addEventListener('click', function (event) {
                if (event.target === filesInput || event.target.closest('#gallery-file-picker')) {
                    return;
                }

                filesInput.click();
            });

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    dropzone.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', function (event) {
                const droppedFiles = Array.from(event.dataTransfer?.files || []);
                if (droppedFiles.length === 0) {
                    return;
                }

                const transfer = new DataTransfer();
                droppedFiles.forEach((file) => transfer.items.add(file));
                filesInput.files = transfer.files;
                updateSelectedFiles();
            });
        }

        if (uploadForm) {
            uploadForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const eventField = uploadForm.querySelector('[name="event_id"]');
                const hasFiles = filesInput && filesInput.files && filesInput.files.length > 0;

                if (!eventField || !eventField.value) {
                    notify('warning', 'Evento obrigatorio', 'Selecione o evento antes de enviar as fotos.');
                    return;
                }

                if (!hasFiles) {
                    notify('warning', 'Fotos obrigatorias', 'Selecione pelo menos uma imagem para publicar.');
                    return;
                }

                const formData = new FormData(uploadForm);
                const originalButtonHtml = submitButton ? submitButton.innerHTML : '';
                const xhr = new XMLHttpRequest();

                xhr.open('POST', uploadForm.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                }

                setUploadProgress(0, 'Preparando envio');

                xhr.upload.addEventListener('progress', function (progressEvent) {
                    if (!progressEvent.lengthComputable) {
                        return;
                    }

                    const percent = (progressEvent.loaded / progressEvent.total) * 100;
                    setUploadProgress(percent, 'Transferindo arquivos');
                });

                xhr.addEventListener('load', function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }

                    let payload = null;
                    try {
                        payload = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                    } catch (error) {
                        payload = null;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && payload && payload.success) {
                        setUploadProgress(100, 'Upload concluido');
                        closeUploadModal();
                        if (shouldAppendUploadedMedia(eventField.value) && Array.isArray(payload.media) && payload.media.length > 0) {
                            const grid = ensureGalleryGrid();
                            if (grid) {
                                payload.media.slice().reverse().forEach(function (item) {
                                    const wrapper = document.createElement('div');
                                    wrapper.innerHTML = renderPanelGalleryCard(item);
                                    const card = wrapper.firstElementChild;

                                    if (card) {
                                        grid.prepend(card);
                                        card.querySelectorAll('[data-lightbox-src]').forEach(bindLightboxTrigger);
                                        card.querySelectorAll('.gallery-delete-form').forEach(bindDeleteForm);
                                    }
                                });
                            }
                        }

                        if (totalValue && payload?.stats?.visible_total !== undefined) {
                            totalValue.textContent = String(payload.stats.visible_total);
                        }

                        notify('success', 'Galeria atualizada', payload.message || 'As fotos foram publicadas com sucesso.');
                        return;
                    }

                    const errorMessage = payload?.message || 'Falha ao enviar as fotos para a galeria.';
                    setUploadProgress(0, 'Falha no envio');
                    notify('error', 'Upload recusado', errorMessage);
                });

                xhr.addEventListener('error', function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }

                    setUploadProgress(0, 'Erro de conexao');
                    notify('error', 'Erro de conexao', 'Nao foi possivel concluir o upload. Tente novamente.');
                });

                xhr.send(formData);
            });
        }
    });
</script>
