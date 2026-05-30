@extends('panel.layouts.app')

@section('title', 'Contabilidade - UNN')

@section('panel_content')
    @php
        $summary = $summary ?? [];
        $period = $period ?? ['period' => 'monthly', 'label' => 'Mensal'];

        $money = fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
        $statusBadge = function ($status) {
            return match ((string) $status) {
                'paid' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
                'refunded' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400',
                default => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
            };
        };
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">Contabilidade</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Resumo automatico das suas vendas e compras feitas na plataforma.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.marketplace.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
                <a href="{{ route('panel.marketplace.accounting.export', request()->query()) }}"
                    class="inline-flex items-center justify-center rounded-full border border-blue-600 dark:border-blue-500 px-5 py-2.5 text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-600/10 transition-all">
                    <i class="fas fa-file-csv mr-2"></i> Exportar CSV
                </a>
                <a href="{{ route('panel.marketplace.accounting.print', request()->query()) }}" target="_blank"
                    class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white px-5 py-2.5 text-sm font-bold hover:brightness-110 transition-all shadow-lg shadow-blue-500/20">
                    <i class="fas fa-print mr-2"></i> Imprimir
                </a>
            </div>
        </div>
    </div>

    <form method="GET"
        class="mt-6 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5">
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.24em] text-slate-500 mb-2">Periodo</label>
                <select name="period"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900">
                    @foreach(['monthly' => 'Mensal', 'quarterly' => 'Trimestral', 'semiannual' => 'Semestral', 'annual' => 'Anual', 'custom' => 'Personalizado'] as $value => $label)
                        <option value="{{ $value }}" {{ ($period['period'] ?? 'monthly') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.24em] text-slate-500 mb-2">De</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.24em] text-slate-500 mb-2">Ate</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Escopo atual: {{ $period['label'] ?? 'Mensal' }}.</p>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mt-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
            <div class="text-sm font-bold text-slate-500">Receita bruta de vendas</div>
            <div class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ $money($summary['sales_gross'] ?? 0) }}</div>
            <div class="mt-2 text-xs text-slate-500">{{ (int) ($summary['sales_count'] ?? 0) }} pedido(s) vendidos</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
            <div class="text-sm font-bold text-slate-500">Estornos + taxas</div>
            <div class="mt-2 text-3xl font-extrabold text-rose-600">{{ $money(($summary['sales_refunds'] ?? 0) + ($summary['sales_fees'] ?? 0)) }}</div>
            <div class="mt-2 text-xs text-slate-500">Estornos: {{ $money($summary['sales_refunds'] ?? 0) }} | Taxas: {{ $money($summary['sales_fees'] ?? 0) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
            <div class="text-sm font-bold text-slate-500">Resultado liquido de vendas</div>
            <div class="mt-2 text-3xl font-extrabold text-emerald-600">{{ $money($summary['sales_net'] ?? 0) }}</div>
            <div class="mt-2 text-xs text-slate-500">Apos comissoes e estornos</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
            <div class="text-sm font-bold text-slate-500">Despesas em compras</div>
            <div class="mt-2 text-3xl font-extrabold text-amber-600">{{ $money($summary['purchase_net'] ?? 0) }}</div>
            <div class="mt-2 text-xs text-slate-500">{{ (int) ($summary['purchase_count'] ?? 0) }} pedido(s) comprados</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 md:col-span-2">
            <div class="text-sm font-bold text-slate-500">Resultado geral do periodo</div>
            <div class="mt-2 text-3xl font-extrabold {{ ($summary['overall_net'] ?? 0) >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                {{ $money($summary['overall_net'] ?? 0) }}
            </div>
            <div class="mt-2 text-xs text-slate-500">Vendas liquidas menos despesas de compras na plataforma</div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 grid-cols-1">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Vendas detalhadas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-bold">
                        <tr>
                            <th class="text-left px-6 py-4">Pedido</th>
                            <th class="text-left px-6 py-4">Comprador</th>
                            <th class="text-left px-6 py-4">Liquido</th>
                            <th class="text-left px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($sales as $order)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-extrabold text-slate-900 dark:text-white">#{{ $order->id }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($order->paid_at ?: $order->created_at)->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $order->items->pluck('title')->filter()->take(2)->join(', ') ?: 'Sem itens' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $order->user->name ?? 'Usuario removido' }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->user->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $money($order->charged_amount - $order->refunded_amount - ((float) $order->platform_fee_amount + (float) $order->fee_amount)) }}</div>
                                    <div class="text-xs text-slate-500">Bruto {{ $money($order->charged_amount) }} | Taxas {{ $money((float) $order->platform_fee_amount + (float) $order->fee_amount) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusBadge($order->status) }}">
                                        {{ $order->status === 'refunded' ? 'Reembolsado' : 'Pago' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">Nenhuma venda no periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($sales, 'links'))
                <div class="px-6 py-5 border-t border-slate-100 dark:border-slate-800">{{ $sales->links() }}</div>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Compras detalhadas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-bold">
                        <tr>
                            <th class="text-left px-6 py-4">Pedido</th>
                            <th class="text-left px-6 py-4">Vendedor</th>
                            <th class="text-left px-6 py-4">Despesa</th>
                            <th class="text-left px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($purchases as $order)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-extrabold text-slate-900 dark:text-white">#{{ $order->id }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($order->paid_at ?: $order->created_at)->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $order->items->pluck('title')->filter()->take(2)->join(', ') ?: 'Sem itens' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $order->seller->name ?? 'Plataforma' }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->seller->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $money($order->charged_amount - $order->refunded_amount) }}</div>
                                    <div class="text-xs text-slate-500">Cobrado {{ $money($order->charged_amount) }} | Estorno {{ $money($order->refunded_amount) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusBadge($order->status) }}">
                                        {{ $order->status === 'refunded' ? 'Reembolsado' : 'Pago' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">Nenhuma compra no periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($purchases, 'links'))
                <div class="px-6 py-5 border-t border-slate-100 dark:border-slate-800">{{ $purchases->links() }}</div>
            @endif
        </div>
    </div>
@endsection
