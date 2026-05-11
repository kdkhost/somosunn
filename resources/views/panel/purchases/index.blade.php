@extends('panel.layouts.app')

@section('title', 'Minhas compras - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Compras</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Histórico de pedidos</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Acompanhe pedidos do marketplace, retome pagamentos pendentes e baixe produtos digitais quando aprovado.
            </p>
        </div>

        <div class="space-y-4">
            @forelse($orders as $order)
                @php
                    $statusBadgeColors = [
                        'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                        'paid'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'failed'    => 'bg-red-50 text-red-700 border-red-200',
                        'refunded'  => 'bg-slate-50 text-slate-700 border-slate-200',
                        'cancelled' => 'bg-slate-50 text-slate-500 border-slate-200',
                    ];
                    $statusLabels = [
                        'pending'   => 'Aguardando pagamento',
                        'paid'      => 'Pago',
                        'failed'    => 'Falhou',
                        'refunded'  => 'Reembolsado',
                        'cancelled' => 'Cancelado',
                    ];
                    $badgeClass = $statusBadgeColors[$order->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                    $statusLabel = $statusLabels[$order->status] ?? strtoupper($order->status);
                    $gatewayLabel = match((string) $order->gateway) {
                        'mercadopago' => 'MercadoPago',
                        'sumup'       => 'SumUp',
                        'free'        => 'Gratuito',
                        default       => ucfirst((string) $order->gateway ?: 'Não definido'),
                    };
                @endphp

                <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h2 class="text-xl font-black text-slate-900 dark:text-white">
                                    Pedido #{{ $order->id }}
                                </h2>
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-black {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $statusLabel }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                    <i class="fas fa-credit-card text-[10px]"></i> {{ $gatewayLabel }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                <i class="fas fa-store mr-1"></i> Vendedor: <strong>{{ $order->seller->name ?? 'Plataforma' }}</strong>
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar mr-1"></i> {{ optional($order->created_at)->format('d/m/Y H:i') }}
                            </p>

                            <p class="mt-1 text-lg font-black text-slate-900 dark:text-white">
                                Total: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                            </p>

                            {{-- Countdown para pagamento pendente --}}
                            @if($order->status === 'pending' && !empty($order->payment_deadline))
                                <div class="mt-3 rounded-2xl {{ $order->is_expired ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }} px-4 py-3">
                                    @if($order->is_expired)
                                        <p class="text-sm font-bold text-red-700 flex items-center gap-2">
                                            <i class="fas fa-times-circle"></i> Prazo de pagamento expirado
                                        </p>
                                        <p class="text-xs text-red-600 mt-1">Este pedido será cancelado automaticamente.</p>
                                    @else
                                        <p class="text-sm font-bold text-amber-800 flex items-center gap-2">
                                            <i class="fas fa-hourglass-half"></i>
                                            Pagamento pendente até <strong>{{ $order->payment_deadline->format('d/m/Y H:i') }}</strong>
                                        </p>
                                        <p class="text-xs text-amber-700 mt-1">
                                            Você tem <span class="font-mono font-black" data-deadline="{{ $order->payment_deadline->timestamp * 1000 }}">calculando...</span> para concluir o pagamento.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($order->shipment)
                            <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-black text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                <i class="fas fa-truck mr-1"></i> {{ $order->shipment->service_name }} - {{ strtoupper($order->shipment->status) }}
                            </span>
                        @endif
                    </div>

                    {{-- Items do pedido --}}
                    <div class="mt-5 space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex flex-col gap-3 rounded-2xl bg-slate-50 dark:bg-slate-950 px-4 py-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $item->title }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ strtoupper($item->item_type) }} • Qtde {{ $item->quantity }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-black text-slate-900 dark:text-white">
                                        R$ {{ number_format((float) $item->price, 2, ',', '.') }}
                                    </div>
                                    @if($order->status === 'paid' && $item->item_type === 'seller_product' && filled(data_get($item->data, 'digital_delivery.type')))
                                        <a href="{{ route('panel.purchases.download', [$order, $item]) }}"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-2 text-xs font-black text-white shadow-lg shadow-blue-500/20 transition">
                                            <i class="fas fa-download"></i> Baixar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Ações do pedido --}}
                    @if($order->status === 'pending')
                        <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-3">
                            @if($order->can_retry)
                                <form action="{{ route('panel.purchases.retry', $order) }}" method="POST" data-no-ajax="true">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition">
                                        <i class="fas fa-redo-alt"></i> Retomar pagamento
                                    </button>
                                </form>
                            @elseif($order->is_expired)
                                <span class="inline-flex items-center gap-2 rounded-2xl bg-slate-200 dark:bg-slate-700 px-5 py-3 text-sm font-black text-slate-500 cursor-not-allowed">
                                    <i class="fas fa-ban"></i> Prazo expirado
                                </span>
                            @endif

                            {{-- Botao cancelar (cliente ou admin) --}}
                            <form action="{{ route('panel.purchases.cancel', $order) }}" method="POST" data-no-ajax="true"
                                onsubmit="return confirm('Tem certeza que deseja cancelar este pedido? Esta acao nao pode ser desfeita.')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-red-50 hover:bg-red-100 border border-red-200 px-5 py-3 text-sm font-bold text-red-700 transition">
                                    <i class="fas fa-times-circle"></i> Cancelar pedido
                                </button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center">
                    <i class="fas fa-shopping-bag text-4xl text-slate-300 mb-3"></i>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Nenhuma compra registrada</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Quando você comprar algo no marketplace, aparecerá aqui.</p>
                    <a href="{{ route('marketplace.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-5 py-3 text-sm font-black text-white transition">
                        <i class="fas fa-store"></i> Explorar marketplace
                    </a>
                </div>
            @endforelse
        </div>

        <div>{{ $orders->links() }}</div>
    </div>

    @push('scripts')
    <script>
    // Countdown para pedidos pendentes
    document.addEventListener('DOMContentLoaded', function() {
        const countdowns = document.querySelectorAll('[data-deadline]');

        function updateCountdown(el) {
            const deadline = parseInt(el.dataset.deadline, 10);
            const now = Date.now();
            let diff = deadline - now;

            if (diff <= 0) {
                el.textContent = 'EXPIRADO';
                el.closest('.rounded-2xl').classList.remove('bg-amber-50', 'border-amber-200');
                el.closest('.rounded-2xl').classList.add('bg-red-50', 'border-red-200');
                return false;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            diff -= days * 1000 * 60 * 60 * 24;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            diff -= hours * 1000 * 60 * 60;
            const minutes = Math.floor(diff / (1000 * 60));
            diff -= minutes * 1000 * 60;
            const seconds = Math.floor(diff / 1000);

            let txt = '';
            if (days > 0) txt += days + 'd ';
            if (hours > 0 || days > 0) txt += String(hours).padStart(2, '0') + 'h ';
            txt += String(minutes).padStart(2, '0') + 'm ' + String(seconds).padStart(2, '0') + 's';

            el.textContent = txt;
            return true;
        }

        countdowns.forEach(el => updateCountdown(el));
        setInterval(() => countdowns.forEach(el => updateCountdown(el)), 1000);
    });
    </script>
    @endpush
@endsection
