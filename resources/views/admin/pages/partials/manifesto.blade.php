{{-- Partial: manifesto --}}
{{-- $data = $page->data ?? [] --}}

{{-- Hero --}}
<div id="sec-hero" class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-fist-raised mr-1"></i> Hero</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-hero" data-section="hero"
                    {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-hero">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Título (parte 1)</label>
                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="Acreditamos no poder">
            </div>
            <div class="form-group col-md-6">
                <label>Título (destaque — parte 2)</label>
                <input type="text" name="hero_title_highlight" class="form-control" value="{{ old('hero_title_highlight', $data['hero_title_highlight'] ?? '') }}" placeholder="das conexões humanas.">
            </div>
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Esse é o nosso manifesto. Leia com o coração.">
        </div>
        <div class="form-group mb-0">
            <label>Citação inicial <small class="text-muted">(parágrafo de abertura)</small></label>
            <textarea name="quote_top" rows="3" class="form-control" placeholder="Nenhum empreendedor chega longe sozinho…">{{ old('quote_top', $data['quote_top'] ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Seções do manifesto --}}
<div id="sec-sections" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Seções do Manifesto</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-sections" data-section="sections"
                    {{ ($data['sections_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-sections">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        @foreach ([1,2,3,4,5] as $i)
        <div class="form-group">
            <label>Seção {{ $i }} — Título</label>
            <input type="text" name="section_{{ $i }}_title" class="form-control" value="{{ old('section_'.$i.'_title', $data['section_'.$i.'_title'] ?? '') }}">
        </div>
        <div class="form-group {{ $i < 5 ? '' : 'mb-0' }}">
            <label>Seção {{ $i }} — Texto</label>
            <textarea name="section_{{ $i }}_text" rows="3" class="form-control">{{ old('section_'.$i.'_text', $data['section_'.$i.'_text'] ?? '') }}</textarea>
        </div>
        @if ($i < 5)<hr>@endif
        @endforeach
    </div>
</div>

{{-- Citação final --}}
<div id="sec-quote" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-quote-right mr-1"></i> Citação Final</h3>
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
            <textarea name="quote_bottom" rows="3" class="form-control" placeholder="A UNN não é apenas uma plataforma. É um movimento.">{{ old('quote_bottom', $data['quote_bottom'] ?? '') }}</textarea>
        </div>
        <div class="form-group mb-0">
            <label>Autor</label>
            <input type="text" name="quote_author" class="form-control" value="{{ old('quote_author', $data['quote_author'] ?? '') }}" placeholder="— Equipe Fundadora UNN">
        </div>
    </div>
</div>

{{-- Pilares --}}
<div id="sec-pillars" class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-columns mr-1"></i> Pilares</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-pillars" data-section="pillars"
                    {{ ($data['pillars_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-pillars">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção de pilares</label>
            <input type="text" name="pillars_title" class="form-control" value="{{ old('pillars_title', $data['pillars_title'] ?? '') }}" placeholder="Os pilares que nos guiam">
        </div>
        <div class="form-row">
            @foreach ([1,2,3,4] as $i)
            <div class="form-group col-md-6">
                <label>Pilar {{ $i }}</label>
                <input type="text" name="pillar_{{ $i }}_title" class="form-control" value="{{ old('pillar_'.$i.'_title', $data['pillar_'.$i.'_title'] ?? '') }}">
            </div>
            @endforeach
        </div>
        <div class="form-group mb-0">
            <label>Texto do link para valores</label>
            <input type="text" name="pillars_link_text" class="form-control" value="{{ old('pillars_link_text', $data['pillars_link_text'] ?? '') }}" placeholder="Conheça todos os nossos valores →">
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
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Você compartilha desses valores?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Quero fazer parte">
        </div>
    </div>
</div>
