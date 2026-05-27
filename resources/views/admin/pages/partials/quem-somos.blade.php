{{-- Partial: quem-somos --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-header">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Cabecalho</h3>
            <div class="card-tools">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-header" data-section="header"
                        {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-header">Exibir no site</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Subtitulo do hero</label>
                <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Conheca as pessoas por tras da maior comunidade de networking do Brasil.">
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold">Imagem de capa <small class="text-muted font-weight-normal">(JPG, PNG, WebP - max 6 MB)</small></label>
                @include('admin.components.upload-global', ['name'=>'cover_image', 'accept'=>'image/*'])
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-founders">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-crown mr-1"></i> Fundadores</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Titulo da secao</label>
                <input type="text" name="founders_title" class="form-control" value="{{ old('founders_title', $data['founders_title'] ?? '') }}" placeholder="Fundadores">
            </div>
            <div id="founders-repeater-container"></div>
            <button type="button" id="add-founder-btn" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-plus mr-1"></i> Adicionar Fundador
            </button>
            <textarea name="founders_json" class="d-none">{{ json_encode($data['founders'] ?? []) }}</textarea>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-team">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-friends mr-1"></i> Equipe</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Titulo da secao</label>
                <input type="text" name="team_title" class="form-control" value="{{ old('team_title', $data['team_title'] ?? '') }}" placeholder="Nossa Equipe">
            </div>
            <div id="team-repeater-container"></div>
            <button type="button" id="add-team-btn" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-plus mr-1"></i> Adicionar Membro
            </button>
            <textarea name="team_json" class="d-none">{{ json_encode($data['team'] ?? []) }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function escHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function normalizeStorageUrl(path) {
        const value = String(path || '').trim();
        if (!value) return '';
        if (value.startsWith('http://') || value.startsWith('https://')) return value;
        return `/storage/${value.replace(/^\/+/, '')}`;
    }

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        Swal.fire({ toast: true, position: 'top-end', icon: type === 'error' ? 'error' : 'success', title: message, showConfirmButton: false, timer: 2800 });
    }

    async function uploadAvatar(file, onProgress) {
        const chunkSize = 1024 * 1024;
        const totalChunks = Math.ceil(file.size / chunkSize);
        const uploadId = `member_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;

        for (let i = 0; i < totalChunks; i++) {
            const start = i * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, end);
            const formData = new FormData();
            formData.append('file', chunk);
            formData.append('upload_id', uploadId);
            formData.append('chunk_index', i);
            formData.append('total_chunks', totalChunks);

            const chunkResponse = await fetch("{{ route('admin.upload.chunk') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });
            const chunkJson = await chunkResponse.json();
            if (!chunkResponse.ok || !chunkJson.ok) {
                throw new Error(chunkJson.error || 'Falha ao enviar parte da imagem.');
            }
            if (typeof onProgress === 'function') onProgress(Math.round(((i + 1) / totalChunks) * 100));
        }

        const assembleResponse = await fetch("{{ route('admin.upload.assemble') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ upload_id: uploadId, filename: file.name, total_chunks: totalChunks })
        });
        const assembleJson = await assembleResponse.json();
        if (!assembleResponse.ok || !assembleJson.ok || !assembleJson.path) {
            throw new Error(assembleJson.error || 'Falha ao finalizar upload da imagem.');
        }
        return assembleJson;
    }

    function bindDropzone(rootEl, updateItem, cfg) {
        rootEl.querySelectorAll(cfg.dropzoneSelector).forEach((dropzone) => {
            if (dropzone.dataset.bound === '1') return;
            dropzone.dataset.bound = '1';

            const input = dropzone.querySelector(cfg.inputSelector);
            const preview = dropzone.querySelector(cfg.previewSelector);
            const fallback = dropzone.querySelector(cfg.fallbackSelector);
            const progress = dropzone.querySelector(cfg.progressSelector);
            const removeBtn = dropzone.querySelector(cfg.removeSelector);
            const hiddenPath = rootEl.querySelector(cfg.hiddenPathSelector);
            const initialsInput = rootEl.querySelector(cfg.initialsSelector);

            function renderInitials() {
                const initials = (initialsInput?.value || cfg.defaultInitial).substring(0, 2).toUpperCase();
                if (fallback) fallback.textContent = initials;
            }

            function showImage(url) {
                if (!preview) return;
                preview.innerHTML = `<img src="${escHtml(url)}" class="w-100 h-100 object-cover" alt="Avatar">`;
                preview.classList.remove('d-none');
                fallback?.classList.add('d-none');
            }

            function clearImage() {
                if (preview) {
                    preview.innerHTML = '';
                    preview.classList.add('d-none');
                }
                fallback?.classList.remove('d-none');
                if (hiddenPath) hiddenPath.value = '';
                updateItem('image', '');
                renderInitials();
            }

            async function processFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                    notify('error', 'Selecione uma imagem valida para o avatar.');
                    return;
                }
                try {
                    if (progress) {
                        progress.classList.remove('d-none');
                        progress.textContent = 'Enviando... 0%';
                    }
                    const uploaded = await uploadAvatar(file, (percent) => {
                        if (progress) progress.textContent = `Enviando... ${percent}%`;
                    });
                    if (hiddenPath) hiddenPath.value = uploaded.path;
                    updateItem('image', uploaded.path);
                    showImage(uploaded.url || normalizeStorageUrl(uploaded.path));
                    if (progress) progress.textContent = 'Upload concluido';
                    notify('success', 'Avatar enviado com sucesso.');
                } catch (error) {
                    notify('error', error.message || 'Nao foi possivel enviar o avatar.');
                } finally {
                    if (progress) setTimeout(() => progress.classList.add('d-none'), 1200);
                }
            }

            dropzone.addEventListener('click', function (event) {
                if (event.target.closest(cfg.removeSelector)) return;
                input?.click();
            });
            dropzone.addEventListener('dragover', function (event) { event.preventDefault(); dropzone.classList.add('founder-dropzone-active'); });
            dropzone.addEventListener('dragleave', function () { dropzone.classList.remove('founder-dropzone-active'); });
            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropzone.classList.remove('founder-dropzone-active');
                processFile(event.dataTransfer?.files?.[0]);
            });
            input?.addEventListener('change', function () { processFile(this.files?.[0]); this.value = ''; });
            removeBtn?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Remover avatar?',
                    text: cfg.removeMessage,
                    showCancelButton: true,
                    confirmButtonText: 'Sim, remover',
                    cancelButtonText: 'Cancelar'
                }).then((result) => { if (result.isConfirmed) clearImage(); });
            });
            initialsInput?.addEventListener('input', renderInitials);
            renderInitials();
        });
    }

    if (typeof window.initJSONRepeater === 'function') {
        window.initJSONRepeater({
            containerId: 'founders-repeater-container',
            inputId: 'founders_json',
            addButtonId: 'add-founder-btn',
            itemSchema: { name: '', role: '', bio: '', initials: '', image: '' },
            initialData: {!! json_encode($data['founders'] ?? []) !!},
            template: (item, index) => `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="founder-dropzone mb-2 mx-auto overflow-hidden rounded shadow-sm border bg-light d-flex flex-column align-items-center justify-content-center p-2" style="width:130px; min-height:130px;" data-founder-dropzone>
                            <div class="mb-1 text-primary"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="small text-muted mb-2">Arraste ou clique</div>
                            <div class="mb-2 mx-auto overflow-hidden rounded border bg-white d-flex align-items-center justify-content-center" style="width:82px; height:82px;">
                                <div data-founder-preview class="${item.image ? '' : 'd-none'} w-100 h-100">
                                    ${item.image ? `<img src="${escHtml(normalizeStorageUrl(item.image))}" class="w-100 h-100 object-cover" alt="Avatar">` : ``}
                                </div>
                                <span data-founder-fallback class="${item.image ? 'd-none' : ''} text-muted small">${escHtml((item.initials || 'F').substring(0, 2).toUpperCase())}</span>
                            </div>
                            <div data-founder-progress class="small text-info d-none"></div>
                            <input type="file" class="d-none" accept="image/*" data-founder-file-input>
                            <input type="hidden" name="founders[${index}][image]" value="${escHtml(item.image || '')}" data-founder-image-path>
                            <button type="button" class="btn btn-xs btn-outline-danger mt-2" data-founder-remove>Remover</button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label class="small font-weight-bold">Nome</label>
                                <input type="text" name="founders[${index}][name]" value="${item.name || ''}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="small font-weight-bold">Iniciais (Avatar)</label>
                                <input type="text" name="founders[${index}][initials]" value="${item.initials || ''}" class="form-control form-control-sm" maxlength="2" data-founder-initials>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small font-weight-bold">Cargo</label>
                                <input type="text" name="founders[${index}][role]" value="${item.role || ''}" class="form-control form-control-sm" placeholder="CEO">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Bio curta</label>
                            <textarea name="founders[${index}][bio]" rows="2" class="form-control form-control-sm">${item.bio || ''}</textarea>
                        </div>
                    </div>
                </div>
            `,
            onRenderItem: ({ wrapper, updateItem }) => bindDropzone(wrapper, updateItem, {
                dropzoneSelector: '[data-founder-dropzone]',
                inputSelector: '[data-founder-file-input]',
                previewSelector: '[data-founder-preview]',
                fallbackSelector: '[data-founder-fallback]',
                progressSelector: '[data-founder-progress]',
                removeSelector: '[data-founder-remove]',
                hiddenPathSelector: '[data-founder-image-path]',
                initialsSelector: '[data-founder-initials]',
                defaultInitial: 'F',
                removeMessage: 'O avatar sera removido deste fundador.'
            })
        });

        window.initJSONRepeater({
            containerId: 'team-repeater-container',
            inputId: 'team_json',
            addButtonId: 'add-team-btn',
            itemSchema: { name: '', role: '', initials: '', image: '' },
            initialData: {!! json_encode($data['team'] ?? []) !!},
            template: (item, index) => `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="founder-dropzone mb-2 mx-auto overflow-hidden rounded shadow-sm border bg-light d-flex flex-column align-items-center justify-content-center p-2" style="width:130px; min-height:130px;" data-team-dropzone>
                            <div class="mb-1 text-primary"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="small text-muted mb-2">Arraste ou clique</div>
                            <div class="mb-2 mx-auto overflow-hidden rounded border bg-white d-flex align-items-center justify-content-center" style="width:82px; height:82px;">
                                <div data-team-preview class="${item.image ? '' : 'd-none'} w-100 h-100">
                                    ${item.image ? `<img src="${escHtml(normalizeStorageUrl(item.image))}" class="w-100 h-100 object-cover" alt="Avatar">` : ``}
                                </div>
                                <span data-team-fallback class="${item.image ? 'd-none' : ''} text-muted small">${escHtml((item.initials || 'M').substring(0, 2).toUpperCase())}</span>
                            </div>
                            <div data-team-progress class="small text-info d-none"></div>
                            <input type="file" class="d-none" accept="image/*" data-team-file-input>
                            <input type="hidden" name="team[${index}][image]" value="${escHtml(item.image || '')}" data-team-image-path>
                            <button type="button" class="btn btn-xs btn-outline-danger mt-2" data-team-remove>Remover</button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="small font-weight-bold">Nome</label>
                                <input type="text" name="team[${index}][name]" value="${item.name || ''}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small font-weight-bold">Iniciais</label>
                                <input type="text" name="team[${index}][initials]" value="${item.initials || ''}" class="form-control form-control-sm" maxlength="2" data-team-initials>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Cargo / Funcao</label>
                            <input type="text" name="team[${index}][role]" value="${item.role || ''}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            `,
            onRenderItem: ({ wrapper, updateItem }) => bindDropzone(wrapper, updateItem, {
                dropzoneSelector: '[data-team-dropzone]',
                inputSelector: '[data-team-file-input]',
                previewSelector: '[data-team-preview]',
                fallbackSelector: '[data-team-fallback]',
                progressSelector: '[data-team-progress]',
                removeSelector: '[data-team-remove]',
                hiddenPathSelector: '[data-team-image-path]',
                initialsSelector: '[data-team-initials]',
                defaultInitial: 'M',
                removeMessage: 'O avatar sera removido deste membro.'
            })
        });
    }
});
</script>
<style>
    .founder-dropzone {
        border: 2px dashed #d1d5db;
        cursor: pointer;
        transition: border-color .2s ease, background-color .2s ease;
    }
    .founder-dropzone:hover,
    .founder-dropzone.founder-dropzone-active {
        border-color: #1F5EDB;
        background-color: #eef4ff !important;
    }
</style>
@endpush

<div class="tab-pane fade" id="sec-stats">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> UNN em Numeros</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Titulo da secao</label>
                <input type="text" name="stats_title" class="form-control" value="{{ old('stats_title', $data['stats_title'] ?? '') }}">
            </div>
            <hr>
            @foreach ([1,2,3,4] as $i)
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Numero {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_value" class="form-control" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}">
                </div>
                <div class="form-group col-md-8">
                    <label>Legenda {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_label" class="form-control" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}">
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Titulo</label>
                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Texto do botao</label>
                <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}">
            </div>
        </div>
    </div>
</div>
