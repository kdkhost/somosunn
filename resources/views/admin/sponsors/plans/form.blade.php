@extends('admin.layouts.app')

@section('title', $plan->exists ? 'Editar Plano de Patrocinio' : 'Novo Plano de Patrocinio')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">{{ $plan->exists ? 'Editar plano de patrocinio' : 'Novo plano de patrocinio' }}</h1>
            <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ $plan->exists ? route('admin.sponsor-plans.update', $plan) : route('admin.sponsor-plans.store') }}">
                @csrf
                @if($plan->exists) @method('PUT') @endif
                <div class="card"><div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nome</label><input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required></div>
                        <div class="col-md-2 form-group"><label>Preco</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required></div>
                        <div class="col-md-2 form-group"><label>Banners</label><input type="number" name="max_banners" class="form-control" value="{{ old('max_banners', $plan->max_banners ?? 0) }}" required></div>
                        <div class="col-md-2 form-group"><label>Eventos</label><input type="number" name="max_events" class="form-control" value="{{ old('max_events', $plan->max_events ?? 0) }}" required></div>
                        <div class="col-md-2 form-group"><label>Leads</label><input type="number" name="max_leads" class="form-control" value="{{ old('max_leads', $plan->max_leads ?? 0) }}" required></div>
                        <div class="col-md-2 form-group"><label>Prioridade</label><input type="number" name="priority" class="form-control" value="{{ old('priority', $plan->priority ?? 0) }}" required></div>
                        <div class="col-md-10 d-flex align-items-center"><div class="custom-control custom-switch mt-4"><input type="checkbox" class="custom-control-input" id="active" name="active" value="1" @checked(old('active', $plan->active ?? true))><label class="custom-control-label" for="active">Plano ativo</label></div></div>
                    </div>
                </div><div class="card-footer text-right"><button type="submit" class="btn btn-primary">Salvar plano</button></div></div>
            </form>
        </div>
    </section>
@endsection
