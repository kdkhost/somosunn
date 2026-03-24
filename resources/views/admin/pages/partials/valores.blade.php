{{-- Partial: valores --}}
{{-- $data = $page->data ?? [] --}}

{{-- Intro --}}
<div id="sec-header" class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-heart mr-1"></i> Cabeçalho</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-header" data-section="header"
                    {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-header">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label>Subtítulo do hero</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Os princípios que guiam tudo o que fazemos na UNN.">
        {{-- Se houver upload de imagem, incluir componente global aqui --}}
        </div>
    </div>
</div>

{{-- Valores (array JSON) --}}
<div id="sec-values" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-ul mr-1"></i> Os 6 Valores</h3>
        <div class="card-tools d-flex align-items-center">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mr-3">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-values" data-section="values"
                    {{ ($data['values_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-values">Exibir no site</label>
            </div>
            <span class="badge badge-secondary">JSON</span>
        </div>
    <div class="card-body">
        <div id="values-repeater-container"></div>
        <button type="button" id="add-value-btn" class="btn btn-outline-primary btn-block mt-3">
            <i class="fas fa-plus mr-1"></i> Adicionar Valor
        </button>
        <textarea name="values_json" class="d-none">{{ json_encode($data['values'] ?? []) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                        <label class="small font-weight-bold">Ícone</label>
                        <input type="text" name="values[${index}][icon]" value="${item.icon || 'fa-heart'}" class="form-control form-control-sm text-center" placeholder="fa-heart">
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Título do Valor</label>
                        <input type="text" name="values[${index}][title]" value="${item.title || ''}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Texto Descritivo</label>
                        <input type="text" name="values[${index}][text]" value="${item.text || ''}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Citação Interna</label>
                        <textarea name="values[${index}][quote]" rows="2" class="form-control form-control-sm">${item.quote || ''}</textarea>
                    </div>
                </div>
            </div>
        `
    });
});
</script>
@endpush

{{-- Blockquote --}}
<div id="sec-quote" class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-quote-left mr-1"></i> Citação Central</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-quote" data-section="quote"
                    {{ ($data['quote_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-quote">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Texto da citação</label>
            <textarea name="blockquote_text" rows="3" class="form-control" placeholder="Valores não são apenas palavras bonitas na parede…">{{ old('blockquote_text', $data['blockquote_text'] ?? '') }}</textarea>
        </div>
        <div class="form-group mb-0">
            <label>Autor</label>
            <input type="text" name="blockquote_author" class="form-control" value="{{ old('blockquote_author', $data['blockquote_author'] ?? '') }}" placeholder="— Equipe Fundadora UNN">
        </div>
    </div>
</div>

{{-- CTA --}}
<div id="sec-cta" class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-cta" data-section="cta"
                    {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-cta">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Compartilha desses valores?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Fazer parte">
        </div>
    </div>
</div>
