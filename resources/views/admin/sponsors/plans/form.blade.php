@extends('admin.layouts.app')

@section('title', $plan->exists ? 'Editar Pacote de Publicidade' : 'Novo Pacote de Publicidade')
@section('page_title', $plan->exists ? 'Editar Pacote de Publicidade' : 'Novo Pacote de Publicidade')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsor-plans.index') }}">Pacotes de Publicidade</a></li>
    <li class="breadcrumb-item active">{{ $plan->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    <form method="POST" action="{{ $plan->exists ? route('admin.sponsor-plans.update', $plan) : route('admin.sponsor-plans.store') }}">
        @csrf
        @if($plan->exists) @method('PUT') @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags me-2 text-primary"></i>Configuração do Pacote por Período</h3>
                <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5 form-group">
                        <label for="plan_name">Nome do Pacote</label>
                        <input type="text" id="plan_name" name="name" class="form-control" value="{{ old('name', $plan->name) }}" placeholder="Ex.: Destaque 30 dias" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="plan_price">Valor do Período (R$)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" min="0" id="plan_price" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="plan_duration_days">Período de Veiculação (dias)</label>
                        <div class="input-group">
                            <input type="number" min="1" max="3650" id="plan_duration_days" name="duration_days" class="form-control" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" required>
                            <span class="input-group-text">dias</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach(\App\Models\SponsorPlan::DURATION_PRESETS as $daysPreset)
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="document.getElementById('plan_duration_days').value={{ $daysPreset }}">
                                    {{ $daysPreset }}d
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="plan_max_banners">Máx. Banners</label>
                        <input type="number" min="0" id="plan_max_banners" name="max_banners" class="form-control" value="{{ old('max_banners', $plan->max_banners ?? 1) }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="plan_max_events">Máx. Eventos</label>
                        <input type="number" min="0" id="plan_max_events" name="max_events" class="form-control" value="{{ old('max_events', $plan->max_events ?? 0) }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="plan_max_leads">Máx. Leads</label>
                        <input type="number" min="0" id="plan_max_leads" name="max_leads" class="form-control" value="{{ old('max_leads', $plan->max_leads ?? 0) }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="plan_priority">Prioridade de Exibição</label>
                        <input type="number" min="0" id="plan_priority" name="priority" class="form-control" value="{{ old('priority', $plan->priority ?? 0) }}" required>
                    </div>

                    <div class="col-12 form-group">
                        <label for="plan_description">Descrição / Benefícios inclusos</label>
                        <textarea id="plan_description" name="description" rows="3" class="form-control" placeholder="Ex.: Exibição no Banner Global e no Feed da Comunidade com relatório de cliques e impressões.">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" @checked(old('active', $plan->active ?? true))>
                            <label class="form-check-label fw-bold" for="active">Pacote ativo para venda</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Salvar Pacote
                </button>
            </div>
        </div>
    </form>
@endsection
