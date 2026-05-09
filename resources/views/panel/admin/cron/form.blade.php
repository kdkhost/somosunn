@extends('panel.layouts.app')

@section('title', $task->exists ? 'Editar tarefa cron' : 'Nova tarefa cron')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.cron.index') }}" class="hover:underline">Cron interno</a>
@endsection

@section('panel_content')
    @php
        $commonCommands = [
            'notifications:cleanup' => 'Limpeza de notificacoes',
            'auth:clear-resets' => 'Limpar tokens de senha',
            'sanctum:prune-expired' => 'Limpar tokens de API',
            'orders:cancel-unpaid' => 'Cancelar pedidos nao pagos',
            'abandoned-cart:send' => 'Disparo de carrinho abandonado',
            'subscriptions:check-expired' => 'Expirar planos vencidos',
            'queue:work --stop-when-empty --tries=3' => 'Processar fila de jobs',
        ];

        $commandValue = old('real_command', $task->command);
        $frequencyValue = old('real_frequency', $task->frequency ?: '* * * * *');
        $isCustomCommand = $commandValue && !array_key_exists($commandValue, $commonCommands);
    @endphp

    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ $task->exists ? 'Editar tarefa agendada' : 'Nova tarefa agendada' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Defina o comando artisan e a frequencia de execucao.
            </p>
        </div>

        @if($errors->any())
            <div
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-300">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $task->exists ? route('panel.admin.cron.update', $task) : route('panel.admin.cron.store') }}"
            class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @if($task->exists)
                @method('PUT')
            @endif

            <div class="space-y-2">
                <label for="command_select" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Comando artisan
                </label>
                <select id="command_select"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    @foreach($commonCommands as $command => $label)
                        <option value="{{ $command }}" @selected($commandValue === $command)>
                            {{ $label }} ({{ $command }})
                        </option>
                    @endforeach
                    <option value="custom" @selected($isCustomCommand)>Personalizado</option>
                </select>
                <input type="hidden" name="real_command" id="real_command" value="{{ $commandValue }}">
            </div>

            <div id="custom-command-wrap" class="{{ $isCustomCommand ? '' : 'hidden' }} space-y-2">
                <label for="command_custom" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Comando personalizado
                </label>
                <div
                    class="flex items-center overflow-hidden rounded-xl border border-slate-300 bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <span class="border-r border-slate-300 px-3 py-2 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        php artisan
                    </span>
                    <input type="text" id="command_custom" value="{{ $isCustomCommand ? $commandValue : '' }}"
                        placeholder="Ex: cache:clear"
                        class="w-full bg-transparent px-3 py-2 text-sm text-slate-900 focus:outline-none dark:text-slate-100">
                </div>
            </div>

            <div class="space-y-3">
                <label for="real_frequency" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Frequencia cron
                </label>
                <input type="text" name="real_frequency" id="real_frequency" value="{{ $frequencyValue }}"
                    placeholder="* * * * *"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-mono text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Formato: minuto hora dia_mes mes dia_semana
                </p>

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="button" data-cron="* * * * *"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        A cada minuto
                    </button>
                    <button type="button" data-cron="*/5 * * * *"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        A cada 5 min
                    </button>
                    <button type="button" data-cron="0 * * * *"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        A cada hora
                    </button>
                    <button type="button" data-cron="0 0 * * *"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Diario
                    </button>
                    <button type="button" data-cron="0 0 * * 0"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Semanal
                    </button>
                    <button type="button" data-cron="0 0 1 * *"
                        class="cron-preset rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Mensal
                    </button>
                </div>
            </div>

            <label class="inline-flex items-center gap-3">
                <input type="checkbox" name="active" value="1" @checked(old('active', $task->active ?? true))
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Ativar tarefa</span>
            </label>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end dark:border-slate-800">
                <a href="{{ route('panel.admin.cron.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-save text-xs"></i>
                    Salvar
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commandSelect = document.getElementById('command_select');
            const customWrap = document.getElementById('custom-command-wrap');
            const customInput = document.getElementById('command_custom');
            const realCommand = document.getElementById('real_command');
            const frequencyInput = document.getElementById('real_frequency');

            function syncCommandValue() {
                if (commandSelect.value === 'custom') {
                    customWrap.classList.remove('hidden');
                    realCommand.value = customInput.value.trim();
                    return;
                }

                customWrap.classList.add('hidden');
                realCommand.value = commandSelect.value;
            }

            commandSelect.addEventListener('change', syncCommandValue);
            customInput.addEventListener('input', syncCommandValue);

            document.querySelectorAll('.cron-preset').forEach((button) => {
                button.addEventListener('click', () => {
                    frequencyInput.value = button.dataset.cron;
                    frequencyInput.focus();
                });
            });

            syncCommandValue();
        });
    </script>
@endpush
