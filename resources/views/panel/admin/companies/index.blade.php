@extends('panel.layouts.app')

@section('title', 'Empresas')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Empresas</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Gestao empresarial integrada ao ecossistema.</p>
            </div>
            <a href="{{ route('panel.admin.companies.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">
                <i class="fas fa-plus"></i>
                <span>Nova empresa</span>
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60">
                    <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500">
                        <th class="px-6 py-4">Empresa</th>
                        <th class="px-6 py-4">Cidade</th>
                        <th class="px-6 py-4 text-center">Membros</th>
                        <th class="px-6 py-4 text-center">Patrocinios</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @forelse($companies as $company)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $company->name }}</div>
                                <div class="text-xs text-slate-500">{{ $company->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ trim(($company->city ? $company->city . ' / ' : '') . $company->state) ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">{{ $company->memberships_count }}</td>
                            <td class="px-6 py-4 text-center">{{ $company->sponsors_count }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $company->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $company->active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('companies.show', $company->slug) }}" target="_blank" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Publico</a>
                                <a href="{{ route('panel.admin.companies.edit', $company) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">Nenhuma empresa cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">{{ $companies->links() }}</div>
        </div>
    </div>
@endsection
