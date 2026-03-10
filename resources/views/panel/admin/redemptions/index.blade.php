@extends('panel.layouts.app')

@section('title', 'Gestao de Resgates')

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $statusLabels = [
        'pending' => 'Pendente',
        'processing' => 'Em separacao',
        'shipped' => 'Enviado',
        'completed' => 'Concluido',
        'cancelled' => 'Cancelado',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'shipped' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
@endphp

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                    <i class="fas fa-exchange-alt text-[10px]"></i>
                    {{ $canManageAllRedemptions ? 'Administracao global' : 'Minha operacao de entrega' }}
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Resgates por {{ $coinName }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Cadastre itens, acompanhe aprovacoes, gerencie envio e preserve o responsavel fixo pela entrega.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Cotacao atual</p>
                    <p class="mt-1 font-black text-slate-900 dark:text-white">
                        {{ number_format((int) ($exchangeSettings['base_points'] ?? 0), 0, ',', '.') }} {{ $coinName }} = R$ {{ number_format((float) ($exchangeSettings['base_amount'] ?? 0), 2, ',', '.') }}
                    </p>
                </div>
                <a href="{{ route('panel.admin.redemptions.create') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                    <i class="fas fa-plus"></i>
                    Novo item
                </a>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">Catalogo de itens</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Itens disponiveis para troca de {{ $coinName }} com valor de referencia e fornecedor travado.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left">
                        <thead class="bg-slate-50 dark:bg-slate-950/50">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                <th class="px-6 py-4">Item</th>
                                <th class="px-6 py-4">Tipo</th>
                                <th class="px-6 py-4">Fornecedor</th>
                                <th class="px-6 py-4 text-right">Valor ref.</th>
                                <th class="px-6 py-4 text-right">{{ $coinName }}</th>
                                <th class="px-6 py-4 text-center">Prazo</th>
                                <th class="px-6 py-4 text-center">Estoque</th>
                                <th class="px-6 py-4 text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($items as $item)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-950/30">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-14 w-14 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
                                                @if($item->image)
                                                    <img src="{{ \App\Support\UploadStorage::url($item->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full items-center justify-center text-slate-300 dark:text-slate-700">
                                                        <i class="fas fa-gift"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $item->name }}</div>
                                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="rounded-full px-2.5 py-1 font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                        {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                                                    </span>
                                                    <span class="text-slate-400 dark:text-slate-500">{{ $item->redemptions_count }} resgates</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-300">{{ $item->item_type_label }}</td>
                                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-300">{{ $item->provider_label }}</td>
                                    <td class="px-6 py-5 text-right text-sm font-bold text-slate-700 dark:text-slate-300">
                                        {{ $item->reference_value !== null ? 'R$ ' . number_format((float) $item->reference_value, 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm font-black text-blue-600 dark:text-blue-300">
                                        {{ number_format((int) $item->points_cost, 0, ',', '.') }} {{ $coinName }}
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        {{ (int) ($item->delivery_lead_days ?? 7) }} dias
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        {{ (int) $item->stock < 0 ? 'Ilimitado' : number_format((int) $item->stock, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('panel.admin.redemptions.edit', $item) }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition-all hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                                                <i class="fas fa-pen"></i>
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Nenhum item cadastrado ate o momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                        {{ $items->links() }}
                    </div>
                @endif
            </section>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Solicitacoes pendentes</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pedidos aguardando aprovacao.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ $pendingRedemptions->total() }}</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse($pendingRedemptions as $redemption)
                            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-black text-slate-900 dark:text-white">{{ $redemption->user->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $redemption->item->name ?? 'Item removido' }}</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $redemption->item_type_label }} · {{ $redemption->provider_label }}</div>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] {{ $statusClasses[$redemption->status] ?? $statusClasses['pending'] }}">
                                        {{ $statusLabels[$redemption->status] ?? ucfirst($redemption->status) }}
                                    </span>
                                </div>
                                <div class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ number_format((int) $redemption->points_spent, 0, ',', '.') }} {{ $coinName }}</span>
                                    <span>{{ $redemption->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <form action="{{ route('panel.admin.redemptions.approve', $redemption) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-emerald-700">
                                            Aprovar
                                        </button>
                                    </form>
                                    <form action="{{ route('panel.admin.redemptions.cancel', $redemption) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full rounded-2xl bg-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 transition-all hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            Cancelar
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                Nenhuma solicitacao pendente.
                            </div>
                        @endforelse
                    </div>

                    @if($pendingRedemptions->hasPages())
                        <div class="mt-4">
                            {{ $pendingRedemptions->links() }}
                        </div>
                    @endif
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Entregas em andamento</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rastreio, observacoes e conclusao.</p>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $deliveryRedemptions->total() }}</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse($deliveryRedemptions as $redemption)
                            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-black text-slate-900 dark:text-white">{{ $redemption->item->name ?? 'Item removido' }}</div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $redemption->user->name }} · {{ $redemption->provider_label }}</div>
                                        <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                            Previsao: {{ optional($redemption->estimated_delivery_at)->format('d/m/Y') ?: 'nao definida' }}
                                        </div>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] {{ $statusClasses[$redemption->status] ?? $statusClasses['processing'] }}">
                                        {{ $statusLabels[$redemption->status] ?? ucfirst($redemption->status) }}
                                    </span>
                                </div>

                                <form action="{{ route('panel.admin.redemptions.ship', $redemption) }}" method="POST" class="mt-4 space-y-3">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="tracking_code" value="{{ old('tracking_code', $redemption->tracking_code) }}"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                            placeholder="Codigo de rastreio">
                                        <input type="url" name="tracking_url" value="{{ old('tracking_url', $redemption->tracking_url) }}"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                            placeholder="URL de rastreio">
                                    </div>
                                    <textarea name="delivery_notes" rows="2"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                        placeholder="Observacoes sobre preparacao, envio ou entrega">{{ old('delivery_notes', $redemption->delivery_notes) }}</textarea>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="submit"
                                            class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-indigo-700">
                                            Marcar enviado
                                        </button>
                                        <button type="submit" formaction="{{ route('panel.admin.redemptions.complete', $redemption) }}"
                                            class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-emerald-700">
                                            Entregue
                                        </button>
                                        <button type="submit" formaction="{{ route('panel.admin.redemptions.cancel', $redemption) }}"
                                            class="rounded-2xl bg-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 transition-all hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                Nenhuma entrega em andamento.
                            </div>
                        @endforelse
                    </div>

                    @if($deliveryRedemptions->hasPages())
                        <div class="mt-4">
                            {{ $deliveryRedemptions->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection
