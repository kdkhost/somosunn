{{-- Partial: valores --}}
{{-- $data = $page->data ?? [] --}}

{{-- Intro --}}
<div id="sec-header" class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-heart mr-1"></i> Cabeçalho</h3></div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label>Subtítulo do hero</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Os princípios que guiam tudo o que fazemos na UNN.">
        </div>
    </div>
</div>

{{-- Valores (array JSON) --}}
<div id="sec-values" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-ul mr-1"></i> Os 6 Valores</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">JSON</span>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Array de 6 objetos. Cada objeto deve ter os campos:
            <code>icon</code> (classe Font Awesome ex: <code>fa-heart</code>),
            <code>title</code>, <code>text</code>, <code>quote</code>.
        </p>
        @error('values_json')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
        <textarea name="values_json"
                  rows="30"
                  data-json="1"
                  class="form-control @error('values_json') is-invalid @enderror"
                  style="font-family: monospace; font-size: 12px">{{ old('values_json', json_encode($data['values'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
        <small class="text-muted mt-1 d-block">Use o botão "Formatar JSON" que aparece abaixo do campo para validar a sintaxe antes de salvar.</small>
    </div>
</div>

{{-- Blockquote --}}
<div id="sec-quote" class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-quote-left mr-1"></i> Citação Central</h3></div>
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
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
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
