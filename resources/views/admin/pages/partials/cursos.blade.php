{{-- Partial: cursos --}}
{{-- $data = $page->data ?? [] --}}

<div id="sec-hero" class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Hero</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Texto do badge <small class="text-muted">(etiqueta acima do título)</small></label>
            <input type="text" name="hero_badge" class="form-control"
                   value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}"
                   placeholder="UNN ACADEMY">
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
            <label class="font-weight-bold">Imagem de destaque <small class="text-muted font-weight-normal">(aparece no card do curso em destaque quando nenhum curso tem thumbnail — JPG/PNG/WebP, máx 6 MB)</small></label>
            <div class="mb-2">
                <img id="prev-cursos-img"
                     src="{{ !empty($data['hero_image']) ? asset('storage/'.$data['hero_image']) : '' }}"
                     class="img-fluid rounded shadow-sm {{ empty($data['hero_image']) ? 'd-none' : '' }}"
                     style="max-height:220px;max-width:100%;object-fit:cover;border:1px solid #dee2e6"
                     alt="Preview">
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="hero_image" name="hero_image"
                       accept="image/*" data-preview="prev-cursos-img">
                <label class="custom-file-label" for="hero_image">
                    {{ !empty($data['hero_image']) ? 'Substituir imagem...' : 'Escolher imagem...' }}
                </label>
            </div>
            @if(!empty($data['hero_image']))
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="remove_hero_image" name="remove_hero_image" value="1">
                    <label class="custom-control-label text-danger" for="remove_hero_image">Remover imagem atual</label>
                </div>
            @endif
        </div>
    </div>
</div>
