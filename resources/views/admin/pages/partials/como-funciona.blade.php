{{-- Partial: como-funciona --}}
{{-- $data = $page->data ?? [] --}}

{{-- Intro --}}
<div id="sec-header" class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Cabeçalho</h3></div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label>Subtítulo do hero</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Entenda como a UNN pode transformar sua rede de contatos…">
        </div>
    </div>
</div>

{{-- Steps (array JSON) --}}
<div id="sec-steps" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-ol mr-1"></i> Passos (4 etapas)</h3>
        <div class="card-tools"><span class="badge badge-secondary">JSON</span></div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Array de 4 objetos com os campos:
            <code>direction</code> (<code>"row"</code> ou <code>"row-reverse"</code>),
            <code>title</code>, <code>text</code>,
            <code>li</code> (array de 3 strings).
        </p>
        @error('steps_json')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
        <textarea name="steps_json"
                  rows="30"
                  data-json="1"
                  class="form-control @error('steps_json') is-invalid @enderror"
                  style="font-family: monospace; font-size: 12px">{{ old('steps_json', json_encode($data['steps'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
        <small class="text-muted mt-1 d-block">Use o botão "Formatar JSON" abaixo do campo para validar a sintaxe.</small>
    </div>
</div>

{{-- Planos --}}
<div id="sec-plans" class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> Seção Planos</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="plans_title" class="form-control" value="{{ old('plans_title', $data['plans_title'] ?? '') }}" placeholder="Escolha seu Plano">
        </div>
        <div class="form-group mb-0">
            <label>Subtítulo</label>
            <input type="text" name="plans_subtitle" class="form-control" value="{{ old('plans_subtitle', $data['plans_subtitle'] ?? '') }}" placeholder="Temos opções para todos os estágios…">
        </div>
    </div>
</div>

{{-- CTA --}}
<div id="sec-cta" class="card card-outline card-success">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Pronto para começar?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Criar conta grátis">
        </div>
    </div>
</div>
