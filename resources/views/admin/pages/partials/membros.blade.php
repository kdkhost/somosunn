{{-- Partial: membros --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-hero">
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Hero</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título principal</label>
                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Subtítulo / descrição</label>
                <textarea name="hero_subtitle" rows="3" class="form-control">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-stats">
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ([1,2,3,4] as $i)
                <div class="col-md-3">
                    <div class="form-group">
                        <label>V{{ $i }} (Valor)</label>
                        <input type="text" name="stat_{{ $i }}_value" class="form-control form-control-sm" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>V{{ $i }} (Rótulo)</label>
                        <input type="text" name="stat_{{ $i }}_label" class="form-control form-control-sm" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título do CTA</label>
                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Texto do Botão</label>
                <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}">
            </div>
        </div>
    </div>
</div>