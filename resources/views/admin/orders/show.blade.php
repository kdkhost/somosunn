@extends('admin.layouts.app')

@section('title', 'Detalhes da Venda')
@section('page_title', 'Venda #' . $order->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">Venda #{{ $order->id }}</li>
@endsection

@section('content')
    @php
        $user = $order->user;
        $grossAmount = (float) $order->gross_amount;
        $netAmount = (float) $order->net_amount;
        $discountAmount = (float) $order->financial_discount_amount;
        $refundedAmount = (float) $order->refunded_amount;
        $remainingRefundableAmount = (float) $order->remaining_refundable_amount;
        $couponCode = trim((string) ($order->coupon_code ?? ''));
        $saleTypeLabel = $order->saleTypeLabel();
        $paymentMethod = trim((string) ($order->payment_method ?? ''));
        $transactionId = trim((string) ($order->transaction_id ?? ''));
        $isPending = (string) $order->status === 'pending';
        $isPaid = (string) $order->status === 'paid';
        $isRefunded = (string) $order->status === 'refunded';
        $statusLabel = match (true) {
            $order->is_partially_refunded => 'Parcialmente reembolsado',
            $isPaid => 'Pago',
            $isPending => 'Pendente',
            $isRefunded => 'Reembolsado',
            (string) $order->status === 'cancelled' => 'Cancelado',
            default => ucfirst((string) $order->status),
        };
        $statusClass = match (true) {
            $order->is_partially_refunded => 'warning',
            $isPaid => 'success',
            $isPending => 'warning',
            $isRefunded => 'danger',
            (string) $order->status === 'cancelled' => 'secondary',
            default => 'light',
        };
        $photo = trim((string) ($user->photo ?? ''));
        $avatarUrl = $photo !== ''
            ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
            : asset('img/default-user.svg');
    @endphp

    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
            <div class="d-flex flex-wrap" style="gap:10px;">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar para vendas
                </a>
                @if($order->invoice)
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-outline-info rounded-pill" data-pjax>
                        <i class="fas fa-file-invoice mr-1"></i> Abrir fatura
                    </a>
                @endif
            </div>
            <span class="badge badge-{{ $statusClass }} px-3 py-2">{{ $statusLabel }}</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Valor bruto</span>
                    <span class="info-box-number">R$ {{ number_format($grossAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-cash-register"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total liquido</span>
                    <span class="info-box-number">R$ {{ number_format($netAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Desconto</span>
                    <span class="info-box-number">{{ $discountAmount > 0 ? 'R$ ' . number_format($discountAmount, 2, ',', '.') : 'Sem desconto' }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-danger elevation-1"><i class="fas fa-undo"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Saldo reembolsavel</span>
                    <span class="info-box-number">R$ {{ number_format($remainingRefundableAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user mr-2 text-primary"></i>Cliente</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="{{ $avatarUrl }}" alt="Cliente" class="img-circle elevation-2 mr-3"
                            style="width:64px; height:64px; object-fit:cover;"
                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                        <div class="min-w-0">
                            <div class="font-weight-bold text-lg">{{ $user->name ?? 'Usuario removido' }}</div>
                            <div class="text-muted text-sm">{{ $user->email ?? 'Sem e-mail' }}</div>
                            @if(!empty($user->phone))
                                <div class="text-muted text-sm"><i class="fas fa-phone mr-1"></i>{{ $user->phone }}</div>
                            @endif
                        </div>
                    </div>

                    @if($order->buyerAddress())
                        <div class="alert alert-light border mt-3 mb-0">
                            <strong><i class="fas fa-map-marker-alt mr-1 text-danger"></i>Endereco</strong>
                            <div class="small mt-1">{{ $order->buyerAddress() }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-receipt mr-2 text-secondary"></i>Resumo da venda</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tipo principal</span>
                            <strong>{{ $saleTypeLabel }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Gateway</span>
                            <strong>{{ ucfirst((string) $order->gateway) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Metodo</span>
                            <strong>{{ $paymentMethod !== '' ? ucfirst(str_replace('_', ' ', $paymentMethod)) : '-' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Criado em</span>
                            <strong>{{ optional($order->created_at)->format('d/m/Y H:i') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Pago em</span>
                            <strong>{{ optional($order->paid_at)->format('d/m/Y H:i') ?: '-' }}</strong>
                        </li>
                        @if($couponCode !== '')
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Cupom</span>
                                <strong>{{ $couponCode }}</strong>
                            </li>
                        @endif
                        @if($order->is_manual_approval)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Origem</span>
                                <strong class="text-warning">Aprovacao manual</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Aprovado por</span>
                                <strong>{{ $order->manualApprover->name ?? '-' }}</strong>
                            </li>
                        @endif
                        @if($transactionId !== '')
                            <li class="list-group-item">
                                <span class="d-block text-muted small mb-1">Transacao</span>
                                <code class="d-block text-break">{{ $transactionId }}</code>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice mr-2 text-info"></i>Fatura</h3>
                </div>
                <div class="card-body">
                    @if($order->invoice)
                        <div class="mb-3">
                            <div class="text-muted text-sm">Numero</div>
                            <div class="font-weight-bold">{{ $order->invoice->number ?: ('#' . $order->invoice->id) }}</div>
                        </div>
                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-outline-secondary btn-sm" data-pjax>
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                            <a href="{{ route('admin.invoices.pdf', $order->invoice) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <form method="POST" action="{{ route('admin.invoices.send', $order->invoice) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="force" value="1">
                                <button class="btn btn-outline-success btn-sm" type="submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Enviar e-mail
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted mb-3">Ainda nao existe fatura emitida para esta venda.</p>
                        <form method="POST" action="{{ route('admin.orders.invoice', $order) }}">
                            @csrf
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-file-invoice mr-1"></i> Emitir e enviar fatura
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card card-outline {{ $isPaid ? 'card-danger' : ($isPending ? 'card-warning' : 'card-light') }} shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-tools mr-2"></i>Acoes</h3>
                </div>
                <div class="card-body">
                    @if($isPaid && $remainingRefundableAmount > 0)
                        <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST"
                            onsubmit="return confirmAction(event, 'Estorno total?', 'Esta acao vai devolver todo o valor restante no gateway. Deseja continuar?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-undo mr-1"></i> Estorno total
                            </button>
                        </form>

                        @if($order->supportsPartialRefund())
                            <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" class="mt-3"
                                onsubmit="return confirmAction(event, 'Estorno parcial?', 'O valor informado sera devolvido ao cliente e o pedido continuara com saldo restante.');">
                                @csrf
                                <label class="small text-muted mb-1">Valor parcial</label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
                                        max="{{ number_format($remainingRefundableAmount, 2, '.', '') }}"
                                        placeholder="Max. {{ number_format($remainingRefundableAmount, 2, ',', '.') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-warning font-weight-bold">
                                            <i class="fas fa-coins mr-1"></i> Estorno parcial
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Disponivel quando o gateway suporta estorno parcial.</small>
                            </form>
                        @endif

                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="mt-3"
                            onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido pago?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar pedido
                            </button>
                        </form>
                    @elseif($isPending)
                        @if($canManualApprove ?? false)
                            <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST"
                                onsubmit="return confirmAction(event, 'Aprovar manualmente?', 'A compra sera aprovada sem pagamento em gateway, com baixa da fatura e envio de e-mails.');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                    <i class="fas fa-check mr-1"></i> Aprovar manualmente
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-secondary btn-block font-weight-bold" disabled>
                                <i class="fas fa-lock mr-1"></i> Aprovacao manual desabilitada
                            </button>
                        @endif

                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="mt-3"
                            onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar pedido
                            </button>
                        </form>
                    @else
                        <div class="text-muted small mb-0">Nao ha acoes disponiveis para o status atual desta venda.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if($order->is_partially_refunded)
                <div class="alert alert-warning">
                    <h5><i class="fas fa-coins mr-1"></i> Estorno parcial registrado</h5>
                    Ja foram estornados R$ {{ number_format($refundedAmount, 2, ',', '.') }}.
                    Ainda restam R$ {{ number_format($remainingRefundableAmount, 2, ',', '.') }} disponiveis.
                    @if($order->last_refund_at)
                        <div class="mt-1">Ultimo estorno em {{ $order->last_refund_at->format('d/m/Y H:i') }}.</div>
                    @endif
                </div>
            @elseif($order->refunded_at)
                <div class="alert alert-danger">
                    <h5><i class="fas fa-ban mr-1"></i> Venda reembolsada</h5>
                    Este pedido foi reembolsado em {{ $order->refunded_at->format('d/m/Y H:i') }}.
                </div>
            @elseif((string) $order->status === 'cancelled')
                <div class="alert alert-secondary">
                    <h5><i class="fas fa-times-circle mr-1"></i> Venda cancelada</h5>
                    Esta venda foi cancelada e nao possui novas acoes operacionais.
                </div>
            @endif

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-box-open mr-2 text-primary"></i>Itens da venda</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th class="text-center">Qtd.</th>
                                <th class="text-right">Unitario bruto</th>
                                <th class="text-right">Total bruto</th>
                                <th class="text-right">Total liquido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $quantity = max(1, (int) $item->quantity);
                                    $itemGrossUnit = (float) $item->gross_unit_price;
                                    $itemGrossLine = (float) $item->line_gross_amount;
                                    $itemNetLine = round((float) $item->price * $quantity, 2);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $item->title }}</div>
                                        @if($couponCode !== '' && $discountAmount > 0)
                                            <div class="small text-success">Cupom aplicado: {{ $couponCode }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $order->saleTypeLabel() }}</td>
                                    <td class="text-center">{{ $quantity }}</td>
                                    <td class="text-right">R$ {{ number_format($itemGrossUnit, 2, ',', '.') }}</td>
                                    <td class="text-right">R$ {{ number_format($itemGrossLine, 2, ',', '.') }}</td>
                                    <td class="text-right font-weight-bold">R$ {{ number_format($itemNetLine, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-right">Resumo</th>
                                <th class="text-right">R$ {{ number_format($grossAmount, 2, ',', '.') }}</th>
                                <th class="text-right">
                                    @if($discountAmount > 0)
                                        <div class="text-success font-weight-bold">- R$ {{ number_format($discountAmount, 2, ',', '.') }}</div>
                                    @endif
                                    <div>R$ {{ number_format($netAmount, 2, ',', '.') }}</div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
