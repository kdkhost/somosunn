@php
    use Illuminate\Support\Str;

    $instance = 'upload-global-' . Str::slug($name ?? 'file', '-') . '-' . Str::random(6);
    $label = $label ?? 'Upload de arquivo, imagem, video ou audio';
    $help = $help ?? 'O envio real acontece ao salvar o formulario. O progresso global aparece automaticamente no topo da tela.';
@endphp

<div class="upload-global-wrapper" data-upload-global-instance="{{ $instance }}">
    <label class="font-weight-bold mb-2 d-block">{{ $label }}</label>
    <div class="upload-drop-area" data-upload-drop-area>
        <span class="d-block mb-2">Arraste e solte arquivos aqui ou clique para selecionar</span>
        <input
            type="file"
            id="{{ $instance }}-input"
            name="{{ $name ?? 'file' }}"
            accept="{{ $accept ?? '*' }}"
            data-upload-input
            hidden
        >
        <button type="button" class="btn btn-outline-primary btn-sm" data-upload-trigger>Selecionar arquivo</button>
        <p class="upload-global-help text-muted small mb-0 mt-3">{{ $help }}</p>
    </div>
    <div class="upload-preview mt-3 d-none" data-upload-preview></div>
    <div class="d-flex align-items-center justify-content-between mt-2">
        <div class="upload-meta text-muted small" data-upload-meta>Nenhum arquivo selecionado.</div>
        <button type="button" class="btn btn-sm btn-outline-danger d-none" data-upload-clear>Remover</button>
    </div>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-upload-global-instance="{{ $instance }}"]');

        if (!root || root.dataset.uploadReady === 'true') {
            return;
        }

        root.dataset.uploadReady = 'true';

        const dropArea = root.querySelector('[data-upload-drop-area]');
        const input = root.querySelector('[data-upload-input]');
        const trigger = root.querySelector('[data-upload-trigger]');
        const preview = root.querySelector('[data-upload-preview]');
        const meta = root.querySelector('[data-upload-meta]');
        const clearButton = root.querySelector('[data-upload-clear]');

        function formatBytes(bytes) {
            if (!bytes || bytes <= 0) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const value = bytes / Math.pow(1024, exponent);

            return value.toFixed(value >= 100 || exponent === 0 ? 0 : 1) + ' ' + units[exponent];
        }

        function dispatchFormChange() {
            const form = input.closest('form');

            if (form) {
                form.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function renderPreview(file) {
            const objectUrl = URL.createObjectURL(file);
            const safeName = file.name || 'arquivo';
            const safeType = file.type || 'arquivo';
            let html = '';

            if (safeType.startsWith('image/')) {
                html = '<img src="' + objectUrl + '" alt="' + safeName + '" class="img-fluid rounded shadow-sm" style="max-height:180px;">';
            } else if (safeType.startsWith('video/')) {
                html = '<video src="' + objectUrl + '" controls class="w-100 rounded shadow-sm" style="max-height:220px;"></video>';
            } else if (safeType.startsWith('audio/')) {
                html = '<audio src="' + objectUrl + '" controls class="w-100"></audio>';
            } else {
                html = '<div class="upload-file-chip"><i class="fas fa-file-alt mr-2"></i>' + safeName + '</div>';
            }

            preview.innerHTML = html;
            preview.classList.remove('d-none');
            meta.textContent = safeName + ' • ' + formatBytes(file.size);
            clearButton.classList.remove('d-none');
        }

        function clearSelection() {
            input.value = '';
            preview.innerHTML = '';
            preview.classList.add('d-none');
            meta.textContent = 'Nenhum arquivo selecionado.';
            clearButton.classList.add('d-none');
            dispatchFormChange();
        }

        function handleFiles(fileList) {
            const file = fileList && fileList.length ? fileList[0] : null;

            if (!file) {
                clearSelection();
                return;
            }

            renderPreview(file);
            dispatchFormChange();
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            input.click();
        });

        dropArea.addEventListener('click', function () {
            input.click();
        });

        dropArea.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropArea.classList.add('is-dragover');
        });

        dropArea.addEventListener('dragleave', function (event) {
            event.preventDefault();
            dropArea.classList.remove('is-dragover');
        });

        dropArea.addEventListener('drop', function (event) {
            event.preventDefault();
            dropArea.classList.remove('is-dragover');

            if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files.length) {
                return;
            }

            if (typeof DataTransfer !== 'undefined') {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(event.dataTransfer.files[0]);
                input.files = dataTransfer.files;
            }

            handleFiles(event.dataTransfer.files);
        });

        input.addEventListener('change', function () {
            handleFiles(input.files);
        });

        clearButton.addEventListener('click', function (event) {
            event.preventDefault();
            clearSelection();
        });
    })();
</script>

<style>
    .upload-global-wrapper img,
    .upload-global-wrapper video {
        display: block;
    }

    .upload-drop-area {
        border: 2px dashed #1f5edb;
        padding: 1.4rem;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        border-radius: 0.9rem;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .upload-drop-area:hover,
    .upload-drop-area.is-dragover {
        background: #eff6ff;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    .upload-file-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.75rem 0.9rem;
        border-radius: 0.9rem;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 600;
    }
</style>
