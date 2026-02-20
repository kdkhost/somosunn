@extends('panel.layouts.app')

@section('title', 'Meu Histórico de Resgates')

@section('panel_content')
    <div class="space-y-8">
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                    Meus Resgates
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium transition-colors">Acompanhe o status dos seus
                    pedidos de prêmios.</p>
            </div>

            <a href="{{ route('redemptions.shop') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                <i class="fas fa-store"></i>
                Voltar para a Loja
            </a>
        </div>

        {{-- History Table/List --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-100 dark:border-slate-800 transition-colors">
                            <th
                                class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Item</th>
                            <th
                                class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-center">
                                Pontos</th>
                            <th
                                class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Data do Pedido</th>
                            <th
                                class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-center">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 transition-colors">
                        @forelse($redemptions as $redemption)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden text-slate-400 transition-colors border border-slate-200 dark:border-slate-700">
                                            @if($redemption->item && $redemption->item->image)
                                                <img src="{{ asset('storage/' . $redemption->item->image) }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-gift text-lg"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white transition-colors">
                                                {{ $redemption->item->name ?? 'Item Removido' }}
                                            </div>
                                            <div
                                                class="text-xs text-slate-500 dark:text-slate-400 font-medium transition-colors">
                                                Cod: #{{ $redemption->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="font-black text-blue-600 dark:text-blue-400 transition-colors">
                                        {{ number_format($redemption->points_spent, 0, ',', '.') }} pts
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-slate-600 dark:text-slate-300 font-bold transition-colors">
                                        {{ $redemption->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 transition-colors">
                                        às {{ $redemption->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendente',
                                            'completed' => 'Concluído',
                                            'cancelled' => 'Cancelado',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statusClasses[$redemption->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$redemption->status] ?? $redemption->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="text-slate-300 dark:text-slate-700 text-6xl mb-4 opacity-10">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 font-bold text-lg transition-colors">Você ainda
                                        não realizou nenhum resgate.</p>
                                    <p class="text-slate-400 dark:text-slate-600 text-sm mt-1 transition-colors">Comece a
                                        acumular pontos para ganhar prêmios!</p>
                                    <a href="{{ route('redemptions.shop') }}"
                                        class="inline-flex mt-6 text-blue-600 dark:text-blue-400 font-black hover:underline transition-all">Ver
                                        Itens Disponíveis</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection