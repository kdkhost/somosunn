@extends('panel.layouts.app')

@section('title', 'Cron interno')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.dashboard') }}" class="hover:underline">Administracao</a>
@endsection

@section('panel_content')
    @php
        $lastHeartbeat = \Illuminate\Support\Facades\Cache::get('cron_heartbeat');
        if ($lastHeartbeat && !($lastHeartbeat instanceof \Carbon\CarbonInterface)) {
            try {
                $lastHeartbeat = \Carbon\Carbon::parse($lastHeartbeat);
            } catch (\Throwable $e) {
                $lastHeartbeat = null;
            }
        }

        $isRunning = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Cron interno</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Gerencie tarefas agendadas executadas internamente pela plataforma.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($isRunning)
                    <span
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-800/50 dark:bg-emerald-900/20 dark:text-emerald-300">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                        Scheduler ativo {{ $lastHeartbeat->format('d/m/Y H:i') }}
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 dark:border-amber-800/50 dark:bg-amber-900/20 dark:text-amber-300">
                        <i class="fas fa-exclamation-triangle"></i>
                        Scheduler nao detectado
                    </span>
                @endif

                <a href="{{ route('panel.admin.cron.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-plus text-xs"></i>
                    Nova tarefa
                </a>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/70">
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">Comando</th>
                            <th class="px-4 py-3 font-semibold">Frequencia</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Ultima execucao</th>
                            <th class="px-4 py-3 font-semibold">Proxima execucao</th>
                            <th class="px-4 py-3 font-semibold text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">#{{ $task->id }}</td>
                                <td class="px-4 py-3">
                                    <code
                                        class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $task->command }}</code>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $task->frequency }}</td>
                                <td class="px-4 py-3">
                                    @if($task->active)
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Ativa</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-300">Inativa</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $task->last_run_at ? $task->last_run_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    @php
                                        try {
                                            $cron = new \Cron\CronExpression($task->frequency);
                                            $nextRun = \Illuminate\Support\Carbon::instance($cron->getNextRunDate());
                                            echo e($nextRun->format('d/m/Y H:i'));
                                        } catch (\Throwable $e) {
                                            echo '<span class="text-rose-500">Invalido</span>';
                                        }
                                    @endphp
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('panel.admin.cron.run', $task) }}"
                                            onsubmit="return confirm('Executar esta tarefa agora?');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800/50 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                                                title="Executar">
                                                <i class="fas fa-play text-xs"></i>
                                            </button>
                                        </form>

                                        <a href="{{ route('panel.admin.cron.edit', $task) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-2 text-blue-700 transition hover:bg-blue-100 dark:border-blue-800/50 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                            title="Editar">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>

                                        <a href="{{ route('panel.admin.cron.logs', $task) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                            title="Logs">
                                            <i class="fas fa-list text-xs"></i>
                                        </a>

                                        <form method="POST" action="{{ route('panel.admin.cron.destroy', $task) }}"
                                            onsubmit="return confirm('Excluir esta tarefa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-2 text-rose-700 transition hover:bg-rose-100 dark:border-rose-800/50 dark:bg-rose-900/20 dark:text-rose-300 dark:hover:bg-rose-900/30"
                                                title="Excluir">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhuma tarefa agendada cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
