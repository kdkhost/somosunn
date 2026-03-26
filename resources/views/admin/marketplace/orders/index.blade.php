@extends('admin.layouts.app')

@section('title', 'Pedidos da loja - Marketplace')
@section('page_title', 'Pedidos da loja')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-truck mr-2"></i>Pedidos dos produtos proprios</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Acompanhe as vendas da sua loja e atualize rastreio e status de envio quando houver entrega fisica.</p>
                </div>
            </div>
        </div>
    </div>

    @forelse($orders as $order)
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="card-title font-weight-bold">Pedido #{{ $order->id }}</h3>
                    <div class="text-muted small mt-1">Comprador: {{ $order->user->name ?? 'Cliente' }} - {{ $order->user->email ?? '-' }}</div>
                    <div class="text-muted small">Total: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }} - Status do pagamento: {{ strtoupper((string) $order->status) }}</div>
                </div>
                @if($order->shipment)
                    <span class="badge {{ in_array($order->shipment->status, ['shipped', 'delivered']) ? 'badge-success' : 'badge-secondary' }} px-3 py-2">Envio: {{ strtoupper($order->shipment->status) }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h6 class="text-uppercase text-muted font-weight-bold">Itens</h6>
                        <div class="list-group mt-3">
                            @foreach($order->items as $item)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="font-weight-bold">{{ $item->title }}</div>
                                        <div class="text-muted small">{{ strtoupper($item->item_type) }} - Qtde {{ $item->quantity }}</div>
                                    </div>
                                    <div class="font-weight-bold">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h6 class="text-uppercase text-muted font-weight-bold">Fulfillment</h6>
                        @if($order->shipment)
                            <p class="mt-3 mb-1">Servico: <strong>{{ $order->shipment->service_name }}</strong></p>
                            <p class="mb-1">Frete: <strong>R$ {{ number_format((float) $order->shipment->shipping_amount, 2, ',', '.') }}</strong></p>
                            <p class="text-muted">Destino: {{ $order->shipment->postal_code }} - {{ $order->shipment->city }}/{{ $order->shipment->state }}</p>

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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-truck mr-1"></i> Atualizar envio
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success mt-3 mb-0">Este pedido nao possui entrega fisica. Se houver item digital, o acesso do comprador sera liberado apos o pagamento.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-outline card-secondary">
            <div class="card-body text-center text-muted py-5">
                Nenhum pedido da loja ainda.
            </div>
        </div>
    @endforelse

    @if(method_exists($orders, 'links'))
        <div>{{ $orders->links() }}</div>
    @endif
@endsection
