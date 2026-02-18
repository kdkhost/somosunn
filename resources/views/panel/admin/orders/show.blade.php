@extends('panel.layouts.app')

@section('title', 'Detalhes do Pedido')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb / Header --}}
        <div class="flex items-center gap-4 text-sm text-slate-500">
            <a href="{{ route('panel.admin.orders.index') }}" class="hover:text-blue-600 transition-colors">Vendas</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-slate-900 font-medium">Pedido #{{ $order->id }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Customer & Actions --}}
            <div class="space-y-6">
                {{-- Customer Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border-2 border-slate-100 mb-4">
                            @if($order->user && $order->user->profile_photo_url)
                                <img src="{{ $order->user->profile_photo_url }}" alt="{{ $order->user->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <span
                                    class="text-2xl font-bold text-slate-500">{{ mb_substr($order->user->name ?? '?', 0, 1) }}</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $order->user->name ?? 'Usuário Removido' }}</h3>
                        <p class="text-sm text-slate-500">{{ $order->user->email ?? 'Sem e-mail' }}</p>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-sm text-slate-500">Status</span>
                            @if($order->status == 'paid')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">Pago</span>
                            @elseif($order->status == 'pending')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">Pendente</span>
                            @elseif($order->status == 'refunded')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">Reembolsado</span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200">{{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-sm text-slate-500">Data</span>
                            <span
                                class="text-sm font-medium text-slate-900">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-sm text-slate-500">Total</span>
                            <span class="text-sm font-bold text-slate-900">R$
                                {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-sm text-slate-500">Gateway</span>
                            <span class="text-sm font-medium text-slate-900">{{ ucfirst($order->gateway) }}</span>
                        </div>
                        @if($order->transaction_id)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-slate-500">Transação ID</span>
                                <span class="text-xs font-mono text-slate-400"
                                    title="{{ $order->transaction_id }}">{{ Str::limit($order->transaction_id, 15) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Invoice Actions (TODO: Migrate Invoices) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h4 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-slate-400"></i> Fatura
                    </h4>

                    @if($order->invoice)
                        <div class="flex items-center justify-between mb-4 bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <span
                                class="text-sm font-mono text-slate-600">{{ $order->invoice->number ?: '#' . $order->invoice->id }}</span>
                            <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">Emitida</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            {{-- Legacy Routes for now as placeholders --}}
                            <a href="{{ route('admin.invoices.show', $order->invoice) }}"
                                class="flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('admin.invoices.pdf', $order->invoice) }}" target="_blank"
                                class="flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <form action="{{ route('admin.invoices.send', $order->invoice) }}" method="POST" class="col-span-2">
                                @csrf
                                <input type="hidden" name="force" value="1">
                                <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors border border-blue-200">
                                    <i class="fas fa-paper-plane"></i> Enviar por e-mail
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('admin.orders.invoice', $order) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-blue-200">
                                <i class="fas fa-magic"></i> Emitir Fatura
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Refund Action --}}
                @if($order->status === 'paid')
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 border-l-4 border-l-red-500">
                        <h4 class="font-bold text-red-700 mb-2">Zona de Perigo</h4>
                        <p class="text-xs text-red-600 mb-4 leading-relaxed">
                            Reembolsar este pedido irá estornar o pagamento no gateway e revogar o acesso do usuário. Esta ação
                            não pode ser desfeita.
                        </p>
                        <form action="{{ route('panel.admin.orders.refund', $order->id) }}" method="POST"
                            onsubmit="return confirmAction(event, 'ATENÇÃO: Isso irá devolver o dinheiro ao cliente. Tem certeza?');">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-bold rounded-xl transition-all border border-red-200">
                                <i class="fas fa-undo-alt"></i> Reembolsar Pedido
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Right Column: Items --}}
            <div class="lg:col-span-2 space-y-6">
                @if($order->refunded_at)
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <div>
                            <h4 class="font-bold text-red-800">Pedido Reembolsado</h4>
                            <p class="text-sm text-red-600">Este pedido foi reembolsado em
                                {{ \Carbon\Carbon::parse($order->refunded_at)->format('d/m/Y \à\s H:i') }}.</p>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900">Itens do Pedido</h3>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Item</th>
                                <th class="px-6 py-3 font-semibold">Tipo</th>
                                <th class="px-6 py-3 font-semibold text-right">Preço</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $item->title }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $item->item_type }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900 text-right">R$
                                        {{ number_format($item->price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-sm font-bold text-slate-900 text-right">Total</td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">R$
                                    {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection