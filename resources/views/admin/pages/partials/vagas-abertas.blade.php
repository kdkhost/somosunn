{{-- Partial: vagas-abertas --}}
{{-- $data = $page->data ?? [] --}}

<div id="sec-hero" class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-briefcase mr-1"></i> Hero</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-hero" data-section="hero"
                    {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-hero">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Texto do badge <small class="text-muted">(etiqueta acima do título)</small></label>
            <input type="text" name="hero_badge" class="form-control"
                value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}" placeholder="Carreiras &amp; Oportunidades">
        </div>
        <div class="form-group">
            <label>Título principal</label>
            <input type="text" name="hero_title" class="form-control"
                value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                placeholder="Descubra seu próximo passo na UNN Startups">
        </div>
        <div class="form-group mb-0">
            <label>Subtítulo / descrição</label>
            <textarea name="hero_subtitle" rows="3" class="form-control summernote-sm"
                placeholder="Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional.">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
    </div>
</div>