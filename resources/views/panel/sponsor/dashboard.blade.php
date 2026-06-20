@extends('panel.layouts.app')

@section('title', 'Painel do Patrocinador')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Patrocinador</div>
                    <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $sponsor->company?->name }}</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Plano {{ $sponsor->plan?->name ?: 'Nao informado' }} • status {{ $sponsor->status }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-5 py-4 text-sm dark:bg-slate-950">
                    <div class="text-slate-500">Ciclo atual</div>
                    <div class="mt-1 font-bold text-slate-900 dark:text-white">{{ optional($sponsor->starts_at)->format('d/m/Y') ?: '-' }} ate {{ optional($sponsor->ends_at)->format('d/m/Y') ?: 'em aberto' }}</div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach([
                'Visualizacoes' => $metrics['visualizacoes'],
                'Cliques' => $metrics['cliques'],
                'CTR' => $metrics['ctr'] . '%',
                'Leads' => $metrics['leads'],
                'Eventos patrocinados' => $metrics['eventos'],
                'Faturas' => $metrics['faturas'],
                'Renovacoes' => $metrics['renovacoes'],
            ] as $label => $value)
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ $label }}</div>
                    <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
