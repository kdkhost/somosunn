@extends('panel.layouts.app')

@section('title', 'Meu Histórico de Resgates')

@section('panel_content')
    @php
        $statusClasses = [
            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            'shipped' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        ];

        $statusLabels = [
            'pending' => 'Pendente',
            'processing' => 'Em separação',
            'shipped' => 'Enviado',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
        ];
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Meus Resgates
                </h1>
                <p class="mt-1 font-medium text-slate-500 dark:text-slate-400">
                    Acompanhe aprovação, envio, prazo e responsável pela entrega dos seus resgates.
                </p>
            </div>

            <a href="{{ route('panel.redemptions.shop') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                <i class="fas fa-store"></i>
                Voltar para a Loja
            </a>
        </div>

        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/50">
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Item</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-center">Pontos</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Data do pedido</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Entrega</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($redemptions as $redemption)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-800">
                                            @if($redemption->item && $redemption->item->image)
                                                <img src="{{ asset('storage/' . $redemption->item->image) }}" alt="{{ $redemption->item->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full items-center justify-center">
                                                    <i class="fas fa-gift text-lg"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                {{ $redemption->item->name ?? 'Item removido' }}
                                            </div>
                                            <div class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                                                Cód. #{{ $redemption->id }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                                Vendido/distribuído por: {{ $redemption->provider_label }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="font-black text-blue-600 dark:text-blue-400">
                                        {{ number_format((int) $redemption->points_spent, 0, ',', '.') }} pts
                                    </span>
                                    @if($redemption->reference_value !== null)
                                        <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                            R$ {{ number_format((float) $redemption->reference_value, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ $redemption->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500">
                                        às {{ $redemption->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                                        <div>
                                            Previsão:
                                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                                {{ optional($redemption->estimated_delivery_at)->format('d/m/Y') ?: 'Não informada' }}
                                            </span>
                                        </div>
                                        @if($redemption->tracking_code)
                                            <div>
                                                Rastreio:
                                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $redemption->tracking_code }}</span>
                                            </div>
                                        @endif
                                        @if($redemption->tracking_url)
                                            <a href="{{ $redemption->tracking_url }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:underline dark:text-blue-400">
                                                Acompanhar entrega
                                                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                            </a>
                                        @endif
                                        @if($redemption->delivery_notes)
                                            <div class="line-clamp-2">
                                                {{ $redemption->delivery_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $statusClasses[$redemption->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $statusLabels[$redemption->status] ?? ucfirst((string) $redemption->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="mb-4 text-6xl text-slate-300 opacity-10 dark:text-slate-700">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <p class="text-lg font-bold text-slate-500 dark:text-slate-400">Você ainda não realizou nenhum resgate.</p>
                                    <p class="mt-1 text-sm text-slate-400 dark:text-slate-600">Comece a acumular pontos para ganhar prêmios.</p>
                                    <a href="{{ route('panel.redemptions.shop') }}"
                                        class="mt-6 inline-flex text-sm font-black text-blue-600 transition-all hover:underline dark:text-blue-400">
                                        Ver itens disponíveis
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($redemptions->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                    {{ $redemptions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
