@extends('admin.layouts.app')

@section('title', 'Pedidos da loja - Marketplace')
@section('page_title', 'Pedidos da loja')

@section('content')
    @php
        $totalOrders = method_exists($orders, 'total') ? $orders->total() : count($orders);
        $paidCount = 0;
        $pendingCount = 0;
        $shippedCount = 0;
        foreach ($orders as $o) {
            if ($o->status === 'paid') $paidCount++;
            elseif ($o->status === 'pending') $pendingCount++;
            if ($o->shipment && in_array($o->shipment->status, ['shipped', 'delivered'])) $shippedCount++;
        }
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-shopping-bag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total</span>
                    <span class="info-box-number">{{ $totalOrders }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Pagos</span>
                    <span class="info-box-number">{{ $paidCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Pendentes</span>
                    <span class="info-box-number">{{ $pendingCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Enviados</span>
                    <span class="info-box-number">{{ $shippedCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-truck mr-2 text-primary"></i>Pedidos da Loja
            </h3>
            <div class="card-tools d-flex flex-wrap" style="gap:6px;">
                <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-box-open mr-1"></i> Produtos
                </a>
                <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-store mr-1"></i> Loja
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if(count($orders) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="border-0 pl-3">Pedido</th>
                                <th class="border-0">Cliente</th>
                                <th class="border-0">Itens</th>
                                <th class="border-0 text-right">Total</th>
                                <th class="border-0 text-center">Pagamento</th>
                                <th class="border-0 text-center">Envio</th>
                                <th class="border-0 text-center" style="width:200px;">Rastreio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $statusBadge = match($order->status) {
                                        'paid' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'refunded' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                    $statusLabel = match($order->status) {
                                        'paid' => 'Pago',
                                        'pending' => 'Pendente',
                                        'refunded' => 'Reembolsado',
                                        default => ucfirst($order->status),
                                    };
                                    $shipStatus = $order->shipment?->status ?? null;
                                    $shipBadge = match($shipStatus) {
                                        'shipped' => 'badge-info',
                                        'delivered' => 'badge-success',
                                        'processing' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-light border',
                                    };
                                    $shipLabel = match($shipStatus) {
                                        'shipped' => 'Enviado',
                                        'delivered' => 'Entregue',
                                        'processing' => 'Preparando',
                                        'cancelled' => 'Cancelado',
                                        'pending' => 'Pendente',
                                        default => 'Digital',
                                    };
                                    $itemsList = $order->items->pluck('title')->take(2)->join(', ');
                                    if ($order->items->count() > 2) $itemsList .= '…';
                                @endphp
                                <tr>
                                    <td class="pl-3">
                                        <span class="font-weight-bold text-primary">#{{ $order->id }}</span>
                                        <div class="text-muted" style="font-size:10px;">{{ $order->created_at?->format('d/m/y H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-sm">{{ $order->user->name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:10px;">{{ $order->user->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ $itemsList ?: '—' }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $statusBadge }}" style="font-size:10px;">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $shipBadge }}" style="font-size:10px;">{{ $shipLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($order->shipment)
                                            <form action="{{ route('admin.marketplace.orders.shipment.update', $order) }}" method="POST"
                                                class="d-flex align-items-center" style="gap:4px;">
                                                @csrf
                                                <select name="status" class="form-control form-control-sm" style="font-size:11px; width:90px;">
                                                    @foreach(['pending' => 'Pend.', 'processing' => 'Prep.', 'shipped' => 'Env.', 'delivered' => 'Entreg.'] as $v => $l)
                                                        <option value="{{ $v }}" {{ $order->shipment->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="tracking_code" value="{{ $order->shipment->tracking_code }}"
                                                    class="form-control form-control-sm" style="font-size:11px; width:90px;" placeholder="Rastreio">
                                                <button type="submit" class="btn btn-xs btn-primary rounded-pill" title="Salvar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted" style="font-size:10px;"><i class="fas fa-cloud mr-1"></i>Digital</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-muted">Nenhum pedido ainda</h5>
                    <p class="text-muted">Quando seus produtos forem vendidos, os pedidos aparecerão aqui.</p>
                </div>
            @endif
        </div>

        @if(method_exists($orders, 'hasPages') && $orders->hasPages())
            <div class="card-footer d-flex justify-content-center border-top">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .align-middle td { vertical-align: middle !important; }
</style>
@endpush
