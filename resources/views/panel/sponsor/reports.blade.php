@extends('panel.layouts.app')

@section('title', 'Relatorios do Patrocinador')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Relatorios</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Sintese operacional do patrocinio atual.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Visualizacoes</div><div class="mt-3 text-3xl font-black">{{ $metrics['visualizacoes'] }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Cliques</div><div class="mt-3 text-3xl font-black">{{ $metrics['cliques'] }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">CTR</div><div class="mt-3 text-3xl font-black">{{ $metrics['ctr'] }}%</div></div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Leads</div><div class="mt-3 text-3xl font-black">{{ $metrics['leads'] }}</div></div>
            </div>
        </div>
    </div>
@endsection
