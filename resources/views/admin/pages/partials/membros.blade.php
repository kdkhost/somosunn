{{-- Partial: membros --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-hero">
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users me-1"></i> Hero</h3>
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

<div class="tab-pane fade" id="sec-rules">
    <div class="card card-outline card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sliders-h me-1"></i> Regras de Exibição de Membros</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="fw-bold">Quem deve aparecer na lista pública de membros?</label>
                <select name="members_display_mode" class="form-control">
                    <option value="all" {{ old('members_display_mode', $data['members_display_mode'] ?? 'all') === 'all' ? 'selected' : '' }}>
                        Todos os membros cadastrados ativos (Recomendado para fortalecer a comunidade)
                    </option>
                    <option value="with_plan" {{ old('members_display_mode', $data['members_display_mode'] ?? '') === 'with_plan' ? 'selected' : '' }}>
                        Apenas membros vinculados a qualquer plano (Starter, Pro, Elite)
                    </option>
                    <option value="paid_only" {{ old('members_display_mode', $data['members_display_mode'] ?? '') === 'paid_only' ? 'selected' : '' }}>
                        Apenas assinantes com plano pago ativo (Pro / Elite)
                    </option>
                </select>
                <small class="form-text text-muted">Controle global do que aparece na listagem pública de membros.</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="fw-bold">Exibir Administradores na lista pública?</label>
                        <select name="members_show_admins" class="form-control">
                            <option value="0" {{ old('members_show_admins', $data['members_show_admins'] ?? '0') == '0' ? 'selected' : '' }}>Não (Apenas membros da rede)</option>
                            <option value="1" {{ old('members_show_admins', $data['members_show_admins'] ?? '0') == '1' ? 'selected' : '' }}>Sim (Exibir administradores)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="fw-bold">Exibir somente perfis com foto cadastrada?</label>
                        <select name="members_only_with_photo" class="form-control">
                            <option value="0" {{ old('members_only_with_photo', $data['members_only_with_photo'] ?? '0') == '0' ? 'selected' : '' }}>Não (Exibir membros com foto ou iniciais)</option>
                            <option value="1" {{ old('members_only_with_photo', $data['members_only_with_photo'] ?? '0') == '1' ? 'selected' : '' }}>Sim (Apenas membros com foto real)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-stats">
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar me-1"></i> Estatísticas</h3>
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
            <h3 class="card-title"><i class="fas fa-bullhorn me-1"></i> CTA Final</h3>
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
