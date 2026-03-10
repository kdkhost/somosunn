<div class="card card-outline card-primary premium-upload-box" id="media-uploader">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-layer-group mr-2 text-primary"></i>Central de Mídias</h3>
        <span class="badge badge-info shadow-sm">Máx. 50MB</span>
    </div>
    <div class="card-body">
        <div class="drop-zone-area p-5 text-center mb-4 transition-all" id="dropzone">
            <div class="dz-message d-flex flex-column align-items-center">
                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 opacity-50"></i>
                <h5 class="font-weight-bold mb-1">Arraste seus arquivos para aqui</h5>
                <p class="text-muted small">ou clique para explorar seu computador</p>
                <button class="btn btn-primary btn-sm rounded-pill px-4 mt-2 shadow-sm" id="selectFilesBtn">
                    <i class="fas fa-search mr-1"></i> Selecionar Arquivos
                </button>
            </div>
            <input type="file" id="fileInput" class="d-none" multiple>
        </div>

        <div id="uploadList" class="upload-list-container"></div>
    </div>
    <div class="card-footer bg-light py-2">
        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Suporte para Imagens, Vídeos, Áudios e
            Documentos (PDF, DOCX, XLSX).</small>
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-crop mr-2"></i>Ajustar Imagem</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center"
                style="min-height: 400px;">
                <div class="w-100 h-100">
                    <img id="cropImage" src="" class="img-fluid" alt="Preview" style="max-height: 70vh;">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                    data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="cropConfirmBtn">
                    <i class="fas fa-check mr-1"></i> Confirmar & Enviar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const dz = document.getElementById('dropzone');
            const uploaderBox = document.getElementById('media-uploader');
            const input = document.getElementById('fileInput');
            const list = document.getElementById('uploadList');
            const btn = document.getElementById('selectFilesBtn');
            const maxSize = 50 * 1024 * 1024;
            const allowed = ['image/', 'video/', 'audio/', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            let cropper = null;
            let pendingRow = null;

            const renderItem = (file, previewUrl = null) => {
                const row = document.createElement('div');
                row.className = 'upload-item-row mb-3 animate__animated animate__fadeInUp';
                const isImage = file.type.startsWith('image/');
                const preview = isImage && previewUrl ? `<img src="${previewUrl}" class="rounded shadow-sm border mr-3" style="width:54px;height:54px;object-fit:cover;">` : `<div class="rounded border bg-light d-flex align-items-center justify-content-center mr-3" style="width:54px;height:54px;"><i class="fas fa-file-alt text-muted fa-lg"></i></div>`;

                row.innerHTML = `
                <div class="d-flex align-items-center justify-content-between bg-white border rounded shadow-xs p-3 transition-all">
                    <div class="d-flex align-items-center min-w-0" style="flex: 1;">
                        ${preview}
                        <div class="text-truncate mr-3">
                            <div class="font-weight-bold text-dark text-truncate small">${file.name}</div>
                            <div class="text-muted x-small">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                        </div>
                    </div>
                    <div class="flex-grow-1 mx-4 d-none d-md-block">
                        <div class="progress rounded-pill overflow-hidden" style="height:8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:0%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="x-small text-muted status">Aguardando...</span>
                            <span class="x-small text-muted progress-label">0%</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        ${isImage ? '<button class="btn btn-xs btn-outline-primary rounded-circle crop-btn p-1" style="width:28px;height:28px;" title="Cortar"><i class="fas fa-crop-alt"></i></button>' : ''}
                        <button class="btn btn-xs btn-outline-danger rounded-circle remove-item-btn p-1" style="width:28px;height:28px;" title="Remover"><i class="fas fa-times"></i></button>
                    </div>
                </div>`;
                return row;
            };

            const uploadFile = (file, row) => {
                const bar = row.querySelector('.progress-bar');
                const status = row.querySelector('.status');
                const label = row.querySelector('.progress-label');
                const form = new FormData();
                form.append('file', file);
                status.textContent = 'Iniciando upload...';

                axios.post('/upload', form, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (e) => {
                        const pct = Math.round((e.loaded * 100) / e.total);
                        bar.style.width = pct + '%';
                        label.textContent = pct + '%';
                        if (pct === 100) status.textContent = 'Processando...';
                    }
                }).then((response) => {
                    bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    bar.classList.replace('bg-primary', 'bg-success');
                    status.textContent = 'Concluído com sucesso!';
                    status.classList.add('text-success');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Upload concluído!',
                            text: `Arquivo ${file.name} salvo.`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                }).catch((err) => {
                    bar.classList.replace('bg-primary', 'bg-danger');
                    bar.style.width = '100%';
                    status.textContent = 'Falha no envio';
                    status.classList.add('text-danger');

                    const errorMsg = err.response?.data?.message || 'Erro inesperado no servidor.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Falha no Upload',
                            text: `Erro ao enviar ${file.name}: ${errorMsg}`,
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            };

            const openCropperModal = (file, row, previewUrl) => {
                const img = document.getElementById('cropImage');
                img.src = previewUrl;
                $('#cropModal').modal('show');
                pendingRow = { file, row };
                if (cropper) { cropper.destroy(); }
                cropper = new Cropper(img, {
                    viewMode: 1,
                    autoCropArea: 0.8,
                    responsive: true,
                    background: false,
                    movable: true,
                    rotatable: true,
                    scalable: true
                });
            };

            const handleFiles = (files) => {
                Array.from(files).forEach(file => {
                    if (!allowed.some(type => file.type.startsWith(type)) && !allowed.includes(file.type)) {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Arquivo não permitido', text: `O tipo ${file.type} não é suportado.` });
                        return;
                    }
                    if (file.size > maxSize) {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Arquivo muito grande', text: `O arquivo ${file.name} excede o limite de 50MB.` });
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const row = renderItem(file, e.target.result);
                        list.prepend(row); // Adiciona no topo

                        const cropBtn = row.querySelector('.crop-btn');
                        const removeBtn = row.querySelector('.remove-item-btn');

                        removeBtn.addEventListener('click', () => row.remove());

                        if (cropBtn) {
                            cropBtn.addEventListener('click', (ev) => {
                                ev.preventDefault();
                                openCropperModal(file, row, e.target.result);
                            });
                        } else {
                            uploadFile(file, row);
                        }
                    };
                    reader.readAsDataURL(file);
                });
            };

            dz.addEventListener('dragover', e => { e.preventDefault(); uploaderBox.classList.add('dragover'); });
            dz.addEventListener('dragleave', e => { uploaderBox.classList.remove('dragover'); });
            dz.addEventListener('drop', e => {
                e.preventDefault();
                uploaderBox.classList.remove('dragover');
                if (e.dataTransfer.files.length) handleFiles(e.dataTransfer.files);
            });

            dz.addEventListener('click', (e) => {
                if (e.target.id === 'selectFilesBtn' || e.target.closest('#selectFilesBtn')) return;
                input.click();
            });

            btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); input.click(); });
            input.addEventListener('change', () => { if (input.files.length) handleFiles(input.files); });

            document.getElementById('cropConfirmBtn').addEventListener('click', () => {
                if (!cropper || !pendingRow) return;
                cropper.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1080 }).toBlob(blob => {
                    const croppedFile = new File([blob], pendingRow.file.name, { type: pendingRow.file.type });
                    uploadFile(croppedFile, pendingRow.row);
                    $('#cropModal').modal('hide');
                    cropper.destroy(); cropper = null; pendingRow = null;
                }, pendingRow.file.type, 0.9);
            });
        })();
    </script>
@endpush