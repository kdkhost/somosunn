@extends('admin.layouts.app')
@section('title', 'Detalhes do Pedido')
@section('page_title', 'Detalhes do Pedido #' . $order->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">Pedido #{{ $order->id }}</li>
@endsection

@section('content')
    @php
        $grossAmount = (float) $order->gross_amount;
        $discountAmount = (float) $order->financial_discount_amount;
        $couponCode = $order->coupon_code;
    @endphp
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @php
                            $photo = trim((string) ($order->user->photo ?? ''));
                            $avatarUrl = $photo !== ''
                                ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
                                : asset('img/default-user.svg');
                        @endphp
                        <img class="profile-user-img img-fluid img-circle" src="{{ $avatarUrl }}" alt="User profile picture"
                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                    </div>
                    <h3 class="profile-username text-center">{{ $order->user->name }}</h3>
                    <p class="text-muted text-center">{{ $order->user->email }}</p>
                    @if($order->user->phone)
                        <p class="text-muted text-center text-sm"><i class="fas fa-phone mr-1"></i>{{ $order->user->phone }}</p>
                    @endif
                    @if($order->buyerAddress())
                        <p class="text-muted text-center text-xs"><i class="fas fa-map-marker-alt mr-1"></i>{{ $order->buyerAddress() }}</p>
                    @endif

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Status</b>
                            <a class="float-right">
                                @if($order->is_partially_refunded)
                                    <span class="badge badge-warning">Parcialmente reembolsado</span>
                                @elseif($order->status === 'paid')
                                    <span class="badge badge-success">Pago</span>
                                @elseif($order->status === 'pending')
                                    <span class="badge badge-warning">Pendente</span>
                                @elseif($order->status === 'refunded')
                                    <span class="badge badge-danger">Reembolsado</span>
                                @else
                                    <span class="badge badge-secondary">{{ $order->status }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b>Data</b>
                            <a class="float-right">{{ $order->created_at->format('d/m/Y H:i') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Valor bruto</b>
                            <a class="float-right">R$ {{ number_format($grossAmount, 2, ',', '.') }}</a>
                        </li>
                        @if($discountAmount > 0)
                            <li class="list-group-item">
                                <b>Cupom {{ $couponCode ?: '-' }}</b>
                                <a class="float-right text-success font-weight-bold">- R$ {{ number_format($discountAmount, 2, ',', '.') }}</a>
                            </li>
                        @endif
                        <li class="list-group-item">
                            <b>Total liquido</b>
                            <a class="float-right">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</a>
                        </li>
                        @if($order->refunded_amount > 0)
                            <li class="list-group-item">
                                <b>Ja estornado</b>
                                <a class="float-right text-warning">R$
                                    {{ number_format($order->refunded_amount, 2, ',', '.') }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Saldo para estorno</b>
                                <a class="float-right">R$
                                    {{ number_format($order->remaining_refundable_amount, 2, ',', '.') }}</a>
                            </li>
                        @endif
                        <li class="list-group-item">
                            <b>Gateway</b>
                            <a class="float-right">{{ ucfirst($order->gateway) }}</a>
                        </li>
                        @if($order->is_manual_approval)
                            <li class="list-group-item">
                                <b>Origem</b>
                                <a class="float-right text-warning font-weight-bold">Aprovacao manual</a>
                            </li>
                            @if($order->manualApprover)
                                <li class="list-group-item">
                                    <b>Aprovado por</b>
                                    <a class="float-right">{{ $order->manualApprover->name }}</a>
                                </li>
                            @endif
                        @endif
                        @if($order->transaction_id)
                            <li class="list-group-item">
                                <b>Transacao ID</b>
                                <a class="float-right text-monospace text-xs">
                                    {{ \Illuminate\Support\Str::limit($order->transaction_id, 20) }}
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Fatura</strong>
                            @if($order->invoice)
                                <span class="badge badge-info">
                                    {{ $order->invoice->number ?: ('#' . $order->invoice->id) }}
                                </span>
                            @else
                                <span class="badge badge-secondary">Nao emitida</span>
                            @endif
                        </div>

                        @if($order->invoice)
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-sm btn-secondary" data-pjax>
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                                <a href="{{ route('admin.invoices.pdf', $order->invoice) }}"
                                    class="btn btn-sm btn-outline-primary" target="_blank">
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

                    @if($order->status === 'paid' && $order->remaining_refundable_amount > 0)
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST"
                                onsubmit="return confirmAction(event, 'Estorno total?', 'Esta acao vai devolver todo o valor restante no gateway. Deseja continuar?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block btn-delete">
                                    <i class="fas fa-undo mr-1"></i> Estorno Total
                                </button>
                            </form>

                            @if($order->supportsPartialRefund())
                                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" class="mt-2"
                                    onsubmit="return confirmAction(event, 'Estorno parcial?', 'O valor informado sera devolvido ao cliente e o pedido continuara com saldo restante.');">
                                    @csrf
                                    <div class="input-group">
                                        <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
                                            max="{{ number_format($order->remaining_refundable_amount, 2, '.', '') }}"
                                            placeholder="Valor parcial (max. {{ number_format($order->remaining_refundable_amount, 2, ',', '.') }})">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-warning font-weight-bold">
                                                <i class="fas fa-coins mr-1"></i> Estorno Parcial
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted text-center mt-2 d-block">
                                        Disponivel apenas para pedidos Mercado Pago.
                                    </small>
                                </form>
                            @endif
                        </div>
                    @elseif($order->status === 'paid')
                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="mt-2"
                            id="form-cancel-{{ $order->id }}"
                            onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido pago?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar Pedido
                            </button>
                        </form>
                    @elseif($order->status === 'pending')
                        @if($canManualApprove ?? false)
                            <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" class="inline-block"
                                onsubmit="return confirmAction(event, 'Aprovar manualmente?', 'A compra sera aprovada sem pagamento em gateway, com baixa da fatura e envio de e-mails.');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                    <i class="fas fa-check mr-1"></i> Aprovar Manualmente (Permuta)
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-secondary btn-block font-weight-bold" disabled>
                                <i class="fas fa-lock mr-1"></i> Aprovacao manual desabilitada
                            </button>
                        @endif
                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="mt-2"
                            id="form-cancel-{{ $order->id }}"
                            onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar Pedido
                            </button>
                        </form>
                    @endif

                    @if($order->status === 'cancelled')
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-ban mb-1"></i><br>
                            Pedido Cancelado
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Itens do Pedido</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>Preco</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php($itemGross = (float) $item->gross_unit_price)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->item_type }}</td>
                                    <td>
                                        R$ {{ number_format($itemGross, 2, ',', '.') }}
                                        @if($discountAmount > 0)
                                            <div class="text-success small font-weight-bold">Cupom {{ $couponCode ?: '-' }}</div>
                                            <div class="text-muted small">Líquido: R$ {{ number_format((float) $item->price, 2, ',', '.') }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($order->is_partially_refunded)
                <div class="alert alert-warning mt-3">
                    <h5><i class="icon fas fa-coins"></i> Pedido com estorno parcial</h5>
                    Ja foram estornados R$ {{ number_format($order->refunded_amount, 2, ',', '.') }} e ainda restam
                    R$ {{ number_format($order->remaining_refundable_amount, 2, ',', '.') }} disponiveis para estorno.
                    @if($order->last_refund_at)
                        <div class="mt-1">Ultimo estorno em {{ $order->last_refund_at->format('d/m/Y H:i') }}.</div>
                    @endif
                </div>
            @elseif($order->refunded_at)
                <div class="alert alert-danger mt-3">
                    <h5><i class="icon fas fa-ban"></i> Pedido Reembolsado!</h5>
                    Este pedido foi reembolsado em {{ $order->refunded_at->format('d/m/Y H:i') }}.
                </div>
            @endif
        </div>
    </div>
@endsection
