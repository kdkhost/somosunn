@props([
    'name',
    'accept' => '*',
    'label' => 'Arraste e solte o arquivo aqui',
    'hint' => 'ou clique para selecionar',
    'icon' => 'fas fa-cloud-upload-alt',
    'required' => false,
    'maxSizeMb' => 100,
    'currentUrl' => null,
    'currentLabel' => null,
    'isImage' => false,
    'theme' => 'light', // light (painel novo) ou admin-lte (painel antigo)
])

@php
    $id = 'dz-' . \Illuminate\Support\Str::random(8);
    $isAdminLte = $theme === 'admin-lte';
@endphp

<div class="unn-dz-wrap" data-name="{{ $name }}" data-max-mb="{{ $maxSizeMb }}" data-is-image="{{ $isImage ? '1' : '0' }}">
    <input type="file" name="{{ $name }}" accept="{{ $accept }}" {{ $required ? 'required' : '' }}
        id="{{ $id }}" class="unn-dz-input" data-filepond-ignore="true"
        style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">

    <label for="{{ $id }}"
        class="unn-dz-area {{ $isAdminLte ? 'unn-dz--lte' : 'unn-dz--panel' }}"
        tabindex="0" role="button"
        aria-label="Selecionar arquivo para {{ $name }}">

        <div class="unn-dz-empty">
            <div class="unn-dz-icon">
                <i class="{{ $icon }}"></i>
            </div>
            <div class="unn-dz-label">{!! $label !!}</div>
            <div class="unn-dz-hint">{{ $hint }}</div>
            <div class="unn-dz-browse">Selecionar arquivo</div>
            <div class="unn-dz-meta">Tamanho maximo: {{ $maxSizeMb }} MB</div>
        </div>

        <div class="unn-dz-preview" hidden>
            @if($isImage)
                <img class="unn-dz-preview-img" alt="Preview">
            @else
                <div class="unn-dz-file-icon"><i class="fas fa-file-pdf"></i></div>
            @endif
            <div class="unn-dz-info">
                <div class="unn-dz-filename"></div>
                <div class="unn-dz-filesize"></div>
            </div>
            <button type="button" class="unn-dz-remove" aria-label="Remover arquivo"><i class="fas fa-times"></i></button>
        </div>

        <div class="unn-dz-error" hidden></div>
    </label>

    @if($currentUrl)
        <div class="unn-dz-current">
            @if($isImage)
                <img src="{{ $currentUrl }}" alt="Atual">
            @else
                <i class="fas fa-file-pdf"></i>
            @endif
            <a href="{{ $currentUrl }}" target="_blank" class="unn-dz-current-link">
                {{ $currentLabel ?? 'Arquivo atual' }}
            </a>
            <small>(sera substituido se voce enviar um novo)</small>
        </div>
    @endif
</div>

@once
@push('styles')
<style>
    .unn-dz-wrap { position: relative; }
    .unn-dz-wrap .unn-dz-input { opacity: 0; pointer-events: none; position: absolute; }

    .unn-dz-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 180px;
        padding: 1.5rem;
        border: 2px dashed #cbd5e1;
        border-radius: 1rem;
        background: #f8fafc;
        cursor: pointer;
        transition: all .2s ease;
        position: relative;
        text-align: center;
    }
    .unn-dz-area:hover, .unn-dz-area:focus-visible {
        border-color: #8b5cf6;
        background: #faf5ff;
        outline: none;
    }
    .unn-dz-area.is-dragover {
        border-color: #7c3aed;
        background: #ede9fe;
        transform: scale(1.01);
    }
    .unn-dz-area.has-file {
        border-style: solid;
        border-color: #7c3aed;
        background: #ffffff;
        min-height: 120px;
    }
    .unn-dz-area.has-error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .dark .unn-dz-area {
        background: rgba(15, 23, 42, .4);
        border-color: rgba(148, 163, 184, .2);
    }
    .dark .unn-dz-area:hover {
        background: rgba(88, 28, 135, .15);
        border-color: #a855f7;
    }

    .unn-dz-icon {
        font-size: 2.25rem;
        color: #a855f7;
        opacity: .85;
        margin-bottom: .25rem;
    }
    .unn-dz-label {
        font-weight: 800;
        font-size: .95rem;
        color: #0f172a;
    }
    .dark .unn-dz-label { color: #f1f5f9; }
    .unn-dz-hint {
        font-size: .8rem;
        color: #64748b;
        margin-top: -.25rem;
    }
    .unn-dz-browse {
        margin-top: .5rem;
        display: inline-block;
        padding: .4rem 1rem;
        background: linear-gradient(135deg, #8b5cf6, #6366f1);
        color: white;
        font-weight: 700;
        font-size: .8rem;
        border-radius: .75rem;
        box-shadow: 0 4px 10px rgba(139,92,246,.3);
    }
    .unn-dz-meta {
        font-size: .7rem;
        color: #94a3b8;
        margin-top: .25rem;
    }

    /* Preview */
    .unn-dz-preview {
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
        padding: .5rem;
    }
    .unn-dz-preview-img {
        width: 72px;
        height: 96px;
        object-fit: cover;
        border-radius: .5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .unn-dz-file-icon {
        width: 72px;
        height: 96px;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
        font-size: 2rem;
        box-shadow: 0 4px 12px rgba(220,38,38,.15);
    }
    .unn-dz-info {
        flex: 1;
        text-align: left;
        min-width: 0;
    }
    .unn-dz-filename {
        font-weight: 800;
        color: #0f172a;
        font-size: .9rem;
        word-break: break-word;
    }
    .dark .unn-dz-filename { color: #f1f5f9; }
    .unn-dz-filesize {
        font-size: .75rem;
        color: #64748b;
        margin-top: .125rem;
    }
    .unn-dz-remove {
        background: #fee2e2;
        color: #dc2626;
        border: 0;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s;
    }
    .unn-dz-remove:hover { background: #fecaca; transform: scale(1.1); }

    .unn-dz-error {
        color: #dc2626;
        font-size: .8rem;
        font-weight: 600;
        margin-top: .5rem;
    }

    .unn-dz-current {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .75rem;
        color: #64748b;
        margin-top: .5rem;
    }
    .unn-dz-current img {
        width: 40px; height: 54px; object-fit: cover; border-radius: .375rem;
    }
    .unn-dz-current-link {
        color: #7c3aed;
        font-weight: 700;
    }
    .unn-dz-current-link:hover { text-decoration: underline; }

    /* AdminLTE variant */
    .unn-dz--lte {
        border-radius: .5rem;
        min-height: 150px;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function humanSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let size = bytes;
        while (size >= 1024 && i < units.length - 1) {
            size /= 1024; i++;
        }
        return size.toFixed(size >= 10 ? 0 : 1) + ' ' + units[i];
    }

    function initDropzone(wrap) {
        if (wrap.dataset.unnDzInit === '1') return;
        wrap.dataset.unnDzInit = '1';

        const input   = wrap.querySelector('.unn-dz-input');
        const area    = wrap.querySelector('.unn-dz-area');
        const empty   = wrap.querySelector('.unn-dz-empty');
        const preview = wrap.querySelector('.unn-dz-preview');
        const filename= wrap.querySelector('.unn-dz-filename');
        const filesize= wrap.querySelector('.unn-dz-filesize');
        const previewImg = wrap.querySelector('.unn-dz-preview-img');
        const errorEl = wrap.querySelector('.unn-dz-error');
        const removeBtn = wrap.querySelector('.unn-dz-remove');
        const isImage = wrap.dataset.isImage === '1';
        const maxMb   = parseInt(wrap.dataset.maxMb || '100', 10);
        const maxBytes= maxMb * 1024 * 1024;
        const accept  = (input.getAttribute('accept') || '').split(',').map(s => s.trim()).filter(Boolean);

        function showError(msg) {
            if (!errorEl) return;
            errorEl.textContent = msg;
            errorEl.hidden = false;
            area.classList.add('has-error');
        }
        function clearError() {
            if (!errorEl) return;
            errorEl.textContent = '';
            errorEl.hidden = true;
            area.classList.remove('has-error');
        }

        function isAcceptable(file) {
            if (!accept.length) return true;
            return accept.some(a => {
                if (a.endsWith('/*')) {
                    return file.type.startsWith(a.slice(0, -1));
                }
                if (a.startsWith('.')) {
                    return file.name.toLowerCase().endsWith(a.toLowerCase());
                }
                return file.type === a;
            });
        }

        function renderFile(file) {
            clearError();

            if (!isAcceptable(file)) {
                showError('Tipo de arquivo nao aceito.');
                resetInput();
                return false;
            }
            if (file.size > maxBytes) {
                showError('Arquivo muito grande. Maximo: ' + maxMb + ' MB.');
                resetInput();
                return false;
            }

            filename.textContent = file.name;
            filesize.textContent = humanSize(file.size);

            if (isImage && previewImg) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; };
                reader.readAsDataURL(file);
            }

            empty.hidden = true;
            preview.hidden = false;
            area.classList.add('has-file');
            return true;
        }

        function resetInput() {
            input.value = '';
            empty.hidden = false;
            preview.hidden = true;
            area.classList.remove('has-file');
        }

        // Input change (click + file picker)
        input.addEventListener('change', function () {
            if (input.files && input.files[0]) {
                renderFile(input.files[0]);
            } else {
                resetInput();
            }
        });

        // Remove button
        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                resetInput();
                clearError();
            });
        }

        // Drag and drop
        ['dragenter', 'dragover'].forEach(evt => {
            area.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                area.classList.add('is-dragover');
            });
        });
        ['dragleave', 'dragend'].forEach(evt => {
            area.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                area.classList.remove('is-dragover');
            });
        });
        area.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            area.classList.remove('is-dragover');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                // Transfere para o input
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                input.files = dt.files;
                renderFile(e.dataTransfer.files[0]);
            }
        });

        // Keyboard accessibility
        area.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input.click();
            }
        });
    }

    function initAll(root) {
        (root || document).querySelectorAll('.unn-dz-wrap').forEach(initDropzone);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initAll());
    } else {
        initAll();
    }

    // Re-init on dynamic content (pjax, ajax modals)
    if (window.MutationObserver && document.body) {
        new MutationObserver(muts => {
            muts.forEach(m => m.addedNodes.forEach(n => {
                if (n.nodeType === 1) initAll(n);
            }));
        }).observe(document.body, { childList: true, subtree: true });
    }
})();
</script>
@endpush
@endonce
