@extends('admin.layouts.app')

@section('page_title', ($plan->id ? 'Editar' : 'Novo') . ' plano')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}" data-pjax>Planos</a></li>
    <li class="breadcrumb-item active">{{ $plan->id ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
@php
    $planFeatures = $planFeatures ?? \App\Models\Plan::siteFeatureLabels();
    $planFeatureGroups = $planFeatureGroups ?? \App\Models\Plan::siteFeatureGroups();
    $periodLabels = \App\Models\Plan::periodLabels();
    $pricePeriods = old('price_periods', method_exists($plan, 'resolvedPricePeriods') ? $plan->resolvedPricePeriods() : ($plan->price_periods ?? []));
    $periodSettings = old('period_settings', method_exists($plan, 'resolvedPeriodSettings') ? $plan->resolvedPeriodSettings() : []);
    $selectedFeatures = old('permissions', $plan->permissions ?? []);
    $benefits = old('benefits', is_array($plan->benefits) ? implode("\n", $plan->benefits) : $plan->benefits);
@endphp

<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="{{ $plan->id ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" enctype="multipart/form-data">
            @csrf
            @if($plan->id)
                @method('PUT')
            @endif

            <div class="form-row">
                <div class="form-group col-md-5">
                    <label>Nome do plano</label>
                    <input name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Preco base</label>
                    <input name="price" class="form-control mask-money" value="{{ old('price', $plan->price) }}" required>
                    <small class="text-muted">Usado como mensal e base para os demais periodos.</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Periodo padrao</label>
                    <select name="period" class="form-control">
                        @foreach(array_merge($periodLabels, ['vitalicio' => 'Vitalicio']) as $value => $label)
                            <option value="{{ $value }}" {{ old('period', $plan->period) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Slug (URL)</label>
                    <input name="slug" class="form-control" value="{{ old('slug', $plan->slug) }}" placeholder="ex: pro, elite">
                    <small class="text-muted">Se vazio, sera gerado automaticamente.</small>
                </div>
                <div class="form-group col-md-8">
                    <label>Descricao</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Resumo do plano">{{ old('description', $plan->description) }}</textarea>
                </div>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">Periodos de cobranca</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th style="width: 160px;">Habilitado</th>
                                    <th style="width: 220px;">Preco total</th>
                                    <th>Observacao</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($periodLabels as $periodKey => $label)
                                    @php
                                        $enabled = old('period_settings.' . $periodKey . '.enabled', data_get($periodSettings, $periodKey . '.enabled', $periodKey === 'mensal'));
                                        $priceValue = old('price_periods.' . $periodKey, $pricePeriods[$periodKey] ?? ($periodKey === 'mensal' ? $plan->price : ''));
                                    @endphp
                                    <tr>
                                        <td class="align-middle font-weight-bold">{{ $label }}</td>
                                        <td class="align-middle">
                                            <input type="hidden" name="period_settings[{{ $periodKey }}][enabled]" value="0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input js-period-toggle" id="period_enabled_{{ $periodKey }}" name="period_settings[{{ $periodKey }}][enabled]" value="1" {{ $enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="period_enabled_{{ $periodKey }}">Ativo</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                                <input type="number" step="0.01" min="0" name="price_periods[{{ $periodKey }}]" class="form-control js-period-price" data-period="{{ $periodKey }}" value="{{ $priceValue }}">
                                            </div>
                                        </td>
                                        <td class="align-middle text-muted">
                                            @if($periodKey === 'mensal')
                                                Mantem o valor base salvo para futura ativacao, mesmo se desabilitado.
                                            @else
                                                Deixe o preco salvo e ligue/desligue a oferta quando quiser.
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="highlight" value="0">
                        <input type="checkbox" class="custom-control-input" id="highlight" name="highlight" value="1" {{ old('highlight', $plan->highlight) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="highlight">Destacar plano</label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="coupons_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="coupons_enabled" name="coupons_enabled" value="1" {{ old('coupons_enabled', $plan->coupons_enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="coupons_enabled">Permitir cupons</label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Plano ativo</label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="is_free" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_free" name="is_free" value="1" {{ old('is_free', $plan->is_free ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_free">Plano gratuito padrao</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $plan->is_recurring) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_recurring">Assinatura recorrente</label>
                    </div>
                </div>
                <div class="form-group col-md-3" id="billing_cycle_group" style="{{ old('is_recurring', $plan->is_recurring) ? '' : 'display:none' }}">
                    <label for="billing_cycle">Ciclo de cobranca (meses)</label>
                    <input type="number" class="form-control" id="billing_cycle" name="billing_cycle" min="1" max="12" value="{{ old('billing_cycle', $plan->billing_cycle ?? 1) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Imagem do plano</label>
                    <input type="hidden" name="remove_image" value="0">
                    <div class="upload-box" data-max-size="5242880" data-crop="1" data-existing-url="{{ $plan->image ? asset('storage/' . $plan->image) : '' }}" data-remove-input="[name='remove_image']">
                        <input type="file" name="image" accept="image/*" class="d-none">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <small class="text-muted upload-help"></small>
                        <div class="progress upload-progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label>Beneficios (um por linha)</label>
                    <textarea name="benefits" class="form-control" rows="8" placeholder="Ex: Acesso ao clube de beneficios&#10;Pitch diferenciado nos eventos">{{ $benefits }}</textarea>
                    <small class="text-muted">As listas exibidas no site saem daqui.</small>
                </div>
            </div>

            <div class="card card-outline card-secondary mb-3">
                <div class="card-header">
                    <h3 class="card-title">Campos comparativos opcionais</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Conexoes por mes</label>
                            <input name="comparison[connections_per_month]" class="form-control" value="{{ old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month')) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria em grupo</label>
                            <input name="comparison[group_mentorship]" class="form-control" value="{{ old('comparison.group_mentorship', data_get($plan->comparison, 'group_mentorship')) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria individual</label>
                            <input name="comparison[individual_mentorship]" class="form-control" value="{{ old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship')) }}">
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="comparison[priority_support]" value="0">
                        <input type="checkbox" class="custom-control-input" id="priority_support" name="comparison[priority_support]" value="1" {{ old('comparison.priority_support', (bool) data_get($plan->comparison, 'priority_support')) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="priority_support">Suporte prioritario</label>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">Permissoes do plano</h3>
                </div>
                <div class="card-body">
                    @foreach($planFeatureGroups as $groupName => $groupKeys)
                        <h6 class="font-weight-bold text-primary {{ $loop->first ? '' : 'mt-3' }}">{{ $groupName }}</h6>
                        <div class="row mb-2">
                            @foreach($groupKeys as $featureKey)
                                @if(isset($planFeatures[$featureKey]))
                                    <div class="col-md-4 col-lg-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="feature_{{ str_replace('.', '_', $featureKey) }}" name="permissions[]" value="{{ $featureKey }}" {{ in_array($featureKey, $selectedFeatures, true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="feature_{{ str_replace('.', '_', $featureKey) }}">{{ $planFeatures[$featureKey] }}</label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
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
    var recurring = document.getElementById('is_recurring');
    var billingGroup = document.getElementById('billing_cycle_group');
    if (recurring && billingGroup) {
        recurring.addEventListener('change', function () {
            billingGroup.style.display = this.checked ? '' : 'none';
        });
    }
})();
</script>
@endpush
@endsection
