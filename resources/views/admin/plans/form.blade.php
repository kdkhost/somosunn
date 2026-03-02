@extends('admin.layouts.app')

@section('page_title', ($plan->id ? 'Editar' : 'Novo').' plano')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}" data-pjax>Planos</a></li>
    <li class="breadcrumb-item active">{{ $plan->id ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="{{ $plan->id ? route('admin.plans.update',$plan) : route('admin.plans.store') }}" enctype="multipart/form-data">
            @csrf
            @if($plan->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome do pacote</label>
                    <input name="name" class="form-control" value="{{ old('name',$plan->name) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Preço</label>
                    <input name="price" class="form-control mask-money" value="{{ old('price',$plan->price) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Período</label>
                    <select name="period" class="form-control">
                        @foreach(['mensal','trimestral','semestral','anual','vitalício'] as $p)
                            <option value="{{ $p }}" {{ old('period',$plan->period)==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Slug (URL)</label>
                    <input name="slug" class="form-control" value="{{ old('slug',$plan->slug) }}" placeholder="ex: pro, elite">
                    <small class="text-muted">Se vazio, será gerado automaticamente.</small>
                </div>
                <div class="form-group col-md-8">
                    <label>Descrição</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Resumo do plano (aparece no site)">{{ old('description',$plan->description) }}</textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="highlight" value="0">
                        <input type="checkbox" class="custom-control-input" id="highlight" name="highlight" value="1" {{ old('highlight',$plan->highlight) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="highlight">Destacar (ribbon)</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="coupons_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="coupons_enabled" name="coupons_enabled" value="1" {{ old('coupons_enabled',$plan->coupons_enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="coupons_enabled">Permitir cupons</label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active',$plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Plano ativo</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_free" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_free" name="is_free" value="1" {{ old('is_free',$plan->is_free ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_free">Plano gratuito padrão <small class="text-muted">(atribuído a novos cadastros)</small></label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-primary mt-4">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring',$plan->is_recurring) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_recurring">Assinatura (Recorrente)</label>
                    </div>
                </div>
                <div class="form-group col-md-3" id="billing_cycle_group" style="{{ old('is_recurring', $plan->is_recurring) ? '' : 'display:none' }}">
                    <label for="billing_cycle">Ciclo de cobrança <small class="text-muted">(meses)</small></label>
                    <input type="number" class="form-control @error('billing_cycle') is-invalid @enderror" id="billing_cycle" name="billing_cycle" min="1" max="12" value="{{ old('billing_cycle', $plan->billing_cycle ?? 1) }}">
                    @error('billing_cycle')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Intervalo entre cobranças no MercadoPago.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Imagem do pacote</label>
                    <input type="hidden" name="remove_image" value="0">
                    <div class="upload-box" data-max-size="5242880" data-crop="1" data-existing-url="{{ $plan->image ? asset('storage/'.$plan->image) : '' }}" data-remove-input="[name='remove_image']">
                        <input type="file" name="image" accept="image/*" class="d-none">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <small class="text-muted upload-help"></small>
                        <div class="progress upload-progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label>Benefícios (um por linha)</label>
                    @php 
                        $benefits = old('benefits', $plan->benefits ?? []);
                        $benefitsText = is_array($benefits) ? implode("\n", $benefits) : $benefits;
                    @endphp
                    <textarea name="benefits" class="form-control" rows="8" placeholder="Ex: Acesso ao portal&#10;Mentorias semanais&#10;Grupo VIP">{{ $benefitsText }}</textarea>
                    <small class="text-muted">Use uma linha por benefício. Permissões abaixo complementam o acesso.</small>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Comparativo (exibição no Site)</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Conexões por mês</label>
                            <input name="comparison[connections_per_month]" class="form-control" placeholder="Ex: 5 / Ilimitadas" value="{{ old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month')) }}">
                            <small class="text-muted">Se vazio: plano grátis → 5, pagos → Ilimitadas.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria em grupo</label>
                            <input name="comparison[group_mentorship]" class="form-control" placeholder="Ex: 1/mês / Ilimitada" value="{{ old('comparison.group_mentorship', data_get($plan->comparison, 'group_mentorship')) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria individual</label>
                            <input name="comparison[individual_mentorship]" class="form-control" placeholder="Ex: 1/mês" value="{{ old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship')) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="comparison[priority_support]" value="0">
                                <input type="checkbox" class="custom-control-input" id="priority_support" name="comparison[priority_support]" value="1" {{ old('comparison.priority_support', (bool) data_get($plan->comparison, 'priority_support')) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="priority_support">Suporte prioritário</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8">
                            <small class="text-muted d-block mt-2">
                                Dica: itens como <strong>Acesso a cursos</strong>, <strong>Eventos</strong> e <strong>Comunidade</strong> são derivados das permissões do plano.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $planFeatures = $planFeatures ?? [];
                $selectedFeatures = old('permissions', $plan->permissions ?? []);
                if (!is_array($selectedFeatures)) {
                    $selectedFeatures = [];
                }
            @endphp

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Recursos do Plano (Site)</h3>
                </div>
                <div class="card-body">
                    @php
                        // Agrupar features por categoria
                        $featureGroups = [
                            'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                            'Cursos' => ['courses', 'courses.certificates', 'courses.downloads'],
                            'Eventos' => ['events', 'events.recordings', 'events.vip'],
                            'Mentorias' => ['mentorships', 'mentorships.group', 'mentorships.individual'],
                            'Extras' => ['rankings', 'support.priority', 'early.access'],
                        ];
                    @endphp

                    @foreach($featureGroups as $groupName => $groupKeys)
                        <h6 class="font-weight-bold text-primary mb-2 {{ !$loop->first ? 'mt-3' : '' }}">
                            <i class="fas fa-folder mr-1"></i> {{ $groupName }}
                        </h6>
                        <div class="row mb-2">
                            @foreach($groupKeys as $featureKey)
                                @if(isset($planFeatures[$featureKey]))
                                    <div class="col-md-4 col-lg-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="feature-{{ $featureKey }}"
                                                name="permissions[]" value="{{ $featureKey }}"
                                                {{ in_array($featureKey, $selectedFeatures, true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="feature-{{ $featureKey }}">{{ $planFeatures[$featureKey] }}</label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach

                    <small class="text-muted d-block mt-2">
                        Esses recursos controlam o acesso no site (ex.: Comunidade/Chat) e alimentam o comparativo em <code>/premium</code>.
                    </small>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var toggle = document.getElementById('is_recurring');
    var group  = document.getElementById('billing_cycle_group');
    if (!toggle || !group) return;
    toggle.addEventListener('change', function () {
        group.style.display = this.checked ? '' : 'none';
    });
})();
</script>
@endpush
@endsection
