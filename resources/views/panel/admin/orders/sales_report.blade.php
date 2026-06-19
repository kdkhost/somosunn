@extends('panel.layouts.app')

@section('title', 'Relatorio de vendas por item')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.orders.index') }}" class="hover:underline">Vendas</a>
    <span class="mx-2 text-slate-300">/</span>
    <span class="font-bold text-slate-600 dark:text-slate-400">Relatorio por item</span>
@endsection

@push('styles')
    <style>
        .sales-buyers-panel {
            color: #0f172a;
        }

        .sales-buyers-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .sales-buyers-k {
            color: #64748b;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .sales-buyers-title {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .sales-buyers-meta {
            color: #64748b;
            font-size: .85rem;
            margin-top: .2rem;
        }

        .sales-buyers-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .sales-buyers-summary > div {
            border: 1px solid #dbe3ef;
            border-radius: .75rem;
            background: #f8fafc;
            padding: .75rem;
        }

        .sales-buyers-summary span,
        .sales-buyers-table small {
            display: block;
            color: #64748b;
            font-size: .75rem;
        }

        .sales-buyers-summary strong {
            display: block;
            font-size: 1rem;
        }

        .sales-buyers-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .sales-buyers-table th,
        .sales-buyers-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem;
            text-align: left;
            vertical-align: top;
        }

        .sales-buyers-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sales-buyers-empty {
            color: #64748b;
            padding: 2rem !important;
        }

        html.dark .sales-buyers-panel,
        .dark .sales-buyers-panel {
            color: #e2e8f0;
        }

        html.dark .sales-buyers-meta,
        html.dark .sales-buyers-k,
        html.dark .sales-buyers-summary span,
        html.dark .sales-buyers-table small,
        html.dark .sales-buyers-empty,
        .dark .sales-buyers-meta,
        .dark .sales-buyers-k,
        .dark .sales-buyers-summary span,
        .dark .sales-buyers-table small,
        .dark .sales-buyers-empty {
            color: #94a3b8;
        }

        html.dark .sales-buyers-summary > div,
        .dark .sales-buyers-summary > div {
            background: #0f172a;
            border-color: #334155;
        }

        html.dark .sales-buyers-table th,
        .dark .sales-buyers-table th {
            background: #020617;
            color: #cbd5e1;
        }

        html.dark .sales-buyers-table th,
        html.dark .sales-buyers-table td,
        .dark .sales-buyers-table th,
        .dark .sales-buyers-table td {
            border-color: #1e293b;
        }

        @media (max-width: 768px) {
            .sales-buyers-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

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
                            <th class="px-6 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($rows as $row)
                            @php
                                $buyerRouteParams = array_merge(request()->query(), [
                                    'item_type' => (string) $row->item_type,
                                    'item_id' => (int) $row->item_id,
                                ]);
                            @endphp
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
                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 text-xs font-black text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40"
                                            data-sales-report-buyers
                                            data-url="{{ route('panel.admin.orders.sales-report.buyers', $buyerRouteParams) }}"
                                            data-print-url="{{ route('panel.admin.orders.sales-report.buyers.print', $buyerRouteParams) }}"
                                            data-pdf-url="{{ route('panel.admin.orders.sales-report.buyers.pdf', $buyerRouteParams) }}">
                                        <i class="fas fa-users"></i>
                                        <span>Lista</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
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

        <div id="panelSalesReportBuyersModal" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-labelledby="panelSalesReportBuyersTitle">
            <div class="absolute inset-0 bg-slate-950/70" data-sales-report-buyers-close></div>
            <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-6">
                <div class="max-h-[88vh] w-full overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xl">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 px-5 py-4">
                        <h2 id="panelSalesReportBuyersTitle" class="text-lg font-black text-slate-900 dark:text-white">
                            <i class="fas fa-users mr-2 text-blue-600"></i>Compradores do item
                        </h2>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" data-sales-report-buyers-close aria-label="Fechar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="panelSalesReportBuyersBody" class="max-h-[62vh] overflow-auto px-5 py-4">
                        <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Carregando...</div>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-slate-100 dark:border-slate-800 px-5 py-4 sm:flex-row sm:justify-end">
                        <a href="#" target="_blank" rel="noopener" data-no-ajax="true" id="panelSalesReportBuyersPrint"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-black text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i class="fas fa-print"></i>
                            <span>Imprimir A4</span>
                        </a>
                        <a href="#" target="_blank" rel="noopener" data-no-ajax="true" id="panelSalesReportBuyersPdf"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white hover:bg-blue-700">
                            <i class="fas fa-file-pdf"></i>
                            <span>PDF</span>
                        </a>
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-black text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800" data-sales-report-buyers-close>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('panelSalesReportBuyersModal');
            const body = document.getElementById('panelSalesReportBuyersBody');
            const printLink = document.getElementById('panelSalesReportBuyersPrint');
            const pdfLink = document.getElementById('panelSalesReportBuyersPdf');

            if (!modal || !body || !printLink || !pdfLink) {
                return;
            }

            const openModal = function () {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeModal = function () {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            };

            modal.querySelectorAll('[data-sales-report-buyers-close]').forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            document.querySelectorAll('[data-sales-report-buyers]').forEach(function (button) {
                button.addEventListener('click', function () {
                    body.innerHTML = '<div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Carregando...</div>';
                    printLink.href = button.dataset.printUrl;
                    pdfLink.href = button.dataset.pdfUrl;
                    openModal();

                    fetch(button.dataset.url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function (response) {
                        if (!response.ok) {
                            throw new Error('Falha ao carregar');
                        }

                        return response.text();
                    }).then(function (html) {
                        body.innerHTML = html;
                    }).catch(function () {
                        body.innerHTML = '<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">Nao foi possivel carregar a lista de compradores.</div>';
                    });
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
@endpush
