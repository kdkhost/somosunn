@extends('panel.layouts.app')

@section('title', $plan->exists ? 'Editar Plano de Patrocinio' : 'Novo Plano de Patrocinio')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $plan->exists ? 'Editar plano de patrocinio' : 'Novo plano de patrocinio' }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Defina capacidade comercial do patrocinio.</p>
            </div>
            <a href="{{ route('panel.admin.sponsor-plans.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Voltar</a>
        </div>
        <form method="POST" action="{{ $plan->exists ? route('panel.admin.sponsor-plans.update', $plan) : route('panel.admin.sponsor-plans.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @if($plan->exists) @method('PUT') @endif
            <div class="grid gap-5 md:grid-cols-3">
                <div><label class="mb-2 block text-sm font-semibold">Nome</label><input type="text" name="name" value="{{ old('name', $plan->name) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Preco</label><input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Prioridade</label><input type="number" name="priority" value="{{ old('priority', $plan->priority ?? 0) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Max. banners</label><input type="number" name="max_banners" value="{{ old('max_banners', $plan->max_banners ?? 0) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Max. eventos</label><input type="number" name="max_events" value="{{ old('max_events', $plan->max_events ?? 0) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Max. leads</label><input type="number" name="max_leads" value="{{ old('max_leads', $plan->max_leads ?? 0) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div class="flex items-center gap-3"><input type="checkbox" name="active" value="1" @checked(old('active', $plan->active ?? true))><span class="text-sm font-semibold">Plano ativo</span></div>
            </div>
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Salvar plano</button></div>
        </form>
    </div>
@endsection
