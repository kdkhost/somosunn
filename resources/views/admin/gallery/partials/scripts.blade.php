<script>
    $(document).ready(function() {
        $('.select2-modal').select2({
            dropdownParent: $('#uploadModal')
        });

        const $form = $('#adminUploadForm');
        const $fileInput = $('#adminFileInput');
        const $dropzone = $('#adminDropzone');
        const $submitBtn = $('#adminSubmitBtn');
        const $progressBar = $('#adminProgressBar');
        const $progressLabel = $('#adminProgressLabel');
        const $progressValue = $('#adminProgressValue');
        const $selectedFiles = $('#adminSelectedFiles');
        const $selectedSummary = $('#adminSelectedSummary');
        const $selectedSize = $('#adminSelectedSize');
        const $selectedList = $('#adminSelectedList');
        const $inlinePreview = $('#adminInlinePreview');
        const $inlinePreviewGrid = $('#adminInlinePreviewGrid');
        const fileInputElement = document.getElementById('adminFileInput');
        let previewObjectUrls = [];

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function syncSelectedFiles(files) {
            if (!fileInputElement) {
                return;
            }

            if (typeof DataTransfer === 'undefined') {
                return;
            }

            const transfer = new DataTransfer();
            files.forEach((file) => transfer.items.add(file));
            fileInputElement.files = transfer.files;
        }

        function formatBytes(bytes) {
            if (!bytes) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const value = bytes / Math.pow(1024, exponent);

            return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
        }

        function updateSelectedFiles() {
            const files = Array.from(fileInputElement?.files || []);
            previewObjectUrls.forEach((url) => URL.revokeObjectURL(url));
            previewObjectUrls = [];

            if (files.length === 0) {
                $selectedFiles.addClass('d-none');
                $selectedSummary.text('');
                $selectedSize.text('');
                $selectedList.html('');
                $inlinePreview.addClass('d-none');
                $inlinePreviewGrid.html('');
                return;
            }

            const totalBytes = files.reduce((sum, file) => sum + (file.size || 0), 0);

            $selectedFiles.removeClass('d-none');
            $selectedSummary.text(`${files.length} arquivo(s) selecionado(s)`);
            $selectedSize.text(formatBytes(totalBytes));
            $selectedList.html(files.map((file, index) => {
                const previewUrl = URL.createObjectURL(file);
                previewObjectUrls.push(previewUrl);

                return `
                    <div class="gallery-admin-selected-item d-flex justify-content-between align-items-center bg-white rounded px-3 py-2 mb-2 border">
                        <div class="d-flex align-items-center gallery-admin-selected-item">
                            <div class="gallery-admin-selected-preview">
                                <img src="${previewUrl}" alt="${escapeHtml(file.name)}">
                            </div>
                            <div class="min-width-0">
                                <p class="font-weight-bold text-dark text-truncate mb-1">${index + 1}. ${escapeHtml(file.name)}</p>
                                <p class="small text-muted mb-0">${formatBytes(file.size || 0)}</p>
                            </div>
                        </div>
                        <span class="badge badge-light text-muted">${(file.type || 'imagem').replace('image/', '').toUpperCase()}</span>
                    </div>
                `;
            }).join(''));
            $inlinePreview.removeClass('d-none');
            $inlinePreviewGrid.html(files.map((file, index) => {
                const previewUrl = previewObjectUrls[index];

                return `
                    <div class="gallery-admin-inline-preview-item" title="${escapeHtml(file.name)}">
                        <img src="${previewUrl}" alt="${escapeHtml(file.name)}">
                    </div>
                `;
            }).join(''));
        }

        function setProgress(percent, label) {
            const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
            $progressBar.css('width', `${safePercent}%`);
            $progressValue.text(`${safePercent}%`);
            $progressLabel.text(label);
        }

        function appendFiles(files) {
            const incomingFiles = Array.from(files || []);
            if (incomingFiles.length === 0 || !fileInputElement) {
                return;
            }

            const currentFiles = Array.from(fileInputElement.files || []);
            syncSelectedFiles(currentFiles.concat(incomingFiles));
            updateSelectedFiles();
        }

        window.adminGallerySyncSelection = function(files) {
            const selected = Array.from(files || []);
            if (selected.length === 0) {
                return;
            }

            syncSelectedFiles(selected);
            updateSelectedFiles();
        };

        $dropzone.on('click', function(e) {
            if ($(e.target).closest('input, label, button, a').length) {
                return;
            }

            fileInputElement?.click();
        });

        $dropzone.on('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') {
                return;
            }

            e.preventDefault();
            fileInputElement?.click();
        });

        $fileInput.on('change', updateSelectedFiles);
        fileInputElement?.addEventListener('change', updateSelectedFiles);

        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $dropzone.on('drop', function(e) {
            const droppedFiles = Array.from(e.originalEvent.dataTransfer.files || []);
            if (droppedFiles.length === 0) {
                return;
            }

            appendFiles(droppedFiles);
        });

        $form.on('submit', function(e) {
            e.preventDefault();

            if (!fileInputElement || fileInputElement.files.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Atencao', text: 'Selecione pelo menos uma imagem.' });
                return;
            }

            const formData = new FormData(this);
            const originalText = $submitBtn.html();
            const xhr = new XMLHttpRequest();

            xhr.open('POST', this.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> ENVIANDO...');
            setProgress(0, 'Preparando envio');

            xhr.upload.addEventListener('progress', function(progressEvent) {
                if (!progressEvent.lengthComputable) {
                    return;
                }

                const percent = (progressEvent.loaded / progressEvent.total) * 100;
                setProgress(percent, 'Transferindo arquivos');
            });

            xhr.addEventListener('load', function() {
                $submitBtn.prop('disabled', false).html(originalText);

                let payload = null;
                try {
                    payload = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                } catch (error) {
                    payload = null;
                }

                if (xhr.status >= 200 && xhr.status < 300 && payload && payload.success) {
                    setProgress(100, 'Upload concluido. Fechando modal...');
                    $('#uploadModal').one('hidden.bs.modal', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Galeria atualizada',
                            text: payload.message || 'As imagens foram publicadas com sucesso.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    }).modal('hide');

                    return;
                }

                setProgress(0, 'Falha no envio');
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: payload?.message || 'Falha ao realizar upload.'
                });
            });

            xhr.addEventListener('error', function() {
                $submitBtn.prop('disabled', false).html(originalText);
                setProgress(0, 'Erro de conexao');
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de conexao',
                    text: 'Nao foi possivel concluir o upload. Tente novamente.'
                });
            });

            xhr.send(formData);
        });

        $('#uploadModal').on('hidden.bs.modal', function() {
            $form[0].reset();
            $selectedFiles.addClass('d-none');
            $selectedSummary.text('');
            $selectedSize.text('');
            $selectedList.html('');
            $inlinePreview.addClass('d-none');
            $inlinePreviewGrid.html('');
            previewObjectUrls.forEach((url) => URL.revokeObjectURL(url));
            previewObjectUrls = [];
            setProgress(0, 'Aguardando selecao dos arquivos.');
            $dropzone.removeClass('dragover');
        });

        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const title = $(this).data('confirm-title') || 'Tem certeza?';
            const text = $(this).data('confirm-text') || 'Esta acao nao podera ser desfeita.';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
