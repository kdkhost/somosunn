{{-- Partial: cursos --}}
{{-- $data = $page->data ?? [] --}}

<div id="sec-hero" class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Hero</h3>
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
                value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}" placeholder="UNN ACADEMY">
        </div>
        <div class="form-group">
            <label>Título principal</label>
            <input type="text" name="hero_title" class="form-control"
                value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                placeholder="A Maestria dos Negócios Começa Aqui.">
        </div>
        <div class="form-group">
            <label>Subtítulo / descrição</label>
            <textarea name="hero_subtitle" rows="2" class="form-control summernote-sm"
                placeholder="Domine as habilidades que transformam mercados. Conteúdo prático para quem não aceita o comum.">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        {{-- Imagem de destaque --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold">Imagem de destaque <small class="text-muted font-weight-normal">(aparece no
                    card do curso em destaque quando nenhum curso tem thumbnail — JPG/PNG/WebP, máx 6
                    MB)</small></label>
            @include('admin.components.upload-global', ['name' => 'hero_image', 'accept' => 'image/*'])
            @if(!empty($data['hero_image']))
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="remove_hero_image" name="remove_hero_image"
                        value="1">
                    <label class="custom-control-label text-danger" for="remove_hero_image">Remover imagem atual</label>
                </div>
            @endif
        </div>
    </div>
</div>