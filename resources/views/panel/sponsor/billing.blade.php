@extends('panel.layouts.app')

@section('title', 'Financeiro do Patrocinador')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Financeiro</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Resumo do contrato de patrocinio de {{ $sponsor->company?->name }}.</p>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Plano</div><div class="mt-3 text-2xl font-black">{{ $sponsor->plan?->name ?: '-' }}</div></div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Valor do plano</div><div class="mt-3 text-2xl font-black">R$ {{ number_format((float) ($sponsor->plan?->price ?? 0), 2, ',', '.') }}</div></div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="text-xs uppercase tracking-[0.18em] text-slate-400">Status</div><div class="mt-3 text-2xl font-black text-blue-600">{{ strtoupper($sponsor->status) }}</div></div>
        </div>
    </div>
@endsection
