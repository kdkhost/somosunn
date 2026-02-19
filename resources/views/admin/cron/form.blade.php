@extends('admin.layouts.app')

@section('page_title', $task->exists ? 'Editar Tarefa Agendada' : 'Nova Tarefa Agendada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cron.index') }}">Cron</a></li>
    <li class="breadcrumb-item active">{{ $task->exists ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $task->exists ? 'Editar' : 'Nova' }} Tarefa</h3>
    </div>
    <form method="POST" action="{{ $task->exists ? route('admin.cron.update', $task) : route('admin.cron.store') }}">
        @csrf
        @if($task->exists) @method('PUT') @endif

        <div class="card-body">
            <!-- Comando -->
            <div class="form-group">
                <label>Comando Artisan</label>
                <select id="command_select" class="form-control select2" style="width: 100%;"
                    onchange="toggleCustomCommand()">
                    <optgroup label="Manutenção do Sistema">
                        <option value="notifications:cleanup" {{ $task->command == 'notifications:cleanup' ? 'selected' : '' }}>Limpeza de Notificações (notifications:cleanup)</option>
                        <option value="auth:clear-resets" {{ $task->command == 'auth:clear-resets' ? 'selected' : '' }}>
                            Limpar Tokens de Senha (auth:clear-resets)</option>
                        <option value="sanctum:prune-expired" {{ $task->command == 'sanctum:prune-expired' ? 'selected' : '' }}>Limpar Tokens de API (sanctum:prune-expired)</option>
                    </optgroup>
                    <optgroup label="Pedidos e Vendas">
                        <option value="orders:cancel-unpaid" {{ $task->command == 'orders:cancel-unpaid' ? 'selected' : '' }}>Cancelar Pedidos Não Pagos (orders:cancel-unpaid)</option>
                        <option value="orders:abandoned-cart" {{ $task->command == 'orders:abandoned-cart' ? 'selected' : '' }}>Email Carrinho Abandonado (orders:abandoned-cart)</option>
                        <option value="subscriptions:check-expired" {{ $task->command == 'subscriptions:check-expired' ? 'selected' : '' }}>Expira Planos Vencidos (subscriptions:check-expired)</option>
                    </optgroup>
                    <optgroup label="Infraestrutura">
                        <option value="queue:work --stop-when-empty --tries=3" {{ $task->command == 'queue:work --stop-when-empty --tries=3' ? 'selected' : '' }}>Processar Fila de Jobs (queue:work)
                        </option>
                    </optgroup>
                    <option value="custom" {{ !in_array($task->command, ['notifications:cleanup', 'queue:work --stop-when-empty --tries=3', 'orders:cancel-unpaid', 'orders:abandoned-cart', 'subscriptions:check-expired', 'auth:clear-resets', 'sanctum:prune-expired']) && $task->exists ? 'selected' : '' }}>Outro (Personalizado)</option>
                </select>
            </div>

            <!-- Custom Command Input -->
            <div class="form-group {{ !in_array($task->command, ['notifications:cleanup', 'queue:work --stop-when-empty --tries=3', 'orders:cancel-unpaid', 'orders:abandoned-cart', 'subscriptions:check-expired', 'auth:clear-resets', 'sanctum:prune-expired']) && $task->exists ? '' : 'd-none' }}"
                id="custom_command_div">
                <label>Comando Personalizado</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">php artisan</span>
                    </div>
                    <input type="text" name="command_custom" id="command_custom" class="form-control"
                        value="{{ $task->command }}" placeholder="Ex: cache:clear">
                </div>
                <small class="text-muted">Digite apenas o nome do comando e argumentos.</small>
            </div>

            <!-- Input real que vai pro banco -->
            <input type="hidden" name="real_command" id="real_command" value="{{ old('command', $task->command) }}">

            <hr>

            <!-- Frequência (Novo Layout Avançado) -->
            <div class="form-group mb-4">
                <label>Frequência de Execução</label>

                <!-- Hidden Real Frequency (o que vai pro banco) -->
                <input type="hidden" name="real_frequency" id="real_frequency"
                    value="{{ old('real_frequency', $task->frequency ?? '* * * * *') }}">

                <div class="card card-outline card-secondary">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="freq-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-presets" data-toggle="pill" href="#content-presets"
                                    role="tab" aria-controls="content-presets" aria-selected="true"
                                    onclick="setMode('presets')">Presets (Simples)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-advanced" data-toggle="pill" href="#content-advanced"
                                    role="tab" aria-controls="content-advanced" aria-selected="false"
                                    onclick="setMode('advanced')">Construtor Avançado</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-raw" data-toggle="pill" href="#content-raw" role="tab"
                                    aria-controls="content-raw" aria-selected="false" onclick="setMode('raw')">Código
                                    Cron (Raw)</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="freq-tabs-content">

                            <!-- ABA PRESETS -->
                            <div class="tab-pane fade show active" id="content-presets" role="tabpanel"
                                aria-labelledby="tab-presets">
                                <label>Escolha uma frequência comum:</label>
                                <select id="frequency_preset" class="form-control" onchange="updateFromPreset()">
                                    <option value="* * * * *">A cada minuto (* * * * *)</option>
                                    <option value="*/5 * * * *">A cada 5 minutos (*/5 * * * *)</option>
                                    <option value="0 * * * *">A cada hora (0 * * * *)</option>
                                    <option value="0 0 * * *">Diariamente à meia-noite (0 0 * * *)</option>
                                    <option value="0 0 * * 0">Semanalmente (Domingo 00:00) (0 0 * * 0)</option>
                                    <option value="0 0 1 * *">Mensalmente (Dia 1 às 00:00) (0 0 1 * *)</option>
                                </select>
                            </div>

                            <!-- ABA AVANÇADO (BUILDER) -->
                            <div class="tab-pane fade" id="content-advanced" role="tabpanel"
                                aria-labelledby="tab-advanced">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Minutos</label>
                                        <select id="adv_min_type" class="form-control mb-2"
                                            onchange="toggleAdvInput('min')">
                                            <option value="*">A cada minuto (*)</option>
                                            <option value="interval">A cada X minutos (*/X)</option>
                                            <option value="specific">Minuto específico (X)</option>
                                        </select>
                                        <input type="number" id="adv_min_val" class="form-control d-none" min="0"
                                            max="59" placeholder="0-59" onchange="buildCron()">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Horas</label>
                                        <select id="adv_hour_type" class="form-control mb-2"
                                            onchange="toggleAdvInput('hour')">
                                            <option value="*">A cada hora (*)</option>
                                            <option value="interval">A cada X horas (*/X)</option>
                                            <option value="specific">Hora específica (X)</option>
                                        </select>
                                        <input type="number" id="adv_hour_val" class="form-control d-none" min="0"
                                            max="23" placeholder="0-23" onchange="buildCron()">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Dia do Mês</label>
                                        <select id="adv_dom_type" class="form-control mb-2"
                                            onchange="toggleAdvInput('dom')">
                                            <option value="*">Todos os dias (*)</option>
                                            <option value="specific">Dia específico (X)</option>
                                        </select>
                                        <input type="number" id="adv_dom_val" class="form-control d-none" min="1"
                                            max="31" placeholder="1-31" onchange="buildCron()">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Mês</label>
                                        <select id="adv_month_type" class="form-control mb-2"
                                            onchange="toggleAdvInput('month')">
                                            <option value="*">Todos os meses (*)</option>
                                            <option value="specific">Mês específico</option>
                                        </select>
                                        <select id="adv_month_val" class="form-control d-none" onchange="buildCron()">
                                            <option value="1">Janeiro</option>
                                            <option value="2">Fevereiro</option>
                                            <option value="3">Março</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Maio</option>
                                            <option value="6">Junho</option>
                                            <option value="7">Julho</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Setembro</option>
                                            <option value="10">Outubro</option>
                                            <option value="11">Novembro</option>
                                            <option value="12">Dezembro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Dia da Semana</label>
                                        <select id="adv_dow_type" class="form-control mb-2"
                                            onchange="toggleAdvInput('dow')">
                                            <option value="*">Todos os dias (*)</option>
                                            <option value="specific">Dia específico</option>
                                        </select>
                                        <select id="adv_dow_val" class="form-control d-none" onchange="buildCron()">
                                            <option value="0">Domingo</option>
                                            <option value="1">Segunda</option>
                                            <option value="2">Terça</option>
                                            <option value="3">Quarta</option>
                                            <option value="4">Quinta</option>
                                            <option value="5">Sexta</option>
                                            <option value="6">Sábado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA RAW (CÓDIGO PURO) -->
                            <div class="tab-pane fade" id="content-raw" role="tabpanel" aria-labelledby="tab-raw">
                                <label>Expressão Cron:</label>
                                <input type="text" id="raw_expression" class="form-control font-monospace"
                                    placeholder="* * * * *" onkeyup="updateFromRaw()">
                                <small class="text-muted">Formato:
                                    <code>minuto hora dia_mes mes dia_semana</code></small>
                            </div>
                        </div>

                        <div class="mt-3 p-2 bg-light border rounded">
                            <strong>Resultado Final: </strong> <code id="cron_preview"
                                class="h5 text-primary ml-2">* * * * *</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $task->active ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="active">Ativo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Agendamento</button>
            <a href="{{ route('admin.cron.index') }}" class="btn btn-default float-right">Cancelar</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    // Init state
    let currentMode = 'presets';

    $(document).ready(function () {
        // Load initial value
        let initialCron = $('#real_frequency').val();
        if (!initialCron) initialCron = '* * * * *';

        // Try to match preset
        let presetMatch = $(`#frequency_preset option[value='${initialCron}']`);
        if (presetMatch.length > 0) {
            $('#frequency_preset').val(initialCron);
            setMode('presets');
            $('#tab-presets').tab('show');
        } else {
            // Default to raw if not strict preset
            $('#raw_expression').val(initialCron);
            setMode('raw');
            $('#tab-raw').tab('show');
        }
        updatePreview(initialCron);

        // Command Init
        toggleCustomCommand();
    });

    function setMode(mode) {
        currentMode = mode;
        if (mode === 'presets') updateFromPreset();
        if (mode === 'advanced') buildCron();
        if (mode === 'raw') updateFromRaw();
    }

    function updateFromPreset() {
        let val = $('#frequency_preset').val();
        $('#real_frequency').val(val);
        $('#raw_expression').val(val); // sync raw
        updatePreview(val);
    }

    function updateFromRaw() {
        let val = $('#raw_expression').val();
        $('#real_frequency').val(val);
        updatePreview(val);
    }

    function toggleAdvInput(type) {
        let selectId = '#adv_' + type + '_type';
        let inputId = '#adv_' + type + '_val';
        let val = $(selectId).val();

        if (val === '*') {
            $(inputId).addClass('d-none');
        } else {
            $(inputId).removeClass('d-none');
        }
        buildCron();
    }

    function buildCron() {
        // Min
        let minType = $('#adv_min_type').val();
        let minVal = $('#adv_min_val').val() || 0;
        let minStr = minType === '*' ? '*' : (minType === 'interval' ? '*/' + minVal : minVal);

        // Hour
        let hourType = $('#adv_hour_type').val();
        let hourVal = $('#adv_hour_val').val() || 0;
        let hourStr = hourType === '*' ? '*' : (hourType === 'interval' ? '*/' + hourVal : hourVal);

        // DOM
        let domType = $('#adv_dom_type').val();
        let domVal = $('#adv_dom_val').val() || 1;
        let domStr = domType === '*' ? '*' : domVal;

        // Month
        let monthType = $('#adv_month_type').val();
        let monthVal = $('#adv_month_val').val() || 1;
        let monthStr = monthType === '*' ? '*' : monthVal;

        // DOW
        let dowType = $('#adv_dow_type').val();
        let dowVal = $('#adv_dow_val').val() || 0;
        let dowStr = dowType === '*' ? '*' : dowVal;

        let cron = `${minStr} ${hourStr} ${domStr} ${monthStr} ${dowStr}`;
        $('#real_frequency').val(cron);
        $('#raw_expression').val(cron); // sync raw
        updatePreview(cron);
    }

    function updatePreview(val) {
        $('#cron_preview').text(val);
    }

    function toggleCustomCommand() {
        var select = document.getElementById('command_select');
        var customDiv = document.getElementById('custom_command_div');
        var customInput = document.getElementById('command_custom');
        var realInput = document.getElementById('real_command');

        if (select.value === 'custom') {
            customDiv.classList.remove('d-none');
            realInput.value = customInput.value;
        } else {
            customDiv.classList.add('d-none');
            realInput.value = select.value;
        }
    }

    // Listener for custom command input
    document.getElementById('command_custom').addEventListener('input', function (e) {
        document.getElementById('real_command').value = e.target.value;
    });
</script>
@stop