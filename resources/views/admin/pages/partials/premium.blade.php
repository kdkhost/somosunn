{{-- Premium partial - admin CMS editor --}}

{{-- ===== HERO ===== --}}
<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-crown mr-2"></i>Hero / Cabecalho</h3>
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
            <label>Badge do hero <small class="text-muted">(hero_badge)</small></label>
            <input type="text" name="hero_badge" class="form-control"
                value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}" placeholder="ex: Associacao Premium">
        </div>

        <div class="form-group">
            <label>Titulo principal <small class="text-muted">(hero_title)</small></label>
            <input type="text" name="hero_title" class="form-control"
                value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="ex: Invista no seu crescimento">
        </div>

        <div class="form-group">
            <label>Subtitulo <small class="text-muted">(hero_subtitle - suporta HTML)</small></label>
            <textarea name="hero_subtitle" class="summernote-sm"
                placeholder="Descricao principal...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Selo de confianca 1 <small class="text-muted">(hero_trust_1)</small></label>
                    <input type="text" name="hero_trust_1" class="form-control"
                        value="{{ old('hero_trust_1', $data['hero_trust_1'] ?? '') }}" placeholder="ex: Sem fidelidade">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Selo de confianca 2 <small class="text-muted">(hero_trust_2)</small></label>
                    <input type="text" name="hero_trust_2" class="form-control"
                        value="{{ old('hero_trust_2', $data['hero_trust_2'] ?? '') }}"
                        placeholder="ex: Cancele quando quiser">
                </div>
            </div>
        </div>

        @php $heroImg = $data['hero_image'] ?? null; @endphp
        <div class="form-group">
            <label>Imagem hero <small class="text-muted">(hero_image)</small></label>
            @include('admin.components.upload-global', ['name' => 'hero_image', 'accept' => 'image/*'])

            @if ($heroImg)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $heroImg) }}" alt="Hero" class="img-thumbnail"
                        style="max-height:160px;">
                    <div class="mt-1">
                        <input type="checkbox" name="remove_hero_image" value="1" id="remove_premium_hero_image">
                        <label for="remove_premium_hero_image" class="text-danger ml-1">Remover imagem atual</label>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== SECAO DE PLANOS ===== --}}
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Secao de Planos</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-plans"
                    data-section="plans" {{ ($data['plans_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-plans">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Titulo da secao de planos <small class="text-muted">(plans_title)</small></label>
            <input type="text" name="plans_title" class="form-control"
                value="{{ old('plans_title', $data['plans_title'] ?? '') }}" placeholder="ex: Escolha seu plano">
        </div>

        <div class="form-group">
            <label>Subtitulo da secao de planos <small class="text-muted">(plans_subtitle)</small></label>
            <input type="text" name="plans_subtitle" class="form-control"
                value="{{ old('plans_subtitle', $data['plans_subtitle'] ?? '') }}"
                placeholder="ex: Sem taxa de adesao. Cancele quando quiser.">
        </div>
    </div>
</div>