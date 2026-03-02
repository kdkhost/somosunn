{{-- Partial: eventos --}}
{{-- $data = $page->data ?? [] --}}

{{-- Hero --}}
<div id="sec-hero" class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Hero</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Texto do badge <small class="text-muted">(pequena etiqueta acima do título)</small></label>
            <input type="text" name="hero_badge" class="form-control"
                   value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}"
                   placeholder="Em destaque">
        </div>
        <div class="form-group">
            <label>Título principal</label>
            <input type="text" name="hero_title" class="form-control"
                   value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                   placeholder="Próximo Evento UNN">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <textarea name="hero_subtitle" rows="2" class="form-control summernote-sm"
                      placeholder="Não perca a oportunidade de expandir sua rede">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        {{-- Imagem de fundo do hero --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold">Imagem do hero <small class="text-muted font-weight-normal">(usada como fundo quando não há evento em destaque — JPG/PNG/WebP, máx 6 MB)</small></label>
            <div class="mb-2">
                <img id="prev-eventos-img"
                     src="{{ !empty($data['hero_image']) ? asset('storage/'.$data['hero_image']) : '' }}"
                     class="img-fluid rounded shadow-sm {{ empty($data['hero_image']) ? 'd-none' : '' }}"
                     style="max-height:220px;max-width:100%;object-fit:cover;border:1px solid #dee2e6"
                     alt="Preview">
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="hero_image" name="hero_image"
                       accept="image/*" data-preview="prev-eventos-img">
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

{{-- CTA Final --}}
<div id="sec-cta" class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção CTA</label>
            <input type="text" name="cta_title" class="form-control"
                   value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                   placeholder="Não perca os próximos eventos">
        </div>
        <div class="form-group">
            <label>Subtítulo / descrição</label>
            <textarea name="cta_subtitle" rows="2" class="form-control summernote-sm"
                      placeholder="Inscreva-se agora e garanta sua vaga.">{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}</textarea>
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control"
                   value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                   placeholder="Ver todos os eventos">
        </div>
    </div>
</div>
