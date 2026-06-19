@extends('panel.layouts.app')

@section('title', 'Detalhes da Venda')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.orders.index') }}" class="hover:underline">Vendas</a>
    <span class="mx-2 text-slate-300">/</span>
    <span class="font-bold text-slate-600 dark:text-slate-400">Venda #{{ $order->id }}</span>
@endsection

@section('panel_content')
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
        $statusClasses = match (true) {
            $order->is_partially_refunded => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
            $isPaid => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200',
            $isPending => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
            $isRefunded => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200',
            (string) $order->status === 'cancelled' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Detalhes da venda #{{ $order->id }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Resumo operacional, financeiro e acoes disponiveis para este pedido.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('panel.admin.orders.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
                @if($order->invoice)
                    <a href="{{ route('panel.admin.invoices.show', $order->invoice) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-950">
                        <i class="fas fa-file-invoice"></i>
                        <span>Fatura</span>
                    </a>
                @endif
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-black {{ $statusClasses }}">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Valor bruto</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">R$ {{ number_format($grossAmount, 2, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Total liquido</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">R$ {{ number_format($netAmount, 2, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Desconto</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $discountAmount > 0 ? 'R$ ' . number_format($discountAmount, 2, ',', '.') : '-' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Saldo reembolsavel</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">R$ {{ number_format($remainingRefundableAmount, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 overflow-hidden rounded-full border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            @if($user && $user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl font-black text-slate-500 dark:text-slate-300">{{ mb_substr($user->name ?? '?', 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-lg font-black text-slate-900 dark:text-white">{{ $user->name ?? 'Usuario removido' }}</div>
                            <div class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email ?? 'Sem e-mail' }}</div>
                            @if(!empty($user->phone))
                                <div class="text-sm text-slate-500 dark:text-slate-400"><i class="fas fa-phone mr-1"></i>{{ $user->phone }}</div>
                            @endif
                        </div>
                    </div>
                    @if($order->buyerAddress())
                        <div class="mt-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-4">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Endereco</p>
                            <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">{{ $order->buyerAddress() }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Resumo da venda</h3>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Tipo principal</dt>
                            <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ $saleTypeLabel }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Gateway</dt>
                            <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ ucfirst((string) $order->gateway) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Metodo</dt>
                            <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ $paymentMethod !== '' ? ucfirst(str_replace('_', ' ', $paymentMethod)) : '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Criado em</dt>
                            <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ optional($order->created_at)->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Pago em</dt>
                            <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ optional($order->paid_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                        </div>
                        @if($couponCode !== '')
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400">Cupom</dt>
                                <dd class="text-sm font-bold text-slate-900 dark:text-white">{{ $couponCode }}</dd>
                            </div>
                        @endif
                        @if($transactionId !== '')
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400">Transacao</dt>
                                <dd class="min-w-0 text-right text-sm font-bold text-blue-700 dark:text-blue-300" style="max-width:68%; overflow-wrap:anywhere;">
                                    {{ $transactionId }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Fatura</h3>
                    @if($order->invoice)
                        <div class="mt-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-4">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Numero</p>
                            <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $order->invoice->number ?: ('#' . $order->invoice->id) }}</p>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('panel.admin.invoices.show', $order->invoice) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('panel.admin.invoices.pdf', $order->invoice) }}" target="_blank"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <form action="{{ route('panel.admin.invoices.send', $order->invoice) }}" method="POST" class="col-span-2">
                                @csrf
                                <input type="hidden" name="force" value="1">
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-4 py-3 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-950">
                                    <i class="fas fa-paper-plane"></i> Enviar por e-mail
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Ainda nao existe fatura emitida para esta venda.</p>
                        <form action="{{ route('panel.admin.orders.invoice', $order) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700">
                                <i class="fas fa-file-invoice"></i> Emitir fatura
                            </button>
                        </form>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Acoes</h3>
                    <div class="mt-4 space-y-3">
                        @if($isPaid && $remainingRefundableAmount > 0)
                            <form action="{{ route('panel.admin.orders.refund', $order->id) }}" method="POST"
                                onsubmit="return confirmAction(event, 'Estorno total?', 'Isso vai devolver todo o valor restante ao cliente. Deseja continuar?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm font-bold text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-950">
                                    <i class="fas fa-undo"></i> Estorno total
                                </button>
                            </form>

                            @if($order->supportsPartialRefund())
                                <form action="{{ route('panel.admin.orders.refund', $order->id) }}" method="POST" class="space-y-3"
                                    onsubmit="return confirmAction(event, 'Estorno parcial?', 'O valor informado sera devolvido ao cliente e o pedido continuara com saldo restante.');">
                                    @csrf
                                    <div>
                                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Valor parcial</label>
                                        <input name="amount" type="number" step="0.01" min="0.01"
                                            max="{{ number_format($remainingRefundableAmount, 2, '.', '') }}"
                                            placeholder="Max. R$ {{ number_format($remainingRefundableAmount, 2, ',', '.') }}"
                                            class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm text-slate-900 dark:text-white">
                                    </div>
                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm font-bold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-950">
                                        <i class="fas fa-coins"></i> Estorno parcial
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('panel.admin.orders.cancel', $order->id) }}" method="POST"
                                onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido pago?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <i class="fas fa-times"></i> Cancelar pedido
                                </button>
                            </form>
                        @elseif($isPending)
                            <form action="{{ route('panel.admin.orders.cancel', $order->id) }}" method="POST"
                                onsubmit="return confirmAction(event, 'Cancelar pedido?', 'Deseja realmente cancelar este pedido?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm font-bold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-950">
                                    <i class="fas fa-times"></i> Cancelar pedido
                                </button>
                            </form>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nao ha acoes operacionais disponiveis para o status atual.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 space-y-6">
                @if($order->is_partially_refunded)
                    <div class="rounded-3xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/20 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Estorno parcial</h3>
                        <p class="mt-2 text-sm font-medium text-amber-800 dark:text-amber-200">
                            Ja foram estornados R$ {{ number_format($refundedAmount, 2, ',', '.') }} e ainda restam
                            R$ {{ number_format($remainingRefundableAmount, 2, ',', '.') }} disponiveis.
                        </p>
                        @if($order->last_refund_at)
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Ultimo estorno em {{ $order->last_refund_at->format('d/m/Y H:i') }}.</p>
                        @endif
                    </div>
                @elseif($order->refunded_at)
                    <div class="rounded-3xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/20 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-red-700 dark:text-red-300">Venda reembolsada</h3>
                        <p class="mt-2 text-sm font-medium text-red-800 dark:text-red-200">Este pedido foi reembolsado em {{ $order->refunded_at->format('d/m/Y H:i') }}.</p>
                    </div>
                @endif

                <div class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                    <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-5">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Itens da venda</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cada linha mostra o total bruto e o total liquido por item.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Item</th>
                                    <th class="px-6 py-4 font-bold">Tipo</th>
                                    <th class="px-6 py-4 text-center font-bold">Qtd.</th>
                                    <th class="px-6 py-4 text-right font-bold">Unitario bruto</th>
                                    <th class="px-6 py-4 text-right font-bold">Total bruto</th>
                                    <th class="px-6 py-4 text-right font-bold">Total liquido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($order->items as $item)
                                    @php
                                        $quantity = max(1, (int) $item->quantity);
                                        $itemGrossUnit = (float) $item->gross_unit_price;
                                        $itemGrossLine = (float) $item->line_gross_amount;
                                        $itemNetLine = round((float) $item->price * $quantity, 2);
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $item->title }}</div>
                                            @if($couponCode !== '' && $discountAmount > 0)
                                                <div class="mt-1 text-xs font-bold text-emerald-600 dark:text-emerald-300">Cupom aplicado: {{ $couponCode }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $saleTypeLabel }}</td>
                                        <td class="px-6 py-4 text-center text-sm font-bold text-slate-900 dark:text-white">{{ $quantity }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-slate-700 dark:text-slate-200">R$ {{ number_format($itemGrossUnit, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-slate-700 dark:text-slate-200">R$ {{ number_format($itemGrossLine, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format($itemNetLine, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-sm font-black text-slate-900 dark:text-white">Resumo</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format($grossAmount, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @if($discountAmount > 0)
                                            <div class="text-sm font-black text-emerald-700 dark:text-emerald-300">- R$ {{ number_format($discountAmount, 2, ',', '.') }}</div>
                                        @endif
                                        <div class="text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format($netAmount, 2, ',', '.') }}</div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
