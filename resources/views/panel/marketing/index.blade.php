@extends('panel.layouts.app')

@section('title', 'Marketing da Plataforma')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8 rounded-2xl bg-gradient-to-br from-purple-600 via-fuchsia-600 to-pink-500 p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white/80 mb-2">
                    <i class="fas fa-bullhorn"></i> Area exclusiva
                </div>
                <h1 class="text-2xl sm:text-3xl font-black mb-1">Marketing da Plataforma</h1>
                <p class="text-sm text-white/90 max-w-2xl">
                    Voce e o Responsavel pelo Marketing e recebe {{ number_format($percent, 2, ',', '.') }}% de cada venda concluida.
                    Utilize esses valores para o trafego pago e a promocao da plataforma.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center backdrop-blur">
                    <i class="fas fa-chart-line text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Recebido</p>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($metrics['total_paid'], 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['count_paid'] }} split(s) pagos</p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                    <i class="fas fa-clock"></i>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">A receber</p>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($metrics['total_pending'], 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['count_pending'] }} split(s) pendentes</p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <i class="fas fa-coins"></i>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total acumulado</p>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($metrics['total_all'], 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['count_all'] }} split(s) no total</p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-600 dark:text-fuchsia-300 flex items-center justify-center">
                    <i class="fas fa-percentage"></i>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Sua cota</p>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($percent, 2, ',', '.') }}%</p>
            <p class="text-xs text-slate-500 mt-1">por venda concluida</p>
        </div>
    </div>

    {{-- Lista de splits --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900 dark:text-white">
                <i class="fas fa-list-ul mr-2 text-purple-500"></i> Historico de splits
            </h2>
            <span class="text-xs text-slate-500">Ultimos 200 registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold uppercase tracking-wider text-xs">#Pedido</th>
                        <th class="px-4 py-3 text-left font-bold uppercase tracking-wider text-xs">Data</th>
                        <th class="px-4 py-3 text-right font-bold uppercase tracking-wider text-xs">%</th>
                        <th class="px-4 py-3 text-right font-bold uppercase tracking-wider text-xs">Valor</th>
                        <th class="px-4 py-3 text-center font-bold uppercase tracking-wider text-xs">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($splits as $split)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                #{{ $split->order_id }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $split->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">
                                {{ number_format($split->percentage, 2, ',', '.') }}%
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                R$ {{ number_format($split->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($split->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                        <i class="fas fa-check text-[9px]"></i> Pago
                                    </span>
                                @elseif($split->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                        <i class="fas fa-times text-[9px]"></i> Rejeitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                        <i class="fas fa-clock text-[9px]"></i> Pendente
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                                <p class="text-slate-500 font-medium">Nenhum split registrado ainda.</p>
                                <p class="text-xs text-slate-400 mt-1">Assim que houver vendas concluidas, os valores aparecem aqui.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info card --}}
    <div class="mt-6 rounded-2xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="text-sm text-purple-900 dark:text-purple-200">
                <p class="font-bold mb-1">Como funciona</p>
                <p class="opacity-90">
                    A cada venda concluida na plataforma, {{ number_format($percent, 2, ',', '.') }}% do valor e reservado para as acoes de marketing e trafego pago.
                    Os pagamentos sao liberados manualmente pelo administrador apos confirmacao do repasse.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
