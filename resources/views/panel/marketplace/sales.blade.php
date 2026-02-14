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

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Minhas vendas</h1>
                <p class="text-slate-600 mt-1">Pedidos do marketplace vinculados ao seu usuário.</p>
            </div>
            <a href="{{ route('panel.marketplace.index') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-sm font-bold text-slate-500">Vendas pagas</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $paidCount }}</div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-sm font-bold text-slate-500">Total líquido (pagos)</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-2">
                Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }} • Comissão: R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-0 mt-6 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-receipt text-slate-500"></i> Pedidos
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left font-bold px-6 py-4 w-28">Pedido</th>
                        <th class="text-left font-bold px-6 py-4">Comprador</th>
                        <th class="text-left font-bold px-6 py-4">Itens</th>
                        <th class="text-left font-bold px-6 py-4 w-40">Total</th>
                        <th class="text-left font-bold px-6 py-4 w-32">Status</th>
                        <th class="text-left font-bold px-6 py-4 w-44">Data</th>
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
                                'paid' => 'bg-emerald-500/10 text-emerald-700',
                                'pending' => 'bg-amber-500/10 text-amber-700',
                                'failed' => 'bg-rose-500/10 text-rose-700',
                                'refunded' => 'bg-slate-500/10 text-slate-700',
                                default => 'bg-slate-500/10 text-slate-700',
                            };
                        @endphp
                        <tr>
                            <td class="px-6 py-4 font-extrabold text-slate-900">#{{ $order->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $order->user->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $order->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $itemsLabel !== '' ? $itemsLabel : '—' }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">R$ {{ number_format((float) ($order->total_amount ?? 0), 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
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
            <div class="p-6 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection

