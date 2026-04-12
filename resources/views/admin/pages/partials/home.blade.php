{{-- Partial: home --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-hero">
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-home mr-1"></i> Hero</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-hero" data-section="hero" {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-hero">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título (linha 1)</label>
                <input type="text" name="hero_title" class="form-control"
                    value="{{ old('hero_title', $data['hero_title'] ?? '') }}">
            </div>
            <div class="form-group">
                <label>Subtítulo (linha 2)</label>
                <input type="text" name="hero_subtitle" class="form-control"
                    value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}">
            </div>
            <div class="form-group">
                <label>Descrição / corpo</label>
                <textarea name="body" rows="3" class="form-control">{{ old('body', $data['body'] ?? '') }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Botão principal</label>
                    <input type="text" name="hero_cta_text" class="form-control"
                        value="{{ old('hero_cta_text', $data['hero_cta_text'] ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Botão secundário</label>
                    <input type="text" name="hero_cta2_text" class="form-control"
                        value="{{ old('hero_cta2_text', $data['hero_cta2_text'] ?? '') }}">
                </div>
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold">Imagem do Hero</label>
                @include('admin.components.upload-global', ['name' => 'hero_image', 'accept' => 'image/*'])
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-stats">
    <div class="card card-outline card-info">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-stats" data-section="stats" {{ ($data['stats_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-stats">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            @foreach ([1, 2, 3, 4] as $i)
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Número {{ $i }}</label>
                        <input type="text" name="stat_{{ $i }}_value" class="form-control"
                            value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}">
                    </div>
                    <div class="form-group col-md-8">
                        <label>Legenda {{ $i }}</label>
                        <input type="text" name="stat_{{ $i }}_label" class="form-control"
                            value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-about">
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Seção "O que é a UNN"</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-about" data-section="about" {{ ($data['about_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-about">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Título da seção</label>
                    <input type="text" name="about_title" class="form-control"
                        value="{{ old('about_title', $data['about_title'] ?? '') }}">
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
</div>

<div class="tab-pane fade" id="sec-journey">
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-route mr-1"></i> Seção "Onde o network me levou"</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-journey" data-section="journey" {{ ($data['journey_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-journey">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Título da seção</label>
                    <input type="text" name="journey_title" class="form-control"
                        value="{{ old('journey_title', $data['journey_title'] ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Subtítulo</label>
                    <input type="text" name="journey_subtitle" class="form-control"
                        value="{{ old('journey_subtitle', $data['journey_subtitle'] ?? '') }}">
                </div>
            </div>
            <hr>
            @foreach ([1, 2, 3] as $i)
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Card {{ $i }} — Chamada</label>
                        <input type="text" name="journey_card_{{ $i }}_title" class="form-control"
                            value="{{ old('journey_card_' . $i . '_title', $data['journey_card_' . $i . '_title'] ?? '') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Card {{ $i }} — Resultado</label>
                        <input type="text" name="journey_card_{{ $i }}_result" class="form-control"
                            value="{{ old('journey_card_' . $i . '_result', $data['journey_card_' . $i . '_result'] ?? '') }}">
                    </div>
                    <div class="form-group col-md-5">
                        <label>Card {{ $i }} — Texto</label>
                        <input type="text" name="journey_card_{{ $i }}_text" class="form-control"
                            value="{{ old('journey_card_' . $i . '_text', $data['journey_card_' . $i . '_text'] ?? '') }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-events">
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-calendar mr-1"></i> Eventos & Mentorias</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-events" data-section="events" {{ ($data['events_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-events">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Eventos — Título</label>
                    <input type="text" name="events_title" class="form-control"
                        value="{{ old('events_title', $data['events_title'] ?? '') }}">
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
                        value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Mentorias — Subtítulo</label>
                    <input type="text" name="mentorships_subtitle" class="form-control"
                        value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-ranking">
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Ranking & Depoimentos</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-ranking" data-section="ranking" {{ ($data['ranking_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-ranking">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Ranking — Título</label>
                    <input type="text" name="ranking_title" class="form-control"
                        value="{{ old('ranking_title', $data['ranking_title'] ?? '') }}">
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
                    value="{{ old('testimonials_title', $data['testimonials_title'] ?? '') }}">
            </div>
            <div id="testimonials-repeater-container"></div>
            <button type="button" id="add-testimonial-btn" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-plus mr-1"></i> Adicionar Depoimento
            </button>
            <textarea name="testimonials_json" class="d-none">{{ json_encode($data['testimonials'] ?? []) }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initJSONRepeater === 'function') {
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
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Texto do Depoimento</label>
                            <textarea name="testimonials[${index}][text]" rows="3" class="form-control form-control-sm">${item.text || ''}</textarea>
                        </div>
                    </div>
                </div>
            `
        });
    }
});
</script>
@endpush

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
            <div class="card-tools ml-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-cta" data-section="cta" {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-cta">Visibilidade</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="cta_section_title" class="form-control"
                    value="{{ old('cta_section_title', $data['cta_section_title'] ?? '') }}">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Botão principal</label>
                    <input type="text" name="cta_section_btn_primary" class="form-control"
                        value="{{ old('cta_section_btn_primary', $data['cta_section_btn_primary'] ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Botão secundário</label>
                    <input type="text" name="cta_section_btn_secondary" class="form-control"
                        value="{{ old('cta_section_btn_secondary', $data['cta_section_btn_secondary'] ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>