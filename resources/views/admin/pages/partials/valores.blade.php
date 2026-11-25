{{-- Partial: valores --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-header">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-heart me-1"></i> Cabeçalho</h3>
            <div class="card-tools">
                <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="form-check-input section-toggle" id="toggle-header" data-section="header"
                        {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="toggle-header">Exibir no site</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group mb-0">
                <label>Subtítulo do hero</label>
                <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Os princípios que guiam tudo o que fazemos na UNN.">
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-values">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list-ul me-1"></i> Os 6 Valores</h3>
            <div class="card-tools d-flex align-items-center">
                <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success me-3">
                    <input type="checkbox" class="form-check-input section-toggle" id="toggle-values" data-section="values"
                        {{ ($data['values_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="toggle-values">Exibir no site</label>
                </div>
                <span class="badge text-bg-secondary">JSON</span>
            </div>
        </div>
        <div class="card-body">
            <div id="values-repeater-container"></div>
            <button type="button" id="add-value-btn" class="btn btn-outline-primary w-100 mt-3">
                <i class="fas fa-plus me-1"></i> Adicionar Valor
            </button>
            <textarea name="values_json" class="d-none">{{ json_encode($data['values'] ?? []) }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initJSONRepeater === 'function') {
        window.initJSONRepeater({
            containerId: 'values-repeater-container',
            inputId: 'values_json',
            addButtonId: 'add-value-btn',
            itemSchema: { icon: 'fa-heart', title: '', text: '', quote: '' },
            initialData: {!! json_encode($data['values'] ?? []) !!},
            template: (item, index) => `
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="mb-2 mx-auto rounded overflow-hidden shadow-sm border bg-light d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                            <i class="fas ${item.icon || 'fa-heart'} fa-2x text-primary"></i>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small fw-bold">Ícone</label>
                            <input type="text" name="values[${index}][icon]" value="${item.icon || 'fa-heart'}" class="form-control form-control-sm text-center">
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group mb-2">
                            <label class="small fw-bold">Título do Valor</label>
                            <input type="text" name="values[${index}][title]" value="${item.title || ''}" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small fw-bold">Texto Descritivo</label>
                            <input type="text" name="values[${index}][text]" value="${item.text || ''}" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-0">
                            <label class="small fw-bold">Citação Interna</label>
                            <textarea name="values[${index}][quote]" rows="2" class="form-control form-control-sm">${item.quote || ''}</textarea>
                        </div>
                    </div>
                </div>
            `
        });
    }
});
</script>
@endpush

<div class="tab-pane fade" id="sec-quote">
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-quote-left me-1"></i> Citação Central</h3>
            <div class="card-tools">
                <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="form-check-input section-toggle" id="toggle-quote" data-section="quote"
                        {{ ($data['quote_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="toggle-quote">Exibir no site</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Texto da citação</label>
                <textarea name="blockquote_text" rows="3" class="form-control">{{ old('blockquote_text', $data['blockquote_text'] ?? '') }}</textarea>
            </div>
            <div class="form-group mb-0">
                <label>Autor</label>
                <input type="text" name="blockquote_author" class="form-control" value="{{ old('blockquote_author', $data['blockquote_author'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn me-1"></i> CTA Final</h3>
            <div class="card-tools">
                <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="form-check-input section-toggle" id="toggle-cta" data-section="cta"
                        {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="toggle-cta">Exibir no site</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Texto do botão</label>
                <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}">
            </div>
        </div>
    </div>
</div>
