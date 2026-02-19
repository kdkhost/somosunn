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
                <form method="POST"
                    action="{{ $task->exists ? route('admin.cron.update', $task) : route('admin.cron.store') }}">
                    @csrf
                    @if($task->exists) @method('PUT') @endif
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tarefa (Comando)</label>
                            <select name="command_select" id="command_select" class="form-control"
                                onchange="toggleCustomCommand()">
                                <option value="notifications:cleanup" {{ $task->command == 'notifications:cleanup' ? 'selected' : '' }}>Limpeza de Notificações (30 dias)</option>
                                <option value="queue:work --stop-when-empty --tries=3" {{ $task->command == 'queue:work --stop-when-empty --tries=3' ? 'selected' : '' }}>Processar Fila (Emails/Uploads)
                                </option>
                                <option value="orders:cancel-unpaid" {{ $task->command == 'orders:cancel-unpaid' ? 'selected' : '' }}>Cancelar Pedidos Não Pagos (>48h)</option>
                                <option value="orders:abandoned-cart" {{ $task->command == 'orders:abandoned-cart' ? 'selected' : '' }}>Email Carrinho Abandonado (>24h)</option>
                                <option value="subscriptions:check-expired" {{ $task->command == 'subscriptions:check-expired' ? 'selected' : '' }}>Expira Planos Vencidos</option>
                                <option value="auth:clear-resets" {{ $task->command == 'auth:clear-resets' ? 'selected' : '' }}>Limpar Tokens de Senha</option>
                                <option value="sanctum:prune-expired" {{ $task->command == 'sanctum:prune-expired' ? 'selected' : '' }}>Limpar Tokens de API (Sanctum)</option>
                                <option value="custom" {{ !in_array($task->command, ['notifications:cleanup', 'queue:work --stop-when-empty --tries=3', 'orders:cancel-unpaid', 'orders:abandoned-cart', 'subscriptions:check-expired', 'auth:clear-resets', 'sanctum:prune-expired']) && $task->exists ? 'selected' : '' }}>Outro (Personalizado)</option>
                            </select>
                        </div>
                        <div class="form-group {{ !in_array($task->command, ['notifications:cleanup', 'queue:work --stop-when-empty --tries=3', 'orders:cancel-unpaid', 'orders:abandoned-cart', 'subscriptions:check-expired', 'auth:clear-resets', 'sanctum:prune-expired']) && $task->exists ? '' : 'd-none' }}"
                            id="custom_command_div">
                            <label>Comando Personalizado</label>
                            <input type="text" name="command_custom" id="command_custom" class="form-control"
                                value="{{ old('command', $task->command) }}" placeholder="ex: schedule:run">
                        </div>

                        // Let's use JS to populate a hidden input 'command' and 'frequency' before submit

                        let finalCmd = cmdSelect.value === 'custom' ? cmdCustom.value : cmdSelect.value;
                        let finalFreq = document.getElementById('frequency_select').value === 'custom' ?
                        document.getElementById('frequency_custom').value :
                        document.getElementById('frequency_select').value;

                        // We can't easily change the request payload without hidden fields if we want to keep controller
                        same.
                        // BUT, I'll update the controller to handle this or use the existing 'command' input if I can reuse
                        it.

                        // Let's create hidden fields if they don't exist, or update the existing ones if we change the
                        layout.
                        // Start SIMPLE: Rename the visible inputs to something else, and use hidden inputs for the real
                        submission.
                        });

                        // Wait, simpler approach:
                        // Update controller to look for 'command_select' and 'command_custom'.
                        // But I can't update controller easily in this step (single file edit tool).
                        // I'll stick to JS populating the input.
                        </script>

                        <!-- Hidden inputs for actual submission -->
                        <input type="hidden" name="command" id="real_command" value="{{ $task->command }}">
                        <input type="hidden" name="frequency" id="real_frequency" value="{{ $task->frequency }}">

                        <script>
                            // Overwrite previous script to be cleaner
                            document.querySelector('form').addEventListener('submit', function (e) {
                                const cmdSelect = document.getElementById('command_select');
                                const cmdCustom = document.getElementById('command_custom');
                                const freqSelect = document.getElementById('frequency_select');
                                const freqCustom = document.getElementById('frequency_custom');

                                document.getElementById('real_command').value = cmdSelect.value === 'custom' ? cmdCustom.value : cmdSelect.value;
                                document.getElementById('real_frequency').value = freqSelect.value === 'custom' ? freqCustom.value : freqSelect.value;
                            });
                        </script>
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