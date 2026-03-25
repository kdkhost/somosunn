@extends('panel.layouts.app')

@section('title', 'Minhas compras - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Compras</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Historico de pedidos</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Acompanhe pedidos do marketplace e baixe produtos digitais quando o pagamento estiver aprovado.</p>
        </div>

        <div class="space-y-4">
            @forelse($orders as $order)
                <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 dark:text-white">Pedido #{{ $order->id }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Vendedor: {{ $order->seller->name ?? 'Plataforma' }} - Status: {{ strtoupper($order->status) }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</p>
                        </div>
                        @if($order->shipment)
                            <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-black text-slate-700 dark:text-slate-200">{{ $order->shipment->service_name }} - {{ strtoupper($order->shipment->status) }}</span>
                        @endif
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex flex-col gap-3 rounded-2xl bg-slate-50 dark:bg-slate-950 px-4 py-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $item->title }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ strtoupper($item->item_type) }} - Qtde {{ $item->quantity }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</div>
                                    @if($order->status === 'paid' && $item->item_type === 'seller_product' && filled(data_get($item->data, 'digital_delivery.type')))
                                        <a href="{{ route('panel.purchases.download', [$order, $item]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-xs font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                                            <i class="fas fa-download"></i> Baixar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                    Nenhuma compra registrada ainda.
                </div>
            @endforelse
        </div>

        <div>{{ $orders->links() }}</div>
    </div>
@endsection
