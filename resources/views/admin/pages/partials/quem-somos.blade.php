{{-- Partial: quem-somos --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-header">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Cabeçalho</h3>
            <div class="card-tools">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-header" data-section="header"
                        {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggle-header">Exibir no site</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Subtítulo do hero</label>
                <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Conheça as pessoas por trás da maior comunidade de networking do Brasil.">
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold">Imagem de capa <small class="text-muted font-weight-normal">(JPG, PNG, WebP — máx 6 MB)</small></label>
                @include('admin.components.upload-global', ['name'=>'cover_image', 'accept'=>'image/*'])
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-founders">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-crown mr-1"></i> Fundadores</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título da seção</label>
                <input type="text" name="founders_title" class="form-control" value="{{ old('founders_title', $data['founders_title'] ?? '') }}" placeholder="Fundadores">
            </div>
            <div id="founders-repeater-container"></div>
            <button type="button" id="add-founder-btn" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-plus mr-1"></i> Adicionar Fundador
            </button>
            <textarea name="founders_json" class="d-none">{{ json_encode($data['founders'] ?? []) }}</textarea>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-team">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-friends mr-1"></i> Equipe</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título da seção</label>
                <input type="text" name="team_title" class="form-control" value="{{ old('team_title', $data['team_title'] ?? '') }}" placeholder="Nossa Equipe">
            </div>
            <div id="team-repeater-container"></div>
            <button type="button" id="add-team-btn" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-plus mr-1"></i> Adicionar Membro
            </button>
            <textarea name="team_json" class="d-none">{{ json_encode($data['team'] ?? []) }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initJSONRepeater === 'function') {
        window.initJSONRepeater({
            containerId: 'founders-repeater-container',
            inputId: 'founders_json',
            addButtonId: 'add-founder-btn',
            itemSchema: { name: '', role: '', bio: '', initials: '', image: '' },
            initialData: {!! json_encode($data['founders'] ?? []) !!},
            template: (item, index) => `
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="mb-2 mx-auto overflow-hidden rounded shadow-sm border bg-light d-flex align-items-center justify-content-center" style="width:100px; height:100px;">
                            ${item.image ? `<img src="/storage/${item.image}" class="w-100 h-100 object-cover">` : `<span class="text-muted small">${item.initials || 'F'}</span>`}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="small font-weight-bold">Nome</label>
                                <input type="text" name="founders[${index}][name]" value="${item.name || ''}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small font-weight-bold">Iniciais (Avatar)</label>
                                <input type="text" name="founders[${index}][initials]" value="${item.initials || ''}" class="form-control form-control-sm" maxlength="2">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Bio curta</label>
                            <textarea name="founders[${index}][bio]" rows="2" class="form-control form-control-sm">${item.bio || ''}</textarea>
                        </div>
                    </div>
                </div>
            `
        });

        window.initJSONRepeater({
            containerId: 'team-repeater-container',
            inputId: 'team_json',
            addButtonId: 'add-team-btn',
            itemSchema: { name: '', role: '', initials: '', image: '' },
            initialData: {!! json_encode($data['team'] ?? []) !!},
            template: (item, index) => `
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="mb-2 mx-auto rounded-circle overflow-hidden shadow-sm border bg-light d-flex align-items-center justify-content-center" style="width:80px; height:80px;">
                            ${item.image ? `<img src="/storage/${item.image}" class="w-100 h-100 object-cover">` : `<span class="text-muted small">${item.initials || 'M'}</span>`}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="small font-weight-bold">Nome</label>
                                <input type="text" name="team[${index}][name]" value="${item.name || ''}" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small font-weight-bold">Iniciais</label>
                                <input type="text" name="team[${index}][initials]" value="${item.initials || ''}" class="form-control form-control-sm" maxlength="2">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Cargo / Função</label>
                            <input type="text" name="team[${index}][role]" value="${item.role || ''}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            `
        });
    }
});
</script>
@endpush

<div class="tab-pane fade" id="sec-stats">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> UNN em Números</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título da seção</label>
                <input type="text" name="stats_title" class="form-control" value="{{ old('stats_title', $data['stats_title'] ?? '') }}">
            </div>
            <hr>
            @foreach ([1,2,3,4] as $i)
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Número {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_value" class="form-control" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}">
                </div>
                <div class="form-group col-md-8">
                    <label>Legenda {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_label" class="form-control" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}">
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Texto do botão</label>
                <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}">
            </div>
        </div>
    </div>
</div>
