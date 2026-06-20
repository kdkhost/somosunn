@extends('admin.layouts.app')

@section('page_title', $task->exists ? 'Editar Tarefa Agendada' : 'Nova Tarefa Agendada')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cron.index') }}">Cron</a></li>
    <li class="breadcrumb-item active">{{ $task->exists ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
@php
    $commandGroups = (array) config('cron-panel.commands', []);
    $firstGroup = $commandGroups ? reset($commandGroups) : [];
    $firstCommand = is_array($firstGroup) ? array_key_first($firstGroup) : null;
    $knownCommands = collect($commandGroups)->flatMap(fn ($commands) => array_keys((array) $commands))->values()->all();
    $commandValue = old('real_command', $task->command ?: $firstCommand);
    $frequencyValue = old('real_frequency', $task->frequency ?: '* * * * *');
    $isCustomCommand = false;
@endphp

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $task->exists ? 'Editar' : 'Nova' }} Tarefa</h3>
    </div>

    <form method="POST" action="{{ $task->exists ? route('admin.cron.update', $task) : route('admin.cron.store') }}">
        @csrf
        @if($task->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="command_select">Comando Artisan</label>
                <select id="command_select" class="form-control select2" style="width: 100%;">
                    @foreach($commandGroups as $groupLabel => $commands)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($commands as $command => $label)
                                <option value="{{ $command }}" {{ $commandValue === $command ? 'selected' : '' }}>
                                    {{ $label }} ({{ $command }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="hidden" name="real_command" id="real_command" value="{{ $commandValue }}">
                <small class="text-muted">Somente comandos seguros e homologados podem ser agendados.</small>
            </div>

            <div class="form-group">
                <label for="real_frequency">Frequencia cron</label>
                <input type="text" name="real_frequency" id="real_frequency" value="{{ $frequencyValue }}"
                    class="form-control font-monospace" placeholder="* * * * *" required>
                <small class="text-muted">Formato: minuto hora dia_mes mes dia_semana. O fuso segue America/Sao_Paulo.</small>
            </div>

            <div class="form-group">
                <label>Presets rapidos</label>
                <div class="d-flex flex-wrap" style="gap: .5rem;">
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="* * * * *">A cada minuto</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="*/5 * * * *">A cada 5 minutos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="*/15 * * * *">A cada 15 minutos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="0 * * * *">A cada hora</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="0 0 * * *">Diario 00:00</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="0 3 * * *">Diario 03:00</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cron-preset" data-cron="0 4 * * 0">Domingo 04:00</button>
                </div>
            </div>

            <div class="custom-control custom-switch">
                <input type="checkbox" name="active" value="1" class="custom-control-input" id="active"
                    {{ old('active', $task->active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="active">Tarefa ativa</label>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.cron.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('command_select');
    const realInput = document.getElementById('real_command');
    const frequencyInput = document.getElementById('real_frequency');

    function syncCommand() {
        realInput.value = select.value;
    }

    select.addEventListener('change', syncCommand);

    document.querySelectorAll('.cron-preset').forEach(function (button) {
        button.addEventListener('click', function () {
            frequencyInput.value = this.dataset.cron || '* * * * *';
        });
    });

    syncCommand();
});
</script>
@endpush
