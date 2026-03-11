@php
    use App\Support\UploadStorage;
    use Illuminate\Support\Str;

    $instance = 'upload-global-' . Str::slug($name ?? 'file', '-') . '-' . Str::random(6);
    $label = $label ?? 'Upload de arquivo, imagem, vídeo ou áudio';
    $help = $help ?? 'O envio começa automaticamente ao selecionar o arquivo.';
    $existing = $value ?? null;
    $existingPath = $path ?? $existing;
    $previewUrl = $preview_url ?? $existing;
    $removeName = $remove_name ?? null;
    $maxSizeBytes = (int) ($max_size ?? 0);
    $chunkSizeBytes = UploadStorage::recommendedChunkSizeBytes($maxSizeBytes > 0 ? $maxSizeBytes : null);
@endphp

<div class="upload-global-wrapper" id="{{ $instance }}" data-upload-global-instance="{{ $instance }}"
    data-upload-max-size="{{ $maxSizeBytes }}"
    data-upload-chunk-size="{{ $chunkSizeBytes }}">
    @if($label)
        <label class="font-weight-bold mb-2 d-block">{{ $label }}</label>
    @endif

    <div class="upload-drop-area @if($previewUrl) d-none @endif" data-upload-drop-area>
        <div class="upload-icon mb-2">
            <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
        </div>
        <span class="d-block mb-2 font-weight-medium">Arraste e solte arquivos aqui</span>
        <span class="text-muted small d-block mb-3">ou clique para selecionar</span>

        <input type="file" id="{{ $instance }}-input" accept="{{ $accept ?? '*' }}" data-upload-input hidden>
        <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm" data-upload-trigger>Selecionar
            Arquivo</button>

        @if($help)
            <p class="upload-global-help text-muted small mb-0 mt-3">{{ $help }}</p>
        @endif
    </div>

    {{-- Progresso --}}
    <div class="upload-progress-container d-none mt-3" data-upload-progress-container>
        <div class="d-flex justify-content-between mb-1 small font-weight-bold">
            <span class="text-primary" data-upload-status>Enviando...</span>
            <span class="text-muted" data-upload-percent>0%</span>
        </div>
        <div class="progress progress-sm shadow-sm" style="height: 8px; border-radius: 4px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                style="width: 0%" data-upload-progress-bar></div>
        </div>
        <div class="d-flex justify-content-between mt-2 small text-muted">
            <span data-upload-speed>0 KB/s</span>
            <span data-upload-time-remaining>Calculando tempo...</span>
        </div>
    </div>

    {{-- Preview --}}
    <div class="upload-preview-container @if(!$previewUrl) d-none @endif mt-3" data-upload-preview-container>
        <div class="upload-preview-card p-2 rounded border bg-white shadow-sm position-relative">
            <div class="upload-preview-content mb-2 text-center" data-upload-preview-content>
                @if($previewUrl)
                    @php
                        $ext = strtolower(pathinfo($existingPath ?: $previewUrl, PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                        $isVid = in_array($ext, ['mp4', 'webm', 'ogg']);
                        $isAud = in_array($ext, ['mp3', 'wav', 'ogg']);
                    @endphp
                    @if($isImg)
                        <img src="{{ $previewUrl }}" class="img-fluid rounded" style="max-height: 200px;">
                    @elseif($isVid)
                        <video src="{{ $previewUrl }}" controls class="w-100 rounded" style="max-height: 200px;"></video>
                    @elseif($isAud)
                        <audio src="{{ $previewUrl }}" controls class="w-100"></audio>
                    @else
                        <div class="p-4 bg-light rounded text-muted">
                            <i class="fas fa-file-alt fa-3x mb-2"></i>
                            <div class="small text-truncate">{{ basename($existingPath ?: $previewUrl) }}</div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded">
                <div class="text-truncate small pr-3" data-upload-filename>
                    {{ $existingPath ? basename($existingPath) : 'Arquivo enviado' }}
                </div>
                <button type="button" class="btn btn-xs btn-danger" data-upload-clear title="Remover">
                    <i class="fas fa-trash-alt mr-1"></i> Remover
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden Input para o formulário --}}
    <input type="hidden" name="{{ $name }}" value="{{ $existingPath }}" data-upload-path-input>
    @if($removeName)
        <input type="hidden" name="{{ $removeName }}" value="0" data-upload-remove-input>
    @endif
</div>

<script>
    (function () {
        const instanceId = "{{ $instance }}";
        const root = document.getElementById(instanceId);
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const dropArea = root.querySelector('[data-upload-drop-area]');
        const input = root.querySelector('[data-upload-input]');
        const trigger = root.querySelector('[data-upload-trigger]');
        const progressContainer = root.querySelector('[data-upload-progress-container]');
        const progressBar = root.querySelector('[data-upload-progress-bar]');
        const percentText = root.querySelector('[data-upload-percent]');
        const statusText = root.querySelector('[data-upload-status]');
        const speedText = root.querySelector('[data-upload-speed]');
        const timeText = root.querySelector('[data-upload-time-remaining]');
        const previewContainer = root.querySelector('[data-upload-preview-container]');
        const previewContent = root.querySelector('[data-upload-preview-content]');
        const filenameText = root.querySelector('[data-upload-filename]');
        const pathInput = root.querySelector('[data-upload-path-input]');
        const removeInput = root.querySelector('[data-upload-remove-input]');
        const clearBtn = root.querySelector('[data-upload-clear]');

        let startTime, uploadedBytes = 0;
        const CHUNK_SIZE = Math.max(262144, parseInt(root.dataset.uploadChunkSize || '1048576', 10));
        const maxSizeBytes = parseInt(root.dataset.uploadMaxSize || '0', 10);

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function blobToDataUrl(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('Falha ao preparar o chunk para envio.'));
                reader.readAsDataURL(blob);
            });
        }

        function shouldRetryChunkAsJson(response) {
            const contentType = response.headers.get('content-type') || '';
            return response.status === 403 && !contentType.includes('application/json');
        }

        async function parseUploadResponse(response, fallbackMessage) {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const json = await response.json();
                if (!response.ok) {
                    throw new Error(json.error || json.message || fallbackMessage);
                }

                return json;
            }

            const text = await response.text();
            if (!response.ok) {
                throw new Error((text || fallbackMessage).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim());
            }

            return { ok: true, raw: text };
        }

        async function sendChunkAsBase64(chunk, uploadId, chunkIndex, totalChunks) {
            const chunkData = await blobToDataUrl(chunk);

            return fetch("{{ route('admin.upload.chunk') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    upload_id: uploadId,
                    chunk_index: chunkIndex,
                    total_chunks: totalChunks,
                    chunk_data: chunkData
                })
            });
        }

        async function uploadFile(file) {
            if (maxSizeBytes > 0 && file.size > maxSizeBytes) {
                Swal.fire({
                    icon: 'error',
                    title: 'Arquivo muito grande',
                    text: 'A imagem nao pode ultrapassar ' + formatBytes(maxSizeBytes) + '.'
                });
                return;
            }

            dropArea.classList.add('d-none');
            progressContainer.classList.remove('d-none');
            previewContainer.classList.add('d-none');

            startTime = Date.now();
            uploadedBytes = 0;
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            const uploadId = 'up_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

            try {
                for (let i = 0; i < totalChunks; i++) {
                    const start = i * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('upload_id', uploadId);
                    formData.append('chunk_index', i);
                    formData.append('total_chunks', totalChunks);

                    let chunkResponse = await fetch("{{ route('admin.upload.chunk') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (shouldRetryChunkAsJson(chunkResponse)) {
                        chunkResponse = await sendChunkAsBase64(chunk, uploadId, i, totalChunks);
                    }

                    await parseUploadResponse(chunkResponse, 'Falha ao enviar uma parte do arquivo.');

                    uploadedBytes += chunk.size;
                    updateProgress(uploadedBytes, file.size);
                }

                // Finalizar (Assemble)
                statusText.textContent = "Finalizando...";
                const res = await fetch("{{ route('admin.upload.assemble') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ upload_id: uploadId, filename: file.name, total_chunks: totalChunks })
                });

                const data = await parseUploadResponse(res, 'Falha ao finalizar o upload.');
                if (data.ok) {
                    showPreview(file, data.url, data.path);
                    Swal.fire({ icon: 'success', title: 'Upload concluído!', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                } else {
                    throw new Error(data.error || 'Erro no processamento');
                }
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Erro no upload', text: err.message });
                resetUploader();
            }
        }

        function updateProgress(uploaded, total) {
            const percent = Math.round((uploaded / total) * 100);
            progressBar.style.width = percent + '%';
            percentText.textContent = percent + '%';

            const elapsed = (Date.now() - startTime) / 1000;
            const speed = uploaded / elapsed;
            speedText.textContent = formatBytes(speed) + '/s';

            if (speed > 0) {
                const remaining = (total - uploaded) / speed;
                const m = Math.floor(remaining / 60);
                const s = Math.round(remaining % 60);
                timeText.textContent = `Restante: ${m > 0 ? m + 'm ' : ''}${s}s`;
            }
        }

        function showPreview(file, url, path) {
            progressContainer.classList.add('d-none');
            previewContainer.classList.remove('d-none');
            pathInput.value = path;
            if (removeInput) removeInput.value = '0';
            filenameText.textContent = file.name;

            const type = file.type;
            let html = '';
            if (type.startsWith('image/')) html = `<img src="${url}" class="img-fluid rounded" style="max-height: 200px;">`;
            else if (type.startsWith('video/')) html = `<video src="${url}" controls class="w-100 rounded" style="max-height: 200px;"></video>`;
            else if (type.startsWith('audio/')) html = `<audio src="${url}" controls class="w-100"></audio>`;
            else html = `<div class="p-4 bg-light rounded text-muted"><i class="fas fa-file-alt fa-3x mb-2"></i><div>${file.name}</div></div>`;

            previewContent.innerHTML = html;

            // Dispara evento de mudança para habilitar o "Salvar" do form
            root.closest('form')?.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function resetUploader() {
            input.value = '';
            pathInput.value = '';
            if (removeInput) removeInput.value = '1';
            dropArea.classList.remove('d-none');
            progressContainer.classList.add('d-none');
            previewContainer.classList.add('d-none');
            progressBar.style.width = '0%';
        }

        trigger.addEventListener('click', () => input.click());
        input.addEventListener('change', () => { if (input.files.length) uploadFile(input.files[0]); });
        clearBtn.addEventListener('click', resetUploader);

        dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('active'); });
        dropArea.addEventListener('dragleave', () => dropArea.classList.remove('active'));
        dropArea.addEventListener('drop', e => {
            e.preventDefault();
            dropArea.classList.remove('active');
            if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
        });
    })();
</script>

<style>
    .upload-global-wrapper .upload-drop-area {
        border: 2px dashed #cbd5e0;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-global-wrapper .upload-drop-area:hover,
    .upload-global-wrapper .upload-drop-area.active {
        border-color: #3182ce;
        background: #ebf8ff;
    }

    .upload-global-wrapper .btn-primary {
        background-color: #3182ce;
        border-color: #3182ce;
    }

    .upload-global-wrapper .progress {
        background-color: #edf2f7;
    }

    .upload-preview-card {
        transition: transform 0.2s ease;
    }

    .upload-preview-card:hover {
        transform: translateY(-2px);
    }
</style>
