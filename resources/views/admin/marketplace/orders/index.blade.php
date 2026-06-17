@extends('admin.layouts.app')

@section('title', 'Pedidos da Loja')
@section('page_title', 'Pedidos da Loja')

@section('content')
    @php
        $totalOrders = method_exists($orders, 'total') ? $orders->total() : count($orders);
        $paidCount = 0;
        $pendingCount = 0;
        $shippedCount = 0;
        $revenueTotal = 0;
        foreach ($orders as $o) {
            if ($o->status === 'paid') { $paidCount++; $revenueTotal += (float) $o->gross_amount; }
            elseif ($o->status === 'pending') $pendingCount++;
            if ($o->shipment && in_array($o->shipment->status, ['shipped', 'delivered'])) $shippedCount++;
        }
    @endphp

    {{-- KPI --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary shadow-sm">
                <div class="inner">
                    <h3>{{ $totalOrders }}</h3>
                    <p>Total de Pedidos</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3>R$ {{ number_format($revenueTotal, 0, ',', '.') }}</h3>
                    <p>Receita (pagos)</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $pendingCount }}</h3>
                    <p>Aguardando Pagamento</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <h3>{{ $shippedCount }}</h3>
                    <p>Enviados / Entregues</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
    </div>

    {{-- Ações --}}
    <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center">
        <h5 class="font-weight-bold mb-0"><i class="fas fa-receipt mr-2 text-primary"></i>Meus Pedidos</h5>
        <div class="d-flex" style="gap:8px;">
            <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 elevation-1">
                <i class="fas fa-box-open mr-1"></i> Produtos
            </a>
            <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 elevation-1">
                <i class="fas fa-store mr-1"></i> Minha Loja
            </a>
        </div>
    </div>

    {{-- Lista de pedidos --}}
    @forelse($orders as $order)
        @php
            $statusColor = match($order->status) {
                'paid' => 'success',
                'pending' => 'warning',
                'refunded' => 'danger',
                'cancelled' => 'secondary',
                default => 'secondary',
            };
            $statusLabel = match($order->status) {
                'paid' => 'Pago',
                'pending' => 'Pendente',
                'refunded' => 'Reembolsado',
                'cancelled' => 'Cancelado',
                default => ucfirst($order->status),
            };
            $hasShipment = (bool) $order->shipment;
            $shipStatus = $order->shipment?->status;
            $grossAmount = (float) $order->gross_amount;
            $discountAmount = (float) $order->financial_discount_amount;
            $couponCode = $order->coupon_code;
        @endphp

        <div class="card shadow-sm mb-3 border-left-{{ $statusColor }}" style="border-left-width:4px !important;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    {{-- Col 1: ID + Data --}}
                    <div class="col-md-1 col-3 text-center">
                        <div class="font-weight-black text-primary" style="font-size:18px;">#{{ $order->id }}</div>
                        <div class="text-muted" style="font-size:10px;">{{ $order->created_at?->format('d/m/y') }}</div>
                    </div>

                    {{-- Col 2: Cliente --}}
                    <div class="col-md-3 col-9">
                        <div class="font-weight-bold">{{ $order->user->name ?? 'Cliente removido' }}</div>
                        <div class="text-muted small">{{ $order->user->email ?? '' }}</div>
                        <div class="mt-1">
                            @foreach($order->items->take(3) as $item)
                                <span class="badge badge-light border mr-1 mb-1" style="font-size:10px;">{{ \Illuminate\Support\Str::limit($item->title, 20) }}</span>
                            @endforeach
                            @if($order->items->count() > 3)
                                <span class="badge badge-light border mb-1" style="font-size:10px;">+{{ $order->items->count() - 3 }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Col 3: Valor + Status --}}
                    <div class="col-md-2 col-6 mt-2 mt-md-0">
                        <div class="font-weight-bold" style="font-size:16px;">R$ {{ number_format($grossAmount, 2, ',', '.') }}</div>
                        @if($discountAmount > 0)
                            <div class="text-success font-weight-bold" style="font-size:10px;">
                                Cupom {{ $couponCode ?: '-' }}: - R$ {{ number_format($discountAmount, 2, ',', '.') }}
                            </div>
                            <div class="text-muted" style="font-size:10px;">Liquido: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</div>
                        @endif
                        <span class="badge badge-{{ $statusColor }} mt-1">
                            <i class="fas fa-circle mr-1" style="font-size:6px;"></i>{{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Col 4: Envio --}}
                    <div class="col-md-6 col-12 mt-2 mt-md-0">
                        @if($hasShipment)
                            <form action="{{ route('admin.marketplace.orders.shipment.update', $order) }}" method="POST">
                                @csrf
                                <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                                    <div>
                                        <label class="mb-0 text-muted" style="font-size:10px; font-weight:700;">STATUS</label>
                                        <select name="status" class="form-control form-control-sm" style="width:120px;">
                                            @foreach(['pending' => 'Pendente', 'processing' => 'Preparando', 'shipped' => 'Enviado', 'delivered' => 'Entregue'] as $v => $l)
                                                <option value="{{ $v }}" {{ $shipStatus === $v ? 'selected' : '' }}>{{ $l }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="mb-0 text-muted" style="font-size:10px; font-weight:700;">RASTREIO</label>
                                        <input type="text" name="tracking_code" value="{{ $order->shipment->tracking_code }}"
                                            class="form-control form-control-sm" placeholder="Código de rastreio">
                                    </div>
                                    <div class="align-self-end">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 elevation-1">
                                            <i class="fas fa-save mr-1"></i> Salvar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="d-flex align-items-center text-muted">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light mr-2" style="width:32px;height:32px;">
                                    <i class="fas fa-cloud-download-alt text-info"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold text-sm">Entrega digital</div>
                                    <div style="font-size:10px;">Acesso liberado automaticamente após pagamento</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-shopping-bag fa-3x text-muted"></i>
                </div>
                <h5 class="font-weight-bold text-muted">Nenhum pedido recebido</h5>
                <p class="text-muted mb-3">Quando seus produtos forem vendidos, os pedidos aparecerão aqui com opções de rastreio.</p>
                <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-primary rounded-pill px-4 elevation-1">
                    <i class="fas fa-box-open mr-1"></i> Ver meus produtos
                </a>
            </div>
        </div>
    @endforelse

    @if(method_exists($orders, 'hasPages') && $orders->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $orders->links() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .border-left-success { border-left-color: #28a745 !important; }
    .border-left-warning { border-left-color: #ffc107 !important; }
    .border-left-danger { border-left-color: #dc3545 !important; }
    .border-left-secondary { border-left-color: #6c757d !important; }
    .border-left-primary { border-left-color: #007bff !important; }
    .font-weight-black { font-weight: 900; }
</style>
@endpush
