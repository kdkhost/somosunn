@extends('admin.layouts.app')

@section('title', 'Pedidos da loja - Marketplace')
@section('page_title', 'Pedidos da loja')

@section('content')
    @php
        $totalOrders = method_exists($orders, 'total') ? $orders->total() : count($orders);
        $shippedCount = 0;
        $pendingShipCount = 0;
        foreach ($orders as $o) {
            if ($o->shipment && in_array($o->shipment->status, ['shipped', 'delivered'])) {
                $shippedCount++;
            } elseif ($o->shipment) {
                $pendingShipCount++;
            }
        }
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-shopping-bag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de pedidos</span>
                    <span class="info-box-number">{{ $totalOrders }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Enviados / Entregues</span>
                    <span class="info-box-number">{{ $shippedCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Envio pendente</span>
                    <span class="info-box-number">{{ $pendingShipCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Header card --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold"><i class="fas fa-truck mr-2"></i>Pedidos dos produtos proprios</h3>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-sm btn-outline-primary rounded-pill elevation-1">
                    <i class="fas fa-box-open mr-1"></i> Produtos
                </a>
                <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-sm btn-outline-secondary rounded-pill elevation-1">
                    <i class="fas fa-store mr-1"></i> Minha loja
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">Acompanhe as vendas da sua loja e atualize rastreio e status de envio quando houver entrega fisica.</p>
        </div>
    </div>

    {{-- Orders list --}}
    @forelse($orders as $order)
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="card-title font-weight-bold">Pedido #{{ $order->id }}</h3>
                    <div class="text-muted small mt-1">
                        <i class="fas fa-user mr-1"></i> {{ $order->user->name ?? 'Cliente' }} - {{ $order->user->email ?? '-' }}
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-money-bill-wave mr-1"></i> Total: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }} -
                        <span class="badge {{ $order->status === 'paid' ? 'badge-success' : ($order->status === 'pending' ? 'badge-warning' : 'badge-secondary') }}">
                            <i class="fas {{ $order->status === 'paid' ? 'fa-check-circle' : ($order->status === 'pending' ? 'fa-hourglass-half' : 'fa-circle') }} mr-1"></i>{{ strtoupper((string) $order->status) }}
                        </span>
                    </div>
                </div>
                @if($order->shipment)
                    @php
                        $shipBadge = in_array($order->shipment->status, ['shipped', 'delivered']) ? 'badge-success' : 'badge-warning';
                        $shipIcon = match ($order->shipment->status) {
                            'shipped' => 'fa-shipping-fast',
                            'delivered' => 'fa-check-double',
                            default => 'fa-clock',
                        };
                    @endphp
                    <span class="badge {{ $shipBadge }} px-3 py-2"><i class="fas {{ $shipIcon }} mr-1"></i> Envio: {{ strtoupper($order->shipment->status) }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h6 class="text-uppercase text-muted font-weight-bold"><i class="fas fa-list mr-1"></i> Itens</h6>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Qtde</th>
                                        <th class="text-right">Preco</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="font-weight-bold">{{ $item->title }}</td>
                                            <td><span class="badge badge-light border">{{ strtoupper($item->item_type) }}</span></td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right font-weight-bold">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h6 class="text-uppercase text-muted font-weight-bold"><i class="fas fa-shipping-fast mr-1"></i> Fulfillment</h6>
                        @if($order->shipment)
                            <div class="mt-3 p-3 bg-light rounded">
                                <p class="mb-1">Servico: <strong>{{ $order->shipment->service_name }}</strong></p>
                                <p class="mb-1">Frete: <strong>R$ {{ number_format((float) $order->shipment->shipping_amount, 2, ',', '.') }}</strong></p>
                                <p class="text-muted mb-0">Destino: {{ $order->shipment->postal_code }} - {{ $order->shipment->city }}/{{ $order->shipment->state }}</p>
                            </div>

                            <form action="{{ route('admin.marketplace.orders.shipment.update', $order) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="shipment_status_{{ $order->id }}">Status do envio</label>
                                        <select name="status" id="shipment_status_{{ $order->id }}" class="form-control">
                                            @foreach(['pending' => 'Pendente', 'processing' => 'Preparando', 'shipped' => 'Enviado', 'delivered' => 'Entregue', 'cancelled' => 'Cancelado'] as $value => $label)
                                                <option value="{{ $value }}" {{ $order->shipment->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tracking_code_{{ $order->id }}">Codigo de rastreio</label>
                                        <input type="text" name="tracking_code" id="tracking_code_{{ $order->id }}" value="{{ $order->shipment->tracking_code }}" class="form-control" placeholder="Codigo de rastreio">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill elevation-1">
                                    <i class="fas fa-truck mr-1"></i> Atualizar envio
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-cloud-download-alt mr-2"></i> Este pedido nao possui entrega fisica. Se houver item digital, o acesso do comprador sera liberado apos o pagamento.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-truck text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="font-weight-bold text-muted">Nenhum pedido da loja ainda</h5>
                <p class="text-muted mb-0">Quando seus produtos forem vendidos, os pedidos aparecerao aqui.</p>
            </div>
        </div>
    @endforelse

    @if(method_exists($orders, 'links'))
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
@endsection
