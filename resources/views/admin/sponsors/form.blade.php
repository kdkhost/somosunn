@extends('admin.layouts.app')

@section('title', $sponsor->exists ? 'Editar Patrocinador' : 'Novo Patrocinador')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">{{ $sponsor->exists ? 'Editar patrocinador' : 'Novo patrocinador' }}</h1>
            <a href="{{ route('admin.sponsors.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ $sponsor->exists ? route('admin.sponsors.update', $sponsor) : route('admin.sponsors.store') }}">
                @csrf
                @if($sponsor->exists) @method('PUT') @endif
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Empresa</label>
                                <select name="company_id" class="form-control" required>
                                    <option value="">Selecione</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" @selected(old('company_id', $sponsor->company_id) == $company->id)>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Plano</label>
                                <select name="sponsor_plan_id" class="form-control" required>
                                    <option value="">Selecione</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" @selected(old('sponsor_plan_id', $sponsor->sponsor_plan_id) == $plan->id)>{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Inicio</label>
                                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($sponsor->starts_at)->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Fim</label>
                                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($sponsor->ends_at)->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    @foreach(['pending' => 'Pendente', 'active' => 'Ativo', 'expired' => 'Expirado', 'cancelled' => 'Cancelado'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $sponsor->status ?: 'pending') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Salvar patrocinador</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
