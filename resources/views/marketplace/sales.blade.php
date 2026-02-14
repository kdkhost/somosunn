@extends('layouts.app')

@section('title', 'Minhas Vendas - Marketplace')

@section('content')
    @php
        $orders = $orders ?? null;
        $paidTotal = (float) ($paidTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
    @endphp

    <div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Minhas vendas</h1>
                    <p class="text-slate-600 mt-2">Histórico de pedidos onde você é o vendedor.</p>
                </div>
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-5 py-2.5 font-bold text-slate-700 hover:bg-slate-50 transition">
                    <i class="fas fa-store"></i> Voltar ao marketplace
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Vendas pagas</div>
                    <div class="mt-2 text-3xl font-black text-slate-900">{{ $paidCount }}</div>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:col-span-2">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total recebido (pagos)</div>
                    <div class="mt-2 text-3xl font-black text-slate-900">R$ {{ number_format($paidTotal, 2, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-slate-500">Valor bruto somado no sistema (sem descontar taxas do gateway).</div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-black text-slate-900"><i class="fas fa-receipt mr-2 text-slate-500"></i>Pedidos</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left font-bold px-6 py-4">Pedido</th>
                                <th class="text-left font-bold px-6 py-4">Comprador</th>
                                <th class="text-left font-bold px-6 py-4">Itens</th>
                                <th class="text-left font-bold px-6 py-4">Total</th>
                                <th class="text-left font-bold px-6 py-4">Status</th>
                                <th class="text-left font-bold px-6 py-4">Data</th>
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
                                        'paid' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'failed' => 'bg-red-100 text-red-700',
                                        'refunded' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 font-bold text-slate-900">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-slate-700">
                                        <div class="font-semibold">{{ $order->user->name ?? '—' }}</div>
                                        <div class="text-xs text-slate-500">{{ $order->user->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">
                                        {{ $itemsLabel !== '' ? $itemsLabel : '—' }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        R$ {{ number_format((float) ($order->total_amount ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ optional($order->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                        Nenhuma venda encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($orders, 'links'))
                    <div class="p-6">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
