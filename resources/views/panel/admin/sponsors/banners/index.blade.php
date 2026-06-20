@extends('panel.layouts.app')

@section('title', 'Banners Patrocinados')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Banners patrocinados</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Criativos ativos por posicao do portal.</p>
            </div>
            <a href="{{ route('panel.admin.sponsor-banners.create') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Novo banner</a>
        </div>
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500"><th class="px-6 py-4">Titulo</th><th class="px-6 py-4">Empresa</th><th class="px-6 py-4">Posicao</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Acoes</th></tr></thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @forelse($banners as $banner)
                        <tr>
                            <td class="px-6 py-4">{{ $banner->title }}</td>
                            <td class="px-6 py-4">{{ $banner->sponsor?->company?->name ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $banner->position }}</td>
                            <td class="px-6 py-4"><span class="rounded-full {{ $banner->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }} px-3 py-1 text-xs font-bold">{{ $banner->active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('panel.admin.sponsor-banners.edit', $banner) }}" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Nenhum banner cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">{{ $banners->links() }}</div>
        </div>
    </div>
@endsection
