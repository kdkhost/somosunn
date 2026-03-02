{{-- Partial: home --}}
{{-- $data = $page->data ?? [] --}}

{{-- Hero --}}
<div id="sec-hero" class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-home mr-1"></i> Hero</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título (linha 1)</label>
            <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="Conectando empreendedores.">
        </div>
        <div class="form-group">
            <label>Subtítulo (linha 2)</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Criando oportunidades reais.">
        </div>
        <div class="form-group">
            <label>Descrição / corpo</label>
            <textarea name="body" rows="3" class="form-control" placeholder="Texto descritivo abaixo do hero.">{{ old('body', $data['body'] ?? '') }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Botão principal (CTA 1)</label>
                <input type="text" name="hero_cta_text" class="form-control" value="{{ old('hero_cta_text', $data['hero_cta_text'] ?? '') }}" placeholder="Quero fazer parte">
            </div>
            <div class="form-group col-md-6">
                <label>Botão secundário (CTA 2)</label>
                <input type="text" name="hero_cta2_text" class="form-control" value="{{ old('hero_cta2_text', $data['hero_cta2_text'] ?? '') }}" placeholder="Conhecer a UNN">
            </div>
        </div>
        {{-- Imagem do hero --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold">Imagem do Hero <small class="text-muted font-weight-normal">(JPG, PNG, WebP, GIF — máx 6 MB)</small></label>
            <div class="mb-2">
                <img id="prev-hero-img"
                     src="{{ !empty($data['hero_image']) ? asset('storage/'.$data['hero_image']) : '' }}"
                     class="img-fluid rounded shadow-sm {{ empty($data['hero_image']) ? 'd-none' : '' }}"
                     style="max-height:220px;max-width:100%;object-fit:cover;border:1px solid #dee2e6"
                     alt="Preview da imagem">
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="hero_image" name="hero_image"
                       accept="image/*" data-preview="prev-hero-img">
                <label class="custom-file-label" for="hero_image">
                    {{ !empty($data['hero_image']) ? 'Substituir imagem...' : 'Escolher imagem...' }}
                </label>
            </div>
            @if (!empty($data['hero_image']))
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="remove_hero_image" name="remove_hero_image" value="1">
                    <label class="custom-control-label text-danger" for="remove_hero_image">Remover imagem atual</label>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Estatísticas --}}
<div id="sec-stats" class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas (4 números)</h3></div>
    <div class="card-body">
        @foreach ([1,2,3,4] as $i)
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Número {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_value" class="form-control" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}" placeholder="5.000+">
            </div>
            <div class="form-group col-md-8">
                <label>Legenda {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_label" class="form-control" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}" placeholder="Empreendedores">
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Seção Sobre --}}
<div id="sec-about" class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Seção "O que é a UNN"</h3></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Título da seção</label>
                <input type="text" name="about_title" class="form-control" value="{{ old('about_title', $data['about_title'] ?? '') }}" placeholder="O que é a UNN">
            </div>
            <div class="form-group col-md-6">
                <label>Subtítulo</label>
                <input type="text" name="about_subtitle" class="form-control" value="{{ old('about_subtitle', $data['about_subtitle'] ?? '') }}">
            </div>
        </div>
        <hr>
        @foreach ([1,2,3,4] as $i)
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Card {{ $i }} — Título</label>
                <input type="text" name="about_card_{{ $i }}_title" class="form-control" value="{{ old('about_card_'.$i.'_title', $data['about_card_'.$i.'_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-8">
                <label>Card {{ $i }} — Texto</label>
                <input type="text" name="about_card_{{ $i }}_text" class="form-control" value="{{ old('about_card_'.$i.'_text', $data['about_card_'.$i.'_text'] ?? '') }}">
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Eventos & Mentorias --}}
<div id="sec-events" class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar mr-1"></i> Eventos & Mentorias</h3></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Eventos — Título</label>
                <input type="text" name="events_title" class="form-control" value="{{ old('events_title', $data['events_title'] ?? '') }}" placeholder="Palestras gratuitas">
            </div>
            <div class="form-group col-md-6">
                <label>Eventos — Subtítulo</label>
                <input type="text" name="events_subtitle" class="form-control" value="{{ old('events_subtitle', $data['events_subtitle'] ?? '') }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Mentorias — Título</label>
                <input type="text" name="mentorships_title" class="form-control" value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}" placeholder="Mentorias premium">
            </div>
            <div class="form-group col-md-6">
                <label>Mentorias — Subtítulo</label>
                <input type="text" name="mentorships_subtitle" class="form-control" value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

{{-- Comunidade --}}
<div id="sec-community" class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-1"></i> Seção Comunidade</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="community_title" class="form-control" value="{{ old('community_title', $data['community_title'] ?? '') }}" placeholder="Comunidade por níveis">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Bloco iniciantes — Título</label>
                <input type="text" name="community_beginner_title" class="form-control" value="{{ old('community_beginner_title', $data['community_beginner_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Bloco iniciantes — Descrição</label>
                <input type="text" name="community_beginner_desc" class="form-control" value="{{ old('community_beginner_desc', $data['community_beginner_desc'] ?? '') }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Bloco sucesso — Título</label>
                <input type="text" name="community_success_title" class="form-control" value="{{ old('community_success_title', $data['community_success_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Bloco sucesso — Descrição</label>
                <input type="text" name="community_success_desc" class="form-control" value="{{ old('community_success_desc', $data['community_success_desc'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

{{-- Ranking + Depoimentos --}}
<div id="sec-ranking" class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Ranking & Depoimentos</h3></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Ranking — Título</label>
                <input type="text" name="ranking_title" class="form-control" value="{{ old('ranking_title', $data['ranking_title'] ?? '') }}" placeholder="Ranking do networking">
            </div>
            <div class="form-group col-md-6">
                <label>Ranking — Subtítulo</label>
                <input type="text" name="ranking_subtitle" class="form-control" value="{{ old('ranking_subtitle', $data['ranking_subtitle'] ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label>Depoimentos — Título da seção</label>
            <input type="text" name="testimonials_title" class="form-control" value="{{ old('testimonials_title', $data['testimonials_title'] ?? '') }}" placeholder="O que dizem nossos membros">
        </div>
        <div class="form-group mb-0">
            <label>
                Depoimentos (JSON)
                <small class="text-muted">— array de objetos: <code>[{"name":"…","role":"…","text":"…","rating":5}]</code></small>
            </label>
            @error('testimonials_json')<div class="text-danger small mb-1">{{ $message }}</div>@enderror
            <textarea name="testimonials_json"
                      rows="10"
                      data-json="1"
                      class="form-control @error('testimonials_json') is-invalid @enderror"
                      style="font-family: monospace; font-size: 12px">{{ old('testimonials_json', json_encode($data['testimonials'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
        </div>
    </div>
</div>

{{-- CTA Final --}}
<div id="sec-cta" class="card card-outline card-success">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_section_title" class="form-control" value="{{ old('cta_section_title', $data['cta_section_title'] ?? '') }}" placeholder="Pronto para transformar sua rede?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_section_subtitle" class="form-control" value="{{ old('cta_section_subtitle', $data['cta_section_subtitle'] ?? '') }}">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Botão principal</label>
                <input type="text" name="cta_section_btn_primary" class="form-control" value="{{ old('cta_section_btn_primary', $data['cta_section_btn_primary'] ?? '') }}" placeholder="Começar agora - É grátis">
            </div>
            <div class="form-group col-md-6">
                <label>Botão secundário</label>
                <input type="text" name="cta_section_btn_secondary" class="form-control" value="{{ old('cta_section_btn_secondary', $data['cta_section_btn_secondary'] ?? '') }}" placeholder="Ver planos Premium">
            </div>
        </div>
    </div>
</div>
