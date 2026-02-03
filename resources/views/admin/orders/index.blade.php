@extends('admin.layouts.app')
@section('title', 'Vendas')
@section('page_title', 'Gerenciamento de Vendas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Vendas</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listagem de Pedidos</h3>
                <div class="card-tools">
                    <form action="{{ route('admin.orders.index') }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control float-right" placeholder="Buscar por cliente/ID" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Valor Total</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $order->user->profile_photo_url ?? asset('images/default-avatar.png') }}" class="img-circle elevation-1 mr-2" style="width:30px;height:30px;object-fit:cover">
                                    {{ $order->user->name ?? 'Usuário Removido' }}
                                </div>
                            </td>
                            <td class="font-weight-bold">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                            <td>
                                @if($order->gateway == 'mercadopago') <span class="badge badge-info mb-0">MercadoPago</span>
                                @elseif($order->gateway == 'pagseguro') <span class="badge badge-success mb-0">PagSeguro</span>
                                @else <span class="badge badge-secondary mb-0">{{ $order->gateway }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status == 'paid') <span class="badge badge-pill badge-success">Pago</span>
                                @elseif($order->status == 'pending') <span class="badge badge-pill badge-warning">Pendente</span>
                                @elseif($order->status == 'refunded') <span class="badge badge-pill badge-danger">Reembolsado</span>
                                @else <span class="badge badge-pill badge-secondary">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhuma venda encontrada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
