{{-- Partial: membros --}}
{{-- $data = $page->data ?? [] --}}

<div id="sec-hero" class="card card-outline card-info mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Hero</h3>
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
            <label>Título principal</label>
            <input type="text" name="hero_title" class="form-control"
                value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="Membros SOMOS UNN">
            <small class="form-text text-muted">Exibido como heading H1 no topo da página.</small>
        </div>
        <div class="form-group mb-0">
            <label>Subtítulo / descrição</label>
            <textarea name="hero_subtitle" rows="3" class="form-control summernote-sm"
                placeholder="Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
    </div>
</div>

<div id="sec-stats" class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-stats" data-section="stats"
                    {{ ($data['stats_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-stats">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estatística 1 (Valor)</label>
                    <input type="text" name="stat_1_value" class="form-control form-control-sm" value="{{ old('stat_1_value', $data['stat_1_value'] ?? '500+') }}">
                </div>
                <div class="form-group">
                    <label>Estatística 1 (Rótulo)</label>
                    <input type="text" name="stat_1_label" class="form-control form-control-sm" value="{{ old('stat_1_label', $data['stat_1_label'] ?? 'Empreendedores') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estatística 2 (Valor)</label>
                    <input type="text" name="stat_2_value" class="form-control form-control-sm" value="{{ old('stat_2_value', $data['stat_2_value'] ?? '50+') }}">
                </div>
                <div class="form-group">
                    <label>Estatística 2 (Rótulo)</label>
                    <input type="text" name="stat_2_label" class="form-control form-control-sm" value="{{ old('stat_2_label', $data['stat_2_label'] ?? 'Mentores') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estatística 3 (Valor)</label>
                    <input type="text" name="stat_3_value" class="form-control form-control-sm" value="{{ old('stat_3_value', $data['stat_3_value'] ?? '27') }}">
                </div>
                <div class="form-group">
                    <label>Estatística 3 (Rótulo)</label>
                    <input type="text" name="stat_3_label" class="form-control form-control-sm" value="{{ old('stat_3_label', $data['stat_3_label'] ?? 'Estados') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estatística 4 (Valor)</label>
                    <input type="text" name="stat_4_value" class="form-control form-control-sm" value="{{ old('stat_4_value', $data['stat_4_value'] ?? '1.2k+') }}">
                </div>
                <div class="form-group">
                    <label>Estatística 4 (Rótulo)</label>
                    <input type="text" name="stat_4_label" class="form-control form-control-sm" value="{{ old('stat_4_label', $data['stat_4_label'] ?? 'Conexões feitas') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sec-cta" class="card card-outline card-success mb-4">
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
            <label>Título do CTA</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? 'Faça parte desta comunidade') }}">
        </div>
        <div class="form-group">
            <label>Subtítulo do CTA</label>
            <textarea name="cta_subtitle" rows="2" class="form-control summernote-sm">{{ old('cta_subtitle', $data['cta_subtitle'] ?? 'Conecte-se com empreendedores de sucesso e expanda sua rede de negócios.') }}</textarea>
        </div>
        <div class="form-group mb-0">
            <label>Texto do Botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? 'Quero fazer parte') }}">
        </div>
    </div>
</div>