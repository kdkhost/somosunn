@extends('panel.layouts.app')

@section('title', 'Planos de Patrocinio')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Planos de patrocinio</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Pacotes comerciais para parceiros patrocinadores.</p>
            </div>
            <a href="{{ route('panel.admin.sponsor-plans.create') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Novo plano</a>
        </div>
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500"><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Preco</th><th class="px-6 py-4">Banners</th><th class="px-6 py-4">Eventos</th><th class="px-6 py-4">Leads</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Acoes</th></tr></thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @forelse($plans as $plan)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $plan->name }}</td>
                            <td class="px-6 py-4">R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $plan->max_banners }}</td>
                            <td class="px-6 py-4">{{ $plan->max_events }}</td>
                            <td class="px-6 py-4">{{ $plan->max_leads }}</td>
                            <td class="px-6 py-4"><span class="rounded-full {{ $plan->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }} px-3 py-1 text-xs font-bold">{{ $plan->active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('panel.admin.sponsor-plans.edit', $plan) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">Nenhum plano cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">{{ $plans->links() }}</div>
        </div>
    </div>
@endsection
