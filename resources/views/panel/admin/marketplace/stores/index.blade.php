@extends('panel.layouts.app')

@section('title', 'Lojas do marketplace - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <h1 class="text-3xl font-black text-slate-900 dark:text-white">Lojas do marketplace</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Modere a publicacao das lojas dos vendedores sem alterar os slugs reservados.</p>
        </div>

        <form method="GET" class="rounded-3xl border border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 shadow-sm">
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por marca, slug ou vendedor" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
        </form>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Marca</th>
                        <th class="px-4 py-3 text-left">Slug</th>
                        <th class="px-4 py-3 text-left">Vendedor</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Acao</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($stores as $store)
                        <tr>
                            <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">{{ $store->brand_name }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $store->slug ?: '-' }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $store->user->name ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $store->is_blocked ? 'Bloqueada' : ($store->is_published ? 'Publicada' : 'Rascunho') }}</td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('panel.admin.marketplace.stores.toggle', ['store' => $store->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="is_blocked" value="{{ $store->is_blocked ? 0 : 1 }}">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-xs font-black {{ $store->is_blocked ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-300' }}">
                                        {{ $store->is_blocked ? 'Desbloquear' : 'Bloquear' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Nenhuma loja encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $stores->links() }}</div>
    </div>
@endsection
