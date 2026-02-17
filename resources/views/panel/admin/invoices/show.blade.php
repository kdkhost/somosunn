@extends('panel.layouts.app')

@section('title', 'Detalhes da Fatura')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb / Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
                <a href="{{ route('panel.admin.invoices.index') }}"
                    class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Faturas</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-900 dark:text-white font-medium transition-colors">{{ $invoice->number ?: '#' . $invoice->id }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('panel.admin.invoices.edit', $invoice) }}"
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all shadow-sm">
                    <i class="fas fa-edit"></i>
                    Editar
                </a>
                <a href="{{ route('panel.admin.invoices.pdf', $invoice) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                    <i class="fas fa-file-pdf"></i>
                    Visualizar PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-sm">
            {{-- Fatura Info (Header style) --}}
            <div
                class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 flex flex-col md:flex-row justify-between gap-8 transition-colors duration-300">
                <div class="space-y-4">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white uppercase transition-colors">Fatura</h1>
                        <p class="text-lg font-mono text-slate-500 dark:text-slate-400 transition-colors">{{ $invoice->number ?: '#' . $invoice->id }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-x-12 gap-y-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500 mb-1 transition-colors">Status</p>
                            @php
                                $statusClasses = match ($invoice->status) {
                                    'paid' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
                                    'draft' => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                    'cancelled' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-100 dark:border-red-800/50',
                                    default => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800/50',
                                };
                                $label = match ($invoice->status) {
                                    'paid' => 'Paga',
                                    'draft' => 'Rascunho',
                                    'cancelled' => 'Cancelada',
                                    default => 'Emitida',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }} transition-colors">
                                {{ $label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500 mb-1 transition-colors">Emissão</p>
                            <p class="font-semibold text-slate-900 dark:text-white transition-colors">
                                {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500 mb-1 transition-colors">Vencimento</p>
                            <p class="font-semibold text-slate-900 dark:text-white transition-colors">
                                {{ $invoice->due_at ? $invoice->due_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        @if($invoice->paid_at)
                            <div>
                                <p class="text-[10px] uppercase tracking-wider font-bold text-emerald-600 dark:text-emerald-500 mb-1 transition-colors">
                                    Pagamento</p>
                                <p class="font-bold text-emerald-700 dark:text-emerald-400 transition-colors">{{ $invoice->paid_at->format('d/m/Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:text-right space-y-4 max-w-sm">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500 mb-1 transition-colors">Cliente</p>
                        <p class="text-base font-bold text-slate-900 dark:text-white transition-colors">{{ $invoice->user->name ?? '—' }}</p>
                        <p class="text-slate-500 dark:text-slate-400 transition-colors">{{ $invoice->user->email ?? '' }}</p>
                    </div>

                    @if($invoice->order_id)
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 transition-colors">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500 mb-1 transition-colors">Referente ao Pedido
                            </p>
                            <a href="{{ route('panel.admin.orders.show', $invoice->order_id) }}"
                                class="text-blue-600 dark:text-blue-400 hover:underline font-bold transition-colors">#{{ $invoice->order_id }}</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Itens da Fatura --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden self-start transition-colors duration-300">
                <table class="w-full text-left">
                    <thead
                        class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold transition-colors">
                        <tr>
                            <th class="px-6 py-4">Descrição</th>
                            <th class="px-6 py-4 w-24 text-center">Qtd.</th>
                            <th class="px-6 py-4 w-32 text-right">Unitário</th>
                            <th class="px-6 py-4 w-32 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($invoice->items as $item)
                            <tr class="transition-colors">
                                <td class="px-6 py-5 text-sm font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500 dark:text-slate-400 text-center">{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-sm text-slate-500 dark:text-slate-400 text-right">R$
                                    {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-6 py-5 text-sm font-bold text-slate-900 dark:text-white text-right">R$
                                    {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totais e Ações --}}
            <div class="space-y-6">
                {{-- Totais --}}
                <div class="bg-slate-900 dark:bg-slate-950 rounded-2xl p-6 shadow-xl shadow-slate-200 dark:shadow-none text-white transition-colors duration-300">
                    <div class="space-y-3">
                        <div
                            class="flex justify-between items-center text-slate-400 dark:text-slate-500 text-xs uppercase tracking-wider font-bold transition-colors">
                            <span>Subtotal</span>
                            <span>R$ {{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($invoice->discount_amount > 0)
                            <div
                                class="flex justify-between items-center text-emerald-400 dark:text-emerald-500 text-xs uppercase tracking-wider font-bold transition-colors">
                                <span>Desconto</span>
                                <span>- R$ {{ number_format((float) $invoice->discount_amount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-4 border-t border-slate-800 dark:border-slate-800 flex justify-between items-end transition-colors">
                            <span class="text-xs uppercase tracking-tight font-black text-slate-500">Total</span>
                            <span class="text-3xl font-black">R$
                                {{ number_format((float) $invoice->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Ações Rápidas --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-3 transition-colors duration-300">
                    <form action="{{ route('panel.admin.invoices.send', $invoice) }}" method="POST">
                        @csrf
                        <input type="hidden" name="force" value="1">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all shadow-sm">
                            <i class="fas fa-paper-plane"></i>
                            Enviar por E-mail
                        </button>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center mt-2 leading-tight transition-colors">
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
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/30 transform hover:scale-[1.02]">
                                <i class="fas fa-check"></i>
                                Marcar como Paga
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Notas --}}
                @if($invoice->notes)
                    <div
                        class="bg-amber-50 dark:bg-amber-900/10 rounded-2xl p-6 border border-amber-100 dark:border-amber-900/30 italic text-amber-900 dark:text-amber-400 text-xs leading-relaxed transition-colors">
                        <p class="font-bold text-[10px] uppercase tracking-wider text-amber-700 dark:text-amber-500 not-italic mb-2 transition-colors">Observações</p>
                        {!! nl2br(e($invoice->notes)) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection