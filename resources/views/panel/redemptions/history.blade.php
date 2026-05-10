@extends('panel.layouts.app')

@section('title', 'Meu Historico de Resgates')

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $statusClasses = [
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'shipped' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];

    $statusLabels = [
        'pending' => 'Pendente',
        'processing' => 'Em separacao',
        'shipped' => 'Enviado',
        'completed' => 'Concluido',
        'cancelled' => 'Cancelado',
    ];
@endphp

@section('panel_content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-white/75 mb-2">
                    <i class="fas fa-history"></i> Historico
                </div>
                <h1 class="text-2xl md:text-3xl font-black mb-1">Meus Resgates</h1>
                <p class="text-sm text-white/85 max-w-2xl">
                    Acompanhe aprovacao, envio, prazo e responsavel pela entrega dos seus resgates em {{ $coinName }}.
                </p>
            </div>

            <a href="{{ route('panel.redemptions.shop') }}"
                class="inline-flex items-center gap-2 rounded-full bg-white text-slate-900 px-5 py-2.5 text-sm font-black shadow-lg hover:bg-slate-100 transition-all">
                <i class="fas fa-store"></i> Voltar para a Loja
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px] text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/50">
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Item</th>
                            <th class="px-8 py-5 text-center text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $coinName }}</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Data do pedido</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Entrega</th>
                            <th class="px-8 py-5 text-center text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($redemptions as $redemption)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-800">
                                            @if($redemption->item && $redemption->item->image)
                                                <img src="{{ \App\Support\UploadStorage::url($redemption->item->image) }}" alt="{{ $redemption->item->name }}" class="h-full w-full object-cover">
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
                                                Cod. #{{ $redemption->id }} · {{ $redemption->item_type_label }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                                Fornecedor: {{ $redemption->provider_label }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="font-black text-blue-600 dark:text-blue-400">
                                        {{ number_format((int) $redemption->points_spent, 0, ',', '.') }} {{ $coinName }}
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
                                        as {{ $redemption->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                                        <div>
                                            Previsao:
                                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                                {{ optional($redemption->estimated_delivery_at)->format('d/m/Y') ?: 'Nao informada' }}
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
                                        @if($redemption->fulfillment_instructions)
                                            <div class="line-clamp-2">
                                                {{ strip_tags((string) $redemption->fulfillment_instructions) }}
                                            </div>
                                        @elseif($redemption->delivery_notes)
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
                                    <p class="text-lg font-bold text-slate-500 dark:text-slate-400">Voce ainda nao realizou nenhum resgate.</p>
                                    <p class="mt-1 text-sm text-slate-400 dark:text-slate-600">Comece a acumular {{ $coinName }} para ganhar premios.</p>
                                    <a href="{{ route('panel.redemptions.shop') }}"
                                        class="mt-6 inline-flex text-sm font-black text-blue-600 transition-all hover:underline dark:text-blue-400">
                                        Ver itens disponiveis
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
