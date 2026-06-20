@extends('panel.layouts.app')

@section('title', 'Extrato de Recebimentos - UNN')

@section('panel_content')
    @php
        $paidTotal = $splits->filter(fn ($split) => ($split->payout->status ?? 'pending') === 'paid')->sum('amount');
        $pendingTotal = $splits->filter(fn ($split) => in_array(($split->payout->status ?? 'pending'), ['pending', 'processing'], true))->sum('amount');
        $failedTotal = $splits->filter(fn ($split) => ($split->payout->status ?? '') === 'failed')->sum('amount');
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">Meus Recebimentos</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Acompanhe o rateio gerado pelas suas vendas e o andamento real do repasse.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-green-700 dark:text-green-300 uppercase block mb-1">Liquidado</span>
                    <span class="text-xl font-extrabold text-green-900 dark:text-green-100">R$ {{ number_format($paidTotal, 2, ',', '.') }}</span>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase block mb-1">Aguardando repasse</span>
                    <span class="text-xl font-extrabold text-blue-900 dark:text-blue-100">R$ {{ number_format($pendingTotal, 2, ',', '.') }}</span>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-300 uppercase block mb-1">Com falha</span>
                    <span class="text-xl font-extrabold text-rose-900 dark:text-rose-100">R$ {{ number_format($failedTotal, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Historico de Recebimentos</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Pedido</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Valor</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Repasse</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Observacao</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($splits as $split)
                        @php
                            $payout = $split->payout;
                            $payoutStatus = $payout?->status ?? 'pending';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 dark:text-white">#{{ $split->order_id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-lg font-extrabold text-slate-900 dark:text-white">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</span>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ number_format((float) $split->percentage, 2, ',', '.') }}% do total</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($payoutStatus === 'paid')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Liquidado
                                    </span>
                                @elseif($payoutStatus === 'failed')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Falha no repasse
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Aguardando repasse
                                    </span>
                                @endif
                                <div class="mt-2 text-[11px] text-slate-400">
                                    {{ ($payout?->provider ?? 'manual') === 'internal' ? 'Liquidacao interna' : 'Repasse manual controlado' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                    @if(!empty($payout?->last_error))
                                        {{ $payout->last_error }}
                                    @elseif($payout?->processed_at)
                                        Confirmado em {{ $payout->processed_at->format('d/m/Y H:i') }}
                                    @else
                                        Aguardando tratamento financeiro
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $split->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4">
                                        <i class="fas fa-receipt text-slate-300 dark:text-slate-600 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 font-medium">Nenhum recebimento registrado ainda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($splits->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                {{ $splits->links() }}
            </div>
        @endif
    </div>

    @if(!$user->pix_key)
        <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-100">Chave PIX nao cadastrada</h4>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">Para receber seus repasses, voce precisa cadastrar sua chave PIX no <a href="{{ route('panel.profile.edit') }}" class="underline font-bold">seu perfil</a>.</p>
            </div>
        </div>
    @endif
@endsection
