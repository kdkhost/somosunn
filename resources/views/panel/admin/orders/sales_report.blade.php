@extends('panel.layouts.app')

@section('title', 'Relatorio de vendas por item')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.orders.index') }}" class="hover:underline">Vendas</a>
    <span class="mx-2 text-slate-300">/</span>
    <span class="font-bold text-slate-600 dark:text-slate-400">Relatorio por item</span>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Relatorio de vendas por item</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consolida as vendas pagas por produto, curso, mentoria, evento e expositor.</p>
            </div>
            <a href="{{ route('panel.admin.orders.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar para vendas</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Itens com venda</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ (int) ($summary['catalog_items_count'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Unidades vendidas</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ (int) ($summary['total_units_sold'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Compradores</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ (int) ($summary['total_buyers_count'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Faturamento liquido</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">R$ {{ number_format((float) ($summary['total_revenue'] ?? 0), 2, ',', '.') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('panel.admin.orders.sales-report') }}"
              class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <label class="space-y-2 xl:col-span-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Busca</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Titulo do item"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Tipo</span>
                    <select name="sale_type" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-sm text-slate-900 dark:text-white">
                        <option value="">Todos</option>
                        @foreach($saleTypeLabels as $key => $label)
                            <option value="{{ $key }}" {{ $saleType === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">De</span>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Ate</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </label>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                    <span>Atualizar relatorio</span>
                </button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4">Item</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4 text-center">Vendidos</th>
                            <th class="px-6 py-4 text-center">Pedidos</th>
                            <th class="px-6 py-4 text-center">Compradores</th>
                            <th class="px-6 py-4 text-right">Faturamento liquido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($rows as $row)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $row->title }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">ID {{ $row->item_id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 dark:bg-blue-900/30 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300">
                                        {{ $saleTypeLabels[$row->item_type] ?? ucfirst($row->item_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">{{ (int) $row->units_sold }}</td>
                                <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-300">{{ (int) $row->orders_count }}</td>
                                <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-300">{{ (int) $row->buyers_count }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">R$ {{ number_format((float) $row->net_revenue, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    Nenhuma venda encontrada para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
