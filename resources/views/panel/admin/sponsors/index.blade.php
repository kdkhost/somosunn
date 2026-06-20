@extends('panel.layouts.app')

@section('title', 'Patrocinadores')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Patrocinadores</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Controle de patrocinio, contratos e publicacao.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('panel.admin.sponsor-plans.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Planos</a>
                <a href="{{ route('panel.admin.sponsor-banners.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Banners</a>
                <a href="{{ route('panel.admin.sponsors.create') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Novo patrocinador</a>
            </div>
        </div>
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500"><th class="px-6 py-4">Empresa</th><th class="px-6 py-4">Plano</th><th class="px-6 py-4">Inicio</th><th class="px-6 py-4">Fim</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Acoes</th></tr></thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @forelse($sponsors as $sponsor)
                        <tr>
                            <td class="px-6 py-4">{{ $sponsor->company?->name ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $sponsor->plan?->name ?: '-' }}</td>
                            <td class="px-6 py-4">{{ optional($sponsor->starts_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td class="px-6 py-4">{{ optional($sponsor->ends_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td class="px-6 py-4"><span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">{{ $sponsor->status }}</span></td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('panel.admin.sponsors.edit', $sponsor) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">Nenhum patrocinador cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">{{ $sponsors->links() }}</div>
        </div>
    </div>
@endsection
