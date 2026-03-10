<div class="drag-drop-container mb-3" id="upload-{{ $name }}">
    <label>{{ $label ?? 'Upload de Arquivo' }}</label>
    <div class="drag-drop-area border-2 border-dashed border-primary rounded p-4 text-center position-relative"
        style="cursor: pointer; transition: all 0.3s ease; background-color: #f8f9fa;">

        <input type="file" name="{{ $name }}" class="d-none file-input" accept="{{ $accept ?? 'image/*' }}">

        <!-- INITIAL STATE -->
        <div class="initial-view">
            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
            <h5 class="text-muted">Arraste e solte o arquivo aqui</h5>
            <p class="small text-muted">ou clique para selecionar</p>
            <p class="small text-muted mt-2">Formatos: {{ $formats ?? 'JPG, PNG, GIF' }}</p>
        </div>

        <!-- PREVIEW STATE -->
        <div class="preview-view d-none">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="" class="img-thumbnail preview-image" style="max-height: 80px; width: auto;">
                </div>
                <div class="col text-left">
                    <h6 class="file-name text-truncate mb-1" style="max-width: 200px;">Nome do Arquivo</h6>
                    <small class="file-size text-muted d-block">0 MB</small>
                    <small class="upload-status text-primary font-weight-bold">Aguardando...</small>

                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-danger remove-file" title="Remover / Cancelar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- EXISTING IMAGE PREVIEW (Optional, for when page loads) -->
        @if(isset($currentValue) && $currentValue)
            <div class="existing-view mt-3 pt-3 border-top {{ $currentValue ? '' : 'd-none' }}">
                <p class="small text-muted mb-2">Imagem Atual:</p>
                <img src="{{ $currentValue }}" class="img-fluid rounded" style="max-height: 100px;">
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const containerId = 'upload-{{ $name }}';
            const container = document.getElementById(containerId);
            if (!container) return;

            const input = container.querySelector('.file-input');
            const dropArea = container.querySelector('.drag-drop-area');
            const initialView = container.querySelector('.initial-view');
            const previewView = container.querySelector('.preview-view');
            const existingView = container.querySelector('.existing-view');

            // Elements for Preview
            const previewImg = container.querySelector('.preview-image');
            const fileName = container.querySelector('.file-name');
            const fileSize = container.querySelector('.file-size');
            const uploadStatus = container.querySelector('.upload-status');
            const progressBar = container.querySelector('.progress-bar');
            const removeBtn = container.querySelector('.remove-file');

            // Drag Events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => dropArea.classList.add('bg-white', 'border-success'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => dropArea.classList.remove('bg-white', 'border-success'), false);
            });

            // Handle Drop & Click
            dropArea.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files), false);
            dropArea.addEventListener('click', (e) => {
                if (e.target !== removeBtn && !removeBtn.contains(e.target)) input.click();
            });
            input.addEventListener('change', (e) => handleFiles(e.target.files));
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                resetUpload();
            });

            function handleFiles(files) {
                if (!files.length) return;
                const file = files[0];

                // Show Preview UI
                initialView.classList.add('d-none');
                if (existingView) existingView.classList.add('d-none');
                previewView.classList.remove('d-none');

                // Update Info
                fileName.textContent = file.name;
                fileSize.textContent = formatBytes(file.size);

                // Image Preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => previewImg.src = e.target.result;
                    reader.readAsDataURL(file);
                } else {
                    previewImg.src = ''; // Placeholder icon could go here
                }

                uploadFile(file);
            }

            function uploadFile(file) {
                const url = '{{ route("admin.settings.upload") }}';
                const formData = new FormData();
                formData.append('file', file);
                formData.append('key', '{{ $name }}');
                formData.append('_token', '{{ csrf_token() }}');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);

                const startTime = new Date().getTime();

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        progressBar.style.width = percent + '%';

                        // Estimate Time
                        const elapsedTime = (new Date().getTime() - startTime) / 1000;
                        const uploadSpeed = e.loaded / elapsedTime; // bytes per second
                        const remainingBytes = e.total - e.loaded;
                        const secondsRemaining = remainingBytes / uploadSpeed;

                        if (isFinite(secondsRemaining) && secondsRemaining > 0) {
                            uploadStatus.textContent = `Enviando... ${Math.ceil(secondsRemaining)}s restantes`;
                        } else {
                            uploadStatus.textContent = 'Processando...';
                        }
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const resp = JSON.parse(xhr.responseText);
                            if (resp.success) {
                                progressBar.classList.remove('bg-primary');
                                progressBar.classList.add('bg-success');
                                uploadStatus.textContent = 'Upload Concluído!';
                                uploadStatus.classList.replace('text-primary', 'text-success');

                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Sucesso!',
                                        text: 'Arquivo salvo com sucesso!',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    });
                                } else {
                                    toastr.success('Arquivo salvo com sucesso!');
                                }
                            } else {
                                throw new Error(resp.message || 'Erro desconhecido');
                            }
                        } catch (err) {
                            failUpload(err.message);
                        }
                    } else {
                        failUpload(`Erro ${xhr.status}: ${xhr.statusText}`);
                    }
                };

                xhr.onerror = () => failUpload('Erro de rede.');

                xhr.send(formData);
            }

            function failUpload(msg) {
                progressBar.classList.remove('bg-primary');
                progressBar.classList.add('bg-danger');
                progressBar.style.width = '100%';
                uploadStatus.textContent = 'Erro: ' + msg;
                uploadStatus.classList.replace('text-primary', 'text-danger');

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro no Upload',
                        text: msg,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Entendi'
                    });
                } else {
                    toastr.error(msg);
                }
            }

            function resetUpload() {
                input.value = '';
                previewView.classList.add('d-none');
                initialView.classList.remove('d-none');
                if (existingView) existingView.classList.remove('d-none');

                progressBar.style.width = '0%';
                progressBar.classList.remove('bg-success', 'bg-danger');
                progressBar.classList.add('bg-primary');
                uploadStatus.textContent = 'Aguardando...';
                uploadStatus.classList.remove('text-success', 'text-danger');
                uploadStatus.classList.add('text-primary');
            }

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
        });
    </script>
@endpush