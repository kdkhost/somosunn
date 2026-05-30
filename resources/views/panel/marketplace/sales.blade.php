@extends('panel.layouts.app')

@section('title', 'Minhas Vendas - UNN')

@section('panel_content')
    @php
        $orders = $orders ?? null;
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
    @endphp

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Minhas
                    vendas</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Pedidos do marketplace vinculados ao
                    seu usuário.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.marketplace.accounting') }}"
                    class="inline-flex items-center justify-center rounded-full border border-blue-600 dark:border-blue-500 px-5 py-2.5 text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-600/10 transition-all">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Contabilidade
                </a>
                <a href="{{ route('panel.marketplace.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 mt-6">
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Vendas pagas</div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">{{ $paidCount }}
            </div>
        </div>
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Total líquido (pagos)</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">R$
                {{ number_format($netTotal, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }} • Comissão: R$
                {{ number_format($platformFeeTotal, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-0 mt-6 overflow-hidden transition-colors duration-300">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                <i class="fas fa-receipt text-slate-500 dark:text-slate-500"></i> Pedidos
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-bold transition-colors">
                    <tr>
                        <th class="text-left px-6 py-4 w-28">Pedido</th>
                        <th class="text-left px-6 py-4">Comprador</th>
                        <th class="text-left px-6 py-4">Itens</th>
                        <th class="text-left px-6 py-4 w-40">Total</th>
                        <th class="text-left px-6 py-4 w-32">Status</th>
                        <th class="text-left px-6 py-4 w-44">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse(($orders ?? []) as $order)
                        @php
                            $items = $order->items ?? collect();
                            $itemsLabel = $items->pluck('title')->filter()->take(3)->join(', ');
                            $itemsCount = $items->count();
                            if ($itemsCount > 3) {
                                $itemsLabel .= '…';
                            }

                            $status = (string) ($order->status ?? '');
                            $statusLabel = match ($status) {
                                'paid' => 'Pago',
                                'pending' => 'Pendente',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                                default => $status ?: '—',
                            };
                            $statusClass = match ($status) {
                                'paid' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
                                'pending' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
                                'failed' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400',
                                'refunded' => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
                                default => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
                            };
                        @endphp
                        <tr class="divide-slate-100 dark:divide-slate-800 transition-colors">
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">#{{ $order->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $order->user->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-400">
                                {{ $itemsLabel !== '' ? $itemsLabel : '—' }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">R$
                                {{ number_format((float) ($order->total_amount ?? 0), 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                {{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">Nenhuma venda encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($orders, 'links'))
            <div class="p-6 border-t border-slate-100 dark:border-slate-800 transition-colors">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
