@extends('panel.layouts.app')

@section('title', 'Detalhes da Fatura')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb / Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 text-sm text-slate-500">
                <a href="{{ route('panel.admin.invoices.index') }}"
                    class="hover:text-blue-600 transition-colors">Faturas</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-900 font-medium">{{ $invoice->number ?: '#' . $invoice->id }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('panel.admin.invoices.edit', $invoice) }}"
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm">
                    <i class="fas fa-edit"></i>
                    Editar
                </a>
                <a href="{{ route('panel.admin.invoices.pdf', $invoice) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-sm shadow-blue-200">
                    <i class="fas fa-file-pdf"></i>
                    Visualizar PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-sm">
            {{-- Fatura Info (Header style) --}}
            <div
                class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col md:flex-row justify-between gap-8">
                <div class="space-y-4">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 uppercase">Fatura</h1>
                        <p class="text-lg font-mono text-slate-500">{{ $invoice->number ?: '#' . $invoice->id }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-x-12 gap-y-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Status</p>
                            @php
                                $statusClasses = match ($invoice->status) {
                                    'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'draft' => 'bg-slate-50 text-slate-600 border-slate-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                                    default => 'bg-blue-50 text-blue-700 border-blue-100',
                                };
                                $label = match ($invoice->status) {
                                    'paid' => 'Paga',
                                    'draft' => 'Rascunho',
                                    'cancelled' => 'Cancelada',
                                    default => 'Emitida',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }}">
                                {{ $label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Emissão</p>
                            <p class="font-semibold text-slate-900">
                                {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Vencimento</p>
                            <p class="font-semibold text-slate-900">
                                {{ $invoice->due_at ? $invoice->due_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        @if($invoice->paid_at)
                            <div>
                                <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1 text-emerald-600">
                                    Pagamento</p>
                                <p class="font-bold text-emerald-700">{{ $invoice->paid_at->format('d/m/Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:text-right space-y-4 max-w-sm">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Cliente</p>
                        <p class="text-base font-bold text-slate-900">{{ $invoice->user->name ?? '—' }}</p>
                        <p class="text-slate-500">{{ $invoice->user->email ?? '' }}</p>
                    </div>

                    @if($invoice->order_id)
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Referente ao Pedido
                            </p>
                            <a href="{{ route('panel.admin.orders.show', $invoice->order_id) }}"
                                class="text-blue-600 hover:underline font-bold">#{{ $invoice->order_id }}</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Itens da Fatura --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden self-start">
                <table class="w-full text-left">
                    <thead
                        class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                        <tr>
                            <th class="px-6 py-4">Descrição</th>
                            <th class="px-6 py-4 w-24 text-center">Qtd.</th>
                            <th class="px-6 py-4 w-32 text-right">Unitário</th>
                            <th class="px-6 py-4 w-32 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-5 text-sm font-medium text-slate-900">{{ $item->description }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500 text-center">{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500 text-right">R$
                                    {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-6 py-5 text-sm font-bold text-slate-900 text-right">R$
                                    {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totais e Ações --}}
            <div class="space-y-6">
                {{-- Totais --}}
                <div class="bg-slate-900 rounded-2xl p-6 shadow-xl shadow-slate-200 text-white">
                    <div class="space-y-3">
                        <div
                            class="flex justify-between items-center text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <span>Subtotal</span>
                            <span>R$ {{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($invoice->discount_amount > 0)
                            <div
                                class="flex justify-between items-center text-emerald-400 text-xs uppercase tracking-wider font-bold">
                                <span>Desconto</span>
                                <span>- R$ {{ number_format((float) $invoice->discount_amount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-4 border-t border-slate-800 flex justify-between items-end">
                            <span class="text-xs uppercase tracking-tight font-black text-slate-500">Total</span>
                            <span class="text-3xl font-black">R$
                                {{ number_format((float) $invoice->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Ações Rápidas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3">
                    <form action="{{ route('panel.admin.invoices.send', $invoice) }}" method="POST">
                        @csrf
                        <input type="hidden" name="force" value="1">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm">
                            <i class="fas fa-paper-plane"></i>
                            Enviar por E-mail
                        </button>
                        <p class="text-[10px] text-slate-400 text-center mt-2 leading-tight">
                            @if($invoice->email_sent_at)
                                Último envio: {{ $invoice->email_sent_at->format('d/m/Y H:i') }}
                            @else
                                Ainda não enviado por e-mail.
                            @endif
                        </p>
                    </form>

                    @if($invoice->status !== 'paid')
                        <form action="{{ route('panel.admin.invoices.update', $invoice) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="paid">
                            {{-- Hidden items to satisfy validation if needed, or controller handles partial updates --}}
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200">
                                <i class="fas fa-check"></i>
                                Marcar como Paga
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Notas --}}
                @if($invoice->notes)
                    <div
                        class="bg-amber-50 rounded-2xl p-6 border border-amber-100 italic text-amber-900 text-xs leading-relaxed">
                        <p class="font-bold text-[10px] uppercase tracking-wider text-amber-700 not-italic mb-2">Observações</p>
                        {!! nl2br(e($invoice->notes)) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection