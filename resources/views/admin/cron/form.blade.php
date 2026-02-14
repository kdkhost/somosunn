@extends('admin.layouts.app')

@section('page_title', $task->exists ? 'Editar Tarefa Agendada' : 'Nova Tarefa Agendada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cron.index') }}">Cron</a></li>
    <li class="breadcrumb-item active">{{ $task->exists ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="card-title">{{ $task->exists ? 'Editar' : 'Nova' }} Tarefa</h3>
            </div>
            <form method="POST" action="{{ $task->exists ? route('admin.cron.update', $task) : route('admin.cron.store') }}">
                @csrf
                @if($task->exists) @method('PUT') @endif
                <div class="card-body">
                    <div class="form-group">
                        <label>Comando Artisan</label>
                        <input type="text" name="command" class="form-control" value="{{ old('command', $task->command) }}" required>
                        <small class="form-text text-muted">Exemplo: schedule:run, queue:work, custom:comando</small>
                    </div>
                    <div class="form-group">
                        <label>Frequência (cron)</label>
                        <input type="text" name="frequency" class="form-control" value="{{ old('frequency', $task->frequency) }}" required>
                        <small class="form-text text-muted">Exemplo: * * * * * (min hora dia mês semana)</small>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="activeCheck" {{ old('active', $task->active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activeCheck">Ativa</label>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="{{ route('admin.cron.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
