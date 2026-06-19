@extends('panel.layouts.app')

@section('title', 'Cupons do Evento')

@section('panel_content')
@php
    $eventDeadline = $event->publicDeadlineAt();
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-950 dark:text-white">Cupons de gratuidade</h1>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $event->title }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route($eventsRoutePrefix . '.edit', $event) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route($routePrefix . '.create', $event) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white hover:bg-blue-700">
                <i class="fas fa-plus"></i> Novo cupom
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-black uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                    <tr>
                        <th class="px-5 py-4">Codigo</th>
                        <th class="px-5 py-4">Tipo</th>
                        <th class="px-5 py-4">Usos</th>
                        <th class="px-5 py-4">Validade</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($coupons as $coupon)
                        @php
                            $effectiveExpiresAt = $coupon->expires_at;
                            if ($eventDeadline && (!$effectiveExpiresAt || $eventDeadline->lt($effectiveExpiresAt))) {
                                $effectiveExpiresAt = $eventDeadline;
                            }
                        @endphp
                        <tr>
                            <td class="px-5 py-4"><span class="rounded-lg bg-slate-900 px-3 py-1 font-mono text-xs font-black text-white">{{ $coupon->code }}</span></td>
                            <td class="px-5 py-4">
                                @if($coupon->type === \App\Models\EventCoupon::TYPE_FREE)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">Gratuidade total</span>
                                @elseif($coupon->type === \App\Models\EventCoupon::TYPE_PERCENT)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 dark:bg-blue-500/15 dark:text-blue-200">{{ number_format((float) $coupon->discount_value, 2, ',', '.') }}%</span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-black text-yellow-900 dark:bg-yellow-500/15 dark:text-yellow-100">R$ {{ number_format((float) $coupon->discount_value, 2, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-700 dark:text-slate-200">{{ (int) $coupon->used_count }} / {{ $coupon->max_uses ? (int) $coupon->max_uses : 'ilimitado' }}</td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-500">
                                @if($coupon->starts_at)<div>Inicio: {{ $coupon->starts_at->format('d/m/Y H:i') }}</div>@endif
                                @if($effectiveExpiresAt)<div>Fim efetivo: {{ $effectiveExpiresAt->format('d/m/Y H:i') }}</div>@endif
                                @if(!$coupon->starts_at && !$effectiveExpiresAt)<span>Sem periodo definido</span>@endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $coupon->active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $coupon->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <form method="POST" action="{{ route($routePrefix . '.toggle', [$event, $coupon]) }}">
                                        @csrf
                                        <button class="rounded-lg border border-slate-200 px-3 py-2 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" title="{{ $coupon->active ? 'Desativar' : 'Ativar' }}">
                                            <i class="fas fa-toggle-{{ $coupon->active ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route($routePrefix . '.edit', [$event, $coupon]) }}" class="rounded-lg border border-blue-100 px-3 py-2 text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route($routePrefix . '.destroy', [$event, $coupon]) }}"
                                        class="js-confirm-action" data-confirm-title="Excluir cupom"
                                        data-confirm-text="Excluir este cupom?" data-confirm-button="Excluir">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-100 px-3 py-2 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">Nenhum cupom cadastrado para este evento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-confirm-action').forEach((form) => {
                if (form.dataset.bound === '1') return;
                form.dataset.bound = '1';

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        icon: 'question',
                        title: form.dataset.confirmTitle || 'Confirmar acao',
                        text: form.dataset.confirmText || 'Deseja continuar?',
                        showCancelButton: true,
                        confirmButtonText: form.dataset.confirmButton || 'Confirmar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
