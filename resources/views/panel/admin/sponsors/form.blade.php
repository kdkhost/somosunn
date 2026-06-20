@extends('panel.layouts.app')

@section('title', $sponsor->exists ? 'Editar Patrocinador' : 'Novo Patrocinador')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $sponsor->exists ? 'Editar patrocinador' : 'Novo patrocinador' }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Vincule empresa, plano e ciclo do patrocinio.</p>
            </div>
            <a href="{{ route('panel.admin.sponsors.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Voltar</a>
        </div>

        <form method="POST" action="{{ $sponsor->exists ? route('panel.admin.sponsors.update', $sponsor) : route('panel.admin.sponsors.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @if($sponsor->exists) @method('PUT') @endif
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-semibold">Empresa</label><select name="company_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required><option value="">Selecione</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $sponsor->company_id) == $company->id)>{{ $company->name }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-semibold">Plano</label><select name="sponsor_plan_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required><option value="">Selecione</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected(old('sponsor_plan_id', $sponsor->sponsor_plan_id) == $plan->id)>{{ $plan->name }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-semibold">Inicio</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($sponsor->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Fim</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($sponsor->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Status</label><select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">@foreach(['pending' => 'Pendente', 'active' => 'Ativo', 'expired' => 'Expirado', 'cancelled' => 'Cancelado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $sponsor->status ?: 'pending') === $value)>{{ $label }}</option>@endforeach</select></div>
            </div>
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Salvar patrocinador</button></div>
        </form>
    </div>
@endsection
