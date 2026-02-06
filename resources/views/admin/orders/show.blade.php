@extends('admin.layouts.app')
@section('title', 'Detalhes do Pedido')
@section('page_title', 'Detalhes do Pedido #' . $order->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">Pedido #{{ $order->id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="{{ $order->user->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{ $order->user->name }}</h3>
                <p class="text-muted text-center">{{ $order->user->email }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right">
                            @if($order->status == 'paid') <span class="badge badge-success">Pago</span>
                            @elseif($order->status == 'pending') <span class="badge badge-warning">Pendente</span>
                            @elseif($order->status == 'refunded') <span class="badge badge-danger">Reembolsado</span>
                            @else <span class="badge badge-secondary">{{ $order->status }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Data</b> <a class="float-right">{{ $order->created_at->format('d/m/Y H:i') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total</b> <a class="float-right">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Gateway</b> <a class="float-right">{{ ucfirst($order->gateway) }}</a>
                    </li>
                    @if($order->transaction_id)
                    <li class="list-group-item">
                        <b>Transação ID</b> <a class="float-right text-monospace text-xs">{{ Str::limit($order->transaction_id, 20) }}</a>
                    </li>
                    @endif
                </ul>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Fatura</strong>
                        @if($order->invoice)
                            <span class="badge badge-info">{{ $order->invoice->number ?: ('#'.$order->invoice->id) }}</span>
                        @else
                            <span class="badge badge-secondary">Não emitida</span>
                        @endif
                    </div>

                    @if($order->invoice)
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-sm btn-secondary" data-pjax>
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                            <a href="{{ route('admin.invoices.pdf', $order->invoice) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <form method="POST" action="{{ route('admin.invoices.send', $order->invoice) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="force" value="1">
                                <button class="btn btn-sm btn-outline-success" type="submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Enviar e-mail
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.orders.invoice', $order) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-invoice mr-1"></i> Emitir e enviar fatura
                            </button>
                        </form>
                    @endif
                </div>

                @if($order->status === 'paid')
                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <button type="button" class="btn btn-danger btn-block btn-delete" data-confirm-delete="true">
                        <i class="fas fa-undo mr-1"></i> Reembolsar Pedido
                    </button>
                    <small class="text-muted text-center mt-2 d-block">Esta ação estornará o pagamento no gateway.</small>
                </form>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Itens do Pedido</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->item_type }}</td>
                            <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($order->refunded_at)
        <div class="alert alert-danger mt-3">
            <h5><i class="icon fas fa-ban"></i> Pedido Reembolsado!</h5>
            Este pedido foi reembolsado em {{ \Carbon\Carbon::parse($order->refunded_at)->format('d/m/Y H:i') }}.
        </div>
        @endif
    </div>
</div>
@endsection
