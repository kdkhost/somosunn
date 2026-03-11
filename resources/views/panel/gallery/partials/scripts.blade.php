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

        document.querySelectorAll('[data-gallery-open-upload]').forEach((button) => {
            button.addEventListener('click', openUploadModal);
        });

        document.querySelectorAll('[data-gallery-close-upload]').forEach((button) => {
            button.addEventListener('click', closeUploadModal);
        });

        document.querySelectorAll('[data-gallery-close-lightbox]').forEach((button) => {
            button.addEventListener('click', closeLightbox);
        });

        document.querySelectorAll('[data-lightbox-src]').forEach((button) => {
            button.addEventListener('click', function () {
                openLightbox(this.dataset.lightboxSrc || '', this.dataset.lightboxTitle || '');
            });
        });

        document.querySelectorAll('.gallery-delete-form').forEach((form) => {
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
        });

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
                        notify('success', 'Galeria atualizada', payload.message || 'As fotos foram publicadas com sucesso.')
                            .then(function () {
                                window.location.reload();
                            });
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
