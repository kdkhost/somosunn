@extends('panel.layouts.app')

@section('title', 'Logs da tarefa')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.cron.index') }}" class="hover:underline">Cron interno</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Logs da tarefa</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    <code
                        class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $task->command }}</code>
                </p>
            </div>
            <a href="{{ route('panel.admin.cron.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <i class="fas fa-arrow-left text-xs"></i>
                Voltar
            </a>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/70">
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3 font-semibold">Execucao</th>
                            <th class="px-4 py-3 font-semibold">Sucesso</th>
                            <th class="px-4 py-3 font-semibold">Saida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($logs as $log)
                            <tr class="align-top hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $log->executed_at ? $log->executed_at->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($log->success)
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            Sim
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                            Nao
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <pre
                                        class="max-h-72 overflow-auto whitespace-pre-wrap rounded-xl bg-slate-950 p-3 text-xs text-slate-100">{{ $log->output }}</pre>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhum log encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
