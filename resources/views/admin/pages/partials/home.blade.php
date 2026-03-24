{{-- Partial: home --}}
{{-- $data = $page->data ?? [] --}}

{{-- Hero --}}
<div id="sec-hero" class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-home mr-1"></i> Hero</h3>
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
            <label>Título (linha 1)</label>
            <input type="text" name="hero_title" class="form-control"
                value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="Conectando empreendedores.">
        </div>
        <div class="form-group">
            <label>Subtítulo (linha 2)</label>
            <input type="text" name="hero_subtitle" class="form-control"
                value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}"
                placeholder="Criando oportunidades reais.">
        </div>
        <div class="form-group">
            <label>Descrição / corpo</label>
            <textarea name="body" rows="3" class="form-control"
                placeholder="Texto descritivo abaixo do hero.">{{ old('body', $data['body'] ?? '') }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Botão principal (CTA 1)</label>
                <input type="text" name="hero_cta_text" class="form-control"
                    value="{{ old('hero_cta_text', $data['hero_cta_text'] ?? '') }}" placeholder="Quero fazer parte">
            </div>
            <div class="form-group col-md-6">
                <label>Botão secundário (CTA 2)</label>
                <input type="text" name="hero_cta2_text" class="form-control"
                    value="{{ old('hero_cta2_text', $data['hero_cta2_text'] ?? '') }}" placeholder="Conhecer a UNN">
            </div>
        </div>
        {{-- Imagem do hero --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold">Imagem do Hero <small class="text-muted font-weight-normal">(JPG, PNG, WebP,
                    GIF — máx 6 MB)</small></label>
            @include('admin.components.upload-global', ['name' => 'hero_image', 'accept' => 'image/*'])
            @if (!empty($data['hero_image']))
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="remove_hero_image" name="remove_hero_image"
                        value="1">
                    <label class="custom-control-label text-danger" for="remove_hero_image">Remover imagem atual</label>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Estatísticas --}}
<div id="sec-stats" class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas (4 números)</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-stats"
                    data-section="stats" {{ ($data['stats_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-stats">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        @foreach ([1, 2, 3, 4] as $i)
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Número {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_value" class="form-control"
                        value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}" placeholder="5.000+">
                </div>
                <div class="form-group col-md-8">
                    <label>Legenda {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_label" class="form-control"
                        value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}"
                        placeholder="Empreendedores">
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Seção Sobre --}}
<div id="sec-about" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Seção "O que é a UNN"</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-about"
                    data-section="about" {{ ($data['about_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-about">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Título da seção</label>
                <input type="text" name="about_title" class="form-control"
                    value="{{ old('about_title', $data['about_title'] ?? '') }}" placeholder="O que é a UNN">
            </div>
            <div class="form-group col-md-6">
                <label>Subtítulo</label>
                <input type="text" name="about_subtitle" class="form-control"
                    value="{{ old('about_subtitle', $data['about_subtitle'] ?? '') }}">
            </div>
        </div>
        <hr>
        @foreach ([1, 2, 3, 4] as $i)
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Card {{ $i }} — Título</label>
                    <input type="text" name="about_card_{{ $i }}_title" class="form-control"
                        value="{{ old('about_card_' . $i . '_title', $data['about_card_' . $i . '_title'] ?? '') }}">
                </div>
                <div class="form-group col-md-8">
                    <label>Card {{ $i }} — Texto</label>
                    <input type="text" name="about_card_{{ $i }}_text" class="form-control"
                        value="{{ old('about_card_' . $i . '_text', $data['about_card_' . $i . '_text'] ?? '') }}">
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Onde o network me levou --}}
<div id="sec-journey" class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-route mr-1"></i> Seção "Onde o network me levou"</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-journey"
                    data-section="journey" {{ ($data['journey_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-journey">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Título da seção</label>
                <input type="text" name="journey_title" class="form-control"
                    value="{{ old('journey_title', $data['journey_title'] ?? '') }}"
                    placeholder="Onde o network me levou">
            </div>
            <div class="form-group col-md-6">
                <label>Subtítulo</label>
                <input type="text" name="journey_subtitle" class="form-control"
                    value="{{ old('journey_subtitle', $data['journey_subtitle'] ?? '') }}"
                    placeholder="Conexões que viraram negócios, palcos e expansão.">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Destaque principal — Legenda</label>
                <input type="text" name="journey_highlight_label" class="form-control"
                    value="{{ old('journey_highlight_label', $data['journey_highlight_label'] ?? '') }}"
                    placeholder="O que a rede certa acelera">
            </div>
            <div class="form-group col-md-6">
                <label>Destaque principal — Texto</label>
                <input type="text" name="journey_highlight_value" class="form-control"
                    value="{{ old('journey_highlight_value', $data['journey_highlight_value'] ?? '') }}"
                    placeholder="Mais visibilidade, mais negócios e mais acesso">
            </div>
        </div>
        <div class="form-group">
            <label>Texto do botão</label>
            <input type="text" name="journey_cta_text" class="form-control"
                value="{{ old('journey_cta_text', $data['journey_cta_text'] ?? '') }}"
                placeholder="Quero viver isso também">
        </div>
        <hr>
        @foreach ([1, 2, 3] as $i)
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Card {{ $i }} — Chamada</label>
                    <input type="text" name="journey_card_{{ $i }}_title" class="form-control"
                        value="{{ old('journey_card_' . $i . '_title', $data['journey_card_' . $i . '_title'] ?? '') }}"
                        placeholder="Parcerias que viram negócio">
                </div>
                <div class="form-group col-md-4">
                    <label>Card {{ $i }} — Resultado</label>
                    <input type="text" name="journey_card_{{ $i }}_result" class="form-control"
                        value="{{ old('journey_card_' . $i . '_result', $data['journey_card_' . $i . '_result'] ?? '') }}"
                        placeholder="Novos contratos e clientes recorrentes">
                </div>
                <div class="form-group col-md-5">
                    <label>Card {{ $i }} — Texto</label>
                    <input type="text" name="journey_card_{{ $i }}_text" class="form-control"
                        value="{{ old('journey_card_' . $i . '_text', $data['journey_card_' . $i . '_text'] ?? '') }}"
                        placeholder="Explique o tipo de resultado gerado pelo networking.">
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Eventos & Mentorias --}}
<div id="sec-events" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar mr-1"></i> Eventos & Mentorias</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-events"
                    data-section="events" {{ ($data['events_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-events">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Eventos — Título</label>
                <input type="text" name="events_title" class="form-control"
                    value="{{ old('events_title', $data['events_title'] ?? '') }}" placeholder="Palestras gratuitas">
            </div>
            <div class="form-group col-md-6">
                <label>Eventos — Subtítulo</label>
                <input type="text" name="events_subtitle" class="form-control"
                    value="{{ old('events_subtitle', $data['events_subtitle'] ?? '') }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Mentorias — Título</label>
                <input type="text" name="mentorships_title" class="form-control"
                    value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}" placeholder="Mentorias premium">
            </div>
            <div class="form-group col-md-6">
                <label>Mentorias — Subtítulo</label>
                <input type="text" name="mentorships_subtitle" class="form-control"
                    value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

{{-- Comunidade --}}
<div id="sec-community" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Seção Comunidade</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-community"
                    data-section="community" {{ ($data['community_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-community">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="community_title" class="form-control"
                value="{{ old('community_title', $data['community_title'] ?? '') }}" placeholder="Comunidade por níveis">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Bloco iniciantes — Título</label>
                <input type="text" name="community_beginner_title" class="form-control"
                    value="{{ old('community_beginner_title', $data['community_beginner_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Bloco iniciantes — Descrição</label>
                <input type="text" name="community_beginner_desc" class="form-control"
                    value="{{ old('community_beginner_desc', $data['community_beginner_desc'] ?? '') }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Bloco sucesso — Título</label>
                <input type="text" name="community_success_title" class="form-control"
                    value="{{ old('community_success_title', $data['community_success_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Bloco sucesso — Descrição</label>
                <input type="text" name="community_success_desc" class="form-control"
                    value="{{ old('community_success_desc', $data['community_success_desc'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

{{-- Ranking + Depoimentos --}}
<div id="sec-ranking" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Ranking & Depoimentos</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-ranking"
                    data-section="ranking" {{ ($data['ranking_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-ranking">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Ranking — Título</label>
                <input type="text" name="ranking_title" class="form-control"
                    value="{{ old('ranking_title', $data['ranking_title'] ?? '') }}" placeholder="Ranking do networking">
            </div>
            <div class="form-group col-md-6">
                <label>Ranking — Subtítulo</label>
                <input type="text" name="ranking_subtitle" class="form-control"
                    value="{{ old('ranking_subtitle', $data['ranking_subtitle'] ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label>Depoimentos — Título da seção</label>
            <input type="text" name="testimonials_title" class="form-control"
                value="{{ old('testimonials_title', $data['testimonials_title'] ?? '') }}"
                placeholder="O que dizem nossos membros">
        </div>
        <div id="testimonials-repeater-container"></div>
        <button type="button" id="add-testimonial-btn" class="btn btn-outline-primary btn-block mt-3">
            <i class="fas fa-plus mr-1"></i> Adicionar Depoimento
        </button>
        <textarea name="testimonials_json" class="d-none">{{ json_encode($data['testimonials'] ?? []) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initJSONRepeater({
        containerId: 'testimonials-repeater-container',
        inputId: 'testimonials_json',
        addButtonId: 'add-testimonial-btn',
        itemSchema: { name: '', role: '', text: '', rating: 5, image: '' },
        initialData: {!! json_encode($data['testimonials'] ?? []) !!},
        template: (item, index) => `
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="mb-2 mx-auto rounded-circle overflow-hidden shadow-sm border bg-light d-flex align-items-center justify-content-center" style="width:80px; height:80px;">
                        ${item.image ? `<img src="/storage/${item.image}" class="w-100 h-100 object-cover">` : `<i class="fas fa-user text-muted fa-2x"></i>`}
                    </div>
                    <button type="button" class="btn btn-xs btn-block btn-outline-info repeater-upload-btn" data-field="image">
                        <i class="fas fa-camera mr-1"></i> Foto
                    </button>
                </div>
                <div class="col-md-9">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="small font-weight-bold">Nome do Autor</label>
                            <input type="text" name="testimonials[${index}][name]" value="${item.name || ''}" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Estrelas (1-5)</label>
                            <input type="number" name="testimonials[${index}][rating]" value="${item.rating || 5}" class="form-control form-control-sm" min="1" max="5">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Cargo / Empresa</label>
                        <input type="text" name="testimonials[${index}][role]" value="${item.role || ''}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Texto do Depoimento</label>
                        <textarea name="testimonials[${index}][text]" rows="3" class="form-control form-control-sm">${item.text || ''}</textarea>
                    </div>
                </div>
            </div>
        `
    });
});
</script>
@endpush

{{-- CTA Final --}}
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
            <input type="text" name="cta_section_title" class="form-control"
                value="{{ old('cta_section_title', $data['cta_section_title'] ?? '') }}"
                placeholder="Pronto para transformar sua rede?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_section_subtitle" class="form-control"
                value="{{ old('cta_section_subtitle', $data['cta_section_subtitle'] ?? '') }}">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Botão principal</label>
                <input type="text" name="cta_section_btn_primary" class="form-control"
                    value="{{ old('cta_section_btn_primary', $data['cta_section_btn_primary'] ?? '') }}"
                    placeholder="Começar agora - É grátis">
            </div>
            <div class="form-group col-md-6">
                <label>Botão secundário</label>
                <input type="text" name="cta_section_btn_secondary" class="form-control"
                    value="{{ old('cta_section_btn_secondary', $data['cta_section_btn_secondary'] ?? '') }}"
                    placeholder="Ver planos Premium">
            </div>
        </div>
    </div>
</div>