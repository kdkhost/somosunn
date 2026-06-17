@extends('panel.layouts.app')

@section('title', 'Pedidos da loja - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Pedidos e logistica</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Pedidos dos produtos proprios</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Acompanhe as vendas da sua loja e atualize rastreio e status de envio quando houver entrega fisica.</p>
        </div>

        <div class="space-y-4">
            @forelse($orders as $order)
                @php
                    $grossAmount = (float) $order->gross_amount;
                    $discountAmount = (float) $order->financial_discount_amount;
                    $couponCode = $order->coupon_code;
                @endphp
                <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 dark:text-white">Pedido #{{ $order->id }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Comprador: {{ $order->user->name ?? 'Cliente' }} ? {{ $order->user->email ?? '-' }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Bruto: R$ {{ number_format($grossAmount, 2, ',', '.') }} ? Status do pagamento: {{ strtoupper($order->status) }}</p>
                            @if($discountAmount > 0)
                                <p class="mt-1 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    Cupom {{ $couponCode ?: '-' }}: - R$ {{ number_format($discountAmount, 2, ',', '.') }} ? Liquido: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                                </p>
                            @endif
                        </div>
                        @if($order->shipment)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black {{ in_array($order->shipment->status, ['shipped', 'delivered']) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                Envio: {{ strtoupper($order->shipment->status) }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-5 grid gap-6 lg:grid-cols-[1fr,0.9fr]">
                        <div class="rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
                            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Itens</h3>
                            <div class="mt-4 space-y-3">
                                @foreach($order->items as $item)
                                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 dark:bg-slate-950 px-4 py-3">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white">{{ $item->title }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ strtoupper($item->item_type) }} ? Qtde {{ $item->quantity }}</p>
                                        </div>
                                        <div class="text-sm font-black text-slate-900 dark:text-white">
                                            R$ {{ number_format((float) $item->gross_unit_price, 2, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-100 dark:border-slate-800 p-5">
                            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Fulfillment</h3>
                            @if($order->shipment)
                                <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">Servico: <strong>{{ $order->shipment->service_name }}</strong> ? Frete R$ {{ number_format((float) $order->shipment->shipping_amount, 2, ',', '.') }}</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Destino: {{ $order->shipment->postal_code }} ? {{ $order->shipment->city }}/{{ $order->shipment->state }}</p>

                                <form action="{{ route('panel.marketplace.orders.shipment.update', $order) }}" method="POST" class="mt-4 space-y-4">
                                    @csrf
                                    <select name="status" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                        @foreach(['pending' => 'Pendente', 'processing' => 'Preparando', 'shipped' => 'Enviado', 'delivered' => 'Entregue', 'cancelled' => 'Cancelado'] as $value => $label)
                                            <option value="{{ $value }}" {{ $order->shipment->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="tracking_code" value="{{ $order->shipment->tracking_code }}" placeholder="Codigo de rastreio" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                                        <i class="fas fa-truck"></i> Atualizar envio
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 p-4 text-sm text-emerald-700 dark:text-emerald-300">
                                    Este pedido nao possui entrega fisica. Se houver item digital, o acesso do comprador sera liberado apos o pagamento.
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                    Nenhum pedido da loja ainda.
                </div>
            @endforelse
        </div>

        <div>{{ $orders->links() }}</div>
    </div>
@endsection
